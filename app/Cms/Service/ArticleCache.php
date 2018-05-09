<?php
namespace App\Cms\Service;

use Phpbe\System\Be;
use Phpbe\System\Cache;
use Phpbe\System\Service;

class ArticleCache extends Service
{

    private $cache = [];

    public function __call($fn, $args)
    {
        switch ($fn) {
            case 'getArticles':
            case 'getArticleCount':
            case 'getSimilarArticles':
            case 'getActiveUsers':

                $cacheKey = 'cache:Cms:Article:'.$fn.':'.md5(serialize($args));
                if (isset($this->cache[$cacheKey])) return $this->cache[$cacheKey];

                $result = Cache::get($cacheKey);
                if ($result) return $result;

                $articleService = Be::getService('Cms.Article');
                $result = call_user_func_array(array($articleService, $fn), $args);

                Cache::set($cacheKey, $result, Be::getConfig('Cms.Article')->cacheExpire);
                $this->cache[$cacheKey] = $result;
                return $result;
        }

        throw new \Exception('方法' . $fn . '不支持缓存调用');
    }

}
