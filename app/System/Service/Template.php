<?php
namespace App\System\Service;

use Phpbe\System\Be;
use Phpbe\System\Service\ServiceException;

class Template extends \Phpbe\System\Service
{


    /**
     * 更新模板
     *
     * @param string $app 应用名
     * @param string $template 模析名
     * @param string $theme 主题名
     * @param bool $admin 是否是后台模析
     * @throws \Exception
     */
    public function update($app, $template, $theme, $admin = false)
    {
        $fileTheme = Be::getRuntime()->getPathRoot() . '/theme/' . $theme . '/' . $theme . '.php';
        if (!file_exists($fileTheme)) {
            throw new ServiceException('主题 ' . $theme . ' 不存在！');
        }

        $fileTemplate = Be::getRuntime()->getPathRoot() . '/App/' . $app . ($admin ? '/AdminTemplate/' : '/Template/') . str_replace('.', '/', $template) . '.php';
        if (!file_exists($fileTemplate)) {
            throw new ServiceException('模板 ' . $template . ' 不存在！');
        }

        $path = Be::getRuntime()->getPathCache() . '/Runtime/Theme/' . $theme. '/App/' . $app . '/'. ($admin ? 'AdminTemplate' : 'Template') . '/' . str_replace('.', '/', $template) . '.php';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $contentTheme = file_get_contents($fileTheme);
        $contentTemplate = file_get_contents($fileTemplate);

        $codePre = '';
        $codeUse = '';
        $codeHtml = '';
        $pattern = '/<!--{html}-->(.*?)<!--{\/html}-->/s';
        if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 html
            $codeHtml = trim($matches[1]);

            if (preg_match_all('/use\s(.+);/', $contentTemplate, $matches)) {
                foreach ($matches[1] as $m) {
                    $codeUse .= 'use ' . $m . ';' . "\n";
                }
            }

            $pattern = '/<\?php(.*?)\?>\s+<!--{html}-->/s';
            if (preg_match($pattern, $contentTemplate, $matches)) {
                $codePre = trim($matches[1]);
                $codePre = preg_replace('/use\s(.+);/', '', $codePre);
                $codePre = preg_replace('/\s+$/m', '', $codePre);
            }

        } else {

            if (preg_match($pattern, $contentTheme, $matches)) {
                $codeHtml = trim($matches[1]);

                $pattern = '/<!--{head}-->(.*?)<!--{\/head}-->/s';
                if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 head
                    $codeHead = $matches[1];
                    $codeHtml = preg_replace($pattern, $codeHead, $codeHtml);
                }

                $pattern = '/<!--{body}-->(.*?)<!--{\/body}-->/s';
                if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 body
                    $codeBody = $matches[1];
                    $codeHtml = preg_replace($pattern, $codeBody, $codeHtml);
                } else {

                    $pattern = '/<!--{north}-->(.*?)<!--{\/north}-->/s';
                    if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 north
                        $codeNorth = $matches[1];
                        $codeHtml = preg_replace($pattern, $codeNorth, $codeHtml);
                    }

