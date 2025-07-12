<?php

namespace Steak\Core\Concerns;

use Illuminate\Http\Request;
use Steak\Core\Magic\Arr;

trait ViewMethods
{

    /**
     * @var string $viewFolder thu muc chua view
     * khong nen thay doi lam gi
     */
    protected $viewFolder = null;

    /**
     * @var string $index file do data cua ham index
     */
    protected $index = 'index';

    /**
     * @var string $list file do data cua ham list
     */
    protected $list = 'list';

    /**
     * @var string $trash file do data cua ham trash
     */
    protected $trash = 'trash';

    /**
     * @var string $detail ten file blade cua detail
     */
    protected $detail = 'detail';

    /**
     * @var string $form blade name
     */
    protected $form = 'form';

    /**
     * @var string $alert blade name
     */
    protected $alert = 'alert';

    /**
     * @var string $error blade name
     */
    protected $error = 'errors';

    protected $moduleBlade = null;

    protected $viewMode = 'direct'; // direct, package, module, theme

    protected $viewBasePath = null;

    protected $defaultViewData = [
        '_base' => 'web.',
        'module_slug' => 'web',
        'module_name' => 'Web',
        'route_name_prefix' => 'web',
        '_component' => 'web.components.',
        '_template' => 'web.templates.',
        '_pagination' => 'web.pagination.',
        '_layout' => 'web.layouts.',
        '_module' => 'web.modules.',
    ];

    public function viewInit()
    {
        $this->viewBasePath = ($this->scope ? '.' . $this->scope : '') . ($this->viewFolder ? '.' . $this->viewFolder : '');
        $this->moduleBlade = ($this->viewBasePath ? '.' . $this->viewBasePath : '') . '.modules';
        $d = $this->viewBasePath ? $this->viewBasePath . '.' : '';
        $this->defaultViewData = [
            '_base' => $d,
            'module_slug' => $this->module,
            'module_name' => $this->moduleName,
            'route_name_prefix' => $this->routeNamePrefix,
            '_component' => $d . 'components.',
            '_template' => $d . 'templates.',
            '_pagination' => $d . 'pagination.',
            '_layout' => $d . 'layouts.',
            '_module' => $d . 'modules.',
        ];
    }



    /**
     * bắt sự kiện
     * @param string $event
     * @param array ...$params
     * @return mixed
     */
    public function callViewEvent(string $event, ...$params)
    {
        if (method_exists($this, $event)) {
            return call_user_func_array([$this, $event], $params);
        }
        $a = $this->fire($event, ...$params);

        return null;
    }

    /**
     * view
     * @param string $bladePath
     * @param array $data
     * @return ViewEngine
     */
    public function render(string $bladePath, array $data = [])
    {
        $d = $this->defaultViewData['_base'];

        $bp = $d . $bladePath;

        if ($this->isViewForm) {
            if (!view()->exists($bp) && $this->mode == 'package') {
                $d = $this->package . ':' . $d;
                $bp = $this->package . ':' . $bp;
            }
        } elseif ($this->mode == 'package' && view()->exists($this->package . ':' . $bp)) {
            $d = $this->package . ':' . $d;
            $bp = $this->package . ':' . $bp;
        }


        $a = explode('.', $bp);
        $b = array_pop($a);
        $current = implode('.', $a) . '.';
        $mdd = [
            '_current' => $current,
            'module_slug' => $this->module,
            'module_name' => $this->moduleName,
            'route_name_prefix' => $this->routeNamePrefix,
            'package' => $this->package
        ];
        if ($this->mode != 'package' || !$this->package) {
            // $mdd = array_merge($mdd, [
            //     '_component' => $d . 'components.', // blade path to folder contains all of components
            //     '_template' => $d . 'templates.',
            //     '_pagination' => $d . 'pagination.',
            //     '_layout' => $d . 'layouts.',
            //     '_base' => $d,

            // ]);
        }
        $viewdata = array_merge($data, $mdd);
        return view($bp, $viewdata);
    }

    /**
     * giống view nhung trỏ sẵn vào module
     * @param string $bladeName
     * @param array $data dữ liệu truyền vào
     */
    public function renderModule($subModule, array $data = [])
    {
        return $this->render($this->moduleBlade . '.' . $subModule, $data);
    }

    /**
     * lấy danh sách 
     * @param Request $request
     * @param array $params
     * @param array $variable
     * @return View
     */
    public function getFlashModeListData(Request $request, array $params = [], array $variable = [])
    {
        $this->callViewEvent('beforeGetListData', $request);
        $data = [];
        $data['results'] = $this->getResults($request, $params);
        $arrData = new Arr($data);
        $this->callViewEvent('beforeGetListView', $request, $arrData);
        $config = new Arr($this->getListConfigData());
        $viewData = $arrData->all();
        $viewData['config'] = $config;
        $viewShareData = array_merge(['list_group' => 'default'], $variable, $viewData);

        return $this->render('_module.list', $viewShareData);
    }

