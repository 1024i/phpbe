<?php
namespace app\system\config;

class user
{
    public $register = true;  // 是否开启注册功能
    public $captcha_login = false;  // 是否开启登陆验证码
    public $captcha_register = true;  // 是否开启注册验证码
    public $email_valid = false;  // 是否发送激活链接验证用户邮箱
    public $email_register = false;  // 注册成功后是否发送提示邮件
    public $email_register_admin = '1024i@gmail.com';  // 新用户注册后给管理员发送邮件
    public $default_avatar_s = '0_s.png';  // 用户默认头像 小 small
    public $default_avatar_m = '0_m.png';  // 用户默认头像 中 medium
    public $default_avatar_l = '0_l.png';  // 用户默认头像 大 large
    public $avatar_s_w = 32;  // 用户头像 小 长度
    public $avatar_s_h = 32;  // 用户头像 小 高度
    public $avatar_m_w = 64;  // 用户头像 中 长度
    public $avatar_m_h = 64;  // 用户头像 中 高度
    public $avatar_l_w = 96;  // 用户头像 大 长度
    public $avatar_l_h = 96;  // 用户头像 大 高度
    public $connect_qq = true;  // 连接QQ账号 0:不连接 1:连接
    public $connect_qq_app_id = '101043103';  // QQ APP ID
    public $connect_qq_app_key = 'a58f79f168cd21815416a93826b485c5';  // QQ APP KEY
    public $connect_sina = true;  // 连接新浪微博账号 0:不连接 1:连接
    public $connect_sina_app_key = '1295333283';  // 新浪微博 App Key
    public $connect_sina_app_secret = '6ea122b52d501ba4433dc92d4fd1d806';  // 新浪微博 App Secret

    public $register_mail_activation_subject = '激活您在{site_name}注册的账号：{username}';
    public $register_mail_activation_body = '您好 {name}，<br /><br />感谢您在{site_name}注册账号。 在您正常使用前，需要激活您的账号。<br />请点击以下链接激活:<br /><a href="{activation_url}" target="_blank">{activation_url}</a><br /><br />激活后您将可以使用以下账号登陆{site_name}：<br /><br />用户名：{username}<br />密码：{password}';
  
    public $register_mail_subject = '您的账号创建成功';
    public $register_mail_body = '您好 {name},<br /><br />您的账号已创建成功。您现在可以使用用户名{username}登陆{site_name}。';

    public $register_mail_to_admin_subject = '一个新用户({username})在{site_name}注册';
    public $register_mail_to_admin_body = '您好管理员，<br /><br />一个新用户在{site_name}注册。<br />账号信息如下：<br /><br />名称：{name}<br />邮箱：{email}<br />用户名：{username}。';

    public $forgot_password_mail_subject = '找回您在{site_name}的密码';
    public $forgot_password_mail_body = '您好，<br /><br />您请求重设在{site_name}的账号密码。点击以下链接重置密码：<br /><br /><a href="{activation_url}" target="_blank">{activation_url}</a>';

    public $forgot_password_reset_mail_subject = '重设您在{site_name}上的密码成功';
    public $forgot_password_reset_mail_body = '您好,<br /><br />您的密码重设成功。您现在可以使用您的新密码登陆<a href="{site_url}" target="_blank">{site_name}</a>。';
  
    public $admin_create_account_mail_subject = '{site_name}网站的管理员为您添加了一个账号';
    public $admin_create_account_mail_body = '<div style="padding:3px;font-size:12px;"><div style="padding:3px 5px;background-color:#f90;color:#fff;">您在 {site_name} 网站上的账号信息</div><div style="padding:20px;color:#666;">{site_name} 网站的管理员为您添加了一个账号， 账号信息如下：</div><ul><li>用户名：{username}</li><li>密码：{password}</li><li>邮箱：{email}</li><li>名字：{name}</li></ul><div style="padding:20px;color:#666;">请牢记您的账号信息，点击这里访问  <a href="{site_url}" target="_blank">{site_name}</a></div><div style="padding:3px;border-top:#ddd 1px solid;font-size:10px;color:#bbb;">本邮件由系统发送给 {email}，请勿直接回复。</div></div>';

    public $remember_me_key = 'dqfCzN7DaU9ABhzrcTwunRd2ujKrZ6wdxSeh9WxZC0P0bPbT2s'; // 记住我 加密串
}
