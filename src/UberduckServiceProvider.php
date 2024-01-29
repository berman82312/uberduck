<?php

namespace littlefish\Uberduck;

use Illuminate\Support\ServiceProvider;

class CometChatServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/uberduck.php', 'uberduck');

        $this->app->bind(Uberduck::class, fn () => new Uberduck(config('uberduck')));
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/uberduck.php' => config_path('uberduck.php'),
        ]);
    }
}
