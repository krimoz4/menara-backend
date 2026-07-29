<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Database\Events\ConnectionEstablished::class,
            function (\Illuminate\Database\Events\ConnectionEstablished $event) {
                if ($event->connectionName === 'sqlsrv') {
                    $event->connection->unprepared('SET DATEFORMAT ymd');
                }
            }
        );
    }
}
