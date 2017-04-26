<?php
namespace template\article;


class articles extends \system\template
{

	protected function head()
	{
	?>
<link type="text/css" rel="stylesheet" href="<?php echo URL_ROOT; ?>/template/article/css/articles.css">
	<?php
	}
	

	protected function center()
	{
		$menu_id = $this->get('menu_id');
		$category_id = $this->get('category_id');
		$articles = $this->get('articles');
		$categories = $this->get('categories');
		
		$pagination = $this->get('pagination');
		
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

	<?php
	}	


	protected function east()
	{
		$hottest_articles = $this->get('hottest_articles');
		$top_articles = $this->get('top_articles');
		
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
		
	}

}
?>