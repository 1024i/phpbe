<?php
use system\be;
?>
<!--{head}-->
<link type="text/css" rel="stylesheet" href="<?php echo URL_ROOT; ?>/app/system/template/user/css/dashboard.css">
<script type="text/javascript" language="javascript" src="<?php echo URL_ROOT; ?>/app/system/template/user/js/dashboard.js"></script>

<script type="text/javascript" language="javascript" src="<?php echo URL_ROOT; ?>/app/system/template/user_profile/js/edit_avatar.js"></script>
<!--{/head}-->


<!--{middle}-->
<div class="theme-west">
    <div class="wrapper">
        <!--{west}-->
        <?php
        include PATH_ROOT.DS.'template'.DS.'user_profile'.DS.'west.php'
        ?>
        <!--{/west}-->
    </div>
</div>
<div class="theme-center">
    <div class="wrapper">
        <!--{message}-->
        <?php
        if ($this->_message !== null) echo '<div class="theme-message theme-message-' . $this->_message->type . '"><a class="close" href="javascript:;">&times;</a>' . $this->_message->body . '</div>';
        ?>
        <!--{/message}-->

        <!--{center}-->
        <?php
        $config_system = be::get_config('system.system');
        $config_user = be::get_config('system.user');
        $my = be::get_user();
        ?>
        <div class="theme-box-container">
            <div class="theme-box">
                <div class="theme-box-title"><?php echo $this->title; ?></div>
                <div class="theme-box-body">

                    <form action="<?php echo url('controller=user_profile&task=edit_avatar_save'); ?>" method="post" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-5">
                                <div class="key">当前头像: </div>
                            </div>
                            <div class="col-15">
                                <div class="val">
                                    <img src="<?php echo URL_ROOT.'/'.DATA.'/user/avatar/'.($my->avatar_l == ''?('default/'.$config_user->default_avatar_l):$my->avatar_l); ?>" />
                                    <?php
                                    $config_user = be::get_config('system.user');
                                    if ($my->avatar_l != '') {
                                        ?>
                                        <a href="<?php echo url('controller=user_profile&task=init_avatar'); ?>" style="font-size:18px;">&times;</a>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="clear-left"></div>
                        </div>
                        <div class="row">
                            <div class="col-5"><div class="key">上传新头像：</div></div>
                            <div class="col-15"><input type="file" name="avatar" /></div>
                            <div class="clear-left"></div>
                        </div>
                        <div class="row">
                            <div class="col-5"></div>
                            <div class="col-15">
                                <div class="val">
                                    <p class="text-muted">允许上传的图像类型: <?php echo implode(', ', $config_system->allow_upload_image_types); ?></p>
                                    <p class="text-muted">图像大小: <?php echo $config_user->avatar_l_w; ?>px &times; <?php echo $config_user->avatar_l_h; ?>px</p>
                                </div>
                            </div>
                            <div class="clear-left"></div>
                        </div>

                        <div class="row">
                            <div class="col-5"></div>
                            <div class="col-15">
                                <div class="val">
                                    <input type="submit" class="btn btn-primary btn-submit" value="提交">
                                    <input type="reset" class="btn" value="重置">
                                </div>
                            </div>
                            <div class="clear-left"></div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
        <!--{/center}-->
    </div>
</div>
<!--{/middle}-->



