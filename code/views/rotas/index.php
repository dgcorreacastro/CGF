<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta http-equiv="Cache-Control" content="no-cache, no-store">
		<meta http-equiv="Pragma" content="no-cache, no-store">

		<meta name="description" content="Sistema de Controle de Fretamento do Grupo TP Transportes">
    <meta name="keywords" content="Frotas, fretamento, onibus, controle de fretamento, TP Transporte.">

		<link rel="shortcut icon" href="/assets/favicon/favicon.ico" type="image/x-icon">

    <title><?php echo APP_NAME; ?></title>

    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/style.css?<?php echo time(); ?>" />
    
    <!-- Scripts -->
    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>

    <?php $apiKey = ($param['apiKey_active'] == 1) ? FRONTKEYGOOGLE . '&libraries=places&v=weekly' : 'xxxxxxxxxxxxxxxxxxxxx'; ?>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $apiKey?>"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/togeojson.js"></script>
    <script type="text/javascript" src="<?php echo BASE_URL; ?>assets/js/jquery.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/sweetalert.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_URL; ?>assets/js/bootstrap.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/select2.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_URL; ?>assets/js/script.js?<?php echo time(); ?>"></script>
    <style>
      /* Estilos CSS para a InfoWindow */
      .custom-infowindow {
        background-color: #fff;
        border: 0;
        padding: 5px;
        font-family: Arial, sans-serif;
        font-size: 12px;
        line-height: 1;
        max-width: 300px;
      }
      .custom-infowindow h3 {
        margin: 0 0 5px;
        font-size: 12px;
        color: #333;
      }
      .gm-style-iw button[title="Fechar"] {
        display: none !important;
      }
      .gm-style-iw button[title="Close"] {
        display: none !important;
      }
    </style>
</head>
<body id="bodyMaps">
    <div id="app">
        <main class="py-2">
            
<div id="telaRota">

    <div class="card-create-body">
        <div class="row">
          <div class="col-md-5" style="text-align: center;">
            <hr>
              <h3 id="rotasEncontradas">LINHAS DISPONÍVEIS PARA O SEU ENDEREÇO</h3>
            <hr>
            <form id="formRota" action="">
                <div class="form-group row">
                  <input type="hidden" id="ic" value="<?php echo $ic; ?>" />
                  <div id="formEnd" class="col-sm-12 col-xs-12">
                    <input id="enderecoToken" class="form-control" placeholder="Digite seu endereço" name="endereco" type="text" onkeydown="checkFind(this.value)">
                  </div>
                  <div class="col-md-12" style="text-align: center;">
                    <button id="btnformRota" class="btn btn-primary btn-lg" style="width: 100%;">
                      BUSCAR PELO ENDEREÇO
                    </button>
                  </div>
                </div>

                <hr style="width:100%; border-color: white; margin: 30px 0;"/>
                <div class="form-group row">
                  <div class="col-md-12" style="text-align: center;">
                    <button id="btnformRotaAll" class="btn btn-warning btn-lg" style="width: 100%;">
                      PESQUISAR TODAS LINHAS
                    </button>
                  </div>
                </div>

            </form>
          </div>
          <div class="col-md-7" style="text-align: center;">
            <div id="linhasEncontradas">
              <hr>
                <h3 id="rotasEncontradas">ITINERÁRIOS ENCONTRADOS:</h3>
              <hr>

              <table class="table table-striped">
              <thead>
                <tr>
                  <th scope="col">Descrição</th>
                  <th scope="col">Linha</th>
                </tr>
              </thead>
              <tbody id="linhasItiner"></tbody>
              </table>
            </div>
          </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="allModais"></div>

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

