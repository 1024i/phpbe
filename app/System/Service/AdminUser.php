<?php
namespace App\System\Service;

use Phpbe\System\Be;
use Phpbe\System\Service;
use Phpbe\System\Service\ServiceException;
use Phpbe\System\Session;
use Phpbe\System\Cookie;
use Phpbe\Util\Random;

class AdminUser extends Service
{

    /**
     * 登录
     * @param string $username 用户名
     * @param string $password 密码
     * @param string $ip IP 地址
     * @return \stdClass
     * @throws \Exception
     */
    public function login($username, $password, $ip)
    {
        $username = trim($username);
        if (!$username) {
            throw new ServiceException('参数用户名或邮箱（username）缺失！');
        }

        $password = trim($password);
        if (!$password) {
            throw new ServiceException('参数密码（password）缺失！');
        }

        $ip = trim($ip);
        if (!$ip) {
            throw new ServiceException('参数IP（$ip）缺失！');
        }

        $times = Session::get($ip);
        if (!$times) $times = 0;
        $times++;
        if ($times > 10) {
            throw new ServiceException('登陆失败次数过多，请稍后再试！');
        }
        Session::set($ip, $times);

        $rowAdminUserAdminLog = Be::getRow('System.AdminUserLog');
        $rowAdminUserAdminLog->username = $username;
        $rowAdminUserAdminLog->ip = $ip;
        $rowAdminUserAdminLog->create_time = time();

        $db = Be::getDb();
        $db->beginTransaction();
        try {

            $rowAdminUser = Be::getRow('System.AdminUser');
            $rowAdminUser->load('username', $username);

            if ($rowAdminUser->id == 0) {
                $rowAdminUser->load(['email' => $username]);
            }

            if ($rowAdminUser->id > 0) {

                $password = $this->encryptPassword($password, $rowAdminUser->salt);

                if ($rowAdminUser->password == $password) {
                    if ($rowAdminUser->block == 1) {
                        throw new ServiceException('管理员账号已被停用！');
                    } else {
                        session::delete($ip);
                        Session::set('_admin_user', $rowAdminUser->toObject());

                        $rowAdminUserAdminLog->success = 1;
                        $rowAdminUserAdminLog->description = '登陆成功！';

                        $rememberMeToken = null;
                        do {
                            $rememberMeToken = Random::complex(32);
                        } while (Be::getRow('System.AdminUser')->where('remember_me_token', $rememberMeToken)->count() > 0);

                        $rowAdminUser->last_login_time = time();
                        $rowAdminUser->remember_me_token = $rememberMeToken;
                        $rowAdminUser->save();

                        cookie::setExpire(time() + 30 * 86400);
                        cookie::set('_admin_remember_me', $rememberMeToken);

                    }
                } else {
                    throw new ServiceException('密码错误！');
                }
            } else {
                throw new ServiceException('管理员名不存在！');
            }

            $rowAdminUserAdminLog->save();

            $db->commit();
            return $rowAdminUser;

        } catch (\Exception $e) {
            $db->rollback();

            $rowAdminUserAdminLog->description = $e->getMessage();
            $rowAdminUserAdminLog->save();
            throw $e;
        }
    }

    /**
     * 记住我
     *
     * @throws \Exception
     * @return bool | \Phpbe\System\Db\Row
     */
    public function rememberMe()
    {
        if (cookie::has('_admin_remember_me')) {
            $adminRememberMe = cookie::get('_admin_remember_me', '');
            if ($adminRememberMe) {
                $rowAdminUser = Be::getRow('System.AdminUser');
                $rowAdminUser->load('remember_me_token', $adminRememberMe);
                if ($rowAdminUser->id && $rowAdminUser->block == 0) {
                    Session::set('_admin_user', Be::getAdminUser($rowAdminUser->id));

                    $db = Be::getDb();
                    $db->beginTransaction();
                    try {

                        $rowAdminUser->lastLoginTime = time();
                        $rowAdminUser->save();

                        $db->commit();
                        return $rowAdminUser;

                    } catch (\Exception $e) {
                        $db->rollback();
                        throw $e;
                    }
                }
            }
        }
        return false;
    }

    /**
     * 退出
     *
     * @throws ServiceException
     */
    public function logout()
    {
        session::delete('_admin_user');
        cookie::delete('_admin_remember_me');
    }

