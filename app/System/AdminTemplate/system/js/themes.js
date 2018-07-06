
function setDefault( theme )
{
	if($("#default-"+theme).hasClass("default-1")) return;
	
	$.ajax({
		type: "GET",
		url: "./?controller=system&action=ajax_theme_set_default&theme="+theme,
		dataType: "json",
		success: function(json){
			if(json.error=="0")
			{
				$(".default-1").removeClass("default-1").addClass("default-0");
				$("#default-"+theme).removeClass("default-0").addClass("default-1");
				
				$(".ui-row .delete").show();
				$(".delete", $("#default-"+theme).closest(".ui-row")).hide();
				
			}
			else
			{
				alert(json.message);
			}
		}
	});	
	
}



function deleteTheme(e, theme)
{
	if(!confirm("该操作不可恢复，确认要卸载吗？")) return;
	
	var $e = $(e);
	var sHtml = $e.html();
	$e.html('<img src="../images/loading.gif" alt="处理中..." align="absmiddle" /> 正在卸载...');
	$e.addClass('disabled');
	
	$.ajax({
		url: './?controller=system&action=ajax_uninstall_theme&theme='+theme,
		dataType: 'json',
		success: function(json)
		{
			$e.removeClass('disabled');
			$e.html( sHtml );
			
			alert(json.message);
			if(json.error=='0')
			{
				window.location.reload();
			}
		}
	});
}


