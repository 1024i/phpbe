<?php
class template_user_register extends theme
{

	protected function head()
	{
	parent::head();
	?>
<link type="text/css" rel="stylesheet" href="<?php echo URL_ROOT; ?>/template/user/css/register.css">
<script type="text/javascript" language="javascript" src="<?php echo URL_ROOT; ?>/template/user/js/register.js"></script>
	<?php
	}
	
	protected function middle($option=array())
	{
		parent::middle(array('west'=>0, 'east'=>0));  // 不需要左右边栏
	}
	
	protected function center()
	{
		$config_user = be::get_config('user');
		?>
<?php $this->center_box_head(); ?>
<div class="theme-box-container">
	<div class="theme-box">
		<div class="theme-box-title"><?php echo $this->get_title(); ?></div>
		<div class="theme-box-body">

			<form id="form-register">

				<div class="row">
					<div class="col-8">
						<div class="key">用户名: <span class="text-required">*</span></div>
					</div>
					<div class="col-12">
						<div class="val"><input type="text" name="username" placeholder="用户名" class="input" style="width:200px;" /></div>
					</div>
					<div class="clear-left"></div>
				</div>
				
				<div class="row">
					<div class="col-8">
						<div class="key">邮箱: <span class="text-required">*</span></div>
					</div>
					<div class="col-12">
						<div class="val"><input type="text" name="email" placeholder="@" class="input" style="width:200px;" /></div>
					</div>
					<div class="clear-left"></div>
				</div>
				
				<div class="row">
					<div class="col-8">
						<div class="key">名称:</div>
					</div>
					<div class="col-12">
						<div class="val"><input type="text" name="name" class="input" style="width:120px;" /></div>
					</div>
					<div class="clear-left"></div>
				</div>
				
				<div class="row">
					<div class="col-8">
						<div class="key">密码: <span class="text-required">*</span></div>
					</div>
					<div class="col-12">
						<div class="val"><input type="password" name="password" id="middle-password" class="input" style="width:200px;" /></div>
					</div>
					<div class="clear-left"></div>
				</div>
				
				<div class="row">
					<div class="col-8">
						<div class="key">确认密码: <span class="text-required">*</span></div>
					</div>
					<div class="col-12">
						<div class="val"><input type="password" name="password2" class="input" style="width:200px;" /></div>
					</div>
					<div class="clear-left"></div>
				</div>

				<?php
				if ($config_user->captcha_register == '1') {
				?>
				<div class="row">
					<div class="col-8">
						<div class="key">验证码: <span class="text-required">*</span></div>
					</div>
					<div class="col-12">
						<div class="val">
							<input type="text" name="captcha" class="input" style="width:90px;" />
							<img src="<?php echo URL_ROOT; ?>/?controller=user&task=captcha_login" onclick="javascript:this.src='<?php echo URL_ROOT; ?>/?controller=user&task=captcha_login&_='+Math.random();" style="cursor:pointer;" />
						</div>
					</div>
					<div class="clear-left"></div>
				</div>
				<?php
				}
				?>
				
				
				<?php
				if ($config_user->connect_qq == '1' || $config_user->connect_sina == '1') {
				?>
				<div class="row">
					<div class="col-8"></div>
					<div class="col-12">
						<div class="val">
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
					</div>
					<div class="clear-left"></div>
				</div>
				<?php
				}
				?>
				
				<div class="row">
					<div class="col-8">
						<div class="key"><input type="checkbox" name="terms_and_conditions" id="terms_and_conditions" class="checkbox" /></div>
					</div>
					<div class="col-12">
						<div class="val">
							<label for="terms_and_conditions">我同意&nbsp;</label>
							<a href="<?php echo url('controller=system&task=terms_and_conditions'); ?>" target="_blank">用户使用条款</a>
						</div>
					</div>
					<div class="clear-left"></div>
				</div>
				
				<div class="row" style="margin-top:20px;">
					<div class="col-8"></div>
					<div class="col-12">
						<div class="val">
							<input type="submit" class="btn btn-primary btn-submit"  value="注册" />
							<input type="reset" class="btn"  value="重设" />
						</div>
					</div>
					<div class="clear-left"></div>
				</div>

			</form>

		</div>
	</div>
</div>
<?php $this->center_box_foot(); ?>
		<?php
	}		
		

}
?>