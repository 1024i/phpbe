
$(function(){
    admin_ui_category.init();
});



var admin_ui_category = {

    m_oChain: new Object(),
    m_iChainHead: 0,
    m_sSaveAction: '',
	m_sDeleteAction: '',
	m_sTemplate : "",
	
	m_iNewID : 0,
    
    addChain: function(id, sName, iPreID, iNextID){
        this.m_oChain[id] = {
            "id": id,
            "name": sName,
            "pre_id": iPreID,
            "next_id": iNextID
        };
    },
    
    setChainHead: function(n){
        this.m_iChainHead = n;
    },

    setSaveAction: function(str){
        this.m_sSaveAction = str;
    },
	
	setDeleteAction: function(str){
        this.m_sDeleteAction = str;
    },
	
    setTemplate: function(str){
        this.m_sTemplate = str;
    },
	
    init: function(){
        admin_ui_category.update();
    },
    
    update: function(){
		
        // 更新排序图标
        if (this.m_iChainHead == 0) 
            return;
        
        var oCategory = this.m_oChain[this.m_iChainHead];
        var oCurrentCategory = oCategory;
		
        while (oCurrentCategory.next_id != 0) {
        
            var $eOrderUp = $("#admin_ui_category_row_" + oCurrentCategory.id + " .order .up");
            var $eOrderDown = $("#admin_ui_category_row_" + oCurrentCategory.id + " .order .down");
            
			// 是否可上移
            if (oCurrentCategory.pre_id != 0)
                $eOrderUp.removeClass("disable");
            else 
                $eOrderUp.addClass("disable");
			
			// 可下移
            $eOrderDown.removeClass("disable");
            
            oCurrentCategory = this.m_oChain[oCurrentCategory.next_id];
        }

		// =================================处理最后一个结点，
		// 是否可上移
        if (oCurrentCategory.pre_id == 0) 
            $("#admin_ui_category_row_" + oCurrentCategory.id + " .order .up").addClass("disable");
        else
            $("#admin_ui_category_row_" + oCurrentCategory.id + " .order .up").removeClass("disable");

		// 不可下移
        $("#admin_ui_category_row_" + oCurrentCategory.id + " .order .down").addClass("disable");
    },
    
    
    
    
    // 上移
    orderUp: function(id)
	{
		if( $("#admin_ui_category_row_" + id + " .order .up").hasClass("disable") ) return;
		
		var oCategory = this.m_oChain[id];
		var oPreCategory = this.m_oChain[oCategory.pre_id];

		if(oPreCategory.pre_id==0)
			this.m_iChainHead = oCategory.id;
		else
			this.m_oChain[oPreCategory.pre_id].next_id = oCategory.id;
	
		oCategory.pre_id = oPreCategory.pre_id;
		oPreCategory.pre_id = oCategory.id;

		if(oCategory.next_id>0)  this.m_oChain[oCategory.next_id].pre_id = oPreCategory.id;
		
		oPreCategory.next_id = oCategory.next_id;
		oCategory.next_id = oPreCategory.id;

		
		$("#admin_ui_category_row_"+oPreCategory.id).before($("#admin_ui_category_row_"+oCategory.id));
		$("#admin_ui_category_row_"+oCategory.id).mouseout();
		
		this.update();
    },
    
    // 下移
    orderDown: function(id)
	{
		if( $("#admin_ui_category_row_" + id + " .order .down").hasClass("disable") ) return;

		var oCategory = this.m_oChain[id];
		var oNextCategory = this.m_oChain[oCategory.next_id];

		if(oCategory.pre_id==0)
			this.m_iChainHead = oNextCategory.id;
		else
			this.m_oChain[oCategory.pre_id].next_id = oNextCategory.id;
			
		oNextCategory.pre_id = oCategory.pre_id;
		oCategory.pre_id = oNextCategory.id;
		
		if(oNextCategory.next_id>0)  this.m_oChain[oNextCategory.next_id].pre_id = oCategory.id;
		
		oCategory.next_id = oNextCategory.next_id;
		oNextCategory.next_id = oCategory.id;

		$("#admin_ui_category_row_"+oNextCategory.id).after($("#admin_ui_category_row_"+oCategory.id));
		$("#admin_ui_category_row_"+oCategory.id).mouseout();
		
		this.update();
    },
    
    // 在分类 iParentID 下添加子分类, iParentID = 0时为添加顶级分类
    add: function(iParentID){
    	if(this.m_iNewID==0)
		{
			for(var x in this.m_oChain)
			{
				x = Number(x);
				if(this.m_iNewID<x) this.m_iNewID = x+1;
			}
		}
		this.m_iNewID++;
		
		var str = '<tr id="admin_ui_category_row_'+this.m_iNewID+'" class="ui-row new">'
		str += '<td>';
		str += '<input type="hidden" name="id[]" value="0" />';
		str += '<input type="text" name="name[]" value="" size="30" maxlength="120" />';
		str += '</td>';
		
		str += this.m_sTemplate;

        str += '<td align="center" width="20" class="order"><a href="javascript:;" onclick="javascript:admin_ui_category.orderUp('+this.m_iNewID+')" class="icon up"></a></td>';
        str += '<td align="center" width="20" class="order"><a href="javascript:;" onclick="javascript:admin_ui_category.orderDown('+this.m_iNewID+')" class="icon down"></a></td>';
        str += '<td align="center"><a href="javascript:;" onclick="javascript:admin_ui_category.remove('+this.m_iNewID+')" class="icon delete"></a></td>';

		str += '</tr>';
		

		if(this.m_iChainHead>0)
		{
			// 如果表头存在，则从表头开始遍历. 直到找到链表的结尾
			var oCurrentCategory = this.m_oChain[this.m_iChainHead];
			while(oCurrentCategory.next_id!=0) oCurrentCategory =  this.m_oChain[oCurrentCategory.next_id];

			oCurrentCategory.next_id = this.m_iNewID;
			this.addChain(this.m_iNewID, "", oCurrentCategory.id, 0);
			$("#admin_ui_category_row_"+oCurrentCategory.id).after(str);
		}
		else
		{
			this.m_iChainHead = this.m_iNewID;
			this.addChain(this.m_iNewID, "", 0, 0);
			$("#admin_ui_category_rows").append(str);
		}

		this.update();
    },
    
    // 删除分类
    remove: function(id){
    	
		var oCurrentCategory = this.m_oChain[id]; // 获取当前操作的节点
		
		var $e = $("#admin_ui_category_row_" + id);
		if (!$e.hasClass("new") && !confirm(LANG_UI_CATEGORY_DELETE_CONFIRM)) return;


		if ($e.hasClass("new")) {

			if(oCurrentCategory.pre_id)
				this.m_oChain[oCurrentCategory.pre_id].next_id = oCurrentCategory.next_id;
			else
				this.m_iChainHead = oCurrentCategory.next_id;
			
			// 如果有直接后继, 则把当前节点的直接前趋赋值给下一个节点的直接前趋
			if(oCurrentCategory.next_id)
				this.m_oChain[oCurrentCategory.next_id].pre_id = oCurrentCategory.pre_id;
	
	
			this.m_oChain[id] = null;
			
			$e.remove();
			
			this.update();
		}
		else 
		{
			var _this = this;
			
			$("#admin_ui_category_row_" + id + " .delete").fadeOut();

			// 提交服务器删除操作
			$.ajax({
				type: 'POST',
				url: admin_ui_category.m_sDeleteAction,
				data: "id=" + id,
				dataType : 'json',
				success: function(json){
					
					if(json.error=="0")
					{
						if(oCurrentCategory.pre_id)
							_this.m_oChain[oCurrentCategory.pre_id].next_id = oCurrentCategory.next_id;
						else
							_this.m_iChainHead = oCurrentCategory.next_id;
						
						// 如果有直接后继, 则把当前节点的直接前趋赋值给下一个节点的直接前趋
						if(oCurrentCategory.next_id)
							_this.m_oChain[oCurrentCategory.next_id].pre_id = oCurrentCategory.pre_id;
				
				
						_this.m_oChain[id] = null;
						
						$e.remove();
			
						admin_ui_category.update();
					}
					else
					{
						$("#admin_ui_category_row_" + id + " .delete").fadeIn();
						
						alert(json.message);
					}
				}
			});
		}

    }
    
}

