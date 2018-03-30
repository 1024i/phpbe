<?php
namespace App\System\Service;

use System\Util\Random;
use System\Util\Validator;
use System\Be;
use System\Cookie;
use System\Session;

class User extends \System\Service
{

    /**
     * 登录
     *
     * @param string $username 用户名
     * @param string $password 密码
     * @param bool $rememberMe 记住我
     * @return \system\row
     * @throws \Exception
     */
    public function login($username, $password, $rememberMe = false)
    {
        $ip = $_SERVER["REMOTE_ADDR"];
        $times = Session::get($ip);
        if (!$times) $times = 0;
        $times++;
        if ($times > 10) {
            throw new \Exception('登陆失败次数过多，请稍后再试！');
        }
        Session::set($ip, $times);

        $rowUser = Be::getRow('System.User');
        $rowUser->load(['username' => $username]);

        if ($rowUser->id > 0) {

            $password = $this->encryptPassword($password, $rowUser->salt);

            if ($rowUser->password == $password) {
                if ($rowUser->block == 1) {
                    throw new \Exception('用户账号已被停用！');
                }

                session::delete($ip);
                Session::set('_user', $rowUser->toObject());

                $rowUser->lastLoginTime = time();
                $rowUser->save();

                if ($rememberMe) {
                    $configUser = Be::getConfig('System.User');
                    $rememberMe = $username . '|||' . $this->encryptPassword($password, $rowUser->salt);
                    $rememberMe = $this->rc4($rememberMe, $configUser->rememberMeKey);
                    $rememberMe = base64_encode($rememberMe);
                    cookie::setExpire(time() + 30 * 86400);
                    cookie::set('_remember_me', $rememberMe);
                }
                return $rowUser;
            } else {
                throw new \Exception('密码错误！');
            }
        } else {
            throw new \Exception('用户名不存在！');
        }
    }

    /**
     * 记住我 自动登录
     *
     * @return \Object | false
     */
    public function rememberMe()
    {
        if (cookie::has('_remember_me')) {
            $rememberMe = cookie::get('_remember_me', '');
            if ($rememberMe) {
                $configUser = Be::getConfig('System.User');
                $rememberMe = base64_decode($rememberMe);
                $rememberMe = $this->rc4($rememberMe, $configUser->rememberMeKey);
                $rememberMe = explode('|||', $rememberMe);
                if (count($rememberMe) == 2) {
                    $username = $rememberMe[0];
                    $password = $rememberMe[0];

                    $rowUser = Be::getRow('System.User');
                    $rowUser->load(['username' => $username]);

                    if ($rowUser->id > 0 && $this->encryptPassword($rowUser->password, $rowUser->salt) == $password && $rowUser->block == 0) {
                        $rowUser->lastLoginTime = time();
                        $rowUser->save();

                        $user = $rowUser->toObject();
                        Session::set('_user', $user);
                        return $user;
                    }
                }
            }
        }

        return false;
    }

    /**
     * 退出
     *
     */
    public function logout()
    {
        session::delete('_user');
        cookie::delete('_remember_me');
    }

    /**
     * 注册
     *
     * @param string $username 用户名
     * @param string $email 邮箱
     * @param string $password 密码
     * @param string $name 名称
     * @return \System\Row
     * @throws \Exception
     *
     */
    public function register($username, $email, $password, $name = '')
    {
        $rowUser = Be::getRow('System.User');
        $rowUser->load(['username' => $username]);

        if ($rowUser->id > 0) {
            throw new \Exception('用户名(' . $username . ')已被占用！');
        }

        $rowUser = Be::getRow('System.User');
        $rowUser->load(['email' => $email]);

        if ($rowUser->id > 0) {
            throw new \Exception('邮箱(' . $email . ')已被占用！');
        }

        if ($name == '') $name = $username;

        $t = time();

        $configUser = Be::getConfig('System.User');

        $salt = random::complex(32);

        $rowUser = Be::getRow('System.User');
        $rowUser->username = $username;
        $rowUser->email = $email;
        $rowUser->name = $name;
        $rowUser->password = $this->encryptPassword($password, $salt);
        $rowUser->salt = $salt;
        $rowUser->token = random::complex(32);
        $rowUser->register_time = $t;
        $rowUser->last_login_time = $t;
        $rowUser->block = ($configUser->emailValid == '1' ? 1 : 0);
        $rowUser->save();

        $configSystem = Be::getConfig('System.System');

        $configUser = Be::getConfig('System.User');
        if ($configUser->emailValid == '1') {
            $activationUrl = url('app=System&controller=User&task=forgetPasswordReset&userId=' . $rowUser->id . '&token=' . $rowUser->token);

            $data = array(
                'siteName' => $configSystem->siteName,
                'username' => $rowUser->username,
                'password' => $password,
                'name' => $rowUser->name,
                'activationUrl' => $activationUrl
            );

            $libMail = Be::getLib('Mail');

            $subject = $libMail->format($configUser->registerMailActivationSubject, $data);
            $body = $libMail->format($configUser->registerMailActivationBody, $data);

            $libMail->setSubject($subject);
            $libMail->setBody($body);
            $libMail->to($rowUser->email, $rowUser->name);
            $libMail->send();
        } else {
            if ($configUser->emailRegister == '1') {
                $data = array(
                    'siteName' => $configSystem->siteName,
                    'username' => $rowUser->username,
                    'name' => $rowUser->name
                );

                $libMail = Be::getLib('Mail');

                $subject = $libMail->format($configUser->registerMailSubject, $data);
                $body = $libMail->format($configUser->registerMailBody, $data);

                $libMail->setSubject($subject);
                $libMail->setBody($body);
                $libMail->to($rowUser->email, $rowUser->name);
                $libMail->send();
            }
        }

        if ($configUser->emailRegisterAdmin != '') {
            if (validator::isEmail($configUser->emailRegisterAdmin)) {
                $data = array(
                    'siteName' => $configSystem->siteName,
                    'username' => $rowUser->username,
                    'email' => $email,
                    'name' => $rowUser->name
                );

                $libMail = Be::getLib('Mail');

                $subject = $libMail->format($configUser->registerMailToAdminSubject, $data);
                $body = $libMail->format($configUser->registerMailToAdminBody, $data);

                $libMail->setSubject($subject);
                $libMail->setBody($body);
                $libMail->to($configUser->emailRegisterAdmin);
                $libMail->send();
            }
        }

        return $rowUser;
    }

