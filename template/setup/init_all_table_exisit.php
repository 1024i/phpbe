<?php


class template_step_init_all_table_exisit extends theme
{

	public function middle()
	{
		$created_tables = $this->get('created_tables');

		?>
		<script type="text/javascript" language="javascript"> gotoStep(2);</script>
		
		<div class="error">
			检测到数据库中已包含全部系统表.
		</div>
		
		<div>
			<ul>
			<?php
			foreach ($created_tables as $row) {
				echo '<li>'.$row.'</li>';
			}
			?>
			</ul>
		</div>
		
		<br />
		你可以执行以下操作:<br />
		<ul>
		<li>退出安装程序</li>
		<li>选择一个新的数据库进行安装</li>
		<li>备份后清空数据库重新安装</li>
		</ul>
		
		<div class="init-form">
			<div><label>&nbsp;</label><input type="button" class="button" value="上一步" onclick="javascript:window.location.href='index.php?controller=install&task=step_setting'" /> </div>
		</div>		
		<?php
		
	}	

}
?>