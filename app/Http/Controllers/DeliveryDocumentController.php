<?php

namespace App\Http\Controllers;

use App\Errors\NotFoundError;
use App\Helpers\ResponseHandler;
use App\Http\Requests\CreateDeliveryDocumentRequest;
use App\Http\Resources\DeliveryDocumentResource;
use App\Models\DeliveryDocument;
use App\Services\Storage\ImageStorageService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DeliveryDocumentController extends Controller
{
    public function index()
    {
        try {
            $delivery_documents = DeliveryDocument::with(['employee', 'user'])->get();
            $data = DeliveryDocumentResource::collection($delivery_documents);

            return ResponseHandler::success($data, 'Documentos de Entrega Obtenidos Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    public function store(CreateDeliveryDocumentRequest $request)
    {
        try {
            $data = $request->validated();

            $items = $data['items'];
            unset($data['items']);

            $data['user_id'] = auth()->user()->id;
            $data['delivery_date'] = Carbon::now();

            $imageServer = new ImageStorageService;
            $file = $request->file('responsable_signature');
            $file2 = $request->file('administrador_signature');

            $filename = $imageServer->store($file);
            $filename2 = $imageServer->store($file2);

            $data['responsable_signature'] = $filename;
            $data['administrador_signature'] = $filename2;

            DB::transaction(function () use ($data, $items) {
                $delivery_document = DeliveryDocument::create($data);
                $delivery_document->details()->createMany($items);
            });

            return ResponseHandler::success(true, 'Documento de Entregas Creado Correctamente', 201);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $delivery_documents = $this->findDeliveryDocumentOrFail($id);

            return ResponseHandler::success(new DeliveryDocumentResource($delivery_documents), 'Documento de Entrega Obtenido Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    public function delete(string $id)
    {
        try {
            $delivery_documents = $this->findDeliveryDocumentOrFail($id);
            $delivery_documents->delete();

            return ResponseHandler::success(true, 'Documento de Entrega  Eliminados Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * @throws NotFoundError si el documento no existe.
     */
    private function findDeliveryDocumentOrFail(string $id): DeliveryDocument
    {
        $delivery_documents = DeliveryDocument::find($id);

        if (! $delivery_documents) {
            throw new NotFoundError('Documento de Entrega no encontrado');
        }

        return $delivery_documents;
    }
}
