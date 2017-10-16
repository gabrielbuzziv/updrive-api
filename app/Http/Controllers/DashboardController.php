<?php

namespace App\Http\Controllers;

use App\Company;
use App\Document;
use App\DocumentHistory;
use App\Http\Controllers\Traits\Transformable;
use App\UPCont\Transformer\DocumentTransformer;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends ApiController
{

    use Transformable;

    /**
     * DashboardController constructor.
     */
    public function __construct()
    {
        $this->middleware('permission:manage-core');
    }

    /**
     * Get overview statistics from documents.
     *
     * @return mixed
     */
    public function getOverview()
    {
        $interval = request('interval') ?: 'month';
        $labels = [];
        $data = [
            'sent'   => [
                'name'          => 'Documentos enviados',
                'data'          => [],
                'fillOpacity'   => '0.3',
                'color'         => '#4a90e2',
                'negativeColor' => '#4a90e2'
            ],
            'opened' => [
                'name'          => 'Documentos abertos',
                'data'          => [],
                'fillOpacity'   => '0.3',
                'color'         => '#27ae60',
                'negativeColor' => '#27ae60'
            ],
        ];


        switch ($interval) {
            case 'week':
                $time = 7;
                $documents = $this->getDocumentsFromDaysAgo($time);
                break;
            case 'month':
                $time = 30;
                $documents = $this->getDocumentsFromDaysAgo($time);
                break;
            case 'year':
                $time = 12;
                $documents = $documents = $this->getDocumentsFromMonthsAgo($time);
                break;
        }

        for ($i = $time; $i > 0; $i --) {
            $period = $interval == 'year' ? Carbon::today()->subMonths($i - 1)->format('m/Y') : Carbon::today()->subDays($i - 1)->format('d/m/Y');

            $labels[] = $period;
            $data['sent']['data'][$period] = 0;
            $data['opened']['data'][$period] = 0;

            $documentsFromPeriod = $documents->filter(function ($document) use ($period) {
                return $document->date == $period;
            })->toArray();

            foreach ($documentsFromPeriod as $document) {
                $document = (object) $document;
                switch ($document->action) {
                    case 2:
                        $data['sent']['data'][$period] += $document->amount;
                        break;
                    case 3:
                    case 4:
                        $data['opened']['data'][$period] += $document->amount;
                        break;
                    case 5:
                        break;
                }
            }
        }

        $data['sent']['data'] = array_values($data['sent']['data']);
        $data['opened']['data'] = array_values($data['opened']['data']);

        $sent = array_reduce($data['sent']['data'], function ($carry, $data) {
            return $carry + $data;
        }, 0);

        $opened = array_reduce($data['opened']['data'], function ($carry, $data) {
            return $carry + $data;
        }, 0);

        return $this->respond([
            'labels' => $labels,
            'data'   => array_values($data),
            'sent'   => $sent,
            'opened' => $opened,
        ]);
    }

    /**
     * Get last 11 pending documents.
     *
     * @return mixed
     */
    public function getPendingDocuments()
    {
        $include = ['company'];
        $documents = Document::with($include)->pending()->orderBy('created_at', 'desc')->take(11)->get();

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

    /**
     * Fetch the documents history $days before today.
     *
     * @param $days
     * @return mixed
     */
    private function getDocumentsFromDaysAgo($days)
    {
        $start = Carbon::today()->subDays($days)->format('Y-m-d');
        $end = Carbon::today()->format('Y-m-d');

        return (new DocumentHistory())
            ->select(
                DB::raw('COUNT(documents_history.document_id) as amount, DATE_FORMAT(documents_history.created_at, "%d/%m/%Y") as date, documents_history.action')
            )
            ->join('documents', 'documents.id', 'documents_history.document_id')
            ->whereIn('documents_history.action', [2, 3, 4])
            ->whereBetween('documents_history.created_at', [$start, $end])
            ->groupBy(DB::raw('DATE_FORMAT(documents_history.created_at, "%Y%m%d")'), 'documents_history.action')
            ->get();
    }

    /**
     * Fetch the documents history $months before today.
     *
     * @param $months
     * @return mixed
     */
    private function getDocumentsFromMonthsAgo($months)
    {
        $start = Carbon::today()->subMonths($months - 1)->startOfMonth()->format('Y-m-d');
        $end = Carbon::today()->endOfMonth()->format('Y-m-d');

        return (new DocumentHistory)
            ->select(
                DB::raw('COUNT(documents_history.document_id) as amount, DATE_FORMAT(documents_history.created_at, "%m/%Y") as date, documents_history.action')
            )
            ->join('documents', 'documents.id', 'documents_history.document_id')
            ->whereNull('documents.deleted_at')
            ->whereIn('documents_history.action', [2, 3, 4])
            ->whereBetween('documents_history.created_at', [$start, $end])
            ->groupBy(DB::raw('DATE_FORMAT(documents_history.created_at, "%Y%m")'), 'documents_history.action')
            ->get();
    }

}
