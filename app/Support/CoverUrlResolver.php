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

        if ($publicPath = self::findInPublicRoots($normalized)) {
            return asset($normalized);
        }

        $diskPath = self::normalizeDiskPath($normalized);
        if ($diskPath !== '') {
            if (self::findInPublicRoots($diskPath)) {
                return asset($diskPath);
            }

            if (Storage::disk('public')->exists($diskPath)) {
                return asset($diskPath);
            }
        }

        if (! str_contains($normalized, '/')) {
            $defaultPath = 'admin/cover-buku/'.$normalized;

            if (self::findInPublicRoots($defaultPath)) {
                return asset($defaultPath);
            }

            if (Storage::disk('public')->exists($defaultPath)) {
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

    private static function findInPublicRoots(string $relativePath): ?string
    {
        $normalized = ltrim($relativePath, '/');
        $roots = [public_path()];

        $custom = env('PUBLIC_MIRROR_PATH');
        if ($custom && is_dir($custom)) {
            $roots[] = rtrim($custom, '/');
        }

        $sibling = base_path('../public_html');
        if (is_dir($sibling)) {
            $roots[] = realpath($sibling) ?: $sibling;
        }

        foreach (array_unique($roots) as $root) {
            $candidate = rtrim($root, '/').'/'.$normalized;
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
