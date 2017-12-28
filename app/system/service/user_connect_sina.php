<?php
namespace service;

use System\Be;
use System\Session;
use System\Request;

class userConnectSina extends \System\Service
{
	
	private $appKey = '';
	private $appSecret = '';

    // 构造函数
    public function __construct()
    {
		$config = Be::getConfig('System.user');
        $this->appKey = $config->connectSinaAppKey;
		$this->appSecret = $config->connectSinaAppSecret;
    }

	public function login()
	{
		$state = md5(uniqid(rand(), true));
		session::set('userConnectSinaState', $state);

        $url = 'https://api.weibo.com/oauth2/authorize';
		$url .= '?clientId='.$this->appKey;
		$url .= '&ResponseType=code';
		$url .= '&redirectUri='.urlencode(URL_ROOT.'/?controller=user&task=sinaLoginCallback');
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
		if (Request::get('state', '')!=session::get('userConnectSinaState')) {
			$this->setError('返回信息被篡改！');
			return false;
		}

        $url = 'https://api.weibo.com/oauth2/accessToken';

		$data = array();
		$data['clientId'] = $this->appKey;
		$data['clientSecret'] = $this->appSecret;
		$data['grantType'] = 'authorizationCode';
		$data['redirectUri'] = URL_ROOT.'/?controller=user&task=sinaLoginCallback';
		$data['code'] = Request::get('code','');

		$libHttp = Be::getLib('Http');
		$Response = $libHttp->post($url, $data); // 本步骤比较特殊，用 POST 发送
		$Response = jsonDecode($Response);

		if (isset($Response->error)) {
			$this->setError($Response->errorCode.': '.$Response->error);
			return false;
		}

		return $Response->accessToken;
	}

	public function getUid($accessToken)
	{
		$url = 'https://api.weibo.com/2/account/getUid.json';
		$url .= '?accessToken='.$accessToken;

		$libHttp = Be::getLib('Http');
		$Response = $libHttp->get($url);
        $Response = jsonDecode($Response);

        if (isset($Response->error)) {
			$this->setError($Response->errorCode.': '.$Response->error);
			return false;
        }

        return $Response->uid;
	}


	public function getUserInfo($accessToken, $uid)
	{
		$url = 'https://api.weibo.com/2/users/show.json';
		$url .= '?accessToken='.$accessToken;
		$url .= '&uid='.$uid;

		$libHttp = Be::getLib('Http');
		$Response = $libHttp->get($url);

		$Response = jsonDecode($Response);

        if (isset($Response->error)) {
			$this->setError($Response->errorCode.': '.$Response->error);
			return false;
        }

		return $Response;
	}

	public function register($userInfo)
	{
		$configUser = Be::getConfig('System.user');

		$t = time();
		$rowUser = Be::getRow('System.user');
		$rowUser->connect = 'sina';
		$rowUser->name = $userInfo->name;
		$rowUser->registerTime = $t;
		$rowUser->lastVisitTime = $t;
		$rowUser->isAdmin = 0;
		$rowUser->block = 0;
		$rowUser->save();

		$libHttp = Be::getLib('Http');
		$Response = $libHttp->get($userInfo->avatarLarge);

		$t = date('YmdHis', $t);
		
        $tmpAvatar = PATH_DATA.DS.'system'.DS.'tmp'.DS.'userConnectSina_'.$t.'_'.$rowUser->id;
        file_put_contents($tmpAvatar, $Response);

		$libImage = Be::getLib('image');
		$libImage->open($tmpAvatar);
		if ($libImage->isImage()) {
			$libImage->resize($configUser->avatarLW, $configUser->avatarLH, 'north');
			$libImage->save(PATH_DATA.DS.'user'.DS.'avatar'.DS.$rowUser->id.'_'.$t.'L.'.$libImage->getType());
			$rowUser->avatarL = $rowUser->id.'_'.$t.'L.'.$libImage->getType();

			$libImage->resize($configUser->avatarMW, $configUser->avatarMH, 'north');
			$libImage->save(PATH_DATA.DS.'user'.DS.'avatar'.DS.$rowUser->id.'_'.$t.'M.'.$libImage->getType());
			$rowUser->avatarM = $rowUser->id.'_'.$t.'M.'.$libImage->getType();

			$libImage->resize($configUser->avatarSW, $configUser->avatarSH, 'north');
			$libImage->save(PATH_DATA.DS.'user'.DS.'avatar'.DS.$rowUser->id.'_'.$t.'S.'.$libImage->getType());
			$rowUser->avatarS = $rowUser->id.'_'.$t.'S.'.$libImage->getType();

			$rowUser->save();
		}
		
		unlink($tmpAvatar);

		return $rowUser;
	}

	public function systemLogin($userId)
	{
		session::set('User', Be::getUser($userId));
	}

}
