<?php
use \system\be;
?>
<!--{head}-->
<link type="text/css" rel="stylesheet" href="<?php echo URL_ROOT; ?>/template/article/css/articles.css">
<!--{/head}-->

<!--{middle}-->
<div class="row">

    <div class="col" style="width:<?php echo (!$west && !$east)?100:70; ?>%;">

        <div class="theme-center-container">
            <div class="theme-center">

                <!--{center}-->
                <?php
                $category_id = $this->category_id;
                $articles = $this->articles;

                $pagination = $this->pagination;

                $config_article = be::get_config('article');

                if (count($articles)) {
                    $config_article = be::get_config('article');

                    if ($pagination->get_page() == 1) {
                        $article = array_shift($articles);
                        ?>
                        <h4 class="head-article-title"><a href="<?php echo url('controller=article&task=detail&article_id='.$article->id); ?>" title="<?php echo $article->title; ?>"><?php echo $article->title; ?></a></h4>
                        <div class="head-article-summary"><?php echo $article->summary; ?></div>
                        <div class="head-article-thumbnail">
                            <a href="<?php echo url('controller=article&task=detail&article_id='.$article->id); ?>" title="<?php echo $article->title; ?>">
                                <img src="<?php echo URL_ROOT.'/'.DATA.'/article/thumbnail/'; ?><?php echo $article->thumbnail_l == ''?('default/'.$config_article->default_thumbnail_l):$article->thumbnail_l; ?>" alt="<?php echo $article->title; ?>" />
                            </a>
                        </div>
                        <?php
                    }
                }
                ?>

                <?php
                if (count($articles)) {
                    foreach ($articles as $article) {
                        ?>
                        <div class="article">
                            <div class="article-thumbnail" style="width:<?php echo $config_article->thumbnail_m_w; ?>px; height:<?php echo $config_article->thumbnail_m_h; ?>px;">
                                <a href="<?php echo url('controller=article&task=detail&article_id='.$article->id); ?>" title="<?php echo $article->title; ?>">
                                    <img src="<?php echo URL_ROOT.'/'.DATA.'/article/thumbnail/'; ?><?php echo $article->thumbnail_m == ''?('default/'.$config_article->default_thumbnail_m):$article->thumbnail_m; ?>" alt="<?php echo $article->title; ?>" />
                                </a>
                            </div>

                            <div style="margin-left:<?php echo $config_article->thumbnail_m_w; ?>px;">
                                <h4 class="article-title"><a href="<?php echo url('controller=article&task=detail&article_id='.$article->id); ?>" title="<?php echo $article->title; ?>"><?php echo $article->title; ?></a></h4>
                                <div class="article-time"><?php echo date('Y-m-d H:i:s', $article->create_time); ?></div>
                                <div class="article-summary"><?php echo $article->summary; ?></div>
                            </div>
                        </div>

                        <div class="clear-left"></div>
                        <?php
                    }
                }
                ?>
                <div style="padding:10px 0;"><?php $pagination->display(); ?></div>
                <!--{/center}-->

            </div>
        </div>

    </div>


    <div class="col" style="width:30%;">
        <div class="theme-east-container">
            <div class="theme-east">

                <!--{east}-->
                <?php
                $hottest_articles = $this->hottest_articles;
                $top_articles = $this->top_articles;

                $config_article = be::get_config('article');

                if (count($hottest_articles)) {
                    ?>
                    <div class="theme-box-container">
                        <div class="theme-box">
                            <div class="theme-box-title">热门文章</div>
                            <div class="theme-box-body">

                                <?php
                                foreach ($hottest_articles as $article) {
                                    ?>
                                    <div class="hottest-article">

                                        <div class="hottest-article-thumbnail" style="width:<?php echo $config_article->thumbnail_s_w; ?>px; height:<?php echo $config_article->thumbnail_s_h; ?>px;">
                                            <a href="<?php echo url('controller=article&task=detail&article_id='.$article->id); ?>" title="<?php echo $article->title; ?>">
                                                <img src="<?php echo URL_ROOT.'/'.DATA.'/article/thumbnail/'; ?><?php echo $article->thumbnail_s == ''?('default/'.$config_article->default_thumbnail_s):$article->thumbnail_s; ?>" alt="<?php echo $article->title; ?>" />
                                            </a>
                                        </div>

                                        <div style="margin-left:<?php echo $config_article->thumbnail_s_w; ?>px;">
                                            <h5 class="hottest-article-title"><a href="<?php echo url('controller=article&task=detail&article_id='.$article->id); ?>" title="<?php echo $article->title; ?>"><?php echo $article->title; ?></a></h5>
                                            <div class="hottest-article-time"><?php echo date('Y-m-d H:i:s', $article->create_time); ?></div>
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


                if (count($top_articles)) {
                    ?>
                    <div class="theme-box-container">
                        <div class="theme-box">
                            <div class="theme-box-title">推荐文章</div>
                            <div class="theme-box-body">

                                <?php
                                foreach ($top_articles as $article) {
                                    ?>
                                    <div class="top-article">

                                        <div class="top-article-thumbnail" style="width:<?php echo $config_article->thumbnail_s_w; ?>px; height:<?php echo $config_article->thumbnail_s_h; ?>px;">
                                            <a href="<?php echo url('controller=article&task=detail&article_id='.$article->id); ?>" title="<?php echo $article->title; ?>">
                                                <img src="<?php echo URL_ROOT.'/'.DATA.'/article/thumbnail/'; ?><?php echo $article->thumbnail_s == ''?('default/'.$config_article->default_thumbnail_s):$article->thumbnail_s; ?>" alt="<?php echo $article->title; ?>" />
                                            </a>
                                        </div>

                                        <div style="margin-left:<?php echo $config_article->thumbnail_s_w; ?>px;">
                                            <h5 class="top-article-title"><a href="<?php echo url('controller=article&task=detail&article_id='.$article->id); ?>" title="<?php echo $article->title; ?>"><?php echo $article->title; ?></a></h5>
                                            <div class="top-article-time"><?php echo date('Y-m-d H:i:s', $article->create_time); ?></div>
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

            </div>
        </div>
    </div>

    <div class="clear-left"></div>
</div>
<!--{/middle}-->
