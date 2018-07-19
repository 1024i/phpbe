<?php
use Phpbe\System\Be;
use Phpbe\System\Request;
?>
<!--{html}-->
<?php
$config = Be::getConfig('System', 'System');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="description" content="<?php echo $this->metaDescription; ?>"/>
    <meta name="keywords" content="<?php echo $this->metaKeywords; ?>"/>
    <title><?php echo $this->title . ' - ' . $config->siteName; ?></title>

    <base href="<?php echo Be::getRuntime()->getUrlRoot(); ?>" />

    <script src="/theme/huxiu/js/jquery-1.12.4.min.js"></script>
    <script src="/theme/huxiu/js/jquery.validate.min.js"></script>
    <script src="/theme/huxiu/js/jquery.cookie.js"></script>

    <link rel="stylesheet" href="/theme/huxiu/css/be.css" />
    <script src="/theme/huxiu/js/be.js"></script>

    <link rel="stylesheet" href="/theme/huxiu/css/theme.css" />
    <script src="/theme/huxiu/js/theme.js"></script>

    <!--{head}-->
    <!--{/head}-->

</head>

<body>
<div class="theme-body-container">
<div class="theme-body">
    <!--{body}-->

    <div class="theme-north-container">
    <div class="theme-north">
        <!--{north}-->
        <?php
        $configSystem = Be::getConfig('System', 'System');
        $configUser = Be::getConfig('System', 'user');

        $menuId = Request::get('menuId', 0, 'int');

        $my = Be::getUser();
        ?>
        <div class="row">
            <div class="col-3">
                <img src="<?php echo Be::getRuntime()->getUrlRoot(); ?>/system/theme/huxiu/images/logo.gif" alt="<?php echo $configSystem->siteName; ?>" />
            </div>
            <div class="col-17">

                <div class="login-form">

                    <?php
                    if (!isset($my->id) || $my->id == 0) {
                        ?>
                        <a href="<?php echo url('controller=user&action=login'); ?>">登陆</a><a href="<?php echo url('controller=user&action=register'); ?>">注册</a>
                        <?php
                    } else {
                        ?>
                        <img src="<?php echo Be::getRuntime()->getUrlRoot().'/'.DATA.'/user/avatar/'.($my->avatarL == ''?('default/'.$configUser->defaultAvatarL):$my->avatarL); ?>" />
                        <a href="<?php echo url('controller=userProfile&action=home'); ?>"><?php echo $my->name; ?></a>
                        <input type="button" class="btn btn-small btn-warning" onclick="javascript:window.location.href='<?php echo url('controller=user&action=logout'); ?>';" value="退出" />
                        <?php
                    }
                    ?>
                </div>
                <div class="menu">
                    <ul class="inline">
                        <?php
                        $northMenu = Be::getMenu('north');
                        $northMenuTree = $northMenu->getMenuTree();

                        if (count($northMenuTree)) {
                            foreach ($northMenuTree as $menu) {
                                $menuOn = true;
                                if ($menuId>0) {
                                    $menuOn = $menu->id == $menuId?true:false;
                                }
                                elseif (count($menu->params)) {
                                    foreach ($menu->params as $key=>$val) {
                                        if (Request::get($key, '')!=$val) {
                                            $menuOn = false;
                                            break;
                                        }
                                    }
                                } else {
                                    $menuOn = false;
                                }

                                if ($menuOn)
                                    echo '<li class="active">';
                                else
                                    echo '<li>';
                                echo '<a href="';
                                if ($menu->home)
                                    echo Be::getRuntime()->getUrlRoot();
                                else
                                    echo $menu->url;
                                echo '" target="'.$menu->target.'"><span>'.$menu->name.'</span></a>';
                                echo '</li>';
                            }
                        }
                        ?>
                    </ul>
                </div>

            </div>
            <div class="clear-left"></div>
        </div>
        <!--{/north}-->
    </div>
    </div>

    <div class="theme-middle-container">
    <div class="theme-middle">
        <!--{middle}-->
        <div class="row">

            <div class="col-14">

                <div class="theme-center-container">
                    <div class="theme-center">
                        <!--{message}-->
                        <?php
                        if ($this->Message !== null) echo '<div class="theme-message theme-message-' . $this->Message->type . '"><a class="close" href="javascript:;">&times;</a>' . $this->Message->body . '</div>';
                        ?>
                        <!--{/message}-->

                        <!--{center}-->
                        <!--{/center}-->
                    </div>
                </div>
            </div>

            <div class="col-6">
                <div class="theme-east-container">
                    <div class="theme-east">
                        <!--{east}-->
                        <!--{/east}-->
                    </div>
                </div>
            </div>

            <div class="clear-left"></div>
        </div>
        <!--{/middle}-->
    </div>
    </div>

    <div class="theme-south-container">
    <div class="theme-south">
        <!--{south}-->
        <?php
        $southMenu = Be::getMenu('south');
        $southMenuTree = $southMenu->getMenuTree();
        if (count($southMenuTree)) {
            echo '<div class="menu">';
            echo '<ul>';
            $i=1;
            $n=count($southMenuTree);
            foreach ($southMenuTree as $menu) {
                echo '<li><a href="';
                if ($menu->home)
                    echo Be::getRuntime()->getUrlRoot();
                else
                    echo $menu->url;
                echo '" target="'.$menu->target.'"><span>'.$menu->name.'</span></a></li>';

                if ($i<$n) echo '<li>|</li>';
                $i++;
            }
            echo '</ul>';
            echo '</div>';
        }
        ?>
        <div class="copyright">
            <?php echo Be::getHtml('copyright'); ?>
        </div>
        <!--{/south}-->
    </div>
    </div>
    <!--{/body}-->
</div>
</div>
</body>
</html>
<!--{/html}-->