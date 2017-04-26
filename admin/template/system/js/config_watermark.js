$(function(){
	$(".position").click(function(){
		$(".position").removeClass("on");
		$(this).addClass("on");
		$("#selected-position").val($(this).attr("data-position"));
	});
});