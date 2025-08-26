<?php

class gruposController extends controller 
{

	public function index()
	{
		$dados = array();	
		
		$grupos 			= new Relatorios();
		$dados['grupos']	= $grupos->getGrupos();

		################## TRATA GRUPOS #################
		if(count($dados['grupos'])>0){
			foreach ($dados['grupos'] as $k => $lin){
				$dados['grupos'][$k]['NOME'] = $lin['NOME'];
			}
		}
		#################################################

		$this->loadTemplate('grupos/grupos', $dados);
		exit();
	}


	/**
	 * PARA OS PARAMETROS DOS GRUPOS
	 */
	public function parameters()
	{

		if(isset($_GET['id'])) {
			$group 				= new ParameterGroup();
			$dados['parameter'] = $group->getParameters($_GET['id']);			
			$this->loadTemplate('grupos/parametro', $dados);
			exit;
		}

		$_SESSION['merr'] = "Ocorreu um erro, tente novamente!";
		header("Location: " . BASE_URL . "grupos");
		exit;
	}


	public function updateParameter()
	{

		$group = new ParameterGroup();
		$save = $group->updateParameters($_POST);

		if($save)
			$_SESSION['ms'] = "Edição Salva com sucesso!";
		else
			$_SESSION['merr'] = "Ocorreu um erro ao atualizar, tente novamente!";

		unset($_POST);

		header("Location: " . BASE_URL . "grupos");
		exit();
	}


}