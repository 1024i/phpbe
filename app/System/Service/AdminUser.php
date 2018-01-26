<?php
namespace App\System\Service;

use System\Be;
use System\Session;
use System\Cookie;

class AdminUser extends \System\Service
{

    /**
     * 登录
     * @param string $username 用户名
     * @param string $password 密码
     * @return bool|\stdClass
     */
    public function login($username, $password)
    {
        $ip = $_SERVER["REMOTE_ADDR"];
        $times = Session::get($ip);
        if (!$times) $times = 0;
        $times++;
        if ($times > 100) {
            $this->setError('登陆失败次数过多，请稍后再试！');
            return false;
        }
        Session::set($ip, $times);

        $rowAdminUserAdminLog = Be::getRow('System.AdminUserLog');
        $rowAdminUserAdminLog->username = $username;
        $rowAdminUserAdminLog->ip = $ip;
        $rowAdminUserAdminLog->create_time = time();

        $rowAdminUser = Be::getRow('adminUser');
        $rowAdminUser->load('username', $username);

        $result = false;
        if ($rowAdminUser->id) {
            if ($rowAdminUser->password == $this->encryptPassword($password)) {
                if ($rowAdminUser->block == 1) {
                    $rowAdminUserAdminLog->description = '管理员账号已被停用！';
                    $this->setError('管理员账号已被停用！');
                } else {
                    session::delete($ip);
                    $beAdminUser = Be::getAdminUser($rowAdminUser->id);

                    Session::set('AdminUser', $beAdminUser);

                    $rowAdminUserAdminLog->success = 1;
                    $rowAdminUserAdminLog->description = '登陆成功！';

                    $rowAdminUser->last_login_time = time();
                    $rowAdminUser->save();

                    $adminConfigAdminUser = Be::getConfig('System.AdminUser');
                    $rememberMeAdmin = $username . '|||' . $this->encryptPassword($rowAdminUser->password);
                    $rememberMeAdmin = $this->rc4($rememberMeAdmin, $adminConfigAdminUser->remember_me_key);
                    $rememberMeAdmin = base64_encode($rememberMeAdmin);
                    cookie::setExpire(time() + 30 * 86400);
                    cookie::set('_remember_me_admin', $rememberMeAdmin);
                    $result = $beAdminUser;
                }
            } else {
                $rowAdminUserAdminLog->description = '密码错误！';
                $this->setError('密码错误！');
            }
        } else {
            $rowAdminUserAdminLog->description = '管理员名不存在！';
            $this->setError('管理员名不存在！');
        }
        $rowAdminUserAdminLog->save();
        return $result;
    }

    /**
     * 记住我
     *
     * @return bool|mixed|\system\row
     */
    public function rememberMe()
    {
        if (cookie::has('_remember_me_admin')) {
            $rememberMeAdmin = cookie::get('_remember_me_admin', '');
            if ($rememberMeAdmin) {
                $adminConfigAdminUser = Be::getConfig('System.AdminUser');
                $rememberMeAdmin = base64_decode($rememberMeAdmin);
                $rememberMeAdmin = $this->rc4($rememberMeAdmin, $adminConfigAdminUser->rememberMeKey);
                $rememberMeAdmin = explode('|||', $rememberMeAdmin);
                if (count($rememberMeAdmin) == 2) {
                    $username = $rememberMeAdmin[0];
                    $password = $rememberMeAdmin[0];

                    $rowAdminUser = Be::getRow('adminUser');
                    $rowAdminUser->load('username', $username);

                    if ($rowAdminUser->id && $this->encryptPassword($rowAdminUser->password) == $password && $rowAdminUser->block == 0) {
                        Session::set('AdminUser', Be::getAdminUser($rowAdminUser->id));

                        $rowAdminUser->lastLoginTime = time();
                        $rowAdminUser->save();
                        return $rowAdminUser;
                    }
                }
            }
        }
        return false;
    }

