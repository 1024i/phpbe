<?php
namespace admin\template\admin_user;

class admin_group_permissions extends \admin\theme
{
	protected function head()
	{
		parent::head();
		
		$admin_ui_editor = be::get_admin_ui('editor');
		$admin_ui_editor->head();
		?>
<script type="text/javascript" language="javascript" src="template/user/js/admin_group_permissions.js"></script>
		<?php
	}
	
	protected function center()
	{
    	$group = $this->get('group');
		$apps = $this->get('apps');
		
		$admin_ui_editor = be::get_admin_ui('editor');
		
		$admin_ui_editor->set_action('save', './?controller=user&task=admin_group_permissions_save');	// 显示提交按钮
		$admin_ui_editor->set_action('reset');// 显示重设按钮
		$admin_ui_editor->set_action('back', './?controller=user&task=admin_groups');	// 显示返回按钮


		$admin_ui_editor->add_field(
			array(
			    'label'=>'后台用户组',
			    'html'=>$group->name
			)
		);
		

		$admin_ui_editor->add_field(
			array(
				'type'=>'radio',
				'name'=>'permission',
				'label'=>'权限',
				'value'=>$group->permission,
				'options'=>array('1'=>'所有后台功能', '0'=>'禁用任何功能', '-1'=>'自定义')
			)
		);

		
		$permissions = explode(',', $group->permissions);
		
		foreach ($apps as $app) {

			$app_permissions = $app->get_admin_permissions();
			
			if (count($app_permissions)>0) {
			
				$select_all = true;

				foreach ($app_permissions as $ket=>$val) {
					if (!in_array($ket, $permissions)) {
						$select_all = false;
						break;
					}
				}
				
				$admin_ui_editor->add_field(
					array(
						'type'=>'checkbox',
						'name'=>'permissions',
						'label'=>'<label class="checkbox inline" style="padding:0;color:#468847;font-weight:bold;"><input type="checkbox" class="select-app-permissions"'.($select_all?' checked="checked"':'').'>'.$app->label.'</label>',
						'value'=>$permissions,
						'options'=>$app_permissions
					)
				);
			}
		}

		$admin_ui_editor->add_hidden('group_id', $group->id);
		$admin_ui_editor->display();

	}	

}
?>