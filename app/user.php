<?php

namespace app;

use Phpbe\System\Be;

class user extends \system\app
{

    public function __construct()
    {
        parent::__construct(2, '用户', '1.0', 'template/user/images/user.gif');
    }

    // 新建前台菜单时可用的链接项
    public function getMenus()
    {
        return array(
            array(
                'name' => '登陆页面',
                'url' => 'controller=user&task=login'
            ),
            array(
                'name' => '注册页面',
                'url' => 'controller=user&task=register'
            ),
            array(
                'name' => '找回密码页面',
                'url' => 'controller=user&task=forgetPassword'
            )
        );
    }

    public function getAdminMenus()
    {
        return array(
            array(
                'name' => '用户列表',
                'url' => './?controller=user&task=users',
                'icon' => 'template/user/images/users.gif'
            ),
            array(
                'name' => '用户角色',
                'url' => './?controller=user&task=roles',
                'icon' => 'template/user/images/roles.png'
            ),
            array(
                'name' => '设置',
                'url' => './?controller=user&task=setting',
                'icon' => 'template/user/images/setting.png'
            )
        );
    }


    public function getPermissions()
    {
        return [
            '-' => [
                'user.index',
                'user.login',
                'user.captchaLogin',
                'user.loginCheck',
                'user.ajaxLoginCheck',
                'user.qqLogin',
                'user.qqLoginCallback',
                'user.sinaLogin',
                'user.sinaLoginCallback',
                'user.register',
                'user.captchaRegister',
                'user.ajaxRegisterSave',
                'user.registerSuccess',
                'user.forgotPassword',
                'user.ajaxForgotPasswordSave',
                'user.forgotPasswordReset',
                'user.ajaxForgotPasswordResetSave',
                'user.logout',

                'userProfile.home',
                'userProfile.editAvatar',
                'userProfile.editAvatarSave',
                'userProfile.initAvatar',
                'userProfile.edit',
                'userProfile.ajaxEditSave',
                'userProfile.editPassword',
                'userProfile.ajaxEditPasswordSave',
            ]
        ];
    }

    public function getAdminPermissions()
    {
        return [
            '-' => [
                'user.login',
                'user.ajaxLoginCheck',
                'user.logout',
            ],
            '查看用户列表' => [
                'user.users',
            ],
            '添加/修改用户资料' => [
                'user.edit',
                'user.editSave',
                'user.checkUsername',
                'user.checkEmail',
                'user.unblock',
                'user.block',
                'user.ajaxInitAvatar',
            ],
            '删除用户' => [
                'user.delete',
            ],
            '管理用户组及权限' => [
                'user.roles',
                'user.rolesSave',
                'user.ajaxSetDefaultRole',
                'user.ajaxDeleteRole',
                'user.rolePermissions',
                'user.rolePermissionsSave',
            ],
            '设置用户系统参数' => [
                'user.setting',
                'user.settingSave',
            ],
        ];
    }


    public function getDbTables()
    {
        return array('beUser', 'beUserAdminLog');
    }


    public function installDb()
    {
        $db = Be::getDb();
        $db->execute('
CREATE TABLE IF NOT EXISTS `be_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(120) NOT NULL,
  `password` char(32) NOT NULL,
  `key` char(32) NOT NULL,
  `avatar` varchar(60) NOT NULL,
  `avatarS` varchar(60) NOT NULL,
  `avatarL` varchar(60) NOT NULL,
  `email` varchar(120) NOT NULL,
  `name` varchar(120) NOT NULL,
  `isAdmin` tinyint(1) NOT NULL,
  `block` tinyint(1) NOT NULL,
  `registerTime` int(11) NOT NULL,
  `lastVisitTime` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8
		');

        $db->execute('
CREATE TABLE IF NOT EXISTS `be_user_admin_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(120) NOT NULL,
  `success` tinyint(1) NOT NULL,
  `description` varchar(240) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
		');


        $db->execute("
INSERT INTO `be_user` (`id`, `username`, `password`, `key`, `avatar`, `avatarS`, `avatarL`, `email`, `name`, `isAdmin`, `block`, `registerTime`, `lastVisitTime`) VALUES
(1, 'admin', '52341be594af95eb94323a23ce48a010', '', 'template/user/images/avatar.png', 'template/user/images/avatarS.png', 'template/user/images/avatarL.png', '', '管理员', 1, 0, 946656000, 946656000);
		");


        $db->execute("
CREATE TABLE IF NOT EXISTS `be_user_connect_qq` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `accessToken` varchar(60) NOT NULL,
  `openid` varchar(60) NOT NULL,
  `nickname` varchar(60) NOT NULL,
  `figureurl` varchar(120) NOT NULL,
  `figureurl_1` varchar(120) NOT NULL,
  `figureurl_2` varchar(120) NOT NULL,
  `figureurlQq_1` varchar(120) NOT NULL,
  `figureurlQq_2` varchar(120) NOT NULL,
  `gender` varchar(4) NOT NULL,
  `isYellowVip` int(11) NOT NULL,
  `vip` int(11) NOT NULL,
  `yellowVipLevel` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
		");

        $db->execute("
CREATE TABLE IF NOT EXISTS `be_user_connect_sina` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `accessToken` varchar(60) NOT NULL,
  `uid` varchar(30) NOT NULL,
  `screenName` varchar(60) NOT NULL,
  `name` varchar(60) NOT NULL,
  `province` int(11) NOT NULL,
  `city` int(11) NOT NULL,
  `location` varchar(120) NOT NULL,
  `description` varchar(240) NOT NULL,
  `url` varchar(120) NOT NULL,
  `profileImageUrl` varchar(120) NOT NULL,
  `profileUrl` varchar(120) NOT NULL,
  `domain` varchar(60) NOT NULL,
  `weihao` varchar(60) NOT NULL,
  `gender` varchar(4) NOT NULL,
  `followersCount` int(11) NOT NULL,
  `friendsCount` int(11) NOT NULL,
  `statusesCount` int(11) NOT NULL,
  `favouritesCount` int(11) NOT NULL,
  `createdAt` varchar(30) NOT NULL,
  `following` tinyint(4) NOT NULL,
  `allowAllActMsg` tinyint(4) NOT NULL,
  `geoEnabled` tinyint(4) NOT NULL,
  `verified` tinyint(4) NOT NULL,
  `verifiedType` tinyint(4) NOT NULL,
  `remark` varchar(240) NOT NULL,
  `allowAllComment` tinyint(4) NOT NULL,
  `avatarLarge` varchar(120) NOT NULL,
  `avatarHd` varchar(120) NOT NULL,
  `verifiedReason` varchar(60) NOT NULL,
  `followMe` tinyint(4) NOT NULL,
  `onlineStatus` tinyint(4) NOT NULL,
  `biFollowersCount` int(11) NOT NULL,
  `lang` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
		");

    }


    public function uninstall()
    {
        $this->setError('系统基本应用，不可删除');
        return false;
    }

    public function installConfig() {

        $this->addConfig('是否开启注册功能', 'register', true, 'bool');



    }
}
