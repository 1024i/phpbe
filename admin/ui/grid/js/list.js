
function admin_ui_list()
{
	this.m_index = 0;
	this.m_aActions = {
        "create": false,
        "edit": false,
        "block": false,
        "unblock": false,
        "delete": false
    };
	
	this.setAction = function(sType, sAction){
        this.m_aActions[sType] = sAction;
    }
	
	this.init = function( index )
	{
    	this.m_index = index;
		
		var _self = this;
		

        $("#admin_ui_list_"+this.m_index+"_action").val(this.m_sAction);
        
        $("#admin_ui_list_"+this.m_index+" .ui-row").hover(function(){
            $(this).addClass("hover");
        }, function(){
            $(this).removeClass("hover");
        });

        $("#admin_ui_list_"+this.m_index+"_check_all").click(function(){
            if ($(this).prop("checked")) {
                $("#admin_ui_list_"+_self.m_index+" .id").prop("checked", true);
                $("#admin_ui_list_"+_self.m_index+" .ui-row").addClass("checked");
            }
            else {
                $("#admin_ui_list_"+_self.m_index+" .id").prop("checked", false);
                $("#admin_ui_list_"+_self.m_index+" .ui-row").removeClass("checked");
            }
            
            _self.update();
        });
        
        $("#admin_ui_list_"+this.m_index+" .id").change(function(){
			
            $("#admin_ui_list_"+_self.m_index+"_check_all").prop("checked", $("#admin_ui_list_"+_self.m_index+" .id").length == $("#admin_ui_list_"+_self.m_index+" .id:checked").length);
            
            if ($(this).prop("checked")) 
                $("#admin_ui_list_"+_self.m_index+"_row_" + $(this).val()).addClass("checked");
            else 
                $("#admin_ui_list_"+_self.m_index+"_row_" + $(this).val()).removeClass("checked");
            
            _self.update();
        });
        
        
        $("#admin_ui_list_"+this.m_index+" .id:checked").each(function(){
            $("#admin_ui_list_"+_self.m_index+"_row_" + $(this).val()).addClass("checked");
        });
        
        _self.update();
    }
	
	this.update = function(){
    
        $checkedID = $("#admin_ui_list_"+this.m_index+" .id:checked");
        
        var aID = new Array();
        $checkedID.each(function(){
            aID.push($(this).val());
        });
        $("#admin_ui_list_"+this.m_index+"_id").val(aID.join(","));
        
        var $edit = $("#admin_ui_list_"+this.m_index+"_toolbar_edit"), $unblock = $("#admin_ui_list_"+this.m_index+"_toolbar_unblock"), $block = $("#admin_ui_list_"+this.m_index+"_toolbar_block"), $delete = $("#admin_ui_list_"+this.m_index+"_toolbar_delete");
        
        var iLen = $checkedID.length;
        if (iLen == 0) {
            $edit.removeClass("able").addClass("disable");
            $unblock.removeClass("able").addClass("disable");
            $block.removeClass("able").addClass("disable");
            $delete.removeClass("able").addClass("disable");
        }
        else {
            $delete.removeClass("disable").addClass("able");
            if (iLen == 1) 
                $edit.removeClass("disable").addClass("able");
            else 
                $edit.removeClass("able").addClass("disable");
            
            if ($("#admin_ui_list_"+this.m_index+" .checked .block").length > 0) 
                $unblock.removeClass("disable").addClass("able");
            else 
                $unblock.removeClass("able").addClass("disable");
            
            if ($("#admin_ui_list_"+this.m_index+" .checked .unblock").length > 0) 
                $block.removeClass("disable").addClass("able");
            else 
                $block.removeClass("able").addClass("disable");
        }
    }
    
    this.create = function(){
        $("#admin_ui_list_"+this.m_index+"_id").val("0");
        $("#admin_ui_list_"+this.m_index+"_form").attr("action", this.m_aActions['create']).submit();
    }
    
    this.block = function(id){
		if (id != 0) $("#admin_ui_list_"+this.m_index+"_id").val(id);
		$("#admin_ui_list_"+this.m_index+"_form").attr("action", this.m_aActions['block']).submit();
    }
    
    this.unblock = function(id){
        if (id != 0) $("#admin_ui_list_"+this.m_index+"_id").val(id);
		$("#admin_ui_list_"+this.m_index+"_form").attr("action", this.m_aActions['unblock']).submit();
    }
    
    this.edit = function(id){
        if (id != 0) $("#admin_ui_list_"+this.m_index+"_id").val(id);
		$("#admin_ui_list_"+this.m_index+"_form").attr("action", this.m_aActions['edit']).submit();
    }
    
    this.remove = function(id){
        if (!confirm(LANG_UI_LIST_DELETE_CONFIRM)) 
            return false;
		if (id != 0) $("#admin_ui_list_"+this.m_index+"_id").val(id);
		$("#admin_ui_list_"+this.m_index+"_form").attr("action", this.m_aActions['delete']).submit();
    }
    
	this.filter = function(){
        $("#admin_ui_list_"+this.m_index+"_form").attr("action", this.m_aActions['list']).submit();
    }
	
    this.orderBy = function(sField, sDir){
        $("#admin_ui_list_"+this.m_index+"_id").remove();
        $("#admin_ui_list_"+this.m_index+"_order_by").val(sField);
        $("#admin_ui_list_"+this.m_index+"_order_by_dir").val(sDir);
        $("#admin_ui_list_"+this.m_index+"_form").attr("action", this.m_aActions['list']).submit();
    }
    
    this.gotoPage = function(n){
        $("#admin_ui_list_"+this.m_index+"_id").remove();
        $("#admin_ui_list_"+this.m_index+"_page").val(n);
        $("#admin_ui_list_"+this.m_index+"_action").val(this.m_sAction);
        $("#admin_ui_list_"+this.m_index+"_form").attr("action", this.m_aActions['list']).submit();
    }	
	
}