<?php

namespace App\Livewire\Guru;

use App\Models\Peminjaman;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

class ScanPeminjaman extends Component
{
    #[Layout('components.layouts.dashboard-layouts')]
    #[Title('Scan Peminjaman')]
    public ?array $loan = null;

    public ?string $lastPayload = null;

    public ?string $errorMessage = null;

    #[On('qr-scanned')]
    public function handleScan(mixed $event): void
    {
        $this->reset(['errorMessage', 'loan', 'lastPayload']); // Reset state sebelum memproses scan baru

        $payload = is_string($event) ? $event : ($event['payload'] ?? null); // Ambil payload dari event
        $data = $payload ? json_decode($payload, true) : null; // Decode payload JSON

        if (! is_array($data) || empty($data['code'])) { // Validasi format data QR
            $this->errorMessage = 'QR code tidak valid.';
            return;
        }

        $user = Auth::user(); // Ambil user yang sedang login
        $guru = $user?->guru; // Ambil data guru terkait

        if (! $guru) { // Cek apakah user memiliki data guru
            $this->errorMessage = 'Akun guru belum memiliki data guru.';
            return;
        }

        $loan = Peminjaman::query() // Cari peminjaman berdasarkan kode QR
            ->with(['items.buku', 'siswa.user', 'siswa.kelas']) // Muat relasi untuk detail
            ->where('kode', $data['code']) // Cari berdasarkan kode
            ->first();

        if (! $loan) { // Cek apakah peminjaman ditemukan
            $this->errorMessage = 'Data peminjaman tidak ditemukan.';
            return;
        }

        if (isset($data['loan_id']) && (int) $data['loan_id'] !== $loan->id) { // Validasi id peminjaman cocok
            $this->errorMessage = 'Kode peminjaman tidak cocok.';
            return;
        }

        if ($loan->status === 'pending') { // Jika status masih pending, update ke accepted
            DB::transaction(function () use ($loan, $guru) { // Jalankan dalam transaksi database
                $loan->update([ // Update status peminjaman
                    'status' => 'accepted', // Ganti status ke accepted
                    'guru_id' => $guru->id, // Tetapkan ID guru yang menyetujui
                    'accepted_at' => now(), // Tandai waktu penerimaan
                    'due_at' => now()->addWeek(), // Tetapkan tanggal jatuh tempo (1 minggu dari sekarang)
                ]);
            });

            $loan->refresh(); // Refresh data dari database
        }

        $this->loan = [ // Format data peminjaman untuk ditampilkan
            'id' => $loan->id,
            'kode' => $loan->kode,
            'status' => $loan->status,
            'accepted_at' => $loan->accepted_at,
            'due_at' => $loan->due_at,
            'returned_at' => $loan->returned_at,
            'student_name' => $loan->siswa?->user?->nama_user, // Nama siswa
            'student_class' => optional($loan->siswa?->kelas)->nama_kelas ?? '-', // Kelas siswa
            'books' => $loan->items->map(fn ($item) => [ // Daftar buku yang dipinjam
                'id' => $item->buku_id, // ID buku
                'title' => $item->buku->nama_buku, // Judul buku
            ])->values()->all(), // Kumpulkan semua buku
        ];

        $this->lastPayload = $payload; // Simpan payload terakhir untuk referensi
    } // Tangani event scan QR code untuk peminjaman

    #[On('qr-scanner-error')]
    public function handleScannerError(mixed $event = null): void
    {
        $message = is_string($event) ? $event : ($event['message'] ?? null); // Ambil pesan error dari event
        $this->errorMessage = $message ?: null; // Tetapkan pesan error atau null jika tidak ada
    } // Tangani event error scanner QR

    public function render()
    {
        return view('livewire.guru.scan-peminjaman');
    } // Render tampilan komponen
}
