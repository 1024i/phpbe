<?php
use System\Be;
?>

<!--{head}-->
<link type="text/css" rel="stylesheet" href="template/system/css/dashboard.css">
<script type="text/javascript" language="javascript" src="template/system/js/dashboard.js"></script>
<!--{/head}-->

<!--{center}-->
<?php
$my = Be::getAdminUser();
$user = $this->user;
$recentLogs = $this->recentLogs;
$userCount = $this->userCount;
$appCount = $this->appCount;
$themeCount = $this->themeCount;

$configUser = Be::getConfig('System.user');
$adminUserRole = Be::getAdminUserRole($my->roleId);
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
                <td style="width:80px; text-align:left; "><img src="../<?php echo DATA.'/user/avatar/'.($user->avatarM == ''?('default/'.$configUser->defaultAvatarM):$user->avatarM); ?>" /></td>
                <td valign="top">
                    <p>您好 <span style="font-weight:bold; color:red;"><?php echo $user->name; ?></span>(<?php echo $adminUserRole->name; ?>), 欢迎回来。</p>
                    <p class="muted">上次登陆时间：<?php echo date('Y-m-d H:i', $user->lastLoginTime); ?> [<a href="./?controller=user&task=logs" class="text-info">查看登陆日志</a>]</p>
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
                <a href="http://www.phpbe.com/" target="Blank">BE v <?php echo Be::getVersion(); ?></a>
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
                    <a href="./?app=System&controller=System&task=apps" title="管理这 <?php echo $appCount; ?> 个应用" data-toggle="tooltip"  style="font-size:36px; " class="text-info">
                    <?php echo $appCount; ?>
                    </a>
                </td>
                <td style="width:33%; text-align:center; border-right:#ccc 1px solid;;padding-top:10px;">
                    <a href="./?app=System&controller=System&task=themes" title="管理这 <?php echo $themeCount; ?> 个主题" data-toggle="tooltip"  style="font-size:36px; " class="text-info"><?php echo $themeCount; ?></a>
                </td>
                <td style="width:33%; text-align:center; font-size:36px;padding-top:10px;">
                    <a href="./?controller=user&task=listing" title="管理这 <?php echo $userCount; ?> 个用户" data-toggle="tooltip"  style="font-size:36px; " class="text-info"><?php echo $userCount; ?></a>
                </td>
            </tr>
            </table>
            </div>
        </div>

    </div>
</div>

<div class="row-fluid recentLogs">
    <div class="span12">
        <div class="box">
            <div class="box-title">
                <table style="width:100%;">
                <tr>
                    <td style="text-align:left; "><i class="icon-list"></i> 最近操作日志</td>
                    <td style="text-align:right; padding-right:10px;"><a href="./?app=System&controller=System&task=logs" title="查看更多系统操作日志" data-toggle="tooltip" class="text-info">更多...</a></td>
                </tr>
                </table>
            </div>
            <div class="box-body">
                <?php
                $uiList = Be::getUi('grid');

                $libIp = Be::getLib('ip');

                $date = '';
                foreach ($recentLogs as $log) {
                    $newDate = date('Y-m-d',$log->createTime);
                    if ($date == $newDate) {
                        $log->createTime = '<span style="visibility:hidden;">'. $newDate .' &nbsp;</span>'. date('H:i:s',$log->createTime);
                    } else {
                        $log->createTime = $newDate .' &nbsp;'. date('H:i:s',$log->createTime);
                        $date = $newDate;
                    }
                    $log->address = $libIp->convert($log->ip);
                }

                $uiList->setData($recentLogs);

                $uiList->setFields(
                    array(
                        'name'=>'createTime',
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
                $uiList->display();
                ?>
            </div>
        </div>
    </div>

</div>
<!--{/center}-->