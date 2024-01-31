<?php

namespace littlefish\Uberduck;

use Illuminate\Support\ServiceProvider;

class UberduckServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/uberduck.php', 'uberduck');

        $this->app->bind(UberduckClient::class, fn () => new UberduckClient(config('uberduck')));
        $this->app->bind(Uberduck::class, fn () => new Uberduck($this->app->make(UberduckClient::class)));
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/uberduck.php' => config_path('uberduck.php'),
        ]);
    }
}
