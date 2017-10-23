<?php

namespace App\UPCont\Transformer;

use League\Fractal\TransformerAbstract;
use App\Tag;

class TagTransformer extends TransformerAbstract
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
       'contacts'
    ];

    /**
     * User default transformation.
     *
     * @param Tag $tag
     * @return array
     */
    public function transform(Tag $tag)
    {
        return [
            'id' => (int) $tag->id,
            'name' => $tag->name
        ];
    }

    /**
     * Include notifications transformer.
     *
     * @param Tag $tag
     * @return \League\Fractal\Resource\Collection
     */
    public function includeContacts(Tag $tag)
    {
        return $this->collection($tag->contacts, new ContactTransformer());
    }
}