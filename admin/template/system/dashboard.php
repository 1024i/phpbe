<?php
use system\be;
?>

<!--{head}-->
<link type="text/css" rel="stylesheet" href="template/system/css/dashboard.css">
<script type="text/javascript" language="javascript" src="template/system/js/dashboard.js"></script>
<!--{/head}-->

<!--{center}-->
<?php
$my = be::get_admin_user();
$user = $this->user;
$recent_logs = $this->recent_logs;
$user_count = $this->user_count;
$app_count = $this->app_count;
$theme_count = $this->theme_count;

$config_user = be::get_config('user');
$admin_user_role = be::get_admin_user_role($my->role_id);
?>

 <div class="row-fluid">
    <div class="span6">

        <div class="box">
            <div class="box-title">
                <table style="width:100%;">
                <tr>
                    <td style="text-align:left; "><i class="icon-user"></i> 管理员信息</td>
                    <td style="text-align:right; padding-right:10px;"><a href="./?controller=user&task=edit&id=<?php echo $my->id; ?>" title="修改当前管理员用户资料" data-toggle="tooltip"><i class="icon-pencil"></i></a></td>
                </tr>
                </table>
            </div>
            <div class="box-body" style="height:80px;">
            <table style="width:100%;">
            <tr>
                <td style="width:80px; text-align:left; "><img src="../<?php echo DATA.'/user/avatar/'.($user->avatar_m == ''?('default/'.$config_user->default_avatar_m):$user->avatar_m); ?>" /></td>
                <td valign="top">
                    <p>您好 <span style="font-weight:bold; color:red;"><?php echo $user->name; ?></span>(<?php echo $admin_user_role->name; ?>), 欢迎回来。</p>
                    <p class="muted">上次登陆时间：<?php echo date('Y-m-d H:i', $user->last_login_time); ?> [<a href="./?controller=user&task=logs" class="text-info">查看登陆日志</a>]</p>
                </td>
            </tr>
            </table>
            </div>
        </div>

    </div>
    <div class="span6">

        <div class="box">
            <div class="box-title">
                <i class="icon-info-sign"></i>
                <a href="http://www.phpbe.com/" target="_blank">BE v <?php echo be::get_version(); ?></a>
            </div>
            <div class="box-body" style="height:80px;">

            <table style="width:100%;">
            <tr>
                <td style="width:33%; text-align:center; border-right:#ccc 1px solid; ">已安装的应用</td>
                <td style="width:33%; text-align:center; border-right:#ccc 1px solid; ">已安装的主题</td>
                <td style="width:33%; text-align:center; ">注册用户数</td>
            </tr>
            <tr>
                <td style="width:33%; text-align:center; border-right:#ccc 1px solid; padding-top:10px; height:50px;">
                    <a href="./?controller=system&task=apps" title="管理这 <?php echo $app_count; ?> 个应用" data-toggle="tooltip"  style="font-size:36px; " class="text-info">
                    <?php echo $app_count; ?>
                    </a>
                </td>
                <td style="width:33%; text-align:center; border-right:#ccc 1px solid;;padding-top:10px;">
                    <a href="./?controller=system&task=themes" title="管理这 <?php echo $theme_count; ?> 个主题" data-toggle="tooltip"  style="font-size:36px; " class="text-info"><?php echo $theme_count; ?></a>
                </td>
                <td style="width:33%; text-align:center; font-size:36px;padding-top:10px;">
                    <a href="./?controller=user&task=listing" title="管理这 <?php echo $user_count; ?> 个用户" data-toggle="tooltip"  style="font-size:36px; " class="text-info"><?php echo $user_count; ?></a>
                </td>
            </tr>
            </table>
            </div>
        </div>

    </div>
</div>

<div class="row-fluid recent_logs">
    <div class="span12">
        <div class="box">
            <div class="box-title">
                <table style="width:100%;">
                <tr>
                    <td style="text-align:left; "><i class="icon-list"></i> 最近操作日志</td>
                    <td style="text-align:right; padding-right:10px;"><a href="./?controller=system&task=logs" title="查看更多系统操作日志" data-toggle="tooltip" class="text-info">更多...</a></td>
                </tr>
                </table>
            </div>
            <div class="box-body">
                <?php
                $ui_list = be::get_admin_ui('grid');

                $lib_ip = be::get_lib('ip');

                $date = '';
                foreach ($recent_logs as $log) {
                    $new_date = date('Y-m-d',$log->create_time);
                    if ($date == $new_date) {
                        $log->create_time = '<span style="visibility:hidden;">'. $new_date .' &nbsp;</span>'. date('H:i:s',$log->create_time);
                    } else {
                        $log->create_time = $new_date .' &nbsp;'. date('H:i:s',$log->create_time);
                        $date = $new_date;
                    }
                    $log->address = $lib_ip->convert($log->ip);
                }

                $ui_list->set_data($recent_logs);

                $ui_list->set_fields(
                    array(
                        'name'=>'create_time',
                        'label'=>'时间',
                        'align'=>'center',
                        'width'=>'180'
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
                $ui_list->display();
                ?>
            </div>
        </div>
    </div>

</div>
<!--{/center}-->