<?php
namespace ui\pagination;

class pagination extends \system\ui
{

    private $total = 0; // 总记录数
    private $pages = 0; // 总页数
    private $limit = 20; // 每页记录数
    private $page = 1; // 页码
    private $url = './'; // 链接

	public function head()
	{
	}
	
    // 设置当前第几页
    public function set_page($page)
    {
        $page = intval($page);
        if ($page < 0) $page = 1;
        $this->page = $page;
		
		$this->update();
    }

    // 设置总记录数
    public function set_total($total)
    {
        $total = intval($total);
        if ($total < 0) $total = 0;
        $this->total = $total;
		
		$this->update();
    }

    // 设置链接
    public function set_url($url)
    {
        $this->url = $url;
    }

    // 设置第页显示多少条记录
    public function set_limit($limit)
    {
        $limit = intval($limit);
        if ($limit < 0) $limit = 10;
        $this->limit = $limit;
		
		$this->update();
    }


    // 更新
    public function update()
    {
		if ($this->total<=0) return;
		
        $this->pages = ceil($this->total / $this->limit);
        if ($this->page > $this->pages) $this->page = $this->pages;
        if ($this->page < 1) $this->page = 1;
    }
	
    public function get_total()
    {
        return $this->total;
    }

    public function get_pages()
    {
        return $this->pages;
    }

    public function get_limit()
    {
        return $this->limit;
    }

    public function get_page()
    {
        return $this->page;
    }

    public function get_url()
    {
        return $this->url;
    }

    public function get_offset()
    {
        return ($this->page - 1) * $this->limit;
    }


    public function display()
    {
        if ($this->pages <= 1) return;
        $window = 15;
        $start_page = $this->page - intval($window / 2);
        if ($start_page < 1) $start_page = 1;
        $end_page = $start_page + $window - 1;
        if ($end_page > $this->pages) $end_page = $this->pages;
		
        echo '<div class="pagination">';
        echo '<ul>';
        if ($this->page == 1) {
            echo '<li class="prev disabled"><a href="javascript:;">&larr; 上一页</a></li>';
        } else {
            echo '<li class="prev"><a href="' . url($this->url.'&page='.($this->page - 1)) . '">&larr; 上一页</a></li>';
        }
        for ($i = $start_page; $i <= $end_page; $i++)
        {
            if ($i == $this->page) {
                echo '<li class="active"><a href="javascript:;">' . $i . '</a></li>';
            } else {
                echo '<li><a href="' . url($this->url.'&page='.$i) . '">' . $i . '</a></li>';
            }
        }
        if ($this->page < $this->pages) {
            echo '<li class="next"><a href="' . url($this->url.'&page='.($this->page+1)) . '">下一页  &rarr;</a></li>';
        } else {
            echo '<li class="next disabled"><a href="javascript:;">下一页  &rarr;</a></li>';
        }
        echo '</ul>';
        echo '</div>';
    }



}
?>