    /**
     * 退出
     *
     * @return bool
     */
    public function logout()
    {
        session::delete('_admin_user');
        cookie::delete('_remember_me_admin');
        return true;
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
     * @return bool
     */
    public function unblock($ids)
    {
        $db = Be::getDb();
        try {
            $db->beginTransaction();

            $table = Be::getTable('System.AdminUser');
            if (!$table->where('id', 'in', explode(',', $ids))
                ->update(['block' => 0])
            ) {
                throw new \Exception($table->getError());
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();

            $this->setError($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 启用管理员账号
     *
     * @param string $ids 以逗号分隔的多个管理员ID
     * @return bool
     */
    public function block($ids)
    {
        $array = explode(',', $ids);
        if (in_array(1, $array)) {
            $this->setError('默认管理员不能屏蔽');
            return false;
        }

        $my = Be::getAdminUser();
        if (in_array($my->id, $array)) {
            $this->setError('不能屏蔽自已');
            return false;
        }

        $db = Be::getDb();
        try {
            $db->beginTransaction();

            $table = Be::getTable('System.AdminUser');
            if (!$table->where('id', 'in', $array)
                ->update(['block' => 1])
            ) {
                throw new \Exception($table->getError());
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();

            $this->setError($e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * 删除管理员账号
     *
     * @param string $ids 以逗号分隔的多个管理员ID
     * @return bool
     */
    public function delete($ids)
    {
        $array = explode(',', $ids);
        if (in_array(1, $array)) {
            $this->setError('默认管理员不能删除');
            return false;
        }

        $my = Be::getAdminUser();
        if (in_array($my->id, $array)) {
            $this->setError('不能删除自已');
            return false;
        }

        $db = Be::getDb();
        try {
            $db->beginTransaction();

            $files = [];

            $array = explode(',', $ids);
            foreach ($array as $id) {

                $rowAdminUser = Be::getRow('System.AdminUser');
                $rowAdminUser->load($id);

                if ($rowAdminUser->avatar_s != '') $files[] = PATH_CACHE . '/System/AdminUser/Avatar/' .  $rowAdminUser->avatar_s;
                if ($rowAdminUser->avatar_m != '') $files[] = PATH_CACHE . '/System/AdminUser/Avatar/' .  $rowAdminUser->avatar_m;
                if ($rowAdminUser->avatar_l != '') $files[] = PATH_CACHE . '/System/AdminUser/Avatar/' .  $rowAdminUser->avatar_l;

                if (!$rowAdminUser->delete()) {
                    throw new \Exception($rowAdminUser->getError());
                }
            }

            $db->commit();

            foreach ($files as $file) {
                if (file_exists($file)) @unlink($file);
            }

        } catch (\Exception $e) {
            $db->rollback();

            $this->setError($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 初始化管理员头像
     *
     * @param int $userId 管理员ID
     * @return bool
     */
    public function initAvatar($userId)
    {
        $db = Be::getDb();
        try {
            $db->beginTransaction();

            $rowAdminUser = Be::getRow('System.AdminUser');
            $rowAdminUser->load($userId);

            $files = [];
            if ($rowAdminUser->avatar_s != '') $files[] = PATH_DATA . '/System/AdminUser/Avatar/' .  $rowAdminUser->avatar_s;
            if ($rowAdminUser->avatar_m != '') $files[] = PATH_DATA . '/System/AdminUser/Avatar/' .  $rowAdminUser->avatar_m;
            if ($rowAdminUser->avatar_l != '') $files[] = PATH_DATA . '/System/AdminUser/Avatar/' .  $rowAdminUser->avatar_l;

            $rowAdminUser->avatar_s = '';
            $rowAdminUser->avatar_m = '';
            $rowAdminUser->avatar_l = '';

            if (!$rowAdminUser->save()) {
                throw new \Exception($rowAdminUser->getError());
            }

            $db->commit();

            foreach ($files as $file) {
                if (file_exists($file)) @unlink($file);
            }

        } catch (\Exception $e) {
            $db->rollback();

            $this->setError($e->getMessage());
            return false;
        }

        return true;
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
     * @return bool
     */
    public function deleteLogs()
    {
        $table = Be::getTable('System.AdminUserLog');
        if (!$table->where('create_time', '<', time() - 90 * 86400)
            ->delete()
        ) {
            $this->setError($table->getError());
            return false;
        }
        return true;
    }

    /**
     * 管理员密码加密算法
     *
     * @param string $password 密码
     * @return string
     */
    public function encryptPassword($password)
    {
        // return md5($password.md5('BE'));
        return md5($password . 'd3dcf429c679f9af82eb9a3b31c4df44');
    }

    /**
     * 文本加密与解密
     *
     * @param string $txt 需要加解密的文本
     * @param string $pwd 加密解密文本密码
     * @param int $level 加密级别 1=简单线性加密, >1 = RC4加密数字越大越安全越慢, 默认=256
     *
     * @return string 加密或解密后的明码字符串
     */
    public function rc4($txt, $pwd, $level = 256)
    {
        $result = '';
        $kL = strlen($pwd);
        $tL = strlen($txt);

        $key = array();
        $box = array();

        if ($level > 1) {                                                                                               //非线性加密
            for ($i = 0; $i < $level; ++$i) {
                $key[$i] = ord($pwd[$i % $kL]);
                $box[$i] = $i;
            }

            for ($j = $i = 0; $i < $level; ++$i) {
                $j = ($j + $box[$i] + $key[$i]) % $level;
                $tmp = $box[$i];
                $box[$i] = $box[$j];
                $box[$j] = $tmp;
            }

            for ($a = $j = $i = 0; $i < $tL; ++$i) {
                $a = ($a + 1) % $level;
                $j = ($j + $box[$a]) % $level;

                $tmp = $box[$a];
                $box[$a] = $box[$j];
                $box[$j] = $tmp;

                $k = $box[($box[$a] + $box[$j]) % $level];
                $result .= chr(ord($txt[$i]) ^ $k);
            }
        } else {                                                                                                        //简单线性加密
            for ($i = 0; $i < $tL; ++$i) {
                $result .= $txt[$i] ^ $pwd[$i % $kL];
            }
        }
        return $result;
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
