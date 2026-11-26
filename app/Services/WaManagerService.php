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

        if (! $setting->is_enabled || empty($setting->gateway_url)) {
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
            $request = Http::timeout(4);
            if ($setting->api_key) {
                $request = $request->withHeaders(['X-API-KEY' => $setting->api_key]);
            }

            $response = $request->get(rtrim($setting->gateway_url, '/') . '/status');

            if ($response->successful()) {
                $json = $response->json();
                $connected = (bool) ($json['connected'] ?? false);
                $status = (string) ($json['status'] ?? ($connected ? 'connected' : 'waiting_qr'));
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
        try {
            $request = Http::timeout(6);
            if ($setting->api_key) {
                $request = $request->withHeaders(['X-API-KEY' => $setting->api_key]);
            }

            $response = $request->post(rtrim($setting->gateway_url, '/') . '/send-message', [
                'phone' => $record->recipient_phone,
                'message' => $record->message,
            ]);

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
