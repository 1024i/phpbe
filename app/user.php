<?php
namespace app;

class user extends \system\app
{

	public function __construct()
	{
		parent::__construct(2, '用户', '1.0', 'template/user/images/user.gif');
	}

	// 新建前台菜单时可用的链接项
    public function get_menus()
    {
		return array(
			array(
				'name'=>'登陆页面',
				'url'=>'controller=user&task=login'
			),
			array(
				'name'=>'注册页面',
				'url'=>'controller=user&task=register'
			),
			array(
				'name'=>'找回密码页面',
				'url'=>'controller=user&task=forget_password'
			)
		);
    }

    public function get_admin_menus()
	{
		return array(
			array(
				'name'=>'用户列表',
				'url'=>'./?controller=user&task=users',
				'icon'=>'template/user/images/users.gif'
			),
			array(
				'name'=>'用户组',
				'url'=>'./?controller=user&task=groups',
				'icon'=>'template/user/images/groups.png'
			),
			array(
				'name'=>'设置',
				'url'=>'./?controller=user&task=setting',
				'icon'=>'template/user/images/setting.png'
			)
		);
	}


	public function get_permission_maps()
	{
		return array(
			'user.index'=>'-',
			'user.login'=>'-',
			'user.captcha_login'=>'-',
			'user.login_check'=>'-',
			'user.ajax_login_check'=>'-',
			'user.qq_login'=>'-',
			'user.qq_login_callback'=>'-',
			'user.sina_login'=>'-',
			'user.sina_login_callback'=>'-',
            'user.register'=>'-',
            'user.captcha_register'=>'-',
            'user.ajax_register_save'=>'-',
            'user.register_success'=>'-',
            'user.forgot_password'=>'-',
            'user.ajax_forgot_password_save'=>'-',
            'user.forgot_password_reset'=>'-',
            'user.ajax_forgot_password_reset_save'=>'-',
            'user.logout'=>'-',

            'user_profile.home'=>'-',
            'user_profile.edit_avatar'=>'-',
            'user_profile.edit_avatar_save'=>'-',
            'user_profile.init_avatar'=>'-',
            'user_profile.edit'=>'-',
            'user_profile.ajax_edit_save'=>'-',
            'user_profile.edit_password'=>'-',
            'user_profile.ajax_edit_password_save'=>'-',           
		);
	}

	public function get_admin_permissions()
	{
		return array(
			'users'=>'查看用户列表',
			'edit'=>'添加/修改用户资料',
			'delete'=>'删除用户',
			'groups'=>'管理用户组及权限',
            'setting'=>'设置用户系统参数',
		);
	}

	public function get_admin_permission_maps()
	{
		return array(
            'user.login'=>'-',
            'user.ajax_login_check'=>'-',
            'user.logout'=>'-',

			'user.users'=>'users',
			'user.edit'=>'edit',
			'user.edit_save'=>'edit',
            'user.check_username'=>'edit',
            'user.check_email'=>'edit',
			'user.unblock'=>'edit',
			'user.block'=>'edit',
            'user.ajax_init_avatar'=>'edit',
			'user.delete'=>'delete',
            
            'user.groups'=>'groups',
            'user.groups_save'=>'groups',
            'user.ajax_group_set_default'=>'groups',
            'user.ajax_group_delete'=>'groups',
            'user.group_permissions'=>'groups',
            'user.group_permissions_save'=>'groups',

			'user.setting'=>'setting',
			'user.setting_save'=>'setting'
		);
	}
    

	public function get_db_tables()
	{
		return array('be_user', 'be_user_admin_log') ;
	}


	public function install_db()
	{
		db::execute('
CREATE TABLE IF NOT EXISTS `be_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(120) NOT NULL,
  `password` char(32) NOT NULL,
  `key` char(32) NOT NULL,
  `avatar` varchar(60) NOT NULL,
  `avatar_s` varchar(60) NOT NULL,
  `avatar_l` varchar(60) NOT NULL,
  `email` varchar(120) NOT NULL,
  `name` varchar(120) NOT NULL,
  `is_admin` tinyint(1) NOT NULL,
  `block` tinyint(1) NOT NULL,
  `register_time` int(11) NOT NULL,
  `last_visit_time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8
		');

		db::execute('
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


		db::execute("
INSERT INTO `be_user` (`id`, `username`, `password`, `key`, `avatar`, `avatar_s`, `avatar_l`, `email`, `name`, `is_admin`, `block`, `register_time`, `last_visit_time`) VALUES
(1, 'admin', '52341be594af95eb94323a23ce48a010', '', 'template/user/images/avatar.png', 'template/user/images/avatar_s.png', 'template/user/images/avatar_l.png', '', '管理员', 1, 0, 946656000, 946656000);
		");


		db::execute("
CREATE TABLE IF NOT EXISTS `be_user_connect_qq` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `access_token` varchar(60) NOT NULL,
  `openid` varchar(60) NOT NULL,
  `nickname` varchar(60) NOT NULL,
  `figureurl` varchar(120) NOT NULL,
  `figureurl_1` varchar(120) NOT NULL,
  `figureurl_2` varchar(120) NOT NULL,
  `figureurl_qq_1` varchar(120) NOT NULL,
  `figureurl_qq_2` varchar(120) NOT NULL,
  `gender` varchar(4) NOT NULL,
  `is_yellow_vip` int(11) NOT NULL,
  `vip` int(11) NOT NULL,
  `yellow_vip_level` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
		");

		db::execute("
CREATE TABLE IF NOT EXISTS `be_user_connect_sina` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `access_token` varchar(60) NOT NULL,
  `uid` varchar(30) NOT NULL,
  `screen_name` varchar(60) NOT NULL,
  `name` varchar(60) NOT NULL,
  `province` int(11) NOT NULL,
  `city` int(11) NOT NULL,
  `location` varchar(120) NOT NULL,
  `description` varchar(240) NOT NULL,
  `url` varchar(120) NOT NULL,
  `profile_image_url` varchar(120) NOT NULL,
  `profile_url` varchar(120) NOT NULL,
  `domain` varchar(60) NOT NULL,
  `weihao` varchar(60) NOT NULL,
  `gender` varchar(4) NOT NULL,
  `followers_count` int(11) NOT NULL,
  `friends_count` int(11) NOT NULL,
  `statuses_count` int(11) NOT NULL,
  `favourites_count` int(11) NOT NULL,
  `created_at` varchar(30) NOT NULL,
  `following` tinyint(4) NOT NULL,
  `allow_all_act_msg` tinyint(4) NOT NULL,
  `geo_enabled` tinyint(4) NOT NULL,
  `verified` tinyint(4) NOT NULL,
  `verified_type` tinyint(4) NOT NULL,
  `remark` varchar(240) NOT NULL,
  `allow_all_comment` tinyint(4) NOT NULL,
  `avatar_large` varchar(120) NOT NULL,
  `avatar_hd` varchar(120) NOT NULL,
  `verified_reason` varchar(60) NOT NULL,
  `follow_me` tinyint(4) NOT NULL,
  `online_status` tinyint(4) NOT NULL,
  `bi_followers_count` int(11) NOT NULL,
  `lang` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
		");

	}



	public function uninstall()
	{
		$this->set_error('系统基本应用，不可删除');
		return false;
	}

}
?>