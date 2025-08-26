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
        #refData{
            flex:4;
            text-align: right;
        }
        .bdye{
            padding: 2px 20px;
            background-color:yellow;
            border: 1px solid black;
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
            margin: 30px 5px 10px 0;
        }
        .isFolga{
            background-color: red;
            font-weight: 900;
            color: white;
        }
        .isAf{
            background-color: yellow;
            color:black;
            font-weight: 900;
        }
        .isFe{
            background-color: green;
            font-weight: 900;
            color: white;
        }
        .isTurno{
            background-color: dodgerblue;
            font-weight: 900;
            color: white;
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

    <div id="dept" class="bdye">
        <h5 style="text-align: center;padding: 5px;margin: 0px;">SETOR: <?php echo $setor; ?></h5>
    </div>

    <div id="dobyEscal">
        <table>
            <thead>
                <tr>
                    <th style="text-align:center" colspan="37" class="bdye">PREDIAL</th>
                </tr>
                <tr>
                    <th style="width: 5% !important;" rowspan="2">RE</th>
                    <th style="width: 10% !important;" rowspan="2">NOME</th>
                    <th style="width: 10% !important;" rowspan="2">CARGO</th>
                    <th style="width: 5% !important;" rowspan="2">HORÁRIO</th>
                    <?php for($i=1;$i <= count($daysMon); $i++){ ?>
                        <?php if( $daysMon[$i]['color'] ) { ?>
                            <th class="infTMQ" style="max-width: 1% !important;background-color:#b97800"> <?php echo $daysMon[$i]['letter']; ?> </th>
                        <?php }else { ?>
                            <th class="infTMQ" style="max-width: 1% !important;"> <?php echo $daysMon[$i]['letter']; ?> </th>
                    <?php }}; ?>
                    <th style="width: 5% !important;" rowspan="2">TOTAL</th>
                    <th style="width: 20% !important;" rowspan="2">ASSINATURA</th>
                </tr>
                <tr>
                    <?php for($i=1;$i <= count($daysMon); $i++){ ?>
                        <?php if( $daysMon[$i]['color'] ) { ?>
                            <th class="infTMQ" style="max-width: 1% !important;background-color:#b97800"> <?php echo $i; ?> </th>
                        <?php }else { ?>
                            <th class="infTMQ" style="max-width: 1% !important;"> <?php echo $i; ?> </th>
                    <?php }}; ?>
                </tr>
            </thead>
            <tbody>
                <?php 
                    $ttPerDayFolda = array();
                    $ttPerDayWork  = array();
                ?>
                <?php foreach($escal['itemEscala'] AS $itens){ 
                    $ttColFolga = 0;
                ?>  
                    <tr>
                        <td><?php echo $itens['re']; ?></td>
                        <td><?php echo $itens['nome']; ?></td>
                        <td><?php echo $itens['funcao']; ?></td>
                        <td><?php echo $itens['TURNO']; ?></td>
                        <?php 
                        $contaFolga = array(1,2,3);
                        $contaTrabalho = array(0,4);
                        for($i=1;$i <= count($daysMon); $i++){ 

                            if (in_array($itens['t'.$i], $contaFolga)) {
                                $ttColFolga++; 
                                $ttPerDayFolda[$i] = isset($ttPerDayFolda[$i]) ? ( $ttPerDayFolda[$i] + 1 ) : 1;
                            }    

                            if (in_array($itens['t'.$i], $contaTrabalho)) {
                                $ttPerDayWork[$i] = isset($ttPerDayWork[$i]) ?  ( $ttPerDayWork[$i] + 1 ) : 1;
                            }  

                            if($itens['t'.$i] == 1){?>
                                <td class="isFolga">F</td>
                            <?php }else if($itens['t'.$i] == 2){?>
                                <td class="isAf">AF</td>
                            <?php }else if($itens['t'.$i] == 3){?>
                                <td class="isFe">FE</td>
                            <?php }else if($itens['t'.$i] == 4){?>
                                <td class="isTurno">T</td>
                            <?php }else {?>
                                <td></td>
                            <?php }
                        }; ?>
                        <td><?php echo $ttColFolga; ?></td>
                        <td></td>
                    </tr>
                <?php }; ?>
               
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="font-size: 9px !important;font-weight: 800;">EM FOLGA/<br>AUSENTE</td>
                    <?php
                        $ttFolgas = 0;
                        for($i=1;$i <= count($daysMon); $i++){ 
                            $ttFolgas +=  isset($ttPerDayFolda[$i]) ? $ttPerDayFolda[$i] : 0;
                        ?>
                        <td> <?php echo isset($ttPerDayFolda[$i]) ? $ttPerDayFolda[$i] : 0;  ?> </td>
                    <?php }; ?>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="font-size: 9px !important;font-weight: 800;">EM TRABALHO</td>
                    <?php 
                        $ttWorks = 0;
                        for($i=1;$i <= count($daysMon); $i++){ 
                            $ttWorks +=  isset($ttPerDayWork[$i]) ? $ttPerDayWork[$i] : 0;
                        ?>
                        <td> <?php echo isset($ttPerDayWork[$i]) ? $ttPerDayWork[$i] : 0;  ?> </td>
                    <?php }; ?>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="footInfos">
        <div class="infof">RE GESTOR</div>
        <span>___________________________________</span>
    </div>
    <div class="footInfos">
        <div class="infof">NOME GESTOR</div>
        <span>____________________________________________________________</span>
    </div>
    <div class="footInfos">
        <div class="infof">RAMAL</div>
        <span>_____________________</span>
    </div>
    <div class="footInfos">
        <div class="infof">DATA ENTREGA</div>
        <span>________/________/__________</span>
    </div>
    <p style="color:red; font-size:11px">AS ESCALAS NÃO PODERÃO SER MODIFICADAS APÓS ENTREGA.</p>
</body>

</html>


