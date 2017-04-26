
function deleteApp(e, sAppName)
{
	
	if(!confirm("该操作将删除该应用生成的所有数据，且不可恢复，确认卸载吗？")) return;
	
	var $e = $(e);
	var sHtml = $e.html();
	$e.html( g_sHandling );
	$e.addClass('disabled');
	
	$.ajax({
		url: './?controller=system&task=ajax_uninstall_app&app_name='+sAppName,
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