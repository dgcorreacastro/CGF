<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"> 
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <title><?php echo $titulo; ?></title>

        <style>
            @page {
                margin: 0cm !important;
                padding: 0cm !important;
            }

            .boxShadow {
                box-shadow: rgba(0, 0, 0, 0.2) 0px -3px 0px inset;
            }

            body {
                margin: 0cm;
                padding: 0cm;
                font-family: Arial, Helvetica, sans-serif;
                box-sizing: border-box;
            }

            .logoCgf {
                position: absolute;
                height: 80px;
                margin-top: -20px;
            }

            .a4-page {
                width: 21cm;
                height: 29.7cm;
                padding: 1cm;
                margin: 1cm auto;
                border: 1px solid #000;
                box-shadow: 0 0 10px rgba(0,0,0,0.5);
                background: white;
            }

            .a4-page .titulo {
                width: 90%;
                position: relative;
                text-align: center;
                margin: 0 0 0 10%;
                padding: 0 .2em .5em .2em;
                min-height: 66px;
                white-space: nowrap;
                box-sizing: border-box;
                opacity: 0;
                transition: opacity 250ms;
            }

            .a4-page .titulo.show {
                opacity: 1;
            }

            .a4-page .titulo p {
                font-size: 22px;
                color: grey;
                margin: 0;
            }

            section {
                width: 100%;
                position: relative;
                display: flex;
                flex-direction: column;
                flex-wrap: nowrap;
                align-items: center;
                margin: 0 auto 1em auto;
            }

            section h4 {
                width: 100%;
                text-align: center;
                padding: 1em 0 .5em 0;
                margin: 0 auto .5em auto;
            }

            section .innerSection {
                display: grid;
                width: 100%;
                flex-direction: row;
                flex-wrap: nowrap;
                grid-template-columns: 1.5fr 2fr;
                gap: 2em;
                align-items: stretch;
                justify-items: stretch;
                justify-content: start;
                align-content: center;
            }

            section .tops {
                position: relative;
                display: flex;
                width: 100%;
                flex-direction: row;
                flex-wrap: nowrap;
                align-items: flex-start;
                justify-content: center;
                padding: 1em;
                gap: 1em;
                margin-top: 1em;
                font-size: 14px;
            }

            section .tops .top {
                position: relative;
                display: flex;
                width: 50%;
                border: 1px solid #000;
                border-radius: 10px;
                overflow: hidden;
                flex-direction: column;
                flex-wrap: wrap;
                align-items: stretch;
                justify-content: flex-start;
            }

            section .tops .top div {
                display: grid;
                width: 100%;
                grid-template-columns: 5fr 1fr;
                padding:0;
                box-sizing: border-box;
            }
            
            section .tops .top div span {
                padding: .5em;
            }

            section .tops .top div:not(:last-child) span {
                border-bottom: 1px solid #000;
            }

            section .tops .top div span:nth-child(2) {
                border-left: 1px solid #000;
                text-align: center;
            }

            section .tops div h4 {
                display: flex;
                margin: 0;
                padding: .5em;
                font-size: 14px;
                flex-direction: row;
                justify-content: center;
                align-items: center;
            }

            .legend {
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: flex-start;
                flex-wrap: wrap;
                gap: .6em;
                font-size: 14px;
            }

            .legend div {
                display: flex;
                margin: 0;
                flex-direction: row;
                flex-wrap: nowrap;
                align-items: center;
                justify-content: flex-start;
                gap: .2em;
            }

            .legend span {
                display: inline-block;
                width: 12px;
                height: 12px;
            }

            .trips {
                width: 100%;
                display: grid;
                grid-template-columns: repeat(8, auto);
                font-weight: bold;
                text-align: center;
                font-size: 14px;    
                position: relative;            
            }

            .trips .cabecalho {
                background-color: #105965;
                color: #ffffff;
                padding: 4px;
                border-bottom: 1px solid #ddd;
            }

            .trips .celula {
                background-color: #ffffff;
                color: #000000;
                padding: 4px;
                border-bottom: 1px solid #ddd; 
            }

            .trips .celula.border_left {
                border-left: 1px solid #105965; 
            }

            .trips .headGrupo {
                display: flex;
                grid-column: 1 / span 8;
                background-color: #17a2b8;
                color: #ffffff;
                font-weight: bold;
                padding: 8px 4px;
                position: relative;
                flex-direction: row;
                align-items: center;
                justify-content: flex-start;
            }

            .trips .headGrupo:not(:first-of-type){
                margin-top: .5em;
            }

            .trips .headGrupo .linePontual {
                position: absolute;
                padding: .3em;
                bottom:0;
                right: 0;
                font-size: 13px;
                z-index: 2;
            }

            #piechartPontual, #piechartRegistros, #barChartCard {
                position: relative;
                width: 100%; 
                height: 220px;
                overflow: hidden !important;
            }

            #barChartCard {
                height: 240px;
                margin-top: -2em;
            }

            .printBt {
                position: fixed;
                top: 1em;
                right: 1em;
                margin: auto;
                text-align: center;
                padding: .3em;
                font-size: 20px;
                background-color: #ffc107;
                z-index: 9999;
                cursor: pointer;
            }

            @media print {
                body {                                   
                    width: 100%;
                    height: 100%;
                    margin: 0;
                    padding: 0;
                }
                .a4-page {
                    margin: 0 auto !important;
                    border: none;
                    box-shadow: none;
                    page-break-after: always;
                }

                .printBt {
                    display: none !important;
                }
            }
        </style>

    </head>

    <body>
        <span class="printBt" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</span>
        <div class="a4-page">
            <img class="logoCgf" src="<?php echo BASE_URLB;?>assets/images/logoApp.png">
            <h2 class="titulo"><?php echo $titulo;?><p><?php echo $data;?></p></h2>
            <section style="border-top: 2px solid #ccc;">
                <?php if(isset($totalTrips) && $totalTrips > 0):?>
                    <h4>PONTUALIDADE DE CHEGADA DAS VIAGENS - <?php echo $totalTrips;?> Linha<?php echo $totalTrips > 1 ? "s":"";?></h4>
                    <div class="innerSection">
                        <div id="piechartPontual"></div>
                        <div class="legend">
                            <?php if(isset($pontual) && $pontual > 0):?>
                                <div><span class="boxShadow" style="background-color: <?php echo $graphParams[1]['bg'];?>"></span><?php echo $graphParams[1]['txt'];?>: <b><?php echo $percenPontual; ?>% (<?php echo $pontual;?>)</b></div>
                            <?php endif;?>
                            <?php if(isset($adiantado) && $adiantado > 0):?>
                                <div><span class="boxShadow" style="background-color: <?php echo $graphParams[2]['bg'];?>"></span><?php echo $graphParams[2]['txt'];?>: <b><?php echo $percenAdianta; ?>% (<?php echo $adiantado;?>)</b></div>
                            <?php endif;?>
                            <?php if(isset($atrasado) && $atrasado > 0):?>
                                <div><span class="boxShadow" style="background-color: <?php echo $graphParams[3]['bg'];?>"></span><?php echo $graphParams[3]['txt'];?>: <b><?php echo $percenAtrasa; ?>% (<?php echo $atrasado;?>)</b></div>
                            <?php endif;?>
                            <?php if(isset($nes) && $nes > 0):?>
                                <div><span class="boxShadow" style="background-color: <?php echo $graphParams[4]['bg'];?>"></span><?php echo $graphParams[4]['txt'];?>: <b><?php echo $percenNes; ?>% (<?php echo $nes;?>)</b></div>
                            <?php endif;?>
                        </div>
                    </div>
                    
                <?php else:?>
                    <h4>PONTUALIDADE DE CHEGADA DAS VIAGENS</h4>
                    <h5 style="color: #585858;font-weight: bold;font:normal 100%/150% arial,helvetica,sans-serif">SEM DADOS</h5>
                <?php endif;?>
            </section>
            <section style="border-top: 2px solid #ccc;">
                <?php if(isset($totalTrips) && $totalTrips > 0):?>
                    <h4>REGISTROS DE EMBARQUE</h4>
                    <div class="innerSection">
                        <div id="piechartRegistros"></div>
                        <div class="legend">
                            <?php if(isset($inUsePer) && $inUsePer > 0):?>
                                <div><span class="boxShadow" style="background-color: <?php echo $graphParams[5]['bg'];?>"></span><?php echo $graphParams[5]['txt'];?>: <b><?php echo $inUsePer; ?>%</b></div>
                            <?php endif;?>
                            <?php if(isset($noUsePer) && $noUsePer > 0):?>
                                <div><span class="boxShadow" style="background-color: <?php echo $graphParams[6]['bg'];?>"></span><?php echo $graphParams[6]['txt'];?>: <b><?php echo $noUsePer; ?>%</b></div>
                            <?php endif;?>
                        </div>
                    </div>
                    
                    <?php if(count($top5) > 0 || count($bottom5) > 0):?>
                        <div class="tops">
                            <?php if(count($top5) > 0):?>
                                <div class="top boxShadow">
                                    <h4 style="background-color: <?php echo $graphParams[5]['bg'];?>; color: white;"><?php echo count($top5);?> Linha<?php echo count($top5) > 1 ? "s":"";?> Com Mais Registros de Embarque</h4>
                                    
                                    <?php foreach($top5 as $t5):?>
                                        <div>
                                            <span><?php echo $t5['descricao'];?></span>
                                            <span><?php echo $t5['regemb'];?>%</span>
                                        </div>
                                    <?php endforeach;?>
                                </div>
                            <?php endif;?>
                            <?php if(count($bottom5) > 0):?>
                                <div class="top boxShadow">
                                    <h4 style="background-color: <?php echo $graphParams[6]['bg'];?>;"><?php echo count($bottom5);?> Linha<?php echo count($bottom5) > 1 ? "s":"";?> Com Menos Registros de Embarque</h4>
                                    
                                    <?php foreach($bottom5 as $b5):?>
                                        <div>
                                            <span><?php echo $b5['descricao'];?></span>
                                            <span><?php echo $b5['regemb'];?>%</span>
                                        </div>
                                    <?php endforeach;?>
                                </div>
                            <?php endif;?>
                        </div>
                    <?php endif;?>
                <?php else:?>
                    <h4>REGISTROS DE EMBARQUE</h4>
                    <h5 style="color: #585858;font-weight: bold;font:normal 100%/150% arial,helvetica,sans-serif">SEM DADOS</h5>
                <?php endif;?>
            </section>
            
            <?php if(isset($cardNotUse) && count($cardNotUse) > 0):?>
                <section style="border-top: 2px solid #ccc;">
                        <h4 style="z-index:2;">CARTÕES NÃO UTILIZADOS NOS ÚLTIMOS 7 DIAS</h4>
                        <div id="barChartCard"></div>                    
                </section>
            <?php endif;?>
        </div>
        <?php if(isset($totalTrips) && $totalTrips > 0):?>
            <div class="a4-page">
                <section>
                
                    <h4>VIAGENS - <?php echo $totalTrips;?> Linha<?php echo $totalTrips > 1 ? "s":"";?></h4>
                    <div class="trips">
                        <?php 
                        
                            $i=1;
                            $minus = 0; 

                            foreach ($trips as $trip){
                                $breakPage = false;

                                if(count($trips) > $i){
                                    
                                    if ($i % 13 == 12) {
                                        $breakPage = true;
                                    }
                                    
                                }

                                ?>
                                    <div class="headGrupo"><?php echo $trip['titulo'];?>
                                        <span class="linePontual boxShadow" style="background-color: <?php echo $graphParams[$trip['pontualidade']]['bg'];?>;"><?php echo $graphParams[$trip['pontualidade']]['txt'];?></span>
                                    </div>
                                    <div class="cabecalho">Chegada Prevista</div>
                                    <div class="cabecalho">Chegada Real</div>
                                    <div class="cabecalho"><i class="far fa-clock"></i></div>
                                    <div class="cabecalho"><i class="fas fa-road"></i></div>
                                    <div class="cabecalho">Veículo</div>
                                    <div class="cabecalho">Capacidade</div>
                                    <div class="cabecalho">Embarcados</div>
                                    <div class="cabecalho">% Reg. Embarque</div>

                                    <div title="Chegada Prevista" class="celula"><?php echo $trip['chegadaPrev'];?></div>
                                    <div title="Chegada Real" class="celula border_left"><?php echo $trip['chegadaReal'];?></div>
                                    <div title="Tempo Percurso" class="celula border_left"><?php echo $trip['timePer'];?></div>
                                    <div title="Km" class="celula border_left"><?php echo $trip['km'];?></div>	
                                    <div title="Veículo" class="celula border_left"><?php echo $trip['veic'];?></div>
                                    <div title="Capacidade" class="celula border_left"><?php echo $trip['cap'];?></div>
                                    <div title="Embarcados" class="celula border_left"><?php echo $trip['emb'];?></div>
                                    <div title="% Reg. Embarque" class="celula border_left"><?php echo $trip['regemb'];?></div>

                                <?php 
                                if($breakPage){?>
                                    </div>
                                    </section>
                                    </div>
                                    <div class="a4-page">
                                        <section>
                                        <div class="trips">
                                <?php
                                }
                                $i++;
                            } 
                        ?>
                    </div>
                </section>
            </div>
        <?php endif;?> 
    </body>
    <script type="text/javascript">
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            
            <?php if(isset($totalTrips) && $totalTrips > 0):?>
                var dataPontual = google.visualization.arrayToDataTable([
                    ['Status', 'Qtd'],
                    ['<?php echo $graphParams[1]['txt'];?>', <?php echo $pontual; ?>],
                    ['<?php echo $graphParams[2]['txt'];?>', <?php echo $adiantado; ?>],
                    ['<?php echo $graphParams[3]['txt'];?>', <?php echo $atrasado; ?>],
                    ['<?php echo $graphParams[4]['txt'];?>', <?php echo $nes; ?>]
                ]);

                var colorsPontual = [
                    '<?php echo $graphParams[1]['bg'];?>', 
                    '<?php echo $graphParams[2]['bg'];?>', 
                    '<?php echo $graphParams[3]['bg'];?>', 
                    '<?php echo $graphParams[4]['bg'];?>'
                ];

                var optionsPontual = {
                    title: ' ',
                    
                    chartArea: {width: '90%', height: '90%'},
                    legend: {position: 'none'},
                    tooltip: { trigger: 'none' },
                    colors: colorsPontual,
                    pieSliceText: 'percentage',
                    pieSliceTextStyle: {
                        color: '<?php echo $graphParams[1]['txtColor'];?>'
                    },
                    slices: [
                        {textStyle: {color: '<?php echo $graphParams[1]['txtColor'];?>'}},
                        {textStyle: {color: '<?php echo $graphParams[2]['txtColor'];?>'}},
                        {textStyle: {color: '<?php echo $graphParams[3]['txtColor'];?>'}},
                        {textStyle: {color: '<?php echo $graphParams[4]['txtColor'];?>'}}
                    ]
                };
                

                var chartPontual = new google.visualization.PieChart(document.getElementById('piechartPontual'));

                chartPontual.draw(dataPontual, optionsPontual);

                var dataRegistros = google.visualization.arrayToDataTable([
                    ['Status', 'Qtd'],
                    ['<?php echo $graphParams[5]['txt'];?>', <?php echo $inUsePer; ?>],
                    ['<?php echo $graphParams[6]['txt'];?>', <?php echo $noUsePer; ?>]
                ]);

                var colorsRegistros = [
                    '<?php echo $graphParams[5]['bg'];?>', 
                    '<?php echo $graphParams[6]['bg'];?>'
                ];

                var optionsRegistros = {
                    title: ' ',
                    chartArea: {width: '90%', height: '90%'},
                    legend: {position: 'none'},
                    tooltip: { trigger: 'none' },
                    colors: colorsRegistros,
                    pieSliceText: 'percentage',
                    pieSliceTextStyle: {
                        color: '<?php echo $graphParams[5]['txtColor'];?>'
                    },
                    slices: [
                        {textStyle: {color: '<?php echo $graphParams[5]['txtColor'];?>'}},
                        {textStyle: {color: '<?php echo $graphParams[6]['txtColor'];?>'}}
                    ]
                };
                

                var chartRegistros = new google.visualization.PieChart(document.getElementById('piechartRegistros'));

                chartRegistros.draw(dataRegistros, optionsRegistros); 

            <?php endif;?>
            
            <?php if(isset($cardNotUse) && count($cardNotUse) > 0):?>
                var dataCard = google.visualization.arrayToDataTable([
                    ["Dia", "Qtd", { role: "style" } ],
                    
                    <?php foreach($cardNotUse as $notUse){ ?>
                        ["<?php echo $notUse[0] ?>", <?php echo $notUse[1] ?>, "<?php echo $graphParams[7]['bg'];?>"],
                    <?php } ?>
                    
                ]);

                var view = new google.visualization.DataView(dataCard);
                view.setColumns([0, 1,
                                { calc: "stringify",
                                    sourceColumn: 1,
                                    type: "string",
                                    role: "annotation" },
                                2]);

                var optionsCard = {
                    title: '',
                    bar: {groupWidth: "30%"},
                    tooltip: { trigger: 'none' },
                    legend: { position: "none" },
                    annotations: {
                        alwaysOutside: true,
                        textStyle: {
                            fontSize: 12,
                            color: '#000',
                            auraColor: '#FFF'
                        }
                    }
                };
                var chart = new google.visualization.ColumnChart(document.getElementById('barChartCard'));
                chart.draw(view, optionsCard);
            <?php endif;?>
            
        }

        function hideAriaLabelDivs(containerId) {
            var container = document.getElementById(containerId);
            if (container) {
                var divs = container.getElementsByTagName('div');
                for (var i = 0; i < divs.length; i++) {
                    var divStyle = window.getComputedStyle(divs[i]);
                    if (divStyle.left === '-10000px') {
                        divs[i].style.display = 'none';
                    }
                }
            }
        }

        window.addEventListener('beforeprint', function() {
            hideAriaLabelDivs('piechartPontual');
            hideAriaLabelDivs('piechartRegistros');
            hideAriaLabelDivs('barChartCard');
        });

        window.onload = function() {

            var titulo = document.querySelector('.titulo');
            var containerWidth = titulo.offsetWidth;
            var fontSize = 24;

            titulo.style.fontSize = fontSize + 'px';
            
            while (titulo.scrollWidth > containerWidth && fontSize > 0) {
                fontSize--;
                titulo.style.fontSize = fontSize + 'px';
            }

            titulo.classList.add('show');
        };
    </script>
</html>   