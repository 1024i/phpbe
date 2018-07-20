<?php
namespace App\System\AdminTrait;

use Phpbe\System\Be;
use Phpbe\System\Request;
use Phpbe\System\Response;
use Phpbe\System\Session;
use Phpbe\System\Cookie;

trait Resource
{

    /*
     * 在子类中必须定义 $config 属性，示例值如下：
    protected $config = [
        'base' => [
            'name' => '用户管理'
        ],

        'lists' => [

            'toolbar' => [
                'create' => '新建',
                'export' => '导出'
            ],

            'action' => [
                'detail' => '查看',
                'edit' => '编辑',
                'delete' => '删除',
            ],
        ],

        'detail' => [
            'tabs' => array()
        ],

        'create' => [],

        'edit' => [],

        'block' => [
            'field' => 'block',
            'value' => 1
        ],

        'unblock' => [
            'field' => 'block',
            'value' => 0
        ],

        'delete' => [
            'field' => 'is_delete',
            'value' => 1
        ],

        'export' => [],
    ];
    */

    /**
     * 列表展示
     */
    public function lists()
    {
        $app = Be::getRuntime()->getApp();
        $controller = Be::getRuntime()->getController();

        $table = Be::getTable($app, $controller);

        $primaryKey = $table->getPrimaryKey();

        if (Request::isPost()) {

            $this->buildWhere($table, Request::post(null, null, ''));

            $offset = Request::post('offset', 0);
            $limit = Request::post('limit', 0, 'int');
            if ($limit < 0) $limit = 0;


            $cookieLimitKey = '_'.$app.'_'.$controller.'_limit';

            if (!$limit) {
                $cookieLimit = Cookie::get($cookieLimitKey, 0, 'int');
                if ($cookieLimit > 0) {
                    $limit = $cookieLimit;
                } else {
                    $limit = Be::getConfig('System', 'Admin')->limit;
                }
            } else {
                Cookie::set($cookieLimitKey, $limit, 86400 * 30);
            }

            $total = $table->count();

            $table->offset($offset)->limit($limit);

            $orderBy = Request::post('sort', $primaryKey);
            $orderByDir = Request::post('order', 'DESC');
            $table->orderBy($orderBy, $orderByDir);

            $lists = $table->getObjects();

            $fields = $table->getFields();
            foreach ($lists as &$x) {
                $this->formatData($x, $fields);
            }

            Response::set('total', $total);
            Response::set('rows', $lists);
            Response::ajax();
        }

        Response::setTitle($this->config['base']['name'] . ' - 列表');
        Response::set('table', $table);
        Response::set('config', $this->config);

        Response::display('System', 'Resource.lists');
    }

    /**
     * 明细
     */
    public function detail()
    {
        $app = Be::getRuntime()->getApp();
        $controller = Be::getRuntime()->getController();

        $row = Be::getRow($app, $controller);

        $primaryKey = $row->getPrimaryKey();
        $primaryKeyValue = Request::get($primaryKey, null);

        if (!$primaryKeyValue) {
            Response::error('参数（' . $primaryKey . '）缺失！');
        }

        $row->load($primaryKeyValue);
        if (!$row->$primaryKey) {
            Response::error('主键编号（' . $primaryKey . '）为 ' . $primaryKeyValue . ' 的记录不存在！');
        }

        $fields = $row->getFields();
        $this->formatData($row, $fields);

        Response::setTitle($this->config['base']['name'] . ' - 明细');
        Response::set('row', $row);
        Response::display('System', 'Resource.detail');
    }

    /**
     * 创建
     */
    public function create()
    {
        $app = Be::getRuntime()->getApp();
        $controller = Be::getRuntime()->getController();

        $row = Be::getRow($app, $controller);

        if (Request::isPost()) {

            Be::getDb()->startTransaction();
            try {
                $row->bind(Request::post());
                $primaryKey = $row->getPrimaryKey();
                unset($row->$primaryKey);
                $row->save();

                Be::getDb()->commit();
            } catch (\Exception $e) {

                Be::getDb()->rollback();
                Response::error($e->getMessage());
            }

            Response::success('创建成功！');

        } else {
            Response::setTitle($this->config['base']['name'] . ' - 创建');
            Response::set('row', $row);
            Response::display('System', 'Resource.create');
        }
    }

