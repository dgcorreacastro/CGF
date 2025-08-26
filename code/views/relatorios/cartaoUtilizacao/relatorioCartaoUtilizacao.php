<main class="py-4">

    <div id="relCartaoUtiliza" class="personContainer">
        <input type="hidden" id="cartoesUltizac" value="" />

        <div class="card-body">
            <div class="card-create-header">
                <h2 class="pageTitle"> <b class="h4">&#10148; <?= $nomeGrupo ?> &#10148;</b>
                    <b class="h4" id="diasCartaoTitulo">Últimos 7 dias</b>
                    <button title="Baixar Excel" type="button" class="btn btn-success btnExcel py-2 px-3 m-1 dn" onclick="downloadRelScreen('tbodyListagem')"><i class="fas fa-file-excel" style="font-size:22px;color:white"></i></button>
                </h2>
                    <div class="filterRelResultContainer">
                    <input type="text" id="filterRelResult" class="form-control" placeholder="Digite aqui para filtrar o relatório..."/>
                </div>
            </div>

            <hr>

            <div class="card-create-body">
                <div class="TableRelatorios" style=" display: flex; flex-direction: column; flex-wrap: nowrap; align-items: center;">
                    <table id="table" class="table table-striped customScroll" style="position: sticky; top:0; z-index:3; margin-bottom: 0; max-width:fit-content;">
                        <thead>
                          <tr class="headerTr topHeader">
                            <th scope="col" colspan="5">Controle de Acesso</th>
                          </tr>
                          <tr class="headerTr applyWidth">
                            <th scope="col" style="min-width: 600px;">Nome</th>
                            <th scope="col">Código</th>
                            <th scope="col" style="min-width: 200px;">Grupo</th>
                            <th scope="col">Matrícula funcional</th>
                            <th scope="col">Poltronas</th>
                          </tr>
                          <tr class="dn headExcel">
                            <th scope="col">Nome</th>
                            <th scope="col">Código</th>
                            <th scope="col">Grupo</th>
                            <th scope="col">Matrícula funcional</th>
                            <th scope="col">Poltronas</th>
                          </tr>
                        </thead>
                    </table>             
                    <table id="table" class="table table-striped tBodyScroll" style="max-width:fit-content;">
                        <tbody id="tbodyListagem"><?php echo $dadosRel; ?></tbody>
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
          <input type="hidden" id="downloadName" value="" /> 
            <div id="boxContFilter">
              <div>
                <div>
                  <label for="qtdDias" class="form-label">Quantidade Dias Sem Utilizar:</label>
                  <input class="form-control" min="1" name="qtdDias" type="number" value="7" id="qtdDias">
                </div>
              </div>
      
              <div>
                <div class="holdFiltroSelect">
                  <label for="grupo" class="form-label">Grupo:</label>
                  <span class="filtroSelect" title="SELECIONE UM GRUPO" originaltxt="SELECIONE UM GRUPO" checkboxesFiltro="gruposSel"><i class="fa fa-users" aria-hidden="true"></i> <texto>SELECIONE UM GRUPO</texto></span>
                  <label class="labelSelect"><input id="todosGrupos" type="checkbox" name="todosGrupos"> Todos os Grupos </label>
                </div>
              </div>
              
            </div>
        </form>
        <div class="btsFiltro">
          <button title="Buscar" type="button" class="btn btn-warning btnRelatorio" onclick="gerarRelatorioCartao()"> <i class="fa fa-search"></i></button>
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
<script>
    window.onload = function(e){ 
      checkShowDownload('tbodyListagem'); 
    }
</script>