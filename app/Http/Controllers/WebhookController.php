<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{

    public function trackingDeliveries()
    {
        $params = request()->all();

        Log::useDailyFiles(storage_path('logs/tracking-deliveries.log'));
        Log::info([
            'params' => $params
//            'account' => $params['account'],
//            'dispatch' => $params['dispatch']
        ]);
    }
}
