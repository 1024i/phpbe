<?php
use Phpbe\System\Be;
?>

<!--{head}-->
<link type="text/css" rel="stylesheet" href="template/systemFilemanager/css/browser.css">
<script type="text/javascript" language="javascript" src="template/systemFilemanager/js/browser.js"></script>
<!--{/head}-->

<!--{body}-->
<?php
$path = $this->get('path');
$view = $this->get('view');
$sort = $this->get('sort');

$filterImage = $this->get('filterImage');
$filterName = ($filterImage == 1?'图片':'文件');

$srcId = $this->get('srcId');

$files = $this->get('files');

$configSystem = Be::getConfig('System.System');
$configWatermark = Be::getConfig('System.Watermark');

?>
<form action="./?controller=systemFilemanager&task=browser" method="post" id="form-systemFilemanager">
    <input type="hidden" id="systemFilemanagerPath" name="path" value="<?php echo $path; ?>" />
    <input type="hidden" id="systemFilemanagerView" name="view" value="<?php echo $view; ?>" />
    <input type="hidden" id="systemFilemanagerSort" name="sort" value="<?php echo $sort; ?>" />
</form>


<form action="./?controller=systemFilemanager&task=createDir" method="post" class="form-horizontal">
    <div class="modal hide fade" id="modal-createDir" data-backdrop="static">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h3>新建文件夹</h3>
        </div>
        <div class="modal-body">
            <div class="control-group">
                <label class="control-label">文件夹名: </label>
                <div class="controls"><input type="text" name="dirName" value="<?php echo date('Y-m-d'); ?>" /></div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="submit" value="创建" class="btn btn-primary" />
            <input type="button" value="取消" class="btn" data-dismiss="modal" />
        </div>
    </div>
</form>

<form action="./?controller=systemFilemanager&task=editDirName" method="post" class="form-horizontal">
    <div class="modal hide fade" id="modal-editDirName" data-backdrop="static">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h3>修改文件夹名称</h3>
        </div>
        <div class="modal-body">
            <div class="control-group">
                <label class="control-label">文件夹名: </label>
                <div class="controls"><input type="text" name="newDirName" id="editDirName-newDirName" /></div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="submit" value="保存" class="btn btn-primary" />
            <input type="button" value="取消" class="btn" data-dismiss="modal" />
        </div>
    </div>
    <input type="hidden" name="oldDirName" id="editDirName-oldDirName" />
</form>

<form action="./?controller=systemFilemanager&task=uploadFile" method="post" class="form-horizontal" enctype="multipart/form-data">
    <div class="modal hide fade" id="modal-upload" data-backdrop="static">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h3>上传 <?php echo $filterName; ?></h3>
        </div>
        <div class="modal-body">
            <div class="control-group">
                <label class="control-label">选择本地<?php echo $filterName; ?>：</label>
                <div class="controls"><input type="file" name="file" /></div>
            </div>
            <div class="control-group">
                <div class="controls"><label class="checkbox"><input type="checkbox" name="watermark" value="1"<?php echo $configWatermark->watermark == '0'?'':' checked="checked"'; ?> />图片添加水印</label></div>
            </div>
            <div class="control-group">
                <div style="font-size:12px; color:#999; text-align:center;">
                    允许上传的<?php echo $filterName; ?>类型：
                    <?php echo $filterImage == 1?implode('，',$configSystem->allowUploadImageTypes):implode('，',$configSystem->allowUploadFileTypes); ?>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="submit" value="上传" class="btn btn-primary" />
            <input type="button" value="取消" class="btn" data-dismiss="modal" />
        </div>
    </div>
</form>


<form action="./?controller=systemFilemanager&task=editFileName" method="post" class="form-horizontal">
    <div class="modal hide fade" id="modal-editFileName" data-backdrop="static">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h3>修改 <?php echo $filterName; ?> 名称</h3>
        </div>
        <div class="modal-body">
            <div class="control-group">
                <label class="control-label"><?php echo $filterName; ?> 名称：</label>
                <div class="controls"><input type="text" name="newFileName" id="editFileName-newFileName" /></div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="submit" value="保存" class="btn btn-primary" />
            <input type="button" value="取消" class="btn" data-dismiss="modal" />
        </div>
    </div>
    <input type="hidden" name="oldFileName" id="editFileName-oldFileName" />
