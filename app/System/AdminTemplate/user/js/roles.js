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


function setDefault( e, iGroupID )
{
	var $e = $(e);
	if($e.hasClass("icon-default-1")) return;
	
	$.ajax({
		type: "GET",
		url: "./?controller=user&action=ajax_group_set_default&group_id="+iGroupID,
		dataType: "json",
		success: function(json){
			if(json.error=="0")
			{
				$(".icon-default-1").removeClass("icon-default-1").addClass("icon-default-0");
				$e.removeClass("icon-default-0").addClass("icon-default-1");
				
				updateRows();
			}
			else
			{
				alert(json.message);
			}
		}
	});	
}