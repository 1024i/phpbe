<?php

namespace service;

use app\system\tool\random;
use app\system\tool\validator;
use system\be;
use system\session;
use system\cookie;

class user extends \system\service
{

    /**
     * 登录
     *
     * @param string $username 用户名
     * @param string $password 密码
     * @param bool $remember_me 记住我
     * @return bool|mixed|\system\row
     */
    public function login($username, $password, $remember_me = false)
    {
        $ip = $_SERVER["REMOTE_ADDR"];
        $times = session::get($ip);
        if (!$times) $times = 0;
        $times++;
        if ($times > 10) {
            $this->set_error('登陆失败次数过多，请稍后再试！');
            return false;
        }
        session::set($ip, $times);

        $row_user = be::get_row('system.user');
        $row_user->load(['username' => $username]);

        if ($row_user->id > 0) {

            $password = $this->encrypt_password($password, $row_user->salt);

            if ($row_user->password == $password) {
                if ($row_user->block == 1) {
                    $this->set_error('用户账号已被停用！');
                    return false;
                }

                session::delete($ip);
                session::set('_user', be::get_user($row_user->id));

                $row_user->last_login_time = time();
                $row_user->save();

                if ($remember_me) {
                    $config_user = be::get_config('system.user');
                    $remember_me = $username . '|||' . $this->encrypt_password($password, $row_user->salt);
                    $remember_me = $this->rc4($remember_me, $config_user->remember_me_key);
                    $remember_me = base64_encode($remember_me);
                    cookie::set_expire(time() + 30 * 86400);
                    cookie::set('_remember_me', $remember_me);
                }
                return $row_user;
            } else {
                $this->set_error('密码错误！');
            }
        } else {
            $this->set_error('用户名不存在！');
        }
        return false;
    }

    /**
     * 记住我 自动登录
     *
     * @return bool|mixed|\system\row
     */
    public function remember_me()
    {
        if (cookie::has('_remember_me')) {
            $remember_me = cookie::get('_remember_me', '');
            if ($remember_me) {
                $config_user = be::get_config('system.user');
                $remember_me = base64_decode($remember_me);
                $remember_me = $this->rc4($remember_me, $config_user->remember_me_key);
                $remember_me = explode('|||', $remember_me);
                if (count($remember_me) == 2) {
                    $username = $remember_me[0];
                    $password = $remember_me[0];

                    $row_user = be::get_row('system.user');
                    $row_user->load(['username' => $username]);

                    if ($row_user->id > 0 && $this->encrypt_password($row_user->password, $row_user->salt) == $password && $row_user->block == 0) {
                        session::set('_user', be::get_user($row_user->id));

                        $row_user->last_login_time = time();
                        $row_user->save();

                        return $row_user;
                    }
                }
            }
        }

        return true;
    }

    /**
     * 退出
     *
     * @return bool
     */
    public function logout()
    {
        session::delete('_user');
        cookie::delete('_remember_me');
        return true;
    }

