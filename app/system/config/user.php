<?php
namespace App\System\Config;

class User
{
    public $register = true;  // 是否开启注册功能
    public $captchaLogin = false;  // 是否开启登陆验证码
    public $captchaRegister = true;  // 是否开启注册验证码
    public $emailValid = false;  // 是否发送激活链接验证用户邮箱
    public $emailRegister = false;  // 注册成功后是否发送提示邮件
    public $emailRegisterAdmin = '1024i@gmail.com';  // 新用户注册后给管理员发送邮件
    public $defaultAvatarS = '0_s.png';  // 用户默认头像 小 small
    public $defaultAvatarM = '0_m.png';  // 用户默认头像 中 medium
    public $defaultAvatarL = '0_l.png';  // 用户默认头像 大 large
    public $avatarSW = 32;  // 用户头像 小 长度
    public $avatarSH = 32;  // 用户头像 小 高度
    public $avatarMW = 64;  // 用户头像 中 长度
    public $avatarMH = 64;  // 用户头像 中 高度
    public $avatarLW = 96;  // 用户头像 大 长度
    public $avatarLH = 96;  // 用户头像 大 高度
    public $connectQq = true;  // 连接QQ账号 0:不连接 1:连接
    public $connectQqAppId = '101043103';  // QQ APP ID
    public $connectQqAppKey = 'a58f79f168cd21815416a93826b485c5';  // QQ APP KEY
    public $connectSina = true;  // 连接新浪微博账号 0:不连接 1:连接
    public $connectSinaAppKey = '1295333283';  // 新浪微博 App Key
    public $connectSinaAppSecret = '6ea122b52d501ba4433dc92d4fd1d806';  // 新浪微博 App Secret

    public $registerMailActivationSubject = '激活您在{siteName}注册的账号：{username}';
    public $registerMailActivationBody = '您好 {name}，<br /><br />感谢您在{siteName}注册账号。 在您正常使用前，需要激活您的账号。<br />请点击以下链接激活:<br /><a href="{activationUrl}" target="Blank">{activationUrl}</a><br /><br />激活后您将可以使用以下账号登陆{siteName}：<br /><br />用户名：{username}<br />密码：{password}';
  
    public $registerMailSubject = '您的账号创建成功';
    public $registerMailBody = '您好 {name},<br /><br />您的账号已创建成功。您现在可以使用用户名{username}登陆{siteName}。';

    public $registerMailToAdminSubject = '一个新用户({username})在{siteName}注册';
    public $registerMailToAdminBody = '您好管理员，<br /><br />一个新用户在{siteName}注册。<br />账号信息如下：<br /><br />名称：{name}<br />邮箱：{email}<br />用户名：{username}。';

    public $forgotPasswordMailSubject = '找回您在{siteName}的密码';
    public $forgotPasswordMailBody = '您好，<br /><br />您请求重设在{siteName}的账号密码。点击以下链接重置密码：<br /><br /><a href="{activationUrl}" target="Blank">{activationUrl}</a>';

    public $forgotPasswordResetMailSubject = '重设您在{siteName}上的密码成功';
    public $forgotPasswordResetMailBody = '您好,<br /><br />您的密码重设成功。您现在可以使用您的新密码登陆<a href="{siteUrl}" target="Blank">{siteName}</a>。';
  
    public $adminCreateAccountMailSubject = '{siteName}网站的管理员为您添加了一个账号';
    public $adminCreateAccountMailBody = '<div style="padding:3px;font-size:12px;"><div style="padding:3px 5px;background-color:#f90;color:#fff;">您在 {siteName} 网站上的账号信息</div><div style="padding:20px;color:#666;">{siteName} 网站的管理员为您添加了一个账号， 账号信息如下：</div><ul><li>用户名：{username}</li><li>密码：{password}</li><li>邮箱：{email}</li><li>名字：{name}</li></ul><div style="padding:20px;color:#666;">请牢记您的账号信息，点击这里访问  <a href="{siteUrl}" target="Blank">{siteName}</a></div><div style="padding:3px;border-top:#ddd 1px solid;font-size:10px;color:#bbb;">本邮件由系统发送给 {email}，请勿直接回复。</div></div>';

    public $rememberMeKey = 'dqfCzN7DaU9ABhzrcTwunRd2ujKrZ6wdxSeh9WxZC0P0bPbT2s'; // 记住我 加密串
}
