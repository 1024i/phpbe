<?php
namespace App\Cms\Controller;

use System\Be;
use System\Request;
use System\Response;
use System\AdminController;

class Article extends AdminController
{

    public function articles()
    {
        $orderBy = Request::post('orderBy', 'id');
        $orderByDir = Request::post('orderByDir', 'ASC');
        $categoryId = Request::post('categoryId', -1, 'int');
        $key = Request::post('key', '');
        $status = Request::post('status', -1, 'int');
        $limit = Request::post('limit', -1, 'int');

        if ($limit == -1) {
            $adminConfigSystem = Be::getConfig('System.admin');
            $limit = $adminConfigSystem->limit;
        }

        $adminServiceArticle = Be::getService('Cms.Article');
        Response::setTitle('文章列表');

        $option = array('categoryId' => $categoryId, 'key' => $key, 'status' => $status);

        $pagination = Be::getUi('Pagination');
        $pagination->setLimit($limit);
        $pagination->setTotal($adminServiceArticle->getArticleCount($option));
        $pagination->setPage(Request::post('page', 1, 'int'));

        Response::set('pagination', $pagination);
        Response::set('orderBy', $orderBy);
        Response::set('orderByDir', $orderByDir);
        Response::set('categoryId', $categoryId);
        Response::set('key', $key);
        Response::set('status', $status);

        $option['orderBy'] = $orderBy;
        $option['orderByDir'] = $orderByDir;
        $option['offset'] = $pagination->getOffset();
        $option['limit'] = $limit;

        $articles = $adminServiceArticle->getArticles($option);
        foreach ($articles as $article) {
            $article->commentCount = $adminServiceArticle->getCommentCount(array('articleId' => $article->id));
        }
        Response::set('articles', $articles);

        $serviceArticle = Be::getService('Cms.Article');
        Response::set('categories', $serviceArticle->getCategories());

        Response::display();

        $libHistory = Be::getLib('History');
        $libHistory->save();
    }


    public function edit()
    {
        $id = Request::post('id', 0, 'int');

        $rowArticle = Be::getRow('Cms.article');
        $rowArticle->load($id);

        if ($id == 0) {
            Response::setTitle('添加文章');
        } else {
            Response::setTitle('编辑文章');
        }
        Response::set('article', $rowArticle);

        $serviceArticle = Be::getService('Cms.Article');
        $categories = $serviceArticle->getCategories();
        Response::set('categories', $categories);
        Response::display();
    }


