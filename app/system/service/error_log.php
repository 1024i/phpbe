<?php
namespace app\system\service;


class errorLog extends \System\Service
{

    public function getYears()
    {
        $dir = PATH_DATA . DS . 'system' . DS . 'errorLog';
        $years = array();
        if (file_exists($dir) && is_dir($dir)) {
            $fileNames = scandir($dir);
            foreach ($fileNames as $fileName) {
                if ($fileName != '.' && $fileName != '..' && is_dir($dir . DS . $fileName)) {
                    $years[] = $fileName;
                }
            }
        }
        return $years;
    }

    public function getMonths($year)
    {
        $dir = PATH_DATA . DS . 'system' . DS . 'errorLog' . DS . $year;
        $months = array();
        if (file_exists($dir) && is_dir($dir)) {
            $fileNames = scandir($dir);
            foreach ($fileNames as $fileName) {
                if ($fileName != '.' && $fileName != '..' && is_dir($dir . DS . $fileName)) {
                    $months[] = $fileName;
                }
            }
        }
        return $months;
    }

    public function getDays($year, $month)
    {
        $dir = PATH_DATA . DS . 'system' . DS . 'errorLog' . DS . $year . DS . $month;
        $days = array();
        if (file_exists($dir) && is_dir($dir)) {
            $fileNames = scandir($dir);
            foreach ($fileNames as $fileName) {
                if (is_file($dir . DS . $fileName) && strrchr($fileName, '.') == '.data') {
                    $days[] = substr($fileName, 0, strpos($fileName, '.'));
                }
            }
        }
        return $days;
    }

    public function getErrorLogs($options = array())
    {
        if (!isset($options['year'])) return array();
        if (!isset($options['month'])) return array();
        if (!isset($options['day'])) return array();

        $dataPath = PATH_DATA . DS . 'system' . DS . 'errorLog' . DS . $options['year'] . DS . $options['month'] . DS . $options['day'] . '.data';
        if (!is_file($dataPath)) return array();

        $indexPath = PATH_DATA . DS . 'system' . DS . 'errorLog' . DS . $options['year'] . DS . $options['month'] . DS . $options['day'] . '.index';
        if (!is_file($indexPath)) return array();

        $offset = 0;
        $limit = 0;
        if (isset($options['offset']) && is_numeric($options['offset'])) $offset = intval($options['offset']);
        if (isset($options['limit']) && is_numeric($options['limit'])) $limit = intval($options['limit']);
        if ($offset < 0) $offset = 0;
        if ($limit <= 0) $limit = 100;

        $max = intval(filesize($indexPath) / 4) - 1;
        if ($max < 0) return array();

        $from = $offset;
        $to = $offset + $limit - 1;

        if ($from > $max) $from = $max;
        if ($to > $max) $to = $max;

        $fData = fopen($dataPath, 'rb');
        $fIndex = fopen($indexPath, 'rb');
        if (!$fData) return array();
        if (!$fIndex) return array();

        $errorLogs = array();
        for ($i = $from; $i <= $to; $i++) {
            fseek($fIndex, $i * 4);
            $dataOffsetFrom = intval(implode('', unpack('L', fread($fIndex, 4))));
            fseek($fData, $dataOffsetFrom);

            $dataOffsetTo = null;
            if ($i == $to) {
                $dataOffsetTo = intval(filesize($dataPath));
            } else {
                $dataOffsetTo = intval(implode('', unpack('L', fread($fIndex, 4))));
            }
            $data = fread($fData, $dataOffsetTo - $dataOffsetFrom);

            $errorLogs[$i] = unserialize($data);
        }
        fclose($fData);
        fclose($fIndex);

        return $errorLogs;
    }

    public function getErrorLogCount($options = array())
    {
        if (!isset($options['year'])) return 0;
        if (!isset($options['month'])) return 0;
        if (!isset($options['day'])) return 0;

        $path = PATH_DATA . DS . 'system' . DS . 'errorLog' . DS . $options['year'] . DS . $options['month'] . DS . $options['day'] . '.index';
        if (!is_file($path)) return 0;

        return filesize($path) / 4;
    }

    /**
     * 获取指定日期和索引的日志明细
     *
     *
     */
    public function getErrorLog($year, $month, $day, $index)
    {
        $dataPath = PATH_DATA . DS . 'system' . DS . 'errorLog' . DS . $year . DS . $month . DS . $day . '.data';
        if (!is_file($dataPath)) {
            $this->setError('打开日志数据文件不存在！');
            return false;
        }

        $indexPath = PATH_DATA . DS . 'system' . DS . 'errorLog' . DS . $year . DS . $month . DS . $day . '.index';
        if (!is_file($indexPath)) {
            $this->setError('日志索引文件不存在！');
            return false;
        }

        $fData = fopen($dataPath, 'rb');
        $fIndex = fopen($indexPath, 'rb');
        if (!$fData) {
            $this->setError('打开日志数据文件出错！');
            return false;
        }

        if (!$fIndex) {
            $this->setError('打开日志索引文件出错！');
            return false;
        }

        $max = intval(filesize($indexPath) / 4) - 1;
        if ($index < 0 || $index > $max) {
            $this->setError('读取日志文件索引位置错误！');
            return false;
        }

        fseek($fIndex, $index * 4);
        $dataOffsetFrom = intval(implode('', unpack('L', fread($fIndex, 4))));
        fseek($fData, $dataOffsetFrom);

        $dataOffsetTo = null;
        if ($index == $max) {
            $dataOffsetTo = intval(filesize($dataPath));
        } else {
            $dataOffsetTo = intval(implode('', unpack('L', fread($fIndex, 4))));
        }

        $data = fread($fData, $dataOffsetTo - $dataOffsetFrom);

        $data = unserialize($data);
        $data['dataLength'] = $dataOffsetTo - $dataOffsetFrom;
        return $data;
    }
}