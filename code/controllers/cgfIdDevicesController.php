<?php

class cgfIdDevicesController extends controller {

	public function index() 
	{
        $dados      = array();
		$pag    	= 1;
		$int		= 1;
		$device_id 	= 0;
		$model 		= "";
		$carro 		= "";
		$version	= "";
		$cad		= "";
		$lost72		= false;
		$noLoc		= false;
		$noRec72	= false;
		$withRec72	= false;
		$circLine	= false;

		if( isset($_GET['p']) ) $pag = $_GET['p'];
		if( isset($_GET['int']) ) $int = 0;
		if( isset($_GET['device_id']) ) $device_id = $_GET['device_id'];
		if( isset($_GET['model']) ) $model = $_GET['model'];
		if( isset($_GET['carrosFilter']) ) $carro = $_GET['carrosFilter'];
		if( isset($_GET['version']) ) $version = $_GET['version'];
		if( isset($_GET['cad']) ) $cad = $_GET['cad'];
		if( isset($_GET['lost72']) ) $lost72 = true;
		if( isset($_GET['noLoc']) ) $noLoc = true;
		if( isset($_GET['noRec72']) ) $noRec72 = true;
		if( isset($_GET['withRec72']) ) $withRec72 = true;
		if( isset($_GET['circLine']) ) $circLine = true;

		$devices 	= new FaceDevices();
		$getDevices = $devices->getDevices($pag, $int, $device_id, $model, $carro, $version, $cad, $lost72, $noLoc, $noRec72, $withRec72, $circLine);

		$carros 		 = new Relatorios();
		$dados['carros'] = $carros->getCarros();

		$dados['selDevices'] = $getDevices['selDevices'];
		$dados['modelos'] = $getDevices['modelos'];
		$dados['devices'] = $getDevices['devices'];
		$dados['selectVersion'] = $getDevices['selectVersion'];

		$dados['device_id'] = $device_id;
		$dados['model'] = $model;
		$dados['ttPages']   = $getDevices['total'];

        $this->loadTemplate('appCgf/cgfIdDevices/index', $dados);
		exit;
	}


	public function activeInactive()
	{

		ignore_user_abort(false);
        session_write_close();

		$devices = new FaceDevices();

        $activeInactive = $devices->activeInactive($_GET['id'], $_GET['ativo']);

		echo json_encode($activeInactive);

        die();
	}

	public function switchCirc()
	{
		ignore_user_abort(false);
        session_write_close();

		$devices = new FaceDevices();

        $switchCirc = $devices->switchCirc($_GET['id'], $_GET['circular']);

		echo json_encode($switchCirc);

        die();
	}

	public function requestConfig()
	{

		ignore_user_abort(false);
        session_write_close();

		$devices = new FaceDevices();

		$requestConfig = $devices->requestConfig($_POST);

		echo json_encode($requestConfig);

        die();

	}

	public function requestDetections()
	{
		ignore_user_abort(false);
        session_write_close();

		$devices = new FaceDevices();

		$requestDetections = $devices->requestDetections($_POST);

		echo json_encode($requestDetections);

        die();
	}

	public function getVeicAndLocation()
	{
		ignore_user_abort(false);
        session_write_close();

		$devices = new FaceDevices();

		$getVeicAndLocation = $devices->getVeicAndLocation($_POST);

		echo json_encode($getVeicAndLocation);

        die();
	}

	public function getRecognitionsFace()
	{
		$devices = new FaceDevices();

		$getRecognitionsFace = $devices->getRecognitionsFace($_POST);

		echo json_encode($getRecognitionsFace);

        die();

	}

	public function getTryAgainFace()
	{
		$devices = new FaceDevices();

		$getTryAgainFace = $devices->getTryAgainFace($_POST);

		echo json_encode($getTryAgainFace);

        die();

	}

	public function updateFaceCar()
	{
		$devices = new FaceDevices();

		$updateFaceCar = $devices->updateFaceCar($_POST);

		echo json_encode($updateFaceCar);

        die();
	}

	public function changeTintColor()
	{
		$devices = new FaceDevices();

		$changeTintColor = $devices->changeTintColor($_POST);

		echo json_encode($changeTintColor);

        die();
	}

}