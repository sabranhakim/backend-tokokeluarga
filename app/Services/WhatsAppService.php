<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $token;
    protected string $baseUrl;

    public function __construct()
    {
        $this->token = config('services.whatsapp.token', env('WHATSAPP_TOKEN', 'YOUR_TOKEN_HERE'));
        $this->baseUrl = 'https://api.fonnte.com/send';
    }

    /**
     * Mengirim pesan WhatsApp
     */
    public function sendMessage(string $target, string $message): bool
    {
        try {
            $target = preg_replace('/[^0-9]/', '', $target);

            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->asForm()->post($this->baseUrl, [
                'target'  => $target,
                'message' => $message,
                'delay'   => '2',
            ]);

            if ($response->successful()) {
                Log::info("WA Terkirim ke {$target}");
                return true;
            }

            Log::error("Gagal kirim WA ke {$target}: " . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error("Error WhatsAppService: " . $e->getMessage());
            return false;
        }
    }
}
