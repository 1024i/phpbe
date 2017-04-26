<?php
namespace admin\template\user;

class logs extends \admin\theme
{

	protected function head()
	{
		parent::head();
	
		$ui_list = be::get_admin_ui('list');
		$ui_list->head();
?>
<script type="text/javascript" language="javascript" src="template/user/js/logs.js"></script>
<?php
	}

	protected function center()
	{

	    $logs = $this->get('logs');
	    
		$ui_list = be::get_admin_ui('list');

		$ui_list->set_action('listing', './?controller=user&task=logs');
		
		$ui_list->set_filters(
            array(
            	'type'=>'text', 
            	'name'=>'key', 
            	'label'=>'按用户名搜索', 
            	'value'=>$this->get('key'), 
            	'width'=>'100px'
           ),
            array(
            	'type'=>'select', 
                'name'=>'success', 
            	'label'=>'登陆状态', 
            	'options'=>array(
                    '-1'=>'所有',
                    '1'=>'登陆成功',
            		'0'=>'登陆失败'                    
               ),
                'value'=>$this->get('success')
           ),
            array(
            	'type'=>'button', 
                'value'=>'删除三个月前的日志',
				'click'=>'javascript:deleteLogs(this);',
				'class'=>'btn btn-danger'
           )
		);

		$lib_ip = be::get_lib('ip');
		
		$date = '';
		foreach ($logs as $log) {
			$new_date = date('Y-m-d',$log->create_time);
		    if ($date == $new_date) {
		        $log->create_time = '<span style="visibility:hidden;">'. $new_date .' &nbsp;</span>'. date('H:i:s',$log->create_time);
		    } else {
		        $log->create_time = $new_date .' &nbsp;'. date('H:i:s',$log->create_time);
		        $date = $new_date;
		    }
			$log->address = $lib_ip->convert($log->ip);
		}
		
		$ui_list->set_data($logs);
		
		$ui_list->set_fields(
            array(
    			'name'=>'create_time',
    			'label'=>'时间',
    			'align'=>'center',
    		    'width'=>'150'
    		),
    		array(
    			'name'=>'username',
    			'label'=>'时间',
    			'align'=>'center',
    		    'width'=>'120'
    		),
    		array(
    			'name'=>'success',
    			'label'=>'登陆成功?',
    			'align'=>'center',
    		    'width'=>'150',
    		    'template'=>'<a class="icon checked-{success}"></a>'
    		),
    		array(
    			'name'=>'description',
    			'label'=>'描述',
    			'align'=>'left'
    		),
    		array(
    			'name'=>'ip',
    			'label'=>'IP',
    			'align'=>'center',
    		    'width'=>'120'
    		),
    		array(
    			'name'=>'address',
    			'label'=>'地理位置',
    			'align'=>'left',
    		    'width'=>'200'
    		)
		);

		$ui_list->set_pagination($this->get('pagination'));
		$ui_list->display();

	}	

}
?>