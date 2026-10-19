<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Images
{
    public static function store(UploadedFile $file, string $directory): string
    {
        return 'storage/'.$file->store($directory, 'public');
    }

    public static function delete(?string $path): void
    {
        if ($path && str_starts_with($path, 'storage/')) {
            Storage::disk('public')->delete(substr($path, 8));
        }
    }
}
