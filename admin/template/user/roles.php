<?php
use system\be;
?>

<!--{head}-->
<script type="text/javascript" language="javascript" src="./template/user/js/roles.js"></script>
<!--{/head}-->

<!--{center}-->
<?php
$roles = $this->get('roles');

$admin_ui_category = be::get_admin_ui('category');
$admin_ui_category->set_action('save', './?controller=user&task=roles_save');
$admin_ui_category->set_action('delete', './?controller=user&task=ajax_delete_role');

foreach ($roles as $role) {
    if ($role->id == 1) {
        $role->html_default = '';
        $role->html_user_count = '<span class="user_count"></span>';
    } else {
        $role->html_default = '<a href="javascript:;" onclick="javascript:setDefault(this, '.$role->id.');" class="icon icon-default icon-default-'.$role->default.'"></a>';
        $role->html_user_count = '<span class="badge'.($role->user_count>0?' badge-success user_count':'').'">'.$role->user_count.'</span>';
    }
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
        'name'=>'html_default',
        'label'=>'默认角色',
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
        'template'=>'<a class="btn btn-small btn-success" href="./?controller=user&task=role_permissions&role_id={id}">权限管理</a>'
    )
);

$admin_ui_category->display();
?>
<!--{/center}-->