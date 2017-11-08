<?php

namespace App\UPCont\Transformer;

use App\DispatchTracking;
use App\Http\Controllers\DocumentCategoryController;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use App\Document;

class DispatchTrackingTransformer extends TransformerAbstract
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
        'recipient', 'dispatch',
    ];

    /**
     * Document default transformation.
     *
     * @param DispatchTracking $track
     * @return array
     */
    public function transform(DispatchTracking $track)
    {
        return [
            'id'         => (int) $track->id,
            'status'     => $track->status,
            'created_at' => $track->created_at->format('d/m/Y \à\s H:i'),
        ];
    }

    /**
     * Include contact transformer.
     *
     * @param DispatchTracking $track
     * @return \League\Fractal\Resource\Item
     */
    public function includeRecipient(DispatchTracking $track)
    {
        return $this->item($track->recipient, new ContactTransformer());
    }

    /**
     * Include dispatch transformer.
     *
     * @param DispatchTracking $track
     * @return \League\Fractal\Resource\Item
     */
    public function includeDispatch(DispatchTracking $track)
    {
        return $this->item($track->dispatch, new DispatchTransformer());
    }
}