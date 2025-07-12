<?php

namespace Steak\Core\Concerns;

use Steak\Core\Magic\Arr;
use Illuminate\Http\Request;

use Steak\Core\Html\Menu;

use Steak\Core\Laravel\Router;
use Steak\Core\Masks\EmptyCollection;

/**
 * các thuộc tính và phương thức của form sẽ được triển trong ManagerController
 * @property \Steak\Core\Repositories\BaseRepository $repository
 */
trait ModuleMethods
{

    /**
     * @var string $module day là tên module cung la ten thu muc view va ten so it cua bang, thu muc trong asset
     * override del chinh sua
     */
    protected $module = 'test';


    /**
     * @var string $moduleName tên của module và cũng là tiêu đề trong form
     */
    protected $moduleName = '';

    /**
     * @var string $routeNamePrefix
     */
    protected $routeNamePrefix = '';

    /**
     * @var string $menuName
     */
    protected $menuName = 'menu';

    /**
     * @var bool $flashMode cho biết có chia chức năng này thành module rieng ko hay sử dụng trung
     * Chuẩn hóa module thoe mguyen6 mẫu Crazy CMS 
     */
    protected $flashMode = true;

    /**
     * @var string $modulePath
     */
    protected $modulePath = '';

    protected $scope = '';


    protected $mode = 'system';

    public function callRepositoryMethod($method, $args = [], $default = null)
    {
        if ($this->repository && is_object($this->repository)) {
            return $this->repository->{$method}(...$args);
        }
        if (is_string($default) && class_exists($default)) {
            return app($default);
        }
        return $default;
    }

    /**
     * thực hiện một hành dộng với repository bất kể xuất hiện lỗi hay không
     *
     * @param callable $callback
     * @param mixed $default
     * @return mixed
     */
    public function repositoryTap($callback, $default = null){
        $result = is_string($default) && class_exists($default) ? app($default) : $default;
        try{
            if(is_callable($callback) && is_object($this->repository)){
                $result = $callback($this->repository);
            }
        } catch (\Exception $e) {
        }
        return $result;
    }


    /**
     * lấy dữ liệu damg5 danh sách
     * @param Request $request
     * @param array $args
     *
     * @return collection
     */
    public function getResults(Request $request, array $args = [])
    {
        return $this->repositoryTap(function($repository) use ($request, $args){
            return $repository->getResults($request, $args);
        }, EmptyCollection::class);
    }

    /**
     * thiết lập module
     */
    public function moduleInit()
    {
        if (!$this->moduleBlade) $this->moduleBlade = $this->module;

        if ($this->repository)
            $this->repository->notTrashed();
        
    }

    /**
     * actice module menu
     */
    public function activeMenu($activeKey = null)
    {
        Menu::removeActiveKey($this->menuName);
        Menu::addActiveKey($this->menuName, $activeKey ? $activeKey : $this->module);
    }

    /**
     * get route url
     * @param string $routeName
     * @param array $params
     * 
     * @return string
     */
    public function getRouteUrl($routeName = null, array $params = [])
    {
        if (!is_string($routeName) || !strlen($routeName)) return null;
        if (Router::getByName($this->routeNamePrefix . $routeName)) {
            return \route($this->routeNamePrefix . $routeName, $params);
        }
        return null;
    }

    /**
     * get route url
     * @param string $routeName
     * @param array $params
     * 
     * @return Route
     */
    public function getModuleRoute($routeName = null, array $params = [])
    {
        return $this->getRouteUrl($this->module . '.' . $routeName, $params);
    }

    /**
     * thêm nút thêm mới
     * 
     */
    public function addHeaderButtons(...$buttons)
    {
        $btns = [
            'create' => [
                'url' => $this->getModuleRoute('create'),
                'text' => 'Thêm mới',
                'icon' => 'plus'
            ]
        ];
        $data = [];
        if ($buttons) {
            foreach ($buttons as $i => $button) {
                if (isset($btns[$button])) {
                    $data[] = $btns[$button];
                }
            }
            // admin_breadcrumbs($data);
        }
    }



    /**
     * lấy dữ liệu list
     * @param Arr $config
     * 
     */
    public function getListConfigData()
    {
        $data = [];
        // nếu sử dụng flash mode
        if ($this->flashMode) {
            $file = $this->modulePath . '/list';
            $data = $this->getJsonData($file);
            if ($data) {
                $data = $this->checkListExtendsAndInclude($file, $data);
            }
        }

        return $data;
    }


    public function checkListExtendsAndInclude($filename, $data)
    {
        if (array_key_exists('extends', $data)) {
            $mergeData = [];
            $paths = explode('/', $filename);
            array_pop($paths);

            if (is_string($data['extends']) && strlen($data['extends'])) {
                $ejPath = $data['extends'];
                $clonePaths = $paths;
                if (substr($ejPath, 0, 1) != '/') {
                    $ExtPaths = explode('/', $ejPath);
                    foreach ($ExtPaths as $path) {
                        if ($path == '..') {
                            array_pop($clonePaths);
                        } elseif ($path != '.') {
                            $clonePaths[] = $path;
                        }
                    }
                    $ejPath = implode('/', $clonePaths);
                } else {
                    $ejPath = substr($ejPath, 1);
                }
                if ($configData = $this->getJsonData($ejPath)) {

                    $mergeData = $this->checkListExtendsAndInclude($ejPath, $configData);
                }
            }
            unset($data['extends']);
            return Arr::deepMerge($mergeData, $data);
        }
        return $data;
    }
}
