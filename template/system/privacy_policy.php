<?php
class template_system_privacy_policy extends theme
{

	protected function middle($option=array())
	{
		parent::middle(array('west'=>0, 'east'=>0));  // 不需要左右边栏
	}
	
	protected function center()
	{
		$privacy_policy = $this->get('privacy_policy');
		?>
<div class="theme-box-container">
	<div class="theme-box">
		<div class="theme-box-title"><?php echo $this->get_title(); ?></div>
		<div class="theme-box-body">
		
			<?php echo $privacy_policy->body; ?>

		</div>
	</div>
</div>
		<?php
	}		
		

}
?>