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
        // Nạp file cấu hình steak.php từ src/config vào hệ thống cấu hình Laravel với namespace 'steak'.
        $this->mergeConfigFrom(__DIR__ . '/../../config/steak.php', 'steak');

        // Đăng ký OctaneServiceProvider nếu Laravel Octane được phát hiện
        // Nếu project có cài Laravel Octane thì tự động đăng ký provider OctaneServiceProvider để hỗ trợ Octane.
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
        // Cho phép Laravel tự động nhận diện và chạy các file migration của package khi chạy artisan migrate.
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        
        // Load migrations từ ứng dụng nếu có (backward compatibility)
        // Nếu thư mục migrations của app tồn tại thì cũng nạp migrations ở đó (giữ tương thích cũ hoặc cho phép override migrations).
        if (is_dir(base_path('database/migrations'))) {
            $this->loadMigrationsFrom(base_path('database/migrations'));
        }

        // Load translations nếu có
        // Cho phép package sử dụng các file ngôn ngữ (lang) của ứng dụng nếu có.
        if (is_dir(base_path('resources/lang'))) {
            $this->loadTranslationsFrom(base_path('resources/lang'), 'Steak');
        }

        // Load views nếu có
        // Cho phép package sử dụng các file view của ứng dụng nếu có.
        if (is_dir(base_path('resources/views'))) {
            $this->loadViewsFrom(base_path('resources/views'), 'Steak');
        }

        // Register console commands
        // Đăng ký command tùy chỉnh cho artisan, ví dụ: php artisan steak:publish-migrations
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Steak\Core\Console\Commands\PublishSteakMigrationsCommand::class,
            ]);

            // Publish config file
            // Cho phép người dùng copy file cấu hình mặc định của package ra thư mục config của app để tùy chỉnh.
            $this->publishes([
                __DIR__ . '/../../config/steak.php' => config_path('steak.php'),
            ], 'steak-config');

            // Publish migrations
            // Cho phép người dùng copy các file migration của package ra thư mục database/migrations của app để tùy chỉnh.
            $this->publishes([
                __DIR__ . '/../../database/migrations' => database_path('migrations'),
            ], 'steak-migrations');
        }
    }
}