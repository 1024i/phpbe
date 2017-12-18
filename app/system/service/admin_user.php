<?php
namespace app\system\service;

use system\be;
use system\session;
use system\cookie;

class admin_user extends \system\service
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
        $times = session::get($ip);
        if (!$times) $times = 0;
        $times++;
        if ($times > 100) {
            $this->set_error('登陆失败次数过多，请稍后再试！');
            return false;
        }
        session::set($ip, $times);

        $row_admin_user_admin_log = be::get_row('admin_user_log');
        $row_admin_user_admin_log->username = $username;
        $row_admin_user_admin_log->ip = $ip;
        $row_admin_user_admin_log->create_time = time();

        $row_admin_user = be::get_row('admin_user');
        $row_admin_user->load('username', $username);

        $result = false;
        if ($row_admin_user->id) {
            if ($row_admin_user->password == $this->encrypt_password($password)) {
                if ($row_admin_user->block == 1) {
                    $row_admin_user_admin_log->description = '管理员账号已被停用！';
                    $this->set_error('管理员账号已被停用！');
                } else {
                    session::delete($ip);
                    $be_admin_user = be::get_admin_user($row_admin_user->id);

                    session::set('_admin_user', $be_admin_user);

                    $row_admin_user_admin_log->success = 1;
                    $row_admin_user_admin_log->description = '登陆成功！';

                    $row_admin_user->last_login_time = time();
                    $row_admin_user->save();

                    $admin_config_admin_user = be::get_config('system.admin_user');
                    $remember_me_admin = $username . '|||' . $this->encrypt_password($row_admin_user->password);
                    $remember_me_admin = $this->rc4($remember_me_admin, $admin_config_admin_user->remember_me_key);
                    $remember_me_admin = base64_encode($remember_me_admin);
                    cookie::set_expire(time() + 30 * 86400);
                    cookie::set('_remember_me_admin', $remember_me_admin);
                    $result = $be_admin_user;
                }
            } else {
                $row_admin_user_admin_log->description = '密码错误！';
                $this->set_error('密码错误！');
            }
        } else {
            $row_admin_user_admin_log->description = '管理员名不存在！';
            $this->set_error('管理员名不存在！');
        }
        $row_admin_user_admin_log->save();
        return $result;
    }

    /**
     * 记住我
     *
     * @return bool|mixed|\system\row
     */
    public function remember_me()
    {
        if (cookie::has('_remember_me_admin')) {
            $remember_me_admin = cookie::get('_remember_me_admin', '');
            if ($remember_me_admin) {
                $admin_config_admin_user = be::get_config('system.admin_user');
                $remember_me_admin = base64_decode($remember_me_admin);
                $remember_me_admin = $this->rc4($remember_me_admin, $admin_config_admin_user->remember_me_key);
                $remember_me_admin = explode('|||', $remember_me_admin);
                if (count($remember_me_admin) == 2) {
                    $username = $remember_me_admin[0];
                    $password = $remember_me_admin[0];

                    $row_admin_user = be::get_row('admin_user');
                    $row_admin_user->load('username', $username);

                    if ($row_admin_user->id && $this->encrypt_password($row_admin_user->password) == $password && $row_admin_user->block == 0) {
                        session::set('_admin_user', be::get_admin_user($row_admin_user->id));

                        $row_admin_user->last_login_time = time();
                        $row_admin_user->save();
                        return $row_admin_user;
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
    public function get_users($conditions = array())
    {
        $table_admin_user = be::get_table('system.admin_user');
        $table_admin_user->where($this->create_user_where($conditions));

        if (isset($conditions['order_by_string']) && $conditions['order_by_string']) {
            $table_admin_user->order_by($conditions['order_by_string']);
        } else {
            $order_by = 'rank';
            $order_by_dir = 'DESC';
            if (isset($conditions['order_by']) && $conditions['order_by']) $order_by = $conditions['order_by'];
            if (isset($conditions['order_by_dir']) && $conditions['order_by_dir']) $order_by_dir = $conditions['order_by_dir'];
            $table_admin_user->order_by($order_by, $order_by_dir);
        }

        if (isset($conditions['offset']) && $conditions['offset']) $table_admin_user->offset($conditions['offset']);
        if (isset($conditions['limit']) && $conditions['limit']) $table_admin_user->limit($conditions['limit']);

        return $table_admin_user->get_objects();
    }

    /**
     * 获取指定条件的管理员总数
     *
     * @param array $conditions 查询条件
     * @return int
     */
    public function get_user_count($conditions = array())
    {
        return be::get_table('system.admin_user')
            ->where($this->create_user_where($conditions))
            ->count();
    }

    /**
     * 生成查询条件 where 数组
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
            $where[] = ['role_id', '>', $conditions['role_id']];
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
        $db = be::get_db();
        try {
            $db->begin_transaction();

            $table = be::get_table('system.admin_user');
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
     * 启用管理员账号
     *
     * @param string $ids 以逗号分隔的多个管理员ID
     * @return bool
     */
    public function block($ids)
    {
        $array = explode(',', $ids);
        if (in_array(1, $array)) {
            $this->set_error('默认管理员不能屏蔽');
            return false;
        }

        $my = be::get_admin_user();
        if (in_array($my->id, $array)) {
            $this->set_error('不能屏蔽自已');
            return false;
        }

        $db = be::get_db();
        try {
            $db->begin_transaction();

            $table = be::get_table('system.admin_user');
            if (!$table->where('id', 'in', $array)
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
     * 删除管理员账号
     *
     * @param string $ids 以逗号分隔的多个管理员ID
     * @return bool
     */
    public function delete($ids)
    {
        $array = explode(',', $ids);
        if (in_array(1, $array)) {
            $this->set_error('默认管理员不能删除');
            return false;
        }

        $my = be::get_admin_user();
        if (in_array($my->id, $array)) {
            $this->set_error('不能删除自已');
            return false;
        }

        $db = be::get_db();
        try {
            $db->begin_transaction();

            $files = [];

            $array = explode(',', $ids);
            foreach ($array as $id) {

                $row_admin_user = be::get_row('system.user');
                $row_admin_user->load($id);

                if ($row_admin_user->avatar_s != '') $files[] = PATH_DATA . DS . 'system' . DS . 'admin_user' . DS . 'avatar' . DS . $row_admin_user->avatar_s;
                if ($row_admin_user->avatar_m != '') $files[] = PATH_DATA . DS . 'system' . DS . 'admin_user' . DS . 'avatar' . DS . $row_admin_user->avatar_m;
                if ($row_admin_user->avatar_l != '') $files[] = PATH_DATA . DS . 'system' . DS . 'admin_user' . DS . 'avatar' . DS . $row_admin_user->avatar_l;

                if (!$row_admin_user->delete()) {
                    throw new \exception($row_admin_user->get_error());
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
     * 初始化管理员头像
     *
     * @param int $user_id 管理员ID
     * @return bool
     */
    public function init_avatar($user_id)
    {
        $db = be::get_db();
        try {
            $db->begin_transaction();

            $row_admin_user = be::get_row('system.user');
            $row_admin_user->load($user_id);

            $files = [];
            if ($row_admin_user->avatar_s != '') $files[] = PATH_DATA . DS . 'system' . DS . 'admin_user' . DS . 'avatar' . DS . $row_admin_user->avatar_s;
            if ($row_admin_user->avatar_m != '') $files[] = PATH_DATA . DS . 'system' . DS . 'admin_user' . DS . 'avatar' . DS . $row_admin_user->avatar_m;
            if ($row_admin_user->avatar_l != '') $files[] = PATH_DATA . DS . 'system' . DS . 'admin_user' . DS . 'avatar' . DS . $row_admin_user->avatar_l;

            $row_admin_user->avatar_s = '';
            $row_admin_user->avatar_m = '';
            $row_admin_user->avatar_l = '';

            if (!$row_admin_user->save()) {
                throw new \exception($row_admin_user->get_error());
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
        $table = be::get_table('system.admin_user');
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
        $table = be::get_table('system.admin_user');
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
        return be::get_table('admin_user_role')->order_by('rank', 'asc')->get_objects();
    }

    /**
     * 获取符合条件的管理员操作日志列表
     *
     * @param array $conditions 查询条件
     * @return array
     */
    public function get_logs($conditions = array())
    {
        $table_admin_user_log = be::get_table('admin_user_log');
        $table_admin_user_log->where($this->create_log_where($conditions));

        if (isset($conditions['order_by_string']) && $conditions['order_by_string']) {
            $table_admin_user_log->order_by($conditions['order_by_string']);
        } else {
            $order_by = 'create_time';
            $order_by_dir = 'DESC';
            if (isset($conditions['order_by']) && $conditions['order_by']) $order_by = $conditions['order_by'];
            if (isset($conditions['order_by_dir']) && $conditions['order_by_dir']) $order_by_dir = $conditions['order_by_dir'];
            $table_admin_user_log->order_by($order_by, $order_by_dir);
        }

        if (isset($conditions['offset']) && $conditions['offset']) $table_admin_user_log->offset($conditions['offset']);
        if (isset($conditions['limit']) && $conditions['limit']) $table_admin_user_log->limit($conditions['limit']);

        return $table_admin_user_log->get_objects();
    }

    /**
     * 获取符合条件的管理员操作日志总数
     *
     * @param array $conditions 查询条件
     * @return int
     */
    public function get_log_count($conditions = array())
    {
        return be::get_table('admin_user_log')
            ->where($this->create_log_where($conditions))
            ->count();
    }

    /**
     * 跟据查询条件生成 where
     *
     * @param array $conditions 查询条件
     * @return array
     */
    private function create_log_where($conditions = array())
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
    public function delete_logs()
    {
        $table = be::get_table('admin_user_log');
        if (!$table->where('create_time', '<', time() - 90 * 86400)
            ->delete()
        ) {
            $this->set_error($table->get_error());
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
    public function encrypt_password($password)
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
    public function update_admin_user_roles()
    {
        $roles = $this->get_roles();

        $service_system = be::get_service('system');
        foreach ($roles as $role) {
            $service_system->update_cache_admin_user_role($role->id);
        }
    }

    /**
     * 更新指定角色缓存
     *
     * @param int $role_id 角色ID
     */
    public function update_admin_user_role($role_id)
    {
        $service_system = be::get_service('system');
        $service_system->update_cache_admin_user_role($role_id);
    }
}