    /**
     * 获取指定条件的管理员列表
     *
     * @param array $conditions 查询条件
     * @return array
     */
    public function getUsers($conditions = array())
    {
        $tableAdminUser = Be::getTable('System.AdminUser');
        $tableAdminUser->where($this->createUserWhere($conditions));

        if (isset($conditions['orderByString']) && $conditions['orderByString']) {
            $tableAdminUser->orderBy($conditions['orderByString']);
        } else {
            $orderBy = 'ordering';
            $orderByDir = 'DESC';
            if (isset($conditions['orderBy']) && $conditions['orderBy']) $orderBy = $conditions['orderBy'];
            if (isset($conditions['orderByDir']) && $conditions['orderByDir']) $orderByDir = $conditions['orderByDir'];
            $tableAdminUser->orderBy($orderBy, $orderByDir);
        }

        if (isset($conditions['offset']) && $conditions['offset']) $tableAdminUser->offset($conditions['offset']);
        if (isset($conditions['limit']) && $conditions['limit']) $tableAdminUser->limit($conditions['limit']);

        return $tableAdminUser->getObjects();
    }

    /**
     * 获取指定条件的管理员总数
     *
     * @param array $conditions 查询条件
     * @return int
     */
    public function getUserCount($conditions = array())
    {
        return Be::getTable('System.AdminUser')
            ->where($this->createUserWhere($conditions))
            ->count();
    }

    /**
     * 生成查询条件 where 数组
     *
     * @param array $conditions 查询条件
     * @return array
     */
    private function createUserWhere($conditions = [])
    {
        $where = [];
        if (isset($conditions['key']) && $conditions['key']) {
            $where[] = '(';
            $where[] = ['username', 'like', '%' . $conditions['key'] . '%'];
            $where[] = 'OR';
            $where[] = ['name', 'like', '%' . $conditions['key'] . '%'];
            $where[] = 'OR';
            $where[] = ['email', 'like', '%' . $conditions['key'] . '%'];
            $where[] = ')';
        }

        if (isset($conditions['status']) && is_numeric($conditions['status']) && $conditions['status'] != -1) {
            $where[] = ['block', $conditions['status']];
        }

        if (isset($conditions['roleId']) && is_numeric($conditions['roleId']) && $conditions['roleId'] > 0) {
            $where[] = ['role_id', '>', $conditions['roleId']];
        }

        return $where;
    }

