

function changeLanguage(sLanguage)
{
	$.ajax({
		url: Be::getRuntime()->getUrlRoot()+'/?controller=system&task=ajax_change_language&language='+sLanguage,
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
