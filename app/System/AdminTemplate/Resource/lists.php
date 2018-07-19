<!--{head}-->
<?php
$primaryKey = $this->table->getPrimaryKey();
$fields = $this->table->getFields();

foreach ($fields as &$field) {
    $field['option'] = $field['option']->getKeyValues();
}
unset($field);
?>
<script>
    var g_aFields = <?php echo json_encode($fields); ?>;

    function addCondition() {
        var sTemplate = $("#condition-template").html();
        $("#conditions").append(sTemplate);

        changeConditionField($("#conditions .condition-field:last"));
    }
    
    function changeConditionField(e) {
        var $e = $(e);
        var sField = $e.val();
        var oField = g_aFields[sField];

        var $Operator = $(".condition-operator", $e.closest(".condition"));
        var eOperator = $Operator.get(0);

        var $ConditionValueSpan = $(".condition-value-span", $e.closest(".condition"));
        var sConditionValueHtml = '';

        eOperator.options.length = 0;

        if (oField.optionType != 'null') {

            eOperator.add(new Option("等于", "="));

            sConditionValueHtml = '<select  name="conditionValue[]" class="condition-value">';
            for (var x in oField.option) {
                sConditionValueHtml += '<option value="'+ x +'">' + oField.option[x] + '</option>';
            }
            sConditionValueHtml += '</select>';

        } else {

            if (oField.isNumber) {
                eOperator.add(new Option("等于", "="));
                eOperator.add(new Option("大于", ">"));
                eOperator.add(new Option("大于等于", ">="));
                eOperator.add(new Option("小于", "<"));
                eOperator.add(new Option("小于等于", "<="));

                sConditionValueHtml = '<input type="text" name="conditionValue[]" value="" class="condition-value" />';
            } else {
                switch(oField.type) {
                    case "varchar" :
                        eOperator.add(new Option("等于", "="));
                        eOperator.add(new Option("包含", "like"));
                        eOperator.add(new Option("以...开头", "like1"));
                        eOperator.add(new Option("以...结尾", "like2"));
                        break;
                }

                sConditionValueHtml = '<input type="text" name="conditionValue[]" value="" class="condition-value" />';
            }
        }

        $ConditionValueSpan.html(sConditionValueHtml);

    }


    function getHeight() {
        return $(window).height()-140;
    }

    function resizeTable() {
        $("#table").bootstrapTable('resetView', {
            height: getHeight()
        });
    }



    $(function(){
        addCondition();

        $("#table").bootstrapTable({
            height: getHeight()
        });

        $(window).resize(function () {
            resizeTable();
        });

        $('#btn_search').click(function(){  // 点击查询
            $("#table").bootstrapTable('refresh',{query: {offset: 0}});
        });

        $(".search-form").submit(function () {
            $('#btn_search').trigger("click");
            return false;
        });
    });

    function loadData(params) {
        $.extend(params.data, $(".search-form").serializeObject());
        $.post(location.href, params.data,function(result){
            params.success(result);
            resizeTable();
        });
    }


    function operateFormatter(value, row, index) {
        <?php
        $actions = '';
        foreach ($this->config['lists']['action'] as $key => $val) {
            $actions .= '<a href="javascript:;" onclick="action(this, \' + row.'.$primaryKey.' + \')" data-action="'.$key.'">'. $val .'</a> ';
        }
        echo 'return \''.$actions.'\';';
        ?>
    }

    function action(e, id) {
        var $e = $(e);
        var sUrl =  window.location.href + "?task=" + $e.data("action") + "&<?php echo $primaryKey; ?>=" + id;

        window.location.href = sUrl;
    }


    function exportData() {
        var $this = $(this);

        var eForm = document.createElement("form");
        eForm.action = window.location.href + "?task=export";
        eForm.target = "_blank";
        eForm.method = "post";
        eForm.style.display = "none";
        var params = $(".search-form").serializeObject();

        for (var x in params) {
            var e = document.createElement("input");
            e.name = x;
            e.value = params[x];
            eForm.appendChild(e);
        }

        document.body.appendChild(eForm);
        eForm.submit();

        setTimeout(function () {
            $(eForm).remove();
        }, 3000);
        return false;
    }


</script>


