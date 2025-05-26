<?php

namespace Steak\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Octane\Events\RequestReceived;
use Laravel\Octane\Events\RequestTerminated;
use Laravel\Octane\Events\WorkerStarting;
use Steak\Core\System;
use Steak\Services\Service;
use Steak\Engines\ViewManager;
use Steak\Repositories\BaseRepository;
use Steak\Concerns\MagicMethods;
use Steak\Contracts\OctaneCompatible;

class OctaneServiceProvider extends ServiceProvider
{
    protected $container = [];
    
    /**
     * Danh sách các lớp triển khai OctaneCompatible
     * 
     * @var array
     */
    protected $octaneAwareClasses = [];
    
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
        if (!$this->app->bound('octane')) {
            // octane not found
            return;
        }

        // Phát hiện các lớp triển khai OctaneCompatible
        $this->discoverOctaneAwareClasses();

        // Xử lý khi worker bắt đầu
        $this->app['events']->listen(WorkerStarting::class, function () {
            // Khởi tạo trạng thái ban đầu
            $this->prepareOctaneEnvironment();
        });

        // Xử lý khi nhận request
        $this->app['events']->listen(RequestReceived::class, function () {
            // Chuẩn bị cho request mới
            $this->prepareForNextRequest();
        });

        // Xử lý khi request kết thúc
        $this->app['events']->listen(RequestTerminated::class, function () {
            // Reset các trạng thái tĩnh sau khi xử lý request
            $this->resetStaticState();
            $this->resetServicesState();
        });
    }

    /**
     * Add service to the container
     *
     * @param mixed $service
     * @return $this
     */
    public function addService($service)
    {
        $this->container[] = $service;
        return $this;
    }

    /**
     * Phát hiện các lớp triển khai OctaneCompatible
     * 
     * @return void
     */
    protected function discoverOctaneAwareClasses(): void
    {
        // Thêm các lớp đã biết triển khai OctaneCompatible
        $this->octaneAwareClasses = [
            \Steak\Core\OctaneAwareService::class,
            // Thêm các lớp khác tại đây
        ];
    }

    /**
     * Chuẩn bị môi trường cho Octane
     */
    protected function prepareOctaneEnvironment(): void
    {
        // Cấu hình ban đầu cho worker
    }

    /**
     * Chuẩn bị cho request tiếp theo
     */
    protected function prepareForNextRequest(): void
    {
        // Thực hiện các tác vụ chuẩn bị cho mỗi request mới
    }

    /**
     * Reset trạng thái tĩnh
     */
    protected function resetStaticState(): void
    {
        // Reset các trạng thái tĩnh chính
        ViewManager::$shared = false;
        
        // Reset trạng thái của System class
        $this->resetSystemState();

        // Reset trạng thái của MagicMethods và Event Listeners
        $this->resetMagicMethodsState();
        
        // Reset trạng thái của các lớp triển khai OctaneCompatible
        $this->resetOctaneAwareClasses();
    }
    
    /**
     * Reset trạng thái của các lớp triển khai OctaneCompatible
     * 
     * @return void
     */
    protected function resetOctaneAwareClasses(): void
    {
        foreach ($this->octaneAwareClasses as $class) {
            if (class_exists($class) && is_subclass_of($class, OctaneCompatible::class)) {
                // Reset trạng thái tĩnh
                $class::resetStaticState();
                
                // Reset trạng thái của instance nếu đã được đăng ký trong container
                if ($this->app->bound($class)) {
                    $instance = $this->app->make($class);
                    $instance->resetInstanceState();
                }
            }
        }
    }

    /**
     * Reset trạng thái của System class
     */
    protected function resetSystemState(): void
    {
        // Reset các thuộc tính tĩnh cụ thể của System 
        // mà có thể gây rò rỉ trạng thái giữa các requests
        if (class_exists(System::class)) {
            // Reset các trạng thái tĩnh quan trọng
            $reflectionClass = new \ReflectionClass(System::class);
            $staticProperties = $reflectionClass->getStaticProperties();
            
            // Reset các thuộc tính tĩnh cụ thể mà không phải là readonly
            if (isset($staticProperties['_appinfo'])) {
                System::$_appinfo = null;
            }
            
            // Các thuộc tính khác có thể được reset tại đây
        }
    }

    /**
     * Reset trạng thái của MagicMethods và Event Listeners
     */
    protected function resetMagicMethodsState(): void
    {
        // Reset các event listeners và dynamic methods
        // để tránh rò rỉ trạng thái giữa các requests
        if (trait_exists(MagicMethods::class)) {
            $reflection = new \ReflectionClass(MagicMethods::class);
            
            // Tìm thuộc tính tĩnh $methods
            try {
                $methodsProperty = $reflection->getProperty('methods');
                $methodsProperty->setAccessible(true);
                
                // Lưu giữ các phương thức global
                $methods = $methodsProperty->getValue();
                $globalMethods = $methods['@global'] ?? ['static' => [], 'nonstatic' => []];
                
                // Reset và chỉ giữ lại global methods
                $methodsProperty->setValue([
                    '@global' => $globalMethods
                ]);
            } catch (\ReflectionException $e) {
                // Không tìm thấy thuộc tính, bỏ qua
            }
        }
        
        // Reset các lớp sử dụng MagicMethods
        $this->resetServiceState();
        $this->resetRepositoryState();
    }
    
    /**
     * Reset trạng thái của Service
     */
    protected function resetServiceState(): void
    {
        if (class_exists(Service::class)) {
            // Reset các trạng thái tĩnh của Service nếu cần
        }
    }
    
    /**
     * Reset trạng thái của Repository
     */
    protected function resetRepositoryState(): void
    {
        if (class_exists(BaseRepository::class)) {
            // Reset các trạng thái tĩnh của BaseRepository nếu cần
        }
    }

    protected function resetServicesState(): void
    {
        // Reset các phương thức có thể reset trạng thái của các service
        $resetFunctions = ['reset', 'resetState', 'clear', 'destroy'];
        // Reset các service trong container
        foreach ($this->container as $service) {
            // Kiểm tra nếu service là đối tượng và có phương thức reset
            if(!is_object($service)) {
                continue;
            }
            
            // Nếu service triển khai OctaneCompatible, gọi resetInstanceState
            if ($service instanceof OctaneCompatible) {
                $service->resetInstanceState();
                continue;
            }
            
            // Reset các phương thức có thể reset trạng thái của service
            foreach($resetFunctions as $function) {
                // Kiểm tra nếu phương thức tồn tại
                if(method_exists($service, $function)) {
                    // Gọi phương thức reset trạng thái của service
                    $service->$function();
                }
            }
        }
    }
} 