    /**
     * 屏蔽管理员账号
     *
     * @param string $ids 以逗号分隔的多个管理员ID
     * @throws \Exception
     */
    public function unblock($ids)
    {
        $db = Be::getDb();
        $db->beginTransaction();
        try {
            Be::getTable('System.AdminUser')->where('id', 'in', explode(',', $ids))->update(['block' => 0]);
            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    /**
     * 启用管理员账号
     *
     * @param string $ids 以逗号分隔的多个管理员ID
     * @throws \Exception
     */
    public function block($ids)
    {
        $array = explode(',', $ids);
        if (in_array(1, $array)) {
            throw new ServiceException('默认管理员不能屏蔽');
        }

        $my = Be::getAdminUser();
        if (in_array($my->id, $array)) {
            throw new ServiceException('不能屏蔽自已');
        }

        $db = Be::getDb();
        $db->beginTransaction();
        try {
            Be::getTable('System.AdminUser')->where('id', 'in', explode(',', $ids))->update(['block' => 1]);
            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    /**
     * 删除管理员账号
     *
     * @param string $ids 以逗号分隔的多个管理员ID
     * @throws \Exception
     */
    public function delete($ids)
    {
        $array = explode(',', $ids);
        if (in_array(1, $array)) {
            throw new ServiceException('默认管理员不能删除');
        }

        $my = Be::getAdminUser();
        if (in_array($my->id, $array)) {
            throw new ServiceException('不能删除自已');
        }

        $db = Be::getDb();
        $db->beginTransaction();
        try {

            $files = [];

            $array = explode(',', $ids);
            foreach ($array as $id) {

                $rowAdminUser = Be::getRow('System.AdminUser');
                $rowAdminUser->load($id);

                if ($rowAdminUser->avatar_s != '') $files[] = Be::getRuntime()->getPathData() . '/System/AdminUser/Avatar/' . $rowAdminUser->avatar_s;
                if ($rowAdminUser->avatar_m != '') $files[] = Be::getRuntime()->getPathData() . '/System/AdminUser/Avatar/' . $rowAdminUser->avatar_m;
                if ($rowAdminUser->avatar_l != '') $files[] = Be::getRuntime()->getPathData() . '/System/AdminUser/Avatar/' . $rowAdminUser->avatar_l;

                $rowAdminUser->delete();
            }

            $db->commit();

            foreach ($files as $file) {
                if (file_exists($file)) @unlink($file);
            }

        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    /**
     * 初始化管理员头像
     *
     * @param int $userId 管理员ID
     * @throws \Exception
     */
    public function initAvatar($userId)
    {
        $db = Be::getDb();
        $db->beginTransaction();
        try {

            $rowAdminUser = Be::getRow('System.AdminUser');
            $rowAdminUser->load($userId);

            $files = [];
            if ($rowAdminUser->avatar_s != '') $files[] = Be::getRuntime()->getPathData() . '/System/AdminUser/Avatar/' . $rowAdminUser->avatar_s;
            if ($rowAdminUser->avatar_m != '') $files[] = Be::getRuntime()->getPathData() . '/System/AdminUser/Avatar/' . $rowAdminUser->avatar_m;
            if ($rowAdminUser->avatar_l != '') $files[] = Be::getRuntime()->getPathData() . '/System/AdminUser/Avatar/' . $rowAdminUser->avatar_l;

            $rowAdminUser->avatar_s = '';
            $rowAdminUser->avatar_m = '';
            $rowAdminUser->avatar_l = '';

            $rowAdminUser->save();

            $db->commit();

            foreach ($files as $file) {
                if (file_exists($file)) @unlink($file);
            }

        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    /**
     * 检测用户名是否可用
     *
     * @param $username
     * @param int $userId
     * @return bool
     */
    public function isUsernameAvailable($username, $userId = 0)
    {
        $table = Be::getTable('System.AdminUser');
        if ($userId > 0) {
            $table->where('id', '!=', $userId);
        }
        $table->where('username', $username);
        return $table->count() == 0;
    }

    /**
     * 检测邮箱是否可用
     *
     * @param $email
     * @param int $userId
     * @return bool
     */
    public function isEmailAvailable($email, $userId = 0)
    {
        $table = Be::getTable('System.AdminUser');
        if ($userId > 0) {
            $table->where('id', '!=', $userId);
        }
        $table->where('email', $email);
        return $table->count() == 0;
    }

    /**
     * 获取角色列表
     *
     * @return array
     */
    public function getRoles()
    {
        return Be::getTable('System.AdminUserRole')->orderBy('ordering', 'ASC')->getObjects();
    }

    /**
     * 获取符合条件的管理员操作日志列表
     *
     * @param array $conditions 查询条件
     * @return array
     */
    public function getLogs($conditions = array())
    {
        $tableAdminUserLog = Be::getTable('System.AdminUserLog');
        $tableAdminUserLog->where($this->createLogWhere($conditions));

        if (isset($conditions['orderByString']) && $conditions['orderByString']) {
            $tableAdminUserLog->orderBy($conditions['orderByString']);
        } else {
            $orderBy = 'create_time';
            $orderByDir = 'DESC';
            if (isset($conditions['orderBy']) && $conditions['orderBy']) $orderBy = $conditions['orderBy'];
            if (isset($conditions['orderByDir']) && $conditions['orderByDir']) $orderByDir = $conditions['orderByDir'];
            $tableAdminUserLog->orderBy($orderBy, $orderByDir);
        }

        if (isset($conditions['offset']) && $conditions['offset']) $tableAdminUserLog->offset($conditions['offset']);
        if (isset($conditions['limit']) && $conditions['limit']) $tableAdminUserLog->limit($conditions['limit']);

        return $tableAdminUserLog->getObjects();
    }

    /**
     * 获取符合条件的管理员操作日志总数
     *
     * @param array $conditions 查询条件
     * @return int
     */
    public function getLogCount($conditions = array())
    {
        return Be::getTable('System.AdminUserLog')
            ->where($this->createLogWhere($conditions))
            ->count();
    }

    /**
     * 跟据查询条件生成 where
     *
     * @param array $conditions 查询条件
     * @return array
     */
    private function createLogWhere($conditions = array())
    {
        $where = [];
        if (isset($conditions['key']) && $conditions['key']) {
            $where[] = ['username', 'like', '%' . $conditions['key'] . '%'];
        }

        if (isset($conditions['success']) && is_numeric($conditions['success']) && $conditions['success'] != -1) {
            $where[] = ['success', $conditions['success']];
        }

        return $where;
    }

    /**
     * 删除三个月(90天)前的后台管理员登陆日志
     *
     * @throws \Exception
     */
    public function deleteLogs()
    {
        $db = Be::getDb();
        $db->beginTransaction();
        try {
            Be::getTable('System.AdminUserLog')->where('create_time', '<', time() - 90 * 86400)->delete();
            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    /**
     * 管理员密码加密算法
     *
     * @param string $password 密码
     * @param string $salt 盐值
     * @return string
     */
    public function encryptPassword($password, $salt)
    {
        return sha1(sha1($password) . $salt);
    }

    /**
     * 更新所有角色缓存
     */
    public function updateAdminUserRoles()
    {
        $roles = $this->getRoles();

        $service = Be::getService('System.Cache');
        foreach ($roles as $role) {
            $service->updateCacheAdminUserRole($role->id);
        }
    }

    /**
     * 更新指定角色缓存
     *
     * @param int $roleId 角色ID
     */
    public function updateAdminUserRole($roleId)
    {
        $service = Be::getService('System.Cache');
        $service->updateCacheAdminUserRole($roleId);
    }
}
