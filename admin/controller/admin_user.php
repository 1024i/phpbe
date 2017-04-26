<?php
namespace admin\controller;

use \system\be;
use \system\request;

class admin_user extends \admin\system\controller
{
    
	// 登陆页面
	public function login()
	{
		$my = be::get_admin_user();

		if ($my->id>0) {
			$this->redirect('./?controller=system&task=dashboard');
		}

		$template = be::get_admin_template('admin_user.login');
		$template->set_title('登录');

		$template->display();
	}

	// 登陆检查
	public function ajax_login_check()
	{
		$username = request::post('username', '');
		$password = request::post('password', '');

		if ($username == '') {
			$this->set('error', 1);
			$this->set('message', '请输入用户名！');
			$this->ajax();
		}
        
        if ($password == '') {
			$this->set('error', 2);
			$this->set('message', '请输入密码！');
			$this->ajax();
		}

		$admin_model_admin_user = be::get_admin_model('admin_user');
		$user = $admin_model_admin_user->login($username, $password);
		
		if ($user) {
		    system_log('登录后台');
		    
			$this->set('error', 0);
			$this->set('message', '登录成功！');
			$this->ajax();
		} else {
			$this->set('error', 2);
			$this->set('message', $admin_model_admin_user->get_error());
			$this->ajax();
		}
	}


	// 退出登陆
	public function logout()
	{
		$admin_model_admin_user = be::get_admin_model('admin_user');
		$admin_model_admin_user->logout();

		$this->redirect('./?controller=user&task=login', '成功退出！');
	}



    // 管理用户列表
    public function users()
    {
        $order_by = request::post('order_by', 'id');
        $order_by_dir = request::post('order_by_dir', 'ASC');
        $key = request::post('key', '');
        $status = request::post('status', -1, 'int');
		$limit = request::post('limit', -1, 'int');
		$group_id = request::post('group_id', 0, 'int');
		$admin_group_id = request::post('admin_group_id', 0, 'int');

		if ($limit == -1) {
			$admin_config_system = be::get_admin_config('system');
			$limit = $admin_config_system->limit;
		}

		$option = array(
			'key'=>$key,
			'status'=>$status
		);
		if ($group_id>0) $option['group_id'] = $group_id;
		if ($admin_group_id>0) $option['admin_group_id'] = $admin_group_id;
        
        $admin_model_user = be::get_admin_model('user');
        
        $template = be::get_admin_template('user.users');
        $template->set_title('用户列表');

        $pagination = be::get_admin_ui('pagination');
        $pagination->set_limit($limit);
        $pagination->set_total($admin_model_user->get_user_count($option));
        $pagination->set_page(request::post('page', 1, 'int'));
        
        $template->set('pagination', $pagination);
        $template->set('order_by', $order_by);
        $template->set('order_by_dir', $order_by_dir);
        $template->set('key', $key);
        $template->set('status', $status);
		$template->set('group_id', $group_id);
		$template->set('admin_group_id', $admin_group_id);

		$option['order_by'] = $order_by;
		$option['order_by_dir'] = $order_by_dir;
		$option['offset'] = $pagination->get_offset();
		$option['limit'] = $limit;

        $template->set('users', $admin_model_user->get_users($option));
        $template->display();

		$lib_history = be::get_lib('history');
		$lib_history->save();
    }

    // 修改用户
    public function edit()
    {
        $id = request::request('id', 0, 'int');
        
        $user = be::get_row('user');
        if ($id != 0) $user->load($id);
        
        $template = be::get_admin_template('user.edit');
        
        if ($id != 0)
            $template->set_title('修改用户资料');
        else
            $template->set_title('添加新用户');

        $template->set('user', $user);
        $template->display();
    }

