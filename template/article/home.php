<?php
use \system\be;
$config = be::get_config('system');
$config_article = be::get_config('article');
?>
<!--{head}-->
<link type="text/css" rel="stylesheet" href="<?php echo URL_ROOT; ?>/template/article/css/bjqs.css">
<script type="text/javascript" language="javascript" src="<?php echo URL_ROOT; ?>/template/article/js/bjqs-1.3.min.js"></script>

<link type="text/css" rel="stylesheet" href="<?php echo URL_ROOT; ?>/template/article/css/home.css">
<script type="text/javascript" language="javascript" src="<?php echo URL_ROOT; ?>/template/article/js/home.js"></script>

<style type="text/css">
ol.bjqs-markers li a{
	padding:2px 6px;
	background:<?php echo $this->get_color(3); ?>;
	color:#fff;
	margin:2px;
	text-decoration: none;
}

ol.bjqs-markers li.active-marker a,
ol.bjqs-markers li a:hover{
	background:<?php echo $this->get_color(); ?>;
}
</style>
<script type="text/javascript" language="javascript">
jQuery(document).ready(function($) {

	$('#banner-fade').bjqs({
		height: <?php echo 280*$config_article->thumbnail_l_h/$config_article->thumbnail_l_w; ?>,
		width: 280,
		responsive: true,
		showcontrols:false
	});
});
</script>
<!--{/head}-->

<!--{center}-->
<?php
$latest_thumbnail_articles = $this->latest_thumbnail_articles;
$top_articles = $this->top_articles;
$categories = $this->categories;
?>
<div class="row">
    <div class="col-8">

        <div id="banner-fade">
            <ul class="bjqs">
            <?php
            foreach ($latest_thumbnail_articles as $article) {
            ?>
                <li>
                <a href="<?php echo url('controller=article&task=detail&article_id='.$article->id); ?>" title="<?php echo $article->title; ?>">
                <img src="<?php echo URL_ROOT.'/'.DATA.'/article/thumbnail/'.$article->thumbnail_l; ?>" alt="<?php echo $article->title; ?>" title="<?php echo $article->title; ?>" style="max-width:100%;">
                </a>
                </li>
            <?php
            }
            ?>
            </ul>
        </div>

    </div>
    <div class="col-12">

        <div class="top-articles">
        <?php
        if (count($top_articles)) {
            $article = $top_articles[0];
            ?>
            <h4><a href="<?php echo url('controller=article&task=detail&article_id='.$article->id); ?>" title="<?php echo $article->title; ?>"><?php echo $article->title; ?></a></h4>
            <div class="summary"><?php echo $article->summary; ?><a href="<?php echo url('controller=article&task=detail&article_id='.$article->id); ?>" title="<?php echo $article->title; ?>">详细 &gt;</a></div>
            <ul>
            <?php
            foreach ($top_articles as $article) {
            ?>
            <li>
                <a href="<?php echo url('controller=article&task=detail&article_id='.$article->id); ?>" title="<?php echo $article->title; ?>"><?php echo $article->title; ?></a>
            </li>
            <?php
            }
            ?>
            </ul>
            <?php
        }
        ?>
        </div>

    </div>
    <div class="clear-left"></div>
