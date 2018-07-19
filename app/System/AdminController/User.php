<?php
namespace App\System\AdminController;

use Phpbe\System\Be;
use Phpbe\System\Request;
use Phpbe\System\Response;
use Phpbe\System\AdminController;

class User extends AdminController
{

    // 管理用户列表
    public function users()
    {
        $orderBy = Request::post('orderBy', 'id');
        $orderByDir = Request::post('orderByDir', 'ASC');
        $key = Request::post('key', '');
        $status = Request::post('status', -1, 'int');
        $limit = Request::post('limit', -1, 'int');
        $roleId = Request::post('roleId', 0, 'int');

        if ($limit == -1) {
            $adminConfigSystem = Be::getConfig('System', 'Admin');
            $limit = $adminConfigSystem->limit;
        }

        $option = array(
            'key' => $key,
            'status' => $status
        );
        if ($roleId > 0) $option['roleId'] = $roleId;

        $adminServiceUser = Be::getService('System', 'User');

        Response::setTitle('用户列表');

        $pagination = Be::getUi('Pagination');
        $pagination->setLimit($limit);
        $pagination->setTotal($adminServiceUser->getUserCount($option));
        $pagination->setPage(Request::post('page', 1, 'int'));

        Response::set('pagination', $pagination);
        Response::set('orderBy', $orderBy);
        Response::set('orderByDir', $orderByDir);
        Response::set('key', $key);
        Response::set('status', $status);
        Response::set('roleId', $roleId);

        $option['orderBy'] = $orderBy;
        $option['orderByDir'] = $orderByDir;
        $option['offset'] = $pagination->getOffset();
        $option['limit'] = $limit;

        Response::set('users', $adminServiceUser->getUsers($option));

        Response::set('roles', $adminServiceUser->getRoles());
        Response::display();

        $libHistory = Be::getLib('History');
        $libHistory->save();
    }

    // 修改用户
    public function edit()
    {
        $id = Request::request('id', 0, 'int');

        $user = Be::getRow('System', 'User');
        if ($id != 0) $user->load($id);

        if ($id != 0)
            Response::setTitle('修改用户资料');
        else
            Response::setTitle('添加新用户');

        Response::set('user', $user);

        $adminServiceUser = Be::getService('System', 'User');
        Response::set('roles', $adminServiceUser->getRoles());

        Response::display();
    }

    // 保存修改用户
    public function editSave()
    {
        $id = Request::post('id', 0, 'int');

        if (Request::post('username', '') == '') {
            Response::setMessage('请输入用户名！', 'error');
            Response::redirect('./?controller=user&action=edit&id=' . $id);
        }

        if (Request::post('email', '') == '') {
            Response::setMessage('请输入邮箱！', 'error');
            Response::redirect('./?controller=user&action=edit&id=' . $id);
        }

        $password = Request::post('password', '');
        if ($password != Request::post('password2', '')) {
            Response::setMessage('两次输入的密码不匹配！', 'error');
            Response::redirect('./?controller=user&action=edit&id=' . $id);
        }

        if ($id == 0 && $password == '') {
            Response::setMessage('密码不能为空！', 'error');
            Response::redirect('./?controller=user&action=edit&id=' . $id);
        }

        $rowUser = Be::getRow('System', 'User');
        if ($id > 0) $rowUser->load($id);

        $rowUser->bind(Request::post());

        $adminServiceUser = Be::getService('System', 'User');

        if (!$adminServiceUser->isUsernameAvailable($rowUser->username, $id)) {
            Response::setMessage('用户名(' . $rowUser->username . ')已被占用！', 'error');
            Response::redirect('./?controller=user&action=edit&id=' . $id);
        }

        if (!$adminServiceUser->isEmailAvailable($rowUser->email, $id)) {
            Response::setMessage('邮箱(' . $rowUser->email . ')已被占用！', 'error');
            Response::redirect('./?controller=user&action=edit&id=' . $id);
        }

        if ($password != '') {
            $serviceUser = Be::getService('System', 'User');
            $rowUser->password = $serviceUser->encryptPassword($password);
        } else
            unset($rowUser->password);

        if ($id == 0) {
            $rowUser->registerTime = time();
            $rowUser->lastLoginTime = 0;
        } else {
            unset($rowUser->registerTime);
            unset($rowUser->lastLoginTime);
        }

        if (!$rowUser->save()) {
            Response::end($rowUser->getError());
        }

        $configUser = Be::getConfig('System', 'User');

        $avatar = $_FILES['avatar'];
        if ($avatar['error'] == 0) {
            $libImage = Be::getLib('image');
            $libImage->open($avatar['tmpName']);
            if ($libImage->isImage()) {
                $adminServiceUser->deleteAvatarFile($rowUser);

                $t = date('YmdHis');

                $libImage->resize($configUser->avatarLW, $configUser->avatarLH, 'north');
                $libImage->save(Be::getRuntime()->getPathData() . '/system/user/avatar/' .  $rowUser->id . '_' . $t . 'L.' . $libImage->getType());
                $rowUser->avatarL = $rowUser->id . '_' . $t . 'L.' . $libImage->getType();

                $libImage->resize($configUser->avatarMW, $configUser->avatarMH, 'north');
                $libImage->save(Be::getRuntime()->getPathData() . '/system/user/avatar/' .  $rowUser->id . '_' . $t . 'M.' . $libImage->getType());
                $rowUser->avatarM = $rowUser->id . '_' . $t . 'M.' . $libImage->getType();

                $libImage->resize($configUser->avatarSW, $configUser->avatarSH, 'north');
                $libImage->save(Be::getRuntime()->getPathData() . '/system/user/avatar/' .  $rowUser->id . '_' . $t . 'S.' . $libImage->getType());
                $rowUser->avatarS = $rowUser->id . '_' . $t . 'S.' . $libImage->getType();

                if (!$rowUser->save()) {
                    Response::end($rowUser->getError());
                }
            }
        }

        if ($id == 0) {
            $configSystem = Be::getConfig('System', 'System');

            $data = array(
                'siteName' => $configSystem->siteName,
                'username' => $rowUser->username,
                'email' => $rowUser->email,
                'password' => $password,
                'name' => $rowUser->name,
                'siteUrl' => Be::getRuntime()->getUrlRoot()
            );

            $libMail = Be::getLib('mail');

            $subject = $libMail->format($configUser->adminCreateAccountMailSubject, $data);
            $body = $libMail->format($configUser->adminCreateAccountMailBody, $data);

            $libMail = Be::getLib('mail');
            $libMail->setSubject($subject);
            $libMail->setBody($body);
            $libMail->to($rowUser->email);
            $libMail->send();
        }

        Response::setMessage($id == 0 ? '成功添加新用户！' : '成功修改用户资料！');
        systemLog($id == 0 ? ('添加新用户：' . $rowUser->username) : ('修改用户(' . $rowUser->username . ')资料'));

        $libHistory = Be::getLib('History');
        $libHistory->back();
    }

