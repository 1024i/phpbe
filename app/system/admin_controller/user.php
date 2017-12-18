<?php
namespace app\system\admin_controller;

use system\be;
use system\request;
use system\response;

class user extends \system\admin_controller
{

    // 管理用户列表
    public function users()
    {
        $order_by = request::post('order_by', 'id');
        $order_by_dir = request::post('order_by_dir', 'ASC');
        $key = request::post('key', '');
        $status = request::post('status', -1, 'int');
        $limit = request::post('limit', -1, 'int');
        $role_id = request::post('role_id', 0, 'int');

        if ($limit == -1) {
            $admin_config_system = be::get_config('system.admin');
            $limit = $admin_config_system->limit;
        }

        $option = array(
            'key' => $key,
            'status' => $status
        );
        if ($role_id > 0) $option['role_id'] = $role_id;

        $admin_service_user = be::get_service('system.user');

        response::set_title('用户列表');

        $pagination = be::get_ui('pagination');
        $pagination->set_limit($limit);
        $pagination->set_total($admin_service_user->get_user_count($option));
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

        response::set('users', $admin_service_user->get_users($option));

        response::set('roles', $admin_service_user->get_roles());
        response::display();

        $lib_history = be::get_lib('history');
        $lib_history->save();
    }

    // 修改用户
    public function edit()
    {
        $id = request::request('id', 0, 'int');

        $user = be::get_row('system.user');
        if ($id != 0) $user->load($id);

        if ($id != 0)
            response::set_title('修改用户资料');
        else
            response::set_title('添加新用户');

        response::set('user', $user);

        $admin_service_user = be::get_service('system.user');
        response::set('roles', $admin_service_user->get_roles());

        response::display();
    }

    // 保存修改用户
    public function edit_save()
    {
        $id = request::post('id', 0, 'int');

        if (request::post('username', '') == '') {
            response::set_message('请输入用户名！', 'error');
            response::redirect('./?controller=user&task=edit&id=' . $id);
        }

        if (request::post('email', '') == '') {
            response::set_message('请输入邮箱！', 'error');
            response::redirect('./?controller=user&task=edit&id=' . $id);
        }

        $password = request::post('password', '');
        if ($password != request::post('password2', '')) {
            response::set_message('两次输入的密码不匹配！', 'error');
            response::redirect('./?controller=user&task=edit&id=' . $id);
        }

        if ($id == 0 && $password == '') {
            response::set_message('密码不能为空！', 'error');
            response::redirect('./?controller=user&task=edit&id=' . $id);
        }

        $row_user = be::get_row('system.user');
        if ($id > 0) $row_user->load($id);

        $row_user->bind(request::post());

        $admin_service_user = be::get_service('system.user');

        if (!$admin_service_user->is_username_available($row_user->username, $id)) {
            response::set_message('用户名(' . $row_user->username . ')已被占用！', 'error');
            response::redirect('./?controller=user&task=edit&id=' . $id);
        }

        if (!$admin_service_user->is_email_available($row_user->email, $id)) {
            response::set_message('邮箱(' . $row_user->email . ')已被占用！', 'error');
            response::redirect('./?controller=user&task=edit&id=' . $id);
        }

        if ($password != '') {
            $service_user = be::get_service('system.user');
            $row_user->password = $service_user->encrypt_password($password);
        } else
            unset($row_user->password);

        if ($id == 0) {
            $row_user->register_time = time();
            $row_user->last_login_time = 0;
        } else {
            unset($row_user->register_time);
            unset($row_user->last_login_time);
        }

        if (!$row_user->save()) {
            response::end($row_user->get_error());
        }

        $config_user = be::get_config('system.user');

        $avatar = $_FILES['avatar'];
        if ($avatar['error'] == 0) {
            $lib_image = be::get_lib('image');
            $lib_image->open($avatar['tmp_name']);
            if ($lib_image->is_image()) {
                $admin_service_user->delete_avatar_file($row_user);

                $t = date('YmdHis');

                $lib_image->resize($config_user->avatar_l_w, $config_user->avatar_l_h, 'north');
                $lib_image->save(PATH_DATA . DS . 'system' . DS . 'user' . DS . 'avatar' . DS . $row_user->id . '_' . $t . '_l.' . $lib_image->get_type());
                $row_user->avatar_l = $row_user->id . '_' . $t . '_l.' . $lib_image->get_type();

                $lib_image->resize($config_user->avatar_m_w, $config_user->avatar_m_h, 'north');
                $lib_image->save(PATH_DATA . DS . 'system' . DS . 'user' . DS . 'avatar' . DS . $row_user->id . '_' . $t . '_m.' . $lib_image->get_type());
                $row_user->avatar_m = $row_user->id . '_' . $t . '_m.' . $lib_image->get_type();

                $lib_image->resize($config_user->avatar_s_w, $config_user->avatar_s_h, 'north');
                $lib_image->save(PATH_DATA . DS . 'system' . DS . 'user' . DS . 'avatar' . DS . $row_user->id . '_' . $t . '_s.' . $lib_image->get_type());
                $row_user->avatar_s = $row_user->id . '_' . $t . '_s.' . $lib_image->get_type();

                if (!$row_user->save()) {
                    response::end($row_user->get_error());
                }
            }
        }

        if ($id == 0) {
            $config_system = be::get_config('system.system');

            $data = array(
                'site_name' => $config_system->site_name,
                'username' => $row_user->username,
                'email' => $row_user->email,
                'password' => $password,
                'name' => $row_user->name,
                'site_url' => URL_ROOT
            );

            $lib_mail = be::get_lib('mail');

            $subject = $lib_mail->format($config_user->admin_create_account_mail_subject, $data);
            $body = $lib_mail->format($config_user->admin_create_account_mail_body, $data);

            $lib_mail = be::get_lib('mail');
            $lib_mail->set_subject($subject);
            $lib_mail->set_body($body);
            $lib_mail->to($row_user->email);
            $lib_mail->send();
        }

        response::set_message($id == 0 ? '成功添加新用户！' : '成功修改用户资料！');
        system_log($id == 0 ? ('添加新用户：' . $row_user->username) : ('修改用户(' . $row_user->username . ')资料'));

        $lib_history = be::get_lib('history');
        $lib_history->back();
    }

