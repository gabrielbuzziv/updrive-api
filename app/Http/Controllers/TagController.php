<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\Transformable;
use App\Tag;
use App\UPCont\Transformer\TagTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

    /**
     * Create tag.
     *
     * @return mixed
     */
    public function create()
    {
        Validator::make(request()->all(), ['tag' => 'required'])->validate();
        $tag = Tag::create(['name' => request('tag')]);

        return $this->respondCreated($this->transformItem($tag, new TagTransformer()));
    }
}
