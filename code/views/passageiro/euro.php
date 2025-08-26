<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"> 

  <meta http-equiv="Cache-Control" content="no-cache, no-store">
	<meta http-equiv="Pragma" content="no-cache, no-store">

  <meta name="description" content="Sistema de Controle de Fretamento do Grupo TP Transportes">
  <meta name="keywords" content="Frotas, fretamento, onibus, controle de fretamento, TP Transporte.">

  <link rel="shortcut icon" href="/assets/favicon/favicon.ico" type="image/x-icon">

  <title><?php echo APP_NAME; ?></title>

  <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css" integrity="sha256-+N4/V/SbAFiW1MPBCXnfnP9QSN3+Keu+NlB+0ev/YKQ=" crossorigin="anonymous" />   
  <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/style.css" />
  <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/mapaEuroNew.css" />
  
  <!-- Scripts -->
  <script src="<?php echo BASE_URL; ?>assets/js/sweetalert.min.js"></script>
  <script type="text/javascript" src="<?php echo BASE_URL; ?>assets/js/jquery.min.js"></script>
  <script type="text/javascript" src="<?php echo BASE_URL; ?>assets/js/mapaEuroNewCommon.js"></script>
  <script type="text/javascript" src="<?php echo BASE_URL; ?>assets/js/mapaEuroNewView.js"></script>
		<script src="<?php echo BASE_URL; ?>assets/js/select2.min.js"></script>
  
</head>

<body oncontextmenu="return false;">
  <div id="bodyMapsNew" class="view">
      <div class="loadingMapa">
        <h2>Carregando mapa...</h2>
        <div class="progressBar">
          <span id="progressNumber">0%</span>
        </div>
      </div>

      <div id="appMapaEuro" class="loading">
          
          <input type="hidden" id="mapaBackground" value="<?php echo BASE_URL; ?>assets/images/pontos.jpg?<?php echo time(); ?>" />
            <div id="telaRotaMapaEuro" class="view">
              <div class="mapaEuroNew" id="mapaEuroNew">
                <div class="mapaEuroNewTitle">
                  <?php 
                  $nome = '';
                  $cliente = explode('-', $Cliente);  
                  if(isset($cliente[0])){
                    $nome = ucfirst(strtolower($cliente[0]));
                  }
                  ?>
                  <h3 id="rotasEncontradas" style="margin:0;">Pontos de Embarque - Circular <?php echo $nome ?></h3>
                  <p  class="click" style="margin:0;">Clique sobre o ponto para consultar os horários</p>
                </div>
                <?php 
                  foreach($itens as $k => $mark){ 
                  $pos = json_decode($mark['posicaoIcone']);
                  $top = ($pos->top);
                  $left = ($pos->left);
                  $nomePonto = $mark['nome_ponto'];
                  ?>
                  <div class="pontoMapaEuroNew" id="<?php echo $mark['id'] ?>" idCount="<?php echo $k+1; ?>"
                    style="
                    top: <?php echo $top; ?>px; 
                    left: <?php echo $left; ?>px;">
                      <div class="menuPonto">
                        <span acao="editarPonto">
                          <i class="fas fa-edit"></i>
                          Editar Ponto
                        </span>
                        <span acao="removerPonto">
                          <i class="fas fa-trash-alt"></i>
                          Remover Ponto
                        </span>
                      </div>
                      <span class="loaderMapaEuroNew"></span>
                      <i class="fa fa-window-close closePonto" aria-hidden="true"></i>
                      <span class="nomePonto"><nome><?php echo $nomePonto; ?></nome></span>
                      <div class="erroPonto">
                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                        <errorMsg></errorMsg>
                      </div>
                      <div class="dadosPonto">
                        <div class="horariosMapaNew">
                          <span class="tituloHorarios add" tipo="manha">Manhã</span>
                          <ul class="manha"></ul>
                        </div>

                        <div class="horariosMapaNew">
                          <span class="tituloHorarios add" tipo="tarde">Tarde</span>
                          <ul class="tarde"></ul>
                        </div>
                        
                        <div class="horariosMapaNew">
                          <span class="tituloHorarios">Pico-Almoço</span>
                          <ul class="picoAlmoco"></ul>
                        </div>

                        <div class="horariosMapaNew">
                          <span class="tituloHorarios add" tipo="noite">Noite</span>
                          <ul class="noite"></ul>
                        </div>

                        <div class="horariosMapaNew">
                          <span class="tituloHorarios">Restaurante</span>
                          <ul class="restaurante"></ul>
                        </div>

                      </div>
                  </div>
                <?php } ?>
                <img class="logosPontos" src="<?php BASE_URL; ?>/assets/images/logos.png">
                <div class="legendasMapaNew">
                  <span class="horaVerde">Horário Intermediário</span>
                  <span class="horaAzul">Horário Fixo Restaurante</span>
                  <span class="horaAmarelo">Horário de Pico</span>
                </div>
              </div>
          
            </div>
        </div>
      
    </div>
  </div>
</body>

