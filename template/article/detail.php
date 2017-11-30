<?php
use system\be;
$config = be::get_config('system');
$config_article = be::get_config('article');
?>
<!--{head}-->
<link type="text/css" rel="stylesheet" href="<?php echo URL_ROOT; ?>/template/article/css/listing.css">
	
<script type="text/javascript" language="javascript" src="<?php echo URL_ROOT; ?>/template/article/js/detail.js"></script>
<link type="text/css" rel="stylesheet" href="<?php echo URL_ROOT; ?>/template/article/css/detail.css">
<!--{/head}-->



<!--{center}-->
<?php
$article = $this->article;
$similar_articles = $this->similar_articles;
$comments = $this->comments;

$config_article = be::get_config('article');
$config_user = be::get_config('user');

$my = be::get_user();
?>
<h3 class="title"><?php echo $article->title; ?></h3>
<div class="sub-title"><span>作者：<?php echo be::get_user($article->create_by_id)->name; ?></span><span>发布时间：<?php echo date('Y-m-d H:i:s', $article->create_time); ?></span><span>访问量：<?php echo $article->hits; ?></span></div>
<div class="body">
    <?php echo $article->body; ?>
</div>

<div class="article-vote">
    <div class="row">
        <div class="col-3">
            <a class="article-like" href="javascript:;" title="喜欢" onclick="javascript:like(<?php echo $article->id; ?>);"><?php echo $article->like; ?></a>
        </div>
        <div class="col-3">
            <a class="article-dislike" href="javascript:;" title="不喜欢" onclick="javascript:dislike(<?php echo $article->id; ?>);"><?php echo $article->dislike; ?></a>
        </div>

        <div class="col-14">

            <!-- Baidu Button BEGIN -->
            <div id="bdshare" class="bdshare_t bds_tools get-codes-bdshare" style="float:right;">
            <span class="bds_more">分享到：</span>
            <a class="bds_qzone"></a>
            <a class="bds_tsina"></a>
            <a class="bds_tqq"></a>
            <a class="bds_renren"></a>
            <a class="bds_t163"></a>
            </div>
            <script type="text/javascript" id="bdshare_js" data="type=tools&amp;mini=1&amp;uid=0" ></script>
            <script type="text/javascript" id="bdshell_js"></script>
            <script type="text/javascript">
            document.getElementById("bdshell_js").src = "http://bdimg.share.baidu.com/static/js/shell_v2.js?cdnversion=" + Math.ceil(new Date()/3600000)
            </script>
            <!-- Baidu Button END -->
        </div>
    </div>
</div>

<?php
if (count($similar_articles)>0) {
?>
<div class="similar_articles">
    <div class="similar_articles-title"><div>您可能感兴趣的文章</div></div>
    <ul>
    <?php
    foreach ($similar_articles as $similar_article) {
    ?>
    <li class="similar_article">
        <a href="<?php echo url('controller=article&task=detail&article_id='.$similar_article->id); ?>">
            <?php echo $similar_article->title; ?>
        </a>
    </li>
    <?php
    }
    ?>
    </ul>
</div>
<?php
}
?>

<!--{/center}-->
