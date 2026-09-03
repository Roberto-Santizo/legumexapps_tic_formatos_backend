<?php

use App\Providers\AppServiceProvider;
use App\Providers\Auth\AuthProvider;
use App\Providers\Storage\StorageProvider;

return [
    AppServiceProvider::class,
    AuthProvider::class,
    StorageProvider::class,
];
