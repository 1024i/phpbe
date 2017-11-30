<?php

namespace admin\controller;

use system\be;
use system\request;
use system\response;

class admin_user extends \admin\system\controller
{

    // 登陆页面
    public function login()
    {
        $my = be::get_admin_user();

        if ($my->id > 0) {
            response::redirect('./?controller=system&task=dashboard');
        }

        response::set_title('登录');
        response::display();
    }

    // 登陆检查
    public function ajax_login_check()
    {
        $username = request::post('username', '');
        $password = request::post('password', '');

        if ($username == '') {
            response::set('error', 1);
            response::set('message', '请输入管理员名！');
            response::ajax();
        }

        if ($password == '') {
            response::set('error', 2);
            response::set('message', '请输入密码！');
            response::ajax();
        }

        $admin_service_admin_user = be::get_admin_service('admin_user');
        $user = $admin_service_admin_user->login($username, $password);

        if ($user) {
            system_log('登录后台');

            response::set('error', 0);
            response::set('message', '登录成功！');
            response::ajax();
        } else {
            response::set('error', 2);
            response::set('message', $admin_service_admin_user->get_error());
            response::ajax();
        }
    }

    // 退出登陆
    public function logout()
    {
        $admin_service_admin_user = be::get_admin_service('admin_user');
        $admin_service_admin_user->logout();

        response::set_message('成功退出！');
        response::redirect('./?controller=admin_user&task=login');
    }

    // 管理管理员列表
    public function users()
    {
        $order_by = request::post('order_by', 'id');
        $order_by_dir = request::post('order_by_dir', 'ASC');
        $key = request::post('key', '');
        $status = request::post('status', -1, 'int');
        $limit = request::post('limit', -1, 'int');
        $role_id = request::post('role_id', 0, 'int');

        if ($limit == -1) {
            $admin_config_system = be::get_admin_config('system');
            $limit = $admin_config_system->limit;
        }

        $option = array(
            'key' => $key,
            'status' => $status
        );
        if ($role_id > 0) $option['role_id'] = $role_id;

        $admin_service_admin_user = be::get_admin_service('admin_user');

        response::set_title('管理员列表');

        $pagination = be::get_admin_ui('pagination');
        $pagination->set_limit($limit);
        $pagination->set_total($admin_service_admin_user->get_user_count($option));
        $pagination->set_page(request::post('page', 1, 'int'));

        response::set('pagination', $pagination);
        response::set('order_by', $order_by);
        response::set('order_by_dir', $order_by_dir);
        response::set('key', $key);
        response::set('status', $status);
        response::set('role_id', $role_id);

        $option['order_by'] = $order_by;
        $option['order_by_dir'] = $order_by_dir;
        $option['offset'] = $pagination->get_offset();
        $option['limit'] = $limit;

        response::set('users', $admin_service_admin_user->get_users($option));

        response::set('roles', $admin_service_admin_user->get_roles());
        response::display();

        $lib_history = be::get_lib('history');
        $lib_history->save();
    }

    // 修改管理员
    public function edit()
    {
        $id = request::request('id', 0, 'int');

        $admin_user = be::get_row('admin_user');
        if ($id != 0) $admin_user->load($id);

        if ($id != 0)
            response::set_title('修改管理员资料');
        else
            response::set_title('添加新管理员');

        response::set('admin_user', $admin_user);

        $admin_service_admin_user = be::get_admin_service('admin_user');
        response::set('roles', $admin_service_admin_user->get_roles());

        response::display();
    }

