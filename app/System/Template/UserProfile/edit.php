<?php
use Phpbe\System\Be;
?>
<!--{head}-->
<link type="text/css" rel="stylesheet" href="<?php echo Be::getRuntime()->getUrlRoot(); ?>/app/System/Template/User/css/dashboard.css">
<script type="text/javascript" language="javascript" src="<?php echo Be::getRuntime()->getUrlRoot(); ?>/app/System/Template/User/js/dashboard.js"></script>

<script type="text/javascript" language="javascript" src="<?php echo Be::getRuntime()->getUrlRoot(); ?>/app/System/Template/userProfile/js/edit.js"></script>
<!--{/head}-->

<!--{middle}-->
<div class="theme-west">
    <div class="wrapper">
        <!--{west}-->
        <?php
        include Be::getRuntime()->getPathRoot() . '/template/userProfile/west.php'
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
        <?php
        $my = Be::getUser();
        ?>
        <div class="theme-box-container">
            <div class="theme-box">
                <div class="theme-box-title"><?php echo $this->title; ?></div>
                <div class="theme-box-body">

                    <form id="form-userProfileEdit">
                        <div class="row">
                            <div class="col-5">
                                <div class="key">用户名: </div>
                            </div>
                            <div class="col-15">
                                <div class="val"><?php echo $my->username; ?></div>
                            </div>
                            <div class="clear-left"></div>
                        </div>
                        <div class="row">
                            <div class="col-5">
                                <div class="key">邮箱: </div>
                            </div>
                            <div class="col-15">
                                <div class="val"><?php echo $my->email; ?></div>
                            </div>
                            <div class="clear-left"></div>
                        </div>
                        <div class="row">
                            <div class="col-5">
                                <div class="key">名称: </div>
                            </div>
                            <div class="col-15">
                                <div class="val"><input type="text" class="input" name="name" value="<?php echo $my->name; ?>" style="width:120px;" /></div>
                            </div>
                            <div class="clear-left"></div>
                        </div>
                        <div class="row">
                            <div class="col-5">
                                <div class="key">性别: </div>
                            </div>
                            <div class="col-15">
                                <div class="val">
                                    <label><input type="radio" name="gender" value="-1"<?php echo $my->gender == -1?' checked="checked"':''; ?> />保密</label>
                                    <label><input type="radio" name="gender" value="1"<?php echo $my->gender == 1?' checked="checked"':''; ?> />男</label>
                                    <label><input type="radio" name="gender" value="0"<?php echo $my->gender == 0?' checked="checked"':''; ?> />女</label>
                                </div>
                            </div>
                            <div class="clear-left"></div>
                        </div>
                        <div class="row">
                            <div class="col-5">
                                <div class="key">电话: </div>
                            </div>
                            <div class="col-15">
                                <div class="val"><input type="text" class="input" name="phone" value="<?php echo $my->phone; ?>" style="width:200px;" /></div>
                            </div>
                            <div class="clear-left"></div>
                        </div>
                        <div class="row">
                            <div class="col-5">
                                <div class="key">手机: </div>
                            </div>
                            <div class="col-15">
                                <div class="val"><input type="text" class="input" name="mobile" value="<?php echo $my->mobile; ?>" style="width:200px;" /></div>
                            </div>
                            <div class="clear-left"></div>
                        </div>
                        <div class="row">
                            <div class="col-5">
                                <div class="key">QQ: </div>
                            </div>
                            <div class="col-15">
                                <div class="val"><input type="text" class="input" name="qq" value="<?php echo $my->qq; ?>" style="width:200px;" /></div>
                            </div>
                            <div class="clear-left"></div>
                        </div>
                        <div class="row" style="margin-top:20px;">
                            <div class="col-5"></div>
                            <div class="col-15">
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