<?php
namespace App\System\Service;

use Phpbe\System\Be;
use Phpbe\System\Service;

/**
 * 导出任务功能
 *
 * Class Export
 * @package Haitun\Service\M\Service
 */
class ExportTask extends Service
{

    /**
     * 更新 数据库行记灵对象
     *
     * @param string $name 名称
     * @param array $condition 查询条件
     * @return string 任务ID
     * @throws |Exception
     */
    public function create($name, $condition)
    {
        $app = Be::getRuntime()->getApp();
        $controller = Be::getRuntime()->getController();

        $pathIndex = Be::getRuntime()->getDataPath() . '/ExportTask/' . $app . '/' . $controller . '/index';
        $dir = dirname($pathIndex);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $taskId = uniqid();
        $pathData = Be::getRuntime()->getDataPath() . '/ExportTask/' . $app . '/' . $controller . '/data';
        $dir = dirname($pathData);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $bootstrapUrl = adminurl(''.$app.'', ''.$controller.'', 'exportTaskRun', ['taskId' => $taskId]);
        $data = [
            'taskId' => $taskId,
            'name' => $name,
            'createTime' => date('Y-m-d H:i:s'),
            'completeTime' => '-',
            'error' => '-',
            'condition' => $condition,
            'bootstrapUrl' => $bootstrapUrl
        ];
        file_put_contents($pathData, serialize($data), LOCK_EX);

        file_put_contents($pathIndex, $taskId . PHP_EOL, FILE_APPEND | LOCK_EX);

        return $taskId;
    }

    /**
     * 任务列表
     *
     * @return array 任务列表
     */
    public function getTasks()
    {
        $app = Be::getRuntime()->getApp();
        $controller = Be::getRuntime()->getController();

        $pathIndex = Be::getRuntime()->getDataPath() . '/ExportTask/' . $app . '/' . $controller . '/index';
        if (!file_exists($pathIndex)) {
            return array();
        }

        $tasks = array();

        $fIndex = fopen($pathIndex, 'r');
        if ($fIndex) {
            while (($taskId = fgets($fIndex)) !== false) {
                $taskId = trim($taskId);
                $task = $this->getTask($taskId);
                if ($task) $tasks[] = $task;
            }
            fclose($fIndex);
        }

        $tasks = array_reverse($tasks);

        return $tasks;
    }

    /**
     * 获取任务
     *
     * @return array 任务
     */
    public function getTask($taskId)
    {
        $app = Be::getRuntime()->getApp();
        $controller = Be::getRuntime()->getController();

        $task = [];
        $pathData = Be::getRuntime()->getDataPath() . '/ExportTask/' . $app . '/' . $controller . '/' . $taskId . '/data';
        $pathCsvData = Be::getRuntime()->getDataPath() . '/ExportTask/' . $app . '/' . $controller . '/' . $taskId . '/csvData';
        if (file_exists($pathData)) {
            $task = unserialize(file_get_contents($pathData));
            $task['progress'] = $this->getProgress($task['taskId']);
            $size = 0;
            if (file_exists($pathCsvData)) {
                $size = filesize($pathCsvData);
                if ($size > 1073741824) {
                    $size = number_format($size / 1073741824, 2, '.', '').' GB';
                } elseif ($size > 1048576) {
                    $size = number_format($size / 1048576, 2, '.', '').' MB';
                } elseif ($size > 1024) {
                    $size = number_format($size / 1024, 0, '.', '').' KB';
                } else {
                    $size = $size.' B';
                }
            }
            $task['size'] = $size;

            if (!isset($task['completeTime'])) $task['completeTime'] = '-';

            $executeTime = '-';
            if ($task['completeTime'] != '-') {
                $executeTimeStr = '';
                $executeTimeS = strtotime($task['completeTime']) - strtotime($task['createTime']);
                if ($executeTimeS > 3600) {
                    $executeTimeStr .= intval($executeTimeS / 3600);
                    $executeTimeS = $executeTimeS % 3600;
                } else {
                    $executeTimeStr .= '0';
                }
                $executeTimeStr .= ':';

                if ($executeTimeS > 60) {
                    $m = intval($executeTimeS / 60);
                    if ($m < 10) $executeTimeStr .= '0';
                    $executeTimeStr .= $m;
                    $executeTimeS = $executeTimeS % 60;
                } else {

                    $executeTimeStr .= '00';
                }
                $executeTimeStr .= ':';

                if ($executeTimeS > 0) {
                    if ($executeTimeS < 10) $executeTimeStr .= '0';
                    $executeTimeStr .= $executeTimeS;
                } else {
                    $executeTimeStr .= '00';
                }

                $executeTime = $executeTimeStr;
            }

            $task['executeTime'] = $executeTime;

            $task['createTime'] = date('Y-m-d H:i', strtotime($task['createTime']));
            if ($task['completeTime'] != '-') $task['completeTime'] = date('Y-m-d H:i', strtotime($task['completeTime']));

            if (!isset($task['error'])) {
                $task['error'] = '-';
            } else {
                if ($task['error'] != '-') {
                    $path = Be::getRuntime()->getDataPath() . '/ExportTask/' . $app . '/' . $controller . '/' . $taskId . '/error';  // 错误
                    $task['errorDetails'] = nl2br(file_get_contents($path));
                }
            }
        }
        return $task;
    }

