<?php
use system\be;
?>
<!--{head}-->
<link type="text/css" rel="stylesheet" href="<?php echo URL_ROOT; ?>/template/user/css/dashboard.css">
<script type="text/javascript" language="javascript" src="<?php echo URL_ROOT; ?>/template/user/js/dashboard.js"></script>

<script type="text/javascript" language="javascript" src="<?php echo URL_ROOT; ?>/template/user_profile/js/edit_password.js"></script>
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
        $my = be::get_user();
        ?>
        <div class="theme-box-container">
            <div class="theme-box">
                <div class="theme-box-title"><?php echo $this->title; ?></div>
                <div class="theme-box-body">

                    <form id="form-user_profile_edit_password">
                        <div class="row">
                            <div class="col-6">
                                <div class="key">当前密码<span class="text-required">*</span></div>
                            </div>
                            <div class="col-14">
                                <div class="val"><input type="password" class="input" name="password" value="" style="width:200px;" /></div>
                            </div>
                            <div class="clear-left"></div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="key">新密码: <span class="text-required">*</span></div>
                            </div>
                            <div class="col-14">
                                <div class="val"><input type="password" class="input" name="password1" id="center-password1" value="" style="width:200px;" /></div>
                            </div>
                            <div class="clear-left"></div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="key">确认新密码: <span class="text-required">*</span></div>
                            </div>
                            <div class="col-14">
                                <div class="val"><input type="password" class="input" name="password2" value="" style="width:200px;" /></div>
                            </div>
                            <div class="clear-left"></div>
                        </div>
                        <div class="row" style="margin-top:20px;">
                            <div class="col-6"></div>
                            <div class="col-14">
                                <div class="val">
                                    <input type="submit" class="btn btn-primary btn-submit" value="提交" />
                                    <input type="reset" class="btn" value="重置" />
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

