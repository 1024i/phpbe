<?php
define('ROOT', dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);

require_once ROOT.DS.'less.php'.DS.'Less.php';


$options = array(
	// 'cache_dir'=>ROOT.DS.'cache',  // ����·������α���ʱ���١�
	'compress'=>true  // �Ƿ�ѹ��
);

$parser = new Less_Parser($options);
$parser->parseFile(ROOT.DS.'be.less');
$css = $parser->getCss();

file_put_contents(ROOT.DS.'be.css', $css);

echo 'be.less compiled!';
?>