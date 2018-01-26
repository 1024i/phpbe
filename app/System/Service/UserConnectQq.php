<?php
namespace App\System\Service;

use System\Be;
use System\Session;
use System\Request;

class UserConnectQq extends \System\Service
{

    private $appId = '';
    private $appKey = '';

    // 构造函数
    public function __construct()
    {
        $config = Be::getConfig('System.User');
        $this->appId = $config->connectQqAppId;
        $this->appKey = $config->connectQqAppKey;
    }


    public function login()
    {
        $state = md5(uniqid(rand(), true));
        Session::set('user_connect_qq_state', $state);

        $url = 'https://graph.qq.com/oauth2.0/authorize';
        $url .= '?ResponseType=code';
        $url .= '&clientId=' . $this->appId;
        $url .= '&redirectUri=' . urlencode(URL_ROOT . '/?app=System&controller=User&task=qqLoginCallback');
        $url .= '&state=' . $state;

        header("Location:$url");

        echo '<html>';
        echo '<head>';
        echo '<meta http-equiv="refresh" content="0; url=' . $url . '">';
        echo '<script language="javascript">';
        echo 'window.location.href="' . $url . '";';
        echo '</script>';
        echo '</head>';
        echo '<body></body>';
        echo '</html>';
    }

    public function callback()
    {
        if (Request::get('state', '') != Session::get('user_connect_qq_state')) {
            $this->setError('返回信息被篡改！');
            return false;
        }

        $url = 'https://graph.qq.com/oauth2.0/token';
        $url .= '?grantType=authorizationCode';
        $url .= '&clientId=' . $this->appId;
        $url .= '&clientSecret=' . $this->appKey;
        $url .= '&code=' . Request::get('code', '');
        $url .= '&redirectUri=' . urlencode(URL_ROOT . '/?app=System&controller=User&task=qqLoginCallback');

        $libHttp = Be::getLib('Http');
        $response = $libHttp->get($url);

        if (strpos($response, "callback") !== false) {

            $lpos = strpos($response, "(");
            $rpos = strrpos($response, ")");
            $response = substr($response, $lpos + 1, $rpos - $lpos - 1);
            $msg = json_decode($response);

            if (isset($msg->error)) {
                $this->setError($msg->error . ': ' . $msg->errorDescription);
                return false;
            }
        }

        $params = array();
        parse_str($response, $params);

        return $params['accessToken'];
    }

    public function getOpenid($accessToken)
    {
        $url = 'https://graph.qq.com/oauth2.0/me';
        $url .= '?accessToken=' . $accessToken;

        $libHttp = Be::getLib('Http');
        $response = $libHttp->get($url);

        //--------检测错误是否发生
        if (strpos($response, "callback") !== false) {

            $lpos = strpos($response, "(");
            $rpos = strrpos($response, ")");
            $response = substr($response, $lpos + 1, $rpos - $lpos - 1);
        }

        $response = json_decode($response);
        if (isset($response->error)) {
            $this->setError($response->error . ': ' . $response->errorDescription);
            return false;
        }

        return $response->openid;
    }

    public function getUserInfo($accessToken, $openid)
    {
        $url = 'https://graph.qq.com/user/getUserInfo';
        $url .= '?oauthConsumerKey=' . $this->appId;
        $url .= '&accessToken=' . $accessToken;
        $url .= '&openid=' . $openid;
        $url .= '&format=json';

        $libHttp = Be::getLib('Http');
        $response = $libHttp->get($url);

        $response = json_decode($response);

        if ($response->ret != 0) {
            $this->setError($response->msg);
            return false;
        }

        return $response;
    }

    public function register($userInfo)
    {
        $configUser = Be::getConfig('System.User');

        $t = time();
        $rowUser = Be::getRow('System.User');
        $rowUser->connect = 'qq';
        $rowUser->name = $userInfo->nickname;
        $rowUser->register_time = $t;
        $rowUser->last_visit_time = $t;
        $rowUser->block = 0;
        $rowUser->save();

        $libHttp = Be::getLib('Http');
        $response = $libHttp->get($userInfo->figureurl_qq_2 ? $userInfo->figureurl_qq_1 : $userInfo->figureurl_qq_2);

        $t = date('YmdHis', $t);

        $tmpAvatar = PATH_DATA . '/System/Tmp/user_connect_qq_' . $t . '_' . $rowUser->id;
        file_put_contents($tmpAvatar, $response);

        $libImage = Be::getLib('image');
        $libImage->open($tmpAvatar);
        if ($libImage->isImage()) {

            $libImage->resize($configUser->avatar_l_w, $configUser->avatar_l_h, 'north');
            $libImage->save(PATH_DATA . '/System/User/Avatar/' . $rowUser->id . '_' . $t . '_l.' . $libImage->getType());
            $rowUser->avatar_l = $rowUser->id . '_' . $t . '_l.' . $libImage->getType();

            $libImage->resize($configUser->avatar_m_w, $configUser->avatar_m_h, 'north');
            $libImage->save(PATH_DATA . '/System/User/Avatar/' . $rowUser->id . '_' . $t . '_m.' . $libImage->getType());
            $rowUser->avatar_m = $rowUser->id . '_' . $t . '_m.' . $libImage->getType();

            $libImage->resize($configUser->avatar_s_w, $configUser->avatar_s_h, 'north');
            $libImage->save(PATH_DATA . '/System/User/Avatar/' . $rowUser->id . '_' . $t . '_s.' . $libImage->getType());
            $rowUser->avatar_s = $rowUser->id . '_' . $t . '_s.' . $libImage->getType();

            $rowUser->save();
        }

        unlink($tmpAvatar);

        return $rowUser;
    }

    // 用户登陆到BE系统
    public function systemLogin($userId)
    {
        Session::set('_user', Be::getUser($userId));
    }

}
