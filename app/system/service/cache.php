<?php

namespace App\System\Service;

use Phpbe\System\Be;

class Cache extends \Phpbe\System\Service
{

    /**
     * 清除缓存
     *
     * @param string $dir 缓存文件夹名，为 null 时清空所有缓存
     * @param string $file 指定缓存文件夹下的文件名，为 null 时清空整个文件夹
     * @return bool 是否清除成功
     */
    public function clear($dir = null, $file = null)
    {
        if ($dir === null) {
            return $this->clear('File')
                && $this->clear('Html')
                && $this->clear('Menu')
                && $this->clear('UserRole')
                && $this->clear('AdminUserRole')
                && $this->clear('Row')
                && $this->clear('Table')
                && $this->clear('Template')
                && $this->clear('AdminTemplate');
        }

        $libFso = Be::getLib('Fso');
        if ($file === null) return $libFso->rmDir(Be::getRuntime()->getCachePath() . '/' . $dir);
        return $libFso->rmDir(Be::getRuntime()->getCachePath() . '/' . $dir . '/' . $file);
    }


}
