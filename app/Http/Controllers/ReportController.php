<?php

namespace App\Http\Controllers;

use App\Document;
use App\Http\Controllers\Traits\Transformable;
use App\Report;
use App\UPCont\Transformer\DocumentTransformer;
use App\UPCont\Transformer\ReportTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends ApiController
{

    use Transformable;

    /**
     * Get reports by id.
     *
     * @param $id
     * @return mixed
     */
    public function index($id)
    {
        $include = ['user'];
        $reports = Report::with($include)
            ->where('report_id', $id)
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return $this->respond([
            'total' => $reports->total(),
            'items' => $this->transformCollection($reports, new ReportTransformer(), $include)
        ]);
    }

    /**
     * Store report in database.
     *
     * @param $id
     * @return mixed
     */
    public function store($id)
    {
        $data = request()->all();

        if (isset($data['between']) && $data['between'][0] == null && $data['between'][1] == null) {
            unset($data['between']);
        }

        $report = Report::create([
            'report_id' => $id,
            'user_id'   => auth()->user()->id,
            'filters'   => serialize($data)
        ]);

        return $this->respondCreated($this->transformItem($report, new ReportTransformer()));
    }

    /**
     * Show report.
     *
     * @param Report $report
     * @return mixed
     */
    public function show(Report $report)
    {
        return $this->respond($this->transformItem($report, new ReportTransformer()));
    }

    /**
     * Get result of status report.
     *
     * @param Report $report
     * @return mixed
     */
    public function getStatusReport(Report $report)
    {
        $filters = array_filter(unserialize($report->filters), function ($filter) {
            return ! empty($filter);
        });

        if (isset($filters['between'])) {
            $filters['between'][0] = Carbon::createFromFormat('Y-m-d\TH:i:s.000Z', $filters['between'][0]);
            $filters['between'][1] = Carbon::createFromFormat('Y-m-d\TH:i:s.000Z', $filters['between'][1]);
        }

        $include = ['company', 'user', 'sharedWith'];
        $documents = Document::with($include)
            ->where(function ($query) use ($filters) {
                $filters = (object) $filters;

                if (isset($filters->document))
                    $query->where('name', 'like', "%{$filters->document}%");

                if (isset($filters->status))
                    $query->whereIn('status', $filters->status);

                if (isset($filters->company))
                    $query->where('company_id', $filters->company['id']);

                if (isset($filters->sender))
                    $query->where('user_id', $filters->sender['id']);

                if (isset($filters->between)) {
                    $query->whereBetween('created_at', [$filters->between[0], $filters->between[1]]);
                }
            })
            ->orderBy(DB::raw('CASE WHEN status = 2 THEN 1 ELSE 2 END'))
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return $this->respond([
            'total' => $documents->total(),
            'items' => $this->transformCollection($documents, new DocumentTransformer(), $include)
        ]);
    }
}
