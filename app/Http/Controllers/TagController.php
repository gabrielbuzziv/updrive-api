<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\Transformable;
use App\Tag;
use App\UPCont\Transformer\TagTransformer;
use Illuminate\Http\Request;

class TagController extends ApiController
{

    use Transformable;


    /**
     * Get all tags.
     *
     * @return mixed
     */
    public function index()
    {
        $tags = Tag::orderBy('id', 'desc')->get();

        return $this->respond([
            'items' => $this->transformCollection($tags, new TagTransformer()),
        ]);
    }
}
