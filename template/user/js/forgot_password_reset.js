$(function(){

	$("#form-forgot_password_reset").validate({
		rules: {
			password: {
				required: true,
				minlength: 6
			},
			password2: {
				required: true,
				equalTo: "#middle-password"
			}
		},
		messages: {
			password: {
				required: "密码不能为空！",
				minlength: "密码至少{0}个字符！"
			},
			password2: {
				required: "确认密码不能为空！",
				equalTo: "两次输入的密码不匹配！"
			}
		},
		
		submitHandler: function(form){
			
			var $submit = $(".btn-submit", $(form));
			var sValue = $submit.val();
			
			$submit.prop("disabled", true).val("处理中，请稍候...");

			$.ajax({
				type: "POST",
				url: URL_ROOT+"/?controller=user&task=ajax_forgot_password_reset_save",
				data: $(form).serialize(),
				dataType: "json",
				success: function(json){
					
					$submit.prop("disabled", false).val(sValue);
					
					alert( json.message );
					if(json.error=='0')
					{
						window.location.href = URL_ROOT+"/?controller=user&task=login";
					}
				}
			});	
			
			
		}
	});

});