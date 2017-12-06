<?php
namespace app\cms\controller;

use system\be;
use system\request;
use system\response;

class article extends \system\admin_controller
{

    public function articles()
    {
        $order_by = request::post('order_by', 'id');
        $order_by_dir = request::post('order_by_dir', 'ASC');
        $category_id = request::post('category_id', -1, 'int');
        $key = request::post('key', '');
        $status = request::post('status', -1, 'int');
        $limit = request::post('limit', -1, 'int');

        if ($limit == -1) {
            $admin_config_system = be::get_admin_config('system');
            $limit = $admin_config_system->limit;
        }

        $admin_service_article = be::get_service('cms.article');
        response::set_title('文章列表');

        $option = array('category_id' => $category_id, 'key' => $key, 'status' => $status);

        $pagination = be::get_admin_ui('pagination');
        $pagination->set_limit($limit);
        $pagination->set_total($admin_service_article->get_article_count($option));
        $pagination->set_page(request::post('page', 1, 'int'));

        response::set('pagination', $pagination);
        response::set('order_by', $order_by);
        response::set('order_by_dir', $order_by_dir);
        response::set('category_id', $category_id);
        response::set('key', $key);
        response::set('status', $status);

        $option['order_by'] = $order_by;
        $option['order_by_dir'] = $order_by_dir;
        $option['offset'] = $pagination->get_offset();
        $option['limit'] = $limit;

        $articles = $admin_service_article->get_articles($option);
        foreach ($articles as $article) {
            $article->comment_count = $admin_service_article->get_comment_count(array('article_id' => $article->id));
        }
        response::set('articles', $articles);

        $service_article = be::get_service('cms.article');
        response::set('categories', $service_article->get_categories());

        response::display();

        $lib_history = be::get_lib('history');
        $lib_history->save();
    }


    public function edit()
    {
        $id = request::post('id', 0, 'int');

        $row_article = be::get_row('cms.article');
        $row_article->load($id);

        if ($id == 0) {
            response::set_title('添加文章');
        } else {
            response::set_title('编辑文章');
        }
        response::set('article', $row_article);

        $service_article = be::get_service('cms.article');
        $categories = $service_article->get_categories();
        response::set('categories', $categories);
        response::display();
    }


