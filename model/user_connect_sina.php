<?php
namespace model;

use \system\be;
use \system\session;
use \system\request;

class user_connect_sina extends \system\model
{
	
	private $app_key = '';
	private $app_secret = '';

    // 构造函数
    public function __construct()
    {
		$config = be::get_config('user');
        $this->app_key = $config->connect_sina_app_key;
		$this->app_secret = $config->connect_sina_app_secret;
    }

	public function login()
	{
		$state = md5(uniqid(rand(), true));
		session::set('user_connect_sina_state', $state);

        $url = 'https://api.weibo.com/oauth2/authorize';
		$url .= '?client_id='.$this->app_key;
		$url .= '&response_type=code';
		$url .= '&redirect_uri='.urlencode(URL_ROOT.'/?controller=user&task=sina_login_callback');
		$url .= '&state='.$state;

        header("Location:$url");

		echo '<html>';
		echo '<head>';
		echo '<meta http-equiv="refresh" content="0; url='.$url.'">';
		echo '<script language="javascript">';
		echo 'window.location.href="'.$url.'";';
		echo '</script>';
		echo '</head>';
		echo '<body></body>';
		echo '</html>';
	}

	public function callback()
	{
		if (request::get('state', '')!=session::get('user_connect_sina_state')) {
			$this->set_error('返回信息被篡改！');
			return false;
		}

        $url = 'https://api.weibo.com/oauth2/access_token';

		$data = array();
		$data['client_id'] = $this->app_key;
		$data['client_secret'] = $this->app_secret;
		$data['grant_type'] = 'authorization_code';
		$data['redirect_uri'] = URL_ROOT.'/?controller=user&task=sina_login_callback';
		$data['code'] = request::get('code','');

		$lib_http = be::get_lib('http');
		$response = $lib_http->post($url, $data); // 本步骤比较特殊，用 POST 发送
		$response = json_decode($response);

		if (isset($response->error)) {
			$this->set_error($response->error_code.': '.$response->error);
			return false;
		}

		return $response->access_token;
	}

	public function get_uid($access_token)
	{
		$url = 'https://api.weibo.com/2/account/get_uid.json';
		$url .= '?access_token='.$access_token;

		$lib_http = be::get_lib('http');
		$response = $lib_http->get($url);
        $response = json_decode($response);

        if (isset($response->error)) {
			$this->set_error($response->error_code.': '.$response->error);
			return false;
        }

        return $response->uid;
	}


	public function get_user_info($access_token, $uid)
	{
		$url = 'https://api.weibo.com/2/users/show.json';
		$url .= '?access_token='.$access_token;
		$url .= '&uid='.$uid;

		$lib_http = be::get_lib('http');
		$response = $lib_http->get($url);

		$response = json_decode($response);

        if (isset($response->error)) {
			$this->set_error($response->error_code.': '.$response->error);
			return false;
        }

		return $response;	
	}

	public function register($user_info)
	{
		$config_user = be::get_config('user');

		$t = time();
		$row_user = be::get_row('user');
		$row_user->connect = 'sina';
		$row_user->name = $user_info->name;
		$row_user->register_time = $t;
		$row_user->last_visit_time = $t;
		$row_user->is_admin = 0;
		$row_user->block = 0;
		$row_user->save();

		$lib_http = be::get_lib('http');
		$response = $lib_http->get($user_info->avatar_large);

		$t = date('YmdHis', $t);
		
        $tmp_avatar = PATH_DATA.DS.'system'.DS.'tmp'.DS.'user_connect_sina_'.$t.'_'.$row_user->id;
        file_put_contents($tmp_avatar, $response);

		$lib_image = be::get_lib('image');
		$lib_image->open($tmp_avatar);
		if ($lib_image->is_image()) {
			$lib_image->resize($config_user->avatar_l_w, $config_user->avatar_l_h, 'north');
			$lib_image->save(PATH_DATA.DS.'user'.DS.'avatar'.DS.$row_user->id.'_'.$t.'_l.'.$lib_image->get_type());
			$row_user->avatar_l = $row_user->id.'_'.$t.'_l.'.$lib_image->get_type();

			$lib_image->resize($config_user->avatar_m_w, $config_user->avatar_m_h, 'north');
			$lib_image->save(PATH_DATA.DS.'user'.DS.'avatar'.DS.$row_user->id.'_'.$t.'_m.'.$lib_image->get_type());
			$row_user->avatar_m = $row_user->id.'_'.$t.'_m.'.$lib_image->get_type();

			$lib_image->resize($config_user->avatar_s_w, $config_user->avatar_s_h, 'north');
			$lib_image->save(PATH_DATA.DS.'user'.DS.'avatar'.DS.$row_user->id.'_'.$t.'_s.'.$lib_image->get_type());
			$row_user->avatar_s = $row_user->id.'_'.$t.'_s.'.$lib_image->get_type();

			$row_user->save();
		}
		
		unlink($tmp_avatar);

		return $row_user;
	}

	public function system_login($user_id)
	{
		session::set('_user', be::get_user($user_id));
	}

}
