<?php

namespace service;

use system\be;
use system\db;
use system\session;
use system\cookie;

class user extends \system\service
{


    // 登陆
    public function login($username, $password, $remember_me = false)
    {
        $password = $this->encrypt_password($password);

        $ip = $_SERVER["REMOTE_ADDR"];
        $times = session::get($ip);
        if (!$times) $times = 0;
        $times++;
        if ($times > 10) {
            $this->set_error('登陆失败次数过多，请稍后再试！');
            return false;
        }
        session::set($ip, $times);

        $row_user = be::get_row('user');
        $row_user->load(['username' => $username]);

        if ($row_user->id > 0) {
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
                    $config_user = be::get_config('user');
                    $remember_me = $username . '|||' . $this->encrypt_password($row_user->password);
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


    // 记住我
    public function remember_me()
    {
        if (cookie::has('_remember_me')) {
            $remember_me = cookie::get('_remember_me', '');
            if ($remember_me) {
                $config_user = be::get_config('user');
                $remember_me = base64_decode($remember_me);
                $remember_me = $this->rc4($remember_me, $config_user->remember_me_key);
                $remember_me = explode('|||', $remember_me);
                if (count($remember_me) == 2) {
                    $username = $remember_me[0];
                    $password = $remember_me[0];

                    $row_user = be::get_row('user');
                    $row_user->load(['username' => $username]);

                    if ($row_user->id > 0 && $this->encrypt_password($row_user->password) == $password && $row_user->block == 0) {
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

    // 退出
    public function logout()
    {
        session::delete('_user');
        cookie::delete('_remember_me');
        return true;
    }


    // 注册
    public function register($username, $email, $password, $name = '')
    {
        $row_user = be::get_row('user');
        $row_user->load(['username' => $username]);

        if ($row_user->id >0) {
            $this->set_error('用户名(' . $username . ')已被占用！');
            return false;
        }

        $row_user = be::get_row('user');
        $row_user->load(['email' => $email]);

        if ($row_user->id >0) {
            $this->set_error('邮箱(' . $email . ')已被占用！');
            return false;
        }

        if ($name == '') $name = $username;

        $t = time();

        $config_user = be::get_config('user');

        $row_user = be::get_row('user');
        $row_user->username = $username;
        $row_user->email = $email;
        $row_user->name = $name;
        $row_user->password = $this->encrypt_password($password);
        $row_user->token = md5(rand());
        $row_user->register_time = $t;
        $row_user->last_login_time = $t;
        $row_user->is_admin = 0;
        $row_user->block = ($config_user->email_valid == '1' ? 1 : 0);
        $row_user->save();

        $config_system = be::get_config('system');


        $config_user = be::get_config('user');
        if ($config_user->email_valid == '1') {
            $activation_url = url('controller=user&task=forget_password_reset&user_id=' . $row_user->id . '&token=' . $row_user->token);

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
            if ($this->is_email($config_user->email_register_admin)) {
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


    // 忘记密码
    public function forgot_password($username)
    {
        if ($username == '') {
            $this->set_error('用户名不能为空！');
            return false;
        }

        $row_user = be::get_row('user');
        $row_user->load('username', $username);

        if ($row_user->id == 0) {
            $this->set_error('账号不存在！');
            return false;
        }

        if ($row_user->id == 1) {
            $this->set_error('超级管理禁止使用该功能！');
            return false;
        }

        $row_user->token = md5(rand());
        $row_user->save();

        $config_system = be::get_config('system');

        $activation_url = url('controller=user&task=forgot_password_reset&user_id=' . $row_user->id . '&token=' . $row_user->token);

        $data = array(
            'site_name' => $config_system->site_name,
            'activation_url' => $activation_url
        );
        $config_user = be::get_config('user');

        $lib_mail = be::get_lib('mail');

        $subject = $lib_mail->format($config_user->forgot_password_mail_subject, $data);
        $body = $lib_mail->format($config_user->forgot_password_mail_body, $data);

        $lib_mail->set_subject($subject);
        $lib_mail->set_body($body);
        $lib_mail->to($row_user->email, $row_user->name);
        $lib_mail->send();

        return true;
    }

    public function forgot_password_reset($user_id, $token, $password)
    {
        $row_user = be::get_row('user');
        $row_user->load($user_id);

        if ($row_user->token != $token) {
            if ($row_user->token == '')
                $this->set_error('您的密码已重设！');
            else
                $this->set_error('重设密码链接已失效！');
            return false;
        }

        $row_user->password = $this->encrypt_password($password);
        $row_user->token = '';
        $row_user->save();

        $config_system = be::get_config('system');

        $data = array(
            'site_name' => $config_system->site_name,
            'site_url' => URL_ROOT
        );

        $config_user = be::get_config('user');

        $lib_mail = be::get_lib('mail');

        $subject = $lib_mail->format($config_user->forgot_password_reset_mail_subject, $data);
        $body = $lib_mail->format($config_user->forgot_password_reset_mail_body, $data);

        $lib_mail->set_subject($subject);
        $lib_mail->set_body($body);
        $lib_mail->to($row_user->email, $row_user->name);
        $lib_mail->send();

        return true;
    }


    public function encrypt_password($password)
    {
        // return sha1($password.sha1('BE'));
        return sha1($password . '3472ff5765a2d9cb8605e9b928f61808c7010096');
    }

    public function is_email($email)
    {
        return preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i", $email);
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

}
