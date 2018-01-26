<?php
namespace App\System\AdminController;

use System\Be;
use System\Request;
use System\Response;
use System\AdminController;

class AdminUser extends AdminController
{

    // 登陆页面
    public function login()
    {
        $my = Be::getAdminUser();

        if ($my->id > 0) {
            Response::redirect('./?app=System&controller=System&task=dashboard');
        }

        Response::setTitle('登录');
        Response::display();
    }

    // 登陆检查
    public function ajaxLoginCheck()
    {
        $username = Request::post('username', '');
        $password = Request::post('password', '');

        if ($username == '') {
            Response::set('error', 1);
            Response::set('message', '请输入管理员名！');
            Response::ajax();
        }

        if ($password == '') {
            Response::set('error', 2);
            Response::set('message', '请输入密码！');
            Response::ajax();
        }

        $adminServiceAdminUser = Be::getService('System.AdminUser');
        $user = $adminServiceAdminUser->login($username, $password);

        if ($user) {
            systemLog('登录后台');

            Response::set('error', 0);
            Response::set('message', '登录成功！');
            Response::ajax();
        } else {
            Response::set('error', 2);
            Response::set('message', $adminServiceAdminUser->getError());
            Response::ajax();
        }
    }

    // 退出登陆
    public function logout()
    {
        $adminServiceAdminUser = Be::getService('System.AdminUser');
        $adminServiceAdminUser->logout();

        Response::setMessage('成功退出！');
        Response::redirect('./?app=System&controller=AdminUser&task=login');
    }

    // 管理管理员列表
    public function users()
    {
        $orderBy = Request::post('orderBy', 'id');
        $orderByDir = Request::post('orderByDir', 'ASC');
        $key = Request::post('key', '');
        $status = Request::post('status', -1, 'int');
        $limit = Request::post('limit', -1, 'int');
        $roleId = Request::post('roleId', 0, 'int');

        if ($limit == -1) {
            $adminConfigSystem = Be::getConfig('System.admin');
            $limit = $adminConfigSystem->limit;
        }

        $option = array(
            'key' => $key,
            'status' => $status
        );
        if ($roleId > 0) $option['roleId'] = $roleId;

        $adminServiceAdminUser = Be::getService('System.AdminUser');

        Response::setTitle('管理员列表');

        $pagination = Be::getUi('Pagination');
        $pagination->setLimit($limit);
        $pagination->setTotal($adminServiceAdminUser->getUserCount($option));
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

        Response::set('users', $adminServiceAdminUser->getUsers($option));

        Response::set('roles', $adminServiceAdminUser->getRoles());
        Response::display();

        $libHistory = Be::getLib('History');
        $libHistory->save();
    }

    // 修改管理员
    public function edit()
    {
        $id = Request::request('id', 0, 'int');

        $adminUser = Be::getRow('adminUser');
        if ($id != 0) $adminUser->load($id);

        if ($id != 0)
            Response::setTitle('修改管理员资料');
        else
            Response::setTitle('添加新管理员');

        Response::set('adminUser', $adminUser);

        $adminServiceAdminUser = Be::getService('System.AdminUser');
        Response::set('roles', $adminServiceAdminUser->getRoles());

        Response::display();
    }

