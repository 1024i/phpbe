<?php
use system\be;
?>

<!--{head}-->
<script type="text/javascript" language="javascript" src="template/admin_user/js/roles.js"></script>
<!--{/head}-->

<!--{center}-->
<?php
$roles = $this->get('roles');

$admin_ui_category = be::get_ui('category');
$admin_ui_category->set_action('save', './?controller=admin_user&task=roles_save');
$admin_ui_category->set_action('delete', './?controller=admin_user&task=ajax_role_delete');

foreach ($roles as $role) {
    $role->html_user_count = '<span class="badge'.($role->user_count>0?' badge-success user_count':'').'">'.$role->user_count.'</span>';
    $role->html_permission = '<a href="./?controller=admin_user&task=role_permissions&role_id='.$role->id.'" class="btn btn-small btn-success">权限管理</a>';
}

$admin_ui_category->set_data($roles);
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
?>
<!--{/center}-->
