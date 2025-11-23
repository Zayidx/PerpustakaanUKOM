<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

trait HandlesImageUploads
{
    public function canPreviewImage($file): bool
    {
        if (! $file instanceof TemporaryUploadedFile) {
            return false;
        }

        $mimeType = $file->getMimeType();

        return is_string($mimeType) && str_starts_with($mimeType, 'image/');
    }

    protected function storeImageAndReturnName(
        TemporaryUploadedFile $file,
        string $directory,
        ?string $existingFilename = null,
        bool $deleteExisting = true
    ): string {
        $directory = trim($directory, '/');
        $filename = $file->hashName();

        Storage::disk('public')->makeDirectory($directory);

        if ($deleteExisting && $existingFilename) {
            $this->deleteImage($directory, $existingFilename);
        }

        $file->storeAs($directory, $filename, 'public');
        $this->mirrorToPublic($directory, $filename, $file->getRealPath());

        return $filename;
    }

    protected function deleteImage(string $directory, ?string $filename): void
    {
        $path = $this->normalizeUploadPath($directory, $filename);

        if ($path && ! Str::startsWith($path, ['http://', 'https://', 'assets/'])) {
            Storage::disk('public')->delete($path);
            $publicPath = public_path($path);
            if (is_file($publicPath)) {
                @unlink($publicPath);
            }
        }
    }

    protected function normalizeUploadPath(string $directory, ?string $filename): ?string
    {
        if (! $filename) {
            return null;
        }

        $cleanDirectory = trim($directory, '/');
        $cleanFilename = ltrim($filename, '/');

        if (Str::startsWith($cleanFilename, ['http://', 'https://'])) {
            return $cleanFilename;
        }

        if (Str::startsWith($cleanFilename, 'assets/')) {
            return $cleanFilename;
        }

        if (Str::startsWith($cleanFilename, 'storage/')) {
            return substr($cleanFilename, strlen('storage/'));
        }

        if ($cleanDirectory === '') {
            return $cleanFilename;
        }

        if (Str::startsWith($cleanFilename, $cleanDirectory.'/')) {
            return $cleanFilename;
        }

        return $cleanDirectory.'/'.$cleanFilename;
    }

    public function imageUrl(?string $filename, string $directory): ?string
    {
        $path = $this->normalizeUploadPath($directory, $filename);

        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if (Str::startsWith($path, 'assets/')) {
            return asset($path);
        }

        if (Storage::disk('public')->exists($path)) {
            $this->mirrorToPublic(dirname($path), basename($path));
        }

        if ($this->findPublicFile($path)) {
            return asset($path);
        }

        if (Storage::disk('public')->exists($path)) {
            return asset($path);
        }

        return null;
    }

    protected function onlyFilename(string $directory, ?string $filename): ?string
    {
        $path = $this->normalizeUploadPath($directory, $filename);

        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', 'assets/'])) {
            return $path;
        }

        return basename($path);
    }

    private function mirrorToPublic(string $directory, string $filename, ?string $sourcePath = null): void
    {
        $normalized = $this->normalizeUploadPath($directory, $filename);

        if (! $normalized) {
            return;
        }

        $storagePath = $sourcePath && is_file($sourcePath)
            ? $sourcePath
            : Storage::disk('public')->path($normalized);

        if (! is_file($storagePath)) {
            return;
        }

        foreach ($this->publicRoots() as $root) {
            $targetPath = rtrim($root, '/').'/'.$normalized;
            $targetDir = dirname($targetPath);

            File::ensureDirectoryExists($targetDir);
            File::copy($storagePath, $targetPath);
        }
    }

    private function findPublicFile(string $relativePath): ?string
    {
        $normalized = ltrim($relativePath, '/');

        foreach ($this->publicRoots() as $root) {
            $candidate = rtrim($root, '/').'/'.$normalized;
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function publicRoots(): array
    {
        $roots = [public_path()];

        $custom = env('PUBLIC_MIRROR_PATH');
        if ($custom && is_dir($custom)) {
            $roots[] = rtrim($custom, '/');
        }

        $sibling = base_path('../public_html');
        if (is_dir($sibling)) {
            $roots[] = realpath($sibling) ?: $sibling;
        }

        return array_values(array_unique($roots));
    }
}
