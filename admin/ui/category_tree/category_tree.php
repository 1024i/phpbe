<?php
namespace admin\ui\category_tree;

class category_tree extends \system\ui
{
	private $actions = array();

    private $data = null;
    private $fields = null;
    
    private $header = ''; //页眉
    private $footer = ''; // 页脚
	
    public function __construct()
    {
	    $this->actions['save'] = false;
		$this->actions['delete'] = false;
    }

    public function set_action($type, $url = null, $label = null)
    {
		if ($label == null) {
			switch($type)
			{
				case 'save': $label = '保存'; break;
				case 'delete': $label = '保存'; break;
			}
		}
		$this->actions[$type] = array('url'=>$url, 'label'=>$label);
    }




   /**
    * 设置分类数据
    * 传递的数据项必须包含 parent_id, name, ordering 属性
    * @param array $objs
    */
    public function set_data($objs)
    {
        $this->data = $this->_create_categories($this->_create_categoy_tree($objs));
    }

    public function set_fields()
    {
        $this->fields = func_get_args();
    }
    
    public function set_header($header)
    {
        $this->header = $header;
    }
    
    public function set_footer($footer)
    {
        $this->footer = $footer;
    }

	private $head = false;
	public function head()
	{
		if (!$this->head) {
			$this->head = true;
			?>
<link type="text/css" rel="stylesheet" href="ui/category_tree/css/category_tree.css" />
<script type="text/javascript" language="javascript">
	var LANG_UI_CATEGORY_TREE_DELETE_CONFIRM = '<?php echo '该操作不可恢复， 确认删除吗?'; ?>';
	var LANG_UI_CATEGORY_TREE_DELETING = '<?php echo '删除中...'; ?>';
</script>
<script type="text/javascript" language="javascript" src="ui/category_tree/js/category_tree.js"></script>
			<?php
		}
	}
	

