<?php
namespace App\System\AdminController;

use System\Be;
use System\Request;
use System\Response;
use System\AdminController;

// 友情链接
class Link extends AdminController
{

    public function links()
    {
        $orderBy = Request::post('orderBy', 'ordering');
        $orderByDir = Request::post('orderByDir', 'ASC');

        $key = Request::post('key', '');
        $status = Request::post('status', -1, 'int');
        $limit = Request::post('limit', -1, 'int');

        if ($limit == -1) {
            $adminConfigSystem = Be::getConfig('System.Admin');
            $limit = $adminConfigSystem->limit;
        }

        $adminServiceSystemLink = Be::getService('System.Link');
        Response::setTitle('友情链接');

        $option = array('key' => $key, 'status' => $status);

        $pagination = Be::getUi('Pagination');
        $pagination->setLimit($limit);
        $pagination->setTotal($adminServiceSystemLink->getSystemLinkCount($option));
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

        $systemLinks = $adminServiceSystemLink->getSystemLinks($option);
        Response::set('systemLinks', $systemLinks);

        Response::display();

        $libHistory = Be::getLib('History');
        $libHistory->save();
    }


    public function edit()
    {
        $id = Request::post('id', 0, 'int');

        $rowSystemLink = Be::getRow('System.link');
        if ($id > 0) $rowSystemLink->load($id);

        if ($id == 0)
            Response::setTitle('添加友情链接');
        else
            Response::setTitle('编辑友情链接');

        Response::set('systemLink', $rowSystemLink);

        Response::display();
    }


    public function editSave()
    {
        $id = Request::post('id', 0, 'int');

        $rowSystemLink = Be::getRow('System.link');
        if ($id > 0) $rowSystemLink->load($id);

        $rowSystemLink->bind(Request::post());

        if ($rowSystemLink->save()) {
            $adminServiceSystemLink = Be::getService('System.Link');
            $adminServiceSystemLink->update();

            if ($id == 0) {
                Response::setMessage('添加友情链接成功！');
                systemLog('添加友情链接：' . $rowSystemLink->name);
            } else {
                Response::setMessage('修改友情链接成功！');
                systemLog('修改友情链接：' . $rowSystemLink->name);
            }
        } else {
            Response::setMessage($rowSystemLink->getError(), 'error');
        }

        $libHistory = Be::getLib('History');
        $libHistory->back();
    }


    public function unblock()
    {
        $ids = Request::post('id', '');

        $adminServiceSystemLink = Be::getService('System.Link');
        if ($adminServiceSystemLink->unblock($ids)) {
            $adminServiceSystemLink->update();

            Response::setMessage('公开友情链接成功！');
            systemLog('公开友情链接：#' . $ids);
        } else
            Response::setMessage($adminServiceSystemLink->getError(), 'error');

        $libHistory = Be::getLib('History');
        $libHistory->back();
    }

    public function block()
    {
        $ids = Request::post('id', '');

        $adminServiceSystemLink = Be::getService('System.Link');
        if ($adminServiceSystemLink->block($ids)) {
            $adminServiceSystemLink->update();

            Response::setMessage('屏蔽友情链接成功！');
            systemLog('屏蔽友情链接：#' . $ids);
        } else
            Response::setMessage($adminServiceSystemLink->getError(), 'error');

        $libHistory = Be::getLib('History');
        $libHistory->back();
    }

    public function delete()
    {
        $ids = Request::post('id', '');

        $adminServiceSystemLink = Be::getService('System.Link');
        if ($adminServiceSystemLink->delete($ids)) {
            $adminServiceSystemLink->update();

            Response::setMessage('删除友情链接成功！');
            systemLog('删除友情链接：#' . $ids);
        } else
            Response::setMessage($adminServiceSystemLink->getError(), 'error');

        $libHistory = Be::getLib('History');
        $libHistory->back();
    }


}