    /**
     * 忘记密码
     * 向用户邮箱发送一封重置密码的邮件
     *
     * @param string $username 用户名
     * @throws \Exception
     */
    public function forgotPassword($username)
    {
        if ($username == '') {
            throw new \Exception('用户名不能为空！');
        }

        $rowUser = Be::getRow('System.User');
        $rowUser->load('username', $username);

        if ($rowUser->id == 0) {
            throw new \Exception('账号不存在！');
        }

        if ($rowUser->id == 1) {
            throw new \Exception('超级管理禁止使用该功能！');
        }

        $rowUser->token = random::complex(32);
        $rowUser->save();

        $configSystem = Be::getConfig('System.System');

        $activationUrl = url('app=System&controller=User&task=forgotPasswordReset&userId=' . $rowUser->id . '&token=' . $rowUser->token);

        $data = array(
            'siteName' => $configSystem->siteName,
            'activationUrl' => $activationUrl
        );
        $configUser = Be::getConfig('System.User');

        $libMail = Be::getLib('Mail');

        $subject = $libMail->format($configUser->forgotPasswordMailSubject, $data);
        $body = $libMail->format($configUser->forgotPasswordMailBody, $data);

        $libMail->setSubject($subject);
        $libMail->setBody($body);
        $libMail->to($rowUser->email, $rowUser->name);
        $libMail->send();
    }

    /**
     * 忘记密码重置
     *
     * @param int $userId 用户ID
     * @param string $token 邮件发送的 token
     * @param string $password 新密码
     * @throws \Exception
     */
    public function forgotPasswordReset($userId, $token, $password)
    {
        $rowUser = Be::getRow('System.User');
        $rowUser->load($userId);

        if ($rowUser->token != $token) {
            if ($rowUser->token == '')
                throw new \Exception('您的密码已重设！');
            else
                throw new \Exception('重设密码链接已失效！');
        }
        $salt = random::complex(32);
        $rowUser->password = $this->encryptPassword($password, $salt);
        $rowUser->salt = $salt;
        $rowUser->token = '';
        $rowUser->save();

        $configSystem = Be::getConfig('System.System');

        $data = array(
            'siteName' => $configSystem->siteName,
            'siteUrl' => URL_ROOT
        );

        $configUser = Be::getConfig('System.User');

        $libMail = Be::getLib('Mail');

        $subject = $libMail->format($configUser->forgotPasswordResetMailSubject, $data);
        $body = $libMail->format($configUser->forgotPasswordResetMailBody, $data);

        $libMail->setSubject($subject);
        $libMail->setBody($body);
        $libMail->to($rowUser->email, $rowUser->name);
        $libMail->send();
    }


