<?php

namespace Steak\Core;

// use App\Models\PermissionModule;
use Steak\Magic\Arr;
use Steak\Laravel\Router;
use Illuminate\Support\Facades\Route;


class ModuleManager
{
    protected static $autoIncrement = 0;
    /**
     * container
     *
     * @var array{[key:string]:Scope}
     */
    protected static $containers = [];

    protected static $map = [];

    /**
     * module
     *
     * @var EmptyObj
     */
    protected static $empty = null;
    protected static $_active = true;

    public static function active()
    {
        self::$_active = true;
    }
    /**
     * Emty
     *
     * @return EmptyObj
     */
    public static function getEmpty(){return static::$empty;}
    public static function isActive()
    {
        if(!static::$empty) static::$empty = app(EmptyObj::class);
        return self::$_active;
    }

    public static function getIncrementID()
    {
        static::$autoIncrement++;
        return static::$autoIncrement;
    }

    public static function addScope($scope, $name = '', $data = [])
    {
        if (!self::isActive()) return static::$empty;
        if (!array_key_exists($scope, static::$containers)) {
            static::$containers[$scope] = new Scope(array_merge([
                'type' => 'scope',
                'slug' => $scope,
                'name' => $name ? $name : $scope,
                'modules' => [],
                'path' => $scope,
                'prefix' => $scope
            ], $data));
        } elseif ($name) {
            static::$containers[$scope]->name = $name;
        }
    }

    /**
     * thêm module
     *
     * @param string $scope
     * @param string $slug
     * @param array $module
     * @return Module
     */
    public static function addModule($scope = 'admin', $slug = '', $module = [])
    {
        if (!self::isActive()) {
            // if($slug == '') dd(static::$empty);
            return static::$empty;

        }
        if (!array_key_exists($scope, static::$containers)) {
            static::addScope($scope);
        }

        if (!static::$containers[$scope]->hasModule($slug)) {
            static::$containers[$scope]->addModule($slug, array_merge([
                'scope' => $scope,
                'type' => 'module'
            ], $module));
        } else {
            static::$containers[$scope]->getModule($slug)->setData($module);
        }

        return static::$containers[$scope]->getModule($slug);
    }

    /**
     * add module by route
     *
     * @param Route $router
     * @param string $description
     * @param string $name
     * @param string $slug
     * @param string $scope
     * @return Module|false
     */
    public static function addModuleByRouter($router = null, $name = '', $description = null, $slug = null, $scope = 'admin')
    {
        if (!self::isActive()) return static::$empty;
        $rd = new Arr([
            'type' => 'module',
            'description' => $description
        ]);

        $a = array_copy(Router::getRouteInfo($router), 'name', 'prefix', 'uri');
        // dump($a);
        $rd->merge($a);
        if ($slug) {
            $rd->slug = $slug;
        }
        if ($rd->name) {
            $rd->path = $rd->name;
            $rd->route = $rd->name;
            $a = explode('.', $rd->name);
            $t = count($a);
            if ($t >= 2) {
                if (!$slug) {
                    $rd->slug = array_pop($a);
                    $slug = $rd->slug;
                }
            }
            if (!$scope) $scope = $a[0];
        } else {
            return false;
        }
        $rd->name = $name ? $name : $rd->slug;
        if (!array_key_exists($scope, static::$containers)) static::addScope($scope);
        if (!static::$containers[$scope]->hasModule($slug)) $moduleRouter = static::addModule($scope, $rd->slug, $rd->all());
        else $moduleRouter = static::$containers[$scope]->getModule($rd->slug);
        return $moduleRouter;
    }
    public static function addSubModule($scope, $slug, $children = [])
    {
        if (!self::isActive()) return static::$empty;
        if (!array_key_exists($scope, static::$containers)) static::addScope($scope);
        if (!static::$containers[$scope]->hasModule($slug)) static::addModule($scope, $slug);
        return static::$containers[$scope]->getModule($slug)->addSubs($children);
    }
    public static function getScope($scope)
    {
        if (!self::isActive()) return static::$empty;
        if (!array_key_exists($scope, static::$containers)) return null;
        return static::$containers[$scope];
    }