    // 保存修改管理员
    public function edit_save()
    {
        $id = request::post('id', 0, 'int');

        if (request::post('username', '') == '') {
            response::set_message('请输入管理员名！', 'error');
            response::redirect('./?controller=admin_user&task=edit&id=' . $id);
        }

        if (request::post('email', '') == '') {
            response::set_message('请输入邮箱！', 'error');
            response::redirect('./?controller=admin_user&task=edit&id=' . $id);
        }

        $password = request::post('password', '');
        if ($password != request::post('password2', '')) {
            response::set_message('两次输入的密码不匹配！', 'error');
            response::redirect('./?controller=admin_user&task=edit&id=' . $id);
        }

        if ($id == 0 && $password == '') {
            response::set_message('密码不能为空！', 'error');
            response::redirect('./?controller=admin_user&task=edit&id=' . $id);
        }

        $row_admin_user = be::get_row('admin_user');
        if ($id > 0) $row_admin_user->load($id);

        $row_admin_user->bind(request::post());
        $admin_service_admin_user = be::get_admin_service('admin_user');

        if (!$admin_service_admin_user->is_username_available($row_admin_user->username, $id)) {
            response::set_message('管理员名(' . $row_admin_user->username . ')已被占用！', 'error');
            response::redirect('./?controller=admin_user&task=edit&id=' . $id);
        }

        if (!$admin_service_admin_user->is_email_available($row_admin_user->email, $id)) {
            response::set_message('邮箱(' . $row_admin_user->email . ')已被占用！', 'error');
            response::redirect('./?controller=admin_user&task=edit&id=' . $id);
        }

        if ($password != '') {
            $row_admin_user->password = $admin_service_admin_user->encrypt_password($password);
        } else
            unset($row_admin_user->password);

        if ($id == 0) {
            $row_admin_user->create_time = time();
            $row_admin_user->last_visit_time = time();
        } else {
            unset($row_admin_user->create_time);
            unset($row_admin_user->last_visit_time);
        }

        $row_admin_user->save();

        $config_user = be::get_admin_config('admin_user');

        $avatar = $_FILES['avatar'];
        if ($avatar['error'] == 0) {
            $lib_image = be::get_lib('image');
            $lib_image->open($avatar['tmp_name']);
            if ($lib_image->is_image()) {
                $admin_service_admin_user->delete_avatar_file($row_admin_user);

                $t = date('YmdHis');

                $lib_image->resize($config_user->avatar_l_w, $config_user->avatar_l_h, 'north');
                $lib_image->save(PATH_DATA . DS . 'admin_user' . DS . 'avatar' . DS . $row_admin_user->id . '_' . $t . '_l.' . $lib_image->get_type());
                $row_admin_user->avatar_l = $row_admin_user->id . '_' . $t . '_l.' . $lib_image->get_type();

                $lib_image->resize($config_user->avatar_m_w, $config_user->avatar_m_h, 'north');
                $lib_image->save(PATH_DATA . DS . 'admin_user' . DS . 'avatar' . DS . $row_admin_user->id . '_' . $t . '_m.' . $lib_image->get_type());
                $row_admin_user->avatar_m = $row_admin_user->id . '_' . $t . '_m.' . $lib_image->get_type();

                $lib_image->resize($config_user->avatar_s_w, $config_user->avatar_s_h, 'north');
                $lib_image->save(PATH_DATA . DS . 'admin_user' . DS . 'avatar' . DS . $row_admin_user->id . '_' . $t . '_s.' . $lib_image->get_type());
                $row_admin_user->avatar_s = $row_admin_user->id . '_' . $t . '_s.' . $lib_image->get_type();

                $row_admin_user->save();
            }
        }

        response::set_message($id == 0 ? '成功添加新管理员！' : '成功修改管理员资料！');
        system_log($id == 0 ? ('添加新管理员：' . $row_admin_user->username) : ('修改管理员(' . $row_admin_user->username . ')资料'));

        $lib_history = be::get_lib('history');
        $lib_history->back();
    }

    public function check_username()
    {
        $username = request::get('username', '');

        $admin_service_admin_user = be::get_admin_service('admin_user');
        echo $admin_service_admin_user->is_username_available($username) ? 'true' : 'false';
    }

    public function check_email()
    {
        $email = request::get('email', '');

        $admin_service_admin_user = be::get_admin_service('admin_user');
        echo $admin_service_admin_user->is_email_available($email) ? 'true' : 'false';
    }

