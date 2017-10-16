<?php

namespace App\Http\Controllers;

use App\Company;
use App\DocumentHistory;
use App\Http\Controllers\Traits\Transformable;
use App\Http\Requests\CompanyRequest;
use App\UPCont\Transformer\CompanyTransformer;
use App\UPCont\Transformer\ContactTransformer;
use App\UPCont\Transformer\FolderTransformer;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CompanyController extends ApiController
{

    use Transformable;

    /**
     * CompanyController constructor.
     */
    public function __construct()
    {
        $this->middleware('permission:manage-core');
    }

    /**
     * Show all companies based in the request.
     *
     * @return mixed
     */
    public function index()
    {
        $limit = request('limit') ?: 25;
        $companies = Company::search(request('filter'), null, true, true)
            ->orderBy('identifier')
            ->orderBy('name', 'ASC')
            ->paginate($limit);

        return $this->respond([
            'total' => $companies->total(),
            'items' => $this->transformCollection($companies, new CompanyTransformer()),
        ]);
    }

    /**
     * Store company in database.
     *
     * @param CompanyRequest $request
     * @return mixed;
     */
    public function store(CompanyRequest $request)
    {
        try {
            $company = Company::create($request->all());
            $this->saveAddress($company);

            return $this->respondCreated($this->transformItem($company, new CompanyTransformer()));
        } catch (QueryException $e) {
            Log::error(logMessage($e, 'Ocorreu um erro de SQL durante a criação da empresa.'), logUser());

            return $this->respondBadRequest($e, 'Ops, verifique se você preencheu todos os campos corretamente e tente novamente.');
        } catch (\Exception $e) {
            Log::critical(logMessage($e, 'Ocorreu um erro durante a criaçao da empresa.'), logUser());

            return $this->respondInternalError($e);
        }
    }

    /**
     * Update company in database.
     *
     * @param Company $company
     * @param CompanyRequest $request
     * @return mixed
     */
    public function update(Company $company, CompanyRequest $request)
    {
        try {
            $company->update($request->all());
            $this->saveAddress($company);

            return $this->respond($this->transformItem($company, new CompanyTransformer()));
        } catch (QueryException $e) {
            Log::error(logMessage($e, "Ocorreu um erro SQL durante a atualização da empresa. (ID: {$company->id})"), logUser());

            return $this->respondBadRequest($e, 'Ops, verifique se você preencheu todos os campos corretamente e tente novamente.');
        } catch (\Exception $e) {
            Log::critical(logMessage($e, "Ocorreu um erro durante a atualização da empresa. (ID: {$company->id})"), logUser());

            return $this->respondInternalError($e);
        }
    }

    /**
     * Walk in an array of ids and try to instanciate each one as a Company,
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
                if ($company = Company::find($item)) {
                    $company->delete();
                    $deleted ++;
                }
            }

            return $this->respond(['total' => $deleted]);
        } catch (\Exception $e) {
            Log::error(logMessage($e, 'Ocorreu um erro ao remover empresas.'), logUser());

            return $this->respondInternalError($e);
        }
    }

    /**
     * Return the data of the requested company.
     *
     * @param Company $company
     * @return mixed
     */
    public function show(Company $company)
    {
        $include = ['address', 'contacts.address', 'contacts.phones'];
        $company = Company::with($include)->findOrFail($company->id);

        return $this->respond($this->transformItem($company, new CompanyTransformer(), $include));
    }

    /**
     * Get the count of companies.
     *
     * @return mixed
     */
    public function total()
    {
        $companies = Company::withTrashed()->count();

        return $this->respond(['total' => $companies]);
    }

    /**
     * Return the download of default import sheet.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadImportSheet()
    {
        return response()->download(storage_path("app/import/importar_empresas.csv"));
    }

    /**
     * Import companies from csv file to database.
     * 
     * @return mixed
     */
    public function import()
    {
        $companies = [];

        foreach ($this->parseImport() as $company) {
            $data = [
                'identifier'     => isset($company[0]) && ! empty($company[0])  ? $company[0] : null,
                'name'           => isset($company[1]) && ! empty($company[1]) ? $company[1] : null,
                'nickname'       => isset($company[2]) && ! empty($company[2]) ? $company[2] : null,
                'email'          => isset($company[3]) && ! empty($company[3]) ? $company[3] : null,
                'phone'          => isset($company[4]) && ! empty($company[4]) ? $company[4] : null,
                'taxvat'         => isset($company[5]) && ! empty($company[5]) ? $company[5] : null,
                'docnumber'      => isset($company[6]) && ! empty($company[6]) ? $company[6] : null,
                'docnumber_town' => isset($company[7]) && ! empty($company[7]) ? $company[7] : null,
            ];

            $address = [
                'postcode'   => isset($company[8]) && ! empty($company[8]) ? $company[8] : null,
                'street'     => isset($company[9]) && ! empty($company[9]) ? $company[9] : null,
                'number'     => isset($company[10]) && ! empty($company[10]) ? $company[10] : null,
                'complement' => isset($company[11]) && ! empty($company[11]) ? $company[11] : null,
                'district'   => isset($company[12]) && ! empty($company[12]) ? $company[12] : null,
                'city'       => isset($company[13]) && ! empty($company[13]) ? $company[13] : null,
                'state'      => isset($company[14]) && ! empty($company[14]) ? $company[14] : null,
            ];

            $company = Company::firstOrCreate(['identifier' => $data['identifier']], $data);
            $company->address()->update($address);

            $companies[] = $company;
        }

        return $this->respond($this->transformCollection($companies, new CompanyTransformer()));
    }

    /**
     * Return the contacts from requested company id.
     *
     * @param Company $company
     * @return mixed
     */
    public function contacts(Company $company)
    {
        $query = request('query');
        $contacts = $company->contacts()->search($query)->get();

        return $this->respond([
            'items' => $this->transformCollection($contacts, new ContactTransformer()),
        ]);
    }

    /**
     * Get the monthly opened documents.
     *
     * @param Company $company
     * @return mixed
     */
    public function monthlyOpenedDocuments(Company $company)
    {
        $labels = [];
        $data = [
            'sent'    => [
                'name'  => 'Recebidos',
                'data'  => [],
                'color' => '#e6ad5c',
            ],
            'opened'  => [
                'name'  => 'Abertos',
                'data'  => [],
                'color' => '#3498db',
            ],
            'expired' => [
                'name'  => 'Vencidos',
                'data'  => [],
                'color' => '#c9302c',
            ],
        ];

        for ($i = 11; $i >= 0; $i --) {
            $period = Carbon::now()->subMonth($i);

            $data['sent']['data'][] =
                DocumentHistory::join('documents', 'documents.id', 'documents_history.document_id')
                    ->where('documents.company_id', $company->id)
                    ->where('action', 2)
                    ->whereMonth('documents_history.created_at', $period->month)
                    ->whereYear('documents_history.created_at', $period->year)
                    ->count();

            $data['opened']['data'][] =
                DocumentHistory::join('documents', 'documents.id', 'documents_history.document_id')
                    ->where('documents.company_id', $company->id)
                    ->whereIn('action', [3, 4])
                    ->whereMonth('documents_history.created_at', $period->month)
                    ->whereYear('documents_history.created_at', $period->year)
                    ->count();

            $data['expired']['data'][] =
                DocumentHistory::join('documents', 'documents.id', 'documents_history.document_id')
                    ->where('documents.company_id', $company->id)
                    ->where('action', 5)
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
                } else {
                    $this->validateImportSheet($line);
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
        if ($header[0] != 'Código'
            || $header[1] != 'Razão Social'
            || $header[2] != 'Nome Fantasia'
            || $header[3] != 'E-mail'
            || $header[4] != 'Telefone'
            || $header[5] != 'CNPJ'
            || $header[6] != 'Inscrição Estadual'
            || $header[7] != 'Inscrição Municipal'
            || $header[8] != 'CEP'
            || $header[9] != 'Logradouro'
            || $header[10] != 'Número'
            || $header[11] != 'Complemento'
            || $header[12] != 'Bairro'
            || $header[13] != 'Cidade'
            || $header[14] != 'Estado') {
            abort(404);
        }
    }

    /**
     * Get the address from request data and save in the
     * company address.
     *
     * @param Company $company
     */
    private function saveAddress(Company $company)
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

        $company->address()->update($address);
    }
}