    public function checkUsername()
    {
        $username = Request::get('username', '');

        $serviceUser = Be::getService('System', 'User');
        echo $serviceUser->isUsernameAvailable($username) ? 'true' : 'false';
    }

    public function checkEmail()
    {
        $email = Request::get('email', '');

        $serviceUser = Be::getService('System', 'User');
        echo $serviceUser->isEmailAvailable($email) ? 'true' : 'false';
    }

    public function unblock()
    {
        $ids = Request::post('id', '');

        $serviceUser = Be::getService('System', 'User');
        if ($serviceUser->unblock($ids)) {
            Response::setMessage('启用用户账号成功！');
            systemLog('启用用户账号：#' . $ids);
        } else
            Response::setMessage($serviceUser->getError(), 'error');

        $libHistory = Be::getLib('History');
        $libHistory->back();
    }

    public function block()
    {
        $ids = Request::post('id', '');

        $serviceUser = Be::getService('System', 'User');
        if ($serviceUser->block($ids)) {
            Response::setMessage('屏蔽用户账号成功！');
            systemLog('屏蔽用户账号：#' . $ids);
        } else
            Response::setMessage($serviceUser->getError(), 'error');

        $libHistory = Be::getLib('History');
        $libHistory->back();
    }

    public function ajaxInitAvatar()
    {
        $userId = Request::get('userId', 0, 'int');

        $adminServiceUser = Be::getService('System', 'User');
        if ($adminServiceUser->initAvatar($userId)) {
            systemLog('删除 #' . $userId . ' 用户头像');

            Response::set('error', 0);
            Response::set('message', '删除头像成功！');
        } else {
            Response::set('error', 2);
            Response::set('message', $adminServiceUser->getError());
        }

        Response::ajax();

    }

    public function delete()
    {
        $ids = Request::post('id', '');

        $adminServiceUser = Be::getService('System', 'User');
        if ($adminServiceUser->delete($ids)) {
            Response::setMessage('删除用户账号成功！');
            systemLog('删除用户账号：#' . $ids);
        } else
            Response::setMessage($adminServiceUser->getError(), 'error');

        $libHistory = Be::getLib('History');
        $libHistory->back();
    }

    public function roles()
    {
        $adminServiceUser = Be::getService('System', 'User');
        $roles = $adminServiceUser->getRoles();

        foreach ($roles as $role) {
            if ($role->id > 1) $role->userCount = $adminServiceUser->getUserCount(array('roleId' => $role->id));
        }

        Response::setTitle('用户组');
        Response::set('roles', $roles);
        Response::display();
    }

