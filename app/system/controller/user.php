<?php
namespace controller;

use system\be;
use system\request;
use system\response;
use system\session;

class user extends \system\controller
{

    public function index()
    {
        $this->login();
    }
    
    // 登陆页面
    public function login()
    {
        $my = be::get_user();
        if ($my->id>0) {
			response::redirect(url('controller=user_profile&task=home'));
		}

		// 登陆成功后跳转到的网址
		$return = request::get('return','');
		if ($return == 'http_referer' && isset($_SERVER['HTTP_REFERER'])) $return = base64_encode($_SERVER['HTTP_REFERER']);

        response::set_title('登陆');
        response::set('return', $return);
        response::display();
    }

	// 登陆验证码
    public function captcha_login()
    {
		$template = be::get_template('user.login');
		$color = response::get_color();

		$lib_css = be::get_lib('css');
		$rgb_color = $lib_css->hex_to_rgb($color);

        $captcha = be::get_lib('captcha');
		$captcha->set_font_color($rgb_color);
        $captcha->point(20); // 添加干扰点
        $captcha->line(3); // 添加干扰线
		$captcha->distortion();	// 扭曲
        $captcha->border(1, $rgb_color); // 添加边框
        $captcha->output();
        
        session::set('captcha_login', $captcha->to_string());
    }

    // 登陆检查
    public function login_check()
    {
        $username = request::post('username', '');
        $password = request::post('password', '');
        $remember_me = request::post('remember_me', '0');
        
        $return = request::post('return', '');
        $error_return = url('controller=user&task=login&return=' . $return);

        if ($username == '') {
			response::error('用户名不能为空！', $error_return);
        }
        
        if ($password == '') {
            response::error('密码不能为空！', $error_return);
        }
        
		$config_user = be::get_config('user');
		if ($config_user->captcha_login) {
			if (request::post('captcha', '') != session::get('captcha_login')) {
                response::error('验证码错误！', $error_return);
			}
		}
        
        $service_user = be::get_service('user');
        if ($service_user->login($username, $password, $remember_me)) {
			if ($config_user->captcha_login) session::delete('captcha_login');

			$redirect_url = null;
			if ($return == '') {
				$redirect_url = url('controller=user_profile&task=home');
			} else {
				$redirect_url = base64_decode($return);
			}

            response::success('登陆成功！', $redirect_url);
		} else {
            response::error($service_user->get_error(), $error_return);
		}
    }

	public function qq_login()
	{
		$config_user = be::get_config('user');
		if (!$config_user->connect_qq) response::end('使用QQ账号登陆未启用！');

		$service_user_connect_qq = be::get_service('user_connect_qq');
		$service_user_connect_qq->login();
	}

	public function qq_login_callback()
	{
		$config_user = be::get_config('user');
		if (!$config_user->connect_qq) response::end('使用QQ账号登陆未启用！');

		$service_user_connect_qq = be::get_service('user_connect_qq');
		$access_token = $service_user_connect_qq->callback();
		if ($access_token == false) response::end($service_user_connect_qq->get_error());

		$openid = $service_user_connect_qq->get_openid($access_token);
		if ($openid == false) response::end($service_user_connect_qq->get_error());

		$user_info = $service_user_connect_qq->get_user_info($access_token, $openid);
		if ($user_info == false) response::end($service_user_connect_qq->get_error());

		$row_user_connect_qq = be::get_row('user_connect_qq');
		$row_user_connect_qq->load_by('openid', $openid);
		if ($row_user_connect_qq->user_id>0) {
			$service_user_connect_qq->system_login($row_user_connect_qq->user_id);
		} else {
			$user = $service_user_connect_qq->register($user_info);
			$row_user_connect_qq->user_id = $user->id;

			$service_user_connect_qq->system_login($user->id);
		}

		unset($user_info->id);
		unset($user_info->user_id);

		$row_user_connect_qq->bind($user_info);
		$row_user_connect_qq->access_token = $access_token;
		$row_user_connect_qq->openid = $openid;
		$row_user_connect_qq->save();

        response::redirect(url('controller=user_profile&task=home'));
	}


	public function sina_login()
	{
		$config_user = be::get_config('user');
		if (!$config_user->connect_sina) response::end('使用新浪微博账号登陆未启用！');

		$service_user_connect_sina = be::get_service('user_connect_sina');
		$service_user_connect_sina->login();
	}

