<?php
namespace App\System\Controller;

use Phpbe\System\Be;
use Phpbe\System\Controller;
use Phpbe\System\Request;
use Phpbe\System\Response;

class UserProfile extends Controller
{

    public function __construct()
    {
        $my = Be::getUser();
        if ($my->id == 0) {
            Response::error('登陆超时，请重新登陆！', url('app=System&controller=User&action=login&return=httpReferer'), -1);
        }
    }

    public function home()
    {
        Response::setTitle('用户中心');
        Response::display();
    }


    // 上传头像
    public function uploadAvatar()
    {
        if (Request::isPost()) {
            $my = Be::getUser();
            try {
                Be::getService('System', 'User')->uploadAvatar($my->id, Request::files('avatar'));
                Response::setMessage('您的头像已更新！');
            } catch (\Exception $e) {
                Response::setMessage($e->getMessage(), 'error');
            }
            Response::redirect(url('controller=userProfile&action=editAvatar'));
        } else {
            Response::setTitle('上传头像');
            Response::display();
        }
    }


    // 删除头像，即改成系统默认头像
    public function initAvatar()
    {
        $my = Be::getUser();

        $return = url('controller=userProfile&action=editAvatar');
        try {
            Be::getService('System', 'User')->initAvatar($my->id);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), $return);
        }

        // 改为默认头像
        $my->avatarS = '';
        $my->avatarM = '';
        $my->avatarL = '';

        Response::success('您的头像已删除！', $return);
    }


    // 修改用户资料
    public function edit()
    {
        if (Request::isPost()) {

            try {
                $my = Be::getUser();

                $rowUser = Be::getService('System', 'User')->edit($my->id, Request::post());

                $my->name = $rowUser->name;
                $my->gender = $rowUser->gender;
                $my->phone = $rowUser->phone;
                $my->mobile = $rowUser->mobile;
                $my->qq = $rowUser->qq;

            } catch (\Exception $e) {
                Response::error($e->getMessage(), 'error');
            }

            Response::success('您的资料已保存！');
        } else {
            Response::setTitle('修改资料');
            Response::display();
        }
    }

    // 修改密码
    public function changePassword()
    {
        if (Request::isPost()) {
            $password = Request::post('password', '');
            $password1 = Request::post('password1', '');
            $password2 = Request::post('password2', '');

            if ($password1 != $password2) {
                Response::error('两次输入的密码不匹配！');
            }

            try {
                Be::getService('System', 'User')->changePassword(Be::getUser()->id, $password, $password1);
            } catch (\Exception $e) {
                Response::error($e->getMessage(), 'error');
            }

            Response::success('您的密码已重设！');
        } else {
            Response::setTitle('修改密码');
            Response::display();
        }
    }
}
