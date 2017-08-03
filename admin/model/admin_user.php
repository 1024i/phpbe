<?php
namespace admin\model;

use system\be;
use system\db;
use system\session;
use system\cookie;

class admin_user extends \system\service
{

	public function login($username, $password)
	{
		$ip = $_SERVER["REMOTE_ADDR"];
		$times = session::get($ip);
		if (!$times) $times=0;
		$times++;
		if ($times>100) {
			$this->set_error('登陆失败次数过多，请稍后再试！');
			return false;
		}
		session::set($ip, $times);

		$row_user_admin_log = be::get_row('user_admin_log');
		$row_user_admin_log->username = $username;
		$row_user_admin_log->ip = $ip;
        $row_user_admin_log->create_time = time();
		
		$user = db::get_object('SELECT * FROM `be_admin_user` WHERE `username`=\''.$username.'\'');

		$result = false;
		
		if ($user) {
			if ($user->group_id == 0) {
			    $row_user_admin_log->description = '该用户账号没有后台权限';
				$this->set_error('该用户账号没有后台权限');
			} else {
				if ($user->password == $this->encrypt_password($password)) {
					if ($user->block == 1) {
					    $row_user_admin_log->description = '用户账号已被停用！';
						$this->set_error('用户账号已被停用！');
					} else {
    					session::delete($ip);
    					$be_user = be::get_admin_user($user->id);

    					session::set('_admin_user', $be_user);

    					$row_user_admin_log->success = 1;
    					$row_user_admin_log->description = '登陆成功！';

    					db::execute('UPDATE `be_admin_user` SET `last_login_time`='.time().' WHERE `id`='.$user->id);

						$admin_config_admin_user = be::get_admin_config('admin_user');
						$remember_me_admin = $username.'|||'.$this->encrypt_password($user->password);
						$remember_me_admin = $this->rc4($remember_me_admin, $admin_config_admin_user->remember_me_key);
						$remember_me_admin = base64_encode($remember_me_admin);
                        cookie::set_expire(time() + 30*86400);
                        cookie::set('_remember_me_admin', $remember_me_admin);
    					$result = $be_user;
                    }
				} else {
				    $row_user_admin_log->description = '密码错误！';
					$this->set_error('密码错误！');
				}
			}
		} else {
		    $row_user_admin_log->description = '用户名不存在！';
			$this->set_error('用户名不存在！');
		}
		$row_user_admin_log->save();
		return $result;
	}
    

	// 记住我
	public function remember_me()
	{
        if (cookie::has('_remember_me_admin')) {
			$remember_me_admin = cookie::get('_remember_me_admin', '');
			if ($remember_me_admin) {
				$admin_config_admin_user = be::get_admin_config('admin_user');
				$remember_me_admin = base64_decode($remember_me_admin);
				$remember_me_admin = $this->rc4($remember_me_admin, $admin_config_admin_user->remember_me_key);
				$remember_me_admin = explode('|||', $remember_me_admin);
				if (count($remember_me_admin) == 2) {
					$username = $remember_me_admin[0];
					$password = $remember_me_admin[0];

					$user = db::get_object('SELECT * FROM `be_admin_user` WHERE `username`=\''.db::escape($username).'\'');

					if ($user && $this->encrypt_password($user->password) == $password  && $user->block == 0) {
						session::set('_admin_user', be::get_admin_user($user->id));

						db::execute('UPDATE `be_admin_user` SET `last_login_time`='.time().' WHERE `id`='.$user->id);
						return $user;
					}
				}
			}
        }
	}


	// 退出
	public function logout()
	{
		session::delete('_admin_user');
		cookie::delete('_remember_me_admin');
		return true;
	}

	// 获取指定条件的用户列表
	public function get_users($option = array())
	{
		$sql = 'SELECT * FROM `be_admin_user` WHERE 1'.$this->create_user_sql($option);

		if (array_key_exists('order_by_string', $option) && $option['order_by_string']) {
			$sql .= ' ORDER BY '.$option['order_by_string'];
		} else {
			$order_by = '`id`';
			$order_by_dir = 'ASC';

			if (array_key_exists('order_by', $option) && $option['order_by']) $order_by = $option['order_by'];
			if (array_key_exists('order_by_dir', $option) && $option['order_by_dir']) $order_by_dir = $option['order_by_dir'];
			$sql .= ' ORDER BY '.$order_by.' '.$order_by_dir;
		}

		$offset = 0;
		$limit = 0;
		if (array_key_exists('offset', $option) && $option['offset']) $offset = $option['offset'];
		if (array_key_exists('limit', $option) && $option['limit']) $limit = $option['limit'];

        return db::get_objects($sql, $offset, $limit);
	}
    
	// 获取指定条件的用户总数
    public function get_user_count($option = array())
    {
        $query = 'SELECT COUNT(*) FROM `be_admin_user` WHERE 1 ' . $this->create_user_sql($option);
        return db::get_value($query);
    }

