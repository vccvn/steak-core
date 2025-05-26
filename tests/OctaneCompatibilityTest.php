<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Octane\Events\RequestTerminated;
use Laravel\Octane\Events\WorkerStarting;
use Laravel\Octane\Octane;
use Steak\Core\System;
use Steak\Engines\ViewManager;
use Steak\Providers\OctaneServiceProvider;
use Tests\TestCase;

class OctaneCompatibilityTest extends TestCase
{
    /**
     * Kiểm tra khởi tạo OctaneServiceProvider.
     */
    public function test_octane_service_provider_registers_correctly()
    {
        $this->assertTrue(class_exists(OctaneServiceProvider::class));
        
        $provider = new OctaneServiceProvider($this->app);
        $this->assertInstanceOf(OctaneServiceProvider::class, $provider);
    }
    
    /**
     * Kiểm tra không có rò rỉ trạng thái khi đăng ký.
     */
    public function test_no_state_leakage_when_registering_octane_service_provider()
    {
        if (!class_exists(Octane::class)) {
            $this->markTestSkipped('Laravel Octane is not installed.');
            return;
        }
        
        // Thiết lập một số trạng thái trong ViewManager
        ViewManager::$shared = true;
        
        // Giả lập một sự kiện RequestTerminated
        Event::fake();
        Event::dispatch(new RequestTerminated(request(), response()));
        
        // Kiểm tra trạng thái đã được reset
        $this->assertFalse(ViewManager::$shared);
    }
    
    /**
     * Kiểm tra khả năng xử lý trạng thái tĩnh.
     */
    public function test_static_state_is_properly_reset()
    {
        if (!class_exists(Octane::class)) {
            $this->markTestSkipped('Laravel Octane is not installed.');
            return;
        }
        
        // Giả lập một request
        $request1 = $this->get('/');
        
        // Đặt một số trạng thái tĩnh
        if (property_exists(System::class, '_appinfo')) {
            System::$_appinfo = ['test' => 'data'];
        }
        
        // Giả lập sự kiện RequestTerminated
        Event::fake();
        Event::dispatch(new RequestTerminated(request(), response()));
        
        // Kiểm tra trạng thái đã được reset
        if (property_exists(System::class, '_appinfo')) {
            $this->assertNull(System::$_appinfo);
        }
    }
} 