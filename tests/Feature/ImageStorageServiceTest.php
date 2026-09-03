<?php

use App\Errors\BadRequestError;
use App\Interfaces\Storage\ImageStorageServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->service = app(ImageStorageServiceInterface::class);
});

it('guarda un png y devuelve la ruta con uuid', function () {
    $path = $this->service->store(UploadedFile::fake()->image('firma.png'));

    expect($path)->toMatch('/^signatures\/[0-9a-f-]{36}\.png$/');

    Storage::disk('public')->assertExists($path);
});

it('guarda un jpg normalizando la extensión', function () {
    $path = $this->service->store(UploadedFile::fake()->image('firma.jpeg'));

    expect($path)->toEndWith('.jpg');

    Storage::disk('public')->assertExists($path);
});

it('permite indicar el directorio destino', function () {
    $path = $this->service->store(UploadedFile::fake()->image('logo.png'), 'logos');

    expect($path)->toStartWith('logos/');
});

it('rechaza archivos que no son imágenes soportadas', function () {
    $this->service->store(UploadedFile::fake()->create('firma.pdf', 10, 'application/pdf'));
})->throws(BadRequestError::class, 'El archivo debe ser una imagen JPG, PNG o WEBP');

it('rechaza imágenes de más de 5 MB', function () {
    $this->service->store(UploadedFile::fake()->image('firma.png')->size(6144));
})->throws(BadRequestError::class, 'La imagen no puede pesar más de 5 MB');

it('elimina una imagen guardada', function () {
    $path = $this->service->store(UploadedFile::fake()->image('firma.png'));

    expect($this->service->delete($path))->toBeTrue();

    Storage::disk('public')->assertMissing($path);
});

it('devuelve la url pública de la imagen', function () {
    $path = $this->service->store(UploadedFile::fake()->image('firma.png'));

    expect($this->service->url($path))->toContain('/storage/'.$path);
});