    // 保存修改管理员
    public function editSave()
    {
        $id = Request::post('id', 0, 'int');

        if (Request::post('username', '') == '') {
            Response::setMessage('请输入管理员名！', 'error');
            Response::redirect('./?app=System&controller=AdminUser&task=edit&id=' . $id);
        }

        if (Request::post('email', '') == '') {
            Response::setMessage('请输入邮箱！', 'error');
            Response::redirect('./?app=System&controller=AdminUser&task=edit&id=' . $id);
        }

        $password = Request::post('password', '');
        if ($password != Request::post('password2', '')) {
            Response::setMessage('两次输入的密码不匹配！', 'error');
            Response::redirect('./?app=System&controller=AdminUser&task=edit&id=' . $id);
        }

        if ($id == 0 && $password == '') {
            Response::setMessage('密码不能为空！', 'error');
            Response::redirect('./?app=System&controller=AdminUser&task=edit&id=' . $id);
        }

        $rowAdminUser = Be::getRow('adminUser');
        if ($id > 0) $rowAdminUser->load($id);

        $rowAdminUser->bind(Request::post());
        $adminServiceAdminUser = Be::getService('System.AdminUser');

        if (!$adminServiceAdminUser->isUsernameAvailable($rowAdminUser->username, $id)) {
            Response::setMessage('管理员名(' . $rowAdminUser->username . ')已被占用！', 'error');
            Response::redirect('./?app=System&controller=AdminUser&task=edit&id=' . $id);
        }

        if (!$adminServiceAdminUser->isEmailAvailable($rowAdminUser->email, $id)) {
            Response::setMessage('邮箱(' . $rowAdminUser->email . ')已被占用！', 'error');
            Response::redirect('./?app=System&controller=AdminUser&task=edit&id=' . $id);
        }

        if ($password != '') {
            $rowAdminUser->password = $adminServiceAdminUser->encryptPassword($password);
        } else
            unset($rowAdminUser->password);

        if ($id == 0) {
            $rowAdminUser->createTime = time();
            $rowAdminUser->lastVisitTime = time();
        } else {
            unset($rowAdminUser->createTime);
            unset($rowAdminUser->lastVisitTime);
        }

        $rowAdminUser->save();

        $configUser = Be::getConfig('System.AdminUser');

        $avatar = $_FILES['avatar'];
        if ($avatar['error'] == 0) {
            $libImage = Be::getLib('image');
            $libImage->open($avatar['tmpName']);
            if ($libImage->isImage()) {
                $adminServiceAdminUser->deleteAvatarFile($rowAdminUser);

                $t = date('YmdHis');

                $libImage->resize($configUser->avatarLW, $configUser->avatarLH, 'north');
                $libImage->save(PATH_DATA . '/adminUser/avatar/' .  $rowAdminUser->id . '_' . $t . 'L.' . $libImage->getType());
                $rowAdminUser->avatarL = $rowAdminUser->id . '_' . $t . 'L.' . $libImage->getType();

                $libImage->resize($configUser->avatarMW, $configUser->avatarMH, 'north');
                $libImage->save(PATH_DATA . '/adminUser/avatar/' .  $rowAdminUser->id . '_' . $t . 'M.' . $libImage->getType());
                $rowAdminUser->avatarM = $rowAdminUser->id . '_' . $t . 'M.' . $libImage->getType();

                $libImage->resize($configUser->avatarSW, $configUser->avatarSH, 'north');
                $libImage->save(PATH_DATA . '/adminUser/avatar/' .  $rowAdminUser->id . '_' . $t . 'S.' . $libImage->getType());
                $rowAdminUser->avatarS = $rowAdminUser->id . '_' . $t . 'S.' . $libImage->getType();

                $rowAdminUser->save();
            }
        }

        Response::setMessage($id == 0 ? '成功添加新管理员！' : '成功修改管理员资料！');
        systemLog($id == 0 ? ('添加新管理员：' . $rowAdminUser->username) : ('修改管理员(' . $rowAdminUser->username . ')资料'));

        $libHistory = Be::getLib('History');
        $libHistory->back();
    }

    public function checkUsername()
    {
        $username = Request::get('username', '');

        $adminServiceAdminUser = Be::getService('System.AdminUser');
        echo $adminServiceAdminUser->isUsernameAvailable($username) ? 'true' : 'false';
    }

    public function checkEmail()
    {
        $email = Request::get('email', '');

        $adminServiceAdminUser = Be::getService('System.AdminUser');
        echo $adminServiceAdminUser->isEmailAvailable($email) ? 'true' : 'false';
    }

    public function unblock()
    {
        $ids = Request::post('id', '');

        $adminServiceAdminUser = Be::getService('System.AdminUser');
        if ($adminServiceAdminUser->unblock($ids)) {
            Response::setMessage('启用管理员账号成功！');
            systemLog('启用管理员账号：#' . $ids);
        } else
            Response::setMessage($adminServiceAdminUser->getError(), 'error');

        $libHistory = Be::getLib('History');
        $libHistory->back();
    }

    public function block()
    {
        $ids = Request::post('id', '');

        $adminServiceAdminUser = Be::getService('System.AdminUser');
        if ($adminServiceAdminUser->block($ids)) {
            Response::setMessage('屏蔽管理员账号成功！');
            systemLog('屏蔽管理员账号：#' . $ids);
        } else
            Response::setMessage($adminServiceAdminUser->getError(), 'error');

        $libHistory = Be::getLib('History');
        $libHistory->back();
    }

    public function ajaxInitAvatar()
    {
        $userId = Request::get('userId', 0, 'int');

        $adminServiceAdminUser = Be::getService('System.AdminUser');
        if ($adminServiceAdminUser->initAvatar($userId)) {
            systemLog('删除 #' . $userId . ' 管理员头像');

            Response::set('error', 0);
            Response::set('message', '删除头像成功！');
        } else {
            Response::set('error', 2);
            Response::set('message', $adminServiceAdminUser->getError());
        }

        Response::ajax();

    }

