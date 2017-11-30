<?php
namespace lib\history;

use system\session;
use system\request;

/*
@版本日期: 2013年12月20日
@更新日期: 2016年9月14日
@著作权所有: PHPBE (http://www.phpbe.com)

获得使用本类库的许可, 您必须保留著作权声明信息.
报告漏洞，意见或建议, 请联系 Lou Barnes(i@liu12.com) http://www.liu12.com
*/

class history extends \system\lib
{

    // 构造函数

    public function __construct()
    {
    }

    // 析构函数

    public function __destruct()
    {
    }

    // 保存当前页面
    public function save()
    {
        session::set('_history_url', $_SERVER['REQUEST_URI']);
        session::set('_history_data_post', serialize(request::post()));
    }

    // 返回保存的页面
    public function back()
    {
        $url = session::get('_history_url', $_SERVER['HTTP_REFERER']);
        if ($url == '') $url = './';

        $data_post = session::get('_history_data_post');
        $data_post = unserialize($data_post);
        ?>
        <!DOCTYPE html>
        <html lang="zh">
        <head>
            <meta charset="utf-8"/>
        </head>
        <body>
        <form action="<?php echo $url; ?>" id="from_history" method="post">
            <?php
            if (is_array($data_post) && count($data_post) > 0) {
                foreach ($data_post as $key => $val) {
                    echo '<input type="hidden" name="' . $key . '" value="' . $val . '"/>';
                }
            }
            ?>
        </form>
        <script>document.getElementById("from_history").submit();</script>
        </body>
        </html>
    <?php
    }

}

?>