<?php
namespace service;

use System\Be;
use System\Session;
use System\Request;

class userConnectQq extends \System\Service
{
	
	private $appId = '';
	private $appKey = '';

    // 构造函数
    public function __construct()
    {
		$config = Be::getConfig('System.user');
        $this->appId = $config->connectQqAppId;
		$this->appKey = $config->connectQqAppKey;
    }


	public function login()
	{
		$state = md5(uniqid(rand(), true));
		session::set('userConnectQqState', $state);

        $url = 'https://graph.qq.com/oauth2.0/authorize';
		$url .= '?ResponseType=code';
		$url .= '&clientId='.$this->appId;
		$url .= '&redirectUri='.urlencode(URL_ROOT.'/?controller=user&task=qqLoginCallback');
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
		if (Request::get('state', '')!=session::get('userConnectQqState')) {
			$this->setError('返回信息被篡改！');
			return false;
		}

        $url = 'https://graph.qq.com/oauth2.0/token';
		$url .= '?grantType=authorizationCode';
		$url .= '&clientId='.$this->appId;
		$url .= '&clientSecret='.$this->appKey;
		$url .= '&code='.Request::get('code','');
		$url .= '&redirectUri='.urlencode(URL_ROOT.'/?controller=user&task=qqLoginCallback');

		$libHttp = Be::getLib('Http');
		$Response = $libHttp->get($url);

        if (strpos($Response, "callback") !== false){

            $lpos = strpos($Response, "(");
            $rpos = strrpos($Response, ")");
            $Response  = substr($Response, $lpos + 1, $rpos - $lpos -1);
            $msg = jsonDecode($Response);

            if (isset($msg->error)){
				$this->setError($msg->error.': '.$msg->errorDescription);
				return false;
            }
        }

        $params = array();
        parseStr($Response, $params);

		return $params['accessToken'];
	}

	public function getOpenid($accessToken)
	{
		$url = 'https://graph.qq.com/oauth2.0/me';
		$url .= '?accessToken='.$accessToken;

		$libHttp = Be::getLib('Http');
		$Response = $libHttp->get($url);

        //--------检测错误是否发生
        if (strpos($Response, "callback") !== false){

            $lpos = strpos($Response, "(");
            $rpos = strrpos($Response, ")");
            $Response = substr($Response, $lpos + 1, $rpos - $lpos -1);
        }

        $Response = jsonDecode($Response);
        if (isset($Response->error)) {
			$this->setError($Response->error.': '.$Response->errorDescription);
			return false;
        }

        return $Response->openid;
	}

	public function getUserInfo($accessToken, $openid)
	{
		$url = 'https://graph.qq.com/user/getUserInfo';
		$url .= '?oauthConsumerKey='.$this->appId;
		$url .= '&accessToken='.$accessToken;
		$url .= '&openid='.$openid;
		$url .= '&format=json';

		$libHttp = Be::getLib('Http');
		$Response = $libHttp->get($url);

		$Response = jsonDecode($Response);

		if ($Response->ret!=0) {
			$this->setError($Response->msg);
			return false;
		}

		return $Response;
	}

	public function register($userInfo)
	{
		$configUser = Be::getConfig('System.user');

		$t = time();
		$rowUser = Be::getRow('System.user');
		$rowUser->connect = 'qq';
		$rowUser->name = $userInfo->nickname;
		$rowUser->registerTime = $t;
		$rowUser->lastVisitTime = $t;
		$rowUser->isAdmin = 0;
		$rowUser->block = 0;
		$rowUser->save();

		$libHttp = Be::getLib('Http');
		$Response = $libHttp->get($userInfo->figureurlQq_2?$userInfo->figureurlQq_1:$userInfo->figureurlQq_2);
		
		$t = date('YmdHis', $t);

		$tmpAvatar = PATH_DATA.DS.'system'.DS.'tmp'.DS.'userConnectQq_'.$t.'_'.$rowUser->id;
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

	// 用户登陆到BE系统
	public function systemLogin($userId)
	{
		session::set('User', Be::getUser($userId));
	}

}
