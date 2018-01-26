<?php
namespace App\System\AdminController;

use System\Be;
use System\Request;
use System\Response;
use System\AdminController;

// 公告
class Announcement extends AdminController
{

    /**
     *
     */
    public function announcements()
    {
        $orderBy = Request::post('orderBy', 'id');
        $orderByDir = Request::post('orderByDir', 'DESC');

        $key = Request::post('key', '');
        $status = Request::post('status', -1, 'int');
        $limit = Request::post('limit', -1, 'int');

        if ($limit == -1) {
            $adminConfigSystem = Be::getConfig('System.Admin');
            $limit = $adminConfigSystem->limit;
        }

        $adminServiceSystemAnnouncement = Be::getService('System.Announcement');
        Response::setTitle('公告');

        $option = array('key' => $key, 'status' => $status);

        $pagination = Be::getUi('Pagination');
        $pagination->setLimit($limit);
        $pagination->setTotal($adminServiceSystemAnnouncement->getSystemAnnouncementCount($option));
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

        $systemAnnouncements = $adminServiceSystemAnnouncement->getSystemAnnouncements($option);
        Response::set('systemAnnouncements', $systemAnnouncements);

        Response::display();

        $libHistory = Be::getLib('History');
        $libHistory->save();
    }


    public function edit()
    {
        $id = Request::post('id', 0, 'int');

        $rowSystemAnnouncement = Be::getRow('System.announcement');
        if ($id > 0) $rowSystemAnnouncement->load($id);

        if ($id == 0)
            Response::setTitle('添加公告');
        else
            Response::setTitle('编辑公告');

        Response::set('systemAnnouncement', $rowSystemAnnouncement);

        Response::display();
    }


    public function editSave()
    {
        $id = Request::post('id', 0, 'int');

        $rowSystemAnnouncement = Be::getRow('System.announcement');
        if ($id > 0) $rowSystemAnnouncement->load($id);

        $rowSystemAnnouncement->bind(Request::post());

        $rowSystemAnnouncement->createTime = strtotime($rowSystemAnnouncement->createTime);
        $rowSystemAnnouncement->body = Request::post('body', '', 'html');

        if ($rowSystemAnnouncement->save()) {
            if ($id == 0) {
                Response::setMessage('添加公告成功！');
                systemLog('添加公告：' . $rowSystemAnnouncement->title);
            } else {
                Response::setMessage('修改公告成功！');
                systemLog('修改公告：' . $rowSystemAnnouncement->title);
            }
        } else {
            Response::setMessage($rowSystemAnnouncement->getError(), 'error');
        }

        $libHistory = Be::getLib('History');
        $libHistory->back();
    }


    public function unblock()
    {
        $ids = Request::post('id', '');

        $adminServiceSystemAnnouncement = Be::getService('System.Announcement');
        if ($adminServiceSystemAnnouncement->unblock($ids)) {
            Response::setMessage('公开公告成功！');
            systemLog('公开公告：#' . $ids);
        } else
            Response::setMessage($adminServiceSystemAnnouncement->getError(), 'error');

        $libHistory = Be::getLib('History');
        $libHistory->back();
    }

    public function block()
    {
        $ids = Request::post('id', '');

        $adminServiceSystemAnnouncement = Be::getService('System.Announcement');
        if ($adminServiceSystemAnnouncement->block($ids)) {
            Response::setMessage('屏蔽公告成功！');
            systemLog('屏蔽公告：' . $ids);
        } else
            Response::setMessage($adminServiceSystemAnnouncement->getError(), 'error');

        $libHistory = Be::getLib('History');
        $libHistory->back();
    }

    public function delete()
    {
        $ids = Request::post('id', '');

        $adminServiceSystemAnnouncement = Be::getService('System.Announcement');
        if ($adminServiceSystemAnnouncement->delete($ids)) {
            Response::setMessage('删除公告成功！');
            systemLog('删除公告：' . $ids);
        } else
            Response::setMessage($adminServiceSystemAnnouncement->getError(), 'error');

        $libHistory = Be::getLib('History');
        $libHistory->back();
    }


}