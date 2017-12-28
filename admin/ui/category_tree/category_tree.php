<?php
namespace admin\ui\categoryTree;

class categoryTree extends \system\ui
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

    public function setAction($type, $url = null, $label = null)
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
    * 传递的数据项必须包含 parentId, name, ordering 属性
    * @param array $objs
    */
    public function setData($objs)
    {
        $this->data = $this->CreateCategories($this->CreateCategoyTree($objs));
    }

    public function setFields()
    {
        $this->fields = func_get_args();
    }
    
    public function setHeader($header)
    {
        $this->header = $header;
    }
    
    public function setFooter($footer)
    {
        $this->footer = $footer;
    }

	private $head = false;
	public function head()
	{
		if (!$this->head) {
			$this->head = true;
			?>
<link type="text/css" rel="stylesheet" href="ui/categoryTree/css/categoryTree.css" />
<script type="text/javascript" language="javascript">
	var LANG_UI_CATEGORY_TREE_DELETE_CONFIRM = '<?php echo '该操作不可恢复， 确认删除吗?'; ?>';
	var LANG_UI_CATEGORY_TREE_DELETING = '<?php echo '删除中...'; ?>';
</script>
<script type="text/javascript" language="javascript" src="ui/categoryTree/js/categoryTree.js"></script>
			<?php
		}
	}
	

    public function display()
    {
		if ($this->actions['save'] ===false || $this->actions['delete'] ===false) {
			echo 'Please set (uiCategoryTree->setAction)';
			return;
		}
		
        $this->head();
        echo '<script type="text/javascript" language="javascript">';
        if (count($this->data)) {
            $preId = 0;
            $currentId = 0;
            $nextId = 0;
            
            $category = null;
            foreach ($this->data as $cat) {
                if ($category) {
                    $preId = $currentId;
                    $currentId = $category->id;
                    $nextId = $cat->id;
                    echo 'uiCategoryTree.addChain(' . $category->id . ',"' . $category->name . '",' . $category->parentId . ',' . $preId . ',' . $nextId . ',' . $category->level . ',' . $category->children . ');';
                }
                else
                    echo 'uiCategoryTree.setChainHead(' . $cat->id . ');';
                $category = $cat;
            }
            echo 'uiCategoryTree.addChain(' . $category->id . ',"' . $category->name . '",' . $category->parentId . ',' . $currentId . ',0,' . $category->level . ',' . $category->children . ');';
            
			echo 'uiCategoryTree.setSaveAction("' . $this->actions['save']['url'] . '");';
            echo 'uiCategoryTree.setDeleteAction("' . $this->actions['delete']['url'] . '");';
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
        
        echo 'uiCategoryTree.setTemplate("' . $template . '");';
        echo '</script>';
        
        echo '<div class="uiCategoryTree">';
        echo '<form action="'.$this->actions['save']['url'].'" id="uiCategoryTreeForm" method="post">';
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
        
        echo '<tbody id="uiCategoryTreeRows">';
        
        $n = count($this->fields) + 6;
        
        if (count($this->data)) {
            foreach ($this->data as $obj) {
                echo '<tr id="uiCategoryTreeRow_' . $obj->id . '" class="ui-row'.(($obj->level == 0)?' top':' sub').'">';
                echo '<td align="right"><a href="javascript:;" onclick="javascript:uiCategoryTree.add(' . $obj->id . ')" class="icon add" title="'.'在此分类下添加子分类'.'" data-toggle="tooltip"></a></td>';
                echo '<td align="center" class="toggle">';
                echo '<a class="icon" href="javascript:;" onclick="javascript:uiCategoryTree.toggle(' . $obj->id . ')"';
                if ($obj->children == 0) echo ' style="display:none;"';
                echo '></a>';
                echo '</td>';
                echo '<td>';
                
                echo '<div class="name"';
                if ($obj->level > 0) echo ' style="padding-left:' . ($obj->level*40) . 'px;background-position:' . ($obj->level*40-20) . 'px 0px;"';
                echo '>';
                echo '<input type="hidden" name="id[]" value="' . $obj->id . '" />';
                echo '<input type="hidden" name="parentId[]" value="' . $obj->parentId . '" />';
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
                
                echo '<td align="center" width="20" class="order"><a href="javascript:;" onclick="javascript:uiCategoryTree.orderUp(' . $obj->id . ')" class="icon up"></a></td>';
                echo '<td align="center" width="20" class="order"><a href="javascript:;" onclick="javascript:uiCategoryTree.orderDown(' . $obj->id . ')" class="icon down"></a></td>';
                echo '<td align="center" width="20"><a href="javascript:;" onclick="javascript:uiCategoryTree.remove(' . $obj->id . ')" class="icon delete"';
                if ($obj->children!=0) echo ' style="display:none;"';
                echo '></a></td>';
                
                echo '</tr>';
            }
        }
        echo '</tbody>';
        
        echo '<tfoot>';
        echo '<tr>';
        echo '<td colspan="' . $n . '">';
        
        echo '<input type="button" class="btn btn-success" value="'.'添加'.'" onclick="javascript:uiCategoryTree.add(0)"/> &nbsp;';
        echo '<input type="submit" class="btn btn-primary" value="'.$this->actions['save']['label'].'" />';
        
        echo '</td>';
        echo '</tr>';
        echo '</tfoot>';
        
        echo '</table>';
        echo $this->footer;
        echo '</form>';
        echo '</div>';
    
    }
    

    private function CreateCategories($categoryTree = null, &$categories = array())
    {
        if (count($categoryTree)) {
            foreach ($categoryTree as $category) {
                $subCategory = null;
                if (isset($category->subCategory)) {
                    $subCategory = $category->subCategory;
                    unset($category->subCategory);
                }
                $categories[] = $category;
                
                if ($subCategory !== null) $this->CreateCategories($subCategory, $categories);
            }
        }
        return $categories;
    }

    private function CreateCategoyTree(&$categories = null, $parentId = 0, $level = 0)
    {
        $tree = array();
        foreach ($categories as $category) {
            if ($category->parentId == $parentId) {
                $category->level = $level;
                $subCategory = $this->CreateCategoyTree($categories, $category->id, $level + 1);
                if (count($subCategory)) $category->subCategory = $subCategory;
                $category->children = count($subCategory);
                $tree[] = $category;
            }
        }
        return $tree;
    }
    

}