    /**
     * 密码 Hash
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

        if ($level > 1) {                                                                                              //非线性加密
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
     * 获取指定条件的用户列表
     *
     * @param array $conditions 查询条件
     * @return array
     */
    public function getUsers($conditions = [])
    {
        $tableUser = Be::getTable('System.User');

        $where = $this->createUserWhere($conditions);
        $tableUser->where($where);

        if (isset($conditions['orderByString']) && $conditions['orderByString']) {
            $tableUser->orderBy($conditions['orderByString']);
        } else {
            $orderBy = 'id';
            $orderByDir = 'DESC';
            if (isset($conditions['orderBy']) && $conditions['orderBy']) $orderBy = $conditions['orderBy'];
            if (isset($conditions['orderByDir']) && $conditions['orderByDir']) $orderByDir = $conditions['orderByDir'];
            $tableUser->orderBy($orderBy, $orderByDir);
        }

        if (isset($conditions['offset']) && $conditions['offset']) $tableUser->offset($conditions['offset']);
        if (isset($conditions['limit']) && $conditions['limit']) $tableUser->limit($conditions['limit']);

        return $tableUser->getObjects();
    }

    /**
     * 获取指定条件的用户总数
     *
     * @param array $conditions 查询条件
     * @return int
     */
    public function getUserCount($conditions = [])
    {
        return Be::getTable('System.User')
            ->where($this->createUserWhere($conditions))
            ->count();
    }

    /**
     * 生成查询条件 where
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
            $where[] = ['role_id', $conditions['roleId']];
        }

        return $where;
    }

    /**
     * 屏蔽用户账号
     *
     * @param string $ids 以逗号分隔的多个用户ID
     * @throws \Exception
     */
    public function unblock($ids)
    {
        $db = Be::getDb();
        try {
            $db->beginTransaction();

            $table = Be::getTable('System.User');
            if (!$table->where('id', 'in', explode(',', $ids))
                ->update(['block' => 0])
            ) {
                throw new \Exception($table->getError());
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    /**
     * 启用用户账号
     *
     * @param string $ids 以逗号分隔的多个用户ID
     * @throws \Exception
     */
    public function block($ids)
    {
        $db = Be::getDb();
        try {
            $db->beginTransaction();

            $table = Be::getTable('System.User');
            if (!$table->where('id', 'in', explode(',', $ids))
                ->update(['block' => 1])
            ) {
                throw new \Exception($table->getError());
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    /**
     * 删除用户账号
     *
     * @param string $ids 以逗号分隔的多个用户ID
     * @throws \Exception
     */
    public function delete($ids)
    {
        $db = Be::getDb();
        try {
            $db->beginTransaction();

            $files = [];

            $array = explode(',', $ids);
            foreach ($array as $id) {

                $rowUser = Be::getRow('System.User');
                $rowUser->load($id);

                if ($rowUser->avatar_s != '') $files[] = PATH_DATA . '/System/User/Avatar/' .  $rowUser->avatar_s;
                if ($rowUser->avatar_m != '') $files[] = PATH_DATA . '/System/User/Avatar/' .  $rowUser->avatar_m;
                if ($rowUser->avatar_l != '') $files[] = PATH_DATA . '/System/User/Avatar/' .  $rowUser->avatar_l;

                if (!$rowUser->delete()) {
                    throw new \Exception($rowUser->getError());
                }
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
     * 初始化用户头像
     *
     * @param int $userId 用户ID
     * @throws \Exception
     */
    public function initAvatar($userId)
    {
        $db = Be::getDb();
        try {
            $db->beginTransaction();

            $rowUser = Be::getRow('System.User');
            $rowUser->load($userId);

            $files = [];
            if ($rowUser->avatar_s != '') $files[] = PATH_DATA . '/System/User/Avatar/' .  $rowUser->avatar_s;
            if ($rowUser->avatar_m != '') $files[] = PATH_DATA . '/System/User/Avatar/' .  $rowUser->avatar_m;
            if ($rowUser->avatar_l != '') $files[] = PATH_DATA . '/System/User/Avatar/' .  $rowUser->avatar_l;

            $rowUser->avatar_s = '';
            $rowUser->avatar_m = '';
            $rowUser->avatar_l = '';

            if (!$rowUser->save()) {
                throw new \Exception($rowUser->getError());
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
     * 检测用户名是否可用
     *
     * @param $username
     * @param int $userId
     * @return bool
     */
    public function isUsernameAvailable($username, $userId = 0)
    {
        $table = Be::getTable('System.User');
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
        $table = Be::getTable('System.User');
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
        return Be::getTable('System.UserRole')->orderBy('ordering', 'asc')->getObjects();
    }

    /**
     * 更新所有角色缓存
     */
    public function updateUserRoles()
    {
        $roles = $this->getRoles();
        $service = Be::getService('System.Cache');
        foreach ($roles as $role) {
            $service->updateCacheUserRole($role->id);
        }
    }

    /**
     * 更新指定角色缓存
     *
     * @param int $roleId 角色ID
     */
    public function updateUserRole($roleId)
    {
        $service = Be::getService('System.Cache');
        $service->updateCacheUserRole($roleId);
    }
}
