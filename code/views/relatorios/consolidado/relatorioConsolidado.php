<main class="py-4">
    <div id="relConsolidado" class="personContainer">

      <div class="card-body">
          <div class="card-create-header">
              <h2 class="pageTitle"> <button title="Baixar Excel" type="button" class="btn btn-success btnExcel py-2 px-3 m-1 dn" onclick="downloadRelScreen('bodyConsolidado')"><i class="fas fa-file-excel" style="font-size:22px;color:white"></i></button>
                <?php if($showRelTimer == 1):?><div class="relsCronometer h5">00:00:00</div><?php endif;?>
              </h2>
          </div>

          <hr>

          <div class="card-create-body">
              <div class="chartsContainer relPage">
                  <div class="donutChartContainer relPage" id="donutOcupaLinhas">
                      <div class="donutSemDados">
                          <h4>SEM DADOS PARA OS FILTROS SELECIONADOS.</h4>
                      </div>
                      <div class="donutCarrega">
                          <img width="100" src="<?php echo BASE_URL; ?>assets/images/carregando.gif">
                      </div>
                      <h4 style="font-size: 15px">REGISTROS DE EMBARQUE
                          <p class="atualizado"></p>
                      </h4>
                      <hr>
                      <ul class="donutChart">
                          
                      </ul>
                      <ul class="donutChartLegends">
                          
                      </ul>
                  </div>
              </div>

              <div class="filterRelResultContainer">
                  <input type="text" id="filterRelResult" class="form-control" placeholder="Digite aqui para filtrar o relatório..."/>
              </div>
              <hr>
              <div class="TableRelatorios" style=" display: flex; flex-direction: column; flex-wrap: nowrap; align-items: center;">
                  <table id="table" class="table table-striped customScroll" style="position: sticky; top:0; z-index:3; margin-bottom: 0; max-width:fit-content;">
                      <thead>
                        <tr class="headerTr topHeader">
                          <th scope="col" style="min-width: 72px !important;" colspan="1" rowspan="2">Data</th>
                          <th scope="col" colspan="1" style="min-width: 72px !important;">Linha</th>
                          <th scope="col" colspan="1" style="min-width: 70px !important;">Itinerário</th>
                          <th scope="col" colspan="6">Viagem</th>
                          <th scope="col" colspan="3">Veículo</th>
                          <th scope="col" colspan="2">Passageiros</th>
                          <th scope="col" colspan="1" rowspan="2">Pontualidade</th>
                        </tr>
                        <tr class="headerTr applyWidth">
                          <th class="dn" scope="col">Data</th>
                          <th scope="col" class="tdBorder5" style="min-width: 72px !important;">Prefixo</th>
                          <th scope="col" class="tdBorder5" style="min-width: 70px !important;">Sentido</th>
                          <!-- <th scope="col" style="min-width: 18.8vw !important; max-width: 18.8vw !important;">Descrição</th>   -->
                          <th scope="col" style="min-width: 72px !important;" class="tdBorder5">Saída<br>Prevista</th>
                          <th scope="col" style="min-width: 72px !important;">Saída<br>Real</th>
                          <th scope="col" style="min-width: 72px !important;">Chegada<br>Prevista</th>
                          <th scope="col" style="min-width: 72px !important;">Chegada<br>Real</th>
                          <th scope="col" style="min-width: 70px !important;"><i class="far fa-clock"></i></th>
                          <th scope="col" style="min-width: 70px !important;"><i class="fas fa-road"></i></th>
                          <th scope="col" class="tdBorder5" style="min-width: 72px !important;">Prefixo</th>
                          <th scope="col" style="min-width: 70px !important;">Cap.</th>
                          <th scope="col" style="min-width: 72px !important;">% Reg.<br>Embarque</th>
                          <th scope="col" class="tdBorder5" style="min-width: 70px !important;">Cad.</th>
                          <th scope="col" style="min-width: 70px !important;">Emb.</th>
                          <th class="dn" scope="col">Pontualidade</th>
                        </tr>
                        <tr class="dn headExcel">
                          <th scope="col">Prefixo</th>
                          <th scope="col">Sentido</th>
                          <!-- <th scope="col">Descrição</th>   -->
                          <th scope="col">Saída Prevista</th>
                          <th scope="col">Saída Real</th>
                          <th scope="col">Chegada Prevista</th>
                          <th scope="col">Chegada Real</th>
                          <th scope="col">Tempo Percurso</th>
                          <th scope="col">Km</th>
                          <th scope="col">Prefixo</th>
                          <th scope="col">Capacidade</th>
                          <th scope="col">% Reg. Embarque</th>
                          <th scope="col">Cadastrados</th>
                          <th scope="col">Embarcados</th>
                        </tr>
                      </thead>
                  </table>
                  <table id="table" class="table table-striped tBodyScroll" style="max-width:fit-content;">
                      <tbody id="bodyConsolidado"></tbody>
                  </table>
                  <div class="wrapper1">
                      <div class="div1"></div>
                  </div>
                  <div class="wrapper1after"></div>
              </div>
          </div>
          
      </div>
      
    </div>

    <div class="filtroDivNew">
        <div class="filtrosBtn">
            <div>
            <i class="fa fa-filter" aria-hidden="true"></i><b>FILTROS</b>
            </div>
        </div>

        <div class="agendamentosBtn">
            <div>
              <i class="fas fa-clipboard-list" aria-hidden="true"></i><b>AGENDAMENTOS</b>
            </div>
        </div>

        <form id="gerarRelat" class="form-horizontal form-label-left input_mask" action="" method="post" target='_blank'>
            <input type="hidden" id="limit" value="0" />
            <input type="hidden" id="embarcado" value="0" />
            <input type="hidden" id="timeAtualiza" value="<?php echo $timeAtualiza; ?>" />
            <input type="hidden" id="relDays" value="<?php echo $relDays; ?>" />
            <?php if($cad_pax_tag == 1):?>
              <input type="hidden" id="cad_pax_tag" value="<?php echo $cad_pax_tag; ?>" />
            <?php endif;?>
            <input type="hidden" id="notificaScreen" value="Relatório" />
            <input type="hidden" id="notificaDownload" value="Relatório" />  
            <input type="hidden" id="downloadName" value="" />
            <input type="hidden" id="graphReColor" value="<?php echo $filterPontual[5]['bg'];?>"/>
            <input type="hidden" id="graphSreColor" value="<?php echo $filterPontual[6]['bg'];?>"/>
            <input type="hidden" id="graphReTxt" value="<?php echo $filterPontual[5]['txt'];?>"/>
            <input type="hidden" id="graphSreTxt" value="<?php echo $filterPontual[6]['txt'];?>"/>
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
                  <i class="intervaloDiasRels"><i title="A partir de <?php echo $relDays == 1 ? '1 dia' : ($relDays+1).' dias';?> o relatório será agendado e estará disponível na aba Agendamentos." class="fas fa-exclamation-triangle mr-1"></i> O período máximo permitido é de 1 mês.</i>
                </div>
  
                <div>
                  <div>
                    <label for="sentido" class="form-label">Sentido:</label>
                    <select name="sentido" id="sentido" class="form-control filtroSelect2">
                      <option value="">Todos</option>
                      <option value="1">Ida</option>
                      <option value="2">Volta</option>
                    </select>
                  </div>
                </div>
  
                <div>
                  <div>
                    <label for="pontual" class="form-label">Pontualidade:</label>
                    <select name="pontual" id="pontual" class="form-control filtroSelect2">
                      <option value="">Todos</option>
                        <?php foreach($filterPontual as $kp => $filter){
                          if($kp <= 4){
                        ?>
                          <option value="<?php echo $kp;?>"><?php echo $filter['txt'];?></option>
                        <?php }}?>
                    </select>
                  </div>
                </div>
  
                <div class="avisarContainer">
                  <label class="labelSelect datasContainer"><input id="notifyReady" type="checkbox" name="notifyReady">
                  Avisar quando estiver pronto
                  <i title="Dependendo do número de Linhas e da quantidade de dados ou horário da consulta, o relatório pode levar alguns minutos para ser criado. Se quiser podemos avisar quando estiver tudo pronto." style="color:yellow; font-size:15px" class="fas fa-question-circle"></i>
                  </label>
                </div>
            </div>
        </form>

        <div class="btsFiltro">
            <button title="Buscar" type="button" class="btn btn-warning btnRelatorio btnBuscar" style="vertical-align: middle;" onclick="gerarRelatorioConsolidado()"> <i class="fa fa-search"></i><b>Buscar</b></button>
        </div>

        <div class="agendamentos">
            <input type="hidden" id="vendoAgenda" value=""/>
            <input type="hidden" id="errorViagem" value="0"/>
            <span class="row bg-info p-1" style="position: fixed; z-index: 2; top: 0; font-size:80%;">Agendamentos Restantes para hoje: <b class="ml-1" id="agendamentosLeft"><?php echo $agendaLeft;?></b></span>
            <div class="legendaAgendamentos">
  
              <b class="btn btn-warning btnAgPendente"><i class="fas fa-pause"></i> Pendentes
                <i class="qtdAgenda"><?php echo $pendentes;?></i>
              </b>
  
              <b class="btn btn-success btnAgPronto"><i class="fas fa-bookmark"></i> Prontos
                <i class="qtdAgenda"><?php echo $prontos;?></i>
              </b>
              
            </div>
  
            <div class="horasAgendamento">
                <b><i class="fas fa-info-circle"></i> Agendamentos feitos:</b>
                <i><b>- Até 11:00 ficarão disponíveis a partir das 13:00;</b></i>
                <i><b>- A partir de 12:00 ficarão disponíveis a partir das 15:00;</b></i>
                <i><b>- A partir das 16:00 ficarão disponíveis no dia seguinte.</b></i>
            </div>
  
            <ul class="listaAgendamentos">
              <?php 
              if(count($agendamentos) > 0):
              foreach($agendamentos as $key => $agendamento): 
              ?>
  
                <?php if($key == 0):?>
                  <b class="bg-info p-1 dataAgenda" id="<?php echo date("d-m-Y", strtotime($agendamento->created_at)) ;?>"><?php echo date("d/m/Y", strtotime($agendamento->created_at));?></b>
                <?php elseif(date("d/m/Y", strtotime($agendamentos[($key-1)]->created_at)) != date("d/m/Y", strtotime($agendamento->created_at))):?>
                  <b class="bg-info p-1 dataAgenda" id="<?php echo date("d-m-Y", strtotime($agendamento->created_at)) ;?>"><?php echo date("d/m/Y", strtotime($agendamento->created_at));?></b>
                <?php endif;?>
  
                <li title="<?php echo ($agendamento->isready == 0) ? 'Relatório ainda não está disponível' : 'Relatório disponível'; ?>" class="rounded show <?php echo ($agendamento->isready == 0) ? 'bg-warning agPendente' : 'bg-success agPronto'; ?>">
                  <div class="row bg-primary text-white mx-0 mb-1 p-1 w-100 d-flex align-items-center">
                    <div class="col col-6 p-0 m-0 text-left">
                      Agendamento # <b><?php echo $agendamento->id;?></b>
                    </div>
                    <div class="btnsAgenda">
  
                      <?php if($agendamento->isready == 1):?>
                        <i title="Ver Agendamento" class="fas fa-eye bg-success p-1 viewDate-<?php echo date('d-m-Y', strtotime($agendamento->created_at));?>" onclick="gerarRelatorioConsolidado(false, <?php echo $agendamento->id;?>)"></i>
                      <?php endif;?>
                      <?php if($agendamento->isready == 0):?>
                        <i title="Excluir Agendamento" class="fas fa-trash-alt bg-danger p-1 delDate-<?php echo date('d-m-Y', strtotime($agendamento->created_at));?>" onclick="excluirAgenda(<?php echo $agendamento->id;?>, '<?php echo date('d-m-Y', strtotime($agendamento->created_at));?>', this)"></i>
                      <?php endif;?>
                      <i title="Expandir Detalhes" class="fas fa-expand-alt detalhaAgenda p-1" onclick="expandAgenda(this)"></i>
  
                    </div>
                  </div>
                  <div class="px-2 w-100 <?php echo ($agendamento->isready == 0) ? 'text-dark' : 'text-white'; ?>">
                    <span><b>Agendado em: </b><?php echo date("d/m/Y - H:i", strtotime($agendamento->created_at)) ;?></span>
                    <?php if($agendamento->isready == 1):?>
                    <hr class="w-100 mt-0 mb-0">
                    <span><b>Gerado em: </b><?php echo date("d/m/Y - H:i", strtotime($agendamento->updated_at)) ;?></span>
                    <?php endif;?>
                    <hr class="w-100 mt-0 mb-0">
                    <span>
                      <b>Data Início: </b><?php echo date("d/m/Y", strtotime($agendamento->data_inicio)) ;?> - 
                      <b>Data Fim: </b><?php echo date("d/m/Y", strtotime($agendamento->data_fim)) ;?>
                    </span>
                    <hr class="w-100 mt-0">
                    <span><b>Sentido: </b><?php echo $agendamento->sentidotxt;?></span>
                    <hr class="w-100 mt-0 mb-0">
                    <span><b>Pontualidade: </b><?php echo $agendamento->pontualtxt;?></span>
                    
                    <?php if(count($agendamento->linhas) > 0):?>
                      <hr class="w-100 mt-0 mb-0">
                      <span><b><?php echo (count($agendamento->linhas) == 1) ? 'Linha' : 'Linhas';?>:</b></span>
                      <div class="linhasAgenda">
                        <?php foreach($agendamento->linhas as $linhaAg):?>
                          <i><?php echo $linhaAg;?></i>
                        <?php endforeach;?>
                      </div>
                    <?php endif;?>
                    
                  </div>
                  
  
                </li>
              <?php endforeach; else:?>
                <b title='Clique aqui para ir para aba "Filtros"' class="btn btn-info w-100 text-start semagenda mt-3" style="white-space: normal !important;">Nenhum agendamento. <br> Realize agendamentos na aba "Filtros" selecionando um período a partir de <?php echo $relDays == 1 ? '1 dia' : ($relDays+1).' dias';?> entre a Data Início e a Data Fim.</b>
              <?php endif; ?>
            </ul>
            
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

    <div class="iframeViagem">
        <div class="col-md-2 col-xs-12 m-0 pb-0" style="text-align: center; margin:auto; flex:0; min-height:unset !important;">
          <span onclick="abortGetViagem()"class="btn btn-danger w-100">Fechar</span>
        </div>
    </div>

</main>