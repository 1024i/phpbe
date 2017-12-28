<?php
namespace admin\ui\category;

class category extends \system\ui
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
				case 'delete': $label = '删除'; break;
			}
		}
		$this->actions[$type] = array('url'=>$url, 'label'=>$label);
    }




   /**
    * 设置分类数据
    * 传递的数据项必须包含 name, ordering 属性
    * @param array $objs
    */
    public function setData($objs)
    {
        $this->data = $objs;
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
<link type="text/css" rel="stylesheet" href="ui/category/css/category.css" />
<script type="text/javascript" language="javascript">
	var LANG_UI_CATEGORY_DELETE_CONFIRM = '<?php echo '该操作不可恢复， 确认删除吗?'; ?>';
</script>
<script type="text/javascript" language="javascript" src="ui/category/js/category.js"></script>
			<?php
		}
	}


    public function display()
    {
		if ($this->actions['save'] ===false || $this->actions['delete'] ===false) {
			echo 'Please set (uiCategory->setAction)';
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
                    echo 'uiCategory.addChain(' . $category->id . ',"' . $category->name . '",' . $preId . ',' . $nextId .');';
                }
                else
                    echo 'uiCategory.setChainHead(' . $cat->id . ');';
                $category = $cat;
            }
            echo 'uiCategory.addChain(' . $category->id . ',"' . $category->name . '",' . $currentId . ',0'. ');';
            
			echo 'uiCategory.setSaveAction("' . $this->actions['save']['url'] . '");';
            echo 'uiCategory.setDeleteAction("' . $this->actions['delete']['url'] . '");';
        }
        
        $template = '';
        if ($this->fields != null) {
            foreach ($this->fields as $field) {
                $template .= '<td style="text-align:' . (isset($field['align']) ? $field['align'] : 'center') . '">';
                $template .= isset($field['default']) ? $field['default'] : '';
                $template .= '</td>';
            }
        }
        $template = str_replace('"', '\"', $template);
        
        echo 'uiCategory.setTemplate("' . $template . '");';
        echo '</script>';
        
        echo '<div class="uiCategory">';
        echo '<form action="'.$this->actions['save']['url'].'" id="uiCategoryForm" method="post">';
        echo $this->header;
        echo '<table class="table table-striped table-hover">';
        echo '<thead>';
        echo '<tr>';
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
        
        echo '<tbody id="uiCategoryRows">';
        
        $n = count($this->fields) + 6;
        
        if (count($this->data)) {
            foreach ($this->data as $obj) {
                echo '<tr id="uiCategoryRow_' . $obj->id . '" class="ui-row">';
                echo '<td>';                
                echo '<input type="hidden" name="id[]" value="' . $obj->id . '" />';
                echo '<input type="text" name="name[]" value="' . $obj->name . '" size="30" maxlength="120" />';
                echo '</td>';
                
                if ($this->fields != null) {
                    foreach ($this->fields as $field) {
                        echo '<td style="text-align:' . (isset($field['align']) ? $field['align'] : 'center') . '"' . (isset($field['width']) ? (' width="' . intval($field['width']).'"') : '') . '>';
                        
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
                
                echo '<td align="center" width="20" class="order"><a href="javascript:;" onclick="javascript:uiCategory.orderUp(' . $obj->id . ')" class="icon up"></a></td>';
                echo '<td align="center" width="20" class="order"><a href="javascript:;" onclick="javascript:uiCategory.orderDown(' . $obj->id . ')" class="icon down"></a></td>';
                echo '<td align="center"><a href="javascript:;" onclick="javascript:uiCategory.remove(' . $obj->id . ')" class="icon delete"';
                echo '></a></td>';
                
                echo '</tr>';
            }
        }
        echo '</tbody>';
        
        echo '<tfoot>';
        echo '<tr>';
        echo '<td colspan="' . $n . '">';
        
        echo '<input type="button" class="btn btn-success" value="'.'添加'.'" onclick="javascript:uiCategory.add(0)"/> &nbsp;';
        echo '<input type="submit" class="btn btn-primary" value="'.$this->actions['save']['label'].'" />';
        
        echo '</td>';
        echo '</tr>';
        echo '</tfoot>';
        
        echo '</table>';
        echo $this->footer;
        echo '</form>';
        echo '</div>';
    
    }
    
    

}
?>