$(function(){
	
	$.ajaxSetup({cache: false});

	// 提示信息
	$(".theme-message .close").click(function(){
		$(this).parent().fadeOut();
		return false;
	});	
	setTimeout(function(){$(".theme-message").fadeOut();}, 5000);
	
});


$.validator.setDefaults({
	highlight:function(element){ $(element).closest(".row").removeClass("success").addClass("error"); },
	success:function(element){ $(element).closest(".row").removeClass("error").addClass("success");$(element).remove(); },
});