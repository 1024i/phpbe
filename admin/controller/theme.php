<?php

namespace admin\controller;

use system\be;
use system\request;
use system\response;

class theme extends \admin\system\controller
{

    // 登陆后首页
    public function dashboard()
    {
        $my = be::get_admin_user();

        response::set_title('后台首页');

        $row_admin_user = be::get_row('admin_user');
        $row_admin_user->load($my->id);
        response::set('admin_user', $row_admin_user);

        $admin_service_user = be::get_admin_service('user');
        $user_count = $admin_service_user->get_user_count();
        response::set('user_count', $user_count);

        $admin_service_system = be::get_admin_service('system');
        $admin_service_app = be::get_admin_service('app');
        $admin_service_theme = be::get_admin_service('theme');
        response::set('recent_logs', $admin_service_system->get_logs(array('user_id' => $my->id, 'offset' => 0, 'limit' => 10)));
        response::set('app_count', $admin_service_app->get_app_count());
        response::set('theme_count', $admin_service_theme->get_theme_count());

        response::display();
    }


    // 菜单管理
    public function menus()
    {
        $group_id = request::get('group_id', 0, 'int');

        $admin_service_menu = be::get_admin_service('menu');

        $groups = $admin_service_menu->get_menu_groups();
        if ($group_id == 0) $group_id = $groups[0]->id;

        response::set_title('菜单列表');
        response::set('menus', $admin_service_menu->get_menus($group_id));
        response::set('group_id', $group_id);
        response::set('groups', $groups);
        response::display();
    }

    public function menus_save()
    {
        $group_id = request::post('group_id', 0, 'int');

        $ids = request::post('id', array(), 'int');
        $parent_ids = request::post('parent_id', array(), 'int');
        $names = request::post('name', array());
        $urls = request::post('url', array(), 'html');
        $targets = request::post('target', array());
        $params = request::post('params', array());

        if (count($ids) > 0) {
            for ($i = 0, $n = count($ids); $i < $n; $i++) {
                $id = $ids[$i];

                if ($id == 0 && $names[$i] == '') continue;

                $row_system_menu = be::get_row('system_menu');
                if ($id != 0) $row_system_menu->load($id);
                $row_system_menu->group_id = $group_id;
                $row_system_menu->parent_id = $parent_ids[$i];
                $row_system_menu->name = $names[$i];
                $row_system_menu->url = $urls[$i];
                $row_system_menu->target = $targets[$i];
                $row_system_menu->params = $params[$i];
                $row_system_menu->rank = $i;
                $row_system_menu->save();
            }
        }

        $row_system_menu_group = be::get_row('system_menu_group');
        $row_system_menu_group->load($group_id);

        $service_system = be::get_service('system');
        $service_system->update_cache_menu($row_system_menu_group->class_name);

        system_log('修改菜单：' . $row_system_menu_group->name);

        response::set_message('保存菜单成功！');
        response::redirect('./?controller=system&task=menus&group_id=' . $group_id);
    }


    public function ajax_menu_delete()
    {
        $id = request::post('id', 0, 'int');
        if (!$id) {
            response::set('error', 2);
            response::set('message', '参数(id)缺失！');
        } else {
            $row_system_menu = be::get_row('system_menu');
            $row_system_menu->load($id);

            $admin_service_menu = be::get_admin_service('menu');
            if ($admin_service_menu->delete_menu($id)) {

                $row_system_menu_group = be::get_row('system_menu_group');
                $row_system_menu_group->load($row_system_menu->group_id);

                $service_system = be::get_service('system');
                $service_system->update_cache_menu($row_system_menu_group->class_name);

                response::set('error', 0);
                response::set('message', '删除菜单成功！');

                system_log('删除菜单: #' . $id . ' ' . $row_system_menu->name);
            } else {
                response::set('error', 3);
                response::set('message', $admin_service_menu->get_error());
            }
        }
        response::ajax();
    }

