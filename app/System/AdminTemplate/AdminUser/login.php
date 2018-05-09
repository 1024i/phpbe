<?php
use Phpbe\System\Be;
use Phpbe\System\Request;
?>
<!--{head}-->
<link type="text/css" rel="stylesheet" href="template/adminUser/css/login.css">
<script type="text/javascript" language="javascript" src="template/adminUser/js/login.js"></script>
<!--{/head}-->

<!--{body}-->
<?php
$config = Be::getConfig('System.System');
?>
<div class="body">
	
	<div class="logo"></div>

    <!--{message}--><!--{/message}-->
	
	<div class="login-box">

		<div class="login-box-tl"><div class="login-box-tr"></div></div>
		
		<div class="login-box-ml">
			<div class="login-box-mr">

				<form id="form-login" style="margin:0; padding:12px 0;">
				
				<div class="input-prepend">
					<span class="add-on"><i class="icon icon-user"></i></span>
					<input style="width:160px;" type="text" name="username" id="input-username" placeholder="用户名" >
				</div>
				
				<div class="input-prepend">
					<span class="add-on"><i class="icon icon-lock"></i></span>
					<input style="width:160px;" type="password" name="password" id="input-password" placeholder="密码">
				</div>
				
				<div style="text-align:right; width:220px;">
					<input type="button" class="btn btn-danger" id="btn-login" onclick="javascript:login();" value="登陆" />
				</div>
				
				<?php
				$return = Request::get('return','');
				if ($return!='') $return = base64_decode($return);
				?>
				<input type="hidden" id="return" value="<?php echo $return; ?>" />

				</form>

			</div>
		</div>

		<div class="login-box-bl"><div class="login-box-br"></div></div>
		
		<div id="login-msg"></div>
	
	</div>

</div>
<!--{/body}-->