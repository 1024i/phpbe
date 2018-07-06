
$(function(){

	$.ajaxSetup({cache: false});
	var $ajaxLoader = $('<div id="ajax-loader">'+g_sHandling+'</div>').appendTo("body").hide();
	$(document).ajaxStart(function(){
		$ajaxLoader.fadeIn();
	}).ajaxStop(function(){
		$ajaxLoader.fadeOut();
	}).ajaxError(function(a, b, e){throw e;});

	// 左栏树
	$("#west-tree .node:first").addClass("first");
	$("#west-tree .node:last").addClass("last");
	$("li:last", $("#west-tree .node")).addClass("last");

	$("#west-tree .node div").click(function(){
		$(this).parent().toggleClass("open");
		
		// 将打开的菜单ID存入 cookie
		var aApp = new Array();
		$("#west-tree .open").each(function(){ aApp.push($(this).attr("id")); })
		$.cookie("west-tree", aApp.join(","), {"expires":365});
	});
	
	// 自动打开菜单
	var sWestTree = $.cookie("west-tree");
	if(sWestTree) $( "#"+sWestTree.replace(/,/g, ", #") ).addClass("open");

	// 提示信息
	setTimeout(function(){$(".theme-message").fadeOut();}, 3000);
	
	$('.tooltip, a[data-toggle=tooltip]').tooltip();
	$('a[data-toggle=popover]').popover();
	
});




function changeLanguage(sLanguage)
{
	$.ajax({
		url: './?controller=system&action=ajax_change_language&language='+sLanguage,
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