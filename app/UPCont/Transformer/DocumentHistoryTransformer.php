<?php

namespace App\UPCont\Transformer;

use League\Fractal\TransformerAbstract;
use App\DocumentHistory;

class DocumentHistoryTransformer extends TransformerAbstract
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
        'user', 'document'
    ];

    /**
     * DocumentHistory default transformation.
     *
     * @param DocumentHistory $history
     * @return array
     */
    public function transform(DocumentHistory $history)
    {
        return [
            'id' => (int) $history->id,
            'action' => $history->action,
            'description' => $history->description,
            'created_at' => $history->created_at->format('d/m/Y \à\s H:i')
        ];
    }

    /**
     * Include document transformer.
     *
     * @param DocumentHistory $history
     * @return \League\Fractal\Resource\Item
     */
    public function includeDocument(DocumentHistory $history)
    {
        return $this->item($history->document, new DocumentTransformer());
    }

    /**
     * Include user transformer.
     *
     * @param DocumentHistory $history
     * @return \League\Fractal\Resource\Item
     */
    public function includeUser(DocumentHistory $history)
    {
        if ($history->user) {
            return $this->item($history->user, new UserTransformer());
        }
    }
}