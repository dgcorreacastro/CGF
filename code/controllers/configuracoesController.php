<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;

class configuracoesController extends controller 
{

	##################################################################
	########### INICIANDO CONFIGURAÇÕES DOS PARAMETROS ###############
	##################################################################
	public function parametro()
	{
		$dados = array();

		$param 			= new Parametro();
		$param 			= $param->getParametros();
		

		//para configurações dos gráficos
		$rel = new Relatorios();

		$param['graphPontualTxtColor'] = isset($param['graphPontualColor']) ? $rel->getTextColorBasedOnBgColor($param['graphPontualColor']) : "#000000";
		$param['graphAdiantadoTxtColor'] = isset($param['graphAdiantadoColor']) ? $rel->getTextColorBasedOnBgColor($param['graphAdiantadoColor']) : "#000000";
		$param['graphAtrasadoTxtColor'] = isset($param['graphAtrasadoColor']) ? $rel->getTextColorBasedOnBgColor($param['graphAtrasadoColor']) : "#000000";
		$param['graphNesTxtColor'] = isset($param['graphNesColor']) ? $rel->getTextColorBasedOnBgColor($param['graphNesColor']) : "#000000";
		$param['graphAgendaTxtColor'] = isset($param['graphAgendaColor']) ? $rel->getTextColorBasedOnBgColor($param['graphAgendaColor']) : "#000000";

		$param['graphReTxtColor'] = isset($param['graphReColor']) ? $rel->getTextColorBasedOnBgColor($param['graphReColor']) : "#000000";
		$param['graphSreTxtColor'] = isset($param['graphSreColor']) ? $rel->getTextColorBasedOnBgColor($param['graphSreColor']) : "#000000";
		
		$dados['param'] = $param;

		$this->loadTemplate('configuracoes/parametros/parametro', $dados);
		exit();
	}

	public function parametroAtualizar()
	{
		$dados = array();

		$param 	= new Parametro();

		$apiKey_active = isset($_POST['apiKey_active']) ? 1 : 0;
		$apiKey_app_active = isset($_POST['apiKey_app_active']) ? 1 : 0;
		$show_rel_timer = isset($_POST['show_rel_timer']) ? 1 : 0;
		$get_veic_veltrac = isset($_POST['get_veic_veltrac']) ? 1 : 0;
		$get_linha_veltrac = isset($_POST['get_linha_veltrac']) ? 1 : 0;
		$get_gr_veltrac = isset($_POST['get_gr_veltrac']) ? 1 : 0;
		$get_cag_veltrac = isset($_POST['get_cag_veltrac']) ? 1 : 0;
		$get_iti_veltrac = isset($_POST['get_iti_veltrac']) ? 1 : 0;
		$get_trips_veltrac = isset($_POST['get_trips_veltrac']) ? 1 : 0;
		$get_tag_veltrac = isset($_POST['get_tag_veltrac']) ? 1 : 0;
		$get_pax_veltrac = isset($_POST['get_pax_veltrac']) ? 1 : 0;
		
		$re = '/^\d+(?:,\d+)*$/';

		$inactiveGroupsForm = str_replace(' ', '', $_POST['inactiveGroups']);

		$inactiveGroups =  preg_match($re, $inactiveGroupsForm) ?  $inactiveGroupsForm : null;

		$graphPontualColor 		= trim($_POST['graphPontualColor']) != "" ? trim($_POST['graphPontualColor']) : null;
		$graphAdiantadoColor 	= trim($_POST['graphAdiantadoColor']) != "" ? trim($_POST['graphAdiantadoColor']) : null;
		$graphAtrasadoColor 	= trim($_POST['graphAtrasadoColor']) != "" ? trim($_POST['graphAtrasadoColor']) : null;
		$graphNesColor 			= trim($_POST['graphNesColor']) != "" ? trim($_POST['graphNesColor']) : null;
		$graphAgendaColor 		= trim($_POST['graphAgendaColor']) != "" ? trim($_POST['graphAgendaColor']) : null;
		$graphReColor 			= trim($_POST['graphReColor']) != "" ? trim($_POST['graphReColor']) : null;
		$graphSreColor 			= trim($_POST['graphSreColor']) != "" ? trim($_POST['graphSreColor']) : null;
		$graphBarraColor 		= trim($_POST['graphBarraColor']) != "" ? trim($_POST['graphBarraColor']) : null;

		$graphPontualTxt 		= trim($_POST['graphPontualTxt']) != "" ? trim($_POST['graphPontualTxt']) : null;
		$graphAdiantadoTxt 		= trim($_POST['graphAdiantadoTxt']) != "" ? trim($_POST['graphAdiantadoTxt']) : null;
		$graphAtrasadoTxt 		= trim($_POST['graphAtrasadoTxt']) != "" ? trim( $_POST['graphAtrasadoTxt']) : null;
		$graphNesTxt 			= trim($_POST['graphNesTxt']) != "" ? trim($_POST['graphNesTxt']) : null;
		$graphAgendaTxt 		= trim($_POST['graphAgendaTxt']) != "" ? trim($_POST['graphAgendaTxt']) : null;
		$graphReTxt 			= trim($_POST['graphReTxt']) != "" ? trim($_POST['graphReTxt']) : null;
		$graphSreTxt 			= trim($_POST['graphSreTxt']) != "" ? trim($_POST['graphSreTxt']) : null;

		$atualRet = $param->atualizarParametros($_POST['Distancia'], $_POST['time_atualizar'], $_POST['ranger_dash'], $apiKey_active, $apiKey_app_active, $inactiveGroups, $_POST['rel_days'], $_POST['qtd_agendas'], $show_rel_timer, $get_veic_veltrac, $get_linha_veltrac, $get_gr_veltrac, $get_cag_veltrac, $get_iti_veltrac, $get_trips_veltrac, $get_tag_veltrac, $get_pax_veltrac, $_POST['cgfVersion'], $_POST['cgfVersionamento'], $graphPontualColor, $graphAdiantadoColor, $graphAtrasadoColor, $graphNesColor, $graphAgendaColor, $graphReColor, $graphSreColor, $graphBarraColor, $graphPontualTxt, $graphAdiantadoTxt, $graphAtrasadoTxt, $graphNesTxt, $graphAgendaTxt, $graphReTxt, $graphSreTxt);

		if($atualRet){
			$_SESSION['ms'] = "Edição Salva com sucesso!";

			if($_POST['cgfVersionamento'] != $_POST['orginalCgfVersionamento']){

				$_SESSION['clearCache'] = 1;
				$_SESSION['controlVersionChange'] = $_POST['cgfVersion'];
				$_SESSION['versionChange'] = $_POST['cgfVersionamento'];

			}
		} else {
			$_SESSION['merr'] = "Ocorreu um erro, tente novamente!";
		}

		header("Location: " . BASE_URL . "configuracoes/parametro");
		exit();
	}

