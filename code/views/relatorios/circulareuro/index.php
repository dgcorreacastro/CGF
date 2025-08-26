<main class="py-4">
    <div id="relCircularEuro" class="personContainer">

      <div class="card-body">
          <div class="card-create-header">
              <h2 class="pageTitle"> <button title="Baixar Excel" type="button" class="btn btn-success btnExcel py-2 px-3 m-1 dn" onclick="downloadRelScreen('bodyCircularEuro')"><i class="fas fa-file-excel" style="font-size:22px;color:white"></i></button>
                  <?php if($showRelTimer == 1):?><div class="relsCronometer h5">00:00:00</div><?php endif;?>  
                </h2>
          </div>

          <hr>

          <div class="card-create-body">
              <div class="filterRelResultContainer">
                  <input type="text" id="filterRelResult" class="form-control" placeholder="Digite aqui para filtrar o relatório..."/>
                  <hr>
              </div>

              <div class="TableRelatorios">
                  <table id="table" class="table table-striped customScroll" style="position: sticky; top:0; z-index:3; margin-bottom: 0;">
                      <thead>
                        
                        <tr class="headerTr applyWidth">
                          <th scope="col">Data</th>
                          <th scope="col">Ponto</th>
                        </tr>
                        <tr class="dn headExcel">
                          <th scope="col">Data</th>
                          <th scope="col">Ponto</th>
                          <!-- <th scope="col">Aparelho</th> -->
                        </tr>
                      </thead>
                  </table>
                  <table id="table" class="table table-striped tBodyScroll">
                      <tbody id="bodyCircularEuro"><?php echo $html;?></tbody>
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
        
        <form id="gerarRelat" class="form-horizontal form-label-left input_mask" action="" method="post" target='_blank'>
          <input type="hidden" id="timeAtualiza" value="<?php echo $timeAtualiza; ?>" />
          <input type="hidden" id="notificaScreen" value="Relatório" />
          <input type="hidden" id="notificaDownload" value="Relatório" /> 
          <input type="hidden" id="downloadName" value="" /> 
          <div id="boxContFilter">
            <div class="datasContainer">
              <div>
                <label for="data_inicio" class="form-label">Data In&iacute;cio:</label>
                <input class="form-control" name="data_inicio" type="date" value="<?php echo $data_inicio; ?>" max="<?php echo date($dateEnd, strtotime("- 1 day")); ?>" id="data_inicio">
              </div>
              <div>
                <label for="data_fim" class="form-label">Data Fim:</label>
                <input class="form-control" name="data_fim" type="date" value="<?php echo $data_fim; ?>" min="<?php echo $dataIni; ?>" max="<?php echo $dateEnd; ?>" id="data_fim">
              </div>
            </div>
            <div>
              <div>
                <label for="carro" class="form-label">Carro:</label>
                <select name="carro" id="carro" class="form-control filtroSelect2">
                  <option value="0">Todos</option>
                  <?php foreach($vans as $van):?>
                  <option value="<?php echo $van['vanId'];?>"><?php echo $van['NomeVan'];?></option>
                  <?php endforeach;?>
                </select>
              </div>
            </div>

            <!-- <div>
              <div>
                <label for="distancia" class="form-label">Distância do Ponto Até: <b id="dist_value"></b></label>
                <input name="distancia" id="distancia" type="range" min="1" max="10" class="form-control p-0" list="markers" step="0.1" value="10"/>
                <datalist id="markers">
                  <option value="1" label="1"></option>
                  <option value="2" label="2"></option>
                  <option value="3" label="3"></option>
                  <option value="4" label="4" ></option>
                  <option value="5" label="5"></option>
                  <option value="6" label="6"></option>
                  <option value="7" label="7"></option>
                  <option value="8" label="8"></option>
                  <option value="9" label="9"></option>
                  <option value="10" label="10"></option>
                </datalist>
              </div>
            </div> -->
    
            <!-- <div>
              <div>
                <label for="device_id" class="form-label">Aparelho:</label>
                <select name="device_id" id="device_id" class="form-control filtroSelect2">
                  <option value="">Todos</option>
                  <?php foreach($vans as $van):?>
                  <option value="<?php echo $van['deviceId'];?>"><?php echo $van['nomeDevice'];?></option>
                  <?php endforeach;?>
                </select>
              </div>
            </div> -->
    
            <div class="avisarContainer">
              <label class="labelSelect datasContainer"><input id="notifyReady" type="checkbox" name="notifyReady">
              Avisar quando estiver pronto
              <i title="Dependendo do número de Linhas ou Grupos e da quantidade de dados ou horário da consulta, o relatório pode levar alguns minutos para ser criado. Se quiser podemos avisar quando estiver tudo pronto." style="color:yellow; font-size:15px" class="fas fa-question-circle"></i>
              </label>
            </div>
    
    
          </div>
        </form>
        <div class="btsFiltro">
          <button title="Buscar" type="button" class="btn btn-warning btnRelatorio btnBuscar" onclick="gertarRelatorioCircEuro()"> <i class="fa fa-search"></i><b>Buscar</b></button>
        </div>
      </div>

</main>
<script>
    // const dist_value = document.querySelector("#dist_value");
    // const distancia = document.querySelector("#distancia");
    // dist_value.textContent = `${distancia.value} ${distancia.value == 1 ? 'metro' : 'metros'}`;
    // distancia.addEventListener("input", (event) => {
    //   dist_value.textContent = `${event.target.value} ${event.target.value == 1 ? 'metro' : 'metros'}`;
    // });
    window.onload = function(e){ 
      checkShowDownload('bodyCircularEuro'); 
    }
</script>