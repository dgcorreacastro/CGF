<!DOCTYPE html>
<html lang="pt-BR">
	<head>
		<meta charset="UTF-8">
		<meta charset="ISO-8859-1">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="Sistema de Controle de Fretamento do Grupo TP Transportes">
  		<meta name="keywords" content="Frotas, fretamento, onibus, controle de fretamento, TP Transporte.">

		<link rel="shortcut icon" href="/assets/favicon/favicon.ico" type="image/x-icon">

		<title><?php echo $title; ?> - <?php echo APP_NAME; ?> PASS</title>
		<link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css" />
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
		<link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/style.css" />

	</head>

    <body id="body" style="background-color: #e3e1e1; overflow-x: hidden !important;">

		<div class="row my-5 d-flex flex-column flex-wrap align-items-center">
			<img style="width: 100px" src="<?php echo BASE_URL; ?>assets/images/logoApp.png">
			<b class="h6"><?php echo APP_NAME; ?> PASS</b>
		</div>
		
        <div class="container mb-5 p-4" style="background-color: #FFF;border-radius: 5px;box-shadow: 1px 1px 1px 1px lightgrey;">
			<?php print_r($content); ?>
		</div>

    </body>
	<!-----//--------------------------------\\-------->
	<!---- ||------------- ESO ---------------||------->
	<!-----\\--------------------------------//-------->
</html>