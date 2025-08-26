<?php

class relatorioSinteticoController extends controller 
{

	private $linhasIniciais = "2592, 2593, 2707, 2710, 2708, 2711, 1169, 1627, 2798, 2799, 1166, 1168, 2796, 2797, 2818, 2819, 1170, 1628, 2801, 2802, 2824, 2825";

	public function index() 
	{
		$dadosRet = array();

		####################### MONTA O FILTRO #######################
        $dataIni 	= date("Y-m-d");
        $dateEnd 	= date("Y-m-d");
		$linhas 			    = new Relatorios();
		$dadosRet['linhas']	    = $linhas->getLinhas(null, null, '0');

		##############################################################
		####################### GET DADOS REL  #######################
		$rel 		 		= new Relatorios();
		$req                = new \stdClass();
		$req->data_inicio 	= $dataIni;
		$req->data_fim 		= $dateEnd;

        if(isset($_SESSION['cType']) && $_SESSION['cType'] == 1){
            $req->lns    = $this->linhasIniciais;
        } else {
            $lines = array();
            if(count($dadosRet['linhas'])>0){
                foreach ($dadosRet['linhas'] as $lin){
                    $lines[] = $lin['ID_ORIGIN'];
                }
            }

            $req->lns    = count($lines) > 0 ? implode(",", $lines) : 0;
        }

         ################## TRATA LINHAS #################
         if(count($dadosRet['linhas'])>0){
            foreach ($dadosRet['linhas'] as $k => $lin){
                $dadosRet['linhas'][$k]['NOME'] = $lin['PREFIXO'] . " - " . $lin['NOME'] . " - " . $lin['DESCRICAO'] . " - " . ( $lin['SENTIDO'] == 0 ? "ENTRADA" : "RETORNO");
            }
        }

        $param 				        = new Parametro();
        $param 				        = $param->getParametros(true);
        $dadosRet['timeAtualiza']   = $param['time_atualizar'] ? $param['time_atualizar'] : 20;
        $dadosRet['relDays']        = $param['rel_days'] ?? 7;
        $dadosRet['showRelTimer']   = $param['show_rel_timer'] ?? 0;
        $dadosRet['cad_pax_tag']    = $param['cad_pax_tag'] ?? 1;

        $agendamentos               = $this->getAgendamentos();
        $prontos                    = 0;
        $pendentes                  = 0;
        
        if(count($agendamentos) > 0){
            foreach($agendamentos as $agenda){
                if($agenda->status == 1){
                    $prontos += 1;
                }else{
                    $pendentes += 1;
                }
            }
        }

        $dadosRet['agendamentos']   = $agendamentos;
        $dadosRet['pendentes']      = $pendentes;
        $dadosRet['prontos']        = $prontos;
        
        $dadosRet['agendaLeft']     = $rel->getAgLeft('agenda_sintetico');

        $dadosRet['dateEnd'] 	    = $dateEnd;
        $dadosRet['dataIni'] 	    = $dataIni;
        $dadosRet['filterPontual']  = $rel->getGraphParams(true);
        
		$this->loadTemplate('relatorios/sintetico/index', $dadosRet);
		exit;
	}

	public function resultado()
	{	

        ignore_user_abort(false);
        session_write_close();

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);
        
		####################### GET DADOS REL  #######################
		$arrayRet 			= array();   
		$rel 		 		= new Relatorios();
		$req                = new \stdClass();
		$req->data_inicio 	= $body->data_inicio;
        $req->data_fim 		= $body->data_fim;
		$req->lns 		    = $body->lns;
        $req->sentido 		= 0;
		$req->pontual 		= $body->pontual;
        $idAgenda           = $body->agenda;   
        $graphParams        = $rel->getGraphParams(true);     

        if($idAgenda == 0){

            $param 				    = new Parametro();
            $param 				    = $param->getParametros(true);
            $cad_pax_tag            = $param['cad_pax_tag'] ?? 1;

            $dadosRel = $rel->getDadosSintetico($req, $cad_pax_tag);
            $filterPontual = isset($req->pontual) && $req->pontual != "" ? $req->pontual : 0;
            $arrayRet = $this->itensSintetico($dadosRel, $filterPontual, $graphParams);

        }else{

            $dadosRel = $rel->getDadosAgendado('agenda_sintetico_ready', $idAgenda);

            $getAgenda = $rel->getAgendamentos('agenda_sintetico', $idAgenda);

            if(count($getAgenda) == 1){

                $agendamento = $getAgenda[0];
                $filterPontual = isset($agendamento->pontual) && $agendamento->pontual != "" ? $agendamento->pontual : 0;
                $arrayRet = $this->itensSintetico($dadosRel, $filterPontual, $graphParams);
                $arrayRet['data_inicio']    = $agendamento->data_inicio;
                $arrayRet['data_fim']       = $agendamento->data_fim;
                $arrayRet['errorViagem']    = $agendamento->errorViagem;

            }

        }
        
