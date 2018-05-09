<?php
namespace admin\ui\tinymce;

class tinymce extends \ui\tinymce\tinymce
{

	public function head()
	{
		if (!$this->head) {
			$this->head = true;
?>
<script type="text/javascript" src="<?php echo Be::getRuntime()->getUrlRoot(); ?>/system/ui/tinymce/4.1.5/jquery.tinymce.min.js"></script>

<script type="text/javascript">
	$().ready(function() {
		$('textarea.tinymce').tinymce({
			scriptUrl : '<?php echo Be::getRuntime()->getUrlRoot(); ?>/ui/tinymce/4.1.5/tinymce.min.js',
            language : "zh_CN",
            forcedRootBlock : false,
            forcePNewlines : false,
			plugins : 'advlist link filemanager charmap preview code adminimage table textcolor colorpicker textpattern',

			toolbar1: "styleselect bold italic underline forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link filemanager adminimage | preview code",
			
			// 插入内容时跟据当前路径转换链接的路径。
			convertUrls: false
		});

	});
</script>
<?php
		}
	}
	

}
?>