    public function rolesSave()
    {
        $ids = Request::post('id', array(), 'int');
        $names = Request::post('name', array());
        $notes = Request::post('note', array());

        if (count($ids) > 0) {
            for ($i = 0, $n = count($ids); $i < $n; $i++) {
                $id = $ids[$i];

                if ($id == 1) continue;

                if ($id == 0 && $names[$i] == '') continue;

                $rowUserRole = Be::getRow('System', 'UserRole');
                if ($id != 0) $rowUserRole->load($id);
                $rowUserRole->name = $names[$i];
                $rowUserRole->note = $notes[$i];
                $rowUserRole->ordering = $i;
                $rowUserRole->save();
            }
        }

        $adminServiceUser = Be::getService('System', 'User');
        $adminServiceUser->updateUserRoles();

        systemLog('修改用户角色');

        Response::setMessage('修改用户角色成功！');
        Response::redirect('./?controller=user&action=roles');
    }

    public function ajaxSetDefaultRole()
    {
        $roleId = Request::get('roleId', 0, 'int');
        if ($roleId == 0) {
            Response::set('error', 1);
            Response::set('message', '参数(roleId)缺失！');
            Response::ajax();
        }

        $rowUserRole = Be::getRow('System', 'UserRole');
        $rowUserRole->load($roleId);
        if ($rowUserRole->id == 0) {
            Response::set('error', 2);
            Response::set('message', '不存在的角色！');
            Response::ajax();
        }

        $rowUserRole->setDefault();

        systemLog('设置用户角色 ' . $rowUserRole->name . ' 为默认用户角色');

        Response::set('error', 0);
        Response::set('message', '设置前台默认用户角色成功！');
        Response::ajax();
    }

    public function ajaxDeleteRole()
    {
        $roleId = Request::post('id', 0, 'int');
        if ($roleId == 0) {
            Response::set('error', 1);
            Response::set('message', '参数(roleId)缺失！');
            Response::ajax();
        }

        $rowUserRole = Be::getRow('System', 'user_role');
        $rowUserRole->load($roleId);
        if ($rowUserRole->id == 0) {
            Response::set('error', 2);
            Response::set('message', '不存在的角色！');
            Response::ajax();
        }

        if ($rowUserRole->default == 1) {
            Response::set('error', 3);
            Response::set('message', '默认角色不能删除！');
            Response::ajax();
        }

        $adminServiceUser = Be::getService('System', 'User');
        $userCount = $adminServiceUser->getUserCount(array('roleId' => $roleId));
        if ($userCount > 0) {
            Response::set('error', 4);
            Response::set('message', '当前有' . $userCount . '个用户属于这个角色，禁止删除！');
            Response::ajax();
        }

        $rowUserRole->delete();

        systemLog('删除用户角色：' . $rowUserRole->name);

        Response::set('error', 0);
        Response::set('message', '删除用户组成功！');
        Response::ajax();
    }

    public function rolePermissions()
    {
        $roleId = Request::get('roleId', 0, 'int');
        if ($roleId == 0) Response::end('参数(roleId)缺失！');

        $rowUserRole = Be::getRow('System', 'UserRole');
        $rowUserRole->load($roleId);
        if ($rowUserRole->id == 0) Response::end('不存在的角色！');

        $adminServiceApp = Be::getService('System', 'App');
        $apps = $adminServiceApp->getApps();

        Response::setTitle('用户角色(' . $rowUserRole->name . ')权限设置');
        Response::set('role', $rowUserRole);
        Response::set('apps', $apps);
        Response::display();
    }


    public function rolePermissionsSave()
    {
        $roleId = Request::post('roleId', 0, 'int');
        if ($roleId == 0) Response::end('参数(roleId)缺失！');

        $rowUserRole = Be::getRow('System', 'UserRole');
        $rowUserRole->load($roleId);
        if ($rowUserRole->id == 0) Response::end('不存在的角色！');
        $rowUserRole->permission = Request::post('permission', 0, 'int');

        if ($rowUserRole->permission == -1) {
            $publicPermissions = [];
            $adminServiceApp = Be::getService('System', 'App');
            $apps = $adminServiceApp->getApps();
            foreach ($apps as $app) {
                $appPermissions = $app->getPermissions();
                if (count($appPermissions) > 0) {
                    foreach ($appPermissions as $key => $val) {
                        if ($key == '-') {
                            $publicPermissions = array_merge($publicPermissions, $val);
                        }
                    }
                }
            }

            $permissions = Request::post('permissions', array());
            $permissions = array_merge($publicPermissions, $permissions);
            $rowUserRole->permissions = implode(',', $permissions);
        } else {
            $rowUserRole->permissions = '';
        }

        $rowUserRole->save();

        $adminServiceUser = Be::getService('System', 'User');
        $adminServiceUser->updateUserRole($roleId);

        systemLog('修改用户角色 ' . $rowUserRole->name . ' 权限');

        Response::setMessage('修改用户角色权限成功！');
        Response::redirect('./?controller=user&action=roles');
    }

