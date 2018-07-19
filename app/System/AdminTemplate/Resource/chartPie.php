
<!--{head}-->

<!--{/head}-->

<!--{body}-->

<div class="panel panel-default">

    <div class="panel-heading">
        <h5 class="panel-title"><?php echo $this->title; ?></h5>
    </div>


    <div class="panel-body">

        <?php
        $fields = $this->table->getFields();

        $aggField = $this->aggField;
        $aggLimit = $this->aggLimit;
        ?>
        <form method="get" style="text-align: center; padding-bottom: 20px;">
            <input type="hidden" name="task" value="chartPie">
            聚合字段：
            <select name="aggField" onchange="$(this).closest('form').submit();">
                <option value=""> - 请选择 - </option>
                <?php
                foreach ($fields as $field) {
                    ?>
                    <option value="<?php echo $field['field']; ?>" <?php echo $field['field'] == $aggField ? 'selected' : ''; ?>><?php echo $field['name']; ?></option>
                    <?php
                }
                ?>
            </select>
            结果集前：
            <select name="limit" onchange="$(this).closest('form').submit();">
                <option value=""> - 请选择 - </option>
                <option value="5" <?php echo 5 == $aggLimit ? 'selected' : ''; ?>>5</option>
                <option value="10" <?php echo 10 == $aggLimit ? 'selected' : ''; ?>>10</option>
                <option value="15" <?php echo 15 == $aggLimit ? 'selected' : ''; ?>>15</option>
                <option value="20" <?php echo 20 == $aggLimit ? 'selected' : ''; ?>>20</option>
                <option value="25" <?php echo 25 == $aggLimit ? 'selected' : ''; ?>>25</option>
                <option value="30" <?php echo 30 == $aggLimit ? 'selected' : ''; ?>>30</option>
                <option value="40" <?php echo 40 == $aggLimit ? 'selected' : ''; ?>>40</option>
                <option value="50" <?php echo 50 == $aggLimit ? 'selected' : ''; ?>>50</option>
                <option value="60" <?php echo 60 == $aggLimit ? 'selected' : ''; ?>>60</option>
                <option value="80" <?php echo 80 == $aggLimit ? 'selected' : ''; ?>>80</option>
                <option value="100" <?php echo 100 == $aggLimit ? 'selected' : ''; ?>>100</option>
            </select> 位
        </form>

        <?php
        if (!$aggField) return;


        $field = $this->table->getField($aggField);
        ?>


        <!-- 为ECharts准备一个具备大小（宽高）的Dom -->
        <div id="main" style="width:100%; height:600px"></div>

        <!-- ECharts单文件引入 -->
        <script src="http://echarts.baidu.com/build/dist/echarts.js"></script>

        <script type="text/javascript">
            // 路径配置
            require.config({
                paths: {
                    echarts: 'http://echarts.baidu.com/build/dist'
                }
            });

            // 使用
            require(
                [
                    'echarts',
                    'echarts/chart/pie' // 使用柱状图就加载bar模块，按需加载
                ],
                function (ec) {
                    // 基于准备好的dom，初始化echarts图表
                    var myChart = ec.init(document.getElementById('main'));

                    var option = {
                        title : {
                            text: '<?php echo $field['name']; ?>分布',
                            x:'center'
                        },
                        tooltip : {
                            trigger: 'item',
                            formatter: "{a} <br/>{b} : {c} ({d}%)"
                        },
                        legend: {
                            orient : 'vertical',
                            x : 'left',
                            data:[
                                <?php
                                $i = 0;
                                $n = count($this->aggData);
                                foreach ($this->aggData as $aggData)
                                {
                                    $i++;

                                    if ($field['optionType'] != null) {
                                        $keyValues = $field['option']->getKeyValues();
                                        if (isset($keyValues[$aggData->$aggField])) {
                                            $aggData->$aggField = $keyValues[$aggData->$aggField];
                                        }
                                    }

                                    if ($aggData->$aggField == '') $aggData->$aggField = ' ';
                                    echo '"' . $aggData->$aggField . '"';

                                    if ($i<$n) echo ',';
                                }
                                ?>
                            ]
                        },
                        toolbox: {
                            show : true,
                            feature : {
                                mark : {show: true},
                                dataView : {show: true, readOnly: false},
                                magicType : {
                                    show: true,
                                    type: ['pie', 'funnel'],
                                    option: {
                                        funnel: {
                                            x: '25%',
                                            width: '50%',
                                            funnelAlign: 'left',
                                            max: 1548
                                        }
                                    }
                                },
                                restore : {show: true},
                                saveAsImage : {show: true}
                            }
                        },
                        calculable : true,
                        series : [
                            {
                                name:"<?php echo $field['name']; ?>",
                                type:"pie",
                                radius : '55%',
                                center: ['50%', '60%'],
                                data:[
                                    <?php
                                    $i = 0;
                                    $n = count($this->aggData);
                                    foreach ($this->aggData as $aggData) {
                                    ?>
                                    {
                                        value : "<?php echo $aggData->quantity; ?>",
                                        name : "<?php echo $aggData->$aggField; ?>"
                                    }
                                    <?php
                                    $i++;
                                    if ($i<$n) echo ',';
                                    }
                                    ?>

                                ]
                            }
                        ]
                    };

                    myChart.setOption(option);
                }
            );

        </script>





    </div>
</div>

<!--{/body}-->


