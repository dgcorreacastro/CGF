<?php

class usuariosController extends controller 
{

	public function index()
	{
		$user   	= new Usuarios();
		$dados['users'] = $user->getAllUsers();

		// print_r($dados);
		// die;
		$this->loadTemplate('usuarios/usuarios', $dados);
		exit();
	}

	public function create()
	{
		$dados = array();	
		
		$relGer 				= new Relatorios();
		$dados['linhas']		= $relGer->getLinhas();
		$dados['carros']		= $relGer->getCarros();

		$user 					= new Usuarios();
		$dados['grupos']		= $user->acessoGrupo();
		$dados['gruposUser']    = $user->grupoUsers();
		$dados['menusSys']		= $user->allMenus(true);

		$this->loadTemplate('usuarios/usuariosCreate', $dados);
		exit();
	}

	public function salvar()
	{ 

		$dados 		= array();
		$user   	= new Usuarios();
		$save 		= $user->salvarUsuario($_POST);

		if($save)
			$_SESSION['ms'] = "Cadastrado com sucesso!";
		else
			$_SESSION['merr'] = "Ocorreu um erro ao cadastrar, tente novamente!";
		
		unset($_POST);

		header("Location: " . BASE_URL . "usuarios/");
		exit();
	}


	public function salvarAjax()
	{

		$user   	= new Usuarios();
		$save 		= $user->salvarUsuario($_POST);

		echo json_encode($save);
		die();

	}

	public function editar()
	{
		$dados 			= array();
		$user   		= new Usuarios();

		if(isset($_GET['id'])){
			$relGer 				= new Relatorios();
			$dados['linhas']		= $relGer->getLinhasNot($_GET['id']);
			$dados['carros']		= $relGer->getCarrosNot($_GET['id']);
			$dados['grupos']		= $user->acessoGrupoNot($_GET['id']);
			$linhasIn 			    = $relGer->getLinhasIn($_GET['id']);
			$carrosIn				= $relGer->getCarrosIn($_GET['id']);
			$gruposIn				= $user->acessoGrupoIn($_GET['id']);
			$dados['userEdt'] 		= $user->getUser($_GET['id']);
			$dados['gruposUser']    = $user->grupoUsers();
			$dados['menusSys']		= $user->allMenus(true);

			if($dados['userEdt']['type'] != 3){
				$dados['menuUser'] 		= $user->userMenus($_GET['id'], 1);
			}
			
			$idsLin = array();
			foreach ($linhasIn as $lin) {
				$idsLin[] = $lin['id'];
			}

			$idsCar = array();
			foreach ($carrosIn as $car) {
				$idsCar[] = $car['id'];
			}

			$idsgr = array();
			foreach ($gruposIn as $gr) {
				$idsgr[] = $gr['id'];
			}

			$dados['linhasIn']		= $linhasIn;
			$dados['carrosIn']		= $carrosIn;
			$dados['gruposIn']		= $gruposIn;

			$dados['linPerm']		= implode(",", $idsLin);
			$dados['carPerm']		= implode(",", $idsCar);
			$dados['grPerm']		= implode(",", $idsgr);
		

			$this->loadTemplate('usuarios/usuarioEdit', $dados);

		} else {

			$_SESSION['merr'] = "Ocorreu um erro, tente novamente!";
			header("Location: " . BASE_URL . "usuarios/");
		}

		exit();
	}

	public function atualizar()
	{

		$dados 		= array();
		$user   	= new Usuarios();
		$save 		= $user->atualizarUsuario($_POST);

		if($save)
			$_SESSION['ms'] = "Edição Salva com sucesso!";
		else
			$_SESSION['merr'] = "Ocorreu um erro ao atualizar, tente novamente!";

		unset($_POST);

		header("Location: " . BASE_URL . "usuarios/");
		exit();
	}

	public function atualizarAjax()
	{

		$user   	= new Usuarios();
		$save 		= $user->atualizarUsuario($_POST);

		echo json_encode($save);
		die();

	}

	public function usuarioDados()
	{

		$user   	= new Usuarios();
		$save 		= $user->usuarioDados($_POST);

		echo json_encode($save);
		die();

	}

	public function passwordReset(){

		$user   	= new Usuarios();
		$save 		= $user->passwordReset($_POST);

		echo json_encode($save);
		die();

	}

	public function deletar()
	{
		$retorno = array(
			"status" => true,
			"title" => "SUCESSO",
			"text" => "Usuário com sucesso!",
			"icon" => "success",
			"button" => "OK"
		);

		$user   = new Usuarios();

		$delete = $user->delUser($_GET['id']);

		if(!$delete['success']){
			$retorno['title'] =  "ERRO";
			$retorno['icon'] = "error";
		}

		$retorno['status'] = $delete['success'];
		$retorno['text'] = $delete['msg'];

		echo json_encode($retorno);
        die();
	}


	/**
	 * PARA USUÁRIO PODER OU NÃO PODER ALTERAR DADOS
	 */


	public function canAltDados(){

		$user = new Usuarios();

		if(isset($_POST['userIDs'])){

			$save = $user->canAltDados($_POST);

		}else{

			$save = $user->canAltDadosSingle($_POST);

		}
		

		echo json_encode($save);
		die();

	}

	/**
	 * PARA OS PARAMETROS DOS USUÁRIOS
	 */
	public function parameters()
	{

		if(isset($_GET['id'])) {
			$user 				= new ParameterUser();
			$dados['parameter'] = $user->getParameters($_GET['id']);
			$dados['idUser']    = $_SESSION['cType'] == 1 ? $_GET['id'] : $_SESSION['cLogin'];

			$this->loadTemplate('usuarios/parameter', $dados);
			exit;
		}

		$_SESSION['merr'] = "Ocorreu um erro, tente novamente!";
		header("Location: " . BASE_URL . "usuarios/");
		exit;
	}

	public function updateParameter()
	{

		$user = new ParameterUser();
		$save = $user->updateParameters($_POST);

		if($save)
			$_SESSION['ms'] = "Edição Salva com sucesso!";
		else
			$_SESSION['merr'] = "Ocorreu um erro ao atualizar, tente novamente!";

		unset($_POST);

		header("Location: " . BASE_URL . "usuarios/");
		exit();
	}



}