<?php
class theme extends template
{

	protected $primary_color = '#ff3300';		// 主题 主色

	public function display()
	{
		$config = bone::get_config('system');
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<meta name="description" content="<?php echo $this->get_meta_description();?>" />
<meta name="keywords" content="<?php echo $this->get_meta_keywords();?>" />
<base href="<?php echo BONE_URL; ?>/" />
<title><?php echo $this->get_title().' - '.$config->site_name; ?></title>

<script src="<?php echo BONE_URL; ?>/js/jquery-1.9.1.min.js"></script>
<script src="<?php echo BONE_URL; ?>/js/jquery.validate.js"></script>

<link rel="stylesheet" href="<?php echo BONE_URL; ?>/themes/sample/css/bone.css" />
<script src="<?php echo BONE_URL; ?>/themes/sample/js/bone.js"></script>

<script src="<?php echo BONE_URL; ?>/js/global.js"></script>

<link rel="stylesheet" href="<?php echo BONE_URL; ?>/themes/sample/css/theme.css" />
<script src="<?php echo BONE_URL; ?>/themes/sample/js/theme.js"></script>

<script>
	var BONE_URL = '<?php echo BONE_URL; ?>';
</script>

<?php $this->head(); ?>

</head>
<body>
	<div class="theme-body-container">
		<div class="theme-body">
			<?php $this->body();?>
		</div>
	</div>
</body>
</html>
<?php
	}

	protected function body()
	{
	?>
<div class="theme-north-container">
	<div class="theme-north">
		<?php $this->north(); ?>
	</div>
</div>

<div class="theme-middle-container">
	<div class="theme-middle">
		<?php $this->middle(); ?>
	</div>
</div>

<div class="theme-south-container">
	<div class="theme-south">
		<?php $this->south(); ?>
	</div>
</div>
	<?php
	}
	
	protected function north()
	{
	$config = bone::get_config('system');
	?>
<div class="row">
	<div class="col-5">
		<img src="<?php echo BONE_URL; ?>/images/logo.png" alt="<?php echo $config->site_name; ?>" />
	</div>
	<div class="col-15">
	
		<div class="menu">
			<ul class="inline">
				<?php
				$north_menu = bone::get_menu('north');
				$north_menu_tree = $north_menu->get_menu_tree();
				
				if (count($north_menu_tree)) {
					foreach ($north_menu_tree as $menu) {
					
						$menu_on = true;
						if (count($menu->params)) {
							foreach ($menu->params as $key=>$val) {
								if (get::_($key)!=$val) {
									$menu_on = false;
								}
							}
						} else {
							$menu_on = false;
						}
						
						if ($menu_on)
							echo '<li class="active">';
						else
							echo '<li>';
						echo '<a href="';
						if ($menu->home)
							echo BONE_URL;
						else
							echo $menu->url;
						echo '" target="'.$menu->target.'"><span>'.$menu->name.'</span></a>';
						echo '</li>';
					}
				}
				?>
			</ul>
		</div>
		
	</div>
</div>
	<?php
	}



	// 网页的中部
	protected function middle($option=array())
	{
	?>
<div class="row">
	<div class="col-5">
	
		<div class="theme-west-container">
			<div class="theme-west">
				<?php $this->west(); ?>
			</div>
		</div>
		
	</div>
	<div class="col-15">
	
		<div class="theme-center-container">
			<div class="theme-center">
				<?php 
				$this->message();
				$this->center();
				?>
			</div>
		</div>
		
	</div>
</div>
	<?php
	}

	// 南部 即网页底部
	protected function south()
	{

		$south_menu = bone::get_menu('south');
		$south_menu_tree = $south_menu->get_menu_tree();
		if (count($south_menu_tree)) {
			echo '<div class="menu">';
			echo '<ul class="inline">';
			$i=1;
			$n=count($south_menu_tree);
			foreach ($south_menu_tree as $menu) {
				echo '<li><a href="';
				if ($menu->home)
					echo BONE_URL;
				else
					echo $menu->url;
				echo '" target="'.$menu->target.'"><span>'.$menu->name.'</span></a></li>';
				
				if ($i<$n) echo '<li>|</li>';
				$i++;
			}
			echo '</ul>';
			echo '</div>';
		}
		
		$config = bone::get_config('system');		
		/* 免费使用骨头系统， 请保留 www.mrbone.org 链接 */
	?>
<div class="copyright">
&copy;2010 版权所有: <?php echo $config->site_name; ?> &nbsp; 
使用 <a href="http://www.mrbone.org" target="_blank" title="访问骨头官网">骨头v<?php echo bone::get_version(); ?></a> 开发
</div>
	<?php
	}
	
	
	
	// 主体的西部， 即网页主休的左部
	protected function west()
	{

$my = bone::get_user();
	?>
	
<div class="theme-box-container">
	<div class="theme-box">
		<div class="theme-box-title">用户登陆</div>
		<div class="theme-box-body">

			<?php
			if ($my->id == 0) {
			?>
			<form action="<?php echo url('controller=user&task=login_check'); ?>" method="post" onSubmit="javascript: return $('#west-username').val()!='' && $('#west-password').val()!=''  ">
				<div class="row" style="padding:3px 0px;">
					<div class="col-8"><label>用户名：</label></div>
					<div class="col-12"><input type="text" name="username" id="west-username" placeholder="用户名" style="width:120px;" /></div>
				</div>
				
				<div class="row" style="padding:3px 0px;">
					<div class="col-8"><label>密码：</label></div>
					<div class="col-12"><input type="password" name="password" id="west-password" style="width:120px;" /></div>
				</div>
				
				<div class="row" style="padding:3px 0px;">
					<div class="col-8"><label>记住我：</label></div>
					<div class="col-12"><input type="checkbox" name="rememberme" value="1"></div>
				</div>
				
				<div class="row" style="padding:3px 0px;">
					<div class="col-12 col-offset-8">
						<input type="submit" class="btn btn-primary"  value="登陆"/>
					</div>
				</div>
			</form>
			<?php
			} else {
			?>
			<p>你好, <?php echo $my->username; ?></p>
			<p><a href="<?php echo url('controller=user_profile&task=dashboard'); ?>">用户中心</a></p>
			<p><a href="<?php echo url('controller=user&task=logout'); ?>" class="btn btn-primary">退出</a></p>
			<?php
			}
			?>
		</div>
	</div>
</div>
<?php
	}


}

?>