    public static function getContainers()
    {
        if (!self::isActive()) return static::$empty;
        return static::$containers;
    }
}

class EmptyObj{
    public function __construct()
    {
        
    }
    public function __get($name)
    {
        return $this;
    }
    public function __set($name, $value)
    {
        
    }
    public function __call($name, $arguments)
    {
        return $this;
    }
    public static function __callStatic($name, $arguments)
    {
        return app(static::class);
    }
}

class Scope extends Arr
{
    /**
     * Undocumented variable
     *
     * @var Module[]
     */
    protected $modules = [];

    public function __construct($data = [])
    {
        $this->setData($data);
    }
    public function setData($data = [])
    {
        $modules = [];
        if (array_key_exists('modules', $data)) {
            $modules = $data['modules'];
            unset($data['modules']);
        }
        if ($modules && is_array($modules)) {
            $this->addModules($modules);
        }

        $this->merge($data);
        return $this;
    }


    public function addModules(array $modules)
    {
        foreach ($modules as $key => $sub) {
            if (is_array($sub)) {
                $this->addModule(is_numeric($key) && array_key_exists('slug', $sub) ? $sub['slug'] : $key, $sub);
            }
        }
    }

    public function addModule($slug, $module)
    {
        if (array_key_exists($slug, $this->modules)) {
            $this->modules[$slug]->setData($module);
        } else {
            $this->modules[$slug] = new Module(array_merge(['type' => 'module', 'slug' => $slug], $module), $this);
        }
        return $this->modules[$slug];
    }


    /**
     * get module
     *
     * @param string $slug
     * @return Module
     */
    public function getModule($slug)
    {
        if (array_key_exists($slug, $this->modules)) {
            return $this->modules[$slug];
        }
        return null;
    }



    /**
     * kiểm tra có module hay ko
     *
     * @param string $slug
     * @return boolean
     */
    public function hasModule($slug)
    {
        return in_array($slug, $this->modules);
    }

    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'modules' => $this->getModulesToArray()
        ]);
    }
    public function getModulesToArray()
    {
        $data = [];
        foreach ($this->modules as $key => $value) {
            $data[$key] = $value->toArray();
        }
        return $data;
    }
}
class Module extends Arr
{

    /**
     * parent
     *
     * @var Module
     */
    protected $parent = null;

    /**
     * sublist
     *
     * @var Module[]
     */
    protected $children = [];

    /**
     * sublist
     *
     * @var Module[]
     */
    protected $actions = [];

    /**
     * sublist
     *
     * @var Module[]
     */
    protected $groups = [];


    /**
     * sublist
     *
     * @var Module[]
     */
    protected $subs = [];


    /**
     * create new instance of Module
     *
     * @param array $module
     */
    public function __construct($module, $parent = null)
    {
        if ($parent) $this->parent = $parent;
        $module = array_merge([
            '_id' => $this->getNewID()
        ], $module);
        $this->setData($module);
    }

    public function setData($module = [])
    {
        $children = [];
        if (array_key_exists('children', $module)) {
            $children = $module['children'];
            unset($module['children']);
        }
        if ($children && is_array($children)) {
            $this->addChildren($children);
        }

        $actions = [];
        if (array_key_exists('actions', $module)) {
            $actions = $module['actions'];
            unset($module['actions']);
        }
        if ($actions && is_array($actions)) {
            $this->addActions($actions);
        }

        $groups = [];
        if (array_key_exists('groups', $module)) {
            $groups = $module['groups'];
            unset($module['groups']);
        }
        if ($groups && is_array($groups)) {
            $this->addGroups($groups);
        }
        $subs = [];
        if (array_key_exists('subs', $module)) {
            $subs = $module['subs'];
            unset($module['subs']);
        }
        if ($subs && is_array($subs)) {
            $this->addSubs($subs);
        }
        $this->merge($module);
        return $this;
    }

