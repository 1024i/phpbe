<?php
use Phpbe\System\Be;
use Phpbe\System\Session;
?>

<!--{html}-->
<?php
$config = Be::getConfig('System.System');
$my = Be::getAdminUser();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title><?php echo $this->title . ' - ' . $config->siteName; ?></title>

<base href="<?php echo URL_ADMIN; ?>/" />

<script src="theme/classic/js//jquery-1.12.4.min.js"></script>
<script src="theme/classic/js//jquery.validate.min.js"></script>
<script src="theme/classic/js//jquery.cookie.js"></script>

<link rel="stylesheet" href="Theme/bootstrap/2.3.2/css/bootstrap.min.css" />
<script src="Theme/bootstrap/2.3.2/js/bootstrap.min.js"></script>

<script type="text/javascript" language="javascript">
	var gSLoadingImage = '<img src="Theme/classic/images/loading.gif" alt="加载中..." align="absmiddle" /> ';
	var gSLoading = gSLoadingImage + ' 加载中...';
	var gSHandling = gSLoadingImage + ' 处理中...';
</script>

<link rel="stylesheet" href="Theme/classic/css/theme.css" />
<script src="Theme/classic/js/theme.js"></script>

    <!--{head}-->
    <!--{/head}-->
</head>
<body>
    <!--{body}-->
    <div class="theme-north">
        <!--{north}-->
        <?php
        $adminConfigAdminUser = Be::getConfig('System.AdminUser');
        ?>
        <div class="theme-north-header">
            <?php echo '您好： '; ?><img src="../<?php echo DATA.'/adminUser/avatar/'.($my->avatarS == ''?('default/'.$adminConfigAdminUser->defaultAvatarS):$my->avatarS); ?>" style="max-width:24px;max-height:24px;" /> <?php echo $my->name; ?> &nbsp; &nbsp; <a href="./?controller=adminUser&task=logout" class="btn btn-warning btn-small"><i class="icon-white icon-off"></i> 退出</a>
        </div>

        <div class="theme-north-menu" id="north-menu">

            <ul class="nav nav-pills">
                <li><a href="./?app=System&controller=System&task=dashboard">后台首页</a></li>
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">菜单<b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li><a href="./?app=System&controller=System&task=menus">菜单列表</a></li>
                        <li><a href="./?app=System&controller=System&task=menuGroups">菜单分组</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">主题<b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li><a href="./?app=System&controller=System&task=themes">已安装的主题</a></li>
                        <li><a href="./?app=System&controller=System&task=remoteThemes">安装新主题</a></li>
                    </ul>
                </li>

                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">应用<b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <?php
                        $serviceApp = Be::getService('System.app');
                        $apps = $serviceApp->getApps();
                        ?>
                        <li><a href="./?app=System&controller=System&task=apps">已安装的应用<span class="badge badge-warning" style="margin-left:10px;"><?php echo count($apps); ?></span></a></li>
                        <li><a href="./?app=System&controller=System&task=remoteApps">安装新应用</a></li>
                    </ul>
                </li>

                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">系统管理<b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li><a href="./?app=System&controller=System&task=config">系统基本设置</a></li>
                        <li><a href="./?app=System&controller=System&task=configMail">发送邮件设置</a></li>
                        <li><a href="./?app=System&controller=System&task=configWatermark">水印设置</a></li>
                        <li class="divider"></li>
                        <li><a href="./?app=System&controller=System&task=cache">缓存管理</a></li>
                        <li><a href="./?app=System&controller=System&task=errorLogs">错误日志</a></li>
                        <li><a href="./?app=System&controller=System&task=logs">系统日志</a></li>
                    </ul>
                </li>

                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">帮助<b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li><a href="http://www.phpbe.com/" target="Blank">官方网站</a></li>
                        <li><a href="http://support.phpbe.com/" target="Blank">技术支持</a></li>
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
                    <p>当前BE系统版本号：v <?php echo Be::getVersion(); ?></p>
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
                    if ($this->Message)
                    {
                        $message = $this->Message;
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
            $serviceApp = Be::getService('System.app');
            $apps = $serviceApp->getApps();
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
                                $menus = $app->getAdminMenus();
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