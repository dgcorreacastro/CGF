<?php

ini_set('memory_limit', '-1');
date_default_timezone_set('America/Sao_Paulo');

class FaceDevices extends model 
{

    public function getDevices($pag, $int, $device_id, $model, $carro, $version, $cad, $lost72, $noLoc, $noRec72, $withRec72, $circLine)
    {

        $now = new DateTime();
        $now->modify('-72 hours');
        $formattedDate = $now->format('Y-m-d H:i:s');

        $w = "";

        //FILTRAGEM FEITA PELA URL
		if(isset($device_id) && $device_id != 0)
            $w .= " AND fd.id = {$device_id}";

        if(isset($model) && $model != "")
            $w .= " AND TRIM(fd.model) = TRIM('{$model}')";

        if(isset($int) && $int == 1)
			$w .= " AND fd.ativo = 1";

        if(isset($carro) && $carro != ""){
            if($carro == "nocar"){
                $w .= " AND fv.veiculo_id IS NULL";
            }else{
                $w .= " AND TRIM(fv.veiculo_id) = TRIM('{$carro}')";
            }
        }
        
        if(isset($version) && $version != "")
            $w .= " AND TRIM(fd.app_version) = TRIM('{$version}')";

        if(isset($cad) && $cad != ""){
            $cad = $cad == "det" ? 0 : 1;
            $w .= " AND fd.cad = {$cad}";
        }

        if($lost72){

            $w .= " AND fl.updated_at < '{$formattedDate}'";

            if($noLoc){
                $w .= " OR fl.updated_at IS NULL";
            }

        }

        if($noLoc){
            $w .= " AND fl.updated_at IS NULL";
        }

        if($noRec72){
            $w .= " AND fd_max.real_time IS NULL";
        }

        if($withRec72){
            $w .= " AND fd_max.real_time IS NOT NULL";
        }

        if($circLine){
            $w .= " AND fd.circular = 1";
        }
        
        #######################################################################
        ############################# GET TOTAL ###############################
        #######################################################################
        $sql="SELECT COUNT(*) AS total 
        FROM face_devices fd 
        LEFT JOIN face_veiculo fv ON fv.device_id = fd.device_id
        LEFT JOIN face_location fl ON fl.device_id = fd.device_id
        LEFT JOIN (
            SELECT 
                device_id, 
                MAX(real_time) AS real_time
            FROM 
                face_recognitions
            WHERE 
                deleted_at IS NULL AND real_time >= '{$formattedDate}'
            GROUP BY 
                device_id
        ) AS fd_max ON fd_max.device_id = fd.device_id
        WHERE fd.deleted_at IS NULL {$w}";
        $sql = $this->db->prepare($sql);
        $sql->execute();
        $tt = $sql->fetch(PDO::FETCH_OBJ);
        #######################################################################
        ######################### CONTINUE FILTERS ############################
        #######################################################################
		$limPag     = 12;
        $ttPages    = intval( ceil($tt->total / $limPag) ); 
		$of         = $limPag * ($pag - 1);
        $offset     = $of > 0 ? " OFFSET $of" : "";

        $getDevices = $this->db->prepare("SELECT fd.*, fv.veiculo_id, fv.updated_at AS veic_update, CONCAT(veic.NOME, ' - ', veic.PLACA) AS VEICULO, fl.latitude AS latitude_now, fl.longitude AS longitude_now, fl.updated_at AS loc_update, fl.timezone, fd_max.real_time AS latest_real_time, fbat.batteryLevel,
        CASE 
            WHEN fbat.batteryState = 3 THEN 'batFull'
            WHEN fbat.batteryLevel <= 20 THEN 'batDanger'
            WHEN fbat.batteryLevel <= 60 THEN 'batWarm'
            ELSE 'batSuccess'
        END AS batteryClass,
        CASE 
            WHEN (fbat.batteryState = 1 OR fbat.batteryState = 3) AND fbat.batteryLevel >= 21 THEN ''
            WHEN fbat.batteryState = 1 AND fbat.batteryLevel <= 20 THEN '!'
            WHEN fbat.batteryState = 2 THEN '&#9889;'
            ELSE ''
        END AS batteryStateOk
        FROM face_devices fd
        LEFT JOIN 
            face_veiculo fv ON fv.device_id = fd.device_id
        LEFT JOIN 
            veiculos veic ON veic.ID_ORIGIN = fv.veiculo_id
        LEFT JOIN 
            face_location fl ON fl.device_id = fd.device_id
        LEFT JOIN (
            SELECT 
                device_id, 
                MAX(real_time) AS real_time
            FROM 
                face_recognitions
            WHERE 
                deleted_at IS NULL AND real_time >= '{$formattedDate}'
            GROUP BY 
                device_id
        ) AS fd_max ON fd_max.device_id = fd.device_id
        LEFT JOIN
            face_battery fbat ON fbat.device_id = fd.device_id
        WHERE fd.deleted_at IS NULL {$w} ORDER BY fd.device_id, fd.ativo ASC LIMIT {$limPag} {$offset}");

        $getDevices->execute();
        $devices = $getDevices->fetchAll(PDO::FETCH_OBJ);

        $getSelectDevices = $this->db->prepare("SELECT id, device_id, model FROM face_devices WHERE deleted_at IS NULL");
        $getSelectDevices->execute();

        $selDevices = $getSelectDevices->fetchAll(PDO::FETCH_OBJ);

        $modelos = [];

        $getUniqueModels = $this->db->prepare("SELECT DISTINCT model FROM face_devices WHERE deleted_at IS NULL");
        $getUniqueModels->execute();
        $uniqueModels = $getUniqueModels->fetchAll(PDO::FETCH_OBJ);

        foreach ($uniqueModels as $model) {
            $img = $this->createDeviceImage($model->model);
            $model->img = $img;
        }

        $modelos = $uniqueModels;

        $selectVersion = [];
        $getUniqueVersions = $this->db->prepare("SELECT DISTINCT app_version FROM face_devices WHERE deleted_at IS NULL ORDER BY app_version ASC");
        $getUniqueVersions->execute();
        $selectVersion = $getUniqueVersions->fetchAll(PDO::FETCH_OBJ);

        return array ( "devices" => $devices, "selDevices" => $selDevices, "modelos" => $modelos, "selectVersion" => $selectVersion, "total" => $ttPages );

    }

    public function activeInactive($id, $ativo)
    {
        $now = date("Y-m-d H:i:s");

        $updateDevice = $this->db->prepare("UPDATE face_devices SET updated_at = '$now', ativo = :ativo WHERE id = :id");
        $updateDevice->bindParam(':id', $id);
        $updateDevice->bindParam(':ativo', $ativo);

        try {

            $updateDevice->execute();

            return ['status' => true];

            
        } catch (\Throwable $th) {
            return ['status' => false];
        }
    }

    public function switchCirc($id, $circular)
    {
        $now = date("Y-m-d H:i:s");

        $updateDevice = $this->db->prepare("UPDATE face_devices SET updated_at = '$now', circular = :circular WHERE id = :id");
        $updateDevice->bindParam(':id', $id);
        $updateDevice->bindParam(':circular', $circular);

        try {

            $updateDevice->execute();

            return ['status' => true];

            
        } catch (\Throwable $th) {
            return ['status' => false];
        }
    }

    public function requestConfig($post)
    {

        $device_id = $post['device_id'];
        $config_type = $post['config_type'];

        $errorMsg = "Erro ao se conectar ao aparelho";

        $errorMsg .= $config_type == 0 ? " ou já está na tela de configurações" : " ou não está na tela de configurações";

        $errorMsg .= "\n Tente novamente.";

        $okMsg = $config_type == 0 ? "Tela de Detecções aberta com sucesso!" : "Tela de Configurações aberta com sucesso!";

        $now = date("Y-m-d H:i:s");
        $insertRequest = $this->db->prepare("INSERT INTO face_configs (device_id, config_type, created_at) VALUES (:device_id, :config_type, '$now')");
        $insertRequest->bindValue(":device_id", $device_id);
        $insertRequest->bindValue(":config_type", $config_type);


        try {
           
            $insertRequestSuccess = $insertRequest->execute();

            if($insertRequestSuccess){

                $lastInsertId = $this->db->lastInsertId();

                $startTime = time();
                $maxTimeTryConnection = 60;

                $hasConection = false;

                while (time() - $startTime <= $maxTimeTryConnection) {

                    sleep(5);

                    $checkResult = $this->db->query("SELECT deleted_at FROM face_configs WHERE id = $lastInsertId")->fetch();

                    if ($checkResult && $checkResult['deleted_at'] != null) {

                        $hasConection = true;

                        break;

                    }
                }

                if (!$hasConection) {
                    $this->db->query("DELETE FROM face_configs WHERE id = $lastInsertId");
                    return ['status' => false, 'msg' => $errorMsg];
                }

                return ['status' => true, 'msg' => $okMsg];

            }else{
                return ['status' => false, 'msg' => "Erro ao enviar solicitação, tente novamente."];
            }

        } catch (\Throwable $th) {
            return ['status' => false, 'msg' => "Erro ao enviar solicitação, tente novamente."];
        }

    }

    public function requestDetections($post)
    {
        $device_id = $post['device_id'];
        $now = date("Y-m-d H:i:s");
        $recCount = 0;

        try {
            
            $checkExisting = $this->db->prepare("SELECT COUNT(*) as count, MAX(created_at) as last_created_at FROM face_request_info WHERE device_id = :device_id");
            $checkExisting->bindValue(":device_id", $device_id);
            $checkExisting->execute();

            $result = $checkExisting->fetch();
            $existingCount = $result['count'];
            $lastTimestamp = $result['last_created_at'];

            if ($existingCount == 1) {
                
                $currentTime = time();

                if ($currentTime - strtotime($lastTimestamp) <= 300) {

                    return ['status' => false, 'msg' => "O aparelho: $device_id está enviando as reconhecimentos no momento. Aguarde para uma nova solicitação.", 'icon' => 'warning', 'title' => 'AVISO'];

                } else {

                    $deleteOldRecords = $this->db->prepare("DELETE FROM face_request_info WHERE device_id = :device_id AND created_at <= :lastTimestamp");
                    $deleteOldRecords->bindValue(":device_id", $device_id);
                    $deleteOldRecords->bindValue(":lastTimestamp", $lastTimestamp);
                    $deleteOldRecords->execute();
                }

            }else{

                $insertRequest = $this->db->prepare("INSERT INTO face_request_info (device_id, created_at) VALUES (:device_id, '$now')");
                $insertRequest->bindValue(":device_id", $device_id);

                $insertRequestSuccess = $insertRequest->execute();

                if ($insertRequestSuccess) {
                    
                    $lastInsertId = $this->db->lastInsertId();
                    $startTime = time();
                    $maxTimeTryConnection = 30;

                    $hasConection = false;

                    while (time() - $startTime <= $maxTimeTryConnection) {

                        sleep(5);
        
                        $checkResult = $this->db->query("SELECT retorno, recCount FROM face_request_info WHERE id = $lastInsertId")->fetch();
        
                        if ($checkResult && $checkResult['retorno'] != 0) {
        
                            $hasConection = true;

                            if ($checkResult['retorno'] == 2) {
        
                                $msgRetorno = "O Aparelho: $device_id não tem reconhecimentos para enviar no momento.";
                                $icon = 'warning';  
                                $title = 'AVISO';

                            }else{

                                $icon = 'success';
                                $title = 'SUCESSO'; 
                                $recCount = $checkResult['recCount'];
                                $word = ($recCount > 1) ? 'reconhecimentos' : 'reconhecimento';
                                $msgRetorno = "Solicitação enviada para o Aparelho: $device_id!\nAparelho enviando $recCount $word.";

                            }
                            
                            $this->db->query("DELETE FROM face_request_info WHERE id = $lastInsertId");

                            break;
        
                        }
                    }

                    if (!$hasConection) {
                        $this->db->query("DELETE FROM face_request_info WHERE id = $lastInsertId");
                        return ['status' => false, 'msg' => "Erro ao se comunicar com o Aparelho: $device_id.\nO aparelho pode estar sem conexão ou na tela de Configurações."];
                    }

                    return ['status' => true, 'msg' => $msgRetorno, 'icon' => $icon, 'title' => $title, 'recCount' => $recCount];

                }else{

                    return ['status' => false, 'msg' => "Erro ao enviar solicitação, tente novamente."];

                }

            }

        } catch (\Throwable $th) {
            return ['status' => false, 'msg' => "Erro ao criar solicitação, tente novamente."];
        }
    }

    public function getVeicAndLocation($post)
    {
        $device_id = $post['device_id'];

        $now = new DateTime();
        $now->modify('-72 hours');
        $formattedDate = $now->format('Y-m-d H:i:s');

        try {

            $getDevice = $this->db->prepare("SELECT fd.device_id, fd.cad AS isCad, fv.veiculo_id, fv.updated_at AS veic_update, CONCAT(veic.NOME, ' - ', veic.PLACA) AS VEICULO, fl.latitude AS latitude_now, fl.longitude AS longitude_now, fl.updated_at AS loc_update, fl.timezone, frec.real_time AS more72_rec, fbat.batteryLevel,
            CASE 
                WHEN fl.updated_at >= '{$formattedDate}' THEN 'mais'
                ELSE 'menos'
            END AS more72,
            CASE 
                WHEN fbat.batteryState = 3 THEN 'batFull'
                WHEN fbat.batteryLevel <= 20 THEN 'batDanger'
                WHEN fbat.batteryLevel <= 60 THEN 'batWarm'
                ELSE 'batSuccess'
            END AS batteryClass,
            CASE 
                WHEN (fbat.batteryState = 1 OR fbat.batteryState = 3) AND fbat.batteryLevel >= 21 THEN ''
                WHEN fbat.batteryState = 1 AND fbat.batteryLevel <= 20 THEN '!'
                WHEN fbat.batteryState = 2 THEN '&#9889;'
                ELSE ''
            END AS batteryStateOk
            FROM face_devices fd
            LEFT JOIN 
                face_veiculo fv ON fv.device_id = fd.device_id
            LEFT JOIN 
                veiculos veic ON veic.ID_ORIGIN = fv.veiculo_id
            LEFT JOIN 
                face_location fl ON fl.device_id = fd.device_id
            LEFT JOIN (
                SELECT 
                    device_id, 
                    MAX(real_time) AS real_time
                FROM 
                    face_recognitions
                WHERE 
                    deleted_at IS NULL AND real_time >= '{$formattedDate}'
                GROUP BY 
                    device_id
            ) AS fd_max ON fd_max.device_id = fd.device_id
            LEFT JOIN
                face_recognitions frec ON frec.device_id = fd_max.device_id AND frec.real_time = fd_max.real_time
            LEFT JOIN
                face_battery fbat ON fbat.device_id = fd.device_id
            WHERE fd.device_id = '{$device_id}' LIMIT 1");

            $getDevice->execute();
            if ($getDevice->rowCount() == 1) {

                $device = $getDevice->fetch();

                $more72 = false;
                $more72_rec = false;
                $veic_update = null;

                if($device['isCad'] == 0){
                    $more72 = ($device['more72'] == 'menos') ? true : false;
                    $more72_rec = (isset($device['more72_rec'])) ? false : true;

                    
                    if(isset($device['veic_update'])){
                        $veic_update = date("d/m/Y - H:i", strtotime($device['veic_update']));
                    }
                }

                $loc_update = null;
                if(isset($device['loc_update'])){
                    $loc_update = date("d/m/Y - H:i", strtotime($device['loc_update']));
                }

                return [
                    'status' => true,
                    'device_id' => $device['device_id'],
                    'VEICULO' => $device['VEICULO'],
                    'veic_update' => $veic_update,
                    'latitude_now' => $device['latitude_now'],
                    'longitude_now' => $device['longitude_now'],
                    'loc_update' => $loc_update,
                    'timezone' => $device['timezone'],
                    'more72' => $more72,
                    'more72_rec' => $more72_rec,
                    'batteryLevel' => $device['batteryLevel'],
                    'batteryState' => $device['batteryStateOk'],
                    'batteryClass' => $device['batteryClass'],
                ];

            }else{
                return ['status' => false];
            }

        } catch (\Throwable $th) {

            return ['status' => false];

        }
    }

    public function getRecognitionsFace($post)
    {

        $device_id  = $post['device_id'];
        $data = $post['data'];

        $start = $data . " 00:00:00";
		$end = $data . " 23:59:59";

        $dataInfo = date("d/m/Y", strtotime($data));

        $noRecognitionMsg = "Nenhum reconhecimento encontrado em $dataInfo";

        $getRecognitions = $this->db->prepare("SELECT 
                face_recognitions.id, face_recognitions.faceID, face_recognitions.controle_acesso_id, face_recognitions.img, face_recognitions.real_time,
                controle_acessos.NOME
            FROM 
                face_recognitions 
            LEFT JOIN controle_acessos ON controle_acessos.id = face_recognitions.controle_acesso_id
            WHERE 
                face_recognitions.device_id = :device_id 
                AND face_recognitions.real_time BETWEEN :start AND :end
                AND face_recognitions.deleted_at IS NULL 
            ORDER BY 
                face_recognitions.real_time
        ");

        $getRecognitions->bindParam(':device_id', $device_id);
        $getRecognitions->bindParam(':start', $start);
        $getRecognitions->bindParam(':end', $end);

        try{

            $getRecognitions->execute();

            if ($getRecognitions->rowCount() > 0) {

                $recognitions = $getRecognitions->fetchAll(PDO::FETCH_OBJ);

                // $recognitionsArray = [];
                // $seenRecognitions = [];

                foreach ($recognitions as $key => $recognition) {

                    // if ($recognition->controle_acesso_id == 0) {
                    //     if(isset($recognitions[$key + 1]) && $recognitions[$key + 1]->controle_acesso_id == 0){
                    //         $timeNext = strtotime($recognitions[$key + 1]->real_time);
                    //         $timeDifference = strtotime($recognition->real_time) - $timeNext;

                    //         if (abs($timeDifference) < 10) {
                    //             $now = date("Y-m-d H:i:s");
                    //             $deletedRecogntion = $this->db->prepare("UPDATE face_recognitions 
                    //                                                 SET deleted_at = '$now' 
                    //                                                 WHERE id = :id");
                    //             $deletedRecogntion->bindParam(':id', $recognition->id);
                    //             $deletedRecogntion->execute();
                    //             continue;
                    //         }
                    //     }
                    // }

                    // if ($recognition->controle_acesso_id != 0) {
                        
                    //     if (isset($seenRecognitions[$recognition->controle_acesso_id])) {
                            
                    //         $timeDifference = strtotime($recognition->real_time) - strtotime($seenRecognitions[$recognition->controle_acesso_id]);
                            
                    //         if (abs($timeDifference) < 300) {
                    //             $now = date("Y-m-d H:i:s");
                    //             $deletedRecogntion = $this->db->prepare("UPDATE face_recognitions 
                    //                                                 SET deleted_at = '$now' 
                    //                                                 WHERE id = :id");
                    //             $deletedRecogntion->bindParam(':id', $recognition->id);
                    //             $deletedRecogntion->execute();
                    //             continue;
                                
                    //         }
                    //     }
                        
                       
                    //     $seenRecognitions[$recognition->controle_acesso_id] = $recognition->real_time;
                    // }

                    $nome = "Não Reconhecido";

                    if($recognition->controle_acesso_id != 0 && isset($recognition->NOME)){
                        $nome = $recognition->NOME;
                    }

                    $recognitionsArray[] = [
                        'id' => $recognition->id,
                        'img' => base64_encode($recognition->img),
                        'real_time' => $recognition->real_time,
                        'formated_time' => date("d/m/Y - H:i:s", strtotime($recognition->real_time)),
                        'controle_acesso_id' => $recognition->controle_acesso_id,
                        'nome' => $nome
                    ];
                
                }              

                $retorno = array('status' => true, 'recognitions' => $recognitionsArray);
                

            } else {
                $retorno = array('status' => false, 'msg' => $noRecognitionMsg, 'title' => $device_id, 'icon' => 'warning');
            }


        } catch (\Throwable $th) {
            $retorno = array('status' => false, 'msg' => 'Erro ao encontrar reconhecimentos');
        }

        return $retorno;
    }

    public function getTryAgainFace($post)
    {

        $device_id  = $post['device_id'];
        $data = $post['data'];

        $start = $data . " 00:00:00";
		$end = $data . " 23:59:59";

        $dataInfo = date("d/m/Y", strtotime($data));

        $noRecognitionMsg = "Nenhum Tente Novamente encontrado em $dataInfo";

        $getRecognitions = $this->db->prepare("SELECT 
                id, faceID, img, real_time
            FROM 
                face_try_again 
            WHERE 
                device_id = :device_id 
                AND real_time BETWEEN :start AND :end
                AND deleted_at IS NULL 
            ORDER BY 
                real_time
        ");

        $getRecognitions->bindParam(':device_id', $device_id);
        $getRecognitions->bindParam(':start', $start);
        $getRecognitions->bindParam(':end', $end);

        try{

            $getRecognitions->execute();

            if ($getRecognitions->rowCount() > 0) {

                $recognitions = $getRecognitions->fetchAll(PDO::FETCH_OBJ);

                $recognitionsArray = [];
                $seenRecognitions = [];

                foreach ($recognitions as $key => $recognition) {

                    $recognitionsArray[] = [
                        'id' => $recognition->id,
                        'img' => base64_encode($recognition->img),
                        'real_time' => $recognition->real_time,
                        'formated_time' => date("d/m/Y - H:i:s", strtotime($recognition->real_time))
                    ];
                
                }              

                $retorno = array('status' => true, 'recognitions' => $recognitionsArray);
                

            } else {
                $retorno = array('status' => false, 'msg' => $noRecognitionMsg, 'title' => $device_id, 'icon' => 'warning');
            }


        } catch (\Throwable $th) {
            $retorno = array('status' => false, 'msg' => 'Erro ao encontrar reconhecimentos');
        }

        return $retorno;
    }

    public function updateFaceCar($post)
    {

        $retorno = [
            'status' => true,
            'veic_update' => null,
        ];

        $now = date("Y-m-d H:i:s"); 

        $device_id  = $post['device_id'];
        $veiculo_id  = $post['veiculo_id'];

        try {

            $getDeviceVeicNot = $this->db->prepare("SELECT * FROM face_veiculo WHERE device_id <> :device_id AND veiculo_id = :veiculo_id LIMIT 1");
            $getDeviceVeicNot->bindParam(':device_id', $device_id);
            $getDeviceVeicNot->bindParam(':veiculo_id', $veiculo_id);
            $getDeviceVeicNot->execute();

            if($getDeviceVeicNot->rowCount() == 1) {

                $other = $getDeviceVeicNot->fetch(PDO::FETCH_OBJ);

                return array('status' => false, 'msg' => "Veículo já está em uso pelo aparelho $other->device_id", 'title' => $device_id, 'icon' => 'warning');

            }
            
            $getDeviceVeic = $this->db->prepare("SELECT * FROM face_veiculo WHERE device_id = :device_id");
            $getDeviceVeic->bindParam(':device_id', $device_id);

            $getDeviceVeic->execute();

            if($getDeviceVeic->rowCount() == 0) {

                $insertDeviceVeic = $this->db->prepare("INSERT INTO face_veiculo (device_id, veiculo_id, updated_at) VALUES (:device_id, :veiculo_id, '$now')");
                $insertDeviceVeic->bindValue(":device_id", $device_id);
                $insertDeviceVeic->bindValue(":veiculo_id", $veiculo_id);
                $insertDeviceVeic->execute(); 

            }else{

                $updateDeviceVeic = $this->db->prepare("UPDATE face_veiculo SET veiculo_id = :veiculo_id, updated_at = '$now' WHERE device_id = :device_id");
                $updateDeviceVeic->bindParam(':veiculo_id', $veiculo_id);
                $updateDeviceVeic->bindParam(':device_id', $device_id);
                $updateDeviceVeic->execute(); 

            }            

            $veic_update = date("d/m/Y - H:i", strtotime($now));
            $retorno['veic_update'] = $veic_update;

        } catch (\Throwable $th) {
            $retorno['status'] = false;
        }

        return $retorno;

    }

    public function changeTintColor($post){

        $id = $post['deviceId'];
        $color = $post['color'];

        $now = date("Y-m-d H:i:s");

        $updateDevice = $this->db->prepare("UPDATE face_devices SET updated_at = '$now', tintColorTarget = :tintColorTarget WHERE id = :id");
        $updateDevice->bindParam(':id', $id);
        $updateDevice->bindParam(':tintColorTarget', $color);

        try {

            $updateDevice->execute();

            return ['status' => true];

            
        } catch (\Throwable $th) {
            return ['status' => false];
        } 

    }

    private function createDeviceImage($model)
    {

        //verificar se já tem imagem do modelo do aparelho
        $hasImage = '.'.PICSDEVICES . 'semimagem.gif';
        $fileName =  $model.'.png';
        $filePath = '.'.PICSDEVICES . $fileName;

        if (!file_exists($filePath)) {

            $searchOk = true;
            $parameter 	= new Parametro();
            $param    	= $parameter->getParametros();
            $apiKey = ($param['apiKey_active'] == 1) ? BACKKEYGOOGLE : 'xxxxxxxxxxxxxxxxxxxxx';
            $searchEngineId = SEARCHENGINEID;
            $siteFilter = 'phonemore.com';
            $url = "https://www.googleapis.com/customsearch/v1?q={$model}&cx={$searchEngineId}&searchType=image&key={$apiKey}&siteSearch={$siteFilter}";

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($curl);

            if(curl_errno($curl)){
                $searchOk = false;
            }

            curl_close($curl);
            $data = json_decode($response, true);

            if(isset($data['error'])){
                $searchOk = false;
            }

            if($searchOk){
                if(isset($data['items']) && count($data['items']) > 0){
                    $link = $data['items'][0]['link'];
                    $img = file_get_contents($link);
                    $imagem = imagecreatefromstring($img);
                    if($img !== false){
                       
                        imagepng($imagem, $filePath);
                        imagedestroy($imagem);
                        $hasImage = $filePath;
            
                    }
                }
            }                
            
        }else{
            $hasImage = $filePath;
        }

        return $hasImage;

    }

}
?>