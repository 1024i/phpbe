<?php
class template_user_profile_edit extends template_user_dashboard
{
	
	protected function head()
	{
	parent::head();
	?>
<script type="text/javascript" language="javascript" src="<?php echo URL_ROOT; ?>/template/user_profile/js/edit.js"></script>
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


			<form id="form-user_profile_edit">
			<div class="row">
				<div class="col-5">
					<div class="key">用户名: </div>
				</div>
				<div class="col-15">
					<div class="val"><?php echo $my->username; ?></div>
				</div>
				<div class="clear-left"></div>
			</div>
			<div class="row">
				<div class="col-5">
					<div class="key">邮箱: </div>
				</div>
				<div class="col-15">
					<div class="val"><?php echo $my->email; ?></div>
				</div>
				<div class="clear-left"></div>
			</div>
			<div class="row">
				<div class="col-5">
					<div class="key">名称: </div>
				</div>
				<div class="col-15">
					<div class="val"><input type="text" class="input" name="name" value="<?php echo $my->name; ?>" style="width:120px;" /></div>
				</div>
				<div class="clear-left"></div>
			</div>
			<div class="row">
				<div class="col-5">
					<div class="key">性别: </div>
				</div>
				<div class="col-15">
					<div class="val">
						<label><input type="radio" name="gender" value="-1"<?php echo $my->gender == -1?' checked="checked"':''; ?> />保密</label>
						<label><input type="radio" name="gender" value="1"<?php echo $my->gender == 1?' checked="checked"':''; ?> />男</label>
						<label><input type="radio" name="gender" value="0"<?php echo $my->gender == 0?' checked="checked"':''; ?> />女</label>
					</div>
				</div>
				<div class="clear-left"></div>
			</div>
			<div class="row">
				<div class="col-5">
					<div class="key">电话: </div>
				</div>
				<div class="col-15">
					<div class="val"><input type="text" class="input" name="phone" value="<?php echo $my->phone; ?>" style="width:200px;" /></div>
				</div>
				<div class="clear-left"></div>
			</div>
			<div class="row">
				<div class="col-5">
					<div class="key">手机: </div>
				</div>
				<div class="col-15">
					<div class="val"><input type="text" class="input" name="mobile" value="<?php echo $my->mobile; ?>" style="width:200px;" /></div>
				</div>
				<div class="clear-left"></div>
			</div>
			<div class="row">
				<div class="col-5">
					<div class="key">QQ: </div>
				</div>
				<div class="col-15">
					<div class="val"><input type="text" class="input" name="qq" value="<?php echo $my->qq; ?>" style="width:200px;" /></div>
				</div>
				<div class="clear-left"></div>
			</div>
			<div class="row" style="margin-top:20px;">
				<div class="col-5"></div>
				<div class="col-15">
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