<?php
namespace Steak\Core\Engines;

use Steak\Core\Repositories\Html\AreaRepository;
use Steak\Core\Repositories\Html\HtmlAreaList;
use Steak\Core\Repositories\Html\Options;
use Steak\Core\Files\Filemanager;
use Steak\Core\Helpers\Arr;

class ViewDataEngine
{
    static $shared = false;

    
    public static function share($name = null, $value=null)
    {
        if(static::$shared) return true;;
        $a = $name?(is_array($name)?$name:(is_string($name)?[$name=>$value]: [])):[];
        view()->share($a);

        static::$shared = true;

        return true;
    }
}
