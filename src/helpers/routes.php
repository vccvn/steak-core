<?php

use Illuminate\Support\Facades\Route;
use Steak\Core\Module;
use Steak\Core\ModuleManager;
use Steak\Laravel\Router;
use Steak\Magic\Arr;

if (!function_exists('get_route_options')) {
    /**
     * lấy thông tin route trả về dạng option key => value
     *
     * @param string $prefix
     * @return array
     */
    function get_route_options($prefix = null)
    {
        $data = [];
        $routes = Router::getSelectNameAndUri();
        if ($prefix) {
            if ($prefix == 'frontend') {
                foreach ($routes as $name => $uri) {
                    if (preg_match('/^' . $prefix . '\./i', $name) || $uri == '/') {
                        $data[$name] = $uri;
                    }
                }
            } else {
                foreach ($routes as $name => $uri) {
                    if (preg_match('/^' . $prefix . '\./i', $name)) {
                        $data[$name] = $uri;
                    }
                }
            }
        } else {
            $data = $routes;
        }
        return $data;
    }
}

if (!function_exists('add_web_module_routes')) {
    /**
     * dinh nghia cac route cho một module
     *
     * @param string $controller
     * @param array|boolean $list
     * @param string|boolean $routeName
     * @param string $scope
     * @param boolean $registerModule
     * @param string $moduleSlug
     * @param string $moduleName
     * @param string $moduleDescription
     * @param string|module $parent
     * @return Module|null
     */
    function add_web_module_routes($controller = null, $list = [], $routeName = null, $scope = '', $registerModule = false, $moduleSlug = null, $moduleName = '', $moduleDescription = '', $parent = null)
    {
        /**
         * Module Name         => [method,    uri,                   function,                     name]
         */
        $routeData = [
            'index'            => ['get',     '/',                    'getIndex',                  ''],
            'list'             => ['get',     'list.html',            'getList',                   'list'],
            'trash'            => ['get',     'trash.html',           'getTrash',                  'trash'],
            'detail'           => ['get',     'detail/{id}.html',     'getDetail',                 'detail'],
            'ajax'             => ['get',     'ajax-search',          'ajaxSearch',                'ajax'],
            'create'           => ['get',     'create.html',          'getCreateForm',             'create'],
            'update'           => ['get',     'update/{id}.html',     'getUpdateForm',             'update'],
            'save'             => ['post',    'save',                 'save',                      'save'],
            'move-to-trash'    => ['post',    'move-to-trash',        'moveToTrash',               'move-to-trash'],
            'delete'           => ['post',    'delete',               'delete',                    'delete'],
            'restore'          => ['post',    'restore',              'restore',                   'restore'],
            'form-config'      => ['get',     'form/config/{action?}','getConfigForm',             'form.config.edit'],
            'form-config-save' => ['post',    'form/config/save',     'saveConfigForm',            'form.config.save']
        ];

        /**
         * Group Name        => [name:string,                   list:array<string>]
         */
        $group = [
            'view'             => ['name' => 'Xem',            'list' => ['list', 'detail', 'ajax', 'trash']],
            'create'           => ['name' => 'Thêm',           'list' => ['create', 'save']],
            'update'           => ['name' => 'Chỉnh sửa',      'list' => ['update', 'save']],
            'delete'           => ['name' => 'Xóa',            'list' => ['trash', 'move-to-trash', 'delete']],
            'restore'          => ['name' => 'Khôi phục',      'list' => ['restore']],
            'config'           => ['name' => 'Cấu hình form',  'list' => ['form-config', 'form-config-save']],
            'refs'             => ['name' => 'Liên kết',       'list' => ['refs']],
            'extra'            => ['name' => 'Mở rộng',        'list' => []]
        ];
        /**
         * Module Name         => [group:string]
         */
        $routeDataMap = [
            'index'            => ['view'],
            'list'             => ['view'],
            'detail'           => ['view'],
            'ajax'             => ['view'],
            'create'           => ['create'],
            'update'           => ['update'],
            'save'             => ['create', 'update'],
            'trash'            => ['delete', 'view'],
            'move-to-trash'    => ['delete'],
            'delete'           => ['delete'],
            'restore'          => ['restore'],
            'form-config'      => ['config'],
            'form-config-save' => ['config']
        ];

        if (!is_array($list)) {
            if (in_array($list, ['all', '*']) || !$list) {
                $list = array_keys($routeData);
            } elseif (array_key_exists($list, $routeData)) {
                $list = [$list];
            } else {
                return false;
            }
        } elseif (!count($list)) {
            $list = array_keys($routeData);
        }


        $moduleRouter = null;
        if ($routeName) {
            $masterModule = null;
            $rd = new Arr([
                'type' => 'module',
                'description' => $moduleDescription
            ]);
            if (count($list) && $list[0] == 'index') {
                $masterModule = Route::get('/', $controller ? [$controller, 'getIndex'] : 'getIndex')->name(is_bool($routeName) ? '' : $routeName);
                array_shift($list);
            }
            if ($registerModule && ModuleManager::isActive()) {
                if ($masterModule) {
                    $a = Router::getRouteInfo($masterModule);
                    // dump($a);
                    $rd->merge($a);
                    if ($moduleSlug) {
                        $rd->slug = $moduleSlug;
                    }
                    if ($rd->name) {
                        $rd->path = $rd->name;
                        $rd->route = $rd->name;
                        $a = explode('.', $rd->name);
                        $t = count($a);
                        if ($t >= 2) {
                            if (!$moduleSlug) {
                                $rd->slug = array_pop($a);
                            }
                            // if ($t > 2 && !$parent) $parent = array_pop($a);
                        }
                        if (!$scope) $scope = $a[0];
                    }
                    $rd->name = $moduleName ? $moduleName : $rd->slug;
                    if($parent && is_a($parent, Module::class)){
                        $moduleRouter = $parent->addSub($rd->slug, $rd->all());
                    }
                    else{
                        $moduleRouter = ModuleManager::addModule($scope, $rd->slug, $rd->all());
                    }
                }
            }else {
                $moduleRouter = ModuleManager::getEmpty();
                // dd($masterModule);
            }
            $groupRouter = Route::name(is_bool($routeName) ? '.' : $routeName . '.');
            if ($controller) {
                $groupRouter->controller($controller);
            }
            $groupRouter->group(function ($router) use ($list, $routeData, $routeName, $masterModule, $rd, $registerModule, $moduleName, $moduleSlug, $parent, $scope, $group, $routeDataMap, $moduleRouter) {
                $submodules = [];
                foreach ($list as $key) {
                    if (array_key_exists($key, $routeData)) {
                        $detail = $routeData[$key];
                        $router = call_user_func_array(['Route', $detail[0]], [$detail[1], $detail[2]]);
                        $router->name($detail[3]);
                        if ($registerModule && ModuleManager::isActive()) {
                            $info = Router::getRouteInfo($router);
                            if (!$masterModule) {

                                $rd->merge($info);
                                if ($moduleSlug) {
                                    $rd->slug = $moduleSlug;
                                }
                                if ($rd->name) {
                                    $rd->path = $rd->name;
                                    $rd->route = $rd->name;
                                    $a = explode('.', $rd->name);
                                    $t = count($a);
                                    if ($t >= 3) {
                                        if($detail[3] != '') array_pop($a);
                                        if (!$moduleSlug) {
                                            $rd->slug = array_pop($a);
                                            $moduleSlug = $rd->slug;
                                        }

                                        // if (!$parent) $parent = array_pop($a);
                                    }
                                    if (!$scope) $scope = $a[0];
                                }
                                $rd->name = $moduleName ? $moduleName : $rd->slug;
                                if($parent && is_a($parent, Module::class)){
                                    $moduleRouter = $parent->addSub($rd->slug, $rd->all());
                                }
                                else{
                                    $moduleRouter = ModuleManager::addModule($scope, $rd->slug, $rd->all());
                                }
                            } else {
                                foreach ($routeDataMap[$key] as $subslug) {
                                    if (!array_key_exists($subslug, $submodules)) {
                                        $submodules[$subslug] = [
                                            'type' => 'group',
                                            'name' => $group[$subslug]['name'],
                                            'slug' => $subslug,
                                            'actions' => [
                                                $key => [
                                                    'type' => 'action',
                                                    'name' => ucfirst($key),
                                                    'slug' => $key,
                                                    'route' => $info['name'] ?? $key,
                                                    'path' => $info['name'] ?? ($parent ? "$parent.$moduleSlug.$key" : "$moduleSlug.$key")
                                                ]
                                            ]
                                        ];
                                    } else {
                                        $submodules[$subslug]['actions'][$key] = [
                                            'type' => 'action',
                                            'name' => ucfirst($key),
                                            'slug' => $key,
                                            'route' => $info['name'] ?? $key,
                                            'path' => $info['name'] ?? ($parent ? "$parent.$moduleSlug.$key" : "$moduleSlug.$key")
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
                if ($registerModule && $moduleRouter && ModuleManager::isActive()) {
                    $moduleRouter->addGroups($submodules);
                }
            });
            // if($registerModule && !$moduleRouter){
            //     if($rd->moduleRouter){
            //         $moduleRouter = $rd->moduleRouter;
            //     }
            // }
        } else {
            if ($controller) {
                Route::controller($controller)->group(function () use ($list, $routeData) {
                    foreach ($list as $key) {
                        if (array_key_exists($key, $routeData)) {
                            $detail = $routeData[$key];
                            $router = call_user_func_array(['Route', $detail[0]], [$detail[1], $detail[2]]);
                        }
                    }
                });
            } else {
                // Route::controller($controller)->group(function () use ($list, $routeData, $route) {
                foreach ($list as $key) {
                    if (array_key_exists($key, $routeData)) {
                        $detail = $routeData[$key];
                        $router = call_user_func_array(['Route', $detail[0]], [$detail[1], $detail[2]]);
                    }
                }
                // });
            }
        }
        return $moduleRouter;
    }
}




if (!function_exists('api_routes')) {
    /**
     * dinh nghia cac route cho một module nào đó phần manager
     * @param string<class> $controller class
     * @param string|bool $route      prefix of route
     * @param boolean $require_index_route
     *
     * @return void
     */
    function api_routes($controller, $route = null, $require_index_route = false, $registerModule = false, $moduleSlug = null, $moduleName = '', $moduleDescription = '', $parent = null)
    {
        // Route::controller($controller);
        /**
         * --------------------------------------------------------------------------------------------------------------------
         *    Method | URI                                | Controller @ Nethod              | Route Name                     |
         * --------------------------------------------------------------------------------------------------------------------
         */

        if ($route) {
            if ($require_index_route) {
                Route::get('/',       [$controller, 'index'])->name(is_bool($route) ? '' : $route);
            }
            $route .= '.';
            Route::controller($controller)->name(is_bool($route) ? '.' : $route . '.')->group(function () {
                Route::get('/list',                             'index')->name('list');
                Route::get('/trash',                            'trash')->name('trash');
                Route::get('/detail/{id}',                      'detail')->name('detail');
                Route::post('/create',                          'create')->name('create');
                Route::post('/store',                           'store')->name('store');
                Route::put('/update/{id}',                      'update')->name('update');
                Route::post('/save',                            'save')->name('save');
                Route::delete('/move-to-trash',                 'moveToTrash')->name('move-to-trash');
                Route::delete('/delete',                        'delete')->name('delete');
                Route::put('/restore',                          'restore')->name('restore');
            });
        } else {
            if ($require_index_route)  Route::get('/',       'index');
            Route::get('/list',                             'index');
            Route::get('/trash',                            'trash');
            Route::get('/detail/{id}',                      'detail');
            Route::post('/create',                          'create');
            Route::post('/store',                           'store');
            Route::put('/update/{id}',                      'update');
            Route::post('/save',                            'save');
            Route::delete('/move-to-trash',                 'moveToTrash');
            Route::delete('/delete',                        'delete');
            Route::put('/restore',                          'restore');
        }
    }
}




if (!function_exists('admin_routes')) {
    /**
     * dinh nghia cac route cho một module nào đó phần admin
     *
     * @param string $controller
     * @param string|boolean $route
     * @param boolean $require_index_route
     * @param boolean $registerModule
     * @param string $moduleSlug
     * @param string $moduleName
     * @param string $moduleDescription
     * @param string|module $parent
     * @return Module|null
     */
    function admin_routes(
        $controller = null,
        $route = null,
        $require_index_route = false,
        $registerModule = false,
        $moduleSlug = null,
        $moduleName = '',
        $moduleDescription = '',
        $parent = null
    ) {
        $routeData = [
            'index', 'list', 'trash', 'detail', 'ajax',  'create', 'update',
            'save', 'move-to-trash', 'delete', 'restore', 'form-config', 'form-config-save'
        ];

        if (!$require_index_route) array_shift($routeData);

        return add_web_module_routes($controller, $routeData, $route, 'admin', $registerModule, $moduleSlug, $moduleName, $moduleDescription, $parent);
    }
}



if (!function_exists('merchant_routes')) {
    /**
     * dinh nghia cac route cho một module nào đó phần merchant_routes
     *
     * @param string $controller
     * @param string|boolean $route
     * @param boolean $require_index_route
     * @param boolean $registerModule
     * @param string $moduleSlug
     * @param string $moduleName
     * @param string $moduleDescription
     * @param string|module $parent
     * @return Module|null
     */
    function merchant_routes(
        $controller = null,
        $route = null,
        $require_index_route = false,
        $registerModule = false,
        $moduleSlug = null,
        $moduleName = '',
        $moduleDescription = '',
        $parent = null
    ) {
        $routeData = [
            'index', 'list', 'trash', 'detail', 'ajax',  'create', 'update',
            'save', 'move-to-trash', 'delete', 'restore', 'form-config', 'form-config-save'
        ];

        if (!$require_index_route) array_shift($routeData);

        return add_web_module_routes($controller, $routeData, $route, 'merchant', $registerModule, $moduleSlug, $moduleName, $moduleDescription, $parent);
    }
}

if (!function_exists('app_routes')) {
    /**
     * dinh nghia cac route cho một module nào đó phần app_routes
     *
     * @param string $controller
     * @param string|boolean $route
     * @param boolean $require_index_route
     * @param boolean $registerModule
     * @param string $moduleSlug
     * @param string $moduleName
     * @param string $moduleDescription
     * @param string|Module $parent
     * @return Module|null
     */
    function app_routes(
        $controller = null,
        $route = null,
        $require_index_route = false,
        $registerModule = false,
        $moduleSlug = null,
        $moduleName = '',
        $moduleDescription = '',
        $parent = null
    ) {
        $routeData = [
            'index', 'list', 'trash', 'detail', 'ajax',  'create', 'update',
            'save', 'move-to-trash', 'delete', 'restore', 'form-config', 'form-config-save'
        ];

        if (!$require_index_route) array_shift($routeData);

        return add_web_module_routes($controller, $routeData, $route, 'app', $registerModule, $moduleSlug, $moduleName, $moduleDescription, $parent);
    }
}