    public function edit_save()
    {
        $id = request::post('id', 0, 'int');

        $my = be::get_admin_user();

        $row_article = be::get_row('cms.article');
        if ($id != 0) $row_article->load($id);
        $row_article->bind(request::post());

        $row_article->create_time = strtotime($row_article->create_time);

        $body = request::post('body', '', 'html');

        $config_system = be::get_config('system');

        // 找出内容中的所有图片
        $images = array();

        $image_types = implode('|', $config_system->allow_upload_image_types);
        preg_match_all("/src=[\\\|\"|'|\s]{0,}(http:\/\/([^>]*)\.($image_types))/isU", $body, $images);
        $images = array_unique($images[1]);

        // 过滤掉本服务器上的图片
        $remote_images = array();
        if (count($images) > 0) {
            $be_url_len = strlen(URL_ROOT);
            foreach ($images as $image) {
                if (substr($image, 0, $be_url_len) != URL_ROOT) {
                    $remote_images[] = $image;
                }
            }
        }

        $thumbnail_source = request::post('thumbnail_source', ''); // upload：上传缩图图 / url：从指定网址获取缩图片
        $thumbnail_pick_up = request::post('thumbnail_pick_up', 0, 'int'); // 是否提取第一张图作为缩略图
        $download_remote_image = request::post('download_remote_image', 0, 'int'); // 是否下载远程图片
        $download_remote_image_watermark = request::post('download_remote_image_watermark', 0, 'int'); // 是否下截远程图片添加水印

        // 下载远程图片
        if ($download_remote_image == 1) {
            if (count($remote_images) > 0) {
                $lib_http = be::get_lib('http');

                // 下载到本地的文件夹
                $dir_name = date('Y-m-d');
                $dir_path = PATH_DATA . DS . 'article' . DS . $dir_name;

                // 文件夹不存在时自动创建
                if (!file_exists($dir_path)) {
                    $lib_fso = be::get_lib('fso');
                    $lib_fso->mk_dir($dir_path);
                }

                $t = date('YmdHis');
                $i = 0;
                foreach ($remote_images as $remote_image) {
                    $local_image_name = $t . $i . '.' . strtolower(substr(strrchr($remote_image, '.'), 1));
                    $data = $lib_http->get($remote_image);

                    file_put_contents($dir_path . DS . $local_image_name, $data);

                    // 下截远程图片添加水印
                    if ($download_remote_image_watermark == 1) {
                        $service_system = be::get_service('system');
                        $service_system->watermark($dir_path . DS . $local_image_name);
                    }

                    $body = str_replace($remote_image, URL_ROOT . '/' . DATA . '/article/' . $dir_name . '/' . $local_image_name, $body);
                    $i++;
                }
            }
        }
        $row_article->body = $body;

        $config_article = be::get_config('article');

        // 提取第一张图作为缩略图
        if ($thumbnail_pick_up == 1) {
            if (count($images) > 0) {
                $lib_http = be::get_lib('http');
                $data = $lib_http->get($images[0]);

                if ($data != false) {
                    $tmp_image = PATH_DATA . DS . 'tmp' . DS . date('YmdHis') . '.' . strtolower(substr(strrchr($images[0], '.'), 1));
                    file_put_contents($tmp_image, $data);

                    $lib_image = be::get_lib('image');
                    $lib_image->open($tmp_image);

                    if ($lib_image->is_image()) {
                        $t = date('YmdHis');
                        $dir = PATH_DATA . DS . 'article' . DS . 'thumbnail';
                        if (!file_exists($dir)) {
                            $lib_fso = be::get_lib('fso');
                            $lib_fso->mk_dir($dir);
                        }

                        $thumbnail_l_name = $t . '_l.' . $lib_image->get_type();
                        $lib_image->resize($config_article->thumbnail_l_w, $config_article->thumbnail_l_h, 'scale');
                        $lib_image->save($dir . DS . $thumbnail_l_name);
                        $row_article->thumbnail_l = $thumbnail_l_name;

                        $thumbnail_m_name = $t . '_m.' . $lib_image->get_type();
                        $lib_image->resize($config_article->thumbnail_m_w, $config_article->thumbnail_m_h, 'scale');
                        $lib_image->save($dir . DS . $thumbnail_m_name);
                        $row_article->thumbnail_m = $thumbnail_m_name;

                        $thumbnail_s_name = $t . '_s.' . $lib_image->get_type();
                        $lib_image->resize($config_article->thumbnail_s_w, $config_article->thumbnail_s_h, 'scale');
                        $lib_image->save($dir . DS . $thumbnail_s_name);
                        $row_article->thumbnail_s = $thumbnail_s_name;
                    }

                    @unlink($tmp_image);
                }
            }
        } else {
            // 上传缩图图
            if ($thumbnail_source == 'upload') {
                $thumbnail_upload = $_FILES['thumbnail_upload'];
                if ($thumbnail_upload['error'] == 0) {
                    $lib_image = be::get_lib('image');
                    $lib_image->open($thumbnail_upload['tmp_name']);
                    if ($lib_image->is_image()) {
                        $t = date('YmdHis');
                        $dir = PATH_DATA . DS . 'article' . DS . 'thumbnail';
                        if (!file_exists($dir)) {
                            $lib_fso = be::get_lib('fso');
                            $lib_fso->mk_dir($dir);
                        }

                        $thumbnail_l_name = $t . '_l.' . $lib_image->get_type();
                        $lib_image->resize($config_article->thumbnail_l_w, $config_article->thumbnail_l_h, 'scale');
                        $lib_image->save($dir . DS . $thumbnail_l_name);
                        $row_article->thumbnail_l = $thumbnail_l_name;

                        $thumbnail_m_name = $t . '_m.' . $lib_image->get_type();
                        $lib_image->resize($config_article->thumbnail_m_w, $config_article->thumbnail_m_h, 'scale');
                        $lib_image->save($dir . DS . $thumbnail_m_name);
                        $row_article->thumbnail_m = $thumbnail_m_name;

                        $thumbnail_s_name = $t . '_s.' . $lib_image->get_type();
                        $lib_image->resize($config_article->thumbnail_s_w, $config_article->thumbnail_s_h, 'scale');
                        $lib_image->save($dir . DS . $thumbnail_s_name);
                        $row_article->thumbnail_s = $thumbnail_s_name;
                    }
                }
            } elseif ($thumbnail_source == 'url') { // 从指定网址获取缩图片
                $thumbnail_url = request::post('thumbnail_url', '');
                if ($thumbnail_url != '' && substr($thumbnail_url, 0, 7) == 'http://') {
                    $lib_http = be::get_lib('http');
                    $data = $lib_http->get($thumbnail_url);

                    if ($data != false) {
                        $tmp_image = PATH_DATA . DS . 'tmp' . DS . date('YmdHis') . '.' . strtolower(substr(strrchr($thumbnail_url, '.'), 1));
                        file_put_contents($tmp_image, $data);

                        $lib_image = be::get_lib('image');
                        $lib_image->open($tmp_image);

                        if ($lib_image->is_image()) {
                            $t = date('YmdHis');
                            $dir = PATH_DATA . DS . 'article' . DS . 'thumbnail';
                            if (!file_exists($dir)) {
                                $lib_fso = be::get_lib('fso');
                                $lib_fso->mk_dir($dir);
                            }

                            $thumbnail_l_name = $t . '_l.' . $lib_image->get_type();
                            $lib_image->resize($config_article->thumbnail_l_w, $config_article->thumbnail_l_h, 'scale');
                            $lib_image->save($dir . DS . $thumbnail_l_name);
                            $row_article->thumbnail_l = $thumbnail_l_name;

                            $thumbnail_m_name = $t . '_m.' . $lib_image->get_type();
                            $lib_image->resize($config_article->thumbnail_m_w, $config_article->thumbnail_m_h, 'scale');
                            $lib_image->save($dir . DS . $thumbnail_m_name);
                            $row_article->thumbnail_m = $thumbnail_m_name;

                            $thumbnail_s_name = $t . '_s.' . $lib_image->get_type();
                            $lib_image->resize($config_article->thumbnail_s_w, $config_article->thumbnail_s_h, 'scale');
                            $lib_image->save($dir . DS . $thumbnail_s_name);
                            $row_article->thumbnail_s = $thumbnail_s_name;
                        }

                        @unlink($tmp_image);
                    }
                }
            }
        }


        if ($id == 0) {
            $row_article->create_by_id = $my->id;
        } else {
            $row_article->modify_time = time();
            $row_article->modify_by_id = $my->id;
        }

        if ($row_article->save()) {
            if ($id == 0) {
                response::set_message('添加文章成功！');
                system_log('添加文章：#' . $row_article->id . ': ' . $row_article->title);
            } else {
                response::set_message('修改文章成功！');
                system_log('编辑文章：#' . $id . ': ' . $row_article->title);
            }
        } else {
            response::set_message($row_article->get_error(), 'error');
        }

        $lib_history = be::get_lib('history');
        $lib_history->back();
    }


