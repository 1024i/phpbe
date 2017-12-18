<?php
namespace admin\ui\editor;

use system\be;

class editor extends \system\ui
{
    
	private $actions = array();
	private $hidden = array();
	
    private $method = 'post';
    private $fields = null;
	
	private $left_width = null;

    public function __construct()
    {
        $this->actions['save'] = false;
		$this->actions['reset'] = false;
		$this->actions['back'] = false;
    }
	
    public function set_action($type, $url = null, $label = null)
    {
		if ($label == null) {
			switch($type)
			{
				case 'save': $label = '保存'; break;
				case 'reset': $label = '重设'; break;
				case 'back': $label = '返回'; break;
			}
		}
		$this->actions[$type] = array('url'=>$url, 'label'=>$label);
    }

    public function set_method($method)
    {
        $this->method = $method;
    }
	
	public function set_left_width($width)
	{
		$this->left_width = $width;
	}

    /*
	set_fields(

			array(
				'type'=>'text',
				'name'=>'username',
				'label'=>'用户名',
				'value'=>$user->username,
				'width'=>'200px',
				'required'=>'请输入用户名'
			)
			,
			array(
				'type'=>'text',
				'name'=>'name',
				'label'=>'呢称',
				'value'=>$user->name,
				'width'=>'200px',
				'validate'=>array(
					'required'=>true
				),
				'message'=>array(
					'required'=>'请输入呢称'
				)
			)
			,
			array(
				'type'=>'file',
				'name'=>'avatar',
				'label'=>'头像',
				'html'=>'<img src="'.$user->avatar.'"><input type="file" name="avatar" id="avatar">'
			)
			,
			array(
				'type'=>'radio',
				'name'=>'sex',
				'label'=>'性别',
				'value'=>$user->sex,
				'options'=>array{
					'0'=>'男',
					'1'=>'女'
				}
			)
			,
			array(
				'type'=>'checkbox',
				'name'=>'block',
				'label'=>'屏蔽该用户',
				'value'=>$user->block,
				'options'=>array{
					'1'=>''
				}
			)
			,
			array(
				'type'=>'select',
				'name'=>'timezone',
				'label'=>'时区',
				'value'=>$user->timezone,
				'options'=>array{
					'1'=>'GMT',
					'2'=>'GMT+01',
					'3'=>'GMT+02',
					'4'=>'GMT+03',
					'5'=>'GMT+04',
					'6'=>'GMT+05'
				}
			)
		) 
		
	验证方式: 
    (1)required:true 必输字段
    (2)remote:"check.php" 使用ajax方法调用check.php验证输入值
    (3)email:true 必须输入正确格式的电子邮件
    (4)url:true 必须输入正确格式的网址
    (5)date:true 必须输入正确格式的日期
    (6)date_iso:true 必须输入正确格式的日期(ISO)，例如：2009-06-23，1998/01/22 只验证格式，不验证有效性
    (7)number:true 必须输入合法的数字(负数，小数)
    (8)digits:true 必须输入整数
    (9)creditcard: 必须输入合法的信用卡号
    (10)equal_to:'field' 输入值必须和#field相同
    (11)accept:'jpg|jpeg|gif|png' 输入拥有合法后缀名的字符串（如上传文件的后缀）
    (12)max_length:5 输入长度最多是5的字符串(汉字算一个字符)
    (13)min_length:10 输入长度最小是10的字符串(汉字算一个字符)
    (14)range_length:[5,10] 输入长度必须介于 5 和 10 之间的字符串")(汉字算一个字符)
    (15)range:[5,10] 输入值必须介于 5 和 10 之间
    (16)max:5 输入值不能大于5
    (17)min:10 输入值不能小于10 
		
	*/
    public function add_field($array)
    {
        $this->fields[] = $array;
    }
    
    public function add_fields()
    {
        $args = func_get_args();
        $this->fields = array_merge($this->fields, $args);
    }
    
    public function set_fields()
    {
        $this->fields = func_get_args();
    }
    
    public function add_hidden($name, $value = null)
    {
        $this->hidden[$name] = $value;
    }

	
	private $head = false;
	public function head()
	{
		if (!$this->head) {
			$this->head = true;
			echo '<link type="text/css" rel="stylesheet" href="ui/editor/css/editor.css" />';
			
			if ($this->left_width !== null) {
			?>
<style type="text/css">
.admin_ui_editor .form-horizontal .control-label{ width:<?php echo $this->left_width; ?>px;}
.admin_ui_editor .form-horizontal .controls{ margin-left:<?php echo $this->left_width+20; ?>px;}
.admin_ui_editor .form-horizontal .control-group{ background-position:<?php echo $this->left_width-590; ?>px;}
</style>
			<?php
			}
		}
	}

