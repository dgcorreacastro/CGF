<?php

class parameterEscalaController extends controller 
{
    public $array = array("JANEIRO", "FEVEREIRO", "MARÇO", "ABRIL", "MAIO", "JUNHO", "JULHO", "AGOSTO", "SETEMBRO", "OUTUBRO", "NOVEMBRO", "DEZEMBRO");

	public function index()
	{
        $data               = array();
        $param              = new ParameterEscala();
		$data['param']      = $param->get();
        $users 	            = new UserEscala();
		$data['unidades']   = $users->getUnidades();
		$data['meses']      = $this->array;
       
		$this->loadTemplate('escalaTrabalho/parameters/index', $data);
		exit();
	}

    public function update()
    {
        $param  = new ParameterEscala();
		$save   = $param->update($_POST);

        if( $save ) 
            $_SESSION['ms'] = "Editado com sucesso!";
        else
            $_SESSION['merr'] = "Ocorreu um erro ao editar, tente novamente!";

        unset($_POST);
        header("Location: " . BASE_URL . "parameterEscala/");
		
		exit();
    }

    public function dataGroup()
    {
        
        if( isset($_POST['uniID']) )
        {
            
            $param  = new ParameterEscala();
		    $grop   = $param->get($_POST['uniID']);

            echo json_encode($grop);

        } else {

            $data = array("success"=> false, "msg" => "nem entrou");
            echo json_encode( $data);
        }

        return true;
    }

}