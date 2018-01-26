
function deleteLogs(e)
{
	if( !confirm(LANG_UI_LIST_DELETE_CONFIRM) ) return;
	
	var $e = $(e);
	var sValue = $e.val();
	$e.val(g_sHandling).prop("disabled", true);

	$.ajax({
		url : "./?controller=admin_user&task=ajax_delete_logs",
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
