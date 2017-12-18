<?php
use system\be;
use system\request;
?>
<!--{html}-->
<?php
$config = be::get_config('system.system');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="description" content="<?php echo $this->meta_description; ?>"/>
    <meta name="keywords" content="<?php echo $this->meta_keywords; ?>"/>
    <title><?php echo $this->title . ' - ' . $config->site_name; ?></title>

    <script src="<?php echo URL_ROOT; ?>/system/theme/huxiu/js/jquery-1.12.4.min.js"></script>
    <script src="<?php echo URL_ROOT; ?>/system/theme/huxiu/js/jquery.validate.min.js"></script>
    <script src="<?php echo URL_ROOT; ?>/system/theme/huxiu/js/jquery.cookie.js"></script>

    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/system/theme/huxiu/css/be.css" />
    <script src="<?php echo URL_ROOT; ?>/system/theme/huxiu/js/be.js"></script>

    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/system/theme/huxiu/css/theme.css" />
    <script src="<?php echo URL_ROOT; ?>/system/theme/huxiu/js/theme.js"></script>

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
        $config_system = be::get_config('system.system');
        $config_user = be::get_config('system.user');

        $menu_id = request::get('menu_id', 0, 'int');

        $my = be::get_user();
        ?>
        <div class="row">
            <div class="col-3">
                <img src="<?php echo URL_ROOT; ?>/system/theme/huxiu/images/logo.gif" alt="<?php echo $config_system->site_name; ?>" />
            </div>
            <div class="col-17">

                <div class="login-form">

                    <?php
                    if (!isset($my->id) || $my->id == 0) {
                        ?>
                        <a href="<?php echo url('controller=user&task=login'); ?>">登陆</a><a href="<?php echo url('controller=user&task=register'); ?>">注册</a>
                        <?php
                    } else {
                        ?>
                        <img src="<?php echo URL_ROOT.'/'.DATA.'/user/avatar/'.($my->avatar_l == ''?('default/'.$config_user->default_avatar_l):$my->avatar_l); ?>" />
                        <a href="<?php echo url('controller=user_profile&task=home'); ?>"><?php echo $my->name; ?></a>
                        <input type="button" class="btn btn-small btn-warning" onclick="javascript:window.location.href='<?php echo url('controller=user&task=logout'); ?>';" value="退出" />
                        <?php
                    }
                    ?>
                </div>
                <div class="menu">
                    <ul class="inline">
                        <?php
                        $north_menu = be::get_menu('north');
                        $north_menu_tree = $north_menu->get_menu_tree();

                        if (count($north_menu_tree)) {
                            foreach ($north_menu_tree as $menu) {
                                $menu_on = true;
                                if ($menu_id>0) {
                                    $menu_on = $menu->id == $menu_id?true:false;
                                }
                                elseif (count($menu->params)) {
                                    foreach ($menu->params as $key=>$val) {
                                        if (request::get($key, '')!=$val) {
                                            $menu_on = false;
                                            break;
                                        }
                                    }
                                } else {
                                    $menu_on = false;
                                }

                                if ($menu_on)
                                    echo '<li class="active">';
                                else
                                    echo '<li>';
                                echo '<a href="';
                                if ($menu->home)
                                    echo URL_ROOT;
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
                        if ($this->_message !== null) echo '<div class="theme-message theme-message-' . $this->_message->type . '"><a class="close" href="javascript:;">&times;</a>' . $this->_message->body . '</div>';
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
        $south_menu = be::get_menu('south');
        $south_menu_tree = $south_menu->get_menu_tree();
        if (count($south_menu_tree)) {
            echo '<div class="menu">';
            echo '<ul>';
            $i=1;
            $n=count($south_menu_tree);
            foreach ($south_menu_tree as $menu) {
                echo '<li><a href="';
                if ($menu->home)
                    echo URL_ROOT;
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
            <?php echo be::get_html('copyright'); ?>
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