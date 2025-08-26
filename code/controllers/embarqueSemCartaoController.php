<?php

class embarqueSemCartaoController extends controller 
{

	// public function index()
	// {
	// 	$dados = array();

	// 	$emb   	= new Embarque();
	// 	$dados['embarques'] = $emb->getAllEmbarques();

	// 	$this->loadTemplate('embarqueSemCartao/embarqueSemCartao', $dados);
	// 	exit();
	// }

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
            
		$dados['grupo'] = $grupo;

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

		$this->loadTemplate('embarqueSemCartao/embarqueSemCartao', $dados);
		exit();
	}


	public function resultado(){

		ignore_user_abort(false);
        session_write_close();

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);
        
		$dados 				= array();
        $req     			= new \stdClass();
        $req->data_inicio 	= $body->data_inicio;
        $req->data_fim 		= $body->data_fim;
        $req->grupo 		= $body->grupo;
        $req->matricula     = $body->matricula;
        $req->lns           = $body->lns;            
        
        $dados["html"]  	= $this->getDadosEmbarques($req);

        echo json_encode($dados);
    	die();

	}


	private function getDadosEmbarques($req){

		$emb   	= new Relatorios();
		$embarques = $emb->getEmbarquesSemRfId($req);

		$html = "";

		$embarques = (Object) $embarques;

		foreach ($embarques AS $item) 
            {
				
                $html .= $this->item($item);
				
            }

		return $html;
	}


	private function item($rel){

        $rel = (Object) $rel;

		########## MOSTRANDO O MAPA #########
        $maps  = "";
        $maps2 = "";
		$maps3 = "";

		if ( 
            $rel->pontoEmb->LATITUDE != 0 && $rel->pontoEmb->LATITUDE != null && 
            $rel->pontoEmb->LONGITUDE != 0 && $rel->pontoEmb->LONGITUDE != null 
        ){
            $maps  = "<a title='Abrir no Mapa' target='_blank' href='http://maps.google.com/?q={$rel->pontoEmb->LATITUDE},{$rel->pontoEmb->LONGITUDE}'> <i class='fas fa-map' style='font-size:18px;color:#6aff2e'></i></a>";
        }

		if ( 
            $rel->dados_embarque->lat != 0 && $rel->dados_embarque->lat != null && 
            $rel->dados_embarque->long != 0 && $rel->dados_embarque->long != null 
        ){
            $maps2  = "<a title='Abrir no Mapa' target='_blank' href='http://maps.google.com/?q={$rel->dados_embarque->lat},{$rel->dados_embarque->long}'> <i class='fas fa-map' style='font-size:18px;color:#6aff2e'></i></a>";
        }

		if ( 
			$rel->hasDesembarque &&
            $rel->dados_desembarque->lat != 0 && $rel->dados_desembarque->lat != null && 
            $rel->dados_desembarque->long != 0 && $rel->dados_desembarque->long != null 
        ){
            $maps3  = "<a title='Abrir no Mapa' target='_blank' href='http://maps.google.com/?q={$rel->dados_desembarque->lat},{$rel->dados_desembarque->long}'> <i class='fas fa-map' style='font-size:18px;color:#6aff2e'></i></a>";
        }

        $html = "<tr class='toMark'>";
		$html .= "<td>{$rel->nomeGrupo}</td>";
		$html .= "<td>{$rel->id_embarque}</td>";
        $html .= "<td>{$rel->passageiro->nome}</td>";
        $html .= "<td>{$rel->passageiro->matricula}</td>";
        $html .= "<td>{$rel->passageiro->motivo}</td>";
		// $html .= "<td class='tdBorder5'>{$rel->pontoEmb->NOME} {$maps}</td>";
        // $html .= "<td>{$rel->pontoEmb->LOGRADOURO}</td>";
        // $html .= "<td>{$rel->pontoEmb->LOCALIZACAO}</td>";
        $html .= "<td class='tdBorder5'>{$rel->dados_embarque->pontoRef->NOME} {$maps2}</td>";
        $html .= "<td>". date("d/m/Y H:i:s", strtotime($rel->dados_embarque->data_hora)) ."</td>";
        $html .= "<td>{$rel->dados_embarque->pontoRef->LOGRADOURO}</td>";
        $html .= "<td>{$rel->dados_embarque->pontoRef->LOCALIZACAO}</td>";
		if($rel->hasDesembarque){
			$html .= "<td class='tdBorder5'>{$rel->dados_desembarque->pontoRef->NOME} {$maps3}</td>";
			$html .= "<td>". date("d/m/Y H:i:s", strtotime($rel->dados_desembarque->data_hora)) ."</td>";
			$html .= "<td>{$rel->dados_desembarque->pontoRef->LOGRADOURO}</td>";
			$html .= "<td>{$rel->dados_desembarque->pontoRef->LOCALIZACAO}</td>";
		}else{
			$html .= "<td class='tdBorder5'> - </td>";
			$html .= "<td> - </td>";
			$html .= "<td> - </td>";
			$html .= "<td> - </td>";
		}
		
		$html .= "<td class='tdBorder5'>{$rel->veiculo->prefixo}</td>";
        $html .= "<td>{$rel->veiculo->placa}</td>";
		$html .= "<td class='tdBorder5'>{$rel->linha->prefixo}</td>";
        $html .= "<td>{$rel->linha->nome}</td>";
		$html .= "<td class='tdBorder5'>{$rel->viagem->DATAHORA_INICIAL_REALIZADO}</td>";
        $html .= "<td>{$rel->viagem->DATAHORA_FINAL_REALIZADO}</td>";
		// $html .= "<td class='tdBorder5'>{$rel->sentido}</td>";
		// $html .= "<td>{$rel->viagem->ITIDESC}</td>";
        $html .= "</tr>";

        return $html;
        
    }

	public function create()
	{
		$dados = array();		

		$relGer 				= new Relatorios();
		$dados['linhas']		= $relGer->getLinhas();
		$dados['prefVeiculo']	= $relGer->getCarros();
		$dados['cliente']		= $relGer->getGrupos();

		################## TRATA LINHAS #################
		if(count($dados['linhas'])>0){
			foreach ($dados['linhas'] as $k => $lin){
				$dados['linhas'][$k]['NOME'] = $lin['NOME'];
			}
		}
		#################################################
		################## TRATA #################
		if(count($dados['cliente'])>0){
			foreach ($dados['cliente'] as $k => $lin){
				$dados['cliente'][$k]['NOME'] = $lin['NOME'];
			}
		}
		#################################################

		$user 					= new Usuarios();
		$dados['grupoAcesso']	= $user->acessoGrupo();
		################## TRATA #################
		if(count($dados['grupoAcesso'])>0){
			foreach ($dados['grupoAcesso'] as $k => $lin){
				$dados['grupoAcesso'][$k]['NOME'] = $lin['NOME'];
			}
		}
		#################################################

		$this->loadTemplate('embarqueSemCartao/embarqueSemCartaoCreate', $dados);
		exit();
	}

	public function salvar()
	{ 
		$dados = array();

		$emb   		= new Embarque();
		$save 		= $emb->salvarEmbarque($_POST);

		if($save)
			$_SESSION['ms'] = "Cadastrado com sucesso!";
		else 
			$_SESSION['merr'] = "Ocorreu um erro ao cadastrar, tente novamente!";

		unset($_POST);

		header("Location: " . BASE_URL . "embarqueSemCartao");
		exit();
	}

	public function editar()
	{

		$dados = array();
		$emb   	= new Embarque();

		if(isset($_GET['id'])){
			$relGer 				= new Relatorios();
			$dados['linhas']		= $relGer->getLinhas();
			$dados['prefVeiculo']	= $relGer->getCarros();
			$dados['cliente']		= $relGer->getGrupos();

			$user 					= new Usuarios();
			$dados['grupoAcesso']	= $user->acessoGrupo();

			$dados['embarqueEdt'] 	= $emb->getEmbarque($_GET['id']);

			$this->loadTemplate('embarqueSemCartao/embarqueSemCartaoEdit', $dados);
		} else {
			$_SESSION['merr'] = "Ocorreu um erro, tente novamente!";
			header("Location: " . BASE_URL . "embarqueSemCartao");
		}

		exit();
	}

	public function atualizar()
	{
		
		$dados 		= array();	
		$emb   		= new Embarque();

		if(isset($_POST['id'])){
			
			$atualRet 	= $emb->atualizarEmbarque($_POST);

			if($atualRet)
				$_SESSION['ms'] = "Edição Salva com sucesso!";
			else 
				$_SESSION['merr'] = "Ocorreu um erro ao atualizar, tente novamente!";
			
		} else {
			$_SESSION['merr'] = "Ocorreu um erro ao atualizar, tente novamente!";
		}
	
		unset($_POST);

		header("Location: " . BASE_URL . "embarqueSemCartao");
		exit();
	}

	public function deletar()
	{
		$dados 	= array();	
		$emb   	= new Embarque();

		if(isset($_POST['idDel'])){
			$emb->delEmbarque($_POST['idDel']);
			$_SESSION['ms'] = "Deletado com sucesso!";
		} else {
			$_SESSION['merr'] = "Ocorreu um erro ao deletar, tente novamente!";
		}

		header("Location: " . BASE_URL . "embarqueSemCartao");
		exit();
	}


}