    public function check_username()
    {
        $username = request::get('username', '');

        $service_user = be::get_service('system.user');
        echo $service_user->is_username_available($username) ? 'true' : 'false';
    }

    public function check_email()
    {
        $email = request::get('email', '');

        $service_user = be::get_service('system.user');
        echo $service_user->is_email_available($email) ? 'true' : 'false';
    }

    public function unblock()
    {
        $ids = request::post('id', '');

        $service_user = be::get_service('system.user');
        if ($service_user->unblock($ids)) {
            response::set_message('启用用户账号成功！');
            system_log('启用用户账号：#' . $ids);
        } else
            response::set_message($service_user->get_error(), 'error');

        $lib_history = be::get_lib('history');
        $lib_history->back();
    }

    public function block()
    {
        $ids = request::post('id', '');

        $service_user = be::get_service('system.user');
        if ($service_user->block($ids)) {
            response::set_message('屏蔽用户账号成功！');
            system_log('屏蔽用户账号：#' . $ids);
        } else
            response::set_message($service_user->get_error(), 'error');

        $lib_history = be::get_lib('history');
        $lib_history->back();
    }

    public function ajax_init_avatar()
    {
        $user_id = request::get('user_id', 0, 'int');

        $admin_service_user = be::get_service('system.user');
        if ($admin_service_user->init_avatar($user_id)) {
            system_log('删除 #' . $user_id . ' 用户头像');

            response::set('error', 0);
            response::set('message', '删除头像成功！');
        } else {
            response::set('error', 2);
            response::set('message', $admin_service_user->get_error());
        }

        response::ajax();

    }

    public function delete()
    {
        $ids = request::post('id', '');

        $admin_service_user = be::get_service('system.user');
        if ($admin_service_user->delete($ids)) {
            response::set_message('删除用户账号成功！');
            system_log('删除用户账号：#' . $ids);
        } else
            response::set_message($admin_service_user->get_error(), 'error');

        $lib_history = be::get_lib('history');
        $lib_history->back();
    }

