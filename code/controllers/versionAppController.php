<?php

class versionAppController extends controller {

	public function index() 
	{
        $dados      = array();
        $parameter 	= new Parametro();
        $dados['param'] = $parameter->getParametros();
        
        $this->loadTemplate('appCgf/versionApp/index', $dados);
		exit;
	}

    public function update()
    {
		$parametros = new Parametro();

        $readCardApp = isset($_POST['readCardApp']) ? 1 : 0;

		$atualVersion = $parametros->updateVersion($_POST['vAndroid'], $_POST['vIos'], $_POST['msgApp'], $_POST['urlAndroid'], $_POST['urlIos'], $readCardApp);

        if($atualVersion)
            $_SESSION['ms'] = "Edição Salva com sucesso!";
        else 
            $_SESSION['merr'] = "Ocorreu um erro ao atualizar, tente novamente!";
	
		unset($_POST);

		header("Location: " . BASE_URL . "versionApp/");
		exit();
    }

}