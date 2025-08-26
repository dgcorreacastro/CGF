<?php

class homeController extends controller 
{

	public function index() 
	{

        if(isset($_GET['forbidden'])){
            $_SESSION['forbidden'] = [
                "code" => "403",
                "msg" => "Conteúdo Protegido.",
            ];

            if(!isset($_SESSION['cType'])){
                header("Location: /");
                exit;
            }
        }
		$dados = array();

        $dataIni 	= date("Y-m-d");
        $dateEnd 	= date("Y-m-d");

        if(!isset($_SESSION['cFret'])){

            // CARREGA LAYOUT PARA USUÁRIOS DO TIPO COMUM
            if($_SESSION['cType'] == 2){
                $user 				= new Usuarios();
                $grupo 				= $user->acessoGrupo();

                if(count($grupo) == 0){
                    $this->loadTemplate('home');
                    exit;
                }

                $dados['grupos'] 	= $grupo;

                $rel 		 		= new Relatorios();
                $dados['linhas']	= $rel->getLinhas(null, null, '0');

                ################## TRATA LINHAS #################
                if(count($dados['linhas'])>0){
                    foreach ($dados['linhas'] as $k => $lin){
                        $dados['linhas'][$k]['NOME'] = $lin['PREFIXO'] . " - " . $lin['NOME'] . " - " . $lin['DESCRICAO'] . " - " . ( $lin['SENTIDO'] == 0 ? "ENTRADA" : "RETORNO");
                    }
                }
                #################################################
                $grUs = array();

                if(count($grupo) > 0){
                    foreach ($grupo as $gr)
                        $grUs[] = $gr['ID_ORIGIN'];
                }

                $grIn = count($grUs) > 0 ? implode(",", $grUs) : 0; 
                $grNm = $grupo[0]['NOME'];

                $dados['nomeGrupo'] = $grNm;

                $param 				    = new Parametro();
                $param 				    = $param->getParametros(true);
                $cad_pax_tag            = $param['cad_pax_tag'] ?? 1;
                $dados['timeAtualiza']  = $param['time_atualizar'] ? $param['time_atualizar'] : 10;
                $dados['relDays']       = $param['rel_days'] ?? 7;
                $dados['dataIni'] 	    = $dataIni;
                $dados['dateEnd'] 	    = $dateEnd;
                $dados['cad_pax_tag'] 	= $cad_pax_tag;

                $dados['graphParams']  = $rel->getGraphParams(true);

                $this->loadTemplate('home', $dados);
		        exit;

            }else{

                // CARREGA LAYOUT PARA ADM
                if($_SESSION['cType'] == 1){
                    $this->loadTemplate('homeAdm');
                    exit;
                }
                    

                // CARREGA LAYOUT PARA USUÁRIOS DE MONITORAMENTO
                if($_SESSION['cType'] == 3){
                    $this->loadTemplate('homeMonitoramento');
                    exit;
                }
                    
            }
            
        }
	}

