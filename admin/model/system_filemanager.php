<?php

namespace admin\model;

/**
 */
class system_filemanager extends \model
{

    public function get_files($option = array())
    {
        $abs_path = $this->get_abs_path($option['path']);
        if ($abs_path == false) $abs_path = PATH_DATA;

        $return = array();

        $config_system = be::get_config('system');

        // 分析目录
        $files = scandir($abs_path);
        foreach ($files as $x => $name) {
            if ($name == "." || $name == "..") continue;

            $item_path = $abs_path . DS . $name;

            $size = 0;
            $type = 'dir';
            if (!is_dir($item_path)) {
                $size = $this->format_size(filesize($item_path));
                $type = strtolower(substr(strrchr($name, '.'), 1));
            }

            // 是否只显示图像文件，插入图像时使用
            $filter = false;
            if ($option['filter_image'] == 1 && $type != 'dir' && !in_array($type, $config_system->allow_upload_image_types)) $filter = true;

            if (!$filter) $return[$x] = array('name' => $name, 'date' => filemtime($item_path), 'size' => $size, 'type' => $type);
        }

        return $return;
    }


    public function create_dir($dir_name, $path = null)
    {
        $abs_path = $this->get_abs_path($path);
        if ($abs_path == false) return false;

        if (strpos($dir_name, '/') !== false) {
            $this->set_error('文件夹名称不合法！');
            return false;
        }

        $dir_path = $abs_path . DS . $dir_name;
        if (file_exists($dir_path)) {
            $this->set_error('已存在名称为 ' . $dir_name . ' 的文件夹！');
            return false;
        }

        mkdir($dir_path, 0777, true);

        return true;
    }

    public function delete_dir($dir_name, $path = null)
    {
        $abs_dir_path = $this->get_abs_dir_path($dir_name, $path);
        if ($abs_dir_path == false) return false;

        $lib_fso = be::get_lib('fso');
        $lib_fso->rm_dir($abs_dir_path);

        return true;
    }

    public function edit_dir_name($old_dir_name, $new_dir_name, $path = null)
    {
        $abs_path = $this->get_abs_path($path);
        if ($abs_path == false) return false;

        if (strpos($old_dir_name, '/') !== false || strpos($new_dir_name, '/') !== false) {
            $this->set_error('文件夹名称不合法！');
            return false;
        }

        $src_path = $abs_path . DS . $old_dir_name;
        if (!file_exists($src_path)) {
            $this->set_error('文件夹 ' . $old_dir_name . ' 不存在！');
            return false;
        }

        $dst_path = $abs_path . DS . $new_dir_name;
        if (file_exists($dst_path)) {
            $this->set_error('已存在名称为 ' . $new_dir_name . ' 的文件夹！');
            return false;
        }

        if (!rename($src_path, $dst_path)) {
            $this->set_error('重命名文件夹失败！');
            return false;
        }

        return true;
    }


    public function delete_file($file_name, $path = null)
    {
        $abs_file_path = $this->get_abs_file_path($file_name, $path);
        if ($abs_file_path == false) return false;

        if (!unlink($abs_file_path)) {
            $this->set_error('删除文件失败，请检查是否有权限！');
            return false;
        }

        return true;
    }


    public function edit_file_name($old_file_name, $new_file_name, $path = null)
    {
        $abs_path = $this->get_abs_path($path);
        if ($abs_path == false) return false;

        if (strpos($old_file_name, '/') !== false || strpos($new_file_name, '/') !== false) {
            $this->set_error('文件名称不合法！');
            return false;
        }

        $src_path = $abs_path . DS . $old_file_name;
        if (!file_exists($src_path)) {
            $this->set_error('文件 ' . $old_file_name . ' 不存在！');
            return false;
        }

        $type = strtolower(substr(strrchr($new_file_name, '.'), 1));
        $config = be::get_config('system');
        if (!in_array($type, $config->allow_upload_file_types)) {
            $this->set_error('不允许的文件格式！');
            return false;
        }

        $dst_path = $abs_path . DS . $new_file_name;
        if (file_exists($dst_path)) {
            $this->set_error('文件 ' . $new_file_name . ' 已存在！');
            return false;
        }

        if (!rename($src_path, $dst_path)) {
            $this->set_error('修改文件名失败，请检查名称是否合法！');
            return false;
        }

        return true;
    }


    public function get_abs_path($path = null)
    {
        if ($path == null) $path = session::get('system_filemanager_path');

        // 禁止用户查看其它目录
        if (strpos($path, './') != false) {
            $this->set_error('路径不合法！');
            return false;
        }

        if (substr($path, -1, 1) == '/') {
            $this->set_error('路径不合法！');
            return false;
        }

        // 绝对路径
        $abs_path = PATH_DATA . str_replace('/', DS, $path);
        if (!is_dir($abs_path)) {
            $this->set_error('路径不存在！');
            return false;
        }

        return $abs_path;
    }


    public function get_abs_dir_path($dir_name = '', $path = null)
    {
        $abs_path = $this->get_abs_path($path);
        if ($abs_path == false) return false;

        if (strpos($dir_name, '/') !== false) {
            $this->set_error('文件夹名称不合法！');
            return false;
        }

        $abs_dir_path = $abs_path . DS . $dir_name;
        if (!file_exists($abs_dir_path) || !is_dir($abs_dir_path)) {
            $this->set_error('文件夹 ' . $dir_name . ' 不存在！');
            return false;
        }

        return $abs_dir_path;
    }


    public function get_abs_file_path($file_name = '', $path = null)
    {
        $abs_path = $this->get_abs_path($path);
        if ($abs_path == false) return false;

        if (strpos($file_name, '/') !== false) {
            $this->set_error('文件名称不合法！');
            return false;
        }

        $abs_file_path = $abs_path . DS . $file_name;
        if (!file_exists($abs_file_path) || is_dir($abs_file_path)) {
            $this->set_error('文件 ' . $file_name . ' 不存在！');
            return false;
        }

        return $abs_file_path;
    }


    public function format_size($size)
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

?>