	public function getGraphDefault()
	{
		$param = new Parametro;
		$param = $param->getParametros();
		echo json_encode($param);
		die;

	}

	public function getGraphGroup()
	{
		
		$param = new ParameterGroup;
		$param = $param->getParameters($_GET['idGroup'], true);
		echo json_encode($param);
		die;

	}

	##################################################################
	################# INICIANDO TOTEM ITINERARIO #####################
	##################################################################
	public function totem()
	{
		$dados = array();

		$param 			= new Totem();
		$dados['totem'] = $param->getTotem(1);

		################## TRATA Totem #################
		if(count($dados['totem'])>0){
			foreach ($dados['totem'] as $k => $lin){
				$dados['totem'][$k]['NOME'] = $lin['NOME'];
			}
		}
		#################################################

		$this->loadTemplate('configuracoes/totem/totem', $dados);
		exit();
	}

	//FUNCÃO PARA PEGAR AS QUANTIDADES DE INSTALACOES E ACESSOS totemitinerario
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function statisticsTotem(){

        $apps = new Totem();
		$getStatistics = $apps->getStatistics($_GET);

        if(is_array($getStatistics) && $getStatistics['status'] == true)
        {

            $this->loadTemplate('configuracoes/totem/statistics', $getStatistics);
		    exit();

        }else{

            $_SESSION['merr'] = "Ocorreu um erro ao carregar, tente novamente!";
            header("Location: " . BASE_URL . "configuracoes/totem/totem");

        }
		
		exit();
    }

	public function statisticsTotemExcel()
    {
       
        ignore_user_abort(false);
        session_write_close();

        $req                = new \stdClass();
        $req->groupId    = $_GET['groupId'];
        $req->nomegr    = $_GET['nomegr'];
        $req->start     = $_GET['start'];
        $req->end       = $_GET['end'];
       
        $apps = new Totem();
		$getStatistics = $apps->getStatistics($_GET);

        $nomearquivo = utf8_decode($_GET['nomegr'])." - ".utf8_decode($getStatistics['pagetitle'])." - ".APP_NAME." TOTEM - ".utf8_decode($getStatistics['mesinfo']);
       
        $dadosXls = "<table border='1'>";
        $dadosXls .= "<tr>";
        $dadosXls .= "<td colspan='2' style='text-align: center' align='center'>".utf8_decode($_GET['nomegr'])."</td>";
        $dadosXls .= "</tr>";
        $dadosXls .= "<tr>";
        $dadosXls .= "<td>".utf8_decode("Mês/Ano")."</td>";
        $dadosXls .= "<td style='width:100px'>".utf8_decode($getStatistics['pagetitle'])."</td>";
        $dadosXls .= "</tr>";         

        if(count($getStatistics['grafico']) > 0){

            foreach($getStatistics['grafico'] as $gr){
                $dadosXls .= "<tr>";
                $dadosXls .= "<td>".utf8_decode($gr[0])."</td>";
                $dadosXls .= "<td>".utf8_decode($gr[1])."</td>";
                $dadosXls .= "</tr>";
            }

        }

        $dadosXls .= "</table>";

        setcookie('excelStatisticsTotem', 'ready', -1, '/'); 
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment;filename=".$nomearquivo.".xls");
        header("Cache-Control:max-age=0");
        header("Cache-Control:max-age=1");

        echo $dadosXls;
        die();

    }

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function totemCreate()
	{
		$dados = array();

		$param 			= new Totem();
		$dados['grLin'] = $param->getGrupoLinhas();

		################## TRATA #################
		if(count($dados['grLin'])>0){
			foreach ($dados['grLin'] as $k => $lin){
				$dados['grLin'][$k]['NOME'] = $lin['NOME'];
			}
		}
		#################################################

		$this->loadTemplate('configuracoes/totem/totemCreate', $dados);
		exit();
	}

	public function totemCadastrar()
	{
		$param 		= new Totem();
		$atualRet 	= $param->atualizarTotem($_POST['ID_ORIGIN'], $_POST['LINK']);

		if($atualRet)
			$_SESSION['ms'] = "Cadastrado com sucesso!";
		else 
			$_SESSION['merr'] = "Ocorreu um erro ao cadastrar, tente novamente!";

		header("Location: " . BASE_URL . "configuracoes/totem");
		exit();
	}

	public function totemEdit()
	{
		$dados = array();

		if(isset($_GET['id']) && $_GET['id'] != ""){
			$param 				= new Totem();
			$dados['totemEdt'] 	= $param->getTotemEdit($_GET['id']);
			$dados['grLin'] 	= $param->getGrupoLinhas(true);
			################## TRATA #################
			if(count($dados['grLin'])>0){
				foreach ($dados['grLin'] as $k => $lin){
					$dados['grLin'][$k]['NOME'] = $lin['NOME'];
				}
			}
			#################################################
			$this->loadTemplate('configuracoes/totem/totemEdit', $dados);
		} else {
			$_SESSION['merr'] = "Ocorreu um erro, tente novamente!";
			header("Location: " . BASE_URL . "configuracoes/totem");
		}

		exit();
	}

