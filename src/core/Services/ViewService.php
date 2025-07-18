<?php

namespace Steak\Core\Services;


class ViewService extends Service
{
    use Methods\ViewMethods;
    public function __construct()
    {
        parent::__construct();
        $this->viewInit();
        $this->cacheKey = md5(static::class);
    }
}
