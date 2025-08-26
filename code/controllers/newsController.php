<?php

class newsController extends controller 
{

    public function index() 
	{
        $_SESSION['forbidden'] = [
            "code" => "404",
            "msg" => "Página não encontrada.",
            "showLogin" => false
        ];
        header("Location: /");
        die();
	}

	public function daily() 
	{

        if(!isset($_GET['token']) || $_GET['token'] == ""){
            $_SESSION['forbidden'] = [
                "code" => "403",
                "msg" => "Não Autorizado.",
                "showLogin" => false
            ];
            header("Location: /");
            die();
        }

        $token = $_GET['token'];
        
        $news = new News();

        $getDados = $news->getDados($token);

        if(!$getDados['status']){
            $_SESSION['forbidden'] = [
                "code" => $getDados['code'] ?? "404",
                "msg" => $getDados['msg'] ?? "Não Autorizado.",
                "showLogin" => false
            ];
            header("Location: /");
            die();
        }

        $this->loadTemplateExterno('news/daily', $getDados['dadosRel']);

		exit;
	}

}