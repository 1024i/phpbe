<?php
namespace App\Cms\AdminController;

use System\Be;
use System\Db\Exception;
use System\Log;
use System\Request;
use System\Response;
use System\AdminController;

class Category extends AdminController
{


    public function categories()
    {
        $serviceCategory = Be::getService('Cms.Category');

        Response::setTitle('分类管理');
        Response::set('categories', $serviceCategory->getCategories());
        Response::display();
    }

    public function saveCategories()
    {
        $ids = Request::post('id', array(), 'int');
        $parentIds = Request::post('parentId', array(), 'int');
        $names = Request::post('name', array());

        $db = Be::getDb();
        $db->startTransaction();

        try {
            $rowUser = Be::getRow('System.User');
            $rowUser->load(1);
            if (count($ids)) {
                for ($i = 0, $n = count($ids); $i < $n; $i++) {
                    if (!$ids[$i] && !$names[$i]) continue;

                    $rowCategory = Be::getRow('Cms.Category');
                    $rowCategory->id = $ids[$i];
                    $rowCategory->parent_id = $parentIds[$i];
                    $rowCategory->name = $names[$i];
                    $rowCategory->ordering = $i;
                    $rowCategory->save();
                }
            }
            $db->commit();

            systemLog('修改文章分类信息');

            Response::setMessage('保存分类成功！');
            Response::redirect(adminUrl('app=Cms&controller=Article&task=categories'));

        } catch (Exception $e) {
            $db->rollback();

            Log::log($e);

            Response::setMessage('保存分类失败：'.$e->getMessage());
            Response::redirect(adminUrl('app=Cms&controller=Article&task=categories'));
        }
    }

    public function ajaxDeleteCategory()
    {
        $categoryId = Request::post('id', 0, 'int');
        if (!$categoryId) {
            Response::set('error', 1);
            Response::set('message', '参数(id)缺失！');
        } else {
            $rowCategory = Be::getRow('Cms.Category');
            $rowCategory->load($categoryId);

            $serviceCategory = Be::getService('Cms.Category');
            if ($serviceCategory->deleteCategory($categoryId)) {
                Response::set('error', 0);
                Response::set('message', '分类删除成功！');

                systemLog('删除文章分类：#' . $categoryId . ': ' . $rowCategory->title);
            } else {
                Response::set('error', 2);
                Response::set('message', $serviceCategory->getError());
            }
        }
        Response::ajax();
    }

}