	public function atualizarTotem()
	{
		$param 		= new Totem();
		$atualRet 	= $param->atualizarTotem($_POST['ID_ORIGIN'], $_POST['LINK']);

		if($atualRet)
			$_SESSION['ms'] = "Edição Salva com sucesso!";
		else
			$_SESSION['merr'] = "Ocorreu um erro, tente novamente!";

		header("Location: " . BASE_URL . "configuracoes/totem");
		exit();
	}

	public function totemDel()
	{
		$retorno = array(
			"status" => true,
			"title" => "SUCESSO",
			"text" => "Usuário com sucesso!",
			"icon" => "success",
			"button" => "OK"
		);

		$param = new Totem();

		$delete = $param->delLinkTotem($_GET['id']);

		if(!$delete['success']){
			$retorno['title'] =  "ERRO";
			$retorno['icon'] = "error";
		}

		$retorno['status'] = $delete['success'];
		$retorno['text'] = $delete['msg'];

		echo json_encode($retorno);
        die();
	}

	##################################################################
	######### INICIANDO CONFIGURAÇÕES PARA ATUALIZAR DB ##############
	##################################################################
	public function atualizarDB()
	{
		$user = new Usuarios();
		$dados['gruposUser'] = $user->grupoUsers();

		$param	= new Parametro();
		$param	= $param->getParametros();

		$dados['get_veic_veltrac'] 	= $param['get_veic_veltrac'] ?? 0;
		$dados['get_linha_veltrac']	= $param['get_linha_veltrac'] ?? 0;
		$dados['get_gr_veltrac'] 	= $param['get_gr_veltrac'] ?? 0;
		$dados['get_cag_veltrac'] 	= $param['get_cag_veltrac'] ?? 0;
		$dados['get_iti_veltrac'] 	= $param['get_iti_veltrac'] ?? 0;
		$dados['get_trips_veltrac'] = $param['get_trips_veltrac'] ?? 0;
		$dados['get_tag_veltrac'] 	= $param['get_tag_veltrac'] ?? 0;
		$dados['get_pax_veltrac'] 	= $param['get_pax_veltrac'] ?? 0;
		
		$this->loadTemplate('configuracoes/atualizarDB/atualizarDB', $dados);
		exit();
	}

	//NOVA Função para Atualizar DB Manualmente
	public function updateTables(){

		$type = $_POST['type'];

		$retorno = array(
			"status" => true,
			"title" => "SUCESSO",
			"text" => "Atualizado com sucesso!",
			"icon" => "success",
			"button" => "OK"
		);

		if(!isset($_SESSION['cType']) || (isset($_SESSION['cType']) && $_SESSION['cType'] != 1)){

			$retorno['status'] = false;
			$retorno['title'] =  "ERRO";
			$retorno['text'] = "Você não tem permissão para fazer essa alteração.";
			$retorno['icon'] = "error";

			echo json_encode($retorno);
        	die();
		}

		$veltracUpdater = new VeltracUpdater('USER', $_SESSION['cLogin']);
		
		//Para atualizar Veículos
		if($type == 'updateVeiculos'){
			$update = $veltracUpdater->updateVeiculos();
		}

		//Para atualizar Grupos de Linhas
		if($type == 'updateGRLinhas'){
			$update = $veltracUpdater->updateGRLinhas();
		}

		//Para atualizar Clientes(Grupos Controle Acesso)
		if($type == 'updateCAGrupo'){
			$update = $veltracUpdater->updateCAGrupo();
		}

		//Para atualizar Linhas
		if($type == 'updateLinhas'){
			$update = $veltracUpdater->updateLinhas();
		}

		//Para atualizar Itinerários
		if($type == 'updateItine'){
			$update = $veltracUpdater->updateItine();
		}

		//Para atualizar Viagens
		if($type == 'updateViagens'){
			$update = $veltracUpdater->updateViagens();
		}

		// Para atualizar Passageiros(Controle Acesso)
		if($type == 'updateCA'){

			$byGroup = $_POST['byGroup'];
			$update = $veltracUpdater->updateCA($byGroup);

		}

		// Para atualizar TAGS
		if($type == 'updateRfids'){

			$byGroup = $_POST['byGroup'];
			$update = $veltracUpdater->updateRfids($byGroup);

		}

		if(!$update['success']){
			$retorno['title'] =  "ERRO";
			$retorno['icon'] = "error";
		}

		$retorno['status'] = $update['success'];
		$retorno['text'] = $update['msg'];

		echo json_encode($retorno);
        die();

	}

	##################################################################
	################### INICIANDO TOTEM USUARIO ######################
	##################################################################
	public function totemUser()
	{
		$dados = array();

		$param 			= new TotemUser();
		$dados['totem'] = $param->getTotem(2);
		################## TRATA #################
		if(count($dados['totem'])>0){
			foreach ($dados['totem'] as $k => $lin){
				$dados['totem'][$k]['NOME'] = $lin['NOME'];
			}
		}
		#################################################
		$this->loadTemplate('configuracoes/totem/totemUser', $dados);
		exit();
	}

	public function totemCreateUser()
	{
		$dados = array();

		$param 			= new TotemUser();
		$dados['grLin'] = $param->getGrupoLinhas();
		################## TRATA #################
		if(count($dados['grLin'])>0){
			foreach ($dados['grLin'] as $k => $lin){
				$dados['grLin'][$k]['NOME'] = $lin['NOME'];
			}
		}
		#################################################

		$user 				= new Usuarios();
		$dados['grupos'] 	= $user->acessoGrupo();
		################## TRATA #################
		if(count($dados['grupos'])>0){
			foreach ($dados['grupos'] as $k => $lin){
				$dados['grupos'][$k]['NOME'] = $lin['NOME'];
			}
		}
		#################################################

		$this->loadTemplate('configuracoes/totem/totemCreateUser', $dados);
		exit();
	}

