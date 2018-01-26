$(function(){
	$('#ajax-loader').remove();
	
	$('#input-username,#input-password').keypress(function(e){
		if(e.which==13) login();
	});

});


function chkForm()
{
	var e;
	
	e = document.getElementById("input-username");
	if(e.value=="")
	{
		$("#login-msg").attr("class", "message-error").html("用户名不能为空！");
		e.focus();
		return false;
	}
	
	e = document.getElementById("input-password");
	if(e.value=="")
	{
		$("#login-msg").attr("class", "message-error").html("密码不能为空！");
		e.focus();
		return false;
	}
	
	return true;
}


function login()
{
	if(!chkForm()) return;
	
	var $e = $("#btn-login");
	var sValue = $e.val();
	$e.attr('disabled', 'disabled').val("登陆中，请稍候...");
	
	$("#login-msg").removeClass().html(g_sLoadingImage + "登陆中，请稍候...");
	
	$.ajax({
		type: "POST",
		url: "./?controller=admin_user&task=ajax_login_check",
		data: $("#form-login").serialize(),
		dataType: "json",
		success: function(json){
			$e.removeAttr('disabled').val(sValue);
			if(json.error=="0")
			{
				$("#login-msg").attr("class", "message-success").html(json.message);
				
				var sReturn = $("#return").val();
				if(sReturn=="") sReturn = "./?controller=system&task=dashboard";
				window.location.href = sReturn;
			}
			else
			{
				$("#login-msg").attr("class", "message-error").html(json.message);
			}

		}
	});

}