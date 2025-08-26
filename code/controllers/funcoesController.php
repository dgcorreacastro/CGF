<?php

class funcoesController extends controller 
{

	public function index()
	{
        $data           = array();
        $functions      = new FunctionCollaborador();
		$data['ret']    = $functions->list();
    
		$this->loadTemplate('escalaTrabalho/funcoes/index', $data);
		exit();
	}

    public function create()
    {
        $data           = array();
        $grupos 	    = new Relatorios();
		$data['grupos']= $grupos->getGrupos();

        $this->loadTemplate('escalaTrabalho/funcoes/create', $data);
		exit();
    }

    public function store()
    {
        /// Remove unset gerais \\\
		unset($_SESSION['old']);

        $funcao             = new FunctionCollaborador();

        $_POST['userID']    = $_SESSION['cLogin'];
		$save 	            = $funcao->save($_POST);

        if(is_array($save) && $save['error'] == true)
        {
            $_SESSION['merr'] = $save['msg'];
            $_SESSION['old']  = $_POST;

            unset($_POST);
            header("Location: " . BASE_URL . "funcoes/create");

        } else if($save){

            $_SESSION['ms'] = "Cadastrado com sucesso!";
            unset($_POST);
            header("Location: " . BASE_URL . "funcoes/");

        }else{

            $_SESSION['merr'] = "Ocorreu um erro ao cadastrar, tente novamente!";
            $_SESSION['old']  = $_POST;

            unset($_POST);
            header("Location: " . BASE_URL . "funcoes/create");

        }
		
		exit();
    }

    public function edit()
    {
        $data           = array();
        $setor          = new FunctionCollaborador();
		$data['funcao'] = $setor->get($_GET['id']);

        $grupos 	    = new Relatorios();
		$data['grupos'] = $grupos->getGrupos();
    
        $this->loadTemplate('escalaTrabalho/funcoes/edit', $data);
		exit();
    }

    public function update()
    {

        $funct              = new FunctionCollaborador();
		$save 	            = $funct->update($_POST);

        if(is_array($save) && $save['error'] == true)
        {
            $_SESSION['merr'] = $save['msg'];

            unset($_POST);
            header("Location: " . BASE_URL . "funcoes/edit?id=" . $_POST['id'] );

        } else if($save){

            $_SESSION['ms'] = "Editado com sucesso!";
            unset($_POST);
            header("Location: " . BASE_URL . "funcoes/");

        }else{

            $_SESSION['merr'] = "Ocorreu um erro ao editar, tente novamente!";

            unset($_POST);
            header("Location: " . BASE_URL . "funcoes/edit?id=" . $_POST['id'] );

        }
		
		exit();
    }
    
    public function delete()
    {

        $retorno = array(
			"status" => true,
			"title" => "SUCESSO",
			"text" => "Usuário com sucesso!",
			"icon" => "success",
			"button" => "OK"
		);

		$func = new FunctionCollaborador();

		$delete = $func->delete($_GET['id']);

		if(!$delete['success']){
			$retorno['title'] =  "ERRO";
			$retorno['icon'] = "error";
		}

		$retorno['status'] = $delete['success'];
		$retorno['text'] = $delete['msg'];

		echo json_encode($retorno);
        die();
    }

}