		##############################################################
		echo json_encode($arrayRet);
		die();

	}

    private function itensSintetico($dadosRel, $filterPontual, $graphParams){

        $dataTrata  		= array();
        $capacUso			= array();
        $countPax           = array();
        $hasPax             = array();

        $param          = new Parametro();
        $param          = $param->getParametros(true);
        $cad_pax_pics   = $param['cad_pax_pics'] ?? 0;
        $ranger         = isset($param['ranger_dash']) && $param['ranger_dash'] > 0 ? $param['ranger_dash'] : 10;

        $rel = new Relatorios();

        $html   = "";

        $capacUso['limits'] = 0;
        $capacUso['embarcados'] = 0;

        $viagensFace = [];

        foreach($dadosRel AS $k => $ddrel){

            $ddrel  = (Object) $ddrel;

            $pontualidade = $rel->trataPontualidade($ranger, false, $ddrel->DATAFIMREAL, $ddrel->DATAFIMPREV);

            if ($filterPontual != 0 && ($filterPontual != $pontualidade))
                continue;

            $pax = 0;

            if( (!isset($hasPax[$ddrel->IDVIAGEM]) || !in_array($ddrel->TAG, $hasPax[$ddrel->IDVIAGEM])) && $ddrel->TIPO_USUARIO == 2 ){
                $hasPax[$ddrel->IDVIAGEM][] = $ddrel->TAG;
                $countPax[$ddrel->IDVIAGEM] = isset($countPax[$ddrel->IDVIAGEM]) ? ($countPax[$ddrel->IDVIAGEM] + 1) : 1;
            }

            if(isset($countPax[$ddrel->IDVIAGEM]))
                $pax = $countPax[$ddrel->IDVIAGEM];

            if($cad_pax_pics == 1 && ($ddrel->DATAINIREAL != null && $ddrel->DATAINIREAL != "0000-00-00" && $ddrel->DATAFIMREAL != null && $ddrel->DATAFIMREAL != "0000-00-00")){

                $viagensFace[$ddrel->IDVIAGEM] = [
                    'IDVIAGEM'=> $ddrel->IDVIAGEM,
                    'embarcados' => $pax,
                    'cadastrados' => $ddrel->PAXCADASTRADO,
                    'IDVEIC' => $ddrel->IDVEIC,
                    'DATAINIREAL' => $ddrel->DATAINIREAL,
                    'DATAFIMREAL' => $ddrel->DATAFIMREAL,
                    'SENTIDO' => $ddrel->SENTIDO,
                    'IDTINEREAL' => $ddrel->ITINERARIO_ID
                ];
            }

            ######## AJUSTANDO DADOS PARA O JSON ############# 
            $dataTrata[$ddrel->IDVIAGEM]['data']            = date("d/m/Y", strtotime($ddrel->DATAINIPREVISTO));
            $dataTrata[$ddrel->IDVIAGEM]['linha']           = $ddrel->PREFIXO;
            $dataTrata[$ddrel->IDVIAGEM]['descricao']       = $ddrel->NOMELINHA;
            $dataTrata[$ddrel->IDVIAGEM]['sentido']         = $ddrel->SENTIDO == 0 ? "Ida" : "Volta";
            $dataTrata[$ddrel->IDVIAGEM]['dtFinalPrev']     = date("H:i:s", strtotime($ddrel->DATAFIMPREV)); 
            $dataTrata[$ddrel->IDVIAGEM]['DATAFIMPREV']     = $ddrel->DATAFIMPREV;
            $dataTrata[$ddrel->IDVIAGEM]['dtInicReal']      = $ddrel->DATAINIREAL;
            $dataTrata[$ddrel->IDVIAGEM]['DATAFIMREAL']     = $ddrel->DATAFIMREAL;
            $dataTrata[$ddrel->IDVIAGEM]['dtFinalReal']     = $ddrel->DATAFIMREAL ? date("H:i:s", strtotime($ddrel->DATAFIMREAL)) : "";
            $dataTrata[$ddrel->IDVIAGEM]['kmviagem']        = number_format($ddrel->KMVIAGEM, 0, "", '.');
            $dataTrata[$ddrel->IDVIAGEM]['prefixoCar']      = $ddrel->PREFIXOVEIC;
            $dataTrata[$ddrel->IDVIAGEM]['veiculo_id']      = $ddrel->IDVEIC;
            $dataTrata[$ddrel->IDVIAGEM]['capacidade']      = $ddrel->CAPACIDADEVEIC;
            $dataTrata[$ddrel->IDVIAGEM]['embarcados']      = $pax;
            // $dataTrata[$ddrel->IDVIAGEM]['perUse']          = $pax > 0 && $ddrel->CAPACIDADEVEIC > 0 ? ($pax * 100 / $ddrel->CAPACIDADEVEIC) : 0;
            $dataTrata[$ddrel->IDVIAGEM]['pontualidade']    = $pontualidade;
            $dataTrata[$ddrel->IDVIAGEM]['cadastrados']     = $ddrel->PAXCADASTRADO;
            $dataTrata[$ddrel->IDVIAGEM]['DTINIFIM']        = $ddrel->DATAINIREAL ? date("Y-m-d", strtotime($ddrel->DATAINIREAL)) : date("Y-m-d", strtotime($ddrel->DATAINIPREVISTO));
            $dataTrata[$ddrel->IDVIAGEM]['ITINERARIO_ID']   = $ddrel->ITINERARIO_ID;

        }

        if(count($viagensFace) > 0){
            $recFilters['viagensFace'] = $viagensFace;
            $treatRecognitions = $rel->treatRecognitions($recFilters);

            if($treatRecognitions['status'] === true){
                foreach($treatRecognitions['embCad'] as $viagenId => $d){
                    $dataTrata[$viagenId]['embarcados'] = $d['embarcados'];
                    $dataTrata[$viagenId]['cadastrados'] = $d['cadastrados'];
                }
            }
        }

        foreach($dataTrata AS $k => $dados){

            $viagens = (Object) $dados;
            $percursor = " - ";

            if ($viagens->dtInicReal != null && $viagens->dtInicReal != "0000-00-00" && $viagens->DATAFIMREAL != null && $viagens->DATAFIMREAL != "0000-00-00"){

                $seconds    = strtotime($viagens->DATAFIMREAL) - strtotime($viagens->dtInicReal);
                $hours      = floor($seconds / 3600);
                $mins       = floor(($seconds - ($hours*3600)) / 60);
                $percursor  = sprintf("%02d", $hours).":".sprintf("%02d", $mins);

            }

            $perUse = $viagens->embarcados > 0 && $viagens->capacidade > 0 ? ($viagens->embarcados * 100 / $viagens->capacidade) : 0;
                    
            $html .= "<tr class='toMark'>";
            $html .= "<td style='min-width: 72px !important;'>". $viagens->data ."</td>";
            $html .= "<td style='min-width: 72px !important;' class='tdBorder5'>". $viagens->linha ."</td>";
            $html .= "<td style='min-width: 18.8vw !important; max-width: 18.8vw !important; text-align: left'>". $viagens->descricao ."</td>";
            $html .= "<td style='min-width: 72px !important;' class='tdBorder5'>". $viagens->dtFinalPrev ."</td>";
            $html .= "<td style='min-width: 72px !important;'>". $viagens->dtFinalReal ."</td>";
            $html .= "<td title='Tempo Percurso' style='background-color: #0468bf; min-width: 70px !important;'>{$percursor}</td>";
            $html .= "<td title='KM' style='min-width: 70px !important;'>{$viagens->kmviagem}</td>";
            $html .= "<td style='min-width: 72px !important;' class='tdBorder5'>{$viagens->prefixoCar}</td>";
            $html .= "<td style='min-width: 70px !important;'>{$viagens->capacidade}</td>";
            $html .= $perUse > 100 ? "<td style='background-color: red; min-width: 72px !important;'>".round($perUse,2)."</td>" : "<td style='min-width: 72px !important;'>".round($perUse,2)."</td>";
            
            $html .= "<td style='min-width: 72px !important;' class='tdBorder5'>{$viagens->cadastrados}</td>";

            if ($viagens->embarcados > 0){
                $frase = $viagens->embarcados == 1 ? 'Ver o passageiro embarcado' : 'Ver os '.$viagens->embarcados.' passageiros embarcados';
                $html .= "<td style='min-width: 72px !important;'><span style='cursor:pointer;' onclick=\"verViagem('{$k}', '{$viagens->DTINIFIM}', '{$viagens->DTINIFIM}')\"><b title='{$frase}' style='padding:.5em;'><i class='fa fa-eye' aria-hidden='true' style='margin-right:.5em;'></i>{$viagens->embarcados}</b></span></td>";
            }else{
                $html .= "<td style='min-width: 72px !important;'>{$viagens->embarcados}</td>";
            } 

            $html .= "<td class='tdBorder5 tablePontual' style='background-color: {$graphParams[$viagens->pontualidade]['bg']}; color: {$graphParams[$viagens->pontualidade]['txtColor']};'><b>{$graphParams[$viagens->pontualidade]['txt']}</b></td>";
            $html .= "</tr>";

            if($viagens->pontualidade != 8 && $viagens->pontualidade != 4){
                $capacUso['limits'] += $viagens->capacidade;
                $capacUso['embarcados'] += $viagens->embarcados;
            }
            
        }

        $arrayRet['capacUso'] = $capacUso;
        $arrayRet['html']     = $html;

        return $arrayRet;

    }

    public function agendar(){

        $rel    = new Relatorios();
        $save   = $rel->agendarSintetico($_POST);

        echo json_encode($save);
		die();

    }

    private function getAgendamentos(){

        $rel            = new Relatorios();
        $agendamentos   = $rel->getAgendamentos('agenda_sintetico');
        return $agendamentos;

    }

    public function removerAgenda()
	{
		$dados = array();

		$rel = new Relatorios();

        $del = $rel->delAgenda('agenda_sintetico', $_GET['id']);

		echo json_encode($del);

        die();
	}

}