    public function addChildren(array $children)
    {
        foreach ($children as $key => $sub) {
            if (is_array($sub)) {
                $this->addChild(is_numeric($key) && array_key_exists('slug', $sub) ? $sub['slug'] : $key, $sub);
            }
        }
        return $this;
    }

    public function addChild($slug, $module)
    {
        if (array_key_exists($slug, $this->children)) {
            $this->children[$slug]->setData($module);
        } else {
            $this->children[$slug] = new static($module, $this);
        }
        return $this->children[$slug];
    }


    public function addActions(array $actions)
    {
        foreach ($actions as $key => $sub) {
            if (is_array($sub)) {
                $this->addAction(is_numeric($key) && array_key_exists('slug', $sub) ? $sub['slug'] : $key, $sub);
            }
        }
        return $this;
    }

    public function addAction($slug, $module)
    {
        $module['type'] = 'action';
        if (array_key_exists($slug, $this->actions)) {
            $this->actions[$slug]->setData($module);
        } else {
            $this->actions[$slug] = new static($module, $this);
        }
        return $this->actions[$slug];
    }


    public function addGroups(array $groups)
    {
        foreach ($groups as $key => $sub) {
            if (is_array($sub)) {
                $slug = is_numeric($key) && array_key_exists('slug', $sub) ? $sub['slug'] : $key;
                $name = array_key_exists('name', $sub) ? $sub['name'] : $slug;
                $this->addGroup($slug, $name, $sub);
            }
        }
        return $this;
    }

    public function addGroup($slug, $name = null, $items = [])
    {
        if (!array_key_exists($slug, $this->groups)) {
            $this->groups[$slug] = new static([
                'type' => 'group',
                'name' => $name ?? $slug
            ]);
        }
        if ($items) {
            if ((array_key_exists('slug', $items) || array_key_exists('name', $items)) && ($a = array_key_exists('actions', $items) | $g = array_key_exists('groups', $items) | $s = array_key_exists('subs', $items) | $c = array_key_exists('children', $items))) {
                if ($a) $this->groups[$slug]->addActions($items['actions']);
                if ($g) $this->groups[$slug]->addGroups($items['groups']);
                if ($s) $this->groups[$slug]->addSubs($items['subs']);
                if ($c) $this->groups[$slug]->addChildren($items['children']);
            }
        }
        return $this->groups[$slug];
    }


    public function addSubs(array $subs)
    {
        foreach ($subs as $key => $sub) {
            if (is_array($sub)) {
                $this->addChild(is_numeric($key) && array_key_exists('slug', $sub) ? $sub['slug'] : $key, $sub);
            }
        }
        return $this;
    }

    public function addSub($slug, $module)
    {
        $module['type'] = 'module';
        if (array_key_exists($slug, $this->subs)) {
            $this->subs[$slug]->setData($module);
        } else {
            $this->subs[$slug] = new static($module, $this);
        }
        return $this->subs[$slug];
    }

    public function addSubByMasterRouter($router, $name = null, $description = null, $slug = null)
    {
        $info = array_copy(Router::getRouteInfo($router), 'name', 'prefix', 'uri');
        $rd = new Arr([
            'type' => 'module',
            'description' => $description
        ]);
        $rd->merge($info);
        if ($slug) {
            $rd->slug = $slug;
        }
        if ($rd->name) {
            $rd->path = $rd->name;
            $rd->route = $rd->name;
            $a = explode('.', $rd->name);
            $t = count($a);
            if ($t >= 2) {
                if (!$slug) {
                    $rd->slug = array_pop($a);
                }
            }
        }
        $rd->name = $name ? $name : $rd->slug;
        return $this->addSub($rd->slug, $rd->all());
    }


