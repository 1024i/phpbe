<!--{head}-->
<script>
    function save(e) {
        var $e = $(e);
        var $form = $e.closest("form");
        var sValue = $e.val();
        $e.val("处理中...").prop("disabled", true);

        $.ajax({
            url: window.location.href,
            method: "post",
            data: $form.serialize(),
            dataType: "json",
            success: function (json) {
                $e.val(sValue).prop("disabled", false);
                alert(json.message);
                if (json.success) {
                    window.location.href = "<?php echo $_SERVER['HTTP_REFERER']?>";
                }
            },
            error: function () {
                $e.val(sValue).prop("disabled", false);
                alert("系统错误！");
            }
        });
    }
</script>
<!--{/head}-->


<!--{body}-->

<div class="panel panel-default">

    <div class="panel-heading">
        <h5 class="panel-title"><?php echo $this->title; ?></h5>
    </div>

    <div class="panel-body">

        <form class="form-horizontal" >
            <?php
            $primaryKey = $this->row->getPrimaryKey();
            $fields = $this->row->getFields();
            foreach ($fields as $field) {
                if ($field['disable']) continue;

                $f = $field['field'];
                ?>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo $field['name']; ?>：</label>
                    <div class="col-sm-8">
                        <?php
                        if ($field['editable']) {

                            if ($field['optionType'] != 'null') {

                                $keyValues = $field['option']->getKeyValues();
                                ?>
                                <select name="<?php echo $field['field']; ?>">
                                    <option value="<?php echo $field['default']; ?>">请选择</option>
                                    <?php
                                    foreach ($keyValues as $key => $val) {
                                        ?>
                                        <option value="<?php echo $key; ?>" <?php echo $key == $this->row->$f ? 'selected' : ''; ?>><?php echo $val; ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>

                                <?php

                            } else {
                                ?>
                                <input name="<?php echo $field['field']; ?>" class="form-control" type="text" value="<?php echo $this->row->$f; ?>">
                                <?php
                            }
                            ?>
                            <?php
                        } else {
                            ?>
                            <p class="form-control-static">
                                <input name="<?php echo $field['field']; ?>" type="hidden" value="<?php echo $this->row->$f; ?>" />
                                <?php echo $this->row->$f; ?>
                            </p>
                            <?php
                        }
                        ?>

                    </div>
                </div>
                <?php
            }
            ?>
            <div class="form-group">
                <div class="col-sm-8 col-sm-offset-4">
                    <input type="button" value="保存" class="btn btn-primary" onclick="save(this);" />
                    <input type="button" value="返回" class="btn btn-info" onclick="history.go(-1);"/>
                </div>
            </div>
        </form>

    </div>

</div>
<!--{/body}-->
