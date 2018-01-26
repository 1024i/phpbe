<?php
namespace App\System\Controller;

use System\Be;
use System\Request;
use System\Response;

class UserProfile extends UserAuth
{

    public function home()
    {
        Response::setTitle('用户中心');
        Response::display();
    }


    // 上传头像
    public function editAvatar()
    {
        Response::setTitle('上传头像');
        Response::display();
    }


    // 上传头像 保存
    public function editAvatarSave()
    {
        $configSystem = Be::getConfig('System.System');

        $avatar = $_FILES['avatar'];
        if ($avatar['error'] == 0) {
            $name = strtolower($avatar['name']);
            $type = '';
            $pos = strrpos($name, '.');
            if ($pos !== false) {
                $type = substr($name, $pos + 1);
            }
            if (!in_array($type, $configSystem->allowUploadImageTypes)) {
                Response::setMessage('您上传的不是合法的图像文件！', 'error');
            } else {
                $libImage = Be::getLib('image');
                $libImage->open($avatar['tmpName']);
                if (!$libImage->isImage()) {
                    Response::setMessage('您上传的不是合法的图像文件！', 'error');
                } else {
                    $my = Be::getUser();

                    $rowUser = Be::getRow('System.User');
                    $rowUser->load($my->id);

                    $configUser = Be::getConfig('System.User');

                    $avatarDir = PATH_DATA . '/system/user/avatar/';
                    if (!file_exists($avatarDir)) {
                        mkdir($avatarDir, 0777, true);
                    }

                    // 删除旧头像
                    if ($rowUser->avatarS != '') @unlink($avatarDir . $rowUser->avatarS);
                    if ($rowUser->avatarM != '') @unlink($avatarDir . $rowUser->avatarM);
                    if ($rowUser->avatarL != '') @unlink($avatarDir . $rowUser->avatarL);

                    $t = date('YmdHis');

                    $imageType = $libImage->getType();

                    // 按配置文件里的尺寸大小生成新头像
                    $libImage->resize($configUser->avatarLW, $configUser->avatarLH, 'north');
                    $libImage->save($avatarDir . $my->id . '_' . $t . 'L.' . $imageType);
                    $my->avatarL = $rowUser->avatarL = $my->id . '_' . $t . 'L.' . $imageType;

                    $libImage->resize($configUser->avatarMW, $configUser->avatarMH, 'north');
                    $libImage->save($avatarDir . $my->id . '_' . $t . 'M.' . $imageType);
                    $my->avatarM = $rowUser->avatarM = $my->id . '_' . $t . 'M.' . $imageType;

                    $libImage->resize($configUser->avatarSW, $configUser->avatarSH, 'north');
                    $libImage->save($avatarDir . $my->id . '_' . $t . 'S.' . $imageType);
                    $my->avatarS = $rowUser->avatarS = $my->id . '_' . $t . 'S.' . $imageType;

                    if ($rowUser->save()) {
                        Response::setMessage('您的头像已更新！');
                    } else {
                        Response::setMessage($rowUser->getError(), 'error');
                    }
                }
            }

            @unlink($avatar['tmpName']);
        } else {
            $uploadErrors = array(
                '1' => '您上传的文件过大！',
                '2' => '您上传的文件过大！',
                '3' => '文件只有部分被上传！',
                '4' => '没有文件被上传！',
                '5' => '上传的文件大小为 0！'
            );
            $error = null;
            if (array_key_exists($avatar['error'], $uploadErrors)) {
                $error = $uploadErrors[$avatar['error']];
            } else {
                $error = '错误代码：' . $avatar['error'];
            }
            Response::setMessage('上传失败' . '(' . $error . ')', 'error');
        }

        Response::redirect(url('controller=userProfile&task=editAvatar'));
    }

    // 删除头像，即改成系统默认头像
    public function initAvatar()
    {
        $my = Be::getUser();

        $rowUser = Be::getRow('System.User');
        $rowUser->load($my->id);

        $configUser = Be::getConfig('System.User');

        $avatarDir = PATH_DATA . '/system/user/avatar/';

        // 删除旧头像
        if ($rowUser->avatarS != '') @unlink($avatarDir . $rowUser->avatarS);
        if ($rowUser->avatarM != '') @unlink($avatarDir . $rowUser->avatarM);
        if ($rowUser->avatarL != '') @unlink($avatarDir . $rowUser->avatarL);

        // 改为默认头像
        $my->avatarS = $rowUser->avatarS = '';
        $my->avatarM = $rowUser->avatarM = '';
        $my->avatarL = $rowUser->avatarL = '';

        $return = url('controller=userProfile&task=editAvatar');
        if ($rowUser->save()) {
            Response::success('您的头像已删除！', $return);
        } else {
            Response::error($rowUser->getError(), $return);
        }
    }


    // 修改用户资料
    public function edit()
    {
        Response::setTitle('修改资料');
        Response::display();
    }

    // 修改用户资料
    public function ajaxEditSave()
    {
        $my = Be::getUser();

        $rowUser = Be::getRow('System.User');
        $rowUser->load($my->id);

        $my->name = $rowUser->name = Request::post('name', '');
        $my->gender = $rowUser->gender = Request::post('gender', 0, 'int');
        $my->phone = $rowUser->phone = Request::post('phone', '');
        $my->mobile = $rowUser->mobile = Request::post('mobile', '');
        $my->qq = $rowUser->qq = Request::post('qq', '');

        $rowUser->save();

        Response::success('您的资料已保存！');
    }

    // 修改密码
    public function editPassword()
    {
        Response::setTitle('修改密码');
        Response::display();
    }

    // 修改密码
    public function ajaxEditPasswordSave()
    {
        $my = Be::getUser();

        $password = Request::post('password', '');
        $password1 = Request::post('password1', '');
        $password2 = Request::post('password2', '');

        $rowUser = Be::getRow('System.User');
        $rowUser->load($my->id);

        $serviceUser = Be::getService('System.User');
        if ($serviceUser->encryptPassword($password) != $rowUser->password) {
            Response::error('当前密码错误！');
        }

        if ($password1 != $password2) {
            Response::error('两次输入的密码不匹配！');
        }

        $rowUser->password = $serviceUser->encryptPassword($password1);
        $rowUser->save();

        Response::success('您的密码已重设！');
    }

}
