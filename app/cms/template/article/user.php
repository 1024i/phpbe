<?php
use system\be;
?>
<!--{head}-->
<script type="text/javascript" language="javascript" src="<?php echo URL_ROOT; ?>/app/cms/template/article/js/user.js"></script>
<link type="text/css" rel="stylesheet" href="<?php echo URL_ROOT; ?>/app/cms/template/article/css/user.css">

<style type="text/css">
.theme-center .user-menu-bar li{background-color:<?php echo $this->get_color(3); ?>;}
.theme-center .user-menu-bar li.active{ background-color:<?php echo $this->get_color(); ?>;}

.theme-center .profile .profile_item{ background-color:<?php echo $this->get_color(); ?>;}
.theme-center .profile .profile_item_value_border{border:<?php echo $this->get_color(5); ?> 1px solid; background-color:<?php echo $this->get_color(9); ?>;margin-top:5px;}
</style>
<!--{/head}-->


<!--{center}-->
<?php
$user = $this->get('user');
$articles = $this->get('articles');
$article_count = $this->get('article_count');
$comments = $this->get('comments');
$comment_count = $this->get('comment_count');

$config_user = be::get_config('user');
$config_article = be::get_config('article');
?>
<div style="border:#eee 1px solid; border-left:<?php echo $this->primary_color; ?> 5px solid;  background-color:#FFFFFF; padding:20px; box-shadow:1px 1px 3px #ccc;">
    <div class="row">
        <div class="col-3 text-center">
            <img src="<?php echo URL_ROOT.'/'.DATA.'/user/avatar/'.($user->avatar_l == ''?('default/'.$config_user->default_avatar_l):$user->avatar_l); ?>" alt="<?php echo $user->name; ?>" />

        </div>
        <div class="col-17">
            <h4><?php echo $user->name; ?></h4>
            <p style="font-size:12px; color:#999;">注册于 <?php echo date('Y-m-d H:i', $user->register_time); ?></p>
        </div>
    </div>
</div>

<div class="user-menu-bar" style="border-bottom:<?php echo $this->get_color(); ?> 1px solid;">
    <ul>
        <li class="active" data-content="articles">TA的文章 <sup><?php echo $article_count; ?></sup></li><li data-content="comments">TA的评论 <sup><?php echo $comment_count; ?></sup></li><li data-content="profile">TA的资料</li>
    </ul>
</div>

<div class="user-tab user-tab-articles">
    <?php
    if ($article_count == 0) {
    ?>
    <p class="text-muted text-center"><?php echo $user->name; ?> 未曾发表过文章</p>
    <?php
    } else {
        foreach ($articles as $article) {
        ?>
            <div class="article">
                <div class="article-thumbnail" style="width:<?php echo $config_article->thumbnail_s_w; ?>px; height:<?php echo $config_article->thumbnail_s_h; ?>px;">
                    <a href="<?php echo url('controller=article&task=detail&article_id='.$article->id); ?>" title="<?php echo $comment->article->title; ?>" target="_blank">
                    <img src="<?php echo URL_ROOT.'/'.DATA.'/article/thumbnail/'; ?><?php echo $article->thumbnail_s == ''?('default/'.$config_article->default_thumbnail_s):$article->thumbnail_s; ?>" alt="<?php echo $comment->article->title; ?>" />
                    </a>
                </div>

                <div style="margin-left:<?php echo $config_article->thumbnail_s_w; ?>px;">
                    <h4 class="article-title"><a href="<?php echo url('controller=article&task=detail&article_id='.$article->id); ?>" title="<?php echo $comment->article->title; ?>" target="_blank"><?php echo $article->title; ?></a></h4>
                    <div class="article-time"><?php echo date('Y-m-d H:i:s', $article->create_time); ?></div>
                    <div class="article-summary"><?php echo $article->summary; ?></div>
                </div>
            </div>

            <div class="clear_left"></div>
        <?php
        }
    }
    ?>
</div>

<div class="user-tab user-tab-comments" style="display:none;">
    <?php
    if ($comment_count == 0) {
    ?>
    <p class="text-muted text-center"><?php echo $user->name; ?> 未曾发表过评论</p>
    <?php
    } else {
        foreach ($comments as $comment) {
        ?>
        <div class="comment">
            <h4 class="article-title">评论文章：<a href="<?php echo url('controller=article&task=detail&article_id='.$comment->article->id); ?>" title="<?php echo $comment->article->title; ?>" target="_blank"><?php echo $comment->article->title; ?></a></h4>
            <div class="comment-time"><?php echo date('Y-m-d H:i:s', $comment->create_time); ?></div>
            <div class="comment-body"><?php echo $comment->body; ?></div>
        </div>
        <?php
        }
    }
    ?>
</div>

<div class="user-tab user-tab-profile" style="display:none;">
    <div class="profile">

        <table>
            <tbody>
                <tr>
                    <td>
                        <div class="profile_item">用户名: </div>
                    </td>
                    <td>
                        <div class="profile_item_value_border">
                            <div class="profile_item_value"><?php echo $user->username; ?></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="profile_item">名称: </div>
                    </td>
                    <td>
                        <div class="profile_item_value_border">
                            <div class="profile_item_value"><?php echo $user->name == ''?'-':$user->name; ?></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="profile_item">性别: </div>
                    </td>
                    <td>
                        <div class="profile_item_value_border">
                            <div class="profile_item_value">
                                <?php
                                if ($user->gender == 0) {
                                    echo '女';
                                }
                                elseif ($user->gender == 1) {
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
                        <div class="profile_item">QQ: </div>
                    </td>
                    <td>
                        <div class="profile_item_value_border">
                            <div class="profile_item_value"><?php echo $user->qq == ''?'-':$user->qq; ?></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="profile_item">注册于: </div>
                    </td>
                    <td>
                        <div class="profile_item_value_border">
                            <div class="profile_item_value"><?php echo date('Y-m-d H:i', $user->register_time); ?></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="profile_item">上次登陆时间: </div>
                    </td>
                    <td>
                        <div class="profile_item_value_border">
                            <div class="profile_item_value"><?php echo date('Y-m-d H:i', $user->last_visit_time); ?></div>
                        </div>
                    </td>
                </tr>

            </tbody>
        </table>

    </div>
</div>
<!--{/center}-->