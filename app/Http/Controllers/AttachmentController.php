<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function destroy(Request $request, Attachment $attachment): RedirectResponse
    {
        $user = $request->user();
        if (! $user->isAdmin() && $attachment->uploaded_by !== $user->id) {
            abort(403, 'Anda tidak berhak menghapus lampiran ini.');
        }

        if ($attachment->isFile() && $attachment->file_path) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();

        return back()->with('success', 'Lampiran bukti dukung berhasil dihapus.');
    }
}