    /**
     * 注册
     *
     * @param string $username 用户名
     * @param string $email 邮箱
     * @param string $password 密码
     * @param string $name 名称
     * @return mixed|\system\row
     */
    public function register($username, $email, $password, $name = '')
    {
        $row_user = be::get_row('system.user');
        $row_user->load(['username' => $username]);

        if ($row_user->id > 0) {
            $this->set_error('用户名(' . $username . ')已被占用！');
            return false;
        }

        $row_user = be::get_row('system.user');
        $row_user->load(['email' => $email]);

        if ($row_user->id > 0) {
            $this->set_error('邮箱(' . $email . ')已被占用！');
            return false;
        }

        if ($name == '') $name = $username;

        $t = time();

        $config_user = be::get_config('system.user');

        $salt = random::complex(32);

        $row_user = be::get_row('system.user');
        $row_user->username = $username;
        $row_user->email = $email;
        $row_user->name = $name;
        $row_user->password = $this->encrypt_password($password, $salt);
        $row_user->salt = $salt;
        $row_user->token = random::complex(32);
        $row_user->register_time = $t;
        $row_user->last_login_time = $t;
        $row_user->is_admin = 0;
        $row_user->block = ($config_user->email_valid == '1' ? 1 : 0);
        $row_user->save();

        $config_system = be::get_config('system.system');

        $config_user = be::get_config('system.user');
        if ($config_user->email_valid == '1') {
            $activation_url = url('app=system&controller=user&task=forget_password_reset&user_id=' . $row_user->id . '&token=' . $row_user->token);

            $data = array(
                'site_name' => $config_system->site_name,
                'username' => $row_user->username,
                'password' => $password,
                'name' => $row_user->name,
                'activation_url' => $activation_url
            );

            $lib_mail = be::get_lib('mail');

            $subject = $lib_mail->format($config_user->register_mail_activation_subject, $data);
            $body = $lib_mail->format($config_user->register_mail_activation_body, $data);

            $lib_mail->set_subject($subject);
            $lib_mail->set_body($body);
            $lib_mail->to($row_user->email, $row_user->name);
            $lib_mail->send();
        } else {
            if ($config_user->email_register == '1') {
                $data = array(
                    'site_name' => $config_system->site_name,
                    'username' => $row_user->username,
                    'name' => $row_user->name
                );

                $lib_mail = be::get_lib('mail');

                $subject = $lib_mail->format($config_user->register_mail_subject, $data);
                $body = $lib_mail->format($config_user->register_mail_body, $data);

                $lib_mail->set_subject($subject);
                $lib_mail->set_body($body);
                $lib_mail->to($row_user->email, $row_user->name);
                $lib_mail->send();
            }
        }

        if ($config_user->email_register_admin != '') {
            if (validator::is_email($config_user->email_register_admin)) {
                $data = array(
                    'site_name' => $config_system->site_name,
                    'username' => $row_user->username,
                    'email' => $email,
                    'name' => $row_user->name
                );

                $lib_mail = be::get_lib('mail');

                $subject = $lib_mail->format($config_user->register_mail_to_admin_subject, $data);
                $body = $lib_mail->format($config_user->register_mail_to_admin_body, $data);

                $lib_mail->set_subject($subject);
                $lib_mail->set_body($body);
                $lib_mail->to($config_user->email_register_admin);
                $lib_mail->send();
            }
        }

        return $row_user;
    }

    /**
     * 忘记密码
     * 向用户邮箱发送一封重置密码的邮件
     *
     * @param string $username 用户名
     * @return bool
     */
    public function forgot_password($username)
    {
        if ($username == '') {
            $this->set_error('用户名不能为空！');
            return false;
        }

        $row_user = be::get_row('system.user');
        $row_user->load('username', $username);

        if ($row_user->id == 0) {
            $this->set_error('账号不存在！');
            return false;
        }

        if ($row_user->id == 1) {
            $this->set_error('超级管理禁止使用该功能！');
            return false;
        }

        $row_user->token = random::complex(32);
        $row_user->save();

        $config_system = be::get_config('system.system');

        $activation_url = url('controller=user&task=forgot_password_reset&user_id=' . $row_user->id . '&token=' . $row_user->token);

        $data = array(
            'site_name' => $config_system->site_name,
            'activation_url' => $activation_url
        );
        $config_user = be::get_config('system.user');

        $lib_mail = be::get_lib('mail');

        $subject = $lib_mail->format($config_user->forgot_password_mail_subject, $data);
        $body = $lib_mail->format($config_user->forgot_password_mail_body, $data);

        $lib_mail->set_subject($subject);
        $lib_mail->set_body($body);
        $lib_mail->to($row_user->email, $row_user->name);
        $lib_mail->send();

        return true;
    }

