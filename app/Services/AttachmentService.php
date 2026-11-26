<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Attachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

class AttachmentService
{
    /**
     * @param  array<int, UploadedFile>  $files
     */
    public function storeFiles(Model $attachable, array $files, int $userId): void
    {
        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $path = $file->store('attachments', 'public');

            Attachment::create([
                'attachable_type' => $attachable->getMorphClass(),
                'attachable_id' => $attachable->getKey(),
                'uploaded_by' => $userId,
                'type' => 'file',
                'title' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }
    }

    public function storeUrl(Model $attachable, string $url, ?string $title, int $userId): Attachment
    {
        return Attachment::create([
            'attachable_type' => $attachable->getMorphClass(),
            'attachable_id' => $attachable->getKey(),
            'uploaded_by' => $userId,
            'type' => 'url',
            'title' => $title ?: $url,
            'external_url' => $url,
        ]);
    }
}