    public function unblock()
    {
        $ids = request::post('id', '');

        $admin_service_admin_user = be::get_admin_service('admin_user');
        if ($admin_service_admin_user->unblock($ids)) {
            response::set_message('启用管理员账号成功！');
            system_log('启用管理员账号：#' . $ids);
        } else
            response::set_message($admin_service_admin_user->get_error(), 'error');

        $lib_history = be::get_lib('history');
        $lib_history->back();
    }

    public function block()
    {
        $ids = request::post('id', '');

        $admin_service_admin_user = be::get_admin_service('admin_user');
        if ($admin_service_admin_user->block($ids)) {
            response::set_message('屏蔽管理员账号成功！');
            system_log('屏蔽管理员账号：#' . $ids);
        } else
            response::set_message($admin_service_admin_user->get_error(), 'error');

        $lib_history = be::get_lib('history');
        $lib_history->back();
    }

    public function ajax_init_avatar()
    {
        $user_id = request::get('user_id', 0, 'int');

        $admin_service_admin_user = be::get_admin_service('admin_user');
        if ($admin_service_admin_user->init_avatar($user_id)) {
            system_log('删除 #' . $user_id . ' 管理员头像');

            response::set('error', 0);
            response::set('message', '删除头像成功！');
        } else {
            response::set('error', 2);
            response::set('message', $admin_service_admin_user->get_error());
        }

        response::ajax();

    }

    public function delete()
    {
        $ids = request::post('id', '');

        $admin_service_admin_user = be::get_admin_service('admin_user');
        if ($admin_service_admin_user->delete($ids)) {
            response::set_message('删除管理员账号成功！');
            system_log('删除管理员账号：#' . $ids);
        } else
            response::set_message($admin_service_admin_user->get_error(), 'error');

        $lib_history = be::get_lib('history');
        $lib_history->back();
    }


    public function roles()
    {
        $admin_service_admin_user = be::get_admin_service('admin_user');
        $roles = $admin_service_admin_user->get_roles();

        foreach ($roles as $role) {
            $role->user_count = $admin_service_admin_user->get_user_count(array('role_id' => $role->id));
        }

        response::set_title('管理员角色');
        response::set('roles', $roles);
        response::set('tab', 'backend');
        response::display();
    }

    public function roles_save()
    {
        $ids = request::post('id', array(), 'int');
        $names = request::post('name', array());
        $notes = request::post('note', array());

        if (count($ids) > 0) {
            for ($i = 0, $n = count($ids); $i < $n; $i++) {
                $id = $ids[$i];

                if ($id == 1) continue;

                if ($id == 0 && $names[$i] == '') continue;

                $row_admin_user_role = be::get_row('admin_user_role');
                if ($id != 0) $row_admin_user_role->load($id);
                $row_admin_user_role->name = $names[$i];
                $row_admin_user_role->note = $notes[$i];
                $row_admin_user_role->rank = $i;
                $row_admin_user_role->save();
            }
        }

        $admin_service_admin_user = be::get_admin_service('admin_user');
        $admin_service_admin_user->update_admin_user_roles();

        system_log('修改后台管理员组');

        response::set_message('修改后台管理员组成功！');
        response::redirect('./?controller=admin_user&task=roles');
    }

    public function ajax_delete_role()
    {
        $role_id = request::post('id', 0, 'int');
        if ($role_id == 0) {
            response::set('error', 1);
            response::set('message', '参数(role_id)缺失！');
            response::ajax();
        }

        $row_admin_user_role = be::get_row('admin_user_role');
        $row_admin_user_role->load($role_id);
        if ($row_admin_user_role->id == 0) {
            response::set('error', 2);
            response::set('message', '不存在的分组');
            response::ajax();
        }

        $admin_service_user = be::get_admin_service('user');
        $user_count = $admin_service_user->get_user_count(array('role_id' => $role_id));
        if ($user_count > 0) {
            response::set('error', 3);
            response::set('message', '当前有' . $user_count . '个管理员属于这个分组，禁止删除！');
            response::ajax();
        }

        $row_admin_user_role->delete();

        system_log('删除后台管理员组：' . $row_admin_user_role->name);

        response::set('error', 0);
        response::set('message', '删除管理员组成功！');
        response::ajax();
    }

