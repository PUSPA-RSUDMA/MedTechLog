<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\DiklatTask;
use App\Models\DiklatUnit;
use App\Models\GoogleDriveSetting;
use App\Models\Profession;
use App\Services\GoogleDriveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DiklatTaskController extends Controller
{
    public function __construct(
        private readonly GoogleDriveService $driveService,
    ) {}

    public function index(Request $request): View
    {
        $query = DiklatTask::query()->with(['unit', 'profession']);

        if ($request->filled('search')) {
            $s = $request->string('search')->toString();
            $query->where(fn ($q) => $q->where('participant_name', 'like', "%{$s}%")->orWhere('material', 'like', "%{$s}%"));
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->integer('unit_id'));
        }

        if ($request->filled('profession_id')) {
            $query->where('profession_id', $request->integer('profession_id'));
        }

        $diklatTasks = $query->latest()->paginate(15)->withQueryString();

        return view('diklat.index', [
            'diklatTasks' => $diklatTasks,
            'units' => DiklatUnit::active()->orderBy('name')->get(),
            'professions' => Profession::active()->orderBy('name')->get(),
            'driveSetting' => GoogleDriveSetting::current(),
            'driveConfigured' => $this->driveService->isConfigured(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'participant_name' => ['required', 'string', 'max:150'],
            'unit_id' => ['required', 'exists:diklat_units,id'],
            'profession_id' => ['required', 'exists:professions,id'],
            'material' => ['required', 'string', 'max:255'],
            'event_date' => ['nullable', 'date'],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png', 'max:10240'],
        ], [
            'participant_name.required' => 'Nama wajib diisi.',
            'unit_id.required' => 'Unit wajib dipilih.',
            'profession_id.required' => 'Profesi wajib dipilih.',
            'material.required' => 'Materi diklat wajib diisi.',
            'file.max' => 'Ukuran file maksimal 10MB.',
        ]);

        $filePath = null;
        $fileName = null;
        $driveFileId = null;
        $driveUrl = null;
        $uploadedToDrive = false;

        $eventDate = ! empty($data['event_date']) ? $data['event_date'] : now()->toDateString();

        if ($request->hasFile('file')) {
            $uploaded = $request->file('file');
            if ($this->driveService->isConfigured()) {
                try {
                    $result = $this->driveService->upload($uploaded, [$eventDate, $data['material']]);
                    $driveFileId = $result['file_id'];
                    $driveUrl = $result['web_view_link'];
                    $fileName = $result['file_name'];
                    $uploadedToDrive = true;
                } catch (\Throwable $e) {
                    return back()->withInput()->with('error', 'Upload ke Google Drive gagal: ' . $e->getMessage());
                }
            } else {
                $filePath = $uploaded->store('diklat', 'public');
                $fileName = $uploaded->getClientOriginalName();
            }
        }

        DiklatTask::create([
            'participant_name' => $data['participant_name'],
            'unit_id' => $data['unit_id'],
            'profession_id' => $data['profession_id'],
            'material' => $data['material'],
            'event_date' => $eventDate,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'drive_file_id' => $driveFileId,
            'drive_url' => $driveUrl,
            'created_by' => $request->user()->id,
        ]);

        $msg = $uploadedToDrive
            ? 'Data Tugas Diklat berhasil disimpan dan file diunggah langsung ke Google Drive.'
            : 'Data Tugas Diklat berhasil ditambahkan.';

        return redirect()->route('diklat.index')->with('success', $msg);
    }

    public function update(Request $request, DiklatTask $diklatTask): RedirectResponse
    {
        $data = $request->validate([
            'participant_name' => ['required', 'string', 'max:150'],
            'unit_id' => ['required', 'exists:diklat_units,id'],
            'profession_id' => ['required', 'exists:professions,id'],
            'material' => ['required', 'string', 'max:255'],
            'event_date' => ['nullable', 'date'],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png', 'max:10240'],
        ]);

        $payload = [
            'participant_name' => $data['participant_name'],
            'unit_id' => $data['unit_id'],
            'profession_id' => $data['profession_id'],
            'material' => $data['material'],
            'event_date' => ! empty($data['event_date']) ? $data['event_date'] : $diklatTask->event_date,
        ];

        if ($request->hasFile('file')) {
            $uploaded = $request->file('file');
            $folderDate = $payload['event_date'] instanceof \Carbon\Carbon
                ? $payload['event_date']->format('Y-m-d')
                : (string) ($payload['event_date'] ?: now()->toDateString());

            if ($this->driveService->isConfigured()) {
                try {
                    $result = $this->driveService->upload($uploaded, [$folderDate, $data['material']]);

                    if ($diklatTask->drive_file_id) {
                        $this->driveService->delete($diklatTask->drive_file_id);
                    }
                    if ($diklatTask->file_path) {
                        Storage::disk('public')->delete($diklatTask->file_path);
                    }

                    $payload['drive_file_id'] = $result['file_id'];
                    $payload['drive_url'] = $result['web_view_link'];
                    $payload['file_name'] = $result['file_name'];
                    $payload['file_path'] = null;
                } catch (\Throwable $e) {
                    return back()->with('error', 'Upload file baru ke Google Drive gagal: ' . $e->getMessage());
                }
            } else {
                if ($diklatTask->file_path) {
                    Storage::disk('public')->delete($diklatTask->file_path);
                }
                $payload['file_path'] = $uploaded->store('diklat', 'public');
                $payload['file_name'] = $uploaded->getClientOriginalName();
                $payload['drive_file_id'] = null;
                $payload['drive_url'] = null;
            }
        }

        $diklatTask->update($payload);

        return redirect()->route('diklat.index')->with('success', 'Data Tugas Diklat berhasil diperbarui.');
    }

    public function destroy(DiklatTask $diklatTask): RedirectResponse
    {
        if ($diklatTask->drive_file_id) {
            $this->driveService->delete($diklatTask->drive_file_id);
        }

        if ($diklatTask->file_path) {
            Storage::disk('public')->delete($diklatTask->file_path);
        }

        $diklatTask->delete();

        return redirect()->route('diklat.index')->with('success', 'Data Tugas Diklat berhasil dihapus.');
    }

    public function saveDriveSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'is_enabled' => ['nullable', 'boolean'],
            'client_id' => ['nullable', 'string', 'max:255'],
            'client_secret' => ['nullable', 'string', 'max:255'],
            'refresh_token' => ['nullable', 'string'],
            'folder_id' => ['nullable', 'string', 'max:200'],
        ]);

        GoogleDriveSetting::current()->update([
            'is_enabled' => $request->boolean('is_enabled'),
            'client_id' => $data['client_id'] ?: null,
            'client_secret' => $data['client_secret'] ?: null,
            'refresh_token' => $data['refresh_token'] ?: null,
            'folder_id' => $data['folder_id'] ?: null,
        ]);

        return redirect()->route('diklat.index')->with('success', 'Pengaturan Google Drive berhasil disimpan.');
    }

    public function testDriveConnection(): RedirectResponse
    {
        $result = $this->driveService->testConnection();

        return redirect()->route('diklat.index')->with(
            $result['ok'] ? 'success' : 'error',
            $result['message']
        );
    }
}
