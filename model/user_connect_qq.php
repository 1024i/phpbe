<?php
namespace model;

use \system\be;
use \system\session;
use \system\request;

class user_connect_qq extends \system\model
{
	
	private $app_id = '';
	private $app_key = '';

    // 构造函数
    public function __construct()
    {
		$config = be::get_config('user');
        $this->app_id = $config->connect_qq_app_id;
		$this->app_key = $config->connect_qq_app_key;
    }


	public function login()
	{
		$state = md5(uniqid(rand(), true));
		session::set('user_connect_qq_state', $state);

        $url = 'https://graph.qq.com/oauth2.0/authorize';
		$url .= '?response_type=code';
		$url .= '&client_id='.$this->app_id;
		$url .= '&redirect_uri='.urlencode(URL_ROOT.'/?controller=user&task=qq_login_callback');
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
		if (request::get('state', '')!=session::get('user_connect_qq_state')) {
			$this->set_error('返回信息被篡改！');
			return false;
		}

        $url = 'https://graph.qq.com/oauth2.0/token';
		$url .= '?grant_type=authorization_code';
		$url .= '&client_id='.$this->app_id;
		$url .= '&client_secret='.$this->app_key;
		$url .= '&code='.request::get('code','');
		$url .= '&redirect_uri='.urlencode(URL_ROOT.'/?controller=user&task=qq_login_callback');

		$lib_http = be::get_lib('http');
		$response = $lib_http->get($url);

        if (strpos($response, "callback") !== false){

            $lpos = strpos($response, "(");
            $rpos = strrpos($response, ")");
            $response  = substr($response, $lpos + 1, $rpos - $lpos -1);
            $msg = json_decode($response);

            if (isset($msg->error)){
				$this->set_error($msg->error.': '.$msg->error_description);
				return false;
            }
        }

        $params = array();
        parse_str($response, $params);

		return $params['access_token'];
	}

	public function get_openid($access_token)
	{
		$url = 'https://graph.qq.com/oauth2.0/me';
		$url .= '?access_token='.$access_token;

		$lib_http = be::get_lib('http');
		$response = $lib_http->get($url);

        //--------检测错误是否发生
        if (strpos($response, "callback") !== false){

            $lpos = strpos($response, "(");
            $rpos = strrpos($response, ")");
            $response = substr($response, $lpos + 1, $rpos - $lpos -1);
        }

        $response = json_decode($response);
        if (isset($response->error)) {
			$this->set_error($response->error.': '.$response->error_description);
			return false;
        }

        return $response->openid;
	}

	public function get_user_info($access_token, $openid)
	{
		$url = 'https://graph.qq.com/user/get_user_info';
		$url .= '?oauth_consumer_key='.$this->app_id;
		$url .= '&access_token='.$access_token;
		$url .= '&openid='.$openid;
		$url .= '&format=json';

		$lib_http = be::get_lib('http');
		$response = $lib_http->get($url);

		$response = json_decode($response);

		if ($response->ret!=0) {
			$this->set_error($response->msg);
			return false;
		}

		return $response;	
	}

	public function register($user_info)
	{
		$config_user = be::get_config('user');

		$t = time();
		$row_user = be::get_row('user');
		$row_user->connect = 'qq';
		$row_user->name = $user_info->nickname;
		$row_user->register_time = $t;
		$row_user->last_visit_time = $t;
		$row_user->is_admin = 0;
		$row_user->block = 0;
		$row_user->save();

		$lib_http = be::get_lib('http');
		$response = $lib_http->get($user_info->figureurl_qq_2?$user_info->figureurl_qq_1:$user_info->figureurl_qq_2);
		
		$t = date('YmdHis', $t);

		$tmp_avatar = PATH_DATA.DS.$t.'_'.$row_user->id;
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

	// 用户登陆到BE系统
	public function system_login($user_id)
	{
		session::set('_user', be::get_user($user_id));
	}

}
?>