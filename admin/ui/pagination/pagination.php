<?php

namespace admin\ui\pagination;

class pagination extends \system\ui
{

    private $total = 0; // 总记录数
    private $pages = 0; // 总页数
    private $limit = 20; // 每页记录数
    private $page = 1; // 页码
    private $url = './'; // 链接

    private $head = false;

    public function head()
    {
        if (!$this->head) {
            $this->head = true;
            echo '<link type="text/css" rel="stylesheet" href="ui/pagination/css/pagination.css" />';
        }
    }

    // 设置当前第几页
    public function setPage($page)
    {
        $page = intval($page);
        if ($page < 0) $page = 1;
        $this->page = $page;

        $this->update();
    }

    // 设置总记录数
    public function setTotal($total)
    {
        $total = intval($total);
        if ($total < 0) $total = 0;
        $this->total = $total;

        $this->update();
    }

    // 设置链接
    public function setUrl($url)
    {
        if (strpos($url, '?') === false)
            $url .= '?';
        else
            $url .= '&';
        $this->url = $url;
    }

    // 设置第页显示多少条记录
    public function setLimit($limit)
    {
        $limit = intval($limit);
        if ($limit < 0) $limit = 10;
        $this->limit = $limit;

        $this->update();
    }


    // 更新
    public function update()
    {
        if ($this->total <= 0) return;

        $this->pages = ceil($this->total / $this->limit);
        if ($this->page > $this->pages) $this->page = $this->pages;
        if ($this->page < 1) $this->page = 1;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function getPages()
    {
        return $this->pages;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getOffset()
    {
        return ($this->page - 1) * $this->limit;
    }


    public function display()
    {
        if ($this->pages <= 1) return;
        $window = 15;
        $startPage = $this->page - intval($window / 2);
        if ($startPage < 1) $startPage = 1;
        $endPage = $startPage + $window - 1;
        if ($endPage > $this->pages) $endPage = $this->pages;

        $this->head();
        echo '<div class="adminUiPagination">';
        echo '<ul>';
        if ($this->page == 1) {
            echo '<li class="prev disabled"><a href="#">&larr; ' . '上一页' . '</a></li>';
        } else {
            echo '<li class="prev"><a href="' . $this->url . 'page=' . ($this->page - 1) . '">&larr; ' . '上一页' . '</a></li>';
        }
        for ($i = $startPage; $i <= $endPage; $i++) {
            if ($i == $this->page) {
                echo '<li class="active"><a href="#">' . $i . '</a></li>';
            } else {
                echo '<li><a href="' . $this->url . 'page=' . $i . '">' . $i . '</a></li>';
            }
        }
        if ($this->page < $this->pages) {
            echo '<li class="next"><a href="' . $this->url . 'page=' . ($this->page + 1) . '">' . '下一页' . '  &rarr;</a></li>';
        } else {
            echo '<li class="next disabled"><a href="#">' . '下一页' . '  &rarr;</a></li>';
        }
        echo '</ul>';
        echo '</div>';
    }
}
