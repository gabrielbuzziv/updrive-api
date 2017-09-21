<?php

namespace App\Http\Controllers;

use App\Document;
use App\Http\Controllers\Traits\Transformable;
use App\Http\Requests\UploadDocumentRequest;
use App\UPCont\Transformer\CompanyTransformer;
use App\UPCont\Transformer\ContactTransformer;
use App\UPCont\Transformer\DocumentTransformer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Tymon\JWTAuth\Facades\JWTAuth;

class DocumentController extends ApiController
{

    use Transformable;

    /**
     * Updrive Path
     *
     * @var string
     */
    protected $path = 'updrive';

    /**
     * DocumentController constructor.
     */
    public function __construct()
    {
        $this->middleware('permission:manage-updrive', ['only' => ['update', 'destroy']]);
    }

    /**
     * Update document in database.
     *
     * @param Document $document
     * @return mixed
     */
    public function update(Document $document)
    {
        try {
            $this->validate(request(), ['name' => 'required']);

            $document->update(request()->all());

            return $this->respond($this->transformItem($document, new DocumentTransformer()));
        } catch (Exception $e) {
            Log::critical(logMessage($e, 'Ocorreu um erro durante a atualização do documento'), logUser());

            return $this->respondInternalError($e);
        }
    }

    /**
     * Remove document from database and the file from storage.
     *
     * @param Document $document
     * @return mixed
     * @throws \Exception
     */
    public function destroy(Document $document)
    {
        try {
            $filename = $document->filename;

            $document->delete();
            Storage::delete("{$this->path}/{$filename}");

            return $this->respond(['deleted' => true]);
        } catch (Exception $e) {
            Log::critical(logMessage($e, 'Ocorreu um erro ao remover o documento'), logUser());

            return $this->respondInternalError($e);
        }
    }

    /**
     * Return the request document collection.
     *
     * @param id
     * @return mixed
     */
    public function show($id)
    {
        $include = ['history.user'];
        $document = Document::with($include)->findOrFail($id);

        return $this->respond($this->transformItem($document, new DocumentTransformer(), $include));
    }

    /**
     * Get the count of documents.
     *
     * @return mixed
     */
    public function total()
    {
        $documents = Document::withTrashed()->count();

        return $this->respond(['total' => $documents]);
    }

    /**
     * Generate the document procotol.
     *
     * @param Document $document
     * @return mixed
     */
    public function protocol(Document $document)
    {
        $barcodeGenerator = new \Picqer\Barcode\BarcodeGeneratorPNG();
        $barcode = $barcodeGenerator->getBarcode($document->id, $barcodeGenerator::TYPE_CODE_128);
        $barcode = sprintf('data:image/png;base64,%s', base64_encode($barcode));

        $pdf = app()->make('dompdf.wrapper');

        $pdf->loadHTML(view('documents.protocol', compact('document', 'barcode')));

        return $pdf->stream();
    }

    /**
     * Download file from storage.
     *
     * @param Document $document
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download(Document $document)
    {
        if (!! auth()->user()->sharedDocuments->where('id', $document->id)->count()) {
            $this->isAuthorized($document);

            $document->status = 3;
            $document->save();
            $document->history()->create(['user_id' => Auth::user()->id, 'action' => 3]);
        }

        $path = sprintf('%s/documents/%s', config('account')->slug, $document->filename);

        if (Storage::disk('s3')->exists($path)) {
            $file = Storage::disk('s3')->get($path);
            $headers = [
                'Content-Type'        => Storage::disk('s3')->mimeType($path),
                'Content-Description' => 'File Transfer',
                'Content-Disposition' => "attachment; filename={$document->filename}",
                'Content-Transfer-Encoding' => 'binary',
            ];

            return response($file, 200, $headers);
        }
    }

    /**
     * @param Document $document
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function visualize(Document $document)
    {
        if (!! auth()->user()->sharedDocuments->where('id', $document->id)->count()) {
            $this->isAuthorized($document);
            $document->status = 3;
            $document->save();
            $document->history()->create(['user_id' => Auth::user()->id, 'action' => 4]);
        }

        $path = sprintf('%s/documents/%s', config('account')->slug, $document->filename);

        if (Storage::disk('s3')->exists($path)) {
            $file = Storage::disk('s3')->get($path);
            $type = Storage::disk('s3')->mimeType($path);

            return Response::make($file, 200)->header('Content-Type', $type);
        }
    }

    /**
     * Check if user is allowed to access.
     *
     * @param Document $document
     * @return mixed
     */
    private function isAuthorized(Document $document)
    {
        return (bool) auth()->user()->sharedDocuments()->where('document_id', $document->id)->count();
    }
}
