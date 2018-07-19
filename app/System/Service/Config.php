<?php
namespace App\System\Service;

use Phpbe\System\Service;

class Config extends Service
{

    /**
     * 获取配置文件树状结构
     */
    public function getConfigTree()
    {

        return [
            [
                'app' => 'Cms',
                'name' => '内容管理系统',
                'configs' => [
                    'Article' => '文章'
                ]
            ],
            [
                'app' => 'System',
                'name' => '系统',
                'configs' => [
                    'User' => '用户'
                ]
            ]
        ];
    }

    /**
     * 获取配置文件文档注释
     *
     * @param string $app 应用名
     * @param string $config 配置文件名
     * @return array
     */
    public function getConfig($app, $config)
    {
        $result = [];

        $className = 'App' . $app . 'Config' . $config;
        $result['instance'] = new $className();

        $reflection = new \ReflectionClass($className);

        // 类注释
        $docComment = $reflection->getDocComment();
        $result['class'] = $this->parseDocComment($docComment);

        // 属性注释
        $result['properties'] = [];
        $properties = $reflection->getProperties(\ReflectionMethod::IS_PUBLIC);
        foreach ($properties as &$property) {
            $docComment = $property->getDocComment();
            $result['properties'][$property->getName()] = $this->parseDocComment($docComment);
        }

        // 方法注释
        $result['methods'] = [];
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as &$method) {
            $docComment = $method->getDocComment();
            $result['methods'][$method->getName()] = $this->parseDocComment($docComment);
        }

        return $result;
    }


    /**
     * 解析文档注释
     *
     * @param string $docComment 文档注释
     * @return array
     */
    public function parseDocComment($docComment)
    {
        $result = [];
        if (preg_match('#^/\*\*(.*)\*/#s', $docComment, $comment) === false) return [];
        $comment = trim($comment[1]);

        if (preg_match_all('#^\s*\*(.*)#m', $comment, $lines) === false) return [];
        $lines = $lines[1];

        $description = [];
        foreach ($lines as $line) {

            $line = trim($line);

            if ($line) {
                // 该行注释由 @ 开头
                if (strpos($line, '@') === 0) {
                    if (strpos($line, ' ') > 0) {
                        $param = substr($line, 1, strpos($line, ' ') - 1);
                        $value = substr($line, strlen($param) + 2);
                    } else {
                        $param = substr($line, 1);
                        $value = '';
                    }

                    if ($param == 'param' || $param == 'return') {
                        $pos = strpos($value, ' ');
                        $type = substr($value, 0, $pos);
                        $value = '(' . $type . ')' . substr($value, $pos + 1);
                    } elseif ($param == 'class') {
                        $r = preg_split("[|]", $value);
                        if (is_array($r)) {
                            $param = $r[0];
                            parse_str($r[1], $value);
                            foreach ($value as $key => $val) {
                                $val = explode(',', $val);
                                if (count($val) > 1)
                                    $value[$key] = $val;
                            }
                        } else {
                            $param = 'Unknown';
                        }
                    }

                    if (empty ($result[$param])) {
                        $result[$param] = $value;
                    } else if ($param == 'param') {
                        $arr = array(
                            $result[$param],
                            $value
                        );
                        $result[$param] = $arr;
                    } else {
                        $result[$param] = $value + $result[$param];
                    }

                    if (!isset($result['summary']) && count($description) > 0) {
                        $result['summary'] = implode(PHP_EOL, $description);
                        $description = [];
                    }
                } else {
                    $description[] = $line;
                }
            } else {
                if (!isset($result['summary']) && count($description) > 0) {
                    $result['summary'] = implode(PHP_EOL, $description);
                    $description = [];
                }
            }
        }

        if (count($description) > 0) {
            $description = implode(' ', $description);
            $result['description'] = $description;
        }

        return $result;
    }

    /*
     * 保存配置文件到指定咱径
     *
     * @param string $app 应用名称
     * @param Object $config 配置对象
     *
     * @return bool 是否保存成功
     */
    public function updateConfig($app, $config)
    {
        $comments = array();
        if (file_exists($path)) {
            $f = fopen($path, 'r');
            while (!feof($f)) {
                $line = fgets($f, 4096);
                $line = trim($line);
                if (strlen($line) > 8 && strtolower(substr($line, 0, 8)) == 'public $') {
                    $keyStartPos = strpos($line, '$');
                    $keyEndPos = strpos($line, '=');
                    if ($keyStartPos !== false && $keyEndPos !== false) {
                        $key = substr($line, $keyStartPos + 1, $keyEndPos - 1 - $keyStartPos);
                        $key = trim($key);

                        $commentPos = strrpos($line, '//');
                        if ($commentPos !== false) {
                            $comment = substr($line, $commentPos + 2);
                            $comment = trim($comment);

                            if (substr($comment, -1, 1) != ';') $comments[$key] = $comment;
                        }
                    }
                }
            }
            fclose($f);
        }

        $vars = get_object_vars($config);

        $class = get_class($config);

        $namespace = substr($class, 0, strrpos($class, '\\'));
        $className = substr($class, strrpos($class, '\\') + 1);

        $buf = "<?php\n";
        $buf .= 'namespace ' . $namespace . ';' . "\n\n";
        $buf .= 'class ' . $className . "\n";
        $buf .= "{\r\n";

        foreach ($vars as $key => $val) {
            if (is_array($val)) {
                $indexed = true;

                $i = 0;
                foreach ($val as $index => $x) {
                    if ($i !== $index) {
                        $indexed = false;
                        break;
                    }
                    $i++;
                }

                $arr = array();
                foreach ($val as $index => $x) {
                    $x = str_replace('\'', '&#039;', $x);

                    // 数组含有非数字的索引
                    if ($indexed) {
                        $arr[] = '\'' . $x . '\'';
                    } else {
                        $arr[] = '\'' . $index . '\'=>\'' . $x . '\'';
                    }
                }
                $buf .= '  public $' . $key . ' = [' . implode(', ', $arr) . '];';
            } elseif (is_bool($val)) {
                $buf .= '  public $' . $key . ' = ' . ($val ? 'true' : 'false') . ';';
            } elseif (is_int($val) || is_float($val)) {
                $buf .= '  public $' . $key . ' = ' . $val . ';';
            } else {
                $val = str_replace('\'', '&#039;', $val);
                $buf .= '  public $' . $key . ' = \'' . $val . '\';';
            }

            if (array_key_exists($key, $comments)) $buf .= '  // ' . $comments[$key];

            $buf .= "\n";
        }
        $buf .= "}\n";

        return file_put_contents($path, $buf, LOCK_EX);
    }

}






