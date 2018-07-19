<?php
use Phpbe\System\Be;
?>

<!--{head}-->

<script type="text/javascript" language="javascript">

</script>
<script type="text/javascript" language="javascript" src="/app/System/AdminTemplate/Db/js/tables.js"></script>
<!--{/head}-->

<!--{center}-->
<div id="apps">
    <ul>
        <?php
        foreach($this->apps as $app => $name) {
            ?>
            <li data-url="<?php echo adminUrl('app=System&controller=Db&action=tables&type=lists&app=' . $this->app); ?>">><?php echo $name; ?>（<?php echo $app; ?>）</li>
            <?php
        }
        ?>
    </ul>
</div>
<div id="tables"></div>
<div id="table-config"></div>
<!--{/center}-->