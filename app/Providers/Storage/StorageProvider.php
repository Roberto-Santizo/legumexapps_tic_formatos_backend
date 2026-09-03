<?php

namespace App\Providers\Storage;

use App\Interfaces\Storage\ImageStorageServiceInterface;
use App\Services\Storage\ImageStorageService;
use Illuminate\Support\ServiceProvider;

class StorageProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ImageStorageServiceInterface::class, ImageStorageService::class);
    }

    public function boot(): void
    {
        //
    }
}
