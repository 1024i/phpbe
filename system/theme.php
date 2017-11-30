<?php
namespace system;

/**
 * 
 * 主题基类
 * 主题和模板里只放控制界面的代码，如数据格式，页面布局，不要有业务代码， 更不要操作数据库 
 *
 */
class theme
{
	/*
	网站主色调
	数组 10 个元素
	下标（index）：0, 1, 2, 3, 4, 5, 6, 7, 8, 9，
	主颜色: $this->colors[0], 模板主要颜色，
	其它颜色 依次减淡 10%, 即 ([index]*10)%
	
	可以仅有一个元素 即 $this->colors[0], 指定下标不存在时自动跟据主颜色按百分比换算。
	*/
	protected $colors = array('#333333');

	protected $template = null;
	protected $template_methods = null;

	/*
	 * @param object $template 模板
	 */
	public function set_template($template)
	{
		$this->template = $template;
		$this->template_methods = get_class_methods($template);
	}

	/**
     * 
     * 输出函数
	 *
     */
    public function display()
    {
		if ($this->template === null) return;
		$template = $this->template;
		$template_methods = $this->template_methods;

		$config = be::get_config('system');
		?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
			<meta name="description" content="<?php echo response::get_meta_description();?>" />
			<meta name="keywords" content="<?php echo response::get_meta_keywords();?>" />
			<title><?php echo response::get_title().' - '.$config->site_name; ?></title>

			<script type="text/javascript" language="javascript" src="<?php echo URL_ROOT; ?>/theme/default/js/jquery-1.11.0.min.js"></script>
			<script type="text/javascript" language="javascript" src="<?php echo URL_ROOT; ?>/theme/default/js/jquery.validate.min.js"></script>

			<script type="text/javascript" language="javascript">
				var URL_ROOT = '<?php echo URL_ROOT; ?>';
			</script>

			<?php $this->head(); ?>
			<?php if (in_array('head', $template_methods)) response::head(); ?>

		</head>
		<body>
			<div class="theme-body-container">
				<div class="theme-body">
				<?php
				if (in_array('body', $template_methods)) {
					response::body();
				} else {
					$this->body();
				}
				?>
				</div>
			</div>
		</body>
		</html>
		<?php
    }



    /**
     * 
     * <head></head>头可加 js/css
     */
    protected function head()
    {
?>
<link type="text/css" rel="stylesheet" href="<?php echo URL_ROOT; ?>/css/be.less/be.css" />
<script type="text/javascript" language="javascript" src="<?php echo URL_ROOT; ?>/js/be.js"></script>
<?php
    }


	/**
	 *
	 * 项部
	 */
	protected function body()
	{
		$template = $this->template;
		$template_methods = $this->template_methods;
		?>
		<div class="theme-north-container">
			<div class="theme-north">
				<?php
				if (in_array('north', $template_methods)) {
					response::north();
				} else {
					$this->north();
				}
				?>
			</div>
		</div>

		<div class="theme-middle-container">
			<div class="theme-middle">
				<?php
				if (in_array('middle', $template_methods)) {
					response::middle();
				} else {
					$this->middle();
				}
				?>
			</div>
		</div>

		<div class="theme-south-container">
			<div class="theme-south">
				<?php
				if (in_array('south', $template_methods)) {
					response::south();
				} else {
					$this->south();
				}
				?>
			</div>
		</div>
		<?php
	}

    /**
     * 
     * 项部
     */
    protected function north()
    {
    }


	protected function middle()
	{
		$template = $this->template;
		$template_methods = $this->template_methods;

		$west = in_array('west', $template_methods);
		$east = in_array('east', $template_methods);

		$west_width = 25;
		$center_width = 50;
		$east_width = 25;

		if (!$west || !$east) {
			if (!$west && !$east) {
				$center_width = 100;
			} else {
				$center_width = 70;
				$west_width = $east_width = 30;
			}
		}
	?>
<div class="row">
	<?php
	if ($west) {
	?>
	<div class="col" style="width:<?php echo $west_width; ?>%;">
		<div class="theme-west-container">
			<div class="theme-west">
				<?php response::west(); ?>
			</div>
		</div>
	</div>
	<?php
	}
	?>
	
	<div class="col" style="width:<?php echo $center_width; ?>%;">
	
		<div class="theme-center-container">
			<div class="theme-center">
				<?php
				$this->message();

				response::center();
				?>
			</div>
		</div>
		
	</div>
	
	<?php
	if ($east) {
	?>
	<div class="col" style="width:<?php echo $east_width; ?>%;">
		<div class="theme-east-container">
			<div class="theme-east">
				<?php
				response::east();
				?>
			</div>
		</div>
	</div>
	<?php
	}
	?>
	<div class="clear-left"></div>
</div>
	<?php
	}

    /**
     * 
     * 底部
     */
    protected function south()
    {
    }


    public function get_color($index = 0)
    {
		if ($index == 0) return $this->colors[0];
		if (array_key_exists($index, $this->colors)) return $this->colors[$index];
		
		$lib_css = be::get_lib('css');
		return $lib_css->lighter($this->colors[0], $index*10);
    }


    /**
     * 
     * 显示跳转过程中传递的信息
     */
    protected function message()
    {
        if (session::has('_message')) {
			$message = session::delete('_message');

			//$message->type: success:成功 / error:错误 / warning:警告 / info:普通信息 

			echo '<div class="theme-message theme-message-' . $message->type . ' alert alert-' . $message->type . '">';
			echo '<a href="javascript:;" class="close">&times;</a>';
			echo $message->body;
			echo '</div>';
		}
    }

}
?>