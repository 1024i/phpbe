<?php
use Phpbe\System\Be;
?>

<!--{head}-->
<?php
$uiList = Be::getUi('grid');
$uiList->head();
?>
<script type="text/javascript" language="javascript" src="template/user/js/logs.js"></script>
<!--{/head}-->

<!--{center}-->
<?php

$logs = $this->get('logs');

$uiList = Be::getUi('grid');

$uiList->setAction('listing', './?controller=user&task=logs');

$uiList->setFilters(
    array(
        'type'=>'text',
        'name'=>'key',
        'label'=>'按用户名搜索',
        'value'=>$this->get('key'),
        'width'=>'100px'
   ),
    array(
        'type'=>'select',
        'name'=>'success',
        'label'=>'登陆状态',
        'options'=>array(
            '-1'=>'所有',
            '1'=>'登陆成功',
            '0'=>'登陆失败'
       ),
        'value'=>$this->get('success')
   ),
    array(
        'type'=>'button',
        'value'=>'删除三个月前的日志',
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
        'label'=>'时间',
        'align'=>'center',
        'width'=>'120'
    ),
    array(
        'name'=>'success',
        'label'=>'登陆成功?',
        'align'=>'center',
        'width'=>'150',
        'template'=>'<a class="icon checked-{success}"></a>'
    ),
    array(
        'name'=>'description',
        'label'=>'描述',
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