    /**
     * Hiển thị danh sách các kết quar tim dc
     * @param Request $request
     * @return View
     */
    public function getIndex(Request $request)
    {
        if ($this->flashMode) {
            return $this->getFlashModeListData($request, [], ['list_group' => 'default']);
        }

        $this->callViewEvent('beforeGetIndexData', $request);
        $data = [];
        $data['results'] = $this->getResults($request);
        $arrData = new Arr($data);
        $this->callViewEvent('beforeGetIndexView', $request, $arrData);
        // co the code them =))))))

        return $this->renderModule($this->index, $arrData->all());
    }

    /**
     * Hiển thị danh sách các kết quar tim dc
     * @param Request $request
     * @return View
     */
    function getList(Request $request)
    {
        $this->activeMenu($this->module . '.list');
        if ($this->flashMode) {
            return $this->getFlashModeListData($request, [], ['list_group' => 'default']);
        }
        $this->callViewEvent('beforeGetListData', $request);
        $data = [];
        $data['results'] = $this->getResults($request);
        $arrData = new Arr($data);
        $this->callViewEvent('beforeGetListView', $request, $arrData);

        // co the code them =))))))

        return $this->renderModule($this->list, $arrData->all());
    }


    /**
     * Hiển thị danh sách các kết quar tim dc
     * @param Request $request
     * @return View
     */
    function getDetail(Request $request, $id = null)
    {
        $this->repository->notTrashed();
        if ($this->flashMode) {
            return $this->getFlashModeDetailData($request);
        }
        $this->callViewEvent('beforeGetDetailData', $request);

        $keyName = $this->repository->getKeyName();
        if ($id && $detail = $this->repository->getDetail([$keyName => $id])) {
            $data = [];
            $data['detail'] = $detail;
            $arrData = new Arr($data);
            $this->callViewEvent('beforeGetDetailView', $request, $arrData);
            return $this->renderModule($this->detail, $arrData->all());
        }

        // co the code them =))))))
        return $this->showError($request, 404, "Mục này không tồn tại hoặc đã bị xóa");
    }


    /**
     * Hiển thị danh sách Dã bị xóa tạm thời
     * @param Request $request
     * @return View
     */
    function getTrash(Request $request)
    {
        $this->activeMenu($this->module . '.trash');
        $this->repository->trashed(true);

        $this->callViewEvent('beforeGetTrashData', $request);
        if ($this->flashMode) {
            return $this->getFlashModeListData($request, [], ['list_group' => 'trash']);
        }

        // co the code them =))))))
        $data = [];
        $data['results'] = $this->getResults($request, []);
        $arrData = new Arr($data);
        $this->callViewEvent('beforeGetTrashView', $request, $arrData);

        // co the code them =))))))

        return $this->renderModule($this->trash, $arrData->all());
    }

    /**
     * hiển thị form thêm mới dữ liệu
     * @param Request
     * @return View
     *
     * @override để xử lý
     */
    public function getCreateForm(Request $request)
    {
        $this->activeMenu($this->module . '.create');
        // return $this->viewModule('add-form');
        return $this->getCrudForm($request, ['type' => 'create']);
    }

    /**
     * hiển thị form cập nhật
     * @param Request $request
     * @param int $id
     * @return View
     */
    public function getUpdateForm(Request $request, $id = null)
    {
        $this->repository->notTrashed();
        $keyName = $this->repository->getKeyName();
        $id = $request->id ?? $request->uuid;
        if ($id && $detail = $this->repository->getFormData([$keyName => $id])) {
            $this->repository->setActiveID($detail->{$keyName});
            $this->activeMenu($this->module . '.update');
            return $this->getCrudForm($request, ['type' => 'update'], $detail);
        }
        return $this->showError($request, 404, "Mục này không tồn tại hoặc đã bị xóa");
    }



    /**
     * hiển thị form thêm mới dữ liệu
     * @param Request
     * @return View
     *
     * @override để xử lý
     */
    public function getFreeForm(Request $request)
    {
        return $this->getForm($request, ['type' => 'free']);
    }


    /**
     * hiển thị lỗi
     * @param Request $request
     * @param int $code error code
     * @param string $message
     * @return View
     */
    public function showError(Request $request, $code = 404, $message = "")
    {
        if (!$message && $request->message) $message = $request->message;
        $code = in_array($code, [403, 404, 500]) ? $code : 404;
        return $this->render($this->error . '.' . $code, compact('message'));
    }

    /**
     * hiển thị lỗi
     * @param string $message
     * @return View
     */
    public function alert($message = null, $type = null)
    {
        return $this->render('alert.message', compact('message', 'type'));
    }
}
