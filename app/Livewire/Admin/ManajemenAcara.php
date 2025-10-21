<?php

namespace App\Livewire\Admin;

use App\Models\Acara;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\KategoriAcara;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.dashboard-layouts')]
#[Title('Manajemen Acara')]
class ManajemenAcara extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $sort = 'mulai_at_desc';
    public array $sortOptions = [
        'mulai_at_desc' => 'Jadwal Terbaru',
        'mulai_at_asc' => 'Jadwal Terlama',
        'judul_asc' => 'Judul A-Z',
        'judul_desc' => 'Judul Z-A',
    ];

    public int $perPage = 10;
    public array $perPageOptions = [10, 25, 50];

    public ?int $acaraId = null;
    public string $judul = '';
    public string $lokasi = '';
    public ?int $kategori_acara_id = null;
    public ?string $poster_url = null;
    public ?string $deskripsi = null;
    public string $mulai_at_input = '';
    public ?string $selesai_at_input = null;

    protected $rules = [
        'judul' => ['required', 'string', 'max:255'],
        'lokasi' => ['required', 'string', 'max:255'],
        'kategori_acara_id' => ['required', 'exists:kategori_acara,id'],
        'poster_url' => ['nullable', 'url'],
        'mulai_at_input' => ['required', 'date'],
        'selesai_at_input' => ['nullable', 'date', 'after:mulai_at_input'],
        'deskripsi' => ['nullable', 'string'],
    ];

    protected $messages = [
        'judul.required' => 'Judul acara wajib diisi.',
        'lokasi.required' => 'Lokasi acara wajib diisi.',
        'kategori_acara_id.required' => 'Kategori acara wajib dipilih.',
        'kategori_acara_id.exists' => 'Kategori acara tidak valid.',
        'mulai_at_input.required' => 'Tanggal dan waktu mulai wajib diisi.',
        'selesai_at_input.after' => 'Waktu selesai harus setelah waktu mulai.',
        'poster_url.url' => 'Poster harus berupa tautan yang valid.',
    ];

    public function mount(): void
    {
        $this->perPage = $this->normalizePerPage($this->perPage);
        $this->sort = $this->normalizeSort($this->sort);
    }

    public function updatedSearch(): void
    {
        $this->search = trim((string) $this->search);
        $this->resetPage();
    }

    public function updatedSort($value): void
    {
        $this->sort = $this->normalizeSort((string) $value);
        $this->resetPage();
    }

    public function updatedPerPage($value): void
    {
        $this->perPage = $this->normalizePerPage($value);
        $this->resetPage();
    }

    public function render()
    {
        [$field, $direction] = $this->resolveSort();

        $events = Acara::query()
            ->search($this->search)
            ->orderBy($field, $direction)
            ->paginate($this->perPage);

        return view('livewire.admin.manajemen-acara', [
            'eventList' => $events,
            'kategoriOptions' => KategoriAcara::orderBy('nama')->get(),
        ]);
    }

    public function create(): void
    {
        $this->resetForm();
        $this->resetValidation();
    }

    public function edit(int $id): void
    {
        $this->resetValidation();

        $event = Acara::findOrFail($id);

        $this->acaraId = $event->id;
        $this->judul = $event->judul;
        $this->lokasi = $event->lokasi;
        $this->kategori_acara_id = $event->kategori_acara_id;
        $this->poster_url = $event->poster_url;
        $this->deskripsi = $event->deskripsi;
        $this->mulai_at_input = optional($event->mulai_at)->format('Y-m-d\TH:i') ?? '';
        $this->selesai_at_input = optional($event->selesai_at)->format('Y-m-d\TH:i');
    }

    public function save(): void
    {
        $validated = $this->validate();

        $mulai = Carbon::parse($validated['mulai_at_input']);
        $selesai = $validated['selesai_at_input'] ? Carbon::parse($validated['selesai_at_input']) : null;

        $slug = $this->generateSlug($validated['judul']);

        $data = [
            'judul' => $validated['judul'],
            'slug' => $slug,
            'lokasi' => $validated['lokasi'],
            'kategori_acara_id' => (int) $validated['kategori_acara_id'],
            'poster_url' => $validated['poster_url'] ?: null,
            'deskripsi' => $validated['deskripsi'] ?: null,
            'mulai_at' => $mulai,
            'selesai_at' => $selesai,
            'admin_id' => Auth::id(),
        ];

        Acara::updateOrCreate([
            'id' => $this->acaraId,
        ], $data);

        session()->flash('message', $this->acaraId ? 'Acara berhasil diperbarui.' : 'Acara baru berhasil dibuat.');

        $this->dispatch('close-modal', id: 'modal-acara');
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        Acara::findOrFail($id)->delete();

        session()->flash('message', 'Acara berhasil dihapus.');
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
            'acaraId',
            'judul',
            'lokasi',
            'kategori_acara_id',
            'poster_url',
            'deskripsi',
            'mulai_at_input',
            'selesai_at_input',
        ]);
    }

    private function normalizePerPage($value): int
    {
        $value = (int) $value;

        return in_array($value, $this->perPageOptions, true) ? $value : $this->perPageOptions[0];
    }

    private function normalizeSort(string $value): string
    {
        return array_key_exists($value, $this->sortOptions) ? $value : 'mulai_at_desc';
    }

    private function resolveSort(): array
    {
        return match ($this->sort) {
            'mulai_at_asc' => ['mulai_at', 'asc'],
            'judul_asc' => ['judul', 'asc'],
            'judul_desc' => ['judul', 'desc'],
            default => ['mulai_at', 'desc'],
        };
    }

    private function generateSlug(string $judul): string
    {
        $baseSlug = Str::slug($judul) ?: 'acara';
        $slug = $baseSlug;
        $counter = 1;

        while (
            Acara::where('slug', $slug)
                ->when($this->acaraId, fn ($query) => $query->where('id', '!=', $this->acaraId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
