<?php

ini_set('memory_limit', '-1');
set_time_limit(0);
date_default_timezone_set('America/Sao_Paulo');


class News extends model 
{

    private $host   = ""; // TODO: POPULATE WITH DATABASE HOST ADDRESS
    private $port   = ""; // TODO: POPULATE WITH DATABASE PORT NUMBER
    private $user   = ""; // TODO: POPULATE WITH DATABASE USER
    private $pass   = ""; // TODO: POPULATE WITH DATABASE PASSWORD
    private $dbName = ""; // TODO: POPULATE WITH DATABASE NAME

    public function getDados($token){

        $extractDataFromToken = $this->extractDataFromToken($token);
        $idToken = $extractDataFromToken['id'];

        $getToken = $this->db->prepare("SELECT * FROM daily_news_tk WHERE token = :token AND id = :id AND deleted_at IS NULL");
        $getToken->bindValue(":token", $token);
        $getToken->bindValue(":id", $idToken);
        $getToken->execute();

		if($getToken->rowCount() == 0) {
			return array('status' => false, 'code' => '404', 'msg' => 'Relatório Não Encontrado.');
		}

        $grupoID = $extractDataFromToken['group_id'];
        $email = $extractDataFromToken['email'];

        $findUser = $this->db->prepare("SELECT id FROM users WHERE email = :email AND groupUserID = :group_id AND ativo = 1 AND deleted_at IS NULL");
        $findUser->bindValue(":email", $email);
        $findUser->bindValue(":group_id", $grupoID);
        $findUser->execute();

        if($findUser->rowCount() == 0) {
			return array('status' => false, 'code' => '403', 'msg' => 'Usuário não encontrado.');
		}

        $canGroup = $this->db->prepare("SELECT PG.ranger_dash, PG.cad_pax_pics, PG.cad_pax_tag, PG.daily_info, GL.NOME AS grupoNome, GL.ID_ORIGIN AS grupoIDOrigin 
        FROM parameter_group PG
        INNER JOIN grupo_linhas GL ON GL.id = PG.group_id
        WHERE PG.group_id = :group_id AND PG.daily_info = 1 AND PG.deleted_at IS NULL");
        $canGroup->bindValue(":group_id", $grupoID);
        $canGroup->execute();

        if($canGroup->rowCount() == 0) {
			return array('status' => false, 'code' => '403', 'msg' => 'Grupo não encontrado.');
		}

        $paramGroup = $canGroup->fetch();

        $ranger = $paramGroup['ranger_dash'];

        if(!$ranger){
            $getRangerGeral = $this->db->prepare("SELECT ranger_dash FROM parametros");
            $getRangerGeral->execute();
            $rangerGeral = $getRangerGeral->fetch();
            $rangerGeral = isset($rangerGeral['ranger_dash']) && $rangerGeral['ranger_dash'] > 0 ? $rangerGeral['ranger_dash'] : 10;
            $ranger = $rangerGeral;
        }

        $infoToken = $getToken->fetch();
        $dataToken = $infoToken['created_at'];
        $usuario_id = $findUser->fetch();
        $usuario_id = $usuario_id['id'];
        
        $data           = date("Y-m-d", strtotime($dataToken));
        $cad_pax_pics   = $paramGroup['cad_pax_pics'] ?? 0;
        $cad_pax_tag    = $paramGroup['cad_pax_tag'] ?? 1;
        $linhas         = $this->getLinhas($paramGroup['grupoIDOrigin']);

        $titulo = INFO_TITULO;
        $titulo = $paramGroup['grupoNome'] ? $titulo." - ".$paramGroup['grupoNome'] :  $titulo;
        
        $dadosRel = $this->getDadosSintetico($data, $cad_pax_pics, $cad_pax_tag, $usuario_id, $linhas, $ranger, $paramGroup['grupoIDOrigin']);
        $dadosRel['titulo'] = $titulo;
        return array('status' => true, 'dadosRel' => $dadosRel);

    }


    private function getDadosSintetico($data, $cad_pax_pics, $cad_pax_tag, $usuario_id, $linhas, $ranger, $grupoIDOrigin){

        $req                = new \stdClass();
        $req->data_inicio 	= $data;
		$req->data_fim 		= $data;
        $req->lns 		    = $linhas;
        $req->sentido 		= 1;
        $relGet = new Relatorios;
        $dadosRel = $relGet->getDadosSintetico($req, $cad_pax_tag, false, $usuario_id);
        
        $dataTrata      = array();
        $capacUso       = array("limits" => 0, "embarcados" => 0);
        $countPax       = array();
        $hasPax         = array();

        $viagensFace = [];

        foreach($dadosRel AS $k => $ddrel){
            $ddrel  = (Object) $ddrel;
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

            ######## AJUSTANDO DADOS ############# 
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
            $dataTrata[$ddrel->IDVIAGEM]['cadastrados']     = $ddrel->PAXCADASTRADO;
            $dataTrata[$ddrel->IDVIAGEM]['DTINIFIM']        = $ddrel->DATAINIREAL ? date("Y-m-d", strtotime($ddrel->DATAINIREAL)) : date("Y-m-d", strtotime($ddrel->DATAINIPREVISTO));
            $dataTrata[$ddrel->IDVIAGEM]['DATAINIPREVISTO'] = $ddrel->DATAINIPREVISTO;
            $dataTrata[$ddrel->IDVIAGEM]['ITINERARIO_ID']   = $ddrel->ITINERARIO_ID;
        }

        if(count($viagensFace) > 0){
            $recFilters['viagensFace'] = $viagensFace;
            $treatRecognitions = $relGet->treatRecognitions($recFilters);

            if($treatRecognitions['status'] === true){
                foreach($treatRecognitions['embCad'] as $viagenId => $d){
                    $dataTrata[$viagenId]['embarcados'] = $d['embarcados'];
                    $dataTrata[$viagenId]['cadastrados'] = $d['cadastrados'];
                }
            }
        }

        $trips = array();
        $pontual = 0;
        $adiantado = 0;
        $atrasado = 0;
        $nes = 0;
        $top5 = array();
        $bottom5 = array();

        foreach($dataTrata AS $dados){

            $viagens = (Object) $dados;
            $percursor = " - ";

            $perUse = $viagens->embarcados > 0 && $viagens->capacidade > 0 ? ($viagens->embarcados * 100 / $viagens->capacidade) : 0;
            $regemb = round($perUse,1);

            $pontualidade = $relGet->trataPontualidade($ranger, false, $viagens->DATAFIMREAL, $viagens->DATAFIMPREV);
            switch ($pontualidade) {
                case 1:
                    $pontual += 1;
                    break;
                case 2:
                    $adiantado += 1;
                    break;
                case 3:
                    $atrasado += 1;
                    break;
                case 4:
                    $nes += 1;
                    break;
                default:
                    $nes += 1;
                    break;
            }
            if($pontualidade != 8 && $pontualidade != 4){
                $capacUso['limits'] += $viagens->capacidade;
                $capacUso['embarcados'] += $viagens->embarcados; 
            }

            if ($viagens->dtInicReal != null && $viagens->dtInicReal != "0000-00-00" && $viagens->DATAFIMREAL != null && $viagens->DATAFIMREAL != "0000-00-00"){

                $seconds    = strtotime($viagens->DATAFIMREAL) - strtotime($viagens->dtInicReal);
                $hours      = floor($seconds / 3600);
                $mins       = floor(($seconds - ($hours*3600)) / 60);
                $percursor  = sprintf("%02d", $hours).":".sprintf("%02d", $mins);

                $addTops = [
                    "descricao" => $viagens->descricao,
                    "linha" => $viagens->linha,
                    "regemb" => $regemb
                ];  

                if ($regemb >= 45) {
                    array_push($top5, $addTops);
                    usort($top5, function($a, $b) {
                        return $b['regemb'] <=> $a['regemb'];
                    });
                    if (count($top5) > 5) {
                        array_pop($top5);
                    }
                }

                if ($regemb <= 44) {
                    array_push($bottom5, $addTops);
                    usort($bottom5, function($a, $b) {
                        return $a['regemb'] <=> $b['regemb'];
                    });
                    if (count($bottom5) > 5) {
                        array_pop($bottom5);
                    }
                }
            }

            $trip = [
                "descricao" => $viagens->descricao,
                "linha" => $viagens->linha,
                "titulo" => "$viagens->data - $viagens->linha - $viagens->descricao",
                "pontualidade" => $pontualidade,
                "chegadaPrev" => $viagens->dtFinalPrev,
                "chegadaReal" => $viagens->dtFinalReal,
                "timePer" => $percursor,
                "km" => $viagens->kmviagem,
                "veic" => $viagens->prefixoCar,
                "cap" => $viagens->capacidade,
                "emb" => $viagens->embarcados,
                "regemb" => $regemb
            ];   

            $trips[] = $trip;
            
        }

        // Para o Gráfico do "Embarque Registrado" e "Sem Registro de Embarque"
        $inUse    = $capacUso['embarcados'] * 100 / $capacUso['limits'];
        $inUsePer = round($inUse, 1);
        $noUsePer = round((100 - $inUsePer), 1);

        // Para o Gráfico Pontualidade
        $totalTrips = count($trips);
        $percenAtrasa  = $atrasado > 0 ? round( ($atrasado * 100 / $totalTrips), 1) : 0;
        $percenAdianta = $adiantado > 0 ? round( ($adiantado * 100 / $totalTrips), 1) : 0;
        $percenPontual = $pontual > 0 ? round( ($pontual * 100 / $totalTrips), 1) : 0;
        $percenNes = $nes > 0 ? round( ($nes * 100 / $totalTrips), 1) : 0;
        
        $cardNotUse = array();

        if($cad_pax_tag == 1){
           $grupos = $relGet->getGruposLogado(false, $usuario_id);
           $cardNotUse = $this->getNotUsedCars($grupos, $data);
        }

        $graphParams = $relGet->getGraphParams($grupoIDOrigin); 

        return array(
            "data" => date("d/m/Y", strtotime($data)), 
            "trips" => $trips,
            "totalTrips" => $totalTrips,
            "pontual" => $pontual,
            "adiantado" => $adiantado,
            "atrasado" => $atrasado,
            "nes" => $nes,
            "percenAtrasa" => $percenAtrasa,
            "percenAdianta" => $percenAdianta,
            "percenPontual" => $percenPontual,
            "percenNes" => $percenNes,
            "inUsePer" => $inUsePer,
            "noUsePer" => $noUsePer,
            "top5" => $top5,
            "bottom5" => $bottom5,
            "cardNotUse" => $cardNotUse,
            "graphParams" => $graphParams
        );
    }

    private function getLinhas($grupoID)
    {
        $linhas = null;

        $sqlLines = $this->db->prepare("SELECT linhas.ID_ORIGIN FROM linhas INNER JOIN itinerarios ON itinerarios.LINHA_ID = linhas.ID_ORIGIN
            WHERE linhas.deleted_at is null AND linhas.GRUPO_LINHA_ID = {$grupoID} AND itinerarios.ATIVO = 1 order by linhas.NOME");
        $sqlLines->execute();
        $arrayLines = $sqlLines->fetchAll();

        foreach ($arrayLines as $lin)
            $linhas[] = $lin['ID_ORIGIN'];

        $linhas = count($linhas) > 0 ? implode(",", $linhas) : null;   

        return $linhas;
    }

    private function getNotUsedCars($grupos, $data)
    {
        
        $cardNotUse = array();

        try {
            $pdoSql = new \PDO ("dblib:host=$this->host:$this->port;dbname=$this->dbName;charset=utf8","$this->user","$this->pass");
        } catch (\Throwable $th) {
           $cardNotUse;
        }
        
        $dtSt       = date("Y-m-d", strtotime($data . " - 7 days"));
        
        $c          = 0;
    
        while($dtSt < $data || $c < 7) {

            $dataStart  = "{$dtSt} 00:00:00";
            $dataEnd    = "{$dtSt} 23:59:59";

            $sql = "SELECT (SELECT COUNT(*) AS T FROM CONTROLE_ACESSO_EVENTOS CAE WHERE CAE.DATAHORA BETWEEN '{$dataStart}' AND '{$dataEnd}' AND CAE.TAG = CA.TAG) AS MARK FROM CONTROLE_ACESSO AS CA JOIN CONTROLE_ACESSO_GRUPO AS CAG ON CAG.ID = CA.CONTROLE_ACESSO_GRUPO_ID WHERE CA.CONTROLE_ACESSO_GRUPO_ID IN ({$grupos}) AND CA.ATIVO = 1;";

            $sql = "SELECT 
                (SELECT COUNT(*) AS T FROM CONTROLE_ACESSO_EVENTOS CAE WHERE CAE.DATAHORA BETWEEN '{$dataStart}' AND '{$dataEnd}' AND CAE.TAG = CA.TAG) AS MARK
            FROM 
            (SELECT DISTINCT TAG, CONTROLE_ACESSO_GRUPO_ID, ATIVO
            FROM CONTROLE_ACESSO) AS CA
            JOIN CONTROLE_ACESSO_GRUPO AS CAG ON CAG.ID = CA.CONTROLE_ACESSO_GRUPO_ID 
            WHERE CA.CONTROLE_ACESSO_GRUPO_ID IN ({$grupos}) AND CA.ATIVO = 1;";
            
            $consulta = $pdoSql->query($sql);
            $count    = 0;

            if($consulta){
                $retorn = $consulta->fetchAll();
        
                foreach($retorn as $ret){
                    if ( $ret['MARK'] == 0) $count++;
                }
            } 

            $cardNotUse[$c] = array(date("d/m/Y", strtotime($dtSt)), $count);
            $dtSt = date("Y-m-d", strtotime("+1 day", strtotime($dtSt)));
            $c++;
        }

        $numberBig = 0;

        foreach($cardNotUse as $thn) 
            $numberBig = $thn[1] > $numberBig ? $thn[1] : $numberBig;

        foreach($cardNotUse as $k => $thn) 
            $cardNotUse[$k]['percent'] = $thn[1] > 0 ? round( ($thn[1] * 65 / $numberBig), 2) : 0;

        return $cardNotUse;
    }

    private function extractDataFromToken($token) {
        
        $data = base64_decode($token);
        
        list($rand, $email, $group_id, $id) = explode('|', $data);
        
        return [
            'email' => $email,
            'group_id' => $group_id,
            'id' => $id
        ];
    }

}

?>