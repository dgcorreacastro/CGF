<?php

class reportRateController extends controller 
{

	public function index() 
	{
		$dados = array();

        $dataIni 	= date("Y-m-d");
        $dateEnd 	= date("Y-m-d");

        $rel 		 		= new Relatorios();
        $dados['linhas']	= $rel->getLinhas();

        ################## TRATA LINHAS #################
        if(count($dados['linhas'])>0){
            foreach ($dados['linhas'] as $k => $lin){
                $dados['linhas'][$k]['NOME'] = $lin['PREFIXO'] . " - " . $lin['NOME'] . " - " . $lin['DESCRICAO'] . " - " . ( $lin['SENTIDO'] == 0 ? "ENTRADA" : "RETORNO");
            }
        }
		##############################################################


        $user 		        = new Usuarios();
        $carros 	        = $user->veiculo();
        $dados['carros'] 	= $carros;

        $param 				    = new Parametro();
        $param 				    = $param->getParametros(true);
        $dados['timeAtualiza']  = $param['time_atualizar'] ? $param['time_atualizar'] : 20;
        $dados['relDays']       = $param['rel_days'] ?? 7;

        $dados['dateEnd'] 	= $dateEnd;
        $dados['dataIni'] 	= $dataIni;

		$this->loadTemplate('appCgf/reportRate/index', $dados);
		exit;
	}

    public function resultado()
	{
		$dados 			= array();
        $html 			= "";

        $req     		    = new \stdClass();
        $req->data_inicio   = $_POST['data_inicio'];
        $req->data_fim 	    = $_POST['data_fim'];
        $req->lines 	    = $_POST['lines'];
        $req->veiculo 	    = $_POST['veiculo'];

        $rel 		 	= new App();
        $dadosRel 	    = $rel->reportRate($req);
        $html 			= "";

        foreach($dadosRel as $rel){
            
            $html .= "<tr>";
            $html .= "<td>". $rel->grupo ."</td>";
            $html .= "<td>". $rel->PREFIXO . '-' . $rel->LINHA ."</td>";
            $html .= "<td>". $rel->MARCA . '-' . $rel->MODELO . '-' . $rel->NOME . '-' . $rel->PLACA ."</td>";
            $html .= "<td>".$rel->obs."</td>";
            $html .= "<td>".$rel->dataAvaliacao."</td>";
            $html .= "<td>".$rel->limpeza." / 5</td>";
            $html .= "<td>".$rel->conservacao." / 5</td>";
            $html .= "<td>".$rel->pontual." / 5</td>";
            $html .= "<td>".$rel->cordial." / 5</td>";
            $html .= "<td>".$rel->direcao." / 5</td>";
            $html .= "</tr>";

        }
  
        $dados['html'] = $html;

        echo json_encode($dados);
    	die();
	}

    public function excel()
    {
        
        $req     		    = new \stdClass();
        $req->data_inicio   = $_GET['dti'];
        $req->data_fim 	    = $_GET['dtf'];
        $req->lines 	    = $_GET['lines'];
        $req->veiculo 	    = $_GET['veiculo'];

        $dadosXls = "<table border='1'>";
        $dadosXls .= "<tr>";
        $dadosXls .= "<td style='width:100px'>GRUPO</td>";
        $dadosXls .= "<td style='width:100px'>LINHA</td>";
        $dadosXls .= "<td>VEICULO</td>";
        $dadosXls .= "<td style='width:100px'>OBSERVACAO</td>";
        $dadosXls .= "<td>DATA AVALIACAO</td>";
        $dadosXls .= "<td>LIMPEZA ONIBUS</td>";
        $dadosXls .= "<td>CONSERVACAO ONIBUS</td>";
        $dadosXls .= "<td>PONTUALIDADE</td>";
        $dadosXls .= "<td>CORDIALIDADE</td>";
        $dadosXls .= "<td>DIRECAO MOTORISTA</td>";
        $dadosXls .= "</tr>"; 

        $rel 		 	= new App();
        $dadosRel 	    = $rel->reportRate($req);

        foreach($dadosRel as $rel){
            
            $dadosXls .= "<tr>";
            $dadosXls .= "<td>". $rel->grupo ."</td>";
            $dadosXls .= "<td>". $rel->PREFIXO . '-' . $rel->LINHA ."</td>";
            $dadosXls .= "<td>". $rel->MARCA . '-' . $rel->MODELO . '-' . $rel->NOME . '-' . $rel->PLACA ."</td>";
            $dadosXls .= "<td>".$rel->obs."</td>";
            $dadosXls .= "<td>".$rel->dataAvaliacao."</td>";
            $dadosXls .= "<td>".$rel->limpeza." / 5</td>";
            $dadosXls .= "<td>".$rel->conservacao." / 5</td>";
            $dadosXls .= "<td>".$rel->pontual." / 5</td>";
            $dadosXls .= "<td>".$rel->cordial." / 5</td>";
            $dadosXls .= "<td>".$rel->direcao." / 5</td>";
            $dadosXls .= "</tr>";

        }

        $dadosXls .= "</table>";

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment;filename=relatorioavalia.xls");
        header("Cache-Control:max-age=0");
        header("Cache-Control:max-age=1");

        echo $dadosXls;
        echo "<script>window.close();</script>";
        die();
    }

}