    /**
     * 编辑
     */
    public function edit()
    {
        $app = Be::getRuntime()->getApp();
        $controller = Be::getRuntime()->getController();

        $row = Be::getRow($app, $controller);

        $primaryKey = $row->getPrimaryKey();
        $primaryKeyValue = Request::get($primaryKey, null);

        if (!$primaryKeyValue) {
            Response::error('参数（' . $primaryKey . '）缺失！');
        }

        $row->load($primaryKeyValue);
        if (!$row->$primaryKey) {
            Response::error('主键编号（' . $primaryKey . '）为 ' . $primaryKeyValue . ' 的记录不存在！');
        }

        if (Request::isPost()) {

            Be::getDb()->startTransaction();
            try {
                $row->bind(Request::post());
                $row->save();

                Be::getDb()->commit();
            } catch (\Exception $e) {

                Be::getDb()->rollback();
                Response::error($e->getMessage());
            }

            Response::success('修改成功！');

        } else {

            Response::setTitle($this->config['base']['name'] . ' - 编辑');
            Response::set('row', $row);
            Response::display('System', 'Resource.edit');
        }
    }

    /*
     * 导出
     */
    public function export()
    {
        $app = Be::getRuntime()->getApp();
        $controller = Be::getRuntime()->getController();

        $table = Be::getTable($app, $controller);

        $this->buildWhere($table, Request::post(null, null, ''));

        $lists = $table->getYieldArrays();

        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename='. date('YmdHis').'.csv');
        $handler = fopen('php://output', 'w') or die("can't open php://output");
        fwrite($handler, pack('H*', 'EFBBBF')); // 写入 BOM 头

        $headers = array();
        $fields = $table->getFields();
        foreach ($fields as $field) {
            if ($field['disable']) continue;

            $headers[] = $field['name'];
        }
        fputcsv($handler, $headers);

        $fields = $table->getFields();
        foreach ($lists as &$x) {

            $this->formatData($x, $fields);

            fputcsv($handler, $x);
        }
        fclose($handler) or die("can't close php://output");
    }


    public function exportTaskNew()
    {
        $exportTaskService = Be::getService('System', 'ExportTask');
        $name = $this->config['base']['name'] . '（' . date('YmdHis') . '）';
        $condition = array(
            'get' => Request::get(),
            'post' => Request::post()
        );
        $taskId = $exportTaskService->create($name, $condition);

        $app = Be::getRuntime()->getApp();
        $controller = Be::getRuntime()->getController();
        $taskUrl = adminurl(''.$app.'', ''.$controller.'', 'exportTaskRun', ['taskId' => $taskId]);
        $returnUrl = adminurl(''.$app.'', ''.$controller.'', 'exportTaskRun', ['return' => $_SERVER['HTTP_REFERER']]);

        echo '<iframe src="' . $taskUrl . '" style="width: 0; height: 0"></iframe>';
        echo '<script>setTimeout(function(){window.location.href="' . $returnUrl . '";}, 1000)</script>';
        echo '创建成功，正在跳转...';
    }

    public function exportTasks()
    {
        $app = Be::getRuntime()->getApp();
        $controller = Be::getRuntime()->getController();

        $sessionReturnKey = '_'.$app.'_'.$controller.'_return';
        $return = Request::get('return', '');
        if (!$return) $return = Session::get($sessionReturnKey, '');

        if (!$return) $return = $_SERVER['HTTP_REFERER'];
        Session::set($sessionReturnKey, $return);

        $exportTaskService = Be::getService('System', 'ExportTask');
        $tasks = $exportTaskService->getTasks();

        if (Request::isAjax()) {
            Response::set('success', true);
            Response::set('message', '');
            Response::set('data', $tasks);
            Response::ajax();
        } else {
            Response::setTitle($this->config['base']['name'] . ' - 导出任务列表');

            Response::set('tasks', $tasks);
            Response::set('return', $return);
            Response::display('System', 'Resource.exportTasks');
        }
    }