    /**
     * 忘记密码重置
     *
     * @param int $user_id 用户ID
     * @param string $token 邮件发送的 token
     * @param string $password 新密码
     * @return bool
     */
    public function forgot_password_reset($user_id, $token, $password)
    {
        $row_user = be::get_row('system.user');
        $row_user->load($user_id);

        if ($row_user->token != $token) {
            if ($row_user->token == '')
                $this->set_error('您的密码已重设！');
            else
                $this->set_error('重设密码链接已失效！');
            return false;
        }
        $salt = random::complex(32);
        $row_user->password = $this->encrypt_password($password, $salt);
        $row_user->salt = $salt;
        $row_user->token = '';
        $row_user->save();

        $config_system = be::get_config('system.system');

        $data = array(
            'site_name' => $config_system->site_name,
            'site_url' => URL_ROOT
        );

        $config_user = be::get_config('system.user');

        $lib_mail = be::get_lib('mail');

        $subject = $lib_mail->format($config_user->forgot_password_reset_mail_subject, $data);
        $body = $lib_mail->format($config_user->forgot_password_reset_mail_body, $data);

        $lib_mail->set_subject($subject);
        $lib_mail->set_body($body);
        $lib_mail->to($row_user->email, $row_user->name);
        $lib_mail->send();

        return true;
    }


    /**
     * 密码 Hash
     *
     * @param string $password 密码
     * @param string $salt 盐值
     * @return string
     */
    public function encrypt_password($password, $salt)
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
    public function get_users($conditions = [])
    {
        $table_user = be::get_table('system.user');

        $where = $this->create_user_where($conditions);
        $table_user->where($where);

        if (isset($conditions['order_by_string']) && $conditions['order_by_string']) {
            $table_user->order_by($conditions['order_by_string']);
        } else {
            $order_by = 'id';
            $order_by_dir = 'DESC';
            if (isset($conditions['order_by']) && $conditions['order_by']) $order_by = $conditions['order_by'];
            if (isset($conditions['order_by_dir']) && $conditions['order_by_dir']) $order_by_dir = $conditions['order_by_dir'];
            $table_user->order_by($order_by, $order_by_dir);
        }

        if (isset($conditions['offset']) && $conditions['offset']) $table_user->offset($conditions['offset']);
        if (isset($conditions['limit']) && $conditions['limit']) $table_user->limit($conditions['limit']);

        return $table_user->get_objects();
    }

    /**
     * 获取指定条件的用户总数
     *
     * @param array $conditions 查询条件
     * @return int
     */
    public function get_user_count($conditions = [])
    {
        return be::get_table('system.user')
            ->where($this->create_user_where($conditions))
            ->count();
    }

    /**
     * 生成查询条件 where
     *
     * @param array $conditions 查询条件
     * @return array
     */
    private function create_user_where($conditions = [])
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

        if (isset($conditions['role_id']) && is_numeric($conditions['role_id']) && $conditions['role_id'] > 0) {
            $where[] = ['role_id', $conditions['role_id']];
        }

