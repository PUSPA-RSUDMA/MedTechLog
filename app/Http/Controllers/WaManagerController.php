<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\WaMessage;
use App\Models\WaSetting;
use App\Services\LogbookReminderService;
use App\Services\TaskDeadlineReminderService;
use App\Services\WaManagerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WaManagerController extends Controller
{
    public function __construct(
        private readonly WaManagerService $waService,
        private readonly TaskDeadlineReminderService $taskReminder,
        private readonly LogbookReminderService $logbookReminder,
    ) {}

    public function index(): View
    {
        $setting = WaSetting::current();
        $gatewayStatus = $this->waService->checkGatewayStatus();
        $messages = WaMessage::query()->latest()->paginate(15);

        return view('wa-manager.index', [
            'setting' => $setting->fresh(),
            'gatewayStatus' => $gatewayStatus,
            'messages' => $messages,
            'stats' => [
                'sent' => WaMessage::where('status', 'sent')->count(),
                'pending' => WaMessage::where('status', 'pending')->count(),
                'failed' => WaMessage::where('status', 'failed')->count(),
            ],
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'gateway_url' => ['required', 'url', 'max:255'],
            'api_key' => ['nullable', 'string', 'max:255'],
            'default_target_phone' => ['nullable', 'string', 'max:40'],
            'reminder_time' => ['required', 'date_format:H:i'],
            'is_enabled' => ['nullable', 'boolean'],
            'auto_remind_tasks' => ['nullable', 'boolean'],
            'auto_remind_logbook' => ['nullable', 'boolean'],
        ]);

        WaSetting::current()->update([
            'gateway_url' => rtrim($data['gateway_url'], '/'),
            'api_key' => $data['api_key'] ?: null,
            'default_target_phone' => $data['default_target_phone'] ?: null,
            'reminder_time' => $data['reminder_time'],
            'is_enabled' => $request->boolean('is_enabled'),
            'auto_remind_tasks' => $request->boolean('auto_remind_tasks'),
            'auto_remind_logbook' => $request->boolean('auto_remind_logbook'),
        ]);

        return redirect()->route('wa-manager.index')->with('success', 'Pengaturan WA Manager berhasil disimpan.');
    }

    public function sendTest(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:40'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $msg = $this->waService->send($data['phone'], $data['message'], 'manual', 'Pesan Manual');

        $flash = $msg->status === 'sent'
            ? 'Pesan WhatsApp berhasil terkirim.'
            : 'Pesan dicatat di antrean WA Manager (status: ' . strtoupper($msg->status) . '). Anda juga bisa klik "Buka WA Web" pada tabel riwayat.';

        return redirect()->route('wa-manager.index')->with('success', $flash);
    }

    public function runRemindersNow(): RedirectResponse
    {
        $taskRes = $this->taskReminder->sendDueReminders();
        $logbookSent = $this->logbookReminder->sendDueReminders();

        return redirect()->route('wa-manager.index')->with(
            'success',
            "Pengecekan reminder selesai: Tugas H-2 ({$taskRes['h2']}), Tugas H-1 ({$taskRes['h1']}), Logbook H-1 ({$logbookSent})."
        );
    }

    public function retryMessage(WaMessage $waMessage): RedirectResponse
    {
        $updated = $this->waService->retry($waMessage);

        return redirect()->route('wa-manager.index')->with(
            $updated->status === 'sent' ? 'success' : 'error',
            $updated->status === 'sent'
                ? 'Pesan berhasil dikirim ulang.'
                : 'Gagal mengirim ulang: ' . ($updated->error_message ?? 'Gateway offline')
        );
    }
}
