<?php

namespace App\Providers;

use App\User;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Broadcast::channel('App.User.*', function ($user, $id) {
            return (int) $user->id == (int) $id;
        });

        Broadcast::routes(['middleware' => ['jwt.auth']]);
    }
}
