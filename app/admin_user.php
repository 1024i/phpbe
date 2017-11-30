<?php
namespace app;

class admin_user extends \system\app
{

	public function __construct()
	{
		parent::__construct(2, '管理员', '1.0', 'template/admin_user/images/user.gif');
	}


    public function get_admin_menus()
	{
		return [
			[
				'name'=>'管理员列表',
				'url'=>'./?controller=admin_user&task=users',
				'icon'=>'template/admin_user/images/users.gif'
			],
			[
				'name'=>'管理员角色',
				'url'=>'./?controller=admin_user&task=roles',
				'icon'=>'template/admin_user/images/roles.png'
			],
            [
                'name'=>'设置',
                'url'=>'./?controller=admin_user&task=setting',
                'icon'=>'template/admin_user/images/setting.png'
            ],
            [
                'name'=>'登陆日志',
                'url'=>'./?controller=admin_user&task=logs',
                'icon'=>'template/admin_user/images/logs.gif'
            ]
		];
	}

	public function get_admin_permissions()
	{
		return [
		    '-' => [
                'admin_user.login',
                'admin_user.ajax_login_check',
                'admin_user.logout',
            ],
			'查看管理员列表' => [
			    'admin_user.users',
            ],
			'添加/修改管理员资料' => [
                'admin_user.edit',
                'admin_user.edit_save',
                'admin_user.check_username',
                'admin_user.check_email',
                'admin_user.unblock',
                'admin_user.block',
                'admin_user.ajax_init_avatar',
            ],
			'删除管理员' => [
			    'admin_user.delete',
            ],
			'角色管理' => [
                'admin_user.roles',
                'admin_user.roles_save',
                'admin_user.ajax_delete_role',
                'admin_user.role_permissions',
                'admin_user.role_permissions_save',
            ],
            '查看登陆日志' => [
                'admin_user.logs',
            ],
            '删除登陆日志' => [
                'admin_user.ajax_delete_logs',
            ]
		];
	}

}
