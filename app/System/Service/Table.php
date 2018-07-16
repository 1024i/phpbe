<?php

namespace App\System\Service;

use Phpbe\System\Service\ServiceException;
use Phpbe\System\Be;
use Phpbe\System\Db;
use Phpbe\System\db\DbException;
use Phpbe\Util\String;

class Table extends \Phpbe\System\Service
{


    public function getTables()
    {

    }


    public function getTable($table)
    {

    }

    public function updateTableConfig($table)
    {

    }


    /**
     * 更新表
     *
     * @param string $name 要表新的表名
     * @throws |Exception
     */
    public function updateTable($app, $name)
    {
        $tableName = String::snakeCase($app) . '_' . String::snakeCase($name);
        $db = Be::getDb();
        if (!$db->getValue('SHOW TABLES LIKE \'' . $tableName . '\'')) {
            throw new ServiceException('未找到名称为 ' . $tableName . ' 的数据库表！');
        }

        $fields = $db->getObjects('SHOW FULL FIELDS FROM ' . $tableName);
        $primaryKey = 'id';
        foreach ($fields as $field) {
            if ($field->Key == 'PRI') {
                $primaryKey = $field->Field;
            }
        }

        $formattedFields = $this->formatTableFields($app, $name, $fields);

        $code = '<?php' . "\n";
        $code .= 'namespace Cache\\Table\\' . $app . ';' . "\n";
        $code .= "\n";
        $code .= 'class ' . $name . ' extends \\Phpbe\\System\\Db\\Table' . "\n";
        $code .= '{' . "\n";
        $code .= '    protected $tableName = \'' . $tableName . '\'; // 表名' . "\n";
        $code .= '    protected $primaryKey = \'' . $primaryKey . '\'; // 主键' . "\n";
        $code .= '    protected $fields = [\'' . var_export($formattedFields, true) . '\']; // 字段列表' . "\n";
        $code .= '}' . "\n";
        $code .= "\n";

        $path = Be::getRuntime()->getPathCache() . '/Table/' . $app . '/' . $name . '.php';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        file_put_contents($path, $code, LOCK_EX);
        chmod($path, 0755);
    }


    public function formatTableFields($app, $name, $fields) {

        $tableConfig = Be::getTableConfig($app, $name);

        $formattedFields = array();

        foreach ($fields as $field) {

            $type = $field->Type;
            $typeLength = 0;
            $unsigned = strpos($field->Type, 'unsigned') !== false;

            $pos = strpos($field->Type, '(');
            if ($pos !== false) {
                $type = substr($field->Type, 0,  $pos);
                $typeLength = substr($field->Type, $pos + 1, strpos($field->Type, ')') - $pos - 1 );
            }

            //if (!is_numeric($typeLength)) $typeLength = -1;

            $numberTypes = array('int', 'mediumint', 'tinyint', 'smallint', 'bigint', 'decimal', 'float', 'double', 'real', 'bit', 'boolean', 'serial');
            $isNumber = in_array($type, $numberTypes);

            $default = null;
            if ($isNumber) {
                $default = $field->Default ? $field->Default : 0;
            } else {
                $default = $field->Default ? addslashes($field->Default) : '';
            }

            $extra = $field->Extra;

            $comment = addslashes($field->Comment);

            $optionType = 'null';
            $optionData = '';

            if ($type == 'enum') {
                $optionType = 'array';
                $optionData = str_replace(',', "\n", $typeLength);
            }

            $name = $field->Field;
            $disable = false;
            $show = true;
            $editable = true;
            $create = true;
            $format = '';


            $configField = $tableConfig->getField($field->Field);
            if ($configField) {
                $name = $configField['name'];

                if (isset($configField['optionType']) &&
                    in_array($configField['optionType'], array('null', 'array', 'sql')) &&
                    isset($configField['optionData'])
                ) {

                    $optionType = $configField['optionType'];
                    $optionData = $configField['optionData'];
                }

                if (isset($configField['disable'])) {
                    $disable = $configField['disable'] ? true : false;
                }

                if (isset($configField['show'])) {
                    $show = $configField['show'] ? true : false;
                }

                if (isset($configField['editable'])) {
                    $editable = $configField['editable'] ? true : false;
                }

                if (isset($configField['format']) && $configField['format']) {
                    $format = $configField['format'];
                }

                if (isset($configField['create'])) {
                    $create = $configField['create'] ? true : false;
                }
            }

            $formattedFields[$field->Field] = array(
                'name' => $name, // 字段名
                'field' => $field->Field, // 字段名
                'type' => $type, // 类型
                'typeLength' => $typeLength, // 类型长度
                'optionType' => $optionType, // 枚举类型取值范围
                'optionData' => $optionData, // 枚举类型取值范围
                'isNumber' => $isNumber,  // 是否数字
                'unsigned' => $unsigned, // 是否非负，数字类型时有效
                'default' => $default, // 默认值
                'extra' => $extra, // 附加内容
                'comment' => $comment, // 注释
                'disable' => $disable, // 是否禁用
                'show' => $show, // 是否默认展示
                'editable' => $editable, // 是否可编辑
                'create' => $create, // 是否可新建
                'format' => $format, // 格式化
            );
        }

        return $formattedFields;
    }

}
