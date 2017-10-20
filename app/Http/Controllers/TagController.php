<?php

namespace App\Http\Controllers;

use App\Tag;
use Illuminate\Http\Request;

class TagController extends ApiController
{


    public function index()
    {
        $tags = Tag::get();

        return $this->respond($tags->toArray());
    }
}
