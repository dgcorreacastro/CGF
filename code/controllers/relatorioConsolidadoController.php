<?php

class relatorioConsolidadoController extends controller 
{

	private $linhasIniciais = "2592, 2593, 2707, 2710, 2708, 2711, 1169, 1627, 2798, 2799, 1166, 1168, 2796, 2797, 2818, 2819, 1170, 1628, 2801, 2802, 2824, 2825";

	public function index() 
	{
		$dadosRet = array();

		####################### MONTA O FILTRO #######################
        $dataIni 	= date("Y-m-d");
        $dateEnd 	= date("Y-m-d");
		$linhas 			= new Relatorios();
		$dadosRet['linhas']	= $linhas->getLinhas();
        $capacUso           = array();
        $capacUso['limits'] = 0;
        $capacUso['embarcados'] = 0;

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
                //$pref = explode(" ", $lin['PREFIXO']);
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

        $dadosRet['agendamentos']  = $agendamentos;
        $dadosRet['pendentes']     = $pendentes;
        $dadosRet['prontos']       = $prontos;

        $dadosRet['agendaLeft']    = $rel->getAgLeft('agenda_consolidado');
		
        $dadosRet['dadosRel']       = "";
        $dadosRet['dateEnd']        = $dateEnd;
        $dadosRet['dataIni'] 	    = $dataIni;
        $dadosRet['filterPontual']  = $rel->getGraphParams(true);
		##############################################################
		$this->loadTemplate('relatorios/consolidado/relatorioConsolidado', $dadosRet);
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
		$req->sentido 		= $body->sentido;
		$req->pontual 		= $body->pontual;
        $idAgenda           = $body->agenda;
        $graphParams        = $rel->getGraphParams(true);  
        
        if($idAgenda == 0){

            $param 				    = new Parametro();
            $param 				    = $param->getParametros(true);
            $cad_pax_tag            = $param['cad_pax_tag'] ?? 1;

            $dadosRel = $rel->getDadosConsolidadoViagem($req, $cad_pax_tag);
            $filterPontual = isset($req->pontual) && $req->pontual != "" ? $req->pontual : 0;
            $arrayRet = $this->itensConsolidado($dadosRel, $filterPontual, $graphParams);

        }else{

            $dadosRel = $rel->getDadosAgendado('agenda_consolidado_ready', $idAgenda);

            $getAgenda = $rel->getAgendamentos('agenda_consolidado', $idAgenda);

            if(count($getAgenda) == 1){

                $agendamento = $getAgenda[0];
                $filterPontual = isset($agendamento->pontual) && $agendamento->pontual != "" ? $agendamento->pontual : 0;
                $arrayRet = $this->itensConsolidado($dadosRel, $filterPontual, $graphParams);
                $arrayRet['data_inicio']    = $agendamento->data_inicio;
                $arrayRet['data_fim']       = $agendamento->data_fim;
                $arrayRet['errorViagem']    = $agendamento->errorViagem;

            }

        }

		##############################################################
		echo json_encode($arrayRet);
		die();
	}

