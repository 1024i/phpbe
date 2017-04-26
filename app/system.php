<?php
namespace app;

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
	

	public function get_permission_maps()
	{
		return array(
			'system.terms_and_conditions'=>'-',
			'system.privacy_policy'=>'-',
			'system.ajax_change_language'=>'-'
		);
	}
    
	public function get_admin_permissions()
	{
		return array(
			'menus'=>'管理菜单',
			'menu_groups'=>'管理菜单分组',
			'apps'=>'管理应用',
			'themes'=>'管理主题',
			'config'=>'配置系统参数',
			'config_mail'=>'配置邮件参数',
			'config_watermark'=>'水印设置',
			'logs'=>'查看系统日志',
			'delete_logs'=>'删除系统日志',
			'browser_file'=>'文件管理器：查看已上传的文件',
			'upload_file'=>'文件管理器：上传文件',
			'delete_file'=>'文件管理器：删除文件或文件夹',
			'edit_file'=>'文件管理器：重命名文件或文件夹',
			'download_file'=>'文件管理器：下载文件',
			'announcements'=>'管理公告',
			'links'=>'管理友情链接',
			'htmls'=>'管理自定义模块'
		);
	}

	public function get_admin_permission_maps()
	{
		return array(
            
            'system.dashboard'=>'-',
            
			'system.menus'=>'menus',
            'system.menus_save'=>'menus',
            'system.ajax_menu_delete'=>'menus',
            'system.menu_set_link'=>'menus',
            'system.ajax_menu_set_home'=>'menus',

			'system.menu_groups'=>'menu_groups',
            'system.menu_group_edit'=>'menu_groups',
            'system.menu_group_edit_save'=>'menu_groups',
            'system.menu_group_delete'=>'menu_groups',
            
			'system.apps'=>'apps',
            'system.ajax_uninstall_app'=>'apps',
            'system.remote_apps'=>'apps',
            'system.ajax_install_app'=>'apps',
            
			'system.themes'=>'themes',
            'system.ajax_theme_set_default'=>'themes',
            'system.remote_themes'=>'themes',
            'system.ajax_install_theme'=>'themes',
            'system.ajax_uninstall_theme'=>'themes',
            
			'system.config'=>'config',
            'system.config_save'=>'config',
            
			'system.config_mail'=>'config_mail',
            'system.config_mail_save'=>'config_mail',
            'system.config_mail_test'=>'config_mail',
            'system.config_mail_test_save'=>'config_mail',
              
			'system.config_watermark'=>'config_watermark',
            'system.config_watermark_save'=>'config_watermark',
            'system.config_watermark_test'=>'config_watermark',

			'system.logs'=>'logs',
			'system.ajax_delete_logs'=>'delete_logs',
            
            'system.history_back'=>'-',
            
            'system.ajax_change_language'=>'-',
            
            'system_filemanager.browser'=>'browser_file',
            'system_filemanager.create_dir'=>'upload_file',
            'system_filemanager.delete_dir'=>'delete_file',
            'system_filemanager.edit_dir_name'=>'edit_file',
            'system_filemanager.upload_file'=>'upload_file',
            'system_filemanager.delete_file'=>'delete_file',
            'system_filemanager.edit_file_name'=>'edit_file',
            'system_filemanager.download_file'=>'download_file',
            
            'system_announcement.announcements'=>'announcements',
            'system_announcement.edit'=>'announcements',
            'system_announcement.edit_save'=>'announcements',
            'system_announcement.unblock'=>'announcements',
            'system_announcement.block'=>'announcements',
            'system_announcement.delete'=>'announcements',
            
            'system_link.links'=>'links',
            'system_link.edit'=>'links',
            'system_link.edit_save'=>'links',
            'system_link.unblock'=>'links',
            'system_link.block'=>'links',
            'system_link.delete'=>'links',
            
            'system_html.htmls'=>'htmls',
            'system_html.edit'=>'htmls',
            'system_html.edit_save'=>'htmls',
            'system_html.unblock'=>'htmls',
            'system_html.block'=>'htmls',
            'system_html.delete'=>'htmls'
		);
	}

	public function get_db_tables()
	{
		return array('be_system_log', 'be_system_menu', 'be_system_menu_group');
	}
    
	public function install_db()
	{

		db::execute('
CREATE TABLE IF NOT EXISTS `be_system_menu_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `class_name` varchar(120) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8
		');


		db::execute("
INSERT INTO `be_menu_group` (`id`, `name`, `class_name`) VALUES
(1, '顶部菜单', 'north'),
(2, '底部菜单', 'south')
		");

		db::execute('
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

		db::execute("
INSERT INTO `be_system_menu` (`id`, `group_id`, `parent_id`, `name`, `url`, `target`, `params`, `home`, `block`, `rank`) VALUES
(1, 1, 0, '首页', 'controller=article&task=detail&id=1', '_self', '', 1, 0, 0)
		");



		db::execute('
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