    // 保存修改用户
    public function edit_save()
    {
        $id = request::post('id', 0, 'int');

		if (request::post('username', '') == '') {
			$this->set_message('请输入用户名！', 'error');
            $this->redirect('./?controller=admin_user&task=edit&id=' . $id);
        }
        
        if (request::post('email', '') == '') {
			$this->set_message('请输入邮箱！', 'error');
            $this->redirect('./?controller=admin_user&task=edit&id=' . $id);
        }
        
        $password = request::post('password', '');
        if ($password != request::post('password2', '')) {
			$this->set_message('两次输入的密码不匹配！', 'error');
            $this->redirect('./?controller=admin_user&task=edit&id=' . $id);
        }

        if ($id == 0 && $password == '') {
			$this->set_message('密码不能为空！', 'error');
            $this->redirect('./?controller=admin_user&task=edit&id=' . $id);
        }

        $row_user = be::get_row('user');
        if ($id>0) $row_user->load($id);
        
        $my = be::get_admin_user();
        
		// 如果被修改的用户是超级管理员
		if ($row_user->admin_group_id == 1 && $my->admin_group_id!=1) {
			$this->set_message('您没有权限修改超级管理员资料！', 'error');
            $this->redirect('./?controller=admin_user&task=edit&id=' . $id);
		}

        $row_user->bind(request::post());

		if ($id == 1 && $row_user->group_id!=1) {
			$this->set_message('默认超级管理员禁止修改后台用户组！', 'error');
            $this->redirect('./?controller=admin_user&task=edit&id=' . $id);
		}

		$admin_model_user = be::get_admin_model('user');

		if (!$admin_model_user->is_username_available($row_user->username, $id)) {
			$this->set_message('用户名('.$row_user->username.')已被占用！', 'error');
            $this->redirect('./?controller=admin_user&task=edit&id=' . $id);
		}

		if (!$admin_model_user->is_email_available($row_user->email, $id)) {
			$this->set_message('邮箱('.$row_user->email.')已被占用！', 'error');
            $this->redirect('./?controller=admin_user&task=edit&id=' . $id);
		}

        if ($password != '') {
			$model_user = be::get_model('user');
            $row_user->password = $model_user->encrypt_password($password);
		}
        else
            unset($row_user->password);
        
        if ($id == 0) {
            $row_user->create_time = time();
            $row_user->last_visit_time = time();
        } else {
            unset($row_user->create_time);
            unset($row_user->last_visit_time);
        }

        $row_user->save();

		$config_user = be::get_config('user');
        
        $avatar = $_FILES['avatar'];
        if ($avatar['error'] == 0) {
            $lib_image = be::get_lib('image');
            $lib_image->open($avatar['tmp_name']);
            if ($lib_image->is_image()) {
				$admin_model_user->delete_avatar_file($row_user);
                
				$t = date('YmdHis');

				$lib_image->resize($config_user->avatar_l_w, $config_user->avatar_l_h, 'north');
				$lib_image->save(PATH_DATA.DS.'admin_user'.DS.'avatar'.DS.$row_user->id.'_'.$t.'_l.'.$lib_image->get_type());
                $row_user->avatar_l = $row_user->id.'_'.$t.'_l.'.$lib_image->get_type();

				$lib_image->resize($config_user->avatar_m_w, $config_user->avatar_m_h, 'north');
				$lib_image->save(PATH_DATA.DS.'admin_user'.DS.'avatar'.DS.$row_user->id.'_'.$t.'_m.'.$lib_image->get_type());
                $row_user->avatar_m = $row_user->id.'_'.$t.'_m.'.$lib_image->get_type();

				$lib_image->resize($config_user->avatar_s_w, $config_user->avatar_s_h, 'north');
				$lib_image->save(PATH_DATA.DS.'admin_user'.DS.'avatar'.DS.$row_user->id.'_'.$t.'_s.'.$lib_image->get_type());
                $row_user->avatar_s = $row_user->id.'_'.$t.'_s.'.$lib_image->get_type();

                $row_user->save();
            }
        }
        
        if ($id == 0) {
            $config_system = be::get_config('system');

            $data = array(
                'site_name'=>$config_system->site_name,
                'username'=>$row_user->username,
                'email'=>$row_user->email,
                'password'=>$password,
                'name'=>$row_user->name,
                'site_url'=>URL_ROOT
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

		$this->set_message($id == 0?'成功添加新用户！':'成功修改用户资料！');
		system_log($id == 0?('添加新用户：'.$row_user->username):('修改用户('.$row_user->username.')资料'));

		$lib_history = be::get_lib('history');
		$lib_history->back();
    }

    public function check_username()
    {
        $username = request::get('username','');
        
        $model_user = be::get_admin_model('user');
        echo $model_user->is_username_available($username) ? 'true' : 'false';
    }

    public function check_email()
    {
        $email = request::get('email','');
        
        $model_user = be::get_admin_model('user');
        echo $model_user->is_email_available($email) ? 'true' : 'false';
    }

    public function unblock()
    {
        $ids = request::post('id', '');
        
        $model_user = be::get_admin_model('user');
        if ($model_user->unblock($ids)) {
            $this->set_message('启用用户账号成功！');
            system_log('启用用户账号：#'.$ids);
        }
        else
            $this->set_message($model_user->get_error(), 'error');
        
		$lib_history = be::get_lib('history');
		$lib_history->back();
    }

    public function block()
    {
        $ids = request::post('id', '');
        
        $model_user = be::get_admin_model('user');
        if ($model_user->block($ids)) {
            $this->set_message('屏蔽用户账号成功！');
            system_log('屏蔽用户账号：#'.$ids);
        }
        else
            $this->set_message($model_user->get_error(), 'error');
        
		$lib_history = be::get_lib('history');
		$lib_history->back();
    }

    public function ajax_init_avatar()
    {
        $user_id = request::get('user_id', 0, 'int');
        
        $admin_model_user = be::get_admin_model('user');
        if ($admin_model_user->init_avatar($user_id)) {
            system_log('删除 #'.$user_id.' 用户头像');
            
            $this->set('error', 0);
            $this->set('message', '删除头像成功！');
        } else {
            $this->set('error', 2);
            $this->set('message', $admin_model_user->get_error());
        }
        
        $this->ajax();

    }

    public function delete()
    {
        $ids = request::post('id', '');
        
        $admin_model_user = be::get_admin_model('user');
        if ($admin_model_user->delete($ids)) {
            $this->set_message('删除用户账号成功！');
            system_log('删除用户账号：#'.$ids);
        }
        else
            $this->set_message($admin_model_user->get_error(), 'error');
        
		$lib_history = be::get_lib('history');
		$lib_history->back();
    }


	public function groups()
	{
		$admin_model_user = be::get_admin_model('user');
		$admin_groups = $admin_model_user->get_admin_groups();

		foreach ($admin_groups as $group) {
			$group->user_count = $admin_model_user->get_user_count(array('admin_group_id'=>$group->id));
		}

		$template = be::get_admin_template('user.admin_groups');
        $template->set_title('后台用户组');
		$template->set('admin_groups', $admin_groups);
		$template->set('tab', 'backend');
        $template->display();
	}

	public function groups_save()
	{
        $ids = request::post('id', array(), 'int');
        $names = request::post('name', array());
        $notes = request::post('note', array());
        
        if (count($ids)>0) {
            for ($i = 0, $n = count($ids); $i < $n; $i++)
            {
				$id = $ids[$i];

				if ($id == 1) continue;

                if ($id == 0 && $names[$i] == '') continue;
                
                $row_user_admin_group = be::get_row('user_admin_group');
				if ($id!=0) $row_user_admin_group->load($id);
                $row_user_admin_group->name = $names[$i];
				$row_user_admin_group->note = $notes[$i];
                $row_user_admin_group->rank = $i;
                $row_user_admin_group->save();
            }
        }
        
		$this->update_admin_config_user_group();

        system_log('修改后台用户组');

		$this->set_message('修改后台用户组成功！');
        $this->redirect('./?controller=user&task=admin_groups');
	}

	public function ajax_group_delete()
	{
		$group_id = request::post('id', 0, 'int');
		if ($group_id == 0) {
			$this->set('error', 1);
			$this->set('message', '参数(group_id)缺失！');
			$this->ajax();
		}

		$row_user_admin_group = be::get_row('user_admin_group');
		$row_user_admin_group->load($group_id);
		if ($row_user_admin_group->id == 0) {
			$this->set('error', 2);
			$this->set('message', '不存在的分组');
			$this->ajax();
		}

		$admin_model_user = be::get_admin_model('user');
		$user_count = $admin_model_user->get_user_count(array('admin_group_id'=>$group_id));
		if ($user_count>0) {
			$this->set('error', 3);
			$this->set('message', '当前有'.$user_count.'个用户属于这个分组，禁止删除！');
			$this->ajax();
		}

		$row_user_admin_group->delete();

		$this->update_admin_config_user_group();

		system_log('删除后台用户组：'.$row_user_admin_group->name);

		$this->set('error', 0);
		$this->set('message', '删除用户组成功！');
		$this->ajax();
	}

	public function group_permissions()
	{
		$group_id = request::get('group_id', 0, 'int');
		if ($group_id == 0) be_exit('参数(group_id)缺失！');

		$row_user_admin_group = be::get_row('user_admin_group');
		$row_user_admin_group->load($group_id);
		if ($row_user_admin_group->id == 0) be_exit('不存在的分组！');

		$admin_model_system = be::get_admin_model('system');
		$apps = $admin_model_system->get_apps();

		$template = be::get_admin_template('user.admin_group_permissions');
        $template->set_title('后台用户组('.$row_user_admin_group->name.')权限设置');
		$template->set('group', $row_user_admin_group);
		$template->set('apps', $apps);
		$template->set('tab', 'backend');
        $template->display();
	}


	public function group_permissions_save()
	{
		$group_id = request::post('group_id', 0, 'int');
		if ($group_id == 0) be_exit('参数(group_id)缺失！');

		$row_user_admin_group = be::get_row('user_admin_group');
		$row_user_admin_group->load($group_id);
		if ($row_user_admin_group->id == 0) be_exit('不存在的分组！');
		$row_user_admin_group->permission = request::post('permission', 0, 'int');
		
		if ($row_user_admin_group->permission == -1) {
			$permissions = request::post('permissions', array());
			$row_user_admin_group->permissions = implode(',', $permissions);
		}

		$row_user_admin_group->save();

        
		$this->update_admin_config_user_group();

        system_log('修改后台用户组('.$row_user_admin_group->name.')权限');

		$this->set_message('修改后台用户组权限成功！');
        $this->redirect('./?controller=user&task=admin_groups');
	}


	private function update_admin_config_user_group()
	{
		$admin_model_user = be::get_admin_model('user');
		$admin_groups = $admin_model_user->get_admin_groups();

		$names = array();
		foreach ($admin_groups as $group) {
			$names[$group->id]=$group->name;
		}

		$admin_config_user_group = be::get_admin_config('user_group');
		$admin_config_user_group->names = $names;

		$vars = get_object_vars($admin_config_user_group);
		foreach ($vars as $var=>$val) {
			if (substr($var, 0, 12) == 'permissions_') unset($admin_config_user_group->$var);
		}

		foreach ($admin_groups as $group) {
			if ($group->id == 1) {
				// 超级管理员拥有所有权限
				$admin_config_user_group->permissions_1 = '1';
			} else {
				$field_name = 'permissions_'.$group->id;
				if ($group->permission == 0 || $group->permission == 1) {
					// 所有权限 或 没有任何权限
					$admin_config_user_group->$field_name = $group->permission;
				} else {
					// 自定义权限
					$admin_config_user_group->$field_name = explode(',', $group->permissions);
				}
			}
		}

		$model_system = be::get_admin_model('system');
		$model_system->save_config_file($admin_config_user_group, PATH_ADMIN.DS.'configs'.DS.'user_group.php');
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
			'key'=>$key,
			'success'=>$success
		);

        $model_user = be::get_admin_model('user');
        $template = be::get_admin_template('user.logs');
        $template->set_title('后台登陆日志');

        $pagination = be::get_admin_ui('pagination');
        $pagination->set_limit($limit);
        $pagination->set_total($model_user->get_log_count($option));
        $pagination->set_page(request::post('page', 1, 'int'));

		$option['offset'] = $pagination->get_offset();
		$option['limit'] = $limit;

        $template->set('pagination', $pagination);
        $template->set('key', $key);
        $template->set('success', $success);
        $template->set('logs', $model_user->get_logs($option));
        
        $template->display();
    }

	// 后台登陆日志
    public function ajax_delete_logs()
    {
        $admin_model_user = be::get_admin_model('user');
        $admin_model_user->delete_logs();

		system_log('删除后台用户登陆日志');

		$this->set('error', 0);
		$this->set('message', '删除后台用户登陆日志成功！');
		$this->ajax();
    }


    
	public function setting()
	{
		$template = be::get_admin_template('user.setting');
		$template->set_title('用户系统设置');
		$template->set('config_user', be::get_config('user'));
		$template->display();
	}

	public function setting_save()
	{
		$config_user = be::get_config('user');
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
				$default_avatar_l_name = date('YmdHis').'_l.'.$lib_image->get_type();
				$default_avatar_l_path = PATH_DATA.DS.'admin_user'.DS.'avatar'.DS.'default'.DS.$default_avatar_l_name;
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
				$default_avatar_m_name = date('YmdHis').'_m.'.$lib_image->get_type();
				$default_avatar_m_path = PATH_DATA.DS.'admin_user'.DS.'avatar'.DS.'default'.DS.$default_avatar_m_name;
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
				$default_avatar_s_name = date('YmdHis').'_s.'.$lib_image->get_type();
				$default_avatar_s_path = PATH_DATA.DS.'admin_user'.DS.'avatar'.DS.'default'.DS.$default_avatar_s_name;
				if (move_uploaded_file($default_avatar_s['tmp_name'], $default_avatar_s_path)) {
					// @unlink(PATH_DATA.DS.'user'.DS.'avatar'.DS.'default'.DS.$config_user->default_avatar_s);
					$config_user->default_avatar_s = $default_avatar_s_name;
				}
			}
		}

		$model_system = be::get_admin_model('system');
		$model_system->save_config_file($config_user, PATH_ROOT.DS.'configs'.DS.'user.php');
		
		system_log('设置用户系统参数');
		
		$this->set_message('成功保存用户系统设置！');
		$this->redirect('./?controller=user&task=setting');
	}
}
?>