	public function getTaxaOcupacao($req)
	{

        $param          = new Parametro();
        $param          = $param->getParametros(true);
        $cad_pax_tag    = $param['cad_pax_tag'] ?? 1;
        $cad_pax_pics   = $param['cad_pax_pics'] ?? 0;

		$rel 		= new Relatorios();
        $dadosRel   = $rel->getDadosConsolidadoViagem($req, $cad_pax_tag);
        $dataTrata  = array();
        $countPax   = array();
        $hasPax     = array();
        $viagensFace = [];

        if(isset($dadosRel['error'])){
            $retorn = array('error' => true, 'msg' => $dadosRel['msg']);
            return $retorn;
        }
        
        foreach($dadosRel as $k => $ddrel){

            $ddrel = (Object) $ddrel;
            $pax   = 0;

            if($ddrel->TIPO_USUARIO == 2){

                if(!isset($hasPax[$ddrel->IDVIAGEM]) || !in_array($ddrel->TAG, $hasPax[$ddrel->IDVIAGEM])){
                    $hasPax[$ddrel->IDVIAGEM][] = $ddrel->TAG;

                    if(isset($countPax[$ddrel->IDVIAGEM]))
                        $countPax[$ddrel->IDVIAGEM] += 1;
                    else 
                        $countPax[$ddrel->IDVIAGEM] = 1;

                }

            }

            if(isset($countPax[$ddrel->IDVIAGEM]))
                $pax = $countPax[$ddrel->IDVIAGEM];

            if($cad_pax_pics == 1 && ($ddrel->DATAINIREAL != null && $ddrel->DATAINIREAL != "0000-00-00" && $ddrel->DATAFIMREAL != null && $ddrel->DATAFIMREAL != "0000-00-00")){

                $viagensFace[$ddrel->IDVIAGEM] = [
                    'IDVIAGEM'=> $ddrel->IDVIAGEM,
                    'embarcados' => $pax,
                    'cadastrados' => 0,
                    'IDVEIC' => $ddrel->IDVEIC,
                    'DATAINIREAL' => $ddrel->DATAINIREAL,
                    'DATAFIMREAL' => $ddrel->DATAFIMREAL,
                    'SENTIDO' => $ddrel->SENTIDO,
                    'IDTINEREAL' => $ddrel->ITINERARIO_ID
                ];
            }

            $real = $ddrel->SENTIDO == 0 ? $ddrel->DATAFIMREAL : $ddrel->DATAINIREAL;
            $prev = $ddrel->SENTIDO == 0 ? $ddrel->DATAFIMPREV : $ddrel->DATAINIPREVISTO;

            $pontualidade = $rel->trataPontualidade($ranger, null, $real, $prev);
            
            $dataTrata[$ddrel->IDLINHA]['V'][$ddrel->IDVIAGEM]['LIMITEVEIC'] = $ddrel->LIMITEVEIC;
            $dataTrata[$ddrel->IDLINHA]['V'][$ddrel->IDVIAGEM]['PAXEMBARCADO'] = $pax;
            $dataTrata[$ddrel->IDLINHA]['V'][$ddrel->IDVIAGEM]['veiculo_id'] = $ddrel->IDVEIC;
            $dataTrata[$ddrel->IDLINHA]['V'][$ddrel->IDVIAGEM]['DTINREL'] = $ddrel->DATAINIREAL;
            $dataTrata[$ddrel->IDLINHA]['V'][$ddrel->IDVIAGEM]['DTFIMREL'] = $ddrel->DATAFIMREAL;
            $dataTrata[$ddrel->IDLINHA]['V'][$ddrel->IDVIAGEM]['ITINERARIO_ID'] = $ddrel->ITINERARIO_ID;
            $dataTrata[$ddrel->IDLINHA]['V'][$ddrel->IDVIAGEM]['pontualidade'] = $pontualidade;
        }

        if(count($viagensFace) > 0){
            
            $recFilters['viagensFace'] = $viagensFace;
            $treatRecognitions = $rel->treatRecognitions($recFilters);

            if ($treatRecognitions['status'] === true) {
                foreach ($treatRecognitions['embCad'] as $viagenId => $d) {
                    foreach ($dataTrata as &$line) {
                        if (isset($line['V'][$viagenId])) {
                            $line['V'][$viagenId]['PAXEMBARCADO'] = $d['embarcados'];
                        }
                    }
                }
            }
            
        }
       
        ############## Trantando para entregar já pronto para view ###############
        $capacUso = array('limits' => 0, 'embarcados' => 0);

        foreach($dataTrata AS $dados){

            $dados = (Object) $dados;
 
            foreach($dados->V AS $viagens){
                $viagens = (Object) $viagens;
                if($viagens->pontualidade != 8 && $viagens->pontualidade != 4){
                    $capacUso['limits'] += $viagens->LIMITEVEIC;
                    $capacUso['embarcados'] += $viagens->PAXEMBARCADO;
                }
            }
        }

        return array('capacUso' => $capacUso);
	}

