<?php
use system\be;
use system\session;
?>

<!--{html}-->
<?php
$config = be::get_config('system');
$my = be::get_admin_user();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title><?php echo $this->title . ' - ' . $config->site_name; ?></title>

<base href="<?php echo URL_ADMIN; ?>/" />

<script src="theme/classic/js//jquery-1.12.4.min.js"></script>
<script src="theme/classic/js//jquery.validate.min.js"></script>
<script src="theme/classic/js//jquery.cookie.js"></script>

<link rel="stylesheet" href="theme/bootstrap/2.3.2/css/bootstrap.min.css" />
<script src="theme/bootstrap/2.3.2/js/bootstrap.min.js"></script>

<script type="text/javascript" language="javascript">
	var g_sLoadingImage = '<img src="theme/classic/images/loading.gif" alt="加载中..." align="absmiddle" /> ';
	var g_sLoading = g_sLoadingImage + ' 加载中...';
	var g_sHandling = g_sLoadingImage + ' 处理中...';
</script>

<link rel="stylesheet" href="theme/classic/css/theme.css" />
<script src="theme/classic/js/theme.js"></script>

    <!--{head}-->
    <!--{/head}-->
</head>
<body>
    <!--{body}-->
    <div class="theme-north">
        <!--{north}-->
        <?php
        $admin_config_admin_user = be::get_admin_config('admin_user');
        ?>
        <div class="theme-north-header">
            <?php echo '您好： '; ?><img src="../<?php echo DATA.'/admin_user/avatar/'.($my->avatar_s == ''?('default/'.$admin_config_admin_user->default_avatar_s):$my->avatar_s); ?>" style="max-width:24px;max-height:24px;" /> <?php echo $my->name; ?> &nbsp; &nbsp; <a href="./?controller=admin_user&task=logout" class="btn btn-warning btn-small"><i class="icon-white icon-off"></i> 退出</a>
        </div>

        <div class="theme-north-menu" id="north-menu">

            <ul class="nav nav-pills">
                <li><a href="./?controller=system&task=dashboard">后台首页</a></li>
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">菜单<b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li><a href="./?controller=system&task=menus">菜单列表</a></li>
                        <li><a href="./?controller=system&task=menu_groups">菜单分组</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">主题<b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li><a href="./?controller=system&task=themes">已安装的主题</a></li>
                        <li><a href="./?controller=system&task=remote_themes">安装新主题</a></li>
                    </ul>
                </li>

                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">应用<b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <?php
                        $service_app = be::get_admin_service('app');
                        $apps = $service_app->get_apps();
                        ?>
                        <li><a href="./?controller=system&task=apps">已安装的应用<span class="badge badge-warning" style="margin-left:10px;"><?php echo count($apps); ?></span></a></li>
                        <li><a href="./?controller=system&task=remote_apps">安装新应用</a></li>
                    </ul>
                </li>

                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">系统管理<b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li><a href="./?controller=system&task=config">系统基本设置</a></li>
                        <li><a href="./?controller=system&task=config_mail">发送邮件设置</a></li>
                        <li><a href="./?controller=system&task=config_watermark">水印设置</a></li>
                        <li class="divider"></li>
                        <li><a href="./?controller=system&task=cache">缓存管理</a></li>
                        <li><a href="./?controller=system&task=error_logs">错误日志</a></li>
                        <li><a href="./?controller=system&task=logs">系统日志</a></li>
                    </ul>
                </li>

                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">帮助<b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li><a href="http://www.phpbe.com/" target="_blank">官方网站</a></li>
                        <li><a href="http://support.phpbe.com/" target="_blank">技术支持</a></li>
                        <li class="divider"></li>
                        <li><a href="javascript:;" onClick="javasscript:$('#modal-about-be').modal();">关于...</a></li>
                    </ul>
                </li>
            </ul>

            <div class="modal hide fade" id="modal-about-be">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h3>关于</h3>
                </div>
                <div class="modal-body">
                    <p>当前BE系统版本号：v <?php echo be::get_version(); ?></p>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn" data-dismiss="modal">关闭</a>
                </div>
            </div>

        </div>

        <!--{/north}-->
    </div>
    <div class="theme-middle">

        <!--{middle}-->
        <div class="theme-center-wrap">
            <div class="theme-center">
                <div class="center-title"><div class="title-icon"><?php echo $this->title; ?></div></div>
                <div class="center-body">
                    <!--{message}-->
                    <?php
                    if ($this->_message)
                    {
                        $message = $this->_message;
                        //$message->type: success:成功 / error:错误 / warning:警告 / info:普通信息

                        echo '<div class="theme-message theme-message-' . $message->type . ' alert alert-' . $message->type . '">';
                        echo '<a href="javascript:;" class="close">&times;</a>';
                        echo $message->body;
                        echo '</div>';
                    }
                    ?>
                    <!--{/message}-->

                    <!--{center}-->
                    <!--{/center}-->

                </div>
            </div>
        </div>

        <div class="theme-west">
            <!--{west}-->
            <?php
            $service_app = be::get_admin_service('app');
            $apps = $service_app->get_apps();
            ?>
            <div class="west-title"><div class="title-icon">已安装的应用</div></div>

            <div class="theme-west-tree" id="west-tree">
                <ul>
                    <?php
                    foreach ($apps as $app) {
                        ?>
                        <li class="node" id="app-<?php echo $app->name; ?>">
                            <div><a href="javascript:;"><img src="<?php echo $app->icon; ?>" max-width="24" max-height="24" /><?php echo $app->label; ?></a></div>
                            <ul>
                                <?php
                                $menus = $app->get_admin_menus();
                                foreach ($menus as $menu) {
                                    ?>
                                    <li><a href="<?php echo $menu['url']; ?>"><img src="<?php echo $menu['icon']; ?>" max-width="24" max-height="24" /><?php echo $menu['name']; ?></a></li>
                                    <?php
                                }
                                ?>
                            </ul>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
            </div>
            <!--{/west}-->
        </div>
        <!--{/middle}-->

    </div>
    <!--{/body}-->
</body>
</html>
<!--{/html}-->