	public function totemCadastrarUser()
	{
		$dados = array();

		$grupos = count($_POST['grupo']) > 0 ? implode(",", $_POST['grupo']) : "";

		$param 		= new TotemUser();
		$atualRet 	= $param->atualizarTotem($_POST['ID_ORIGIN'], $_POST['LINK'], $grupos);

		if($atualRet)
			$_SESSION['ms'] = "Cadastrado com sucesso!";
		else
			$_SESSION['merr'] = "Ocorreu um erro ao cadastrar, tente novamente!";

		header("Location: " . BASE_URL . "configuracoes/totemUser/");
		exit();
	}

	public function totemEditUser()
	{
		$dados = array();

		if(isset($_GET['id']) && $_GET['id'] != ""){
			$param 				= new TotemUser();
			$dados['totemEdt'] 	= $param->getTotemEdit($_GET['id']);
			$dados['grLin'] 	= $param->getGrupoLinhas(true);
			################## TRATA #################
			if(count($dados['grLin'])>0){
				foreach ($dados['grLin'] as $k => $lin){
					$dados['grLin'][$k]['NOME'] = $lin['NOME'];
				}
			}
			#################################################
			$dados['groups']    = explode(",", $dados['totemEdt']['GRUPOSUSER']);

			$user 				= new Usuarios();
			$dados['grupos'] 	= $user->acessoGrupo();
			################## TRATA #################
			if(count($dados['grupos'])>0){
				foreach ($dados['grupos'] as $k => $lin){
					$dados['grupos'][$k]['NOME'] = $lin['NOME'];
				}
			}
			#################################################

			$this->loadTemplate('configuracoes/totem/totemEditUser', $dados);

		} else {
			$_SESSION['merr'] = "Ocorreu um erro, tente novamente!";
			header("Location: " . BASE_URL . "configuracoes/totemUser");
		}

		exit();
	}

	public function atualizarTotemUser()
	{
		$grupos = count($_POST['grupo']) > 0 ? implode(",", $_POST['grupo']) : "";

		$param 		= new TotemUser();
		$atualRet 	= $param->atualizarTotem($_POST['ID_ORIGIN'], $_POST['LINK'], $grupos);

		if($atualRet)
			$_SESSION['ms'] = "Edição Salva com sucesso!";
		else
			$_SESSION['merr'] = "Ocorreu um erro, tente novamente!";

		header("Location: " . BASE_URL . "configuracoes/totemUser");
		exit();
	}

	public function totemDelUser()
	{

		$retorno = array(
			"status" => true,
			"title" => "SUCESSO",
			"text" => "Usuário com sucesso!",
			"icon" => "success",
			"button" => "OK"
		);

		$param = new TotemUser();

		$delete = $param->delLinkTotem($_GET['id']);

		if(!$delete['success']){
			$retorno['title'] =  "ERRO";
			$retorno['icon'] = "error";
		}

		$retorno['status'] = $delete['success'];
		$retorno['text'] = $delete['msg'];

		echo json_encode($retorno);
        die();

	}

	##################################################################
	##################### INICIANDO EUROFARMA ########################
	##################################################################
	public function totemEuro()
	{

		$dados = array();

		$param 			= new TotemEuro();
		$dados['totem'] = $param->getTotem(3);

		################## TRATA #################
		if(count($dados['totem'])>0){
			foreach ($dados['totem'] as $k => $lin)
			{
				$dados['totem'][$k]['NOME'] = "EUROFARMA";
				$dados['totem'][$k]['PONTO'] = $lin['nome_ponto'];
				$dados['totem'][$k]['isOld'] = empty($lin['device_id']);
			}
		}
		#################################################
		$this->loadTemplate('configuracoes/totem/totemEuro', $dados);
		exit();
	}

	public function totemCreateEuro()
	{
		$dados = array();
		$this->loadTemplateEuroNew('configuracoes/totem/totemCreateEuro', $dados);
		exit();
	}

	public function cadastrarTotemEuro()
	{
		$dados = array();

		$param 		= new TotemEuro();

		$cadRet = $param->cadastrarTotem($_GET['ID_ORIGIN'], $_GET['LINK'], 1);
		
		if($cadRet){
			$dados['success'] = true;
			$dados['novaId'] = $cadRet;
		}

		else {
			$dados['success'] = false;
			$dados['msg'] = "Ocorreu um erro buscar o registro!";
		}

		echo json_encode($dados);die;
		
	}

	public function totemEditEuro()
	{
		$dados = array();

		if(isset($_GET['id']) && $_GET['id'] != ""){
			$param 				= new TotemEuro();
			$dados['totemEdt'] 	= $param->getTotemEdit($_GET['id']);
			$srcMarcador = [];
			$trsPonto = '';

			// foreach($dados['totemEdt']['itens'] as $item){
			// 	$pos = json_decode($item['posicaoIcone']);

			// 	$trsPonto .= '<tr><td class="nomeP-'.$item['id'].'">'.$item['nome_ponto'].'</td></tr>';   

				
			// 	if($pos->left <= '500' && ($pos->top > '110' && $pos->top <= 500)){
			// 		$pos->left += 17;
			// 		$pos->top -= 35;
			// 		$srcMarcador[$item['id']]['class'] = 'circleLeft';

			// 	}

			// 	if($pos->top < 110 && $pos->top <= 500){
			// 		$pos->left -= 35;
			// 		$pos->top += 20;
			// 		$srcMarcador[$item['id']]['class'] = 'circleUp';
			// 	}
			// 	 if($pos->top > 500){
      		// 		$pos->left -= 35; //ajustando a posição
      		// 		$pos->top -= 90 ; //ajustando a posição
			// 		$srcMarcador[$item['id']]['class'] = 'circleDown';
    		// 	}

    		// 	if($pos->left > 500 && ($pos->top > 110 && $pos->top <= 500)){
			//       $srcMarcador[$item['id']]['class'] = 'circleRight';
			//       $pos->left -= 83; //ajustando a posição
			//       $pos->top -=  35 ; //ajustando a posição
    		// 	}

			// 	$srcMarcador[$item['id']]['nome_ponto'] = $item['nome_ponto'];
				

			// 	$srcMarcador[$item['id']]['style'] = "width: 100px; position: absolute; top:".$pos->top.";left:".$pos->left;
			// 	$srcMarcador[$item['id']]['style2'] = "top:".$pos->top.";left:".$pos->left;
			// 	$srcMarcador[$item['id']]['posicaoMarcador'] = json_encode(['top' => $pos->top, 'left' => $pos->left]);
			// }
			
			// $dados['totemEdt']['geral']['srcMarker'] = $srcMarcador;
			// $dados['totemEdt']['geral']['Pontos'] = $trsPonto;
			$dados['itens'] = $dados['totemEdt']['itens'];

			$this->loadTemplateEuroNew('configuracoes/totem/totemEditEuro', $dados);

		} else {
			$_SESSION['merr'] = "Ocorreu um erro, tente novamente!";
			header("Location: " . BASE_URL . "configuracoes/totemEuro");
		}

		exit();
	}

