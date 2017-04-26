<?php


class template_article_detail extends theme
{

	protected function head()
	{
	parent::head();
	?>
<link type="text/css" rel="stylesheet" href="<?php echo URL_ROOT; ?>/template/article/css/listing.css">
	
<script type="text/javascript" language="javascript" src="<?php echo URL_ROOT; ?>/template/article/js/detail.js"></script>
<link type="text/css" rel="stylesheet" href="<?php echo URL_ROOT; ?>/template/article/css/detail.css">
	<?php
	}

	protected function middle($option=array())
	{
		parent::middle(array('west'=>0, 'east'=>30));
	}

	public function center()
	{
	$article = $this->get('article');
	$similar_articles = $this->get('similar_articles');
	$comments = $this->get('comments');
	
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

	<?php
	if ($config_article->comment == 1) {
	?>
	<div class="comments">
		
		<?php
		if (!isset($my->id) || $my->id == 0) {
			?>
			发布评论？请先 <a href="<?php echo url('controller=user&task=register'); ?>">注册</a> 或 <a href="<?php echo url('controller=user&task=login'); ?>">登陆</a>
			<?php
			if ($config_user->connect_qq == '1' || $config_user->connect_sina == '1') {
			?>
			<div style="padding:20px 0;">
				<?php
				if ($config_user->connect_qq == '1') {
				?>
				<a href="<?php echo url('controller=user&task=qq_login'); ?>"><img src="<?php echo URL_ROOT; ?>/template/user/images/qq_login.png" /></a> &nbsp;
				<?php
				}
				
				if ($config_user->connect_sina == '1') {
				?>
				<a href="<?php echo url('controller=user&task=sina_login'); ?>"><img src="<?php echo URL_ROOT; ?>/template/user/images/sina_login.png" /></a>
				<?php
				}
				?>
			</div>
			<?php
			}
		} else {
		?>
		<div class="comment-form">
			<form id="form-comment">
				<div class="row">
					<div class="col-3 text-center">
						<img src="<?php echo URL_ROOT.'/'.DATA.'/user/avatar/'.($my->avatar_m == ''?('default/'.$config_user->default_avatar_m):$my->avatar_m); ?>" />
					</div>
					<div class="col-17">
						<textarea name="body" style="width:100%; height:135px;"></textarea>
					</div>
				</div>
	
				<div class="row" style="padding:10px 0px;">
					<div class="col-17 col-offset-3 text-right">
						<input type="submit" class="btn btn-primary btn-submit" value="发表评论" />
					</div>
				</div>
				<input type="hidden" name="article_id" value="<?php echo $article->id; ?>" />
			</form>
		</div>	
		<?php
		}
		
		
		if (count($comments)) {
			foreach ($comments as $comment) {
				$comment_user = be::get_user($comment->user_id);
				?>
				<div class="comment">
					<div class="row">
						<div class="col-3 text-center">
							<img src="<?php echo URL_ROOT.'/'.DATA.'/user/avatar/'.($comment_user->avatar_m == ''?('default/'.$config_user->default_avatar_m):$comment_user->avatar_m); ?>" />
						</div>
						<div class="col-17">
							<div class="comment-user_name"><?php echo $comment_user->name; ?></div>
							
							<div class="comment-body">
								<?php echo $comment->body; ?>
							</div>
							
							<div class="row" style="padding:12px 0;">
								<div class="col-5">
									<div class="comment-time"><?php echo format_time($comment->create_time); ?></div>
								</div>
								
								<div class="col-15 text-right">
									<div class="comment-vote">
										<a href="javascript:;" onclick="javascript:commentLike(<?php echo $comment->id; ?>);">顶<?php echo $comment->like>0?('('.$comment->like.')'):''; ?></a> &nbsp; 
										<a href="javascript:;" onclick="javascript:commentDislike(<?php echo $comment->id; ?>);">踩<?php echo $comment->dislike>0?('('.$comment->dislike.')'):''; ?></a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php
			}
		}
		?>
	</div>
	<?php
	}
	?>

	<?php
	}

	protected function east()
	{
		$hottest_articles = $this->get('hottest_articles');
		$top_articles = $this->get('top_articles');
		
		$template = be::get_template('article.articles');
		$template->set('hottest_articles', $hottest_articles);
		$template->set('top_articles', $top_articles);
		$template->east();
	}

}
?>