<?php

class relatorioAnaliticoController extends controller 
{

	public function index() 
	{

		$dados = array();

		####################### MONTA O FILTRO #######################
        $dataIni 	= date("Y-m-d");
        $dateEnd 	= date("Y-m-d");
		$user 		= new Usuarios();
		$grupo 		= $user->acessoGrupo();
        $linhas 			= new Relatorios();
		$dados['linhas']	= $linhas->getLinhas();
		##############################################################

        ################## TRATA LINHAS #################
          if(count($dados['linhas'])>0){
            foreach ($dados['linhas'] as $k => $lin){
                //$pref = explode(" ", $lin['PREFIXO']);
                $dados['linhas'][$k]['NOME'] = $lin['PREFIXO'] . " - " . $lin['NOME'] . " - " . $lin['DESCRICAO'] . " - " . ( $lin['SENTIDO'] == 0 ? "ENTRADA" : "RETORNO");
            }
        }

        if(isset($_SESSION['cType']) && $_SESSION['cType'] == 1)
        {
            $grIn = [708];
        } else {
            $grUs = array();
            if(count($grupo) > 0){
                foreach ($grupo as $k => $gr){
                    if($k < 5)
                        $grUs[] = $gr['ID_ORIGIN'];
                }
            }

            $grIn = count($grUs) > 0 ? implode(",", $grUs) : 0; 
        }
            
		$dados['grupo'] 	= $grupo;
		//$dados['carros'] 	= $carros;
        $dados['dataIni'] 	= $dataIni;
        $dados['dateEnd'] 	= $dateEnd;
        
        ################## TRATA #################
        if(count($dados['grupo'])>0)
        {
            foreach ($dados['grupo'] as $k => $lin)
                $dados['grupo'][$k]['NOME'] = $lin['NOME'];
        }
        #################################################

        $param 				    = new Parametro();
        $param 				    = $param->getParametros(true);
        $dados['timeAtualiza']  = $param['time_atualizar'] ? $param['time_atualizar'] : 20;
        $dados['relDays']       = $param['rel_days'] ?? 7;
        $dados['relMonth']      = 6;
        $dados['showRelTimer']  = $param['show_rel_timer'] ?? 0;
        $dados['cad_pax_tag']   = $param['cad_pax_tag'] ?? 1;

        $agendamentos           = $this->getAgendamentos();
        $prontos                = 0;
        $pendentes              = 0;
        
        if(count($agendamentos) > 0){
            foreach($agendamentos as $agenda){
                if($agenda->status == 1){
                    $prontos += 1;
                }else{
                    $pendentes += 1;
                }
            }
        }

        $dados['agendamentos']  = $agendamentos;
        $dados['pendentes']     = $pendentes;
        $dados['prontos']       = $prontos;

        $rel                    = new Relatorios();
        $dados['agendaLeft']    = $rel->getAgLeft('agenda_analitico');

		$this->loadTemplate('relatorios/analitico/relatorioAnalitico', $dados);
		exit;
	}

	public function resultado()
	{

        ignore_user_abort(false);
        session_write_close();

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);
        
		$dados 				= array();
        $req     			= new \stdClass();
        $req->veiculos  	= $body->veiculos;
        $req->data_inicio 	= $body->data_inicio;
        $req->data_fim 		= $body->data_fim;
        $req->grupo 		= $body->grupo;
        $req->todosGrupos   = $body->todosGrupos;
        $req->matricula     = $body->matricula;
        $req->previsto      = $body->previsto;
        $req->lns           = $body->lns; 
        $req->agenda        = $body->agenda;           
        
        $dados["html"]  	= $this->getDataAnalitic($req, $body->viagemID);

