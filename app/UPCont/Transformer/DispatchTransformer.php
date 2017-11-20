<?php

namespace App\UPCont\Transformer;

use App\Dispatch;
use App\Http\Controllers\DocumentCategoryController;
use League\Fractal\TransformerAbstract;

class DispatchTransformer extends TransformerAbstract
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
        'sender', 'company', 'recipients', 'documents', 'tracking'
    ];

    /**
     * Document default transformation.
     *
     * @param Dispatch $dispatch
     * @return array
     */
    public function transform(Dispatch $dispatch)
    {
        return [
            'id'         => (int) $dispatch->id,
            'subject'    => $dispatch->subject,
            'message'    => $dispatch->message,
            'created_at' => $dispatch->created_at->format('d/m/Y \à\s H:i')
        ];
    }

    /**
     * Include tracking transformer.
     *
     * @param Dispatch $dispatch
     * @return \League\Fractal\Resource\Item
     */
    public function includeTracking(Dispatch $dispatch)
    {
        return $this->collection($dispatch->tracking, new DispatchTrackingTransformer());
    }

    /**
     * Include user transformer.
     *
     * @param Dispatch $dispatch
     * @return \League\Fractal\Resource\Item
     */
    public function includeSender(Dispatch $dispatch)
    {
        return $this->item($dispatch->sender, new UserTransformer());
    }

    /**
     * Include company transformer.
     *
     * @param Dispatch $dispatch
     * @return \League\Fractal\Resource\Item
     */
    public function includeCompany(Dispatch $dispatch)
    {
        return $this->item($dispatch->company, new CompanyTransformer());
    }

    /**
     * Include contact transformer.
     *
     * @param Dispatch $dispatch
     * @return \League\Fractal\Resource\Item
     */
    public function includeRecipients(Dispatch $dispatch)
    {
        return $this->collection($dispatch->recipients, new ContactTransformer());
    }

    /**
     * Include document transformer.
     *
     * @param Dispatch $dispatch
     * @return \League\Fractal\Resource\Item
     */
    public function includeDocuments(Dispatch $dispatch)
    {
        return $this->collection($dispatch->documents, new DocumentTransformer());
    }
}