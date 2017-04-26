<?php
namespace admin\template\dev;


class db_table_edit extends \admin\theme
{


	protected function center()
	{
		$db_table = $this->get('db_table');
		?>
		<table style="width: 98%;">
			<thead>
			<tr>
				<th style="width: 50%;">
					当前表
				</th>
				<th style="width: 50%;">
					更新后
				</th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td style="vertical-align: top;">
					<pre><?php echo htmlspecialchars($db_table->system_table_code); ?><?php echo htmlspecialchars($db_table->system_table_admin_code); ?></pre>
				</td>
				<td style="vertical-align: top;">
					<form action="./?controller=dev&task=db_table_edit_save" method="post">
						类型：
						<label><input class="data-toggle" name="type" type="radio" value="1" checked="checked" />前台 table</label>
						<label><input class="data-toggle" name="type" type="radio" value="2" />后台 table</label>
						<textarea id="data" name="data" style="width:100%; height:400px;"><?php echo $db_table->db_table_code; ?></textarea>
						<input type="hidden" name="file_name" value="<?php echo $db_table->system_table_name; ?>" />
						<input type="submit" value="保存" class="btn btn-primary" />
						<a href="./?controller=dev&task=db_tables" class="btn">返回</a>
					</form>
					<textarea id="template-data-1" style="display: none;"><?php echo $db_table->db_table_code; ?></textarea>
					<textarea id="template-data-2" style="display: none;"><?php echo $db_table->db_table_admin_code; ?></textarea>

				</td>
			</tr>
			</tbody>
		</table>

		<script type="text/javascript" language="javascript">
			$(function(){
				$(".data-toggle").click(function(){
					$("#data").val($("#template-data-"+$(this).val()).val());
				})
			})
		</script>
		<?php
	}	

}
?>