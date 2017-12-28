
function deleteLogs(e)
{
	if( !confirm("该操作不可恢复, 确认要删除吗？") ) return;
	var $e = $(e);
	var sValue = $e.val();
	$e.val("删除中...").prop("disabled", true);

	$.ajax({
		url : "./?controller=system&task=ajax_delete_logs",
		dataType : "json",
		success : function(json)
		{
			$e.prop("disabled", false).val(sValue);
			
			if(json.error=="0")
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
