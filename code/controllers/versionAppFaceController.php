<?php

class versionAppFaceController extends controller {

	public function index() 
	{
        $dados      = array();
        $parameter 	= new Parametro();
        $dados['param'] = $parameter->getParametros();
        
        $this->loadTemplate('appCgf/versionAppFace/index', $dados);
		exit;
	}

    public function update()
    {
		$parametros = new Parametro();

		$atualVersion = $parametros->updateVersionFace($_POST['vAndroid'], $_POST['msgApp'], $_POST['urlAndroid'], $_POST['time_send_infos_face']);

        if($atualVersion)
            $_SESSION['ms'] = "Edição Salva com sucesso!";
        else 
            $_SESSION['merr'] = "Ocorreu um erro ao atualizar, tente novamente!";
	
		unset($_POST);

		header("Location: " . BASE_URL . "versionAppFace/");
		exit();
    }

}