<?php
use Phpbe\System\Be;
?>

<!--{head}-->
<?php
$uiList = Be::getUi('grid');
$uiList->head();
?>
<!--{/head}-->

<!--{center}-->
<?php
$users = $this->users;
$roles = $this->roles;

$roleMap = array('0'=>'所有角色');
foreach ($roles as $role) {
    $roleMap[$role->id] = $role->name;
}

$uiList = Be::getUi('grid');

$uiList->setAction('list', './?app=System&controller=AdminUser&task=users');
$uiList->setAction('create', './?app=System&controller=AdminUser&task=edit');
$uiList->setAction('edit', './?app=System&controller=AdminUser&task=edit');
$uiList->setAction('unblock', './?app=System&controller=AdminUser&task=unblock', '启用');
$uiList->setAction('block', './?app=System&controller=AdminUser&task=block');
$uiList->setAction('delete', './?app=System&controller=AdminUser&task=delete');

$uiList->setFilters(
    array(
        'type'=>'text',
        'name'=>'key',
        'label'=>'关键字',
        'value'=>$this->get('key'),
        'width'=>'120px'
   ),
    array(
        'type'=>'select',
        'name'=>'status',
        'label'=>'状态',
        'options'=>array(
            '-1'=>'所有',
            '0'=>'公开',
            '1'=>'屏蔽'
       ),
        'value'=>$this->get('status'),
        'width'=>'80px'
   ),
    array(
        'type'=>'select',
        'name'=>'roleId',
        'options'=>$roleMap,
        'value'=>$this->get('roleId'),
        'width'=>'160px'
   )
);

$configAdminUser = Be::getConfig('System.AdminUser');

foreach ($users as $user) {
    $user->registerTime =	date('Y-m-d H:i',$user->registerTime);
    $user->lastLoginTime = date('Y-m-d H:i',$user->lastLoginTime);
    $user->avatar = '<img src="../'.DATA.'/adminUser/avatar/'.($user->avatarS == ''?('default/'.$configAdminUser->defaultAvatarS):$user->avatarS).'" width="32" />';
    $user->roleName = '<span class="label label-info">'.$roleMap[$user->roleId].'</span>';
}

$uiList->setData($users);
$uiList->setFields(
    array(
        'name'=>'id',
        'label'=>'ID',
        'align'=>'center',
        'width'=>'30',
        'orderBy'=>'id'
    ),
    array(
        'name'=>'avatar',
        'label'=>'头像',
        'align'=>'center',
        'style'=>'margin:0;padding:2px;',
        'width'=>'50'
    ),
    array(
        'name'=>'username',
        'label'=>'用户名',
        'align'=>'left',
        'orderBy'=>'username'
    ),
    array(
        'name'=>'name',
        'label'=>'名称',
        'align'=>'left',
        'width'=>'80'
    ),
    array(
        'name'=>'email',
        'label'=>'邮箱',
        'align'=>'center',
        'width'=>'200',
        'orderBy'=>'email'
    ),
    array(
        'name'=>'registerTime',
        'label'=>'注册时间',
        'align'=>'center',
        'width'=>'120',
        'orderBy'=>'registerTime'
    ),
    array(
        'name'=>'lastLoginTime',
        'label'=>'上次登陆时间',
        'align'=>'center',
        'width'=>'120',
        'orderBy'=>'lastLoginTime'
    ),
    array(
        'name'=>'roleName',
        'label'=>'',
        'align'=>'center',
        'width'=>'80'
    )
);


$uiList->setPagination($this->pagination);
$uiList->orderBy($this->orderBy, $this->orderByDir);
$uiList->display();
?>
<!--{/center}-->
