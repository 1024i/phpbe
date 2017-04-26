<?php
class template_user_profile_edit_password extends template_user_dashboard
{
	
	protected function head()
	{
	parent::head();
	?>
<script type="text/javascript" language="javascript" src="<?php echo URL_ROOT; ?>/template/user_profile/js/edit_password.js"></script>
	<?php
	}

	protected function center()
	{
		$my = be::get_user();
		?>
<?php $this->center_box_head(); ?>
<div class="theme-box-container">
	<div class="theme-box">
		<div class="theme-box-title"><?php echo $this->get_title(); ?></div>
		<div class="theme-box-body">

			<form id="form-user_profile_edit_password">
			<div class="row">
				<div class="col-6">
					<div class="key">当前密码<span class="text-required">*</span></div>
				</div>
				<div class="col-14">
					<div class="val"><input type="password" class="input" name="password" value="" style="width:200px;" /></div>
				</div>
				<div class="clear-left"></div>
			</div>
			<div class="row">
				<div class="col-6">
					<div class="key">新密码: <span class="text-required">*</span></div>
				</div>
				<div class="col-14">
					<div class="val"><input type="password" class="input" name="password1" id="center-password1" value="" style="width:200px;" /></div>
				</div>
				<div class="clear-left"></div>
			</div>
			<div class="row">
				<div class="col-6">
					<div class="key">确认新密码: <span class="text-required">*</span></div>
				</div>
				<div class="col-14">
					<div class="val"><input type="password" class="input" name="password2" value="" style="width:200px;" /></div>
				</div>
				<div class="clear-left"></div>
			</div>
			<div class="row" style="margin-top:20px;">
				<div class="col-6"></div>
				<div class="col-14">
					<div class="val">
						<input type="submit" class="btn btn-primary btn-submit" value="提交" />
						<input type="reset" class="btn" value="重置" />
					</div>
				</div>
				<div class="clear-left"></div>
			</div>
			</form>
			
		</div>
	</div>
</div>
<?php $this->center_box_foot(); ?>
		<?php
	}		
		

}
?>