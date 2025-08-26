<?php

class relatorioCartaoUtilizacaoController extends controller 
{

	public function index() 
	{
		$dados = array();
		####################### MONTA O FILTRO #######################
		$user 				= new Usuarios();
        $dados['grupos'] 	= $user->acessoGrupo();
        $grupo              = $user->acessoGrupo();
        
        ################## TRATA #################
        if(count($dados['grupos'])>0){
            foreach ($dados['grupos'] as $k => $lin){
                $dados['grupos'][$k]['NOME'] = $lin['NOME'];
            }
        }

		####################### GET DADOS REL  #######################
        
		$rel 		 		= new Relatorios();
		$req                = new \stdClass();
        $dados['linhas']	= $rel->getLinhas();
        
        if(isset($_SESSION['cType']) && $_SESSION['cType'] == 1){
            $grIn = [708, 709];
            $grNm = "CEVA LOUVEIRA";
        } else {
            $grUs = array();
            if(count($dados['grupos']) > 0){
                foreach ($dados['grupos'] as $gr){
                    $grUs[] = $gr['ID_ORIGIN'];
                }
            }
            $grIn = count($grUs) > 0 ? implode(",", $grUs) : 0; 
            $grNm = $grupo[0]['NOME'];
        }

        $req->grupo         = $grIn;
        $dadosRel 	  		= $rel->getDadosCartoesUtilizacao($req);
        $html 				= "";

        foreach($dadosRel as $rel){

            $poltrona = explode(";",$rel['CentroCusto']);

            if (count($poltrona) > 1 ) {
                $poltrona = "{$poltrona[0]} - {$poltrona[1]}";
            } else {
                $poltrona = "-";
            }

            $html .= "<tr>";
            $html .= "<td>".$rel['Nome']."</td>";
            $html .= "<td>".$rel['Codigo']."</td>";
            $html .= "<td>".$rel['Grupo']."</td>";
            $html .= "<td>".$rel['MatriculaFuncional']."</td>";
            $html .= "<td>".$poltrona."</td>";
            $html .= "</tr>";
        }
        
        $dados['dadosRel']  = $html;
        $dados['nomeGrupo'] = $grNm;

        $param 				    = new Parametro();
        $param 				    = $param->getParametros(true);
        $dados['timeAtualiza']  = $param['time_atualizar'] ? $param['time_atualizar'] : 20;

		$this->loadTemplate('relatorios/cartaoUtilizacao/relatorioCartaoUtilizacao', $dados);

		exit;
	}

	public function resultado()
	{

        ignore_user_abort(false);
        session_write_close();

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

		$dados 			= array();
        $html 			= "";

        $req     		= new \stdClass();
        $req->qtdDias 	= $body->qtdDias;
        $req->grupo 	= $body->grupo;

        $rel 		 	= new Relatorios();
        $dadosRel 	    = $rel->getDadosCartoesUtilizacao($req);
        $html 			= "";

        foreach($dadosRel as $rel){

            $poltrona = explode(";",$rel['CentroCusto']);

            if (count($poltrona) > 1 ) {
                $poltrona = "{$poltrona[0]} - {$poltrona[1]}";
            } else {
                $poltrona = "-";
            }

            $html .= "<tr class='toMark'>";
            $html .= "<td>".$rel['Nome']."</td>";
            $html .= "<td>".$rel['Codigo']."</td>";
            $html .= "<td>".$rel['Grupo']."</td>";
            $html .= "<td>".$rel['MatriculaFuncional']."</td>";
            $html .= "<td>".$poltrona."</td>";
            $html .= "</tr>";
        }
  
        $dados['html'] = $html;

        echo json_encode($dados);
    	die();
	}

    public function getDataDash()
    {

        $dadosRe = array('success' => false, 'message' => 'Ocorreu um erro ao processar os dados');
        $grIn    = "";

        if(isset($_SESSION['cType']) && $_SESSION['cType'] == 1){

            $grIn = "708, 709";

        } else {

            if (isset($_POST['groups']) && $_POST['groups'] != "") {

                $grIn = $_POST['groups']; 

            } else {

                $grInArr= array();   
                $user   = new Usuarios();
                $grupo  = $user->acessoGrupo();

                foreach ($grupo as $gr){
                    $grInArr[] = $gr['ID_ORIGIN'];
                }

                $grIn = implode(",", $grInArr);
            }
        }

        $rel                = new Relatorios();
        $req                = new \stdClass();

        $req->grupo         = $grIn;
        $req->qtdDias       = $_POST['qtdDias'] ?? 7;
        $req->isScreenUtil  = true;
     
        $cardUtil           = $rel->getCardNotUsedGraphic($req, false, true);

        if ($cardUtil) {
            $dadosRe['success'] = true;
            $dadosRe['message'] = 'OK';
            $dadosRe['cartsPerDayAndLine'] = base64_encode(json_encode($cardUtil));
        }
        
        echo json_encode($dadosRe);
        die();
    }
}