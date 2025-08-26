<?php

class Parametro extends model 
{

	public function getParametros($group = false) 
	{

		$array 	= array();

		$sql = $this->db->prepare("SELECT * FROM parametros");
		$sql->execute();
		$paramGerais = $sql->fetch();
		
		$paramGerais['cad_pax_pics'] = 0;
		$paramGerais['cad_pax_tag'] = 0;

		//SE NÃO FOR ADMIN E ESTIVER COMO $group = true TENTA ACHAR O PARAMETRO DO GRUPO
		if($group){

			//SE ESTIVER LOGADO
			if($_SESSION['cType'] != 1 && $_SESSION['cLogin'] && !is_numeric($group)){

				$user = new Usuarios();
				$findUser = $user->getUser($_SESSION['cLogin']);

				if($findUser && $findUser['groupUserID']){
					
					$getParameterGroup = new ParameterGroup();
					$findGroupParameter = $getParameterGroup->getParameters($findUser['groupUserID']);

					if($findGroupParameter){
						$paramGerais['Distancia'] = $findGroupParameter->Distancia ?? $paramGerais['Distancia'];
						$paramGerais['time_atualizar'] = $findGroupParameter->time_atualizar ?? $paramGerais['time_atualizar'];
						$paramGerais['ranger_dash'] = $findGroupParameter->ranger_dash ?? $paramGerais['ranger_dash'];
						$paramGerais['cad_pax_pics'] = $findGroupParameter->cad_pax_pics ?? 0;
						$paramGerais['cad_pax_tag'] = $findGroupParameter->cad_pax_tag ?? 1;
						$paramGerais['graphPontualColor'] = $findGroupParameter->graphPontualColor ?? $paramGerais['graphPontualColor'];
						$paramGerais['graphAdiantadoColor'] = $findGroupParameter->graphAdiantadoColor ?? $paramGerais['graphAdiantadoColor'];
						$paramGerais['graphAtrasadoColor'] = $findGroupParameter->graphAtrasadoColor ?? $paramGerais['graphAtrasadoColor'];
						$paramGerais['graphNesColor'] = $findGroupParameter->graphNesColor ?? $paramGerais['graphNesColor'];
						$paramGerais['graphAgendaColor'] = $findGroupParameter->graphAgendaColor ?? $paramGerais['graphAgendaColor'];
						$paramGerais['graphReColor'] = $findGroupParameter->graphReColor ?? $paramGerais['graphReColor'];
						$paramGerais['graphSreColor'] = $findGroupParameter->graphSreColor ?? $paramGerais['graphSreColor'];
						$paramGerais['graphBarraColor'] = $findGroupParameter->graphBarraColor ?? $paramGerais['graphBarraColor'];
						$paramGerais['graphPontualTxt'] = $findGroupParameter->graphPontualTxt ?? $paramGerais['graphPontualTxt'];
						$paramGerais['graphAdiantadoTxt'] = $findGroupParameter->graphAdiantadoTxt ?? $paramGerais['graphAdiantadoTxt'];
						$paramGerais['graphAtrasadoTxt'] = $findGroupParameter->graphAtrasadoTxt ?? $paramGerais['graphAtrasadoTxt'];
						$paramGerais['graphNesTxt'] = $findGroupParameter->graphNesTxt ?? $paramGerais['graphNesTxt'];
						$paramGerais['graphAgendaTxt'] = $findGroupParameter->graphAgendaTxt ?? $paramGerais['graphAgendaTxt'];
						$paramGerais['graphReTxt'] = $findGroupParameter->graphReTxt ?? $paramGerais['graphReTxt'];
						$paramGerais['graphSreTxt'] = $findGroupParameter->graphSreTxt ?? $paramGerais['graphSreTxt'];
					}
					
				}
			}

			//SE NÃO ESTIVER LOGADO PARA VIEWS EXTERNAS
			if(is_numeric($group)){

				$sql = $this->db->prepare("SELECT id 
				FROM grupo_linhas
				WHERE ID_ORIGIN = {$group}");
				$sql->execute();
				$findGrupoLinha = $sql->fetch(PDO::FETCH_OBJ);
					
				if($findGrupoLinha){
					$getParameterGroup = new ParameterGroup();
					$findGroupParameter = $getParameterGroup->getParameters($findGrupoLinha->id);

					if($findGroupParameter){
						$paramGerais['Distancia'] = $findGroupParameter->Distancia ?? $paramGerais['Distancia'];
						$paramGerais['time_atualizar'] = $findGroupParameter->time_atualizar ?? $paramGerais['time_atualizar'];
						$paramGerais['ranger_dash'] = $findGroupParameter->ranger_dash ?? $paramGerais['ranger_dash'];
						$paramGerais['cad_pax_pics'] = $findGroupParameter->cad_pax_pics ?? 0;
						$paramGerais['cad_pax_tag'] = $findGroupParameter->cad_pax_tag ?? 1;
						$paramGerais['graphPontualColor'] = $findGroupParameter->graphPontualColor ?? $paramGerais['graphPontualColor'];
						$paramGerais['graphAdiantadoColor'] = $findGroupParameter->graphAdiantadoColor ?? $paramGerais['graphAdiantadoColor'];
						$paramGerais['graphAtrasadoColor'] = $findGroupParameter->graphAtrasadoColor ?? $paramGerais['graphAtrasadoColor'];
						$paramGerais['graphNesColor'] = $findGroupParameter->graphNesColor ?? $paramGerais['graphNesColor'];
						$paramGerais['graphAgendaColor'] = $findGroupParameter->graphAgendaColor ?? $paramGerais['graphAgendaColor'];
						$paramGerais['graphReColor'] = $findGroupParameter->graphReColor ?? $paramGerais['graphReColor'];
						$paramGerais['graphSreColor'] = $findGroupParameter->graphSreColor ?? $paramGerais['graphSreColor'];
						$paramGerais['graphBarraColor'] = $findGroupParameter->graphBarraColor ?? $paramGerais['graphBarraColor'];
						$paramGerais['graphPontualTxt'] = $findGroupParameter->graphPontualTxt ?? $paramGerais['graphPontualTxt'];
						$paramGerais['graphAdiantadoTxt'] = $findGroupParameter->graphAdiantadoTxt ?? $paramGerais['graphAdiantadoTxt'];
						$paramGerais['graphAtrasadoTxt'] = $findGroupParameter->graphAtrasadoTxt ?? $paramGerais['graphAtrasadoTxt'];
						$paramGerais['graphNesTxt'] = $findGroupParameter->graphNesTxt ?? $paramGerais['graphNesTxt'];
						$paramGerais['graphAgendaTxt'] = $findGroupParameter->graphAgendaTxt ?? $paramGerais['graphAgendaTxt'];
						$paramGerais['graphReTxt'] = $findGroupParameter->graphReTxt ?? $paramGerais['graphReTxt'];
						$paramGerais['graphSreTxt'] = $findGroupParameter->graphSreTxt ?? $paramGerais['graphSreTxt'];
					}
				}

			}
			
		}
		
		$array = $paramGerais;
		return $array;
	}

	public function atualizarParametros($distan, $timeAtual, $rangerDash, $apiKey_active, $apiKey_app_active, $inactiveGroups, $rel_days, $qtd_agendas, $show_rel_timer, $get_veic_veltrac, $get_linha_veltrac, $get_gr_veltrac, $get_cag_veltrac, $get_iti_veltrac, $get_trips_veltrac, $get_tag_veltrac, $get_pax_veltrac, $cgfVersion, $cgfVersionamento, $graphPontualColor, $graphAdiantadoColor, $graphAtrasadoColor, $graphNesColor, $graphAgendaColor, $graphReColor, $graphSreColor, $graphBarraColor, $graphPontualTxt, $graphAdiantadoTxt, $graphAtrasadoTxt, $graphNesTxt, $graphAgendaTxt, $graphReTxt, $graphSreTxt)
	{

		$sql = $this->db->prepare("UPDATE parametros SET Distancia = :Distancia, time_atualizar = :time_atualizar, ranger_dash = :ranger_dash, apiKey_active = :apiKey_active, apiKey_app_active = :apiKey_app_active, inactiveGroups = :inactiveGroups, rel_days = :rel_days, qtd_agendas = :qtd_agendas, show_rel_timer = :show_rel_timer, get_veic_veltrac = :get_veic_veltrac, get_linha_veltrac = :get_linha_veltrac, get_gr_veltrac = :get_gr_veltrac, get_cag_veltrac = :get_cag_veltrac, get_iti_veltrac = :get_iti_veltrac, get_trips_veltrac = :get_trips_veltrac, get_tag_veltrac = :get_tag_veltrac, get_pax_veltrac = :get_pax_veltrac, cgfVersion = :cgfVersion, cgfVersionamento = :cgfVersionamento, graphPontualColor = :graphPontualColor, graphAdiantadoColor = :graphAdiantadoColor, graphAtrasadoColor = :graphAtrasadoColor, graphNesColor = :graphNesColor, graphAgendaColor = :graphAgendaColor, graphReColor = :graphReColor, graphSreColor = :graphSreColor, graphBarraColor = :graphBarraColor, graphPontualTxt = COALESCE(:graphPontualTxt, graphPontualTxt), graphAdiantadoTxt = COALESCE(:graphAdiantadoTxt, graphAdiantadoTxt), graphAtrasadoTxt = COALESCE(:graphAtrasadoTxt, graphAtrasadoTxt), graphNesTxt = COALESCE(:graphNesTxt, graphNesTxt), graphAgendaTxt = COALESCE(:graphAgendaTxt, graphAgendaTxt), graphReTxt = COALESCE(:graphReTxt, graphReTxt), graphSreTxt = COALESCE(:graphSreTxt, graphSreTxt)");

		$sql->bindValue(":Distancia", $distan);
		$sql->bindValue(":time_atualizar", $timeAtual);
		$sql->bindValue(":ranger_dash", $rangerDash);
		$sql->bindValue(":apiKey_active", $apiKey_active);
		$sql->bindValue(":apiKey_app_active", $apiKey_app_active);
		$sql->bindValue(":inactiveGroups", $inactiveGroups);
		$sql->bindValue(":rel_days", $rel_days);
		$sql->bindValue(":qtd_agendas", $qtd_agendas);
		$sql->bindValue(":show_rel_timer", $show_rel_timer);
		$sql->bindValue(":get_veic_veltrac", $get_veic_veltrac);
		$sql->bindValue(":get_linha_veltrac", $get_linha_veltrac);
		$sql->bindValue(":get_gr_veltrac", $get_gr_veltrac);
		$sql->bindValue(":get_cag_veltrac", $get_cag_veltrac);
		$sql->bindValue(":get_iti_veltrac", $get_iti_veltrac);
		$sql->bindValue(":get_trips_veltrac", $get_trips_veltrac);
		$sql->bindValue(":get_tag_veltrac", $get_tag_veltrac);
		$sql->bindValue(":get_pax_veltrac", $get_pax_veltrac);
		$sql->bindValue(":cgfVersion", $cgfVersion);
		$sql->bindValue(":cgfVersionamento", $cgfVersionamento);
		$sql->bindValue(":graphPontualColor", $graphPontualColor);
		$sql->bindValue(":graphAdiantadoColor", $graphAdiantadoColor);
		$sql->bindValue(":graphAtrasadoColor", $graphAtrasadoColor);
		$sql->bindValue(":graphNesColor", $graphNesColor);
		$sql->bindValue(":graphAgendaColor", $graphAgendaColor);
		$sql->bindValue(":graphReColor", $graphReColor);
		$sql->bindValue(":graphSreColor", $graphSreColor);
		$sql->bindValue(":graphBarraColor", $graphBarraColor);
		$sql->bindValue(":graphPontualTxt", $graphPontualTxt);
		$sql->bindValue(":graphAdiantadoTxt", $graphAdiantadoTxt);
		$sql->bindValue(":graphAtrasadoTxt", $graphAtrasadoTxt);
		$sql->bindValue(":graphNesTxt", $graphNesTxt);
		$sql->bindValue(":graphAgendaTxt", $graphAgendaTxt);
		$sql->bindValue(":graphReTxt", $graphReTxt);
		$sql->bindValue(":graphSreTxt", $graphSreTxt);
		
		$sql->execute();

		if (!$sql)
			return false;

		return true;
	}

	public function getIntercalada()
	{
		$sql 	= $this->db->prepare("SELECT intercala FROM parametros LIMIT 1");
		$sql->execute();
		$ret = $sql->fetch();

		return $ret['intercala'];
	}

	public function saveParamPol($post)
	{
		$sql = $this->db->prepare("UPDATE acesso_grupos SET intercalarPOL = :intercalarPOL WHERE ID_ORIGIN = :ID_ORIGIN");
		$sql->bindValue(":intercalarPOL", $post['inter']);
		$sql->bindValue(":ID_ORIGIN", $post['gr']);
		$sql->execute();

		if (!$sql)
			return false;

		return true;
	}

	public function updateTerms($post)
	{

		$col = $post['id'] == 1 ? "terms" : "privacy";
		$val = addslashes($post['conteudo']);

		$sql = $this->db->prepare("UPDATE parametros SET {$col} = '{$val}' WHERE id = 1");
		$sql->execute();

		if (!$sql)
			return false;

		return true;
	}

	public function updateVersion($vAndroid, $vIos, $msgApp, $urlAndroid, $urlIos, $readCardApp)
	{
		$sql = $this->db->prepare("UPDATE parametros SET version_android = :version_android, version_ios = :version_ios, message_app = :message_app, url_android = :url_android, url_ios = :url_ios, readCardApp = :readCardApp");
		$sql->bindValue(":version_android", $vAndroid);
		$sql->bindValue(":version_ios", $vIos);
		$sql->bindValue(":message_app", $msgApp);
		$sql->bindValue(":url_android", $urlAndroid);
		$sql->bindValue(":url_ios", $urlIos);
		$sql->bindValue(":readCardApp", $readCardApp);
		$sql->execute();

		if (!$sql)
			return false;

		return true;
	}

	public function updateVersionFace($vAndroid, $msgApp, $urlAndroid, $time_send_infos_face)
	{
		$sql = $this->db->prepare("UPDATE parametros SET version_android_face = :version_android_face, message_app_face = :message_app_face, url_android_face = :url_android_face, time_send_infos_face = :time_send_infos_face");
		$sql->bindValue(":version_android_face", $vAndroid);
		$sql->bindValue(":message_app_face", $msgApp);
		$sql->bindValue(":url_android_face", $urlAndroid);
		$sql->bindValue(":time_send_infos_face", $time_send_infos_face);
		$sql->execute();

		if (!$sql)
			return false;

		return true;
	}


}
?>