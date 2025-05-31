<?php

namespace Steak\Http;

use Psr\Http\Message\ResponseInterface;

/**
 * doi tuong quan li request toi api
 * @method ResponseInterface|string|array get(string $url, array $query = [], array $headers = [])
 * @method ResponseInterface|string|array post(string $url, array $data = [], array $headers = [])
 * @method ResponseInterface|string|array put(string $url, array $data = [], array $headers = [])
 * @method ResponseInterface|string|array path(string $url, array $data = [], array $headers = [])
 * @method ResponseInterface|string|array delete(string $url, array $data = [], array $headers = [])
 * @method ResponseInterface|string|array options(string $url, array $data = [], array $headers = [])
 * 
 * @method static ResponseInterface|string|array get(string $url, array $query = [], array $headers = [])
 * @method static ResponseInterface|string|array post(string $url, array $data = [], array $headers = [])
 * @method static ResponseInterface|string|array put(string $url, array $data = [], array $headers = [])
 * @method static ResponseInterface|string|array path(string $url, array $data = [], array $headers = [])
 * @method static ResponseInterface|string|array delete(string $url, array $data = [], array $headers = [])
 * @method static ResponseInterface|string|array options(string $url, array $data = [], array $headers = [])
 * 
 */
class Client extends BaseApi{
    // test
    protected static $instance;
    public function __call($name, $arguments)
    {
        $method = strtoupper($name);
        return $this->send($method, ...$arguments);
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public static function __callStatic($name, $arguments)
    {
        return self::getInstance()->$name(...$arguments);
    }
}