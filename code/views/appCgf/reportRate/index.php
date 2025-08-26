<main class="py-4">
  <style>
    #table th, #table td {
      min-width: 120px !important;
    }
  </style>

    <div id="relReportRate" class="card-body">
      <div class="personContainer">
          <div class="card-body">
              <div class="card-create-header">
                <h2 class="pageTitle"></h2>
              </div>
              <hr>
              <div class="card-create-body">
              </div>
              <div class="TableCSS">
                <div class="wrapper1">
                  <div class="div1"></div>
                </div>
              <table id="table" class="table table-striped customScroll">
                <thead>
                  <tr class="headerTr">
                    <th scope="col" style="min-width: 300px !important;">Grupo</th>
                    <th scope="col" style="min-width: 300px !important;">Linha</th>
                    <th scope="col" style="min-width: 150px !important;">Veículo</th>
                    <th scope="col" style="min-width: 300px !important;">Observação</th>
                    <th scope="col">Data Avaliação</th>
                    <th scope="col">Limpeza Ônibus</th>
                    <th scope="col">Conservação Ônibus</th>
                    <th scope="col">Pontualidade</th>
                    <th scope="col">Cordialidade</th>
                    <th scope="col">Direção Motorista</th>
                  </tr>
                </thead>
                <tbody id="bodyTable"></tbody>
              </table>
              </div>
              <hr>
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
      <input type="hidden" id="itinIda" value="" />
      <input type="hidden" id="itinVolta" value="" />
      <input type="hidden" id="timeAtualiza" value="<?php echo $timeAtualiza; ?>" />
      <input type="hidden" id="viagemID" value="<?php echo $viagemID ?>" />
      <input type="hidden" id="relDays" value="<?php echo $relDays; ?>" />
      <div id="boxContFilter">
        
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
        
        <div>

          <div>
              <label for="lines" class="form-label">Linha:</label>
              <select id="lines" class="form-control">
                <option value="0">Todos</option>
                <?php foreach($linhas as $gr): ?>

                  <option value="<?php echo $gr['ID_ORIGIN'] ?>"> <?php echo $gr['NOME'] ?></option>

                <?php endforeach; ?>
              </select>
          </div>

        </div>

        <div>
          <div>
              <label for="veiculo" class="form-label">Veículo:</label>
              <select id="veiculo" class="form-control">
                  <option value="0">Todos</option>
                  <?php foreach($carros as $cr): ?>

                  <option value="<?php echo $cr['ID_ORIGIN'] ?>"> <?php echo $cr['NOME'] . ' - ' . $cr['MODELO'] . ' - ' . $cr['MARCA'] ?></option>

                  <?php endforeach; ?>
              </select>
          </div>
        </div>

      </div>
    </form>
    <div class="btsFiltro">
      <button title="Baixar Excel" type="button" class="btn btn-primary btnRelatorio" onclick="gerarRelatorioReportRate('/reportRate/excel')"><i class="fas fa-file-excel"></i></button>
      <button title="Buscar" type="button" class="btn btn-warning btnRelatorio" onclick="gerarRelatorioReportRate('/reportRate/resultado', 1)"> <i class="fa fa-search"></i></button>
    </div>
  </div>
</div>
</main>