	public function sina_login_callback()
	{
		$config_user = be::get_config('user');
		if (!$config_user->connect_sina) response::end('使用新浪微博账号登陆未启用！');

		$service_user_connect_sina = be::get_service('user_connect_sina');
		$access_token = $service_user_connect_sina->callback();
		if ($access_token == false) response::end($service_user_connect_sina->get_error());

		$uid = $service_user_connect_sina->get_uid($access_token);
		if ($uid == false) response::end($service_user_connect_sina->get_error());

		$user_info = $service_user_connect_sina->get_user_info($access_token, $uid);
		if ($user_info == false) response::end($service_user_connect_sina->get_error());

		$row_user_connect_sina = be::get_row('user_connect_sina');
		$row_user_connect_sina->load_by('uid', $uid);
		if ($row_user_connect_sina->user_id>0) {
			$service_user_connect_sina->system_login($row_user_connect_sina->user_id);
		} else {
			$user = $service_user_connect_sina->register($user_info);
			$row_user_connect_sina->user_id = $user->id;

			$service_user_connect_sina->system_login($user->id);
		}

		unset($user_info->id);
		unset($user_info->user_id);

		$row_user_connect_sina->bind($user_info);
		$row_user_connect_sina->access_token = $access_token;
		$row_user_connect_sina->uid = $uid;
		$row_user_connect_sina->save();

        response::redirect(url('controller=user_profile&task=home'));
	}


    // 注册新用户
    public function register()
    {
		$config_user = be::get_config('user');
		if (!$config_user->register) response::end('注册功能已禁用！');

        response::set_title('注册新账号');
        response::display();
    }

	// 验证码
    public function captcha_register()
    {
		$template = be::get_template('user.register');
		$color = response::get_color();

		$lib_css = be::get_lib('css');
		$rgb_color = $lib_css->hex_to_rgb($color);

        $captcha = be::get_lib('captcha');
		$captcha->set_font_color($rgb_color);
        $captcha->point(20); // 添加干扰点
        $captcha->line(3); // 添加干扰线
		$captcha->distortion();	// 扭曲
        $captcha->border(1, $rgb_color); // 添加边框
        $captcha->output();

        session::set('captcha_register', $captcha->to_string());
    }

    // 保存新注册用户
    public function ajax_register_save()
    {
		$config_user = be::get_config('user');

		if (!$config_user->register) {
            response::error('注册功能已禁用！');
        }

        $username = request::post('username', '');
        $email = request::post('email', '');
        $name = request::post('name', '');
        $password = request::post('password', '');
        $password2 = request::post('password2', '');

		$service_user = be::get_service('user');
        
        
        if ($username == '') {
            response::error('用户名不能为空！');
        }
        
        if ($email == '') {
            response::error('邮箱不能为空！');
        }
        
        if (!$service_user->is_email($email)) {
            response::error('非法的邮箱格式！');
        }
        
        if ($password == '') {
            response::error('密码不能为空！');
        }
        
        if ($password != $password2) {
            response::error('两次输入的密码不匹配！');
        }

		if ($config_user->captcha_register) {
			if (request::post('captcha', '') != session::get('captcha_register')) {
                response::error('验证码错误！');
			}
		}
        
        if ($service_user->register($username, $email, $password, $name)) {
			if ($config_user->captcha_register) session::delete('captcha_register');

            response::success('您的账号已成功创建！', url('controller=user&task=register_success&username='.$username.'&email='.$email));
        } else {
            response::error($service_user->get_error());
        }
    }

    // 注册成功
    public function register_success()
    {
        $username = request::get('username','');
        $email = request::get('email','');

        response::set_title('注册成功');
        response::set('username', $username);
		response::set('email', $email);
        response::display();
    }


    //找回密码表单
    public function forgot_password()
    {
        response::set_title('忘记密码');
        response::display();
    }

    //提交找回密码
    public function ajax_forgot_password_save()
    {
        $username = request::post('username', '');
        if ($username == '') {
            response::error('参数(username)缺失！');
        }
        
        $model = be::get_service('user');
        if ($model->forgot_password($username)) {
            response::success('找回密码链接已发送到您的邮箱。');
        } else {
            response::error($model->get_error());
        }
    }

    // 重设密码
    public function forgot_password_reset()
    {
        $user_id = request::get('user_id', 0, 'int');
        $token = request::get('token','');
        if ($user_id == 0 || $token == '') response::end('找回密码链接已失效！');
        
        $row_user = be::get_row('user');
        $row_user->load($user_id);
        
        if ($row_user->token == '') response::end('您的密码已重设！');
        if ($row_user->token != $token) response::end('找回密码链接非法！');

        response::set_title('重设密码');
        response::set('user', $row_user);
        response::display();
    }

    // 重设密码保存
    public function ajax_forgot_password_reset_save()
    {
        $user_id = request::post('user_id', 0, 'int');
        $token = request::post('token', '');
        
        if ($user_id == 0 || $token == '') {
            response::error('参数(user_id/token)缺失！');
        }
        
        $password = request::post('password', '');
        $password2 = request::post('password2', '');
        
        if ($password != $password2) {
            response::error('两次输入的密码不匹配！');
        }
        
        $model = be::get_service('user');
        if ($model->forgot_password_reset($user_id, $token, $password)) {
            response::success('重设密码成功！');
        } else {
            response::error($model->get_error());
        }
    
    }

    // 退出登陆
    public function logout()
    {
        $model = be::get_service('user');
        $model->logout();

        response::success('成功退出！', url('controller=user&task=login'));
    }

}
