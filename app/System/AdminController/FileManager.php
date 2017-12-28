<?php
namespace App\System\AdminController;

use System\Be;
use System\Request;
use System\Response;
use System\Session;

// 文件管理器
class FileManager extends \System\AdminController
{

    public function browser()
    {
        // 要查看的路径
        $path = Request::post('path', '');

        // 显示方式 thumbnail 缩略图 list 详细列表
        $view = Request::post('view', '');

        // 排序
        $sort = Request::post('sort', '');

        // 只显示图像
        $filterImage = Request::get('filterImage', -1, 'int');

        $srcId = Request::get('srcId', '');


        // session 缓存用户选择
        if ($path == '') {
            $sessionPath = session::get('systemFilemanagerPath');
            if ($sessionPath != '') $path = $sessionPath;
        } else {
            if ($path == '/') $path = '';
            session::set('systemFilemanagerPath', $path);
        }

        if ($view == '') {
            $view = 'thumbnail';
            $sessionView = session::get('systemFilemanagerView');
            if ($sessionView != '' && ($sessionView == 'thumbnail' || $sessionView == 'list')) $view = $sessionView;
        } else {
            if ($view != 'thumbnail' && $view != 'list') $view = 'thumbnail';
            session::set('systemFilemanagerView', $view);
        }

        if ($sort == '') {
            $sessionSort = session::get('systemFilemanagerSort');
            if ($sessionSort == '') {
                $sort = 'name';
            } else {
                $sort = $sessionSort;
            }

        } else {
            session::set('systemFilemanagerSort', $sort);
        }

        if ($filterImage == -1) {
            $filterImage = 0;
            $sessionFilterImage = session::get('systemFilemanagerFilterImage', -1);
            if ($sessionFilterImage != -1 && ($sessionFilterImage == 0 || $sessionFilterImage == 1)) $filterImage = $sessionFilterImage;
        } else {
            if ($filterImage != 0 && $filterImage != 1) $filterImage = 0;
            session::set('systemFilemanagerFilterImage', $filterImage);
        }

        if ($srcId == '') {
            $srcId = session::get('systemFilemanagerSrcId', '');
        } elseif ($srcId == 'img') {
            $srcId = '';
            session::set('systemFilemanagerSrcId', $srcId);
        } else {
            session::set('systemFilemanagerSrcId', $srcId);
        }


        $option = array();
        $option['path'] = $path;
        $option['view'] = $view;
        $option['sort'] = $sort;
        $option['filterImage'] = $filterImage;

        $serviceSystemFilemanager = Be::getAdminService('systemFilemanager');
        $files = $serviceSystemFilemanager->getFiles($option);

        $template = Be::getAdminTemplate('systemFilemanager.browser');
        Response::set('path', $path);
        Response::set('view', $view);
        Response::set('sort', $sort);
        Response::set('filterImage', $filterImage);
        Response::set('srcId', $srcId);

        Response::set('files', $files);
        Response::display();
    }

    public function createDir()
    {
        $dirName = Request::post('dirName', '');

        $serviceSystemFilemanager = Be::getAdminService('systemFilemanager');
        if ($serviceSystemFilemanager->createDir($dirName)) {
            Response::setMessage('创建文件夹(' . $dirName . ')成功！');
        } else {
            Response::setMessage($serviceSystemFilemanager->getError(), 'error');
        }

        Response::redirect('./?controller=systemFilemanager&task=browser');
    }

    // 删除文件夹
    public function deleteDir()
    {
        $dirName = Request::get('dirName', '');

        $serviceSystemFilemanager = Be::getAdminService('systemFilemanager');
        if ($serviceSystemFilemanager->deleteDir($dirName)) {
            Response::setMessage('删除文件夹(' . $dirName . ')成功！');
        } else {
            Response::setMessage($serviceSystemFilemanager->getError(), 'error');
        }

        Response::redirect('./?controller=systemFilemanager&task=browser');
    }

    // 修改文件夹名称
    public function editDirName()
    {
        $oldDirName = Request::post('oldDirName', '');
        $newDirName = Request::post('newDirName', '');

        $serviceSystemFilemanager = Be::getAdminService('systemFilemanager');
        if ($serviceSystemFilemanager->editDirName($oldDirName, $newDirName)) {
            Response::setMessage('重命名文件夹成功！');
        } else {
            Response::setMessage($serviceSystemFilemanager->getError(), 'error');
        }

        Response::redirect('./?controller=systemFilemanager&task=browser');
    }


