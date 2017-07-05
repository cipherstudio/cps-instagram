<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// @fixed for mysql5.6
// @see https://laravel-news.com/laravel-5-4-key-too-long-error
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // @fixed msyql is not higher than 5.7.7
        // @see https://laravel-news.com/laravel-5-4-key-too-long-error
        Schema::defaultStringLength(191);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
