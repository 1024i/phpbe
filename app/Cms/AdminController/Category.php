<?php
namespace App\Cms\Controller;

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
        $serviceArticle = Be::getService('Cms.Article');

        Response::setTitle('分类管理');
        Response::set('categories', $serviceArticle->getCategories());
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
            $rowUser = Be::getRow('System.user');
            $rowUser->load(1);
            if (count($ids)) {
                for ($i = 0, $n = count($ids); $i < $n; $i++) {
                    if (!$ids[$i] && !$names[$i]) continue;

                    $rowArticleCategory = Be::getRow('Cms.Category');
                    $rowArticleCategory->id = $ids[$i];
                    $rowArticleCategory->parentId = $parentIds[$i];
                    $rowArticleCategory->name = $names[$i];
                    $rowArticleCategory->ordering = $i;
                    $rowArticleCategory->save();
                }
            }
            $db->commit();

            systemLog('修改文章分类信息');

            Response::setMessage('保存分类成功！');
            Response::redirect('./?app=Cms&controller=Article&task=categories');

        } catch (Exception $e) {
            $db->rollback();

            Log::log($e);

            Response::setMessage('保存分类失败：'.$e->getMessage());
            Response::redirect('./?app=Cms&controller=Article&task=categories');
        }
    }

    public function ajaxDeleteCategory()
    {
        $categoryId = Request::post('id', 0, 'int');
        if (!$categoryId) {
            Response::set('error', 1);
            Response::set('message', '参数(id)缺失！');
        } else {
            $rowCategory = Be::getRow('Cms.category');
            $rowCategory->load($categoryId);

            $serviceArticle = Be::getService('Cms.Article');
            if ($serviceArticle->deleteCategory($categoryId)) {
                Response::set('error', 0);
                Response::set('message', '分类删除成功！');

                systemLog('删除文章分类：#' . $categoryId . ': ' . $rowCategory->title);
            } else {
                Response::set('error', 2);
                Response::set('message', $serviceArticle->getError());
            }
        }
        Response::ajax();
    }



    public function setting()
    {
        Response::setTitle('设置文章系统参数');
        Response::set('configArticle', Be::getConfig('Cms.Article'));
        Response::display();
    }

    public function settingSave()
    {
        $configArticle = Be::getConfig('Cms.Article');

        $configArticle->getSummary = Request::post('getSummary', 0, 'int');
        $configArticle->getMetaKeywords = Request::post('getMetaKeywords', 0, 'int');
        $configArticle->getMetaDescription = Request::post('getMetaDescription', 0, 'int');
        $configArticle->downloadRemoteImage = Request::post('downloadRemoteImage', 0, 'int');
        $configArticle->comment = Request::post('comment', 0, 'int');
        $configArticle->commentPublic = Request::post('commentPublic', 0, 'int');

        $configArticle->thumbnailLW = Request::post('thumbnailLW', 0, 'int');
        $configArticle->thumbnailLH = Request::post('thumbnailLH', 0, 'int');
        $configArticle->thumbnailMW = Request::post('thumbnailMW', 0, 'int');
        $configArticle->thumbnailMH = Request::post('thumbnailMH', 0, 'int');
        $configArticle->thumbnailSW = Request::post('thumbnailSW', 0, 'int');
        $configArticle->thumbnailSH = Request::post('thumbnailSH', 0, 'int');

        // 缩图图大图
        $defaultThumbnailL = $_FILES['defaultThumbnailL'];
        if ($defaultThumbnailL['error'] == 0) {
            $libImage = Be::getLib('image');
            $libImage->open($defaultThumbnailL['tmpName']);
            if ($libImage->isImage()) {
                $defaultThumbnailLName = date('YmdHis') . 'L.' . $libImage->getType();
                $defaultThumbnailLPath = PATH_DATA . DS . 'Cms' . DS . 'Article' . DS . 'Thumbnail' . DS . 'Default' . DS . $defaultThumbnailLName;
                if (move_uploaded_file($defaultThumbnailL['tmpName'], $defaultThumbnailLPath)) {
                    // @unlink(PATH_DATA.DS.'article'.DS.'thumbnail'.DS.'default'.DS.$configArticle->defaultThumbnailL);
                    $configArticle->defaultThumbnailL = $defaultThumbnailLName;
                }
            }
        }

        // 缩图图中图
        $defaultThumbnailM = $_FILES['defaultThumbnailM'];
        if ($defaultThumbnailM['error'] == 0) {
            $libImage = Be::getLib('image');
            $libImage->open($defaultThumbnailM['tmpName']);
            if ($libImage->isImage()) {
                $defaultThumbnailMName = date('YmdHis') . 'M.' . $libImage->getType();
                $defaultThumbnailMPath = PATH_DATA . DS . 'Cms' . DS . 'Article' . DS . 'Thumbnail' . DS . 'Default' . DS . $defaultThumbnailMName;
                if (move_uploaded_file($defaultThumbnailM['tmpName'], $defaultThumbnailMPath)) {
                    // @unlink(PATH_DATA.DS.'article'.DS.'thumbnail'.DS.'default'.DS.$configArticle->defaultThumbnailM);
                    $configArticle->defaultThumbnailM = $defaultThumbnailMName;
                }
            }
        }

        // 缩图图小图
        $defaultThumbnailS = $_FILES['defaultThumbnailS'];
        if ($defaultThumbnailS['error'] == 0) {
            $libImage = Be::getLib('image');
            $libImage->open($defaultThumbnailS['tmpName']);
            if ($libImage->isImage()) {
                $defaultThumbnailSName = date('YmdHis') . 'S.' . $libImage->getType();
                $defaultThumbnailSPath = PATH_DATA . DS . 'Cms' . DS . 'Article' . DS . 'Thumbnail' . DS . 'Default' . DS . $defaultThumbnailSName;
                if (move_uploaded_file($defaultThumbnailS['tmpName'], $defaultThumbnailSPath)) {
                    // @unlink(PATH_DATA.DS.'article'.DS.'thumbnail'.DS.'default'.DS.$configArticle->defaultThumbnailS);
                    $configArticle->defaultThumbnailS = $defaultThumbnailSName;
                }
            }
        }

        $serviceSystem = Be::getService('System.Admin');
        $serviceSystem->updateConfig($configArticle, PATH_ROOT . DS . 'Configs' . DS . 'Article.php');

        systemLog('设置文章系统参数');

        Response::setMessage('设置成功！');
        Response::redirect('./?app=Cms&controller=Article&task=setting');
    }

}
