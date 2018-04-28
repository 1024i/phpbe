<?php
namespace System;

/**
 *  运行时
 * @package System
 *
 */
class Runtime
{

    private $pathRoot = null;

    private $urlRoot = null;

    private $dirData = 'data';

    private $dirCache = 'cache';

    /**
     * @param null $pathRoot
     */
    public function setPathRoot($pathRoot)
    {
        $this->pathRoot = $pathRoot;
    }

    /**
     * @return null
     */
    public function getPathRoot()
    {
        return $this->pathRoot;
    }

    /**
     * @return null
     */
    public function getPathCache()
    {
        return $this->pathRoot.'/'.$this->dirCache;
    }

    /**
     * @return null
     */
    public function getPathData()
    {
        return $this->pathRoot.'/'.$this->dirData;
    }

    /**
     * @param null $urlRoot
     */
    public function setUrlRoot($urlRoot)
    {
        $this->urlRoot = $urlRoot;
    }

    /**
     * @return null
     */
    public function getUrlRoot()
    {
        return $this->urlRoot;
    }

    /**
     * @return null
     */
    public function getUrlData()
    {
        return $this->urlRoot.'/'.$this->dirData;
    }

    /**
     * @param string $dirData
     */
    public function setDirData($dirData)
    {
        $this->dirData = $dirData;
    }

    /**
     * @return string
     */
    public function getDirData()
    {
        return $this->dirData;
    }

    /**
     * @return string
     */
    public function getDirCache()
    {
        return $this->dirCache;
    }

    /**
     * @param string $dirCache
     */
    public function setDirCache($dirCache)
    {
        $this->dirCache = $dirCache;
    }
}
