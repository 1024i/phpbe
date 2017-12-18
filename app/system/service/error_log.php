<?php
namespace app\system\service;


class error_log extends \system\service
{

    public function get_years()
    {
        $dir = PATH_DATA . DS . 'system' . DS . 'error_log';
        $years = array();
        if (file_exists($dir) && is_dir($dir)) {
            $file_names = scandir($dir);
            foreach ($file_names as $file_name) {
                if ($file_name != '.' && $file_name != '..' && is_dir($dir . DS . $file_name)) {
                    $years[] = $file_name;
                }
            }
        }
        return $years;
    }

    public function get_months($year)
    {
        $dir = PATH_DATA . DS . 'system' . DS . 'error_log' . DS . $year;
        $months = array();
        if (file_exists($dir) && is_dir($dir)) {
            $file_names = scandir($dir);
            foreach ($file_names as $file_name) {
                if ($file_name != '.' && $file_name != '..' && is_dir($dir . DS . $file_name)) {
                    $months[] = $file_name;
                }
            }
        }
        return $months;
    }

    public function get_days($year, $month)
    {
        $dir = PATH_DATA . DS . 'system' . DS . 'error_log' . DS . $year . DS . $month;
        $days = array();
        if (file_exists($dir) && is_dir($dir)) {
            $file_names = scandir($dir);
            foreach ($file_names as $file_name) {
                if (is_file($dir . DS . $file_name) && strrchr($file_name, '.') == '.data') {
                    $days[] = substr($file_name, 0, strpos($file_name, '.'));
                }
            }
        }
        return $days;
    }

    public function get_error_logs($options = array())
    {
        if (!isset($options['year'])) return array();
        if (!isset($options['month'])) return array();
        if (!isset($options['day'])) return array();

        $data_path = PATH_DATA . DS . 'system' . DS . 'error_log' . DS . $options['year'] . DS . $options['month'] . DS . $options['day'] . '.data';
        if (!is_file($data_path)) return array();

        $index_path = PATH_DATA . DS . 'system' . DS . 'error_log' . DS . $options['year'] . DS . $options['month'] . DS . $options['day'] . '.index';
        if (!is_file($index_path)) return array();

        $offset = 0;
        $limit = 0;
        if (isset($options['offset']) && is_numeric($options['offset'])) $offset = intval($options['offset']);
        if (isset($options['limit']) && is_numeric($options['limit'])) $limit = intval($options['limit']);
        if ($offset < 0) $offset = 0;
        if ($limit <= 0) $limit = 100;

        $max = intval(filesize($index_path) / 4) - 1;
        if ($max < 0) return array();

        $from = $offset;
        $to = $offset + $limit - 1;

        if ($from > $max) $from = $max;
        if ($to > $max) $to = $max;

        $f_data = fopen($data_path, 'rb');
        $f_index = fopen($index_path, 'rb');
        if (!$f_data) return array();
        if (!$f_index) return array();

        $error_logs = array();
        for ($i = $from; $i <= $to; $i++) {
            fseek($f_index, $i * 4);
            $data_offset_from = intval(implode('', unpack('L', fread($f_index, 4))));
            fseek($f_data, $data_offset_from);

            $data_offset_to = null;
            if ($i == $to) {
                $data_offset_to = intval(filesize($data_path));
            } else {
                $data_offset_to = intval(implode('', unpack('L', fread($f_index, 4))));
            }
            $data = fread($f_data, $data_offset_to - $data_offset_from);

            $error_logs[$i] = unserialize($data);
        }
        fclose($f_data);
        fclose($f_index);

        return $error_logs;
    }

    public function get_error_log_count($options = array())
    {
        if (!isset($options['year'])) return 0;
        if (!isset($options['month'])) return 0;
        if (!isset($options['day'])) return 0;

        $path = PATH_DATA . DS . 'system' . DS . 'error_log' . DS . $options['year'] . DS . $options['month'] . DS . $options['day'] . '.index';
        if (!is_file($path)) return 0;

        return filesize($path) / 4;
    }

    /**
     * 获取指定日期和索引的日志明细
     *
     *
     */
    public function get_error_log($year, $month, $day, $index)
    {
        $data_path = PATH_DATA . DS . 'system' . DS . 'error_log' . DS . $year . DS . $month . DS . $day . '.data';
        if (!is_file($data_path)) {
            $this->set_error('打开日志数据文件不存在！');
            return false;
        }

        $index_path = PATH_DATA . DS . 'system' . DS . 'error_log' . DS . $year . DS . $month . DS . $day . '.index';
        if (!is_file($index_path)) {
            $this->set_error('日志索引文件不存在！');
            return false;
        }

        $f_data = fopen($data_path, 'rb');
        $f_index = fopen($index_path, 'rb');
        if (!$f_data) {
            $this->set_error('打开日志数据文件出错！');
            return false;
        }

        if (!$f_index) {
            $this->set_error('打开日志索引文件出错！');
            return false;
        }

        $max = intval(filesize($index_path) / 4) - 1;
        if ($index < 0 || $index > $max) {
            $this->set_error('读取日志文件索引位置错误！');
            return false;
        }

        fseek($f_index, $index * 4);
        $data_offset_from = intval(implode('', unpack('L', fread($f_index, 4))));
        fseek($f_data, $data_offset_from);

        $data_offset_to = null;
        if ($index == $max) {
            $data_offset_to = intval(filesize($data_path));
        } else {
            $data_offset_to = intval(implode('', unpack('L', fread($f_index, 4))));
        }

        $data = fread($f_data, $data_offset_to - $data_offset_from);

        $data = unserialize($data);
        $data['data_length'] = $data_offset_to - $data_offset_from;
        return $data;
    }
}