    public function delete()
    {
        $ids = Request::post('id', '');

        $adminServiceAdminUser = Be::getService('System.AdminUser');
        if ($adminServiceAdminUser->delete($ids)) {
            Response::setMessage('删除管理员账号成功！');
            systemLog('删除管理员账号：#' . $ids);
        } else
            Response::setMessage($adminServiceAdminUser->getError(), 'error');

        $libHistory = Be::getLib('History');
        $libHistory->back();
    }


    public function roles()
    {
        $adminServiceAdminUser = Be::getService('System.AdminUser');
        $roles = $adminServiceAdminUser->getRoles();

        foreach ($roles as $role) {
            $role->userCount = $adminServiceAdminUser->getUserCount(array('roleId' => $role->id));
        }

        Response::setTitle('管理员角色');
        Response::set('roles', $roles);
        Response::set('tab', 'backend');
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

                $rowAdminUserRole = Be::getRow('System.AdminUserRole');
                if ($id != 0) $rowAdminUserRole->load($id);
                $rowAdminUserRole->name = $names[$i];
                $rowAdminUserRole->note = $notes[$i];
                $rowAdminUserRole->ordering = $i;
                $rowAdminUserRole->save();
            }
        }

        $adminServiceAdminUser = Be::getService('System.AdminUser');
        $adminServiceAdminUser->updateAdminUserRoles();

        systemLog('修改后台管理员组');

        Response::setMessage('修改后台管理员组成功！');
        Response::redirect('./?app=System&controller=AdminUser&task=roles');
    }

    public function ajaxDeleteRole()
    {
        $roleId = Request::post('id', 0, 'int');
        if ($roleId == 0) {
            Response::set('error', 1);
            Response::set('message', '参数(roleId)缺失！');
            Response::ajax();
        }

        $rowAdminUserRole = Be::getRow('System.AdminUserRole');
        $rowAdminUserRole->load($roleId);
        if ($rowAdminUserRole->id == 0) {
            Response::set('error', 2);
            Response::set('message', '不存在的分组');
            Response::ajax();
        }

        $adminServiceUser = Be::getService('System.User');
        $userCount = $adminServiceUser->getUserCount(array('roleId' => $roleId));
        if ($userCount > 0) {
            Response::set('error', 3);
            Response::set('message', '当前有' . $userCount . '个管理员属于这个分组，禁止删除！');
            Response::ajax();
        }

        $rowAdminUserRole->delete();

        systemLog('删除后台管理员组：' . $rowAdminUserRole->name);

        Response::set('error', 0);
        Response::set('message', '删除管理员组成功！');
        Response::ajax();
    }

    public function rolePermissions()
    {
        $roleId = Request::get('roleId', 0, 'int');
        if ($roleId == 0) Response::end('参数(roleId)缺失！');

        $rowAdminUserRole = Be::getRow('System.AdminUserRole');
        $rowAdminUserRole->load($roleId);
        if ($rowAdminUserRole->id == 0) Response::end('不存在的分组！');

        $adminServiceApp = Be::getService('System.app');
        $apps = $adminServiceApp->getApps();

        Response::setTitle('管理员组(' . $rowAdminUserRole->name . ')权限设置');
        Response::set('role', $rowAdminUserRole);
        Response::set('apps', $apps);
        Response::display();
    }

    public function rolePermissionsSave()
    {
        $roleId = Request::post('roleId', 0, 'int');
        if ($roleId == 0) Response::end('参数(roleId)缺失！');

        $rowAdminUserRole = Be::getRow('System.AdminUserRole');
        $rowAdminUserRole->load($roleId);
        if ($rowAdminUserRole->id == 0) Response::end('不存在的分组！');
        $rowAdminUserRole->permission = Request::post('permission', 0, 'int');

        if ($rowAdminUserRole->permission == -1) {
            $publicPermissions = [];
            $adminServiceApp = Be::getService('System.app');
            $apps = $adminServiceApp->getApps();
            foreach ($apps as $app) {
                $appPermissions = $app->getAdminPermissions();
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
            $permissions = implode(',', $permissions);
            $rowAdminUserRole->permissions = $permissions;
        } else {
            $rowAdminUserRole->permissions = '';
        }

        $rowAdminUserRole->save();

        $adminServiceAdminUser = Be::getService('System.AdminUser');
        $adminServiceAdminUser->updateAdminUserRole($roleId);

        systemLog('修改管理员组(' . $rowAdminUserRole->name . ')权限');

        Response::setMessage('修改管理员组权限成功！');
        Response::redirect('./?app=System&controller=AdminUser&task=roles');
    }


    // 后台登陆日志
    public function logs()
    {
        $key = Request::post('key', '');
        $success = Request::post('success', -1, 'int');
        $limit = Request::post('limit', -1, 'int');

        if ($limit == -1) {
            $adminConfigSystem = Be::getConfig('System.admin');
            $limit = $adminConfigSystem->limit;
        }

        $option = array(
            'key' => $key,
            'success' => $success
        );

        $adminServiceAdminUser = Be::getService('System.AdminUser');
        Response::setTitle('登陆日志');

        $pagination = Be::getUi('Pagination');
        $pagination->setLimit($limit);
        $pagination->setTotal($adminServiceAdminUser->getLogCount($option));
        $pagination->setPage(Request::post('page', 1, 'int'));

        $option['offset'] = $pagination->getOffset();
        $option['limit'] = $limit;

        Response::set('pagination', $pagination);
        Response::set('key', $key);
        Response::set('success', $success);
        Response::set('logs', $adminServiceAdminUser->getLogs($option));

        Response::display();
    }

    // 后台登陆日志
    public function ajaxDeleteLogs()
    {
        $adminServiceAdminUser = Be::getService('System.AdminUser');
        $adminServiceAdminUser->deleteLogs();

        systemLog('删除管理员登陆日志');

        Response::set('error', 0);
        Response::set('message', '删除管理员登陆日志成功！');
        Response::ajax();
    }


    public function setting()
    {
        Response::setTitle('管理员系统设置');
        Response::set('configAdminUser', Be::getConfig('System.AdminUser'));
        Response::display();
    }

    public function settingSave()
    {
        $adminConfigAdminUser = Be::getConfig('System.AdminUser');
        $adminConfigAdminUser->avatarSW = Request::post('avatarSW', 0, 'int');
        $adminConfigAdminUser->avatarSH = Request::post('avatarSH', 0, 'int');
        $adminConfigAdminUser->avatarMW = Request::post('avatarMW', 0, 'int');
        $adminConfigAdminUser->avatarMH = Request::post('avatarMH', 0, 'int');
        $adminConfigAdminUser->avatarLW = Request::post('avatarLW', 0, 'int');
        $adminConfigAdminUser->avatarLH = Request::post('avatarLH', 0, 'int');

        // 缩图图大图
        $defaultAvatarL = $_FILES['defaultAvatarL'];
        if ($defaultAvatarL['error'] == 0) {
            $libImage = Be::getLib('image');
            $libImage->open($defaultAvatarL['tmpName']);
            if ($libImage->isImage()) {
                $defaultAvatarLName = date('YmdHis') . 'L.' . $libImage->getType();
                $defaultAvatarLPath = PATH_DATA . '/adminUser/avatar/Default/' .  $defaultAvatarLName;
                if (move_uploaded_file($defaultAvatarL['tmpName'], $defaultAvatarLPath)) {
                    // @unlink(PATH_DATA.'/user/avatar/default/'.$adminConfigAdminUser->defaultAvatarL);
                    $adminConfigAdminUser->defaultAvatarL = $defaultAvatarLName;
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
                $defaultAvatarMPath = PATH_DATA . '/adminUser/avatar/Default/' .  $defaultAvatarMName;
                if (move_uploaded_file($defaultAvatarM['tmpName'], $defaultAvatarMPath)) {
                    // @unlink(PATH_DATA.'/user/avatar/default/'.$adminConfigAdminUser->defaultAvatarM);
                    $adminConfigAdminUser->defaultAvatarM = $defaultAvatarMName;
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
                $defaultAvatarSPath = PATH_DATA . '/adminUser/avatar/Default/' .  $defaultAvatarSName;
                if (move_uploaded_file($defaultAvatarS['tmpName'], $defaultAvatarSPath)) {
                    // @unlink(PATH_DATA.'/user/avatar/default/'.$adminConfigAdminUser->defaultAvatarS);
                    $adminConfigAdminUser->defaultAvatarS = $defaultAvatarSName;
                }
            }
        }

        $serviceSystem = Be::getService('system');
        $serviceSystem->updateConfig($adminConfigAdminUser, PATH_DATA . '/adminConfig/adminUser.php');

        systemLog('设置管理员系统参数');

        Response::setMessage('成功保存管理员系统设置！');
        Response::redirect('./?app=System&controller=AdminUser&task=setting');
    }
}

?>