	public function atualizarTotemEuro()
	{
		$dados = array();

		$id = $_POST['idToten'];

		$param 		= new TotemEuro();

		$cadRet 	= $param->atualizarTotem($id, $_POST['LINK'], $_POST['Ativo']);
		$cadRetItem = false;
		// if($auxiliar){
		// 	for($i = 0; $i < $auxiliar; $i++){
		// 	$nomePonto = isset($_POST['nomeponto-'.$i]) ? $_POST['nomeponto-'.$i] : null;
		// 	$horarioManha = isset($_POST['dadosManha-'.$i]) ? $_POST['dadosManha-'.$i] : null;
		// 	$horarioTarde = isset($_POST['dadosTarde-'.$i]) ? $_POST['dadosTarde-'.$i] : null;
		// 	$horarioNoite = isset($_POST['dadosNoite-'.$i]) ? $_POST['dadosNoite-'.$i] : null;
		// 	$posicaoIcone = isset($_POST['posicaoIcone-'.$i]) ? $_POST['posicaoIcone-'.$i] : null;


		// 	$cadRetItem = $param->cadastrarItensTotem($id, $nomePonto,$horarioManha,$horarioTarde, $horarioNoite, $posicaoIcone);
		// 	}
		// }
		
		if($cadRet)
			$_SESSION['ms'] = "Edição Salva com sucesso!";
		else
			$_SESSION['merr'] = "Ocorreu um erro, tente novamente!";

		header("Location: " . BASE_URL . "configuracoes/totemEuro");

		exit();
	}

	public function removerHorario()
	{
		$dados = array();

		$param = new TotemEuro();

		if(isset($_GET['id'])){
			$id = $_GET['id'];
			$del = $param->delHorario($id);
			if($del == 1){
				$dados['success'] = true;
				$dados['msg'] = "Registro removido com sucesso";
			}

		} else {
			$dados['success'] = false;
			$dados['msg'] = "Ocorreu um erro, tente novamente!";
		}

		echo json_encode($dados);die;
	}

	public function totemDeleteEuro()
	{
		$retorno = array(
			"status" => true,
			"title" => "SUCESSO",
			"text" => "Usuário com sucesso!",
			"icon" => "success",
			"button" => "OK"
		);

		$param = new TotemEuro();

		$delete = $param->delLinkTotem($_GET['id']);

		if(!$delete['success']){
			$retorno['title'] =  "ERRO";
			$retorno['icon'] = "error";
		}

		$retorno['status'] = $delete['success'];
		$retorno['text'] = $delete['msg'];

		echo json_encode($retorno);
        die();
	}

	public function getDot()
	{
		$dados = array();

		$param = new TotemEuro();

		$trsManha = '';
		$trsTarde = '';
		$trsNoite = '';

		if(isset($_GET['url'])){
			$id = explode("id-", $_GET['url'])[1];
			$dot = $param->getDot($id);
			
			if(count($dot) > 0){
				foreach($dot['horarios'] as $horario){
					
					
					$pico = $horario['horario_pico'] == 1 ? 'Não' : 'Sim';
					$rest = $horario['restaurante'] == 1 ? 'Não' : 'Sim';
					$picoAlm = $horario['pico_almoco'] == 1 ? 'Não' : 'Sim';
					if($horario['tipo'] == 'manha'){
						$tipo = 1;
					}elseif ($horario['tipo'] == 'tarde') {
						$tipo = 2;
					}
					else{
						$tipo = 3;
					}
					

					$trs = '';					
					$trs = "<tr role='row' class='odd'>";
					$trs .= "<td class = 'text-center'>".date('H:i', strtotime($horario['horario']))."</td>";
					$trs .= "<td class = 'text-center'>".$pico."</td>";
					$trs .= "<td class = 'text-center'>".$rest."</td>";
					$trs .= "<td class = 'text-center'>".$picoAlm."</td>";
					$trs .= "<td class = 'text-center'>
					<span class='btn btn-info' title='Editar' onclick='editarHorario(".$horario['id'].", ".$tipo.")'><i class='fa fa-edit'></i></span>
					<span class='btn btn-danger' title='Excluir' onclick='removerHorario(1,this, 0, 0, ".$horario['id'].")'><i class='fa fa-trash'></i></span>
					</td>";
					$trs .= "</tr>";
					if($horario['tipo'] == 'manha'){
						$trsManha .= $trs;
					}
					else if ($horario['tipo'] == 'tarde') {
						$trsTarde .= $trs;
					}
					else if($horario['tipo'] == 'noite'){
						$trsNoite .= $trs;
					}

					
				}
			}

			$dados['Item'] = $dot['Item'];
			$dados['TRManha'] = $trsManha;
			$dados['TRTarde'] = $trsTarde;
			$dados['TRNoite'] = $trsNoite;

			//var_dump($dados);die;
			
			if($dados){
				$dados['success'] = true;
				$dados['dados'] = $dados;
			}

		} else {
			$dados['success'] = false;
			$dados['msg'] = "Ocorreu um erro buscar o registro!";
		}

		echo json_encode($dados);die;
	}