    private function itensConsolidado($dadosRel, $filterPontual, $graphParams){

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

            $ddrel = (Object) $ddrel;

            $real = $ddrel->SENTIDO == 0 ? $ddrel->DATAFIMREAL : $ddrel->DATAINIREAL;
            $prev = $ddrel->SENTIDO == 0 ? $ddrel->DATAFIMPREV : $ddrel->DATAINIPREVISTO;

            $pontualidade = $rel->trataPontualidade($ranger, null, $real, $prev);

            if ($filterPontual != 0 && ($filterPontual != $pontualidade))
                continue;

            $nameLine = trim($ddrel->NOMELINHA);

            ######## AJUSTANDO DADOS PARA O JSON ############# 
            $dataTrata[$nameLine]['NOMELINHA'] = $ddrel->NOMELINHA;
            $dataTrata[$nameLine]['V'][$ddrel->IDVIAGEM]['data'] = date("d/m/Y", strtotime($ddrel->DATAINIPREVISTO));
            $dataTrata[$nameLine]['V'][$ddrel->IDVIAGEM]['GRUPO'] = $ddrel->GRUPO;
            $dataTrata[$nameLine]['V'][$ddrel->IDVIAGEM]['PREFIXO'] = $ddrel->PREFIXO;
            $dataTrata[$nameLine]['V'][$ddrel->IDVIAGEM]['TIPO'] = $ddrel->TIPO == 0? "Soltura" : ($ddrel->TIPO == 2? "Viagem" : " - ");
            $dataTrata[$nameLine]['V'][$ddrel->IDVIAGEM]['SENTIDO'] = $ddrel->SENTIDO == 0 ? "Ida" : "Volta";
            $dataTrata[$nameLine]['V'][$ddrel->IDVIAGEM]['TRECHO'] = $ddrel->TRECHO;
            $dataTrata[$nameLine]['V'][$ddrel->IDVIAGEM]['DESCRICAO'] = $ddrel->DESCRICAO;
            $dataTrata[$nameLine]['V'][$ddrel->IDVIAGEM]['DATAINIPREVISTO'] = date("H:i:s", strtotime($ddrel->DATAINIPREVISTO));
            $dataTrata[$nameLine]['V'][$ddrel->IDVIAGEM]['DATAINIREAL'] = $ddrel->DATAINIREAL ? date("H:i:s", strtotime($ddrel->DATAINIREAL)) : "";
            $dataTrata[$nameLine]['V'][$ddrel->IDVIAGEM]['DATAFIMPREV'] = date("H:i:s", strtotime($ddrel->DATAFIMPREV));
            $dataTrata[$nameLine]['V'][$ddrel->IDVIAGEM]['DATAFIMREAL'] = $ddrel->DATAFIMREAL ? date("H:i:s", strtotime($ddrel->DATAFIMREAL)) : "";
            $dataTrata[$nameLine]['V'][$ddrel->IDVIAGEM]['KMVIAGEM'] = number_format($ddrel->KMVIAGEM, 0, "", '.');
            $dataTrata[$nameLine]['V'][$ddrel->IDVIAGEM]['PLACA'] = $ddrel->PLACA;
            $dataTrata[$nameLine]['V'][$ddrel->IDVIAGEM]['PREFIXOVEIC'] = $ddrel->PREFIXOVEIC;
            $dataTrata[$nameLine]['V'][$ddrel->IDVIAGEM]['veiculo_id'] = $ddrel->IDVEIC;
            $dataTrata[$nameLine]['V'][$ddrel->IDVIAGEM]['CAPACIDADEVEIC'] = $ddrel->CAPACIDADEVEIC;
            $dataTrata[$nameLine]['V'][$ddrel->IDVIAGEM]['LIMITEVEIC'] = $ddrel->LIMITEVEIC;
            $dataTrata[$nameLine]['V'][$ddrel->IDVIAGEM]['PAXCADASTRADO'] = $ddrel->PAXCADASTRADO + $ddrel->PAXCADASTRADOV;
            $dataTrata[$nameLine]['V'][$ddrel->IDVIAGEM]['DTINREL'] = $ddrel->DATAINIREAL;
            $dataTrata[$nameLine]['V'][$ddrel->IDVIAGEM]['DTFIMREL'] = $ddrel->DATAFIMREAL;
            $dataTrata[$nameLine]['V'][$ddrel->IDVIAGEM]['DTINIFIM'] = $ddrel->DATAINIREAL ? date("Y-m-d", strtotime($ddrel->DATAINIREAL)) : date("Y-m-d", strtotime($ddrel->DATAINIPREVISTO));
            $dataTrata[$nameLine]['V'][$ddrel->IDVIAGEM]['ITINERARIO_ID'] = $ddrel->ITINERARIO_ID;

            $pax = 0;

            if( (!isset($hasPax[$ddrel->IDVIAGEM]) || !in_array($ddrel->TAG, $hasPax[$ddrel->IDVIAGEM])) && $ddrel->TIPO_USUARIO == 2 ){

                $hasPax[$ddrel->IDVIAGEM][] = $ddrel->TAG;

                if(isset($countPax[$ddrel->IDVIAGEM]))
                    $countPax[$ddrel->IDVIAGEM] += 1;
                else 
                    $countPax[$ddrel->IDVIAGEM] = 1;

            }

            if(isset($countPax[$ddrel->IDVIAGEM]))
                $pax = $countPax[$ddrel->IDVIAGEM];

            if($cad_pax_pics == 1 && ($ddrel->DATAINIREAL != null && $ddrel->DATAINIREAL != "0000-00-00" && $ddrel->DATAFIMREAL != null && $ddrel->DATAFIMREAL != "0000-00-00")){

                $viagensFace[$ddrel->IDVIAGEM] = [
                    'IDVIAGEM'=> $ddrel->IDVIAGEM,
                    'embarcados' => $pax,
                    'cadastrados' => $ddrel->PAXCADASTRADO + $ddrel->PAXCADASTRADOV,
                    'IDVEIC' => $ddrel->IDVEIC,
                    'DATAINIREAL' => $ddrel->DATAINIREAL,
                    'DATAFIMREAL' => $ddrel->DATAFIMREAL,
                    'SENTIDO' => $ddrel->SENTIDO,
                    'IDTINEREAL' => $ddrel->ITINERARIO_ID
                ];
            }
    
            // $dataTrata[$nameLine]['V'][$ddrel->IDVIAGEM]['PORCENUSO'] = $pax > 0 && $ddrel->CAPACIDADEVEIC > 0 ? ($pax * 100 / $ddrel->CAPACIDADEVEIC) : 0;
            $dataTrata[$nameLine]['V'][$ddrel->IDVIAGEM]['PAXEMBARCADO'] = $pax;
            $dataTrata[$nameLine]['V'][$ddrel->IDVIAGEM]['IPK'] = $pax > 0 && $ddrel->KMVIAGEM > 0 ? ($pax / $ddrel->KMVIAGEM / 1000) : 0;
            $dataTrata[$nameLine]['V'][$ddrel->IDVIAGEM]['UTILIZACAO'] = $pax == $ddrel->LIMITEVEIC ? "Limite" : ($pax > $ddrel->LIMITEVEIC ? "Acima" : "Abaixo");
            $dataTrata[$nameLine]['V'][$ddrel->IDVIAGEM]['pontualidade'] = $pontualidade;
        }

