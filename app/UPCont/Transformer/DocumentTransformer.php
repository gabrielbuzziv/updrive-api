<?php

namespace App\UPCont\Transformer;

use App\Http\Controllers\DocumentCategoryController;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use App\Document;

class DocumentTransformer extends TransformerAbstract
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
        'history', 'user', 'company', 'sharedWith', 'dispatch'
    ];

    /**
     * Document default transformation.
     *
     * @param Document $document
     * @return array
     */
    public function transform(Document $document)
    {
        return [
            'id'          => (int) $document->id,
            'name'        => $document->name,
            'filename'    => $document->filename,
            'cycle'       => $document->cycle,
            'validity'    => $document->validity,
            'note'        => $document->note,
            'status'      => $document->status,
            'type'        => $document->type,
            'links'       => [
                'download'  => action('DocumentController@download', [config('account')->slug, $document->id]),
                'visualize' => action('DocumentController@visualize', [config('account')->slug, $document->id]),
            ],
            'created_at' => $document->created_at->format('d/m/Y')
        ];
    }

    /**
     * Include dispatch transformer.
     *
     * @param Document $document
     * @return \League\Fractal\Resource\Item
     */
    public function includeDispatch(Document $document)
    {
        if ($document->dispatch) {
            return $this->item($document->dispatch, new DocumentDispatchTransformer());
        }
    }

    /**
     * Include contact transformer.
     *
     * @param Document $document
     * @return \League\Fractal\Resource\Item
     */
    public function includeSharedWith(Document $document)
    {
        return $this->collection($document->sharedWith, new ContactTransformer());
    }

    /**
     * Include user transformer.
     *
     * @param Document $document
     * @return \League\Fractal\Resource\Item
     */
    public function includeUser(Document $document)
    {
        return $this->item($document->user, new UserTransformer());
    }

    /**
     * Include company transformer.
     *
     * @param Document $document
     * @return \League\Fractal\Resource\Item
     */
    public function includeCompany(Document $document)
    {
        return $this->item($document->company, new CompanyTransformer());
    }

    /**
     * Include document history transformer
     *
     * @param Document $document
     * @return \League\Fractal\Resource\Collection
     */
    public function includeHistory(Document $document)
    {
        return $this->collection($document->history, new DocumentHistoryTransformer());
    }
}