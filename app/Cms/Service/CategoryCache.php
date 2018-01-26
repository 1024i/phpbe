<?php
namespace App\Cms\Service;

use System\Be;
use System\Cache;
use System\Service;

class CategoryCache extends Service
{


    private $cache = [];

    public function __call($fn, $args)
    {
        switch ($fn) {
            case 'getCategories':
            case 'getCategoryFlatTree':
            case 'getCategoryCount':
            case 'getCategoryTree':
            case 'getSubCategoryIds':
            case 'getCategory':
            case 'getTopParentCategory':

                $cacheKey = 'cache:Cms:Category:'.$fn.':'.md5(serialize($args));
                if (isset($this->cache[$cacheKey])) return $this->cache[$cacheKey];

                $result = Cache::get($cacheKey);
                if ($result) return $result;

                $categoryService = Be::getService('Cms.Category');
                $result = call_user_func_array(array($categoryService, $fn), $args);

                Cache::set($cacheKey, $result, Be::getConfig('Cms.Article')->cacheExpire);
                $this->cache[$cacheKey] = $result;
                return $result;
        }

        throw new \Exception('方法' . $fn . '不支持缓存调用');
    }

}
