<?php
use Phpbe\System\Be;
?>
<!--{head}-->
<link type="text/css" rel="stylesheet" href="<?php echo Be::getRuntime()->getUrlRoot(); ?>/app/System/Template/User/css/register.css">
<script type="text/javascript" language="javascript" src="<?php echo Be::getRuntime()->getUrlRoot(); ?>/app/System/Template/User/js/register.js"></script>
<!--{/head}-->

<!--{middle}-->
<!--{center}-->
<?php
$configUser = Be::getConfig('System', 'User');
?>
<div class="theme-box-container">
	<div class="theme-box">
		<div class="theme-box-title"><?php echo $this->title; ?></div>
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
				if ($configUser->captchaRegister == '1') {
				?>
				<div class="row">
					<div class="col-8">
						<div class="key">验证码: <span class="text-required">*</span></div>
					</div>
					<div class="col-12">
						<div class="val">
							<input type="text" name="captcha" class="input" style="width:90px;" />
							<img src="<?php echo Be::getRuntime()->getUrlRoot(); ?>/?controller=user&action=captchaLogin" onclick="javascript:this.src='<?php echo Be::getRuntime()->getUrlRoot(); ?>/?controller=user&action=captchaLogin&_='+Math.random();" style="cursor:pointer;" />
						</div>
					</div>
					<div class="clear-left"></div>
				</div>
				<?php
				}
				?>
				
				
				<?php
				if ($configUser->connectQq == '1' || $configUser->connectSina == '1') {
				?>
				<div class="row">
					<div class="col-8"></div>
					<div class="col-12">
						<div class="val">
						<?php
						if ($configUser->connectQq == '1') {
						?>
						<a href="<?php echo url('controller=user&action=qqLogin'); ?>"><img src="<?php echo Be::getRuntime()->getUrlRoot(); ?>/app/System/Template/User/images/qqLogin.png" /></a> &nbsp;
						<?php
						}
						
						if ($configUser->connectSina == '1') {
						?>
						<a href="<?php echo url('controller=user&action=sinaLogin'); ?>"><img src="<?php echo Be::getRuntime()->getUrlRoot(); ?>/app/System/Template/User/images/sinaLogin.png" /></a>
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
						<div class="key"><input type="checkbox" name="termsAndConditions" id="termsAndConditions" class="checkbox" /></div>
					</div>
					<div class="col-12">
						<div class="val">
							<label for="termsAndConditions">我同意&nbsp;</label>
							<a href="<?php echo url('controller=system&action=termsAndConditions'); ?>" target="Blank">用户使用条款</a>
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
<!--{/center}-->
<!--{/middle}-->