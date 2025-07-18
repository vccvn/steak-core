# Steak Core Database Migrations

Thư mục này chứa các migration cần thiết cho thư viện Steak Core.

## 📋 Danh sách Migrations

### 1. `steak_settings` - Bảng cài đặt hệ thống
- **Mục đích**: Lưu trữ các cài đặt cấu hình của hệ thống
- **Các trường chính**:
  - `key`: Khóa cài đặt (unique)
  - `value`: Giá trị cài đặt
  - `type`: Kiểu dữ liệu (string, json, boolean, integer)
  - `group`: Nhóm cài đặt
  - `is_public`: Có thể truy cập công khai hay không

### 2. `steak_shortcodes` - Bảng quản lý shortcode
- **Mục đích**: Lưu trữ thông tin về các shortcode đã đăng ký
- **Các trường chính**:
  - `tag`: Tên shortcode (unique)
  - `name`: Tên hiển thị
  - `callback`: Callback function (serialized)
  - `attributes`: Thuộc tính mặc định (JSON)
  - `category`: Danh mục shortcode
  - `is_active`: Trạng thái hoạt động

### 3. `steak_cache_tasks` - Bảng quản lý cache
- **Mục đích**: Lưu trữ thông tin cache và quản lý TTL
- **Các trường chính**:
  - `key`: Khóa cache (unique)
  - `value`: Giá trị cache
  - `type`: Kiểu cache (data, html, json, file)
  - `driver`: Driver cache (file, redis, database)
  - `ttl`: Thời gian sống (giây)
  - `expires_at`: Thời gian hết hạn
  - `tags`: Cache tags (JSON)

### 4. `steak_file_logs` - Bảng log file operations
- **Mục đích**: Ghi log các thao tác với file
- **Các trường chính**:
  - `path`: Đường dẫn file
  - `filename`: Tên file
  - `action`: Hành động (create, update, delete, move, copy)
  - `user_id`: ID người dùng thực hiện
  - `metadata`: Metadata bổ sung (JSON)

## 🚀 Cách sử dụng

### 1. Chạy migrations

```bash
# Chạy tất cả migrations của thư viện
php artisan migrate

# Chạy migration cụ thể
php artisan migrate --path=vendor/steak/core/database/migrations

# Rollback migrations
php artisan migrate:rollback --path=vendor/steak/core/database/migrations
```

### 2. Publish migrations (tùy chọn)

```bash
# Publish migrations vào ứng dụng
php artisan vendor:publish --provider="Steak\Core\Providers\SteakServiceProvider" --tag="migrations"
```

### 3. Tạo migration mới

```bash
# Tạo migration mới trong thư viện
php artisan make:migration create_steak_new_table --path=database/migrations
```

## 🔧 Cấu hình

### Prefix bảng
Mặc định các bảng sẽ có prefix `steak_`. Bạn có thể thay đổi trong config:

```php
// config/steak.php
return [
    'table_prefix' => 'steak_',
    // ...
];
```

### Connection database
Mặc định sử dụng connection mặc định. Có thể cấu hình riêng:

```php
// config/steak.php
return [
    'database' => [
        'connection' => 'steak',
        'prefix' => 'steak_',
    ],
];
```

## 📊 Seeding

Tạo seeder cho dữ liệu mẫu:

```bash
php artisan make:seeder SteakSettingsSeeder
```

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SteakSettingsSeeder extends Seeder
{
    public function run()
    {
        DB::table('steak_settings')->insert([
            [
                'key' => 'app_name',
                'value' => 'Steak Application',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Tên ứng dụng',
                'is_public' => true,
            ],
            [
                'key' => 'cache_driver',
                'value' => 'file',
                'type' => 'string',
                'group' => 'cache',
                'description' => 'Driver cache mặc định',
                'is_public' => false,
            ],
        ]);
    }
}
```

## 🔒 Bảo mật

- Tất cả migrations đều sử dụng prepared statements
- Các trường nhạy cảm được mã hóa
- Logging đầy đủ cho audit trail
- Index tối ưu cho performance

## 📈 Performance

- Sử dụng index cho các trường thường query
- Composite index cho các trường liên quan
- Partitioning cho bảng lớn (nếu cần)
- Soft deletes để tránh mất dữ liệu

## 🧪 Testing

```bash
# Test migrations
php artisan test --filter=MigrationTest

# Test với database riêng
php artisan test --env=testing
``` 