    public function addSubByActionRouter($router, $name = null, $description = null, $slug = null)
    {
        $info = array_copy(Router::getRouteInfo($router), 'name', 'prefix', 'uri');
        $rd = new Arr([
            'type' => 'module',
            'description' => $description
        ]);
        $rd->merge($info);
        if ($slug) {
            $rd->slug = $slug;
        }
        if ($rd->name) {
            $rd->path = $rd->name;
            $rd->route = $rd->name;
            $a = explode('.', $rd->name);
            $t = count($a);
            if ($t >= 3) {
                array_pop($a);
                if (!$slug) {
                    $rd->slug = array_pop($a);
                }
            }
        }
        $rd->name = $name ? $name : $rd->slug;
        return $this->addSub($rd->slug, $rd->all());
    }



    public function getChild($slug = null)
    {
        if (!$slug) return $this->children;
        return array_key_exists($slug, $this->children) ? $this->children[$slug] : null;
    }

    public function getGroup($slug = null)
    {
        // if ($this->type != 'module' || !$slug) return null;
        return array_key_exists($slug, $this->children) ? $this->children[$slug] : null;
    }


    public function addActionByRouter($router, $group = null, $name = '', $slug = null)
    {
        // if ($this->type != 'module') return false;
        $info = array_copy(Router::getRouteInfo($router), 'name', 'prefix', 'uri');
        $rd = new Arr([
            'type' => 'action'
        ]);
        $rd->merge($info);
        if ($slug) {
            $rd->slug = $slug;
        }
        if ($rd->name) {
            $rd->path = $rd->name;
            $rd->route = $rd->name;
            $a = explode('.', $rd->name);
            $t = count($a);
            if ($t >= 3) {
                if (!$slug) {
                    $rd->slug = array_pop($a);
                }
            }
        }
        $rd->name = $name ? $name : $rd->slug;
        if (!$group) $group = 'extra';
        if (is_array($group)) {
            $a = [];
            foreach ($group as $g) {
                $a[] = $this->addGroup($g)->addAction($rd->slug, $rd->all());
            }
            return $a;
        }
        return $this->addGroup($group)->addAction($rd->slug, $rd->all());
    }

    public function addRouteAction($group, $routeParam)
    {
        # code...
    }

    public function getNewID()
    {
        return ModuleManager::getIncrementID();
    }

    public function getchildren()
    {
        return $this->children;
    }

    public function getChildrenToArray()
    {
        $data = [];
        foreach ($this->children as $slug => $child) {
            $data[$slug] = $child->toArray();
        }
        return $data;
    }

    public function getActionsToArray()
    {
        $data = [];
        foreach ($this->actions as $slug => $action) {
            $data[$slug] = $action->toArray();
        }
        return $data;
    }

    public function getGroupsToArray()
    {
        $data = [];
        foreach ($this->groups as $slug => $child) {
            $data[$slug] = $child->toArray();
        }
        return $data;
    }

    public function getSubsToArray()
    {
        $data = [];
        foreach ($this->subs as $slug => $sub) {
            $data[$slug] = $sub->toArray();
        }
        return $data;
    }


    /**
     * kiểm tra có action hay ko
     *
     * @param string $slug
     * @return boolean
     */
    public function hasAction($slug)
    {
        return in_array($slug, $this->actions);
    }


    /**
     * kiểm tra có sub hay ko
     *
     * @param string $slug
     * @return boolean
     */
    public function hasSub($slug)
    {
        return in_array($slug, $this->subs);
    }


    /**
     * kiểm tra có sub hay ko
     *
     * @param string $slug
     * @return boolean
     */
    public function hasGroup($slug)
    {
        return in_array($slug, $this->groups);
    }


    /**
     * kiểm tra có sub hay ko
     *
     * @param string $slug
     * @return boolean
     */
    public function hasChild($slug)
    {
        return in_array($slug, $this->children);
    }



    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'actions' => $this->getActionsToArray(),
            'groups' => $this->getGroupsToArray(),
            'subs' => $this->getSubsToArray(),
            'children' => $this->getChildrenToArray()
        ]);
    }
}
