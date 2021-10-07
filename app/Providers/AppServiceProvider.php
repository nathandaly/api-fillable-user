<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Context\User\Contract\ApiConsumer;
use App\Context\User\Services\ReqresService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ApiConsumer::class, ReqresService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
