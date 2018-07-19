<!--{head}-->
<link type="text/css" rel="stylesheet" href="/app/System/Template/User/css/login.css">
<script type="text/javascript" language="javascript" src="/app/System/Template/User/js/login.js"></script>
<!--{/head}-->

<!--{middle}-->
<div class="theme-box-container">
	<div class="theme-box">
		<div class="theme-box-title"><?php echo $this->getTitle(); ?></div>
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
				if ($configUser->captchaLogin == '1') {
				?>
				<div class="row">
					<div class="col-8">
						<div class="key">验证码: <span class="text-required">*</span></div>
					</div>
					<div class="col-12">
						<div class="val">
							<input type="text" name="captcha" class="input" style="width:90px;" />
							<img src="<?php echo url('app=System&controller=User&action=captchaLogin'); ?>" onclick="javascript:this.src='/?app=System&controller=User&action=captchaLogin&_='+Math.random();" style="cursor:pointer;" />
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
							<a href="<?php echo url('app=System&controller=User&action=forgotPassword'); ?>">忘记密码？</a>
						</div>
					</div>
					<div class="clear-left"></div>
				</div>
				
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
							<a href="<?php echo url('app=System&controller=User&action=qqLogin'); ?>"><img src="/app/System/Template/User/images/qqLogin.png" /></a> &nbsp;
							<?php
							}
							
							if ($configUser->connectSina == '1') {
							?>
							<a href="<?php echo url('app=System&controller=User&action=sinaLogin'); ?>"><img src="/app/System/Template/User/images/sinaLogin.png" /></a>
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