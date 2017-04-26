<?php
namespace lib\fso;

/*
@版本日期: 2010年08月28日
@更新日期: 2016年9月14日
@著作权所有: Lou Barnes (http://www.liu12.com)

获得使用本类库的许可, 您必须保留著作权声明信息.
报告漏洞，意见或建议, 请联系 Lou Barnes(i@liu12.com) 或登陆 http://www.liu12.com
*/

class fso extends \system\lib
{

    // 构造函数
    public function __construct()
    {
    }


    // 析构函数
    public function __destruct()
    {
    }


    // 删除文件夹, 同时删除文件夹下的所有文件
    public function rm_dir($path)
    {
        if (!file_exists($path)) {
            $this->set_error($path . ' 不存在');
            return false;
        }

        if (is_dir($path)) {
            $handle = opendir($path);
            while (($file = readdir($handle)) ! == false) {
                if ($file != '.' && $file != '..') {
                    $this->rm_dir($path . DS . $file);
                }
            }
            closedir($handle);

            rmdir($path);
        } else {
            unlink($path);
        }

        return true;
    }


    // 建立文件夹， 支持多级文件夹 如 aaa\bbb\ccc\...
    public function mk_dir($path, $mode = 0777)
    {
        $dirs = explode(DS, $path);

        $dir = '';
        for ($i = 0, $n = count($dirs); $i < $n; $i++) {
            $dir .= $dirs[$i] . DS;
            if (!file_exists($dir)) mkdir($dir, $mode);
        }
    }


    // 复制文件夹
    public function copy_dir($src, $dst, $overwrite = false)
    {
        if (!is_dir($dst)) $this->mk_dir($dst);

        $handle = opendir($src);
        if ($handle) {
            while (false ! == ($file = readdir($handle))) {
                if ($file != '.' && $file != '..') {
                    $path = $src . DS . $file;
                    if (is_file($path)) {
                        if (!is_file($dst . DS . $file) || $overwrite)
                            @copy($path, $dst . DS . $file);
                    } else {
                        if (!is_dir($dst . DS . $file)) mkdir($dst . DS . $file);
                        $this->copy_dir($path, $dst . DS . $file, $overwrite);
                    }
                }
            }
        }
        closedir($handle);
    }


}
