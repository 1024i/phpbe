<?php
namespace theme\huxiu;

use system\be;
use system\request;

class huxiu extends \system\theme
{

	protected $colors = array('#2D2D2D');		// 主题 主色

	protected function head()
	{
	 parent::head();  // 如果自定义了 be.css / be.js 屏敝父类 head
?>
<link rel="stylesheet" href="<?php echo URL_ROOT; ?>/theme/huxiu/css/be.css" />
<script src="<?php echo URL_ROOT; ?>/theme/huxiu/js/be.js"></script>

<link rel="stylesheet" href="<?php echo URL_ROOT; ?>/theme/huxiu/css/theme.css" />
<script src="<?php echo URL_ROOT; ?>/theme/huxiu/js/theme.js"></script>
<?php
	}

	protected function north()
	{
	$config_system = be::get_config('system');
	$config_user = be::get_config('user');

	$menu_id = $this->template->get('menu_id', 0);
	
	$my = be::get_user();
	?>
<div class="row">
	<div class="col-3">
		<img src="<?php echo URL_ROOT; ?>/theme/huxiu/images/logo.gif" alt="<?php echo $config_system->site_name; ?>" />
	</div>
	<div class="col-17">
	
		<div class="login-form">
			
			<?php
			if (!isset($my->id) || $my->id == 0) {
			?>
			<a href="<?php echo url('controller=user&task=login'); ?>">登陆</a><a href="<?php echo url('controller=user&task=register'); ?>">注册</a>
			<?php
			} else {
			?>
			<img src="<?php echo URL_ROOT.'/'.DATA.'/user/avatar/'.($my->avatar_l == ''?('default/'.$config_user->default_avatar_l):$my->avatar_l); ?>" />
			<a href="<?php echo url('controller=user_profile&task=home'); ?>"><?php echo $my->name; ?></a>
			<input type="button" class="btn btn-small btn-warning" onclick="javascript:window.location.href='<?php echo url('controller=user&task=logout'); ?>';" value="退出" />
			<?php
			}
			?>
		</div>
		<div class="menu">
			<ul class="inline">
				<?php
				$north_menu = be::get_menu('north');
				$north_menu_tree = $north_menu->get_menu_tree();
				
				if (count($north_menu_tree)) {
					foreach ($north_menu_tree as $menu) {
						$menu_on = true;
						if ($menu_id>0) {
							$menu_on = $menu->id == $menu_id?true:false;
						}
						elseif (count($menu->params)) {
							foreach ($menu->params as $key=>$val) {
								if (request::get($key, '')!=$val) {
									$menu_on = false;
									break;
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
							echo URL_ROOT;
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
	<div class="clear-left"></div>
</div>
	<?php
	}


	protected function middle()
	{
		$template = $this->template;
		$template_methods = $this->template_methods;

		$west = in_array('west', $template_methods);
		$east = in_array('east', $template_methods);

		?>
		<div class="row">

			<div class="col" style="width:<?php echo (!$west && !$east)?100:70; ?>%;">

				<div class="theme-center-container">
					<div class="theme-center">
						<?php
						$this->message();

						$template->center();
						?>
					</div>
				</div>

			</div>

			<?php
			if ($west || $east) {
				?>
				<div class="col" style="width:30%;">
					<div class="theme-east-container">
						<div class="theme-east">
							<?php
							if ($east) $template->east();
							else $template->west();
							?>
						</div>
					</div>
				</div>
			<?php
			}
			?>
			<div class="clear-left"></div>
		</div>
	<?php
	}



	// 南部 即网页底部
	protected function south()
	{
		$south_menu = be::get_menu('south');
		$south_menu_tree = $south_menu->get_menu_tree();
		if (count($south_menu_tree)) {
			echo '<div class="menu">';
			echo '<ul>';
			$i=1;
			$n=count($south_menu_tree);
			foreach ($south_menu_tree as $menu) {
				echo '<li><a href="';
				if ($menu->home)
					echo URL_ROOT;
				else
					echo $menu->url;
				echo '" target="'.$menu->target.'"><span>'.$menu->name.'</span></a></li>';
				
				if ($i<$n) echo '<li>|</li>';
				$i++;
			}
			echo '</ul>';
			echo '</div>';
		}
	?>
<div class="copyright">
	<?php echo be::get_html('copyright'); ?>
</div>
	<?php
	}
	
	
}

?>
