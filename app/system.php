<?php
namespace app;

use system\be;

class system extends \system\app
{

	public function __construct()
	{
		parent::__construct(0, '系统', '1.0', 'template/system/images/system.png');
	}


    public function get_menus()
    {
		return array(
			array(
				'name'=>'用户使用条款',
				'url'=>'controller=system&task=terms_and_conditions'
			),
			array(
				'name'=>'隐私保护',
				'url'=>'controller=system&task=privacy_policy'
			)
		);
    }
    
    /**
     * 后台菜单项
     */
    public function get_admin_menus()
	{
		return array(
			array(
                'name'=>'公告',
                'url'=>'./?controller=system_announcement&task=announcements',
                'icon'=>'template/system_announcement/images/announcement.png'
            ),
			array(
				'name'=>'友情链接',
				'url'=>'./?controller=system_link&task=links',
				'icon'=>'template/system_link/images/link.png'
			),
			array(
				'name'=>'自定义模块',
				'url'=>'./?controller=system_html&task=htmls',
				'icon'=>'template/system_html/images/html.png'
			)
		);
	}
	

	public function get_permissions()
	{
		return [
		    '-' => [
                'system.terms_and_conditions',
                'system.privacy_policy',
                'system.ajax_change_language',
            ]
        ];
	}
    
	public function get_admin_permissions()
	{
		return [
		    '-' => [
		        'system.dashboard',
                'system.history_back',
            ],
            '管理菜单' => [
                'system.menus',
                'system.menus_save',
                'system.ajax_menu_delete',
                'system.menu_set_link',
                'system.ajax_menu_set_home',
            ],
            '管理菜单分组' => [
                'system.menu_groups',
                'system.menu_group_edit',
                'system.menu_group_edit_save',
                'system.menu_group_delete',
            ],
            '管理应用' => [
                'system.apps',
                'system.ajax_uninstall_app',
                'system.remote_apps',
                'system.ajax_install_app',
            ],
            '管理主题' => [
                'system.themes',
                'system.ajax_theme_set_default',
                'system.remote_themes',
                'system.ajax_install_theme',
                'system.ajax_uninstall_theme',
            ],
            '配置系统参数' => [
                'system.config',
                'system.config_save',
            ],
            '配置邮件参数' => [
                'system.config_mail',
                'system.config_mail_save',
                'system.config_mail_test',
                'system.config_mail_test_save',
            ],
            '水印设置' => [
                'system.config_watermark',
                'system.config_watermark_save',
                'system.config_watermark_test',
            ],
            '查看系统日志' => [
                'system.logs',
            ],
            '删除系统日志' => [
                'system.ajax_delete_logs',
            ],
            '文件管理器：查看已上传的文件' => [
                'system_filemanager.browser',
            ],
            '文件管理器：上传文件' => [
                'system_filemanager.create_dir',
                'system_filemanager.upload_file',
            ],
            '文件管理器：删除文件或文件夹' => [
                'system_filemanager.delete_dir',
                'system_filemanager.delete_file',
            ],
            '文件管理器：重命名文件或文件夹' => [
                'system_filemanager.edit_dir_name',
                'system_filemanager.edit_file_name',
            ],
            '文件管理器：下载文件' => [
                'system_filemanager.download_file',
            ],
            '管理公告' => [
                'system_announcement.announcements',
                'system_announcement.edit',
                'system_announcement.edit_save',
                'system_announcement.unblock',
                'system_announcement.block',
                'system_announcement.delete',
            ],
            '管理友情链接' => [
                'system_link.links',
                'system_link.edit',
                'system_link.edit_save',
                'system_link.unblock',
                'system_link.block',
                'system_link.delete',
            ],
            '管理自定义模块' => [
                'system_html.htmls',
                'system_html.edit',
                'system_html.edit_save',
                'system_html.unblock',
                'system_html.block',
                'system_html.delete'
            ],
		];
	}

	public function get_db_tables()
	{
		return array('be_system_log', 'be_system_menu', 'be_system_menu_group');
	}
    
	public function install_db()
	{
        $db = be::get_db();

		$db->execute('
CREATE TABLE IF NOT EXISTS `be_system_menu_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `class_name` varchar(120) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8
		');


		$db->execute("
INSERT INTO `be_menu_group` (`id`, `name`, `class_name`) VALUES
(1, '顶部菜单', 'north'),
(2, '底部菜单', 'south')
		");

		$db->execute('
CREATE TABLE IF NOT EXISTS `be_system_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `url` varchar(240) NOT NULL,
  `target` varchar(7) NOT NULL,
  `params` varchar(240) NOT NULL,
  `home` tinyint(1) NOT NULL,
  `block` tinyint(1) NOT NULL,
  `rank` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8
		');

		$db->execute("
INSERT INTO `be_system_menu` (`id`, `group_id`, `parent_id`, `name`, `url`, `target`, `params`, `home`, `block`, `rank`) VALUES
(1, 1, 0, '首页', 'controller=article&task=detail&id=1', '_self', '', 1, 0, 0)
		");



		$db->execute('
CREATE TABLE IF NOT EXISTS `be_system_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(240) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `create_time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8
		');

	}


	public function uninstall()
	{
		$this->set_error('不可删除');
		return false;
	}

}

?>