    public function editSave()
    {
        $id = Request::post('id', 0, 'int');

        $my = Be::getAdminUser();

        $rowArticle = Be::getRow('Cms.article');
        if ($id != 0) $rowArticle->load($id);
        $rowArticle->bind(Request::post());

        $rowArticle->createTime = strtotime($rowArticle->createTime);

        $body = Request::post('body', '', 'html');

        $configSystem = Be::getConfig('System.System');

        // 找出内容中的所有图片
        $images = array();

        $imageTypes = implode('|', $configSystem->allowUploadImageTypes);
        preg_match_all("/src=[\\\|\"|'|\s]{0,}(http:\/\/([^>]*)\.($imageTypes))/isU", $body, $images);
        $images = array_unique($images[1]);

        // 过滤掉本服务器上的图片
        $remoteImages = array();
        if (count($images) > 0) {
            $beUrlLen = strlen(URL_ROOT);
            foreach ($images as $image) {
                if (substr($image, 0, $beUrlLen) != URL_ROOT) {
                    $remoteImages[] = $image;
                }
            }
        }

        $thumbnailSource = Request::post('thumbnailSource', ''); // upload：上传缩图图 / url：从指定网址获取缩图片
        $thumbnailPickUp = Request::post('thumbnailPickUp', 0, 'int'); // 是否提取第一张图作为缩略图
        $downloadRemoteImage = Request::post('downloadRemoteImage', 0, 'int'); // 是否下载远程图片
        $downloadRemoteImageWatermark = Request::post('downloadRemoteImageWatermark', 0, 'int'); // 是否下截远程图片添加水印

        // 下载远程图片
        if ($downloadRemoteImage == 1) {
            if (count($remoteImages) > 0) {
                $libHttp = Be::getLib('Http');

                // 下载到本地的文件夹
                $dirName = date('Y-m-d');
                $dirPath = PATH_DATA . DS . 'Cms' . DS . 'Article' . DS . $dirName;

                // 文件夹不存在时自动创建
                if (!file_exists($dirPath)) {
                    $libFso = Be::getLib('fso');
                    $libFso->mkDir($dirPath);
                }

                $t = date('YmdHis');
                $i = 0;
                foreach ($remoteImages as $remoteImage) {
                    $localImageName = $t . $i . '.' . strtolower(substr(strrchr($remoteImage, '.'), 1));
                    $data = $libHttp->get($remoteImage);

                    file_put_contents($dirPath . DS . $localImageName, $data);

                    // 下截远程图片添加水印
                    if ($downloadRemoteImageWatermark == 1) {
                        $serviceSystem = Be::getService('System.Admin');
                        $serviceSystem->watermark($dirPath . DS . $localImageName);
                    }

                    $body = str_replace($remoteImage, URL_ROOT . '/' . DATA . '/Article/' . $dirName . '/' . $localImageName, $body);
                    $i++;
                }
            }
        }
        $rowArticle->body = $body;

        $configArticle = Be::getConfig('Cms.Article');

        // 提取第一张图作为缩略图
        if ($thumbnailPickUp == 1) {
            if (count($images) > 0) {
                $libHttp = Be::getLib('Http');
                $data = $libHttp->get($images[0]);

                if ($data != false) {
                    $tmpImage = PATH_DATA . DS . 'Tmp' . DS . date('YmdHis') . '.' . strtolower(substr(strrchr($images[0], '.'), 1));
                    file_put_contents($tmpImage, $data);

                    $libImage = Be::getLib('image');
                    $libImage->open($tmpImage);

                    if ($libImage->isImage()) {
                        $t = date('YmdHis');
                        $dir = PATH_DATA . DS . 'Cms' . DS . 'Article' . DS . 'Thumbnail';
                        if (!file_exists($dir)) {
                            $libFso = Be::getLib('fso');
                            $libFso->mkDir($dir);
                        }

                        $thumbnailLName = $t . 'L.' . $libImage->getType();
                        $libImage->resize($configArticle->thumbnailLW, $configArticle->thumbnailLH, 'scale');
                        $libImage->save($dir . DS . $thumbnailLName);
                        $rowArticle->thumbnailL = $thumbnailLName;

                        $thumbnailMName = $t . 'M.' . $libImage->getType();
                        $libImage->resize($configArticle->thumbnailMW, $configArticle->thumbnailMH, 'scale');
                        $libImage->save($dir . DS . $thumbnailMName);
                        $rowArticle->thumbnailM = $thumbnailMName;

                        $thumbnailSName = $t . 'S.' . $libImage->getType();
                        $libImage->resize($configArticle->thumbnailSW, $configArticle->thumbnailSH, 'scale');
                        $libImage->save($dir . DS . $thumbnailSName);
                        $rowArticle->thumbnailS = $thumbnailSName;
                    }

                    @unlink($tmpImage);
                }
            }
        } else {
            // 上传缩图图
            if ($thumbnailSource == 'upload') {
                $thumbnailUpload = $_FILES['thumbnailUpload'];
                if ($thumbnailUpload['error'] == 0) {
                    $libImage = Be::getLib('image');
                    $libImage->open($thumbnailUpload['tmpName']);
                    if ($libImage->isImage()) {
                        $t = date('YmdHis');
                        $dir = PATH_DATA . DS . 'Cms' . DS . 'Article' . DS . 'Thumbnail';
                        if (!file_exists($dir)) {
                            $libFso = Be::getLib('fso');
                            $libFso->mkDir($dir);
                        }

                        $thumbnailLName = $t . 'L.' . $libImage->getType();
                        $libImage->resize($configArticle->thumbnailLW, $configArticle->thumbnailLH, 'scale');
                        $libImage->save($dir . DS . $thumbnailLName);
                        $rowArticle->thumbnailL = $thumbnailLName;

                        $thumbnailMName = $t . 'M.' . $libImage->getType();
                        $libImage->resize($configArticle->thumbnailMW, $configArticle->thumbnailMH, 'scale');
                        $libImage->save($dir . DS . $thumbnailMName);
                        $rowArticle->thumbnailM = $thumbnailMName;

                        $thumbnailSName = $t . 'S.' . $libImage->getType();
                        $libImage->resize($configArticle->thumbnailSW, $configArticle->thumbnailSH, 'scale');
                        $libImage->save($dir . DS . $thumbnailSName);
                        $rowArticle->thumbnailS = $thumbnailSName;
                    }
                }
            } elseif ($thumbnailSource == 'url') { // 从指定网址获取缩图片
                $thumbnailUrl = Request::post('thumbnailUrl', '');
                if ($thumbnailUrl != '' && substr($thumbnailUrl, 0, 7) == 'http://') {
                    $libHttp = Be::getLib('Http');
                    $data = $libHttp->get($thumbnailUrl);

                    if ($data != false) {
                        $tmpImage = PATH_DATA . DS . 'Tmp' . DS . date('YmdHis') . '.' . strtolower(substr(strrchr($thumbnailUrl, '.'), 1));
                        file_put_contents($tmpImage, $data);

                        $libImage = Be::getLib('image');
                        $libImage->open($tmpImage);

                        if ($libImage->isImage()) {
                            $t = date('YmdHis');
                            $dir = PATH_DATA . DS . 'Cms' . DS . 'Article' . DS . 'Thumbnail';
                            if (!file_exists($dir)) {
                                $libFso = Be::getLib('fso');
                                $libFso->mkDir($dir);
                            }

                            $thumbnailLName = $t . 'L.' . $libImage->getType();
                            $libImage->resize($configArticle->thumbnailLW, $configArticle->thumbnailLH, 'scale');
                            $libImage->save($dir . DS . $thumbnailLName);
                            $rowArticle->thumbnailL = $thumbnailLName;

                            $thumbnailMName = $t . 'M.' . $libImage->getType();
                            $libImage->resize($configArticle->thumbnailMW, $configArticle->thumbnailMH, 'scale');
                            $libImage->save($dir . DS . $thumbnailMName);
                            $rowArticle->thumbnailM = $thumbnailMName;

                            $thumbnailSName = $t . 'S.' . $libImage->getType();
                            $libImage->resize($configArticle->thumbnailSW, $configArticle->thumbnailSH, 'scale');
                            $libImage->save($dir . DS . $thumbnailSName);
                            $rowArticle->thumbnailS = $thumbnailSName;
                        }

                        @unlink($tmpImage);
                    }
                }
            }
        }


        if ($id == 0) {
            $rowArticle->createById = $my->id;
        } else {
            $rowArticle->modifyTime = time();
            $rowArticle->modifyById = $my->id;
        }

        if ($rowArticle->save()) {
            if ($id == 0) {
                Response::setMessage('添加文章成功！');
                systemLog('添加文章：#' . $rowArticle->id . ': ' . $rowArticle->title);
            } else {
                Response::setMessage('修改文章成功！');
                systemLog('编辑文章：#' . $id . ': ' . $rowArticle->title);
            }
        } else {
            Response::setMessage($rowArticle->getError(), 'error');
        }

        $libHistory = Be::getLib('History');
        $libHistory->back();
    }


