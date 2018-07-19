<!--{html}-->
<ul>
    <?php
    foreach($this->tables as $table) {
        ?>
        <li data-url="<?php echo adminUrl('app=System&controller=Db&action=tables&type=config&app=' . $this->app.'&table='.$table->tableName); ?>">><?php echo $table->tableName; ?></li>
        <?php
    }
    ?>
</ul>
<!--{/html}-->