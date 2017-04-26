<?php
namespace admin\template\system;

class remote_app extends \admin\theme
{

	protected function head()
	{
		parent::head();
?>
<link rel="stylesheet" href="bootstrap/2.3.2/css/bootstrap-lightbox.min.css" type="text/css" />
<script src="bootstrap/2.3.2/js/bootstrap-lightbox.min.js"></script>

<script type="text/javascript" language="javascript" src="template/system/js/remote_app.js"></script>
<link type="text/css" rel="stylesheet" href="template/system/css/remote_app.css" />
<?php
	}

	protected function center()
	{
		$model_system = be::get_admin_model('system');
        $installed_apps = $model_system->get_apps();
		
	    $remote_app = $this->get('remote_app');
	    if ($remote_app->status!='0') {
	        echo $remote_app->description;
	        return;
	    }

		$app = $remote_app->app;
		
		$installed = false;
		foreach ($installed_apps as $installed_app) {
			if ($installed_app->id == $app->id) {
				$installed = true;
				break;
			}
		}
		?>
		<div class="remote_app">
		
			<div class="name">（#<?php echo $app->id; ?>）：<?php echo $app->label; ?></div>
			
			<div class="row-fluid">
				<div class="span2">
					<div class="logo"><img src="<?php echo $app->logo; ?>" /></div>
				</div>
				<div class="span10">
					<div class="note">
						发布时间: <?php echo date('Y-m-d', $app->create_time); ?> &nbsp; 
						查看次数: <?php echo $app->hits; ?> &nbsp; 
						安装次数: <?php echo $app->download_times; ?>
					</div>
					<div class="summary"><?php echo $app->summary; ?></div>
					
					
					<?php
					if (count($app->version->screenshots)) {
						?>
						<div class="screenshots">
						<?php
						foreach ($app->version->screenshots as $screenshot) {
							?>
							<a href="javascript:" onclick="javascript:$('#screenshot_<?php echo $screenshot->id; ?>').lightbox();" data-title="" data-content="<div style='width:360px;height:360px;'><img src='<?php echo $screenshot->image_m; ?>' style='max-width:360px;' /></div>" data-toggle="popover" data-html="true" data-trigger="hover"><img src="<?php echo $screenshot->image_s; ?>" style="width:60px; height:60px;" class="img-rounded" /></a>
							
							<div id="screenshot_<?php echo $screenshot->id; ?>" class="lightbox fade hide">
								<div class='lightbox-dialog'>
									<div class='lightbox-content'>
										<img src="<?php echo $screenshot->image_l; ?>" />
										<div class="lightbox-caption"><p><?php echo $screenshot->name; ?></p></div>
									</div>
								</div>
							</div>
							<?php
						}
						?>
						</div>
						<?php
					}
					?>
					
					<div class="buttons">
						<a href="<?php echo $app->version->demo_url; ?>" class="btn btn-info" target="_blank"><i class="icon-white icon-search"></i> 查看演示</a>
						<?php
						if ($installed) {
						?>
						<a href="javascript:;" class="btn btn-success disabled" ><i class="icon-white icon-ok"></i> 已安装</a>
						<?php
						} else {
						?>
						<a href="javascript:;" class="btn btn-success" onclick="javascript:install(this, <?php echo $app->id; ?>);"><i class="icon-white icon-download-alt"></i> 安装</a>
						<?php
						}
						?>
					</div>
				</div>
			</div>

			<ul class="nav nav-tabs">
				<li class="active"><a href="#description" data-toggle="tab"><i class="icon-search"></i> 详细描述</a></li>
				<li><a href="#auther" data-toggle="tab"><i class="icon-user"></i> 作者</a></li>
				<li><a href="#other" data-toggle="tab"><i class="icon-question-sign"></i> 其它信息</a></li>
			</ul>
			
			<div class="tab-content" style="padding:0px 20px;">
				<div class="tab-pane active" id="description">
					<?php echo $app->version->description; ?>
				</div>
				<div class="tab-pane" id="auther">
				
					<i class="icon-user"></i> 作者: <?php echo $app->auther; ?><br />
					<i class="icon-envelope"></i> 作者邮箱: <?php
					if ($app->auther_email!='') {
						echo '<a href="mailto:'.$app->auther_email.'">'.$app->auther_email.'</a>';
					} else {
						echo '-';
					}
					?><br />
					<i class="icon-globe"></i> 作者网站: <?php
					if ($app->auther_website!='') {
						echo '<a href="'.$app->auther_website.'" target="_blank">'.$app->auther_website.'</a>';
					} else {
						echo '-';
					}
					?>
				</div>
				<div class="tab-pane" id="other">
					ID: <?php echo $app->id; ?><br />
					标识: <?php echo $app->name; ?><br />
					版本: <?php echo $app->version->name; ?>
				</div>
			</div>

		</div>
		<?php
		//print_r($app);
	}	

}
?>