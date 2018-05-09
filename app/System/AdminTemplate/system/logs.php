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
$logs = $this->get('logs');

$uiList = Be::getUi('grid');

$uiList->setAction('listing', './?app=System&controller=System&task=logs');

$options = array();
$options['0'] = '所有';
foreach ($this->adminUsers as $adminUser) {
    $options[$adminUser->id] = $adminUser->username;
}

$uiList->setFilters(
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

$uiList->setData($logs);

$uiList->setFields(
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

$uiList->setPagination($this->get('pagination'));
$uiList->display();
?>
<!--{/center}-->