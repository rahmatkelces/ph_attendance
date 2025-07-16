<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        DB::listen(function ($query) {
            // Log::info($query->sql);
            // Log::info($query->bindings);
            Log::info($query->time);
        });
    }

    public function register()
    {
        //
    }
}
