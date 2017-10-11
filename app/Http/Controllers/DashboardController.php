<?php

namespace App\Http\Controllers;

use App\Company;
use App\Document;
use App\Http\Controllers\Traits\Transformable;
use App\UPCont\Transformer\DocumentTransformer;
use App\User;
use Illuminate\Http\Request;

class DashboardController extends ApiController
{

    use Transformable;

    public function getOverview()
    {
        
    }

    public function getPendingDocuments()
    {
        $include = ['company'];
        $documents = Document::with($include)->pending()->orderBy('created_at', 'desc')->get();

        return $this->respond($this->transformCollection($documents, new DocumentTransformer(), $include));
    }
    
    /**
     * Get count of companies, companies and documents.
     *
     * @return mixed
     */
    public function getMetrics()
    {
        return $this->respond([
            'companies' => Company::count(),
            'contacts'  => User::contact()->count(),
            'documents' => Document::count(),
        ]);
    }

}
