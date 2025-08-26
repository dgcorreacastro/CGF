<?php

class setoresController extends controller 
{

	public function index()
	{
        $data           = array();
        $setor          = new Setores();
		$data['ret']    = $setor->list();
    
		$this->loadTemplate('escalaTrabalho/setores/index', $data);
		exit();
	}

    public function create()
    {
        $data           = array();
        $this->loadTemplate('escalaTrabalho/setores/create', $data);
		exit();
    }

    public function store()
    {
        /// Remove unset gerais \\\
		unset($_SESSION['old']);

        $setor              = new Setores();

        $_POST['userID']    = $_SESSION['cLogin'];
		$save 	            = $setor->save($_POST);

        if(is_array($save) && $save['error'] == true)
        {
            $_SESSION['merr'] = $save['msg'];
            $_SESSION['old']  = $_POST;

            unset($_POST);
            header("Location: " . BASE_URL . "setores/create");

        } else if($save){

            $_SESSION['ms'] = "Cadastrado com sucesso!";
            unset($_POST);
            header("Location: " . BASE_URL . "setores/");

        }else{

            $_SESSION['merr'] = "Ocorreu um erro ao cadastrar, tente novamente!";
            $_SESSION['old']  = $_POST;

            unset($_POST);
            header("Location: " . BASE_URL . "setores/create");

        }
		
		exit();
    }

    public function edit()
    {
        $data           = array();
        $setor          = new Setores();
		$data['setor']  = $setor->get($_GET['id']);

        $this->loadTemplate('escalaTrabalho/setores/edit', $data);
		exit();
    }

    public function update()
    {

        $setor              = new Setores();

		$save 	            = $setor->update($_POST);

        if(is_array($save) && $save['error'] == true)
        {
            $_SESSION['merr'] = $save['msg'];

            unset($_POST);
            header("Location: " . BASE_URL . "setores/edit?id=" . $_POST['id'] );

        } else if($save){

            $_SESSION['ms'] = "Editado com sucesso!";
            unset($_POST);
            header("Location: " . BASE_URL . "setores/");

        }else{

            $_SESSION['merr'] = "Ocorreu um erro ao editar, tente novamente!";

            unset($_POST);
            header("Location: " . BASE_URL . "setores/edit?id=" . $_POST['id'] );

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

		$setor = new Setores();

		$delete = $setor->delete($_GET['id']);

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