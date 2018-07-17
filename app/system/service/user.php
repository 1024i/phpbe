<?php

namespace App\System\Service;

use Phpbe\System\Service\ServiceException;
use Phpbe\Util\Random;
use Phpbe\Util\Validator;
use Phpbe\System\Be;
use Phpbe\System\Cookie;
use Phpbe\System\Session;
use PHPMailer\PHPMailer\Exception;

class User extends \Phpbe\System\Service
{

    /**
     * 登录
     *
     * @param string $username 用户名
     * @param string $password 密码
     * @param string $ip IP 地址
     * @param bool $rememberMe 记住我
     * @return \Phpbe\System\Db\Row
     * @throws \Exception
     */
    public function login($username, $password, $ip, $rememberMe = false)
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

        $rowUser = Be::getRow('System', 'User');
        $rowUser->load(['username' => $username]);

        if ($rowUser->id == 0) {
            $rowUser->load(['email' => $username]);
        }

        if ($rowUser->id > 0) {

            $password = $this->encryptPassword($password, $rowUser->salt);

            if ($rowUser->password == $password) {
                if ($rowUser->block == 1) {
                    throw new ServiceException('用户账号已被停用！');
                }

                session::delete($ip);
                Session::set('_user', $rowUser->toObject());

                if ($rememberMe) {

                    $rememberMeToken = null;
                    do {
                        $rememberMeToken = Random::complex(32);
                    } while (Be::getRow('System', 'User')->where('remember_me_token', $rememberMeToken)->count() > 0);

                    $rowUser->remember_me_token = $rememberMeToken;

                    cookie::setExpire(time() + 30 * 86400);
                    cookie::set('_remember_me', $rememberMeToken);
                }

                $rowUser->last_login_time = time();
                $rowUser->save();

                return $rowUser;
            } else {
                throw new ServiceException('密码错误！');
            }
        } else {
            throw new ServiceException('用户名或邮箱为 ' . $username . ' 的用户不存在！');
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
                $rowUser = Be::getRow('System', 'User');
                $rowUser->load('remember_me_token', $rememberMe);

                if ($rowUser->id > 0 && $rowUser->block == 0) {
                    $rowUser->last_login_time = time();
                    $rowUser->save();

                    $user = $rowUser->toObject();
                    Session::set('_user', $user);
                    return $user;
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
     * @param array $data 用户数据
     * @return \Phpbe\System\Db\Row
     * @throws \Exception
     *
     */
    public function register($data = [])
    {
        if (!isset($data['username']) || !$data['username']) {
            throw new ServiceException('参数用户名（username）缺失！');
        }
        $username = trim($data['username']);

        if (!isset($data['email']) || !$data['email']) {
            throw new ServiceException('参数邮箱（email）缺失！');
        }
        $email = trim($data['email']);

        if (!Validator::isEmail($email)) {
            throw new ServiceException('邮箱（' . $email . '）不是合法的邮箱格式！');
        }

        if (!isset($data['password']) || !$data['password']) {
            throw new ServiceException('参数密码（password）缺失！');
        }
        $password = trim($data['password']);

        $name = null;
        if (isset($data['name']) && $data['name']) {
            $name = $data['name'];
        } else {
            $name = $username;
        }

        $rowUser = Be::getRow('System', 'User');
        $rowUser->load(['username' => $username]);

        if ($rowUser->id > 0) {
            throw new ServiceException('用户名(' . $username . ')已被占用！');
        }

        $rowUser->load(['email' => $email]);
        if ($rowUser->id > 0) {
            throw new ServiceException('邮箱(' . $email . ')已被占用！');
        }

        $t = time();

        $configUser = Be::getConfig('System', 'User');

        $salt = Random::complex(32);

        $rowUser = Be::getRow('System', 'User');
        $rowUser->username = $username;
        $rowUser->email = $email;
        $rowUser->name = $name;
        $rowUser->password = $this->encryptPassword($password, $salt);
        $rowUser->salt = $salt;
        $rowUser->token = Random::complex(32);
        $rowUser->register_time = $t;
        $rowUser->last_login_time = $t;
        $rowUser->block = ($configUser->emailValid == '1' ? 1 : 0);
        $rowUser->save();

        $configSystem = Be::getConfig('System', 'System');

        $configUser = Be::getConfig('System', 'User');
        if ($configUser->emailValid == '1') {
            $activationUrl = url('app=System&controller=User&action=activate&userId=' . $rowUser->id . '&token=' . $rowUser->token);

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
            if (Validator::isEmail($configUser->emailRegisterAdmin)) {
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
     * 激活
     *
     * @param int $userId 用户ID
     * @param string $token 邮件发送的 token
     * @throws \Exception
     */
    public function activate($userId, $token)
    {
        if (!$userId) {
            throw new ServiceException('参数用户ID（userId）缺失！');
        }

        $token = trim($token);
        if (!$token) {
            throw new ServiceException('参数Token（token）缺失！');
        }

        $rowUser = Be::getRow('System', 'User');
        $rowUser->load($userId);

        if ($rowUser->id == 0) {
            throw new ServiceException('账号（#' . $userId . '）不存在！');
        }

        if ($rowUser->token != $token) {
            if ($rowUser->token == '')
                throw new ServiceException('您的账号已激活！');
            else
                throw new ServiceException('您的账号激活链接已失效！');
        }

        $rowUser->token = '';
        $rowUser->block = 0;
        $rowUser->save();
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
        if (!$username) {
            throw new ServiceException('用户名不能为空！');
        }

        $rowUser = Be::getRow('System', 'User');
        $rowUser->load('username', $username);

        if ($rowUser->id == 0) {
            $rowUser->load('email', $username);
        }

        if ($rowUser->id == 0) {
            throw new ServiceException('账号不存在！');
        }

        $rowUser->token = Random::complex(32);
        $rowUser->save();

        $configSystem = Be::getConfig('System', 'System');

        $resetPasswordUrl = url('app=System&controller=User&action=forgotPasswordReset&userId=' . $rowUser->id . '&token=' . $rowUser->token);

        $data = array(
            'siteName' => $configSystem->siteName,
            'resetPasswordUrl' => $resetPasswordUrl
        );
        $configUser = Be::getConfig('System', 'User');

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
        if (!$userId) {
            throw new ServiceException('参数用户ID（userId）缺失！');
        }

        $token = trim($token);
        if (!$token) {
            throw new ServiceException('参数Token（token）缺失！');
        }

        $password = trim($password);
        if (!$password) {
            throw new ServiceException('参数密码（password）缺失！');
        }

        $rowUser = Be::getRow('System', 'User');
        $rowUser->load($userId);

        if ($rowUser->token != $token) {
            if ($rowUser->token == '')
                throw new ServiceException('您的密码已重设！');
            else
                throw new ServiceException('重设密码链接已失效！');
        }
        $salt = Random::complex(32);
        $rowUser->password = $this->encryptPassword($password, $salt);
        $rowUser->salt = $salt;
        $rowUser->token = '';
        $rowUser->save();

        $configSystem = Be::getConfig('System', 'System');

        $data = array(
            'siteName' => $configSystem->siteName,
            'siteUrl' => Be::getRuntime()->getUrlRoot()
        );

        $configUser = Be::getConfig('System', 'User');

        $libMail = Be::getLib('Mail');

        $subject = $libMail->format($configUser->forgotPasswordResetMailSubject, $data);
        $body = $libMail->format($configUser->forgotPasswordResetMailBody, $data);

        $libMail->setSubject($subject);
        $libMail->setBody($body);
        $libMail->to($rowUser->email, $rowUser->name);
        $libMail->send();
    }

    /**
     * 修改用户密码
     *
     * @param int $userId 用户ID
     * @param string $password 当前密码
     * @param string $newPassword 新密码
     * @throws \Exception
     */
    public function changePassword($userId, $password, $newPassword)
    {
        $userId = intval($userId);
        if (!$userId) {
            throw new ServiceException('参数用户ID（userId）缺失！');
        }

        $password = trim($password);
        if (!$password) {
            throw new ServiceException('参数当前密码（password）缺失！');
        }

        $newPassword = trim($newPassword);
        if (!$newPassword) {
            throw new ServiceException('参数新密码（newPassword）缺失！');
        }

        $rowUser = Be::getRow('System', 'User');
        $rowUser->load($userId);

        if ($this->encryptPassword($password, $rowUser->salt) != $rowUser->password) {
            throw new Exception('当前密码错误！');
        }

        $newSalt = Random::complex(32);
        $rowUser->password = $this->encryptPassword($newPassword, $newSalt);
        $rowUser->salt = $newSalt;
        $rowUser->save();
    }

    public function edit($userId, $data = [])
    {
        $rowUser = Be::getRow('System', 'User');
        $rowUser->load($userId);
        $rowUser->bind($data);
        $rowUser->save();
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
     * 获取指定条件的用户列表
     *
     * @param array $conditions 查询条件
     * @return array
     */
    public function getUsers($conditions = [])
    {
        $tableUser = Be::getTable('System', 'User');

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
        return Be::getTable('System', 'User')
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
        $db->beginTransaction();

        try {
            Be::getTable('System', 'User')->where('id', 'in', explode(',', $ids))->update(['block' => 0]);
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
        $db->beginTransaction();
        try {
            Be::getTable('System', 'User')->where('id', 'in', explode(',', $ids))->update(['block' => 1]);
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
        $db->beginTransaction();
        try {

            $files = [];

            $array = explode(',', $ids);
            foreach ($array as $id) {

                $rowUser = Be::getRow('System', 'User');
                $rowUser->load($id);

                if ($rowUser->avatar_s != '') $files[] = Be::getRuntime()->getPathData() . '/System/User/Avatar/' . $rowUser->avatar_s;
                if ($rowUser->avatar_m != '') $files[] = Be::getRuntime()->getPathData() . '/System/User/Avatar/' . $rowUser->avatar_m;
                if ($rowUser->avatar_l != '') $files[] = Be::getRuntime()->getPathData() . '/System/User/Avatar/' . $rowUser->avatar_l;

                $rowUser->delete();
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

    public function uploadAvatar($userId, $avatarFile)
    {
        $configSystem = Be::getConfig('System', 'System');

        if ($avatarFile['error'] == 0) {
            $name = strtolower($avatarFile['name']);
            $type = '';
            $pos = strrpos($name, '.');
            if ($pos !== false) {
                $type = substr($name, $pos + 1);
            }
            if (!in_array($type, $configSystem->allowUploadImageTypes)) {
                throw new ServiceException('您上传的不是合法的图像文件！');
            } else {
                $libImage = Be::getLib('image');
                $libImage->open($avatarFile['tmp_name']);
                if (!$libImage->isImage()) {
                    throw new ServiceException('您上传的不是合法的图像文件！');
                } else {
                    $rowUser = Be::getRow('System', 'User');
                    $rowUser->load($userId);

                    $configUser = Be::getConfig('System', 'User');

                    $avatarDir = Be::getRuntime()->getPathData() . '/system/user/avatar/';
                    if (!file_exists($avatarDir)) {
                        mkdir($avatarDir, 0777, true);
                    }

                    // 删除旧头像
                    if ($rowUser->avatar_s != '') @unlink($avatarDir . $rowUser->avatar_s);
                    if ($rowUser->avatar_m != '') @unlink($avatarDir . $rowUser->avatar_m);
                    if ($rowUser->avatar_l != '') @unlink($avatarDir . $rowUser->avatar_l);

                    $t = date('YmdHis');

                    $imageType = $libImage->getType();

                    // 按配置文件里的尺寸大小生成新头像
                    $libImage->resize($configUser->avatarLW, $configUser->avatarLH, 'north');
                    $libImage->save($avatarDir . $userId . '_' . $t . '_l.' . $imageType);
                    $rowUser->avatar_l = $userId . '_' . $t . '_l.' . $imageType;

                    $libImage->resize($configUser->avatarMW, $configUser->avatarMH, 'north');
                    $libImage->save($avatarDir . $userId . '_' . $t . '_m.' . $imageType);
                    $rowUser->avatar_m = $userId . '_' . $t . '_m.' . $imageType;

                    $libImage->resize($configUser->avatarSW, $configUser->avatarSH, 'north');
                    $libImage->save($avatarDir . $userId . '_' . $t . '_s.' . $imageType);
                    $rowUser->avatar_s = $userId . '_' . $t . '_s.' . $imageType;

                    $rowUser->save();
                }
            }

            @unlink($avatarFile['tmp_name']);
        } else {
            $uploadErrors = array(
                '1' => '您上传的文件过大！',
                '2' => '您上传的文件过大！',
                '3' => '文件只有部分被上传！',
                '4' => '没有文件被上传！',
                '5' => '上传的文件大小为 0！'
            );
            $error = null;
            if (array_key_exists($avatarFile['error'], $uploadErrors)) {
                $error = $uploadErrors[$avatarFile['error']];
            } else {
                $error = '错误代码：' . $avatarFile['error'];
            }

            throw new ServiceException('上传失败' . '(' . $error . ')');
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
        $db->beginTransaction();
        try {

            $rowUser = Be::getRow('System', 'User');
            $rowUser->load($userId);

            $files = [];
            if ($rowUser->avatar_s != '') $files[] = Be::getRuntime()->getPathData() . '/System/User/Avatar/' . $rowUser->avatar_s;
            if ($rowUser->avatar_m != '') $files[] = Be::getRuntime()->getPathData() . '/System/User/Avatar/' . $rowUser->avatar_m;
            if ($rowUser->avatar_l != '') $files[] = Be::getRuntime()->getPathData() . '/System/User/Avatar/' . $rowUser->avatar_l;

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
        $table = Be::getTable('System', 'User');
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
        $table = Be::getTable('System', 'User');
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
        return Be::getTable('System', 'UserRole')->orderBy('ordering', 'asc')->getObjects();
    }

    /**
     * 更新所有角色缓存
     */
    public function updateUserRoles()
    {
        $roles = $this->getRoles();
        $service = Be::getService('System', 'Cache');
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
        $service = Be::getService('System', 'Cache');
        $service->updateCacheUserRole($roleId);
    }
}
