# Steak Core

[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue.svg)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/Laravel-11.x%20%7C%2012.x-green.svg)](https://laravel.com)
[![Laravel Octane](https://img.shields.io/badge/Octane-2.x-orange.svg)](https://laravel.com/docs/octane)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

**Steak Core** - Thư viện Laravel Core cho phát triển nhanh và bảo mật, được thiết kế để tương thích hoàn toàn với Laravel 11, Laravel 12 và Laravel Octane.

## 🚀 Tính năng chính

### 🔧 Core Engines
- **ShortCode Engine**: Hệ thống shortcode mạnh mẽ tương tự WordPress
- **View Manager**: Quản lý view và template với cache thông minh
- **Cache Engine**: Hệ thống cache đa lớp với auto-invalidation
- **DCrypt Engine**: Mã hóa/giải mã dữ liệu an toàn
- **JSON Data Engine**: Xử lý dữ liệu JSON hiệu quả

### 🎯 Magic Classes
- **Arr**: Array wrapper với magic methods và helper functions
- **Str**: String utilities với hỗ trợ tiếng Việt
- **Any**: Universal data wrapper cho mọi kiểu dữ liệu

### 🗂️ File Management
- **Filemanager**: Quản lý file và thư mục toàn diện
- **File Methods**: Các phương thức xử lý file nâng cao
- **Zip Methods**: Nén và giải nén file
- **File Converter**: Chuyển đổi định dạng file

### 🌐 HTTP & API
- **HTTP Client**: HTTP client với Promise support
- **CURL Wrapper**: CURL utilities nâng cao
- **Base API**: Framework cho API development
- **HTTP Promise**: Promise-based HTTP requests

### 🎨 HTML & UI
- **HTML Builder**: Tạo HTML elements programmatically
- **Form Builder**: Form generation với validation
- **Menu Builder**: Menu system linh hoạt
- **Input Types**: Input components đa dạng

### 📊 Repository Pattern
- **Base Repository**: Repository pattern implementation
- **CRUD Actions**: CRUD operations tự động
- **Filter Actions**: Advanced filtering và searching
- **Cache Tasks**: Cache management cho repositories

### 🔐 Security & Validation
- **Validators**: Validation system mở rộng
- **Default Methods**: Security utilities
- **System Mail Alert**: Email security alerts

### 🎯 Event System
- **EventMethods**: Hệ thống quản lý sự kiện mạnh mẽ
- **Event Dispatcher**: Event dispatching và handling
- **Event Methods**: Magic methods cho event management
- **Multi-listener Support**: Một event có thể có nhiều listeners
- **Class-based Isolation**: Mỗi class có vùng events riêng biệt

### 🌍 Internationalization
- **Locale Management**: Multi-language support
- **Language Files**: Dynamic language loading

## 📋 Yêu cầu hệ thống

- **PHP**: ^8.1
- **Laravel**: ^11.0 | ^12.0
- **Laravel Octane**: ^2.0 (tùy chọn)

## 🛠️ Cài đặt

### 1. Cài đặt qua Composer

```bash
composer require steak/core
```

### 2. Đăng ký Service Provider

Service Provider sẽ được tự động đăng ký thông qua Laravel's auto-discovery.

### 3. Publish Configuration và Migrations (tùy chọn)

```bash
# Publish config file
php artisan vendor:publish --provider="Steak\Core\Providers\SteakServiceProvider" --tag="steak-config"

# Publish migrations
php artisan vendor:publish --provider="Steak\Core\Providers\SteakServiceProvider" --tag="steak-migrations"

# Hoặc publish tất cả
php artisan vendor:publish --provider="Steak\Core\Providers\SteakServiceProvider"
```

### 4. Chạy Migrations

```bash
# Chạy migrations của thư viện
php artisan migrate

# Hoặc sử dụng command riêng
php artisan steak:publish-migrations
php artisan migrate
```

## 🚀 Sử dụng nhanh

### ShortCode Engine

```php
use Steak\Core\Engines\ShortCode;

// Đăng ký shortcode
ShortCode::addShortcode('hello', function($atts, $content, $tag) {
    return '<h2>Xin chào từ shortcode!</h2>';
});

// Sử dụng trong nội dung
$content = "Đây là nội dung. [hello] Và đây là nội dung sau.";
$result = ShortCode::do($content, false);
```

### Magic Array

```php
use Steak\Core\Magic\Arr;

$data = new Arr(['name' => 'John', 'age' => 30]);

// Magic methods
echo $data->name; // John
echo $data->get('age'); // 30
echo $data->has('email'); // false

// Array operations
$data->set('email', 'john@example.com');
$data->remove('age');
```

### File Management

```php
use Steak\Core\Files\Filemanager;

$fm = new Filemanager('/path/to/directory');

// File operations
$fm->saveHtml('index.html', '<h1>Hello World</h1>');
$content = $fm->getHtml('index.html');

// Directory operations
$files = $fm->getList();
$fm->copy('source.txt', 'destination.txt');
```

### HTTP Client

```php
use Steak\Core\Http\Client;

$client = new Client();

// GET request
$response = $client->get('https://api.example.com/users');

// POST request với data
$response = $client->post('https://api.example.com/users', [
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);
```

### Repository Pattern

```php
use Steak\Core\Repositories\BaseRepository;

class UserRepository extends BaseRepository
{
    protected $model = User::class;
    
    public function findByEmail($email)
    {
        return $this->model::where('email', $email)->first();
    }
}
```

### Event System

```php
use Steak\Core\Events\EventMethods;

class UserService
{
    use EventMethods;
    
    public function createUser($data)
    {
        // Đăng ký event listeners
        static::on('user.creating', function($userData) {
            // Validate trước khi tạo
            return $userData;
        });
        
        static::on('user.created', function($user) {
            // Gửi email chào mừng
            Mail::to($user->email)->send(new WelcomeMail($user));
        });
        
        // Kích hoạt event trước khi tạo
        $data = static::trigger('user.creating', $data);
        
        $user = User::create($data);
        
        // Kích hoạt event sau khi tạo
        static::trigger('user.created', $user);
        
        return $user;
    }
}

// Sử dụng từ bên ngoài
UserService::on('user.created', function($user) {
    // Log activity
    Activity::log('user_created', $user);
});

// Kiểm tra event có tồn tại
if (UserService::hasEvent('user.created')) {
    // Event đã được đăng ký
}

### Event System - Chi tiết kỹ thuật

EventMethods trait cung cấp hệ thống quản lý sự kiện hoàn chỉnh với các tính năng:

#### **Cấu trúc dữ liệu**
- **Class-based Isolation**: Mỗi class có vùng events riêng biệt
- **Multi-listener Support**: Một event có thể có nhiều listeners
- **Case-insensitive**: Event names tự động chuyển về lowercase

#### **Các phương thức chính**
- `_addEventListener()`: Đăng ký listener cho event
- `_dispatchEvent()`: Kích hoạt event và thực thi listeners
- `_removeEvent()`: Xóa event listeners
- `_eventExists()`: Kiểm tra event có tồn tại
- `callEventMethod()`: Router chính để gọi các method

#### **Magic Methods Support**
Trait được thiết kế để hoạt động với magic methods:
- `__callStatic()`: Xử lý static method calls
- `__call()`: Xử lý instance method calls

#### **Return Values**
- `_dispatchEvent()` trả về mảng kết quả từ tất cả listeners
- Các method khác trả về boolean hoặc void tùy theo chức năng

#### **Ví dụ sử dụng thực tế**

```php
class OrderService
{
    use EventMethods;
    
    public function processOrder($orderData)
    {
        // Đăng ký các event listeners
        static::on('order.validating', function($data) {
            // Validate order data
            if (empty($data['items'])) {
                throw new Exception('Order must have items');
            }
            return $data;
        });
        
        static::on('order.processing', function($order) {
            // Update inventory
            foreach ($order->items as $item) {
                Inventory::decrease($item->product_id, $item->quantity);
            }
        });
        
        static::on('order.completed', function($order) {
            // Send confirmation email
            Mail::to($order->customer_email)->send(new OrderConfirmation($order));
            
            // Log activity
            Activity::log('order_completed', $order);
        });
        
        // Kích hoạt validation event
        $orderData = static::trigger('order.validating', $orderData);
        
        // Tạo order
        $order = Order::create($orderData);
        
        // Kích hoạt processing event
        static::trigger('order.processing', $order);
        
        // Cập nhật trạng thái
        $order->update(['status' => 'completed']);
        
        // Kích hoạt completion event
        static::trigger('order.completed', $order);
        
        return $order;
    }
}
```

## 🔧 Laravel Octane Support

Steak Core được thiết kế để tương thích hoàn toàn với Laravel Octane:

### Tự động State Management

```php
use Steak\Core\Contracts\OctaneCompatible;

class MyService implements OctaneCompatible
{
    private static $cache = [];
    
    public static function resetStaticState(): void
    {
        self::$cache = [];
    }
    
    public function resetInstanceState(): void
    {
        // Reset instance state
    }
    
    public static function getStaticProperties(): array
    {
        return ['cache'];
    }
}
```

### Octane Events

- **WorkerStarting**: Khởi tạo worker
- **RequestReceived**: Xử lý request mới
- **RequestTerminated**: Reset state sau request

## 📚 API Documentation

### ShortCode API

| Method | Description |
|--------|-------------|
| `ShortCode::addShortcode($tag, $callback)` | Đăng ký shortcode mới |
| `ShortCode::do($content, $ignore_html)` | Xử lý nội dung có shortcode |
| `ShortCode::hasShortcode($content, $tag)` | Kiểm tra shortcode có tồn tại |
| `ShortCode::removeShortcode($tag)` | Xóa shortcode |

### Arr API

| Method | Description |
|--------|-------------|
| `$arr->get($key, $default)` | Lấy giá trị theo key |
| `$arr->set($key, $value)` | Gán giá trị |
| `$arr->has($key)` | Kiểm tra key có tồn tại |
| `$arr->remove($key)` | Xóa key |
| `$arr->merge($array)` | Merge với array khác |

### Filemanager API

| Method | Description |
|--------|-------------|
| `$fm->saveHtml($filename, $content)` | Lưu file HTML |
| `$fm->getHtml($filename)` | Đọc file HTML |
| `$fm->copy($src, $dst)` | Copy file/thư mục |
| `$fm->move($src, $dst)` | Di chuyển file/thư mục |
| `$fm->delete($path)` | Xóa file/thư mục |

### Event System API

| Method | Description |
|--------|-------------|
| `static::on($event, $closure)` | Đăng ký event listener |
| `static::addEventListener($event, $closure)` | Đăng ký event listener (alias) |
| `static::trigger($event, ...$params)` | Kích hoạt event |
| `static::fire($event, ...$params)` | Kích hoạt event (alias) |
| `static::emit($event, ...$params)` | Kích hoạt event (alias) |
| `static::hasEvent($event)` | Kiểm tra event có tồn tại |
| `static::eventExists($event)` | Kiểm tra event có tồn tại (alias) |
| `static::hasEventListener($event)` | Kiểm tra event có tồn tại (alias) |
| `static::removeEvent($event, $closure)` | Xóa event listener |
| `static::off($event, $closure)` | Xóa event listener (alias) |
| `static::removeEventListener($event, $closure)` | Xóa event listener (alias) |

## 🧪 Testing

### Chạy tests

```bash
composer test
```

### Octane Compatibility Tests

```bash
php artisan test --filter=OctaneCompatibilityTest
```

## 🔒 Security

- Tất cả input được sanitize tự động
- SQL injection protection
- XSS protection
- CSRF protection
- Secure file operations

## 🌍 Internationalization

```php
use Steak\Core\Languages\Locale;

// Set language
Locale::setLang('vi');

// Get translation
$message = Locale::get('welcome.message');
```

## 📦 Package Structure

```
src/
├── core/
│   ├── Async/            # Async/await utilities
│   ├── Concerns/         # Traits và shared functionality
│   ├── Contracts/        # Interfaces và contracts
│   ├── Console/          # Console commands
│   ├── Crawlers/         # Web crawling utilities
│   ├── Database/         # Database utilities
│   ├── Engines/          # Core engines (ShortCode, Cache, etc.)
│   ├── Events/           # Event system (EventMethods, EventDispatcher)
│   ├── Files/            # File management system
│   ├── Html/             # HTML builders và components
│   ├── Http/             # HTTP client và utilities
│   ├── Languages/        # Internationalization
│   ├── Laravel/          # Laravel integrations
│   ├── Magic/            # Magic classes (Arr, Str, Any)
│   ├── Mailer/           # Email system
│   ├── Masks/            # Data masking và transformation
│   ├── Models/           # Base models
│   ├── Promise/          # Promise utilities
│   ├── Providers/        # Service providers
│   ├── Queues/           # Queue management
│   ├── Repositories/     # Repository pattern implementation
│   ├── Services/         # Service classes
│   ├── System/           # System utilities
│   └── Validators/       # Validation system
├── helpers/              # Helper functions
└── tests/                # Test files
```

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

- **Documentation**: [https://steak.dev/docs](https://steak.dev/docs)
- **Issues**: [GitHub Issues](https://github.com/steak/core/issues)
- **Discussions**: [GitHub Discussions](https://github.com/steak/core/discussions)
- **Email**: support@steak.dev

## 🏆 Credits

Developed with ❤️ by the Steak Team

---

**Steak Core** - Empowering Laravel development with powerful tools and utilities.