    public function display()
    {
		if ($this->actions['save'] ===false) return;
		 
        $this->head();
		
        echo '<div class="admin_ui_editor">';
        echo '<form id="admin_ui_editor_form" class="form-horizontal" action="'.$this->actions['save']['url'].'" method="'.$this->method.'"';
        foreach ($this->fields as $field) {
            if (isset($field['type']) && $field['type'] == 'file') {
                echo ' enctype="multipart/form-data"';
                break;
            }
        }
        echo '>';
        
        foreach ($this->fields as $field) {
            echo '<div class="control-group">';
            echo '<label class="control-label"';
			if (isset($field['name'])) echo ' for="'.$field['name'].'"';
			echo '>';
			echo $field['label'];
            if (isset($field['validate']['required']) && $field['validate']['required']) echo '<span class="required">*</span>';
            echo '：</label>';
            echo '<div class="controls">';
            
            if (isset($field['html']))
            	echo $field['html'];
			else
                switch ($field['type'])
                {
                    case 'text':
                        echo '<input type="text" name="'.$field['name'].'" id="'.$field['name'].'"';
                        if (isset($field['value'])) echo ' value="'.$field['value'].'"';
                        if (isset($field['width'])) echo ' style="width:'.$field['width'].';"';
                        echo ' />';
                        break;
                    case 'password':
                        echo '<input type="password" name="'.$field['name'].'" id="'.$field['name'].'"';
                        if (isset($field['width'])) echo ' style="width:'.$field['width'].';"';
                        echo ' />';
                        break;
                    case 'radio':
                        if (isset($field['options']) && count($field['options'])) {
                            foreach ($field['options'] as $key => $val) {
								echo '<label class="radio inline">';
                                echo '<input type="radio" name="'.$field['name'].'" id="'.$field['name'].'-'.$key.'" value="'.$key.'"';
                                if (isset($field['value']) && $field['value'] == $key) echo ' checked="checked"';
                                echo ' />';
                                echo $val.'</label>';
                            }
                        }
                        break;
                    case 'checkbox':
                        if (isset($field['options']) && count($field['options'])) {

                            $name = $field['name'];
                            if (substr($name, -2, 2)!='[]' && count($field['options']) > 1) $name .= '[]';

                            foreach ($field['options'] as $key => $val) {
								echo '<label class="checkbox inline">';
                                echo '<input type="checkbox" name="'.$name.'" id="'.$field['name'].'-'.$key.'" value="'.$key.'"';
								if (isset($field['value'])) {
									if (is_array($field['value'])) {
										if (in_array($key, $field['value'])) echo ' checked="checked"';
									}
									elseif ($field['value'] == $key) {
										echo ' checked="checked"';
									}									
								}
                                echo ' />';
                                echo $val.'</label>';
                            }
                        }
                        break;
                    case 'select':
                        echo '<select name="'.$field['name'].'" id="'.$field['name'].'">';
                        if (isset($field['options']) && count($field['options'])) {
                            foreach ($field['options'] as $key => $val) {
                                echo '<option value="'.$key.'"';
                                if (isset($field['value']) && $field['value'] == $key) echo ' selected="selected"';
                                echo '>'.$val.'</option>';
                            }
                        }
                        echo '</select>';
                        break;
                    case 'file':
                        if (isset($field['value'])) echo $field['value'].'<br />';
                        echo '<input type="file" name="'.$field['name'].'" id="'.$field['name'].'" />';
                        break;
                    case 'textarea':
                        echo '<textarea name="'.$field['name'].'" id="'.$field['name'].'"';
                        echo ' style="width:'.(isset($field['width']) ? $field['width'] : '100%').';height:'.(isset($field['height']) ? $field['height'] : '200px').';"';
                        echo '>';
                        if (isset($field['value'])) echo $field['value'];
                        echo '</textarea>';
                        break;
                    case 'richtext':
                        $ui_tinymce = be::get_ui('tinymce');
                        $ui_tinymce->set_name($field['name']);
                        if (isset($field['width'])) $ui_tinymce->set_width($field['width']);
                        if (isset($field['height'])) $ui_tinymce->set_height($field['height']);
                        if (isset($field['value'])) $ui_tinymce->set_value($field['value']);
                        $ui_tinymce->display();
                        break;
                }
            echo '</div>';
            echo '</div>';
        }

        echo '<div class="control-group">';
		echo '<div class="controls">';
        echo '<input type="submit" class="btn btn-primary" value="'.$this->actions['save']['label'].'" /> &nbsp;';
        if ($this->actions['reset'] !== false) echo '<input type="reset" class="btn btn-danger" value="'.$this->actions['reset']['label'].'" /> &nbsp;';
        if ($this->actions['back'] !== false) {
            echo '<input type="button" class="btn" value="'.$this->actions['back']['label'].'" onclick="javascript:';
            echo  'window.location.href=\''.(($this->actions['back']['url'] === null)?'./?controller=system&task=history_back':$this->actions['back']['url']).'\'';
            echo ';" />';
        }
        echo '</div>';
		echo '</div>';

        foreach ($this->hidden as $key => $val) {
            echo '<input type="hidden" name="'.$key.'" id="'.$key.'"';
            if ($val !== null) echo ' value="'.$val.'"';
            echo ' />';
        }
        echo '</form>';
        echo '</div>';
        
        $validate_rules = array();
        $validate_messages = array();
        
        foreach ($this->fields as $field) {
            $rule = array();
            $message = array();
            if (isset($field['validate']) && count($field['validate'])) {
                if (isset($field['validate']['required']) && $field['validate']['required']) {
                    $rule[] = 'required:'.$field['validate']['required'];
                    if (isset($field['message']) && isset($field['message']['required']))
                        $message[] = 'required:"'.htmlspecialchars($field['message']['required']).'"';
                    else
                        $message[] = 'required:"'.'请输入'.$field['label'].'"';
                }
                
                if (isset($field['validate']['min_length'])) {
                    $rule[] = 'minlength:'.$field['validate']['min_length'];
                    
                    if (isset($field['message']) && isset($field['message']['min_length']))
                        $message[] = 'minlength:"'.htmlspecialchars($field['message']['min_length']).'"';
                    else
                        $message[] = 'minlength:"'.'至少需要输入{0}个字符'.'"';
                }
                
                if (isset($field['validate']['max_length'])) {
                    $rule[] = 'maxlength:'.$field['validate']['max_length'];
                    
                    if (isset($field['message']) && isset($field['message']['max_length']))
                        $message[] = 'maxlength:"'.htmlspecialchars($field['message']['max_length']).'"';
                    else
                        $message[] = 'maxlength:"'.'最多可以输入{0}个字符'.'"';
                }
                if (isset($field['validate']['range_length'])) {
                    $rule[] = 'rangelength:'.$field['validate']['range_length'];
                    
                    if (isset($field['message']) && isset($field['message']['range_length']))
                        $message[] = 'rangelength:"'.htmlspecialchars($field['message']['range_length']).'"';
                    else
                        $message[] = 'rangelength:"'.'请输入{0}-{1}个字符'.'"';
                }
                
                if (isset($field['validate']['min'])) {
                    $rule[] = 'min:'.$field['validate']['min'];
                    
                    if (isset($field['message']) && isset($field['message']['min']))
                        $message[] = 'min:"'.htmlspecialchars($field['message']['min']).'"';
                    else
                        $message[] = 'min:"'.'请输入大于{0}的数'.'"';
                }
                
                if (isset($field['validate']['max'])) {
                    $rule[] = 'max:'.$field['validate']['max'];
                    
                    if (isset($field['message']) && isset($field['message']['max']))
                        $message[] = 'max:"'.htmlspecialchars($field['message']['max']).'"';
                    else
                        $message[] = 'max:"'.'请输入小于{0}的数'.'"';
                }
                if (isset($field['validate']['range'])) {
                    $rule[] = 'range:'.$field['validate']['range'];
                    
                    if (isset($field['message']) && isset($field['message']['range']))
                        $message[] = 'range:"'.htmlspecialchars($field['message']['range']).'"';
                    else
                        $message[] = 'range:"'.'请输入{0}-{1}的数'.'"';
                }
                
                if (isset($field['validate']['number']) && $field['validate']['number']) {
                    $rule[] = 'number:true';
                    
                    if (isset($field['message']) && isset($field['message']['number']))
                        $message[] = 'number:"'.htmlspecialchars($field['message']['number']).'"';
                    else
                        $message[] = 'number:"'.'请输入一个数字'.'"';
                }
                
                if (isset($field['validate']['digits']) && $field['validate']['digits']) {
                    $rule[] = 'digits:true';
                    
                    if (isset($field['message']) && isset($field['message']['digits']))
                        $message[] = 'digits:"'.htmlspecialchars($field['message']['digits']).'"';
                    else
                        $message[] = 'digits:"'.'请输入一个整数'.'"';
                }
                
                if (isset($field['validate']['email']) && $field['validate']['email']) {
                    $rule[] = 'email:true';
                    
                    if (isset($field['message']) && isset($field['message']['email']))
                        $message[] = 'email:"'.htmlspecialchars($field['message']['email']).'"';
                    else
                        $message[] = 'email:"'.'邮箱格式错误'.'"';
                }
                
                if (isset($field['validate']['url']) && $field['validate']['url']) {
                    $rule[] = 'url:true';
                    
                    if (isset($field['message']) && isset($field['message']['url']))
                        $message[] = 'url:"'.htmlspecialchars($field['message']['url']).'"';
                    else
                        $message[] = 'url:"'.'请输入合法的网址'.'"';
                }
                
                if (isset($field['validate']['date']) && $field['validate']['date']) {
                    $rule[] = 'date:true';
                    
                    if (isset($field['message']) && isset($field['message']['date']))
                        $message[] = 'date:"'.htmlspecialchars($field['message']['date']).'"';
                    else
                        $message[] = 'date:"'.'请输入合法的日期'.'"';
                }
                
                if (isset($field['validate']['date_iso']) && $field['validate']['date_iso']) {
                    $rule[] = 'dateISO:true';
                    
                    if (isset($field['message']) && isset($field['message']['date_iso']))
                        $message[] = 'dateISO:"'.htmlspecialchars($field['message']['date_iso']).'"';
                    else
                        $message[] = 'dateISO:"'.'请输入合法的日期'.'"';
                }
                
                if (isset($field['validate']['creditcard']) && $field['validate']['creditcard']) {
                    $rule[] = 'creditcard:true';
                    
                    if (isset($field['message']) && isset($field['message']['creditcard']))
                        $message[] = 'creditcard:"'.htmlspecialchars($field['message']['creditcard']).'"';
                    else
                        $message[] = 'creditcard:"'.'请输入合法的信用卡号'.'"';
                }
                
                if (isset($field['validate']['equal_to'])) {
                    $rule[] = 'equalTo:"#'.$field['validate']['equal_to'].'"';
                    
                    if (isset($field['message']) && isset($field['message']['equal_to']))
                        $message[] = 'equalTo:"'.htmlspecialchars($field['message']['equal_to']).'"';
                    else
                        $message[] = 'equalTo:"'.$field['label'].' 不正确'.'"';
                }
                
                if (isset($field['validate']['remote'])) {
                    $rule[] = 'remote:"'.$field['validate']['remote'].'"';
                    
                    if (isset($field['message']) && isset($field['message']['remote']))
                        $message[] = 'remote:"'.htmlspecialchars($field['message']['remote']).'"';
                    else
                        $message[] = 'remote:"'.$field['label'].' 输入错误'.'"';
                }
                
                if (isset($field['validate']['accept'])) {
                    $rule[] = 'accept:"'.$field['validate']['accept'].'"';
                    
                    if (isset($field['message']) && isset($field['message']['accept']))
                        $message[] = 'accept:"'.htmlspecialchars($field['message']['accept']).'"';
                    else
                        $message[] = 'accept:"请选择 '.$field['validate']['accept'].' 后缀的文件"';
                }
            }
            
            if (count($rule)) $validate_rules[] = $field['name'].':{'.implode(',', $rule).'}';
            if (count($message)) $validate_messages[] = $field['name'].':{'.implode(',', $message).'}';

        }
        
        $js = '';
        $js .= '$(function(){';
        $js .= '$("#admin_ui_editor_form").validate({';
        $js .= 'rules: {';
        $js .= implode(',', $validate_rules);
        $js .= '}';
        $js .= ',messages:{ ';
        $js .= implode(',', $validate_messages);
        $js .= '}';
		$js .= ',highlight:function(element){ $(element).closest(".control-group").removeClass("success").addClass("error"); }';
		$js .= ',success:function(element){ $(element).addClass("success").closest(".control-group").removeClass("error").addClass("success"); }';
        $js .= '});';
        $js .= '});';
        
        echo '<script type="text/javascript" language="javascript">'.$js.'</script>';
    }

}

?>