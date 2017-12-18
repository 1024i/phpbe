
$(function(){
	$("#permission-1, #permission-0").change(function(){
		updatePermissions();
	});
	
	$("#permission--1").change(function(){
		updatePermissions();
	});
	
	$(".select-app-permissions").change(function(){
		$(":checkbox", $(this).closest(".control-group")).prop("checked", $(this).prop("checked"));
	});
	
	$(".select-app-permissions").closest(".control-group").addClass("info");

	updatePermissions();
});	


function updatePermissions()
{
	if($("#permission--1").prop("checked"))
	{
		$(".select-app-permissions").closest(".control-group").slideDown();
	}
	else
	{
		$(".select-app-permissions").closest(".control-group").slideUp();
	}
}