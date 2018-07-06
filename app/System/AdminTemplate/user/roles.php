<?php
use Phpbe\System\Be;
?>

<!--{head}-->
<script type="text/javascript" language="javascript" src="./template/user/js/roles.js"></script>
<!--{/head}-->

<!--{center}-->
<?php
$roles = $this->get('roles');

$adminUiCategory = Be::getUi('category');
$adminUiCategory->setAction('save', './?controller=user&action=rolesSave');
$adminUiCategory->setAction('delete', './?controller=user&action=ajaxDeleteRole');

foreach ($roles as $role) {
    if ($role->id == 1) {
        $role->htmlDefault = '';
        $role->htmlUserCount = '<span class="userCount"></span>';
    } else {
        $role->htmlDefault = '<a href="javascript:;" onclick="javascript:setDefault(this, '.$role->id.');" class="icon icon-default icon-default-'.$role->default.'"></a>';
        $role->htmlUserCount = '<span class="badge'.($role->userCount>0?' badge-success userCount':'').'">'.$role->userCount.'</span>';
    }
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
        'name'=>'htmlDefault',
        'label'=>'默认角色',
        'align'=>'center',
        'width'=>'120',
    ),
    array(
        'name'=>'htmlUserCount',
        'label'=>'用户数',
        'align'=>'center',
        'width'=>'80',
    ),
    array(
        'name'=>'note',
        'label'=>'权限管理',
        'align'=>'center',
        'width'=>'180',
        'template'=>'<a class="btn btn-small btn-success" href="./?controller=user&action=rolePermissions&roleId={id}">权限管理</a>'
    )
);

$adminUiCategory->display();
?>
<!--{/center}-->