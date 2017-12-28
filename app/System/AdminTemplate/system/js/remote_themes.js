
function install(e, iThemeID)
{
	var $e = $(e);
	var sHtml = $e.html();
	$e.html( g_sHandling );
	$e.addClass('disabled');
	
	$.ajax({
		url: './?controller=system&task=ajax_install_theme&theme_id='+iThemeID,
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
