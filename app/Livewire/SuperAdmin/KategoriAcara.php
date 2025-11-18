<?php

namespace App\Livewire\SuperAdmin;

use App\Livewire\Concerns\HandlesAlerts;
use App\Models\KategoriAcara as KategoriAcaraModel;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.dashboard-layouts')]
#[Title('Kategori Acara')]
class KategoriAcara extends Component
{
    use HandlesAlerts;
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';
    public int $perPage = 10;
    public array $perPageOptions = [10, 25, 50];

    public ?int $kategoriId = null;
    public string $nama = '';
    public ?string $deskripsi = null;

    protected $rules = [
        'nama' => ['required', 'string', 'max:255'],
        'deskripsi' => ['nullable', 'string', 'max:255'],
    ];

    protected $messages = [
        'nama.required' => 'Nama kategori wajib diisi.',
        'nama.max' => 'Nama kategori maksimal 255 karakter.',
        'deskripsi.max' => 'Deskripsi maksimal 255 karakter.',
    ];

    public function updatedSearch(): void
    {
        $this->search = trim((string) $this->search); 
        $this->resetPage(); 
    } 

    public function updatedPerPage($value): void
    {
        $this->perPage = $this->normalizePerPage($value); 
        $this->resetPage(); 
    } 

    public function render()
    {
        $categories = KategoriAcaraModel::query()
            ->withCount('acara') 
            ->when($this->search !== '', function ($query) {
                $query->where('nama', 'like', '%' . $this->search . '%'); 
            })
            ->orderBy('nama') 
            ->paginate($this->perPage); 

        return view('livewire.super-admin.kategori-acara', [
            'kategoriList' => $categories, 
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

        $kategori = KategoriAcaraModel::findOrFail($id); 

        $this->kategoriId = $kategori->id; 
        $this->nama = $kategori->nama; 
        $this->deskripsi = $kategori->deskripsi; 
    } 

    public function save(): void
    {
        $data = $this->validate(); 

        KategoriAcaraModel::updateOrCreate([ 
            'id' => $this->kategoriId,
        ], $data);

        $this->flashSuccess($this->kategoriId ? 'Kategori acara berhasil diperbarui.' : 'Kategori acara baru berhasil dibuat.');

        $this->dispatch('close-modal', id: 'modal-kategori-acara'); 
        $this->resetForm(); 
    } 

    public function delete(int $id): void
    {
        $kategori = KategoriAcaraModel::withCount('acara')->findOrFail($id); 

        if ($kategori->acara_count > 0) { 
            $this->flashError('Kategori tidak dapat dihapus karena masih digunakan.');
            return;
        }

        $kategori->delete(); 
        $this->flashSuccess('Kategori acara berhasil dihapus.');
    } 

    private function resetForm(): void
    {
        $this->reset([
            'kategoriId',
            'nama',
            'deskripsi',
        ]); 
    } 

    private function normalizePerPage($value): int
    {
        $value = (int) $value; 

        return in_array($value, $this->perPageOptions, true) ? $value : $this->perPageOptions[0]; 
    } 
}
