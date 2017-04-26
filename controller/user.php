<?php
namespace controller;

use \system\be;
use \system\request;
use \system\session;

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
			$this->redirect(url('controller=user_profile&task=home'));
		}

		// 登陆成功后跳转到的网址
		$return = request::get('return','');
		if ($return == 'http_referer' && isset($_SERVER['HTTP_REFERER'])) $return = base64_encode($_SERVER['HTTP_REFERER']);

        $template = be::get_template('user.login');
        $template->set_title('登陆');
		$template->set('return', $return);
        $template->display();
    }

	// 登陆验证码
    public function captcha_login()
    {
		$template = be::get_template('user.login');
		$color = $template->get_color();

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
        $rememberme = request::post('rememberme', '0');
        
        $return = request::post('return', '');

        if ($username == '') {
            $this->set_message('用户名不能为空！', 'error');
			$this->redirect(url('controller=user&task=login&return=' . $return));
        }
        
        if ($password == '') {
            $this->set_message('密码不能为空！', 'error');
			$this->redirect(url('controller=user&task=login&return=' . $return));
        }
        
		$config_user = be::get_config('user');
		if ($config_user->captcha_login == '1') {
			if (request::post('captcha', '') != session::get('captcha_login')) {
				$this->set_message('验证码错误！', 'error');
				$this->redirect(url('controller=user&task=login&return=' . $return));
			}
		}
        
        $model_user = be::get_model('user');
        if ($model_user->login($username, $password, $rememberme)) {
			if ($config_user->captcha_login == '1') session::delete('captcha_login');

			$redirect_url = '';
			if ($return == '') {
				$redirect_url = url('controller=user_profile&task=home');
			} else {
				$redirect_url = base64_decode($return);
			}

            $this->redirect($redirect_url);
		} else {
			$this->set_message($model_user->get_error(), 'error');
			$this->redirect(url('controller=user&task=login&return=' . $return));
		}
    }

    // 登陆检查
    public function ajax_login_check()
    {
        $username = request::post('username', '');
        $password = request::post('password', '');
        $rememberme = request::post('rememberme', '0');
		$return = request::post('return', '');
        
        
        if ($username == '') {
            $this->set('error', 1);
            $this->set('message', '用户名不能为空！');
            $this->ajax();
        }
        
        if ($password == '') {
            $this->set('error', 2);
            $this->set('message', '密码不能为空！');
            $this->ajax();
        }

		$config_user = be::get_config('user');
		if ($config_user->captcha_login == '1') {
			if (request::post('captcha', '') != session::get('captcha_login')) {
				$this->set('error', 3);
				$this->set('message', '验证码错误！');
				$this->ajax();
			}
		}
        
        $model_user = be::get_model('user');
        if ($model_user->login($username, $password, $rememberme)) {
			if ($config_user->captcha_login == '1') session::delete('captcha_login');

			$redirect_url = '';
			if ($return == '') {
				$redirect_url = url('controller=user_profile&task=home');
			} else {
				$redirect_url = base64_decode($return);
			}

            $this->set('error', 0);
			$this->set('redirect_url', $redirect_url);
            $this->set('message', '登陆成功！');
            $this->ajax();
        } else {
            $this->set('error', 4);
            $this->set('message', $model_user->get_error());
            $this->ajax();
        }
    }

	public function qq_login()
	{
		$config_user = be::get_config('user');
		if ($config_user->connect_qq == '0') be_exit('使用QQ账号登陆未启用！');

		$model_user_connect_qq = be::get_model('user_connect_qq');
		$model_user_connect_qq->login();
	}

	public function qq_login_callback()
	{
		$config_user = be::get_config('user');
		if ($config_user->connect_qq == '0') be_exit('使用QQ账号登陆未启用！');

		$model_user_connect_qq = be::get_model('user_connect_qq');
		$access_token = $model_user_connect_qq->callback();
		if ($access_token == false) be_exit($model_user_connect_qq->get_error());

		$openid = $model_user_connect_qq->get_openid($access_token);
		if ($openid == false) be_exit($model_user_connect_qq->get_error());

		$user_info = $model_user_connect_qq->get_user_info($access_token, $openid);
		if ($user_info == false) be_exit($model_user_connect_qq->get_error());

		$row_user_connect_qq = be::get_row('user_connect_qq');
		$row_user_connect_qq->load_by('openid', $openid);
		if ($row_user_connect_qq->user_id>0) {
			$model_user_connect_qq->system_login($row_user_connect_qq->user_id);
		} else {
			$user = $model_user_connect_qq->register($user_info);
			$row_user_connect_qq->user_id = $user->id;

			$model_user_connect_qq->system_login($user->id);
		}

		unset($user_info->id);
		unset($user_info->user_id);

		$row_user_connect_qq->bind($user_info);
		$row_user_connect_qq->access_token = $access_token;
		$row_user_connect_qq->openid = $openid;
		$row_user_connect_qq->save();

		$this->redirect(url('controller=user_profile&task=home'));
	}


	public function sina_login()
	{
		$config_user = be::get_config('user');
		if ($config_user->connect_sina == '0') be_exit('使用新浪微博账号登陆未启用！');

		$model_user_connect_sina = be::get_model('user_connect_sina');
		$model_user_connect_sina->login();
	}

	public function sina_login_callback()
	{
		$config_user = be::get_config('user');
		if ($config_user->connect_sina == '0') be_exit('使用新浪微博账号登陆未启用！');

		$model_user_connect_sina = be::get_model('user_connect_sina');
		$access_token = $model_user_connect_sina->callback();
		if ($access_token == false) be_exit($model_user_connect_sina->get_error());

		$uid = $model_user_connect_sina->get_uid($access_token);
		if ($uid == false) be_exit($model_user_connect_sina->get_error());

		$user_info = $model_user_connect_sina->get_user_info($access_token, $uid);
		if ($user_info == false) be_exit($model_user_connect_sina->get_error());

		$row_user_connect_sina = be::get_row('user_connect_sina');
		$row_user_connect_sina->load_by('uid', $uid);
		if ($row_user_connect_sina->user_id>0) {
			$model_user_connect_sina->system_login($row_user_connect_sina->user_id);
		} else {
			$user = $model_user_connect_sina->register($user_info);
			$row_user_connect_sina->user_id = $user->id;

			$model_user_connect_sina->system_login($user->id);
		}

		unset($user_info->id);
		unset($user_info->user_id);

		$row_user_connect_sina->bind($user_info);
		$row_user_connect_sina->access_token = $access_token;
		$row_user_connect_sina->uid = $uid;
		$row_user_connect_sina->save();

		$this->redirect(url('controller=user_profile&task=home'));
	}


    // 注册新用户
    public function register()
    {
		$config_user = be::get_config('user');
		if ($config_user->register == '0') be_exit('注册功能已禁用！');

        $template = be::get_template('user.register');
        $template->set_title('注册新账号');
        $template->display();
    }

	// 验证码
    public function captcha_register()
    {
		$template = be::get_template('user.register');
		$color = $template->get_color();

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

		if ($config_user->register == '0') {
            $this->set('error', 1);
            $this->set('message', '注册功能已禁用！');
            $this->ajax();
        }

        $username = request::post('username', '');
        $email = request::post('email', '');
        $name = request::post('name', '');
        $password = request::post('password', '');
        $password2 = request::post('password2', '');

		$model_user = be::get_model('user');
        
        
        if ($username == '') {
            $this->set('error', 2);
            $this->set('message', '用户名不能为空！');
            $this->ajax();
        }
        
        if ($email == '') {
            $this->set('error', 3);
            $this->set('message', '邮箱不能为空！');
            $this->ajax();
        }
        
        if (!$model_user->is_email($email)) {
            $this->set('error', 4);
            $this->set('message', '非法的邮箱格式！');
            $this->ajax();
        }
        
        if ($password == '') {
            $this->set('error', 5);
            $this->set('message', '密码不能为空！');
            $this->ajax();
        }
        
        if ($password != $password2) {
            $this->set('error', 6);
            $this->set('message', '两次输入的密码不匹配！');
            $this->ajax();
        }

		if ($config_user->captcha_register == '1') {
			if (request::post('captcha', '') != session::get('captcha_register')) {
				$this->set('error', 6);
				$this->set('message', '验证码错误！');
				$this->ajax();
			}
		}
        
        if ($model_user->register($username, $email, $password, $name)) {
			if ($config_user->captcha_register == '1') session::delete('captcha_register');

            $this->set('error', 0);
			$this->set('redirect_url', url('controller=user&task=register_success&username='.$username.'&email='.$email));
            $this->set('message', '您的账号已成功创建！');
            $this->ajax();
        } else {
            $this->set('error', 7);
            $this->set('message', $model_user->get_error());
            $this->ajax();
        }
    }

    // 注册成功
    public function register_success()
    {
        $username = request::get('username','');
        $email = request::get('email','');

        $template = be::get_template('user.register_success');
        $template->set_title('注册成功');
		$template->set('username', $username);
		$template->set('email', $email);
        $template->display();
    }


    //找回密码表单
    public function forgot_password()
    {
        $template = be::get_template('user.forgot_password');
        $template->set_title('忘记密码');
        $template->display();
    }

    //提交找回密码
    public function ajax_forgot_password_save()
    {
        $username = request::post('username', '');
        if ($username == '') {
            $this->set('error', 1);
            $this->set('message', '参数(username)缺失！');
            $this->ajax();
        }
        
        $model = be::get_model('user');
        if ($model->forgot_password($username)) {
            $this->set('error', 0);
            $this->set('message', '找回密码链接已发送到您的邮箱。');
            $this->ajax();
        } else {
            $this->set('error', 2);
            $this->set('message', $model->get_error());
            $this->ajax();
        }
    }

    // 重设密码
    public function forgot_password_reset()
    {
        $user_id = request::get('user_id', 0, 'int');
        $token = request::get('token','');
        if ($user_id == 0 || $token == '') be_exit('找回密码链接已失效！');
        
        $row_user = be::get_row('user');
        $row_user->load($user_id);
        
        if ($row_user->token == '') be_exit('您的密码已重设！');
        if ($row_user->token != $token) be_exit('找回密码链接非法！');
        
        $template = be::get_template('user.forgot_password_reset');
        $template->set_title('重设密码');
        $template->set('user', $row_user);
        $template->display();
    }

    // 重设密码保存
    public function ajax_forgot_password_reset_save()
    {
        $user_id = request::post('user_id', 0, 'int');
        $token = request::post('token', '');
        
        if ($user_id == 0 || $token == '') {
            $this->set('error', 1);
            $this->set('message', '参数(user_id/token)缺失！');
            $this->ajax();
        }
        
        $password = request::post('password', '');
        $password2 = request::post('password2', '');
        
        if ($password != $password2) {
            $this->set('error', 2);
            $this->set('message', '两次输入的密码不匹配！');
            $this->ajax();
        }
        
        $model = be::get_model('user');
        if ($model->forgot_password_reset($user_id, $token, $password)) {
            $this->set('error', 0);
            $this->set('message', '重设密码成功！');
            $this->ajax();
        } else {
            $this->set('error', 3);
            $this->set('message', $model->get_error());
            $this->ajax();
        }
    
    }

    // 退出登陆
    public function logout()
    {
        $model = be::get_model('user');
        $model->logout();
        
		$this->set_message('成功退出！');
        $this->redirect(url('controller=user&task=login'));
    }

}

?>