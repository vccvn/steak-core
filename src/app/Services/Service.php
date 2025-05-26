<?php

namespace Steak\Services;

use Steak\Events\EventMethods;
use Steak\Concerns\MagicMethods;
use Steak\Concerns\ModuleMethods;

class Service
{
    use EventMethods, MagicMethods, ModuleMethods;

    public function __construct()
    {
        $this->moduleInit();
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
        if (count($params)) {
            if(in_array($method, static::$eventMethods)){
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

    public function __get($name)
    {
        return null;
    }

    public function __set($name, $value)
    {
        return null;
    }

    public function __isset($name){
        return false;
    }

    public function __unset($name){
        return null;
    }

}

Service::globalStaticFunc('on', '_addEventListener');
Service::globalFunc('on', 'addEventListener');
