
function deleteLogs(e)
{
	if( !confirm("确认删除么？") ) return;
	
	var $e = $(e);
	var sValue = $e.val();
	$e.val(g_sHandling).prop("disabled", true);

	$.ajax({
		url : "./?app=System&controller=AdminUser&action=ajax_delete_logs",
		dataType : "json",
		success : function(json)
		{
			$e.prop("disabled", false).val(sValue);
			
			if(json.success)
			{
				window.location.reload();
			}
			else
			{
				alert(json.message);
			}
		}
	});
	
}