	public function getDots()
	{
		$dados = array();

		$param = new TotemEuro();

		if(isset($_GET['id'])){
			$id = $_GET['id'];

			$dot = $param->getDot($id);
			$manha = [];
			$tarde = [];
			$noite = [];
			$picoAlmoco = [];
			$restaurante = [];

			if(count($dot) > 0){
				foreach($dot['horarios'] as $horario){

					if($horario['tipo'] == 'manha'){
						if($horario['pico_almoco'] == 2){
							$picoAlmoco[] = $horario;
						}
						else{
							$manha[] = $horario;
						}
						
					}
					else if ($horario['tipo'] == 'tarde') {
						if($horario['pico_almoco'] == 2){
							$picoAlmoco[] = $horario;
						}
						else{
							$tarde[] = $horario;
						}
						
					}
					else if($horario['tipo'] == 'noite'){
						if($horario['restaurante'] == 2){
							$restaurante[] = $horario;
						}
						else{
							$noite[] = $horario;
						}
						
					}
					
				}
				$manha = $this->ordena($manha);
				$tarde = $this->ordena($tarde);
				$noite = $this->ordena($noite);
				$restaurante = $this->ordena($restaurante);
				$picoAlmoco = $this->ordena($picoAlmoco);

				
				$Inter = 'Horário Intermediário';
	            $Pico = 'Horário de Pico';
	            $PicoA = 'Horário de Pico-Almoço';
	            $rest = 'Horário Fixo Restaurante';
	            $verde = "horaVerde";
	            $azul = "horaAzul";
	            $amarelo = "horaAmarelo";

				$hourManha = [];
				
				$hourTarde = [];
				
				$hourNoite = [];
				
				$hourpicoAlmoco = [];
				
				$hourrest = [];
				
				foreach ($manha as $key => $value) {
					
                    if($value['horario_pico'] == 2 && $value['restaurante'] == 1){
						array_push($hourManha, [
							'id' => $value['id'],
							'tipo' => 'manha',
							'title' => $Pico,
							'classe' => $amarelo,
							'hora' => date('H:i', strtotime($value['horario'])),
							'novo' => 'nao'
						]);	
                    //   $hourManha.= '<li novo="nao" id="'.$value['id'].'" title="'.$Pico.'" class="'.$amarelo.'">'.date('H:i', strtotime($value['horario'])).'</li>';
                    }
                    if($value['restaurante'] == 2 && $value['horario_pico'] == 1){
						array_push($hourManha, [
							'id' => $value['id'],
							'tipo' => 'manha',
							'title' => $rest,
							'classe' => $azul,
							'hora' => date('H:i', strtotime($value['horario'])),
							'novo' => 'nao'
						]);	
						// $hourManha.= '<li novo="nao" id="'.$value['id'].'" title="'.$rest.'" class="'.$azul.'">'.date('H:i', strtotime($value['horario'])).'</li>';
                    }
                    else if ($value['restaurante'] == 1 && $value['horario_pico'] == 1) {
						array_push($hourManha, [
							'id' => $value['id'],
							'tipo' => 'manha',
							'title' => $Inter,
							'classe' => $verde,
							'hora' => date('H:i', strtotime($value['horario'])),
							'novo' => 'nao'
						]);	
                    //   $hourManha.= '<li novo="nao" id="'.$value['id'].'" title="'.$Inter.'" class="'.$verde.'">'.date('H:i', strtotime($value['horario'])).'</li>';
                    }
				}
			}

			foreach ($tarde as $key => $value) {

				if($value['horario_pico'] == 2 && $value['restaurante'] == 1){
					array_push($hourTarde, [
						'id' => $value['id'],
						'title' => $Pico,
						'tipo' => 'tarde',
						'classe' => $amarelo,
						'hora' => date('H:i', strtotime($value['horario'])),
						'novo' => 'nao'
					]);	
					// $hourTarde.= '<li novo="nao" id="'.$value['id'].'" title="'.$Pico.'" class="'.$amarelo.'">'.date('H:i', strtotime($value['horario'])).'</li>';
				}
				if($value['restaurante'] == 2 && $value['horario_pico'] == 1){
					array_push($hourTarde, [
						'id' => $value['id'],
						'tipo' => 'tarde',
						'title' => $rest,
						'classe' => $azul,
						'hora' => date('H:i', strtotime($value['horario'])),
						'novo' => 'nao'
					]);	
					// $hourTarde.= '<li novo="nao" id="'.$value['id'].'" title="'.$rest.'" class="'.$azul.'">'.date('H:i', strtotime($value['horario'])).'</li>';
				}
				else if ($value['restaurante'] == 1 && $value['horario_pico'] == 1) {
					array_push($hourTarde, [
						'id' => $value['id'],
						'tipo' => 'tarde',
						'title' => $Inter,
						'classe' => $verde,
						'hora' => date('H:i', strtotime($value['horario'])),
						'novo' => 'nao'
					]);	
					// $hourTarde.= '<li novo="nao" id="'.$value['id'].'" title="'.$Inter.'" class="'.$verde.'">'.date('H:i', strtotime($value['horario'])).'</li>';
				}
			}
			
			foreach ($noite as $key => $value) {

				if($value['horario_pico'] == 2 && $value['restaurante'] == 1){
					array_push($hourNoite, [
						'id' => $value['id'],
						'tipo' => 'noite',
						'title' => $Pico,
						'classe' => $amarelo,
						'hora' => date('H:i', strtotime($value['horario'])),
						'novo' => 'nao'
					]);	
					// $hourNoite.= '<li novo="nao" id="'.$value['id'].'" title="'.$Pico.'" class="'.$amarelo.'">'.date('H:i', strtotime($value['horario'])).'</li>';
				}
				if($value['restaurante'] == 2 && $value['horario_pico'] == 1){
					array_push($hourNoite, [
						'id' => $value['id'],
						'tipo' => 'noite',
						'title' => $rest,
						'classe' => $azul,
						'hora' => date('H:i', strtotime($value['horario'])),
						'novo' => 'nao'
					]);	
					// $hourNoite.= '<li novo="nao" id="'.$value['id'].'" title="'.$rest.'" class="'.$azul.'">'.date('H:i', strtotime($value['horario'])).'</li>';
				}
				else if ($value['restaurante'] == 1 && $value['horario_pico'] == 1) {
					array_push($hourNoite, [
						'id' => $value['id'],
						'tipo' => 'noite',
						'title' => $Inter,
						'classe' => $verde,
						'hora' => date('H:i', strtotime($value['horario'])),
						'novo' => 'nao'
					]);	
					// $hourNoite.= '<li novo="nao" id="'.$value['id'].'" title="'.$Inter.'" class="'.$verde.'">'.date('H:i', strtotime($value['horario'])).'</li>';
				}
			}

			foreach ($picoAlmoco as $key => $value) {
				array_push($hourpicoAlmoco, [
					'id' => $value['id'],
					'tipo' => 'picoAlmoco',
					'title' => $PicoA,
					'classe' => $amarelo,
					'hora' => date('H:i', strtotime($value['horario'])),
					'novo' => 'nao'
				]);	
				// $hourpicoAlmoco.= '<li novo="nao" id="'.$value['id'].'" title="'.$PicoA.'" class="'.$amarelo.'">'.date('H:i', strtotime($value['horario'])).'</li>';
			}
			
			foreach ($restaurante as $key => $value) {
				array_push($hourrest, [
					'id' => $value['id'],
					'tipo' => 'restaurante',
					'title' => $rest,
					'classe' => $azul,
					'hora' => date('H:i', strtotime($value['horario'])),
					'novo' => 'nao'
				]);	
				// $hourrest.= '<li novo="nao" id="'.$value['id'].'" title="'.$rest.'" class="'.$azul.'">'.date('H:i', strtotime($value['horario'])).'</li>';
			}

			$dados['horarios']['manha'] = $hourManha;
			$dados['horarios']['tarde'] = $hourTarde;
			$dados['horarios']['noite'] = $hourNoite;
			$dados['horarios']['picoAlmoco'] = $hourpicoAlmoco;
			$dados['horarios']['restaurante'] = $hourrest;
			echo json_encode($dados);
		}
	}

