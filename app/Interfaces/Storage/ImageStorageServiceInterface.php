<?php

namespace App\Interfaces\Storage;

use Illuminate\Http\UploadedFile;

interface ImageStorageServiceInterface
{
    public function store(UploadedFile $image, string $directory = 'signatures'): string;

    public function delete(string $path): bool;

    public function url(string $path): string;
}
