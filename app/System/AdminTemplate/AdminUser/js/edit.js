function hidePassword()
{
	$("#password, #password2").val("").attr("disabled", "disabled");
}


function changePassword(bool)
{
	if(bool)
		$("#password, #password2").attr("disabled", false);
	else
		$("#password, #password2").val("").attr("disabled", "disabled");
}

function deleteAvatar(e, iUserID)
{
	var $e = $(e);
	$e.after("<span>"+g_sHandling+"</span>");
	$e.hide();
	
	$.ajax({
		url : './?controller=admin_user&action=ajax_init_avatar&user_id='+iUserID,
		dataType : 'json',
		success : function(json)
		{
			$e.next().remove();
			if(json.error=="0")
			{
				window.location.reload();
			}
			else
			{
				$e.show();
				alert(json.message);
			}
		}
	});
	
}
