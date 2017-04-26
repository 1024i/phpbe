<?php
class template_system_terms_and_conditions extends theme
{

	protected function middle($option=array())
	{
		parent::middle(array('west'=>0, 'east'=>0));  // 不需要左右边栏
	}
	
	protected function center()
	{
		$terms_and_conditions = $this->get('terms_and_conditions');
		?>
<?php $this->center_box_head(); ?>
<div class="theme-box-container">
	<div class="theme-box">
		<div class="theme-box-title"><?php echo $this->get_title(); ?></div>
		<div class="theme-box-body">
		
			<?php echo $terms_and_conditions->body; ?>

		</div>
	</div>
</div>
<?php $this->center_box_foot(); ?>
		<?php
	}		
		

}
?>