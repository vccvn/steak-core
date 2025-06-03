<?php

namespace Steak\Http;

use Psr\Http\Message\ResponseInterface;
use Steak\Promise\Promise;

class HttpPromise extends Promise{
    public function __construct($callback)
    {
        parent::__construct($callback, true);
    }

    
    /**
     * await promise
     * @return mixed
     */
    public function await()
    {
        if ($this->_state === self::STATE_PENDING) {
            $this->__start__();
        } 
        if ($this->_state === self::STATE_REJECTED && !$this->_catchCallbackCalled) {
            $this->__catch($this->_error);
        } 
        $value = $this->_value;
        if($value instanceof ResponseInterface){
            return $value->getBody()->getContents();
        }
        return $value;
    }
}