    public function menu_set_link()
    {
        $id = request::get('id', 0, 'int');
        $url = request::get('url', '', '');

        if ($url != '') $url = base64_decode($url);


        response::set('url', $url);

        $admin_service_system = be::get_admin_service('system');
        $apps = $admin_service_system->get_apps();
        response::set('apps', $apps);

        response::display();
    }

    public function ajax_menu_set_home()
    {
        $id = request::get('id', 0, 'int');
        if ($id == 0) {
            response::set('error', 1);
            response::set('message', '参数(id)缺失！');
        } else {
            $row_system_menu = be::get_row('system_menu');
            $row_system_menu->load($id);

            $admin_service_menu = be::get_admin_service('menu');
            if ($admin_service_menu->set_home_menu($id)) {

                $row_system_menu_group = be::get_row('system_menu_group');
                $row_system_menu_group->load($row_system_menu->group_id);

                $service_system = be::get_service('system');
                $service_system->update_cache_menu($row_system_menu_group->class_name);

                response::set('error', 0);
                response::set('message', '设置首页菜单成功！');

                system_log('设置新首页菜单：#' . $id . ' ' . $row_system_menu->name);
            } else {
                response::set('error', 2);
                response::set('message', $admin_service_menu->get_error());
            }
        }
        response::ajax();
    }


    // 菜单分组管理
    public function menu_groups()
    {
        $admin_service_menu = be::get_admin_service('menu');

        response::set_title('添加新菜单组');
        response::set('groups', $admin_service_menu->get_menu_groups());
        response::display();
    }


    // 修改菜单组
    public function menu_group_edit()
    {
        $id = request::request('id', 0, 'int');

        $row_menu_group = be::get_row('system_menu_group');
        if ($id != 0) $row_menu_group->load($id);

        if ($id != 0)
            response::set_title('修改菜单组');
        else
            response::set_title('添加新菜单组');

        response::set('menu_group', $row_menu_group);
        response::display();
    }

    // 保存修改菜单组
    public function menu_group_edit_save()
    {
        $id = request::post('id', 0, 'int');

        $class_name = request::post('class_name', '');
        $row_menu_group = be::get_row('system_menu_group');
        $row_menu_group->load(array('class_name' => $class_name));
        if ($row_menu_group->id > 0) {
            response::set_message('已存在(' . $class_name . ')类名！', 'error');
            response::redirect('./?controller=system&task=menu_group_edit&id=' . $id);
        }

        if ($id != 0) $row_menu_group->load($id);
        $row_menu_group->bind(request::post());
        if ($row_menu_group->save()) {
            system_log($id == 0 ? ('添加新菜单组：' . $row_menu_group->name) : ('修改菜单组：' . $row_menu_group->name));
            response::set_message($id == 0 ? '添加菜单组成功！' : '修改菜单组成功！');

            response::redirect('./?controller=system&task=menu_groups');
        } else {
            response::set_message($row_menu_group->get_error(), 'error');
            response::redirect('./?controller=system&task=menu_group_edit&id=' . $id);
        }
    }


    // 删除菜单组
    public function menu_group_delete()
    {
        $id = request::post('id', 0, 'int');

        $row_menu_group = be::get_row('system_menu_group');
        $row_menu_group->load($id);

        if ($row_menu_group->id == 0) {
            response::set_message('菜单组不存在！', 'error');
        } else {
            if (in_array($row_menu_group->class_name, array('north', 'south', 'dashboard'))) {
                response::set_message('系统菜单不可删除！', 'error');
            } else {
                $admin_service_menu = be::get_admin_service('menu');
                if ($admin_service_menu->delete_menu_group($row_menu_group->id)) {
                    system_log('成功删除菜单组！');
                    response::set_message('成功删除菜单组！');
                } else {
                    response::set_message($admin_service_menu->get_error(), 'error');
                }
            }
        }


        response::redirect('./?controller=system&task=menu_groups');

    }


