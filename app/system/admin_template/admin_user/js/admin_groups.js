$(function(){
	$("#admin_ui_category_row_1").addClass("success");
	$("#admin_ui_category_row_1 input").prop("disabled", true);
	$("#admin_ui_category_row_1 .delete").fadeOut();
	
	$(".ui-row").each(function(){
		if($(".user_count", $(this)).length>0) $(".delete", $(this)).fadeOut();
	});
});	
