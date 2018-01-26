<?php
use System\Be;
use System\Request;
?>

<!--{head}-->
<link type="text/css" rel="stylesheet" href="<?php echo URL_ROOT; ?>/app/System/template/user/css/forgotPasswordReset.css">
<script type="text/javascript" language="javascript" src="<?php echo URL_ROOT; ?>/app/System/template/user/js/forgotPasswordReset.js"></script>
<!--{/head}-->

<!--{middle}-->
<!--{center}-->
<?php
$user = $this->user;
?>
<div class="theme-box-container">
	<div class="theme-box">
		<div class="theme-box-title"><?php echo $this->title; ?></div>
		<div class="theme-box-body">
			<form id="form-forgotPasswordReset">
		
		
				<div class="row">
					<div class="col-8">
						<div class="key">用户名: </div>
					</div>
					<div class="col-12">
						<div class="val"><?php echo $user->username; ?></div>
					</div>
				</div>
				
				<div class="row">
					<div class="col-8">
						<div class="key">邮箱: </div>
					</div>
					<div class="col-12">
						<div class="val"><?php echo $user->email; ?></div>
					</div>
				</div>
				
				<div class="row">
					<div class="col-8">
						<div class="key">新密码: <span class="text-required">*</span></div>
					</div>
					<div class="col-12">
						<div class="val"><input type="password" name="password" class="input" id="middle-password" style="width:200px;" /></div>
					</div>
				</div>
				
				<div class="row">
					<div class="col-8">
						<div class="key">确认新密码: <span class="text-required">*</span></div>
					</div>
					<div class="col-12">
						<div class="val"><input type="password" name="password2" class="input" style="width:200px;" /></div>
					</div>
				</div>
				
				<div class="row" style="margin-top:20px;">
					<div class="col-8"></div>
					<div class="col-12">
						<input type="submit" class="btn btn-primary btn-submit"  value="重设密码"/>
						<input type="reset" class="btn"  value="重置"/>
					</div>
				</div>

				<input type="hidden" name="userId" value="<?php echo Request::get('userId', 0, 'int'); ?>" />
				<input type="hidden" name="token" value="<?php echo Request::get('token',''); ?>" />

			</form>

		</div>
	</div>
</div>
<!--{/center}-->
<!--{/middle}-->