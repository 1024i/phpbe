<?php
use system\be;
?>
<!--{head}-->
<?php
$ui_list = be::get_ui('grid');
$ui_list->head();
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

$error_logs = $this->error_logs;
$error_log_count = $this->error_log_count;
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
                    <a href="<?php echo './?controller=system&task=error_logs&year=' . $x; ?>"><?php echo $x; ?></a>
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
                    <a href="<?php echo './?controller=system&task=error_logs&year=' . $year . '&month=' . $x; ?>"><?php echo $x; ?></a>
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
                    <a href="<?php echo './?controller=system&task=error_logs&year=' . $year . '&month=' . $month . '&day=' . $x; ?>"><?php echo $x; ?></a>
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

if (count($error_logs)) {
    $ui_list = be::get_ui('grid');

    $ui_list->set_action('listing', './?controller=system&task=error_logs');

    $formatted_error_logs = [];
    foreach ($error_logs as $i => $error_log) {
        $error_log['operation'] = '<a href="./?controller=system&task=error_log&year=' . $year . '&month=' . $month . '&day=' . $day . '&index=' . $i . '" target="_blank">查看</a>';
        $error_log['time'] = date('H:i:s', $error_log['time']);
        $error_log['message'] = limit($error_log['message'], 50);

        $formatted_error_logs[] = (object)$error_log;
    }

    $ui_list->set_data($formatted_error_logs);

    $ui_list->set_fields(
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

    $ui_list->set_pagination($this->get('pagination'));
    $ui_list->display();
}
?>
<!--{/center}-->