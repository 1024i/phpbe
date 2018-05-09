<?php
namespace App;

class AdminUser extends \Phpbe\System\App
{

	public function __construct()
	{
		parent::__construct(2, '管理员', '1.0', 'template/adminUser/images/user.gif');
	}


    public function getAdminMenus()
	{
		return [
			[
				'name'=>'管理员列表',
				'url'=>'./?controller=AdminUser&task=users',
				'icon'=>'template/adminUser/images/users.gif'
			],
			[
				'name'=>'管理员角色',
				'url'=>'./?controller=AdminUser&task=roles',
				'icon'=>'template/adminUser/images/roles.png'
			],
            [
                'name'=>'设置',
                'url'=>'./?controller=AdminUser&task=setting',
                'icon'=>'template/adminUser/images/setting.png'
            ],
            [
                'name'=>'登陆日志',
                'url'=>'./?controller=AdminUser&task=logs',
                'icon'=>'template/adminUser/images/logs.gif'
            ]
		];
	}

	public function getAdminPermissions()
	{
		return [
		    '-' => [
                'adminUser.login',
                'adminUser.ajaxLoginCheck',
                'adminUser.logout',
            ],
			'查看管理员列表' => [
			    'adminUser.users',
            ],
			'添加/修改管理员资料' => [
                'adminUser.edit',
                'adminUser.editSave',
                'adminUser.checkUsername',
                'adminUser.checkEmail',
                'adminUser.unblock',
                'adminUser.block',
                'adminUser.ajaxInitAvatar',
            ],
			'删除管理员' => [
			    'adminUser.delete',
            ],
			'角色管理' => [
                'adminUser.roles',
                'adminUser.rolesSave',
                'adminUser.ajaxDeleteRole',
                'adminUser.rolePermissions',
                'adminUser.rolePermissionsSave',
            ],
            '查看登陆日志' => [
                'adminUser.logs',
            ],
            '删除登陆日志' => [
                'adminUser.ajaxDeleteLogs',
            ]
		];
	}

}
