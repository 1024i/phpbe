<!--{head}-->
<?php
$error_log = $this->error_log;
if ($error_log['data_length'] < 100 * 1024) {
    ?>
    <link rel="stylesheet" href="template/system/google-code-prettify/prettify.css" type="text/css"/>
    <script type="text/javascript" language="javascript"
            src="template/system/google-code-prettify/prettify.js"></script>
    <script type="text/javascript">
        $().ready(function () {
            prettyPrint();
        });
    </script>
    <?php
}
?>
<!--{/head}-->

<!--{center}-->
<?php
$error_log = $this->get('error_log');
?>
<ul class="nav nav-tabs">
    <li class="active">
        <a href="#tab-base" data-toggle="tab"><span>基本信息</span></a>
    </li>
    <li>
        <a href="#tab-trace" data-toggle="tab"><span>跟踪信息</span></a>
    </li>
    <li>
        <a href="#tab-get" data-toggle="tab"><span>$_GET</span></a>
    </li>
    <li>
        <a href="#tab-post" data-toggle="tab"><span>$_POST</span></a>
    </li>
    <li>
        <a href="#tab-request" data-toggle="tab"><span>$_REQUEST</span></a>
    </li>
    <li>
        <a href="#tab-session" data-toggle="tab"><span>$_SESSION</span></a>
    </li>
    <li>
        <a href="#tab-cookie" data-toggle="tab"><span>$_COOKIE</span></a>
    </li>
    <li>
        <a href="#tab-server" data-toggle="tab"><span>$_SERVER</span></a>
    </li>
</ul>

<div class="tab-content" style="padding: 0 10px">
    <div class="tab-pane active" id="tab-base">
        类型：<?php echo $error_log['type']; ?><br />
        错误码：<?php echo $error_log['code']; ?><br />
        错误信息：<?php echo $error_log['message']; ?><br />
        文件：<?php echo $error_log['file']; ?><br />
        行号：<?php echo $error_log['line']; ?><br />
        时间：<?php echo date('Y-m-d H:i:s', $error_log['time']); ?>
    </div>
    <div class="tab-pane" id="tab-trace">
        <pre class="prettyprint" style="background-color: #fff;color:#000;white-space: pre-wrap;word-wrap: break-word;"><?php print_r($error_log['trace']); ?></pre>
    </div>
    <div class="tab-pane" id="tab-get">
        <pre class="prettyprint" style="background-color: #fff;color:#000;white-space: pre-wrap;word-wrap: break-word;"><?php print_r($error_log['GET']); ?></pre>
    </div>
    <div class="tab-pane" id="tab-post">
        <pre class="prettyprint" style="background-color: #fff;color:#000;white-space: pre-wrap;word-wrap: break-word;"><?php print_r($error_log['POST']); ?></pre>
    </div>
    <div class="tab-pane" id="tab-request">
        <pre class="prettyprint" style="background-color: #fff;color:#000;white-space: pre-wrap;word-wrap: break-word;"><?php print_r($error_log['REQUEST']); ?></pre>
    </div>
    <div class="tab-pane" id="tab-session">
        <pre class="prettyprint" style="background-color: #fff;color:#000;white-space: pre-wrap;word-wrap: break-word;"><?php print_r($error_log['SESSION']); ?></pre>
    </div>
    <div class="tab-pane" id="tab-cookie">
        <pre class="prettyprint" style="background-color: #fff;color:#000;white-space: pre-wrap;word-wrap: break-word;"><?php print_r($error_log['COOKIE']); ?></pre>
    </div>
    <div class="tab-pane" id="tab-server">
        <pre class="prettyprint" style="background-color: #fff;color:#000;white-space: pre-wrap;word-wrap: break-word;"><?php print_r($error_log['SERVER']); ?></pre>
    </div>
</div>
<!--{/center}-->