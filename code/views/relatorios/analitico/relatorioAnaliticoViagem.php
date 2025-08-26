<!DOCTYPE html>
<html lang="pt-BR">
    <head>
		<meta charset="UTF-8">
		<meta charset="ISO-8859-1">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="Sistema de Controle de Fretamento do Grupo TP Transportes">
  		<meta name="keywords" content="Frotas, fretamento, onibus, controle de fretamento, TP Transporte.">

		<link rel="shortcut icon" href="/assets/favicon/favicon.ico" type="image/x-icon">

		
		<title><?php echo APP_NAME; ?></title>
		<link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css" />
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
		<link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/style.css?v=<?php echo $_SESSION['cgfVersion'] ?? 1;?>" />
		<link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/charts.css?v=<?php echo $_SESSION['cgfVersion'] ?? 1;?>" />
		<link href="<?php echo BASE_URL; ?>assets/css/select2.min.css" rel="stylesheet" />

		<link href="<?php echo BASE_URL; ?>assets/editor/richtext.min.css" type="text/css" rel="stylesheet"/>

		<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/html2canvas@1.0.0-rc.1/dist/html2canvas.min.js"></script>

	</head>
    <body style="background-color: #054a4a">
        <main style="width:100% !important;">

            <div id="relAnalitico" class="personContainerIframe">
                <input type="hidden" id="viagemID" value="<?php echo $viagemID ?>" />
                <input type="hidden" id="data_inicio" value="<?php echo $data_inicio ?>" />
                <input type="hidden" id="data_fim" value="<?php echo $data_fim ?>" />
                <input style="display:none;" id="todosGrupos" type="checkbox" name="todosGrupos" checked>
                <input style="display:none;" id="notifyReady" type="checkbox" name="notifyReady" <?php echo $notify == 1 ? 'checked' : ''; ?>>
                <input type="hidden" id="notificaDownload" value="Download Relatório Embarcados Viagem concluído!" /> 
                
                <div class="card-body">
                    <div class="card-create-header">
                        <h2 class="pageTitle">Embarcados <p class="agendamentoTitle badge badge-warning p-2 m-1">Viagem: <?php echo $viagemID; ?></p>
                            <?php if($tagAgenda != ""):?>
                                <p class="agendamentoTitle badge badge-primary p-2 m-1">Agendamento # <?php echo $tagAgenda;?></p>
                            <?php endif;?>
                            <button title="Baixar Excel" type="button" class="btn btn-success btnExcel py-2 px-3 m-1" onclick="downloadRelScreen('bodyTable', 'Embarcados Viagem <?php echo $viagemID;?>')"><i class="fas fa-file-excel" style="font-size:22px;color:white"></i></button>
                        </h2>
                        <div class="filterRelResultContainer show">
                            <input type="text" id="filterRelResult" class="form-control" placeholder="Digite aqui para filtrar o relatório..."/>
                        </div>
                    </div>
    
                    <hr>
    
                    <div class="card-create-body">
                        <div class="TableRelatorios">
                            <table id="table" class="table table-striped customScroll" style="position: sticky; top:0; z-index:2; margin-bottom: 0;">
                                <thead id="thead">
                                    <tr class="headerTr topHeader">
                                        <th scope="col" colspan="2">Veículo</th>
                                        <th scope="col" colspan="5">Controle de Acesso</th>
                                        <th scope="col" colspan="5">Embarque</th>
                                        <th scope="col" colspan="5">Desembarque</th>
                                        <th scope="col" colspan="2">Previsto</th>
                                        <th scope="col" colspan="3">Realizado</th>
                                        <th scope="col" colspan="1" rowspan="2" style="min-width: 72px !important;">Previsto</th>
                                    </tr>
                                    <tr class="headerTr applyWidth">
                                        <th scope="col" style="min-width: 72px !important;">Prefixo</th>
                                        <th scope="col" style="min-width: 72px !important;">Placa</th>
                                        <th scope="col" style="min-width: 100px !important;" class="tdBorder5">Grupo</th>
                                        <th scope="col" style="min-width: 100px !important;">Código</th>
                                        <th scope="col">Nome</th>
                                        <th scope="col" style="min-width: 100px !important;">Matrícula</th>
                                        <th scope="col" style="min-width: 70px !important;">Status</th>
                                        <th scope="col" class="tdBorder5">Pt. Referência</th>
                                        <th scope="col" style="min-width: 72px !important;">Data</th>
                                        <th scope="col">Logradouro</th>
                                        <th scope="col" style="min-width: 90px !important;">Localização</th>
                                        <th scope="col">Imagem</th>
                                        <th scope="col" class="tdBorder5">Pt. Referência</th>
                                        <th scope="col" style="min-width: 72px !important;">Data</th>
                                        <th scope="col">Logradouro</th>
                                        <th scope="col" style="min-width: 90px !important;">Localização</th>
                                        <th scope="col">Imagem</th>
                                        <th scope="col" class="tdBorder5">Itinerário Ida</th>
                                        <th scope="col">Itinerário Volta</th>
                                        <th scope="col" class="tdBorder5">Itinerário</th>
                                        <th scope="col" style="min-width: 70px !important;">Sentido</th>
                                        <th scope="col" style="min-width: 72px !important;">Saída Real<br>Viagem</th>
                                    </tr>
                                    <tr class="dn headExcel">
                                        <th scope="col">Prefixo</th>
                                        <th scope="col">Placa</th>
                                        <th scope="col">Grupo</th>
                                        <th scope="col">Código</th>
                                        <th scope="col">Nome</th>
                                        <th scope="col">Matrícula</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Pt. Referência</th>
                                        <th scope="col">Data</th>
                                        <th scope="col">Logradouro</th>
                                        <th scope="col">Localização</th>
                                        <th scope="col">Imagem</th>
                                        <th scope="col">Pt. Referência</th>
                                        <th scope="col">Data</th>
                                        <th scope="col">Logradouro</th>
                                        <th scope="col">Localização</th>
                                        <th scope="col">Imagem</th>
                                        <th scope="col">Itinerário Ida</th>
                                        <th scope="col">Itinerário Volta</th>
                                        <th scope="col">Itinerário</th>
                                        <th scope="col">Sentido</th>
                                        <th scope="col">Saída Real Viagem</th>
                                    </tr>
                                </thead>
                            </table>
                            <table id="table" class="table table-striped tBodyScroll">
                                <tbody id="bodyTable"><?php echo $dadosRel; ?></tbody>
                            </table>
                            <div class="wrapper1">
                                <div class="div1"></div>
                            </div>
                            <div class="wrapper1after"></div>
                        </div>
                    </div>
                    
                </div>               
        
            </div>
        
        </main>
        <script type="text/javascript" src="<?php echo BASE_URL; ?>assets/js/jquery.min.js"></script>
		
        <script src="<?php echo BASE_URL; ?>assets/js/sweetalert.min.js"></script>
        <script type="text/javascript" src="<?php echo BASE_URL; ?>assets/js/bootstrap.min.js"></script>
        <script src="<?php echo BASE_URL; ?>assets/js/select2.min.js"></script>
      
        <script type="text/javascript" src="<?php echo BASE_URL; ?>assets/editor/jquery.richtext.min.js"></script>
      
        <script type="text/javascript" src="<?php echo BASE_URL; ?>assets/js/script.js?v=<?php echo $_SESSION['cgfVersion'] ?? 1;?>"></script>
      
        <div id="carregando" class="carregando">
          <div class="loader">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
        </div>
      
        <div class="col-md-2 col-xs-12 m-0 pb-0" style="text-align: center;margin:auto; flex:0">
            <span id="abortGetExcel" style="display:none;" onclick="abortGetExcel()" class="btn btn-danger w-100">Cancelar</span>
        </div>
    </body>
</html>