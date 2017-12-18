
$(function(){
    ui_category_tree.init();
});



var ui_category_tree = {

    m_oChain: new Object(),
    m_iChainHead: 0,
    m_sSaveAction: '',
	m_sDeleteAction: '',
	m_sTemplate : "",
	
	m_iNewID : 0,
    
    addChain: function(id, sName, iParentID, iPreID, iNextID, iLevel, iChildren){
        this.m_oChain[id] = {
            "id": id,
            "name": sName,
            "parent_id": iParentID,
            "pre_id": iPreID,
            "next_id": iNextID,
            "level": iLevel,
            "children": iChildren
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
		
	    $(".ui_category_tree .ui-row").hover(function(){
            $(this).addClass("hover");
        }, function(){
            $(this).removeClass("hover");
        });
	
        ui_category_tree.update();
    },
    
    update: function(){

        // 更新排序图标
        if (this.m_iChainHead == 0) 
            return;
        
        var oCategory = this.m_oChain[this.m_iChainHead];
        var oCurrentCategory = oCategory;
        while (oCurrentCategory.next_id != 0) {
        
            var $eOrderUp = $("#ui_category_tree_row_" + oCurrentCategory.id + " .order .up");
            var $eOrderDown = $("#ui_category_tree_row_" + oCurrentCategory.id + " .order .down");
            
            if (oCurrentCategory.pre_id != 0) {
                if (this.m_oChain[oCurrentCategory.pre_id].level < oCurrentCategory.level) 
                    $eOrderUp.addClass("disable");
                else
                    $eOrderUp.removeClass("disable");
            }
            else 
                $eOrderUp.addClass("disable");
            
            if (this.m_oChain[oCurrentCategory.next_id].level < oCurrentCategory.level) 
                $eOrderDown.addClass("disable");
            else 
                $eOrderDown.removeClass("disable");
            
            if (oCurrentCategory.children == 0) { // 有子分类时禁止删除
				$("#ui_category_tree_row_" + oCurrentCategory.id + " .delete").fadeIn();
			}
			else
			{
                var oTmp = this.m_oChain[oCurrentCategory.next_id];
                while (oTmp.next_id != 0 && oTmp.level > oCurrentCategory.level) 
                    oTmp = this.m_oChain[oTmp.next_id];
                
                if (oTmp.level != oCurrentCategory.level) {
                    $eOrderDown.addClass("disable");
                }
				
				$("#ui_category_tree_row_" + oCurrentCategory.id + " .delete").fadeOut();
            }
            
            oCurrentCategory = this.m_oChain[oCurrentCategory.next_id];
        }
        
        if (oCurrentCategory.pre_id == 0) 
            $("#ui_category_tree_row_" + oCurrentCategory.id + " .order .up").addClass("disable");
        else {
            if (this.m_oChain[oCurrentCategory.pre_id].level >= oCurrentCategory.level) 
                $("#ui_category_tree_row_" + oCurrentCategory.id + " .order .up").removeClass("disable");
            else 
                $("#ui_category_tree_row_" + oCurrentCategory.id + " .order .up").addClass("disable");
        }

        $("#ui_category_tree_row_" + oCurrentCategory.id + " .order .down").addClass("disable");
        
        if (oCurrentCategory.level != 0) {
            while (oCurrentCategory.level != 0) {
                oCurrentCategory = this.m_oChain[oCurrentCategory.pre_id];
            }
            
            $("#ui_category_tree_row_tree_" + oCurrentCategory.id + " .order .down").addClass("disable");
        }
		
		// 有子分类时禁止删除
		if (oCurrentCategory.children == 0)
			$("#ui_category_tree_row_" + oCurrentCategory.id + " .delete").fadeIn();
		else
			$("#ui_category_tree_row_" + oCurrentCategory.id + " .delete").fadeOut();
    },
    
    
    
    // 打开/合上子节点
    toggle: function(id){
        var $e = $("#ui_category_tree_row_" + id + " .toggle a");
        
        if ($e.hasClass("ui-close")) 
            $e.removeClass("ui-close");
        else 
            $e.addClass("ui-close");
        
        var oCategory = this.m_oChain[id];
        var oCurrentCategory = oCategory;

        while (oCurrentCategory.next_id > 0) {
            oCurrentCategory = this.m_oChain[oCurrentCategory.next_id]; // 链表后移
            if (oCurrentCategory.level > oCategory.level) {
                if ($e.hasClass("ui-close"))
				{
                    $("#ui_category_tree_row_" + oCurrentCategory.id).hide();
                }
                else
				{
                    $("#ui_category_tree_row_" + oCurrentCategory.id).show();
					$("#ui_category_tree_row_" + oCurrentCategory.id + " .toggle a").removeClass("ui-close");
                }
            }
            else 
                break;
        }
    },
    
    
    
    
    // 上移
    orderUp: function(id)
	{
		
		if( $("#ui_category_tree_row_" + id + " .order .up").hasClass("disable") ) return;
		
		var oCategory = this.m_oChain[id];
		
		var oPreCategoryHead = this.m_oChain[oCategory.pre_id];
		var oPreCategoryTail = this.m_oChain[oCategory.pre_id];
		
		while(oPreCategoryHead.pre_id!=0 && oPreCategoryHead.level>oCategory.level) oPreCategoryHead = this.m_oChain[oPreCategoryHead.pre_id];
	
		if(oPreCategoryHead.pre_id==0)
			this.m_iChainHead = oCategory.id;
		else
			this.m_oChain[oPreCategoryHead.pre_id].next_id = oCategory.id;
	
		oCategory.pre_id = oPreCategoryHead.pre_id;
	
		$("#ui_category_tree_row_"+oPreCategoryHead.id).before($("#ui_category_tree_row_"+oCategory.id));
		$("#ui_category_tree_row_"+oCategory.id).mouseout();
		
		
		var oLastMovedCategory;
		if(oCategory.next_id==0)
		{
			oLastMovedCategory = oCategory;
		}
		else
		{
			oLastMovedCategory = this.m_oChain[oCategory.next_id];
			while(oLastMovedCategory.next_id!=0 && oLastMovedCategory.level>oCategory.level)
			{
				$("#ui_category_tree_row_"+oPreCategoryHead.id).before($("#ui_category_tree_row_"+oLastMovedCategory.id));
				oLastMovedCategory =  this.m_oChain[oLastMovedCategory.next_id];
			}
			
			if(oLastMovedCategory.next_id==0)
			{
				if(oLastMovedCategory.level>oCategory.level)
					$("#ui_category_tree_row_"+oPreCategoryHead.id).before($("#ui_category_tree_row_"+oLastMovedCategory.id));
				else
					oLastMovedCategory = this.m_oChain[oLastMovedCategory.pre_id];
			}
			else
				oLastMovedCategory = this.m_oChain[oLastMovedCategory.pre_id];
		}
		
		if(oLastMovedCategory.next_id!=0)
			this.m_oChain[oLastMovedCategory.next_id].pre_id = oPreCategoryTail.id;
			
		oPreCategoryHead.pre_id = oLastMovedCategory.id;
		oPreCategoryTail.next_id = oLastMovedCategory.next_id;
		oLastMovedCategory.next_id = oPreCategoryHead.id;
	
		this.update();
    },
    
    // 下移
    orderDown: function(id)
	{
		if( $("#ui_category_tree_row_" + id + " .order .down").hasClass("disable") ) return;

		var oCategory = this.m_oChain[id];
	
		var oNextCategoryHead = this.m_oChain[oCategory.next_id];
		while( oNextCategoryHead.level>oCategory.level ) oNextCategoryHead = this.m_oChain[oNextCategoryHead.next_id];
	
		var oNextCategoryTail = oNextCategoryHead;
		while(oNextCategoryTail.next_id!=0 && this.m_oChain[oNextCategoryTail.next_id].level>oNextCategoryHead.level) oNextCategoryTail = this.m_oChain[oNextCategoryTail.next_id];
	
		if(oCategory.pre_id==0)
			this.m_iChainHead = oNextCategoryHead.id;
		else
			this.m_oChain[oCategory.pre_id].next_id = oNextCategoryHead.id;
	
		oNextCategoryHead.pre_id = oCategory.pre_id;
		oCategory.pre_id = oNextCategoryTail.id;
		
		oLastMovedCategory = oCategory;
		while(oLastMovedCategory.next_id != oNextCategoryHead.id)
		{
			$("#ui_category_tree_row_"+oLastMovedCategory.pre_id).after($("#ui_category_tree_row_"+oLastMovedCategory.id));
			oLastMovedCategory = this.m_oChain[oLastMovedCategory.next_id];
		}
		$("#ui_category_tree_row_"+oLastMovedCategory.pre_id).after($("#ui_category_tree_row_"+oLastMovedCategory.id));
		$("#ui_category_tree_row_"+oCategory.id).mouseout();
		
		if(oNextCategoryTail.next_id!=0)
			this.m_oChain[oNextCategoryTail.next_id].pre_id = oLastMovedCategory.id;
	
		oLastMovedCategory.next_id = oNextCategoryTail.next_id;
		oNextCategoryTail.next_id = oCategory.id;
	
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
		
		var str = '<tr id="ui_category_tree_row_'+this.m_iNewID+'" class="ui-row new'+(iParentID==0?' top':' sub')+'" onMouseOver="javascript:$(this).addClass(\'hover\');" onMouseOut="javascript:$(this).removeClass(\'hover\');">'
		str += '<td></td>'
		str += '<td></td>'
		str += '<td>';
		str += '<div class="name"';
		if(iParentID>0)
		{
			var oCategory = this.m_oChain[iParentID];
			str += ' style="padding-left:'+((oCategory.level + 1) * 40)+'px;background-position:'+((oCategory.level + 1) * 40-20)+'px 0px;"';
		}
		str +=  '>';
		str += '<input type="hidden" name="id[]" value="0" />';
		str += '<input type="hidden" name="parent_id[]" value="'+iParentID+'" />';
		str += '<input type="text" name="name[]" value="" size="30" maxlength="120" />';
		str +=  '</div>';
		str += '</td>';
		
		str += this.m_sTemplate;

        str += '<td align="center" width="20" class="order"><a href="javascript:;" onclick="javascript:ui_category_tree.orderUp('+this.m_iNewID+')" class="icon up"></a></td>';
        str += '<td align="center" width="20" class="order"><a href="javascript:;" onclick="javascript:ui_category_tree.orderDown('+this.m_iNewID+')" class="icon down"></a></td>';
        str += '<td align="center"><a href="javascript:;" onclick="javascript:ui_category_tree.remove('+this.m_iNewID+')" class="icon delete"></a></td>';

		str += '</tr>';
		
		if(iParentID==0)
		{
			if(this.m_iChainHead>0)
			{
				// 如果表头存在，则从表头开始遍历. 直到找到链表的结尾
				var oCurrentCategory = this.m_oChain[this.m_iChainHead];
				while(oCurrentCategory.next_id!=0) oCurrentCategory =  this.m_oChain[oCurrentCategory.next_id];
	
				oCurrentCategory.next_id = this.m_iNewID;
				this.addChain(this.m_iNewID, "", 0, oCurrentCategory.id, 0, 0, 0);
				$("#ui_category_tree_row_"+oCurrentCategory.id).after(str);
			}
			else
			{
				this.m_iChainHead = this.m_iNewID;
				this.addChain(this.m_iNewID, "", 0, 0, 0, 0, 0);
				$("#ui_category_tree_rows").append(str);
			}
		}
		else
		{
			
			// 在指定 iParentID 下添加子节点， 遍历链表中 iParentID 之后的元素， 直到找出不是 iParentID 节点子孙的节点。 在该节点之前添加即可
			var oParentCategory = this.m_oChain[iParentID];
			var oCurrentCategory = oParentCategory;
			
			if(oCurrentCategory.next_id>0)
			{
				while(oCurrentCategory.next_id!=0)
				{
					oCurrentCategory =  this.m_oChain[oCurrentCategory.next_id];	// 链表后移
					
					if(oCurrentCategory.level<=oParentCategory.level)
					{
						oCurrentCategory = this.m_oChain[oCurrentCategory.pre_id];	// 找到不是 iParentID 子孙的节点， 因为该节点的 level 等于或小于 iParentID 对应的节点
						break;
					}
				}
			}
			
			if(oCurrentCategory.next_id==0)
			{
				// 如果找到的节点是链表尾， 则新添加的节点为新的表尾
				oCurrentCategory.next_id = this.m_iNewID;
				this.addChain(this.m_iNewID, "", iParentID, oCurrentCategory.id, 0, oParentCategory.level+1, 0);
			}
			else
			{
				//在链表中插入新节点，
				var oNextCategory =  this.m_oChain[oCurrentCategory.next_id];
				oCurrentCategory.next_id = this.m_iNewID;
				oNextCategory.pre_id = this.m_iNewID;
				this.addChain(this.m_iNewID, "", iParentID, oCurrentCategory.id, oNextCategory.id, oParentCategory.level+1, 0);
			}

			$("#ui_category_tree_row_"+oCurrentCategory.id).after(str);
			
			var $e = $("#ui_category_tree_row_" + oParentCategory.id + " .toggle a");
			if(oParentCategory.children)
			{
				// 添加新节点时， 如果父节点是合上的， 则自动打开
				if ($e.hasClass("ui-close")) 	
					this.toggle(iParentID)
			}
			else
			{
				$e.fadeIn();
			}
			
			oParentCategory.children++;
			
		}
		this.update();
    },
    
    // 删除分类
    remove: function(id){
    	
		var oCurrentCategory = this.m_oChain[id]; // 获取当前操作的节点
		if(oCurrentCategory.children>0) return;
		
		var $e = $("#ui_category_tree_row_" + id);
		if (!$e.hasClass("new") && !confirm(LANG_UI_CATEGORY_TREE_DELETE_CONFIRM)) return;

		if(oCurrentCategory.pre_id)
			this.m_oChain[oCurrentCategory.pre_id].next_id = oCurrentCategory.next_id;
		else
			this.m_iChainHead = oCurrentCategory.next_id;
		
		// 如果有直接后继, 则把当前节点的直接前趋赋值给下一个节点的直接前趋
		if(oCurrentCategory.next_id)
			this.m_oChain[oCurrentCategory.next_id].pre_id = oCurrentCategory.pre_id;


		if(oCurrentCategory.parent_id>0)
		{
			// 判数父节点是否已清空， 如果已为空， 则隐藏打开/缩起图标
			var oParentCategory = this.m_oChain[oCurrentCategory.parent_id];
			oParentCategory.children--;
			
			if (oParentCategory.children == 0) {
				$("#ui_category_tree_row_" + oParentCategory.id + " .toggle a").fadeOut();
				$("#ui_category_tree_row_" + oParentCategory.id + " .delete").fadeIn();
			}
		}

		this.m_oChain[id] = null;


		if ($e.hasClass("new")) {
			$e.remove();
			this.update();
		}
		else 
		{
			$("#ui_category_tree_row_" + id + " .add, #ui_category_tree_row_" + id + " .delete").fadeOut();
			$("#ui_category_tree_row_" + id + " .name").append(" &nbsp; " + g_sLoadingImage + LANG_UI_CATEGORY_TREE_DELETING);

			// 提交服务器删除操作
			$.ajax({
				type: 'POST',
				url: ui_category_tree.m_sDeleteAction,
				data: "id=" + id,
				dataType : 'json',
				success: function(json){
					//if(json.error=="0")
					//{
						$e.remove();
						ui_category_tree.update();
					//}
					//else
					//{
						alert(json.message);
					//}
				}
			});
		}

    }
    
}

