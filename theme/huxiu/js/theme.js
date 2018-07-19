

function changeLanguage(sLanguage)
{
	$.ajax({
		url: '/?app=System&controller=System&action=ajax_change_language&language='+sLanguage,
		dataType: 'json',
		success: function(json)
		{
			if(json.error=="0")
			{
				window.location.href = window.location.href;
			}
			else
			{
				alert(json.message);
			}
		}
		
	});
	
}
