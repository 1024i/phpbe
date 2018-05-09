<?php
use Phpbe\System\Be;
?>

<!--{html}-->
<?php
$config = Be::getConfig('System.System');
$apps = $this->get('apps');

$url = $this->get('url');

$selectedAppName = '';
foreach ($apps as $app) {
    $menus = $app->getMenus();
    if (count($menus)>0) {
        foreach ($menus as $menu) {
            if ($menu['url'] == $url) {
                $selectedAppName = $app->name;
                break 2;
            }
        }
    }
}
?>

<div class="pageSystemMenuSetLink">
<table>
<tr>
    <th>选择应用</th>
	<th></th>
    <th>选择链接页面</th>
</tr>


<tr>
<td width="48%">
<div class="apps">
    <ul>
    <?php
    foreach ($apps as $app) {
        $menus = $app->getMenus();
        if (count($menus)>0) {
            echo '<li onmouseover="javascript:mouseover(this);" onmouseout="javascript:mouseout(this);"';
			if ($app->name == $selectedAppName) echo ' class="on"';
			echo ' onclick="javascript:clickApp(this, \''.$app->name.'\');"><img src="'.$app->icon.'" align="absmiddle" />'.$app->label.' </li>';
        }
    }
    ?>
    </ul>
</div>
</td>
<td width="4%"></td>
<td width="48%">
<div class="menus">
    <?php
    foreach ($apps as $app) {
        $menus = $app->getMenus();
        if (count($menus)>0) {
			echo '<div class="menu" id="menu_'.$app->name.'"';
			if ($app->name == $selectedAppName) echo ' style="display:block;"';
			echo '>';
			echo '<ul>';
			$i = 0;
			foreach ($menus as $menu) {
				echo '<li onmouseover="javascript:mouseover(this);" onmouseout="javascript:mouseout(this);"';
				if ($menu['url'] == $url) echo ' class="on"';
				$params = '';
				if (isset($menu['params'])) $params = $menu['params'];
				echo ' onclick="javascript:clickMenu(this, \''.$menu['url'].'\', \''.$params.'\');">'.$menu['name'].'</li>';
				$i++;
			}
			echo '</ul>';
			echo '</div>';
        }
    }
    ?>
</div>
</td>
</tr>
</table>
</div>
<!--{/html}-->