    public function role_permissions()
    {
        $role_id = request::get('role_id', 0, 'int');
        if ($role_id == 0) response::end('参数(role_id)缺失！');

        $row_admin_user_role = be::get_row('admin_user_role');
        $row_admin_user_role->load($role_id);
        if ($row_admin_user_role->id == 0) response::end('不存在的分组！');

        $admin_service_app = be::get_admin_service('app');
        $apps = $admin_service_app->get_apps();

        response::set_title('管理员组(' . $row_admin_user_role->name . ')权限设置');
        response::set('role', $row_admin_user_role);
        response::set('apps', $apps);
        response::display();
    }

    public function role_permissions_save()
    {
        $role_id = request::post('role_id', 0, 'int');
        if ($role_id == 0) response::end('参数(role_id)缺失！');

        $row_admin_user_role = be::get_row('admin_user_role');
        $row_admin_user_role->load($role_id);
        if ($row_admin_user_role->id == 0) response::end('不存在的分组！');
        $row_admin_user_role->permission = request::post('permission', 0, 'int');

        if ($row_admin_user_role->permission == -1) {
            $public_permissions = [];
            $admin_service_app = be::get_admin_service('app');
            $apps = $admin_service_app->get_apps();
            foreach ($apps as $app) {
                $app_permissions = $app->get_admin_permissions();
                if (count($app_permissions) > 0) {
                    foreach ($app_permissions as $key => $val) {
                        if ($key == '-') {
                            $public_permissions = array_merge($public_permissions, $val);
                        }
                    }
                }
            }

            $permissions = request::post('permissions', array());
            $permissions = array_merge($public_permissions, $permissions);
            $permissions = implode(',', $permissions);
            $row_admin_user_role->permissions = $permissions;
        } else {
            $row_admin_user_role->permissions = '';
        }

        $row_admin_user_role->save();

        $admin_service_admin_user = be::get_admin_service('admin_user');
        $admin_service_admin_user->update_admin_user_role($role_id);

        system_log('修改管理员组(' . $row_admin_user_role->name . ')权限');

        response::set_message('修改管理员组权限成功！');
        response::redirect('./?controller=admin_user&task=roles');
    }


    // 后台登陆日志
    public function logs()
    {
        $key = request::post('key', '');
        $success = request::post('success', -1, 'int');
        $limit = request::post('limit', -1, 'int');

        if ($limit == -1) {
            $admin_config_system = be::get_admin_config('system');
            $limit = $admin_config_system->limit;
        }

        $option = array(
            'key' => $key,
            'success' => $success
        );

        $admin_service_admin_user = be::get_admin_service('admin_user');
        response::set_title('登陆日志');

        $pagination = be::get_admin_ui('pagination');
        $pagination->set_limit($limit);
        $pagination->set_total($admin_service_admin_user->get_log_count($option));
        $pagination->set_page(request::post('page', 1, 'int'));

        $option['offset'] = $pagination->get_offset();
        $option['limit'] = $limit;

        response::set('pagination', $pagination);
        response::set('key', $key);
        response::set('success', $success);
        response::set('logs', $admin_service_admin_user->get_logs($option));

        response::display();
    }

    // 后台登陆日志
    public function ajax_delete_logs()
    {
        $admin_service_admin_user = be::get_admin_service('admin_user');
        $admin_service_admin_user->delete_logs();

        system_log('删除管理员登陆日志');

        response::set('error', 0);
        response::set('message', '删除管理员登陆日志成功！');
        response::ajax();
    }


