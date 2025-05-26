<?php

namespace Steak\Services;

class ViewService extends Service
{
    protected $module = '';
    protected $viewFolder = '';
    protected $data = [];
    protected $viewMode = 'direct'; // template, theme, direct, file
    protected $cache = false;
    protected $cacheTime = 0;
    protected $cacheKey = '';
    protected $scope = 'global'; // global, admin, web, account
    public function __construct()
    {
        parent::__construct();
        $this->cacheKey = md5(static::class);
    }

    public function render($view, $data = [])
    {
        return view($view, $data);
    }
}