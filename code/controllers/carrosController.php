<?php

class carrosController extends controller 
{

	public function index()
	{
		
		$dados = array();

		$carros 			= new Relatorios();
		$dados['carros']	= $carros->getCarros();

		$this->loadTemplate('carros/carros', $dados);
		exit();
	}


}