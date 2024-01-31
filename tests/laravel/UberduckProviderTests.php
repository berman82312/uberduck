<?php

namespace littlefish\Uberduck\laravel;

use littlefish\Uberduck\Uberduck;
use littlefish\Uberduck\UberduckServiceProvider;
use Orchestra\Testbench\TestCase;

class UberduckProviderTests extends TestCase
{
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string<\Illuminate\Support\ServiceProvider>>
     */
    protected function getPackageProviders($app)
    {
        return [
            UberduckServiceProvider::class,
        ];
    }

    public function test_uberduck_service_registered_correctly()
    {
        $uberduck = $this->app->make(Uberduck::class);

        $this->assertInstanceOf(Uberduck::class, $uberduck);
    }
}
