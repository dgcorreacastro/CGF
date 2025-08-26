<?php

class linhasController extends controller 
{

	public function index()
	{
		$dados = array();	
		
		$linhas 			= new Relatorios();
		$dados['linhas']	= $linhas->getLinhas();

		################## TRATA LINHAS #################
		if(count($dados['linhas'])>0){
			foreach ($dados['linhas'] as $k => $lin){
				$dados['linhas'][$k]['NOME'] = $lin['NOME'];
			}
		}
		#################################################

		$this->loadTemplate('linhas/linhas', $dados);
		exit();
	}

	public function linhasXls()
	{

		$linhas  	= new Relatorios();
		$linhasAll	= $linhas->getLinhas();

		################## TRATA LINHAS #################
		$dadosXls  = "<table>";
		$dadosXls .= "<td><strong>Prefixo da Linha</strong></td>";
		$dadosXls .= "<td><strong>Sentido</strong></td>";
		$dadosXls .= "<td><strong>Nome da Linha</strong></td>";
		$dadosXls .= "</tr>"; 

		if( count( $linhasAll ) > 0 )
		{
			foreach ($linhasAll as $k => $lin)
			{
				$dadosXls .= "<tr>";
				$dadosXls .= "<td>". utf8_decode($lin['PREFIXO']) ."</td>";
				$dadosXls .= "<td>". ($lin['SENTIDO'] == 0?"IDA":"VOLTA") ."</td>";
				$dadosXls .= "<td>". utf8_decode($lin['NOME']) ."</td>";
				$dadosXls .= "</tr>";
			}
		}

		#################################################
		$dadosXls .= "</table>";

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Type: application/force-download');
		header("Content-Disposition: attachment;filename=linhas.xls");
		header("Cache-Control:max-age=0");
		header("Cache-Control:max-age=1");
		header('Pragma: no-cache');

		echo $dadosXls;
		echo "<script>window.close();</script>";
		die();

	}

}