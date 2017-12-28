function mouseover(e)
{
	if(!$(e).hasClass("on")) $(e).addClass("hover");
}

function mouseout(e)
{
	$(e).removeClass("hover");
}

function clickApp(e, sApp)
{
	var $e = $(e);
	
	if($e.hasClass("on")) return;
	$(".apps .on").removeClass("on");
	$(".menus .on").removeClass("on");
	$e.addClass("on");
	
	$(".menus .menu").hide();
	$("#menu_"+sApp).show();
	
	disableSaveMenu(true);
}

function clickMenu(e, sUrl, sParams)
{
	var $e = $(e);
	
	if($e.hasClass("on")) return;
	$(".menus .on").removeClass("on");
	$e.addClass("on");
	
	g_sUrl = sUrl;
	g_sParams = sParams;
	disableSaveMenu(false);
}


