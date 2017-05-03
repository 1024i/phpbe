<?php
class theme extends template
{

	public function display()
	{
		$config = be::get_config('system');
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<meta name="description" content="<?php echo $this->get_meta_description();?>" />
<meta name="keywords" content="<?php echo $this->get_meta_keywords();?>" />
<title><?php echo $this->get_title().' - '.$config->site_name; ?></title>

<script src="<?php echo BONE_URL; ?>/js/jquery-1.9.1.min.js"></script>
<script src="<?php echo BONE_URL; ?>/js/jquery.validate.js"></script>

<link rel="stylesheet" href="<?php echo BONE_URL; ?>/bootstrap/2.3.1/css/bootstrap.min.css" />
<script src="<?php echo BONE_URL; ?>/bootstrap/2.3.1/js/bootstrap.min.js"></script>

<link rel="stylesheet" href="<?php echo BONE_URL; ?>/css/global.css" />
<script src="<?php echo BONE_URL; ?>/js/global.js"></script>

<link rel="stylesheet" href="<?php echo BONE_URL; ?>/themes/default/css/theme.css" />
<script src="<?php echo BONE_URL; ?>/themes/default/js/theme.js"></script>

<script>
	var BONE_URL = '<?php echo BONE_URL; ?>';
</script>

<?php $this->head(); ?>

</head>

<body>

<div class="theme-body">
<?php $this->body();?>
</div>

</body>
</html>
<?php
	}

	protected function body()
	{
	?>
<div class="theme-north">
	<?php $this->north(); ?>
</div>

<div class="theme-middle">
	<?php $this->middle(); ?>
</div>

<div class="theme-south">
	<?php $this->south(); ?>
</div>
	<?php
	}
	
	protected function north()
	{
	$config = be::get_config('system');
	?>

<div class="logo">
	<img src="<?php echo BONE_URL; ?>/images/logo.gif" alt="<?php echo $config->site_name; ?>" />
</div>

<div class="menu">
	<ul>
		<?php
		$menu_id = request::get('menu_id', 0, 'int');
		
		$north_menu = be::get_menu('north');
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
					echo '<li class="menu-on">';
				else
					echo '<li class="menu-off">';
				echo '<a href="';
				if ($menu->home)
					echo BONE_URL;
				else
					echo url($menu->url);
				echo '" target="'.$menu->target.'"><span>'.$menu->name.'</span></a>';
				echo '</li>';
			}
		}
		?>
	</ul>
</div>
<div class="clrl"></div>

	<?php
	}



	// 网页的中部
	protected function middle($option=array())
	{
	?>
<div class="theme-west">
	<div class="wraper">
		<?php $this->west(); ?>
	</div>
</div>

<div class="theme-center">
	<div class="wraper">
	<?php 
	$this->message();
	$this->center();
	?>
	</div>
</div>

<div class="clrl"></div>
	<?php
	}

	// 南部 即网页底部
	protected function south()
	{

		$south_menu = be::get_menu('south');
		$south_menu_tree = $south_menu->get_menu_tree();
		if (count($south_menu_tree)) {
			echo '<div class="foot-menu">';
			echo '<ul>';
			$i=1;
			$n=count($south_menu_tree);
			foreach ($south_menu_tree as $menu) {
				echo '<li><a href="';
				if ($menu->home)
					echo BONE_URL;
				else
					echo url($menu->url);
				echo '" target="'.$menu->target.'"><span>'.$menu->name.'</span></a></li>';
				
				if ($i<$n) echo '<li>|</li>';
				$i++;
			}
			echo '</ul>';
			echo '</div>';
		}
		
		$config = be::get_config('system');
		/* 免费使用BE系统， 请保留 www.phpbe.com 链接 */
	?>
<div class="copyright clr">
&copy;2010 版权所有: <?php echo $config->site_name; ?> &nbsp; 
使用 <a href="http://www.phpbe.com" target="_blank" title="访问BE官网">BEV<?php echo be::get_version(); ?></a> 开发
</div>
	<?php
	}
	
	
	
	// 主体的西部， 即网页主休的左部
	protected function west()
	{

$my = be::get_user();
	?>
<h2>用户登陆</h2>

<?php
if ($my->id == 0) {
?>
<ul class="login-form">
<form action="./" method="post" onSubmit="javascript: return $('#west-username').val()!='' && $('#west-password').val()!=''  ">
<li><label>用户名: </label><input type="text" name="username" id="west-username" /></li>
<li><label>密码: </label><input type="password" name="password" id="west-password" /></li>
<li><label>记住我: </label><input type="checkbox" name="rememberme" value="1"></li>
<li><label>&nbsp;</label><input type="submit" value="登陆"/> 
<a href="<?php echo url('controller=user&task=register'); ?>">注册</a>  
<a href="<?php echo url('controller=user&task=forget_password'); ?>">忘记密码?</a></li>
<input type="hidden" name="controller" value="user" />
<input type="hidden" name="task" value="login_check" />
</form>
</ul>
<?php
} else {
?>
<p>你好, <?php echo $my->username; ?></p>
<p><a href="<?php echo url('controller=user&task=edit'); ?>">修改资料</a> <a href="<?php echo url('controller=user&task=reset_password'); ?>">修改密码</a></p>
<p><input type="button" value="退出" onClick="javascript:window.location.href='<?php echo url('controller=user&task=logout'); ?>';"/></p>
<?php
}
	}
	
    protected function message()
    {
        $message = $this->get('_message');
        if ($message !== null) echo '<div class="theme-message theme-message-' . $message->type . '"><a class="close" href="javascript:;">&times;</a>' . $message->body . '</div>';
    }

}

?>
