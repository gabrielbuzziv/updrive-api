<?php

namespace App\Http\Controllers;

use App\Company;
use App\DocumentHistory;
use App\Http\Controllers\Traits\Filterable;
use App\Http\Controllers\Traits\Sortable;
use App\Http\Controllers\Traits\Transformable;
use App\Http\Requests\ContactRequest;
use App\Notifications\WelcomeUser;
use App\UPCont\Transformer\CompanyTransformer;
use App\UPCont\Transformer\ContactTransformer;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ContactController extends ApiController
{

    use Transformable;

    /**
     * ContactController constructor.
     */
    public function __construct()
    {
        $this->middleware('permission:manage-contacts', ['except' => ['companies', 'checkForNewDocuments']]);
    }

    /**
     * Show all contact users based in the request.
     *
     * @return mixed
     */
    public function index()
    {
        $limit = request('limit') ?: 25;
        $contacts = User::contact()
            ->search(request('filter'), null, true, true)
            ->orderBy('name')
            ->paginate($limit);

        return $this->respond([
            'total' => $contacts->total(),
            'items' => $this->transformCollection($contacts, new ContactTransformer()),
        ]);
    }

    /**
     * Store user in database as contact.
     *
     * @param ContactRequest $request
     * @return mixed;
     */
    public function store(ContactRequest $request)
    {
        $data = $request->all();

        try {
            $data['password'] = str_random(6);
            $data['is_contact'] = 1;
            $data['is_active'] = 1;

            $contact = User::create($data);

            $this->syncCompanies($contact);
            $this->savePhones($contact);
            $this->saveAddress($contact);

            return $this->respondCreated($this->transformItem($contact, new ContactTransformer()));
        } catch (\Exception $e) {
            Log::critical(logMessage($e, 'Ocorreu um erro durante a criação do contato'), logUser());

            return $this->respondInternalError($e);
        }
    }

    /**
     * Update user in database as contact.
     *
     * @param User $contact
     * @param ContactRequest $request
     * @return mixed;
     */
    public function update(User $contact, ContactRequest $request)
    {
        try {
            $contact->update($request->all());
            $this->syncCompanies($contact);
            $this->savePhones($contact);
            $this->saveAddress($contact);

            return $this->respond($this->transformItem($contact, new ContactTransformer()));
        } catch (\Exception $e) {
            Log::critical(logMessage($e, 'Não foi possível atualizar o contato'), logUser());

            return $this->respondInternalError($e);
        }
    }

    /**
     * Walk in an array of ids and try to instanciate each one as a User,
     * if exists delete from database.
     *
     * @param Request $request
     * @return mixed
     */
    public function destroy(Request $request)
    {
        $items = $request->input('items');

        try {
            $deleted = 0;

            foreach ($items as $item) {
                if ($contact = User::find($item)) {
                    $contact->delete();
                    $deleted ++;
                }
            }

            return $this->respond(['total' => $deleted]);
        } catch (\Exception $e) {
            Log::error(logMessage($e, 'Ocorreu um erro ao remover contatos.'), logUser());

            return $this->respondInternalError($e);
        }
    }

    /**
     * Return the data of the requested user.
     *
     * @param User $contact
     * @return mixed
     */
    public function show(User $contact)
    {
        $include = ['phones', 'address', 'companies.address'];
        $contact = User::contact()
            ->with($include)
            ->findOrFail($contact->id);

        return $this->respond($this->transformItem($contact, new ContactTransformer(), $include));
    }

    /**
     * Get the count of contacts.
     *
     * @return mixed
     */
    public function total()
    {
        $contacts = User::contact()->withTrashed()->count();

        return $this->respond(['total' => $contacts]);
    }

    /**
     * Return the download of default import sheet.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadImportSheet()
    {
        return response()->download(storage_path("app/import/importar_contatos.csv"));
    }

    /**
     * Import companies from csv file to database.
     *
     * @return mixed
     */
    public function import()
    {
        $contacts = [];

        foreach ($this->parseImport() as $contact) {
            $data = [
                'name'       => isset($contact[0]) && ! empty($contact[0]) ? $contact[0] : null,
                'email'      => isset($contact[1]) && ! empty($contact[1]) ? $contact[1] : null,
                'phone'      => isset($contact[2]) && ! empty($contact[2]) ? $contact[2] : null,
                'password'   => str_random(8),
                'is_contact' => true,
                'is_active'  => true,
            ];

            $address = [
                'postcode'   => isset($contact[3]) && ! empty($contact[3]) ? $contact[3] : null,
                'street'     => isset($contact[4]) && ! empty($contact[4]) ? $contact[4] : null,
                'number'     => isset($contact[5]) && ! empty($contact[5]) ? $contact[5] : null,
                'complement' => isset($contact[6]) && ! empty($contact[6]) ? $contact[6] : null,
                'district'   => isset($contact[7]) && ! empty($contact[7]) ? $contact[7] : null,
                'city'       => isset($contact[8]) && ! empty($contact[8]) ? $contact[8] : null,
                'state'      => isset($contact[9]) && ! empty($contact[9]) ? $contact[9] : null,
            ];

            $contact = User::firstOrCreate(['email' => $data['email']], $data);
            $contact->phones()->firstOrCreate(['number' => $data['phone']], ['number' => $data['phone'], 'type' => 0]);
            $contact->address()->update($address);

            $contacts[] = $contact;
        }

        return $this->respond($this->transformCollection($contacts, new ContactTransformer()));
    }

    /**
     * Get all companies that is related to the contact.
     *
     * @param User $contact
     * @return mixed
     */
    public function companies(User $contact)
    {
        $companies = $contact->companies()->get();

        return $this->respond([
            'items' => $this->transformCollection($companies, new CompanyTransformer()),
        ]);
    }

    /**
     * Get the monthly opened documents.
     *
     * @param User $contact
     * @return mixed
     */
    public function monthlyOpenedDocuments(User $contact)
    {
        $labels = [];
        $data = [
            'sent'   => [
                'name'  => 'Recebidos',
                'data'  => [],
                'color' => '#e6ad5c',
            ],
            'opened' => [
                'name'  => 'Abertos',
                'data'  => [],
                'color' => '#3498db',
            ],
        ];

        for ($i = 11; $i >= 0; $i --) {
            $period = Carbon::now()->subMonth($i);

            $data['sent']['data'][] =
                DocumentHistory::leftJoin('document_contact', 'document_contact.document_id', 'documents_history.document_id')
                    ->where('document_contact.contact_id', $contact->id)
                    ->where('action', 2)
                    ->whereMonth('documents_history.created_at', $period->month)
                    ->whereYear('documents_history.created_at', $period->year)
                    ->count();

            $data['opened']['data'][] =
                DocumentHistory::where('user_id', $contact->id)
                    ->whereIn('action', [3, 4])
                    ->whereMonth('documents_history.created_at', $period->month)
                    ->whereYear('documents_history.created_at', $period->year)
                    ->count();

            $labels[] = $period->format('m/Y');
        }

        return $this->respond([
            'labels' => $labels,
            'data'   => array_values($data),
        ]);
    }


    /**
     * Add Contact in company
     *
     * @param Company $company
     * @return mixed
     */
    public function addToCompany(Company $company)
    {
        $this->validate(request(), ['contats.*' => 'required|email']);
        $contacts = [];

        foreach (request('contacts') as $contact) {
            $contact = User::firstOrCreate(['email' => $contact], [
                'name'       => $contact,
                'password'   => str_random(8),
                'is_contact' => true,
                'is_active'  => true,
            ]);

            if (! $company->contacts->contains($contact->id)) {
                $company->contacts()->attach($contact->id);
            }

            $contacts[] = $this->transformItem($contact, new ContactTransformer());
        }

        return $this->respond($contacts);
    }

    /**
     * Revoke contact from company.
     *
     * @param Company $company
     * @param User $contact
     * @return mixed
     */
    public function revokeFromCompany(Company $company, User $contact)
    {
        $detach = $company->contacts()->detach($contact->id);

        return $this->respond(['revoked' => (bool) $detach]);
    }

    /**
     * Parse the companies by line.
     *
     * @return array
     */
    private function parseImport()
    {
        if (request()->hasFile('import')) {
            $lines = [];
            $cont = 0;

            $file = fopen(request()->file('import'), 'r');
            while (($line = fgetcsv($file)) !== false) {
                if ($cont > 0) {
                    $lines[] = $line;
                }
                $cont ++;
            }
            fclose($file);

            return $lines;
        }

        abort(404);
    }

    /**
     * Validate if import sheet header is right.
     *
     * @param $header
     */
    private function validateImportSheet($header)
    {
        if ($header[0] != 'Nome'
            || $header[1] != 'E-mail'
            || $header[2] != 'Telefone'
            || $header[3] != 'CEP'
            || $header[4] != 'Logradouro'
            || $header[5] != 'Número'
            || $header[6] != 'Complemento'
            || $header[7] != 'Bairro'
            || $header[8] != 'Cidade'
            || $header[9] != 'Estado') {
            abort(404);
        }
    }

    /**
     * Sync companies from request.
     *
     * @param User $contact
     */
    private function syncCompanies(User $contact)
    {
        if (request('companies')) {
            return $contact->companies()->sync(request('companies'));
        }

        return $contact->companies()->sync([]);
    }

    /**
     * Remove from database the old ones and
     * update with the newest phones.
     *
     * @param User $contact
     */
    private function savePhones(User $contact)
    {
        if (app('request')->input('phones')) {
            $contact->phones()->delete();

            array_map(function ($phone) use ($contact) {
                $contact->phones()->create($phone);
            }, app('request')->input('phones'));
        }
    }

    /**
     * Get the address from request data and save in the
     * contact address.
     *
     * @param User $contact
     */
    private function saveAddress(User $contact)
    {
        $address = [
            'postcode'   => app('request')->input('postcode'),
            'street'     => app('request')->input('street'),
            'number'     => app('request')->input('number') ?: 0,
            'complement' => app('request')->input('complement'),
            'district'   => app('request')->input('district'),
            'city'       => app('request')->input('city'),
            'state'      => app('request')->input('state'),
        ];

        $contact->address()->update($address);
    }
}