    // 应用管理
    public function apps()
    {
        $admin_service_app = be::get_admin_service('app');
        $apps = $admin_service_app->get_apps();

        response::set_title('已安装的应用');
        response::set('apps', $apps);
        response::display();
    }

    public function remote_apps()
    {
        $admin_service_app = be::get_admin_service('app');
        $remote_apps = $admin_service_app->get_remote_apps(request::post());

        response::set_title('安装新应用');
        response::set('remote_apps', $remote_apps);
        response::display();
    }

    public function remote_app()
    {
        $app_id = request::get('app_id', 0, 'int');
        if ($app_id == 0) response::end('参数(app_id)缺失！');

        $admin_service_system = be::get_admin_service('system');

        $remote_app = $admin_service_system->get_remote_app($app_id);

        response::set_title('安装新应用：' . ($remote_app->status == '0' ? $remote_app->app->label : ''));
        response::set('remote_app', $remote_app);
        response::display();
    }

    public function ajax_install_app()
    {
        $app_id = request::get('app_id', 0, 'int');
        if ($app_id == 0) {
            response::set('error', 1);
            response::set('message', '参数(app_id)缺失！');
            response::ajax();
        }

        $admin_service_system = be::get_admin_service('system');
        $remote_app = $admin_service_system->get_remote_app($app_id);
        if ($remote_app->status != '0') {
            response::set('error', 2);
            response::set('message', $remote_app->description);
            response::ajax();
        }

        $app = $remote_app->app;
        if (file_exists(PATH_ADMIN . DS . 'apps' . DS . $app->name . 'php')) {
            response::set('error', 3);
            response::set('message', '已存在安装标识为' . $app->name . '的应用');
            response::ajax();
        }

        if ($admin_service_system->install_app($app)) {
            system_log('安装新应用：' . $app->name);

            response::set('error', 0);
            response::set('message', '应用安装成功！');
        } else {
            response::set('error', 4);
            response::set('message', $admin_service_system->get_error());
        }

        response::ajax();
    }

    public function ajax_uninstall_app()
    {
        $app_name = request::get('app_name', '');
        if ($app_name == '') {
            response::set('error', 1);
            response::set('message', '参数(app_name)缺失！');
            response::ajax();
        }

        $admin_service_system = be::get_admin_service('system');
        if ($admin_service_system->uninstall_app($app_name)) {
            system_log('卸载应用：' . $app_name);

            response::set('error', 0);
            response::set('message', '应用卸载成功！');
        } else {
            response::set('error', 2);
            response::set('message', $admin_service_system->get_error());
        }

        response::ajax();
    }


    // 主题管理
    public function themes()
    {
        $admin_service_theme = be::get_admin_service('theme');
        $themes = $admin_service_theme->get_themes(request::post());

        response::set_title('已安装的主题');
        response::set('themes', $themes);
        response::display();
    }

    // 设置默认主题
    public function ajax_theme_set_default()
    {
        $theme = request::get('theme', '');
        if ($theme == '') {
            response::set('error', 1);
            response::set('message', '参数(theme)缺失！');
        } else {
            $admin_service_theme = be::get_admin_service('theme');
            if ($admin_service_theme->set_default_theme($theme)) {
                system_log('设置主题（' . $theme . ') 为默认主题！');

                response::set('error', 0);
                response::set('message', '设置默认主题成功！');
            } else {
                response::set('error', 2);
                response::set('message', $admin_service_theme->get_error());
            }
        }
        response::ajax();
    }


    // 在线主题
    public function remote_themes()
    {
        $admin_service_theme = be::get_admin_service('theme');

        $local_themes = $admin_service_theme->get_themes();
        $remote_themes = $admin_service_theme->get_remote_themes(request::post());

        response::set_title('安装新主题');
        response::set('local_themes', $local_themes);
        response::set('remote_themes', $remote_themes);
        response::display();
    }