    /**
     * 输出CSV数据
     *
     * @param string $taskId 任务ID
     * @param array $csvData CSV 数据
     */
    public function addCsvData($taskId, $csvData = array())
    {
        $app = Be::getRuntime()->getApp();
        $controller = Be::getRuntime()->getController();

        $path = Be::getRuntime()->getDataPath() . '/ExportTask/' . $app . '/' . $controller . '/' . $taskId . '/csvData';  // 数据

        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        file_put_contents($path, json_encode($csvData) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    /**
     * 获取 CSV 数据
     *
     * @param $namespace
     * @param $taskId
     * @return \Generator
     */
    public function getCsvData($taskId)
    {
        $app = Be::getRuntime()->getApp();
        $controller = Be::getRuntime()->getController();

        $path = Be::getRuntime()->getDataPath() . '/ExportTask/' . $app . '/' . $controller . '/' . $taskId . '/csvData';  // 数据

        $fCsvData = fopen($path, 'r');
        flock($fCsvData, LOCK_EX);

        while (($csvData = fgets($fCsvData, 40960)) !== false) {
            $csvData = trim($csvData);
            if ($csvData) {
                yield json_decode($csvData);
            }
        }

        flock($fCsvData, LOCK_UN);
        fclose($fCsvData);
    }

    /**
     * 设置指定任务的进度
     *
     * @param string $taskId 任务ID
     * @param $progress
     */
    public function setProgress($taskId, $progress)
    {
        $app = Be::getRuntime()->getApp();
        $controller = Be::getRuntime()->getController();

        $path = Be::getRuntime()->getDataPath() . '/ExportTask/' . $app . '/' . $controller . '/' . $taskId . '/progress';  // 进度

        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        if ($progress > 100) $progress = 100;

        file_put_contents($path, $progress, LOCK_EX);

        if ($progress == 100) {
            $task = $this->getTask($taskId);
            $task['completeTime'] = date('Y-m-d H:i:s');

            $pathData = Be::getRuntime()->getDataPath() . '/ExportTask/' . $app . '/' . $controller . '/' . $taskId . '/data';
            file_put_contents($pathData, serialize($task), LOCK_EX);
        }
    }

    /**
     * 设置指定任务的进度
     *
     * @param string $taskId 任务ID
     * @param \Exception $e 异常
     */
    public function error($taskId, $e)
    {
        $app = Be::getRuntime()->getApp();
        $controller = Be::getRuntime()->getController();

        $task = $this->getTask($taskId);
        $task['error'] = $e->getMessage();

        $pathData = Be::getRuntime()->getDataPath() . '/ExportTask/' . $app . '/' . $controller . '/' . $taskId . '/data';
        file_put_contents($pathData, serialize($task), LOCK_EX);

        $path = Be::getRuntime()->getDataPath() . '/ExportTask/' . $app . '/' . $controller . '/' . $taskId . '/error';  // 错误

        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $error = '错误信息: '.$e->getMessage() . PHP_EOL;
        $error .= '错误码: '.$e->getCode() . PHP_EOL;
        $error .= '文件: '.$e->getFile() . PHP_EOL;
        $error .= '行号: '.$e->getLine() . PHP_EOL;
        $error .= '跟踪: ' . PHP_EOL;
        $error .= $e->getTraceAsString() . PHP_EOL;

        file_put_contents($path, $error, LOCK_EX);
    }

    /**
     * 获取指定任务的进度
     *
     * @param string $taskId 任务ID
     * @return int
     */
    public function getProgress($taskId)
    {
        $app = Be::getRuntime()->getApp();
        $controller = Be::getRuntime()->getController();

        $path = Be::getRuntime()->getDataPath() . '/ExportTask/' . $app . '/' . $controller . '/' . $taskId . '/progress';  // 进度
        if (!file_exists($path)) {
            return 0;
        }

        return intval(file_get_contents($path));
    }


    /**
     * 删除任务记录
     *
     * @param string $taskId 任务ID
     * @return bool 是否删除成功
     */
    public function delete($taskId)
    {
        $app = Be::getRuntime()->getApp();
        $controller = Be::getRuntime()->getController();

        $path = Be::getRuntime()->getDataPath() . '/ExportTask/' . $app . '/' . $controller . '/' . $taskId;
        Be::getLib('Fso')->rmDir($path);

        $pathIndex = Be::getRuntime()->getDataPath() . '/ExportTask/' . $app . '/' . $controller . '/index';
        if (!file_exists($pathIndex)) {
            return true;
        }

        $pathIndex0 = Be::getRuntime()->getDataPath() . '/ExportTask/' . $app . '/' . $controller . '/index0';

        $fIndex = fopen($pathIndex, 'r');
        flock($fIndex, LOCK_EX);

        $fIndex0 = fopen($pathIndex0, 'w');
        flock($fIndex0, LOCK_EX);

        while (($tTaskId = fgets($fIndex)) !== false) {
            $tTaskId = trim($tTaskId);
            if ($tTaskId != $taskId) {
                fwrite($fIndex0, $tTaskId . PHP_EOL);
            }
        }

        flock($fIndex, LOCK_UN);
        fclose($fIndex);

        flock($fIndex0, LOCK_UN);
        fclose($fIndex0);

        unlink($pathIndex);
        rename($pathIndex0, $pathIndex);

        return true;
    }


}
