<?php
use System\Be;
?>
<!--{head}-->
<link type="text/css" rel="stylesheet" href="<?php echo URL_ROOT; ?>/app/System/template/user/css/dashboard.css">
<script type="text/javascript" language="javascript" src="<?php echo URL_ROOT; ?>/app/System/template/user/js/dashboard.js"></script>

<link type="text/css" rel="stylesheet" href="<?php echo URL_ROOT; ?>/app/System/template/userProfile/css/home.css" />

<style type="text/css">
    .theme-center .profile .profileItem{ background-color:<?php echo $this->getColor(); ?>;}
    .theme-center .profile .profileItemValueBorder{border:<?php echo $this->getColor(5); ?> 1px solid; background-color:<?php echo $this->getColor(9); ?>;margin-top:5px;}
</style>
<!--{/head}-->

<!--{middle}-->
<div class="theme-west">
    <div class="wrapper">
        <!--{west}-->
        <?php
        include PATH_ROOT . '/template/userProfile/west.php'
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
        $configUser = Be::getConfig('System.User');
        $my = Be::getUser();
        ?>
        <div class="theme-box-container">
            <div class="theme-box">
                <div class="theme-box-title"><?php echo $this->title; ?></div>
                <div class="theme-box-body">

                    <table style="width:100%;">
                        <tr>
                            <td style="width:200px; vertical-align: top; text-align:center;">
                                <p><img src="<?php echo URL_ROOT.'/'.DATA.'/user/avatar/'.($my->avatarL == ''?('default/'.$configUser->defaultAvatarL):$my->avatarL); ?>" /></p>
                                <p class="border-radius-5"  style="background-color:<?php echo $this->primaryColor; ?>; color:#FFFFFF; padding:2px;"><?php echo $my->name; ?></p>
                                <p style="font-size:12px; color:#999;">注册于 <?php echo date('Y-m-d H:i', $my->registerTime); ?></p>
                            </td>
                            <td style="vertical-align:top; padding-left:30px;">

                                <div style="padding-bottom:20px; color:#999;">
                                    上次登陆时间: <?php echo date('Y-m-d H:i:s', $my->lastLoginTime); ?>
                                </div>

                                <div class="profile">
                                    <table>
                                        <tbody>
                                        <tr>
                                            <td>
                                                <div class="profileItem">用户名: </div>
                                            </td>
                                            <td>
                                                <div class="profileItemValueBorder">
                                                    <div class="profileItemValue"><?php echo $my->username; ?></div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="profileItem">名称: </div>
                                            </td>
                                            <td>
                                                <div class="profileItemValueBorder">
                                                    <div class="profileItemValue"><?php echo $my->name == ''?'-':$my->name; ?></div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="profileItem">邮箱: </div>
                                            </td>
                                            <td>
                                                <div class="profileItemValueBorder">
                                                    <div class="profileItemValue"><?php echo $my->email; ?></div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="profileItem">性别: </div>
                                            </td>
                                            <td>
                                                <div class="profileItemValueBorder">
                                                    <div class="profileItemValue">
                                                        <?php
                                                        if ($my->gender == 0) {
                                                            echo '女';
                                                        }
                                                        elseif ($my->gender == 1) {
                                                            echo '男';
                                                        } else {
                                                            echo '未知';
                                                        }
                                                        ?>

                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <div class="profileItem">电话: </div>
                                            </td>
                                            <td>
                                                <div class="profileItemValueBorder">
                                                    <div class="profileItemValue"><?php echo $my->phone == ''?'-':$my->phone; ?></div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="profileItem">手机: </div>
                                            </td>
                                            <td>
                                                <div class="profileItemValueBorder">
                                                    <div class="profileItemValue"><?php echo $my->mobile == ''?'-':$my->mobile; ?></div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="profileItem">QQ: </div>
                                            </td>
                                            <td>
                                                <div class="profileItemValueBorder">
                                                    <div class="profileItemValue"><?php echo $my->qq == ''?'-':$my->qq; ?></div>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td></td>
                                            <td>
                                                <p style="text-align:right; padding-top:10px;">
                                                    <a href="<?php echo url('controller=userProfile&task=edit'); ?>" class="btn btn-primary">
                                                        修改
                                                    </a>
                                                </p>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>


                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <!--{/center}-->
    </div>
</div>
<!--{/middle}-->

