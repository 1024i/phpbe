
// ��װ����ͼ����ת
function gotoStep(n)
{
	for(var i=1; i<=3; i++)
	{
		if(i<n)
			jQuery("#icon-"+i).parent().attr("class", "step-on");
		else if(i==n)
			jQuery("#icon-"+i).parent().attr("class", "step-over");
		else
			jQuery("#icon-"+i).parent().attr("class", "step-off");
	}
}