    // 安装主题
    public function ajax_install_theme()
    {
        $theme_id = request::get('theme_id', 0, 'int');
        if ($theme_id == 0) {
            response::set('error', 1);
            response::set('message', '参数(theme_id)缺失！');
            response::ajax();
        }

        $admin_service_system = be::get_admin_service('system');
        $remote_theme = $admin_service_system->get_remote_theme($theme_id);

        if ($remote_theme->status != '0') {
            response::set('error', 2);
            response::set('message', $remote_theme->description);
            response::ajax();
        }

        if ($admin_service_system->install_theme($remote_theme->theme)) {
            system_log('安装新主题：' . $remote_theme->theme->name);

            response::set('error', 0);
            response::set('message', '主题新安装成功！');
            response::ajax();
        } else {
            response::set('error', 3);
            response::set('message', $admin_service_system->get_error());
            response::ajax();
        }
    }


    // 删除主题
    public function ajax_uninstall_theme()
    {
        $theme = request::get('theme', '');
        if ($theme == '') {
            response::set('error', 1);
            response::set('message', '参数(theme)缺失！');
            response::ajax();
        }

        $admin_service_system = be::get_admin_service('system');
        if ($admin_service_system->uninstall_theme($theme)) {
            system_log('卸载主题：' . $theme);

            response::set('error', 0);
            response::set('message', '主题卸载成功！');
            response::ajax();
        } else {
            response::set('error', 2);
            response::set('message', $admin_service_system->get_error());
            response::ajax();
        }
    }


    // 系统配置
    public function config()
    {
        response::set_title('系统基本设置');
        response::set('config', be::get_config('system'));
        response::display();
    }

    public function config_save()
    {
        $config = be::get_config('system');
        $config->offline = request::post('offline', 0, 'int');
        $config->offline_message = request::post('offline_message', '', 'html');
        $config->site_name = request::post('site_name', '');
        $config->sef = request::post('sef', 0, 'int');
        $config->sef_suffix = request::post('sef_suffix', '');
        $config->home_title = request::post('home_title', '');
        $config->home_meta_keywords = request::post('home_meta_keywords', '');
        $config->home_meta_description = request::post('home_meta_description', '');

        $allow_upload_file_types = request::post('allow_upload_file_types', '');
        $allow_upload_file_types = explode(',', $allow_upload_file_types);
        $allow_upload_file_types = array_map('trim', $allow_upload_file_types);
        $config->allow_upload_file_types = $allow_upload_file_types;

        $allow_upload_image_types = request::post('allow_upload_image_types', '');
        $allow_upload_image_types = explode(',', $allow_upload_image_types);
        $allow_upload_image_types = array_map('trim', $allow_upload_image_types);
        $config->allow_upload_image_types = $allow_upload_image_types;

        $service_system = be::get_service('system');
        $service_system->update_config($config, PATH_DATA . DS . 'config' . DS . 'system.php');

        system_log('改动系统基本设置');

        response::set_message('保存成功！');
        response::redirect('./?controller=system&task=config');
    }


    // 邮件服务配置
    public function config_mail()
    {
        $config = be::get_config('mail');

        response::set_title('发送邮件设置');
        response::set('config', $config);
        response::display();
    }

    public function config_mail_save()
    {
        $config = be::get_config('mail');

        $config->from_mail = request::post('from_mail', '');
        $config->from_name = request::post('from_name', '');
        $config->smtp = request::post('smtp', 0, 'int');
        $config->smtp_host = request::post('smtp_host', '');
        $config->smtp_port = request::post('smtp_port', 0, 'int');
        $config->smtp_user = request::post('smtp_user', '');
        $config->smtp_pass = request::post('smtp_pass', '');
        $config->smtp_secure = request::post('smtp_secure', '');

        $service_system = be::get_service('system');
        $service_system->update_config($config, PATH_DATA . DS . 'config' . DS . 'mail.php');

        system_log('改动发送邮件设置');

        response::set_message('保存成功！');
        response::redirect('./?controller=system&task=config_mail');
    }

