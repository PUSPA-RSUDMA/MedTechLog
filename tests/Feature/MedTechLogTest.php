<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PriorityLevel;
use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Models\DiklatTask;
use App\Models\DiklatUnit;
use App\Models\LogbookEvent;
use App\Models\Pic;
use App\Models\Profession;
use App\Models\Task;
use App\Models\Unit;
use App\Models\User;
use App\Models\WaMessage;
use App\Models\WaSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MedTechLogTest extends TestCase
{
    use RefreshDatabase;

    private Unit $unitIt;
    private Unit $unitRm;
    private DiklatUnit $diklatUnit;
    private Pic $picIt;
    private Profession $profession;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unitIt = Unit::create(['code' => 'IT-SIMRS', 'name' => 'IT & SIMRS', 'is_active' => true]);
        $this->unitRm = Unit::create(['code' => 'RM', 'name' => 'Rekam Medis', 'is_active' => true]);
        $this->diklatUnit = DiklatUnit::create(['code' => 'IGD', 'name' => 'Instalasi Gawat Darurat', 'is_active' => true]);
        $this->picIt = Pic::create(['name' => 'Andi Pratama', 'phone' => '081234567890', 'unit_id' => $this->unitIt->id, 'is_active' => true]);
        $this->profession = Profession::create(['code' => 'PRW', 'name' => 'Perawat', 'is_active' => true]);

        $this->admin = User::factory()->create([
            'role' => UserRole::Admin,
            'unit_id' => $this->unitIt->id,
            'is_active' => true,
        ]);

        WaSetting::current()->update([
            'default_target_phone' => '081299999999',
            'auto_remind_tasks' => true,
            'auto_remind_logbook' => true,
        ]);
    }

    public function test_admin_pages_and_master_data_render_cleanly(): void
    {
        $this->actingAs($this->admin)->get(route('dashboard'))->assertOk();
        $this->actingAs($this->admin)->get(route('tasks.it'))->assertOk();
        $this->actingAs($this->admin)->get(route('tasks.rm'))->assertOk();
        $this->actingAs($this->admin)->get(route('diklat.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('logbook.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('wa-manager.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('reports.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('master.units.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('master.diklat-units.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('master.pics.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('master.professions.index'))->assertOk();
    }

    public function test_it_rm_task_auto_completes_at_100_and_tracks_due_date_history_and_reminders(): void
    {
        $dueH2 = now()->addDays(2)->toDateString();

        // 1. Create Tugas IT (target paten 100, H-2 triggers reminder + WA queue)
        $this->actingAs($this->admin)->post(route('tasks.store'), [
            'department' => 'it',
            'title' => 'Integrasi Bridging SIMRS',
            'pic_id' => $this->picIt->id,
            'estimated_start_date' => now()->toDateString(),
            'due_date' => $dueH2,
            'priority' => PriorityLevel::High->value,
        ])->assertRedirect();

        $task = Task::firstOrFail();
        $this->assertEquals('it', $task->department);
        $this->assertEquals(100, (int) $task->estimated_output);
        $this->assertNotNull($task->reminded_h2_at);
        $this->assertGreaterThanOrEqual(1, $this->admin->unreadNotifications()->count());
        $this->assertTrue(WaMessage::where('category', 'task_h2')->exists());

        // 2. Update tanggal target -> records history
        $newDue = now()->addDays(15)->toDateString();
        $this->actingAs($this->admin)->post(route('tasks.update-due-date', $task), [
            'new_due_date' => $newDue,
            'reason' => 'Diperpanjang karena menunggu vendor',
        ])->assertRedirect(route('tasks.show', $task));

        $task->refresh();
        $this->assertEquals($newDue, $task->due_date->toDateString());
        $this->assertEquals(1, $task->dueDateHistories()->count());
        $this->assertEquals('Diperpanjang karena menunggu vendor', $task->dueDateHistories()->first()->reason);

        // 3. Update ketercapaian 40% -> still in progress
        $this->actingAs($this->admin)->post(route('work-logs.store'), [
            'task_id' => $task->id,
            'log_date' => now()->toDateString(),
            'output_achieved' => 40,
            'activity_description' => 'Progres awal integrasi',
            'status_after_log' => TaskStatus::InProgress->value,
        ])->assertRedirect(route('tasks.show', $task));

        $task->refresh();
        $this->assertEquals(40, $task->progress_percent);
        $this->assertEquals(TaskStatus::InProgress, $task->status);

        // 4. Update +60% -> total 100% -> auto Completed without verification
        $this->actingAs($this->admin)->post(route('work-logs.store'), [
            'task_id' => $task->id,
            'log_date' => now()->toDateString(),
            'output_achieved' => 60,
            'activity_description' => 'Selesai penuh 100%',
            'status_after_log' => TaskStatus::InProgress->value,
        ])->assertRedirect(route('tasks.show', $task));

        $task->refresh();
        $this->assertEquals(100, $task->progress_percent);
        $this->assertEquals(TaskStatus::Completed, $task->status);
        $this->assertNotNull($task->actual_completed_at);

        // 5. Reports PDF & Excel export
        $this->actingAs($this->admin)->get(route('reports.export-pdf'))->assertOk();
        $this->actingAs($this->admin)->get(route('reports.export-excel'))->assertOk();
    }

    public function test_diklat_task_and_logbook_wa_manager(): void
    {
        Storage::fake('public');

        // 1. Tugas Diklat CRUD with file upload (using DiklatUnit)
        $file = UploadedFile::fake()->create('materi-diklat.pdf', 200, 'application/pdf');
        $this->actingAs($this->admin)->post(route('diklat.store'), [
            'participant_name' => 'Ns. Budi Santoso',
            'unit_id' => $this->diklatUnit->id,
            'profession_id' => $this->profession->id,
            'material' => 'Pelatihan RME & Koding ICD-10',
            'event_date' => now()->toDateString(),
            'file' => $file,
        ])->assertRedirect(route('diklat.index'));

        $diklat = DiklatTask::firstOrFail();
        $this->assertEquals('Ns. Budi Santoso', $diklat->participant_name);
        $this->assertNotNull($diklat->file_path);

        // 2. Logbook Event H-1 triggers both in-app notification & WA Manager message
        $tomorrow = now()->addDay()->toDateString();
        $this->actingAs($this->admin)->post(route('logbook.store'), [
            'event_date' => $tomorrow,
            'title' => 'Rapat Evaluasi Bulanan',
            'location' => 'Ruang Rapat',
            'color' => '#0F766E',
        ])->assertRedirect();

        $event = LogbookEvent::firstOrFail();
        $this->assertNotNull($event->reminder_sent_at);
        $this->assertTrue(WaMessage::where('category', 'logbook_h1')->exists());

        // 3. Manual WA test message & check-deadlines command
        $this->actingAs($this->admin)->post(route('wa-manager.send-test'), [
            'phone' => '081234567890',
            'message' => 'Tes WA Manager',
        ])->assertRedirect(route('wa-manager.index'));

        $this->artisan('app:check-deadlines')->assertSuccessful();
    }

    public function test_diklat_direct_upload_to_google_drive_without_local_storage(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'https://oauth2.googleapis.com/token' => \Illuminate\Support\Facades\Http::response(['access_token' => 'fake-token'], 200),
            'https://www.googleapis.com/drive/v3/files/folder-xyz*' => \Illuminate\Support\Facades\Http::response([
                'id' => 'folder-xyz',
                'mimeType' => 'application/vnd.google-apps.folder',
                'trashed' => false,
            ], 200),
            'https://www.googleapis.com/drive/v3/files?q=*' => \Illuminate\Support\Facades\Http::response(['files' => []], 200),
            'https://www.googleapis.com/drive/v3/files?fields=id' => \Illuminate\Support\Facades\Http::response(['id' => 'subfolder-id'], 200),
            'https://www.googleapis.com/upload/drive/v3/files*' => \Illuminate\Support\Facades\Http::response([
                'id' => 'gdrive-file-123',
                'name' => 'sertifikat.pdf',
                'webViewLink' => 'https://drive.google.com/file/d/gdrive-file-123/view',
            ], 200),
            'https://www.googleapis.com/drive/v3/files/gdrive-file-123/permissions' => \Illuminate\Support\Facades\Http::response(['id' => 'anyone'], 200),
            'https://www.googleapis.com/drive/v3/files/gdrive-file-123' => \Illuminate\Support\Facades\Http::response([], 204),
        ]);

        // Enable Google Drive setting via controller
        $this->actingAs($this->admin)->post(route('diklat.drive-settings'), [
            'is_enabled' => 1,
            'client_id' => 'test-client-id',
            'client_secret' => 'test-client-secret',
            'refresh_token' => 'test-refresh-token',
            'folder_id' => 'folder-xyz',
        ])->assertRedirect(route('diklat.index'));

        $file = UploadedFile::fake()->create('sertifikat.pdf', 150, 'application/pdf');
        $this->actingAs($this->admin)->post(route('diklat.store'), [
            'participant_name' => 'dr. Siti Aminah',
            'unit_id' => $this->diklatUnit->id,
            'profession_id' => $this->profession->id,
            'material' => 'Keselamatan Pasien',
            'file' => $file,
        ])->assertRedirect(route('diklat.index'));

        $diklat = DiklatTask::where('participant_name', 'dr. Siti Aminah')->firstOrFail();
        $this->assertNull($diklat->file_path); // zero local server storage!
        $this->assertEquals('gdrive-file-123', $diklat->drive_file_id);
        $this->assertEquals('https://drive.google.com/file/d/gdrive-file-123/view', $diklat->file_url);
        $this->assertTrue($diklat->is_google_drive);

        // Delete also triggers Google Drive delete
        $this->actingAs($this->admin)->delete(route('diklat.destroy', $diklat))->assertRedirect(route('diklat.index'));
        $this->assertDatabaseMissing('diklat_tasks', ['id' => $diklat->id]);
    }
}
