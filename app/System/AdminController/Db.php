<?php
namespace App\System\AdminController;

use Phpbe\System\Be;
use Phpbe\System\Request;
use Phpbe\System\Response;

class Db extends \Phpbe\System\AdminController
{

    public function tables()
    {
        $service = Be::getService('System', 'Db');
        if (Request::isPost()) {

            $type = Request::get('type');
            if ($type == 'lists') {
                $app = Request::get('app');
                $tables = $service->getTables($app);
                Response::set('app', $app);
                Response::set('tables', $tables);
                Response::display('System', 'Db.tableLists');
            } else if ($type == 'config') {
                $app = Request::get('app');
                $table = Request::get('table');
                $tables = $service->getTables($app);
                Response::set('tables', $tables);
                Response::display('System', 'Db.tableConfig');
            } else if ($type == 'save') {
                $app = Request::get('app');
                $table = Request::get('table');

            }

        } else {
            $apps = $service->getApps();
            Response::set('apps', $apps);
            Response::setTitle('数据库表配置');
            Response::display();
        }
    }


    /**
     * 配置项
     */
    public function tableConfig()
    {
        $app = Request::get('app');
        $table = Request::get('table');

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

            $serviceSystem = Be::getService('System', 'Db');
            $serviceSystem->updateTableConfig($app, $table, $formattedFields);

            Response::success('修改配置成功！');

        } else {

            Response::setTitle('数据库'. $app . '/' . $table . ' - 配置');
            Response::set('table', $table);
            Response::display();
        }
    }


}