    public function exportTaskRun()
    {
        ignore_user_abort(true);
        session_write_close();
        header("Connection: close");
        header("HTTP/1.1 200 OK");
        ob_implicit_flush();

        echo '已启动！<script>setTimeout(function(){window.close()}, 3000)</script>';

        $taskId = Request::get('taskId');
        $exportTaskService = Be::getService('System', 'ExportTask');
        $task = $exportTaskService->getTask($taskId);

        try {
            $this->exportTaskProgress($task, $exportTaskService);
        } catch (\Exception $e) {
            $exportTaskService->error($taskId, $e);
        }
    }

    /**
     * @param array $task
     *      $task['taskId'] : 任务ID
     *      $task['name'] : 任务名称
     *      $task['condition'] : 查询条件
     * @param \App\System\Service\ExportTask $service
     */
    protected function exportTaskProgress($task, $service)
    {
        set_time_limit(3600);

        for ($i = 0; $i <= 100; $i++) {
            $service->setProgress($task['taskId'], $i);
            $service->addCsvData($task['taskId'], [$i]);
            sleep(1);
        }
    }

    public function exportTaskDetail()
    {
        $taskId = Request::get('taskId');

        $exportTaskService = Be::getService('System', 'ExportTask');
        $task = $exportTaskService->getTask($taskId);

        if (Request::isAjax()) {
            Response::set('success', true);
            Response::set('message', '');
            Response::set('data', $task);
            Response::ajax();
        } else {
            Response::setTitle($this->config['base']['name'] . ' - 导出任务明细（#' . $taskId . '）');
            Response::set('task', $task);
            Response::display('System', 'Resource.exportTaskDetail');
        }
    }

    public function exportTaskDownload()
    {
        $taskId = Request::get('taskId');
        $namespace = get_called_class();
        if (strpos($namespace, '\\') !== false) {
            $namespace = substr(strrchr($namespace, '\\'), 1);
        }

        $exportTaskService = Be::getService('System', 'ExportTask');
        $task = $exportTaskService->getTask($namespace, $taskId);

        if ($task['progress'] < 100) {
            Response::error('该导出任务未100%完成！');
        }

        $isWindows = false;
        if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows')) {
            $isWindows = true;
        }

        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename=' . $task['name'] . '.csv');
        $handler = fopen('php://output', 'w') or die("can't open php://output");
        if (!$isWindows) fwrite($handler, pack('H*', 'EFBBBF')); // 写入 BOM 头