    public function roles()
    {
        $admin_service_user = be::get_service('system.user');
        $roles = $admin_service_user->get_roles();

        foreach ($roles as $role) {
            if ($role->id > 1) $role->user_count = $admin_service_user->get_user_count(array('role_id' => $role->id));
        }

        response::set_title('用户组');
        response::set('roles', $roles);
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

                $row_user_role = be::get_row('user_role');
                if ($id != 0) $row_user_role->load($id);
                $row_user_role->name = $names[$i];
                $row_user_role->note = $notes[$i];
                $row_user_role->rank = $i;
                $row_user_role->save();
            }
        }

        $admin_service_user = be::get_service('system.user');
        $admin_service_user->update_user_roles();

        system_log('修改用户角色');

        response::set_message('修改用户角色成功！');
        response::redirect('./?controller=user&task=roles');
    }

    public function ajax_set_default_role()
    {
        $role_id = request::get('role_id', 0, 'int');
        if ($role_id == 0) {
            response::set('error', 1);
            response::set('message', '参数(role_id)缺失！');
            response::ajax();
        }

        $row_user_role = be::get_row('user_role');
        $row_user_role->load($role_id);
        if ($row_user_role->id == 0) {
            response::set('error', 2);
            response::set('message', '不存在的角色！');
            response::ajax();
        }

        $row_user_role->set_default();

        system_log('设置用户角色 ' . $row_user_role->name . ' 为默认用户角色');

        response::set('error', 0);
        response::set('message', '设置前台默认用户角色成功！');
        response::ajax();
    }

    public function ajax_delete_role()
    {
        $role_id = request::post('id', 0, 'int');
        if ($role_id == 0) {
            response::set('error', 1);
            response::set('message', '参数(role_id)缺失！');
            response::ajax();
        }

        $row_user_role = be::get_row('user_role');
        $row_user_role->load($role_id);
        if ($row_user_role->id == 0) {
            response::set('error', 2);
            response::set('message', '不存在的角色！');
            response::ajax();
        }

        if ($row_user_role->default == 1) {
            response::set('error', 3);
            response::set('message', '默认角色不能删除！');
            response::ajax();
        }

        $admin_service_user = be::get_service('system.user');
        $user_count = $admin_service_user->get_user_count(array('role_id' => $role_id));
        if ($user_count > 0) {
            response::set('error', 4);
            response::set('message', '当前有' . $user_count . '个用户属于这个角色，禁止删除！');
            response::ajax();
        }

        $row_user_role->delete();

        system_log('删除用户角色：' . $row_user_role->name);

        response::set('error', 0);
        response::set('message', '删除用户组成功！');
        response::ajax();
    }

    public function role_permissions()
    {
        $role_id = request::get('role_id', 0, 'int');
        if ($role_id == 0) response::end('参数(role_id)缺失！');

        $row_user_role = be::get_row('user_role');
        $row_user_role->load($role_id);
        if ($row_user_role->id == 0) response::end('不存在的角色！');

        $admin_service_app = be::get_service('system.app');
        $apps = $admin_service_app->get_apps();

        response::set_title('用户角色(' . $row_user_role->name . ')权限设置');
        response::set('role', $row_user_role);
        response::set('apps', $apps);
        response::display();
    }


    public function role_permissions_save()
    {
        $role_id = request::post('role_id', 0, 'int');
        if ($role_id == 0) response::end('参数(role_id)缺失！');

        $row_user_role = be::get_row('user_role');
        $row_user_role->load($role_id);
        if ($row_user_role->id == 0) response::end('不存在的角色！');
        $row_user_role->permission = request::post('permission', 0, 'int');

        if ($row_user_role->permission == -1) {
            $public_permissions = [];
            $admin_service_app = be::get_service('system.app');
            $apps = $admin_service_app->get_apps();
            foreach ($apps as $app) {
                $app_permissions = $app->get_permissions();
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
            $row_user_role->permissions = implode(',', $permissions);
        } else {
            $row_user_role->permissions = '';
        }

        $row_user_role->save();

        $admin_service_user = be::get_service('system.user');
        $admin_service_user->update_user_role($role_id);

        system_log('修改用户角色 ' . $row_user_role->name . ' 权限');

        response::set_message('修改用户角色权限成功！');
        response::redirect('./?controller=user&task=roles');
    }

    public function setting()
    {
        response::set_title('用户系统设置');
        response::set('config_user', be::get_config('system.user'));
        response::display();
    }

    public function setting_save()
    {
        $config_user = be::get_config('system.user');
        $config_user->register = request::post('register', 0, 'int');
        $config_user->captcha_login = request::post('captcha_login', 0, 'int');
        $config_user->captcha_register = request::post('captcha_register', 0, 'int');
        $config_user->email_valid = request::post('email_valid', 0, 'int');
        $config_user->email_register = request::post('email_register', 0, 'int');
        $config_user->email_register_admin = request::post('email_register_admin', '');
        $config_user->avatar_s_w = request::post('avatar_s_w', 0, 'int');
        $config_user->avatar_s_h = request::post('avatar_s_h', 0, 'int');
        $config_user->avatar_m_w = request::post('avatar_m_w', 0, 'int');
        $config_user->avatar_m_h = request::post('avatar_m_h', 0, 'int');
        $config_user->avatar_l_w = request::post('avatar_l_w', 0, 'int');
        $config_user->avatar_l_h = request::post('avatar_l_h', 0, 'int');
        $config_user->connect_qq = request::post('connect_qq', 0, 'int');
        $config_user->connect_qq_app_id = request::post('connect_qq_app_id', '');
        $config_user->connect_qq_app_key = request::post('connect_qq_app_key', '');
        $config_user->connect_sina = request::post('connect_sina', 0, 'int');
        $config_user->connect_sina_app_key = request::post('connect_sina_app_key', '');
        $config_user->connect_sina_app_secret = request::post('connect_sina_app_secret', '');


        // 缩图图大图
        $default_avatar_l = $_FILES['default_avatar_l'];
        if ($default_avatar_l['error'] == 0) {
            $lib_image = be::get_lib('image');
            $lib_image->open($default_avatar_l['tmp_name']);
            if ($lib_image->is_image()) {
                $default_avatar_l_name = date('YmdHis') . '_l.' . $lib_image->get_type();
                $default_avatar_l_path = PATH_DATA . DS . 'system' . DS . 'user' . DS . 'avatar' . DS . 'default' . DS . $default_avatar_l_name;
                if (move_uploaded_file($default_avatar_l['tmp_name'], $default_avatar_l_path)) {
                    // @unlink(PATH_DATA.DS.'user'.DS.'avatar'.DS.'default'.DS.$config_user->default_avatar_l);
                    $config_user->default_avatar_l = $default_avatar_l_name;
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
                $default_avatar_m_path = PATH_DATA . DS . 'system' . DS . 'user' . DS . 'avatar' . DS . 'default' . DS . $default_avatar_m_name;
                if (move_uploaded_file($default_avatar_m['tmp_name'], $default_avatar_m_path)) {
                    // @unlink(PATH_DATA.DS.'user'.DS.'avatar'.DS.'default'.DS.$config_user->default_avatar_m);
                    $config_user->default_avatar_m = $default_avatar_m_name;
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
                $default_avatar_s_path = PATH_DATA . DS . 'system' . DS . 'user' . DS . 'avatar' . DS . 'default' . DS . $default_avatar_s_name;
                if (move_uploaded_file($default_avatar_s['tmp_name'], $default_avatar_s_path)) {
                    // @unlink(PATH_DATA.DS.'user'.DS.'avatar'.DS.'default'.DS.$config_user->default_avatar_s);
                    $config_user->default_avatar_s = $default_avatar_s_name;
                }
            }
        }

        $service_system = be::get_service('system');
        $service_system->update_config($config_user, PATH_DATA . DS . 'config' . DS . 'user.php');

        system_log('设置用户系统参数');

        response::set_message('成功保存用户系统设置！');
        response::redirect('./?controller=user&task=setting');
    }
}