        return $where;
    }

    /**
     * 屏蔽用户账号
     *
     * @param string $ids 以逗号分隔的多个用户ID
     * @return bool
     */
    public function unblock($ids)
    {
        $db = be::get_db();
        try {
            $db->begin_transaction();

            $table = be::get_table('system.user');
            if (!$table->where('id', 'in', explode(',', $ids))
                ->update(['block' => 0])
            ) {
                throw new \exception($table->get_error());
            }

            $db->commit();
        } catch (\exception $e) {
            $db->rollback();

            $this->set_error($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 启用用户账号
     *
     * @param string $ids 以逗号分隔的多个用户ID
     * @return bool
     */
    public function block($ids)
    {
        $db = be::get_db();
        try {
            $db->begin_transaction();

            $table = be::get_table('system.user');
            if (!$table->where('id', 'in', explode(',', $ids))
                ->update(['block' => 1])
            ) {
                throw new \exception($table->get_error());
            }

            $db->commit();
        } catch (\exception $e) {
            $db->rollback();

            $this->set_error($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 删除用户账号
     *
     * @param string $ids 以逗号分隔的多个用户ID
     * @return bool
     */
    public function delete($ids)
    {
        $db = be::get_db();
        try {
            $db->begin_transaction();

            $files = [];

            $array = explode(',', $ids);
            foreach ($array as $id) {

                $row_user = be::get_row('system.user');
                $row_user->load($id);

                if ($row_user->avatar_s != '') $files[] = PATH_DATA . DS . 'system' . DS . 'user' . DS . 'avatar' . DS . $row_user->avatar_s;
                if ($row_user->avatar_m != '') $files[] = PATH_DATA . DS . 'system' . DS . 'user' . DS . 'avatar' . DS . $row_user->avatar_m;
                if ($row_user->avatar_l != '') $files[] = PATH_DATA . DS . 'system' . DS . 'user' . DS . 'avatar' . DS . $row_user->avatar_l;

                if (!$row_user->delete()) {
                    throw new \exception($row_user->get_error());
                }
            }

            $db->commit();

            foreach ($files as $file) {
                if (file_exists($file)) @unlink($file);
            }

        } catch (\exception $e) {
            $db->rollback();

            $this->set_error($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 初始化用户头像
     *
     * @param int $user_id 用户ID
     * @return bool
     */
    public function init_avatar($user_id)
    {
        $db = be::get_db();
        try {
            $db->begin_transaction();

            $row_user = be::get_row('system.user');
            $row_user->load($user_id);

            $files = [];
            if ($row_user->avatar_s != '') $files[] = PATH_DATA . DS . 'system' . DS . 'user' . DS . 'avatar' . DS . $row_user->avatar_s;
            if ($row_user->avatar_m != '') $files[] = PATH_DATA . DS . 'system' . DS . 'user' . DS . 'avatar' . DS . $row_user->avatar_m;
            if ($row_user->avatar_l != '') $files[] = PATH_DATA . DS . 'system' . DS . 'user' . DS . 'avatar' . DS . $row_user->avatar_l;

            $row_user->avatar_s = '';
            $row_user->avatar_m = '';
            $row_user->avatar_l = '';

            if (!$row_user->save()) {
                throw new \exception($row_user->get_error());
            }

            $db->commit();

            foreach ($files as $file) {
                if (file_exists($file)) @unlink($file);
            }

        } catch (\exception $e) {
            $db->rollback();

            $this->set_error($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 检测用户名是否可用
     *
     * @param $username
     * @param int $user_id
     * @return bool
     */
    public function is_username_available($username, $user_id = 0)
    {
        $table = be::get_table('system.user');
        if ($user_id > 0) {
            $table->where('id', '!=', $user_id);
        }
        $table->where('username', $username);
        return $table->count() == 0;
    }

    /**
     * 检测邮箱是否可用
     *
     * @param $email
     * @param int $user_id
     * @return bool
     */
    public function is_email_available($email, $user_id = 0)
    {
        $table = be::get_table('system.user');
        if ($user_id > 0) {
            $table->where('id', '!=', $user_id);
        }
        $table->where('email', $email);
        return $table->count() == 0;
    }

    /**
     * 获取角色列表
     *
     * @return array
     */
    public function get_roles()
    {
        return be::get_table('user_role')->order_by('ordering', 'asc')->get_objects();
    }

    /**
     * 更新所有角色缓存
     */
    public function update_user_roles()
    {
        $roles = $this->get_roles();
        $service_system = be::get_service('system');
        foreach ($roles as $role) {
            $service_system->update_cache_user_role($role->id);
        }
    }

    /**
     * 更新指定角色缓存
     *
     * @param int $role_id 角色ID
     */
    public function update_user_role($role_id)
    {
        $service_system = be::get_service('system');
        $service_system->update_cache_user_role($role_id);
    }
}
