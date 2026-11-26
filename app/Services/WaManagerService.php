<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\WaMessage;
use App\Models\WaSetting;
use Illuminate\Support\Facades\Http;

class WaManagerService
{
    public function send(
        string $phone,
        string $message,
        string $category = 'manual',
        ?string $recipientName = null,
    ): WaMessage {
        $setting = WaSetting::current();
        $normalizedPhone = $this->normalizePhone($phone);

        $record = WaMessage::create([
            'recipient_phone' => $normalizedPhone,
            'recipient_name' => $recipientName,
            'message' => $message,
            'category' => $category,
            'status' => 'pending',
        ]);

        if (empty($setting->gateway_url)) {
            return $record;
        }

        // Pesan uji coba/manual selalu bisa dikirim kapan saja selama gateway_url terisi.
        // Untuk reminder otomatis, tetap mengikuti status is_enabled.
        if ($category !== 'manual' && ! $setting->is_enabled) {
            return $record;
        }

        return $this->dispatchRecord($record, $setting);
    }

    public function retry(WaMessage $record): WaMessage
    {
        return $this->dispatchRecord($record, WaSetting::current());
    }

    /**
     * Check WA Web Bridge status & QR code (if available).
     *
     * @return array{connected: bool, status: string, qr: string|null, phone: string|null, error: string|null}
     */
    public function checkGatewayStatus(): array
    {
        $setting = WaSetting::current();

        if (empty($setting->gateway_url)) {
            return ['connected' => false, 'status' => 'disconnected', 'qr' => null, 'phone' => null, 'error' => 'URL WA Gateway belum dikonfigurasi.'];
        }

        try {
            $request = Http::timeout(5);
            if ($setting->api_key) {
                $request = $request->withHeaders(['X-API-KEY' => $setting->api_key]);
            }

            $baseUrl = rtrim($setting->gateway_url, '/');
            $statusUrl = str_ends_with($baseUrl, '/api') ? $baseUrl . '/status' : $baseUrl . '/api/status';
            $response = $request->get($statusUrl);

            if ($response->status() === 404 && ! str_ends_with($baseUrl, '/api')) {
                $response = $request->get($baseUrl . '/status');
            }

            if ($response->successful()) {
                $json = $response->json();
                $rawStatus = (string) ($json['status'] ?? '');
                $connected = (bool) ($json['connected'] ?? (strtoupper($rawStatus) === 'CONNECTED'));
                $status = $rawStatus !== '' ? $rawStatus : ($connected ? 'connected' : 'waiting_qr');
                $setting->update(['session_status' => $status]);

                return [
                    'connected' => $connected,
                    'status' => $status,
                    'qr' => $json['qr'] ?? null,
                    'phone' => $json['phone'] ?? null,
                    'error' => null,
                ];
            }

            $setting->update(['session_status' => 'disconnected']);

            return ['connected' => false, 'status' => 'error', 'qr' => null, 'phone' => null, 'error' => 'Gateway merespons HTTP ' . $response->status()];
        } catch (\Throwable $e) {
            $setting->update(['session_status' => 'disconnected']);

            return ['connected' => false, 'status' => 'offline', 'qr' => null, 'phone' => null, 'error' => 'WA Web Bridge belum aktif di ' . $setting->gateway_url];
        }
    }

    private function dispatchRecord(WaMessage $record, WaSetting $setting): WaMessage
    {
        if (empty($setting->gateway_url)) {
            $record->update([
                'status' => 'failed',
                'error_message' => 'URL WA Gateway belum dikonfigurasi.',
            ]);

            return $record->fresh();
        }

        try {
            $request = Http::timeout(15);
            if ($setting->api_key) {
                $request = $request->withHeaders(['X-API-KEY' => $setting->api_key]);
            }

            $baseUrl = rtrim($setting->gateway_url, '/');
            $payload = [
                'phone' => $record->recipient_phone,
                'number' => $record->recipient_phone,
                'message' => $record->message,
            ];

            // Prioritaskan endpoint wa-engine (/api/send), fallback ke /send-message
            $primaryUrl = str_ends_with($baseUrl, '/api') ? $baseUrl . '/send' : $baseUrl . '/api/send';
            $response = $request->post($primaryUrl, $payload);

            if ($response->status() === 404) {
                $response = $request->post($baseUrl . '/send-message', $payload);
            }

            if ($response->successful()) {
                $record->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'error_message' => null,
                ]);
                $setting->update(['session_status' => 'connected']);
            } else {
                $record->update([
                    'status' => 'failed',
                    'error_message' => 'HTTP ' . $response->status() . ': ' . mb_substr($response->body(), 0, 200),
                ]);
            }
        } catch (\Throwable $e) {
            $record->update([
                'status' => 'failed',
                'error_message' => 'Gateway tidak terjangkau: ' . mb_substr($e->getMessage(), 0, 180),
            ]);
        }

        return $record->fresh();
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }

        return $digits;
    }
}
