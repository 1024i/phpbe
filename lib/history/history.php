<?php
namespace lib\history;

use System\Session;
use System\Request;

/*
@版本日期: 2013年12月20日
@更新日期: 2016年9月14日
@著作权所有: PHPBE (http://www.phpbe.com)

获得使用本类库的许可, 您必须保留著作权声明信息.
报告漏洞，意见或建议, 请联系 Lou Barnes(i@liu12.com) http://www.liu12.com
*/

class History extends \system\Lib
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
        session::set('_historyUrl', $_SERVER['REQUEST_URI']);
        session::set('_historyDataPost', serialize(Request::post()));
    }

    // 返回保存的页面
    public function back()
    {
        $url = session::get('_historyUrl', $_SERVER['HTTP_REFERER']);
        if ($url == '') $url = './';

        $dataPost = session::get('_historyDataPost');
        $dataPost = unserialize($dataPost);
        ?>
        <!DOCTYPE html>
        <html lang="zh">
        <head>
            <meta charset="utf-8"/>
        </head>
        <body>
        <form action="<?php echo $url; ?>" id="fromHistory" method="post">
            <?php
            if (is_array($dataPost) && count($dataPost) > 0) {
                foreach ($dataPost as $key => $val) {
                    echo '<input type="hidden" name="' . $key . '" value="' . $val . '"/>';
                }
            }
            ?>
        </form>
        <script>document.getElementById("fromHistory").submit();</script>
        </body>
        </html>
    <?php
    }

}

?>