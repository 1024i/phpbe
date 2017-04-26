$(function(){
	$e = $("#smtp_host, #smtp_port, #smtp_user, #smtp_pass");
	$e.attr("disabled", $("#smtp-0").attr('checked'));
	$("#smtp-0").click(function(){$e.attr("disabled", true);});
	$("#smtp-1").click(function(){$e.attr("disabled", false);});
});

