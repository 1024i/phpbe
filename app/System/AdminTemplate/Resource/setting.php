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

    function toggle(e) {
        var $e = $(e);
        $e.prev().val($e.prop('checked') ? 1 : 0);

        updateToggle();
    }

    function updateToggle() {
        $(".toggle-disable").each(function () {
            var $e = $(this);
            if ($e.prop('checked')) {
                $(".toggle-show", $e.closest("tr")).prop("checked", false).prop("disabled", true).prev().val(0);
                $(".toggle-editable", $e.closest("tr")).prop("checked", false).prop("disabled", true).prev().val(0);
            } else {
                $(".toggle-show", $e.closest("tr")).prop("disabled", false);
                $(".toggle-editable", $e.closest("tr")).prop("disabled", false);
            }
        });
    }

    $(function () {
        updateToggle();
    })
</script>
<!--{/head}-->

<!--{body}-->

<div class="panel panel-default">

    <div class="panel-heading">
        <h5 class="panel-title"><?php echo $this->title; ?></h5>
    </div>


    <div class="panel-body">

        <form>

            <?php
            $fields = $this->table->getFields();
            $primaryKey = $this->table->getPrimaryKey();
            ?>
            <table class="table table-hover">

                <thead>
                <tr>
                    <th>字段名</th>
                    <th>中文名称</th>
                    <th>注释</th>
                    <th>类型</th>
                    <th>类型长度</th>
                    <th>附加属性</th>
                    <th>默认值</th>
                    <th>可选项</th>
                    <th>格式</th>
                    <th>禁用？</th>
                    <th>列表默认展示</th>
                    <th>编辑</th>
                    <th>创建</th>
                </tr>

                </thead>

                <tbody>
                <?php
                foreach ($fields as $field) {
                    ?>
                    <tr>
                        <td><?php echo $field['field']; ?></td>
                        <td>
                            <input type="hidden" name="field[]" value="<?php echo $field['field']; ?>">
                            <input type="text" name="name[]" value="<?php echo $field['name']; ?>">
                            <input type="button" onclick="$(this).prev().val('<?php echo $field['comment']; ?>')"
                                   class="btn btn-xs btn-info" value="取注释内容"/>
                        </td>
                        <td><?php echo $field['comment']; ?></td>
                        <td><?php echo $field['type']; ?></td>
                        <td><?php echo $field['typeLength']; ?></td>
                        <td><?php echo $field['extra']; ?></td>
                        <td><?php echo $field['default']; ?></td>
                        <td>
                            <div>
                                <select name="optionType[]">
                                    <option value="null" <?php echo $field['option']->getType() == 'null' ? 'selected' : '' ?>>
                                        无
                                    </option>
                                    <option value="array" <?php echo $field['option']->getType() == 'array' ? 'selected' : '' ?>>
                                        数组
                                    </option>
                                    <option value="sql" <?php echo $field['option']->getType() == 'sql' ? 'selected' : '' ?>>
                                        SQL
                                    </option>
                                </select>
                            </div>
                            <div><textarea name="optionData[]"><?php echo $field['option']->getData(); ?></textarea>
                            </div>
                        </td>
                        <td>
                            <select name="format[]">

                                <?php
                                foreach (array(
                                             '' => '无',
                                             'number' => '数字',
                                             'date(Ymd)' => '日期(Y-m-d)',
                                             'date(YmdHi)' => '日期(Y-m-d H:i)',
                                             'date(YmdHis)' => '日期(Y-m-d H:i:s)',
                                             'email' => '邮箱',
                                             'url' => '网址',
                                             'mobile' => '手机号',
                                             'idCard' => '身份证号',
                                         ) as $key => $val) {
                                    echo '<option value="' . $key . '"' . ($field['format'] == $key ? ' selected' : '') . '>' . $val . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                        <td>
                            <input type="hidden" name="disable[]" value="<?php echo $field['disable'] ? 1 : 0; ?>">
                            <input type="checkbox"
                                   class="toggle-disable" <?php echo $field['disable'] ? 'checked' : ''; ?>
                                   onchange="toggle(this);"/>
                        </td>
                        <td>
                            <input type="hidden" name="show[]" value="<?php echo $field['show'] ? 1 : 0; ?>">
                            <input type="checkbox" class="toggle-show" <?php echo $field['show'] ? 'checked' : ''; ?>
                                   onchange="toggle(this);"/>
                        </td>
                        <td>
                            <input type="hidden" name="editable[]" value="<?php echo $field['editable'] ? 1 : 0; ?>">
                            <input type="checkbox"
                                   class="toggle-editable" <?php echo $field['editable'] ? 'checked' : ''; ?>
                                   onchange="toggle(this);"/>
                        </td>
                        <td>
                            <input type="hidden" name="create[]" value="<?php echo $field['create'] ? 1 : 0; ?>">
                            <input type="checkbox"
                                   class="toggle-create" <?php echo $field['create'] ? 'checked' : ''; ?>
                                   onchange="toggle(this);"/>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>

            </table>

            <input type="button" value="保存" class="btn btn-primary" onclick="save(this);"/>
            <input type="button" value="返回" class="btn btn-info" onclick="history.go(-1);"/>

        </form>
    </div>
</div>
<!--{/body}-->