</form>



<div class="navbar navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container-fluid">
            <div class="nav-collapse collapse">
                <div class="row-fluid">
                    <div class="span6">
                        <button class="btn" title="新建文件夹" onclick="javascript:$('#modal-createDir').modal();"><i class="icon-folder-close"></i> 新建文件夹</button>
                        <button class="btn" title="上传 <?php echo $filterName; ?>" onclick="javascript:$('#modal-upload').modal();">
                            <i class="icon-file"></i> 上传 <?php echo $filterName; ?>
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
            $tmpPath = '';
            foreach ($paths as $p) {
                if ($p == '') continue;

                $tmpPath .= '/'.$p;
                ?>
                <li><a href="javascript:;" onclick="javascript:setPath('<?php echo $tmpPath; ?>');"><?php echo $p; ?></a></li><li><span class="divider">/</span></li>
                <?php
            }
        }
    }
    ?>
</ul>

<?php
$configSystem = Be::getConfig('System.System');

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
                        <img src="template/systemFilemanager/images/types/folder.jpg" />
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
                <a href="javascript:;" onclick="javascript:<?php echo $filterImage == 1?'selectImage':'selectFile'; ?>('<?php echo $file['name']; ?>', '<?php echo Be::getRuntime()->getUrlRoot().'/'.DATA.$path.'/'.$file['name']; ?>', '<?php echo $srcId ?>');">
                    <?php
                    if (in_array($file['type'], $configSystem->allowUploadImageTypes)) {
                        ?>
                        <div class="file-icon">
                            <img src="<?php echo Be::getRuntime()->getUrlRoot().'/'.DATA.$path.'/'.$file['name']; ?>" />
                        </div>
                        <?php
                    }
                    elseif (in_array($file['type'], $configSystem->allowUploadFileTypes)) {
                        ?>
                        <div class="file-icon">
                            <img src="template/systemFilemanager/images/types/<?php echo $file['type']; ?>.jpg" />
                        </div>
                        <?php
                    } else {
                        ?>
                        <div class="file-icon">
                            <img src="template/systemFilemanager/images/types/unknown.jpg" />
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
                    <img src="template/systemFilemanager/images/types/folder.png" />
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
                <a href="javascript:;" onclick="javascript:<?php echo $filterImage == 1?'selectImage':'selectFile'; ?>('<?php echo $file['name']; ?>', '<?php echo Be::getRuntime()->getUrlRoot().'/'.DATA.$path.'/'.$file['name']; ?>', '<?php echo $srcId ?>');">
                    <?php
                    if (in_array($file['type'], $configSystem->allowUploadImageTypes)) {
                        ?>
                        <img src="<?php echo Be::getRuntime()->getUrlRoot().'/'.DATA.$path.'/'.$file['name']; ?>" />
                        <?php
                    }
                    elseif (in_array($file['type'], $configSystem->allowUploadFileTypes)) {
                        ?>
                        <img src="template/systemFilemanager/images/types/<?php echo $file['type']; ?>.jpg" />
                        <?php
                    } else {
                        ?>
                        <img src="template/systemFilemanager/images/types/unknown.jpg" />
                        <?php
                    }
                    ?>
                </a>
                </div>
            </td>
            <td>
                <a href="javascript:;" onclick="javascript:<?php echo $filterImage == 1?'selectImage':'selectFile'; ?>('<?php echo $file['name']; ?>', '<?php echo Be::getRuntime()->getUrlRoot().'/'.DATA.$path.'/'.$file['name']; ?>', '<?php echo $srcId ?>');">
                    <?php echo $file['name']; ?>
                </a>
            </td>
            <td style="text-align:center;"><span class="size"><?php echo $file['size']; ?></span></td>
            <td style="text-align:center;"><span class="time"><?php echo date('Y-m-d H:i:s', $file['date']); ?></span></td>
            <td style="text-align:right;">
                <a href="./?controller=systemFilemanager&task=downloadFile&fileName=<?php echo $file['name']; ?>" target="Blank"><i class="icon-download-alt"></i></a>
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