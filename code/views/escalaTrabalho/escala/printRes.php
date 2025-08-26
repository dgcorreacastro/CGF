<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="description" content="Sistema de Controle de Fretamento do Grupo TP Transportes">
    <meta name="keywords" content="Frotas, fretamento, onibus, controle de fretamento, TP Transporte.">

    <link rel="shortcut icon" href="/assets/favicon/favicon.ico" type="image/x-icon">

    <title><?php echo APP_NAME; ?> - Escala</title>

    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style type="text/css">
        body{
            padding: 5px;
        }
        #top{
            display:flex;
            flex-direction:row;
        }
        .bdye{
            padding: 2px 20px;
            background-color:yellow;
            border: 1px solid black;
        }
        #refData{
            flex:4;
            text-align: right;
        }
        #dept{
            margin: 5px 0;
        }
        th, td {
            border: 1px solid gray !important;
            text-align: center !important;
            font-size: 11px !important;
        }
        .infof{
            font-size: 11px;
            width:100px;
            text-align: center;
            display:inline-block;
            padding:5px;
            border: 1px solid gray !important;
        }
        .footInfos{
            margin: 10px 5px 10px 0;
        }

        #dobyEscal{
            margin-top: 15px;
        }

        .infoData{
            padding: 5px 10px;
            border: 1px solid gray !important;
        }

    </style>

    <style type="text/css" media="print">
        @page { size: landscape; }
    </style>
</head>

<body onload="window.print()">
    
    <div id="top">
        <img width="150" src="https://logodownload.org/wp-content/uploads/2018/03/eurofarma-logo-3.png" />
        <div id="refData">
            <span class="bdye"><?php echo $infoMes; ?></span>
            <span class="bdye"><?php echo $infoAno; ?></span>
        </div>
    </div>

    <div id="dobyEscal">
        <table>
            <thead>
                <tr>
                    <th style="text-align:center" colspan="37" class="bdye">REFEIÇÕES RESTAURANTE</th>
                </tr>
                <tr>
                    <th style="width: 5% !important;" rowspan="2">HORÁRIO</th>
                    <?php for($i=1;$i <= count($daysMon); $i++){ ?>
                        <th style="width: 1% !important;">  <?php echo $daysMon[$i]; ?> </th>
                    <?php }; ?>
                    <th style="width: 5% !important;" rowspan="2">TOTAL P/ TURNO</th>
                </tr>
                <tr>
                    <?php for($i=1;$i <= count($daysMon); $i++){ ?>
                        <th style="width: 1% !important;"> <?php echo $i; ?> </th>
                    <?php }; ?>
                </tr>
            </thead>
            <tbody>

                <?php 
                    $turnos   = array(1 => "1º Turno", 2 => "2º Turno", 3 => "3º Turno", 4 => "ADM");
                    $ttGerais = 0;
                    $ttPDias  = array();

                    foreach($ttPerTurno AS $k => $itens){ 
                    $ttCol = 0;
                ?> 
                    <tr>
                        <td><?php echo $turnos[$k]; ?> </td>

                        <?php 
                            for($i=1;$i <= count($daysMon); $i++){ 
                                $p = isset($itens['t'.$i]) ? $itens['t'.$i] : 0;
                                $ttCol += $p;
                                $ttPDias[$i] = isset($ttPDias[$i]) ? ($ttPDias[$i] + $p) : $p;
                        ?>
                            <td> <?php echo $p ?> </td>
                        <?php }; ?>
                        <td>
                            <?php 
                                echo $ttCol; 
                                $ttGerais += $ttCol;
                            ?>
                        </td>    

                    </tr>             
                <?php }; ?>
                    <tr>
                        <td style="font-weight: 800;background-color: lightgray;">TOTAL GERAL</td>

                        <?php 
                            for($i=1;$i <= count($daysMon); $i++){ 
                        ?>
                            <td style="font-weight: 800;background-color: lightgray;"> 
                            <?php echo isset($ttPDias[$i]) ? $ttPDias[$i] : 0;  ?> </td>
                        <?php }; ?>
                        <td style="font-weight: 800;background-color: lightgray;"><?php echo isset($ttGerais) ? $ttGerais : 0; ?> </td>    

                    </tr>       
            </tbody>
        </table>
    </div>

</body>

</html>


