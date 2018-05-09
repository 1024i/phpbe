<?php

namespace App\System\Controller;

use Phpbe\System\Be;
use Phpbe\System\Request;
use Phpbe\System\Response;
use Phpbe\System\Session;
use Phpbe\System\Controller;

class User extends Controller
{

    public function index()
    {
        $this->login();
    }

    // 登陆页面
    public function login()
    {
        // 登陆成功后跳转到的网址
        $return = Request::get('return', '');
        if ($return == 'httpReferer' && isset($_SERVER['HTTP_REFERER'])) $return = base64_encode($_SERVER['HTTP_REFERER']);
        if ($return == '') $return = url('controller=userProfile&task=home');

        $my = Be::getUser();
        if ($my->id > 0) Response::redirect($return);

        $model = Be::getService('System.User');
        $user = $model->rememberMe();
        if ($user) Response::redirect($return);

        Response::setTitle('登陆');
        Response::set('return', $return);
        Response::display();
    }

    // 登陆验证码
    public function captchaLogin()
    {
        $template = Be::getTemplate('user.login');
        $color = Response::getColor();

        $libCss = Be::getLib('css');
        $rgbColor = $libCss->hexToRgb($color);

        $captcha = Be::getLib('captcha');
        $captcha->setFontColor($rgbColor);
        $captcha->point(20); // 添加干扰点
        $captcha->line(3); // 添加干扰线
        $captcha->distortion();    // 扭曲
        $captcha->border(1, $rgbColor); // 添加边框
        $captcha->output();

        Session::set('captchaLogin', $captcha->toString());
    }

    // 登陆检查
    public function loginCheck()
    {
        $username = Request::post('username', '');
        $password = Request::post('password', '');
        $rememberMe = Request::post('rememberMe', '0');

        $return = Request::post('return', '');
        $errorReturn = url('controller=user&task=login&return=' . $return);

        if ($username == '') {
            Response::error('用户名不能为空！', $errorReturn);
        }

        if ($password == '') {
            Response::error('密码不能为空！', $errorReturn);
        }

        $configUser = Be::getConfig('System.User');
        if ($configUser->captchaLogin) {
            if (Request::post('captcha', '') != Session::get('captchaLogin')) {
                Response::error('验证码错误！', $errorReturn);
            }
        }

        $serviceUser = Be::getService('System.User');
        if ($serviceUser->login($username, $password, $rememberMe)) {
            if ($configUser->captchaLogin) session::delete('captchaLogin');

            $redirectUrl = null;
            if ($return == '') {
                $redirectUrl = url('controller=userProfile&task=home');
            } else {
                $redirectUrl = base64_decode($return);
            }

            Response::success('登陆成功！', $redirectUrl);
        } else {
            Response::error($serviceUser->getError(), $errorReturn);
        }
    }

    public function qqLogin()
    {
        $configUser = Be::getConfig('System.User');
        if (!$configUser->connectQq) Response::end('使用QQ账号登陆未启用！');

        $serviceUserConnectQq = Be::getService('userConnectQq');
        $serviceUserConnectQq->login();
    }

    public function qqLoginCallback()
    {
        $configUser = Be::getConfig('System.User');
        if (!$configUser->connectQq) Response::end('使用QQ账号登陆未启用！');

        $serviceUserConnectQq = Be::getService('userConnectQq');
        $accessToken = $serviceUserConnectQq->callback();
        if ($accessToken == false) Response::end($serviceUserConnectQq->getError());

        $openid = $serviceUserConnectQq->getOpenid($accessToken);
        if ($openid == false) Response::end($serviceUserConnectQq->getError());

        $userInfo = $serviceUserConnectQq->getUserInfo($accessToken, $openid);
        if ($userInfo == false) Response::end($serviceUserConnectQq->getError());

        $rowUserConnectQq = Be::getRow('userConnectQq');
        $rowUserConnectQq->loadBy('openid', $openid);
        if ($rowUserConnectQq->userId > 0) {
            $serviceUserConnectQq->systemLogin($rowUserConnectQq->userId);
        } else {
            $user = $serviceUserConnectQq->register($userInfo);
            $rowUserConnectQq->userId = $user->id;

            $serviceUserConnectQq->systemLogin($user->id);
        }

        unset($userInfo->id);
        unset($userInfo->userId);

        $rowUserConnectQq->bind($userInfo);
        $rowUserConnectQq->accessToken = $accessToken;
        $rowUserConnectQq->openid = $openid;
        $rowUserConnectQq->save();

        Response::redirect(url('controller=userProfile&task=home'));
    }


    public function sinaLogin()
    {
        $configUser = Be::getConfig('System.User');
        if (!$configUser->connectSina) Response::end('使用新浪微博账号登陆未启用！');

        $serviceUserConnectSina = Be::getService('userConnectSina');
        $serviceUserConnectSina->login();
    }

