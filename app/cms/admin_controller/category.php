<?php

namespace app\cms\controller;

use system\be;
use system\db\exception;
use system\error_log;
use system\request;
use system\response;

class category extends \system\admin_controller
{


    public function categories()
    {
        $service_article = be::get_service('cms.article');

        response::set_title('分类管理');
        response::set('categories', $service_article->get_categories());
        response::display();
    }

    public function save_categories()
    {
        $ids = request::post('id', array(), 'int');
        $parent_ids = request::post('parent_id', array(), 'int');
        $names = request::post('name', array());

        $db = be::get_db();
        $db->start_transaction();

        try {
            $row_user = be::get_row('system.user');
            $row_user->load(1);
            if (count($ids)) {
                for ($i = 0, $n = count($ids); $i < $n; $i++) {
                    if (!$ids[$i] && !$names[$i]) continue;

                    $row_article_category = be::get_row('article_category');
                    $row_article_category->id = $ids[$i];
                    $row_article_category->parent_id = $parent_ids[$i];
                    $row_article_category->name = $names[$i];
                    $row_article_category->rank = $i;
                    $row_article_category->save();
                }
            }
            $db->commit();

            system_log('修改文章分类信息');

            response::set_message('保存分类成功！');
            response::redirect('./?controller=article&task=categories');

        } catch (exception $e) {
            $db->rollback();

            error_log::log($e);

            response::set_message('保存分类失败：'.$e->getMessage());
            response::redirect('./?controller=article&task=categories');
        }
    }

    public function ajax_delete_category()
    {
        $category_id = request::post('id', 0, 'int');
        if (!$category_id) {
            response::set('error', 1);
            response::set('message', '参数(id)缺失！');
        } else {
            $row_category = be::get_row('article_category');
            $row_category->load($category_id);

            $service_article = be::get_service('cms.article');
            if ($service_article->delete_category($category_id)) {
                response::set('error', 0);
                response::set('message', '分类删除成功！');

                system_log('删除文章分类：#' . $category_id . ': ' . $row_category->title);
            } else {
                response::set('error', 2);
                response::set('message', $service_article->get_error());
            }
        }
        response::ajax();
    }



    public function setting()
    {
        response::set_title('设置文章系统参数');
        response::set('config_article', be::get_config('article'));
        response::display();
    }

    public function setting_save()
    {
        $config_article = be::get_config('article');

        $config_article->get_summary = request::post('get_summary', 0, 'int');
        $config_article->get_meta_keywords = request::post('get_meta_keywords', 0, 'int');
        $config_article->get_meta_description = request::post('get_meta_description', 0, 'int');
        $config_article->download_remote_image = request::post('download_remote_image', 0, 'int');
        $config_article->comment = request::post('comment', 0, 'int');
        $config_article->comment_public = request::post('comment_public', 0, 'int');

        $config_article->thumbnail_l_w = request::post('thumbnail_l_w', 0, 'int');
        $config_article->thumbnail_l_h = request::post('thumbnail_l_h', 0, 'int');
        $config_article->thumbnail_m_w = request::post('thumbnail_m_w', 0, 'int');
        $config_article->thumbnail_m_h = request::post('thumbnail_m_h', 0, 'int');
        $config_article->thumbnail_s_w = request::post('thumbnail_s_w', 0, 'int');
        $config_article->thumbnail_s_h = request::post('thumbnail_s_h', 0, 'int');

        // 缩图图大图
        $default_thumbnail_l = $_FILES['default_thumbnail_l'];
        if ($default_thumbnail_l['error'] == 0) {
            $lib_image = be::get_lib('image');
            $lib_image->open($default_thumbnail_l['tmp_name']);
            if ($lib_image->is_image()) {
                $default_thumbnail_l_name = date('YmdHis') . '_l.' . $lib_image->get_type();
                $default_thumbnail_l_path = PATH_DATA . DS . 'cms' . DS . 'article' . DS . 'thumbnail' . DS . 'default' . DS . $default_thumbnail_l_name;
                if (move_uploaded_file($default_thumbnail_l['tmp_name'], $default_thumbnail_l_path)) {
                    // @unlink(PATH_DATA.DS.'article'.DS.'thumbnail'.DS.'default'.DS.$config_article->default_thumbnail_l);
                    $config_article->default_thumbnail_l = $default_thumbnail_l_name;
                }
            }
        }

        // 缩图图中图
        $default_thumbnail_m = $_FILES['default_thumbnail_m'];
        if ($default_thumbnail_m['error'] == 0) {
            $lib_image = be::get_lib('image');
            $lib_image->open($default_thumbnail_m['tmp_name']);
            if ($lib_image->is_image()) {
                $default_thumbnail_m_name = date('YmdHis') . '_m.' . $lib_image->get_type();
                $default_thumbnail_m_path = PATH_DATA . DS . 'cms' . DS . 'article' . DS . 'thumbnail' . DS . 'default' . DS . $default_thumbnail_m_name;
                if (move_uploaded_file($default_thumbnail_m['tmp_name'], $default_thumbnail_m_path)) {
                    // @unlink(PATH_DATA.DS.'article'.DS.'thumbnail'.DS.'default'.DS.$config_article->default_thumbnail_m);
                    $config_article->default_thumbnail_m = $default_thumbnail_m_name;
                }
            }
        }

        // 缩图图小图
        $default_thumbnail_s = $_FILES['default_thumbnail_s'];
        if ($default_thumbnail_s['error'] == 0) {
            $lib_image = be::get_lib('image');
            $lib_image->open($default_thumbnail_s['tmp_name']);
            if ($lib_image->is_image()) {
                $default_thumbnail_s_name = date('YmdHis') . '_s.' . $lib_image->get_type();
                $default_thumbnail_s_path = PATH_DATA . DS . 'cms' . DS . 'article' . DS . 'thumbnail' . DS . 'default' . DS . $default_thumbnail_s_name;
                if (move_uploaded_file($default_thumbnail_s['tmp_name'], $default_thumbnail_s_path)) {
                    // @unlink(PATH_DATA.DS.'article'.DS.'thumbnail'.DS.'default'.DS.$config_article->default_thumbnail_s);
                    $config_article->default_thumbnail_s = $default_thumbnail_s_name;
                }
            }
        }

        $service_system = be::get_service('system.admin');
        $service_system->update_config($config_article, PATH_ROOT . DS . 'configs' . DS . 'article.php');

        system_log('设置文章系统参数');

        response::set_message('设置成功！');
        response::redirect('./?controller=article&task=setting');
    }

}
