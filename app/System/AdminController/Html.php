<?php
namespace App\System\AdminController;

use System\Be;
use System\Request;
use System\Response;

// 自定义模块
class Html extends \System\AdminController
{

    public function htmls()
    {
        $orderBy = Request::post('orderBy', 'id');
        $orderByDir = Request::post('orderByDir', 'ASC');

        $key = Request::post('key', '');
        $status = Request::post('status', -1, 'int');
        $limit = Request::post('limit', -1, 'int');

        if ($limit == -1) {
            $adminConfigSystem = Be::getConfig('System.Admin');
            $limit = $adminConfigSystem->limit;
        }

        $adminServiceSystemHtml = Be::getService('System.Html');
        Response::setTitle('自定义模块');

        $option = array('key' => $key, 'status' => $status);

        $pagination = Be::getUi('Pagination');
        $pagination->setLimit($limit);
        $pagination->setTotal($adminServiceSystemHtml->getSystemHtmlCount($option));
        $pagination->setPage(Request::post('page', 1, 'int'));

        Response::set('pagination', $pagination);
        Response::set('orderBy', $orderBy);
        Response::set('orderByDir', $orderByDir);
        Response::set('key', $key);
        Response::set('status', $status);

        $option['orderBy'] = $orderBy;
        $option['orderByDir'] = $orderByDir;
        $option['offset'] = $pagination->getOffset();
        $option['limit'] = $limit;

        $systemHtmls = $adminServiceSystemHtml->getSystemHtmls($option);
        Response::set('systemHtmls', $systemHtmls);

        Response::display();

        $libHistory = Be::getLib('History');
        $libHistory->save();
    }


    public function edit()
    {
        $id = Request::post('id', 0, 'int');

        $rowSystemHtml = Be::getRow('System.html');
        if ($id > 0) $rowSystemHtml->load($id);

        if ($id == 0)
            Response::setTitle('添加自定义模块');
        else
            Response::setTitle('编辑自定义模块');

        Response::set('systemHtml', $rowSystemHtml);

        Response::display();
    }


    public function editSave()
    {
        $id = Request::post('id', 0, 'int');

        $rowSystemHtml = Be::getRow('System.html');
        if ($id > 0) $rowSystemHtml->load($id);

        $rowSystemHtml->bind(Request::post());
        $rowSystemHtml->body = Request::post('body', '', 'html');

        if ($rowSystemHtml->save()) {
            $cleanBody = Request::post('body', '', 'html');
            $dir = PATH_DATA . '/System/Html';
            if (!file_exists($dir)) {
                $libFso = Be::getLib('fso');
                $libFso->mkDir($dir);
            }
            file_put_contents($dir . '/' . $rowSystemHtml->class . '.html', $cleanBody);

            if ($id == 0) {
                Response::setMessage('添加自定义模块成功！');
                systemLog('添加自定义模块：' . $rowSystemHtml->name);
            } else {
                Response::setMessage('修改自定义模块成功！');
                systemLog('修改自定义模块：#' . $id . ': ' . $rowSystemHtml->name);
            }
        } else {
            Response::setMessage($rowSystemHtml->getError(), 'error');
        }

        $libHistory = Be::getLib('History');
        $libHistory->back();
    }

    public function checkClass()
    {
        $id = Request::get('id', 0, 'int');
        $class = Request::get('class', '');

        $adminServiceSystemHtml = Be::getService('System.Html');
        echo $adminServiceSystemHtml->isClassAvailable($class, $id) ? 'true' : 'false';
    }

    public function unblock()
    {
        $ids = Request::post('id', '');

        $adminServiceSystemHtml = Be::getService('System.Html');

        if ($adminServiceSystemHtml->unblock($ids)) {
            Response::setMessage('公开自定义模块成功！');
            systemLog('公开自定义模块：#' . $ids);
        } else
            Response::setMessage($adminServiceSystemHtml->getError(), 'error');

        $libHistory = Be::getLib('History');
        $libHistory->back();
    }

    public function block()
    {
        $ids = Request::post('id', '');

        $adminServiceSystemHtml = Be::getService('System.Html');
        if ($adminServiceSystemHtml->block($ids)) {
            Response::setMessage('屏蔽自定义模块成功！');
            systemLog('屏蔽自定义模块：#' . $ids);
        } else
            Response::setMessage($adminServiceSystemHtml->getError(), 'error');

        $libHistory = Be::getLib('History');
        $libHistory->back();
    }

    public function delete()
    {
        $ids = Request::post('id', '');

        $adminServiceSystemHtml = Be::getService('System.Html');
        if ($adminServiceSystemHtml->delete($ids)) {
            Response::setMessage('删除自定义模块成功！');
            systemLog('删除自定义模块：#' . $ids);
        } else
            Response::setMessage($adminServiceSystemHtml->getError(), 'error');

        $libHistory = Be::getLib('History');
        $libHistory->back();
    }
}

?>