<div class="theme-box-container">
	<div class="theme-box">
		<div class="theme-box-body">
            <?php
            $menu_dashboard = \system\be::get_menu('dashboard');

            $menu_dashboard_tree = $menu_dashboard->get_menu_tree();
            if (count($menu_dashboard_tree)) {
                echo '<div class="menu">';
                echo '<ul>';
                $i=1;
                $n=count($menu_dashboard_tree);
                foreach ($menu_dashboard_tree as $menu) {

                    if (isset($menu->sub_menu) && is_array($menu->sub_menu) && count($menu->sub_menu)>0) {

                        echo '<li class="parent">';
                        echo '<a href="javascript:;" onclick="javascript:$(this).next().slideToggle();">'.$menu->name.'</a>';
                        echo '<ul>';
                        foreach ($menu->sub_menu as $sub_menu) {
                            echo '<li><a href="'.$sub_menu->url.'">'.$sub_menu->name.'</a></li>';
                        }
                        echo '</ul>';
                        echo '</li>';
                    } else {
                        echo '<li>';
                        echo '<a href="';
                        if ($menu->home)
                            echo URL_ROOT;
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
