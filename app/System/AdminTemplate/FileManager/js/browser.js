

function setPath( sPath )
{
	$("#system_filemanager_path").val(sPath);
	$("#form-system_filemanager").submit();
}


function setView( sView )
{
	$("#system_filemanager_view").val(sView);
	$("#form-system_filemanager").submit();
}

function setSort( sSort )
{
	$("#system_filemanager_sort").val(sPath);
	$("#form-system_filemanager").submit();
}

function selectImage( sFileName, sPath, sSrcID )
{
	var $target;
	if(!sSrcID)
	{
		$target = $('.mce-input_img_src input', window.parent.document);
	}
	else
	{
		$target = $("#"+sSrcID, window.parent.document);
	}
	
	$target.val( sPath );
	$target.trigger( "change" );
	parent.tinymce.activeEditor.windowManager.close();
}

function selectFile( sFileName, sPath, sSrcID )
{
    parent.tinymce.activeEditor.insertContent('<a href="'+sPath+'" title="'+sFileName+'">'+sFileName+'</a>');
    parent.tinymce.activeEditor.windowManager.close();
}

function editDirName( sDirName )
{
	$("#edit_dir_name-old_dir_name,#edit_dir_name-new_dir_name").val(sDirName);
	$('#modal-edit_dir_name').modal();
}

function deleteDir( sDirName )
{
	if(!confirm("即将删除 "+sDirName+" 文件夹和文件夹下的所有文件，确认要删除吗？")) return false;
	if(!confirm("本操作不可恢复，请再次确认要删除吗？")) return false;
	
	window.location.href = './?controller=system_filemanager&action=delete_dir&dir_name='+sDirName;
}

function editFileName( sFileName )
{
	$("#edit_file_name-old_file_name,#edit_file_name-new_file_name").val( sFileName );
	$('#modal-edit_file_name').modal();
}

function deleteFile( sFileName )
{
	if(!confirm("即将删除文件 "+sFileName+" ，确认要删除吗？")) return false;
	if(!confirm("本操作不可恢复，请再次确认要删除吗？")) return false;
	
	window.location.href = './?controller=system_filemanager&action=delete_file&file_name='+sFileName;
}