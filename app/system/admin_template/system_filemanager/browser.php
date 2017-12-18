<?php
use system\be;
?>

<!--{head}-->
<link type="text/css" rel="stylesheet" href="template/system_filemanager/css/browser.css">
<script type="text/javascript" language="javascript" src="template/system_filemanager/js/browser.js"></script>
<!--{/head}-->

<!--{body}-->
<?php
$path = $this->get('path');
$view = $this->get('view');
$sort = $this->get('sort');

$filter_image = $this->get('filter_image');
$filter_name = ($filter_image == 1?'图片':'文件');

$src_id = $this->get('src_id');

$files = $this->get('files');

$config_system = be::get_config('system.system');
$config_watermark = be::get_config('watermark');

?>
<form action="./?controller=system_filemanager&task=browser" method="post" id="form-system_filemanager">
    <input type="hidden" id="system_filemanager_path" name="path" value="<?php echo $path; ?>" />
    <input type="hidden" id="system_filemanager_view" name="view" value="<?php echo $view; ?>" />
    <input type="hidden" id="system_filemanager_sort" name="sort" value="<?php echo $sort; ?>" />
</form>


<form action="./?controller=system_filemanager&task=create_dir" method="post" class="form-horizontal">
    <div class="modal hide fade" id="modal-create_dir" data-backdrop="static">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h3>新建文件夹</h3>
        </div>
        <div class="modal-body">
            <div class="control-group">
                <label class="control-label">文件夹名: </label>
                <div class="controls"><input type="text" name="dir_name" value="<?php echo date('Y-m-d'); ?>" /></div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="submit" value="创建" class="btn btn-primary" />
            <input type="button" value="取消" class="btn" data-dismiss="modal" />
        </div>
    </div>
</form>

<form action="./?controller=system_filemanager&task=edit_dir_name" method="post" class="form-horizontal">
    <div class="modal hide fade" id="modal-edit_dir_name" data-backdrop="static">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h3>修改文件夹名称</h3>
        </div>
        <div class="modal-body">
            <div class="control-group">
                <label class="control-label">文件夹名: </label>
                <div class="controls"><input type="text" name="new_dir_name" id="edit_dir_name-new_dir_name" /></div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="submit" value="保存" class="btn btn-primary" />
            <input type="button" value="取消" class="btn" data-dismiss="modal" />
        </div>
    </div>
    <input type="hidden" name="old_dir_name" id="edit_dir_name-old_dir_name" />
</form>

<form action="./?controller=system_filemanager&task=upload_file" method="post" class="form-horizontal" enctype="multipart/form-data">
    <div class="modal hide fade" id="modal-upload" data-backdrop="static">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h3>上传 <?php echo $filter_name; ?></h3>
        </div>
        <div class="modal-body">
            <div class="control-group">
                <label class="control-label">选择本地<?php echo $filter_name; ?>：</label>
                <div class="controls"><input type="file" name="file" /></div>
            </div>
            <div class="control-group">
                <div class="controls"><label class="checkbox"><input type="checkbox" name="watermark" value="1"<?php echo $config_watermark->watermark == '0'?'':' checked="checked"'; ?> />图片添加水印</label></div>
            </div>
            <div class="control-group">
                <div style="font-size:12px; color:#999; text-align:center;">
                    允许上传的<?php echo $filter_name; ?>类型：
                    <?php echo $filter_image == 1?implode('，',$config_system->allow_upload_image_types):implode('，',$config_system->allow_upload_file_types); ?>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="submit" value="上传" class="btn btn-primary" />
            <input type="button" value="取消" class="btn" data-dismiss="modal" />
        </div>
    </div>
</form>


<form action="./?controller=system_filemanager&task=edit_file_name" method="post" class="form-horizontal">
    <div class="modal hide fade" id="modal-edit_file_name" data-backdrop="static">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h3>修改 <?php echo $filter_name; ?> 名称</h3>
        </div>
        <div class="modal-body">
            <div class="control-group">
                <label class="control-label"><?php echo $filter_name; ?> 名称：</label>
                <div class="controls"><input type="text" name="new_file_name" id="edit_file_name-new_file_name" /></div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="submit" value="保存" class="btn btn-primary" />
            <input type="button" value="取消" class="btn" data-dismiss="modal" />
        </div>
    </div>
    <input type="hidden" name="old_file_name" id="edit_file_name-old_file_name" />
