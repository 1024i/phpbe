<?php
namespace admin\template\admin_user;

class groups extends groups_tab
{
	
	protected function head()
	{
		parent::head();
		?>
<script type="text/javascript" language="javascript" src="./template/user/js/groups.js"></script>
		<?php
	}

	protected function tab_content()
	{
    	$groups = $this->get('groups');

		$admin_ui_category = be::get_admin_ui('category');
		$admin_ui_category->set_action('save', './?controller=user&task=groups_save');
		$admin_ui_category->set_action('delete', './?controller=user&task=ajax_group_delete');
		
		foreach ($groups as $group) {
			if ($group->id == 1) {
				$group->html_default = '';
				$group->html_user_count = '<span class="user_count"></span>';
			} else {
				$group->html_default = '<a href="javascript:;" onclick="javascript:setDefault(this, '.$group->id.');" class="icon icon-default icon-default-'.$group->default.'"></a>';
				$group->html_user_count = '<span class="badge'.($group->user_count>0?' badge-success user_count':'').'">'.$group->user_count.'</span>';
			}
		}
		
		$admin_ui_category->set_data($groups);
		$admin_ui_category->set_fields(
			array(
    			'name'=>'note',
    			'label'=>'备注',
    			'align'=>'left',
    		    'width'=>'320',
				'template'=>'<input type="text" name="note[]" value="{note}" style="width:300px;">',
				'default'=>'<input type="text" name="note[]" value="" style="width:300px;">'
    		),
			array(
    			'name'=>'html_default',
    			'label'=>'默认分组',
    			'align'=>'center',
    		    'width'=>'120',
    		),
			array(
    			'name'=>'html_user_count',
    			'label'=>'用户数',
    			'align'=>'center',
    		    'width'=>'80',
    		),
			array(
    			'name'=>'note',
    			'label'=>'权限管理',
    			'align'=>'center',
    		    'width'=>'180',
				'template'=>'<a class="btn btn-small btn-success" href="./?controller=user&task=group_permissions&group_id={id}">权限管理</a>'
    		)
		);
		
		$admin_ui_category->display();

	}	

}
?>