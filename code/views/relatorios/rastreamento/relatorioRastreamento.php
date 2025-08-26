<main class="py-4"> 

<style>
  #table th, #table td {
    min-width: 120px !important;
  }
</style>

<div class="personContainer">

  <div class="card-body">
    <div class="card-create-header">
      <h2 class="pageTitle"></h2>
    </div>
      <div class="wrapper1">
        <div class="div1"></div>
      </div>
      <hr>
    <table id="table" class="table table-striped customScroll">
      <thead>
        <tr class="headerTr">
          <th scope="col" style="min-width: 200px;">Grupo</th>
          <th scope="col">Prefixo</th>
          <th scope="col" style="min-width: 400px;">Linha</th>
          <th scope="col">Sentido</th>
          <th scope="col" style="min-width: 150px;">Data</th>
          <th scope="col">Poltrona</th>
        </tr>
      </thead>
      <tbody id="bodyRelMapPass"></tbody>
    </table>
    <hr>
  </div>

</div>
<div class="filtroDivNew">
  <div class="filtrosBtn">
    <div>
      <i class="fa fa-filter" aria-hidden="true"></i><b>FILTROS</b>
    </div>
  </div>
  <form id="gerarRelat" class="form-horizontal form-label-left input_mask" action="" method="post" target='_blank'>
    <div id="boxContFilter">
      <div>
        <div>
          <label for="nome" class="form-label">Nome Passageiro:</label>
          <input id="nome" class="form-control" name="nome" type="text">
        </div>
      </div>

      <div>
        <div>
          <label for="registro" class="form-label">Matrícula:</label>
          <input id="registro" class="form-control" name="registro" type="text">
        </div>

        <div style="width:20%;">
          <label for="dias" class="form-label">Nr. Dias</label>
          <input id="dias" class="form-control" min="1" name="dias" type="number">
        </div>
      </div>

      
      <div>
        <div class="holdFiltroSelect">
          <label for="grupo" class="form-label">Grupo:</label>
          <span class="filtroSelect" title="SELECIONE UM GRUPO" originaltxt="SELECIONE UM GRUPO" checkboxesFiltro="gruposSel"><i class="fa fa-users" aria-hidden="true"></i> <texto>SELECIONE UM GRUPO</texto></span>
        </div>
      </div>
    </div>
  </form>
  <div class="btsFiltro">
    <button title="Baixar Excel" type="button" class="btn btn-success btnRelatorio btnExcel" onclick="gerarRelatorioRastreaPax('#gerarRelat', '/relatorioRastreamento/excel')"><i class="fas fa-file-excel"></i></button>
    <button title="Buscar" type="button" class="btn btn-warning btnRelatorio" onclick="gerarRelatorioRastreaPax('#gerarRelat', '/relatorioRastreamento/resultado', 1, '#bodyRelMapPass')"> <i class="fa fa-search"></i></button>
  </div>
</div>

<div id="modais"></div>

<!-- Modal -->
<div class="modal fade" id="modalPaxSelect" tabindex="-1" role="dialog" aria-labelledby="modalPaxSelectLab" aria-hidden="true">
  <div class="modal-dialog" role="document" style="min-width: 60%;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalPaxSelectLab">SELECIONE UM PASSAGEIRO</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="TableRelatorios">
          <table class="table table-striped">
            <thead>
              <tr style="background-color: #0b9494;">
                <th scope="col">Nome Passageiro Completo</th>
                <th scope="col">Matrícula</th>
              </tr>
            </thead>
            <tbody id="bodyModalPax"></tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<div class="checkboxesFiltro" id="gruposSel">
  <span class="titleCheckboxesFiltro" title="SELECIONE UM GRUPO"><i class="fa fa-users" aria-hidden="true"></i> SELECIONE UM GRUPO</span>
  <i class="fa fa-window-close fechaCheckboxesFiltro" aria-hidden="true"></i>
  <div class="buscaFiltro">
    <input class="form-control buscaFiltroInput" type="text" placeholder="Digite aqui para filtrar..."/>
  </div>
  <div class="checkboxesFiltroLista">
    <?php foreach($grupos as $gr): ?>
      <input type="checkbox" class="grupoCheck checkFiltro" id="gr-<?php echo $gr['ID_ORIGIN'] ?>" value="<?php echo $gr['ID_ORIGIN'] ?>" name="grupo[]" />
      <label for="gr-<?php echo $gr['ID_ORIGIN'] ?>">
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
</main>