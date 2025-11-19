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