    public function unblock()
    {
        $ids = request::post('id', '');

        $service_article = be::get_service('cms.article');
        if ($service_article->unblock($ids)) {
            response::set_message('公开文章成功！');
            system_log('公开文章：#' . $ids);
        } else {
            response::set_message($service_article->get_error(), 'error');
        }

        $lib_history = be::get_lib('history');
        $lib_history->back();
    }

    public function block()
    {
        $ids = request::post('id', '');

        $service_article = be::get_service('cms.article');
        if ($service_article->block($ids)) {
            response::set_message('屏蔽文章成功！');
            system_log('屏蔽文章：#' . $ids);
        } else {
            response::set_message($service_article->get_error(), 'error');
        }

        $lib_history = be::get_lib('history');
        $lib_history->back();
    }

    public function delete()
    {
        $ids = request::post('id', '');

        $service_article = be::get_service('cms.article');
        if ($service_article->delete($ids)) {
            response::set_message('删除文章成功！');
            system_log('删除文章：#' . $ids);
        } else {
            response::set_message($service_article->get_error(), 'error');
        }

        $lib_history = be::get_lib('history');
        $lib_history->back();
    }


    private function clean_html($html)
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
    public function ajax_get_summary()
    {
        $body = $this->clean_html($_POST['body']);

        $config_article = be::get_config('article');

        response::set('error', 0);
        response::set('summary', limit($body, intval($config_article->get_summary)));
        response::ajax();
    }


    // 从内容中提取 META 关键字
    public function ajax_get_meta_keywords()
    {
        $body = $this->clean_html($_POST['body']);

        $config_article = be::get_config('cms.article');

        $lib_scws = be::get_lib('scws');
        $lib_scws->send_text($body);
        $scws_keywords = $lib_scws->get_tops(intval($config_article->get_meta_keywords));
        $meta_keywords = '';
        if ($scws_keywords !== false) {
            $tmp_meta_keywords = array();
            foreach ($scws_keywords as $scws_keyword) {
                $tmp_meta_keywords[] = $scws_keyword['word'];
            }
            $meta_keywords = implode(' ', $tmp_meta_keywords);
        }

        response::set('error', 0);
        response::set('meta_keywords', $meta_keywords);
        response::ajax();
    }

    // 从内容中提取 META 描述
    public function ajax_get_meta_description()
    {
        $body = $this->clean_html($_POST['body']);

        $config_article = be::get_config('article');

        response::set('error', 0);
        response::set('meta_description', limit($body, intval($config_article->get_meta_description)));
        response::ajax();
    }


