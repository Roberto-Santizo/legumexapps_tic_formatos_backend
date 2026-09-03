<?php

namespace App\Services\Storage;

use App\Errors\BadRequestError;
use App\Interfaces\Storage\ImageStorageServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageStorageService implements ImageStorageServiceInterface
{
    /**
     * Tipos MIME aceptados y la extensión con la que se guarda cada uno.
     *
     * @var array<string, string>
     */
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/pjpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    /**
     * Tamaño máximo permitido por imagen: 5 MB.
     */
    private const MAX_SIZE_IN_BYTES = 5242880;

    public function __construct(private readonly string $disk = 'public') {}

    /**
     * Guarda la imagen en el disco con un nombre uuid y devuelve su ruta relativa.
     *
     * @return string ruta relativa dentro del disco, p. ej. `signatures/9f8a....png`
     *
     * @throws BadRequestError si el archivo es inválido, no es una imagen soportada,
     *                         excede el tamaño máximo o no se pudo escribir en disco.
     */
    public function store(UploadedFile $image, string $directory = 'signatures'): string
    {
        if (! $image->isValid()) {
            throw new BadRequestError('El archivo enviado no es válido');
        }

        if ($image->getSize() > self::MAX_SIZE_IN_BYTES) {
            throw new BadRequestError('La imagen no puede pesar más de 5 MB');
        }

        $extension = self::ALLOWED_MIME_TYPES[$image->getMimeType()] ?? null;

        if (! $extension) {
            throw new BadRequestError('El archivo debe ser una imagen JPG, PNG o WEBP');
        }

        $fileName = Str::uuid()->toString().'.'.$extension;

        $path = Storage::disk($this->disk)->putFileAs(
            trim($directory, '/'),
            $image,
            $fileName
        );

        if (! $path) {
            throw new BadRequestError('No se pudo guardar la imagen');
        }

        return $path;
    }

    /**
     * Elimina la imagen ubicada en la ruta relativa indicada.
     */
    public function delete(string $path): bool
    {
        return Storage::disk($this->disk)->delete($path);
    }

    /**
     * Devuelve la URL pública de la imagen.
     */
    public function url(string $path): string
    {
        return Storage::disk($this->disk)->url($path);
    }
}
