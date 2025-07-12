<?php

namespace Steak\Core\Repositories;

use Illuminate\Database\Eloquent\Model;
use Exception;
use Steak\Core\Concerns\MagicMethods;
use Steak\Core\Events\EventMethods;
use Steak\Core\Repositories\BaseQuery;
use Steak\Core\Repositories\CRUDAction;

abstract class BaseRepository
{
    use BaseQuery, GettingAction, CRUDAction, FilterAction, DataAction, OwnerAction, CacheAction, FileAction, EventMethods, FilterAction, MagicMethods;
    // tự động kiểm tra owner
    protected $checkOwner = true;

    protected $_primaryKeyName = MODEL_PRIMARY_KEY;
    /**
     * @var Model|SQLModel|MongoModel
     */
    protected $_model;

    protected $modelType = 'default';

    protected $model = null;

    public function __construct()
    {
        if ($this->model && class_exists($this->model)) {
            $this->model = app($this->model);
        } elseif (method_exists($this, 'getModel')) {
            $this->setModel();
        }
        if ($this->_model) {
            $this->_primaryKeyName = $this->_model->getKeyName();
            // $this->ownerInit();
            if ($this->required == MODEL_PRIMARY_KEY && $this->_primaryKeyName) {
                $this->required = $this->_primaryKeyName;
            }
            $this->modelType = $this->_model->__getModelType__();

            $this->ownerInit();
            $this->init();
            if (!$this->defaultValues) {
                $this->defaultValues = $this->_model->getDefaultValues();
            }
        }
    }


    public function getKeyName()
    {
        return $this->_primaryKeyName;
    }


    /**
     * chạy các lệnh thiết lập
     */
    protected function init()
    {
    }
    /**
     * tạo một repository mới
     *
     * @return $this
     */
    public function mewRepo()
    {
        return new static();
    }

    /**
     * kiểm tra tồn tại
     *
     * @param string|int|float ...$args
     * @return bool
     */
    final public function exists(...$args)
    {
        $t = count($args);
        if ($t >= 2) {
            return $this->countBy(...$args) ? true : false;
        } elseif ($t == 1) {
            return $this->countBy($this->_primaryKeyName, $args[0]) ? true : false;
        }
        return false;
    }
    public static function checkExists($id)
    {
        return app(static::class)->exists($id);
    }



    /**
     * gọi hàm không dược khai báo từ trước
     *
     * @param string $method
     * @param array $params
     * @return static
     */
    public function __call($method, $params)
    {
        $f = array_key_exists($key = strtolower($method), $this->sqlclause) ? $this->sqlclause[$key] : null;
        if ($f) {
            if (!isset($this->actions) || !is_array($this->actions)) {
                $this->actions = [];
            }
            if ($f == 'groupby') {
                if (count($params) == 1 && is_string($params[0])) {
                    $params = array_map('trim', explode(',', $params[0]));
                }
                foreach ($params as $column) {
                    $this->actions[] = [
                        'method' => $method,
                        'params' => [$column]
                    ];
                }
            } else {
                $this->actions[] = compact('method', 'params');
            }

        } elseif (count($params)) {
            $value = $params[0];
            $fields = array_merge([$this->required], $this->getFields());

            // lấy theo tham số request (set where)
            if ($this->whereable && is_array($this->whereable) && (isset($this->whereable[$key]) || in_array($key, $this->whereable))) {
                if (isset($this->whereable[$key])) {
                    $this->where($this->whereable[$key], $value);
                } else {
                    $this->where($key, $value);
                }
            }
            // elseif($this->searchable && is_array($this->searchable) && (isset($this->searchable[$f]) || in_array($f, $this->searchable))){
            //     if(isset($this->searchable[$f])){
            //         $this->where($this->searchable[$f], $value);
            //     }else{
            //         $this->where($f, $value);
            //     }
            // }
            elseif (in_array($key, $fields)) {
                $this->where($key, $value);
                
            }
            elseif(in_array($method, static::$eventMethods)){
                static::callEventMethod($method, $params);
            }
            elseif($this->_funcExists($method)){
                $this->_nonStaticCall($method, $params);
            }
            elseif (substr($method, 0, 2) == 'on' && strlen($event = substr($method, 2)) > 0 && ctype_upper(substr($event, 0, 1)) && count($params) && (is_callable($params[0]) || is_callable([$this, $params[0]]))) {
                $this->_addEventListener($event, $params[0]);
            }
            elseif (substr($method, 0, 4) == 'emit') {
                if(strlen($event = substr($method, 4)) > 0 && ctype_upper(substr($event, 0, 1))){
                    return static::_dispatchEvent($event, ...$params);
                }else{
                    return static::_dispatchEvent(array_shift($params), ...$params);
                }
            }
        }elseif($this->_funcExists($method)){
            $this->_nonStaticCall($method, $params);
        }
        elseif(substr($method, 0, 4) == 'emit' && strlen($event = substr($method, 4)) > 0 && (ctype_upper(substr($event, 0, 1)))){
            return static::_dispatchEvent($event, ...$params);
        }
        return $this;
    }
    /**
     * Handle calls to missing methods on the controller.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public static function __callStatic($method, $parameters)
    {
        if(in_array($method, static::$eventMethods)){
            return static::_staticCall($method, $parameters);
        }
        return static::_staticCall($method, $parameters);
    }

}

BaseRepository::globalStaticFunc('on', '_addEventListener');
BaseRepository::globalFunc('on', 'addEventListener');

