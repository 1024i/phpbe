<?php

namespace admin\controller;

use system\be;
use system\request;
use system\response;
use system\session;

// 文件管理器
class system_filemanager extends \admin\system\controller
{

    public function browser()
    {
        // 要查看的路径
        $path = request::post('path', '');

        // 显示方式 thumbnail 缩略图 list 详细列表
        $view = request::post('view', '');

        // 排序
        $sort = request::post('sort', '');

        // 只显示图像
        $filter_image = request::get('filter_image', -1, 'int');

        $src_id = request::get('src_id', '');


        // session 缓存用户选择
        if ($path == '') {
            $session_path = session::get('system_filemanager_path');
            if ($session_path != '') $path = $session_path;
        } else {
            if ($path == '/') $path = '';
            session::set('system_filemanager_path', $path);
        }

        if ($view == '') {
            $view = 'thumbnail';
            $session_view = session::get('system_filemanager_view');
            if ($session_view != '' && ($session_view == 'thumbnail' || $session_view == 'list')) $view = $session_view;
        } else {
            if ($view != 'thumbnail' && $view != 'list') $view = 'thumbnail';
            session::set('system_filemanager_view', $view);
        }

        if ($sort == '') {
            $session_sort = session::get('system_filemanager_sort');
            if ($session_sort == '') {
                $sort = 'name';
            } else {
                $sort = $session_sort;
            }

        } else {
            session::set('system_filemanager_sort', $sort);
        }

        if ($filter_image == -1) {
            $filter_image = 0;
            $session_filter_image = session::get('system_filemanager_filter_image', -1);
            if ($session_filter_image != -1 && ($session_filter_image == 0 || $session_filter_image == 1)) $filter_image = $session_filter_image;
        } else {
            if ($filter_image != 0 && $filter_image != 1) $filter_image = 0;
            session::set('system_filemanager_filter_image', $filter_image);
        }

        if ($src_id == '') {
            $src_id = session::get('system_filemanager_src_id', '');
        } elseif ($src_id == 'img') {
            $src_id = '';
            session::set('system_filemanager_src_id', $src_id);
        } else {
            session::set('system_filemanager_src_id', $src_id);
        }


        $option = array();
        $option['path'] = $path;
        $option['view'] = $view;
        $option['sort'] = $sort;
        $option['filter_image'] = $filter_image;

        $service_system_filemanager = be::get_admin_service('system_filemanager');
        $files = $service_system_filemanager->get_files($option);

        $template = be::get_admin_template('system_filemanager.browser');
        response::set('path', $path);
        response::set('view', $view);
        response::set('sort', $sort);
        response::set('filter_image', $filter_image);
        response::set('src_id', $src_id);

        response::set('files', $files);
        response::display();
    }

    public function create_dir()
    {
        $dir_name = request::post('dir_name', '');

        $service_system_filemanager = be::get_admin_service('system_filemanager');
        if ($service_system_filemanager->create_dir($dir_name)) {
            response::set_message('创建文件夹(' . $dir_name . ')成功！');
        } else {
            response::set_message($service_system_filemanager->get_error(), 'error');
        }

        response::redirect('./?controller=system_filemanager&task=browser');
    }

    // 删除文件夹
    public function delete_dir()
    {
        $dir_name = request::get('dir_name', '');

        $service_system_filemanager = be::get_admin_service('system_filemanager');
        if ($service_system_filemanager->delete_dir($dir_name)) {
            response::set_message('删除文件夹(' . $dir_name . ')成功！');
        } else {
            response::set_message($service_system_filemanager->get_error(), 'error');
        }

        response::redirect('./?controller=system_filemanager&task=browser');
    }

    // 修改文件夹名称
    public function edit_dir_name()
    {
        $old_dir_name = request::post('old_dir_name', '');
        $new_dir_name = request::post('new_dir_name', '');

        $service_system_filemanager = be::get_admin_service('system_filemanager');
        if ($service_system_filemanager->edit_dir_name($old_dir_name, $new_dir_name)) {
            response::set_message('重命名文件夹成功！');
        } else {
            response::set_message($service_system_filemanager->get_error(), 'error');
        }

        response::redirect('./?controller=system_filemanager&task=browser');
    }


