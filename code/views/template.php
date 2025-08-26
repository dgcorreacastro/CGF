<!DOCTYPE html>
<html lang="pt-BR">
	<head>

		<?php 
		
		if(isset($_SESSION['clearCache'])){
			unset($_SESSION['clearCache']);
		?>
            <meta http-equiv="Cache-Control" content="no-cache, no-store">
			<meta http-equiv="Pragma" content="no-cache, no-store">
        <?php } ?>
		
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
		<link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/pages-erros.css?v=<?php echo $_SESSION['cgfVersion'] ?? 1;?>" />
		<link href="<?php echo BASE_URL; ?>assets/css/select2.min.css" rel="stylesheet" />

		<link href="<?php echo BASE_URL; ?>assets/editor/richtext.min.css" type="text/css" rel="stylesheet"/>

		<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.min.js"></script> -->
		<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/html2canvas@1.0.0-rc.1/dist/html2canvas.min.js"></script>

		
		<!-- <script type="text/javascript" src="https://html2canvas.hertzen.com/dist/html2canvas.js"></script> -->

	</head>
	<body id="body" style="background-image: url('<?php echo BASE_URL; ?>assets/images/background.jpeg')">
		
		<?php if(isset($_SESSION["cLogin"])) {
			if($_SESSION['cType'] != 3){ ?>
			<i class="fa fa-bars openMenuMob mnon noPrint" aria-hidden="true"></i>
			<div class="newMenu noPrint mnon">
				<?php if(isset($_SESSION['sysMenu'])){  		
					foreach($_SESSION['sysMenu'] AS $menus)
					{
					
						if($_SESSION['cType'] == 1 || in_array($menus['id'], $_SESSION['userMenu']) ) 
						{ ?>
							<?php if(isset($_REQUEST) && isset($_REQUEST['url'])){?>
							<ul class="menuItem mnon <?php echo $menus['link'] == '/'.$_REQUEST['url'] || 
												$menus['link'] == '/'.dirname($_REQUEST['url']).'/' ||
												$menus['link'] == '/'.dirname($_REQUEST['url'])
												? 'current':'' ?>">
							<?php }else{?>
							<ul class="menuItem mnon <?php echo $menus['link'] == '/' ? 'current':'' ?>">
							<?php }
							if($menus['link'] == "#") { ?>
								
									<a class="newMenuLinks paiMenu mnon" id="menu-<?php echo $menus['id'];?>" href="#">
										<i class="<?php echo $menus['icon'];?> mnon" aria-hidden="true"></i>
										<b class="mnon"><?php echo $menus['name'];?></b>
										<i class="fa fa-plus-circle maisMenu mnon" aria-hidden="true"></i>
									</a>
									<ul class="subMenu mnon" id="link-<?php echo $menus['id'];?>">

										<?php foreach($menus['sub'] AS $sub){ 
											if($_SESSION['cType'] == 1 || in_array($sub['id'], $_SESSION['userMenu']) ) {
										?>
											<li class="mnon">
												<a class="mnon <?php echo isset($_REQUEST) && isset($_REQUEST['url']) &&
												($sub['link'] == '/'.$_REQUEST['url'] || $sub['link'] == '/'.$_REQUEST['url'].'/' ||
												$sub['link'] == strtok($_SERVER["REQUEST_URI"], '?') ||
												$sub['link'] == '/'.dirname($_REQUEST['url']).'/' ||
												$sub['link'] == '/'.dirname($_REQUEST['url']) ||
												($sub['link'] == '/app/qrcodes' && dirname($_REQUEST['url']) == 'app')) ? 
												'currentSub':''?>" 
												href="<?php echo $sub['link'];?>" onclick="xhr('<?php echo $sub['link'];?>')">
													<i class="<?php echo $sub['icon'];?> mnon" aria-hidden="true"></i> 
													<?php echo $sub['description'];?>
												</a>
											</li>
										<?php } } // End Foreach Sub ?>

									</ul>
								
								
								

							<?php } else {// Else do Link ?>
								
								<a class="newMenuLinks mnon" href="<?php echo $menus['link'];?>">
									<i class="<?php echo $menus['icon'];?> mnon" onclick="xhr('<?php echo $menus['link'];?>')" aria-hidden="true"></i>
									<b class="mnon"><?php echo $menus['name'];?></b>
								</a>
							<?php } ?>
							</ul><hr>				
				<?php   } ?><?php } } }//End id SysMe ?>
			</div>
		
        <div id="app">
			<nav class="noPrint">
				<h4>Bem Vindo(a) <b id="nomeBoasVindas"><?php echo $_SESSION["cName"]; ?></b></h4>
				<a title="Sair" class="navLogOut" href="<?php echo BASE_URL; ?>login/logout" onclick="xhr('/login/logout')">
					<i class="fa fa-power-off" aria-hidden="true"></i>
				</a> 
			</nav>
        <?php } else { ?>
        	<div id="app">
		<?php }; ?>

		<?php $this->loadViewInTemplate($viewName, $viewData); ?>

		</div>
		
		<script type="text/javascript" src="<?php echo BASE_URL; ?>assets/js/jquery.min.js"></script>

		<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
		
    	<script src="<?php echo BASE_URL; ?>assets/js/sweetalert.min.js"></script>
		<script type="text/javascript" src="<?php echo BASE_URL; ?>assets/js/bootstrap.min.js"></script>
		<script src="<?php echo BASE_URL; ?>assets/js/select2.min.js"></script>

		<script type="text/javascript" src="<?php echo BASE_URL; ?>assets/editor/jquery.richtext.min.js"></script>

		<script type="text/javascript" src="<?php echo BASE_URL; ?>assets/js/script.js?v=<?php echo $_SESSION['cgfVersion'] ?? 1;?>"></script>

		<script type="text/javascript" src="<?php echo BASE_URL; ?>assets/js/qrcode.min.js"></script>
		
		<div id="carregando" class="carregando">

			<div class="loader">
				<span></span>
				<span></span>
				<span></span>
				<span></span>
				<span></span>
			</div>
			
			<div class="col-md-2 col-xs-12 m-0 pb-0" style="text-align: center;margin:auto; flex:0">
				<span id="abortGetRelsBtn" style="display:none;" onclick="abortGetRels()" class="btn btn-danger w-100">Cancelar</span>
			</div>
			
			<div class="col-md-2 col-xs-12 m-0 pb-0" style="text-align: center;margin:auto; flex:0">
				<span id="abortGetViagem" style="display:none;" onclick="abortGetViagem()" class="btn btn-danger w-100">Cancelar</span>
			</div>

			<div class="col-md-2 col-xs-12 m-0 pb-0" style="text-align: center;margin:auto; flex:0">
				<span id="abortGetExcel" style="display:none;" onclick="abortGetExcel()" class="btn btn-danger w-100">Cancelar</span>
			</div>
	    </div>
		<div class="timingRels noPrint"></div>
		<div class="logo noPrint"></div>
		<div class="cgfVersionamento">
			<?php if(isset($_SESSION['cgfVersionamento'])):?>
				<span id="cgfVersionTxt"></span><i id="cgfVersionamentoTxt"><?php echo $_SESSION['cgfVersionamento']; ?></i>
			<?php endif;?>
			<?php if(ENVIRONMENT == 'development'):?>
				<p>Desenvolvimento</p>
			<?php endif;?>
		</div>
		<input type="hidden" id="appName" value="<?php echo APP_NAME; ?>" />
		<input type="hidden" id="portalName" value="<?php echo PORTAL_NAME; ?>" />
	</body>
	<!-----//--------------------------------\\-------->
	<!---- ||------------- ESO ---------------||------->
	<!-----\\--------------------------------//-------->
</html>