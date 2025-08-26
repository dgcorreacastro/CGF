<main class="py-4">
    <h2 class="title"><span>C</span>ENTRO DE <span style="margin-left:.1em">G</span>ESTÃO DO <span style="margin-left:.1em">F</span>RETAMENTO</h2>
    
    <?php if(isset($_SESSION['forbidden'])){?>

        <div class="errosContainer">
            <svg class="errosSvg" width="350px" height="500px" viewBox="0 0 837 1045" version="1.1">
                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                    <path d="M353,9 L626.664028,170 L626.664028,487 L353,642 L79.3359724,487 L79.3359724,170 L353,9 Z" id="Polygon-1" stroke="#007FB2" stroke-width="6"></path>
                    <path d="M78.5,529 L147,569.186414 L147,648.311216 L78.5,687 L10,648.311216 L10,569.186414 L78.5,529 Z" id="Polygon-2" stroke="#EF4A5B" stroke-width="6"></path>
                    <path d="M773,186 L827,217.538705 L827,279.636651 L773,310 L719,279.636651 L719,217.538705 L773,186 Z" id="Polygon-3" stroke="#795D9C" stroke-width="6"></path>
                    <path d="M639,529 L773,607.846761 L773,763.091627 L639,839 L505,763.091627 L505,607.846761 L639,529 Z" id="Polygon-4" stroke="#F2773F" stroke-width="6"></path>
                    <path d="M281,801 L383,861.025276 L383,979.21169 L281,1037 L179,979.21169 L179,861.025276 L281,801 Z" id="Polygon-5" stroke="#36B455" stroke-width="6"></path>
                </g>
            </svg>
            <div class="message-box">
                <h1><?php echo $_SESSION['forbidden']['code'];?></h1>
                <p class="bg bg-warning text-dark p-2"><?php echo $_SESSION['forbidden']['msg'];?></p>
                <div class="buttons-con">
                    <div class="action-link-wrap">
                        <a onclick="history.back(-1)" class="link-button link-back-button">Voltar</a>
                        <a href="/" class="link-button">Home</a>
                    </div>
                </div>
            </div>
        </div>
        
    <?php 
    unset($_SESSION['forbidden']);
    }else{?>

    <?php if(!isset($nomeGrupo)){?>
        <h2 style="padding:.5em; margin-top:2em; background: rgba(173, 216, 230,.6)" class="title">Dados não encontrados, por favor entre em contato com o administrador do sistema.</h2>
    <?php return;}; ?>

    <?php if(!isset($_SESSION['cFret'])){ ?>
        <input type="hidden" id="timeAtualiza" value="<?php echo $timeAtualiza; ?>" />
        <input type="hidden" id="relDays" value="<?php echo $relDays; ?>" />
        <div class="chartsContainer" id="homeDashBoard">
            <div class="donutChartContainer" id="donutPontualidade">
                <div class="donutSemDados">
                    <h4>SEM DADOS PARA OS FILTROS SELECIONADOS.</h4>
                </div>
                <div class="donutCarrega">
                    <img width="100" src="<?php echo BASE_URL; ?>assets/images/carregando.gif">
                </div>
                
                <h4>PONTUALIDADE DE CHEGADA DAS VIAGENS
                    <p class="atualizado"></p>
                </h4>
                <hr>
                <!-- <p>Data Final Real</p> -->
                <div class="donutPercents"></div>
                <ul class="donutChart">
                    
                </ul>
                <ul class="donutChartLegends">
                    
                </ul>
            </div>

            <div class="donutChartContainer" id="donutOcupaLinhas">
                <div class="donutSemDados">
                    <h4>SEM DADOS PARA OS FILTROS SELECIONADOS.</h4>
                </div>
                <div class="donutCarrega">
                    <img width="100" src="<?php echo BASE_URL; ?>assets/images/carregando.gif">
                </div>

                <h4>REGISTROS DE EMBARQUE
                    <p class="atualizado"></p>
                </h4>
                <hr>
                <!-- <p>Veículo Capacidade / Uso</p> -->
                <div class="donutPercents"></div>
                <ul class="donutChart">
                    
                </ul>
                <ul class="donutChartLegends">
                    
                </ul>
            </div>
            <?php if($cad_pax_tag == 1):?>
                <div class="barChartContainer" id="barCartaoUtiliza">
                    <div class="donutSemDados">
                        <h4>SEM DADOS PARA OS FILTROS SELECIONADOS.</h4>
                    </div>
                    <h4>CARTÕES NÃO UTILIZADOS NOS ÚLTIMOS 7 DIAS
                    </h4>
                    <p class="mb-0"><?php echo $nomeGrupo ?></p>
                    <hr>
                    <div class="barChartDash">
                        <div class="marcaChart">
                        </div>
                        <div class="barras">
                        </div>
                        <ul class="datas">
                        </ul>
                    </div>
                    <img class="carrega" src="<?php echo BASE_URL; ?>assets/images/carregando.gif">
                </div>
            <?php endif;?>
        </div>

    <?php } else { ?>

        <input type="hidden" id="cFret" value="1" />
        <div style="width: 100%;text-align: center;min-height: 300px;display: flex;justify-content: center;align-items: center; color: white;">
        <a href="/escala/">
            <div style="background-color: white;padding: 15px;border-radius: 7px;">
                <h4 style="font-size: 2rem;font-weight: 900;margin-bottom: -10px;">ESCALAS</h4> </br>
                <i style="font-size: 3rem;" class="far fa-calendar-alt" aria-hidden="true"></i>
            </div>
        </a>
        </div>
        
    <?php } ; ?>

    <?php if(!isset($_SESSION['cFret'])){ ?>
        <div class="filtroDivNew">
            <div class="filtrosBtn">
                <div>
                <i class="fa fa-filter" aria-hidden="true"></i><b>FILTROS</b>
                </div>
            </div>
            <form id="atualDash" class="form-horizontal form-label-left input_mask" action="" method="post" target='_blank'>
                <input type="hidden" id="pontualViagensIda" value="" />
                <input type="hidden" id="pontualViagensVolta" value="" />

                <input type="hidden" id="cartoesUltizac" value="" />

                <input type="hidden" id="limit" value="" />
                <input type="hidden" id="embarcado" value="" />
                <input type="hidden" id="graphReColor" value="<?php echo $graphParams[5]['bg'];?>"/>
                <input type="hidden" id="graphSreColor" value="<?php echo $graphParams[6]['bg'];?>"/>
                <input type="hidden" id="graphReTxt" value="<?php echo $graphParams[5]['txt'];?>"/>
                <input type="hidden" id="graphSreTxt" value="<?php echo $graphParams[6]['txt'];?>"/>
                <input type="hidden" id="graphBarraColor" value="<?php echo $graphParams[7]['bg'];?>"/>
                <input type="hidden" id="graphBarraTxtColor" value="<?php echo $graphParams[7]['txtColor'];?>"/>
                
                <div id="boxContFilter">
                    <div>
                        <div class="holdFiltroSelect">
                            <label for="linhas" class="form-label">Linhas:</label>
                            <span class="filtroSelect" title="SELECIONE UMA LINHA" originaltxt="SELECIONE UMA LINHA" checkboxesFiltro="linhasSel"><i class="fa fa-bus" aria-hidden="true"></i> <texto>SELECIONE UMA LINHA</texto></span>
                            <label class="labelSelect"><input id="todosLinha" type="checkbox" name="todosLinha"> Todas as Linhas </label>
                        </div>
                    </div>

                    <div class="datasContainer">
                        <div>
                            <label for="data_inicio" class="form-label">Data In&iacute;cio:</label>
                            <input class="form-control" name="data_inicio" type="date" value="<?php echo $dataIni; ?>" max="<?php echo date($dateEnd, strtotime("- 1 day")); ?>" id="data_inicio">
                        </div>
                        <div>
                            <label for="data_fim" class="form-label">Data Fim:</label>
                            <input class="form-control" name="data_fim" type="date" value="<?php echo $dateEnd; ?>" min="<?php echo $dataIni; ?>" max="<?php echo $dateEnd; ?>" id="data_fim">
                        </div>
                        <i class="intervaloDiasRels"><i class="fas fa-exclamation-triangle mr-1"></i> O período máximo permitido é de <?php echo $relDays == 1 ? '1 dia.' : $relDays.' dias.';?></i>
                    </div>
                </div>
            </form>
            <div class="btsFiltro">
                <button title="Buscar" type="button" class="btn btn-warning" style="vertical-align: middle;width:250px" onclick="buscarDadosDash()"> <i class="fa fa-search" style="font-size:18px;color:white"></i></button>
            </div>
        </div>
        
        <div class="checkboxesFiltro" id="linhasSel">
            <span class="titleCheckboxesFiltro" title="SELECIONE UMA LINHA"><i class="fa fa-bus" aria-hidden="true"></i> SELECIONE UMA LINHA</span>
            <i class="fa fa-window-close fechaCheckboxesFiltro" aria-hidden="true"></i>
            <div class="buscaFiltro">
            <input class="form-control buscaFiltroInput" type="text" placeholder="Digite aqui para filtrar..."/>
            </div>
            <div class="checkboxesFiltroLista">
                <?php foreach($linhas as $gr): ?>
                <input type="checkbox" class="linhaCheck checkFiltro" id="ln-<?php echo $gr['ID_ORIGIN'] ?>" value="<?php echo $gr['ID_ORIGIN'] ?>" name="linhas[]" />
                <label for="ln-<?php echo $gr['ID_ORIGIN'] ?>">
                    <?php echo $gr['NOME'] ?>
                </label>
                <?php endforeach; ?>
            </div>
            <div class="checkboxesFiltroBts">
                <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                    <button id="limpaCheckFiltro" class="btn btn-warning w-100">Limpar</button>
                </div>
                <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                    <button id="okCheckFiltro" class="btn btn-success w-100" type="button">OK</button>
                </div>
            </div>
        </div>   
    <?php }}; ?>
</main>