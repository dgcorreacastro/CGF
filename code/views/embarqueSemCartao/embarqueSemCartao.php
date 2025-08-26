<main class="py-4">

    <div id="relEmbSemRfid" class="personContainer">

        <div class="card-body">

            <div class="card-body">
                <div class="card-create-header">
                    <div class="card-create-header">
                        <h2 class="pageTitle"> <button title="Baixar Excel" type="button" class="btn btn-success btnExcel py-2 px-3 m-1 dn" onclick="downloadRelScreen('bodyTable')"><i class="fas fa-file-excel" style="font-size:22px;color:white"></i></button></h2>
                        <div class="filterRelResultContainer">
                            <input type="text" id="filterRelResult" class="form-control" placeholder="Digite aqui para filtrar o relatório..."/>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="card-create-body">
                    <div class="TableRelatorios">
                        <table id="table" class="table table-striped customScroll" style="position: sticky; top:0; z-index:2; margin-bottom: 0;">
                            <thead id="thead">
                                <tr class="headerTr topHeader">
                                    <th scope="col" colspan="5">Controle de Acesso</th>
                                    <th scope="col" colspan="4">Embarque</th>
                                    <th scope="col" colspan="4">Desembarque</th>
                                    <th scope="col" colspan="2">Veículo</th>
                                    <th scope="col" colspan="2">Linha</th>
                                    <th scope="col" colspan="2">Viagem</th>
                                </tr>
                                <tr class="headerTr applyWidth">
                                    <th scope="col">Grupo</th>
                                    <th scope="col">ID Embarque</th>
                                    <th scope="col" style="min-width: 270px;">Nome</th>
                                    <th scope="col">Matrícula</th>
                                    <th scope="col">Motivo</th>
                                    <th scope="col" class="tdBorder5">Pt. Referência</th>
                                    <th scope="col">Data</th>
                                    <th scope="col">Logradouro</th>
                                    <th scope="col">Localização</th>
                                    <th scope="col" class="tdBorder5">Pt. Referência</th>
                                    <th scope="col">Data</th>
                                    <th scope="col">Logradouro</th>
                                    <th scope="col">Localização</th>    
                                    <th scope="col" class="tdBorder5">Prefixo</th>
                                    <th scope="col">Placa</th>     
                                    <th scope="col" class="tdBorder5">Prefixo</th>
                                    <th scope="col">Descrição</th>    
                                    <th scope="col" class="tdBorder5">Dt Inicial Real.</th>
                                    <th scope="col">Dt Final Real.</th>                           
                                </tr>
                                <tr class="dn headExcel">
                                    <th scope="col">Grupo</th>
                                    <th scope="col">ID Embarque</th>
                                    <th scope="col">Nome</th>
                                    <th scope="col">Matrícula</th>
                                    <th scope="col">Motivo</th>
                                    <th scope="col">Pt. Referência</th>
                                    <th scope="col">Data</th>
                                    <th scope="col">Logradouro</th>
                                    <th scope="col">Localização</th>
                                    <th scope="col">Pt. Referência</th>
                                    <th scope="col">Data</th>
                                    <th scope="col">Logradouro</th>
                                    <th scope="col">Localização</th>    
                                    <th scope="col">Prefixo</th>
                                    <th scope="col">Placa</th>     
                                    <th scope="col">Prefixo</th>
                                    <th scope="col">Descrição</th>    
                                    <th scope="col">Dt Inicial Real.</th>
                                    <th scope="col">Dt Final Real.</th>    
                                </tr>
                            </thead>
                        </table>
                        <table id="table" class="table table-striped tBodyScroll">
                        <tbody id="bodyTable"></tbody>
                        </table>
                        <div class="wrapper1">
                            <div class="div1"></div>
                        </div>
                        <div class="wrapper1after"></div>
                    </div>
                </div>

                <hr>
                
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
        <input type="hidden" id="relDays" value="<?php echo $relDays; ?>" />
        <input type="hidden" id="notificaScreen" value="Relatório" />
        <input type="hidden" id="notificaDownload" value="Relatório" />
        <input type="hidden" id="downloadName" value="" />     
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
            <i class="intervaloDiasRels">O período máximo permitido é de 1 mês.</i>
            </div>

            <div>
            <div>
                <label for="matricula" class="form-label">Matrícula:</label>
                <input class="form-control" name="matricula" id="matricula"  type="text" >
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

            <div class="avisarContainer">
            <label class="labelSelect datasContainer"><input id="notifyReady" type="checkbox" name="notifyReady">
            Avisar quando estiver pronto
            <i title="Dependendo do número de Linhas ou Grupos e da quantidade de dados ou horário da consulta, o relatório pode levar alguns minutos para ser criado. Se quiser podemos avisar quando estiver tudo pronto." style="color:yellow; font-size:15px" class="fas fa-question-circle"></i>
            </label>
            </div>
        </div>
        </form>
        <div class="btsFiltro">
        <button title="Buscar" type="button" class="btn btn-warning btnRelatorio btnBuscar" onclick="gerarRelatorioEmbSemCartao()"> <i class="fa fa-search"></i><b>Buscar</b></button>
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
          <?php foreach($grupo as $gr): ?>
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