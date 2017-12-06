<!--{head}-->
<link type="text/css" rel="stylesheet" href="<?php echo URL_ROOT; ?>/app/system/template/user/css/login.css">
<script type="text/javascript" language="javascript" src="<?php echo URL_ROOT; ?>/app/system/template/user/js/login.js"></script>
<!--{/head}-->

<!--{middle}-->
<div class="theme-box-container">
	<div class="theme-box">
		<div class="theme-box-title"><?php echo $this->get_title(); ?></div>
		<div class="theme-box-body">
		
			<form id="form-login">
				<div class="row">
					<div class="col-8">
						<div class="key">用户名：<span class="text-required">*</span></div>
					</div>
					<div class="col-12">
						<div class="val"><input type="text" name="username" class="input" placeholder="用户名" style="width:200px;" /></div>
					</div>
					<div class="clear-left"></div>
				</div>
				
				<div class="row">
					<div class="col-8">
						<div class="key">密码：<span class="text-required">*</span></div>
					</div>
					<div class="col-12">
						<div class="val"><input type="password" name="password" class="input" placeholder="密码"  style="width:200px;" /></div>
					</div>
					<div class="clear-left"></div>
				</div>
				
				<?php
				if ($config_user->captcha_login == '1') {
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
				
				<div class="row">
					<div class="col-8"></div>
					<div class="col-12">
						<div class="val">
							<a href="<?php echo url('controller=user&task=forgot_password'); ?>">忘记密码？</a>
						</div>
					</div>
					<div class="clear-left"></div>
				</div>
				
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
							<a href="<?php echo url('controller=user&task=qq_login'); ?>"><img src="<?php echo URL_ROOT; ?>/app/system/template/user/images/qq_login.png" /></a> &nbsp;
							<?php
							}
							
							if ($config_user->connect_sina == '1') {
							?>
							<a href="<?php echo url('controller=user&task=sina_login'); ?>"><img src="<?php echo URL_ROOT; ?>/app/system/template/user/images/sina_login.png" /></a>
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

				<div class="row" style="margin-top:20px;">
					<div class="col-8"></div>
					<div class="col-12">
						<div class="val">
							<input type="submit" class="btn btn-primary btn-submit"  value="登陆"/>
							<input type="reset" class="btn" value="重设"/>
						</div>
					</div>
					<div class="clear-left"></div>
				</div>
				
				<input type="hidden" name="return" value="<?php echo $this->get('return'); ?>" />
			</form>

		</div>
	</div>
</div>
<!--{/head}-->