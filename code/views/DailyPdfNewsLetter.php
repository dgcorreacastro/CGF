<?php
$totalTrips = 17;
$percenAtrasa = 5.25;
$percenAdianta = 10.50;
$percenPontual = 70.25;
$percenNes = 13.00;
$dataPontual  = array("pontual" => 13,"adiantado" => 1,"atrasado" => 1, "nes" => 2);
?>
<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <title><?php echo utf8_decode($titulo); ?></title>

        <style>
             @page {
                margin: 0cm !important;
                padding: 0cm !important;
            }

            body {
                margin: 0cm;
                padding: 0cm;
                font-family: Arial, Helvetica, sans-serif;
                box-sizing: border-box;
            }

            .a4-page {
                width: 21cm;
                height: 29.7cm;
                margin: 0cm !important;
                padding: 0cm !important;
                box-sizing: border-box;
            }

            .a4-page .titulo {
                position: relative;
                text-align: center;
                margin: .5em auto;
            }

            .a4-page .titulo p {
                font-size: .9em;
                color: grey;
                margin: 0;
            }

            .inner-a4 {
                padding: 1cm;
            }

            .pie-chart {
                width: 200px;
                height: 200px;
                border-radius: 50%;
                background: conic-gradient(
                    #4CAF50 0% 40%, /* Green */
                    #FF9800 40% 70%, /* Orange */
                    #F44336 70% 100% /* Red */
                );
                position: relative;
                display: inline-block;
            }

            .pie-chart .label {
                position: absolute;
                width: 100%;
                height: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                top: 0;
                left: 0;
                font-size: 20px;
                color: white;
            }

            .legend {
            margin-top: 20px;
            }

            .legend div {
                margin-bottom: 5px;
            }

            .legend span {
                display: inline-block;
                width: 20px;
                height: 20px;
                margin-right: 5px;
            }

            .green {
                background-color: #4CAF50;
            }

            .orange {
                background-color: #FF9800;
            }

            .red {
                background-color: #F44336;
            }
        </style>

    </head>

    <body>
        <div class="a4-page">
            <div class="inner-a4">
                <h2 class="titulo">INFORMATIVO DIÁRIO C.G.F. - AJINOMOTO - 108 <p>26/06/2024</p></h2>
                <hr>
                <div class="pie-chart">
                    <div class="label">Total: 100%</div>
                </div>
                <div class="legend">
                    <div><span class="green"></span>Realizadas: 40%</div>
                    <div><span class="orange"></span>Adiantadas: 30%</div>
                    <div><span class="red"></span>Atrasadas: 30%</div>
                </div>
            </div>
            
        </div>
    </body>
</html>   