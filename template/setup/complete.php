<?php


class template_step_complete extends theme
{

	public function middle()
	{
		?>

		
<script type="text/javascript" language="javascript"> gotoStep(3);</script>


<div>
	系统安装成功。
</div>

<ul>
	<li><a href="javascript:window.close();">关闭窗口</a></li>
	<li><a href="../">访问网站首页</a></li>
</ul>

		<?php
		
	}	

}
?>