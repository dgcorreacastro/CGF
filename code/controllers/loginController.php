<?php

class loginController extends controller 
{
	public function index()
	{
		if(isset($_SESSION["cLogin"])){
			header("Location: /");
		}
	}

	public function checkCgfVersion(){

		$arr = array();

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

		$cgfLocal = $body->cgfVersion;

		$param		= new Parametro();
		$param		= $param->getParametros();
		$cgfVersion	= $param['cgfVersion'] ?? 1;



        $arr['success'] = true;
		$arr['cgfVersion'] = $cgfVersion;
		$arr['portalName'] = PORTAL_NAME;

		if($cgfVersion != $cgfLocal){
			$_SESSION['clearCache'] = 1;
		}

		$_SESSION['cgfVersion'] = $cgfVersion;

       	echo json_encode($arr);

       	die;

	}

	public function validar()
	{
		
		$dados = array();

		if(!isset($_POST['email']) || !isset($_POST['password']))
		{
			#### RETORNAR PARA VALIDAR #####
			$dados['error'] = true;
			$dados['msg'] 	= "Informe o email e a senha.";

			$this->loadTemplate('auth/login', $dados);
			exit;
		}
		
		$user 		= new Usuarios();
		$nUser		= $user->login($_POST['email'], $_POST['password']);

		if(!$nUser)
		{
			/// CASO NÃO TENHA LOGIN VAI PROCURAR NA TABELA DE USER FRETAMENTO / ESCALA
			$nUser		= $user->loginEscala($_POST['email'], $_POST['password']);
			if(!$nUser)
			{
				$_SESSION['merr'] = "Email ou senha estão incorretos!";

				header("Location: ".BASE_URL);
				exit;

			} else {

				$_SESSION['sysMenu']  		= $user->allMenusFretamento();
				$_SESSION['userMenu'] 		= array(1,23,24,26,27,28,29,31,32,33); // Provisório
				$_SESSION['userMenuLink'] 	= array("/", "/cadastroPax", "/setores/", "/userEscala/", "/escala/", "/parameterEscala/", "/rh/", "/funcoes/", "/colaborador");

				header("Location: ".BASE_URL);
				exit;
			}

		} else {

			//PEGA OS MENUS PARA USUÁRIOS DO TIPO COMUM OU ADMIN
			if($_SESSION['cType'] != 3){

				$_SESSION['sysMenu']  		= $user->allMenus();
				$_SESSION['userMenu'] 		= $user->userMenus($_SESSION['cLogin'], 1);
				$_SESSION['userMenuLink'] 	= $user->userMenus($_SESSION['cLogin'], 2);

			}else{

				//PEGA OS MENUS PARA USUÁRIOS DO TIPO MONITORAMENTO
				$_SESSION['userMenu'] = $user->menusMonitoramento();

			}

			$param	= new Parametro();
			$param	= $param->getParametros();
			
			$cgfVersionamento = $param['cgfVersionamento'];

			$_SESSION['cgfVersionamento'] = $cgfVersionamento;

			header("Location: ".BASE_URL);
			exit;
		}

	}

	public function logout()
	{

		// unset($_SESSION['cLogin']);
		// unset($_SESSION['cName']);

		// unset($_SESSION['sysMenu']);
		// unset($_SESSION['userMenu']);
		// unset($_SESSION['userMenuLink']);

		// unset($_SESSION['cFret']);

		// unset($_SESSION['forbidden']);
		session_destroy();

		if (isset($_SERVER['HTTP_COOKIE'])) {
			$cookies = explode(';', $_SERVER['HTTP_COOKIE']);
			foreach($cookies as $cookie) {
				$parts = explode('=', $cookie);
				$name = trim($parts[0]);
				setcookie($name, '', time()-1000);
				setcookie($name, '', time()-1000, '/');
			}
		}

		header("Location: ".BASE_URL);
		exit;
	}
	

}