    public function setting()
    {
        Response::setTitle('用户系统设置');
        Response::set('configUser', Be::getConfig('System', 'User'));
        Response::display();
    }

    public function settingSave()
    {
        $configUser = Be::getConfig('System', 'User');
        $configUser->register = Request::post('register', 0, 'int');
        $configUser->captchaLogin = Request::post('captchaLogin', 0, 'int');
        $configUser->captchaRegister = Request::post('captchaRegister', 0, 'int');
        $configUser->emailValid = Request::post('emailValid', 0, 'int');
        $configUser->emailRegister = Request::post('emailRegister', 0, 'int');
        $configUser->emailRegisterAdmin = Request::post('emailRegisterAdmin', '');
        $configUser->avatarSW = Request::post('avatarSW', 0, 'int');
        $configUser->avatarSH = Request::post('avatarSH', 0, 'int');
        $configUser->avatarMW = Request::post('avatarMW', 0, 'int');
        $configUser->avatarMH = Request::post('avatarMH', 0, 'int');
        $configUser->avatarLW = Request::post('avatarLW', 0, 'int');
        $configUser->avatarLH = Request::post('avatarLH', 0, 'int');
        $configUser->connectQq = Request::post('connectQq', 0, 'int');
        $configUser->connectQqAppId = Request::post('connectQqAppId', '');
        $configUser->connectQqAppKey = Request::post('connectQqAppKey', '');
        $configUser->connectSina = Request::post('connectSina', 0, 'int');
        $configUser->connectSinaAppKey = Request::post('connectSinaAppKey', '');
        $configUser->connectSinaAppSecret = Request::post('connectSinaAppSecret', '');


        // 缩图图大图
        $defaultAvatarL = $_FILES['defaultAvatarL'];
        if ($defaultAvatarL['error'] == 0) {
            $libImage = Be::getLib('image');
            $libImage->open($defaultAvatarL['tmpName']);
            if ($libImage->isImage()) {
                $defaultAvatarLName = date('YmdHis') . 'L.' . $libImage->getType();
                $defaultAvatarLPath = Be::getRuntime()->getPathData() . '/system/user/avatar/Default/' .  $defaultAvatarLName;
                if (move_uploaded_file($defaultAvatarL['tmpName'], $defaultAvatarLPath)) {
                    // @unlink(Be::getRuntime()->getPathData().'/user/avatar/default/'.$configUser->defaultAvatarL);
                    $configUser->defaultAvatarL = $defaultAvatarLName;
                }
            }
        }


        // 缩图图中图
        $defaultAvatarM = $_FILES['defaultAvatarM'];
        if ($defaultAvatarM['error'] == 0) {
            $libImage = Be::getLib('image');
            $libImage->open($defaultAvatarM['tmpName']);
            if ($libImage->isImage()) {
                $defaultAvatarMName = date('YmdHis') . 'M.' . $libImage->getType();
                $defaultAvatarMPath = Be::getRuntime()->getPathData() . '/system/user/avatar/Default/' .  $defaultAvatarMName;
                if (move_uploaded_file($defaultAvatarM['tmpName'], $defaultAvatarMPath)) {
                    // @unlink(Be::getRuntime()->getPathData().'/user/avatar/default/'.$configUser->defaultAvatarM);
                    $configUser->defaultAvatarM = $defaultAvatarMName;
                }
            }
        }

        // 缩图图小图
        $defaultAvatarS = $_FILES['defaultAvatarS'];
        if ($defaultAvatarS['error'] == 0) {
            $libImage = Be::getLib('image');
            $libImage->open($defaultAvatarS['tmpName']);
            if ($libImage->isImage()) {
                $defaultAvatarSName = date('YmdHis') . 'S.' . $libImage->getType();
                $defaultAvatarSPath = Be::getRuntime()->getPathData() . '/system/user/avatar/Default/' .  $defaultAvatarSName;
                if (move_uploaded_file($defaultAvatarS['tmpName'], $defaultAvatarSPath)) {
                    // @unlink(Be::getRuntime()->getPathData().'/user/avatar/default/'.$configUser->defaultAvatarS);
                    $configUser->defaultAvatarS = $defaultAvatarSName;
                }
            }
        }

        $serviceSystem = Be::getService('System', 'Cache');
        $serviceSystem->updateConfig($configUser, Be::getRuntime()->getPathData() . '/Config/User.php');

        systemLog('设置用户系统参数');

        Response::setMessage('成功保存用户系统设置！');
        Response::redirect('./?controller=user&action=setting');
    }
}
