<?php
namespace admin\ui;

class grid extends \ui
{
    private $actions = array();
    
    private $data = null;
    private $filters = null;
    private $fields = null;
    private $order_by = null;
    private $order_by_dir = null;
    
    private $footer = null;
    private $pagination = null;
    private $pagination_window = 15; // 控制分页最多显示多少个页码
	
	private $index = 0;				// 一个页面上出现多个 ui list  时防止冲突

    public function __construct()
    {
		$this->actions['list'] = array('url'=>'');
		
        $this->actions['create'] = false;
        $this->actions['edit'] = false;
        $this->actions['block'] = false;
        $this->actions['unblock'] = false;
        $this->actions['delete'] = false;
    }
	

	private $head = false;
	public function head()
	{
		if (!$this->head) {
			$this->head = true;
			?>
<link type="text/css" rel="stylesheet" href="ui/grid/css/list.css" />
<script type="text/javascript" language="javascript">
	var LANG_UI_LIST_DELETE_CONFIRM = '<?php echo '该操作不可恢复， 确认删除吗?'; ?>';
</script>
<script type="text/javascript" language="javascript" src="ui/grid/js/list.js"></script>
			<?php
		}
	}
	
    public function get_index()
    {
        return $this->index;
    }
	
	public function set_action($type, $url, $label = null)
	{
		if ($label == null) {
			switch($type)
			{
				case 'create': $label = '新建'; break;
				case 'edit': $label = '修改'; break;
				case 'block': $label = '屏蔽'; break;
				case 'unblock': $label = '公开'; break;
				case 'delete': $label = '删除'; break;
				default: $label = '';
			}
		}
		$this->actions[$type] = array('url'=>$url, 'label'=>$label);
	}


    public function set_data($objs)
    {
        $this->data = $objs;
    }

    public function set_filters()
    {
        $this->filters = func_get_args();
    }

    public function set_fields()
    {
        $this->fields = func_get_args();
    }

    public function set_footer($footer)
    {
        $this->footer = $footer;
    }


    public function order_by($order_by, $order_by_dir)
    {
        $this->order_by = $order_by;
        $this->order_by_dir = $order_by_dir;
    }

    public function set_pagination($pagination)
    {
        $this->pagination = $pagination;
    }

    public function set_pagination_window($pagination_window)
    {
        $this->pagination_window = $pagination_window;
    }

