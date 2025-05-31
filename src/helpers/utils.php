<?php

use Steak\App\Promise\Promise;
use Steak\Http\Client;

if (!function_exists('steakPipe')) {
    /**
     * Pipe function
     * @param object<T> $object
     * @param callable<T, T> $callback
     * @return mixed
     */
    function steakPipe($object, $callback, $default = null)
    {
        $isCallable = is_callable($callback);
        if ($isCallable) {
            if ($object) {
                return $callback($object);
            }
        }
        return $default;
    }

    function steakPipeChain($object, $callbacks, $default = null)
    {
        $result = $object;
        foreach ($callbacks as $callback) {
            $result = steakPipe($result, $callback, $default);
        }
        return $result;
    }
}
if (!function_exists('promise')) {
    /**
     * @param callable<(function(function(mixed):void,function(mixed):void):void)> $callback
     * @return Promise
     */
    function promise($callback)
    {
        return new Promise($callback);
    }
}

if (!function_exists('promiseAll')) {
    /**
     * @param array<Promise> $promises
     * @return Promise
     */
    function promiseAll(array $promises)
    {
        return Promise::all($promises);
    }
}

if (!function_exists('steak')) {
    /**
     * Thực thi điều gì đó
     *
     * @param string<class-string>|Promise|mixed $t
     * @return Promise|mixed
     */
    function steak($t)
    {
        if (($t instanceof Promise) || is_a($t, Promise::class)) {
            return $t->value();
        }
        return $t;
    }
}


if (!function_exists('await')) {
    /**
     * @param Promise|callable $promise
     * @param callable<function(Promise):mixed>|null $action action to be called after promise is resolved
     * @return mixed
     */
    function await($promise, $action = null)
    {
        if ($promise instanceof Promise) {
            if (is_callable($action)) {
                $result = call_user_func_array($action, [$promise]);
                if($result !== null){
                    return $result;
                }
            }
            return $promise->value();
        }
        if(is_callable($promise)){
            $promiseObject = new Promise(function($resolve, $reject) use ($promise){
                try{
                    $result = call_user_func_array($promise, []);
                    return $resolve($result);
                }
                catch(\Exception $e){
                    $reject($e);
                }
            });
            if(is_callable($action)){
                $result = call_user_func_array($action, [$promiseObject]);
                if($result instanceof Promise){
                    return $result->value();
                }
                elseif($result !== null){
                    return $result;
                }
            }
            return $promiseObject->value();
        }
        return null;
    }
}

echo $test;