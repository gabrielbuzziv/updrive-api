<?php

namespace App\UPCont\Transformer;

use App\DocumentDispatch;
use App\Http\Controllers\DocumentCategoryController;
use League\Fractal\TransformerAbstract;

class DocumentDispatchTransformer extends TransformerAbstract
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
        'user', 'company', 'contacts', 'documents'
    ];

    /**
     * Document default transformation.
     *
     * @param DocumentDispatch $dispatch
     * @return array
     */
    public function transform(DocumentDispatch $dispatch)
    {
        return [
            'id'      => (int) $dispatch->id,
            'subject' => $dispatch->subject,
            'message' => $dispatch->message,
        ];
    }

    /**
     * Include user transformer.
     *
     * @param DocumentDispatch $dispatch
     * @return \League\Fractal\Resource\Item
     */
    public function includeUser(DocumentDispatch $dispatch)
    {
        return $this->item($dispatch->user, new UserTransformer());
    }

    /**
     * Include company transformer.
     *
     * @param DocumentDispatch $dispatch
     * @return \League\Fractal\Resource\Item
     */
    public function includeCompany(DocumentDispatch $dispatch)
    {
        return $this->item($dispatch->company, new CompanyTransformer());
    }

    /**
     * Include contact transformer.
     *
     * @param DocumentDispatch $dispatch
     * @return \League\Fractal\Resource\Item
     */
    public function includeContacts(DocumentDispatch $dispatch)
    {
        return $this->collection($dispatch->contacts, new ContactTransformer());
    }

    /**
     * Include document transformer.
     *
     * @param DocumentDispatch $dispatch
     * @return \League\Fractal\Resource\Item
     */
    public function includeDocuments(DocumentDispatch $dispatch)
    {
        return $this->collection($dispatch->documents, new DocumentTransformer());
    }
}