    public function config_mail_test()
    {
        response::set_title('发送邮件测试');
        response::display();
    }

    public function config_mail_test_save()
    {
        $to_email = request::post('to_email', '');
        $subject = request::post('subject', '');
        $body = request::post('body', '', 'html');

        $lib_mail = be::get_lib('mail');
        $lib_mail->set_subject($subject);
        $lib_mail->set_body($body);
        $lib_mail->to($to_email);

        if ($lib_mail->send()) {
            system_log('发送测试邮件到 ' . $to_email . ' -成功');
            response::set_message('发送邮件成功！');
        } else {
            $error = $lib_mail->get_error();

            system_log('发送测试邮件到 ' . $to_email . ' -失败：' . $error);
            response::set_message('发送邮件失败：' . $error, 'error');
        }

        response::redirect('./?controller=system&task=config_mail_test&to_email=' . $to_email);
    }


    // 水印设置
    public function config_watermark()
    {
        $config = be::get_config('watermark');

        response::set_title('水印设置');
        response::set('config', $config);
        response::display();
    }

    private function is_rgb_color($arr)
    {
        if (!is_array($arr)) return false;
        if (count($arr) != 3) return false;
        foreach ($arr as $x) {
            if (!is_numeric($x)) return false;
            $x = intval($x);
            if ($x < 0) return false;
            if ($x > 255) return false;
        }
        return true;
    }

    public function config_watermark_save()
    {
        $config = be::get_config('watermark');

        $config->watermark = request::post('watermark', 0, 'int');
        $config->type = request::post('type', '');
        $config->position = request::post('position', '');
        $config->offset_x = request::post('offset_x', 0, 'int');
        $config->offset_y = request::post('offset_y', 0, 'int');

        $config->text = request::post('text', '');
        $config->text_size = request::post('text_size', 0, 'int');

        $text_color = request::post('text_color', '');
        $text_colors = explode(',', $text_color);
        $text_colors = array_map('trim', $text_colors);

        if (!$this->is_rgb_color($text_colors)) $text_colors = array(255, 0, 0);
        $config->text_color = $text_colors;

        $image = $_FILES['image'];
        if ($image['error'] == 0) {
            $lib_image = be::get_lib('image');
            $lib_image->open($image['tmp_name']);
            if ($lib_image->is_image()) {
                $watermark_name = date('YmdHis') . '.' . $lib_image->get_type();
                $watermark_path = PATH_DATA . DS . 'system' . DS . 'watermark' . DS . $watermark_name;
                if (move_uploaded_file($image['tmp_name'], $watermark_path)) {
                    // @unlink(PATH_DATA.DS.'system'.DS.'watermark'.DS.$config->image);
                    $config->image = $watermark_name;
                }
            }
        }

        $service_system = be::get_service('system');
        $service_system->update_config($config, PATH_DATA . DS . 'config' . DS . 'watermark.php');

        system_log('修改水印设置');

        response::set_message('保存成功！');
        response::redirect('./?controller=system&task=config_watermark');
    }

    public function config_watermark_test()
    {
        $src = PATH_DATA . DS . 'system' . DS . 'watermark' . DS . 'test-0.jpg';
        $dst = PATH_DATA . DS . 'system' . DS . 'watermark' . DS . 'test-1.jpg';

        if (!file_exists($src)) response::end(DATA . '/system/watermakr/test-0.jpg 文件不存在');
        if (file_exists($dst)) @unlink($dst);

        copy($src, $dst);

        sleep(1);

        $admin_service_system = be::get_admin_service('system');
        $admin_service_system->watermark($dst);

        response::set_title('水印预览');
        response::display();
    }

    public function cache()
    {
        response::set_title('缓存管理');
        response::display();
    }

    public function clear_cache()
    {
        $type = request::request('type');
        $service_system = be::get_service('system');

        $service_system->clear_cache($type);

        system_log('删除缓存（' . $type . '）');

        response::set_message('删除缓存成功！');
        response::redirect('./?controller=system&task=cache');
    }

