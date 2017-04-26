<?php


class template_step_init extends theme
{

	public function middle()
	{
		$create_tables = $this->get('create_tables');
	
		$error_num = $this->get("error_num");
		$error_msg = $this->get("error_msg");
		
		if ($error_num) {
		?>
		<div class="error">
			初始化数据库错误(错误编号 <?php echo $error_num; ?>: <?php echo $error_msg; ?>)，请重新配置.
		</div>
		<?php
		}
		?>
		
<script type="text/javascript" language="javascript"> gotoStep(2);</script>

<br />
将在数据库中创建以下表: <br />
<div>
	<ul>
	<?php
	foreach ($create_tables as $key=>$val) {
		echo '<li>'.$key.'</li>';
	}
	?>
	</ul>
</div>

<form method="post">
	<div class="init-form">
		<div><label>管理员账号:</label><input type="text" name="admin_name" size="50" value="admin" /></div>
		<div><label>管理员密码:</label><input type="text" name="admin_pass" size="50" value="admin" /></div>
		<div><label>管理员E-mail:</label><input type="text" name="admin_mail" size="50" /></div>
		<div><label>&nbsp;</label><input type="button" class="button" value="上一步" onclick="javascript:window.location.href='index.php?controller=install&task=step_setting'" />&nbsp;<input type="submit" class="button" value="下一步" /></div>
	</div>
	<input type="hidden" name="action" value="1" />
</form>
		<?php
		
	}	

}
?>