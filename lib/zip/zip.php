<?php

namespace lib\zip;

use \system\be;

/*
@版本日期: 2010年08月28日
*/

class zip extends \system\lib
{

    private $path = null; // 压缩包路径
    private $data = null; // 二进制数据
    private $info = null; // 压缩包信息


    private $file_header = "\x50\x4b\x03\x04";
    private $dir_header = "\x50\x4b\x01\x02";
    private $dir_end = "\x50\x4b\x05\x06\x00\x00\x00\x00";

    private $methods = array(0x0 => 'None', 0x1 => 'Shrunk', 0x2 => 'Super Fast', 0x3 => 'Fast', 0x4 => 'Normal', 0x5 => 'Maximum', 0x6 => 'Imploded', 0x8 => 'Deflated');

    // 构造函数
    public function __construct()
    {
    }

    // 析构函数
    public function __destruct()
    {
    }

    // 载入文件
    public function open($path)
    {
        $this->path = $path;
    }

    // 加载压缩包数据
    public function load_data()
    {
        $this->data = file_get_contents($this->path);
    }

    // 设置压缩包数据
    public function set_data($data)
    {
        $this->data = $data;
    }

    // 解压缩到
    public function extract_to($folder)
    {
        if (!file_exists($folder)) mkdir($folder);

        if (!$this->data) $this->load_data();
        if (!$this->info) $this->load_info();

        if (!extension_loaded('zlib')) {
            $this->set_error('你的服务器不支持 Zlib');
            return false;
        }

        $fso = be::get_lib('fso');
        for ($i = 0, $n = count($this->info); $i < $n; $i++) {
            if (substr($this->info[$i]['name'], -1, 1) != '/' && substr($this->info[$i]['name'], -1, 1) != '\\') {
                $buffer = $this->get_file_data($i);
                $extract_to_path = $folder . DS . str_replace(array('/', '\\'), DS, $this->info[$i]['name']);
                $extract_to_folder = dirname($extract_to_path);

                if (!file_exists($extract_to_folder)) $fso->mk_dir($extract_to_folder);
                file_put_contents($extract_to_path, $buffer);
            }
        }
        return true;
    }

    function get_file_data($key)
    {
        if ($this->info[$key]['_method'] == 0x8) {
            if (extension_loaded('zlib')) {
                return @ gzinflate(substr($this->data, $this->info[$key]['_dataStart'], $this->info[$key]['csize']));
            }
        } elseif ($this->info[$key]['_method'] == 0x0) {
            return substr($this->data, $this->info[$key]['_dataStart'], $this->info[$key]['csize']);
        } elseif ($this->info[$key]['_method'] == 0x12) {
            if (extension_loaded('bz2')) {
                return bzdecompress(substr($this->data, $this->info[$key]['_dataStart'], $this->info[$key]['csize']));
            }
        }
        return '';
    }

    public function load_info()
    {
        if (!$this->data) $this->load_data();

        $entries = array();

        $last = strpos($this->data, $this->dir_end);
        do {
            $l = $last;
        } while (($last = strpos($this->data, $this->dir_end, $last + 1)) !== false);

        $offset = 0;
        if ($l) {
            $end_of_central_directory = unpack('vNumberOfDisk/vNoOfDiskWithStartOfCentralDirectory/vNoOfCentralDirectoryEntriesOnDisk/vTotalCentralDirectoryEntries/VSizeOfCentralDirectory/VCentralDirectoryOffset/vCommentLength', substr($this->data, $l + 4));
            $offset = $end_of_central_directory['CentralDirectoryOffset'];
        }

        $start = strpos($this->data, $this->dir_header, $offset);
        do {
            if (strlen($this->data) < $start + 31) {
                $this->set_error('ZIP文件数据错误');
                return false;
            }
            $info = unpack('vMethod/VTime/VCRC32/VCompressed/VUncompressed/vLength', substr($this->data, $start + 10, 20));
            $name = substr($this->data, $start + 46, $info['Length']);

            $entries[$name] = array('attr' => null, 'crc' => sprintf("%08s", dechex($info['CRC32'])), 'csize' => $info['Compressed'], 'date' => null, '_dataStart' => null, 'name' => $name, 'method' => $this->methods[$info['Method']], '_method' => $info['Method'], 'size' => $info['Uncompressed'], 'type' => null);
            $entries[$name]['date'] = mktime((($info['Time'] >> 11) & 0x1f), (($info['Time'] >> 5) & 0x3f), (($info['Time'] << 1) & 0x3e), (($info['Time'] >> 21) & 0x07), (($info['Time'] >> 16) & 0x1f), ((($info['Time'] >> 25) & 0x7f) + 1980));

            if (strlen($this->data) < $start + 43) {
                $this->set_error('ZIP文件数据错误');
                return false;
            }
            $info = unpack('vInternal/VExternal', substr($this->data, $start + 36, 6));

            $entries[$name]['type'] = ($info['Internal'] & 0x01) ? 'text' : 'binary';
            $entries[$name]['attr'] = (($info['External'] & 0x10) ? 'D' : '-') . (($info['External'] & 0x20) ? 'A' : '-') . (($info['External'] & 0x03) ? 'S' : '-') . (($info['External'] & 0x02) ? 'H' : '-') . (($info['External'] & 0x01) ? 'R' : '-');
        } while (($start = strpos($this->data, $this->dir_header, $start + 46)) !== false);

        $start = strpos($this->data, $this->file_header);
        do {
            if (strlen($this->data) < $start + 34) {
                $this->set_error('ZIP文件数据错误');
                return false;
            }
            $info = unpack('vMethod/VTime/VCRC32/VCompressed/VUncompressed/vLength/vExtraLength', substr($this->data, $start + 8, 25));
            $name = substr($this->data, $start + 30, $info['Length']);
            $entries[$name]['_dataStart'] = $start + 30 + $info['Length'] + $info['ExtraLength'];
        } while (strlen($this->data) > $start + 30 + $info['Length'] && ($start = strpos($this->data, $this->file_header, $start + 30 + $info['Length'])) !== false);

        $this->info = array_values($entries);
        return true;
    }

    // 检测压缩包是否合法
    public function valid()
    {
        if (!$this->data) $this->load_data();
        if (strpos($this->data, $this->file_header) !== false) return true;
        return false;
    }

}