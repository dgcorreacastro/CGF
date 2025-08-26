<?php

class termsController extends controller {

	public function index() 
	{
        $this->loadTemplate('appCgf/terms/index');
		exit;
	}

    public function edit()
    {
        $dados      = array();

        $parameter 	= new Parametro();
        $param    	= $parameter->getParametros();

        $dados['id']= $_GET['id'];

        if ($_GET['id'] == 1)
        {
            $dados['title']  = "Termos de Uso";
            $dados['content'] = $param['terms'];

        } else if ($_GET['id'] == 2)
        {
            $dados['title'] = "Política de Privacidade";
            $dados['content'] = $param['privacy'];

        } else {

            $_SESSION['merr'] = "Ocorreu um erro, tente novamente!";

            header("Location: " . BASE_URL . "appCgf/terms/index");
		    exit();
        }


        $this->loadTemplate('appCgf/terms/edit', $dados);
		exit;

    }

    public function update()
    {
        $dados 	= array();	
		$emb   	= new Parametro();

		if(isset($_POST['id'])){
			
			$atualRet 	= $emb->updateTerms($_POST);

			if($atualRet)
				$_SESSION['ms'] = "Edição Salva com sucesso!";
			else 
				$_SESSION['merr'] = "Ocorreu um erro ao atualizar, tente novamente!";
			
		} else {
			$_SESSION['merr'] = "Ocorreu um erro ao atualizar, tente novamente!";
		}
	
		unset($_POST);

		header("Location: " . BASE_URL . "terms/");
		exit();
    }

}