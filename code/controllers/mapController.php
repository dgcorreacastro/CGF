<?php

class mapController extends controller {

	public function index() 
	{

		if (!isset($_GET['latitude']) || $_GET['latitude'] == ''
			|| !isset($_GET['longitude']) || $_GET['longitude'] == ''
			|| !isset($_GET['title']) || !isset($_GET['titlePoint'])){
				die("Sem os parâmetros necessários!");
		}

		$dados['latitude'] = $_GET['latitude'];
		$dados['longitude'] = $_GET['longitude'];
		$dados['title'] = $_GET['title'];
		$dados['titlePoint'] = $_GET['titlePoint'];

		$dados['showtop'] = $_GET['showTop'] ?? 0;

		$dados['atualiza'] = $_GET['atualiza'] ?? 0;

		$dados['topName'] = $_GET['topName'] ?? 7;

		$dados['showaddress'] = $_GET['showAddress'] ?? 0;

		$dados['displayName'] = $_GET['displayName'] ?? false;

		$dados['showPano'] = $_GET['showPano'] ?? 1;

		$parameter 	= new Parametro();
        $dados['param'] = $parameter->getParametros();

        $this->loadTemplateExterno('map/index', $dados);
		exit;
	}

}