    public function setting()
    {
        response::set_title('管理员系统设置');
        response::set('config_admin_user', be::get_admin_config('admin_user'));
        response::display();
    }

    public function setting_save()
    {
        $admin_config_admin_user = be::get_admin_config('admin_user');
        $admin_config_admin_user->avatar_s_w = request::post('avatar_s_w', 0, 'int');
        $admin_config_admin_user->avatar_s_h = request::post('avatar_s_h', 0, 'int');
        $admin_config_admin_user->avatar_m_w = request::post('avatar_m_w', 0, 'int');
        $admin_config_admin_user->avatar_m_h = request::post('avatar_m_h', 0, 'int');
        $admin_config_admin_user->avatar_l_w = request::post('avatar_l_w', 0, 'int');
        $admin_config_admin_user->avatar_l_h = request::post('avatar_l_h', 0, 'int');

        // 缩图图大图
        $default_avatar_l = $_FILES['default_avatar_l'];
        if ($default_avatar_l['error'] == 0) {
            $lib_image = be::get_lib('image');
            $lib_image->open($default_avatar_l['tmp_name']);
            if ($lib_image->is_image()) {
                $default_avatar_l_name = date('YmdHis') . '_l.' . $lib_image->get_type();
                $default_avatar_l_path = PATH_DATA . DS . 'admin_user' . DS . 'avatar' . DS . 'default' . DS . $default_avatar_l_name;
                if (move_uploaded_file($default_avatar_l['tmp_name'], $default_avatar_l_path)) {
                    // @unlink(PATH_DATA.DS.'user'.DS.'avatar'.DS.'default'.DS.$admin_config_admin_user->default_avatar_l);
                    $admin_config_admin_user->default_avatar_l = $default_avatar_l_name;
                }
            }
        }


        // 缩图图中图
        $default_avatar_m = $_FILES['default_avatar_m'];
        if ($default_avatar_m['error'] == 0) {
            $lib_image = be::get_lib('image');
            $lib_image->open($default_avatar_m['tmp_name']);
            if ($lib_image->is_image()) {
                $default_avatar_m_name = date('YmdHis') . '_m.' . $lib_image->get_type();
                $default_avatar_m_path = PATH_DATA . DS . 'admin_user' . DS . 'avatar' . DS . 'default' . DS . $default_avatar_m_name;
                if (move_uploaded_file($default_avatar_m['tmp_name'], $default_avatar_m_path)) {
                    // @unlink(PATH_DATA.DS.'user'.DS.'avatar'.DS.'default'.DS.$admin_config_admin_user->default_avatar_m);
                    $admin_config_admin_user->default_avatar_m = $default_avatar_m_name;
                }
            }
        }

        // 缩图图小图
        $default_avatar_s = $_FILES['default_avatar_s'];
        if ($default_avatar_s['error'] == 0) {
            $lib_image = be::get_lib('image');
            $lib_image->open($default_avatar_s['tmp_name']);
            if ($lib_image->is_image()) {
                $default_avatar_s_name = date('YmdHis') . '_s.' . $lib_image->get_type();
                $default_avatar_s_path = PATH_DATA . DS . 'admin_user' . DS . 'avatar' . DS . 'default' . DS . $default_avatar_s_name;
                if (move_uploaded_file($default_avatar_s['tmp_name'], $default_avatar_s_path)) {
                    // @unlink(PATH_DATA.DS.'user'.DS.'avatar'.DS.'default'.DS.$admin_config_admin_user->default_avatar_s);
                    $admin_config_admin_user->default_avatar_s = $default_avatar_s_name;
                }
            }
        }

        $service_system = be::get_service('system');
        $service_system->update_config($admin_config_admin_user, PATH_DATA . DS . 'admin_config' . DS . 'admin_user.php');

        system_log('设置管理员系统参数');

        response::set_message('成功保存管理员系统设置！');
        response::redirect('./?controller=admin_user&task=setting');
    }
}

?>