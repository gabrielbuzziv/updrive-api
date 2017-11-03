<?php

namespace App\Http\Controllers;

use App\Document;
use App\Http\Controllers\Traits\Transformable;
use App\UPCont\Transformer\DocumentTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends ApiController
{

    use Transformable;

    public function sent()
    {
        $include = ['company', 'user', 'sharedWith'];
        $documents = Document::with($include)
            ->where(function($query) {
                $this->filterDocument($query);
                $this->filterCompany($query);
                $this->filterSender($query);
                $this->filterStatus($query);
            })
            ->take(20)
            ->orderBy(DB::raw('CASE WHEN status = 2 THEN 1 ELSE 2 END'))
            ->get();

        return $this->respond($this->transformCollection($documents, new DocumentTransformer(), $include));
    }

    /**
     * Filter document.
     *
     * @param $query
     * @return string
     */
    private function filterDocument($query)
    {
        $document = request('document');
        return request('document') ? $query->where('name', 'like', "%{$document}%") : '';
    }

    /**
     * Filter company.
     *
     * @param $query
     * @return string
     */
    private function filterCompany($query)
    {
        return request('company') ? $query->where('company_id', request('company')) : '';
    }

    /**
     * Filter company.
     *
     * @param $query
     * @return string
     */
    private function filterSender($query)
    {
        return request('sender') ? $query->where('user_id', request('sender')) : '';
    }

    /**
     * Filter company.
     *
     * @param $query
     * @return string
     */
    private function filterStatus($query)
    {
        return request('status') ? $query->whereIn('status', request('status')) : '';
    }
}
