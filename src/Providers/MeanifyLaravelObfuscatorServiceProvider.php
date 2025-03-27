<?php

namespace Meanify\LaravelObfuscator\Providers;

use Illuminate\Support\ServiceProvider;
use Meanify\LaravelObfuscator\Commands\ObfuscatorCommand;

class MeanifyLaravelObfuscatorServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../Config/meanify-laravel-obfuscator.php' => config_path('meanify-laravel-obfuscator.php'),
        ], 'meanify-configs');

        $this->publishes([
            __DIR__ . '/../Database/migrations/' => database_path('migrations'),
        ], 'meanify-migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ObfuscatorCommand::class,
            ]);
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/meanify-laravel-obfuscator.php', 'meanify-laravel-obfuscator');
    }
}
