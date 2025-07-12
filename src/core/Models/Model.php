<?php

namespace Steak\Core\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Steak\Core\Concerns\Uuid;
use Steak\Core\Concerns\ModelEventMethods;
use Steak\Core\Concerns\ModelFileMethods;

/**
 * Class BaseModel
 *
 * Cung cấp các tính năng chung cho tất cả model trong hệ thống.
 */
abstract class Model extends EloquentModel
{
    use SoftDeletes, Uuid, ModelEventMethods, ModelFileMethods;

    /**
     * Khởi động model và gọi các phương thức boot từ các trait.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::bootUuid();
        static::bootModelEventMethods();
        static::bootModelFileMethods();
    }


}
