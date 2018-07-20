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
$years = $this->years;
$year = $this->year;

$months = $this->months;
$month = $this->month;

$days = $this->days;
$day = $this->day;

$errorLogs = $this->errorLogs;
$errorLogCount = $this->errorLogCount;
?>
<ul>
    <?php
    if (count($years)) {
        ?>
        <li>
            <strong>年份：</strong>
            <?php
            foreach ($years as $x) {
                if ($x == $year) {
                    ?>
                    <span class="badge"><?php echo $x; ?></span>
                    <?php
                } else {
                    ?>
                    <a href="<?php echo './?app=System&controller=System&action=errorLogs&year=' . $x; ?>"><?php echo $x; ?></a>
                    <?php
                }
            }
            ?>
        </li>
        <?php
    }

    if (count($months)) {
        ?>
        <li>
            <strong>月份：</strong>
            <?php
            foreach ($months as $x) {
                if ($x == $month) {
                    ?>
                    <span class="badge"><?php echo $x; ?></span>
                    <?php
                } else {
                    ?>
                    <a href="<?php echo './?app=System&controller=System&action=errorLogs&year=' . $year . '&month=' . $x; ?>"><?php echo $x; ?></a>
                    <?php
                }
            }
            ?>
        </li>
        <?php
    }

    if (count($days)) {
        ?>
        <li>
            <strong>日期：</strong>
            <?php
            foreach ($days as $x) {
                if ($x == $day) {
                    ?>
                    <span class="badge"><?php echo $x; ?></span>
                    <?php
                } else {
                    ?>
                    <a href="<?php echo './?app=System&controller=System&action=errorLogs&year=' . $year . '&month=' . $month . '&day=' . $x; ?>"><?php echo $x; ?></a>
                    <?php
                }
            }
            ?>
        </li>
        <?php
    }
    ?>
</ul>
<?php

if (count($errorLogs)) {
    $uiGrid = Be::getUi('grid');

    $uiGrid->setAction('listing', './?app=System&controller=System&action=errorLogs');

    $formattedErrorLogs = [];
    foreach ($errorLogs as $i => $errorLog) {
        $errorLog['operation'] = '<a href="./?app=System&controller=System&action=errorLog&year=' . $year . '&month=' . $month . '&day=' . $day . '&index=' . $i . '" target="Blank">查看</a>';
        $errorLog['time'] = date('H:i:s', $errorLog['time']);
        $errorLog['message'] = limit($errorLog['message'], 50);

        $formattedErrorLogs[] = (object)$errorLog;
    }

    $uiGrid->setData($formattedErrorLogs);

    $uiGrid->setFields(
        [
            'name' => 'type',
            'label' => '类型',
            'align' => 'center',
        ],
        [
            'name' => 'code',
            'label' => '错误码',
            'align' => 'center',
        ],
        [
            'name' => 'file',
            'label' => '文件',
            'align' => 'left'
        ],
        [
            'name' => 'line',
            'label' => '行号',
            'align' => 'center',
        ],
        [
            'name' => 'message',
            'label' => '错误信息',
            'align' => 'left',
        ],
        [
            'name' => 'time',
            'label' => '时间',
            'align' => 'left',
        ],
        [
            'name' => 'operation',
            'label' => '操作',
            'align' => 'left',
        ]
    );

    $uiGrid->setPagination($this->get('pagination'));
    $uiGrid->display();
}
?>
<!--{/center}-->