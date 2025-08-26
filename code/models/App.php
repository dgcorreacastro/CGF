<?php

ini_set('memory_limit', '-1');

date_default_timezone_set('America/Sao_Paulo');

require_once __DIR__ . "/../core/model.php";

require_once  __DIR__ . '/../Services/TalentumNotification.php';

class App extends model 
{

    private $host   = ""; // TODO: POPULATE WITH DATABASE HOST ADDRESS
    private $port   = ""; // TODO: POPULATE WITH DATABASE PORT NUMBER
    private $user   = ""; // TODO: POPULATE WITH DATABASE USER
    private $pass   = ""; // TODO: POPULATE WITH DATABASE PASSWORD
    private $dbName = ""; // TODO: POPULATE WITH DATABASE NAME

    public $meses = array("Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro");

    public function list()
    {
        
        $sql2 = $this->db->prepare("SELECT 
                                        app_links.id,
                                        grupo_linhas.NOME as cliente,
                                        grupo_linhas.ID_ORIGIN as groupId,
                                        app_links.codigo,
                                        app_links.qrcode
                                    FROM app_links 
                                    INNER JOIN grupo_linhas ON grupo_linhas.ID_ORIGIN = app_links.cliente_id
                                    WHERE app_links.deleted_at IS NULL");
        $sql2->execute();

        $lista = $sql2->fetchAll(PDO::FETCH_OBJ);

        if(count($lista) > 0){

            foreach ($lista as $key => $item) {

                $dtI = date("Y-m-") . "01 00:00:00";
		        $dtF = date("Y-m-") . "31 23:59:59";

                $lista[$key]->instalacoes = 0;

                $sqlInstala = $this->db->prepare("SELECT 
                    COUNT(DISTINCT deviceID) as instalacoes
                FROM allowdevices 
                WHERE deviceID != ''
                AND (TRIM(code) = TRIM('{$item->codigo}') OR TRIM(code) = TRIM('{$item->qrcode}'))
                AND dateallow BETWEEN '{$dtI}' AND '{$dtF}'");

                $sqlInstala->execute();

                $a = $sqlInstala->fetch();

                $lista[$key]->instalacoes = $a['instalacoes'];

                $lista[$key]->acessos = 0;

                $sqlAcessos = $this->db->prepare("SELECT 
                    COUNT(deviceID) as acessos
                FROM appUseByDay 
                WHERE groupID = '{$item->groupId}'
                AND dateUse BETWEEN '{$dtI}' AND '{$dtF}'");

                $sqlAcessos->execute();

                $b = $sqlAcessos->fetch();

                $lista[$key]->acessos = $b['acessos'];

            }

        }
      
        $mes = $this->meses[(ltrim(date("m"), '0') - 1)].'/'.date(("Y"));

        $retorno = array("apps" => $lista, "mes" => $mes);
        
		return $retorno;
    }

    public function getStatistics($post){
        
        $groupId = $post['groupId'];
        $nomegr = $post['nomegr'];
       
        $dados = array();

        $grafico = array();

        $dados['status']    = true;
        $dados['groupId']   = $groupId;
        $dados['codigo']    = '';
        $dados['qrcode']    = '';
        $dados['nomegr']    = $nomegr;

        //PARA INSTALAÇÕES
        if(isset($post['qrcode']) && $post['qrcode'] != '' && isset($post['codigo']) && $post['codigo'] != ''){

            $codigo = $post['codigo'];
            $qrcode = $post['qrcode'];

            $dados['codigo'] = $codigo;
            $dados['qrcode'] = $qrcode;


            //SE NÃO VEM COM DATAS, TENTA ACHAR A DATA DO PRIMEIRO E DO ÚLTIMO MES/ANO
            $sqlDataIni = $this->db->prepare("SELECT  
                month(dateallow) as mes, year(dateallow) as ano
                FROM allowdevices 
                WHERE deviceID != '' AND
                (TRIM(code) = TRIM('{$codigo}') OR TRIM(code) = TRIM('{$qrcode}'))
                ORDER BY id ASC LIMIT 1");

                $sqlDataIni->execute();
                $dI = $sqlDataIni->fetch(PDO::FETCH_OBJ);
                
                //SE ENCONTRA MÊS E ANO INICIAL USA, SE NÃO USA MÊS ANO ATUAL
                $start = $dI != '' ? date($dI->ano.'-'.sprintf("%02d", $dI->mes)) : date("Y-m");
                $minStart = $dI != '' ? date($dI->ano.'-'.sprintf("%02d", $dI->mes)) : date("Y-m");
            
            $sqlDataFim = $this->db->prepare("SELECT  
                month(dateallow) as mes, year(dateallow) as ano
                FROM allowdevices 
                WHERE deviceID != '' AND
                (TRIM(code) = TRIM('{$codigo}') OR TRIM(code) = TRIM('{$qrcode}'))
                ORDER BY id DESC LIMIT 1");

                $sqlDataFim->execute();
                $dF = $sqlDataFim->fetch(PDO::FETCH_OBJ);

                //SE ENCONTRA MÊS E ANO FINAL USA, SE NÃO USA MÊS ANO ATUAL
                $end = $dF != '' ? date($dF->ano.'-'.sprintf("%02d", $dF->mes)) : date("Y-m");
                $maxEnd = $dF != '' ? date($dF->ano.'-'.sprintf("%02d", $dF->mes)) : date("Y-m");

            //SE VEM COM DATAS, USA AS DATAS
            if(isset($post['start']) && isset($post['end'])){

                $start  = $post['start'];
                $end    = $post['end'];

            }

            $dtI = $start . "-01 00:00:00";
		    $dtF = $end . "-31 23:59:59";

            $dados['pagetitle'] = 'Instalações';

            $sqlInstala = $this->db->prepare("SELECT  
                COUNT(DISTINCT deviceID) as acessos, month(dateallow) as mes, year(dateallow) as ano
                FROM allowdevices 
                WHERE deviceID != '' AND
                (TRIM(code) = TRIM('{$codigo}') OR TRIM(code) = TRIM('{$qrcode}'))
                AND dateallow BETWEEN '{$dtI}' AND '{$dtF}'
                GROUP BY month(dateallow), year(dateallow)
                ORDER BY ano, mes");

                $sqlInstala->execute();

                $a = $sqlInstala->fetchAll(PDO::FETCH_OBJ);


        }else{

            //PARA ACESSOS

             //SE NÃO VEM COM DATAS, TENTA ACHAR A DATA DO PRIMEIRO E DO ÚLTIMO MES/ANO
             $sqlDataIni = $this->db->prepare("SELECT 
                month(dateUse) as mes, year(dateUse) as ano
                FROM appUseByDay 
                WHERE groupID = '{$groupId}'
                ORDER BY id ASC LIMIT 1");

                $sqlDataIni->execute();
                $dI = $sqlDataIni->fetch(PDO::FETCH_OBJ);
                
                //SE ENCONTRA MÊS E ANO INICIAL USA, SE NÃO USA MÊS ANO ATUAL
                $start = $dI != '' ? date($dI->ano.'-'.sprintf("%02d", $dI->mes)) : date("Y-m");
                $minStart = $dI != '' ? date($dI->ano.'-'.sprintf("%02d", $dI->mes)) : date("Y-m");
             
            $sqlDataFim = $this->db->prepare("SELECT 
                month(dateUse) as mes, year(dateUse) as ano
                FROM appUseByDay 
                WHERE groupID = '{$groupId}'
                ORDER BY id DESC LIMIT 1");

                $sqlDataFim->execute();
                $dF = $sqlDataFim->fetch(PDO::FETCH_OBJ);

                //SE ENCONTRA MÊS E ANO FINAL USA, SE NÃO USA MÊS ANO ATUAL
                $end = $dF != '' ? date($dF->ano.'-'.sprintf("%02d", $dF->mes)) : date("Y-m");
                $maxEnd = $dF != '' ? date($dF->ano.'-'.sprintf("%02d", $dF->mes)) : date("Y-m");

            //SE VEM COM DATAS, USA AS DATAS
            if(isset($post['start']) && isset($post['end'])){

                $start  = $post['start'];
                $end    = $post['end'];

            }

            $dtI = $start . "-01 00:00:00";
		    $dtF = $end . "-31 23:59:59";

            $dados['pagetitle'] = 'Acessos';

            $sqlAcessos = $this->db->prepare("SELECT 
                COUNT(deviceID) as acessos, month(dateUse) as mes, year(dateUse) as ano
                FROM appUseByDay 
                WHERE groupID = '{$groupId}'
                AND dateUse BETWEEN '{$dtI}' AND '{$dtF}'
                GROUP BY month(dateUse), year(dateUse)
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

    public function save( $post )
    {

        $post = (object) $post;

        // CHECK SE TEM SETOR CADASTRADO \\ 
        $sql2 = $this->db->prepare("SELECT * FROM app_links WHERE cliente_id = {$post->ID_ORIGIN} AND deleted_at IS NULL");
        $sql2->execute();
        $m = $sql2->fetch(PDO::FETCH_OBJ);
    
        if( !$m )
        {
            $groups = implode(",", $post->grupo);
            $sql = $this->db->prepare("INSERT INTO app_links (cliente_id, codigo, qrcode, groupAccess, groupDefault, register, embarqueQr, mostraSentido, isCard, exigeCad, exigeMotive, beep_embarque, beep_desembarque, created_at) VALUES ({$post->ID_ORIGIN}, '{$post->codigo}', '{$post->qrcode}', '{$groups}', '{$post->groupDefault}', {$post->register}, {$post->embarqueQr}, {$post->mostraSentido}, {$post->isCard}, {$post->exigeCad}, {$post->exigeMotive}, {$post->beep_embarque}, {$post->beep_desembarque}, NOW())");

            try{

                $sql->execute();
                $this->menuPermEmbarqueSemCartao($post->ID_ORIGIN, $post->embarqueQr); 

            }catch (\Throwable $th) {

                return array("status" => false, "message" => "Erro ao criar, tente novamente.");
            }

        } else {
            return array("status" => false, "message" => "Cadastro já existente");
        }

        return array("status" => true);
    }

    public function get($id)
    {
        $sql = $this->db->prepare("SELECT * FROM app_links WHERE id = {$id}");
        $sql->execute();
      
		return $sql->fetch(PDO::FETCH_OBJ);
    }

    public function update($post)
	{
        $sql = $this->db->prepare("UPDATE app_links SET codigo = :codigo, qrcode = :qrcode, groupAccess = :groupAccess, groupDefault = :groupDefault, register = :register, embarqueQr = :embarqueQr, mostraSentido = :mostraSentido, isCard = :isCard, exigeCad = :exigeCad, exigeMotive = :exigeMotive, beep_embarque = :beep_embarque, beep_desembarque = :beep_desembarque, updated_at = NOW() where id = :id");
		$sql->bindValue(":codigo", $post['codigo']);
		$sql->bindValue(":qrcode", $post['qrcode']);
		$sql->bindValue(":groupAccess", implode(",", $post['grupo']));
        $sql->bindValue(":groupDefault", $post['groupDefault']);
		$sql->bindValue(":register", $post['register']);
        $sql->bindValue(":embarqueQr", $post['embarqueQr']);
        $sql->bindValue(":mostraSentido", $post['mostraSentido']);
        $sql->bindValue(":isCard", $post['isCard']);
        $sql->bindValue(":exigeCad", $post['exigeCad']);
        $sql->bindValue(":exigeMotive", $post['exigeMotive']);
        $sql->bindValue(":beep_embarque", $post['beep_embarque']);
        $sql->bindValue(":beep_desembarque", $post['beep_desembarque']);
		$sql->bindValue(":id", $post['id']);

        try {
            
            $sql->execute();
            $this->menuPermEmbarqueSemCartao($post['ID_ORIGIN_EDIT'], $post['embarqueQr']);            

        } catch (\Throwable $th) {
            return array("status" => false, "message" => "Erro ao atualizar, tente novamente.");
        }
		
		return array("status" => true);
	}

    private function menuPermEmbarqueSemCartao($groupUserID, $embarqueQr){

        $sqlGrupoId = $this->db->prepare("SELECT id 
        FROM grupo_linhas WHERE ID_ORIGIN = {$groupUserID} AND deleted_at IS NULL LIMIT 1");

        $sqlGrupoId->execute();

        if($sqlGrupoId->rowCount() == 1){

            $grupoId = $sqlGrupoId->fetch(PDO::FETCH_OBJ);

            $sqlUsers = $this->db->prepare("SELECT id 
            FROM users WHERE groupUserID = {$grupoId->id} AND type = 2 AND deleted_at IS NULL");

            $sqlUsers->execute();

            if($sqlUsers->rowCount() > 0){
                
                $users = $sqlUsers->fetchAll(PDO::FETCH_OBJ);

                foreach($users as $user){

                    $hasPerm = $this->db->prepare("SELECT id FROM permissionsMenu WHERE  userID = :userID AND menuID = :menuID AND deleted_at IS NULL LIMIT 1");
                    $hasPerm->bindValue(":menuID", 8);
                    $hasPerm->bindValue(":userID", $user->id);
                    $hasPerm->execute();       

                    //Se permitir Embarque QRCode Adiciona Permissão Para ver os Embarques
                    if($embarqueQr == 1 && $hasPerm->rowCount() == 0){

                        $sql = $this->db->prepare("INSERT INTO permissionsMenu SET menuID = :menuID, userID = :userID, created_at = NOW()");
                        $sql->bindValue(":menuID", 8);
                        $sql->bindValue(":userID", $user->id);
                        $sql->execute();
                        
                    }else{
                    
                        //Se não permitir Embarque QRCode Remove Permissão Para ver os Embarques caso tenha
                        if($hasPerm->rowCount() == 1){

                            $perm = $hasPerm->fetch(PDO::FETCH_OBJ);

                            $deletePerm = $this->db->prepare("UPDATE permissionsMenu SET deleted_at = NOW() WHERE id = {$perm->id}");
                            $deletePerm->execute();

                        }
                    
                    }
                }

            }
        }

    }

    public function delete($id)
    {

        $removePerm = false;
        $getGrId = $this->db->prepare("SELECT cliente_id FROM app_links WHERE id = {$id} LIMIT 1");
        $getGrId->execute();

        if($getGrId->rowCount() == 1){
            $grId = $getGrId->fetch(PDO::FETCH_OBJ);
            $removePerm = $grId->cliente_id;
        }

        $sql = $this->db->prepare("UPDATE app_links SET deleted_at = NOW() WHERE id = {$id}");

        try {
            
            $sql->execute();
            
            if($removePerm){
                $this->menuPermEmbarqueSemCartao($removePerm, 0);  
            }
             
            return ["success" => true, "msg" => "Link deletado com sucesso!"]; 

        } catch (\Throwable $th) {
            return ["success" => false, "msg" => "Ocorreu um erro ao remover o link, tente novamente."];
        }
		
    }

    public function getLinesByQrCode($qrCode, $iscode = false, $sentido)
    {

        $w = !$iscode ? "app_links.qrcode = '{$qrCode}'" : "app_links.codigo = '{$qrCode}'";

        $sql = $this->db->prepare("SELECT   
                                        linhas.ID_ORIGIN,
                                        linhas.PREFIXO,
                                        linhas.NOME AS LINHA
                                    FROM linhas
                                INNER JOIN app_links ON app_links.cliente_id = linhas.GRUPO_LINHA_ID
                                INNER JOIN itinerarios ON itinerarios.LINHA_ID = linhas.ID_ORIGIN
                                WHERE {$w} AND linhas.ATIVO = 1 AND itinerarios.SENTIDO = {$sentido} AND itinerarios.ATIVO = 1");
        $sql->execute();
      
		return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    public function getAllLines($groupID, $sentido)
    {
        $sql = $this->db->prepare("SELECT   
                                        linhas.ID_ORIGIN,
                                        linhas.PREFIXO,
                                        linhas.NOME AS LINHA
                                    FROM linhas
                                INNER JOIN app_links ON app_links.cliente_id = linhas.GRUPO_LINHA_ID
                                JOIN itinerarios ON itinerarios.LINHA_ID = linhas.ID_ORIGIN
                                WHERE cliente_id = {$groupID} AND linhas.deleted_at IS NULL AND linhas.ATIVO = 1 AND itinerarios.SENTIDO = {$sentido} AND itinerarios.ATIVO = 1 ORDER BY linhas.PREFIXO, linhas.NOME");
        $sql->execute();
      
		return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    public function getAllPoints($groupID, $line, $sentido)
    {

        $arr = array();

        /// Busca o ID original do Itinerário \\\
        $sql = $this->db->prepare("SELECT itinerarios.ID_ORIGIN AS ITINERID
                                        FROM linhas
                                    INNER JOIN itinerarios ON itinerarios.LINHA_ID = linhas.ID_ORIGIN
                                    WHERE GRUPO_LINHA_ID = {$groupID} AND linhas.ID_ORIGIN = {$line} AND itinerarios.ATIVO = 1");
        $sql->execute();
        $ret = $sql->fetch(PDO::FETCH_OBJ);

        /// Busca o ID original do Itinerário \\\
        if ($ret)
        {
            try {
                $pdoSql = new \PDO ("dblib:host=$this->host:$this->port;dbname=$this->dbName;charset=utf8","$this->user","$this->pass");
            } catch (\Throwable $th) {
                return array('status' => false, 'message'=>'Ocorreu um erro ao tentar conectar, tente novamente.', "error" => true);
            }

            //SE O SENTIDO FOR DE IDA BUSCA TODOS OS PONTOS DO ITINERÁRIO
            //SE O SENTIDO FOR DE VOLTA BUSCA SOMENTE O ÚLTMO PONTO DO ITINERÁRIO
            $top    = $sentido == 1 ? 'TOP 1' : '';
   
            $sql = "SELECT {$top} PTR.ID, PTR.NOME, PTR.LOCALIZACAO
                        FROM PONTOS_ITINERARIO PTI 
                    INNER JOIN PONTOS_REFERENCIA PTR ON PTR.ID = PTI.PONTO_REFERENCIA_ID
                    WHERE PTI.ITINERARIO_ID = {$ret->ITINERID} AND PTR.CATEGORIA_ID IN (3, 4, 5)
                    ORDER BY PTI.SEQUENCIA";
    
            $consulta   = $pdoSql->query($sql);    
            $arr        = $consulta->fetchAll();

        }

		return $arr;
    }

    public function getDriveAndCard($line)
    {
        try {
            $pdoSql = new \PDO ("dblib:host=$this->host:$this->port;dbname=$this->dbName;charset=utf8","$this->user","$this->pass");
        } catch (\Throwable $th) {
            return array('status' => false, 'message'=>'Ocorreu um erro ao tentar conectar, tente novamente.', "error" => true);
        }

        $arr = array('status' => false, 'driver' => ' - ', 'car' => ' - ' );
        
        $start = date("Y-m-d") . " 00:00:00";
        $end   = date("Y-m-d") . " 23:59:59";

        $sql = "SELECT VIC.PLACA, VIC.ID
                    FROM LINHAS LI 
                JOIN ITINERARIOS ITI ON ITI.LINHA_ID = LI.ID AND ITI.ATIVO = 1
                JOIN VIAGENS VI ON VI.ITINERARIO_ID = ITI.ID AND DATAHORA_INICIAL_PREVISTO > '{$start}' AND DATAHORA_FINAL_PREVISTO < '{$end}'
                JOIN VEICULO VIC ON VIC.ID = VI.VEICULO_ID AND VIC.ATIVO = 1
                WHERE LI.ID = {$line}";

        $cons           = $pdoSql->query($sql);    
        $consulta       = $cons->fetch();
        $arr['car']     = $consulta ? $consulta['PLACA'] : ' - ';
        $arr['carID']   = $consulta ? $consulta['ID'] : ' - ';
        $arr['status']  = $consulta ? true : false;

        if ($consulta)
        {
            $sql = "SELECT TOP 1 MVT.DATAHORA_INICIO, MT.NOME 
                        FROM BD_CLIENTE.dbo.MOTORISTAS_VIGENCIA MVT
                    JOIN MOTORISTAS MT ON MT.ID = MVT.MOTORISTA_ID 
                    WHERE MVT.VEICULO_ID = " . $consulta['ID'] . " ORDER BY MVT.DATAHORA_INICIO DESC";

            $motor          = $pdoSql->query($sql);  
            $motorC         = $motor->fetch();    
            $arr['driver']  = $motorC ? $motorC['NOME'] : ' - ';
        }

        return $arr;
    }

    public function hasAccess($body)
    {
        $w = "";

        if (isset($body->register))
            $w = " AND TRIM(LEADING '0' FROM MATRICULA_FUNCIONAL) = TRIM(LEADING '0' FROM '{$body->register}')";

        if (isset($body->numCard))
            $w .= " AND TRIM(LEADING '0' FROM TAG) = TRIM(LEADING '0' FROM '{$body->numCard}')";

            $groupAccess = $this->db->prepare("SELECT groupAccess FROM app_links WHERE cliente_id = '{$body->groupID}'");

            $groupAccess->execute();

            $groupAccess = $groupAccess->fetch(PDO::FETCH_OBJ);

            $groupAccess = $groupAccess ? $groupAccess->groupAccess : 0;

            $sql = $this->db->prepare("SELECT * FROM controle_acessos 
            WHERE ATIVO = 1 {$w} AND CONTROLE_ACESSO_GRUPO_ID IN ($groupAccess)
        ");

        $sql->execute();
        $pax = $sql->fetchAll(PDO::FETCH_OBJ);

        if ( $pax )
            return json_encode( array("status" => true, "message" => "Passageiro encontrato", "pax" => $pax[0]) );

        /**
         * Se não tiver cadastro um passeiro
         * Usei coluna ID_UNICO e unidadeID para deixar como Pendente
         */
        $name       = $body->name;
        $register   = $body->register ?? "";
        $groupID    = $body->groupID;
        $numCard    = $body->numCard ?? "";
        $email      = $body->email ?? "";
        $cellphone  = $body->cellphone ?? "";

        $q = "INSERT INTO controle_acessos (ID_ORIGIN, NOME, CONTROLE_ACESSO_GRUPO_ID, MATRICULA_FUNCIONAL, ID_UNICO, ATIVO, TAG, created_at, unidadeID, email, cellphone) VALUES (0, '{$name}', 0, '{$register}', {$groupID}, 1, '{$numCard}', NOW(), {$groupID}, '{$email}', '{$cellphone}')";
        $sql = $this->db->prepare($q);
        $sql->execute();

        $sql = $this->db->prepare("SELECT * FROM controle_acessos order by id desc LIMIT 1");
        $sql->execute();
        $pax = $sql->fetch(PDO::FETCH_OBJ);
        
        return json_encode( array("status" => true, "message" => "Passageiro encontrato", "pax" => $pax) );
    }

    public function getGroupByCode($code, $iscode = false)
    {
        if(!$iscode){
            $sql = $this->db->prepare("SELECT * FROM app_links WHERE qrcode = '{$code}' AND deleted_at IS NULL");
        }else{
            $sql = $this->db->prepare("SELECT * FROM app_links WHERE codigo = '{$code}' AND deleted_at IS NULL");
        }
        
        $sql->execute();
        $ret = $sql->fetch(PDO::FETCH_OBJ);

        return $ret;
    }

    public function getPointsRef($line)
    {

        try {
            $pdoSql = new \PDO ("dblib:host=$this->host:$this->port;dbname=$this->dbName;charset=utf8","$this->user","$this->pass");
        } catch (\Throwable $th) {
            return array('status' => false, 'message'=>'Ocorreu um erro ao tentar conectar, tente novamente.', "error" => true);
        }

        $start = date("Y-m-d") . " 00:00:00";
        $end   = date("Y-m-d") . " 23:59:59";

        $sql = "SELECT PR.ID, PR.NOME, PR.LOGRADOURO, PR.LOCALIZACAO, PR.LATITUDE, PR.LONGITUDE, HV.DATAHORA_ENTRADA_PREVISTO, HV.DATAHORA_SAIDA_PREVISTO AS 'SAIDAPREVISTA', HV.DATAHORA_SAIDA_REALIZADO, FORMAT(HV.DATAHORA_SAIDA_PREVISTO, 'HH:mm') AS DATAHORA_SAIDA_PREVISTO, HV.PONTO_ITINERARIO_ID
                FROM PONTOS_REFERENCIA PR 
                JOIN PONTOS_ITINERARIO PTI ON PTI.PONTO_REFERENCIA_ID = PR.ID 
                JOIN HORARIOS_VIAGEM HV ON HV.PONTO_ITINERARIO_ID = PTI.ID 
                JOIN VIAGENS VI ON VI.ID = HV.VIAGEM_ID AND DATAHORA_INICIAL_PREVISTO > '{$start}' AND DATAHORA_FINAL_PREVISTO < '{$end}'
                JOIN ITINERARIOS ITI ON ITI.ID = VI.ITINERARIO_ID AND ITI.ATIVO = 1
                WHERE ITI.LINHA_ID = {$line} AND PR.CATEGORIA_ID IN (3, 4, 5)
                ORDER BY HV.PONTO_ITINERARIO_ID";

        $consulta = $pdoSql->query($sql);   

        return $consulta->fetchAll(); 
    }

    public function infosDotsLine($body)
    {

        try {
            $pdoSql = new \PDO ("dblib:host=$this->host:$this->port;dbname=$this->dbName;charset=utf8","$this->user","$this->pass");
        } catch (\Throwable $th) {
            return array('status' => false, 'message'=>'Ocorreu um erro ao tentar conectar, tente novamente.', "error" => true);
        }

        $arr = array();

        /**
         * Buscar Lat e Long do Ponto Embarque 
         */
        $sql          = "SELECT PTR.LATITUDE, PTR.LONGITUDE FROM PONTOS_REFERENCIA PTR WHERE PTR.ID = {$body->boardCod};";
        $cons         = $pdoSql->query($sql);    
        $arr['ptref'] = $cons->fetch();

        $daIni = date("Y-m-d") . " 00:00:00";
        $daFim = date("Y-m-d") . " 23:59:59";

        /**
         * Buscar itinerario_trajeto
         */
        $sql    = "SELECT IT.trajeto
                    FROM BD_CLIENTE.dbo.itinerario_trajeto IT
                JOIN ITINERARIOS ITI ON ITI.ID = IT.itinerario_id 
                WHERE ITI.LINHA_ID = {$body->line} AND ITI.ATIVO = 1;";
        $cons   = $pdoSql->query($sql);  

        $trajs = $cons->fetch();

        $allTrajets = [];

        if (isset($trajs['trajeto']))
        {
            $datasTra = json_decode($trajs['trajeto']);

            foreach($datasTra AS $k => $traj)
            {
                $allTrajets[$k]['latitude']  = $traj->lat;
                $allTrajets[$k]['longitude'] = $traj->lng;
            }

        }

        $arr['trajetos'] = $allTrajets;
     
        return $arr;
    }

    public function infosCar($body)
    {
        try {
            $pdoSql = new \PDO ("dblib:host=$this->host:$this->port;dbname=$this->dbName;charset=utf8","$this->user","$this->pass");
        } catch (\Throwable $th) {
            return json_encode(array('status' => false, 'message'=>'Ocorreu um erro ao tentar conectar, tente novamente.'));
        }

        $daIni = date("Y-m-d") . " 00:00:00";
        $daFim = date("Y-m-d") . " 23:59:59";

        $arr   = array();

        /**
         * Buscar Todas lat e long da viagem
         */
        $sql       = "SELECT VEI.PLACA, VEI.NOME, VI.DATAHORA_INICIAL_PREVISTO, VI.DATAHORA_FINAL_PREVISTO, VI.DATAHORA_FINAL_REALIZADO, VI.ID AS IDVIAGEM
                            FROM ITINERARIOS ITI
                        JOIN VIAGENS VI ON VI.ITINERARIO_ID = ITI.ID
                        JOIN VEICULO VEI ON VEI.ID = VI.VEICULO_ID
                        WHERE ITI.LINHA_ID = {$body->line} AND ITI.ATIVO = 1 
                        AND VI.DATAHORA_INICIAL_PREVISTO BETWEEN '{$daIni}' AND '{$daFim}' AND ITI.SENTIDO = 0
                        GROUP BY VEI.PLACA, VEI.NOME, VI.DATAHORA_INICIAL_PREVISTO, VI.DATAHORA_FINAL_PREVISTO, VI.DATAHORA_FINAL_REALIZADO, VI.ID
                        HAVING VEI.PLACA IS NOT NULL";
   
        $cons     = $pdoSql->query($sql);    
        $veicViag = $cons->fetch();

        $arr['viagem'] = $veicViag;
        $veicViag      = (object) $veicViag;

        if ( $veicViag ){

            $ajustHourStart = date("Y-m-d H:i:s", strtotime("+2 hours", strtotime($veicViag->DATAHORA_INICIAL_PREVISTO)));
            $ajustHourEnd   = date("Y-m-d H:i:s", strtotime("+4 hours", strtotime($veicViag->DATAHORA_FINAL_PREVISTO)));
            // $ajustHourStart = date("Y-m-d H:i:s", strtotime($veicViag->DATAHORA_INICIAL_PREVISTO));
            // $ajustHourEnd = date("Y-m-d H:i:s", strtotime($veicViag->DATAHORA_FINAL_PREVISTO));
       
            /**
             * Buscar os dados Locais
             * Se for base teste busca da tabela de produção pois só é alimentada lá
             */
            $ym = date("Y_m");

            $sql = $this->db->prepare("SELECT * FROM piccolotur_rel.positions_{$ym} WHERE placa = '{$veicViag->PLACA}' AND dataHora BETWEEN '{$ajustHourStart}' AND '{$ajustHourEnd}' ORDER BY dataHora DESC LIMIT 1");

            $sql->execute();
            $arr['positions'] = $sql->fetchAll(PDO::FETCH_OBJ);
        }

        if(count($arr['positions']) == 0){

            $tempoRestante = $this->incialTripAdvisorTime($veicViag->DATAHORA_INICIAL_PREVISTO, false);

            if($tempoRestante) {

                $arr['positionsError'] = $tempoRestante;
    
            }else{ 

                $arr['positionsError'] = "Viagem não iniciada ou com problema de comunicação com o veículo.\n\nTente novamente em poucos minutos.\n\nDirija-se ao seu Ponto de Embarque no horário de costume!";

            }

        } else if(count($arr['positions']) > 0){

            if(date("Y-m-d H:i:s") > date("Y-m-d H:i:s", strtotime("+5 minutes", strtotime($arr['positions'][0]->created_at))) && ( !isset($veicViag->DATAHORA_FINAL_REALIZADO) || $veicViag->DATAHORA_FINAL_REALIZADO == null )){

                $arr['positionsError'] = "SEM COMUNICACAO";
    
            } else{

                $tempoRestante = $this->incialTripAdvisorTime($veicViag->DATAHORA_INICIAL_PREVISTO, true);

                if($tempoRestante){

                    $arr['positionsError'] = $tempoRestante;
    
                } else{

                    $getPointsRef = $this->getPointsRef($body->line);

                    $pontoRef = $getPointsRef ? $getPointsRef[0] : false;

                    if($pontoRef){

                        $firstLat = $pontoRef[0]['LATITUDE'];
                        $firstLong = $pontoRef[0]['LONGITUDE'];
                        $busLat = $arr['positions'][0]->latitude;
                        $busLong = $arr['positions'][0]->longitude;

                        $tempoEstimadoKm = $this->incialTripAdvisorKm($firstLat, $firstLong, $busLat, $busLong);

                        if($tempoEstimadoKm){

                            $arr['positionsError'] = $tempoEstimadoKm;

                        }

                    }

                }
            }

        }

        // if ( count($arr['positions']) > 0 && 
        //         date("Y-m-d H:i:s") < date("Y-m-d H:i:s", strtotime('-10 minutes', strtotime($veicViag->DATAHORA_INICIAL_PREVISTO)))) {
        //     $arr['positionsError'] = "Viagem não iniciada!";

        // } else if ( count($arr['positions']) == 0 && $veicViag->DATAHORA_INICIAL_PREVISTO < date("Y-m-d H:i:s")) {

        //     $arr['positionsError'] = "Viagem não iniciada ou com problema de comunicação com o veículo. Tente novamente em poucos minutos. Se persistir, vá para seu ponto no horário de costume!";

        // } else if (count($arr['positions']) > 0 && date("Y-m-d H:i:s") > date("Y-m-d H:i:s", strtotime("+5 minutes", strtotime($arr['positions'][0]->created_at))) && ( !isset($veicViag->DATAHORA_FINAL_REALIZADO) || $veicViag->DATAHORA_FINAL_REALIZADO == null )){

        //     $arr['positionsError'] = "SEM COMUNICACAO";

        // }

        /** Calculando distancias dos pontos */
        $arrPontos = array();
        $arr['arrDots'] = $arrPontos;

        return json_encode($arr);
    }

    private function incialTripAdvisorTime($previsto, $position) {

        $dataFornecida = strtotime($previsto);
        $dataAtual = strtotime(date("Y-m-d H:i:s"));
    
        $diferencaEmSegundos = $dataFornecida - $dataAtual;

        $frase = "Próxima Viagem Não Iniciada!\n\nPREVISTO";

        if(!$position){

            $dataFormatada = date("d/m/Y H:i:s", $dataFornecida);

            $frase = $frase .= " para {$dataFormatada}";
            $frase .= "\n\nDirija-se ao seu Ponto de Embarque no horário de costume!";

            return ($diferencaEmSegundos < 0) ? false : $frase;

        }

        $diferencaEmMinutos = floor($diferencaEmSegundos / 60);
    
        if ($diferencaEmMinutos > 5) {
    
            $diferencaEmHoras = floor($diferencaEmMinutos / 60);

            if ($diferencaEmHoras > 0) {

                $frase .= " para daqui {$diferencaEmHoras} " . ($diferencaEmHoras == 1 ? 'hora' : 'horas');
                $minutosRestantes = $diferencaEmMinutos % 60;
    
                if ($minutosRestantes > 0) {
                    $frase .= " e {$minutosRestantes} " . ($minutosRestantes == 1 ? 'minuto' : 'minutos');
                }

            } elseif ($diferencaEmMinutos > 0) {

                $frase .= " para daqui {$diferencaEmMinutos} " . ($diferencaEmMinutos == 1 ? 'minuto' : 'minutos');
                
            }

            $frase .= "\n\nDirija-se ao seu Ponto de Embarque no horário de costume!";
    
            return $frase;

        } else {

            return false;

        }

    }
    
    private function incialTripAdvisorKm($firstLat, $firstLong, $busLat, $busLong) {

        $R = 6371;
        $limiteDistancia = 5;
        $notArrived = ($busLat <= $firstLat && $busLong <= $firstLong);
        $velocidadeMedia = 80;
    
        $dLat = deg2rad($busLat - $firstLat);
        $dLon = deg2rad($busLong - $firstLong);
    
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($firstLat)) * cos(deg2rad($busLat)) *
             sin($dLon / 2) * sin($dLon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    
        $distance = $R * $c;
    
        $roundedDistance = floor($distance);
    
        if ($roundedDistance > $limiteDistancia && $notArrived) {
    
            $tempoEstimado = $distance / $velocidadeMedia;
            $tempoEstimadoHoras = floor($tempoEstimado);
            $tempoEstimadoMinutos = round(($tempoEstimado - $tempoEstimadoHoras) * 60);
    
            $frase = "Parece que o Fretado ainda não chegou ao primeiro ponto da Viagem.\n\nESTIMADO";
    
            if ($tempoEstimadoHoras > 0) {
                $frase = " para daqui {$tempoEstimadoHoras} " . ($tempoEstimadoHoras == 1 ? 'hora' : 'horas');
    
                if ($tempoEstimadoMinutos > 0) {
                    $frase .= " e {$tempoEstimadoMinutos} " . ($tempoEstimadoMinutos == 1 ? 'minuto' : 'minutos');
                }
            } else if ($tempoEstimadoMinutos > 0) {
                $frase = " para daqui {$tempoEstimadoMinutos} " . ($tempoEstimadoMinutos == 1 ? 'minuto' : 'minutos');
            }

            $frase .= "\n\nDirija-se ao seu Ponto de Embarque no horário de costume!";
    
            return $frase;
    
        } else {
    
            return false;
    
        }
    }    

    public function saveAllowTermsDevice($qrCode, $uniqueID)
    {
        $sql = $this->db->prepare("INSERT INTO allowdevices (code, deviceID, dateallow) VALUES ('$qrCode', '$uniqueID', NOW());");
        $sql->execute();

        return true;
    }
    
    public function travelReview($body)
    {
        /**
         * Tratando os dados
         * Avaliação de 1 a 5 Estrelas e OBS
         */
        $grupoID  = $body->groupID ?? 0;
        $placacar = $body->infoCar ?? 0;
        $travelID = $body->travelID ?? 0;
        $lineID   = $body->lineID ?? 0;
        $deviceID = $body->uniqueId ?? "";
        $obs      = $body->obsPax ? addslashes($body->obsPax) : "";

        // Onibus
        $cleanBus  = $body->myAssess->cleaningCar ?? 1;
        $conservationCar = $body->myAssess->conservationCar ?? 1;

        //Viagem
        $punctuality = $body->myAssess->punctuality ?? 1;

        // Motorista
        $cordiality = $body->myAssess->cordiality ?? 1; // Cordialidade
        $howDrive = $body->myAssess->howDrive ?? 1; // Como o motorista dirigiu

        $sql = $this->db->prepare("INSERT INTO travelReview (grupoID, travelID, lineID, placacar, deviceID, cleanBus, conservationCar, punctuality, cordiality,  howDrive, obs, created_at) VALUES ($grupoID, $travelID, $lineID, '$placacar', '$deviceID', '$cleanBus','$conservationCar','$punctuality','$cordiality','$howDrive', '$obs', NOW());");
        
        $arr = array('status' => true, 'message'=> 'Avaliação Registrada com sucesso');

        try {
            $sql->execute();
        } catch (\Throwable $th) {
            $arr = array('status' => false, 'message'=> 'Ocorreu um erro ao salvar a avaliação, tente novamente em poucos minutos', 'err'=> $th->getMessage());
        }

        return json_encode($arr);
    }

    public function pontualityLine($body)
    {


        try {
            $pdoSql = new \PDO ("dblib:host=$this->host:$this->port;dbname=$this->dbName;charset=utf8","$this->user","$this->pass");
        } catch (\Throwable $th) {
            return json_encode(array('status' => false, 'message'=>'Ocorreu um erro ao tentar conectar, tente novamente.'));
        }

        $daIni = date("Y-m-d") . " 00:00:00";
        $daFim = date("Y-m-d") . " 23:59:59";
        
        $sql = "SELECT VI.DATAHORA_FINAL_REALIZADO, VI.ID AS IDVIAGEM
                    FROM ITINERARIOS ITI
                JOIN VIAGENS VI ON VI.ITINERARIO_ID = ITI.ID
                WHERE ITI.LINHA_ID = {$body->line} AND ITI.ATIVO = 1 
                AND VI.DATAHORA_INICIAL_PREVISTO BETWEEN '{$daIni}' AND '{$daFim}' AND ITI.SENTIDO = 0
                GROUP BY VI.DATAHORA_FINAL_REALIZADO, VI.ID";

        $cons     = $pdoSql->query($sql);  
        $veicViag = $cons->fetch(); 
   
        $punctuality = '';
        $status      = false;
        $end         = false;

        if ( !$veicViag['DATAHORA_FINAL_REALIZADO'] )
        {
            $viagemID = $veicViag['IDVIAGEM'];

            $q = "SELECT TOP 1 PTI.PONTO_REFERENCIA_ID, HV.*
                    FROM BD_CLIENTE.dbo.HORARIOS_VIAGEM HV
                JOIN PONTOS_ITINERARIO PTI ON PTI.ID = HV.PONTO_ITINERARIO_ID 
                WHERE VIAGEM_ID = {$viagemID}
                AND TIPO_EXECUCAO = 1 AND DATAHORA_ENTRADA_REALIZADO IS NOT NULL 
                ORDER BY PTI.SEQUENCIA DESC;";

            $cons = $pdoSql->query($q);  
            $hasInfo = $cons->fetch(); 
        
            if ($hasInfo){

                $status      = true;
                $punctuality = 'Pontual';

                if ( ($hasInfo['DATAHORA_ENTRADA_REALIZADO'] && strtotime($hasInfo['DATAHORA_ENTRADA_PREVISTO']) < strtotime($hasInfo['DATAHORA_ENTRADA_REALIZADO'])) || strtotime($hasInfo['DATAHORA_ENTRADA_PREVISTO']) < strtotime(date('Y-m-d H:i:s')) ){

                    $punctuality = 'Em Atraso';

                } 

            }
      
        } else if( $veicViag['DATAHORA_FINAL_REALIZADO'] ) {

            $end = true;

        }
   
        return json_encode( array( "status" => $status, "punctuality" => $punctuality, "end" => $end ) );
    }

    public function saveReturnMessage($body, $dbSys)
    {
        $messageId  = $body['messageId'];
        $title      = $body['title'];
        $bdMessa    = $body['body'];
        $topic      = $body['topic'];
        $line       = $body['line'];
        $dot        = $body['dot'];
        $user       = $body['user'];
        $timeSend   = $body['timeSend'];
        $result     = $body['return'];

        $now = date("Y-m-d H:i:s");

        $q = "INSERT INTO return_message (messageId, title, bodyMessage, topic, lineMessage, dot, userSend, timeSend, result, created_at) VALUES  ('$messageId', '$title', '$bdMessa', '$topic', '$line', '$dot', '$user', '$timeSend', '$result', '$now');";

        if ($dbSys){
            $sql = $dbSys->prepare($q);
        } else {
            $sql = $this->db->prepare($q);
        }
        
        try {
            $sql->execute();
        } catch (\Throwable $th) {
           
        }

        return true;
    }

    public function alreadySentMessage($topic, $line, $dot, $timeSend, $pdoMessage)
    {
        $start = date("Y-m-d") . ' 00:00:00';
        $end = date("Y-m-d") . ' 23:59:59';

        $w = ($timeSend == 0) ? " (timeSend = $timeSend OR timeSend != $timeSend)" : " timeSend = $timeSend";

        $q = "SELECT * FROM return_message WHERE TRIM(topic) = TRIM('{$topic}') AND TRIM(lineMessage) = TRIM('{$line}') AND TRIM(dot) = TRIM('{$dot}') AND created_at BETWEEN '{$start}' AND '{$end}' AND userSend = 0 AND {$w}";

        if ($pdoMessage){
            $sql = $pdoMessage->prepare($q);
        } else {
            $sql = $this->db->prepare($q);
        }
       
        $sql->execute();
        $has = $sql->fetch(PDO::FETCH_OBJ);

        if ($has)
            return true; 

        return false;
    }

    /**
     * REPORT
     */
    public function reportRate($req)
    {
        $w = "";

        if(isset($req->lines) && $req->lines > 0){
            $w .= " AND li.ID_ORIGIN = {$req->lines}";
        }

        if(isset($req->veiculo) && $req->veiculo > 0){
            $w .= " AND vei.ID_ORIGIN = {$req->veiculo}";
        }

        if (isset($_SESSION['cType']) && $_SESSION['cType'] == 2){
            $w .= " AND gl.id = " . $_SESSION['groupUserID'];
        }

        $q = "SELECT gl.NOME AS grupo, 
                    li.PREFIXO, 
                    li.NOME AS LINHA,
                    vei.MARCA, 
                    vei.MODELO, 
                    vei.NOME, 
                    vei.PLACA, 
                    tr.obs AS obs, 
                    tr.travelID, 
                    DATE_FORMAT(tr.created_at, '%d/%m/%Y') AS dataAvaliacao, 
                    tr.cleanBus AS limpeza, 
                    tr.conservationCar AS conservacao, 
                    tr.punctuality AS pontual, 
                    tr.cordiality AS cordial, 
                    tr.howDrive AS direcao
                FROM travelReview AS tr
                LEFT JOIN veiculos AS vei ON vei.PLACA = tr.placacar
                LEFT JOIN linhas AS li ON li.ID_ORIGIN = tr.lineID
                LEFT JOIN grupo_linhas AS gl ON gl.ID_ORIGIN = tr.grupoID
                WHERE tr.created_at BETWEEN '{$req->data_inicio} 00:00:00' AND '{$req->data_fim} 23:59:59' $w";

        $sql = $this->db->prepare($q);
        $sql->execute();

        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * EMBARQUE QR
     */
    public function checarAcessoQr($post)
	{
        $q = "SELECT * FROM embarque_qr_code
        WHERE device_id = '{$post->device_id}' AND
              group_id = '{$post->group_id}' AND
              veiculo_id = '{$post->veiculo_id}' AND
              integrado = 0 AND
              created_at BETWEEN DATE_SUB(NOW(), INTERVAL 30 second) AND NOW() AND
              deleted_at IS NULL LIMIT 1";

		$sql = $this->db->prepare($q);
        
        try {
            $sql->execute();
            $encontrado = $sql->fetchColumn();
            
            if($encontrado == 0){
                return $this->storeQr($post);
            }else{
                return $this->updateQr($encontrado);
            }

        } catch (\Throwable $th) {
            return json_encode( array( "status" => false, "message" => 'Erro ao checar acesso!'));
        }
	}

    public function storeQr($data)
	{
        $veiculo_id = $data->veiculo_id;
        $group_id = $data->group_id;
        $device_id = $data->device_id;
        $sql = $this->db->prepare("INSERT INTO embarque_qr_code (veiculo_id, group_id, device_id, primeira_leitura, created_at) VALUES ('$veiculo_id', '$group_id', '$device_id', NOW(), NOW());");
        
        try {
            $sql->execute();
            return json_encode( array( "status" => true, "message" => 'Primeira Leitura cadastrada'));
        } catch (\Throwable $th) {
            return json_encode( array( "status" => false, "message" => 'Erro ao cadastrar primeira leitura!'));
        }
    }

    public function updateQr($id)
    {
        $sql = $this->db->prepare("UPDATE embarque_qr_code SET segunda_leitura = NOW(), updated_at = NOW() WHERE id = {$id} AND updated_at IS NULL");
        
        try {
            $sql->execute();
            if($sql->rowCount()){
                return json_encode( array( "status" => true, "message" => 'Segunda Leitura cadastrada', "id" => $id));
            }else{
                return json_encode( array( "status" => false, "message" => 'Segunda Leitura já foi cadastrada anteriormente!'));
            }
            
        } catch (\Throwable $th) {
            return json_encode( array( "status" => false, "message" => 'Erro ao cadastrar segunda leitura!'));
        }
    }


    /**
     * EMBARQUE SEM RFID
     */

    //CHECAR A MATRICULA PARA LIBERAR EMBARQUE SEM RFID
    public function checarMatriculaSemRFId($body)
	{
            $w = " AND TRIM(LEADING '0' FROM MATRICULA_FUNCIONAL) = TRIM(LEADING '0' FROM '{$body->register}')";
            
            $groupAccess = $this->db->prepare("SELECT groupAccess FROM app_links WHERE cliente_id = '{$body->groupID}'");

            $groupAccess->execute();

            $groupAccess = $groupAccess->fetch(PDO::FETCH_OBJ);

            $groupAccess = $groupAccess ? $groupAccess->groupAccess : 0;

            $sql = $this->db->prepare("SELECT * FROM controle_acessos 
            WHERE ATIVO = 1 {$w} AND CONTROLE_ACESSO_GRUPO_ID IN ($groupAccess)");
            
            try {
                $sql->execute();
                $pax = $sql->fetch(PDO::FETCH_OBJ);
                
                if($pax){
                    return json_encode( array( "status" => true, "message" => 'Matrícula Encontrada!'));
                }else{
                    return json_encode( array( "status" => false, "message" => 'Matrícula Inválida!'));
                }
    
            } catch (\Throwable $th) {
                return json_encode( array( "status" => false, "message" => 'Erro ao checar matricula!'));
            }
    }

    
    //CHECAR SE JÁ REALIZOU O EMBARQUE/DESEMBARQUE E SE A VIAGEM NÃO ESTÁ FINALIZADA
    public function checarEmbarqueSemRFId($body)
	{

        //CHECAR SE É EMBARQUE OU DESEMBARQUE
        $embarque = $body->id_embarque == 0 ? true : false;

        try {
            $pdoSql = new \PDO ("dblib:host=$this->host:$this->port;dbname=$this->dbName;charset=utf8","$this->user","$this->pass");
        } catch (\Throwable $th) {
            return json_encode(array('status' => false, 'message'=>'Ocorreu um erro ao tentar conectar, tente novamente.'));
        }

        //ENCONTRA A VIAGEM NA VELTRAC
        $daIni = date("Y-m-d") . " 00:00:00";
        $daFim = date("Y-m-d") . " 23:59:59";
        
        $sql = "SELECT VI.DATAHORA_FINAL_REALIZADO, VI.ID AS IDVIAGEM
                    FROM ITINERARIOS ITI
                JOIN VIAGENS VI ON VI.ITINERARIO_ID = ITI.ID
                WHERE ITI.LINHA_ID = {$body->linha_id} AND ITI.ATIVO = 1 
                AND VI.DATAHORA_INICIAL_PREVISTO BETWEEN '{$daIni}' AND '{$daFim}' AND ITI.SENTIDO = {$body->sentido}
                GROUP BY VI.DATAHORA_FINAL_REALIZADO, VI.ID";

        $cons     = $pdoSql->query($sql);  
        $veicViag = $cons->fetch();
        $viagemID = $veicViag['IDVIAGEM'];
        
        //VERIFICA SE A VIAGEM NÃO FOI FINALIZADA QUANDO É EMBARQUE
        //ESTA COMO SE NÃO ESTIVER FINALIZADO PARA TESTE, LEMBRAR DE REMOVER O ! NA VERIFICAÇÃO
        if ( $embarque && $veicViag['DATAHORA_FINAL_REALIZADO'] )
        {
            return json_encode( array( "status" => false, "message" => 'Viagem finalizada!'));

        }else{
            //CASO SEJA DESEMBARQUE OU EMBARQUE E A VIAGEM NÃO ESTEJA FINALIZADA PROSSEGUE
            //SALVA O ID DA VIAGEM
            $body->viagem_id = $veicViag['IDVIAGEM'];

            //VERIFICA SE TEM LAT E LONG - SE NÃO TIVER PEGA NA VELTRAC DE ACORDO COM O PONTO SELECIONADO
            if($body->latitude == 0 && $body->longitude == 0){
                $ponto = $embarque ? $body->ponto_id : $body->ultimo_ponto_id;

                $q = "SELECT PTR.LATITUDE, PTR.LONGITUDE FROM PONTOS_REFERENCIA PTR WHERE PTR.ID = {$ponto};";
    
                $cons = $pdoSql->query($q);  
                $getLatLong = $cons->fetch(); 
                
                if ($getLatLong){
                    $body->latitude  = $getLatLong['LATITUDE'];
                    $body->longitude = $getLatLong['LONGITUDE'];
                }
            }

            //CHECA SE JÁ FOI REALIZADO O EMBARQUE/DESEMBARQUE
            //SETA NA VAR $tipo 0 PARA EMBARQUE E 1 PARA DESEMBARQUE
            $body->tipo = $embarque ? 0 : 1;
            
            $w = "";

            //SE FOR EMBARQUE VERFICA PELO TEMPO
            if($embarque){
                $w .= " AND created_at BETWEEN DATE_SUB(NOW(), INTERVAL 10 MINUTE) AND NOW() ";
            }
            $sentido = $body->sentido == 0 ? 'I' : 'V';
            $register = trim($body->register);

            $q = "SELECT * FROM embarque_sem_RFID
            WHERE matricula = '{$register}' AND
                id_embarque = '{$body->id_embarque}' AND 
                tipo = '{$body->tipo}' AND
                sentido = '{$sentido}' AND
                veiculo_id = '{$body->veiculo_id}' AND
                group_id = '{$body->groupID}' AND
                viagem_id = '{$body->viagem_id}' AND
                linha_id = '{$body->linha_id}' AND
                ponto_id = '{$body->ponto_id}' AND
                device_id = '{$body->device_id}'
                {$w}
                AND deleted_at IS NULL LIMIT 1";

            $sql = $this->db->prepare($q);
            
            try {
                $sql->execute();
                $encontrado = $sql->fetchColumn();
                
                //SE NÃO REALIZOU EMBARQUE/DESEMBARQUE, REALIZA
                if($encontrado == 0){
                    return $this->storeEmbarqueSemRFId($body, $embarque);
                }else{
                    $message = $embarque ? 'Já realizou o Embarque por QRCODE!' : 'Já realizou o Desembarque por QRCODE!';
                    return json_encode( array( "status" => false, "message" => $message));
                }

            } catch (\Throwable $th) {
                $message = $embarque ? 'Erro ao checar Embarque!' : 'Erro ao checar Desembarque!';
                return json_encode( array( "status" => false, "message" => $message));
            }
        }
	}

    //SALVAR O EMBARQUE
    public function storeEmbarqueSemRFId($data, $embarque)
	{

        $now = date("Y-m-d H:i:s");
        
        $idEmbarque = $this->gerarIdEmbarque();

        $id_embarque = $embarque ? $idEmbarque : $data->id_embarque;
        $id_embarque = trim($id_embarque);
        $tipo = $data->tipo;
        $sentido = $data->sentido == 0 ? 'I' : 'V';
        $matricula = trim($data->register);
        $veiculo_id = $data->veiculo_id;
        $group_id = trim($data->groupID);
        $viagem_id = $data->viagem_id;
        $linha_id = $data->linha_id;
        $ponto_id = $data->ponto_id;
        $device_id = $data->device_id;
        $latitude = $data->latitude;
        $longitude = $data->longitude;
        $nome = $data->name;
        $motivo = $data->motivo;

        $sql = $this->db->prepare("INSERT INTO embarque_sem_RFID (id_embarque, tipo, sentido, matricula, nome, veiculo_id, group_id, viagem_id, linha_id, ponto_id, device_id, motivo, data_hora, latitude, longitude, created_at) VALUES ('$id_embarque', '$tipo', '$sentido', '$matricula', '$nome', '$veiculo_id', '$group_id', '$viagem_id', '$linha_id', '$ponto_id', '$device_id', '$motivo', '$now', '$latitude', '$longitude', '$now');");
        
        try {
            $sql->execute();
            $message = $embarque ? 'Embarque por QRCODE realizado com sucesso!' : 'Desembarque por QRCODE realizado com sucesso!';
            $id_embarque = $embarque ? $id_embarque : 0;
            $id_embarque = trim($id_embarque);
            return json_encode( array( "status" => true, "id_embarque" => $id_embarque, "message" => $message));
        } catch (\Throwable $th) {
            $message = $embarque ? 'Erro ao realizar Embarque por QRCODE!' : 'Erro ao realizar Desembarque por QRCODE!';
            return json_encode( array( "status" => false, "message" => $message));
        }
    }


    public function gerarIdEmbarque()
    {
        $idEmbarque = strval(rand(100000, 999999));
        $sql = $this->db->prepare("SELECT * FROM embarque_sem_RFID WHERE id_embarque = '{$idEmbarque}' AND deleted_at IS NULL");
        $sql->execute();

        if($sql->rowCount()>0) {
            $try = true;
            while($try){
                $idEmbarque = strval(rand(100000, 999999));
                $sql = $this->db->prepare("SELECT * FROM embarque_sem_RFID WHERE id_embarque = '{$idEmbarque}' AND deleted_at IS NULL");
                $sql->execute();
                if($sql->rowCount()==0) $try = false;
            }   
        }
        return $idEmbarque;
    }

    /**
     * Login no APP
     */
    public function signIn($body)
    {
        $pass = md5($body->pass);

        $q = "SELECT * FROM controle_acessos WHERE email = '{$body->email}' AND `password` = '{$pass}' AND deleted_at IS NULL AND ATIVO = 1";
        $sql = $this->db->prepare($q);
        $sql->execute();
        $user = $sql->fetch(PDO::FETCH_OBJ);

        if (!$user)
            return array('status' => false, 'message' => 'Cadastro não encontrato. Entre em contato com seu Supervisor!');

        return array( 'status' => true, 'pax' => $user );
    }

     /**
     * Para verificar se a TAG (Código do Cartão) existe
     */
    public function checkTag($body)
    {

        $q = "SELECT * FROM controle_acessos WHERE TAG = '{$body->tag}' AND deleted_at IS NULL AND ATIVO = 1";
        $sql = $this->db->prepare($q);
        $sql->execute();
        $user = $sql->fetch(PDO::FETCH_OBJ);

        if (!$user)
            return array('status' => false, 'message' => 'Tag não encontrada.');

        return array( 'status' => true, 'pax' => $user );
    }

    /**
     * Para novos cadastros
     */
    public function register($body)
    {
        /**
         * Verifica se não existem o cadastro
         */
        $w = "";

        // if (isset($body->register))
        //     $w = " AND TRIM(LEADING '0' FROM MATRICULA_FUNCIONAL) = TRIM(LEADING '0' FROM '{$body->register}')";
        
        // if (isset($body->name)){
        //     $nameExp = explode(" ", $body->name);
        //     $nameExpN = $nameExp[0].(isset($nameExp[1]) ? ' '.$nameExp[1] : '');
        //     $w .= " AND NOME LIKE '{$nameExpN}%'";
        // }
            
        
        $w .= " AND TRIM(LEADING '0' FROM TAG) = TRIM(LEADING '0' FROM '{$body->tag}')";

        $sql = $this->db->prepare("SELECT * FROM controle_acessos WHERE ATIVO = 1 {$w}");

        $sql->execute();
        $pax = $sql->fetch(PDO::FETCH_OBJ);
        
        $groupDefault = 0;

        $sqlGroupDefault = $this->db->prepare("SELECT * FROM app_links WHERE cliente_id = {$body->groupID}");
        $sqlGroupDefault->execute();
        $sqlGroupDefault = $sqlGroupDefault->fetch(PDO::FETCH_OBJ);
        if($sqlGroupDefault){
            $groupDefault = $sqlGroupDefault->groupDefault;
        }

        /**
         * retorna o cadastro existe
         */
        
        if ( $pax ) {
            //preparando para atualizar 
            $name             = $pax->NOME;
            $register         = $pax->MATRICULA_FUNCIONAL == '' ? $body->register : $pax->MATRICULA_FUNCIONAL;
            $groupID          = $body->groupID;
            $numCard          = $pax->TAG == '' ? $body->tag : $pax->TAG;
            $email            = $body->email ?? "";
            $cellphone        = $body->cellphone ?? "";
            $password         = md5($body->password);
            $embarqueQrcodeId = $body->embarqueQrcodeId ?? 0;

            //tenta atualizar
            $updatePax = $this->db->prepare("UPDATE controle_acessos SET
            NOME = '$name',
            CONTROLE_ACESSO_GRUPO_ID = $groupDefault,
            MATRICULA_FUNCIONAL = '$register',
            ID_UNICO = $groupID,
            TAG = '$numCard',
            email = '$email',
            cellphone = '$cellphone',
            `password` = '$password',
            embarqueQrcodeId = $embarqueQrcodeId,
            updated_at = NOW() 
            WHERE id = {$pax->id}");
            
            try {
                $updatePax->execute();
                return json_encode( array("status" => true, "message" => "Passageiro encontrato e atualizado", "pax" => $pax) );
    
            } catch (\Throwable $th) {
                return json_encode( array("status" => false, "message" => 'Cadastro não encontrato. Entre em contato com seu Supervisor!'));
            }
        }
           
        /**
         * Se não tiver cadastro | Cadastrar novo
         */

        $name             = $body->name;
        $register         = $body->register ?? "";
        $groupID          = $body->groupID;
        $numCard          = $body->tag ?? "";
        $email            = $body->email ?? "";
        $cellphone        = $body->cellphone ?? "";
        $password         = md5($body->password);
        $embarqueQrcodeId = $body->embarqueQrcodeId ?? 0;

        //antes de cadastrar checa se tem algum passageiro 
        //com TAG igual e nome diferente para desativar
        $checkUser = $this->db->prepare("UPDATE controle_acessos SET
        ATIVO = 0,
        updated_at = NOW() 
        WHERE TAG = $numCard AND NOME != $name LIMIT 1");
        $checkUser->execute();

        $q = "INSERT INTO controle_acessos (ID_ORIGIN, NOME, CONTROLE_ACESSO_GRUPO_ID, MATRICULA_FUNCIONAL, ID_UNICO, ATIVO, TAG, created_at, unidadeID, email, cellphone, `password`, `embarqueQrcodeId`) VALUES (0, '{$name}', {$groupDefault}, '{$register}', {$groupID}, 1, '{$numCard}', NOW(), {$groupID}, '{$email}', '{$cellphone}', '{$password}', {$embarqueQrcodeId})";
        $sql = $this->db->prepare($q);
        $sql->execute();

        $sql = $this->db->prepare("SELECT * FROM controle_acessos order by id desc LIMIT 1");
        $sql->execute();
        $pax = $sql->fetch(PDO::FETCH_OBJ);
        
        return json_encode( array("status" => true, "message" => "Passageiro Cadastrado.", "pax" => $pax) );
    }

    //atualizar linha e ponto dos passageiros
    public function saveDotAndLineUser($body)
    {
        $idPax = $body->userID;
        $lineID = $body->lineID;
        $dotID = $body->dotID;

        //tentar pegar o ID_ORIGIN em itinerarios
        $sql = $this->db->prepare("SELECT ID_ORIGIN FROM itinerarios WHERE ATIVO = 1 AND LINHA_ID = {$lineID}");
        $sql->execute();
        $ITINERARIO_ID_IDA = $sql->fetch(PDO::FETCH_OBJ);

        //se encontrar
        if($ITINERARIO_ID_IDA){

            //tenta atualizar o ITINERARIO_ID_IDA em controle_acessos com o ID_ORIGIN do itinerario
            $updatePax = $this->db->prepare("UPDATE controle_acessos SET
            ITINERARIO_ID_IDA = $ITINERARIO_ID_IDA->ID_ORIGIN,
            updated_at = NOW() 
            WHERE id = {$idPax}");

            try {
                $updatePax->execute();

                //verificar se existe ponto do usuário
                $checkPonto = $this->db->prepare("SELECT id FROM pontos_controle_acesso WHERE controle_acesso_id = {$idPax}");
                $checkPonto->execute();
                $checkPonto = $checkPonto->fetch(PDO::FETCH_OBJ);
                // se já existe atualiza
                if ( $checkPonto ) {
                    $updatePaxPonto = $this->db->prepare("UPDATE pontos_controle_acesso SET
                    ponto_referencia_id_embarque = $dotID,
                    updated_at = NOW() 
                    WHERE id = {$checkPonto->id}");

                    try {
                        $updatePaxPonto->execute();
                        return json_encode( array("status" => true, "message" => "Ponto encontrado e atualizado") );

                    } catch (\Throwable $th) {
                        return json_encode( array("status" => false, "message" => 'Erro ao atualizar ponto'));
                    }

                    }else{
                        //se não tem tenta adicionar
                        $createPontoCa = $this->db->prepare("INSERT INTO pontos_controle_acesso (gerar_alerta, controle_acesso_id, ponto_referencia_id_embarque, ponto_referencia_id_desembarque, ponto_referencia_id_resid_embar, ponto_referencia_id_resid_desem, created_at) VALUES (:gerar_alerta, :controle_acesso_id,:ponto_referencia_id_embarque,:ponto_referencia_id_desembarque,:ponto_referencia_id_resid_embar,:ponto_referencia_id_resid_desem, NOW())");
                        $createPontoCa->bindValue(":gerar_alerta", 0);
                        $createPontoCa->bindValue(":controle_acesso_id", $idPax);
                        $createPontoCa->bindValue(":ponto_referencia_id_embarque", $dotID);
                        $createPontoCa->bindValue(":ponto_referencia_id_desembarque", 0);
                        $createPontoCa->bindValue(":ponto_referencia_id_resid_embar", 0);
                        $createPontoCa->bindValue(":ponto_referencia_id_resid_desem", 0);

                        try {
                            $createPontoCa->execute();
                            return json_encode( array("status" => true, "message" => "Controle ponto de acesso criado.") );
                
                        } catch (\Throwable $th) {
                            return json_encode( array("status" => false, "message" => 'Erro ao criar Controle ponto de acesso!'));
                        }
                    }
            
                } catch (\Throwable $th) {
                return json_encode( array("status" => false, "message" => 'Erro ao atualizar passageiro.'));
            }
        }else{
            return json_encode( array("status" => false, "message" => 'Itinerário não encontrado.'));
        }
    }

    public function getInfosApp($body)
    {

        $q = "SELECT * FROM parametros WHERE id = 1";
        $sql = $this->db->prepare($q);
        $sql->execute();
        $parameters = $sql->fetch(PDO::FETCH_OBJ);
        
        $notification = $parameters->message_app; 

        if ($body->platform == 'android') {
            $versionStores = $parameters->version_android;
            $url = $parameters->url_android; 
        } else {
            $versionStores = $parameters->version_ios;
            $url = $parameters->url_ios; 
        }
 
        $readCardApp = $parameters->readCardApp;

        return array( 'status' => true, 'notification' => $notification, 'versionStores' => $versionStores, 'url' => $url, 'readCardApp' => $readCardApp );
    }

    public function getTimeToMyPoint($body)
    {

        try {
            $pdoSql = new \PDO ("dblib:host=$this->host:$this->port;dbname=$this->dbName;charset=utf8","$this->user","$this->pass");
        } catch (\Throwable $th) {
            return array('status' => false, 'message'=>'Ocorreu um erro ao tentar conectar, tente novamente.', "error" => true);
        }


        $sql = "SELECT PR.ID, HV.DATAHORA_ENTRADA_PREVISTO, HV.DATAHORA_SAIDA_PREVISTO, HV.DATAHORA_SAIDA_REALIZADO, HV.PONTO_ITINERARIO_ID
                FROM PONTOS_REFERENCIA PR 
                JOIN PONTOS_ITINERARIO PTI ON PTI.PONTO_REFERENCIA_ID = PR.ID 
                JOIN HORARIOS_VIAGEM HV ON HV.PONTO_ITINERARIO_ID = PTI.ID 
                WHERE HV.VIAGEM_ID = {$body->tripID} AND PR.CATEGORIA_ID IN (3, 4, 5)
                ORDER BY HV.PONTO_ITINERARIO_ID";

        $consulta = $pdoSql->query($sql);   
        $consulta = $consulta->fetchAll(); 

        $lastReal = "";
        $lastPrev = "";
        $time     = 0;
        $auxTime  = 0;
        $passed   = false;

        foreach($consulta as $cons) {

            if($cons['ID'] == $body->pointID && $cons['DATAHORA_SAIDA_REALIZADO'] != null) {
                $time    = 0;
                $passed  = true;
                break;
            }
            
            // Se não tiver tempo previsto ainda
            if($lastPrev == "") {
                $lastPrev = $cons['DATAHORA_SAIDA_PREVISTO'] ?? $cons['DATAHORA_ENTRADA_PREVISTO'];
                $lastReal = $cons['DATAHORA_SAIDA_REALIZADO'] ?? "";
                continue;
            }

            $currentPrev = $cons['DATAHORA_SAIDA_PREVISTO'] ?? $cons['DATAHORA_ENTRADA_PREVISTO'];
            $diffPrev    = intval( (strtotime($currentPrev) - strtotime($lastPrev)) / 60 );

            if ($diffPrev > 0){
                $auxTime += $diffPrev;
                $lastPrev = $currentPrev;
            }

            if($cons['DATAHORA_SAIDA_REALIZADO'] != null) {
                $lastReal = $cons['DATAHORA_SAIDA_REALIZADO'];
                $auxTime  = 0;
                $time     = 0;
                continue;
            }

            // Calcula minutos
            // Se for o mesmo ponto para o loop
            if($cons['ID'] == $body->pointID) {
                $now             = date("Y-m-d H:i:s");
                $hourPrevistReal = date("Y-m-d H:i:s", strtotime("+ $auxTime minutes", strtotime($lastReal)));
                $time            = $hourPrevistReal > $now ? intval( (strtotime($hourPrevistReal) - strtotime($now)) / 60 ) : 0;
                break;
            }

        }

        return array('status' => true, 'time' => $time, 'passed'=> $passed);
    }

    public function existEmail($email) 
    {

        $checkEmail = $this->db->prepare("SELECT id FROM controle_acessos WHERE email = '{$email}' AND ATIVO = 1");
        $checkEmail->execute();
        $hasRegister = $checkEmail->fetch(PDO::FETCH_OBJ);

        if ($hasRegister)
            return true; 

        return false;
    }

    public function saveTokenEmail($email, $token) 
    {

        $sql = $this->db->prepare("UPDATE controle_acessos SET token = :token, validToken = :validToken, updated_at = NOW() where email = :email AND ATIVO = 1");
		$sql->bindValue(":token", $token);
        $sql->bindValue(":email", $email);
        $sql->bindValue(":validToken", date("Y-m-d H:i:s", strtotime('+ 2 hours')));
		$sql->execute();

        return true;
    }

    public function hasTokenValid($email, $token) 
    {
        $now = date("Y-m-d H:i:s"); // Não filtrou com o NOW() do SQL

        $checkToken = $this->db->prepare("SELECT token FROM controle_acessos WHERE email = '{$email}' AND token = '{$token}' AND validToken > '$now' AND ATIVO = 1");
        $checkToken->execute();
        $hasRegister = $checkToken->fetch(PDO::FETCH_OBJ);

        if ($hasRegister)
            return true; 

        return false;
    }

    public function saveNewPasswordByEmail($email, $password)
    {

        $sql = $this->db->prepare("UPDATE controle_acessos SET `password` = :password, updated_at = NOW() where email = :email AND ATIVO = 1");
		$sql->bindValue(":password", md5($password));
        $sql->bindValue(":email", $email);
		$sql->execute();


        if(!$sql){
            return false;
        }

        return true;

    }

    public function checkAtivo($email, $tag){

        $w = " AND TRIM(LEADING '0' FROM TAG) = TRIM(LEADING '0' FROM '{$tag}')";

        $checkActive = $this->db->prepare("SELECT id FROM controle_acessos WHERE email = '{$email}' AND ATIVO = 1 {$w}");
        $checkActive->execute();
        $isActive = $checkActive->fetch(PDO::FETCH_OBJ);

        if ($isActive)
            return true; 

        return false;

    }

    public function saveAppUseByDay($device, $group)
    {

        $now = date("Y-m-d");

        $sql = $this->db->prepare("INSERT INTO appUseByDay (deviceID, groupID, dateUse) VALUES ('$device', '$group', '$now')");
        
        try {
            $sql->execute();
            return json_encode( array( "status" => true, "message" => 'Aparelho '.$device.' contabilizado uso para o grupo '.$group.' no dia '.$now.''));
        } catch (\Throwable $th) {
            return json_encode( array( "status" => false, "message" => 'Erro ao contabilizar uso do aparelho!'));
        }

    }

    public function getTopics($deviceToken){

        try{

            $notify = new TalentumNotification;

            $getTopics = $notify->getTopics($deviceToken);

            return $getTopics;

        }catch (\Throwable $th) {
            return(['status' => false, 'msg' => "Erro ao carregar os tópicos"]);
        }

    }

    public function saveSubscribeStatus($req)
    {

        try{

            $toSave     = $req->toSave;
            $groupID    = $req->groupID;
            $email      = $req->email;
            $tag        = $req->tag;
            $device_id  = $req->device_id;
            $type       = $req->type;
            $os         = $req->os;

            if(isset($toSave) && count($toSave) > 0){

                $now = date("Y-m-d H:i:s");

                foreach($toSave as $tS){

                    $sql = $this->db->prepare("INSERT INTO topics_controller (topic, msg, groupID, email, tag, device_id, os, type, authorizationStatus, status, motive, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    
                    try{

                        $sql->execute([$tS->topic, $tS->msg, $groupID, $email, $tag, $device_id, $os, $type, $tS->authorizationStatus, $tS->status, $tS->motive, $now]);

                    }catch (\Throwable $th) {
                        
                    }
                    
                }

                return(['status' => true, 'msg' => "Dados salvos com sucesso"]);

            }else{
                return(['status' => true, 'msg' => "Sem dados para salvar"]);
            }

        }catch (\Throwable $th) {
            return(['status' => false, 'msg' => "Erro ao salvar dados"]);
        }

    }

    //funções exclusivas do facial

    public function getInfosAppFace()
    {

        $q = "SELECT * FROM parametros WHERE id = 1";
        $sql = $this->db->prepare($q);
        $sql->execute();
        $parameters = $sql->fetch(PDO::FETCH_OBJ);
        
        $notification = $parameters->message_app_face; 
        $versionStores = $parameters->version_android_face;
        $url = $parameters->url_android_face; 

        return array( 'status' => true, 'notification' => $notification, 'versionStores' => $versionStores, 'url' => $url );
    }

    public function findCarFace($veiculo_id, $device_id)
    {

        $retorno = array('status' => false, 'message' => 'Erro ao encontrar o carro');

        try {
            
            $getDeviceVeicNot = $this->db->prepare("SELECT * FROM face_veiculo WHERE device_id <> :device_id AND veiculo_id = :veiculo_id LIMIT 1");
            $getDeviceVeicNot->bindParam(':device_id', $device_id);
            $getDeviceVeicNot->bindParam(':veiculo_id', $veiculo_id);
            $getDeviceVeicNot->execute();

            if($getDeviceVeicNot->rowCount() == 1) {

                return array('status' => false, 'message' => "Veículo já está em uso por outro aparelho.");

            }

        } catch (\Throwable $th) {
            return array('status' => false, 'message' => "Erro ao checar se o veículo está disponível.");
        }

        $sql = $this->db->prepare("SELECT NOME, PLACA FROM veiculos WHERE ID_ORIGIN = '{$veiculo_id}' AND ATIVO = 1");

        try{

            $sql->execute();
            $ret = $sql->fetch(PDO::FETCH_OBJ);

            if($ret){

                $placa = $ret->PLACA ?? ' - ';
                $nome = $ret->NOME ?? ' - ';

                $retorno = array('status' => true, 'veiculo_id' => $veiculo_id, 'veiculo_placa' => $placa, 'veiculo_nome' => $nome);

                $this->setUpdateFaceVeic($device_id, $veiculo_id);
                
            }

        } catch (\Throwable $th) {
            
        }

        return $retorno;
        
    }
    
    public function createDetection($post)
    {
        return ['status' => true, 'msg' => "Detecção salva com sucesso!"];
    }

    private function toBlob($base64Data)
    {

		try{

			$base64Data = trim($base64Data);
			$base64Data = preg_replace('#^data:image/\w+;base64,#i', '', $base64Data);
			$blobData 	= base64_decode($base64Data);
			return $blobData;

		} catch (\Throwable $th) {
			return null;
		}

    }   

    public function resgiterDeviceFace($post)
    {

        try{

            $now            = date("Y-m-d H:i:s");
            $os             = $post->os;
            $model          = $post->model;
            $app_version    = $post->app_version;
            $screenWidth    = ceil($post->screenWidth);
            $screenHeight   = ceil($post->screenHeight);
            $latitude       = $post->latitude;
            $longitude      = $post->longitude;

            $randomValue = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $device_id = '';

            do {
                $device_id = "$model-$randomValue";
    
                // Checa se o device_id já existe na tabela face_devices
                $checkDuplicate = $this->db->prepare("SELECT COUNT(*) as count FROM face_devices WHERE device_id = :device_id");
                $checkDuplicate->bindParam(':device_id', $device_id);
                $checkDuplicate->execute();
                $result = $checkDuplicate->fetch(PDO::FETCH_ASSOC);
    
                // Se já existir, gera um novo valor numérico aleatório
                if ($result['count'] > 0) {
                    $randomValue = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
                }
            } while ($result['count'] > 0);

            $saveDevice = $this->db->prepare("INSERT INTO face_devices (device_id, os, model, app_version, screenWidth, screenHeight, latitude, longitude, created_at) VALUES (:device_id, :os, :model, :app_version, :screenWidth, :screenHeight, :latitude, :longitude, '$now')");

            $saveDevice->bindValue(":device_id", $device_id);
            $saveDevice->bindValue(":os", $os);
            $saveDevice->bindValue(":model", $model);
            $saveDevice->bindValue(":app_version", $app_version);
            $saveDevice->bindValue(":screenWidth", $screenWidth);
            $saveDevice->bindValue(":screenHeight", $screenHeight);
            $saveDevice->bindValue(":latitude", $latitude);
            $saveDevice->bindValue(":longitude", $longitude);

            try {
                $saveDevice->execute();
                return ['status' => true, 'msg' => "Aparelho cadastrado com sucesso!", 'device_id' => $device_id];
            } catch (\Throwable $th) {
                return ['status' => false, 'msg' => "Erro ao cadastrar aparelho."];
            }

        } catch (\Throwable $th) {
            return ['status' => false, 'msg' => "Houve um erro inesperado."];
        }

    }

    public function checkConfigFace($device_id, $config_type)
    {

        try {

            $tintColorTarget = '#42ff00';

            $getConfig = $this->db->prepare("SELECT * FROM face_configs WHERE device_id = :device_id AND config_type = :config_type AND deleted_at IS NULL ORDER BY id DESC LIMIT 1");
            
            $getConfig->bindParam(':device_id', $device_id);
            $getConfig->bindParam(':config_type', $config_type);

            $msgUm = $config_type == 0 ? "Fechando configurações..." : "Abrindo configurações...";
            $msgDois = $config_type == 0 ? "não precisa fechar config" : "não precisa abrir config";

            $getTintColorTarget = $this->db->prepare("SELECT tintColorTarget FROM face_devices WHERE device_id = :device_id");
            
            $getTintColorTarget->bindParam(':device_id', $device_id);
            $getTintColorTarget->execute();

            if ($getTintColorTarget->rowCount() == 1) {
                $hasColorTarget = $getTintColorTarget->fetch(PDO::FETCH_OBJ);

                $tintColorTarget = $hasColorTarget->tintColorTarget ?? '#42ff00'; 
            }

            try {

                $getConfig->execute();

                if ($getConfig->rowCount() == 1) {

                    $ret = $getConfig->fetch(PDO::FETCH_OBJ);

                    $now = date("Y-m-d H:i:s");

                    $deteleConfig = $this->db->prepare("UPDATE face_configs SET updated_at = '$now', deleted_at = '$now' WHERE id = :id");
                    $deteleConfig->bindParam(':id', $ret->id);


                    try{
                        $deteleConfig->execute();
                        return ['status' => true, 'msg' => $msgUm, 'tintColorTarget' => $tintColorTarget];
                    } catch (\Throwable $th) {
                        return ['status' => false, 'msg' => "erro ao deletar a configuração", 'tintColorTarget' => $tintColorTarget];
                    }


                } else {

                    return ['status' => false, 'msg' => $msgDois, 'tintColorTarget' => $tintColorTarget];
                    
                }

            } catch (\Throwable $th) {

                return ['status' => false, 'msg' => "Erro ao executar a consulta"];

            }

        } catch (\Throwable $th) {
            return ['status' => false, 'msg' => "Houve um erro inesperado"];
        }

    }

    public function checkChangeCarFace($veiculo_id, $device_id){

        try {

            $getDeviceVeic = $this->db->prepare("SELECT * FROM face_veiculo WHERE device_id = :device_id");
            $getDeviceVeic->bindParam(':device_id', $device_id);

            $getDeviceVeic->execute();

            if($getDeviceVeic->rowCount() == 0) {

                return ['status' => false, 'msg' => "Erro ao encontrar o veículo", 'device_id' => $device_id, 'veiculo_id' => $veiculo_id];

            }else{

                $deviceVeic = $getDeviceVeic->fetch(PDO::FETCH_OBJ);

                if($veiculo_id != $deviceVeic->veiculo_id){

                    $sql = $this->db->prepare("SELECT NOME, PLACA FROM veiculos WHERE ID_ORIGIN = '{$deviceVeic->veiculo_id}' AND ATIVO = 1");

                    try{
            
                        $sql->execute();
                        $ret = $sql->fetch(PDO::FETCH_OBJ);
            
                        if($ret){
            
                            $placa = $ret->PLACA ?? ' - ';
                            $nome = $ret->NOME ?? ' - ';
            
                            return ['status' => true, 'veiculo_id' => $deviceVeic->veiculo_id, 'veiculo_placa' => $placa, 'veiculo_nome' => $nome];
                            
                        }
            
                    } catch (\Throwable $th) {
                        return ['status' => false, 'msg' => "Erro ao encontrar o veículo"];
                    }

                }else{
                    return ['status' => false, 'msg' => "Não houve mudança no veículo"];
                }

            }

        } catch (\Throwable $th) {

            return ['status' => false, 'msg' => "Erro ao executar a consulta"];

        }
        
    }

    public function warningSendInfosFace($device_id, $request_id, $type)
    {

        $now = date("Y-m-d H:i:s");

        if($request_id == 0){

            $insertRequest = $this->db->prepare("INSERT INTO face_request_info (device_id, created_at) VALUES (:device_id, '$now')");
            $insertRequest->bindValue(":device_id", $device_id);
            $insertRequestSuccess = $insertRequest->execute();

            $lastInsertId = $insertRequestSuccess ? $this->db->lastInsertId() : 0;

            return ['status' => $insertRequestSuccess, 'msg' => $insertRequestSuccess ? "Aviso recebido" : "Aviso Não Recebido", 'request_id' => $lastInsertId];

        }else{

            if($type == 0){

                $this->db->query("DELETE FROM face_request_info WHERE id = $request_id");
                return ['status' => true, 'msg' => 'Aviso removido'];

            }else{

                $updateRequest = $this->db->prepare("UPDATE face_request_info SET updated_at = '$now', retorno = :retorno WHERE id = :id");
                $updateRequest->bindParam(':id', $request_id);
                $updateRequest->bindParam(':retorno', $type);
                $updateRequest->execute();
                return ['status' => true, 'msg' => 'Aviso recebido'];
            }                      

        }

    }

    
    public function checkSendInfosFace($device_id, $type, $recCount)
    {

        $checkExisting = $this->db->prepare("SELECT * FROM face_request_info WHERE device_id = :device_id AND retorno = 0 ORDER BY created_at DESC LIMIT 1");
        $checkExisting->bindValue(":device_id", $device_id);

        try {

            $checkExisting->execute();

            if($checkExisting->rowCount() == 1) {

                $hasRequest = $checkExisting->fetch(PDO::FETCH_OBJ);
                $request_id = $hasRequest->id;

                $now = date("Y-m-d H:i:s");
                $updateRequest = $this->db->prepare("UPDATE face_request_info SET updated_at = '$now', retorno = :retorno, recCount = :recCount WHERE id = :id");
                $updateRequest->bindParam(':id', $request_id);
                $updateRequest->bindParam(':retorno', $type);
                $updateRequest->bindParam(':recCount', $recCount);
                $updateRequest->execute();

                if($type == 1){
                    return ['status' => true, 'request_id' => $request_id, 'msg' => "Avisei que vai enviar reconhecimentos."];
                }else{
                    return ['status' => true, 'msg' => "Avisei que não tem reconhecimentos."];
                }
                
            }else{
                return ['status' => true, 'msg' => "Nenhuma solicitação."];
            }

        } catch (\Throwable $th) {

            return ['status' => false, 'msg' => "Erro ao procurar solicitação."];
            
        }
        

    }

    public function checkAtivoFaceCad($device_id)
    {
        try {
            $getDevice = $this->db->prepare("SELECT * FROM face_devices WHERE device_id = :device_id AND ativo = 1 AND deleted_at IS NULL LIMIT 1");
            $getDevice->bindParam(':device_id', $device_id);
            $getDevice->execute();
            if($getDevice->rowCount() == 0) {
                return ['status' => false];
            }
            return ['status' => true];
        } catch (\Throwable $th) {
            return ['status' => false];
        }
    }

    public function acessoGrupoIn($groupAccess)
    {
        $grupos = [];
        $sql = $this->db->prepare("SELECT * FROM acesso_grupos 
            WHERE ID_ORIGIN IN ({$groupAccess}) ORDER BY NOME");
        $sql->execute();

        if($sql->rowCount() > 0) {
            $array = $sql->fetchAll(PDO::FETCH_OBJ);
            foreach($array AS $gr)
            {
                $grupos[] = array("label" => $gr->NOME, "value" => $gr->ID_ORIGIN, "key" => $gr->ID_ORIGIN);
            }
        }
        
        return $grupos;
    }

    public function getCaByGroup($groupAccess)
    {
        $cas = [];
        $sql = $this->db->prepare("SELECT id, NOME, MATRICULA_FUNCIONAL FROM controle_acessos 
            WHERE CONTROLE_ACESSO_GRUPO_ID IN ({$groupAccess}) AND ATIVO = 1 AND deleted_at IS NULL AND user_type = 1
            ORDER BY NOME");
        $sql->execute();

        if($sql->rowCount() > 0) {
            $array = $sql->fetchAll(PDO::FETCH_OBJ);
            foreach($array AS $ca)
            {
                $cas[] = array("label" => $ca->NOME.' ('.$ca->MATRICULA_FUNCIONAL.')', "value" => $ca->id, "key" => $ca->id);
            }
        }

        return $cas;
    }

    public function getGroupName($groupID)
    {
        $name = "";
        $getGroupName = $this->db->prepare("SELECT NOME FROM grupo_linhas WHERE id = {$groupID}");
        $getGroupName->execute();

        if($getGroupName->rowCount() == 1) {
            $groupName = $getGroupName->fetch(PDO::FETCH_OBJ);
            $name = $groupName->NOME;
        }

        return $name;
    }

    public function checkAtivoFace($device_id, $app_version, $veiculo_id = '0')
    {
        
        try {

            $getDevice = $this->db->prepare("SELECT * FROM face_devices WHERE device_id = :device_id AND ativo = 1 AND deleted_at IS NULL LIMIT 1");
            $getDevice->bindParam(':device_id', $device_id);

            try {

                $getDevice->execute();

                if($getDevice->rowCount() == 0) {
                    return ['status' => false, 'msg' => "Aparelho $device_id Inativo!\nEntre em contato com um supervisor!"];
                }
                $device = $getDevice->fetch(PDO::FETCH_OBJ);
                $findVeic = array('veiculo_id' => false, 'veiculo_placa' => false, 'veiculo_nome' => false);
                $timeTrySendMin = 3;
                $timeTrySend = $timeTrySendMin * 60 * 1000;

                $timeSendLocation = $device->circular == 1 ? 15000 : 60000;
                $isCircular = $device->circular == 1 ? true : false;
                $isCad = $device->cad == 1 ? true : false;
                $tintColorTarget = $device->tintColorTarget ?? '#42ff00'; 

                try{

                    $getParameter = new Parametro();
                    $parameter = $getParameter->getParametros();
                    $timeTrySendMin = $parameter['time_send_infos_face'] ?? 3;
                    $timeTrySend = $timeTrySendMin * 60 * 1000;

                } catch (\Throwable $th) {}
                

                try{

                    if($app_version != $device->app_version){

                        $now = date("Y-m-d H:i:s"); 

                        $updateAppVersion = $this->db->prepare("UPDATE face_devices SET app_version = :app_version, updated_at = '$now' WHERE id = :id");
                        $updateAppVersion->bindParam(':app_version', $app_version);
                        $updateAppVersion->bindParam(':id', $device->id);
                        $updateAppVersion->execute();

                    }
                    
                    if(!$isCad){
                        $findVeic = $this->setUpdateFaceVeic($device_id, $veiculo_id);
                    }else{
                        $findVeic = null;
                    }
                    
                    
                } catch (\Throwable $th) {}

                return ['status' => true, 'msg' => "Aparelho Ativo.", 'timeTrySend' => $timeTrySend, 'findVeic' => $findVeic, 'timeSendLocation' => $timeSendLocation, 'tintColorTarget' => $tintColorTarget, "isCircular" => $isCircular, "isCad" => $isCad];

            } catch (\Throwable $th) {
                return ['status' => false, 'msg' => $th->getMessage()];
            }


        } catch (\Throwable $th) {
            return ['status' => false, 'msg' => "Houve um erro inesperado.\nEntre em contato com um supervisor!"];
        }
    }

    public function setAppFaceType($post)
    {
        $now        = date("Y-m-d H:i:s");
        $device_id  = $post->device_id;
        $app_type   = $post->app_type;

        try {
            $updateAppType = $this->db->prepare("UPDATE face_devices SET cad = :cad, updated_at = '$now' WHERE device_id = :device_id");
            $updateAppType->bindParam(':cad', $app_type);
            $updateAppType->bindParam(':device_id', $device_id);
            $updateAppType->execute();
            return ['status' => true];
        } catch (\Throwable $th) {
            return ['status' => false];
        }
    }
    
    public function iniAppFaceTakePic($post)
    {

        $device_id              = $post->device_id;
        $controle_acesso_id     = $post->controle_acesso_id;
        $position               = $post->position;

        try {

            $nomepax = 'Novo Cadastro';

            if(is_numeric($controle_acesso_id)){
                $getPaxName = $this->db->prepare("SELECT NOME FROM controle_acessos WHERE id = :controle_acesso_id");
                $getPaxName->bindParam(':controle_acesso_id', $controle_acesso_id);
                $getPaxName->execute();
                if($getPaxName->rowCount() == 0){
                    return ['status' => false];
                }
                $pax = $getPaxName->fetch(PDO::FETCH_OBJ);
                $nomepax = $pax->NOME;
            }

            $insertDeviceTakePic = $this->db->prepare("INSERT INTO face_take_picture (device_id, controle_acesso_id, position) VALUES (:device_id, :controle_acesso_id, :position)");

            $insertDeviceTakePic->bindValue(":device_id", $device_id);
            $insertDeviceTakePic->bindValue(":controle_acesso_id", $controle_acesso_id);
            $insertDeviceTakePic->bindValue(":position", $position);

            $insertDeviceTakePicSuccess = $insertDeviceTakePic->execute();

            if(!$insertDeviceTakePicSuccess){
                return ['status' => false, 'message' => 'Erro ao comunicar.'];
            }
            
            $request_id = $this->db->lastInsertId();
            return ['status' => true, 'request_id' => $request_id, 'nomepax' => $nomepax];

        } catch (\Throwable $th) {
            return ['status' => false];
        }
    }

    public function calcelAppFaceTakePic($post)
    {
        $request_id = $post->request_id;
        $retorno = 3;

        try {
            $cancelTakePic = $this->db->prepare("UPDATE face_take_picture SET retorno = :retorno WHERE id = :request_id");
            $cancelTakePic->bindParam(':retorno', $retorno);
            $cancelTakePic->bindParam(':request_id', $request_id);
            $cancelTakePic->execute();
            return ['status' => true];
        } catch (\Throwable $th) {
            return ['status' => false, 'msg' => $th->getMessage()];
        }
    }

    public function appTakePicture($post)
    {
        try {
            $controle_acesso_id = $post['controle_acesso_id'];
            $position = $post['position'];

            $sql = $this->db->prepare("SELECT * FROM face_take_picture WHERE controle_acesso_id = :controle_acesso_id AND position = :position ORDER BY id desc LIMIT 1");
            $sql->bindParam(':controle_acesso_id', $controle_acesso_id);
            $sql->bindParam(':position', $position);

            $sql->execute();

            if($sql->rowCount() == 0) {

                return ['status' => false];

            }else{

                $infos = $sql->fetch(PDO::FETCH_OBJ);
                $device_id = $infos->device_id;
                $request_id = $infos->id;
                $retorno = $infos->retorno;
                $ca_pic_id = $infos->ca_pic_id;

                $picture = false;

                if($retorno == 3){
                    $deleteRequest = $this->db->prepare("DELETE FROM face_take_picture WHERE id =  :request_id");
                    $deleteRequest->bindParam(':request_id', $request_id);
                    $deleteRequest->execute();
                }

                if($retorno == 1 && $ca_pic_id !== null){

                    $getPic = $this->db->prepare("SELECT img FROM controle_acessos_pics WHERE controle_acesso_id = :controle_acesso_id AND id = :ca_pic_id AND position = :position AND deleted_at IS NULL");
                    $getPic->bindParam(':controle_acesso_id', $controle_acesso_id);
                    $getPic->bindParam(':ca_pic_id', $ca_pic_id);
                    $getPic->bindParam(':position', $position);
                    $getPic->execute();

                    if($getPic->rowCount() == 1){
                        $pic = $getPic->fetch(PDO::FETCH_OBJ);
                        $picture = $this->toBase64UserPic($pic->img);
                    }
                
                }

                return ['status' => true, 'device_id' => $device_id, 'retorno' => $retorno, 'request_id' => $request_id, 'picture' => $picture];

            }
        } catch (\Throwable $th) {
            return ['status' => false];
        }
    }

    public function checkTakingPicFace($post)
    {
        try {

            $request_id = $post->request_id;

            $sql = $this->db->prepare("SELECT * FROM face_take_picture WHERE id = :request_id");
            $sql->bindParam(':request_id', $request_id);
            $sql->execute();

            return ['status' => $sql->rowCount() == 0 ? false : true];

        } catch (\Throwable $th) {
            return ['status' => false];
        }
    }

    public function sendTakingPicFace($post)
    {

        try {
            
            $request_id             = $post->request_id;
            $controle_acesso_id     = $post->controle_acesso_id;
            $position               = $post->position;
            
            $getTakePic = $this->db->prepare("SELECT id FROM face_take_picture WHERE id = :request_id AND controle_acesso_id = :controle_acesso_id AND position = :position");
            $getTakePic->bindParam(':request_id', $request_id);
            $getTakePic->bindParam(':controle_acesso_id', $controle_acesso_id);
            $getTakePic->bindParam(':position', $position);
            $getTakePic->execute();

            if($getTakePic->rowCount() == 0){
                return ['status' => false];
            }

            $img    = $this->toBlob($post->img);
            $ds     = $post->ds;
            $now    = date("Y-m-d H:i:s");

            $userDs = $this->db->prepare("SELECT * FROM controle_acessos_ds WHERE controle_acesso_id = {$controle_acesso_id} AND position = '{$position}' AND deleted_at IS NULL LIMIT 1");

            $userDs->execute();

            if($userDs->rowCount() == 1) {

                $dsFind = $userDs->fetch(PDO::FETCH_OBJ);
                $updateDs = $this->db->prepare("UPDATE controle_acessos_ds SET ds = :ds, updated_at = '{$now}' WHERE id = :id");
                $updateDs->bindValue(":ds", $ds);
                $updateDs->bindValue(":id", $dsFind->id);
                $updateDs->execute();

            }else{

                $insertDs = $this->db->prepare("INSERT INTO controle_acessos_ds (controle_acesso_id, ds, position, created_at) VALUE (:controle_acesso_id, :ds, :position, '{$now}')");

                $insertDs->bindValue(":controle_acesso_id", $controle_acesso_id);
                $insertDs->bindValue(":ds", $ds);
                $insertDs->bindValue(":position", $position);
                $insertDs->execute();

            }

            $userImg = $this->db->prepare("SELECT * FROM controle_acessos_pics WHERE controle_acesso_id = {$controle_acesso_id} AND position = '{$position}' AND deleted_at IS NULL LIMIT 1");

            $userImg->execute();

            $ca_pic_id = null;

            if($userImg->rowCount() == 1) {

                $imgFind = $userImg->fetch(PDO::FETCH_OBJ);
                $updateImg = $this->db->prepare("UPDATE controle_acessos_pics SET img = :img, updated_at = '{$now}' WHERE id = :id");
                $updateImg->bindValue(":img", $img);
                $updateImg->bindValue(":id", $imgFind->id);
                $updateImg->execute();

                $ca_pic_id = $imgFind->id;

            }else{

                $insertImg = $this->db->prepare("INSERT INTO controle_acessos_pics (controle_acesso_id, img, position, created_at) VALUE (:controle_acesso_id, :img, :position, '{$now}')");

                $insertImg->bindValue(":controle_acesso_id", $controle_acesso_id);
                $insertImg->bindValue(":img", $img);
                $insertImg->bindValue(":position", $position);
                $insertImg->execute();
                $ca_pic_id = $this->db->lastInsertId();

            }

            if($ca_pic_id !== null){
                $retorno = 1;
                $updateTakePic = $this->db->prepare("UPDATE face_take_picture SET retorno = :retorno, ca_pic_id = :ca_pic_id WHERE id = :request_id");
                $updateTakePic->bindParam(':retorno', $retorno);
                $updateTakePic->bindParam(':ca_pic_id', $ca_pic_id);
                $updateTakePic->bindParam(':request_id', $request_id);
                $updateTakePic->execute();
                return ['status' => true];
            }else{
                return ['status' => false];
            }
            

        } catch (\Throwable $th) {
            return ['status' => false, 'message' => $th->getMessage()];
        }

    }

    private function toBase64UserPic($blobData)
	{
		try {
			
			$base64Data = base64_encode($blobData);

			if ($base64Data != false) {
				$result = 'data:image/png;base64,' . $base64Data;
				return $result;
			}

			return false;

		} catch (\Throwable $th) {
			return false;
		}
	}

    public function removeAppTakePicture($post)
    {
        $request_id = $post['request_id'];
        $deleteRequest = $this->db->prepare("DELETE FROM face_take_picture WHERE id = :request_id");
        $deleteRequest->bindParam(':request_id', $request_id);
        $deleteRequest->execute();
        return ['status' => true];
    }

    public function sendCurrentLocation($post)
    {
        try{

            $now            = date("Y-m-d H:i:s");
            $latitude       = $post->latitude;
            $longitude      = $post->longitude;
            // $trueHeading    = $post->trueHeading ?? null;
            $timezone       = $post->timezone;
            $device_id      = $post->device_id;
                        
            $getDeviceLocation = $this->db->prepare("SELECT * FROM face_location WHERE device_id = :device_id");
            $getDeviceLocation->bindParam(':device_id', $device_id);

            $getDeviceLocation->execute();

            if($getDeviceLocation->rowCount() == 0) {

                $insertDeviceLocation = $this->db->prepare("INSERT INTO face_location (device_id, latitude, longitude, timezone, updated_at) VALUES (:device_id, :latitude, :longitude, :timezone, '$now')");

                $insertDeviceLocation->bindValue(":device_id", $device_id);
                $insertDeviceLocation->bindValue(":latitude", $latitude);
                $insertDeviceLocation->bindValue(":longitude", $longitude);
                // $insertDeviceLocation->bindValue(":trueHeading", $trueHeading);
                $insertDeviceLocation->bindValue(":timezone", $timezone);
                $insertDeviceLocation->execute(); 

            }else{

                $deviceLocation = $getDeviceLocation->fetch(PDO::FETCH_OBJ);

                $updateDeviceLocation = $this->db->prepare("UPDATE face_location SET latitude = :latitude, longitude = :longitude, timezone = :timezone, updated_at = '$now' WHERE id = :id");
                $updateDeviceLocation->bindParam(':latitude', $latitude);
                $updateDeviceLocation->bindParam(':longitude', $longitude);
                // $updateDeviceLocation->bindValue(":trueHeading", $trueHeading);
                $updateDeviceLocation->bindParam(':timezone', $timezone);
                $updateDeviceLocation->bindParam(':id', $deviceLocation->id);
                $updateDeviceLocation->execute(); 

            }

            try {

                $isCircular = $this->db->prepare("SELECT circular FROM face_devices WHERE device_id = :device_id AND circular = 1");
                $isCircular->bindParam(':device_id', $device_id);
                $isCircular->execute();

                if($isCircular->rowCount() == 1){

                    $getLastPosition = $this->db->prepare("SELECT *, 
                        (6371000 * acos(
                            cos(radians(:latitude)) * cos(radians(latitude)) * cos(radians(longitude) - radians(:longitude)) + 
                            sin(radians(:latitude)) * sin(radians(latitude))
                        )) AS distance 
                        FROM positions_circ_euro 
                        WHERE device_id = :device_id
                        AND TIMESTAMPDIFF(SECOND, created_at, :agora) <= 20 
                        HAVING distance < 200 
                        ORDER BY id DESC 
                        LIMIT 1");
                    $getLastPosition->bindParam(':device_id', $device_id);
                    $getLastPosition->bindParam(':latitude', $latitude);
                    $getLastPosition->bindParam(':longitude', $longitude);
                    $getLastPosition->bindParam(':agora', $now);
                    $getLastPosition->execute();

                    if($getLastPosition->rowCount() == 0){
                        $insertPosition = $this->db->prepare("INSERT INTO positions_circ_euro (device_id, latitude, longitude, created_at) VALUES (:device_id, :latitude, :longitude, '$now')");
                        $insertPosition->bindValue(":device_id", $device_id);
                        $insertPosition->bindValue(":latitude", $latitude);
                        $insertPosition->bindValue(":longitude", $longitude);
                        $insertPosition->execute(); 
                    }

                }

            } catch (\Throwable $th) {}

            return ['status' => true];

        } catch (\Throwable $th) {
            return ['status' => false];
        }
    }

    public function sendBatteryStatus($post)
    {
        try{

            $now            = date("Y-m-d H:i:s");
            $batteryLevel   = $post->batteryLevel;
            $batteryState   = $post->batteryState;
            $device_id  = $post->device_id;
                        
            $getDeviceBattery = $this->db->prepare("SELECT * FROM face_battery WHERE device_id = :device_id");
            $getDeviceBattery->bindParam(':device_id', $device_id);

            $getDeviceBattery->execute();

            if($getDeviceBattery->rowCount() == 0) {

                $insertDeviceBattery = $this->db->prepare("INSERT INTO face_battery (device_id, batteryLevel, batteryState, updated_at) VALUES (:device_id, :batteryLevel, :batteryState, '$now')");

                $insertDeviceBattery->bindValue(":device_id", $device_id);
                $insertDeviceBattery->bindValue(":batteryLevel", $batteryLevel);
                $insertDeviceBattery->bindValue(":batteryState", $batteryState);
                $insertDeviceBattery->execute(); 

            }else{

                $deviceBattery = $getDeviceBattery->fetch(PDO::FETCH_OBJ);

                $updateDeviceBattery = $this->db->prepare("UPDATE face_battery SET batteryLevel = :batteryLevel, batteryState = :batteryState, updated_at = '$now' WHERE id = :id");
                $updateDeviceBattery->bindParam(':batteryLevel', $batteryLevel);
                $updateDeviceBattery->bindParam(':batteryState', $batteryState);
                $updateDeviceBattery->bindParam(':id', $deviceBattery->id);
                $updateDeviceBattery->execute(); 

            }

            return ['status' => true];

        } catch (\Throwable $th) {
            return ['status' => false];
        }
    }

    private function setUpdateFaceVeic($device_id, $veiculo_id)
    {

        try {

            $now = date("Y-m-d H:i:s"); 

            $getDeviceVeic = $this->db->prepare("SELECT * FROM face_veiculo WHERE device_id = :device_id");
            $getDeviceVeic->bindParam(':device_id', $device_id);

            $getDeviceVeic->execute();

            if($getDeviceVeic->rowCount() == '0') {

                if($veiculo_id != '0'){
                    $insertDeviceVeic = $this->db->prepare("INSERT INTO face_veiculo (device_id, veiculo_id, updated_at) VALUES (:device_id, :veiculo_id, '$now')");
                    $insertDeviceVeic->bindValue(":device_id", $device_id);
                    $insertDeviceVeic->bindValue(":veiculo_id", $veiculo_id);
                    $insertDeviceVeic->execute(); 
                }

            }else{

                $deviceVeic = $getDeviceVeic->fetch(PDO::FETCH_OBJ);
                $retorno = array('veiculo_id' => false, 'veiculo_placa' => false, 'veiculo_nome' => false);
                
                if($veiculo_id == '0'){
                    
                    $sql = $this->db->prepare("SELECT NOME, PLACA FROM veiculos WHERE ID_ORIGIN = '{$deviceVeic->veiculo_id}' AND ATIVO = 1");

                    try{

                        $sql->execute();
                        $ret = $sql->fetch(PDO::FETCH_OBJ);

                        if($ret){

                            $placa = $ret->PLACA ?? ' - ';
                            $nome = $ret->NOME ?? ' - ';

                            $retorno = array('veiculo_id' => $deviceVeic->veiculo_id, 'veiculo_placa' => $placa, 'veiculo_nome' => $nome);
                            
                        }

                    } catch (\Throwable $th) {}

                    return $retorno;

                }else{

                    if($veiculo_id != $deviceVeic->veiculo_id){

                        $updateDeviceVeic = $this->db->prepare("UPDATE face_veiculo SET veiculo_id = :veiculo_id, updated_at = '$now' WHERE id = :id");
                        $updateDeviceVeic->bindParam(':veiculo_id', $veiculo_id);
                        $updateDeviceVeic->bindParam(':id', $deviceVeic->id);
                        $updateDeviceVeic->execute(); 
    
                    }else{
                        $sql = $this->db->prepare("SELECT NOME, PLACA FROM veiculos WHERE ID_ORIGIN = '{$deviceVeic->veiculo_id}' AND ATIVO = 1");
                        $sql->execute();
                        $ret = $sql->fetch(PDO::FETCH_OBJ);

                        if($ret){

                            $placa = $ret->PLACA ?? ' - ';
                            $nome = $ret->NOME ?? ' - ';

                            $retorno = array('veiculo_id' => $deviceVeic->veiculo_id, 'veiculo_placa' => $placa, 'veiculo_nome' => $nome);
                            
                        }
                        return $retorno;
                    }

                }

            }

        } catch (\Throwable $th) {}

        return true;
        
    }

    public function confirmDeviceFace($device_id)
    {
        try{

            $getDevice = $this->db->prepare("SELECT * FROM face_devices WHERE device_id = :device_id AND ativo = 1 AND deleted_at IS NULL LIMIT 1");
            $getDevice->bindParam(':device_id', $device_id);

            try{

                $getDevice->execute();

                if($getDevice->rowCount() == 0) {
                    return ['status' => false, 'message' => "Erro ao confirmar aparelho"];
                }

                return ['status' => true, 'message' => "carregado"];

            } catch (\Throwable $th) {

                return ['status' => false, 'message' => "Erro ao confirmar aparelho"];
    
            }

        } catch (\Throwable $th) {

            return ['status' => false, 'message' => "Erro ao confirmar aparelho"];

        }
    }

    public function getUsersFace($post){

        $notIn = $post->notIn;
        $noUserMsg = "Nenhum usuário com fotos encontrado!";
        $getDetections = false;

        $w = "";

        if(isset($notIn) && $notIn != ""){
            $w .= "AND ca.id NOT IN ($notIn)";
            $noUserMsg = "Já foram carregados todos os usuários com fotos!";
            $getDetections = true;
        }

        $sql = $this->db->prepare("SELECT 
            ca.id, ca.NOME FROM controle_acessos ca 
            INNER JOIN controle_acessos_pics cap ON ca.id = cap.controle_acesso_id 
            WHERE ca.ATIVO = 1 {$w} AND cap.error = 0 
            GROUP BY ca.id
        ");
    
        try {

            $sql->execute();
            $users = $sql->fetchAll(PDO::FETCH_OBJ);
            $positions = "'pic_front_smiling', 'pic_front_serious', 'pic_front_smiling_eg', 'pic_front_serious_eg'";
    
            $userArray = array();
            
            foreach ($users as $user) {

                // $userPics = $this->db->prepare("SELECT id, img, position FROM controle_acessos_pics WHERE controle_acesso_id = :user_id AND position IN ($positions)");
                $userPics = $this->db->prepare("SELECT id, img, position FROM controle_acessos_pics WHERE controle_acesso_id = :user_id");
                $userPics->bindParam(':user_id', $user->id);

                try{

                    $userPics->execute();

                    if($userPics->rowCount() > 0) {

                        $fotos = [];

                        $pictures = $userPics->fetchAll(PDO::FETCH_OBJ);

                        foreach($pictures AS $picture){
                            $fotos[] = [
                                'id' => $picture->id,
                                'position' => $picture->position,
                                'img' => base64_encode($picture->img)
                            ];
                        }


                        $userArray[] = array(
                            'id' => $user->id,
                            'nome' => $user->NOME,
                            'fotos' => $fotos
                        );

                    }else{

                        continue;

                    }

                } catch (\Throwable $th) {

                    continue;

                }
                
            }
    
            if (count($userArray) > 0) {
                $retorno = array('status' => true, 'users' => $userArray);
            } else {
                $retorno = array('status' => false, 'message' => $noUserMsg, 'getDetections' => $getDetections);
            }

        } catch (\Throwable $th) {
            $retorno = array('status' => false, 'message' => $th->getMessage());
        }
    
        return $retorno;
    }

    public function getUsersFaceNew($post){

        $notIn = $post->notIn;
        $noUserMsg = "Nenhum usuário com fotos encontrado!";

        $w = "";

        if(isset($notIn) && $notIn != ""){
            $w .= "AND cads.id NOT IN ($notIn)";
            $noUserMsg = "Já foram carregados todos os descritores!";
        }

        $sql = $this->db->prepare("SELECT cads.id, cads.controle_acesso_id, cads.ds, ca.user_type, ca.NOME FROM controle_acessos_ds cads JOIN controle_acessos ca ON ca.id = cads.controle_acesso_id WHERE ca.ATIVO = 1 {$w}");
    
        try {

            $sql->execute();

            if ($sql->rowCount() > 0) {

                $users = $sql->fetchAll(PDO::FETCH_OBJ);
                $retorno = array('status' => true, 'users' => $users);

            }else {
                $retorno = array('status' => false, 'msg' => $noUserMsg);
            }
            

        } catch (\Throwable $th) {
            $retorno = array('status' => false, 'msg' => $th->getMessage());
        }
    
        return $retorno;
    }

    public function removeUserPicture($id){

        $now = date("Y-m-d H:i:s");

        try{

            $updateCA = $this->db->prepare("UPDATE controle_acessos SET errorPicture = 1, userPicture = null, updated_at = '$now' WHERE id = {$id}");
            $updateCASuccess = $updateCA->execute();

            if ($updateCASuccess) {
                return ['status' => true, 'message' => "Foto de $id removida com sucesso!"];
            } else {
                return ['status' => false, 'message' => "Erro ao remover foto de $id"];
            }
            
        }catch (\Throwable $th) {
            return ['status' => false, 'message' => "Erro ao remover foto de $id"];
        }

    }

    public function getDetectionsFace($post)
    {

        $test = false;

        $veiculo_id = $post->veiculo_id;
        $device_id  = $post->device_id;

        $notIn = $post->notIn;
        $noDetectionMsg = "Nenhuma detecção encontrada!";

        $w = "";

        if(isset($notIn) && $notIn != ""){
            $w .= "AND face_detections.id NOT IN ($notIn)";
            $noDetectionMsg = "Nenhuma nova detecção encontrada!";
        }

        // quando for pra testes colocar a data e hora a partir da qual quer pegar
        if($test){
            $w .= " AND face_detections.real_time > '2024-03-21 04:00:00'";
        }

        $getDetections = $this->db->prepare("SELECT face_detections.id, face_detections.img, face_detections.real_time, face_devices.device_id, face_devices.screenWidth, face_devices.screenHeight FROM face_detections JOIN face_devices ON face_devices.device_id = face_detections.device_id WHERE face_detections.deleted_at IS NULL AND face_detections.confirmed = 0 AND face_detections.device_id = :device_id AND face_detections.img IS NOT NULL {$w} AND face_devices.deleted_at IS NULL ORDER BY face_detections.real_time LIMIT 30");

        $getDetections->bindParam(':device_id', $device_id);
        // $getDetections->bindParam(':veiculo_id', $veiculo_id);

        try{

            $getDetections->execute();

            if ($getDetections->rowCount() > 0) {

                $detections = $getDetections->fetchAll(PDO::FETCH_OBJ);

                $first_detection_time = strtotime($detections[0]->real_time);
                $detectionsArray = array();

                $screenWidth = $detections[0]->screenWidth;
                $screenHeight = $detections[0]->screenHeight;

                foreach ($detections as $detection) {

                    if (strtotime($detection->real_time) - $first_detection_time > 600) {
                        break;
                    }

                    $detectionsArray[] = array(
                        'id' => $detection->id,
                        'img' => base64_encode($detection->img),
                        'real_time' => $detection->real_time
                    );

                }

                $retorno = array('status' => true, 'detections' => $detectionsArray, 'screenWidth' => $screenWidth, 'screenHeight' => $screenHeight);
                

            } else {
                $retorno = array('status' => false, 'message' => $noDetectionMsg);
            }


        } catch (\Throwable $th) {
            $retorno = array('status' => false, 'message' => 'Erro ao encontrar detecções');
        }

        return $retorno;
    }

    public function doRecognationsFace($post)
    {

        $toRemove   = $post->toRemove;
        $toUpdate   = $post->toUpdate;
        $veiculo_id = $post->veiculo_id;
        $device_id  = $post->device_id;

        $teste = array();

        $doBackup = array(
            'toRemove' => isset($toRemove) && $toRemove != "" ? true : false,
            'toUpdate' => array(),
        );

        $msgRemove = isset($toRemove) && $toRemove != "" ? 
            "Erro ao fazer remoções" : "Nenhuma remoção para fazer";

        $msgUpdate = isset($toUpdate) && count($toUpdate) != 0 ? 
            "Erro ao criar reconhecimentos" : "Nenhum reconhecimento para fazer";

        try {

            if(isset($toRemove) && $toRemove != ""){
                $deleteDetections = $this->db->prepare("DELETE FROM face_detections WHERE id IN ($toRemove) AND device_id = :device_id");
                $deleteDetections->bindParam(':device_id', $device_id);
                $deleteDetSuccess = $deleteDetections->execute();

                if ($deleteDetSuccess) {
                    $msgRemove = "Remoções feitas com sucesso";
                    $doBackup['toRemove'] = false;
                }
            }

            if(isset($toUpdate) && count($toUpdate) != 0){

                foreach ($toUpdate as $item) {

                    $checkHas = $this->db->prepare("SELECT facedetection_id FROM face_recognitions WHERE facedetection_id = :facedetection_id LIMIT 1");
                    $checkHas->bindValue(":facedetection_id", $item->detectionId);

                    $checkHas->execute();

                    if($checkHas->rowCount() == 0){

                        $getDetectionImg = $this->db->prepare("SELECT veiculo_id, img, latitude, longitude FROM face_detections WHERE id = :id LIMIT 1");
                        $getDetectionImg->bindParam(':id', $item->detectionId);
                        $getDetectionImg->execute();

                        if ($getDetectionImg->rowCount() == 1) {

                            $detectionImg = $getDetectionImg->fetch(PDO::FETCH_OBJ);
                            $img = $this->resizeDetection($item->screenWidth, $item->screenHeight, $detectionImg->img);

                            $now = date("Y-m-d H:i:s");

                            $createRecognition = $this->db->prepare("INSERT INTO face_recognitions (facedetection_id, controle_acesso_id, veiculo_id, device_id, img, faceBoxX, faceBoxY, faceBoxW, faceBoxH, real_time, latitude, longitude, created_at) VALUES (:facedetection_id, :controle_acesso_id, :veiculo_id, :device_id, :img, :faceBoxX, :faceBoxY, :faceBoxW, :faceBoxH, :real_time, :latitude, :longitude, '$now')");
            
                            $createRecognition->bindValue(":facedetection_id", $item->detectionId);
                            $createRecognition->bindValue(":controle_acesso_id", $item->userId);
                            $createRecognition->bindValue(":veiculo_id", $detectionImg->veiculo_id);
                            $createRecognition->bindValue(":device_id", $device_id);
                            $createRecognition->bindValue(":img", $img);
                            $createRecognition->bindValue(":faceBoxX", $item->scaledFaceBoxX);
                            $createRecognition->bindValue(":faceBoxY", $item->scaledFaceBoxY);
                            $createRecognition->bindValue(":faceBoxW", $item->scaledFaceBoxW);
                            $createRecognition->bindValue(":faceBoxH", $item->scaledFaceBoxH);
                            $createRecognition->bindValue(":real_time", $item->real_time);
                            $createRecognition->bindValue(":latitude", $detectionImg->latitude);
                            $createRecognition->bindValue(":longitude", $detectionImg->longitude);

                            try {

                                $createRecognition->execute();
                                $updateDetection = $this->db->prepare("UPDATE face_detections SET confirmed = 1, img = NULL, updated_at = '$now' WHERE id = :id");
                                $updateDetection->bindParam(':id', $item->detectionId);
                                $updateDetection->execute();                            

                            } catch (\Throwable $th) {
                                
                                array_push($doBackup['toUpdate'], $item);

                            }

                        }

                    }

                }

                $msgUpdate = (count($doBackup['toUpdate']) == 0) ? 
                    "".count($toUpdate)." reconhecimentos criados com sucesso" : 
                    "".(count($toUpdate) - count($doBackup['toUpdate']))." de ".count($toUpdate)." reconhecimentos criados";

            }
            
            $retorno = array('status' => true, 'message' => "$msgRemove - $msgUpdate", 'doBackup' => $doBackup);
            
        } catch (\Throwable $th) {
            $retorno = array('status' => false, 'message' => 'Erro ao criar reconhecimentos.');
        }

        return $retorno;
    }

    public function getRecognitionsFace($post)
    {

        $device_id  = $post->device_id;
        $veiculo_id = $post->veiculo_id;
        $noRecognitionMsg = "Nenhum reconhecimento encontrado!";

        $getRecognitions = $this->db->prepare("SELECT 
                id, facedetection_id, controle_acesso_id, img, real_time 
            FROM 
                face_recognitions 
            WHERE 
                device_id = :device_id
                AND deleted_at IS NULL 
                AND real_time >= DATE_SUB((SELECT MAX(real_time) 
                                            FROM face_recognitions 
                                            WHERE device_id = :device_id
                                            AND deleted_at IS NULL), 
                                            INTERVAL 10 DAY)
            ORDER BY 
                controle_acesso_id, real_time
        ");

        // $getRecognitions->bindParam(':veiculo_id', $veiculo_id);
        $getRecognitions->bindParam(':device_id', $device_id);

        try{

            $getRecognitions->execute();

            if ($getRecognitions->rowCount() > 0) {

                $recognitions = $getRecognitions->fetchAll(PDO::FETCH_OBJ);

                $recognitionsArray = [];
                $seenRecognitions = []; // Array auxiliar para rastrear os controle_acesso_id e os tempos correspondentes

                foreach ($recognitions as $key => $recognition) {

                    if ($recognition->controle_acesso_id == 0) {
                        if(isset($recognitions[$key + 1]) && $recognitions[$key + 1]->controle_acesso_id == 0){
                            $timeNext = strtotime($recognitions[$key + 1]->real_time);
                            $timeDifference = strtotime($recognition->real_time) - $timeNext;

                            if (abs($timeDifference) < 10) {
                                $now = date("Y-m-d H:i:s");
                                $deletedRecogntion = $this->db->prepare("UPDATE face_recognitions 
                                                                    SET deleted_at = '$now' 
                                                                    WHERE id = :id");
                                $deletedRecogntion->bindParam(':id', $recognition->id);
                                $deletedRecogntion->execute();
                                $deleteDetection = $this->db->prepare("DELETE FROM face_detections WHERE id = :id");
                                $deleteDetection->bindParam(':id', $recognition->facedetection_id);
                                $deleteDetection->execute();
                                continue;
                            }
                        }
                    }

                    if ($recognition->controle_acesso_id != 0) {
                        // Verifica se já vimos esse controle_acesso_id antes
                        if (isset($seenRecognitions[$recognition->controle_acesso_id])) {
                            // Calcule a diferença de tempo em segundos
                            $timeDifference = strtotime($recognition->real_time) - strtotime($seenRecognitions[$recognition->controle_acesso_id]);
                            // Se a diferença de tempo for menor que 60 segundos, remove
                            if (abs($timeDifference) < 60) {
                                $now = date("Y-m-d H:i:s");
                                $deletedRecogntion = $this->db->prepare("UPDATE face_recognitions 
                                                                    SET deleted_at = '$now' 
                                                                    WHERE id = :id");
                                $deletedRecogntion->bindParam(':id', $recognition->id);
                                $deletedRecogntion->execute();
                                $deleteDetection = $this->db->prepare("DELETE FROM face_detections WHERE id = :id");
                                $deleteDetection->bindParam(':id', $recognition->facedetection_id);
                                $deleteDetection->execute();
                                continue;
                                
                            }
                        }
                        
                        // Atualize o controle_acesso_id e o tempo correspondente
                        $seenRecognitions[$recognition->controle_acesso_id] = $recognition->real_time;
                    }

                    $recognitionsArray[] = [
                        'id' => $recognition->id,
                        'img' => base64_encode($recognition->img),
                        'real_time' => $recognition->real_time,
                        'formated_time' => date("d/m/Y - H:i:s", strtotime($recognition->real_time)),
                        'controle_acesso_id' => $recognition->controle_acesso_id,
                        'facedetection_id' => $recognition->facedetection_id
                    ];
                
                }              

                $retorno = array('status' => true, 'recognitions' => $recognitionsArray);
                

            } else {
                $retorno = array('status' => false, 'message' => $noRecognitionMsg);
            }


        } catch (\Throwable $th) {
            $retorno = array('status' => false, 'message' => 'Erro ao encontrar reconhecimento');
        }

        return $retorno;
    }

    public function updateRecogntionsFace($post)
    {
        $toUpdate = $post->toUpdate;

        $status = true;
        $msgUpdate = isset($toUpdate) && count($toUpdate) != 0 ? 
            "Erro ao atualizar reconhecimentos" : "Nenhum reconhecimento para atualizar";

        try {
            
            if(isset($toUpdate) && count($toUpdate) != 0){
                foreach ($toUpdate as $item) {
    
                    $oneMinuteBefore = date('Y-m-d H:i:s', strtotime($item->real_time) - 60);
                    $oneMinuteAfter = date('Y-m-d H:i:s', strtotime($item->real_time) + 60);
    
                    $findClose = $this->db->prepare("SELECT id FROM face_recognitions 
                        WHERE controle_acesso_id = :controle_acesso_id 
                        AND real_time BETWEEN :one_minute_before AND :one_minute_after");
                    $findClose->bindParam(':controle_acesso_id', $item->controle_acesso_id);
                    $findClose->bindParam(':one_minute_before', $oneMinuteBefore);
                    $findClose->bindParam(':one_minute_after', $oneMinuteAfter);
                    $findClose->execute();
    
                    $now = date("Y-m-d H:i:s");

                    if ($findClose->rowCount() == 0) {
                        
                        $updateRecogntion = $this->db->prepare("UPDATE face_recognitions 
                                                               SET controle_acesso_id = :controle_acesso_id, updated_at = '$now' 
                                                               WHERE id = :id");
                        $updateRecogntion->bindParam(':controle_acesso_id', $item->controle_acesso_id);
                        $updateRecogntion->bindParam(':id', $item->id);
                        $updateRecogntion->execute();

                    }else{

                        $deletedRecogntion = $this->db->prepare("UPDATE face_recognitions 
                                                            SET deleted_at = '$now' 
                                                            WHERE id = :id");
                        $deletedRecogntion->bindParam(':id', $item->id);
                        $deletedRecogntion->execute();

                        $deleteDetection = $this->db->prepare("DELETE FROM face_detections WHERE id = :id");
                        $deleteDetection->bindParam(':id', $item->facedetection_id);
                        $deleteDetection->execute();

                    }                
                    
                }
            }

            $msgUpdate = 'Reconhecimentos atualizados com sucesso';

        } catch (\Throwable $th) {
            $status = false;
        }

        return array('status' => $status, 'message' => $msgUpdate);
        
    }

    public function createRecoginationsFace($post)
    {
        
        try {

            $now = date("Y-m-d H:i:s");

            $request_id     = $post->request_id;
            $device_id      = $post->device_id;
            $screenWidth    = 384;
            $screenHeight   = 786;

            if(isset($post->screenWidth) && isset($post->screenHeight)){
                $screenWidth    = ceil($post->screenWidth);
                $screenHeight   = ceil($post->screenHeight);
            }else{
                $getScreen = $this->db->prepare("SELECT screenWidth, screenHeight FROM face_devices WHERE device_id = :device_id");
                $getScreen->bindValue(":device_id", $device_id);
                $getScreen->execute();
                if($getScreen->rowCount() == 1){
                    $screen = $getScreen->fetch(PDO::FETCH_OBJ);
                    $screenWidth    = ceil($screen->screenWidth);
                    $screenHeight   = ceil($screen->screenHeight);
                }

            }

            $removeRequest = false;

            if($request_id == 0){
                $insertRequest = $this->db->prepare("INSERT INTO face_request_info (device_id, created_at) VALUES (:device_id, '$now')");
                $insertRequest->bindValue(":device_id", $device_id);
                $insertRequestSuccess = $insertRequest->execute();

                if(!$insertRequestSuccess){
                    return array('status' => false, 'msg' => 'Erro ao criar request.');
                }
    
                $removeRequest = true;
                $request_id = $this->db->lastInsertId();
            }

            $veiculo_id = $post->veiculo_id;
            $recognized = $post->recognizedSend;
            $notRecognized = $post->notRecognizedSend;
            $tryAgain = $post->tryAgainSend;

            //remover duplicados não reconhecidos
            foreach ($notRecognized as $key => $item) {

                $faceID = $item->faceID;
                $controle_acesso_id = $item->controle_acesso_id;
                $notRecognizedItems = array_filter($notRecognized, function($notRecognizedItem, $itemKey) use ($faceID, $controle_acesso_id, $key) {
                    return $notRecognizedItem->faceID === $faceID && $notRecognizedItem->controle_acesso_id === $controle_acesso_id && $itemKey !== $key;
                }, ARRAY_FILTER_USE_BOTH);
            
                if(count($notRecognizedItems) > 0){
                    $itemTime = strtotime($item->real_time);
            
                    foreach ($notRecognizedItems as $itemKey => $notRecognizedItem) {
                        $notRecognizedTime = strtotime($notRecognizedItem->real_time);
                        $timeDifference = abs($notRecognizedTime - $itemTime);
                        if ($timeDifference <= 10) {
                            unset($notRecognized[$itemKey]);
                        }
                    }
                }
            }

            //remover não reconhecidos que estejam em $recognized
            foreach ($notRecognized as $key => $item) {

                $faceID = $item->faceID;
                $recognizedItems = array_filter($recognized, function($recognizedItem) use ($faceID) {
                    return $recognizedItem->faceID == $faceID;
                });

                if(count($recognizedItems) > 0){
                    
                    $itemTime = strtotime($item->real_time);

                    foreach ($recognizedItems as $recognizedItem) {
                        $recognizedTime = strtotime($recognizedItem->real_time);
                        $timeDifference = abs($recognizedTime - $itemTime);
                        if ($timeDifference <= 10) {
                            unset($notRecognized[$key]);
                            break;
                        }
                    }

                }
            }

            //remover duplicados reconhecidos
            foreach ($recognized as $key => $item) {

                $faceID = $item->faceID;
                $controle_acesso_id = $item->controle_acesso_id;
                $recognizedItems = array_filter($recognized, function($recognizedItem, $itemKey) use ($faceID, $controle_acesso_id, $key) {
                    return $recognizedItem->faceID === $faceID && $recognizedItem->controle_acesso_id === $controle_acesso_id && $itemKey !== $key;
                }, ARRAY_FILTER_USE_BOTH);
            
                if(count($recognizedItems) > 0){
                    $itemTime = strtotime($item->real_time);
            
                    foreach ($recognizedItems as $itemKey => $recognizedItem) {
                        $recognizedTime = strtotime($recognizedItem->real_time);
                        $timeDifference = abs($recognizedTime - $itemTime);
                        if ($timeDifference <= 10) {
                            unset($recognized[$itemKey]);
                        }
                    }
                }
            }
            
            //juntar reconhecidos e desconhecidos em um mesmo array para criar no banco
            $toCreate = array_merge($recognized, $notRecognized);

            $newDs = [];

            foreach ($toCreate as $item) {

                // $wCh = "";

                // if($item->controle_acesso_id == 0){
                //     $wCh = " AND faceID = :faceID";
                // }

                // $checkHas = $this->db->prepare("SELECT 
                //     faceID, controle_acesso_id, real_time 
                // FROM 
                //     face_recognitions 
                // WHERE 
                //     controle_acesso_id = :controle_acesso_id
                //     AND device_id = :device_id
                //     AND ABS(TIMESTAMPDIFF(SECOND, real_time, :real_time)) <= 60 {$wCh} LIMIT 1");

                // $checkHas->bindValue(":controle_acesso_id", $item->controle_acesso_id);
                // $checkHas->bindValue(":device_id", $device_id);
                // $checkHas->bindValue(":real_time", $item->real_time);

                // if($item->controle_acesso_id == 0){
                //     $checkHas->bindValue(":faceID", $item->faceID);
                // }

                // $checkHas->execute();

                // if($checkHas->rowCount() != 0){
                //     continue;
                // }

                $now = date("Y-m-d H:i:s");

                $createRecognition = $this->db->prepare("INSERT INTO face_recognitions (faceID, controle_acesso_id, veiculo_id, device_id, img, real_time, latitude, longitude, ds, score, created_at) VALUES (:faceID, :controle_acesso_id, :veiculo_id, :device_id, :img, :real_time, :latitude, :longitude, :ds, :score, '$now')");

                // $img = $this->resizeRecognition($screenWidth, $screenHeight, $item->img);
                $img = $this->toBlob($item->img);

                $createRecognition->bindValue(":faceID", $item->faceID);
                $createRecognition->bindValue(":controle_acesso_id", $item->controle_acesso_id);
                $createRecognition->bindValue(":veiculo_id", $veiculo_id);
                $createRecognition->bindValue(":device_id", $device_id);
                $createRecognition->bindValue(":img", $img);
                $createRecognition->bindValue(":real_time", $item->real_time);
                $createRecognition->bindValue(":latitude", $item->latitude);
                $createRecognition->bindValue(":longitude", $item->longitude);
                $createRecognition->bindValue(":ds", $item->ds);
                $createRecognition->bindValue(":score", sprintf('%.2f', $item->score));

                $createRecognitionSuccess = $createRecognition->execute();

                //quando é reconhecido adiciona o descritor para o usuário
                if($createRecognitionSuccess && $item->controle_acesso_id != 0){

                    $createdRecId = $this->db->lastInsertId();
                    $position = "recognition-$createdRecId";

                    $insertDs = $this->db->prepare("INSERT INTO controle_acessos_ds (controle_acesso_id, ds, position, created_at) VALUES (:controle_acesso_id, :ds, :position, '$now')");

                    $insertDs->bindValue(":controle_acesso_id", $item->controle_acesso_id);
                    $insertDs->bindValue(":ds", $item->ds);
                    $insertDs->bindValue(":position", $position);
                    $insertDsSuccess = $insertDs->execute();
                    if($insertDsSuccess){
                        $createdDsId = $this->db->lastInsertId();
                        $newDs[] = $createdDsId;
                    }
                }             
                

            }

            foreach($tryAgain as $fitem){

                try {

                    $now = date("Y-m-d H:i:s");

                    $createTryAgain = $this->db->prepare("INSERT INTO face_try_again (faceID, veiculo_id, device_id, img, real_time, latitude, longitude, created_at) VALUES (:faceID, :veiculo_id, :device_id, :img, :real_time, :latitude, :longitude, '$now')");

                    // $img = $this->resizeRecognition($screenWidth, $screenHeight, $fitem->img);
                    $img = $this->toBlob($fitem->img);

                    $createTryAgain->bindValue(":faceID", $fitem->faceID);
                    $createTryAgain->bindValue(":veiculo_id", $veiculo_id);
                    $createTryAgain->bindValue(":device_id", $device_id);
                    $createTryAgain->bindValue(":img", $img);
                    $createTryAgain->bindValue(":real_time", $fitem->real_time);
                    $createTryAgain->bindValue(":latitude", $fitem->latitude);
                    $createTryAgain->bindValue(":longitude", $fitem->longitude);
                    $createTryAgain->execute();

                } catch (\Throwable $th) {}

            }

            if($removeRequest){
                $this->db->query("DELETE FROM face_request_info WHERE id = $request_id");
            }

            $retorno = array('status' => true, 'msg' => 'Reconhecimentos criados com sucesso!', 'newDs' => $newDs);

        } catch (\Throwable $th) {
            $retorno = array('status' => false, 'msg' => 'Erro ao criar reconhecimentos.');
        }

        return $retorno;

    }

    public function getPaxFace($post)
    {
        $id = $post->id;

        $getPax = $this->db->prepare("SELECT 
            ca.id AS paxId, ca.NOME, ca.CONTROLE_ACESSO_GRUPO_ID AS grupo, ca.MATRICULA_FUNCIONAL AS matricula, ca.monitor, ca.eyeglasses,
            itiIDA.LINHA_ID AS LinhaIda, itiVol.LINHA_ID AS LinhaVolta
			FROM controle_acessos ca
			LEFT JOIN itinerarios itiIDA ON itiIDA.ID_ORIGIN = ca.ITINERARIO_ID_IDA
			LEFT JOIN itinerarios itiVol ON itiVol.ID_ORIGIN = ca.ITINERARIO_ID_VOLTA
			WHERE ca.deleted_at IS NULL AND ca.id = :id");

        $getPax->bindValue(":id", $id);

        $getPax->execute();

        if($getPax->rowCount() == 0){
            return ['status' => false, 'message' => 'Usuário não encontrado.'];
        }

        $linhasAdI = $this->db->prepare("SELECT linhasAdicionais.linha_id
			FROM linhasAdicionais
			JOIN itinerarios ON itinerarios.LINHA_ID = linhasAdicionais.linha_id
			WHERE linhasAdicionais.deleted_at is null AND controle_acesso_id = {$id} AND itinerarios.ATIVO = 1 AND itinerarios.SENTIDO = 0");
        $linhasAdI->execute();
        
        $linhasAdV = $this->db->prepare("SELECT linhasAdicionais.linha_id
            FROM linhasAdicionais
            JOIN itinerarios ON itinerarios.LINHA_ID = linhasAdicionais.linha_id
            WHERE linhasAdicionais.deleted_at is null AND controle_acesso_id = {$id} AND itinerarios.ATIVO = 1 AND itinerarios.SENTIDO = 1");
        $linhasAdV->execute();

        $pictures = false;

        $userPics = $this->db->prepare("SELECT img, position FROM controle_acessos_pics WHERE controle_acesso_id = {$id} AND deleted_at IS NULL");
        $userPics->execute();
        
        if($userPics->rowCount() > 0) {
            $pictures = $userPics->fetchAll(PDO::FETCH_OBJ);

            foreach($pictures as $key => $pic){
                $pictures[$key]->img = $this->toBase64UserPic($pic->img);
                $pictures[$key]->ds = null;
                $userDs = $this->db->prepare("SELECT ds FROM controle_acessos_ds WHERE controle_acesso_id = {$id} AND position = '{$pic->position}' AND deleted_at IS NULL");
                $userDs->execute();
                if($userDs->rowCount() == 1){
                    $userDs = $userDs->fetch(PDO::FETCH_OBJ);
                    $pictures[$key]->ds = $userDs->ds;
                }
            }
        }

        $pax = $getPax->fetch(PDO::FETCH_OBJ);
        $pax->linhasAdI = $linhasAdI->fetchAll(PDO::FETCH_OBJ);
        $pax->linhasAdV = $linhasAdV->fetchAll(PDO::FETCH_OBJ);
        $pax->pics = $pictures;

        return ['status' => true, 'pax' => $pax];

    }

    private function itinerarioByLine($lineID)
	{
		try {
            $pdoSql = new \PDO ("dblib:host=$this->host:$this->port;dbname=$this->dbName;charset=utf8","$this->user","$this->pass");
        } catch (\Throwable $th) {
            return false;
        }

        $sql = "SELECT ID
				FROM ITINERARIOS WHERE ATIVO = 1 AND LINHA_ID = {$lineID};";
        $consulta = $pdoSql->query($sql);
		$retur = $consulta->fetch();

        return $retur['ID'] ?? false;
	}

    public function savePaxFace($post)
    {

        try {

            $now = date("Y-m-d H:i:s");
    
            $paxId = $post->paxId;
            $nome = $post->nome;
            $matricula = $post->matricula != '' ? $post->matricula : 0;
            $grupo = $post->grupo ?? 0;
            $monitor = $post->monitor;
            $linhaIda = $post->linhaIda;
            $linhaVolta = $post->linhaVolta;
            $linhasAdI = $post->linhasAdI;
            $linhasAdV = $post->linhasAdV;
            $pictures = $post->pictures;
            $eyeglasses = $post->eyeglasses;
            $new = ($paxId == 0) ? true : false;
        
            // Itinerários
            $itiIda = 0;
            $itiVolta = 0;

            if($linhaIda && $linhaIda != 0){
                $itiIda = $this->itinerarioByLine($linhaIda) ?? 0;
            }
            
            if($linhaVolta && $linhaVolta != 0){
                $itiVolta = $this->itinerarioByLine($linhaVolta) ?? 0;
            }

            // Inserir ou atualizar o passageiro
            if($paxId == 0){

                $insertPax = $this->db->prepare("INSERT INTO controle_acessos (NOME, ITINERARIO_ID_IDA, ITINERARIO_ID_VOLTA, CONTROLE_ACESSO_GRUPO_ID, MATRICULA_FUNCIONAL, ATIVO, monitor, eyeglasses, created_cgf_id, created_at) VALUES (:NOME, :ITINERARIO_ID_IDA, :ITINERARIO_ID_VOLTA, :CONTROLE_ACESSO_GRUPO_ID, :MATRICULA_FUNCIONAL, :ATIVO, :monitor, :eyeglasses, :created_cgf_id, :created_at)");

                $insertPax->bindValue(":ATIVO", 1);
                $insertPax->bindValue(":created_cgf_id", $now);
                $insertPax->bindValue(":created_at", $now);

            }else{

                $insertPax = $this->db->prepare("UPDATE controle_acessos SET NOME = :NOME, ITINERARIO_ID_IDA = :ITINERARIO_ID_IDA, ITINERARIO_ID_VOLTA = :ITINERARIO_ID_VOLTA, CONTROLE_ACESSO_GRUPO_ID = :CONTROLE_ACESSO_GRUPO_ID, MATRICULA_FUNCIONAL = :MATRICULA_FUNCIONAL, monitor = :monitor, eyeglasses = :eyeglasses, updated_cgf_id = :updated_cgf_id, updated_at = :updated_at WHERE id = :paxId");

                $insertPax->bindValue(":paxId", $paxId, PDO::PARAM_INT);
                $insertPax->bindValue(":updated_cgf_id", $now);
                $insertPax->bindValue(":updated_at", $now);

            }

            $insertPax->bindValue(":NOME", $nome);
            $insertPax->bindValue(":ITINERARIO_ID_IDA", $itiIda);
            $insertPax->bindValue(":ITINERARIO_ID_VOLTA", $itiVolta);
            $insertPax->bindValue(":CONTROLE_ACESSO_GRUPO_ID", $grupo);
            $insertPax->bindValue(":MATRICULA_FUNCIONAL", $matricula);
            $insertPax->bindValue(":monitor", $monitor);
            $insertPax->bindValue(":eyeglasses", $eyeglasses);

            $insertPax->execute();


            $paxId = ($paxId == 0) ? $this->db->lastInsertId() : $paxId;
            
            // Linhas Adicionais
            // Primeiro Remove Todas do Passageiro
            $removeAdLines = $this->db->prepare("DELETE FROM linhasAdicionais WHERE controle_acesso_id = :controle_acesso_id");
            $removeAdLines->bindValue(":controle_acesso_id", $paxId, PDO::PARAM_INT);
            $removeAdLines->execute();
            
            // Linhas Adicionais de Ida
            foreach ($linhasAdI as $linhaAdI) {
                if (ctype_digit($linhaAdI->linha_id) && $linhaAdI->linha_id > 0) {
                    $sql = $this->db->prepare("INSERT INTO linhasAdicionais (linha_id, controle_acesso_id, created_at) VALUES (:linha_id, :controle_acesso_id, :created_at)");
                    $sql->bindValue(":linha_id", $linhaAdI->linha_id, PDO::PARAM_INT);
                    $sql->bindValue(":controle_acesso_id", $paxId, PDO::PARAM_INT);
                    $sql->bindValue(":created_at", $now);
                    $sql->execute();
                }
            }

            // Linhas Adicionais de Volta
            foreach ($linhasAdV as $linhaAdV) {
                if (ctype_digit($linhaAdV->linha_id) && $linhaAdV->linha_id > 0) {
                    $sql = $this->db->prepare("INSERT INTO linhasAdicionais (linha_id, controle_acesso_id, created_at) VALUES (:linha_id, :controle_acesso_id, :created_at)");
                    $sql->bindValue(":linha_id", $linhaAdI->linha_id, PDO::PARAM_INT);
                    $sql->bindValue(":controle_acesso_id", $paxId, PDO::PARAM_INT);
                    $sql->bindValue(":created_at", $now);
                    $sql->execute();
                }
            }
            // Fotos
            foreach ($pictures as $picture) {
                $position = $picture->position;
                $img = $picture->img;
                $ds = $picture->ds;

                if ($ds === null) {
                    
                    $sql = $this->db->prepare("DELETE FROM controle_acessos_ds WHERE position = :position AND controle_acesso_id = :paxId");
                    $sql->bindValue(":position", $position);
                    $sql->bindValue(":paxId", $paxId);
                    $sql->execute();

                } else {
                    
                    $sql = $this->db->prepare("INSERT INTO controle_acessos_ds (position, controle_acesso_id, ds, created_at) VALUES (:position, :paxId, :ds, :created_at)
                                            ON DUPLICATE KEY UPDATE ds = :ds, updated_at = :updated_at");
                    $sql->bindValue(":position", $position);
                    $sql->bindValue(":paxId", $paxId);
                    $sql->bindValue(":ds", $ds);
                    $sql->bindValue(":created_at", $now);
                    $sql->bindValue(":updated_at", $now);
                    $sql->execute();
                }

                if ($img === null) {
                    $sql = $this->db->prepare("DELETE FROM controle_acessos_pics WHERE position = :position AND controle_acesso_id = :paxId");
                    $sql->bindValue(":position", $position);
                    $sql->bindValue(":paxId", $paxId);
                    $sql->execute();
                } else {
                    $imgBlob = $this->toBlob($img);
                    if($imgBlob){
                        $sql = $this->db->prepare("INSERT INTO controle_acessos_pics (position, controle_acesso_id, img, created_at) VALUES (:position, :paxId, :img, :created_at)
                                            ON DUPLICATE KEY UPDATE img = :img, updated_at = :updated_at");
                        $sql->bindValue(":position", $position);
                        $sql->bindValue(":paxId", $paxId);
                        $sql->bindValue(":img", $imgBlob);
                        $sql->bindValue(":created_at", $now);
                        $sql->bindValue(":updated_at", $now);
                        $sql->execute();
                    }
                }
            }

            $msgOk = "Passageiro " . ($new ? "adicionado" : "editado") . " com sucesso!";

            return ['status' => true, 'message' => $msgOk];

        } catch (\Throwable $th) {
            $msgError = "Erro ao " . ($new ? "adicionar" : "editar") . " o passageiro" . $th->getMessage();
            return ['status' => false, 'message' => $msgError];
        }
    }    

    private function resizeDetection($screenWidth, $screenHeight, $blobImage)
    {
        
        $image = imagecreatefromstring($blobImage);
        
        $resizedImage = imagecreatetruecolor($screenWidth, $screenHeight);
        imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $screenWidth, $screenHeight, imagesx($image), imagesy($image));
        
        ob_start();
        imagejpeg($resizedImage);
        $imageBlob = ob_get_clean();

        return $imageBlob;

    }

    private function resizeRecognition($screenWidth, $screenHeight, $base64Data)
    {

        $base64Data = trim($base64Data);
        $base64Data = preg_replace('#^data:image/\w+;base64,#i', '', $base64Data);
        $blobData 	= base64_decode($base64Data);
        
        $image = imagecreatefromstring($blobData);
        
        $resizedImage = imagecreatetruecolor($screenWidth, $screenHeight);
        imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $screenWidth, $screenHeight, imagesx($image), imagesy($image));
        
        ob_start();
        imagejpeg($resizedImage);
        $imageBlob = ob_get_clean();

        return $imageBlob;

    }

} 

?>