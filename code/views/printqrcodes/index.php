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

    <title><?php echo APP_NAME; ?> - QRCode - <?php echo $title; ?></title>

    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css" />
    
    <!-- Scripts -->
    <script type="text/javascript" src="<?php echo BASE_URL; ?>assets/js/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_URL; ?>assets/js/qrcode.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_URL; ?>assets/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
      
      #printQr {
        display: flex;
        flex-direction: column;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
        width: 100vw;
        height: 100vh;
        opacity: 0;
        transition: all 250ms;
        overflow: hidden !important;
        background: white;
      }

      #printQr img {
          max-width: 80%;
          max-height: 80%;
          object-fit: contain;
      }

      @media print {
          #printQr img {
              max-width: 100% !important; 
              max-height: 100% !important;
              width: 800px;
              height: 800px;
          }
      }

    </style>
    
</head>
<body id="printQr">
  <?php if($show_print == 1):?>
    <span title="Imprimir QRCode <?php echo $title; ?>" class="btn btn-warning w-auto mb-3 d-print-none" onclick="printQRCode()"><i class="fas fa-print"></i> Imprimir</span>
  <?php endif;?>
  <script type="text/javascript">
      window.onload = function(e){ 
          let qrcode = new QRCode(document.getElementById("printQr"), {
              text: '<?php echo $link; ?>',
              width: 800,
              height: 800
          });

          $('body').attr('title', '');
          setTimeout(() => {
            $('#printQr').css('opacity','1');
            $('body').attr('title', '');
          }, 150);
      }
      function printQRCode() {
        window.print();
      }
  </script>
</body>
</html>