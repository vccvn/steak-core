<?php

namespace Steak\App\Promise;

use Exception;

/**
 * Promise class
 * @package Steak\App\Promise
 * @author Steak <steak@steak.vn>
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 * @copyright (c) 2025 Steak
 * 
 * @method mixed run() Dùng để chạy promise
 * @method mixed end() Kết thúc promise
 * @method mixed start() Bắt đầu promise
 * @method mixed done() Kết thúc promise
 */
class Promise
{
    protected $_state = 'pending'; // pending, success, fulfilled, rejected

    protected $_value = null;
    protected $_reason = null;
    protected $_thenCallbacks = [];
    protected $_catchCallback = null;
    protected $_finallyCallback = null;
    /**
     * @var callable<(function(function(mixed):void,function(mixed):void):void)>
     */
    protected $_callback = null;
    protected $_error = null;

    /**
     * @param callable<(function(function(mixed):void,function(mixed):void):void)> $callback
     */
    public function __construct($callback)
    {
        if (is_callable($callback)) {
            $this->_callback = $callback;
        } elseif (is_array($callback)) {
            $this->_callback = $callback['handle'] ?? ($callback['action'] ?? ($callback['call'] ?? ($callback['callable'] ?? null)));
            if (array_key_exists('success', $callback)) {
                $this->then($callback['success']);
            }
            if (array_key_exists('fail', $callback)) {
                $this->catch($callback['fail']);
            }
            if (array_key_exists('error', $callback)) {
                $this->catch($callback['error']);
            }
            if (array_key_exists('finally', $callback)) {
                $this->finally($callback['finally']);
            }
        } else {
        }
    }
    /**
     * @param callable<(function(mixed):mixed):mixed> $callback
     */
    public function then($callback)
    {
        if (!is_callable($callback)) {
            return $this;
        }
        if ($this->_state === 'pending') {
            $this->_thenCallbacks[] = $callback;
        } elseif ($this->_state === 'fulfilled') {
            try {
                $this->_value = call_user_func_array($callback, [$this->_value]);
            } catch (\Exception $e) {
                $this->_error = $e;
                $this->_state = 'rejected';
            }
        }
        return $this;
    }

    /**
     * @param callable<(function(mixed):mixed):mixed> $callback
     */
    public function catch(callable $callback)
    {
        if ($this->_state === 'pending') {
            $this->_catchCallback = $callback;
        } elseif ($this->_state === 'rejected' && !$this->_catchCallback) {
            $this->_catchCallback = $callback;
            try {
                $this->_value = call_user_func_array($callback, [$this->_error]);
            } catch (\Exception $e) {
                $this->_error = $e;
                $this->_state = 'rejected';
            }
        }
        return $this;
    }

    /**
     * @param callable<(function(mixed):mixed):mixed> $callback
     */
    public function finally(callable $callback)
    {
        $this->_finallyCallback = $callback;
        return $this;
    }

    public function __start()
    {
        if ($this->_state !== 'pending') {
            return $this;
        }
        $callback = $this->_callback;
        try {
            if (is_callable($callback)) {
                call_user_func_array($callback, [function ($value) {
                    if ($value instanceof Promise) {
                        if ($value->state === 'pending' || $value->state === 'success') {
                            $value->__start();
                        }
                        if ($value->state === 'fulfilled') {
                            $value = $value->value();
                        } elseif ($value->state === 'rejected') {
                            $this->_value = $value->value();
                            $error = $value->error();
                            throw $error;
                        }
                    }
                    $this->_value = $value;
                    $this->_state = 'success';
                }, function ($reason) {
                    $this->_reason = $reason;
                    $this->_state = 'rejected';
                    throw new \Exception($reason);
                }]);
                if ($this->_state === 'success') {
                    $this->__resolve();
                }
            } else {
                $this->_state = 'rejected';
                throw new \Exception('Callback is not a callable');
            }
        } catch (\Exception $e) {
            $this->_error = $e;
            $this->_state = 'rejected';
            $this->__catch($e);
        }
        if ($this->_finallyCallback && is_callable($finallyCallback = $this->_finallyCallback)) {
            call_user_func_array($finallyCallback, []);
        }
    }

    protected function __resolve($value = null)
    {
        if ($value !== null) {
            $this->_value = $value;
        }
        try {
            $this->_state = 'fulfilled';
            foreach ($this->_thenCallbacks as $callback) {
                $value = call_user_func_array($callback, [$this->_value]);
                if ($value instanceof Promise) {
                    if ($value->state === 'pending' || $value->state === 'success') {
                        $value->__start();
                    }
                    if ($value->state === 'fulfilled') {
                        $value = $value->value();
                    } elseif ($value->state === 'rejected') {
                        $error = $value->error();
                        $this->_value = $value->value();
                        throw $error;
                    } else {
                        throw new \Exception('Promise is not in a valid state');
                    }
                }
                $this->_value = $value;
            }
            $this->_state = 'fulfilled';
        } catch (\Exception $e) {
            $this->_error = $e;
            $this->_state = 'rejected';
            throw $e;
        }
    }

    protected function __catch(\Exception $error)
    {
        $this->_state = 'rejected';
        $catchCallback = $this->_catchCallback;
        if ($catchCallback && is_callable($catchCallback)) {
            $value = call_user_func_array($catchCallback, [$error]);
            if ($value instanceof Promise) {
                $value->__start();
                if ($value->state === 'fulfilled') {
                    $value = $value->value();
                } elseif ($value->state === 'rejected') {
                    $this->_value = $value->value();
                    return $this;
                }
                $this->_value = $value;
            }
        } else {
            throw $error;
        }
        return $this;
    }

    public function value()
    {
        $this->__start();
        return $this->_value;
    }

    public function commit()
    {
        $this->__start();
        return $this;
    }

    /**
     * lấy exception
     *
     * @return \Exception
     */
    public function error()
    {
        $this->__start();
        return $this->_error;
    }

    public function __get($name)
    {
        if ($name === 'value' || $name === 'result') {
            return $this->value();
        }
        if ($name === 'state' || $name === 'status') {
            return $this->_state;
        }
        if ($name === 'error') {
            return $this->_error;
        }
        if ($name === 'reason') {
            return $this->_reason;
        }
        return null;
    }

    public function __call($name, $arguments)
    {
        if ($name === 'run' || $name === 'end' || $name === 'start' || $name === 'done' || $name === 'result' || $name === 'val') {
            return $this->value();
        }
        return $this;
    }

    public function __invoke()
    {
        return $this->value();
    }

    public static function all(array $promises)
    {
        return new Promise(function ($resolve, $reject) use ($promises) {
            $values = [];
            try {
                $r = function ($err) {
                    throw $err;
                };
                foreach ($promises as $promise) {
                    $promise->then(function ($value) use (&$values) {
                        $values[] = $value;
                    })->catch(function ($error) use ($r) {
                        $r($error);
                    });
                }
                $resolve($values);
            } catch (\Exception $e) {
                $reject($e);
            }
        });
    }

    public static function resolve($value)
    {
        return new Promise(function ($resolve, $reject) use ($value) {
            $resolve($value);
        });
    }


    public function __toString()
    {
        return $this->value();
    }
}
