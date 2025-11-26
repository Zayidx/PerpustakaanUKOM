<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class QrPayloadSignature
{
    /**
     * Tambahkan signature HMAC-SHA256 ke payload QR.
     *
     * Signature dibangun dari subset field penting (code, loan_id, student_id, admin_perpus_id, action, generated_at)
     * lalu di-hash menggunakan kunci rahasia `app.key` (di-decode bila berbentuk base64:...).
     * Pendekatan ini memastikan QR tidak bisa dimodifikasi tanpa kunci aplikasi
     * dan payload tetap dapat diverifikasi oleh sisi Admin.
     */
    public static function sign(array $payload): array
    {
        $payload['signature'] = static::buildSignature($payload); // Set signature dari payload terkanonisasi.

        return $payload;
    }

    /**
     * Validasi payload QR:
     * - Signature wajib ada dan harus sama dengan hasil perhitungan ulang HMAC.
     * - Opsional: cek usia QR (generated_at) agar kode lama/di-capture tidak berlaku.
     * - Mengembalikan true hanya bila semua syarat terpenuhi.
     */
    public static function isValid(array $payload, ?int $maxAgeMinutes = null): bool
    {
        if (empty($payload['signature'])) { // Wajib ada signature.
            return false;
        }

        $expected = static::buildSignature($payload); // Hitung ulang signature dari payload.

        if (! hash_equals($expected, (string) $payload['signature'])) { // Bandingkan dengan hash_equals untuk mencegah timing leak.
            return false;
        }

        if ($maxAgeMinutes !== null && isset($payload['generated_at'])) { // Batasi usia QR jika diminta.
            try {
                $generatedAt = Carbon::parse($payload['generated_at']); // Parsing timestamp generated_at.
            } catch (\Throwable) {
                return false;
            }

            if (Carbon::now()->diffInMinutes($generatedAt) > $maxAgeMinutes) { // Tolak jika melebihi batas menit.
                return false;
            }
        }

        return true;
    }

    /**
     * Bangun signature HMAC-SHA256 berbasis subset field yang dianggap sensitif.
     * Menggunakan JSON kanonis (tanpa escape tambahan) agar konsisten di setiap perhitungan.
     */
    private static function buildSignature(array $payload): string
    {
        $canonical = [
            'code' => $payload['code'] ?? null, 
            'loan_id' => isset($payload['loan_id']) ? (int) $payload['loan_id'] : null, 
            'student_id' => isset($payload['student_id']) ? (int) $payload['student_id'] : null, 
            'admin_perpus_id' => isset($payload['admin_perpus_id']) ? (int) $payload['admin_perpus_id'] : null, 
            'action' => $payload['action'] ?? null, 
            'generated_at' => $payload['generated_at'] ?? null, /
        ];

        $json = json_encode($canonical, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); // JSON stabil untuk HMAC.

        $key = config('app.key'); // Gunakan app.key sebagai kunci HMAC.

        if (is_string($key) && str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7)); // Decode jika key berbentuk base64:...
        }

        return hash_hmac('sha256', (string) $json, (string) $key); // Hasilkan hash HMAC-SHA256.
    }
}