    public function sinaLoginCallback()
    {
        $configUser = Be::getConfig('System.User');
        if (!$configUser->connectSina) Response::end('使用新浪微博账号登陆未启用！');

        $serviceUserConnectSina = Be::getService('userConnectSina');
        $accessToken = $serviceUserConnectSina->callback();
        if ($accessToken == false) Response::end($serviceUserConnectSina->getError());

        $uid = $serviceUserConnectSina->getUid($accessToken);
        if ($uid == false) Response::end($serviceUserConnectSina->getError());

        $userInfo = $serviceUserConnectSina->getUserInfo($accessToken, $uid);
        if ($userInfo == false) Response::end($serviceUserConnectSina->getError());

        $rowUserConnectSina = Be::getRow('userConnectSina');
        $rowUserConnectSina->loadBy('uid', $uid);
        if ($rowUserConnectSina->userId > 0) {
            $serviceUserConnectSina->systemLogin($rowUserConnectSina->userId);
        } else {
            $user = $serviceUserConnectSina->register($userInfo);
            $rowUserConnectSina->userId = $user->id;

            $serviceUserConnectSina->systemLogin($user->id);
        }

        unset($userInfo->id);
        unset($userInfo->userId);

        $rowUserConnectSina->bind($userInfo);
        $rowUserConnectSina->accessToken = $accessToken;
        $rowUserConnectSina->uid = $uid;
        $rowUserConnectSina->save();

        Response::redirect(url('controller=userProfile&task=home'));
    }


    // 注册新用户
    public function register()
    {
        $configUser = Be::getConfig('System.User');
        if (!$configUser->register) Response::end('注册功能已禁用！');

        Response::setTitle('注册新账号');
        Response::display();
    }

    // 验证码
    public function captchaRegister()
    {
        $template = Be::getTemplate('user.register');
        $color = Response::getColor();

        $libCss = Be::getLib('css');
        $rgbColor = $libCss->hexToRgb($color);

        $captcha = Be::getLib('captcha');
        $captcha->setFontColor($rgbColor);
        $captcha->point(20); // 添加干扰点
        $captcha->line(3); // 添加干扰线
        $captcha->distortion();    // 扭曲
        $captcha->border(1, $rgbColor); // 添加边框
        $captcha->output();

        Session::set('captchaRegister', $captcha->toString());
    }

    // 保存新注册用户
    public function ajaxRegisterSave()
    {
        $configUser = Be::getConfig('System.User');

        if (!$configUser->register) {
            Response::error('注册功能已禁用！');
        }

        $username = Request::post('username', '');
        $email = Request::post('email', '');
        $name = Request::post('name', '');
        $password = Request::post('password', '');
        $password2 = Request::post('password2', '');

        $serviceUser = Be::getService('System.User');


        if ($username == '') {
            Response::error('用户名不能为空！');
        }

        if ($email == '') {
            Response::error('邮箱不能为空！');
        }

        if (!$serviceUser->isEmail($email)) {
            Response::error('非法的邮箱格式！');
        }

        if ($password == '') {
            Response::error('密码不能为空！');
        }

        if ($password != $password2) {
            Response::error('两次输入的密码不匹配！');
        }

        if ($configUser->captchaRegister) {
            if (Request::post('captcha', '') != Session::get('captchaRegister')) {
                Response::error('验证码错误！');
            }
        }

        if ($serviceUser->register($username, $email, $password, $name)) {
            if ($configUser->captchaRegister) session::delete('captchaRegister');

            Response::success('您的账号已成功创建！', url('controller=user&task=registerSuccess&username=' . $username . '&email=' . $email));
        } else {
            Response::error($serviceUser->getError());
        }
    }

    // 注册成功
    public function registerSuccess()
    {
        $username = Request::get('username', '');
        $email = Request::get('email', '');

        Response::setTitle('注册成功');
        Response::set('username', $username);
        Response::set('email', $email);
        Response::display();
    }


    //找回密码表单
    public function forgotPassword()
    {
        Response::setTitle('忘记密码');
        Response::display();
    }

    //提交找回密码
    public function ajaxForgotPasswordSave()
    {
        $username = Request::post('username', '');
        if ($username == '') {
            Response::error('参数(username)缺失！');
        }

        $model = Be::getService('System.User');
        if ($model->forgotPassword($username)) {
            Response::success('找回密码链接已发送到您的邮箱。');
        } else {
            Response::error($model->getError());
        }
    }

    // 重设密码
    public function forgotPasswordReset()
    {
        $userId = Request::get('userId', 0, 'int');
        $token = Request::get('token', '');
        if ($userId == 0 || $token == '') Response::end('找回密码链接已失效！');

        $rowUser = Be::getRow('System.User');
        $rowUser->load($userId);

        if ($rowUser->token == '') Response::end('您的密码已重设！');
        if ($rowUser->token != $token) Response::end('找回密码链接非法！');

        Response::setTitle('重设密码');
        Response::set('user', $rowUser);
        Response::display();
    }

    // 重设密码保存
    public function ajaxForgotPasswordResetSave()
    {
        $userId = Request::post('userId', 0, 'int');
        $token = Request::post('token', '');

        if ($userId == 0 || $token == '') {
            Response::error('参数(userId/token)缺失！');
        }

        $password = Request::post('password', '');
        $password2 = Request::post('password2', '');

        if ($password != $password2) {
            Response::error('两次输入的密码不匹配！');
        }

        $model = Be::getService('System.User');
        if ($model->forgotPasswordReset($userId, $token, $password)) {
            Response::success('重设密码成功！');
        } else {
            Response::error($model->getError());
        }

    }

    // 退出登陆
    public function logout()
    {
        $model = Be::getService('System.User');
        $model->logout();

        Response::success('成功退出！', url('controller=user&task=login'));
    }

}