    public function getDataDash()
    {
        ignore_user_abort(false);
        session_write_close();
        $dateEnd = date("Y-m-d");
        $dataIni = date("Y-m-d");
        $dadosRe = array('success' => false);

        if($_GET['pontualidade'] == 2)
        {

            $dadosRe['success'] = true;

            $user         = new Usuarios();
            $grupo        = $user->acessoGrupo();
            $dados['grupos']  = $grupo;

            $rel                = new Relatorios();
            $dados['linhas']    = $rel->getLinhas(null, null, '0');

            $req                = new \stdClass();
            $req->data_inicio   = $dataIni;
            $req->data_fim      = $dateEnd;

            if(isset($_SESSION['cType']) && $_SESSION['cType'] == 1){
                $lines = array();
                $linhasCevas = $rel->getLinhas(66, 10);
                if($linhasCevas){
                    foreach ($linhasCevas as $lin){
                        $lines[] = $lin['ID_ORIGIN'];
                    }
                }

                $req->lns = count($lines) > 0 ? implode(",", $lines) : 0;
            } else {
                $lines = array();
                if(count($dados['linhas'])>0){
                    foreach ($dados['linhas'] as $lin){
                        $lines[] = $lin['ID_ORIGIN'];
                    }
                }

              $req->lns  = count($lines) > 0 ? implode(",", $lines) : 0;
            }

            $param 				        = new Parametro();
            $param 				        = $param->getParametros(true);
            $ranger                     = isset($param['ranger_dash']) && $param['ranger_dash'] > 0 ? $param['ranger_dash'] : 10;
            $req->grupo                 = $grupo[0]['ID_ORIGIN'];#359;
            $dadosRe['pontualid']       = $rel->trataPontualidade($ranger, $req);
        } 
        else if ($_GET['cartaoUtiliza'] == 2) {
            $dadosRe['success'] = true;

            $user         = new Usuarios();
            $grupo        = $user->acessoGrupo();
            $dados['grupos']  = $grupo;

            $rel        = new Relatorios();
            $dados['linhas']  = $rel->getLinhas(null, null, '0');
            if(isset($_SESSION['cType']) && $_SESSION['cType'] == 1){
                $grIn = [708, 709];
                $grNm = "CEVA LOUVEIRA";
            } else {
                $grUs = array();
                if(count($grupo) > 0){
                    foreach ($grupo as $gr){
                        $grUs[] = $gr['ID_ORIGIN'];
                    }
                }

                $grIn = count($grUs) > 0 ? implode(",", $grUs) : 0; 
                $grNm = $grupo[0]['NOME'];
            }

            $req                        = new \stdClass();
            $req->grupo                 = $grIn;
            $cardUtil                   = $rel->getDadosCartoesUtilizacao($req, true);
            $dadosRe['cartoesUltizac']  = base64_encode(json_encode($cardUtil));
            $dadosRe['nomeGrupo']       = $grNm;

        } 
        else if ($_GET['taxaOcupa'] == 2) 
        {
            $dadosRe['success'] = true;

            $req                = new \stdClass();
            $req->data_inicio   = $dataIni;
            $req->data_fim      = $dateEnd;
            $rel                = new Relatorios();

            if(isset($_SESSION['cType']) && $_SESSION['cType'] == 1){
                $lines = array();
                $linhasCevas = $rel->getLinhas(66, 10);
                if($linhasCevas){
                    foreach ($linhasCevas as $lin){
                        $lines[] = $lin['ID_ORIGIN'];
                    }
                }

                $req->lns = count($lines) > 0 ? implode(",", $lines) : 0;
            } else {
                $lines = array();
                $rel        = new Relatorios();
                $dados['linhas']  = $rel->getLinhas(null, null, '0');
                if(count($dados['linhas'])>0){
                    foreach ($dados['linhas'] as $lin){
                        $lines[] = $lin['ID_ORIGIN'];
                    }
                }

                $req->lns  = count($lines) > 0 ? implode(",", $lines) : 0; ;
            }
          
            $retorn = $this->getTaxaOcupacao($req);
           
            $dadosRe['taxaOcupacao']  = $retorn['capacUso'];

        }

        echo json_encode($dadosRe);
        die();
    }

    public function atualizaDash()
	{
        ignore_user_abort(false);
        session_write_close();

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

		$dados 				= array();
		$user 				= new Usuarios();
		$grupo 				= $user->acessoGrupo();
		$rel 		 		= new Relatorios();
		################## GET TAXA OCUPAÇÃO #########################
        $req                = new \stdClass();
        $req->data_inicio   = $body->data_inicio;
        $req->data_fim      = $body->data_fim;
		$req->lns 		    = $body->lns;

        $retorn             = $this->getTaxaOcupacao($req);
        $dados['limit'] 	= $retorn['capacUso']['limits'];
        $dados['embarcado'] = $retorn['capacUso']['embarcados'];
		##############################################################
		##################### GET PONTUALIDADE #######################
		$param 				        = new Parametro();
        $param 				        = $param->getParametros(true);
        $ranger                     = isset($param['ranger_dash']) && $param['ranger_dash'] > 0 ? $param['ranger_dash'] : 10;
        $req->grupo 		        = $grupo[0]['ID_ORIGIN'];#359;
        $dados['pontualidades']     = $rel->trataPontualidade($ranger, $req);
        
		##############################################################
        $dados['status'] = true;
        echo json_encode($dados);
        die();
	}

}