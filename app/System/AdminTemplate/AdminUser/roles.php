<?php
use Phpbe\System\Be;
?>

<!--{head}-->
<script type="text/javascript" language="javascript" src="template/adminUser/js/roles.js"></script>
<!--{/head}-->

<!--{center}-->
<?php
$roles = $this->get('roles');

$adminUiCategory = Be::getUi('category');
$adminUiCategory->setAction('save', './?app=System&controller=AdminUser&action=rolesSave');
$adminUiCategory->setAction('delete', './?app=System&controller=AdminUser&action=ajaxRoleDelete');

foreach ($roles as $role) {
    $role->htmlUserCount = '<span class="badge'.($role->userCount>0?' badge-success userCount':'').'">'.$role->userCount.'</span>';
    $role->htmlPermission = '<a href="./?controller=adminUser&action=rolePermissions&roleId='.$role->id.'" class="btn btn-small btn-success">权限管理</a>';
}

$adminUiCategory->setData($roles);
$adminUiCategory->setFields(
    array(
        'name'=>'note',
        'label'=>'备注',
        'align'=>'left',
        'width'=>'320',
        'template'=>'<input type="text" name="note[]" value="{note}" style="width:300px;">',
        'default'=>'<input type="text" name="note[]" value="" style="width:300px;">'
    ),
    array(
        'name'=>'htmlUserCount',
        'label'=>'用户数',
        'align'=>'center',
        'width'=>'80',
    ),
    array(
        'name'=>'htmlPermission',
        'label'=>'权限管理',
        'align'=>'center',
        'width'=>'180'
    )
);

$adminUiCategory->display();
?>
<!--{/center}-->