    public function upload_file()
    {
        $config_system = be::get_config('system');

        $return = './?controller=system_filemanager&task=browser';

        $file = $_FILES['file'];
        if ($file['error'] == 0) {
            $file_name = $file['name'];

            $type = strtolower(substr(strrchr($file_name, '.'), 1));
            if (!in_array($type, $config_system->allow_upload_file_types)) {
                response::set_message('不允许上传(' . $type . ')格式的文件！', 'error');
                response::redirect($return);
            }

            if (strpos($file_name, '/') !== false) {
                response::set_message('文件名称不合法！', 'error');
                response::redirect($return);
            }

            $service_system_filemanager = be::get_admin_service('system_filemanager');
            $abs_path = $service_system_filemanager->get_abs_path();
            if ($abs_path == false) {
                response::set_message($service_system_filemanager->get_error(), 'error');
                response::redirect($return);
            }

            $dst_path = $abs_path . DS . $file_name;

            $rename = false;
            if (file_exists($dst_path)) {
                $i = 1;
                $name = substr($file_name, 0, strrpos($file_name, '.'));
                while (file_exists($abs_path . DS . $name . '_' . $i . '.' . $type)) {
                    $i++;
                }

                $dst_path = $abs_path . DS . $name . '_' . $i . '.' . $type;

                $rename = $name . '_' . $i . '.' . $type;
            }

            if (move_uploaded_file($file['tmp_name'], $dst_path)) {
                $watermark = request::post('watermark', 0, 'int');
                if ($watermark == 1 && in_array($type, $config_system->allow_upload_image_types)) {
                    $service_system = be::get_admin_service('system');
                    $service_system->watermark($dst_path);
                }

                if ($rename == false) {
                    response::set_message('上传文件成功！');
                } else {
                    response::set_message('有同名文件，新上传的文件已更名为：' . $rename . '！', 'warning');
                }
            } else {
                response::set_message('上传失败！', 'error');
            }
        } else {

            $upload_errors = array(
                '1' => '您上传的文件过大！',
                '2' => '您上传的文件过大！',
                '3' => '文件只有部分被上传！',
                '4' => '没有文件被上传！',
                '5' => '上传的文件大小为 0！'
            );

            $error = '';
            if (array_key_exists($file['error'], $upload_errors)) {
                $error = $upload_errors[$file['error']];
            } else {
                $error = '错误代码：' . $file['error'];
            }

            response::set_message('上传失败' . '(' . $error . ')', 'error');
        }

        response::redirect($return);
    }

    // 删除文件
    public function delete_file()
    {
        $file_name = request::get('file_name', '');

        $service_system_filemanager = be::get_admin_service('system_filemanager');
        if ($service_system_filemanager->delete_file($file_name)) {
            response::set_message('删除文件(' . $file_name . ')成功！');
        } else {
            response::set_message($service_system_filemanager->get_error(), 'error');
        }

        response::redirect('./?controller=system_filemanager&task=browser');
    }

    // 修改文件名称
    public function edit_file_name()
    {
        $old_file_name = request::post('old_file_name', '');
        $new_file_name = request::post('new_file_name', '');

        $service_system_filemanager = be::get_admin_service('system_filemanager');
        if ($service_system_filemanager->edit_file_name($old_file_name, $new_file_name)) {
            response::set_message('重命名文件成功！');
        } else {
            response::set_message($service_system_filemanager->get_error(), 'error');
        }

        response::redirect('./?controller=system_filemanager&task=browser');
    }

    public function download_file()
    {
        $file_name = request::get('file_name', '');

        $service_system_filemanager = be::get_admin_service('system_filemanager');
        $abs_file_path = $service_system_filemanager->get_abs_file_path($file_name);
        if ($abs_file_path == false) {
            echo $service_system_filemanager->get_error();
        } else {
            header('Pragma: private');
            header('Cache-control: private, must-revalidate');
            header("Content-Type: application/octet-stream");
            header("Content-Length: " . (string)(filesize($abs_file_path)));
            header('Content-Disposition: attachment; filename="' . ($file_name) . '"');
            readfile($abs_file_path);
        }
        exit;
    }

}

?>