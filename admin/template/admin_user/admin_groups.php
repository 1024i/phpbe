<?php
namespace admin\template\admin_user;

class admin_groups extends groups_tab
{
	
	protected function head()
	{
		parent::head();
		?>
<script type="text/javascript" language="javascript" src="./template/user/js/admin_groups.js"></script>
		<?php
	}

	protected function tab_content()
	{
    	$admin_groups = $this->get('admin_groups');

		$admin_ui_category = be::get_admin_ui('category');
		$admin_ui_category->set_action('save', './?controller=user&task=admin_groups_save');
		$admin_ui_category->set_action('delete', './?controller=user&task=ajax_admin_group_delete');
		
		foreach ($admin_groups as $group) {
			$group->html_user_count = '<span class="badge'.($group->user_count>0?' badge-success user_count':'').'">'.$group->user_count.'</span>';
			$group->html_permission = '<a href="./?controller=user&task=admin_group_permissions&group_id='.$group->id.'" class="btn btn-small btn-success">权限管理</a>';
			
			if ($group->id == 1) {
				$group->html_permission = '';
			}
		}
		
		$admin_ui_category->set_data($admin_groups);
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
    			'name'=>'html_user_count',
    			'label'=>'用户数',
    			'align'=>'center',
    		    'width'=>'80',
    		),
			array(
    			'name'=>'html_permission',
    			'label'=>'权限管理',
    			'align'=>'center',
    		    'width'=>'180'
    		)
		);
		
		$admin_ui_category->display();

	}	

}
?>