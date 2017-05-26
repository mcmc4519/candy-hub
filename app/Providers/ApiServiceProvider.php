<?php

namespace GetCandy\Providers;

use GetCandy\Api\Factory;
use Illuminate\Support\ServiceProvider;
use Validator;

class ApiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('unique_name_in_group', 'GetCandy\Http\Validators\Api\AttributeValidator@uniqueNameInGroup');
        Validator::extend('hashid_is_valid', 'GetCandy\Http\Validators\Api\HashidValidator@validForModel');

        $this->app->bind('api', function ($app) {
            return $app->make(Factory::class);
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