<script type="text/html" id="condition-template">
    <div class="condition">
        <select name="conditionField[]" onchange="changeConditionField(this)" class="condition-field">
            <?php
            foreach ($fields as $field) {
                if ($field['disable']) continue;
                ?>
                <option value="<?php echo $field['field']; ?>"><?php echo $field['name']; ?></option>
                <?php
            }
            ?>
        </select>
        <select name="conditionOperator[]" class="condition-operator">

        </select>
        <span class="condition-value-span"></span>
        <a href="javascript:void()" onclick="$(this).closest('.condition').remove();" style="color:red; font-size: 24px; padding: 0 10px;">&times;</a>

    </div>
</script>


<!--{/head}-->


<!--{body}-->
<?php
$primaryKey = $this->table->getPrimaryKey();
$fields = $this->table->getFields();
?>


<div class="panel panel-default">

    <div class="panel-heading">
        <h5 class="panel-title"><?php echo $this->title; ?></h5>
    </div>


    <div class="panel-body">

        <form class="search-form" method="POST">

            <table>
                <tbody>
                <tr>
                    <td>
                        <div class="search-form">
                            <div id="conditions"></div>

                        </div>
                        <input type="button" value="+增加查询条件" class="btn btn-xs btn-info" onclick="addCondition();">

                    </td>

                    <td class="text-right">
                        <div class="btn btn-sm btn-success" id="btn_search"> <i class="fa fa-search"></i> 查询 </div>
                    </td>

                    <td class="text-right">
                        <a href="?task=setting" class="btn btn-sm"> <i class="fa fa-wrench"></i> 配置 </a>
                    </td>

                    <td class="text-right">
                        <a href="?task=chartPie" class="btn btn-sm"> <i class="fa fa-bar-chart"></i> 走势图 </a>
                    </td>

                    <td class="text-right">
                        <a href="?task=chartPie" class="btn btn-sm" > <i class="fa fa-pie-chart"></i> 饼图 </a>
                    </td>

                </tr>

                </tbody>

            </table>
        </form>




        <form id="data_form" action="" method="POST">


            <div id="toolbar">

                <?php
                foreach ($this->config['lists']['toolbar'] as $key => $val) {
                    if ($key == 'create') {
                        ?>
                        <a class="btn btn-sm btn-success" href="?task=<?php echo $key; ?>"><i class="fa fa-plus"></i> <?php echo $val; ?></a>
                        <?php
                        continue;
                    }

                    if ($key == 'export') {
                        ?>
                        <a class="btn btn-sm btn-info" href="javascript:;" onclick="exportData();"><i class="fa fa-download"></i> <?php echo $val; ?></a>
                        <?php
                        continue;
                    }

                    ?>
                    <a class="btn btn-sm btn-primary"  href="?action=<?php echo $key; ?>"><?php echo $val; ?></a>
                    <?php
                }
                ?>
            </div>

            <table id="table"
                   data-ajax="loadData"
                   data-toolbar="#toolbar"
                   data-id-field="<?php echo $primaryKey; ?>"
                   data-select-item-name="<?php echo $primaryKey; ?>[]"
                   data-sort-name="<?php echo $primaryKey; ?>"
                   data-sort-order="desc"
                   data-show-refresh="true"
                   data-show-toggle="true"
                   data-show-columns="true"
                   data-cookie="true"
                   data-cookie-id-table="M-<?php echo $this->table->getTableName(); ?>"
                   data-side-pagination="server"
                   data-stickyHeader="true"
                   data-stickyHeaderOffsetY="0"
                   data-pagination="true"
                   data-cache="false"
                   data-page-size="15"
                   data-page-list="[10,15,25,50,100]"
            >
                <thead>
                <tr>
                    <th data-align="center" data-field="operate" data-formatter="operateFormatter">操作</th>
                    <?php
                    foreach ($fields as $field) {
                        if ($field['disable']) continue;
                        ?>
                        <th data-align="center" data-sortable="true" data-field="<?php echo $field['field']; ?>" <?php echo $field['show']?'':' data-visible="false"'; ?>><?php echo $field['name']; ?></th>
                        <?php
                    }
                    ?>
                </tr>
                </thead>
            </table>
        </form>

    </div>
</div>


<!--{/body}-->
