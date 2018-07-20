<div class="theme-box-container">
	<div class="theme-box">
		<div class="theme-box-body">
            <?php
            $menuDashboard = \system\Be::getMenu('dashboard');

            $menuDashboardTree = $menuDashboard->getMenuTree();
            if (count($menuDashboardTree)) {
                echo '<div class="menu">';
                echo '<ul>';
                $i=1;
                $n=count($menuDashboardTree);
                foreach ($menuDashboardTree as $menu) {

                    if (isset($menu->subMenu) && is_array($menu->subMenu) && count($menu->subMenu)>0) {

                        echo '<li class="parent">';
                        echo '<a href="javascript:;" onclick="javascript:$(this).next().slideToggle();">'.$menu->name.'</a>';
                        echo '<ul>';
                        foreach ($menu->subMenu as $subMenu) {
                            echo '<li><a href="'.$subMenu->url.'">'.$subMenu->name.'</a></li>';
                        }
                        echo '</ul>';
                        echo '</li>';
                    } else {
                        echo '<li>';
                        echo '<a href="';
                        if ($menu->home)
                            echo url();
                        else
                            echo $menu->url;
                        echo '" target="'.$menu->target.'"><span>'.$menu->name.'</span></a>';
                        echo '</li>';
                    }
                }
                echo '</ul>';
                echo '</div>';
            }
            ?>
		</div>
	</div>
</div>
