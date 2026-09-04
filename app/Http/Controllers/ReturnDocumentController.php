<?php

namespace App\Http\Controllers;

use App\Errors\NotFoundError;
use App\Helpers\ResponseHandler;
use App\Http\Resources\ReturnDocumentResource;
use App\Models\ReturnDocument;
use App\Services\Storage\ImageStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class ReturnDocumentController extends Controller
{
    public function index()
    {
        try {
            $return_documents = ReturnDocument::with(['delivery_document_id'])->get();
            $data = ReturnDocumentResource::collection($return_documents);
            
            return ResponseHandler::success($data, 'Devolución de Documentos Obtenidos Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'return_date' => 'required',
                'responsable_signature' => ['required', 'file', 'mimes:png,jpg,jpeg', 'max:2048'],
                'administrador_signature' => ['required', 'mimes:png,jpg,jpeg', 'max:2048'],
                'observations' => ['nullable'],
                'delivery_document_id' =>['required', 'exists:delivery_documents,id']   
            ]);


            $imageServer =  new ImageStorageService();
            $file = $request->file('responsable_signature');
            $file2 = $request->file('administrador_signature');

            $filename = $imageServer->store($file);
            $filename2 = $imageServer->store($file2);

            $data['responsable_signature'] = $filename;
            $data['administrador_signature'] = $filename2;

            ReturnDocument::create($data);

            return ResponseHandler::success($data, 'Devolución de Documento Creado Correctamente', 201);
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
            $return_documents = $this->findReturnDocumentOrFail($id);

            return ResponseHandler::success($return_documents, 'Devolución de Documento Obtenido Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $data = $request->validate([
                'return_date' => 'required',
                'responsable_signature' => ['required', 'max:2048'],
                'administrador_signature' => ['required',  'max:2048'],
                'observations' => ['nullable'],
                'delivery_document_id' =>['required', 'exists:delivery_documents,id']   
            ]);
            
            $return_documents = ReturnDocument::find($id);

            $return_documents->update($data);

            return ResponseHandler::success($return_documents, 'Devolución de Documento Actualizado Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * @throws NotFoundError si el documento no existe.
     */
    private function findReturnDocumentOrFail(string $id): ReturnDocument
    {
        $return_documents = ReturnDocument::find($id);

        if (! $return_documents) {
            throw new NotFoundError('Documento de Entrega no encontrado');
        }

        return $return_documents;
    }
}
