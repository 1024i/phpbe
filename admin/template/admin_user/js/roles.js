$(function(){
	$("#admin_ui_category_row_1").addClass("success");
	$("#admin_ui_category_row_1 input").prop("disabled", true);
	$("#admin_ui_category_row_1 .delete").fadeOut();
	
	updateRows();
});	

function updateRows()
{
	$(".ui-row").each(function(){
		var $thie = $(this);
		if($(".user_count", $thie).length>0 || $(".icon-default-1", $thie).length>0)
		{
			$(".delete", $thie).fadeOut();
		}
		else
		{
			$(".delete", $thie).fadeIn();
		}
	});
}
