<main class="py-4">

    <div id="relListagem" class="personContainer">

      <div class="card-body">
          <div class="card-create-header">
              <h2 class="pageTitle"> <button title="Baixar Excel" type="button" class="btn btn-success btnExcel py-2 px-3 m-1 dn" onclick="downloadRelScreen('tbodyListagem')"><i class="fas fa-file-excel" style="font-size:22px;color:white"></i></button></h2>
              <h4 class="dn" id="totalListagem">Total: <?php echo $totalListagem; ?></h4>
              <div class="filterRelResultContainer">
                <input type="text" id="filterRelResult" class="form-control" placeholder="Digite aqui para filtrar a listagem..."/>
              </div>
          </div>

          <hr>

          <div class="card-create-body">
              <div class="TableRelatorios">
                  <table id="table" class="table table-striped customScroll" style="position: sticky; top:0; z-index:2; margin-bottom: 0;">
                      <thead>
                        <tr class="headerTr topHeader">
                          <th scope="col" colspan="6" especialcolspan="7">Controle de Acesso</th>
                          <th scope="col" colspan="1">Linha Ida</th>
                          <th scope="col" colspan="2">Itinerário Ida</th>
                          <th scope="col" colspan="1">Poltrona Ida</th>
                          <th scope="col" colspan="1">Linha Volta</th>
                          <th scope="col" colspan="2">Itinerário Volta</th>
                          <th scope="col" colspan="1">Poltrona Volta</th>
                          <th scope="col" colspan="1">Linhas Adicionais</th>
                        </tr>
                        <tr class="headerTr applyWidth">
                          <th scope="col" style="min-width: 580px !important;">Nome</th>
                          <th scope="col">Código</th>
                          <th scope="col" style="min-width: 350px !important;">Grupo</th>
                          <th scope="col">Matrícula funcional</th>
                          <th scope="col">Situação</th>
                          <th scope="col">Monitor</th>
                          <th scope="col" class="tdBorder5">Nome</th>
                          <th scope="col" class="tdBorder5">Sentido</th>
                          <th scope="col">Descrição</th>
                          <th scope="col" class="tdBorder5"></th>
                          <th scope="col" class="tdBorder5">Nome</th>
                          <th scope="col" class="tdBorder5">Sentido</th>
                          <th scope="col">Descrição</th>
                          <th scope="col" class="tdBorder5"></th>
                          <th scope="col" class="tdBorder5"></th>
                        </tr>
      
                        <tr class="dn headExcel">
                          <th scope="col">Nome</th>
                          <th><?php echo APP_NAME;?> Pass</th>
                          <th scope="col">Código</th>
                          <th scope="col">Grupo</th>
                          <th scope="col">Matrícula funcional</th>
                          <th scope="col">Situação</th>
                          <th scope="col">Monitor</th>
                          <th scope="col">Nome</th>
                          <th scope="col">Sentido</th>
                          <th scope="col">Descrição</th>
                          <th scope="col">Nome</th>
                          <th scope="col">Sentido</th>
                          <th scope="col">Descrição</th>
                        </tr>
                      </thead>
                  </table>
                  <table id="table" class="table table-striped tBodyScroll">
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
                <label for="nome" class="form-label">Nome:</label>
                <input class="form-control" name="nome" type="text" id="nome">
              </div>
            </div>
      
            <div>
              <div>
                <label for="matricula" class="form-label">Matric. Funcional:</label>
                <input class="form-control" name="matricula" type="text" id="matricula">
              </div>
      
              <div>
                <label for="autocad" class="form-label"><?php echo APP_NAME;?> Pass:</label>
                <select class="form-control filtroSelect2" id="autocad" name="autocad"><option value="0">--</option><option value="1">SIM</option><option value="2">NÃO</option></select>
              </div>
            </div>
      
            <div>
              <div>
                <label for="codigo" class="form-label">C&oacute;digo:</label>
                <input class="form-control" name="codigo" type="text" id="codigo">
              </div>
              <div>
                <label for="situacao" class="form-label">Situa&ccedil;&atilde;o:</label>
                <select class="form-control filtroSelect2" id="situacao" name="situacao"><option value="1">Ativo</option><option value="0">Inativo</option><option value="2">Todos</option></select>
              </div>
            </div>
            
            <div>
              <div class="holdFiltroSelect">
                <label for="linhas" class="form-label">Linhas:</label>
                <span class="filtroSelect" title="SELECIONE UMA LINHA" originaltxt="SELECIONE UMA LINHA" checkboxesFiltro="linhasSel"><i class="fa fa-bus" aria-hidden="true"></i> <texto>SELECIONE UMA LINHA</texto></span>
                <label class="labelSelect"><input id="todosLinha" type="checkbox" name="todosLinha"> Todas as Linhas </label>
              </div>
      
              <div class="holdFiltroSelect">
                <label for="grupo" class="form-label">Grupo:</label>
                <span class="filtroSelect" title="SELECIONE UM GRUPO" originaltxt="SELECIONE UM GRUPO" checkboxesFiltro="gruposSel"><i class="fa fa-users" aria-hidden="true"></i> <texto>SELECIONE UM GRUPO</texto></span>
                <label class="labelSelect"><input id="todosGrupos" type="checkbox" name="todosGrupos"> Todos os Grupos </label>
              </div>
            </div>
      
          </div>
        </form>
        <div class="btsFiltro">
          <button title="Buscar" type="button" class="btn btn-warning btnRelatorio" onclick="gerarRelatorioListagemPax('#gerarRelat', '/relatorioListagem/resultado', 1)"> <i class="fa fa-search"></i></button>
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

    <div class="linhasAdicionais">
        <span class="titleCheckboxesFiltro" title="LINHAS ADICIONAIS"><i class="fa fa-bus" aria-hidden="true"></i> LINHAS ADICIONAIS</span>
        <i class="fa fa-window-close fechaCheckboxesLinhasAdicionais" aria-hidden="true"></i>
        <div class="buscaFiltro">
        <input class="form-control buscaFiltroLinhasAdicionaisInput" type="text" placeholder="Digite aqui para filtrar..."/>
        </div>
        <ul class="checkboxesFiltroLista">
        </ul>
        <div class="checkboxesFiltroBts">
            <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
              <b>Passageiro: <i class="nomePaxLinhasAdicionais"></i></b>
            </div>
            <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                <button id="okCheckFiltro" class="btn btn-dark w-100" type="button">Fechar</button>
            </div>
        </div>
    </div>

</main>
<script>
    window.onload = function(e){ 
      checkShowDownload('tbodyListagem'); 
    }
</script>