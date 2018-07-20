<?php
use Phpbe\System\Be;
?>

<!--{head}-->
<?php
$uiGrid = Be::getUi('grid');
$uiGrid->head();
?>
<!--{/head}-->

<!--{center}-->
<?php
$users = $this->users;
$roles = $this->roles;

$roleMap = array('0'=>'所有用户组');
foreach ($roles as $role) {
    // 游客角色
    if ($role->id == 1) continue;
    $roleMap[$role->id] = $role->name;
}

$uiGrid = Be::getUi('grid');

$uiGrid->setAction('list', './?controller=user&action=users');
$uiGrid->setAction('create', './?controller=user&action=edit');
$uiGrid->setAction('edit', './?controller=user&action=edit');
$uiGrid->setAction('unblock', './?controller=user&action=unblock', '启用');
$uiGrid->setAction('block', './?controller=user&action=block');
$uiGrid->setAction('delete', './?controller=user&action=delete');

$uiGrid->setFilters(
    array(
        'type'=>'text',
        'name'=>'key',
        'label'=>'关键字',
        'value'=>$this->key,
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
        'value'=>$this->status,
        'width'=>'80px'
   ),
    array(
        'type'=>'select',
        'name'=>'roleId',
        'options'=>$roleMap,
        'value'=>$this->groupId,
        'width'=>'160px'
   )
);

$configUser = Be::getConfig('System', 'User');

$adminConfigUserGroup->names[0] = '';
foreach ($users as $user) {
    $user->registerTime =	date('Y-m-d H:i',$user->registerTime);
    $user->lastLoginTime = $user->lastLoginTime == 0?'-':date('Y-m-d H:i',$user->lastLoginTime);
    $user->avatar = '<img src="../'.DATA.'/user/avatar/'.($user->avatarS == ''?('default/'.$configUser->defaultAvatarS):$user->avatarS).'" width="32" />';

    $user->roleName = '<span class="label label-info">'.$roleMap[$user->roleId].'</span>';
}

$uiGrid->setData($users);
$uiGrid->setFields(
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


$uiGrid->setPagination($this->pagination);
$uiGrid->orderBy($this->orderBy, $this->orderByDir);
$uiGrid->display();

?>
<!--{/center}-->