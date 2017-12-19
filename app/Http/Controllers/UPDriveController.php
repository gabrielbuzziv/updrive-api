<?php

namespace App\Http\Controllers;

use App\Company;
use App\Dispatch;
use App\DispatchTracking;
use App\Document;
use App\DocumentDispatch;
use App\DocumentDispatchTracking;
use App\Events\NewMailTracking;
use App\Http\Controllers\Traits\Filterable;
use App\Http\Controllers\Traits\Transformable;
use App\Http\Requests\SendDocumentRequest;
use App\Mail\NewDocuments;
use App\Mail\ResendDocuments;
use App\Notifications\NewDocumentsNotification;
use App\UPCont\Transformer\CompanyTransformer;
use App\UPCont\Transformer\DispatchTrackingTransformer;
use App\UPCont\Transformer\DispatchTransformer;
use App\UPCont\Transformer\DocumentTransformer;
use App\UPCont\Transformer\FolderTransformer;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class UPDriveController extends ApiController
{

    use Transformable;

    /**
     * UPDriveController constructor.
     */
    public function __construct()
    {
        $this->middleware('permission:manage-core', ['only' => 'send']);
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
            ->search(request('filter'), null, true)
            ->leftJoin('company_contact', 'company_contact.company_id', 'companies.id')
            ->where(function ($query) {
                if ( ! auth()->user()->can('manage-core'))
                    $query->where('company_contact.contact_id', auth()->user()->id);
            })
            ->paginate($limit);

        return $this->respond([
            'items' => $this->transformCollection($companies, new CompanyTransformer()),
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
        $include = ['company', 'user', 'sharedWith', 'history.user', 'dispatches.tracking.recipient'];
        $documents = (new Document)
            ->with($include)
            ->search($query)
            ->where(function ($query) {
                if (request('company'))
                    $query->where('company_id', request('company'));

                if ( ! auth()->user()->can('manage-core')) {
                    $user = auth()->user();
                    $query->whereRaw("{$user->id} in (SELECT GROUP_CONCAT(document_contact.contact_id) FROM document_contact WHERE document_contact.document_id = documents.id)");
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

        $dispatch = Dispatch::create([
            'company_id' => $company->id,
            'sender_id'  => auth()->user()->id,
            'subject'    => $subject,
            'message'    => nl2br($message),
        ]);

        $attachments = [];

        foreach ($documents as $document) {
            $document = Document::create([
                'user_id'    => auth()->user()->id,
                'name'       => $document['name'],
                'filename'   => $document['file'],
                'cycle'      => ! empty($document['cycle']) ? $document['cycle'] : null,
                'validity'   => ! empty($document['validity']) ? $document['validity'] : null,
                'status'     => 2,
                'company_id' => $company->id,
                'resent_at' => Carbon::now()
            ]);

            $dispatch->documents()->attach($document->id);
            $attachments[] = $document;

            $document->sharedWith()->attach(array_map(function ($contact) {
                return $contact['id'];
            }, $contacts));

            array_push($documentsId, $document->id);
        }

        foreach ($contacts as $contact) {
            $dispatch->recipients()->attach($contact->id);

            if ( ! $company->contacts->contains($contact->id)) {
                $company->contacts()->attach($contact->id);
            }

            if ( ! $contact->is_contact) {
                $contact->is_contact = true;
                $contact->save();
            }

            // Create a history saying that the document was sent to the contact.
            foreach ($attachments as $attachment) {
                $attachment->history()->create(['user_id' => $contact->id, 'action' => 2]);
            }

            Mail::to($contact->email)->send(new NewDocuments($dispatch, $contact));

            DispatchTracking::create([
                'dispatch_id'  => $dispatch->id,
                'recipient_id' => $contact->id,
                'status'       => 'sent'
            ]);
        }

        return $this->respond([
            'company'  => $company->id,
            'dispatch' => $dispatch,
        ]);
    }

    /**
     * Resend document.
     *
     * @param Document $document
     */
    public function resend(Document $document)
    {
        $user = auth()->user();
        $dispatch = Dispatch::create([
            'company_id' => $document->company->id,
            'sender_id'  => $user->id,
            'subject'    => "Documento {$document->name}",
            'message'    => "",
        ]);

        $dispatch->documents()->attach($document->id);
        $dispatch->recipients()->attach($document->sharedWith->pluck('id')->all());

        $recipients = request('recipientes') ?: $document->sharedWith;
        foreach ($recipients as $recipient) {
            $document->history()->create(['user_id' => $recipient->id, 'action' => 6, 'description' => "Reenvio manual por {$user->name}"]);
            $dispatch->tracking()->create(['recipient_id' => $recipient->id, 'status' => 'sent']);
            Mail::to($recipient->email)->send(new ResendDocuments($dispatch, $recipient));
        }

        $document->sharedWith->each(function ($contact) use ($dispatch, $document, $user) {

        });
    }

    /*
     * Get document dispatch details.
     *
     * @param Document $document
     * @return mixed
     */
    public function dispatchDetails(Document $document)
    {
        $dispatch = $document->dispatches()->first();

        return $this->respond($this->transformItem($dispatch, new DispatchTransformer()));
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
            $user = null;
            $value = $contact['value'];

            switch ($value) {
                case is_numeric($value) :
                    $user = User::find((int) $value);
                    break;
                case is_string($value) :
                    $user = User::firstOrCreate(['email' => $value], ['name' => $value, 'password' => str_random(8), 'is_contact' => true, 'is_active' => true]);
                    break;
            }

            if (isset($contact['tags']))
                $user->tags()->sync($contact['tags']);

            return $user;
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
}
