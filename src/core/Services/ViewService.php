<?php

namespace Steak\Core\Services;

use Steak\Core\Concerns\ViewMethods;

class ViewService extends Service
{
    use ViewMethods;
    public function __construct()
    {
        parent::__construct();
        $this->viewInit();
        $this->cacheKey = md5(static::class);
    }
}
