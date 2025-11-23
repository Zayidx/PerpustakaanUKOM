<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CoverUrlResolver
{
    public static function resolve(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $normalized = ltrim($path, '/');

        if (Str::startsWith($normalized, ['http://', 'https://'])) {
            return $normalized;
        }

        if (Str::startsWith($normalized, 'assets/')) {
            return asset($normalized);
        }

        if (Str::startsWith($normalized, 'storage/')) {
            return asset($normalized);
        }

        $diskPath = self::normalizeDiskPath($normalized);
        if ($diskPath !== '' && Storage::disk('public')->exists($diskPath)) {
            return asset('storage/'.$diskPath);
        }

        if ($diskPath !== '' && is_file(public_path($diskPath))) {
            return asset($diskPath);
        }

        if (! str_contains($normalized, '/')) {
            $defaultPath = 'admin/cover-buku/'.$normalized;

            if (Storage::disk('public')->exists($defaultPath)) {
                return asset('storage/'.$defaultPath);
            }

            $defaultPublicPath = public_path($defaultPath);

            if (is_file($defaultPublicPath)) {
                return asset($defaultPath);
            }
        }

        $publicPath = public_path($normalized);
        if (is_file($publicPath)) {
            return asset($normalized);
        }

        $storagePath = storage_path('app/public/'.$normalized);
        if (is_file($storagePath)) {
            return asset('storage/'.$normalized);
        }

        return null;
    }

    private static function normalizeDiskPath(string $path): string
    {
        if (Str::startsWith($path, 'public/')) {
            $path = substr($path, strlen('public/'));
        }

        if (Str::startsWith($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        return ltrim($path, '/');
    }
}
