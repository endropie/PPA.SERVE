<?php

namespace App\Providers;

use App\Http\Serializers\NoKeyDataSerializer;
use Illuminate\Support\ServiceProvider;
use League\Fractal\Manager;

class ApiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['Dingo\Api\Transformer\Factory']->setAdapter(function ($app) {
            $fractal = new \League\Fractal\Manager();
            $fractal->setSerializer(new \App\Api\Serializers\NoKeyDataSerializer());
            return new \Dingo\Api\Transformer\Adapter\Fractal($fractal);
        });
    }
}