    public function unblock()
    {
        $ids = Request::post('id', '');

        $serviceArticle = Be::getService('Cms.Article');
        if ($serviceArticle->unblock($ids)) {
            Response::setMessage('公开文章成功！');
            systemLog('公开文章：#' . $ids);
        } else {
            Response::setMessage($serviceArticle->getError(), 'error');
        }

        $libHistory = Be::getLib('History');
        $libHistory->back();
    }

    public function block()
    {
        $ids = Request::post('id', '');

        $serviceArticle = Be::getService('Cms.Article');
        if ($serviceArticle->block($ids)) {
            Response::setMessage('屏蔽文章成功！');
            systemLog('屏蔽文章：#' . $ids);
        } else {
            Response::setMessage($serviceArticle->getError(), 'error');
        }

        $libHistory = Be::getLib('History');
        $libHistory->back();
    }

    public function delete()
    {
        $ids = Request::post('id', '');

        $serviceArticle = Be::getService('Cms.Article');
        if ($serviceArticle->delete($ids)) {
            Response::setMessage('删除文章成功！');
            systemLog('删除文章：#' . $ids);
        } else {
            Response::setMessage($serviceArticle->getError(), 'error');
        }

        $libHistory = Be::getLib('History');
        $libHistory->back();
    }


    private function cleanHtml($html)
    {
        if (get_magic_quotes_gpc()) $html = stripslashes($html);
        $html = trim($html);
        $html = strip_tags($html);
        $html = str_replace(array('&nbsp;', '&ldquo;', '&rdquo;', '　'), '', $html);

        $html = preg_replace("/\t/", "", $html);
        $html = preg_replace("/\r\n/", "", $html);
        $html = preg_replace("/\r/", "", $html);
        $html = preg_replace("/\n/", "", $html);
        $html = preg_replace("/ /", "", $html);
        return $html;
    }

