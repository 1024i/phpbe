<?php


class template_setup_setting extends template
{


	public function display()
	{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>骨头 <?php echo bone::get_version(); ?> 安装程序 - <?php echo $this->get_title(); ?></title>

<script type="text/javascript" language="javascript" src="../js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" language="javascript">$.ajaxSetup({cache: false});</script>

<link type="text/css" rel="stylesheet" href="../css/global.css">
<script type="text/javascript" language="javascript" src="../js/global.js"></script>

<script type="text/javascript" language="javascript" src="template/setup/js/theme.js"></script>
<link type="text/css" rel="stylesheet" href="template/setup/css/theme.css">
<?php $this->head();?>

</head>

<body>

<div class="theme-body">
<?php $this->body();?>
</div>

</body>
</html>
<?php
	}
	
	protected function head()
	{
	 
	}

	protected function body()
	{
	?>
<div class="theme-north">
	<?php $this->north(); ?>
</div>

<div class="theme-middle">
	<?php $this->middle(); ?>
</div>
	<?php
	}
	
	protected function north()
	{
	?>
<div class="head">
	<div class="logo">
		<div class="logo-text">骨头 <?php echo bone::get_version(); ?> 安装程序</div>
	</div>	
</div>

<ul class="steps">
	<li class="step-off"><div class="icon" id="icon-1"></div><div class="title">配置数据库</div></li>
	<li class="step-off"><div class="icon" id="icon-2"></div><div class="title">初始化数据库</div></li>
	<li class="step-off"><div class="icon" id="icon-3"></div><div class="title">完成安装</div></li>
</ul>
<div class="clrl"></div>
	<?php
	}

	
	protected function message()
	{
		$message = $this->get('controller.message');
		if ($message) {
			echo '<div class="message-'.$message->type.'">'.$message->body.'</div>';
		}
	}




	public function middle()
	{
		$error_num = $this->get("error_num");
		$error_msg = $this->get("error_msg");

		$config = bone::get_config('system_db');
		
		if ($error_num) {
		?>
		<div class="error">
			无法连接到数据库(错误编号 <?php echo $error_num; ?>: <?php echo $error_msg; ?>)，请重新配置.
		</div>
		<?php
		}
		?>
<script type="text/javascript" language="javascript"> gotoStep(1);</script>
		
		
<form method="post">
<div class="init-form">
	<div><label>数据库IP:</label><input type="text" name="db_host" size="50" value="<?php echo $config->host; ?>" /></div>
	<div><label>数据库用户名:</label><input type="text" name="db_user" size="50" value="<?php echo $config->user; ?>" /></div>
	<div><label>数据库密码:</label><input type="text" name="db_pass" size="50" value="<?php echo $config->pass; ?>" /></div>
	<div><label>数据库名:</label><input type="text" name="db_name" size="50" value="<?php echo $config->name; ?>" /></div>
	<div><label>&nbsp;</label><input type="submit" class="button" value="下一步" /> </div>
</div>
<input type="hidden" name="action" value="1" />
</form>
		<?php
		
	}	

}
?>