<?php
use Phpbe\System\Be;
use Phpbe\System\Request;
?>

<!--{html}-->
<?php
$config = Be::getConfig('System.System');
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

    <script src="<?php echo Be::getRuntime()->getUrlRoot(); ?>/system/theme/sample/js/jquery-1.12.4.min.js"></script>
    <script src="<?php echo Be::getRuntime()->getUrlRoot(); ?>/system/theme/sample/js/jquery.validate.min.js"></script>
    <script src="<?php echo Be::getRuntime()->getUrlRoot(); ?>/system/theme/sample/js/jquery.cookie.js"></script>

    <link rel="stylesheet" href="<?php echo Be::getRuntime()->getUrlRoot(); ?>/system/theme/sample/bootstrap-3.3.7/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="<?php echo Be::getRuntime()->getUrlRoot(); ?>/system/theme/sample/bootstrap-3.3.7/css/bootstrap-theme.min.css"/>

    <script src="<?php echo Be::getRuntime()->getUrlRoot(); ?>/system/theme/sample/bootstrap-3.3.7/js/bootstrap.min.js"></script>

    <link rel="stylesheet" href="<?php echo Be::getRuntime()->getUrlRoot(); ?>/system/theme/sample/css/theme.css"/>
    <script src="<?php echo Be::getRuntime()->getUrlRoot(); ?>/system/theme/sample/js/theme.js"></script>

    <script>
        var Be::getRuntime()->getUrlRoot() = '<?php echo Be::getRuntime()->getUrlRoot(); ?>';
    </script>

    <!--{head}-->
    <!--{/head}-->

</head>

<body>

<div class="theme-body">
    <!--{body}-->
    <div class="theme-north">

        <!--{north}-->
        <div class="logo">
            <a href="<?php echo Be::getRuntime()->getUrlRoot(); ?>" title="<?php echo $config->siteName; ?>">
                <img src="<?php echo Be::getRuntime()->getUrlRoot(); ?>/system/theme/sample/images/logo.gif" alt="<?php echo $config->siteName; ?>"/>
            </a>
        </div>

        <div class="menu">
            <ul>
                <?php
                $menuId = Request::get('menuId', 0, 'int');

                $northMenu = Be::getMenu('north');
                $northMenuTree = $northMenu->getMenuTree();

                if (count($northMenuTree)) {
                    foreach ($northMenuTree as $menu) {

                        $menuOn = true;
                        if (count($menu->params)) {
                            foreach ($menu->params as $key => $val) {
                                if (Request::get($key) != $val) {
                                    $menuOn = false;
                                }
                            }
                        } else {
                            $menuOn = false;
                        }

                        if ($menuOn)
                            echo '<li class="menu-on">';
                        else
                            echo '<li class="menu-off">';
                        echo '<a href="';
                        if ($menu->home)
                            echo Be::getRuntime()->getUrlRoot();
                        else
                            echo $menu->url;
                        echo '" target="' . $menu->target . '"><span>' . $menu->name . '</span></a>';
                        echo '</li>';
                    }
                }
                ?>
            </ul>
        </div>
        <div class="clrl"></div>
        <!--{/north}-->

    </div>

    <div class="theme-middle">
        <!--{middle}-->
        <div class="theme-west">
            <div class="wrapper">
                <!--{west}-->
                <?php
                $my = Be::getUser();
                ?>
                <h2>用户登陆</h2>

                <?php
                if ($my->id == 0) {
                    ?>
                    <form action="" method="post"
                          onSubmit="return ($('#west-username').val()!='' && $('#west-password').val()!='');">
                        <ul class="login-form">
                        <li><label>用户名: </label><input title="用户名" type="text" name="username" id="west-username"/></li>
                            <li><label>密码: </label><input title="密码" type="password" name="password" id="west-password"/></li>
                            <li><label>记住我: </label><input title="记住我" type="checkbox" name="rememberme" value="1"></li>
                            <li><label>&nbsp;</label><input type="submit" value="登陆"/>
                                <a href="<?php echo url('controller=user&action=register'); ?>">注册</a>
                                <a href="<?php echo url('controller=user&action=forgetPassword'); ?>">忘记密码?</a></li>

                        </ul>
                        <input type="hidden" name="controller" value="user"/>
                        <input type="hidden" name="action" value="loginCheck"/>
                    </form>
                    <?php
                } else {
                    ?>
                    <p>你好, <?php echo $my->username; ?></p>
                    <p><a href="<?php echo url('controller=user&action=edit'); ?>">修改资料</a> <a
                                href="<?php echo url('controller=user&action=resetPassword'); ?>">修改密码</a></p>
                    <p><input type="button" value="退出"
                              onClick="window.location.href='<?php echo url('controller=user&action=logout'); ?>';"/></p>
                    <?php
                }
                ?>
                <!--{/west}-->
            </div>
        </div>

        <div class="theme-center">
            <div class="wrapper">
                <!--{message}-->
                <?php
                if ($this->Message !== null) echo '<div class="theme-message theme-message-' . $this->Message->type . '"><a class="close" href="javascript:;">&times;</a>' . $this->Message->body . '</div>';
                ?>
                <!--{/message}-->

                <!--{center}-->
                <!--{/center}-->
            </div>
        </div>

        <!--{east}-->
        <!--{/east}-->

        <div class="clrl"></div>
        <!--{/middle}-->
    </div>

    <div class="theme-south">
        <!--{south}-->
        <?php
        $southMenu = Be::getMenu('south');
        $southMenuTree = $southMenu->getMenuTree();
        if (count($southMenuTree)) {
            echo '<div class="foot-menu">';
            echo '<ul>';
            $i = 1;
            $n = count($southMenuTree);
            foreach ($southMenuTree as $menu) {
                echo '<li><a href="';
                if ($menu->home)
                    echo Be::getRuntime()->getUrlRoot();
                else
                    echo url($menu->url);
                echo '" target="' . $menu->target . '"><span>' . $menu->name . '</span></a></li>';

                if ($i < $n) echo '<li>|</li>';
                $i++;
            }
            echo '</ul>';
            echo '</div>';
        }
        ?>
        <div class="copyright clr">
            &copy;2017 版权所有: <?php echo $config->siteName; ?> &nbsp;
            使用 <a href="http://www.phpbe.com" target="Blank" title="访问BE官网">BEV<?php echo Be::getVersion(); ?></a> 开发
        </div>
        <!--{/south}-->
    </div>
    <!--{/body}-->
</div>

</body>
</html>
<!--{/html}-->