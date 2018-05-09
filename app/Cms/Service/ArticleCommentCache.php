<?php
namespace App\Cms\Service;

use Phpbe\System\Be;
use Phpbe\System\Cache;
use Phpbe\System\Service;

class ArticleCommentCache extends Service
{

    private $cache = [];

    public function __call($fn, $args)
    {
        switch ($fn) {
            case 'getComments':
            case 'getCommentCount':

                $cacheKey = 'cache:Cms:ArticleComment:'.$fn.':'.md5(serialize($args));
                if (isset($this->cache[$cacheKey])) return $this->cache[$cacheKey];

                $result = Cache::get($cacheKey);
                if ($result) return $result;

                $articleCommentService = Be::getService('Cms.ArticleComment');
                $result = call_user_func_array(array($articleCommentService, $fn), $args);

                Cache::set($cacheKey, $result, Be::getConfig('Cms.Article')->cacheExpire);
                $this->cache[$cacheKey] = $result;
                return $result;
        }

        throw new \Exception('方法' . $fn . '不支持缓存调用');
    }

}
