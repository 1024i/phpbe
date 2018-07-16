<?php
namespace App\System\AdminController;

use Phpbe\System\Be;
use Phpbe\System\Request;
use Phpbe\System\Response;

class Table extends \Phpbe\System\AdminController
{

    public function lists()
    {
        $tables = Be::getService('table')->getTables();

        Response::set('tables', $tables);
        Response::display();
    }

    /**
     * 配置项
     */
    public function setting()
    {
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

            $serviceSystem = Be::getService('Cache');
            $serviceSystem->updateTableConfig($table, $formattedFields);

            Response::success('修改配置成功！');

        } else {

            Response::setTitle($table . ' - 配置');
            Response::set('table', $table);
            Response::display();
        }
    }


}