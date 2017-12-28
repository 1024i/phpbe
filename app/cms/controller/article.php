<?php
namespace App\Cms\Controller;

use System\Be;
use System\Request;
use System\Response;

/**
 *
 *
 * @router article
 * @permission 文章
 */
class Article extends \System\Controller
{

    /**
     *
     *
     * @permission 首页
     */
    public function home()
    {
        $serviceArticle = Be::getService('Cms.Article');

        // 最新带图文章
        $latestThumbnailArticles = $serviceArticle->getArticles([
            'block' => 0,
            'thumbnail' => 1,
            'orderBy' => 'createTime',
            'orderByDir' => 'DESC',
            'limit' => 6
        ]);

        $activeUsers = $serviceArticle->getActiveUsers();

        // 本月热点
        $monthHottestArticles = $serviceArticle->getArticles([
            'block' => 0,
            'orderBy' => 'hits',
            'orderByDir' => 'DESC',
            'fromTime' => time() - 86400 * 30,
            'limit' => 6
        ]);

        // 推荐文章
        $topArticles = $serviceArticle->getArticles([
            'block' => 0,
            'top' => 1,
            'orderBy' => 'top',
            'orderByDir' => 'DESC',
            'limit' => 6
        ]);

        $topCategories = array();
        $categories = $serviceArticle->getCategories();
        foreach ($categories as $category) {
            if ($category->parentId > 0) continue;
            $topCategories[] = $category;

            $category->articles = $serviceArticle->getArticles([
                'block' => 0,
                'categoryId' => $category->id,
                'orderBy' => 'createTime',
                'orderByDir' => 'DESC',
                'limit' => 6
            ]);
        }

        $configSystem = Be::getConfig('System.System');
        Response::setTitle($configSystem->homeTitle);
        Response::setMetaKeywords($configSystem->homeMetaKeywords);
        Response::setMetaDescription($configSystem->homeMetaDescription);
        Response::set('latestThumbnailArticles', $latestThumbnailArticles);
        Response::set('activeUsers', $activeUsers);
        Response::set('monthHottestArticles', $monthHottestArticles);
        Response::set('topArticles', $topArticles);
        Response::set('categories', $topCategories);
        Response::display();
    }


    /**
     *
     *
     * @permission 文章列表
     */
    public function articles()
    {
        $configArticle = Be::getConfig('Cms.Article');

        $categoryId = Request::get('categoryId', 0, 'int');
        Response::set('categoryId', $categoryId);

        $rowArticleCategory = Be::getRow('articleCategory');
        $rowArticleCategory->cache($configArticle->cacheExpire);
        $rowArticleCategory->load($categoryId);

        if ($rowArticleCategory->id == 0) Response::end('文章分类不存在！');

        Response::setTitle($rowArticleCategory->name);
        Response::set('category', $rowArticleCategory);

        if ($rowArticleCategory->parentId > 0) {
            $parentCategory = null;
            $tmpCategory = $rowArticleCategory;
            while ($tmpCategory->parentId > 0) {
                $parentId = $tmpCategory->parentId;
                $tmpCategory = Be::getRow('articleCategory');
                $tmpCategory->load($parentId);
            }
            $parentCategory = $tmpCategory;
            Response::set('parentCategory', $parentCategory);

            $northMenu = Be::getMenu('north');
            $northMenuTree = $northMenu->getMenuTree();
            if (count($northMenuTree)) {
                //$menuExist = false;
                foreach ($northMenuTree as $menu) {
                    if (
                        isset($menu->params['controller']) && $menu->params['controller'] == 'article' &&
                        isset($menu->params['task']) && $menu->params['task'] == 'listing' &&
                        isset($menu->params['categoryId']) && $menu->params['categoryId'] == $parentCategory->id
                    ) {
                        Response::set('menuId', $menu->id);
                        break;
                    }
                }
            }
        } else {
            Response::set('parentCategory', $rowArticleCategory);
        }

        $serviceArticle = Be::getService('Cms.Article');

        $option = array('categoryId' => $categoryId);

        $limit = 10;
        $pagination = Be::getUi('Pagination');
        $pagination->setLimit($limit);
        $pagination->setTotal($serviceArticle->getArticleCount($option));
        $pagination->setPage(Request::get('page', 1, 'int'));
        $pagination->setUrl('app=cms&controller=article&task=articles&categoryId=' . $categoryId);
        Response::set('pagination', $pagination);

        $option['offset'] = $pagination->getOffset();
        $option['limit'] = $limit;
        $option['orderByString'] = '`top` DESC, `ordering` DESC, `createTime` DESC';

        $articles = $serviceArticle->getArticles($option);
        Response::set('articles', $articles);

        // 热门文章
        $hottestArticles = $serviceArticle->getArticles([
            'block' => 0,
            'categoryId' => $categoryId,
            'orderBy' => 'hits',
            'orderByDir' => 'DESC',
            'limit' => 10
        ]);
        Response::set('hottestArticles', $hottestArticles);

        // 推荐文章
        $topArticles = $serviceArticle->getArticles(array('categoryId' => $categoryId, 'top' => 1, 'orderBy' => 'top', 'orderByDir' => 'DESC', 'limit' => 10));
        Response::set('topArticles', $topArticles);

        Response::display();
    }