</form>



<div class="navbar navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container-fluid">
            <div class="nav-collapse collapse">
                <div class="row-fluid">
                    <div class="span6">
                        <button class="btn" title="新建文件夹" onclick="javascript:$('#modal-create_dir').modal();"><i class="icon-folder-close"></i> 新建文件夹</button>
                        <button class="btn" title="上传 <?php echo $filter_name; ?>" onclick="javascript:$('#modal-upload').modal();">
                            <i class="icon-file"></i> 上传 <?php echo $filter_name; ?>
                        </button>
                    </div>
                    <div class="span6 text-right">
                        <button class="btn <?php if ($view == 'thumbnail') echo " btn-inverse"; ?>" onclick="javascript:setView('thumbnail');" title="缩略图">
                            <i class="icon-th <?php if ($view == 'thumbnail') echo "icon-white"; ?>"></i>
                        </button>
                        <button class="btn <?php if ($view == 'list') echo " btn-inverse"; ?>" onclick="javascript:setView('list');" title="详细">
                            <i class="icon-align-justify <?php if ($view == 'list') echo "icon-white"; ?>"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<ul class="breadcrumb">
    <li><a href="javascript:;" onclick="javascript:setPath('/');"><i class="icon-home"></i></a></li>
    <li><span class="divider">/</span></li>
    <?php
    if ($path!='') {
        $paths = explode('/', $path);
        if (count($paths)) {
            $tmp_path = '';
            foreach ($paths as $p) {
                if ($p == '') continue;

                $tmp_path .= '/'.$p;
                ?>
                <li><a href="javascript:;" onclick="javascript:setPath('<?php echo $tmp_path; ?>');"><?php echo $p; ?></a></li><li><span class="divider">/</span></li>
                <?php
            }
        }
    }
    ?>
</ul>

<?php
$config_system = be::get_config('system.system');

