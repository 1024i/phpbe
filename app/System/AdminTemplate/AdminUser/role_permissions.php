<?php
use Phpbe\System\Be;
?>

<!--{head}-->
<?php
$adminUiEditor = Be::getUi('editor');
$adminUiEditor->head();
?>
<script type="text/javascript" language="javascript" src="template/adminUser/js/rolePermissions.js"></script>
<!--{/head}-->

<!--{center}-->
<?php
$role = $this->role;
$apps = $this->apps;

$adminUiEditor = Be::getUi('editor');

$adminUiEditor->setAction('save', './?app=System&controller=AdminUser&action=rolePermissionsSave');	// 显示提交按钮
$adminUiEditor->setAction('reset');// 显示重设按钮
$adminUiEditor->setAction('back', './?app=System&controller=AdminUser&action=roles');	// 显示返回按钮


$adminUiEditor->addField(
    array(
        'label'=>'用户组',
        'html'=>$role->name
    )
);


$adminUiEditor->addField(
    array(
        'type'=>'radio',
        'name'=>'permission',
        'label'=>'权限',
        'value'=>$role->permission,
        'options'=>array('1'=>'所有功能', '0'=>'禁用任何功能', '-1'=>'自定义')
    )
);


$permissions = explode(',', $role->permissions);

foreach ($apps as $app) {

    $appPermissions = $app->getAdminPermissions();

    if (count($appPermissions)>0) {

        $selectAll = true;

        $values = [];
        $options = [];
        foreach ($appPermissions as $key => $val) {
            if ($key == '-') continue;

            $value = implode(',', $val);
            if (arrayDiff($val, $permissions)) {
                $selectAll = false;
            } else {
                $values[] = $value;
            }

            $options[$value] = $key;
        }

        $adminUiEditor->addField(
            array(
                'type' => 'checkbox',
                'name' => 'permissions',
                'label' => '<label class="checkbox inline" style="padding:0;color:#468847;font-weight:bold;"><input type="checkbox" class="select-app-permissions"'.($selectAll?' checked="checked"':'').'>'.$app->label.'</label>',
                'value' => $values,
                'options' => $options
            )
        );
    }
}

$adminUiEditor->addHidden('roleId', $role->id);
$adminUiEditor->display();
?>
<!--{/center}-->