                    $pattern = '/<!--{middle}-->(.*?)<!--{\/middle}-->/s';
                    if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 north
                        $codeMiddle = $matches[1];
                        $codeHtml = preg_replace($pattern, $codeMiddle, $codeHtml);
                    } else {
                        $pattern = '/<!--{west}-->(.*?)<!--{\/west}-->/s';
                        if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 west
                            $codeWest = $matches[1];
                            $codeHtml = preg_replace($pattern, $codeWest, $codeHtml);
                        }

                        $pattern = '/<!--{center}-->(.*?)<!--{\/center}-->/s';
                        if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 center
                            $codeCenter = $matches[1];
                            $codeHtml = preg_replace($pattern, $codeCenter, $codeHtml);
                        }

                        $pattern = '/<!--{east}-->(.*?)<!--{\/east}-->/s';
                        if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 east
                            $codeEast = $matches[1];
                            $codeHtml = preg_replace($pattern, $codeEast, $codeHtml);
                        }
                    }

                    $pattern = '/<!--{message}-->(.*?)<!--{\/message}-->/s';
                    if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 message
                        $codeMessage = $matches[1];
                        $codeHtml = preg_replace($pattern, $codeMessage, $codeHtml);
                    }

                    $pattern = '/<!--{south}-->(.*?)<!--{\/south}-->/s';
                    if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 north
                        $codeSouth = $matches[1];
                        $codeHtml = preg_replace($pattern, $codeSouth, $codeHtml);
                    }
                }
            }

            $pattern = '/use\s(.+);/';
            $uses = null;
            if (preg_match_all($pattern, $contentTheme, $matches)) {
                $uses = $matches[1];
                foreach ($matches[1] as $m) {
                    $codeUse .= 'use ' . $m . ';' . "\n";
                }
            }

            if (preg_match_all($pattern, $contentTemplate, $matches)) {
                foreach ($matches[1] as $m) {
                    if ($uses !== null && !in_array($m, $uses)) {
                        $codeUse .= 'use ' . $m . ';' . "\n";
                    }
                }
            }

            $pattern = '/<\?php(.*?)\?>\s+<!--{html}-->/s';
            if (preg_match($pattern, $contentTheme, $matches)) {
                $codePreTheme = trim($matches[1]);
                $codePreTheme = preg_replace('/use\s(.+);/', '', $codePreTheme);
                $codePreTheme = preg_replace('/\s+$/m', '', $codePreTheme);
                $codePre = $codePreTheme . "\n";
            }

            $pattern = '/<\?php(.*?)\?>\s+<!--{(?:html|head|body|north|middle|west|center|east|south|message)}-->/s';
            if (preg_match($pattern, $contentTemplate, $matches)) {
                $codePreTemplate = trim($matches[1]);
                $codePreTemplate = preg_replace('/use\s(.+);/', '', $codePreTemplate);
                $codePreTemplate = preg_replace('/\s+$/m', '', $codePreTemplate);

                $codePre .= $codePreTemplate . "\n";
            }
        }

        $templates = explode('.', $template);
        $className = array_pop($templates);

        $namespaceSuffix = '';
        if (count($templates)) {
            $namespaceSuffix = implode('\\', $templates);
        }

        $codeVars = '';
        $configPath = Be::getRuntime()->getPathRoot() . '/theme/' . $theme . '/config.php';
        if (file_exists($configPath)) {
            include $configPath;
            $themeConfigClassName = ($admin ? 'admin\\' : '') . 'theme\\' . $theme . '\\config';
            if (class_exists($themeConfigClassName)) {
                $themeConfig = new $themeConfigClassName();
                if (isset($themeConfig->colors) && is_array($themeConfig->colors)) {
                    $codeVars .= '  public $colors = [\'' . implode('\',\'', $themeConfig->colors) . '\'];' . "\n";
                }
            }
        }

        $codePhp = '<?php' . "\n";
        $codePhp .= 'namespace Cache\\Runtime\\Theme\\' . $theme . '\\App\\' . $app . '\\' . ($admin ? 'AdminTemplate' : 'Template') . '\\' . $namespaceSuffix . ';' . "\n";
        $codePhp .= "\n";
        $codePhp .= $codeUse;
        $codePhp .= "\n";
        $codePhp .= 'class ' . $className . ' extends \\Phpbe\\System\\Template' . "\n";
        $codePhp .= '{' . "\n";
        $codePhp .= $codeVars;
        $codePhp .= "\n";
        $codePhp .= '  public function display()' . "\n";
        $codePhp .= '  {' . "\n";
        $codePhp .= $codePre;
        $codePhp .= '    ?>' . "\n";
        $codePhp .= $codeHtml . "\n";
        $codePhp .= '    <?php' . "\n";
        $codePhp .= '  }' . "\n";
        $codePhp .= '}' . "\n";
        $codePhp .= "\n";

        file_put_contents($path, $codePhp, LOCK_EX);
        chmod($path, 0755);
    }

}
