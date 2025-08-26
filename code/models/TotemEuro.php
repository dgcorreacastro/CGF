<?php

class TotemEuro extends model 
{
	public $meses = array("Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro");

	public function getTotem($tipo = 0) 
	{
		$dtI = date("Y-m-") . "01 00:00:00";
		$dtF = date("Y-m-") . "31 23:59:59";

		$array 	= array();
		$sql 	= $this->db->prepare("SELECT *,
							(SELECT COUNT(*) FROM access_logs WHERE groupID = totens_gerais.grupo_linhas_id 
							AND typeTotem = '{$tipo}' AND hourAccess BETWEEN '{$dtI}' AND '{$dtF}') AS acessos
							FROM totens_gerais where Ativo = 1 AND grupo_linhas_id = 11");
		$sql->execute();
		$array = $sql->fetchAll();

		return $array;
	}

	public function cadastrarTotem($empresa, $link, $ativo)
	{
		

		$sql = $this->db->prepare("INSERT INTO totens_gerais SET 
			grupo_linhas_id = '{$empresa}',
			link = '{$link}', 
			Ativo = '{$ativo}'
			");

		
		$sql->execute();


		if (!$sql){
			return false;
		}

		$sql = $this->db->prepare("SELECT * FROM totens_gerais order by id DESC LIMIT 1");
		$sql->execute();
		$uNew   = $sql->fetch();
		return $uNew['id'];

		
	}

	public function cadastrarItensTotem($cadRet, $nomePonto,$dadosManha,$dadosTarde, $dadosNoite, $posicaoIcone){

		$sql = $this->db->prepare("INSERT INTO totem_itens SET 
			nome_ponto = '{$nomePonto}',
			posicaoIcone = '{$posicaoIcone}',
			totens_gerais_id = '{$cadRet}', 
			created_at = NOW()
			");
		$sql->execute();

		if (!$sql){
			return false;
		}

		$sql = $this->db->prepare("SELECT * FROM totem_itens order by id DESC LIMIT 1");
		$sql->execute();
		$uNew   = $sql->fetch();
		$id = $uNew['id'];


		if($dadosManha){
			$dadosManha = json_decode($dadosManha);

			foreach ($dadosManha as $data) {
				//var_dump($data->Horario);
				$sqln = $this->db->prepare("INSERT INTO totem_horarios (totem_gerais_id, tipo, horario, horario_pico, restaurante, pico_almoco) 
					VALUES (:totem_gerais_id, :TipoHorario, :Horario, :Pico, :Restaurante, :PicoAlmoco) 
					");
				$sqln->bindParam(":totem_gerais_id", $id);
				$sqln->bindParam(":TipoHorario", $data->TipoHorario);
				$sqln->bindParam(":Horario", $data->Horario);
				$sqln->bindParam(":Pico", $data->Pico);
				$sqln->bindParam(":Restaurante", $data->Restaurante);
				$sqln->bindParam(":PicoAlmoco", $data->PicoAlmoco);
				$sqln->execute();
			}
		}
		if($dadosTarde){
			$dadosTarde = json_decode($dadosTarde);
			foreach ($dadosTarde as $data) {
				//var_dump($data->Horario);
				$sqln = $this->db->prepare("INSERT INTO totem_horarios (totem_gerais_id, tipo, horario, horario_pico, restaurante, pico_almoco) 
					VALUES (:totem_gerais_id, :TipoHorario, :Horario, :Pico, :Restaurante, :PicoAlmoco) 
					");
				$sqln->bindParam(":totem_gerais_id", $id);
				$sqln->bindParam(":TipoHorario", $data->TipoHorario);
				$sqln->bindParam(":Horario", $data->Horario);
				$sqln->bindParam(":Pico", $data->Pico);
				$sqln->bindParam(":Restaurante", $data->Restaurante);
				$sqln->bindParam(":PicoAlmoco", $data->PicoAlmoco);
				$sqln->execute();
			}

		}

		if ($dadosNoite) {
			$dadosNoite = json_decode($dadosNoite);
			foreach ($dadosNoite as $data) {
				//var_dump($data->Horario);
				$sqln = $this->db->prepare("INSERT INTO totem_horarios (totem_gerais_id, tipo, horario, horario_pico, restaurante, pico_almoco) 
					VALUES (:totem_gerais_id, :TipoHorario, :Horario, :Pico, :Restaurante, :PicoAlmoco) 
					");
				$sqln->bindParam(":totem_gerais_id", $id);
				$sqln->bindParam(":TipoHorario", $data->TipoHorario);
				$sqln->bindParam(":Horario", $data->Horario);
				$sqln->bindParam(":Pico", $data->Pico);
				$sqln->bindParam(":Restaurante", $data->Restaurante);
				$sqln->bindParam(":PicoAlmoco", $data->PicoAlmoco);
				$sqln->execute();
			}
		}
		


		if (!$sql)
			return false;

		return true;
	}

	public function getStatisticsEuro($post){
        
        $groupId = $post['groupId'];
        $nomegr = $post['nomegr'];

        $dados = array();
        $grafico = array();

        $dados['status']    = true;
        $dados['groupId']   = $groupId;
        $dados['nomegr']    = $nomegr;

        //acessos gerais
        if(isset($post['groupId'])){
			
            $sqlDataFim = $this->db->prepare("SELECT 
                month(hourAccess) as mes, year(hourAccess) as ano
                FROM access_logs
                WHERE groupID = '{$groupId}'
                AND typeTotem = '3'
                ORDER BY id DESC LIMIT 1");

            $sqlDataFim->execute();
            $dF = $sqlDataFim->fetch(PDO::FETCH_OBJ);

            //SE ENCONTRA MÊS E ANO FINAL USA, SE NÃO USA MÊS ANO ATUAL
            $end = $dF != '' ? date($dF->ano.'-'.sprintf("%02d", $dF->mes)) : date("Y-m");
            $maxEnd = $dF != '' ? date($dF->ano.'-'.sprintf("%02d", $dF->mes)) : date("Y-m");
            
            //SE NÃO VEM COM DATAS, TENTA ACHAR A DATA DO PRIMEIRO E DO ÚLTIMO MES/ANO
            $sqlDataIni = $this->db->prepare("SELECT 
                month(hourAccess) as mes, year(hourAccess) as ano
                FROM access_logs
                WHERE groupID = '{$groupId}'
                AND typeTotem = '3'
                ORDER BY id ASC LIMIT 2");    


            $sqlDataIni->execute();
            $dI = $sqlDataIni->fetch(PDO::FETCH_OBJ);

            //SE ENCONTRA MÊS E ANO INICIAL USA, SE NÃO USA MÊS ANO ATUAL
            $minStart = $dI != '' ? date($dI->ano.'-'.sprintf("%02d", $dI->mes)) : date("Y-m");
            $start = date('Y-m', strtotime("$end -1 year"));
            $start = $minStart > $start ? $minStart : $start;          

            //SE VEM COM DATAS, USA AS DATAS
            if(isset($post['start']) && isset($post['end'])){

                $start  = $post['start'];
                $end    = $post['end'];

            }

            $dtI = $start . "-01 00:00:00";
		    $dtF = $end . "-31 23:59:59";

            $dados['pagetitle'] = 'Acessos';

            $sqlAcessos = $this->db->prepare("SELECT 
                COUNT(ipAccess) as acessos, month(hourAccess) as mes, year(hourAccess) as ano
                FROM access_logs
                WHERE groupID = '{$groupId}'
                AND hourAccess BETWEEN '{$dtI}' AND '{$dtF}'
				AND typeTotem = '3'
                GROUP BY month(hourAccess), year(hourAccess)
                ORDER BY ano, mes");

			$sqlAcessos->execute();

			$a = $sqlAcessos->fetchAll(PDO::FETCH_OBJ);

        }

        foreach($a as $b){
            array_push($grafico, array($this->meses[(ltrim($b->mes, '0') - 1)].'/'.$b->ano, $b->acessos));
        }

        $mesini = $this->meses[(ltrim(date("m", strtotime($start)), '0') - 1)];

        $anoini = date("Y", strtotime($start));
        $dados['mesinfo'] = $mesini.'/'.$anoini;

        if($start != $end){
            $dados['mesinfo'] .= ' - '.$this->meses[(ltrim(date("m", strtotime($end)), '0') - 1)].'/'.date("Y", strtotime($end));
        }

        $dados['start'] = $start;
        $dados['minStart'] = $minStart;
        $dados['end'] = $end;
        $dados['maxEnd'] = $maxEnd;

        $dados['grafico'] = $grafico;

        return $dados;
    }
	
	public function getTotemEdit($id)
	{
		$array 	= array();
		$sql 	= $this->db->prepare("SELECT * FROM totens_gerais where id = :id");
		$sql->bindValue(":id", $id);
		$sql->execute();
		$array['geral'] = $sql->fetch();

		$sql2 	= $this->db->prepare("SELECT * FROM totem_itens where totens_gerais_id = :id AND isnull(deleted_at)");
		$sql2->bindValue(":id", $id);
		$sql2->execute();
		$array['itens'] = $sql2->fetchAll();

		return $array;
	}

	public function getDot($id)
	{
		$array 	= array();
		$sql 	= $this->db->prepare("SELECT * FROM totem_itens where id = :id");
		$sql->bindValue(":id", $id);
		$sql->execute();
		$array['Item'] = $sql->fetch();


		$sql 	= $this->db->prepare("SELECT * FROM totem_horarios where totem_gerais_id = :id && Ativo = 1");
		$sql->bindValue(":id", $id);
		$sql->execute();
		$array['horarios'] = $sql->fetchAll();
		return $array;
	}


	public function atualizarTotem($id, $link, $ativo)
	{


		$sql = $this->db->prepare("UPDATE totens_gerais SET 
			link = '{$link}', 
			Ativo = '{$ativo}'
			WHERE id = {$id}");

		$sql->execute();


		if (!$sql)
			return false;

		return true;
	}

	public function delLinkTotem($id)
	{
		
		$sql = $this->db->prepare("UPDATE totens_gerais SET Ativo = 2 WHERE id = :id");
		$sql->bindValue(":id", $id);
		$sql->execute();

		if (!$sql)
			return false;

		$sql = $this->db->prepare("UPDATE totem_horarios SET Ativo = 2 WHERE totem_gerais_id = :id");
		$sql->bindValue(":id", $id);
		$sql->execute();

		if (!$sql)
			return ["success" => false, "msg" => "Ocorreu um erro ao remover o link, tente novamente."];

		return ["success" => true, "msg" => "Link deletado com sucesso!"];
	}

	public function getVanLocation($vanId){

		$getVan = $this->db->prepare("SELECT
		fl.latitude AS vanlat,
		fl.longitude AS vanlong
		FROM face_veiculo AS fv 
		INNER JOIN face_location AS fl ON fl.device_id = fv.device_id
		WHERE fv.veiculo_id = '{$vanId}'");
		$getVan->execute();
		if($getVan->rowCount() == 0){return array('status' => false);}

		$van = $getVan->fetch();

		$vanLocation = [
			'vanlat' => $van['vanlat'],
			'vanlong' => $van['vanlong']
		];

		return array('status' => true, 'vanLocation' => $vanLocation);
	}

	public function getBusLocation($totem){

		$sql = $this->db->prepare("SELECT 
		t.device_id,
		fl.latitude AS buslat,
		fl.longitude AS buslong
		FROM face_location AS fl
		INNER JOIN face_devices AS fd ON fd.device_id = fl.device_id AND fd.ativo = 1
		INNER JOIN totens_gerais AS t ON t.device_id = fd.device_id
		WHERE t.link = '{$totem}' AND t.Ativo = 1");
		$sql->execute();

		if($sql->rowCount() == 0){return array('status' => false);}

		$bus = $sql->fetch();

		$busLocation = [
			'lat' => $bus['buslat'],
			'lng' => $bus['buslong']
		];

		return array('status' => true, 'busLocation' => $busLocation);
	}

	public function hasTotem($totem)
	{

		$dados 	= array();
		
		$hasTotem = $this->db->prepare("SELECT device_id FROM totens_gerais where link = '{$totem}'");
		$hasTotem->execute();

		if($hasTotem->rowCount() == 0){return false;}
		$getTypeTotem = $hasTotem->fetch();
		$devicesIds = $getTypeTotem['device_id'];
		$typeTotem = empty($devicesIds) ? 'INTERNO' : 'NEWINTERNO';
		$dados['typeTotem'] = $typeTotem;

		if($typeTotem === 'INTERNO'){

			$sql 	= $this->db->prepare("SELECT g.NOME as Cliente, t.* FROM totens_gerais as t
										inner join grupo_linhas as g on t.grupo_linhas_id = g.id 
										where t.link = '{$totem}'");
			$sql->execute();
			$arrayT = $sql->fetch();
			$id = $arrayT['id'];		

			$sql2 	= $this->db->prepare("SELECT * FROM totem_itens where totens_gerais_id = '{$id}' AND deleted_at is null");
			$sql2->execute();
			$arrayI = $sql2->fetchAll();

			$dados['geral'] = $arrayT;

			if(count($arrayI) > 0){
				foreach($arrayI as $item){
				
					$id = $item['id'];

					$pos = json_decode($item['posicaoIcone']);               
				
					if($pos->left <= '500' && ($pos->top > '110' && $pos->top <= 500)){
						$pos->left += 17;
						$pos->top -= 35;
						$srcMarcador[$item['id']]['class'] = 'circleLeft';

					}

					if($pos->top < 110 && $pos->top <= 500){
						$pos->left -= 35;
						$pos->top += 20;
						$srcMarcador[$item['id']]['class'] = 'circleUp';
					}
						if($pos->top > 500){
						$pos->left -= 35; //ajustando a posição
						$pos->top -= 90 ; //ajustando a posição
						$srcMarcador[$item['id']]['class'] = 'circleDown';
					}

					if($pos->left > 500 && ($pos->top > 110 && $pos->top <= 500)){
						$srcMarcador[$item['id']]['class'] = 'circleRight';
						$pos->left -= 83; //ajustando a posição
						$pos->top -=  35 ; //ajustando a posição
					}

				
				
				// $srcMarcador[$item['id']]['nome_ponto'] = $item['nome_ponto'];
				

					$item['style'] = "width: 100px; position: absolute; top:".$pos->top.";left:".$pos->left;
					$item['style2'] = "top:".$pos->top."px;left:".$pos->left."px";
					$item['posicaoMarcador'] = json_encode(['top' => $pos->top, 'left' => $pos->left]);



				
				}
				$dados['geral']['itens'] = $arrayI;
				
				return $dados;
			}

		}
		else if($typeTotem === 'NEWINTERNO'){
			$sql = $this->db->prepare("SELECT 
			g.NOME AS Cliente,
			t.*
			FROM totens_gerais AS t
			INNER JOIN grupo_linhas AS g ON t.grupo_linhas_id = g.id 
			WHERE t.link = '{$totem}' AND t.Ativo = 1 AND t.device_id IS NOT NULL");
			$sql->execute();

			if($sql->rowCount() == 0){return false;}
			$totem = $sql->fetch();

			$devicesIds = $totem['device_id'];
			if (empty($devicesIds)){return false;}
			
			$totemId = $totem['id'];

			$devicesIdsArray = array_map('intval', explode(',', $devicesIds));
			$devicesIdsArray = array_filter($devicesIdsArray, function($id) {
				return $id > 0;
			});
			$devicesIdsSafe = implode(',', $devicesIdsArray);

			if (empty($devicesIdsSafe)){return false;}

			$getVans = $this->db->prepare("SELECT
			fl.latitude AS vanlat,
			fl.longitude AS vanlong,
			SUBSTRING_INDEX(v.NOME, '_', 1) AS NomeVan,
			fv.iconColor, fv.veiculo_id AS vanId
			FROM face_devices AS fd 
			INNER JOIN face_location AS fl ON fd.device_id = fl.device_id
			INNER JOIN face_veiculo AS fv ON fd.device_id = fv.device_id 
			INNER JOIN veiculos AS v ON fv.veiculo_id = v.ID_ORIGIN
			WHERE fd.ATIVO = 1 AND fd.id IN ($devicesIdsSafe)");
			$getVans->execute();

			if($getVans->rowCount() == 0){return false;}
			$vans = $getVans->fetchAll();
			$getPontos = $this->db->prepare("SELECT * FROM pontos_circ_euro WHERE totens_gerais_id = {$totemId} AND deleted_at IS NULL ORDER BY ordem");
			$getPontos->execute();

			if($getPontos->rowCount() == 0){return false;}

			$pontos = $getPontos->fetchAll();

			$pontosOK = [];

			foreach ($pontos as $ponto) {
				array_push($pontosOK, [
					'nome' => $ponto['nome'],
					'latitude' => $ponto['latitude'],
					'longitude' => $ponto['longitude']	
				]);
			}

			$dados['pontos'] = $pontosOK;
			$dados['vans'] = $vans;
			return $dados;
		}		

		return false;
		
	}

	public function getTotemByOriginCode($code) 
	{
		$array 	= array();
		$sql 	= $this->db->prepare("SELECT * FROM grupo_linhas where ID_ORIGIN = {$code} ORDER BY id DESC");
		$sql->execute();
		$array = $sql->fetchAll();

		return $array;
	}

	public function addHorarioAjax($totem_itens_id, $tipo, $horario, $restaurante, $pico_almoco, $horario_pico){
				$sqln = $this->db->prepare("INSERT INTO totem_horarios (totem_gerais_id, tipo, horario, horario_pico, restaurante, pico_almoco) 
					VALUES (:totem_gerais_id, :TipoHorario, :Horario, :Pico, :Restaurante, :PicoAlmoco) 
					");
				$sqln->bindParam(":totem_gerais_id", $totem_itens_id);
				$sqln->bindParam(":TipoHorario", $tipo);
				$sqln->bindParam(":Horario", $horario);
				$sqln->bindParam(":Pico", $horario_pico);
				$sqln->bindParam(":Restaurante", $restaurante);
				$sqln->bindParam(":PicoAlmoco", $pico_almoco);
				$sqln->execute();

				$ret = array();
				if (!$sqln){
					$ret['success'] = false;
				}else{
					$ret['success'] = true;
					$ret['novoId'] = $this->db->lastInsertId();;
				}
				return $ret;
	}

	public function updateHorarioAjax($idHorario, $tipo, $horario, $restaurante, $pico_almoco, $horario_pico){
		$sqln = $this->db->prepare("UPDATE totem_horarios SET 
									tipo = :TipoHorario, 
									horario = :Horario, 
									horario_pico = :Pico, 
									restaurante = :Restaurante, 
									pico_almoco = :PicoAlmoco 
									WHERE id = :id 
								");
		$sqln->bindParam(":id", $idHorario);
		$sqln->bindParam(":TipoHorario", $tipo);
		$sqln->bindParam(":Horario", $horario);
		$sqln->bindParam(":Pico", $horario_pico);
		$sqln->bindParam(":Restaurante", $restaurante);
		$sqln->bindParam(":PicoAlmoco", $pico_almoco);
		$sqln->execute();

		$ret = array();
		if (!$sqln){
			$ret['success'] = false;
		}else{
			$ret['success'] = true;
		}
		return $ret;
	}

	public function delHorario($id){
		
		$sql = $this->db->prepare("UPDATE totem_horarios SET Ativo = :Ativo WHERE id = :id");
		$sql->bindValue(":Ativo", 2);
		$sql->bindValue(":id", $id);
		$sql->execute();

		if (!$sql)
			return false;

		return true;
	}

	public function editDotAjax($id, $nome_ponto, $posicaoIcone){
		//var_dump($id ." - ".$posicaoIcone);die;
		if($posicaoIcone != null){
			$sql = $this->db->prepare("UPDATE totem_itens SET nome_ponto = :nome_ponto, posicaoIcone = :posicaoIcone WHERE id = :id");
			$sql->bindValue(":nome_ponto", $nome_ponto);
			$sql->bindValue(":posicaoIcone", $posicaoIcone);
			$sql->bindValue(":id", $id);
			$sql->execute();
		}else{
			$sql = $this->db->prepare("UPDATE totem_itens SET nome_ponto = :nome_ponto WHERE id = :id");
			$sql->bindValue(":nome_ponto", $nome_ponto);
			$sql->bindValue(":id", $id);
			$sql->execute();
		}
		
		$ret = array();
		if (!$sql){
			$ret['success'] = false;
		}else{
			$ret['success'] = true;
		}
		return $ret;
	}

	public function addDotAjax($nome_ponto, $posicaoIcone, $idToten){
		$sql = $this->db->prepare("INSERT INTO totem_itens SET 
			nome_ponto = '{$nome_ponto}',
			posicaoIcone = '{$posicaoIcone}',
			totens_gerais_id = '{$idToten}', 
			created_at = NOW()
			");
		$sql->execute();

		$ret = array();
		if (!$sql){
			$ret['success'] = false;
		}else{
			$ret['success'] = true;
			$ret['novoId'] = $this->db->lastInsertId();;
		}
		return $ret;
	}
	


	public function removeDot($id){
		$sql = $this->db->prepare("UPDATE totem_itens SET deleted_at = :Dateti WHERE id = :id");
		$sql->bindValue(":Dateti", date("Y-m-d H:i:s"));
		$sql->bindValue(":id", $id);
		$sql->execute();

		$sql = $this->db->prepare("UPDATE totem_horarios SET Ativo = :Ativo WHERE totem_gerais_id = :id");
		$sql->bindValue(":Ativo", 2);
		$sql->bindValue(":id", $id);
		$sql->execute();

		if (!$sql)
			return false;

		return true;
	}

	public function getHorario($id){

		$array 	= array();
		$sql 	= $this->db->prepare("SELECT * FROM totem_horarios where id = {$id} ");
		$sql->execute();
		$array = $sql->fetch();

		return $array;
	}

	//RELATÓRIO CIRCULAR EUROFARMA
	public function getCarrosCirc()
	{
		$vans = [];
		$getVans = $this->db->prepare("SELECT
			SUBSTRING_INDEX(v.NOME, '_', 1) AS NomeVan,
			fv.veiculo_id AS vanId,
			fd.device_id AS nomeDevice, fd.id AS deviceId 
			FROM face_devices AS fd 
			INNER JOIN face_veiculo AS fv ON fd.device_id = fv.device_id 
			INNER JOIN veiculos AS v ON fv.veiculo_id = v.ID_ORIGIN
			WHERE fd.ATIVO = 1 AND fd.circular = 1");
			$getVans->execute();
			$vans = $getVans->fetchAll();
			return $vans;
	}

	public function getDadosCirEuro($req)
	{
		$getPontos = $this->db->prepare("SELECT id, nome, latitude, longitude FROM pontos_circ_euro WHERE deleted_at IS NULL");
		$getPontos->execute();
	
		if ($getPontos->rowCount() == 0) {
			return array("status" => false);
		}
	
		$pontos = $getPontos->fetchAll(PDO::FETCH_OBJ);
	
		$dataStart = $req->data_inicio . " 00:00:00";
		$dataEnd = $req->data_fim . " 23:59:59";
		$distancia = isset($req->distancia) && is_numeric($req->distancia) ? floatval($req->distancia) : 80;
		
		$w = "";
		if(isset($req->carro) && $req->carro != "0"){
			$w = " AND v.ID_ORIGIN = '{$req->carro}'";
		}
	
		$positions = [];
	
		$getPositionCirc = $this->db->prepare("SELECT 
			pce.id AS IDPOS, pce.device_id, pce.latitude, pce.longitude, pce.created_at AS dataPos,
			SUBSTRING_INDEX(v.NOME, '_', 1) AS NomeVan, v.ID_ORIGIN AS VEICID, v.PLACA
			FROM positions_circ_euro pce
			JOIN face_veiculo fv ON fv.device_id = pce.device_id
			LEFT JOIN veiculos v ON v.ID_ORIGIN = fv.veiculo_id
			WHERE pce.created_at BETWEEN '{$dataStart}' AND '{$dataEnd}' {$w}
			ORDER BY NomeVan, pce.created_at");
		$getPositionCirc->execute();
	
		if ($getPositionCirc->rowCount() == 0) {
			return array("status" => false);
		}
	
		$positionsCirc = $getPositionCirc->fetchAll(PDO::FETCH_OBJ);
	
		foreach ($positionsCirc as $posCirc) {
			$closestPonto = null;
			$minDistance = PHP_INT_MAX;
	
			foreach ($pontos as $ponto) {
				$distance = $this->calculateDistance($posCirc->latitude, $posCirc->longitude, $ponto->latitude, $ponto->longitude);
				$distance = round($distance, 1);
	
				if ($distance < $minDistance) {
					$minDistance = $distance;
					$closestPonto = $ponto;
				}
			}
	
			if ($minDistance <= $distancia) {
				$formattedDistance = number_format($minDistance, 1, ',', '.') . 'm';
				$timeCompara = strtotime($posCirc->dataPos);
				$distaceCompara = $distancia;
				$pontoId = $closestPonto->id;
			
				// Filtrar para encontrar pontos com o mesmo pontoId e diferença de tempo menor que 5 minutos e distância menor
				$pointSameId = array_filter($positions[$posCirc->NomeVan], function($position) use ($timeCompara, $pontoId, $distaceCompara) {
					return $position['pontoId'] == $pontoId 
						&& abs($timeCompara - $position['timeCompara']) < 180 
						&& $distaceCompara >= $position['distaceCompara'];
				});

				if (empty($pointSameId)) {
					$positions[$posCirc->NomeVan][] = array(
						"device_id" => $posCirc->device_id,
						"latitude" => $posCirc->latitude,
						"longitude" => $posCirc->longitude,
						"dataPos" => date("d/m/Y H:i:s", strtotime($posCirc->dataPos)),
						"NomeVan" => $posCirc->NomeVan,
						"PLACA" => $posCirc->PLACA,
						"pontoNome" => $closestPonto->nome,
						"distance" => $formattedDistance,
						"pontoId" => $closestPonto->id,
						"timeCompara" => $timeCompara,
						"distaceCompara" => $distaceCompara
					);
				}
			}
			
		}
	
		return array("status" => true, "positions" => $positions);
	}
	
	private function calculateDistance($lat1, $lon1, $lat2, $lon2)
	{
		$earthRadius = 6371000;
	
		$dLat = deg2rad($lat2 - $lat1);
		$dLon = deg2rad($lon2 - $lon1);
	
		$a = sin($dLat / 2) * sin($dLat / 2) +
			cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
			sin($dLon / 2) * sin($dLon / 2);
	
		$c = 2 * atan2(sqrt($a), sqrt(1 - $a));
	
		$distance = $earthRadius * $c;
	
		return $distance;
	}
	

}
?>