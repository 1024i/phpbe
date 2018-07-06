
var g_iMenuID = 0;
var g_sUrl = '';
var g_sParams = '';

$(function(){
	$(".menu-url").keypress(function(){
		$(this).next().val("");
	});
});

function setMenu(e)
{
	var $e = $(e).parent().parent();
	var sId = $e.attr("id");
	var id = sId.substr(sId.lastIndexOf("_")+1);

	g_iMenuID = id;
	
	var sUrl = $("#admin_ui_category_tree_row_"+g_iMenuID+" .menu-url").val();

	$("#modal-menu").modal();
	disableSaveMenu(true);
	$("#modal-menu-body").load( './?controller=system&action=menu_set_link&id='+id+'&url='+base64.encode(sUrl) );
}


function disableSaveMenu( b )
{
	$("#modal-menu-save-button").prop("disabled", b);
}

function saveMenu()
{	
	$("#admin_ui_category_tree_row_"+g_iMenuID+" .menu-url").val( g_sUrl );
	$("#admin_ui_category_tree_row_"+g_iMenuID+" .menu-params").val( g_sParams );
	$("#modal-menu").modal('hide');
}



function setHome(id)
{
	if($("#home-"+id).hasClass("home-1")) return;
	
	$e = $(".home-1");
	$.ajax({
		type: "GET",
		url: "./?controller=system&action=ajax_menu_set_home&id="+id,
		dataType: "json",
		success: function(json){
			if(json.error=="0")
			{
				$e.removeClass("home-1").addClass("home-0");
				$("#home-"+id).removeClass("home-0").addClass("home-1");
			}
			else
			{
				alert(json.message);
			}
		}
	});	
}