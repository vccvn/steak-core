# Hướng dẫn sử dụng Steak với Laravel Octane

Laravel Octane tăng tốc ứng dụng của bạn bằng cách giữ ứng dụng trong bộ nhớ giữa các request. Tuy nhiên, điều này có thể dẫn đến các vấn đề về bảo mật và rò rỉ dữ liệu nếu không được cấu hình đúng cách. Tài liệu này cung cấp hướng dẫn chi tiết để đảm bảo Steak hoạt động tốt và bảo mật với Laravel Octane.

## Cài đặt

Thư viện Steak đã được thiết kế để tương thích với Laravel Octane. Khi phát hiện Laravel Octane, `SteakServiceProvider` sẽ tự động đăng ký `OctaneServiceProvider` để xử lý các vấn đề tương thích.

## Vấn đề phổ biến khi sử dụng Laravel Octane

### 1. Rò rỉ trạng thái tĩnh

Laravel Octane giữ ứng dụng trong bộ nhớ giữa các request, có thể dẫn đến các vấn đề rò rỉ trạng thái nếu bạn sử dụng các thuộc tính tĩnh. Các vấn đề này đã được xử lý tự động thông qua `OctaneServiceProvider`.

### 2. Singletons và chia sẻ dữ liệu

Các singleton và service được chia sẻ giữa các request trong Laravel Octane. Cần cẩn thận khi sử dụng chúng để tránh rò rỉ dữ liệu từ request này sang request khác.

## Cách sử dụng đúng

### Sử dụng trait OctaneCompatible

Đối với các lớp tùy chỉnh có sử dụng trạng thái tĩnh, hãy sử dụng trait `OctaneCompatible`:

```php
use Steak\Concerns\OctaneCompatible;

class YourClass
{
    use OctaneCompatible;
    
    protected static $data = [];
    
    // ...
}
```

### Tránh sử dụng thuộc tính tĩnh cho dữ liệu request

```php
// KHÔNG NÊN
class BadService
{
    public static $requestData = [];
}

// NÊN
class GoodService
{
    protected $requestData = [];
    
    public function setRequestData($data)
    {
        $this->requestData = $data;
    }
}
```

### Xử lý đúng cách các singleton

Nếu bạn sử dụng singleton, hãy đảm bảo reset trạng thái của chúng sau mỗi request:

```php
// Trong AppServiceProvider hoặc một ServiceProvider tùy chỉnh
$this->app->singleton('your-service', function ($app) {
    return new YourService();
});

// Đăng ký event listener để reset sau mỗi request
$this->app['events']->listen(\Laravel\Octane\Events\RequestTerminated::class, function () {
    $this->app->make('your-service')->reset();
});
```

## Kiểm tra tương thích

Để kiểm tra ứng dụng của bạn có tương thích với Laravel Octane hay không, hãy thực hiện các bước sau:

1. Chạy ứng dụng với Laravel Octane: `php artisan octane:start`
2. Thực hiện nhiều request đến cùng một endpoint và kiểm tra xem dữ liệu có bị rò rỉ giữa các request không
3. Kiểm tra các lỗi trong logs

## Hỗ trợ

Nếu bạn gặp vấn đề khi sử dụng Steak với Laravel Octane, vui lòng tạo issue trên GitHub hoặc liên hệ support@Steak.dev. 