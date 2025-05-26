<?php
namespace Steak\Engines;

use Steak\Repositories\Html\AreaRepository;
use Steak\Web\HtmlAreaList;
use Steak\Web\Options;
use Steak\Files\Filemanager;
use Steak\Helpers\Arr;

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