	public function ordena($valor)
	{
		 usort(
				$valor,

					function( $a, $b ) {

						if( $a['horario']  == $b['horario']  ) return 0;

						return ( ( $a['horario']  < $b['horario']  ) ? -1 : 1 );
					}
			);

		return $valor;
	}

	public function getHorario()
	{
		$dados = array();

		$param = new TotemEuro();
		$horario = '';
		if(isset($_GET['url'])){
			$id = explode("id-", $_GET['url'])[1];
			$horario = $param->getHorario($id);
			$dados['success'] = true;
			$dados['horario'] = $horario;

		} else {
			$dados['success'] = false;
			$dados['msg'] = "Ocorreu um erro tente deletar o registro!";
		}

		echo json_encode($dados);die;
	}

	public function salvaHorario()
	{
		$dados = array();

		$totem_itens_id = $_POST['idItem'] ? $_POST['idItem'] : null;
		$tipo = $_POST['tipo'] ? $_POST['tipo'] : null;
		$horario = $_POST['horario'] ? $_POST['horario'] : null;
		$restaurante = $_POST['restaurante'] ? $_POST['restaurante'] : null;
		$pico_almoco = $_POST['pico_almoco'] ? $_POST['pico_almoco'] : null;
		$horario_pico = $_POST['horario_pico'] ? $_POST['horario_pico'] : null;

		$param = new TotemEuro();
		$ret = $param->addHorarioAjax($totem_itens_id, $tipo, $horario, $restaurante, $pico_almoco, $horario_pico);

		if($ret){
			$dados['success'] = true;
			$dados['msg'] = "Horário salvo com sucesso";
		}
		else{
			$dados['success'] = false;
			$dados['msg'] = "Ocorreu um erro, tente novamente!";
		}
		
		echo json_encode($dados);die;
	}

	public function salvaHorarioNew()
	{
		$dados = array();

		$idHorario = $_GET['id'] ? $_GET['id'] : null;
		$totem_itens_id = $_GET['idPonto'] ? $_GET['idPonto'] : null;
		$tipo = $_GET['tipo'] ? $_GET['tipo'] : null;
		$horario = $_GET['horario'] ? $_GET['horario'] : null;
		$restaurante = $_GET['restaurante'] ? $_GET['restaurante'] : null;
		$pico_almoco = $_GET['pico_almoco'] ? $_GET['pico_almoco'] : null;
		$horario_pico = $_GET['horario_pico'] ? $_GET['horario_pico'] : null;

		$param = new TotemEuro();
		
		$ret = $_GET['acao'] == 'edit' ? 
		$param->updateHorarioAjax($idHorario, $tipo, $horario, $restaurante, $pico_almoco, $horario_pico) : 
		$param->addHorarioAjax($totem_itens_id, $tipo, $horario, $restaurante, $pico_almoco, $horario_pico);

		if($ret['success']){
			$dados['success'] = true;
			$dados['msg'] = "Ponto alterado com sucesso";
			if($_GET['acao'] == 'add'){
				$dados['novoId'] = $ret['novoId'];
			}
			
		}

		else{
			$dados['success'] = false;
			$dados['msg'] = "Ocorreu um erro, tente novamente!";
		}
		
		echo json_encode($dados);die;
	}

	public function updateHorario()
	{
		$dados = array();

		$idHorario = $_POST['idHorario'] ? $_POST['idHorario'] : null;
		$tipo = $_POST['tipo'] ? $_POST['tipo'] : null;
		$horario = $_POST['horario'] ? $_POST['horario'] : null;
		$restaurante = $_POST['restaurante'] ? $_POST['restaurante'] : null;
		$pico_almoco = $_POST['pico_almoco'] ? $_POST['pico_almoco'] : null;
		$horario_pico = $_POST['horario_pico'] ? $_POST['horario_pico'] : null;

		$param 	= new TotemEuro();
		$ret = $param->updateHorarioAjax($idHorario, $tipo, $horario, $restaurante, $pico_almoco, $horario_pico);

		if($ret){
			$dados['success'] = true;
			$dados['msg'] = "Horário atualizado com sucesso";
			$dados['idPonto'] = $ret['totem_gerais_id'];
		}
		else{
			$dados['success'] = false;
			$dados['msg'] = "Ocorreu um erro, tente novamente!";
		}
		
		echo json_encode($dados);die;
	}

