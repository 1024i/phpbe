<!--{head}-->
<script>
    var timer = setInterval(function () {

        $.ajax({
            url : window.location.href,
            dataType : "json",
            success : function (json) {
                if (json.success) {
                    var oTask = json.data;

                    $(".progress-bar").css("width", (oTask.progress > 0 ?  oTask.progress : 1) + "%");
                    $(".progress-text").html(oTask.progress + "%");
                    $(".size").html(oTask.size);
                    $(".complete-time", $tr).html(oTask.completeTime);
                    $(".execute-time", $tr).html(oTask.executeTime);

                    if (oTask.progress == 100) {
                        $(".progress-bar").addClass("progress-bar-success");
                        $(".operation .btn").removeClass("disabled");
                        clearInterval(timer);
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
                    window.location.href = "<?php echo \Haitun\Service\M\Util\Url::encode('exportTasks'); ?>";
                }
            },
            error: function () {
                alert("系统错误！");
            }
        });
        return false;
    }

    function toggleNext(e) {
        var $e = $(e);
        var $next = $e.next();
        if ($next.hasClass("hidden")) {
            $next.removeClass("hidden");
            $e.html("- 隐藏");
        } else {
            $next.addClass("hidden");
            $e.html("+ 显示");
        }
    }
</script>
<!--{/head}-->


<!--{body}-->
<div class="panel panel-default">

    <div class="panel-heading">
        <h5 class="panel-title inline"><?php echo $this->title; ?></h5>
        <div class="pull-right"><a href="<?php echo \Haitun\Service\M\Util\Url::encode('exportTasks'); ?>" class="btn btn-xs btn-primary">返回</a></div>
    </div>

    <div class="panel-body">

        <table class="table table-hover">

            <tbody>
                <tr>
                    <td class="text-right">任务ID</td>
                    <td class="text-left"><?php echo $this->task['taskId']; ?></td>
                </tr>
                <tr>
                    <td class="text-right">名称</td>
                    <td class="text-left"><?php echo $this->task['name']; ?></td>
                </tr>
                <tr>
                    <th class="text-right">进度</th>
                    <td class="text-left">
                        <div class="progress" style="margin-bottom: 0;width: 320px;">
                            <div class="progress-bar<?php echo $this->task['progress']==100?' progress-bar-success':''; ?>" role="progressbar" aria-valuenow="60"
                                 aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $this->task['progress'] > 0 ? $this->task['progress'] : 1; ?>%;">
                                <span class="sr-only"><?php echo $this->task['progress']; ?>%</span>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="text-right">进度</th>
                    <td class="text-left progress-text">
                        <?php echo $this->task['progress']; ?>%
                    </td>
                </tr>
                <?php if ($this->task['error'] != '-') { ?>
                <tr>
                    <td class="text-right">错误</td>
                    <td class="text-left">
                        <a href="javascript:;" onclick="toggleNext(this);">+ 显示</a>
                        <pre class="hidden"><?php echo $this->task['errorDetails']; ?></pre>
                    </td>
                </tr>
                <?php } ?>
                <tr>
                    <td class="text-right">文件大小</td>
                    <td class="text-left"><?php echo $this->task['size']; ?></td>
                </tr>
                <tr>
                    <td class="text-right">创建时间</td>
                    <td class="text-left"><?php echo $this->task['createTime']; ?></td>
                </tr>
                <tr>
                    <td class="text-right">结束时间</td>
                    <td class="text-left"><?php echo $this->task['completeTime']; ?></td>
                </tr>
                <tr>
                    <td class="text-right">耗时</td>
                    <td class="text-left"><?php echo $this->task['executeTime']; ?></td>
                </tr>
                <tr>
                    <td class="text-right">查询条件</td>
                    <td class="text-left">
                        <a href="javascript:;" onclick="toggleNext(this);">+ 显示</a>
                        <pre class="hidden"><?php print_r($this->task['condition']); ?></pre>
                    </td>
                </tr>

                <tr>
                    <td class="text-right">下载</td>
                    <td class="text-left operation">
                        <a href="<?php echo \Haitun\Service\M\Util\Url::encode('exportTaskDownload', array('taskId'=> $this->task['taskId'])); ?>" class="btn btn-xs btn-success<?php if ($this->task['progress'] < 100) { echo ' disabled'; } ?>" target="_blank">
                            下载
                        </a>
                    </td>
                </tr>
                <tr>
                    <td class="text-right">删除</td>
                    <td class="text-left operation">
                        <a href="<?php echo \Haitun\Service\M\Util\Url::encode('exportTaskDelete', array('taskId'=> $this->task['taskId'])); ?>" class="btn btn-xs btn-danger<?php if ($this->task['progress'] < 100 && (time() - strtotime($this->task['createTime']) < 86400) ) { echo ' disabled'; } ?>" onclick="return deleteTask(this);">删除</a>
                    </td>
                </tr>
                <tr>
                    <td class="text-right">重新启动</td>
                    <td class="text-left">
                        <a onclick="return confirm('重复启动可能会造成数据重复，确认重新启动么？');" href="<?php echo \Haitun\Service\M\Util\Url::encode('exportTaskRun', array('taskId'=> $this->task['taskId'])); ?>" target="_blank" class="btn btn-xs btn-warning">
                            重新启动
                        </a>
                        确认任务已停止后，通过该网址重新启动
                    </td>
                </tr>
            </tbody>

        </table>

    </div>
</div>

<!--{/body}-->