    public function categories()
    {
        $service_article = be::get_service('cms.article');

        $template = be::get_admin_template('article.categories');
        response::set_title('分类管理');
        response::set('categories', $service_article->get_categories());
        response::display();
    }

    public function save_categories()
    {
        $ids = request::post('id', array(), 'int');
        $parent_ids = request::post('parent_id', array(), 'int');
        $names = request::post('name', array());

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

        system_log('修改文章分类信息');

        response::set_message('保存分类成功！');
        response::redirect('./?controller=article&task=categories');
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


    public function comments()
    {
        $order_by = request::post('order_by', 'create_time');
        $order_by_dir = request::post('order_by_dir', 'DESC');
        $article_id = request::post('article_id', 0, 'int');
        $key = request::post('key', '');
        $status = request::post('status', -1, 'int');
        $limit = request::post('limit', -1, 'int');

        if ($limit == -1) {
            $admin_config_system = be::get_admin_config('system');
            $limit = $admin_config_system->limit;
        }

        $admin_service_article = be::get_service('cms.article');
        response::set_title('评论列表');

        $option = array('article_id' => $article_id, 'key' => $key, 'status' => $status);

        $pagination = be::get_admin_ui('pagination');
        $pagination->set_limit($limit);
        $pagination->set_total($admin_service_article->get_comment_count($option));
        $pagination->set_page(request::post('page', 1, 'int'));

        response::set('pagination', $pagination);
        response::set('order_by', $order_by);
        response::set('order_by_dir', $order_by_dir);
        response::set('key', $key);
        response::set('status', $status);

        response::set('article_id', $article_id);

        $option['order_by'] = $order_by;
        $option['order_by_dir'] = $order_by_dir;
        $option['offset'] = $pagination->get_offset();
        $option['limit'] = $limit;

        $articles = array();
        $comments = $admin_service_article->get_comments($option);
        foreach ($comments as $comment) {
            if (!array_key_exists($comment->article_id, $articles)) {
                $row_article = be::get_row('cms.article');
                $row_article->load($comment->article_id);
                $articles[$comment->article_id] = $row_article;
            }

            $comment->article = $articles[$comment->article_id];
        }

        response::set('comments', $comments);
        response::display();

        $lib_history = be::get_lib('history');
        $lib_history->save();
    }

    public function comments_unblock()
    {
        $ids = request::post('id', '');

        $model = be::get_service('cms.article');

        if ($model->comments_unblock($ids)) {
            response::set_message('公开评论成功！');
            system_log('公开文章评论：#' . $ids);
        } else {
            response::set_message($model->get_error(), 'error');
        }

        $lib_history = be::get_lib('history');
        $lib_history->back();
    }

    public function comments_block()
    {
        $ids = request::post('id', '');

        $model = be::get_service('cms.article');
        if ($model->comments_block($ids)) {
            response::set_message('屏蔽评论成功！');
            system_log('屏蔽文章评论：#' . $ids);
        } else {
            response::set_message($model->get_error(), 'error');
        }

        $lib_history = be::get_lib('history');
        $lib_history->back();
    }

    public function comments_delete()
    {
        $ids = request::post('id', '');

        $model = be::get_service('cms.article');
        if ($model->comments_delete($ids)) {
            response::set_message('删除评论成功！');
            system_log('删除文章评论：#' . $ids . ')');
        } else {
            response::set_message($model->get_error(), 'error');
        }

        $lib_history = be::get_lib('history');
        $lib_history->back();
    }

    public function setting()
    {
        $template = be::get_admin_template('article.setting');
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
                $default_thumbnail_l_path = PATH_DATA . DS . 'article' . DS . 'thumbnail' . DS . 'default' . DS . $default_thumbnail_l_name;
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
                $default_thumbnail_m_path = PATH_DATA . DS . 'article' . DS . 'thumbnail' . DS . 'default' . DS . $default_thumbnail_m_name;
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
                $default_thumbnail_s_path = PATH_DATA . DS . 'article' . DS . 'thumbnail' . DS . 'default' . DS . $default_thumbnail_s_name;
                if (move_uploaded_file($default_thumbnail_s['tmp_name'], $default_thumbnail_s_path)) {
                    // @unlink(PATH_DATA.DS.'article'.DS.'thumbnail'.DS.'default'.DS.$config_article->default_thumbnail_s);
                    $config_article->default_thumbnail_s = $default_thumbnail_s_name;
                }
            }
        }

        $service_system = be::get_service('system');
        $service_system->update_config($config_article, PATH_ROOT . DS . 'configs' . DS . 'article.php');

        system_log('设置文章系统参数');

        response::set_message('设置成功！');
        response::redirect('./?controller=article&task=setting');
    }

}

?>