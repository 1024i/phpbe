<!--{head}-->
<script>
    setInterval(function () {
        $.ajax({
            url : window.location.href,
            dataType : "json",
            success : function (json) {
                if (json.success) {
                    var aTasks = json.data;
                    var oTask = null;
                    for (var i=0; i< aTasks.length; i++) {
                        oTask = aTasks[i];
                        var $tr = $("#row-"+oTask.taskId);
                        $(".progress-bar", $tr).css("width", (oTask.progress > 0 ?  oTask.progress : 1) + "%");
                        $(".progress-text", $tr).html(oTask.progress + "%");
                        $(".size", $tr).html(oTask.size);
                        $(".complete-time", $tr).html(oTask.completeTime);
                        $(".execute-time", $tr).html(oTask.executeTime);

                        if (oTask.progress == 100) {
                            $tr.addClass("success");
                            $(".progress-bar", $tr).addClass("progress-bar-success");
                            $(".operation .btn", $tr).removeClass("disabled");
                        }
                    }
                } else {
                    window.location.reload();
                }
            },
            error: function () {
                window.location.reload();
            }
        });

    }, 5000);

    function deleteTask(e) {
        if (!confirm("确认删除么？")) return false;

        var $e = $(e);
        $e.prop("disabled", true).val("删除中...");

        $.ajax({
            url : $e.attr("href"),
            dataType : "json",
            success : function (json) {
                if (json.success) {
                    if ($("tr", $e.closest("tbody")).length == 1) {
                        window.location.reload();
                    }

                    $e.closest("tr").remove();
                }
            },
            error: function () {
                alert("系统错误！");
            }
        });
        return false;
    }

</script>
<!--{/head}-->


<!--{body}-->
<div class="panel panel-default">

    <div class="panel-heading">
        <h5 class="panel-title inline"><?php echo $this->title; ?></h5>
        <div class="pull-right"><a href="<?php echo $this->return; ?>" class="btn btn-xs btn-primary">返回</a></div>
    </div>

    <div class="panel-body">

        <table class="table table-hover">

            <thead>
                <tr>
                    <th class="text-center">任务ID</th>
                    <th class="text-center">名称</th>
                    <th class="text-center">进度</th>
                    <th class="text-left"></th>
                    <th class="text-center">是否出错</th>
                    <th class="text-center">文件大小</th>
                    <th class="text-center">创建时间</th>
                    <th class="text-center">结束时间</th>
                    <th class="text-center">耗时</th>
                    <th class="text-center">操作</th>
                </tr>

            </thead>

            <tbody>

                <?php
                if (isset($this->tasks) && is_array($this->tasks) && count($this->tasks) > 0) {
                    foreach ($this->tasks as $task) {
                        $class = '';
                        if ($task['error'] != '-') {
                            $class = ' class="error"';
                        } else {
                            if ($task['progress']==100) {
                                $class = ' class="success"';
                            }
                        }
                        ?>
                        <tr id="row-<?php echo $task['taskId']; ?>"<?php echo $class; ?>>
                            <td class="text-center"><?php echo $task['taskId']; ?></td>
                            <td class="text-center"><?php echo $task['name']; ?></td>
                            <td class="text-center" style="width: 160px;">
                                <div class="progress" style="margin-bottom: 0;">
                                    <div class="progress-bar<?php echo $task['progress']==100?' progress-bar-success':''; ?>" role="progressbar" aria-valuenow="60"
                                         aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $task['progress'] > 0 ? $task['progress'] : 1; ?>%;">
                                        <span class="sr-only"><?php echo $task['progress']; ?>%</span>
                                    </div>
                                </div>
                            </td>
                            <td class="text-left progress-text">
                                <?php echo $task['progress']; ?>%
                            </td>
                            <td class="text-center"><?php echo $task['error']; ?></td>
                            <td class="text-center size">
                                <?php echo $task['size']; ?>
                            </td>
                            <td class="text-center"><?php echo $task['createTime']; ?></td>
                            <td class="text-center complete-time"><?php echo $task['completeTime']; ?></td>
                            <td class="text-center execute-time"><?php echo $task['executeTime']; ?></td>
                            <td class="text-center operation">
                                <a href="<?php echo \Haitun\Service\M\Util\Url::encode('exportTaskDetail', array('taskId'=> $task['taskId'])); ?>" class="btn btn-xs btn-info">查看</a>
                                <a href="<?php echo \Haitun\Service\M\Util\Url::encode('exportTaskDownload', array('taskId'=> $task['taskId'])); ?>" class="btn btn-xs btn-success<?php if ($task['progress'] < 100) { echo ' disabled'; } ?>" target="_blank">下载</a>
                                <a href="<?php echo \Haitun\Service\M\Util\Url::encode('exportTaskDelete', array('taskId'=> $task['taskId'])); ?>" class="btn btn-xs btn-danger<?php if ($task['progress'] < 100 && (time() - strtotime($task['createTime']) < 86400) ) { echo ' disabled'; } ?>" onclick="return deleteTask(this);">删除</a>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr><td colspan="5" class="text-center">暂无记录</td></tr>
                    <?php
                }
                ?>

            </tbody>
        </table>

    </div>
</div>

<!--{/body}-->