    /**
     * @permission 文章明细
     */
    public function detail()
    {
        $configArticle = Be::getConfig('Cms.Article');

        $articleId = Request::get('articleId', 0, 'int');
        if ($articleId == 0) Response::end('参数(articleId)缺失！');

        $rowArticle = Be::getRow('Cms.article');
        $rowArticle->cache($configArticle->cacheExpire);
        $rowArticle->load($articleId);
        $rowArticle->increment('hits', 1); // 点击量加 1

        $serviceArticle = Be::getService('Cms.Article');

        $similarArticles = $serviceArticle->getSimilarArticles($rowArticle, 10);

        // 热门文章
        $hottestArticles = $serviceArticle->getArticles([
            'block' => 0,
            'categoryId' => $rowArticle->categoryId,
            'orderBy' => 'hits',
            'orderByDir' => 'DESC',
            'limit' => 10
        ]);

        // 推荐文章
        $topArticles = $serviceArticle->getArticles([
            'block' => 0,
            'categoryId' => $rowArticle->categoryId,
            'top' => 1,
            'orderBy' => 'top',
            'orderByDir' => 'DESC',
            'limit' => 10
        ]);

        $comments = $serviceArticle->getComments([
            'articleId' => $articleId
        ]);

        Response::setTitle($rowArticle->title);
        Response::setMetaKeywords($rowArticle->metaKeywords);
        Response::setMetaDescription($rowArticle->metaDescription);

        $northMenu = Be::getMenu('north');
        $northMenuTree = $northMenu->getMenuTree();
        if (count($northMenuTree)) {
            $menuExist = false;
            foreach ($northMenuTree as $menu) {
                if (
                    isset($menu->params['controller']) && $menu->params['controller'] == 'article' &&
                    isset($menu->params['task']) && $menu->params['task'] == 'detail' &&
                    isset($menu->params['articleId']) && $menu->params['articleId'] == $articleId
                ) {
                    Response::set('menuId', $menu->id);
                    if ($menu->home == 1) Response::set('home', 1);
                    $menuExist = true;
                    break;
                }
            }

            if (!$menuExist) {
                foreach ($northMenuTree as $menu) {
                    if (
                        isset($menu->params['controller']) && $menu->params['controller'] == 'article' &&
                        isset($menu->params['task']) && $menu->params['task'] == 'listing' &&
                        isset($menu->params['categoryId']) && $menu->params['categoryId'] == $rowArticle->categoryId
                    ) {
                        Response::set('menuId', $menu->id);
                        //$menuExist = true;
                        break;
                    }
                }
            }
        }

        Response::set('article', $rowArticle);
        Response::set('similarArticles', $similarArticles);
        Response::set('hottestArticles', $hottestArticles);
        Response::set('topArticles', $topArticles);
        Response::set('comments', $comments);
        Response::display();
    }

}
