<?php

namespace App\UPCont\Transformer;

use App\DocumentDispatchTracking;
use App\Http\Controllers\DocumentCategoryController;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use App\Document;

class DocumentDispatchTrackingTransformer extends TransformerAbstract
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
        'contact', 'dispatch',
    ];

    /**
     * Document default transformation.
     *
     * @param DocumentDispatchTracking $track
     * @return array
     */
    public function transform(DocumentDispatchTracking $track)
    {
        return [
            'id'         => (int) $track->id,
            'status'     => $track->status,
            'created_at' => $track->created_at->format('d/m/Y H:i'),
        ];
    }

    /**
     * Include contact transformer.
     *
     * @param DocumentDispatchTracking $track
     * @return \League\Fractal\Resource\Item
     */
    public function includeContact(DocumentDispatchTracking $track)
    {
        return $this->item($track->contact, new ContactTransformer());
    }

    /**
     * Include dispatch transformer.
     *
     * @param DocumentDispatchTracking $track
     * @return \League\Fractal\Resource\Item
     */
    public function includeDispatch(DocumentDispatchTracking $track)
    {
        return $this->item($track->dispatch, new DocumentDispatchTransformer());
    }
}