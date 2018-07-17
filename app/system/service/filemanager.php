<?php
namespace App\System\Service;

use Phpbe\System\Be;
use Phpbe\System\Session;
use Phpbe\System\Service;
use Phpbe\System\Service\ServiceException;

/**
 */
class FileManager extends Service
{

    public function getFiles($option = array())
    {
        $absPath = $this->getAbsPath($option['path']);
        if ($absPath == false) $absPath = Be::getRuntime()->getPathData();

        $return = array();

        $configSystem = Be::getConfig('System', 'System');

        // 分析目录
        $files = scandir($absPath);
        foreach ($files as $x => $name) {
            if ($name == "." || $name == "..") continue;

            $itemPath = $absPath . '/' . $name;

            $size = 0;
            $type = 'dir';
            if (!is_dir($itemPath)) {
                $size = $this->formatSize(filesize($itemPath));
                $type = strtolower(substr(strrchr($name, '.'), 1));
            }

            // 是否只显示图像文件，插入图像时使用
            $filter = false;
            if ($option['filterImage'] == 1 && $type != 'dir' && !in_array($type, $configSystem->allowUploadImageTypes)) $filter = true;

            if (!$filter) $return[$x] = array('name' => $name, 'date' => filemtime($itemPath), 'size' => $size, 'type' => $type);
        }

        return $return;
    }


    public function createDir($dirName, $path = null)
    {
        $absPath = $this->getAbsPath($path);
        if ($absPath == false) return false;

        if (strpos($dirName, '/') !== false) {
            throw new ServiceException('文件夹名称不合法！');
        }

        $dirPath = $absPath . '/' . $dirName;
        if (file_exists($dirPath)) {
            throw new ServiceException('已存在名称为 ' . $dirName . ' 的文件夹！');
        }

        mkdir($dirPath, 0777, true);

        return true;
    }

    public function deleteDir($dirName, $path = null)
    {
        $absDirPath = $this->getAbsDirPath($dirName, $path);
        if ($absDirPath == false) return false;

        $libFso = Be::getLib('Fso');
        $libFso->rmDir($absDirPath);

        return true;
    }

    public function editDirName($oldDirName, $newDirName, $path = null)
    {
        $absPath = $this->getAbsPath($path);
        if ($absPath == false) return false;

        if (strpos($oldDirName, '/') !== false || strpos($newDirName, '/') !== false) {
            throw new ServiceException('文件夹名称不合法！');
        }

        $srcPath = $absPath . '/' . $oldDirName;
        if (!file_exists($srcPath)) {
            throw new ServiceException('文件夹 ' . $oldDirName . ' 不存在！');
        }

        $dstPath = $absPath . '/' . $newDirName;
        if (file_exists($dstPath)) {
            throw new ServiceException('已存在名称为 ' . $newDirName . ' 的文件夹！');
        }

        if (!rename($srcPath, $dstPath)) {
            throw new ServiceException('重命名文件夹失败！');
        }

        return true;
    }


    public function deleteFile($fileName, $path = null)
    {
        $absFilePath = $this->getAbsFilePath($fileName, $path);
        if ($absFilePath == false) return false;

        if (!unlink($absFilePath)) {
            throw new ServiceException('删除文件失败，请检查是否有权限！');
        }

        return true;
    }


    public function editFileName($oldFileName, $newFileName, $path = null)
    {
        $absPath = $this->getAbsPath($path);
        if ($absPath == false) return false;

        if (strpos($oldFileName, '/') !== false || strpos($newFileName, '/') !== false) {
            throw new ServiceException('文件名称不合法！');
        }

        $srcPath = $absPath . '/' . $oldFileName;
        if (!file_exists($srcPath)) {
            throw new ServiceException('文件 ' . $oldFileName . ' 不存在！');
        }

        $type = strtolower(substr(strrchr($newFileName, '.'), 1));
        $config = Be::getConfig('System', 'System');
        if (!in_array($type, $config->allowUploadFileTypes)) {
            throw new ServiceException('不允许的文件格式！');
        }

        $dstPath = $absPath . '/' . $newFileName;
        if (file_exists($dstPath)) {
            throw new ServiceException('文件 ' . $newFileName . ' 已存在！');
        }

        if (!rename($srcPath, $dstPath)) {
            throw new ServiceException('修改文件名失败，请检查名称是否合法！');
        }

        return true;
    }


    public function getAbsPath($path = null)
    {
        if ($path == null) $path = Session::get('systemFileManagerPath');

        // 禁止用户查看其它目录
        if (strpos($path, './') != false) {
            throw new ServiceException('路径不合法！');
        }

        if (substr($path, -1, 1) == '/') {
            throw new ServiceException('路径不合法！');
        }

        // 绝对路径
        $absPath = Be::getRuntime()->getPathData() . str_replace('/', DS, $path);
        if (!is_dir($absPath)) {
            throw new ServiceException('路径不存在！');
        }

        return $absPath;
    }


    public function getAbsDirPath($dirName = '', $path = null)
    {
        $absPath = $this->getAbsPath($path);
        if ($absPath == false) return false;

        if (strpos($dirName, '/') !== false) {
            throw new ServiceException('文件夹名称不合法！');
        }

        $absDirPath = $absPath . '/' . $dirName;
        if (!file_exists($absDirPath) || !is_dir($absDirPath)) {
            throw new ServiceException('文件夹 ' . $dirName . ' 不存在！');
        }

        return $absDirPath;
    }


    public function getAbsFilePath($fileName = '', $path = null)
    {
        $absPath = $this->getAbsPath($path);
        if ($absPath == false) return false;

        if (strpos($fileName, '/') !== false) {
            throw new ServiceException('文件名称不合法！');
        }

        $absFilePath = $absPath . '/' . $fileName;
        if (!file_exists($absFilePath) || is_dir($absFilePath)) {
            throw new ServiceException('文件 ' . $fileName . ' 不存在！');
        }

        return $absFilePath;
    }


    public function formatSize($size)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $u = 0;
        while ((round($size / 1024) > 0) && ($u < 4)) {
            $size = $size / 1024;
            $u++;
        }
        return (number_format($size, 0) . ' ' . $units[$u]);
    }


}
