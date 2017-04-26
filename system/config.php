<?php
namespace system;

/**
 * 配置文件
 */
class config
{

    /*
     * 保存配置文件到指定咱径
     *
     * @param object $config 配置文件类
     * @param string $path 保存路径
     *
     * @return bool 是否保存成功
     */
    public static function save($config, $path)
    {
        $comments = array();
        if (file_exists($path)) {
            $f = fopen($path, 'r');
            while (!feof($f))
            {
                $line = fgets($f, 4096);
                $line = trim($line);
                if (strlen($line) > 8 && strtolower(substr($line, 0, 8)) == 'public $') {
                    $key_start_pos = strpos($line, '$');
                    $key_end_pos = strpos($line, '=');
                    if ($key_start_pos !== false && $key_end_pos !== false) {
                        $key = substr($line, $key_start_pos + 1, $key_end_pos - 1 - $key_start_pos);
                        $key = trim($key);

                        $comment_pos = strrpos($line, '//');
                        if ($comment_pos !== false) {
                            $comment = substr($line, $comment_pos + 2);
                            $comment = trim($comment);

                            if (substr($comment, -1, 1) != ';') $comments[$key] = $comment;
                        }
                    }
                }
            }
            fclose($f);
        }

        $vars = get_object_vars($config);

        $buf = "<?php\r\n";
        $buf .= 'class ' . get_class($config) . "\r\n";
        $buf .= "{\r\n";

        foreach ($vars as $key=>$val) {
            if (is_array($val)) {
                $indexed = true;

                $i = 0;
                foreach ($val as $index=>$x) {
                    if ($i !== $index) {
                        $indexed = false;
                        break;
                    }
                    $i++;
                }

                $arr = array();
                foreach ($val as $index=>$x) {
                    $x = str_replace('\'', '&#039;', $x);

                    // 数组含有非数字的索引
                    if ($indexed) {
                        $arr[] = '\'' . $x . '\'';
                    } else {
                        $arr[] = '\'' . $index . '\'=>\'' . $x . '\'';
                    }
                }
                $buf .= '  public $' . $key . ' = array(' . implode(', ', $arr) . ');';
            } else {
                $val = str_replace('\'', '&#039;', $val);
                $buf .= '  public $' . $key . ' = \'' . $val . '\';';
            }

            if (array_key_exists($key, $comments)) $buf .= '  // ' . $comments[$key];

            $buf .= "\r\n";
        }
        $buf .= "}\r\n";
        $buf .= '?>';

        return file_put_contents($path, $buf);
    }
}