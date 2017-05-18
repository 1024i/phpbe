<?php
use \system\be;
use \system\request;

$config = be::get_config('system');
?>
<!--{html}-->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo $this->meta_description; ?>"/>
    <meta name="keywords" content="<?php echo $this->meta_keywords; ?>"/>
    <title><?php echo $this->title . ' - ' . $config->site_name; ?></title>

    <script src="<?php echo URL_ROOT; ?>/theme/default/js/jquery-1.11.0.min.js"></script>
    <script src="<?php echo URL_ROOT; ?>/theme/default/js/jquery.validate.min.js"></script>
    <script src="<?php echo URL_ROOT; ?>/theme/default/js/jquery.cookie.js"></script>

    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/theme/default/bootstrap-3.3.7/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/theme/default/bootstrap-3.3.7/css/bootstrap-theme.min.css"/>

    <script src="<?php echo URL_ROOT; ?>/theme/default/bootstrap-3.3.7/js/bootstrap.min.js"></script>

    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/theme/default/css/theme.css"/>
    <script src="<?php echo URL_ROOT; ?>/theme/default/js/theme.js"></script>

    <script>
        var URL_ROOT = '<?php echo URL_ROOT; ?>';
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
            <img src="<?php echo URL_ROOT; ?>/images/logo.gif" alt="<?php echo $config->site_name; ?>"/>
        </div>

        <div class="menu">
            <ul>
                <?php
                $menu_id = request::get('menu_id', 0, 'int');

                $north_menu = be::get_menu('north');
                $north_menu_tree = $north_menu->get_menu_tree();

                if (count($north_menu_tree)) {
                    foreach ($north_menu_tree as $menu) {

                        $menu_on = true;
                        if (count($menu->params)) {
                            foreach ($menu->params as $key => $val) {
                                if (request::get($key) != $val) {
                                    $menu_on = false;
                                }
                            }
                        } else {
                            $menu_on = false;
                        }

                        if ($menu_on)
                            echo '<li class="menu-on">';
                        else
                            echo '<li class="menu-off">';
                        echo '<a href="';
                        if ($menu->home)
                            echo URL_ROOT;
                        else
                            echo url($menu->url);
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
            <div class="wraper">
                <!--{west}-->
                <?php
                $my = be::get_user();
                ?>
                <h2>用户登陆</h2>

                <?php
                if ($my->id == 0) {
                    ?>
                    <form action="./" method="post"
                          onSubmit="return ($('#west-username').val()!='' && $('#west-password').val()!='');">
                        <ul class="login-form">
                        <li><label>用户名: </label><input title="用户名" type="text" name="username" id="west-username"/></li>
                            <li><label>密码: </label><input title="密码" type="password" name="password" id="west-password"/></li>
                            <li><label>记住我: </label><input title="记住我" type="checkbox" name="rememberme" value="1"></li>
                            <li><label>&nbsp;</label><input type="submit" value="登陆"/>
                                <a href="<?php echo url('controller=user&task=register'); ?>">注册</a>
                                <a href="<?php echo url('controller=user&task=forget_password'); ?>">忘记密码?</a></li>

                        </ul>
                        <input type="hidden" name="controller" value="user"/>
                        <input type="hidden" name="task" value="login_check"/>
                    </form>
                    <?php
                } else {
                    ?>
                    <p>你好, <?php echo $my->username; ?></p>
                    <p><a href="<?php echo url('controller=user&task=edit'); ?>">修改资料</a> <a
                                href="<?php echo url('controller=user&task=reset_password'); ?>">修改密码</a></p>
                    <p><input type="button" value="退出"
                              onClick="window.location.href='<?php echo url('controller=user&task=logout'); ?>';"/></p>
                    <?php
                }
                ?>
                <!--{/west}-->
            </div>
        </div>

        <div class="theme-center">
            <div class="wraper">
                <!--{message}-->
                <?php
                if ($this->_message !== null) echo '<div class="theme-message theme-message-' . $this->message->type . '"><a class="close" href="javascript:;">&times;</a>' . $this->message->body . '</div>';
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
        $south_menu = be::get_menu('south');
        $south_menu_tree = $south_menu->get_menu_tree();
        if (count($south_menu_tree)) {
            echo '<div class="foot-menu">';
            echo '<ul>';
            $i = 1;
            $n = count($south_menu_tree);
            foreach ($south_menu_tree as $menu) {
                echo '<li><a href="';
                if ($menu->home)
                    echo URL_ROOT;
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
            &copy;2010 版权所有: <?php echo $config->site_name; ?> &nbsp;
            使用 <a href="http://www.phpbe.com" target="_blank" title="访问BE官网">BEV<?php echo be::get_version(); ?></a> 开发
        </div>
        <!--{/south}-->
    </div>
    <!--{/body}-->
</div>

</body>
</html>
<!--{/html}-->