    public function uploadFile()
    {
        $configSystem = Be::getConfig('System.System');

        $return = './?controller=systemFilemanager&task=browser';

        $file = $_FILES['file'];
        if ($file['error'] == 0) {
            $fileName = $file['name'];

            $type = strtolower(substr(strrchr($fileName, '.'), 1));
            if (!in_array($type, $configSystem->allowUploadFileTypes)) {
                Response::setMessage('不允许上传(' . $type . ')格式的文件！', 'error');
                Response::redirect($return);
            }

            if (strpos($fileName, '/') !== false) {
                Response::setMessage('文件名称不合法！', 'error');
                Response::redirect($return);
            }

            $serviceSystemFilemanager = Be::getAdminService('systemFilemanager');
            $absPath = $serviceSystemFilemanager->getAbsPath();
            if ($absPath == false) {
                Response::setMessage($serviceSystemFilemanager->getError(), 'error');
                Response::redirect($return);
            }

            $dstPath = $absPath . DS . $fileName;

            $rename = false;
            if (file_exists($dstPath)) {
                $i = 1;
                $name = substr($fileName, 0, strrpos($fileName, '.'));
                while (file_exists($absPath . DS . $name . '_' . $i . '.' . $type)) {
                    $i++;
                }

                $dstPath = $absPath . DS . $name . '_' . $i . '.' . $type;

                $rename = $name . '_' . $i . '.' . $type;
            }

            if (move_uploaded_file($file['tmpName'], $dstPath)) {
                $watermark = Request::post('watermark', 0, 'int');
                if ($watermark == 1 && in_array($type, $configSystem->allowUploadImageTypes)) {
                    $serviceSystem = Be::getService('System.Admin');
                    $serviceSystem->watermark($dstPath);
                }

                if ($rename == false) {
                    Response::setMessage('上传文件成功！');
                } else {
                    Response::setMessage('有同名文件，新上传的文件已更名为：' . $rename . '！', 'warning');
                }
            } else {
                Response::setMessage('上传失败！', 'error');
            }
        } else {

            $uploadErrors = array(
                '1' => '您上传的文件过大！',
                '2' => '您上传的文件过大！',
                '3' => '文件只有部分被上传！',
                '4' => '没有文件被上传！',
                '5' => '上传的文件大小为 0！'
            );

            $error = '';
            if (array_key_exists($file['error'], $uploadErrors)) {
                $error = $uploadErrors[$file['error']];
            } else {
                $error = '错误代码：' . $file['error'];
            }

            Response::setMessage('上传失败' . '(' . $error . ')', 'error');
        }

        Response::redirect($return);
    }

    // 删除文件
    public function deleteFile()
    {
        $fileName = Request::get('fileName', '');

        $serviceSystemFilemanager = Be::getAdminService('systemFilemanager');
        if ($serviceSystemFilemanager->deleteFile($fileName)) {
            Response::setMessage('删除文件(' . $fileName . ')成功！');
        } else {
            Response::setMessage($serviceSystemFilemanager->getError(), 'error');
        }

        Response::redirect('./?controller=systemFilemanager&task=browser');
    }

    // 修改文件名称
    public function editFileName()
    {
        $oldFileName = Request::post('oldFileName', '');
        $newFileName = Request::post('newFileName', '');

        $serviceSystemFilemanager = Be::getAdminService('systemFilemanager');
        if ($serviceSystemFilemanager->editFileName($oldFileName, $newFileName)) {
            Response::setMessage('重命名文件成功！');
        } else {
            Response::setMessage($serviceSystemFilemanager->getError(), 'error');
        }

        Response::redirect('./?controller=systemFilemanager&task=browser');
    }

    public function downloadFile()
    {
        $fileName = Request::get('fileName', '');

        $serviceSystemFilemanager = Be::getAdminService('systemFilemanager');
        $absFilePath = $serviceSystemFilemanager->getAbsFilePath($fileName);
        if ($absFilePath == false) {
            echo $serviceSystemFilemanager->getError();
        } else {
            header('Pragma: private');
            header('Cache-control: private, must-revalidate');
            header("Content-Type: application/octet-stream");
            header("Content-Length: " . (string)(filesize($absFilePath)));
            header('Content-Disposition: attachment; filename="' . ($fileName) . '"');
            readfile($absFilePath);
        }
        exit;
    }

}

?>