        $csvData = $exportTaskService->getCsvData($namespace, $taskId);
        foreach ($csvData as $d) {
            if ($isWindows) {
                foreach ($d as $k => $v) {
                    $d[$k] = iconv("UTF-8", "GB2312//IGNORE", $v);
                }
            }
            fputcsv($handler, $d);
        }
        fclose($handler) or die("can't close php://output");
    }

    public function exportTaskDelete()
    {
        $taskId = Request::get('taskId');

        try {
            $exportTaskService = Be::getService('System', 'ExportTask');
            $exportTaskService->delete($taskId);

            Response::set('success', true);
            Response::set('message', '删除成功！');
        } catch (\Exception $e) {
            Response::set('success', false);
            Response::set('message', '删除失败：' . $e->getMessage());
        }
        Response::ajax();
    }


    /**
     * 删除
     */
    public function delete()
    {
        $app = Be::getRuntime()->getApp();
        $controller = Be::getRuntime()->getController();

        $row = Be::getRow($app, $controller);

        $primaryKey = $row->getPrimaryKey();
        $primaryKeyValue = Request::get($primaryKey, null);

        if (!$primaryKeyValue) {
            Response::error('参数（' . $primaryKey . '）缺失！');
        }

        Be::getDb()->startTransaction();
        try {

            if (is_array($primaryKeyValue)) {
                foreach ($primaryKeyValue as $x) {
                    $row->delete($x);
                }
            } else {
                $row->delete($primaryKeyValue);
            }

            Be::getDb()->commit();
        } catch (\Exception $e) {

            Be::getDb()->rollback();
            Response::error($e->getMessage());
        }

        Response::success('创建成功！');
    }

    /**
     * 配置项
     */
    public function setting()
    {
        $app = Be::getRuntime()->getApp();
        $controller = Be::getRuntime()->getController();

        $table = Be::getTable($app, $controller);

        if (Request::isPost()) {

            $fieldItems = Request::post('field');
            $nameItems = Request::post('name');
            $optionTypeItems = Request::post('optionType');
            $optionDataItems = Request::post('optionData');
            $disableItems = Request::post('disable');
            $showItems = Request::post('show');
            $editableItems = Request::post('editable');
            $createItems = Request::post('create');
            $formatItems = Request::post('format');


            $len = count($fieldItems);

            $formattedFields = array();
            for ($i = 0; $i < $len; $i++) {
                $formattedFields[$fieldItems[$i]] = array(
                    'field' => $fieldItems[$i],
                    'name' => $nameItems[$i],
                    'optionType' => $optionTypeItems[$i],
                    'optionData' => $optionDataItems[$i],
                    'disable' => $disableItems[$i],
                    'show' => $showItems[$i],
                    'editable' => $editableItems[$i],
                    'create' => $createItems[$i],
                    'format' => $formatItems[$i],
                );
            }

            $serviceSystem = Be::getService('System', 'Resource');
            $serviceSystem->updateTableConfig($this->config['base']['table'], $formattedFields);

            Response::success('修改配置成功！');

        } else {

            Response::setTitle($this->config['base']['name'] . ' - 配置');
            Response::set('table', $table);
            Response::display('System', 'Resource.setting');
        }
    }

    public function chartPie() {

        // 聚合字段
        $aggField = Request::get('aggField', '');
        $aggLimit = Request::get('aggLimit', 10, 'int');

        $app = Be::getRuntime()->getApp();
        $controller = Be::getRuntime()->getController();

        $table = Be::getTable($app, $controller);

        Response::set('table', $table);
        Response::set('aggField', $aggField);

        if ($aggField) {
            $aggData = $table->groupBy($aggField)
                ->limit($aggLimit)
                ->getObjects($aggField.', COUNT(*) quantity');

            Response::set('table', $table);
            Response::set('aggField', $aggField);
            Response::set('aggData', $aggData);
        }

        Response::setTitle($this->config['base']['name'] . ' - 饼图');
        Response::display('System', 'Resource.chartPie');
    }


    /**
     * @param \Phpbe\System\Db\Table $table
     * @param $condition
     */
    protected function buildWhere($table, $condition) {

        $len = count($condition['conditionField']);
        if ($len > 0) {

            for ($i = 0; $i <= $len; $i++) {
                $conditionField = $condition['conditionField'][$i];
                $conditionOperator = $condition['conditionOperator'][$i];
                $conditionValue = trim($condition['conditionValue'][$i]);

                if ($conditionValue === '') continue;

                switch ($conditionOperator) {
                    case '=':
                    case '>':
                    case '>=':
                    case '<':
                    case '<=':
                        $table->where($conditionField, $conditionOperator, $conditionValue);
                        break;

                    case 'like':
                        $table->where($conditionField, 'LIKE', '%'.$conditionValue.'%');
                        break;
                    case 'like1':
                        $table->where($conditionField, 'LIKE', $conditionValue.'%');
                        break;
                    case 'like2':
                        $table->where($conditionField, 'LIKE', '%'.$conditionValue);
                        break;
                }
            }
        }
    }

    protected function formatData($data, $fields)
    {
        foreach ($fields as $field) {

            $f = $field['field'];

            if ($field['disable']) {
                unset($data->$f);
                continue;
            }

            if ($field['optionType'] != 'null') {

                $keyValues = $field['option']->getKeyValues();

                if (isset($keyValues[$data->$f])) {
                    $data->$f = $keyValues[$data->$f];
                } else {
                    $data->$f = '-';
                }
            } else {

                if ($field['format']) {

                    switch ($field['format']) {

                        case 'date(Ymd)':
                            $data->$f = date('Y-m-d', $data->$f);
                            break;

                        case 'date(YmdHi)':
                            $data->$f = date('Y-m-d H:i', $data->$f);
                            break;

                        case 'date(YmdHis)':
                            $data->$f = date('Y-m-d H:i:s', $data->$f);
                            break;

                    }

                }

            }
        }

        return $data;
    }

}