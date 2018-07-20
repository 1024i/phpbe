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
$logs = $this->get('logs');

$uiGrid = Be::getUi('grid');

$uiGrid->setAction('listing', './?app=System&controller=System&action=logs');

$options = array();
$options['0'] = '所有';
foreach ($this->adminUsers as $adminUser) {
    $options[$adminUser->id] = $adminUser->username;
}

$uiGrid->setFilters(
    array(
        'type'=>'text',
        'name'=>'key',
        'label'=>'搜索',
        'value'=>$this->get('key'),
        'width'=>'100px'
   ),
    array(
        'type'=>'select',
        'name'=>'userId',
        'label'=>'指定管理员',
        'options'=>$options,
        'value'=>$this->get('userId')
   ),
    array(
        'type'=>'button',
        'value'=>'删除三个月前日志',
        'click'=>'javascript:deleteLogs(this);',
        'class'=>'btn btn-danger'
   )
);

$libIp = Be::getLib('ip');

$date = '';
foreach ($logs as $log) {
    $newDate = date('Y-m-d',$log->createTime);
    if ($date == $newDate) {
        $log->createTime = '<span style="visibility:hidden;">'. $newDate .' &nbsp;</span>'. date('H:i:s',$log->createTime);
    } else {
        $log->createTime = $newDate .' &nbsp;'. date('H:i:s',$log->createTime);
        $date = $newDate;
    }
    $log->username = Be::getUser($log->userId)->username;
    $log->address = $libIp->convert($log->ip);
}

$uiGrid->setData($logs);

$uiGrid->setFields(
    array(
        'name'=>'createTime',
        'label'=>'时间',
        'align'=>'center',
        'width'=>'150'
    ),
    array(
        'name'=>'username',
        'label'=>'用户名',
        'align'=>'center',
        'width'=>'120'
    ),
    array(
        'name'=>'title',
        'label'=>'操作',
        'align'=>'left'
    ),
    array(
        'name'=>'ip',
        'label'=>'IP',
        'align'=>'center',
        'width'=>'120'
    ),
    array(
        'name'=>'address',
        'label'=>'地理位置',
        'align'=>'left',
        'width'=>'200'
    )
);

$uiGrid->setPagination($this->get('pagination'));
$uiGrid->display();
?>
<!--{/center}-->