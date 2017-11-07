<?php

namespace App\UPCont\Transformer;

use App\Http\Controllers\DocumentCategoryController;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use App\Report;

class ReportTransformer extends TransformerAbstract
{

    /**
     * The attribute set the default fields to include.
     *
     * @var array
     */
    protected $defaultIncludes = [
        //
    ];

    /**
     * The attribute set the available fields to include.
     *
     * @var array
     */
    protected $availableIncludes = [
        'user'
    ];

    /**
     * Document default transformation.
     *
     * @param Report $report
     * @return array
     */
    public function transform(Report $report)
    {
        return [
            'id'         => (int) $report->id,
            'report'     => (int) $report->report_id,
            'filters'    => unserialize($report->filters),
            'created_at' => Carbon::createFromFormat('Y-m-d H:i:s', $report->created_at)->format('d/m/Y H:i')
        ];
    }

    /**
     * Include user transformer.
     *
     * @param Report $report
     * @return \League\Fractal\Resource\Item
     */
    public function includeUser(Report $report)
    {
        return $this->item($report->user, new UserTransformer());
    }
}