        if(count($viagensFace) > 0){
            
            $recFilters['viagensFace'] = $viagensFace;
            $treatRecognitions = $rel->treatRecognitions($recFilters);

            if ($treatRecognitions['status'] === true) {
                foreach ($treatRecognitions['embCad'] as $viagenId => $d) {
                    foreach ($dataTrata as &$line) {
                        if (isset($line['V'][$viagenId])) {
                            $line['V'][$viagenId]['PAXEMBARCADO'] = $d['embarcados'];
                            $line['V'][$viagenId]['PAXCADASTRADO'] = $d['cadastrados'];
                        }
                    }
                }
            }
            
        }

        foreach($dataTrata AS $dados){

            $dados = (Object) $dados;

            $html .= "<tr class='trHeight'><td scope='col' colspan='15'><div class='nomeLinha'><span>Linha: <b>". $dados->NOMELINHA ."</b></span></div><div class='nomeLinhaIn'></div></td></tr>";
            
            $arrDuplicate = array(); // Veiculo + horario Iniciado

            foreach($dados->V AS $k => $viagens)
            {

                    $viagens = (Object) $viagens;

                if (!isset($arrDuplicate[$viagens->PLACA . '-' . $viagens->DATAINIREAL])){
                    $arrDuplicate[$viagens->PLACA . '-' . $viagens->DATAINIREAL] = 1;

                    $percursor = " - ";

                    if (
                        $viagens->DTINREL != null && $viagens->DTINREL != "0000-00-00" &&
                        $viagens->DTFIMREL != null && $viagens->DTFIMREL != "0000-00-00"
                    ){
                        $seconds    = strtotime($viagens->DTFIMREL) - strtotime($viagens->DTINREL);
                        $hours      = floor($seconds / 3600);
                        $mins       = floor(($seconds - ($hours*3600)) / 60);
    
                        $percursor  = sprintf("%02d", $hours).":".sprintf("%02d", $mins);

                    }
                    
                    $perUse = $viagens->PAXEMBARCADO > 0 && $viagens->CAPACIDADEVEIC > 0 ? ($viagens->PAXEMBARCADO * 100 / $viagens->CAPACIDADEVEIC) : 0;

                    $html .= "<tr class='toMark'>";
                    $html .= "<td style='min-width: 72px !important;'>". $viagens->data ."</td>";
                    $html .= "<td style='min-width: 72px !important;'>". $viagens->PREFIXO ."</td>";
                    $html .= "<td style='min-width: 70px !important;' class='tdBorder5'>". $viagens->SENTIDO ."</td>";
                    // $html .= "<td style='min-width: 18.8vw !important; max-width: 18.8vw !important; text-align: left'>". $viagens->DESCRICAO ."</td>";
                    $html .= "<td class='tdBorder5' style='background-color: #0f4b75; min-width: 72px !important;'>{$viagens->DATAINIPREVISTO}</td>";
                    $html .= "<td style='background-color: #0f4b75; min-width: 72px !important;'>{$viagens->DATAINIREAL}</td>";
                    $html .= "<td style='background-color: #052c4e; min-width: 72px !important;'>{$viagens->DATAFIMPREV}</td>";
                    $html .= "<td style='background-color: #052c4e; min-width: 72px !important;'>{$viagens->DATAFIMREAL}</td>";
                    $html .= "<td title='Tempo Percurso' style='background-color: #0468bf; min-width: 70px !important;'>{$percursor}</td>";
                    $html .= "<td title='KM' style='min-width: 70px !important;'>{$viagens->KMVIAGEM}</td>";
                    $html .= "<td style='min-width: 72px !important;' class='tdBorder5'>{$viagens->PREFIXOVEIC}</td>";
                    $html .= "<td style='min-width: 70px !important;'>{$viagens->CAPACIDADEVEIC}</td>";

                    if($perUse > 100)
                        $html .= "<td style='background-color: red; min-width: 72px !important;'>".round($perUse,2)."</td>";
                    else 
                        $html .= "<td style='min-width: 72px !important;'>".round($perUse,2)."</td>"; 

                    $html .= "<td class='tdBorder5' style='min-width: 72px !important;'>{$viagens->PAXCADASTRADO}</td>";

                    if ($viagens->PAXEMBARCADO > 0){
                        $frase = $viagens->PAXEMBARCADO == 1 ? 'Ver o passageiro embarcado' : 'Ver os '.$viagens->PAXEMBARCADO.' passageiros embarcados';
                        $html .= "<td style='min-width: 72px !important;'><span style='cursor:pointer;' onclick=\"verViagem('{$k}', '{$viagens->DTINIFIM}', '{$viagens->DTINIFIM}')\"><b title='{$frase}' style='padding:.5em;'><i class='fa fa-eye' aria-hidden='true' style='margin-right:.5em;'></i>{$viagens->PAXEMBARCADO}</b></span></td>";
                    }else{
                        $html .= "<td style='min-width: 72px !important;'>{$viagens->PAXEMBARCADO}</td>";
                    } 
                    $html .= "<td class='tdBorder5 tablePontual' style='background-color: {$graphParams[$viagens->pontualidade]['bg']}; color: {$graphParams[$viagens->pontualidade]['txtColor']};'><b>{$graphParams[$viagens->pontualidade]['txt']}</b></td>";
                    $html .= "</tr>";

                    if($viagens->pontualidade != 8 && $viagens->pontualidade != 4){
                        $capacUso['limits'] += $viagens->LIMITEVEIC;
                        $capacUso['embarcados'] += $viagens->PAXEMBARCADO;
                    }

                }
            }
        }

  		$arrayRet['capacUso'] = $capacUso;
        $arrayRet['html'] = $html;

        return $arrayRet;

    }

    public function agendar(){

        $rel    = new Relatorios();
        $save   = $rel->agendarConsolidado($_POST);

        echo json_encode($save);
		die();

    }

    private function getAgendamentos(){

        $rel            = new Relatorios();
        $agendamentos   = $rel->getAgendamentos('agenda_consolidado');
        return $agendamentos;

    }

    public function removerAgenda()
	{
		$dados = array();

		$rel = new Relatorios();

        $del = $rel->delAgenda('agenda_consolidado', $_GET['id']);

		echo json_encode($del);

        die();
	}

}