    public function display()
    {
        $n = count($this->fields);
        if ($n == 0) return;
		
		$checkbox = false;
        if ($this->actions['edit'] != false || $this->actions['block'] != false || $this->actions['unblock'] != false || $this->actions['delete'] != false) {
			$checkbox = true;
			$n++;
		}
        
        if ($this->actions['edit'] != false) $n++;
        if ($this->actions['block'] != false || $this->actions['unblock'] != false) $n++;
        if ($this->actions['delete'] != false) $n++;

		$this->head();
		
		$this->index++;
		
        echo '<script type="text/javascript" language="javascript">';
		echo 'var oAdminUIList_'.$this->index.' = new admin_ui_list();';
		
        foreach ($this->actions as $key=>$val) {
            if ($val!=false) echo 'oAdminUIList_'.$this->index.'.setAction("' . $key . '", "'.$val['url'].'");';
        }
        echo '</script>';
		
        echo '<div class="admin_ui_list" id="admin_ui_list_'.$this->index.'">';

        echo '<form action="'.$this->actions['list']['url'].'" id="admin_ui_list_'.$this->index.'_form" class="form-inline" method="post">';
        echo '<div class="toolbar">';

        echo '<table>';
        echo '<tr>';
        echo '<td align="left" valign="bottom"><div class="filter">';
        
        if ($this->filters ! == null) {
            foreach ($this->filters as $filter) {
				if ($filter['type'] == 'button') continue;
				
				if (isset($filter['label']) && $filter['label']!='') echo $filter['label'] . ': ';
                switch ($filter['type'])
                {
                    case 'text':
                        if (isset($filter['html']))
                            echo $filter['html'];
                        else
                        {
                            echo '<input type="text" name="' . $filter['name'] . '" id="' . $filter['name'] . '"';
                            if (isset($filter['value'])) echo ' value="' . $filter['value'] . '"';
                            if (isset($filter['width'])) echo ' style="width:' . $filter['width'] . ';"';
                            echo ' />';
                        }
                        break;
                    case 'radio':
                        if (isset($filter['html']))
                            echo $filter['html'];
                        else
                        {
                            if (isset($filter['options']) && count($filter['options'])) {
                                foreach ($filter['options'] as $key => $val) {
                                    echo '<input type="radio" name="' . $filter['name'] . '" id="' . $filter['name'] . '-' . $key . '" value="' . $filter['value'] . '"';
                                    if (isset($filter['value']) && $filter['value'] == $key) echo ' checked="checked"';
                                    echo ' />';
                                    echo '<label for="' . $filter['name'] . '-' . $key . '">' . $val . '</label>';
                                    echo '&nbsp;';
                                }
                            }
                        }
                        break;
                    case 'checkbox':
                        if (isset($filter['html']))
                            echo $filter['html'];
                        else
                        {
                            if (isset($filter['options']) && count($filter['options'])) {
                                $name = (count($filter['options']) > 1) ? ($filter['name'] . '[]') : $filter['name'];
                                foreach ($filter['options'] as $key => $val) {
                                    echo '<input type="checkbox" name="' . $name . '" id="' . $filter['name'] . '-' . $key . '" value="' . $filter['value'] . '"';
                                    if (isset($filter['value']) && $filter['value'] == $key) echo ' checked="checked"';
                                    echo ' />';
                                    echo '<label for="' . $filter['name'] . '-' . $key . '">' . $val . '</label>';
                                    echo '&nbsp;';
                                }
                            }
                        }
                        break;
                    case 'select':
                        if (isset($filter['html']))
                            echo $filter['html'];
                        else
                        {
                            echo '<select name="' . $filter['name'] . '" id="' . $filter['name'] . '"';
							if (isset($filter['width'])) echo ' style="width:' . $filter['width'] . ';"';
							echo '>';
							
                            if (isset($filter['options']) && count($filter['options'])) {
                                foreach ($filter['options'] as $key => $val) {
                                    echo '<option value="' . $key . '"';
                                    if (isset($filter['value']) && $filter['value'] == $key) echo ' selected="selected"';
                                    echo '>' . $val . '</option>';
                                }
                            }
                            echo '</select>';
                        }
                        break;
					case 'hidden':
						echo '<input type="hidden" name="' . $filter['name'] . '" id="' . $filter['name'] . '"';
						if (isset($filter['value'])) echo ' value="' . $filter['value'] . '"';
						echo ' />';
                        break;
                }
                echo ' &nbsp; ';
            }
            echo '<input type="button" class="btn btn-primary" onclick="javascript:oAdminUIList_'.$this->index.'.filter();" value="'.'查找'.'" >';
			
			foreach ($this->filters as $filter) {
				if ($filter['type']!='button') continue;
				
				if (isset($filter['label']) && $filter['label']!='') echo $filter['label'] . ': ';
				if (isset($filter['html']))
					echo $filter['html'];
				else
				{
					echo ' <input type="button"';
					if (isset($filter['name'])) echo 'name="' . $filter['name'] . '" id="' . $filter['name'] . '"';
					if (isset($filter['value'])) echo ' value="' . $filter['value'] . '"';
					if (isset($filter['click'])) echo ' onclick="' . $filter['click'] . '"';
					echo ' class="'.(isset($filter['class'])?$filter['class']:'btn').'" />';
				}
				echo ' &nbsp; ';
                   
            }
        }
        echo '</div></td>';
        
        if ($this->actions['create'] != false) echo '<td width="46" style="text-align:center;"><a class="icon create able" id="admin_ui_list_'.$this->index.'_toolbar_create" href="javascript:;" onclick="javascript:oAdminUIList_'.$this->index.'.create();" title="' . $this->actions['create']['label'] . '" data-toggle="tooltip">' . $this->actions['create']['label'] . '</a></td>';
        if ($this->actions['edit'] != false) echo '<td width="46" style="text-align:center;"><a class="icon edit disable" id="admin_ui_list_'.$this->index.'_toolbar_edit" href="javascript:;" onclick="javascript:if (!$(this).hasClass(\'disable\')){oAdminUIList_'.$this->index.'.edit(0);}" title="' . $this->actions['edit']['label'] . '选中项'.'" data-toggle="tooltip">' . $this->actions['edit']['label'] . '</a></td>';
        if ($this->actions['unblock'] != false) echo '<td width="46" style="text-align:center;"><a class="icon unblock disable" id="admin_ui_list_'.$this->index.'_toolbar_unblock" href="javascript:;" onclick="javascript:if (!$(this).hasClass(\'disable\')){oAdminUIList_'.$this->index.'.unblock(0);}" title="' . $this->actions['unblock']['label'] .'选中项'.'" data-toggle="tooltip">' . $this->actions['unblock']['label'] . '</a></td>';
        if ($this->actions['block'] != false) echo '<td width="46" style="text-align:center;"><a class="icon block disable" id="admin_ui_list_'.$this->index.'_toolbar_block" href="javascript:;" onclick="javascript:if (!$(this).hasClass(\'disable\')){oAdminUIList_'.$this->index.'.block(0);}" title="' . $this->actions['block']['label'] .'选中项'.'" data-toggle="tooltip">' . $this->actions['block']['label'] . '</a></td>';
        if ($this->actions['delete'] != false) echo '<td width="46" style="text-align:center;"><a class="icon delete disable" id="admin_ui_list_'.$this->index.'_toolbar_delete" href="javascript:;" onclick="javascript:if (!$(this).hasClass(\'disable\')){oAdminUIList_'.$this->index.'.remove(0);}" title="' . $this->actions['delete']['label'] .'选中项'.'" data-toggle="tooltip">' . $this->actions['delete']['label'] . '</a></td>';
        
        echo '</tr>';
        echo '</table>';
        
        echo '</div>';
        
        echo '<div class="list">';
        
        echo '<table class="table table-striped">';
        echo '<thead>';
        echo '<tr>';
        
        if ($checkbox) echo '<th style="text-align:center;" width="20"><input type="checkbox" id="admin_ui_list_'.$this->index.'_check_all" /></th>';
        foreach ($this->fields as $field) {
            echo '<th style="text-align:' . (isset($field['align']) ? $field['align'] : 'center') . ';" ' . (isset($field['width']) ? ('width=' . intval($field['width'])) : '') . '>';
            if (isset($field['order_by'])) {
                $order_by_dir = 'ASC';
                if ($this->order_by == $field['order_by'] && $this->order_by_dir == 'ASC') $order_by_dir = 'DESC';
                
                echo '<a href="javascript:;" onclick="javascript:oAdminUIList_'.$this->index.'.orderBy(\'' . $field['order_by'] . '\', \'' . $order_by_dir . '\');">';
                echo isset($field['label']) ? $field['label'] : '';
                if ($this->order_by == $field['order_by']) echo ($this->order_by_dir == 'ASC') ? '&darr;' : '&uarr;';
                echo '</a>';
            }
            else
                echo isset($field['label']) ? $field['label'] : '';
            echo '</th>';
        }
        
        if ($this->actions['block'] != false || $this->actions['unblock'] != false) {
            echo '<th style="text-align:center;" width="20"></th>';
        }
        if ($this->actions['edit'] != false) echo '<th style="text-align:center;" width="20"></th>';
        if ($this->actions['delete'] != false) echo '<th style="text-align:center;" width="20"></th>';
        
        echo '</tr>';
        echo '</thead>';
        
        echo '<tbody>';
        if (count($this->data)) {
            foreach ($this->data as $obj) {
                echo '<tr id="admin_ui_list_'.$this->index.'_row_' . $obj->id . '" class="ui-row">';
                
                if ($checkbox) echo '<td style="text-align:center;" width="20"><input type="checkbox" class="id" value="' . $obj->id . '" /></td>';
                foreach ($this->fields as $field) {
                    echo '<td style="text-align:' . (isset($field['align']) ? $field['align'] : 'center').';'.(isset($field['style']) ? $field['style'] : '').'" ' . (isset($field['width']) ? ('width=' . intval($field['width'])) : '') . '>';
                    
                    if (isset($field['template'])) {
                        $str = $field['template'];
                        $start = strpos($str, '{');
                        while ($start ! == false)
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
                
                if ($this->actions['block'] != false || $this->actions['unblock'] != false) {
                    echo '<td style="text-align:center;" width="20">';
                    if ($obj->block == 0) {
                        if ($this->actions['block'] != false) echo '<a href="javascript:;" onclick="javascript:oAdminUIList_'.$this->index.'.block(\'' . $obj->id . '\');" class="icon unblock" title="' . $this->actions['block']['label'] . '" data-toggle="tooltip"></a>';
                    } else {
                        if ($this->actions['unblock'] != false) echo '<a href="javascript:;" onclick="javascript:oAdminUIList_'.$this->index.'.unblock(\'' . $obj->id . '\');" class="icon block" title="' . $this->actions['unblock']['label'] . '" data-toggle="tooltip"></a>';
                    }
                    echo '</td>';
                }
                
                if ($this->actions['edit'] != false) echo '<td style="text-align:center;" width="20"><a href="javascript:;" onclick="javascript:oAdminUIList_'.$this->index.'.edit(\'' . $obj->id . '\');" class="icon edit" title="' . $this->actions['edit']['label'] . '" data-toggle="tooltip"></a></td>';
                if ($this->actions['delete'] != false) echo '<td style="text-align:center;" width="20"><a href="javascript:;" onclick="javascript:oAdminUIList_'.$this->index.'.remove(\'' . $obj->id . '\');" class="icon delete" title="' . $this->actions['delete']['label'] . '" data-toggle="tooltip"></a></td>';
                echo '</tr>';
            }
        } else {
            echo '<tr>';
            echo '<td colspan="' . $n . '" style="text-align:center;">'.'没有记录'.'</td>';
            echo '<tr>';
        }
        echo '</tbody>';
        
        $total = $pages = $limit = $page = 0;
        if ($this->pagination ! == null) {
            $total = $this->pagination->get_total();
            $pages = $this->pagination->get_pages();
            $limit = $this->pagination->get_limit();
            $page = $this->pagination->get_page();
        }
        
        echo '<tfoot>';
        echo '<tr>';
        echo '<td colspan="' . $n . '">';
        if ($this->footer === null) {
            if ($this->pagination ! == null) {
                echo '总计 <strong>'.$total.'</strong> 条记录';
                if ($pages > 1) echo '(<strong>'.$pages.'</strong> 页), 每页显示 <strong>'.$limit.'</strong> 条记录';
            } else {
                echo '&nbsp;';
            }
        } else {
            echo $this->footer;
        }
        echo '</td>';
        echo '</tr>';
        echo '</tfoot>';
        
        echo '</table>';
        echo '</div>';
        
        if ($this->pagination ! == null && $pages > 1) {
            $window = $this->pagination_window;
			$half_window = intval($window / 2);
			if ($pages - $page < $half_window) {
				$start_page = $pages - $window + 1;
			} else {
				$start_page = $page - $half_window;
			}            
            if ($start_page < 1) $start_page = 1;
            $end_page = $start_page + $window - 1;
            if ($end_page > $pages) $end_page = $pages;
            
            echo '<div class="pagination">';
            echo '<ul>';
            if ($page == 1) {
                echo '<li class="disabled"><a href="#">&larr; '.'上一页'.'</a></li>';
            } else {
                echo '<li><a href="javascript:" onclick="javascript:oAdminUIList_'.$this->index.'.gotoPage(' . ($page - 1) . ');">&larr; '.'上一页'.'</a></li>';
            }
            for ($i = $start_page; $i <= $end_page; $i++)
            {
                if ($i == $page) {
                    echo '<li class="active"><a href="#">' . $i . '</a></li>';
                } else {
                    echo '<li><a href="javascript:" onclick="javascript:oAdminUIList_'.$this->index.'.gotoPage(' . $i . ');">' . $i . '</a></li>';
                }
            }
            if ($page < $pages) {
                echo '<li><a href="javascript:" onclick="javascript:oAdminUIList_'.$this->index.'.gotoPage(' . ($page + 1) . ');">'.'下一页'.'  &rarr;</a></li>';
            } else {
                echo '<li class="disabled"><a href="#">'.'下一页'.'  &rarr;</a></li>';
            }
            echo '</ul>';
            echo '</div>';
        }
        
        echo '<input type="hidden" id="admin_ui_list_'.$this->index.'_id" name="id" value="">';
        echo '<input type="hidden" id="admin_ui_list_'.$this->index.'_page" name="page" value="' . $page . '">';
        if ($this->order_by ! == null) {
            echo '<input type="hidden" id="admin_ui_list_'.$this->index.'_order_by" name="order_by" value="' . $this->order_by . '">';
            echo '<input type="hidden" id="admin_ui_list_'.$this->index.'_order_by_dir" name="order_by_dir" value="' . $this->order_by_dir . '">';
        }
        
        echo '</form>';
        echo '</div>';
		
        echo '<script type="text/javascript" language="javascript">';
		echo 'oAdminUIList_'.$this->index.'.init('.$this->index.');';
        echo '</script>';
    }

}
?>