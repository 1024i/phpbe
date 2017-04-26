
$(function(){
	
	$.ajaxSetup({cache: false});

	// 提示信息
	$(".theme-message .close").click(function(){
		$(this).parent().fadeOut();					   
	});	
	setTimeout(function(){$(".theme-message").fadeOut();}, 5000);
	
});
