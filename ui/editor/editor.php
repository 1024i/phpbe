<?php
namespace ui\editor;

class editor extends \system\ui
{
    
    private $action = './';
    private $method = 'post';
    private $fields = null;
    private $hidden = array();
    
    private $submit = '保存';
    private $reset = null;
    private $back = null;
    private $back_url = null;

	public function head()
	{
	}

    public function set_action($action = './')
    {
        $this->action = $action;
    }

    public function set_method($method = 'post')
    {
        $this->method = $method;
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
    public function set_fields()
    {
        $this->fields = func_get_args();
    }

    public function set_submit($submit)
    {
        $this->submit = $submit;
    }

    public function set_reset($reset)
    {
        $this->reset = $reset;
    }

    public function set_back($back, $url = null)
    {
        $this->back = $back;
        $this->back_url = $url;
    }

    public function add_hidden($name, $value = null)
    {
        $this->hidden[$name] = $value;
    }

    public function display()
    {
        
    }

}

?>