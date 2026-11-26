<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\GoogleDriveSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleDriveService
{
    public function isConfigured(): bool
    {
        $setting = GoogleDriveSetting::current();

        return $setting->is_enabled
            && ! empty($setting->client_id)
            && ! empty($setting->client_secret)
            && ! empty($setting->refresh_token);
    }

    public function getAccessToken(bool $forceRefresh = false): string
    {
        $setting = GoogleDriveSetting::current();

        if (empty($setting->client_id) || empty($setting->client_secret) || empty($setting->refresh_token)) {
            throw new RuntimeException('Kredensial Google Drive (Client ID, Client Secret, Refresh Token) belum lengkap.');
        }

        $cacheKey = 'google_drive_access_token_' . md5($setting->client_id . $setting->refresh_token);

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, now()->addMinutes(50), function () use ($setting): string {
            $response = Http::asForm()->timeout(15)->post('https://oauth2.googleapis.com/token', [
                'client_id' => $setting->client_id,
                'client_secret' => $setting->client_secret,
                'refresh_token' => $setting->refresh_token,
                'grant_type' => 'refresh_token',
            ]);

            if (! $response->successful() || empty($response->json('access_token'))) {
                throw new RuntimeException('Gagal mendapatkan Access Token Google Drive: ' . mb_substr($response->body(), 0, 200));
            }

            return (string) $response->json('access_token');
        });
    }

    /**
     * Upload a file directly to Google Drive via Multipart REST API v3 (zero local server storage).
     * Optionally nests the file inside ordered subfolders (e.g. ['2026-11-26', 'Nama Materi']).
     *
     * @param  list<string>  $subFolders
     * @return array{file_id: string, web_view_link: string, file_name: string}
     */
    public function upload(UploadedFile $file, array $subFolders = []): array
    {
        $setting = GoogleDriveSetting::current();
        $accessToken = $this->getAccessToken();

        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';

        $rootFolderId = null;
        if (! empty($setting->folder_id)) {
            $rootFolderId = $this->resolveFolderId($accessToken, $setting->folder_id);
        }

        $parentId = $rootFolderId;

        foreach ($subFolders as $folderName) {
            $cleanName = trim(preg_replace('/[\\\\\/]+/', '-', $folderName) ?? '');
            if ($cleanName === '') {
                continue;
            }
            $cleanName = mb_substr($cleanName, 0, 120);
            $nextId = $this->findOrCreateSubFolder($accessToken, $cleanName, $parentId);
            if ($nextId !== null) {
                $parentId = $nextId;
            }
        }

        $metadata = ['name' => $originalName];
        if ($parentId !== null) {
            $metadata['parents'] = [$parentId];
        }

        $boundary = 'medtechlog_boundary_' . uniqid();
        $fileContents = file_get_contents($file->getRealPath());

        $body = "--{$boundary}\r\n"
            . "Content-Type: application/json; charset=UTF-8\r\n\r\n"
            . json_encode($metadata) . "\r\n"
            . "--{$boundary}\r\n"
            . "Content-Type: {$mimeType}\r\n\r\n"
            . $fileContents . "\r\n"
            . "--{$boundary}--";

        $response = Http::withToken($accessToken)
            ->timeout(60)
            ->withBody($body, "multipart/related; boundary={$boundary}")
            ->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&fields=id,name,webViewLink');

        if (! $response->successful() || empty($response->json('id'))) {
            throw new RuntimeException('Gagal mengunggah file ke Google Drive: ' . mb_substr($response->body(), 0, 250));
        }

        $fileId = (string) $response->json('id');
        $webViewLink = (string) ($response->json('webViewLink') ?: "https://drive.google.com/file/d/{$fileId}/view");

        // Share the root DIKLAT folder once (cached) instead of calling /permissions on every single file upload
        if ($rootFolderId !== null) {
            $this->ensureFolderReadableOnce($accessToken, $rootFolderId);
        }

        return [
            'file_id' => $fileId,
            'web_view_link' => $webViewLink,
            'file_name' => $originalName,
        ];
    }

    public function delete(string $fileId): void
    {
        if (! $this->isConfigured() || empty($fileId)) {
            return;
        }

        try {
            $accessToken = $this->getAccessToken();
            Http::withToken($accessToken)
                ->timeout(15)
                ->delete("https://www.googleapis.com/drive/v3/files/{$fileId}");
        } catch (\Throwable) {
            // Silently ignore if file was already deleted on Google Drive
        }
    }

    /**
     * Accepts a Google Drive Folder ID, a full Drive folder URL, or a plain folder name (e.g. "DIKLAT").
     * If a plain folder name is provided, finds an existing folder with that name or creates it automatically.
     */
    private function resolveFolderId(string $accessToken, string $rawInput): ?string
    {
        $input = trim($rawInput);
        if ($input === '') {
            return null;
        }

        // Extract ID if user pasted full Google Drive folder URL
        if (preg_match('#/folders/([a-zA-Z0-9_-]+)#', $input, $m)) {
            $input = $m[1];
        }

        $cacheKey = 'google_drive_folder_id_' . md5($input);

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($accessToken, $input): ?string {
            // 1. Only probe as direct Drive ID if it looks like a Drive ID (>= 20 chars, no spaces)
            if (strlen($input) >= 20 && ! str_contains($input, ' ')) {
                $check = Http::withToken($accessToken)
                    ->timeout(8)
                    ->get("https://www.googleapis.com/drive/v3/files/{$input}", [
                        'fields' => 'id,mimeType,trashed',
                    ]);

                if ($check->successful() && $check->json('mimeType') === 'application/vnd.google-apps.folder' && ! $check->json('trashed')) {
                    return (string) $check->json('id');
                }
            }

            // 2. Otherwise treat $input as a folder name: search for existing folder
            $escapedName = str_replace("'", "\\'", $input);
            $search = Http::withToken($accessToken)
                ->timeout(10)
                ->get('https://www.googleapis.com/drive/v3/files', [
                    'q' => "name = '{$escapedName}' and mimeType = 'application/vnd.google-apps.folder' and trashed = false",
                    'fields' => 'files(id,name)',
                    'pageSize' => 1,
                ]);

            if ($search->successful() && ! empty($search->json('files.0.id'))) {
                return (string) $search->json('files.0.id');
            }

            // 3. Auto-create the folder if not found
            $create = Http::withToken($accessToken)
                ->timeout(10)
                ->post('https://www.googleapis.com/drive/v3/files?fields=id', [
                    'name' => $input,
                    'mimeType' => 'application/vnd.google-apps.folder',
                ]);

            if ($create->successful() && ! empty($create->json('id'))) {
                return (string) $create->json('id');
            }

            return null;
        });
    }

    private function findOrCreateSubFolder(string $accessToken, string $name, ?string $parentId): ?string
    {
        $cacheKey = 'google_drive_subfolder_' . md5(($parentId ?? 'root') . '|' . $name);

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($accessToken, $name, $parentId): ?string {
            $escapedName = str_replace("'", "\\'", $name);
            $q = "name = '{$escapedName}' and mimeType = 'application/vnd.google-apps.folder' and trashed = false";
            if ($parentId !== null) {
                $q .= " and '{$parentId}' in parents";
            }

            $search = Http::withToken($accessToken)
                ->timeout(10)
                ->get('https://www.googleapis.com/drive/v3/files', [
                    'q' => $q,
                    'fields' => 'files(id,name)',
                    'pageSize' => 1,
                ]);

            if ($search->successful() && ! empty($search->json('files.0.id'))) {
                return (string) $search->json('files.0.id');
            }

            $payload = [
                'name' => $name,
                'mimeType' => 'application/vnd.google-apps.folder',
            ];
            if ($parentId !== null) {
                $payload['parents'] = [$parentId];
            }

            $create = Http::withToken($accessToken)
                ->timeout(10)
                ->post('https://www.googleapis.com/drive/v3/files?fields=id', $payload);

            if ($create->successful() && ! empty($create->json('id'))) {
                return (string) $create->json('id');
            }

            return null;
        });
    }

    private function ensureFolderReadableOnce(string $accessToken, string $folderId): void
    {
        $cacheKey = 'google_drive_folder_shared_' . md5($folderId);
        if (Cache::has($cacheKey)) {
            return;
        }

        try {
            Http::withToken($accessToken)
                ->timeout(8)
                ->post("https://www.googleapis.com/drive/v3/files/{$folderId}/permissions", [
                    'role' => 'reader',
                    'type' => 'anyone',
                ]);
            Cache::put($cacheKey, true, now()->addDays(7));
        } catch (\Throwable) {
            // Ignore permission errors
        }
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function testConnection(): array
    {
        try {
            $token = $this->getAccessToken(forceRefresh: true);
            $res = Http::withToken($token)
                ->timeout(10)
                ->get('https://www.googleapis.com/drive/v3/about?fields=user');

            if ($res->successful()) {
                $email = $res->json('user.emailAddress') ?? 'Akun Google Drive';

                return ['ok' => true, 'message' => "Terhubung ke Google Drive ({$email})."];
            }

            return ['ok' => false, 'message' => 'Gagal mengakses Google Drive API (HTTP ' . $res->status() . ').'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }
}
