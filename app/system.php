<?php
namespace app;

use Phpbe\System\Be;

class system extends \system\app
{

	public function __construct()
	{
		parent::__construct(0, '系统', '1.0', 'template/system/images/system.png');
	}


    public function getMenus()
    {
		return array(
			array(
				'name'=>'用户使用条款',
				'url'=>'controller=system&action=termsAndConditions'
			),
			array(
				'name'=>'隐私保护',
				'url'=>'controller=system&action=privacyPolicy'
			)
		);
    }
    
    /**
     * 后台菜单项
     */
    public function getAdminMenus()
	{
		return array(
			array(
                'name'=>'公告',
                'url'=>'./?controller=systemAnnouncement&action=announcements',
                'icon'=>'template/systemAnnouncement/images/announcement.png'
            ),
			array(
				'name'=>'友情链接',
				'url'=>'./?controller=systemLink&action=links',
				'icon'=>'template/systemLink/images/link.png'
			),
			array(
				'name'=>'自定义模块',
				'url'=>'./?controller=systemHtml&action=htmls',
				'icon'=>'template/systemHtml/images/html.png'
			)
		);
	}
	

	public function getPermissions()
	{
		return [
		    '-' => [
                'system.termsAndConditions',
                'system.privacyPolicy',
                'system.ajaxChangeLanguage',
            ]
        ];
	}
    
	public function getAdminPermissions()
	{
		return [
		    '-' => [
		        'system.dashboard',
                'system.historyBack',
            ],
            '管理菜单' => [
                'system.menus',
                'system.menusSave',
                'system.ajaxMenuDelete',
                'system.menuSetLink',
                'system.ajaxMenuSetHome',
            ],
            '管理菜单分组' => [
                'system.menuGroups',
                'system.menuGroupEdit',
                'system.menuGroupEditSave',
                'system.menuGroupDelete',
            ],
            '管理应用' => [
                'system.apps',
                'system.ajaxUninstallApp',
                'system.remoteApps',
                'system.ajaxInstallApp',
            ],
            '管理主题' => [
                'system.themes',
                'system.ajaxThemeSetDefault',
                'system.remoteThemes',
                'system.ajaxInstallTheme',
                'system.ajaxUninstallTheme',
            ],
            '配置系统参数' => [
                'system.config',
                'system.configSave',
            ],
            '配置邮件参数' => [
                'system.configMail',
                'system.configMailSave',
                'system.configMailTest',
                'system.configMailTestSave',
            ],
            '水印设置' => [
                'system.configWatermark',
                'system.configWatermarkSave',
                'system.configWatermarkTest',
            ],
            '查看系统日志' => [
                'system.logs',
            ],
            '删除系统日志' => [
                'system.ajaxDeleteLogs',
            ],
            '文件管理器：查看已上传的文件' => [
                'systemFilemanager.browser',
            ],
            '文件管理器：上传文件' => [
                'systemFilemanager.createDir',
                'systemFilemanager.uploadFile',
            ],
            '文件管理器：删除文件或文件夹' => [
                'systemFilemanager.deleteDir',
                'systemFilemanager.deleteFile',
            ],
            '文件管理器：重命名文件或文件夹' => [
                'systemFilemanager.editDirName',
                'systemFilemanager.editFileName',
            ],
            '文件管理器：下载文件' => [
                'systemFilemanager.downloadFile',
            ],
            '管理公告' => [
                'systemAnnouncement.announcements',
                'systemAnnouncement.edit',
                'systemAnnouncement.editSave',
                'systemAnnouncement.unblock',
                'systemAnnouncement.block',
                'systemAnnouncement.delete',
            ],
            '管理友情链接' => [
                'systemLink.links',
                'systemLink.edit',
                'systemLink.editSave',
                'systemLink.unblock',
                'systemLink.block',
                'systemLink.delete',
            ],
            '管理自定义模块' => [
                'systemHtml.htmls',
                'systemHtml.edit',
                'systemHtml.editSave',
                'systemHtml.unblock',
                'systemHtml.block',
                'systemHtml.delete'
            ],
		];
	}

	public function getDbTables()
	{
		return array('beSystemLog', 'beSystemMenu', 'beSystemMenuGroup');
	}
    
	public function installDb()
	{
        $db = Be::getDb();

		$db->execute('
CREATE TABLE IF NOT EXISTS `beSystemMenuGroup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `className` varchar(120) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8
		');


		$db->execute("
INSERT INTO `beMenuGroup` (`id`, `name`, `className`) VALUES
(1, '顶部菜单', 'north'),
(2, '底部菜单', 'south')
		");

		$db->execute('
CREATE TABLE IF NOT EXISTS `beSystemMenu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupId` int(11) NOT NULL,
  `parentId` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `url` varchar(240) NOT NULL,
  `target` varchar(7) NOT NULL,
  `params` varchar(240) NOT NULL,
  `home` tinyint(1) NOT NULL,
  `block` tinyint(1) NOT NULL,
  `ordering` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8
		');

		$db->execute("
INSERT INTO `beSystemMenu` (`id`, `groupId`, `parentId`, `name`, `url`, `target`, `params`, `home`, `block`, `ordering`) VALUES
(1, 1, 0, '首页', 'controller=article&action=detail&id=1', 'Self', '', 1, 0, 0)
		");



		$db->execute('
CREATE TABLE IF NOT EXISTS `beSystemLog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `title` varchar(240) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `createTime` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8
		');

	}


	public function uninstall()
	{
		$this->setError('不可删除');
		return false;
	}

}

