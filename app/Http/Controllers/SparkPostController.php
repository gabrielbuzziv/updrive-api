<?php

namespace App\Http\Controllers;

use App\Mail\SparkPostMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SparkPostController extends Controller
{

    public function teste()
    {
        return action('DocumentController@download', [config('account')->slug, 1]);
    }


    public function webhook()
    {
//        $data = request()->all() ?: [];
//
//        Mail::to('gabrielbuzziv@gmail.com')->send(new SparkPostMail($data));
    }
}
