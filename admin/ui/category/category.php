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

    public function set_action($type, $url = null, $label = null)
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
    public function set_data($objs)
    {
        $this->data = $objs;
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
			echo 'Please set (ui_category->set_action)';
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
                    echo 'ui_category.addChain(' . $category->id . ',"' . $category->name . '",' . $pre_id . ',' . $next_id .');';
                }
                else
                    echo 'ui_category.setChainHead(' . $cat->id . ');';
                $category = $cat;
            }
            echo 'ui_category.addChain(' . $category->id . ',"' . $category->name . '",' . $current_id . ',0'. ');';
            
			echo 'ui_category.setSaveAction("' . $this->actions['save']['url'] . '");';
            echo 'ui_category.setDeleteAction("' . $this->actions['delete']['url'] . '");';
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
        
        echo 'ui_category.setTemplate("' . $template . '");';
        echo '</script>';
        
        echo '<div class="ui_category">';
        echo '<form action="'.$this->actions['save']['url'].'" id="ui_category_form" method="post">';
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
        
        echo '<tbody id="ui_category_rows">';
        
        $n = count($this->fields) + 6;
        
        if (count($this->data)) {
            foreach ($this->data as $obj) {
                echo '<tr id="ui_category_row_' . $obj->id . '" class="ui-row">';
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
                
                echo '<td align="center" width="20" class="order"><a href="javascript:;" onclick="javascript:ui_category.orderUp(' . $obj->id . ')" class="icon up"></a></td>';
                echo '<td align="center" width="20" class="order"><a href="javascript:;" onclick="javascript:ui_category.orderDown(' . $obj->id . ')" class="icon down"></a></td>';
                echo '<td align="center"><a href="javascript:;" onclick="javascript:ui_category.remove(' . $obj->id . ')" class="icon delete"';
                echo '></a></td>';
                
                echo '</tr>';
            }
        }
        echo '</tbody>';
        
        echo '<tfoot>';
        echo '<tr>';
        echo '<td colspan="' . $n . '">';
        
        echo '<input type="button" class="btn btn-success" value="'.'添加'.'" onclick="javascript:ui_category.add(0)"/> &nbsp;';
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