	public function salvaEdicaoPonto()
	{
		$dados = array();
		
		$id = $_GET['id'] ? $_GET['id'] : null;
		$nome_ponto = $_GET['nome_ponto'] ? $_GET['nome_ponto'] : null;
		$posicaoIcone = $_GET['posicaoIcone'] ? $_GET['posicaoIcone'] : null;

		$idToten = $_GET['idToten'];

		$param 	= new TotemEuro();
		$ret = $_GET['acao'] == 'editar' ? 
		$param->editDotAjax($id, $nome_ponto, $posicaoIcone) : 
		$param->addDotAjax($nome_ponto, $posicaoIcone, $idToten);

		if($ret['success']){
			$dados['success'] = true;
			$dados['msg'] = "Ponto alterado com sucesso";
			if($_GET['acao'] == 'adicionar'){
				$dados['novoId'] = $ret['novoId'];
			}
			
		}
		else{
			$dados['success'] = false;
			$dados['msg'] = "Ocorreu um erro, tente novamente!";
		}
		
		echo json_encode($dados);die;
	}
	

	public function removePonto()
	{
		$dados = array();

		$param = new TotemEuro();

		if(isset($_GET['id'])){
			$id = $_GET['id'];
					
			$dot = $param->removeDot($id);
			$dados['success'] = $dot ? true : false;
			echo json_encode($dados);die;
		};
	}

	public function saveResoluton()
	{
		if(isset($_POST['width']) && isset($_POST['height'])) {
			$_SESSION['screen_width'] = $_POST['width'];
			$_SESSION['screen_height'] = $_POST['height'];
			echo json_encode(array('outcome'=>'success'));
		} else {
			echo json_encode(array('outcome'=>'error','error'=>"Couldn't save dimension info"));
		}
	}
	##################################################################
	################### INICIANDO UP TABLE PAX #######################
	##################################################################
	public function tablePax()
	{
		$dados = array();

		$pax = new Pax();
		$dados['grLin'] = $pax->getGrupoLinhas();
		################## TRATA #################
		if(count($dados['grLin'])>0){
			foreach ($dados['grLin'] as $k => $lin){
				$dados['grLin'][$k]['NOME'] = utf8_encode($lin['NOME']);
			}
		}
		#################################################
		$this->loadTemplate('configuracoes/pax/tablePax', $dados);
		exit();
	}

	public function paxEdit()
	{
		$dados = array();

		if (!isset($_GET['c']))
		{
			$param 			= new Totem();
			$dados['grLin'] = $param->getGrupoLinhas();

			################## TRATA #################
			if(count($dados['grLin'])>0){
				foreach ($dados['grLin'] as $k => $lin){
					$dados['grLin'][$k]['NOME'] = utf8_encode($lin['NOME']);
				}
			}
			#################################################

			$this->loadTemplate('configuracoes/pax/tablePax', $dados);
			exit();
		}

		$pax 					= new Pax();
		$dados['infos']['id'] 	= $_GET['c'];
		$dados['infos']['g'] 	= $pax->get($_GET['c']);

		$this->loadTemplate('configuracoes/pax/paxEdit', $dados);
		exit();
	}

	public function atualizarPax()
	{
		$pax = new Pax();
		$id  = $_POST['id']; // ID Grupo Linha

		if ( isset($_FILES['file']) && $_FILES['file'] != "" && $_FILES['file']['name'] != "")
		{
			$ext = explode(".", $_FILES['file']['name']);
			$ext = $ext[1];

			if ($ext == "xlsx")
				$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
			else if ($ext == "xls")
				$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
			else if ($ext == "csv")
				$reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
			else{

				$ret = array("error"=>true, "msg"=> "Extensão de arquivo invalido");

				$dados['infos']['id'] 	= $id;
				$dados['infos']['g'] 	= $pax->get( $id );
				$dados['infos']['ret'] 	= $ret;

				$this->loadTemplate('configuracoes/pax/paxEdit', $dados);
				exit();
			}
				
			$file 		 = $_FILES['file']['tmp_name'];
			$spreadsheet = $reader->load($file);
			$sheetData   = $spreadsheet->getActiveSheet()->toArray();

			if( count( $sheetData ) > 0 ){
				// Verifica se tem dados para apagar / sobreescrever no banco
				$pax->deletePaxEspecial( $id );
			}

			foreach ($sheetData as $k => $t) 
			{
				if ($k > 0)
				{ 
					// Salva dos dados no banco
					$pax->insertPaxEspecial( $id, $t );
				}
			}

			$ret 					= array("success"=>true, "msg"=> "Arquivo Carregado com sucesso!");
			$dados['infos']['id'] 	= $id;
			$dados['infos']['g'] 	= $pax->get( $id );
			$dados['infos']['ret'] 	= $ret;

			$this->loadTemplate('configuracoes/pax/paxEdit', $dados);
			exit();
			
		} else {

			$ret 					= array("error"=>true, "msg"=> "Nenhum Arquivo Carregado!");
			$dados['infos']['id'] 	= $id;
			$dados['infos']['g'] 	= $pax->get( $id );
			$dados['infos']['ret'] 	= $ret;

			$this->loadTemplate('configuracoes/pax/paxEdit', $dados);
			exit();

		}

		
	}

	public function meusDados()
	{
		$dados 			= array();
		$user   		= new Usuarios();

		if(isset($_SESSION["cLogin"])){
			
			$dados['userEdt'] = $user->getUser($_SESSION["cLogin"]);		

			$this->loadTemplate('usuarios/usuarioDados', $dados);

		} else {

			$_SESSION['merr'] = "Ocorreu um erro, tente novamente!";
			header("Location: " . BASE_URL . "./");
		}

		exit();
	}
	
}

