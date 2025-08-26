<main class="py-4">
    <div class="personContainer">
        <div class="card-body">
            <h2 class="pageTitle"> <b class="h4">&#10148; Estatísticas &#10148; <?php echo "$nomegr";?></b></h2>
            <h4><?php echo $pagetitle;?> - <i class="mesinfo"><?php echo $mesinfo;?></i></h4>
            <hr>

            <script type="text/javascript">

                window.onload = function(e){

                    setActiveMenu('/configuracoes/totem/');
                    
                    <?php if(count($grafico) !== 0):?>

                        google.charts.load('current', {'packages':['corechart']});
                        google.charts.setOnLoadCallback(drawChart);

                        function drawChart() {
                            var data = new google.visualization.DataTable();
                            data.addColumn('string', 'Mês/Ano');
                            data.addColumn('number', '<?php echo $pagetitle;?>');
                            data.addColumn({ role: 'annotation' });
                            data.addColumn({ role: 'style' });

                            data.addRows([

                                <?php
                                    foreach($grafico as $gr){
                                        echo '["'.$gr[0].'",'.$gr[1].',"'.$gr[1].'", "#252633"],';
                                    }
                                ?>
                            ]);

                            var options = {
                            
                                curveType: 'function',
                                legend: 'none',
                                vAxis: {
                                    textStyle: {
                                        fontSize: 14,
                                        bold: false,
                                        italic: true,
                                        color: '#252633',
                                        auraColor: '#fff'
                                    }
                                },

                                hAxis: {

                                    minValue: 0,
                                    format: 'short',
                                    gridlines: { count: 5 },

                                    textStyle: {
                                        fontSize: 14,
                                        bold: false,
                                        italic: true,
                                        color: '#252633',
                                        auraColor: '#fff'
                                    }
                                },

                                annotations: {
                                    textStyle: {
                                        fontSize: 14,
                                        bold: true,
                                        italic: true,
                                        color: '#fff'
                                    }
                                },

                                bar: {
                                    groupWidth: 10
                                }
                           
                            };

                            var chart = new google.visualization.BarChart(document.getElementById('chart_div'));
                            chart.draw(data, options);
                        }

                        window.onresize = drawChart;

                    <?php endif;?>
                }

            </script>
            <?php if(count($grafico) == 0):?>
                <b>Sem dados com os filtros aplicados.</b>
            <?php else:?>
                <?php $b = count($grafico);?>
                <div id="chart_div" style="height: <?php echo $b>29?'1000':($b>23?'800': ($b>19?'600': ($b>15?'500': ($b>10?'400': ($b>5?'300':'200')))));?>px;" class="chartInAcApp"></div>
            <?php endif;?>
            
        </div>
    </div>
    <div class="filtroDivNew">
            <div class="filtrosBtn">
                <div>
                    <i class="fa fa-filter" aria-hidden="true"></i><b>FILTROS</b>
                </div>
            </div>
            <form action="" method="get" class="form-horizontal form-label-left w-100" name="getStatistics" id="getStatistics">
                <input type="hidden" name="groupId"  id="groupId" value="<?php echo $groupId ?>">
                <input type="hidden" name="nomegr" id="nomegr" value="<?php echo $nomegr ?>">
                <input type="hidden" name="codigo" id="codigo"  value="<?php echo $codigo ?>">
                <input type="hidden" name="qrcode" id="qrcode" value="<?php echo $qrcode ?>"> 
                <div id="boxContFilter">
                    
                    <div>

                        <div>
                            <label for="start" class="form-label">Mês In&iacute;cio:</label>
                            <input class="form-control" name="start" type="month" value="<?php echo $start; ?>" id="start" min="<?php echo $minStart; ?>" max="<?php echo $maxEnd; ?>">
                        </div>

                    </div>
                    <div>

                        <div>
                            <label for="end" class="form-label">Mês Fim:</label>
                            <input class="form-control" name="end" type="month" value="<?php echo $end; ?>" id="end" min="<?php echo $minStart; ?>" max="<?php echo $maxEnd; ?>">
                        </div>

                    </div>

                </div>

                <div class="btsFiltro">
                    <button title="Baixar Excel" type="button" class="btn btn-success btnRelatorio btnExcel" onclick="statisticsTotemExcel()"><i class="fas fa-file-excel"></i></button>
                    <button onClick="getStatisticsApp()" title="Buscar" type="button" class="btn btn-warning btnRelatorio"> <i class="fa fa-search"></i></button>
                </div>
            </form>
            
        </div>
</main>
</div>