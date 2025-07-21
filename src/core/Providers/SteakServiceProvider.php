<?php
namespace Steak\Core\Providers;

use Illuminate\Support\ServiceProvider;

class SteakServiceProvider extends ServiceProvider
{
    /**
     * Đăng ký service, helper và repository nếu môi trường cho phép.
     */
    public function register()
    {
        if (!$this->app) {
            return;
        }

        // Merge config
        $this->mergeConfigFrom(__DIR__ . '/../../../config/steak.php', 'steak');

        // Đăng ký OctaneServiceProvider nếu Laravel Octane được phát hiện
        if (class_exists('Laravel\Octane\Octane')) {
            $this->app->register(OctaneServiceProvider::class);
        }

        // Bind repository vào container (nếu Laravel đang chạy)
        if ($this->app->bound('config')) {
            // $this->app->bind(
            //     \Steak\Contracts\UserRepositoryInterface::class,
            //     \Steak\Core\epositories\UserRepository::class
            // );
        }
    }

    /**
     * Boot các thành phần của Steak.
     */
    public function boot()
    {
        if (!$this->app || !$this->app->runningInConsole()) {
            return;
        }

        // Load migrations từ thư viện
        $this->loadMigrationsFrom(__DIR__ . '/../../../database/migrations');
        
        // Load migrations từ ứng dụng nếu có (backward compatibility)
        if (is_dir(base_path('database/migrations'))) {
            $this->loadMigrationsFrom(base_path('database/migrations'));
        }

        // Load translations nếu có
        if (is_dir(base_path('resources/lang'))) {
            $this->loadTranslationsFrom(base_path('resources/lang'), 'Steak');
        }

        // Load views nếu có
        if (is_dir(base_path('resources/views'))) {
            $this->loadViewsFrom(base_path('resources/views'), 'Steak');
        }

        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Steak\Core\Console\Commands\PublishSteakMigrationsCommand::class,
            ]);

            // Publish config file
            $this->publishes([
                __DIR__ . '/../../config/steak.php' => config_path('steak.php'),
            ], 'steak-config');

            // Publish migrations
            $this->publishes([
                __DIR__ . '/../../database/migrations' => database_path('migrations'),
            ], 'steak-migrations');
        }
    }
}