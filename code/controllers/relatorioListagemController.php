<?php

class relatorioListagemController extends controller 
{

	public function index() 
	{

        ignore_user_abort(false);
        session_write_close();
        
		$dados = array();

		####################### MONTA O FILTRO #######################
		$user 				= new Usuarios();
        $dados['grupos'] 	= $user->acessoGrupo();
        ################## TRATA #################
        if(count($dados['grupos'])>0){
            foreach ($dados['grupos'] as $k => $lin){
                $dados['grupos'][$k]['NOME'] = $lin['NOME'];
            }
        }
        ##############################################################
		##############################################################
		####################### GET DADOS REL  #######################
		$rel 		 		= new Relatorios();
		$req                = new \stdClass();
		$dados['linhas']	= $rel->getLinhas();

         ################## TRATA LINHAS #################
         if(count($dados['linhas'])>0){
            foreach ($dados['linhas'] as $k => $lin){
                //$pref = explode(" ", $lin['PREFIXO']);
                $dados['linhas'][$k]['NOME'] = $lin['PREFIXO'] . " - " . $lin['NOME'] . " - " . $lin['DESCRICAO'] . " - " . ( $lin['SENTIDO'] == 0 ? "ENTRADA" : "RETORNO");
            }
        }
        #################################################

        if(isset($_SESSION['cType']) && $_SESSION['cType'] == 1){
            $grIn = [708, 709];
        } else {
            $grUs = array();
            if(count($dados['grupos']) > 0){
                foreach ($dados['grupos'] as $gr){
                    $grUs[] = $gr['ID_ORIGIN'];
                }
            }

            $grIn = count($grUs) > 0 ? implode(",", $grUs) : 0; 
        }

        $req->grupo         = $grIn;
        
        $dadosLista = $this->getListagemPassageiros($req);
        $dados['dadosRel']          = $dadosLista['html'];
        $dados['totalListagem']     = $dadosLista['total'];

        $param 				    = new Parametro();
        $param 				    = $param->getParametros(true);
        $dados['timeAtualiza']  = $param['time_atualizar'] ? $param['time_atualizar'] : 20;

		##############################################################
		$this->loadTemplate('relatorios/listagem/relatorioListagem', $dados);
		exit;
	}

	public function resultado()
	{

        ignore_user_abort(false);
        session_write_close();

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

		$dados 			= array();
		$linhas     	= array();
        $error      	= array();
        $html 			= "";

        $req     		= new \stdClass();
        $req->nome 		= $body->nome;
        $req->matricula = $body->matricula;
        $req->codigo 	= $body->codigo;
        $req->situacao 	= $body->situacao;
        $req->grupo 	= $body->grupo;
        $req->lns 	    = $body->lns;
        $req->autocad 	= $body->autocad;
        
        $dadosLista = $this->getListagemPassageiros($req);
        $dados['html']              = $dadosLista['html'];
        $dados['totalListagem']     = $dadosLista['total'];

        echo json_encode($dados);
    	die();
	}

    private function getListagemPassageiros($req){

        $rel 		 		= new Relatorios();
        $dadosRel 	  		= $rel->getDadosListagemPassageiros($req);
        $filterAutoCad      = isset($req->autocad) && $req->autocad != "" ? $req->autocad : 0;
        $html 				= "";
        $total              = 0;
        foreach($dadosRel as $rel){

            if($filterAutoCad != 0){

                if($filterAutoCad == 2 && $rel['CGFPASS'] == 'SIM'){
                    continue;
                }

                if($filterAutoCad == 1 && $rel['CGFPASS'] == 'NÃO'){
                    continue;
                }
            }

            $idaPol     = " - ";
            $voltaPol   = " - ";

            if(isset($rel['CentroCusto']))
            {
                $sep = explode(";", $rel['CentroCusto']);
                $idaPol     = isset($sep[0]) ? $sep[0] : $idaPol;
                $voltaPol   = isset($sep[1]) ? $sep[1] : $voltaPol ;
            }

            $sclink = "{$rel['id']}, '{$rel['Nome']}'";

            $linhasAdicionais = $rel['linhaAdd'] > 0 ? '<span style="cursor:pointer" title="Abrir Linhas Adicionais" onclick="openAdicionalLines('.$sclink.')"><i class="fa fa-info-circle" style="font-size:18px;color:#ff740e"></i></span>' : '-';

            $nome = $rel['CGFPASS'] == 'SIM' ? $rel['Nome'].' <i title="'.APP_NAME.' Pass - Autocadastramento" class="fas fa-mobile-alt ml-2" style="font-size: 18px; color: #ffc107; text-shadow: -1px 2px 0px rgb(0 0 0 / 30%);"></i>' : $rel['Nome'];
           
            $ativo = $rel['Status'] == 1 ? 'Ativo' : 'Inativo';
            $monitor = $rel['monitor'] == 1 ? 'SIM' : 'NÃO';

            $html .= "<tr class='toMark'>";
            $html .= "<td>".$nome."</td>";
            $html .= "<td class='dn'>".$rel['CGFPASS']."</td>";
            $html .= "<td>".$rel['Codigo']."</td>";
            $html .= "<td>".$rel['Grupo']."</td>";
            $html .= "<td>".$rel['MatriculaFuncional']."</td>";
            $html .= "<td>".$ativo."</td>";
            $html .= "<td>".$monitor."</td>";
            $html .= "<td class='tdBorder5'>".$rel['PrefixoIda']." - ".$rel['NomeLinhaIda']."</td>";
            $html .= "<td class='tdBorder5'>".($rel['IdaSentido'] == 0 ? 'Ida' : 'Volta')."</td>";
            $html .= "<td>".$rel['IdaDescricao']."</td>";
            $html .= "<td class='tdBorder5'>". $idaPol ."</td>";
            $html .= "<td class='tdBorder5'>".$rel['PrefixoVolta'] ." - ".$rel['NomeLinhaVolta']."</td>";
            $html .= "<td class='tdBorder5'>".($rel['VoltaSentido'] == 0 ? 'Ida' : 'Volta') . "</td>";
            $html .= "<td>". $rel['VoltaDescricao'] ."</td>";
            $html .= "<td class='tdBorder5'>". $voltaPol ."</td>";
            $html .= "<td class='tdBorder5'>". $linhasAdicionais;
            if ($rel['linhaAdd'] > 0) {
                /// Busca AS linhas extras do PAX \\
                $Pax = new Pax();
                $retLines = $Pax->getLinesExtras($rel['id']);
                $html .= "<table style='display: none;'>";
                foreach($retLines AS $rtl)
                {
                    $html .= "<tr><td>".$rtl->PREFIXO." - ".$rtl->NOME."</td></tr>";
                }
                $html .= "</table>";
              
            }
            $html .= "</td>";
            $html .= "</tr>";
            $total++;
        }
     
        return array("html" => $html, "total" => $total);

    }

}
 