<?php
class imageController extends controller {

	public function index() 
	{

        $dados = array();
        $img = $_GET['img'];
        $imgId = pathinfo(parse_url($img)['path'], PATHINFO_FILENAME);
        $dados['img'] = $img;

        $pax = new Pax();

        if(isset($_SESSION['cType']) && $_SESSION['cType'] == 2 && $pax->checkUserGroup($imgId) && in_array( "/cadastroPax", $_SESSION['userMenuLink'])){

            $this->loadView('image', $dados);

        }else{

            $_SESSION['forbidden'] = [
                "code" => "403",
                "msg" => "Imagem Protegida.",
            ];
            header("Location: /");
            die();
        }
		
	}
}