    public function display()
    {
		if ($this->actions['save'] ===false || $this->actions['delete'] ===false) {
			echo 'Please set (ui_category_tree->set_action)';
			return;
		}
		
        $this->head();
        echo '<script type="text/javascript" language="javascript">';
        if (count($this->data)) {
            $pre_id = 0;
            $current_id = 0;
            $next_id = 0;
            
            $category = null;
            foreach ($this->data as $cat) {
                if ($category) {
                    $pre_id = $current_id;
                    $current_id = $category->id;
                    $next_id = $cat->id;
                    echo 'ui_category_tree.addChain(' . $category->id . ',"' . $category->name . '",' . $category->parent_id . ',' . $pre_id . ',' . $next_id . ',' . $category->level . ',' . $category->children . ');';
                }
                else
                    echo 'ui_category_tree.setChainHead(' . $cat->id . ');';
                $category = $cat;
            }
            echo 'ui_category_tree.addChain(' . $category->id . ',"' . $category->name . '",' . $category->parent_id . ',' . $current_id . ',0,' . $category->level . ',' . $category->children . ');';
            
			echo 'ui_category_tree.setSaveAction("' . $this->actions['save']['url'] . '");';
            echo 'ui_category_tree.setDeleteAction("' . $this->actions['delete']['url'] . '");';
        }
        
        $template = '';
        if ($this->fields != null) {
            foreach ($this->fields as $field) {
				$template .=  '<td  style="text-align:' . (isset($field['align']) ? $field['align'] : 'center') . '">';
                $template .= isset($field['default']) ? $field['default'] : '';
                $template .= '</td>';
            }
        }
        $template = str_replace('"', '\"', $template);
        
        echo 'ui_category_tree.setTemplate("' . $template . '");';
        echo '</script>';
        
        echo '<div class="ui_category_tree">';
        echo '<form action="'.$this->actions['save']['url'].'" id="ui_category_tree_form" method="post">';
        echo $this->header;
        echo '<table>';
        echo '<thead>';
        echo '<tr>';
        
        echo '<th width="30"></th>';
        echo '<th width="25"></th>';
        echo '<th align="left">'.'名称'.'</th>';
        
        if ($this->fields != null) {
            foreach ($this->fields as $field) {
				echo '<th style="text-align:' . (isset($field['align']) ? $field['align'] : 'center') . '"' . (isset($field['width']) ? (' width="' . intval($field['width']).'"') : '') . '>';
                echo isset($field['label']) ? $field['label'] : '';
                echo '</th>';
            }
        }
        
        echo '<th width="40" colspan="2" style="text-align:center;">'.'移动'.'</th>';
        echo '<th width="20"></th>';
        
        echo '</tr>';
        echo '</thead>';
        
        echo '<tbody id="ui_category_tree_rows">';
        
        $n = count($this->fields) + 6;
        
        if (count($this->data)) {
            foreach ($this->data as $obj) {
                echo '<tr id="ui_category_tree_row_' . $obj->id . '" class="ui-row'.(($obj->level == 0)?' top':' sub').'">';
                echo '<td align="right"><a href="javascript:;" onclick="javascript:ui_category_tree.add(' . $obj->id . ')" class="icon add" title="'.'在此分类下添加子分类'.'" data-toggle="tooltip"></a></td>';
                echo '<td align="center" class="toggle">';
                echo '<a class="icon" href="javascript:;" onclick="javascript:ui_category_tree.toggle(' . $obj->id . ')"';
                if ($obj->children == 0) echo ' style="display:none;"';
                echo '></a>';
                echo '</td>';
                echo '<td>';
                
                echo '<div class="name"';
                if ($obj->level > 0) echo ' style="padding-left:' . ($obj->level*40) . 'px;background-position:' . ($obj->level*40-20) . 'px 0px;"';
                echo '>';
                echo '<input type="hidden" name="id[]" value="' . $obj->id . '" />';
                echo '<input type="hidden" name="parent_id[]" value="' . $obj->parent_id . '" />';
                echo '<input type="text" name="name[]" value="' . $obj->name . '" size="30" maxlength="120" />';
                echo '</div>';
                echo '</td>';
                
                if ($this->fields != null) {
                    foreach ($this->fields as $field) {
                        echo '<td align="' . (isset($field['align']) ? $field['align'] : 'center') . '"' . (isset($field['width']) ? (' width="' . intval($field['width']).'"') : '') . '>';
                        
                        if (isset($field['template'])) {
                            $str = $field['template'];
                            $start = strpos($str, '{');
                            while ($start !== false)
                            {
                                $end = strpos($str, '}');
                                $key = substr($str, $start + 1, ($end - $start - 1));
                                $val = isset($obj->$key) ? $obj->$key : '';
                                $str = str_replace('{' . $key . '}', $val, $str);
                                $start = strpos($str, '{');
                            }
                            echo $str;
                        } else {
                            echo $obj->{$field['name']};
                        }
                        
                        echo '</td>';
                    }
                }
                
                echo '<td align="center" width="20" class="order"><a href="javascript:;" onclick="javascript:ui_category_tree.orderUp(' . $obj->id . ')" class="icon up"></a></td>';
                echo '<td align="center" width="20" class="order"><a href="javascript:;" onclick="javascript:ui_category_tree.orderDown(' . $obj->id . ')" class="icon down"></a></td>';
                echo '<td align="center" width="20"><a href="javascript:;" onclick="javascript:ui_category_tree.remove(' . $obj->id . ')" class="icon delete"';
                if ($obj->children!=0) echo ' style="display:none;"';
                echo '></a></td>';
                
                echo '</tr>';
            }
        }
        echo '</tbody>';
        
        echo '<tfoot>';
        echo '<tr>';
        echo '<td colspan="' . $n . '">';
        
        echo '<input type="button" class="btn btn-success" value="'.'添加'.'" onclick="javascript:ui_category_tree.add(0)"/> &nbsp;';
        echo '<input type="submit" class="btn btn-primary" value="'.$this->actions['save']['label'].'" />';
        
        echo '</td>';
        echo '</tr>';
        echo '</tfoot>';
        
        echo '</table>';
        echo $this->footer;
        echo '</form>';
        echo '</div>';
    
    }
    

    private function _create_categories($category_tree = null, &$categories = array())
    {
        if (count($category_tree)) {
            foreach ($category_tree as $category) {
                $sub_category = null;
                if (isset($category->sub_category)) {
                    $sub_category = $category->sub_category;
                    unset($category->sub_category);
                }
                $categories[] = $category;
                
                if ($sub_category !== null) $this->_create_categories($sub_category, $categories);
            }
        }
        return $categories;
    }

    private function _create_categoy_tree(&$categories = null, $parent_id = 0, $level = 0)
    {
        $tree = array();
        foreach ($categories as $category) {
            if ($category->parent_id == $parent_id) {
                $category->level = $level;
                $sub_category = $this->_create_categoy_tree($categories, $category->id, $level + 1);
                if (count($sub_category)) $category->sub_category = $sub_category;
                $category->children = count($sub_category);
                $tree[] = $category;
            }
        }
        return $tree;
    }
    

}
