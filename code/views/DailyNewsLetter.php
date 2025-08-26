<?php
// $totalTrips = 17;
// $percenAtrasa = 5.25;
// $percenAdianta = 10.50;
// $percenPontual = 70.25;
// $percenNes = 13.00;
// $dataPontual  = array("pontual" => 13,"adiantado" => 1,"atrasado" => 1, "nes" => 2);
?>
<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"> 
        <title><?php echo utf8_decode($titulo); ?></title>

        <style>

            @media screen and (max-width: 980px){

                .divTable{
                    width: 100%;
                    max-width: 100%;
                    overflow-x: scroll;
                }

                #tableList{
                    width: 1000px !important;
                }

                #pontualDiv,
                #taxaOcuDiv{
                    width: 47% !important;
                    display: inline-block;
                    padding: 5px !important;
                }

                #notUseDiv{
                    width: 100% !important;
                    padding: 1rem 0.5rem;
                    border-top: 1px solid gray;
                    margin-top: 20px;
                }

                #globGraph {
                    display: block !important;
                    width: 100%;
                    max-width: 100%;
                    margin: auto;
                    overflow-x: hidden;
                }

                #globGraph h4{
                    width: 70%;
                    text-align: center;
                    margin: 10px auto;
                }

            }

            @media screen and (max-width: 715px){

                #pontualDiv,
                #taxaOcuDiv{
                    width: 100% !important;
                    border-left: 0px !important;
                    border-right: 0px !important;
                }

            }
        </style>

    </head>

    <body style="background-color: #fdfdfd;">

        <h2 style="text-align:center"><?php echo utf8_decode($titulo2); ?></h2>

        <div id="globGraph" style="display:flex">

            <div id="pontualDiv" class="graphsDiv" style="flex:1;text-align: center;padding: 0.5rem;width: 25%; align-items: center; border-top: 2px solid #808080;">
                <?php if(isset($totalTrips) && $totalTrips > 0){ ?>
                <h4 style="height: 40px;">PONTUALIDADE DE CHEGADA DAS VIAGENS <br> <?php echo $totalTrips;?> Linha<?php echo $totalTrips > 1 ? "s":"";?></h4>
                <table style="border-collapse: collapse;width: 100%">
                    <tbody>
                        <tr>
                            <td style="border-right: 0;" class="th1">
                                <div title="Total Atraso" style="background-color: #880808; width: <?php echo $percenAtrasa > 4 ? $percenAtrasa : 5 ?>%; color: white; padding: 10px 30px 10px 10px; margin-bottom: 10px; text-align: center; border-top-right-radius: 5px;border-bottom-right-radius: 5px; font-weight: bold; font-size: 0.8rem; max-width: 80%;"> <?php echo $percenAtrasa ?>%</div>
                                <div title="Total Adiantado" style="background-color: #FFBF00; width: <?php echo $percenAdianta > 4 ? $percenAdianta : 5 ?>%; color: black; padding: 10px 30px 10px 10px; margin-bottom: 10px; text-align: center; border-top-right-radius: 5px;border-bottom-right-radius: 5px; font-weight: bold;font-size: 0.8rem; max-width: 80%;"><?php echo $percenAdianta ?>% </div>
                                <div title="Total Pontual" style="background-color: #228B22; width: <?php echo $percenPontual > 4 ? $percenPontual : 5 ?>%; color: white; padding: 10px 30px 10px 10px; margin-bottom: 10px; text-align: center; border-top-right-radius: 5px;border-bottom-right-radius: 5px; font-weight: bold; font-size: 0.8rem; max-width: 80%;"><?php echo $percenPontual ?>%</div>
                                <div title="Total <?php echo utf8_decode("Não") ?> Realizado Sistema" style="background-color: #808080; width: <?php echo $percenNes > 4 ? $percenNes : 5 ?>%; color: white; padding: 10px 30px 10px 10px; margin-bottom: 10px; text-align: center; border-top-right-radius: 5px;border-bottom-right-radius: 5px; font-weight: bold; font-size: 0.8rem; max-width: 80%;"><?php echo $percenNes ?>%</div>  
                                <div style="background-color: white;padding: 5px; display: flex;">
                                    <div style=" display: flex;align-items: center;padding: 0.6rem;">
                                        <div style="background-color: #880808; height: 10px; width: 10px;" class="entry-color"></div>
                                        <div style=" margin-left: 5px;color: #585858;font-weight: bold;font:normal 80%/120% arial,helvetica,sans-serif">Atrasado (<?php echo $dataPontual['atrasado'];?>)</div>
                                    </div>
                                    <div style=" display: flex;align-items: center;padding: 0.6rem;">
                                        <div style="background-color: #FFBF00; height: 10px;width: 10px;" class="entry-color"></div>
                                        <div style=" margin-left: 5px;color: #585858;font-weight: bold;font:normal 80%/120% arial,helvetica,sans-serif">Adiantado (<?php echo $dataPontual['adiantado'];?>)</div>
                                    </div>
                                    <div style=" display: flex; align-items: center;padding: 0.6rem;">
                                        <div style="background-color: #228B22; height: 10px;width: 10px;" class="entry-color"></div>
                                        <div style=" margin-left: 5px;color: #585858;font-weight: bold;font:normal 80%/120% arial,helvetica,sans-serif">Pontual (<?php echo $dataPontual['pontual'];?>)</div>
                                    </div>
                                    <div style=" display: flex; align-items: center;padding: 0.6rem;">
                                        <div style="background-color: #808080; height: 10px;width: 10px;" class="entry-color"></div>
                                        <div style=" margin-left: 5px;color: #585858;font-weight: bold;font:normal 80%/120% arial,helvetica,sans-serif"><?php echo utf8_decode("Não") ?> Realizado Sistema (<?php echo $dataPontual['nes'];?>)</div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <?php } else { ?>
                    <h4 style="height: 40px;">PONTUALIDADE DE CHEGADA DAS VIAGENS</h4>
                    <h5 style="padding-top: 5px;color: #585858;font-weight: bold;font:normal 100%/150% arial,helvetica,sans-serif">SEM DADOS</h5>
                <?php } ?>

            </div>
 
            <div id="taxaOcuDiv" class="graphsDiv"  style="flex:1;text-align: center;padding: 0.5rem 1rem;border-left: 1px solid #c5c3c3;border-right: 1px solid #c5c3c3;width: 25%; border-top: 2px solid #808080;">
                <h4 style="height: 40px;">REGISTROS DE EMBARQUE</h4>
                <?php if(isset($totalTrips) && $totalTrips > 0){ ?>
                <table style="border-collapse: collapse;width: 100%">
                    <tbody>
                        <tr>
                            <td style="border-right: 0;" class="th2">
                                <div title="Total em Uso" style="background-color: #008d9f; width: <?php echo $inUsePer > 4 ? $inUsePer : 5 ?>%; color: white; padding: 10px 30px 10px 10px; margin-bottom: 10px; text-align: center; border-top-right-radius: 5px;border-bottom-right-radius: 5px; font-weight: bold; font-size: 0.8rem; max-width: 80%;"> <?php echo $inUsePer ?>%</div>
                                <div title="Total Adiantado" style="background-color: #966310; width: <?php echo $noUsePer > 4 ? $noUsePer : 5 ?>%; color: white; padding: 10px 30px 10px 10px; margin-bottom: 10px; text-align: center; border-top-right-radius: 5px;border-bottom-right-radius: 5px; font-weight: bold;font-size: 0.8rem; max-width: 80%;"><?php echo $noUsePer ?>% </div>
                                <div style="background-color: white;padding: 5px; display: flex;">
                                    <div style=" display: flex;align-items: center;padding: 0.6rem;">
                                        <div style="background-color: #008d9f; height: 10px; width: 10px;"></div>
                                        <div style=" margin-left: 5px;color: #585858;font-weight: bold;font:normal 80%/120% arial,helvetica,sans-serif">Embarque Registrado</div>
                                    </div>
                                    <div style=" display: flex;align-items: center;padding: 0.6rem;">
                                        <div style="background-color: #966310; height: 10px;width: 10px;" ></div>
                                        <div style=" margin-left: 5px;color: #585858;font-weight: bold;font:normal 80%/120% arial,helvetica,sans-serif">Sem Registro de Embarque</div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <?php } else { ?>
                    <h5 style="padding-top: 5px;color: #585858;font-weight: bold;font:normal 100%/150% arial,helvetica,sans-serif">SEM DADOS</h5>
                <?php } ?>

            </div>
            <?php if(!empty($cardNotUse)):?>
                <div id="notUseDiv" class="graphsDiv"  style="flex:2;text-align: center;padding: 0.5rem;width: 50%; border-top: 2px solid #808080;">
                    <h4 style="height: 40px;"><?php echo utf8_decode("CARTÕES NÃO") ?> UTILIZADOS NOS <?php echo utf8_decode("ÚLTIMOS") ?> 7 DIAS</h4>
                    <table style="margin-bottom:1em; width:100%; height:200px;min-width:400px">
                        <tbody>
                            <tr>
                                <td>
                                <?php if(isset($cardNotUse)){ ?>
                                <?php foreach($cardNotUse as $notUse){ ?>
                                    <div style="display: flex; margin-bottom: 10px">
                                        <div title="Total de <?php echo $notUse[1] ?> sem uso" style="background-color: #108496; width: <?php echo $notUse['percent']?>%;color:white; padding: 10px 30px 10px 10px; border-top-right-radius: 5px;border-bottom-right-radius: 5px; font-weight: bold;font-size: 0.8rem; margin-right: 10px;"><?php echo $notUse[1] ?></div> 
                                        <div style="padding-top: 10px;color: #585858;font-weight: bold;font:normal 80%/120% arial,helvetica,sans-serif"><?php echo $notUse[0] ?></div>
                                    </div>
                                <?php } ?>

                            </td>
                        </tr>
                        <?php } else { ?>
                            <h5 style="padding-top: 5px;color: #585858;font-weight: bold;font:normal 100%/150% arial,helvetica,sans-serif">SEM DADOS</h5>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php endif;?>

        </div>
        
        <hr>

        <div class="divTable" style="padding: 0.5rem;">

            <table id="tableList" style="border-collapse: collapse;width: 100%">
                <thead>
                    <tr>
                        <th style="border: 1px solid #ddd;padding: 8px;text-align: left;background-color: #04AA6D;color: white;">Data</th>
                        <th style="border: 1px solid #ddd;padding: 8px;text-align: left;background-color: #04AA6D;color: white;">Linha</th>
                        <th style="border: 1px solid #ddd;padding: 8px;text-align: left;background-color: #04AA6D;color: white;"><?php echo utf8_decode("Descrição") ?></th>
                        <th style="border: 1px solid #ddd;padding: 8px;text-align: left;background-color: #04AA6D;color: white;">Sentido</th>
                        <th style="border: 1px solid #ddd;padding: 8px;text-align: left;background-color: #04AA6D;color: white;">Data Final Prev.</th>
                        <th style="border: 1px solid #ddd;padding: 8px;text-align: left;background-color: #04AA6D;color: white;">Data Final Real</th>
                        <th style="border: 1px solid #ddd;padding: 8px;text-align: left;background-color: #04AA6D;color: white;">Tempo Percurso</th>
                        <th style="border: 1px solid #ddd;padding: 8px;text-align: left;background-color: #04AA6D;color: white;">Pref.</th>
                        <th style="border: 1px solid #ddd;padding: 8px;text-align: left;background-color: #04AA6D;color: white;">Capac.</th>
                        <th style="border: 1px solid #ddd;padding: 8px;text-align: left;background-color: #04AA6D;color: white;">Embarc.</th>
                        <th style="border: 1px solid #ddd;padding: 8px;text-align: left;background-color: #04AA6D;color: white;">% Uso</th>
                        <th style="border: 1px solid #ddd;padding: 8px;text-align: left;background-color: #04AA6D;color: white;">Pontualidade</th>
                    </tr>
                </thead>
                <tbody><?php echo $html ?? "" ?></tbody>
            </table>

        </div>
    </body>
</html>   