	// 生成查找用户的 SQL
	private function create_user_sql($option = array())
	{
		$sql = '';
		
		if (array_key_exists('key', $option) && $option['key']) $sql .= ' AND (`username` LIKE \'%' . $option['key'] . '%\' OR `name` LIKE \'%' . $option['key'] . '%\' OR `email` LIKE \'%' . $option['key'] . '%\')';
		if (array_key_exists('status', $option) && $option['status']!= -1) $sql .= ' AND `block`='.$option['status'];
		if (array_key_exists('group_id', $option) && $option['group_id']>0) $sql .= ' AND `group_id`='.$option['group_id'];
		return $sql;
	}


    public function unblock($ids)
    {
        if (!db::execute('UPDATE `be_admin_user` SET `block`=0 WHERE `id` IN(' . $ids . ')')) {
            $this->set_error(db::get_error());
            return false;
        }
        return true;
    }

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
        
        if (!db::execute('UPDATE `be_admin_user` SET `block`=1 WHERE `id` IN(' . $ids . ')')) {
            $this->set_error(db::get_error());
            return false;
        }
        return true;
    }

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
        
        foreach ($array as $id) {
            $row_user = be::get_row('user');
            $row_user->load($id);
            $this->delete_avatar_file($row_user);
            $row_user->delete();
        }
        return true;
    }

    public function delete_avatar_file($row_user)
    {
		// 删除旧头像
		if ($row_user->avatar_s!='') @unlink(PATH_DATA.DS.'admin_user'.DS.'avatar'.DS.$row_user->avatar_s);
		if ($row_user->avatar_m!='') @unlink(PATH_DATA.DS.'admin_user'.DS.'avatar'.DS.$row_user->avatar_m);
		if ($row_user->avatar_l!='') @unlink(PATH_DATA.DS.'admin_user'.DS.'avatar'.DS.$row_user->avatar_l);
    }

    public function init_avatar($user_id)
    {
        $row_user = be::get_row('user');
        $row_user->load($user_id);

		$this->delete_avatar_file($row_user);

        $row_user->avatar_s = '';
        $row_user->avatar_m = '';
        $row_user->avatar_l = '';
        
        if (!$row_user->save()) {
            $this->set_error($row_user->get_error());
            return false;
        }
        
        return true;
    }


    public function is_username_available($username, $user_id=0)
    {
        return db::get_value('SELECT COUNT(*) FROM `be_admin_user` WHERE '.($user_id>0?'`id`!='.$user_id.' AND ':'').'`username`=\'' . $username . '\'') == 0;
    }

    public function is_email_available($email, $user_id=0)
    {
        return db::get_value('SELECT COUNT(*) FROM `be_admin_user` WHERE '.($user_id>0?('`id`!='.$user_id.' AND '):'').'`email`=\'' . $email . '\'') == 0;
    }

	public function get_groups()
    {
        $sql = 'SELECT * FROM `be_admin_user_group` ORDER BY `rank` ASC';
        return db::get_objects($sql);
    }

    public function get_logs($option = array())
    {
		$sql = 'SELECT * FROM `be_admin_user_log` WHERE 1'.$this->create_log_sql($option);

		if (array_key_exists('order_by_string', $option) && $option['order_by_string']) {
			$sql .= ' ORDER BY '.$option['order_by_string'];
		} else {
			$order_by = '`create_time`';
			$order_by_dir = 'DESC';

			if (array_key_exists('order_by', $option) && $option['order_by']) $order_by = $option['order_by'];
			if (array_key_exists('order_by_dir', $option) && $option['order_by_dir']) $order_by_dir = $option['order_by_dir'];
			$sql .= ' ORDER BY '.$order_by.' '.$order_by_dir;
		}

		$offset = 0;
		$limit = 0;
		if (array_key_exists('offset', $option) && $option['offset']) $offset = $option['offset'];
		if (array_key_exists('limit', $option) && $option['limit']) $limit = $option['limit'];

        $logs = db::get_objects($sql, $offset, $limit);
        return $logs;
    }
    
    public function get_log_count($option = array())
    {
        $sql = 'SELECT COUNT(*) FROM `be_admin_user_log` WHERE 1 '  . $this->create_log_sql($option);
        return db::get_value($sql);
    }

	private function create_log_sql($option = array())
	{
		$sql = '';
		
		if (array_key_exists('key', $option) && $option['key']) $sql .= ' AND `username` LIKE \'%' . $option['key'] . '%\'';
		if (array_key_exists('success', $option) && $option['success']!= -1) $sql .= ' AND `success`='.$option['success'];

		return $sql;
	}
	

	// 删除三个月(90天)前的后台用户登陆日志
	public function delete_logs()
	{
		return db::execute('DELETE FROM `be_admin_user_log` WHERE `create_time`<'.(time()-90*86400));
	}


	public function encrypt_password($password)
	{
		// return md5($password.md5('BE'));
		return md5($password.'d3dcf429c679f9af82eb9a3b31c4df44');
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
			for($i = 0; $i < $tL; ++$i) {
				$result .= $txt[$i] ^ $pwd[$i % $kL];
			}
		}
		return $result;
	}


}
?>