        echo json_encode($dados);
    	die();
	}

    public function viagem() 
	{

        $viagem = $_GET['v'];

        $viagemCheck=hash('sha256',$viagem);

        if (isset($_COOKIE['PHPTRIPVIEW']) && $_COOKIE['PHPTRIPVIEW'] == $viagemCheck) {

            ignore_user_abort(false);
            session_write_close();

            $dados = array();
        
            
            $agenda_viagem_id   = $_GET['avid'];
            $data_inicio        = $_GET['dti'];
            $data_fim           = $_GET['dtf'];
            $notify             = $_GET['notify'];
            $tagAgenda          = $_GET['tagAgenda'];

            $req = new \stdClass();
            $req->data_fim      = $data_inicio;
            $req->data_inicio   = $data_fim;
            $req->todosGrupos = 1;
            $req->agenda = 0;
            $req->matricula = 0;
                
            $dados['viagemID'] 	    = $viagem;
            $dados['data_inicio']   = $data_inicio;
            $dados['data_fim']      = $data_fim;
            $dados['notify']        = $notify;
            $dados['tagAgenda']     = $tagAgenda;

            $dados['dadosRel'] 	= $this->getDataAnalitic($req, $viagem, $agenda_viagem_id);

            if (isset($_COOKIE['PHPTRIPVIEW'])) {
                unset($_COOKIE['PHPTRIPVIEW']); 
                setcookie('PHPTRIPVIEW', '', -1, '/'); 
                
            }
            
            $this->loadView('relatorios/analitico/relatorioAnaliticoViagem', $dados);
            
            exit;
            
        }else{
            $_SESSION['forbidden'] = [
                "code" => "403",
                "msg" => "Você não tem permissão para acessar os dados dessa viagem.",
            ];
            header("Location: /");
            exit;
        } 
		
	}

	private function getDataAnalitic($req, $viagemID = 0, $agenda_viagem_id = 0)
	{

        $idAgenda = $req->agenda;
        $rel    = new Relatorios();
        $html   = "";

        $matricula = $req->matricula;

        //Trata Busca Normal
        if($idAgenda == 0 && $agenda_viagem_id == '0'){

            $param 				    = new Parametro();
            $param 				    = $param->getParametros(true);
            $cad_pax_tag            = $param['cad_pax_tag'] ?? 1;
            $cad_pax_pics           = $param['cad_pax_pics'] ?? 0;

            if($cad_pax_pics == 1) {

                $dadosRel = $rel->getDadosAnaliticoPassageiroFace($req, $viagemID, $cad_pax_tag);
                
            }else{

                $dadosRel = $rel->getDadosAnaliticoPassageiro($req, $viagemID);

            }
            
            foreach ($dadosRel AS $relV) 
            {
                $rels = (Object) $relV;

                foreach($rels AS $rel)
                {

                    $addTdId = ($matricula !== "" && !isset($addTdId)) ? true : false;
                    $html .= $this->itemAnalitic($rel, $addTdId);
                    
                }
            }

        //Trata Agendados
        }else{

            $dadosRel = $rel->getDadosAgendado('agenda_analitico_ready', $idAgenda, $viagemID, $agenda_viagem_id);

            foreach ($dadosRel AS $item) 
            {
                $html .= $this->itemAnalitic($item);
            }

        }
		

        return $html;
	}

    private function itemAnalitic($rel, $addTdId = false){

        $rel = (Object) $rel;

        $html = "";

        $sentRel = isset($rel->SENTREALIZADO)&&$rel->SENTREALIZADO==0? "Ida" : (isset($rel->SENTREALIZADO)&&$rel->SENTREALIZADO==1 ? "Volta" : "-");

        $exc = " - ";
        
        if($rel->PREVOK == 'PREV'){
            $exc = "SIM";
        }

        if($rel->PREVOK == 'NPREV'){
            $exc = "NÃO";
        }
        
        ########## MOSTRANDO O MAPA #########
        $maps  = "";
        $maps2 = "";
        
        if ( 
            $rel->LATITUDEEMB != 0 && $rel->LATITUDEEMB != null && 
            $rel->LONGITUDEEMB != 0 && $rel->LONGITUDEEMB != null 
        ){
            // $maps = '<span title="Ver no Mapa" class="btn btn-success p-1 showPaxEmb ml-1" style="line-height:1;" src="/map?latitude='.$rel->LATITUDEEMB.'&longitude='.$rel->LONGITUDEEMB.'&title='.$rel->NOME.'&titlePoint=Local de Embarque - '.$rel->NOME.'&showTop=0&topName=0&showAddress=1&displayName='.$rel->PONTOREFEREMB.'&showPano=0"><i class="fas fa-map"></i></span>';
            $maps  = "<a title='Abrir no Mapa' target='_blank' href='http://maps.google.com/?q={$rel->LATITUDEEMB},{$rel->LONGITUDEEMB}'> <i class='fas fa-map' style='font-size:18px;color:#6aff2e'></i></a>";
        }

        if ( 
            $rel->LATITUDEDESEMB != 0 && $rel->LATITUDEDESEMB != null && 
            $rel->LONGITUDEDESEMB != 0 && $rel->LONGITUDEDESEMB != null 
        ){
            // $maps2 = '<span title="Ver no Mapa" class="btn btn-success p-1 showPaxDesemb ml-1" style="line-height:1;" src="/map?latitude='.$rel->LATITUDEDESEMB.'&longitude='.$rel->LONGITUDEDESEMB.'&title='.$rel->NOME.'&titlePoint=Local de Desembarque - '.$rel->NOME.'&showTop=0&topName=0&showAddress=1&displayName='.$rel->PONTOREFERDESEMB.'&showPano=0"><i class="fas fa-map"></i></span>';
            $maps2  = "<a title='Abrir no Mapa' target='_blank' href='http://maps.google.com/?q={$rel->LATITUDEDESEMB},{$rel->LONGITUDEDESEMB}'> <i class='fas fa-map' style='font-size:18px;color:#6aff2e'></i></a>";
        }

        $html .= "<tr";
        if (isset($rel->IMGS) && is_array($rel->IMGS)) {
            if (isset($rel->IMGS['emb']) || isset($rel->IMGS['demb'])) {
                $html .= " style='height: 50px !important'";
            }
        }
        $html .= " class='toMark'>";
        $html .= "<td style='min-width: 72px !important;'>". $rel->PREF ."</td>";
        $html .= "<td style='min-width: 72px !important;'>". $rel->PLACA ."</td>";
        $html .= "<td style='min-width: 100px !important;' class='tdBorder5'>". $rel->GRUPO ."</td>";
        $html .= "<td style='min-width: 100px !important;'>" . $rel->CODIGO ."</td>";
        $html .= "<td";
        if ($addTdId) {
            $html .= " id='passNome'";
        }
        $html .= ">" . $rel->NOME . "</td>";
        $html .= "<td style='min-width: 100px !important;'>{$rel->MATRICULA}</td>";
        $html .= "<td style='min-width: 70px !important;'>{$rel->STATUS}</td>";
        $html .= "<td class='tdBorder5'>". $rel->PONTOREFEREMB . $maps . "</td>";
        $html .= "<td style='min-width: 72px !important;'>". nl2br($rel->HORAMARCACAOEMB) ."</td>";
        $html .= "<td>". $rel->LOGRADOUROEMB ."</td>";
        $html .= "<td style='min-width: 90px !important;'>". $rel->LOCALIZACAOEMB ."</td>";
        $html .= "<td class='picTdRel'>";
            if(isset($rel->IMGS) && is_array($rel->IMGS) && isset($rel->IMGS['emb']['img'])){
                $html .= "<img title='Clique para ver a imagem ampliada' class='picListRel' src='".$rel->IMGS['emb']['img']."' recid='".$rel->IMGS['emb']['recid']."'/>";
            } 
        $html .= "</td>";
        $html .= "<td class='tdBorder5'>". $rel->PONTOREFERDESEMB . $maps2 . "</td>";
        $html .= "<td style='min-width: 72px !important;'>". nl2br($rel->HORAMARCACAODESEMB) ."</td>";
        $html .= "<td>". $rel->LOGRADOURODESEMB ."</td>";
        $html .= "<td style='min-width: 90px !important;'>". $rel->LOCALIZACAODESEMB ."</td>";
        $html .= "<td class='picTdRel'>";
            if(isset($rel->IMGS) && is_array($rel->IMGS) && isset($rel->IMGS['demb']['img'])){
                $html .= "<img title='Clique para ver a imagem ampliada' class='picListRel' src='".$rel->IMGS['demb']['img']."' recid='".$rel->IMGS['demb']['recid']."'/>";
            } 
        $html .= "</td>";
        $html .= "<td class='tdBorder5'>". $rel->ITIDAPREV ."</td>";
        $html .= "<td>". $rel->ITVOLTAPREV ."</td>";
        $html .= "<td class='tdBorder5'>". $rel->ITIREALIZADOOK ."</td>";
        $html .= "<td style='min-width: 70px !important;'>{$sentRel}</td>";
        $html .= "<td style='min-width: 72px !important'>". nl2br($rel->DATAREALIZADO) ."</td>";
        $html .= "<td style='min-width: 72px !important;' class='tdBorder5'>{$exc}</td>";
        $html .= "</tr>";

        return $html;
        
    }

    public function agendar(){

        $rel    = new Relatorios();
        $save   = $rel->agendarAnaliticoPassageiro($_POST);

        echo json_encode($save);
		die();

    }

    private function getAgendamentos(){

        $rel            = new Relatorios();
        $agendamentos   = $rel->getAgendamentos('agenda_analitico');
        return $agendamentos;

    }

    public function removerAgenda()
	{
		$dados = array();

		$rel = new Relatorios();

        $del = $rel->delAgenda('agenda_analitico', $_GET['id']);

		echo json_encode($del);

        die();
	}

}