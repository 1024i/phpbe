<?php
namespace lib;

require PATH_ROOT.DS.'libs'.DS.'scws'.DS.'pscws4'.DS.'pscws4.class.php';

class scws extends\ lib
{
    
    private $handler = null;

    // 构造函数
    public function __construct()
    {
		$this->handler = new PSCWS4();

		$this->handler->set_charset('utf8');
		$this->handler->set_dict(PATH_ROOT.DS.'libs'.DS.'scws'.DS.'pscws4'.DS.'etc'.DS.'dict.utf8.xdb');
		$this->handler->set_rule(PATH_ROOT.DS.'libs'.DS.'scws'.DS.'pscws4'.DS.'etc'.DS.'rules.utf8.ini');
    }

	
	public function set_charset($charset='utf8')
	{
		$this->handler->set_charset($charset);
	}

	// 设置词典
	public function set_dict($path)
	{
		$this->handler->set_dict($path);
	}

	// 设置规则集
	public function set_rule($path)
	{
		$this->handler->set_rule($path);
	}


	// 设置忽略符号与无用字符
	public function set_ignore($ignore)
	{
		$this->handler->set_ignore($ignore);
	}

	// 设置复合分词等级 ($level = 0,15)
	public function set_multi($level)
	{
		$this->handler->set_multi($level);
	}

	// 设置是否显示分词调试信息
	public function set_debug($bool)
	{
		$this->handler->set_debug($bool);
	}

	// 设置是否自动将散字二元化
	public function set_duality($bool)
	{
		$this->handler->set_duality($bool);
	}

	// 设置要分词的文本字符串
	public function send_text($text)
	{
		$this->handler->send_text($text);
	}

	// 取回一批分词结果(需要多次调用, 直到返回 false)
	public function get_result()
	{
		return $this->handler->get_result();
	}

	// 取回频率和权重综合最大的前 N 个词
	public function get_tops($limit = 10, $xattr = '')
	{
		return $this->handler->get_tops($limit, $xattr);
	}

    // 析构函数
    public function __destruct()
    {
        $this->handler->close();
    }

	public function close()
	{
		$this->handler->close();
	}

}
?>