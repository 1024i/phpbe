<?php
namespace App\Cms\Router;

use Phpbe\System\Be;
use Phpbe\System\Router;

class Article extends Router
{

    public function encodeUrl($app, $controller, $task, $params = [])
    {
        $configSystem = Be::getConfig('System.System');

        if ($task == 'articles') {
            if (isset($params['categoryId'])) {
                if (isset($params['page'])) {
                    return Be::getRuntime()->getUrlRoot() . '/Cms/Article/c' . $params['categoryId'] . '/p' . $params['page'] . '/';
                }
                return Be::getRuntime()->getUrlRoot() . '/Cms/Article/c' . $params['categoryId'] . '/';
            }
        } elseif ($task == 'detail') {
            if (isset($params['articleId'])) {
                return Be::getRuntime()->getUrlRoot() . '/Cms/Article/' . $params['articleId'] . $configSystem->sefSuffix;
            }
        } elseif ($task == 'user') {
            if (isset($params['userId'])) {
                return Be::getRuntime()->getUrlRoot() . '/Cms/Article/user/' . $params['userId'] . $configSystem->sefSuffix;
            }
        }

        return parent::encodeUrl($app, $controller, $task, $params);
    }

    public function decodeUrl($urls)
    {
        $len = count($urls);
        if ($len > 2) {
            if (is_numeric($urls[2])) {
                $_GET['task'] = $_REQUEST['task'] = 'detail';
                $_GET['articleId'] = $_REQUEST['articleId'] = $urls[2];

                return true;
            } elseif (substr($urls[2], 0, 1) == 'c') {
                $_GET['task'] = $_REQUEST['task'] = 'articles';
                $_GET['categoryId'] = $_REQUEST['categoryId'] = substr($urls[2], 1);

                if ($len > 3 && substr($urls[3], 0, 1) == 'p') {
                    $_GET['page'] = $_REQUEST['page'] = substr($urls[3], 1);
                }
                return true;
            } elseif ($urls[2] == 'user') {
                $_GET['task'] = $_REQUEST['task'] = 'user';
                $_GET['userId'] = $_REQUEST['userId'] = $urls[3];

                return true;
            }
        }

        return parent::decodeUrl($urls);
    }
}