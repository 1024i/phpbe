<?php
namespace App\System\AdminController;

use Phpbe\System\Be;
use Phpbe\System\Request;
use Phpbe\System\Response;
use Phpbe\System\AdminController;

class AdminUser extends AdminController
{

    // 登陆页面
    public function login()
    {
        if (Request::isPost()) {
            $username = Request::post('username', '');
            $password = Request::post('password', '');

            $ip = Request::ip();
            try {
                $serviceAdminUser = Be::getService('System.AdminUser');
                $serviceAdminUser->login($username, $password, $ip);
             } catch (\Exception $e) {
                Response::error($e->getMessage());
            }

            Response::success('登录成功！');

        } else {

            $my = Be::getAdminUser();
            if ($my->id > 0) {
                Response::redirect('./?app=System&controller=System&action=dashboard');
            }

            Response::setTitle('登录');
            Response::display();
        }
    }


    // 退出登陆
    public function logout()
    {
        Be::getService('System.AdminUser')->logout();
        Response::success('成功退出！', './?app=System&controller=AdminUser&action=login');
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

        $serviceAdminUser = Be::getService('System.AdminUser');

        Response::setTitle('管理员列表');

        $pagination = Be::getUi('Pagination');
        $pagination->setLimit($limit);
        $pagination->setTotal($serviceAdminUser->getUserCount($option));
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

        Response::set('users', $serviceAdminUser->getUsers($option));

        Response::set('roles', $serviceAdminUser->getRoles());
        Response::display();

        $libHistory = Be::getLib('History');
        $libHistory->save();
    }

