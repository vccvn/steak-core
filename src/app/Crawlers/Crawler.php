<?php

namespace Steak\Crawlers;


class Crawler
{
    use Crawl;

    
     /**
     * chay lai thiet lap
     */
    public function __construct()
    {
        if(method_exists($this, 'init')){
            $this->init();
        }
    }

    public function __call($name, $arguments)
    {
        
    }
}