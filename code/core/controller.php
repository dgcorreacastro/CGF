<?php

class controller 
{

	public function __construct() 
	{	
	
		if(
			( 
				( isset($_REQUEST) && isset($_REQUEST['url']) && ( 
					$_REQUEST['url'] != "login/validar/" &&
					$_REQUEST['url'] != "login/checkCgfVersion" &&
					$_REQUEST['url'] != "usuarios/passwordReset" &&
					strpos($_REQUEST['url'], 'rotas/itinerario') === false && 
					strpos($_REQUEST['url'], 'rotas/seach') === false && 
					strpos($_REQUEST['url'], 'passageiro/mapsUserItinerario') === false && 
					strpos($_REQUEST['url'], 'passageiro/seach') === false && 
					strpos($_REQUEST['url'], 'passageiro/mapsUser') === false && 
					strpos($_REQUEST['url'], 'passageiro/itinerario') === false && 
					strpos($_REQUEST['url'], 'getDots') === false && 
					strpos($_REQUEST['url'], 'app/') === false && 
					strpos($_REQUEST['url'], 'news') === false && 
					strpos(strtolower($_REQUEST['url']), 'api') === false
				)) ||
				count($_REQUEST) == 0
			 ) && 
			!isset($_SESSION["cLogin"])
		){
			
			$this->loadTemplate('auth/login', []);
			die();

		} else {


			// checar se pode ver a tela 

			// USUÁRIOS DE MONITORAMENTO
			if(isset($_SESSION['cType']) && $_SESSION['cType'] == 3){
				if((isset($_REQUEST['url']) && isset($_SESSION['userMenuLink']))
				&& $_REQUEST['url'] != "login/logout" 
				&& strpos($_REQUEST['url'], 'app/qrcode') === false
				&& empty($_SERVER['HTTP_X_REQUESTED_WITH'])
				&& !isset($_SESSION['forbidden']))
				{
					if(!in_array( "/" . $_REQUEST['url'], $_SESSION['userMenuLink'])){
						$_SESSION['forbidden'] = [
							"code" => "403",
							"msg" => "Você não tem permissão para acessar esse conteúdo.",
						];
						header("Location: /");
						die();
					}
				}
			}
			
			// USUÁRIOS COMUNS
			if(isset($_REQUEST['url']) && isset($_SESSION['cType']) && $_SESSION['cType'] == 2){
				$url = $_REQUEST['url'];
				$url = trim($url, '/');
				if(isset($_SESSION['userMenuLink'])
					&& $url != "login/logout" 
					&& empty($_SERVER['HTTP_X_REQUESTED_WITH'])
					&& $url != "image"
					&& !isset($_COOKIE['PHPTRIPVIEW'])
					&& !isset($_SESSION['forbidden'])
					&& !strpos($_REQUEST['url'], 'map/') === false)
				{

					$erro = 0;
					
					foreach($_SESSION['userMenuLink'] as $valid){
						
						if(is_numeric($valid))
							continue;

						$link = trim($valid, '/');
						$check = substr($url, 0, strlen($valid));
						$check = trim($check, '/');

						if($check == $link){
							$erro = 0;
							break;
						}else{
							$erro = 1;
						}
					}

					if($erro == 1){
						$_SESSION['forbidden'] = [
							"code" => "403",
							"msg" => "Você não tem permissão para acessar esse conteúdo.",
						];
						header("Location: /");
						die();
					}

				}
			
			
			}

		}
		
	}

	public function loadView($viewName, $viewData = array()) {
		extract($viewData);
		require 'views/'.$viewName.'.php';
	}

	public function loadTemplate($viewName, $viewData = array()) {
		require 'views/template.php';
	}

	public function loadTemplateEuroNew($viewName, $viewData = array()) {
		require 'views/templateEuroNew.php';
	}

	public function loadViewInTemplate($viewName, $viewData = array()) {
		extract($viewData);
		require 'views/alertarsistema.php';
		require 'views/'.$viewName.'.php';
	}

	public function loadTemplateExterno($viewName, $viewData = array()) {
		extract($viewData);
		require 'views/'.$viewName.'.php';
	}

	public function createLog($tipo, $idGrupo)
	{
		$log = new LogsAccess();
		$log->create($tipo, $idGrupo);
		return true;
	}

	public function hasPermission($id)
	{
		if($_SESSION['cType'] == 1 || in_array($id, $_SESSION['userMenu']) ) 
		{

			return true;
		}

		return false;
	}

	public function sendMail($para, $mensagem, $subject)
	{

		$data = array(
			"From"			=> "CGF",
			"ReplyTo"		=> "", // TODO: POPULATE WITH REPLY ADDRESS
			"message" 		=> utf8_encode($mensagem),
			"subject" 		=> $subject,
			"to" 			=> $para,
			"atatchements" 	=> [],
			"client" 		=> 0,
			"application" 	=> 9
		);

		$json = json_encode($data);

		try {

			$headers 	= array('Content-Type: application/json');

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, ''); // TODO: POPULATE WITH URL THAT SENDS E-MAILS
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

			$result = curl_exec($ch);

			if (curl_errno($ch)) {
				$response = array("success" => false, "message" => "Erro ao processar");
			} else {
				$response = array("success" => true, "message" => $result);
			}

			curl_close($ch);
			
		} catch (Exception $e) {
			
			$response = array("success" => false, "message" => "Erro ao processar - " . $e->getMessage());
			
		}

		return $response;
	}

}
