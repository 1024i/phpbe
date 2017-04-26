<?php
namespace admin\template\admin_user;

class groups_tab extends \admin\theme
{

	protected function head()
	{
		parent::head();
		?>
<link rel="stylesheet" type="text/css" href="./template/user/css/groups_tab.css" />
		<?php
	}

	protected function center()
	{
		$tab = $this->get('tab', 'frontend');
		?>
		<ul class="nav nav-tabs">
			<li<?php if ($tab == 'frontend') echo ' class="active"'; ?>><a href="./?controller=user&task=groups">前台用户组</a></li>
			<li<?php if ($tab == 'backend') echo ' class="active"'; ?>><a href="./?controller=user&task=admin_groups">后台用户组</a></li>
		</ul>
		<?php
		
		$this->tab_content();
	}
	
	
	protected function tab_content(){}
	
	
}
?>