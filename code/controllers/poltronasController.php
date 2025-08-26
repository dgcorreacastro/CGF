<?php

class poltronasController extends controller 
{

	public function index()
	{
		$dados = array();		

		$rel 				= new Relatorios();
		$dados['linhas']	= $rel->getLinhas();
		$dados['grupos']	= $rel->getGruposAcesso();

		################## TRATA LINHAS #################
		  if(count($dados['linhas'])>0){
			foreach ($dados['linhas'] as $k => $lin){
				//$pref = explode(" ", $lin['PREFIXO']);
				$dados['linhas'][$k]['NOME'] = $lin['NOME'] . " - " . $lin['DESCRICAO'] . " - " . ( $lin['SENTIDO'] == 0 ? "ENTRADA" : "RETORNO");
			}
		}
		#################################################

		// Parametro Intercala
		$param 			= new Parametro();
		$dados['inter']	= $param->getIntercalada();

		$this->loadTemplate('poltronas/poltronas', $dados);

		exit();
	}

	public function paxCar()
	{
		if (!isset( $_POST['gr'] ))
		{
			$ret = array('error' => true, 'msg' => 'Necessário selecionar um Grupo de Acesso.');
			echo json_encode($ret);
        	die();
		}

		$dados = array();

		######### BUSCA PASSAGEIROS ##########
		$linhas 			= new Linhas();
		$dados['paxs']  	= $linhas->paxCars( $_POST['gr'] );

		$trata = $linhas->paxDataLinha( $_POST['lines'] );
		$retr  = array();
		$alw   = array();
		$c     = 0;

		foreach($trata AS $tt)
		{
			if (
				($tt['MATRICULA_FUNCIONAL'] == "" || $tt['MATRICULA_FUNCIONAL'] == null) ||
			!in_array($tt['MATRICULA_FUNCIONAL'] , $alw)
			){
				$retr[$c]['NOME'] 					= $tt['NOME'];
				$retr[$c]['MATRICULA_FUNCIONAL'] 	= $tt['MATRICULA_FUNCIONAL'];
				$retr[$c]['TAG'] 					= $tt['TAG'];
				$retr[$c]['POLTRONA'] 				= $tt['POLTRONA'];
				$retr[$c]['GRUPO'] 					= $tt['GRUPO'];
				$retr[$c]['SENTIDO'] 				= $tt['SENTIDO'];
				$retr[$c]['SENTID'] 				= $tt['SENTID'];

				$alw[] = $tt['MATRICULA_FUNCIONAL'];

				$c++;
			}

		}

		$dados['dataLinha'] = $retr;
		$dados['intercala'] = $linhas->isIntercalar( $_POST['gr'] );

		echo json_encode($dados);
        die();
	}

	public function paxSavePol()
	{
		$dados = array();	

		##### SAVE PASSAGEIROS ######
		$linhas 		   = new Linhas();
		$dados['success']  = $linhas->paxPolSave( $_POST );

		echo json_encode($dados);
        die();
	}

	public function paxRemovePol()
	{
		$dados = array();	

		##### SAVE PASSAGEIROS ######
		$linhas 		   = new Linhas();
		$dados['success']  = $linhas->paxPolRemove( $_POST );

		echo json_encode($dados);
        die();
	}

	public function saveParamPol()
	{
		$dados = array();	

		##### SAVE PARAMETER ######
		$param 		   		= new Parametro();
		$dados['success']  	= $param->saveParamPol( $_POST );

		echo json_encode($dados);
        die();
	}

	public function print()
	{

		$dados = array();	

		if ( $_GET['g'] == "" || $_GET['l'] == "")
		{
			$_SESSION['merr'] = "FAVOR SELECIONE UM GRUPO E LINHA!";
			$this->loadTemplate('poltronas', $dados);
			exit();
		}

		$linhas 	= new Linhas();
		$grLine 	= $linhas->nameGrAndLine( $_GET['g'], $_GET['l'] );
		$dataLinha 	= $linhas->paxDataLinha( $_GET['l'] );

		if($_GET['t'] == 2)
		{ // XLS

			$dadosXls = "<table border='1'>";
			$dadosXls .= "<tr>";
			$dadosXls .= "<td width=400>GRUPO: ". $grLine['ac']['NOME'] ."</td>";
			$dadosXls .= "<td colspan='2' width=400>LINHA: ".$grLine['line']['PREFIXO']." - ".utf8_decode($grLine['line']['NOME'])."</td>";
			$dadosXls .= "</tr>"; 

			$dadosXls .= "<tr>";
			$dadosXls .= "<td width=400>NOME</td>";
			$dadosXls .= "<td width=400>".utf8_decode("Matrícula")."</td>";
			$dadosXls .= "<td >Poltrona</td>";
			$dadosXls .= "</tr>"; 
	
			foreach($dataLinha as $rel)
			{
	
				$dadosXls .= "<tr>";
				$dadosXls .= "<td width=400>". utf8_decode($rel['NOME']) ."</td>";
				$dadosXls .= "<td width=400>". $rel['MATRICULA_FUNCIONAL'] ."</td>";
				$dadosXls .= "<td>". $rel['POLTRONA'] ."</td>";
				$dadosXls .= "</tr>";
	
			}
	
			$dadosXls .= "</table>";
	
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header("Content-Disposition: attachment;filename=poltronas.xls");
			header("Cache-Control:max-age=0");
			header("Cache-Control:max-age=1");
			header('Content-Transfer-Encoding: binary');
			header('Pragma: public');
	
			echo $dadosXls;
			echo "<script>window.close();</script>";
			die();

		} else {

			######### BUSCA PASSAGEIROS ##########
			$linhas 			= new Linhas();
			$dados['grLine']  	= $grLine;
			$dados['dataLinha'] = $dataLinha;

			$this->loadTemplate('poltronas/poltronasPrint', $dados);
		}
		
		exit();

	}


}