// 缩图图方式显示
if ($view == 'thumbnail') {
?>
<div class="view-thumbnail">
<div class="files">
<ul>
<?php
foreach ($files as $file) {
    if ($file['type'] == 'dir') {
        ?>
        <li>
            <div class="file">
                <a href="javascript:;" onclick="javascript:setPath('<?php echo $path.'/'.$file['name']; ?>');">
                    <div class="file-icon">
                        <img src="template/system_filemanager/images/types/folder.jpg" />
                    </div>
                    <div class="file-name"><?php echo $file['name']; ?></div>
                </a>
            </div>
        </li>
        <?php
    }
}

foreach ($files as $file) {
    if ($file['type']!='dir') {
        ?>
        <li>
            <div class="file">
                <a href="javascript:;" onclick="javascript:<?php echo $filter_image == 1?'selectImage':'selectFile'; ?>('<?php echo $file['name']; ?>', '<?php echo URL_ROOT.'/'.DATA.$path.'/'.$file['name']; ?>', '<?php echo $src_id ?>');">
                    <?php
                    if (in_array($file['type'], $config_system->allow_upload_image_types)) {
                        ?>
                        <div class="file-icon">
                            <img src="<?php echo URL_ROOT.'/'.DATA.$path.'/'.$file['name']; ?>" />
                        </div>
                        <?php
                    }
                    elseif (in_array($file['type'], $config_system->allow_upload_file_types)) {
                        ?>
                        <div class="file-icon">
                            <img src="template/system_filemanager/images/types/<?php echo $file['type']; ?>.jpg" />
                        </div>
                        <?php
                    } else {
                        ?>
                        <div class="file-icon">
                            <img src="template/system_filemanager/images/types/unknown.jpg" />
                        </div>
                        <?php
                    }
                    ?>
                    <div class="file-name"><?php echo $file['name']; ?></div>
                </a>
            </div>
        </li>
        <?php
    }

}
?>
</ul>
</div>
</div>
<?php
}
elseif ($view == 'list') // 详细列表方式
{
?>
<div class="view-list">
<table class="table table-hover">
<thead>
    <tr>
        <th style="width:47px;"></th>
        <th>文件名</th>
        <th style="text-align:center;width:80px;">大小</th>
        <th style="text-align:center;width:120px;">最后更改时间</th>
        <th style="text-align:right; width:60px;">操作</th>
    </tr>
</thead>

<tbody>
<?php
foreach ($files as $file) {
    if ($file['type'] == 'dir') {
        ?>
        <tr class="warning">
            <td>
                <div class="file-icon">
                <a href="javascript:;" onclick="javascript:setPath('<?php echo $path.'/'.$file['name']; ?>');">
                    <img src="template/system_filemanager/images/types/folder.png" />
                </a>
                </div>
            </td>
            <td>
                <a href="javascript:;" onclick="javascript:setPath('<?php echo $path.'/'.$file['name']; ?>');">
                    <?php echo $file['name']; ?>
                </a>
            </td>
            <td></td>
            <td style="text-align:center;"><span class="time"><?php echo date('Y-m-d H:i:s', $file['date']); ?></span></td>
            <td style="text-align:right;">
                <a href="javascript:;" onclick="javascript:editDirName('<?php echo $file['name']; ?>');"><i class="icon-pencil"></i></a>
                <a href="javascript:;" onclick="javascript:deleteDir('<?php echo $file['name']; ?>');"><i class="icon-trash"></i></a>
            </td>
        </tr>
        <?php
    }
}

foreach ($files as $file) {
    if ($file['type']!='dir') {
        ?>
        <tr>
            <td>
                <div class="file-icon">
                <a href="javascript:;" onclick="javascript:<?php echo $filter_image == 1?'selectImage':'selectFile'; ?>('<?php echo $file['name']; ?>', '<?php echo URL_ROOT.'/'.DATA.$path.'/'.$file['name']; ?>', '<?php echo $src_id ?>');">
                    <?php
                    if (in_array($file['type'], $config_system->allow_upload_image_types)) {
                        ?>
                        <img src="<?php echo URL_ROOT.'/'.DATA.$path.'/'.$file['name']; ?>" />
                        <?php
                    }
                    elseif (in_array($file['type'], $config_system->allow_upload_file_types)) {
                        ?>
                        <img src="template/system_filemanager/images/types/<?php echo $file['type']; ?>.jpg" />
                        <?php
                    } else {
                        ?>
                        <img src="template/system_filemanager/images/types/unknown.jpg" />
                        <?php
                    }
                    ?>
                </a>
                </div>
            </td>
            <td>
                <a href="javascript:;" onclick="javascript:<?php echo $filter_image == 1?'selectImage':'selectFile'; ?>('<?php echo $file['name']; ?>', '<?php echo URL_ROOT.'/'.DATA.$path.'/'.$file['name']; ?>', '<?php echo $src_id ?>');">
                    <?php echo $file['name']; ?>
                </a>
            </td>
            <td style="text-align:center;"><span class="size"><?php echo $file['size']; ?></span></td>
            <td style="text-align:center;"><span class="time"><?php echo date('Y-m-d H:i:s', $file['date']); ?></span></td>
            <td style="text-align:right;">
                <a href="./?controller=system_filemanager&task=download_file&file_name=<?php echo $file['name']; ?>" target="_blank"><i class="icon-download-alt"></i></a>
                <a href="javascript:;" onclick="javascript:editFileName('<?php echo $file['name']; ?>');"><i class="icon-pencil"></i></a>
                <a href="javascript:;" onclick="javascript:deleteFile('<?php echo $file['name']; ?>');"><i class="icon-trash"></i></a>
            </td>
        </tr>
        <?php
    }

}
?>
</tbody>
</table>
</div>
<?php
}
?>
<!--{/body}-->