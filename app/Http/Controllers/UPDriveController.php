<?php

namespace App\Http\Controllers;

use App\Company;
use App\Document;
use App\DocumentDispatch;
use App\Http\Controllers\Traits\Filterable;
use App\Http\Controllers\Traits\Transformable;
use App\Http\Requests\SendDocumentRequest;
use App\Notifications\NewDocumentsNotification;
use App\UPCont\Transformer\CompanyTransformer;
use App\UPCont\Transformer\DocumentTransformer;
use App\UPCont\Transformer\FolderTransformer;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UPDriveController extends ApiController
{

    use Transformable;

    /**
     * UPDriveController constructor.
     */
    public function __construct()
    {
        $this->middleware('permission:manage-updrive', ['only' => 'send']);
    }

    /**
     * Get companies that user can see.
     *
     * @return mixed
     */
    public function companies()
    {
        $limit = request('limit') ?: 25;
        $companies = Company::select(DB::raw('DISTINCT companies.*'))
            ->search(request('filter'), null, true, true)
            ->leftJoin('company_contact', 'company_contact.company_id', 'companies.id')
            ->where(function ($query) {
                if (auth()->user()->is_contact)
                    $query->where('company_contact.contact_id', auth()->user()->id);
            })
            ->orderBy('identifier')
            ->paginate($limit);

        return $this->respond([
            'items' => $this->transformCollection($companies, new CompanyTransformer()),
        ]);
    }

    /**
     * Get pending documents.
     * Documents that was not opened and have validity.
     *
     * @return mixed
     */
    public function pending()
    {
        $include = ['company'];
        $documents = Document::select(DB::raw('DISTINCT documents.*'))
            ->with($include)
            ->leftJoin('company_contact', 'company_contact.company_id', 'documents.company_id')
            ->leftJoin('document_contact', 'document_contact.document_id', 'documents.id')
            ->where(function ($query) {
                if (request('company'))
                    $query->where('documents.company_id', request('company'));

                if (auth()->user()->is_contact) {
                    $query->where('document_contact.contact_id', auth()->user()->id);
                    $query->where('company_contact.contact_id', auth()->user()->id);
                }

                $query->whereNotNull('documents.validity');
                $query->where('documents.status', 2);
                $query->whereDate('documents.validity', '>=', Carbon::now()->format('Y-m-d'));
            })
            ->orderBy('documents.validity', 'ASC')->get();

        return $this->respond([
            'items' => $this->transformCollection($documents, new DocumentTransformer(), $include),
        ]);
    }

    /**
     * All documents.
     *
     * @return mixed
     */
    public function documents()
    {
        $query = request('query');
        $limit = request('limit') ?: 25;
        $include = ['company'];
        $documents = Document::select(DB::raw('DISTINCT documents.*'))
            ->with($include)
            ->leftJoin('company_contact', 'company_contact.company_id', 'documents.company_id')
            ->leftJoin('document_contact', 'document_contact.document_id', 'documents.id')
            ->search($query)
            ->where(function ($query) {
                if (request('company'))
                    $query->where('documents.company_id', request('company'));

                if (auth()->user()->is_contact) {
                    $query->where('document_contact.contact_id', auth()->user()->id);
                    $query->where('company_contact.contact_id', auth()->user()->id);
                }

                if (request('status'))
                    $query->where('documents.status', request('status'));

                $query->where('documents.status', '>', 1);
            })
            ->orderBy(DB::raw('CASE WHEN status = 2 THEN 1 ELSE 2 END'))
            ->orderBy('documents.created_at', 'DESC')
            ->paginate($limit);

        return $this->respond([
            'total' => $documents->total(),
            'items' => $this->transformCollection($documents, new DocumentTransformer(), $include),
        ]);
    }

    /**
     * Count unnotified documents from requested company
     *
     * @return mixed
     */
    public function amounts()
    {
        $pendings = $this->getDocumentsByCompany()->whereNotNull('validity')
            ->where('documents.status', 2)
            ->whereDate('documents.validity', '>=', Carbon::now()->format('Y-m-d'))
            ->count(DB::raw('DISTINCT documents.id'));
        $documents = $this->getDocumentsByCompany()->where('documents.status', 2)->count(DB::raw('DISTINCT documents.id'));

        return $this->respond([
            'pendings'  => $pendings, 
            'documents' => $documents,
        ]);
    }

    /**
     * Create the document Dispatch and fill with the requested data.
     * Notify the contacts, Create the Document, Add History to Document and share with contacts.
     *
     * @param SendDocumentRequest $request
     * @return mixed
     */
    public function send(SendDocumentRequest $request)
    {
        $company = $this->parseCompany(request('company'));
        $contacts = $this->parseContacts(request('contacts'));
        $subject = ! empty(request('subject')) ? request('subject') : 'Novos documentos disponíveis.';
        $message = request('message');
        $documents = $this->parseDocuments(request('documents'));
        $documentsId = [];

        $dispatch = DocumentDispatch::create([
            'company_id' => $company->id,
            'user_id'    => auth()->user()->id,
            'subject'    => $subject,
            'message'    => $message,
        ]);

        foreach ($documents as $document) {
            $document = Document::create([
                'user_id'     => auth()->user()->id,
                'name'        => $document['name'],
                'filename'    => $document['file'],
                'cycle'       => ! empty($document['cycle']) ? $document['cycle'] : null,
                'validity'    => ! empty($document['validity']) ? $document['validity'] : null,
                'status'      => 2,
                'company_id'  => $company->id,
                'dispatch_id' => $dispatch->id,
            ]);

            $document->history()->create(['user_id' => auth()->user()->id, 'action' => 2]);
            $document->sharedWith()->attach(array_map(function ($contact) {
                return $contact['id'];
            }, $contacts));

            array_push($documentsId, $document->id);
        }

        foreach ($contacts as $contact) {
            $dispatch->contacts()->attach($contact->id);

            if (! $company->contacts->contains($contact->id)) {
                $company->contacts()->attach($contact->id);
            }

            $contact->notify(new NewDocumentsNotification($dispatch, $contact));
        }

        return $this->respond([
            'company'  => $company->id,
            'dispatch' => $dispatch,
        ]);
    }

    /**
     * Parse the company to find is already exist,
     * creating a new one if necessary and than returning the object.
     *
     * @param $company
     * @return array
     */
    private function parseCompany($company)
    {
        switch ($company) {
            case is_numeric($company) :
                return Company::find((int) $company);
            case is_string($company) :
                return Company::firstOrCreate(['name' => $company], ['nickname' => $company]);
        }
    }

    /**
     * Parse contacts to find if their exist,
     * creating a new one if necessary and than returning the object.
     *
     * @param $contacts
     * @return array
     */
    private function parseContacts($contacts)
    {
        return array_map(function ($contact) {
            switch ($contact) {
                case is_numeric($contact) :
                    return User::find((int) $contact);
                case is_string($contact) :
                    return User::firstOrCreate(['email' => $contact], ['name' => $contact, 'password' => str_random(8), 'is_contact' => true, 'is_active' => true]);
            }
        }, $contacts);
    }

    /**
     * Parse documents, than find the respective file document and upload to storage,
     * than return the data.
     *
     * @param $documents
     * @return array
     */
    private function parseDocuments($documents)
    {
        return array_map(function ($document, $key) {
            $document = json_decode($document, true);

            if (request()->hasFile('files')) {
                $file = $this->upload(request()->file('files')[$key]);
                $document['file'] = $file['filename'];

                return $document;
            }

            return $document;
        }, $documents, array_keys($documents));
    }

    /**
     * Upload file into storage.
     *
     * @param $file
     * @return array|bool
     */
    private function upload($file)
    {
        if ($file->isValid()) {
            $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $filename = md5(uniqid(rand(), true)) . '.' . $extension;

            $path = sprintf('%s/documents/%s', config('account')->slug, $filename);
            Storage::disk('s3')->put($path, file_get_contents($file));

            return [
                'name'     => $name,
                'filename' => $filename,
            ];
        }

        return false;
    }

    /**
     * Get documets by company.
     *
     * @return mixed
     */
    private function getDocumentsByCompany()
    {
        return Document::select('documents.*')
            ->join('company_contact', 'company_contact.company_id', 'documents.company_id')
            ->join('document_contact', 'document_contact.document_id', 'documents.id')
            ->where(function ($query) {
                if (auth()->user()->is_contact) {
                    $query->where('document_contact.contact_id', auth()->user()->id);
                    $query->where('company_contact.contact_id', auth()->user()->id);
                }

                if (request('company'))
                    $query->where('documents.company_id', request('company'));
            });
    }
}
