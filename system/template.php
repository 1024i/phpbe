<?php
namespace system;

/**
 * 
 * 主题基类
 * 主题和模板里只放控制界面的代码，如数据格式，页面布局，不要有业务代码， 更不要操作数据库 
 *
 */
class template
{
    protected $title = ''; // 标题
    protected $meta_keywords = ''; // meta keywords
    protected $meta_description = '';  // meta description
    
    protected $data = array(); // 数据


	protected $theme = null;
	/*
	 * @param object $theme 主题
	 */
	public function set_theme($theme)
	{
		$this->theme = $theme;
	}


    /**
     * 
     * 输出函数
     */
    public function display()
    {
		if ($this->theme === null) return;

		$this->theme->set_template($this);
		$this->theme->display();
    }

   /**
     * 
     * 向模板中注入数据
     * @param string $name 名称
     * @param mixed $value 值
     */
    public function set($name, $value = null)
    {
        if (is_array($name)) {
            $this->data = array_merge($this->data, $name);
        } else {
            $this->data[$name] = $value;
        }
    }

    /**
     *
     * 取数据
     * @param string $name 名称
     */
    public function get($name, $default=null)
    {
        if (isset($this->data[$name]))
            return $this->data[$name];
        else
            return $default;
    }

    /**
     * 
     * 设置标题
     * @param string $title
     */
    public function set_title($title)
    {
        $this->title = $title;
    }

	/**
	 *
	 * 获取标题
	 */
	public function get_title()
	{
		return $this->title;
	}

    public function set_meta_keywords($meta_keywords)
    {
        $this->meta_keywords = $meta_keywords;
    }

	public function get_meta_keywords()
	{
		return $this->meta_keywords;
	}


    public function set_meta_description($meta_description)
    {
        $this->meta_description = $meta_description;
    }

	public function get_meta_description()
	{
		return $this->meta_description;
	}

}