    // 错误日志
    public function error_logs()
    {
        $year = request::request('year', date('Y'));
        $month = request::request('month', date('m'));
        $day = request::request('day', date('d'));

        $limit = request::post('limit', -1, 'int');
        if ($limit == -1) {
            $admin_config_system = be::get_admin_config('system');
            $limit = $admin_config_system->limit;
        }

        response::set_title('错误日志列表');

        $admin_service_system_error_log = be::get_admin_service('system_error_log');
        $years = $admin_service_system_error_log->get_years();
        response::set('years', $years);

        if (!$year && count($years)) $year = $years[0];

        if ($year && in_array($year, $years)) {
            response::set('year', $year);

            $months = $admin_service_system_error_log->get_months($year);
            response::set('months', $months);

            if (!$month && count($months)) $month = $months[0];

            if ($month && in_array($month, $months)) {
                response::set('month', $month);

                $days = $admin_service_system_error_log->get_days($year, $month);
                response::set('days', $days);

                if (!$day && count($days)) $day = $days[0];

                if ($day && in_array($day, $days)) {
                    response::set('day', $day);

                    $option = array();
                    $option['year'] = $year;
                    $option['month'] = $month;
                    $option['day'] = $day;

                    $error_count = $admin_service_system_error_log->get_error_log_count($option);
                    response::set('error_log_count', $error_count);

                    $pagination = be::get_admin_ui('pagination');
                    $pagination->set_limit($limit);
                    $pagination->set_total($error_count);
                    $pagination->set_page(request::request('page', 1, 'int'));
                    response::set('pagination', $pagination);

                    $option['offset'] = $pagination->get_offset();
                    $option['limit'] = $limit;

                    $error_logs = $admin_service_system_error_log->get_error_logs($option);
                    response::set('error_logs', $error_logs);
                }
            }
        }

        response::display();
    }

    public function error_log()
    {
        $year = request::request('year');
        $month = request::request('month');
        $day = request::request('day');
        $index = request::request('index', 0, 'int');

        $admin_service_system_error_log = be::get_admin_service('system_error_log');
        $error_log = $admin_service_system_error_log->get_error_log($year, $month, $day, $index);
        if (!$error_log) response::end($admin_service_system_error_log->get_error());

        response::set_title('错误详情');
        response::set('error_log', $error_log);
        response::display();
    }


    // 系统日志
    public function logs()
    {
        $user_id = request::post('user_id', 0, 'int');
        $key = request::post('key', '');
        $limit = request::post('limit', -1, 'int');
        if ($limit == -1) {
            $admin_config_system = be::get_admin_config('system');
            $limit = $admin_config_system->limit;
        }

        $admin_service_system = be::get_admin_service('system');
        response::set_title('系统日志');

        $pagination = be::get_admin_ui('pagination');
        $pagination->set_limit($limit);
        $pagination->set_total($admin_service_system->get_log_count(array('user_id' => $user_id, 'key' => $key)));
        $pagination->set_page(request::post('page', 1, 'int'));

        response::set('pagination', $pagination);
        response::set('user_id', $user_id);
        response::set('key', $key);
        response::set('admin_users', $admin_service_system->get_admin_users());
        response::set('logs', $admin_service_system->get_logs(array('user_id' => $user_id, 'key' => $key, 'offset' => $pagination->get_offset(), 'limit' => $limit)));

        response::display();
    }

    // 后台登陆日志
    public function ajax_delete_logs()
    {
        $admin_service_system = be::get_admin_service('system');
        $admin_service_system->delete_logs();

        system_log('删除三个月前系统日志');

        response::set('error', 0);
        response::set('message', '删除日志成功！');
        response::ajax();
    }

    public function history_back()
    {
        $lib_history = be::get_lib('history');
        $lib_history->back();
    }

}

?>