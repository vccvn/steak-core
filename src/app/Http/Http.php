<?php

namespace Steak\Http;

use Psr\Http\Message\ResponseInterface;
use Steak\Promise\Promise;

/**
 * doi tuong quan li request toi api
 * @method HttpPromise<ResponseInterface|string|array> get(string $url, array $query = [], array $headers = [])
 * @method HttpPromise<ResponseInterface|string|array> post(string $url, array $data = [], array $headers = [])
 * @method HttpPromise<ResponseInterface|string|array> put(string $url, array $data = [], array $headers = [])
 * @method HttpPromise<ResponseInterface|string|array> path(string $url, array $data = [], array $headers = [])
 * @method HttpPromise<ResponseInterface|string|array> delete(string $url, array $data = [], array $headers = [])
 * @method HttpPromise<ResponseInterface|string|array> options(string $url, array $data = [], array $headers = [])
 * 
 * @method static HttpPromise<ResponseInterface|string|array> get(string $url, array $query = [], array $headers = []) Lấy dữ liệu từ api bằng phương thức GET
 * @method static HttpPromise<ResponseInterface|string|array> post(string $url, array $data = [], array $headers = []) Gửi dữ liệu từ api bằng phương thức POST
 * @method static HttpPromise<ResponseInterface|string|array> put(string $url, array $data = [], array $headers = []) Gửi dữ liệu từ api bằng phương thức PUT
 * @method static HttpPromise<ResponseInterface|string|array> path(string $url, array $data = [], array $headers = []) Gửi dữ liệu từ api bằng phương thức PATCH
 * @method static HttpPromise<ResponseInterface|string|array> delete(string $url, array $data = [], array $headers = []) Gửi dữ liệu từ api bằng phương thức DELETE
 * @method static HttpPromise<ResponseInterface|string|array> options(string $url, array $data = [], array $headers = []) Gửi dữ liệu từ api bằng phương thức OPTIONS
 * 
 */
class Http extends BaseApi{
    // test
    protected static $returnType = '';
    protected static $instance;
    public function __call($name, $arguments)
    {
        $method = strtoupper($name);
        $promise = new HttpPromise(function($resolve, $reject) use ($method, $arguments){
            $result = $this->send($method, ...$arguments);
            if($result){
                $resolve($result);
            }
            elseif($error = $this->getException()){
                $reject($error);
            }else{
                $reject(new \Exception('Promise callback must return a value or an instance of \Exception'));
            }
        });
        return $promise;
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * set return type
     * @param string $type json, array, string, object, ...
     * @return void
     */
    public static function setReturnType($type)
    {
        self::$returnType = $type;
    }

    public static function __callStatic($name, $arguments)
    {
        $instance = self::getInstance();
        if(self::$returnType){
            $instance->setOutput(self::$returnType);
        }
        return $instance->$name(...$arguments);
    }

}