    // 修改管理员
    public function edit()
    {
        if (Request::isPost()) {
            $id = Request::post('id', 0, 'int');

            if (Request::post('username', '') == '') {
                Response::setMessage('请输入管理员名！', 'error');
                Response::redirect('./?app=System&controller=AdminUser&action=edit&id=' . $id);
            }

            if (Request::post('email', '') == '') {
                Response::setMessage('请输入邮箱！', 'error');
                Response::redirect('./?app=System&controller=AdminUser&action=edit&id=' . $id);
            }

            $password = Request::post('password', '');
            if ($password != Request::post('password2', '')) {
                Response::setMessage('两次输入的密码不匹配！', 'error');
                Response::redirect('./?app=System&controller=AdminUser&action=edit&id=' . $id);
            }

            if ($id == 0 && $password == '') {
                Response::setMessage('密码不能为空！', 'error');
                Response::redirect('./?app=System&controller=AdminUser&action=edit&id=' . $id);
            }

            $rowAdminUser = Be::getRow('adminUser');
            if ($id > 0) $rowAdminUser->load($id);

            $rowAdminUser->bind(Request::post());
            $serviceAdminUser = Be::getService('System.AdminUser');

            if (!$serviceAdminUser->isUsernameAvailable($rowAdminUser->username, $id)) {
                Response::setMessage('管理员名(' . $rowAdminUser->username . ')已被占用！', 'error');
                Response::redirect('./?app=System&controller=AdminUser&action=edit&id=' . $id);
            }

            if (!$serviceAdminUser->isEmailAvailable($rowAdminUser->email, $id)) {
                Response::setMessage('邮箱(' . $rowAdminUser->email . ')已被占用！', 'error');
                Response::redirect('./?app=System&controller=AdminUser&action=edit&id=' . $id);
            }

            if ($password != '') {
                $rowAdminUser->password = $serviceAdminUser->encryptPassword($password);
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
                    $serviceAdminUser->deleteAvatarFile($rowAdminUser);

                    $t = date('YmdHis');

                    $libImage->resize($configUser->avatarLW, $configUser->avatarLH, 'north');
                    $libImage->save(Be::getRuntime()->getPathData() . '/adminUser/avatar/' .  $rowAdminUser->id . '_' . $t . 'L.' . $libImage->getType());
                    $rowAdminUser->avatarL = $rowAdminUser->id . '_' . $t . 'L.' . $libImage->getType();

                    $libImage->resize($configUser->avatarMW, $configUser->avatarMH, 'north');
                    $libImage->save(Be::getRuntime()->getPathData() . '/adminUser/avatar/' .  $rowAdminUser->id . '_' . $t . 'M.' . $libImage->getType());
                    $rowAdminUser->avatarM = $rowAdminUser->id . '_' . $t . 'M.' . $libImage->getType();

                    $libImage->resize($configUser->avatarSW, $configUser->avatarSH, 'north');
                    $libImage->save(Be::getRuntime()->getPathData() . '/adminUser/avatar/' .  $rowAdminUser->id . '_' . $t . 'S.' . $libImage->getType());
                    $rowAdminUser->avatarS = $rowAdminUser->id . '_' . $t . 'S.' . $libImage->getType();

                    $rowAdminUser->save();
                }
            }

            Response::setMessage($id == 0 ? '成功添加新管理员！' : '成功修改管理员资料！');
            systemLog($id == 0 ? ('添加新管理员：' . $rowAdminUser->username) : ('修改管理员(' . $rowAdminUser->username . ')资料'));

            $libHistory = Be::getLib('History');
            $libHistory->back();
        } else {
            $id = Request::request('id', 0, 'int');

            $adminUser = Be::getRow('System.AdminUser');
            if ($id != 0) $adminUser->load($id);

            if ($id != 0)
                Response::setTitle('修改管理员资料');
            else
                Response::setTitle('添加新管理员');

            Response::set('adminUser', $adminUser);

            $serviceAdminUser = Be::getService('System.AdminUser');
            Response::set('roles', $serviceAdminUser->getRoles());

            Response::display();
        }
    }

    public function checkUsername()
    {
        $username = Request::get('username', '');
        echo Be::getService('System.AdminUser')->isUsernameAvailable($username) ? 'true' : 'false';
    }

    public function checkEmail()
    {
        $email = Request::get('email', '');
        echo Be::getService('System.AdminUser')->isEmailAvailable($email) ? 'true' : 'false';
    }

    public function unblock()
    {
        $ids = Request::post('id', '');

        $serviceAdminUser = Be::getService('System.AdminUser');
        if ($serviceAdminUser->unblock($ids)) {
            Response::setMessage('启用管理员账号成功！');
            systemLog('启用管理员账号：#' . $ids);
        } else
            Response::setMessage($serviceAdminUser->getError(), 'error');

        $libHistory = Be::getLib('History');
        $libHistory->back();
    }

    public function block()
    {
        $ids = Request::post('id', '');

        $serviceAdminUser = Be::getService('System.AdminUser');
        if ($serviceAdminUser->block($ids)) {
            Response::setMessage('屏蔽管理员账号成功！');
            systemLog('屏蔽管理员账号：#' . $ids);
        } else
            Response::setMessage($serviceAdminUser->getError(), 'error');

        $libHistory = Be::getLib('History');
        $libHistory->back();
    }

    public function ajaxInitAvatar()
    {
        $userId = Request::get('userId', 0, 'int');

        $serviceAdminUser = Be::getService('System.AdminUser');
        if ($serviceAdminUser->initAvatar($userId)) {
            systemLog('删除 #' . $userId . ' 管理员头像');

            Response::set('error', 0);
            Response::set('message', '删除头像成功！');
        } else {
            Response::set('error', 2);
            Response::set('message', $serviceAdminUser->getError());
        }

        Response::ajax();

    }

    public function delete()
    {
        $ids = Request::post('id', '');

        $serviceAdminUser = Be::getService('System.AdminUser');
        if ($serviceAdminUser->delete($ids)) {
            Response::setMessage('删除管理员账号成功！');
            systemLog('删除管理员账号：#' . $ids);
        } else
            Response::setMessage($serviceAdminUser->getError(), 'error');

        $libHistory = Be::getLib('History');
        $libHistory->back();
    }


    public function roles()
    {
        $serviceAdminUser = Be::getService('System.AdminUser');
        $roles = $serviceAdminUser->getRoles();

        foreach ($roles as $role) {
            $role->userCount = $serviceAdminUser->getUserCount(array('roleId' => $role->id));
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

        $serviceAdminUser = Be::getService('System.AdminUser');
        $serviceAdminUser->updateAdminUserRoles();

        systemLog('修改后台管理员组');

        Response::setMessage('修改后台管理员组成功！');
        Response::redirect('./?app=System&controller=AdminUser&action=roles');
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

        $serviceAdminUser = Be::getService('System.AdminUser');
        $serviceAdminUser->updateAdminUserRole($roleId);

        systemLog('修改管理员组(' . $rowAdminUserRole->name . ')权限');

        Response::setMessage('修改管理员组权限成功！');
        Response::redirect('./?app=System&controller=AdminUser&action=roles');
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

        $serviceAdminUser = Be::getService('System.AdminUser');
        Response::setTitle('登陆日志');

        $pagination = Be::getUi('Pagination');
        $pagination->setLimit($limit);
        $pagination->setTotal($serviceAdminUser->getLogCount($option));
        $pagination->setPage(Request::post('page', 1, 'int'));

        $option['offset'] = $pagination->getOffset();
        $option['limit'] = $limit;

        Response::set('pagination', $pagination);
        Response::set('key', $key);
        Response::set('success', $success);
        Response::set('logs', $serviceAdminUser->getLogs($option));

        Response::display();
    }

    // 后台登陆日志
    public function ajaxDeleteLogs()
    {
        $serviceAdminUser = Be::getService('System.AdminUser');
        $serviceAdminUser->deleteLogs();

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
        $configAdminUser = Be::getConfig('System.AdminUser');
        $configAdminUser->avatarSW = Request::post('avatarSW', 0, 'int');
        $configAdminUser->avatarSH = Request::post('avatarSH', 0, 'int');
        $configAdminUser->avatarMW = Request::post('avatarMW', 0, 'int');
        $configAdminUser->avatarMH = Request::post('avatarMH', 0, 'int');
        $configAdminUser->avatarLW = Request::post('avatarLW', 0, 'int');
        $configAdminUser->avatarLH = Request::post('avatarLH', 0, 'int');

        // 缩图图大图
        $defaultAvatarL = $_FILES['defaultAvatarL'];
        if ($defaultAvatarL['error'] == 0) {
            $libImage = Be::getLib('image');
            $libImage->open($defaultAvatarL['tmpName']);
            if ($libImage->isImage()) {
                $defaultAvatarLName = date('YmdHis') . 'L.' . $libImage->getType();
                $defaultAvatarLPath = Be::getRuntime()->getPathData() . '/adminUser/avatar/Default/' .  $defaultAvatarLName;
                if (move_uploaded_file($defaultAvatarL['tmpName'], $defaultAvatarLPath)) {
                    // @unlink(Be::getRuntime()->getPathData().'/user/avatar/default/'.$configAdminUser->defaultAvatarL);
                    $configAdminUser->defaultAvatarL = $defaultAvatarLName;
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
                $defaultAvatarMPath = Be::getRuntime()->getPathData() . '/adminUser/avatar/Default/' .  $defaultAvatarMName;
                if (move_uploaded_file($defaultAvatarM['tmpName'], $defaultAvatarMPath)) {
                    // @unlink(Be::getRuntime()->getPathData().'/user/avatar/default/'.$configAdminUser->defaultAvatarM);
                    $configAdminUser->defaultAvatarM = $defaultAvatarMName;
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
                $defaultAvatarSPath = Be::getRuntime()->getPathData() . '/adminUser/avatar/Default/' .  $defaultAvatarSName;
                if (move_uploaded_file($defaultAvatarS['tmpName'], $defaultAvatarSPath)) {
                    // @unlink(Be::getRuntime()->getPathData().'/user/avatar/default/'.$configAdminUser->defaultAvatarS);
                    $configAdminUser->defaultAvatarS = $defaultAvatarSName;
                }
            }
        }

        $serviceSystem = Be::getService('system');
        $serviceSystem->updateConfig($configAdminUser, Be::getRuntime()->getPathData() . '/adminConfig/adminUser.php');

        systemLog('设置管理员系统参数');

        Response::setMessage('成功保存管理员系统设置！');
        Response::redirect('./?app=System&controller=AdminUser&action=setting');
    }
}

