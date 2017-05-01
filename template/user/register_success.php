<?php
class template_user_register_success extends theme
{
	protected function head()
	{
	parent::head();
	?>
<link type="text/css" rel="stylesheet" href="<?php echo URL_ROOT; ?>/template/user/css/register_success.css">
	<?php
	}

	protected function middle($option=array())
	{
		parent::middle(array('west'=>0, 'east'=>0));  // 不需要左右边栏
	}
	
	protected function center()
	{
		$config_user = be::get_config('user');
		$username = $this->get('username');
		$email = $this->get('email');
		?>

<?php $this->center_box_head(); ?>
<div class="theme-box-container">
	<div class="theme-box">
		<div class="theme-box-title"><?php echo $this->get_title(); ?></div>
		<div class="theme-box-body">
		
			<div class="align-center">
				<div class="notice" style="border:<?php echo $this->get_color(5); ?> 1px solid; color:<?php echo $this->get_color(); ?>; background-color:<?php echo $this->get_color(9); ?>;">
					<span>注册成功！</span>
				</div>
			</div>
			
			
			<div class="row">
				<div class="col-10">
					<div class="key">用户名: </div>
				</div>
				<div class="col-10">
					<div class="val"><div class="text-success"><?php echo $username; ?></div></div>
				</div>
				<div class="clear-left"></div>
			</div>

			<div class="row">
				<div class="col-10">
					<div class="key">邮箱: </div>
				</div>
				<div class="col-10">
					<div class="val"><div class="text-success"><?php echo $email; ?></div></div>
				</div>
				<div class="clear-left"></div>
			</div>
			

			<div class="actions">
				<?php
				if ($config_user->email_valid == '1') {
					echo '一封包含验证链接的邮件已发送到您的邮箱。';
				} else {
					?>
					<a href="<?php echo url('controller=user&task=login'); ?>" class="btn btn-primary btn-large">登陆</a>
					<?php
				}
				?>
			</div>
			
			
		</div>
	</div>
</div>
<?php $this->center_box_foot(); ?>

		<?php
	}		
		

}
?>