</div>
<?php
foreach ($categories as $category) {
    if (count($category->articles) == 0) continue;
    ?>
    <div class="theme-box-container">
        <div class="theme-box">
            <div class="theme-box-title"><?php echo $category->name; ?><a href="<?php echo url('controller=article&task=listing&category_id='.$category->id); ?>" class="more" style="float:right;">更多...</a></div>
            <div class="theme-box-body">


                <?php
                $category_thumbnail_article = null;
                foreach ($category->articles as $article) {
                    if ($article->thumbnail_l!='') {
                        $category_thumbnail_article = $article;
                        break;
                    }
                }
                ?>
                <div class="category-articles">
                    <div class="row">
                        <?php
                        if ($category_thumbnail_article == null) {
                        ?>
                        <div class="col-20">
                            <ul>
                            <?php
                            foreach ($category->articles as $article) {
                            ?>
                            <li>
                                <span class="article-time"><?php echo date('m-d', $article->create_time); ?></span><a href="<?php echo url('controller=article&task=detail&article_id='.$article->id); ?>" title="<?php echo $article->title; ?>"><?php echo $article->title; ?></a>
                            </li>
                            <?php
                            }
                            ?>
                            </ul>
                        </div>
                        <?php
                        } else {
                        ?>
                        <div class="col-5 text-center">
                            <a href="<?php echo url('controller=article&task=detail&article_id='.$category_thumbnail_article->id); ?>" title="<?php echo $category_thumbnail_article->title; ?>">
                              <img src="<?php echo URL_ROOT.'/'.DATA.'/article/thumbnail/'.$category_thumbnail_article->thumbnail_m; ?>" alt="<?php echo $category_thumbnail_article->title; ?>" />
                            </a>
                        </div>
                        <div class="col-15">
                            <ul>
                            <?php
                            foreach ($category->articles as $article) {
                            ?>
                            <li>
                                <span class="article-time"><?php echo date('m-d', $article->create_time); ?></span><a href="<?php echo url('controller=article&task=detail&article_id='.$article->id); ?>" title="<?php echo $article->title; ?>"><?php echo $article->title; ?></a>
                            </li>
                            <?php
                            }
                            ?>
                            </ul>
                        </div>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>
<!--{/center}-->


<!--{east}-->
<?php
$active_users = $this->active_users;
$month_hottest_articles = $this->month_hottest_articles;

$config_article = be::get_config('article');

if (count($active_users)) {
$config_user = be::get_config('user');
?>
<div class="theme-box-container">
	<div class="theme-box">
		<div class="theme-box-title">活跃会员</div>
		<div class="theme-box-body">

			<div class="active-users">
			<ul>
			<?php 
			foreach ($active_users as $active_user) {
				?>
				<li style="width:<?php echo $config_user->default_avatar_m_w; ?>px;">
					<div class="active-user-avatar">
					<a href="<?php echo url('controller=article&task=user&user_id='.$active_user->id); ?>" title="查看 <?php echo $active_user->name; ?> 的动态">
					<img src="<?php echo URL_ROOT.'/'.DATA.'/user/avatar/'.(isset($active_user->avatar_m)?$active_user->avatar_m:('default/'.$config_user->default_avatar_m)); ?>" alt="<?php echo $active_user->name; ?>" />
					</a>
					</div>
					<div class="active-user-name">
					<a href="<?php echo url('controller=article&task=user&user_id='.$active_user->id); ?>" title="查看 <?php echo $active_user->name; ?> 的动态">
					<?php echo $active_user->name; ?>
					</a>
					</div>
				</li>
				<?php
			}
			?>
			</ul>
			</div>

		</div>
	</div>
</div>
		<?php
}


if (count($month_hottest_articles)) {
?>
<div class="theme-box-container">
	<div class="theme-box">
		<div class="theme-box-title">本月热点</div>
		<div class="theme-box-body">

			<?php 
			foreach ($month_hottest_articles as $article) {
				?>
				<div class="month-hottest-article">
				
					<div class="month-hottest-article-thumbnail" style="width:<?php echo $config_article->thumbnail_s_w; ?>px; height:<?php echo $config_article->thumbnail_s_h; ?>px;">
						<a href="<?php echo url('controller=article&task=detail&article_id='.$article->id); ?>" title="<?php echo $article->title; ?>">
						<img src="<?php echo URL_ROOT.'/'.DATA.'/article/thumbnail/'; ?><?php echo $article->thumbnail_s == ''?('default/'.$config_article->default_thumbnail_s):$article->thumbnail_s; ?>" alt="<?php echo $article->title; ?>">
						</a>
					</div>
					
					<div style="margin-left:<?php echo $config_article->thumbnail_s_w; ?>px;">
						<h5 class="month-hottest-article-title"><a href="<?php echo url('controller=article&task=detail&article_id='.$article->id); ?>" title="<?php echo $article->title; ?>"><?php echo $article->title; ?></a></h5>
						<div class="month-hottest-article-time"><?php echo date('Y-m-d H:i:s', $article->create_time); ?></div>
					</div>
				</div>
				
				<div class="clear-left"></div>
				<?php
			}
			?>

		</div>
	</div>
</div>
		<?php
}
?>
<!--{/east}-->