    // 从内容中提取摘要
    public function ajaxGetSummary()
    {
        $body = $this->cleanHtml($_POST['body']);

        $configArticle = Be::getConfig('Cms.Article');

        Response::set('error', 0);
        Response::set('summary', limit($body, intval($configArticle->getSummary)));
        Response::ajax();
    }


    // 从内容中提取 META 关键字
    public function ajaxGetMetaKeywords()
    {
        $body = $this->cleanHtml($_POST['body']);

        $configArticle = Be::getConfig('Cms.Article');

        $libScws = Be::getLib('scws');
        $libScws->sendText($body);
        $scwsKeywords = $libScws->getTops(intval($configArticle->getMetaKeywords));
        $metaKeywords = '';
        if ($scwsKeywords !== false) {
            $tmpMetaKeywords = array();
            foreach ($scwsKeywords as $scwsKeyword) {
                $tmpMetaKeywords[] = $scwsKeyword['word'];
            }
            $metaKeywords = implode(' ', $tmpMetaKeywords);
        }

        Response::set('error', 0);
        Response::set('metaKeywords', $metaKeywords);
        Response::ajax();
    }

    // 从内容中提取 META 描述
    public function ajaxGetMetaDescription()
    {
        $body = $this->cleanHtml($_POST['body']);

        $configArticle = Be::getConfig('Cms.Article');

        Response::set('error', 0);
        Response::set('metaDescription', limit($body, intval($configArticle->getMetaDescription)));
        Response::ajax();
    }

    public function comments()
    {
        $orderBy = Request::post('orderBy', 'createTime');
        $orderByDir = Request::post('orderByDir', 'DESC');
        $articleId = Request::post('articleId', 0, 'int');
        $key = Request::post('key', '');
        $status = Request::post('status', -1, 'int');
        $limit = Request::post('limit', -1, 'int');

        if ($limit == -1) {
            $adminConfigSystem = Be::getConfig('System.admin');
            $limit = $adminConfigSystem->limit;
        }

        $adminServiceArticle = Be::getService('Cms.Article');
        Response::setTitle('评论列表');

        $option = array('articleId' => $articleId, 'key' => $key, 'status' => $status);

        $pagination = Be::getUi('Pagination');
        $pagination->setLimit($limit);
        $pagination->setTotal($adminServiceArticle->getCommentCount($option));
        $pagination->setPage(Request::post('page', 1, 'int'));

        Response::set('pagination', $pagination);
        Response::set('orderBy', $orderBy);
        Response::set('orderByDir', $orderByDir);
        Response::set('key', $key);
        Response::set('status', $status);

        Response::set('articleId', $articleId);

        $option['orderBy'] = $orderBy;
        $option['orderByDir'] = $orderByDir;
        $option['offset'] = $pagination->getOffset();
        $option['limit'] = $limit;

        $articles = array();
        $comments = $adminServiceArticle->getComments($option);
        foreach ($comments as $comment) {
            if (!array_key_exists($comment->articleId, $articles)) {
                $rowArticle = Be::getRow('Cms.article');
                $rowArticle->load($comment->articleId);
                $articles[$comment->articleId] = $rowArticle;
            }

            $comment->article = $articles[$comment->articleId];
        }

        Response::set('comments', $comments);
        Response::display();

        $libHistory = Be::getLib('History');
        $libHistory->save();
    }

    public function commentsUnblock()
    {
        $ids = Request::post('id', '');

        $model = Be::getService('Cms.Article');

        if ($model->commentsUnblock($ids)) {
            Response::setMessage('公开评论成功！');
            systemLog('公开文章评论：#' . $ids);
        } else {
            Response::setMessage($model->getError(), 'error');
        }

        $libHistory = Be::getLib('History');
        $libHistory->back();
    }

    public function commentsBlock()
    {
        $ids = Request::post('id', '');

        $model = Be::getService('Cms.Article');
        if ($model->commentsBlock($ids)) {
            Response::setMessage('屏蔽评论成功！');
            systemLog('屏蔽文章评论：#' . $ids);
        } else {
            Response::setMessage($model->getError(), 'error');
        }

        $libHistory = Be::getLib('History');
        $libHistory->back();
    }

    public function commentsDelete()
    {
        $ids = Request::post('id', '');

        $model = Be::getService('Cms.Article');
        if ($model->commentsDelete($ids)) {
            Response::setMessage('删除评论成功！');
            systemLog('删除文章评论：#' . $ids . ')');
        } else {
            Response::setMessage($model->getError(), 'error');
        }

        $libHistory = Be::getLib('History');
        $libHistory->back();
    }

}
