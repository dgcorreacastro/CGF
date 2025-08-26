<?php

// ini_set('memory_limit', '-1');
// ini_set('max_execution_time', 180);

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;

class userEscalaController extends controller 
{

	public function index()
	{
        // Preparate filters
        $pag   = 1;
        $limit = 1; // Option 1 - 15 p/pag, 2 - 30 p/pag, 3 - 50 p/pag , 4 - 100 p/pag
        $unid  = 0;
        $filtroNome = "";

        if( isset($_GET['p']) ) $pag   = $_GET['p'];
        if( isset($_GET['l']) ) $limit = $_GET['l'];
        if( isset($_GET['u'])) $unid  = $_GET['u'];
        if( isset($_GET['fNome'])) $filtroNome = $_GET['fNome'];
        

        $data               = array();
        $user               = new UserEscala();
        $dataRet            = $user->list( $pag, $limit, $unid, $filtroNome ); 
		$data['ret']        = $dataRet['users'];
		$data['unidades']   = $user->getUnidades();
        $data['ttPages']    = $dataRet['total'];
    
		$this->loadTemplate('escalaTrabalho/user/index', $data);
		exit();
	}

    public function create()
    {
        $data               = array();
        $users 	            = new UserEscala();
		$data['unidades']   = $users->getUnidades();

        $grupos 	        = new Relatorios();
		$data['grupos']     = $grupos->getGrupos();
        
        $data['disabled']   = '';

        $this->loadTemplate('escalaTrabalho/user/create', $data);
		exit();
    }

    public function store()
    {
       
        /// Remove unset gerais \\\
		unset($_SESSION['old']);

        $users              = new UserEscala();

        $_POST['userID']    = $_SESSION['cLogin'];
		$save 	            = $users->save($_POST);

        if(is_array($save) && $save['error'] == true)
        {
            $_SESSION['merr'] = $save['msg'];
            $_SESSION['old']  = $_POST;

            unset($_POST);
            header("Location: " . BASE_URL . "userEscala/create");

        } else if($save){

            $_SESSION['ms'] = "Cadastrado com sucesso!";
            unset($_POST);
            header("Location: " . BASE_URL . "userEscala/");

        }else{

            $_SESSION['merr'] = "Ocorreu um erro ao cadastrar, tente novamente!";
            $_SESSION['old']  = $_POST;

            unset($_POST);
            header("Location: " . BASE_URL . "userEscala/create");

        }
		
		exit();
    }

    public function edit()
    {
        $data           = array();
        $users          = new UserEscala();
		$data['lider']  = $users->get($_GET['id']);
		$data['unidades']= $users->getUnidades();

        $grupos 	    = new Relatorios();
		$data['grupos'] = $grupos->getGrupos();
        $data['disabled'] = '';

        if(isset($_SESSION['cFret']) && $_SESSION['cType'] == 1)
            $data['disabled'] = 'disabled';
      
        $this->loadTemplate('escalaTrabalho/user/edit', $data);
		exit();
    }

    public function update()
    {

        $users  = new UserEscala();
		$save   = $users->update($_POST);

        if(is_array($save) && $save['error'] == true)
        {
            $_SESSION['merr'] = $save['msg'];
            $_SESSION['old']  = $_POST;
            unset($_POST);
            header("Location: " . BASE_URL . "userEscala/edit?id=" . $_POST['id'] );

        } else if($save){

            $_SESSION['ms'] = "Editado com sucesso!";
            unset($_POST);
            header("Location: " . BASE_URL . "userEscala/");

        }else{

            $_SESSION['merr'] = "Ocorreu um erro ao editar, tente novamente!";

            unset($_POST);
            header("Location: " . BASE_URL . "userEscala/edit?id=" . $_POST['id'] );

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

		$users = new UserEscala();

		$delete = $users->delete($_GET['id']);

		if(!$delete['success']){
			$retorno['title'] =  "ERRO";
			$retorno['icon'] = "error";
		}

		$retorno['status'] = $delete['success'];
		$retorno['text'] = $delete['msg'];

		echo json_encode($retorno);
        die();
    }

    public function importUser()
    {

        if( 
            isset($_FILES['fileUser']) && 
            $_FILES['fileUser'] != "" && 
            $_FILES['fileUser']['name'] != "" 
        ){
            $error  = false;
			$ext    = explode(".", $_FILES['fileUser']['name']);
			$ext    = $ext[1];

			if ($ext == "xlsx")
				$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
			else if ($ext == "xls")
				$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
			else if ($ext == "csv")
				$reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
			else{
                $error = true;
                $_SESSION['merr'] = "Nenhum Arquivo Carregado!";
			}

            if($error == false)
            {
                $userEscala  = new UserEscala();
                $file 		 = $_FILES['fileUser']['tmp_name'];
                $reader->setReadDataOnly(1);
                $spreadsheet = $reader->load($file);
                $sheetData   = $spreadsheet->getActiveSheet()->toArray();
                $arrErros    = array();
                $ids 	     = array();

                foreach ($sheetData as $k => $t) 
                {
                 
                    if ($k > 0 && ( $t[0] != "" || $t[2] != "" ) )
                    { 
                        $linha = $k + 1;
                        $no = false;
                        // Salva dos dados no banco
                        if( !isset($t[0]) || $t[0] == "" ){
                            $arrErros[$linha]["desc"][] = "Nome";
                            $arrErros[$linha]["has"] = true;
                            $arrErros[$linha]["line"] = $linha;
                            $no = true;
                        }
                      
                        if( !isset($t[2]) || $t[2] == "" ){
                            $arrErros[$linha]["desc"][] = "Email";
                            $arrErros[$linha]["has"] = true;
                            $arrErros[$linha]["line"] = $linha;
                            $no = true;
                        }
                    
                        if($no == false)
                        {
                            $ids[] = $userEscala->importUser( $t );
                        }
                            
                    }
                } // Fim Foreach

                if (count($ids) > 0)
                {
                    $userEscala->updateWithNotHas( $ids );
                }

                $_SESSION['ms'] = "Arquivo Carregado com sucesso!";
                $_SESSION['merrary'] = $arrErros;
                if(count($arrErros) != 0){
                    $_SESSION['checkCadPax'] = true;
                }
            }

        } else {
            $_SESSION['merr'] = "Nenhum arquivo carregado!";
        }

        unset($_FILES);
        header("Location: " . BASE_URL . "userEscala/");

		exit();
    }

}