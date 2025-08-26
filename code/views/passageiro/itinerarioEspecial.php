<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

		<meta name="description" content="Sistema de Controle de Fretamento do Grupo TP Transportes">
    <meta name="keywords" content="Frotas, fretamento, onibus, controle de fretamento, TP Transporte.">

    <meta http-equiv="Cache-Control" content="no-cache, no-store">
		<meta http-equiv="Pragma" content="no-cache, no-store">

		<link rel="shortcut icon" href="/assets/favicon/favicon.ico" type="image/x-icon">

    <title><?php echo APP_NAME; ?></title>

    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css" integrity="sha256-+N4/V/SbAFiW1MPBCXnfnP9QSN3+Keu+NlB+0ev/YKQ=" crossorigin="anonymous" />   
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/style.css?<?php echo time(); ?>" />
    
    <!-- Scripts -->
    <script type="text/javascript" src="<?php echo BASE_URL; ?>assets/js/jquery.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/sweetalert.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_URL; ?>assets/js/bootstrap.min.js"></script>
		<script src="<?php echo BASE_URL; ?>assets/js/select2.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_URL; ?>assets/js/script.js?<?php echo time(); ?>"></script>
    
</head>
<body id="bodyMaps">
    <div id="app">
        <main class="py-2">
            
<div id="telaRota">

    <div class="card-create-body">
        <div class="row">
          <div class="col-md-5" style="text-align: center;">
            <hr>
              <h3 id="rotasEncontradas">ENCONTRE SEU ITINERÁRIO:</h3>
            <hr>
            <form id="formUser" action="">
                <p>Informe um dos dados abaixo e click em Buscar</p>
                <div class="form-group row">
                  <input type="hidden" id="ic" value="<?php echo $ic; ?>" />
                  <div id="formName" class="col-sm-12 col-xs-12">
                    <input id="name" class="form-control" placeholder="Digite seu nome" name="name" type="text">
                  </div>
                  <div id="formMatricula" class="col-sm-12 col-xs-12">
                    <input id="matricula" class="form-control" placeholder="Digite sua Matrícula" name="matricula" type="text">
                  </div>
                  <div class="col-md-12" style="text-align: center;">
                    <button id="btnformPaxEspecial" class="btn btn-primary btn-lg" style="width: 100%;">
                      BUSCAR
                    </button>
                  </div>
                </div>
            </form>
          </div>
          <div class="col-md-7" style="text-align: center;">
            <div id="linhasEncontradas">
              <hr>
                <h3 id="rotasEncontradas">SEUS DADOS:</h3>
              <hr>

              <table class="table table-striped">
              <tbody id="linhasItiner"></tbody>
              </table>
            </div>
          </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="allModais"></div>

<!-- Modal -->
<div class="modal fade" id="modalPaxSelect" tabindex="-1" role="dialog" aria-labelledby="modalPaxSelectLab" aria-hidden="true">
  <div class="modal-dialog" role="document" style="min-width: 60%;">
    <div class="modal-content">
      <div class="modal-header">
        <div style="width: 100%;display: block;">
          <h5 class="modal-title" id="modalPaxSelectLab" style="width: 100%;display: block;text-align: center;font-weight: 800;margin: 0">QUEM É VOCÊ?</h5> 
          <p style="margin: 0;text-align: center;font-weight: bold;font-style: italic;">Click em cima do seu nome na lista</p>
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="TableCSS">
          <table class="table table-striped">
            <thead>
              <tr style="background-color: #0b9494;">
                <th scope="col" style="color: white;font-size: 20px;">Nome Completo</th>
                <th scope="col" style="color: white;font-size: 20px;">Matrícula</th>
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

</main>
</div>
<div id="carregando" class="carregando">
  <div class="loader">
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
  </div>
</div>
</body>
	<!-----//--------------------------------\\-------->
	<!---- ||------------- ESO ---------------||------->
	<!-----\\--------------------------------//-------->

</html>


