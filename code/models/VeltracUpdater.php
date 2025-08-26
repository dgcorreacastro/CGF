<?php

ini_set('memory_limit', '-1');
set_time_limit(0);
date_default_timezone_set('America/Sao_Paulo');

class VeltracUpdater extends model 
{
    private $dbSys;
    private $pdo;
    private $requestType;
    private $userId;
    private $saveDebug;

    public function __construct($rqType = 'CRON', $usId = 0, $svDeb = true) {

        $this->setRequestType($rqType);
        $this->setUserId($usId);
        $this->setSaveDebug($svDeb);

        if ($this->requestType === 'CRON') {
            require '../environment.php';
        }        
    
        require_once  __DIR__ . '/../helpers/cnn.php';
        require_once  __DIR__ . '/../helpers/debug.php';

        $this->dbSys = $dbSys;
        $this->pdo = $pdo;
        
        if ($this->requestType === 'CRON') {
            $this->callCronFunctions();
        }

    }

    public function setUserId($userId) {
        $this->userId = $userId;
    }

    public function setRequestType($requestType) {
        $this->requestType = $requestType;
    }

    public function setSaveDebug($saveDebug) {
        $this->saveDebug = $saveDebug;
    }

    ##################################################################################
    ######## CHAMAR AS FUNÇÕES DE ACORDO COM PARÂMETROS QUANDO FOR CRON ##############
    ##################################################################################
    private function callCronFunctions(){

        $getParameters = $this->dbSys->prepare("SELECT get_veic_veltrac, get_gr_veltrac, get_cag_veltrac, get_linha_veltrac, get_iti_veltrac, get_pax_veltrac, get_trips_veltrac, get_tag_veltrac FROM parametros WHERE id = 1");
        $getParameters->execute();
        $findToUpdate = $getParameters->fetch();

        $this->insertDebug('CRON', 'INICIO VELTRAC CRON');

        if(isset($findToUpdate['get_veic_veltrac']) && $findToUpdate['get_veic_veltrac'] == 1){
            $resultUpdateVeiculos = $this->updateVeiculos();

            if(!$resultUpdateVeiculos['success']){                
                $this->insertDebug('CRON', 'FINAL ERRO VELTRAC CRON');
                return false;
            }
        }

        if(isset($findToUpdate['get_gr_veltrac']) && $findToUpdate['get_gr_veltrac'] == 1){
            $resultUpdateGRLinhas = $this->updateGRLinhas();

            if(!$resultUpdateGRLinhas['success']){
                $this->insertDebug('CRON', 'FINAL ERRO VELTRAC CRON');
                return false;
            }
        }

        if(isset($findToUpdate['get_cag_veltrac']) && $findToUpdate['get_cag_veltrac'] == 1){
            $resultUpdateCAGrupo = $this->updateCAGrupo();
            if(!$resultUpdateCAGrupo['success']){
                $this->insertDebug('CRON', 'FINAL ERRO VELTRAC CRON');
                return false;
            }
        }

        if(isset($findToUpdate['get_linha_veltrac']) && $findToUpdate['get_linha_veltrac'] == 1){
            $resultUpdateLinhas = $this->updateLinhas();
            if(!$resultUpdateLinhas['success']){
                $this->insertDebug('CRON', 'FINAL ERRO VELTRAC CRON');
                return false;
            }
        }

        if(isset($findToUpdate['get_iti_veltrac']) && $findToUpdate['get_iti_veltrac'] == 1){
            $resultUpdateItine = $this->updateItine();
            if(!$resultUpdateItine['success']){
                $this->insertDebug('CRON', 'FINAL ERRO VELTRAC CRON');
                return false;
            }
        }

        if(isset($findToUpdate['get_pax_veltrac']) && $findToUpdate['get_pax_veltrac'] == 1){
            $resultUpdateCA = $this->updateCA();
            if(!$resultUpdateCA['success']){
                $this->insertDebug('CRON', 'FINAL ERRO VELTRAC CRON');
                return false;
            }
        }

        if(isset($findToUpdate['get_trips_veltrac']) && $findToUpdate['get_trips_veltrac'] == 1){
            $resultViagens = $this->updateViagens();
            if(!$resultViagens['success']){
                $this->insertDebug('CRON', 'FINAL ERRO VELTRAC CRON');
                return false;
            }
        }

        if(isset($findToUpdate['get_tag_veltrac']) && $findToUpdate['get_tag_veltrac'] == 1){
            $getGroups = $this->getGroups();
            if(!$getGroups['success']){
                $this->insertDebug('CRON', $getGroups['msg']);
                return false;
            }
        }

        $this->insertDebug('CRON', 'FINAL OK VELTRAC CRON');
            
    }

    ##################################################################################
    ########################### ATUALIZANDO OS CARROS ################################
    ##################################################################################
    public function updateVeiculos()
    {

        $motiveDebug = $this->requestType;
        if($this->userId !== 0){
            $motiveDebug .= " - $this->userId";
        }

        $contentIni = "INICIO VEICULO";
        $contentEnd = "FINAL VEICULO";

        $errorAll = "$motiveDebug - ERRO VEICULO";
        $erroUpdateVeic = "$motiveDebug - ERRO UPDATE VEICULO";
        $erroInsertVeic = "$motiveDebug - ERRO INSERT VEICULO";

        $msgSuccessAjax = "Veículos atualizados com sucesso!";
        $msgErrorAjax = "Erro ao atualizar Veículos, tente novamente.";

        $this->insertDebug($motiveDebug, $contentIni);

        try {
            $getVeicVeltrac = "SELECT ID, CLIENTE_ID, TIPOVEICULO, MARCA, MODELO, NOME, ANO, PLACA, CAPACIDADE_LIMIT_PASSAGEIROS, CAPACIDADE_PASSAGEIROS, ATIVO, GRUPO_ID FROM VEICULO WHERE ATIVO = 1";

            $consulta = $this->pdo->query($getVeicVeltrac);
            $datas = $consulta->fetchAll();

            foreach ($datas as $ddr) {
                
                $ddr = (object) $ddr;
                $carro_id = 0;
                $nowReg = date("Y-m-d H:i:s");

                $getVeic = $this->dbSys->prepare("SELECT * FROM veiculos WHERE ID_ORIGIN = {$ddr->ID} ORDER BY id DESC LIMIT 1");
                $getVeic->execute();
                $hasVeic = $getVeic->fetch(PDO::FETCH_OBJ);

                if ($hasVeic) {
                    $updateVeic = $this->dbSys->prepare("UPDATE veiculos SET CLIENTE_ID = :CLIENTE_ID, TIPOVEICULO = :TIPOVEICULO, MARCA = :MARCA, MODELO = :MODELO, NOME = :NOME, ANO = :ANO, PLACA = :PLACA, CAPACIDADE_LIMIT_PASSAGEIROS = :CAPACIDADE_LIMIT_PASSAGEIROS, CAPACIDADE_PASSAGEIROS = :CAPACIDADE_PASSAGEIROS, ATIVO = :ATIVO, GRUPO_ID = :GRUPO_ID, updated_at = '$nowReg' where id = :id");
                    $updateVeic->bindValue(":CLIENTE_ID", $ddr->CLIENTE_ID);
                    $updateVeic->bindValue(":TIPOVEICULO", $ddr->TIPOVEICULO);
                    $updateVeic->bindValue(":MARCA", $ddr->MARCA);
                    $updateVeic->bindValue(":MODELO", $ddr->MODELO);
                    $updateVeic->bindValue(":NOME", $ddr->NOME);
                    $updateVeic->bindValue(":ANO", $ddr->ANO);
                    $updateVeic->bindValue(":PLACA", $ddr->PLACA);
                    $updateVeic->bindValue(":CAPACIDADE_LIMIT_PASSAGEIROS", $ddr->CAPACIDADE_LIMIT_PASSAGEIROS);
                    $updateVeic->bindValue(":CAPACIDADE_PASSAGEIROS", $ddr->CAPACIDADE_PASSAGEIROS);
                    $updateVeic->bindValue(":ATIVO", $ddr->ATIVO);
                    $updateVeic->bindValue(":GRUPO_ID", $ddr->GRUPO_ID);
                    $updateVeic->bindValue(":id", $hasVeic->id);

                    try {
                        $updateVeic->execute();
                    } catch (\Throwable $th) {
                        $this->insertDebug($erroUpdateVeic, addslashes($th));
                        return array('success' => false, 'msg' => $msgErrorAjax);
                    }

                    $carro_id = $hasVeic->id;

                } else {
                    $insertVeic = $this->dbSys->prepare("INSERT INTO veiculos SET ID_ORIGIN = :ID_ORIGIN, CLIENTE_ID = :CLIENTE_ID, TIPOVEICULO = :TIPOVEICULO, MARCA = :MARCA, MODELO = :MODELO, NOME = :NOME, ANO = :ANO, PLACA = :PLACA, CAPACIDADE_LIMIT_PASSAGEIROS = :CAPACIDADE_LIMIT_PASSAGEIROS, CAPACIDADE_PASSAGEIROS = :CAPACIDADE_PASSAGEIROS, ATIVO = :ATIVO,  GRUPO_ID = :GRUPO_ID, created_at = '$nowReg'");
                    $insertVeic->bindValue(":ID_ORIGIN", $ddr->ID);
                    $insertVeic->bindValue(":CLIENTE_ID", $ddr->CLIENTE_ID);
                    $insertVeic->bindValue(":TIPOVEICULO", $ddr->TIPOVEICULO);
                    $insertVeic->bindValue(":MARCA", $ddr->MARCA);
                    $insertVeic->bindValue(":MODELO", $ddr->MODELO);
                    $insertVeic->bindValue(":NOME", $ddr->NOME);
                    $insertVeic->bindValue(":ANO", $ddr->ANO);
                    $insertVeic->bindValue(":PLACA", $ddr->PLACA);
                    $insertVeic->bindValue(":CAPACIDADE_LIMIT_PASSAGEIROS", $ddr->CAPACIDADE_LIMIT_PASSAGEIROS);
                    $insertVeic->bindValue(":CAPACIDADE_PASSAGEIROS", $ddr->CAPACIDADE_PASSAGEIROS);
                    $insertVeic->bindValue(":ATIVO", $ddr->ATIVO);
                    $insertVeic->bindValue(":GRUPO_ID", $ddr->GRUPO_ID);

                    try {
                        $insertVeic->execute();
                        $carro_id = $this->dbSys->lastInsertId();
                    } catch (\Throwable $th) {
                        $this->insertDebug($erroInsertVeic, addslashes($th));
                        return array('success' => false, 'msg' => $msgErrorAjax);
                    }
                }

                if ($carro_id > 0) {

                    $users = $this->getUsers();

                    if($users){
                        foreach ($users as $user) {
                            $insertPerm = $this->insertPermVeic($user->id, $carro_id, $motiveDebug);
                            if(!$insertPerm){
                                return array('success' => false, 'msg' => $msgErrorAjax);
                            }
                            usleep(500);
                        }
                    }
                   
                }

                usleep(500);
            }
        } catch (\Throwable $th) {
            $this->insertDebug($errorAll, addslashes($th));
            return array('success' => false, 'msg' => $msgErrorAjax);
        }

        $this->insertDebug($motiveDebug, $contentEnd);

        return array('success' => true, 'msg' => $msgSuccessAjax);

    }

    ##################################################################################
    ##################### ATUALIZANDO OS GRUPOS DE LINHAS ############################
    ##################################################################################
    public function updateGRLinhas()
    {
        $motiveDebug = $this->requestType;
        if($this->userId !== 0){
            $motiveDebug .= " - $this->userId";
        }

        $contentIni = "INICIO GR LINHAS";
        $contentEnd = "FINAL GR LINHAS";

        $errorAll = "$motiveDebug - ERRO GR LINHAS";
        $erroUpdateGRL = "$motiveDebug - ERRO UPDATE GR LINHAS";
        $erroInsertGRL = "$motiveDebug - ERRO INSERT GR LINHAS";

        $msgSuccessAjax = "Grupo Linhas atualizados com sucesso!";
        $msgErrorAjax = "Erro ao atualizar Grupo Linhas, tente novamente.";

        $this->insertDebug($motiveDebug, $contentIni);

        try {

            $inactiveGroups = $this->getInactiveGroups();

            $getGRLVeltrac  = "SELECT ID, NOME FROM GRUPO_LINHAS";
            $consulta       = $this->pdo->query($getGRLVeltrac);
            $datas          = $consulta->fetchAll();

            foreach($datas AS $ddr)
            {
                $ddr = (Object) $ddr;
                $nowReg = date("Y-m-d H:i:s");

                $getGRL = $this->dbSys->prepare("SELECT * FROM grupo_linhas WHERE ID_ORIGIN = {$ddr->ID} ORDER BY id DESC LIMIT 1");
                $getGRL->execute();
                $hasGRL = $getGRL->fetch(PDO::FETCH_OBJ);

                if($hasGRL){

                    /// TRATANDO QUANDO HOUVER GRUPOS INATIVOS \\\
                    if(in_array($ddr->ID, $inactiveGroups)){

                        $updateGR = $this->dbSys->prepare("UPDATE grupo_linhas SET deleted_at = '$nowReg' where ID_ORIGIN = :ID_ORIGIN");
                        
                    }else{

                        $updateGR = $this->dbSys->prepare("UPDATE grupo_linhas SET NOME = :NOME, deleted_at = NULL, updated_at = '$nowReg' where ID_ORIGIN = :ID_ORIGIN");
                        $updateGR->bindValue(":NOME", $ddr->NOME);
                        
                    }

                    $updateGR->bindValue(":ID_ORIGIN", $ddr->ID);

                    try {
                        $updateGR->execute();
                    } catch (\Throwable $th) {
                        $this->insertDebug($erroUpdateGRL, addslashes($th));
                        return array('success' => false, 'msg' => $msgErrorAjax);
                    }

                } else {

                    /// SÓ ADICIONA SE NÃO ESTIVER NOS GRUPOS INATIVOS \\\
                    if(!in_array($ddr->ID, $inactiveGroups)){
                        $insertGRL = $this->dbSys->prepare("INSERT INTO grupo_linhas SET ID_ORIGIN = :ID_ORIGIN, NOME = :NOME, created_at = '$nowReg'");
                        $insertGRL->bindValue(":ID_ORIGIN", $ddr->ID);
                        $insertGRL->bindValue(":NOME", $ddr->NOME);

                        try {

                            $insertGRL->execute();

                        } catch (\Throwable $th) {
                            $this->insertDebug($erroInsertGRL, addslashes($th));
                            return array('success' => false, 'msg' => $msgErrorAjax);
                        }
                    }

                }

                usleep(500);

            }

        } catch (\Throwable $th) {
            $this->insertDebug($errorAll, addslashes($th));
            return array('success' => false, 'msg' => $msgErrorAjax);
        }

        $this->insertDebug($motiveDebug, $contentEnd);

        return array('success' => true, 'msg' => $msgSuccessAjax);
    }

    ##################################################################################
    ################### ATUALIZANDO GRUPO CONTROLE ACESSO ############################
    ##################################################################################
    public function updateCAGrupo()
    {
        $motiveDebug = $this->requestType;
        if($this->userId !== 0){
            $motiveDebug .= " - $this->userId";
        }

        $contentIni = "INICIO GR CONTROLE ACESSO";
        $contentEnd = "FINAL GR CONTROLE ACESSO";

        $errorAll = "$motiveDebug - ERRO GR CONTROLE ACESSO";
        $erroUpdateCAG = "$motiveDebug - ERRO UPDATE GR CONTROLE ACESSO";
        $erroInsertCAG = "$motiveDebug - ERRO INSERT GR CONTROLE ACESSO";

        $msgSuccessAjax = "Grupo Controle Acesso atualizados com sucesso!";
        $msgErrorAjax = "Erro ao atualizar Grupo Controle Acesso, tente novamente.";

        $this->insertDebug($motiveDebug, $contentIni);

        try {
            
            $getCAGVeltrac  = "SELECT * FROM CONTROLE_ACESSO_GRUPO";
            $consulta       = $this->pdo->query($getCAGVeltrac);
            $datas          = $consulta->fetchAll();

            foreach($datas AS $ddr)
            {
                $ddr = (Object) $ddr;

                $nowReg = date("Y-m-d H:i:s");
                
                $getCAG = $this->dbSys->prepare("SELECT * FROM acesso_grupos WHERE ID_ORIGIN = {$ddr->ID} ORDER BY id DESC LIMIT 1");
                $getCAG->execute();
                $hasCAG = $getCAG->fetch(PDO::FETCH_OBJ);

                if($hasCAG){

                    $updateCAG = $this->dbSys->prepare("UPDATE acesso_grupos SET NOME = :NOME, updated_at = '$nowReg' where ID_ORIGIN = :ID_ORIGIN");
                    $updateCAG->bindValue(":NOME", $ddr->NOME);
                    $updateCAG->bindValue(":ID_ORIGIN", $ddr->ID);

                    try {
                        $updateCAG->execute();
                    } catch (\Throwable $th) {
                        $this->insertDebug($erroUpdateCAG, addslashes($th));
                        return array('success' => false, 'msg' => $msgErrorAjax);
                    }

                } else {

                    $insertCAG = $this->dbSys->prepare("INSERT INTO acesso_grupos SET ID_ORIGIN = :ID_ORIGIN, NOME = :NOME, created_at = '$nowReg'");
                    $insertCAG->bindValue(":ID_ORIGIN", $ddr->ID);
                    $insertCAG->bindValue(":NOME", $ddr->NOME);

                    try {

                        $insertCAG->execute();

                    } catch (\Throwable $th) {
                        $this->insertDebug($erroInsertCAG, addslashes($th));
                        return array('success' => false, 'msg' => $msgErrorAjax);
                    }
                    
                }

                usleep(500);

            }

        } catch (\Throwable $th) {
            $this->insertDebug($errorAll, addslashes($th));
            return array('success' => false, 'msg' => $msgErrorAjax);
        }

        $this->insertDebug($motiveDebug, $contentEnd);

        return array('success' => true, 'msg' => $msgSuccessAjax);
    }

    ##################################################################################
    ######################### ATUALIZANDO AS LINHAS ##################################
    ##################################################################################
    public function updateLinhas()
    {
        $motiveDebug = $this->requestType;
        if($this->userId !== 0){
            $motiveDebug .= " - $this->userId";
        }

        $contentIni = "INICIO LINHAS";
        $contentEnd = "FINAL LINHAS";

        $errorAll = "$motiveDebug - ERRO LINHAS";
        $erroUpdateLinha = "$motiveDebug - ERRO UPDATE LINHAS";
        $erroInsertLinha = "$motiveDebug - ERRO INSERT LINHAS";

        $msgSuccessAjax = "Linhas atualizadas com sucesso!";
        $msgErrorAjax = "Erro ao atualizar Linhas, tente novamente.";

        $this->insertDebug($motiveDebug, $contentIni);

        try {
           
            $getLinhasVeltrac = "SELECT ID, GRUPO_LINHA_ID, PREFIXO, NOME, TIPO, CATEGORIA, CODIGO_INTEGRACAO, ATIVO, EXISTE_CONFLITO, TIPO_CONTROLE_ACESSO, observacao FROM LINHAS";

            $consulta   = $this->pdo->query($getLinhasVeltrac);
            $datas      = $consulta->fetchAll();

            $inactiveGroups = $this->getInactiveGroups();

            foreach($datas AS $ddr)
            {

                $ddr = (Object) $ddr;

                $idLns = 0;

                $nowReg = date("Y-m-d H:i:s");

                $getLinha = $this->dbSys->prepare("SELECT * FROM linhas WHERE ID_ORIGIN = {$ddr->ID} ORDER BY id DESC LIMIT 1");
                $getLinha->execute();
                $hasLinha = $getLinha->fetch(PDO::FETCH_OBJ);

                if($hasLinha){

                    $ativo = in_array($ddr->GRUPO_LINHA_ID, $inactiveGroups) ? 0 : $ddr->ATIVO;
    
                    $idLns = ($ativo == 0) ? 0 : $hasLinha->id;
                    
                    $ob = utf8_encode($ddr->observacao);
    
                    $updateLinha = $this->dbSys->prepare("UPDATE linhas SET GRUPO_LINHA_ID = :GRUPO_LINHA_ID, PREFIXO = :PREFIXO, NOME = :NOME, TIPO = :TIPO, CATEGORIA = :CATEGORIA, CODIGO_INTEGRACAO = :CODIGO_INTEGRACAO, ATIVO = :ATIVO, EXISTE_CONFLITO = :EXISTE_CONFLITO, TIPO_CONTROLE_ACESSO = :TIPO_CONTROLE_ACESSO, observacao = :observacao, updated_at = '$nowReg' where ID_ORIGIN = :ID_ORIGIN");
                    $updateLinha->bindValue(":GRUPO_LINHA_ID", $ddr->GRUPO_LINHA_ID);
                    $updateLinha->bindValue(":PREFIXO", $ddr->PREFIXO);
                    $updateLinha->bindValue(":NOME", $ddr->NOME);
                    $updateLinha->bindValue(":TIPO", $ddr->TIPO);
                    $updateLinha->bindValue(":CATEGORIA", $ddr->CATEGORIA);
                    $updateLinha->bindValue(":CODIGO_INTEGRACAO", $ddr->CODIGO_INTEGRACAO);
                    $updateLinha->bindValue(":ATIVO", $ativo); 
                    $updateLinha->bindValue(":EXISTE_CONFLITO", $ddr->EXISTE_CONFLITO);
                    $updateLinha->bindValue(":TIPO_CONTROLE_ACESSO", $ddr->TIPO_CONTROLE_ACESSO);
                    $updateLinha->bindValue(":observacao", $ob);
                    $updateLinha->bindValue(":ID_ORIGIN", $ddr->ID);
    
                    try {

                        $updateLinha->execute();
    
                        /// SE A LINHA FOR INATIVA OU DE GRUPO INATIVO, REMOVE AS PERMISSÕES DE USUÁRIOS DA LINHA \\\
                        if($ativo == 0){
                            
                            $removePermLinhas = $this->removePermLinhas($hasLinha->id, $motiveDebug);

                            if(!$removePermLinhas){
                                return array('success' => false, 'msg' => $msgErrorAjax);
                            }
                            
                        }
                        
                    } catch (\Throwable $th) {
                        $this->insertDebug($erroUpdateLinha, addslashes($th));
                        return array('success' => false, 'msg' => $msgErrorAjax);
                    }
    
                } else {
    
                    /// SÓ ADICIONA A LINHA SE FOR ATIVA E NÃO FOR DE GRUPO INATIVO \\\
                    if(!in_array($ddr->GRUPO_LINHA_ID, $inactiveGroups) && $ddr->ATIVO = 1){
    
                        $ob = utf8_encode($ddr->observacao);

                        $insertLinha = $this->dbSys->prepare("INSERT INTO linhas SET ID_ORIGIN = :ID_ORIGIN, GRUPO_LINHA_ID = :GRUPO_LINHA_ID, PREFIXO = :PREFIXO, NOME = :NOME, TIPO = :TIPO, CATEGORIA = :CATEGORIA, CODIGO_INTEGRACAO = :CODIGO_INTEGRACAO, ATIVO = :ATIVO, EXISTE_CONFLITO = :EXISTE_CONFLITO, TIPO_CONTROLE_ACESSO = :TIPO_CONTROLE_ACESSO, observacao = :observacao, created_at = '$nowReg'");
                        $insertLinha->bindValue(":ID_ORIGIN", $ddr->ID);
                        $insertLinha->bindValue(":GRUPO_LINHA_ID", $ddr->GRUPO_LINHA_ID);
                        $insertLinha->bindValue(":PREFIXO", $ddr->PREFIXO);
                        $insertLinha->bindValue(":NOME", $ddr->NOME);
                        $insertLinha->bindValue(":TIPO", $ddr->TIPO);
                        $insertLinha->bindValue(":CATEGORIA", $ddr->CATEGORIA);
                        $insertLinha->bindValue(":CODIGO_INTEGRACAO", $ddr->CODIGO_INTEGRACAO);
                        $insertLinha->bindValue(":ATIVO", $ddr->ATIVO); 
                        $insertLinha->bindValue(":EXISTE_CONFLITO", $ddr->EXISTE_CONFLITO);
                        $insertLinha->bindValue(":TIPO_CONTROLE_ACESSO", $ddr->TIPO_CONTROLE_ACESSO);
                        $insertLinha->bindValue(":observacao", $ob);
    
                        try {
    
                            $insertLinha->execute();
                            $idLns = $this->dbSys->lastInsertId();
    
                        } catch (\Throwable $th) {
                            $this->insertDebug($erroInsertLinha, addslashes($th));
                            return array('success' => false, 'msg' => $msgErrorAjax);
                        }                   
                        
                    }
                    
                }
    
                /// PARA DAR PERMISSÕES AOS USUÁRIOS \\\
                if ($idLns > 0)
                {
                    $insertPermLinhas = $this->insertPermLinhas($ddr->GRUPO_LINHA_ID, $idLns, $motiveDebug);

                    if(!$insertPermLinhas){
                        return array('success' => false, 'msg' => $msgErrorAjax);
                    }      
                }

                usleep(500);

            }

        } catch (\Throwable $th) {
            $this->insertDebug($errorAll, addslashes($th));
            return array('success' => false, 'msg' => $msgErrorAjax);
        }

        $this->insertDebug($motiveDebug, $contentEnd);

        return array('success' => true, 'msg' => $msgSuccessAjax);
    }

    ##################################################################################
    ########################## ATUALIZANDO ITINERÁRIO ################################
    ##################################################################################
    public function updateItine()
    {
        $motiveDebug = $this->requestType;
        if($this->userId !== 0){
            $motiveDebug .= " - $this->userId";
        }

        $contentIni = "INICIO ITINERARIOS";
        $contentEnd = "FINAL ITINERARIOS";

        $errorAll = "$motiveDebug - ERRO ITINERARIOS";
        $erroUpdateItine = "$motiveDebug - ERRO UPDATE ITINERARIOS";
        $erroInsertItine = "$motiveDebug - ERRO INSERT ITINERARIOS";

        $msgSuccessAjax = "Itinerários atualizados com sucesso!";
        $msgErrorAjax = "Erro ao atualizar Itinerários, tente novamente.";

        $this->insertDebug($motiveDebug, $contentIni);

        try {
            
            $getItineVeltrac    = "SELECT * FROM ITINERARIOS";
            $consulta           = $this->pdo->query($getItineVeltrac);
            $datas              = $consulta->fetchAll();

            foreach($datas AS $ddr)
            {
                $ddr = (Object) $ddr;

                $nowReg = date("Y-m-d H:i:s");

                $descLine = utf8_encode($ddr->DESCRICAO);

                $getItine = $this->dbSys->prepare("SELECT * FROM itinerarios WHERE ID_ORIGIN = {$ddr->ID} ORDER BY id DESC LIMIT 1");
                $getItine->execute();
                $hasItine = $getItine->fetch();

                if($hasItine){

                    $updateItine = $this->dbSys->prepare("UPDATE itinerarios SET ITINERARIO_ID_PAI = :ITINERARIO_ID_PAI, LINHA_ID = :LINHA_ID, ATIVO = :ATIVO, TIPO = :TIPO, SENTIDO = :SENTIDO, TRECHO = :TRECHO, TRAJETO_VEICULO_ID = :TRAJETO_VEICULO_ID, CODIGO_INTEGRACAO = :CODIGO_INTEGRACAO, DESCRICAO = :DESCRICAO, CODIGO_INTEGRACAO_NB = :CODIGO_INTEGRACAO_NB, updated_at = '$nowReg' where ID_ORIGIN = :ID_ORIGIN");
                    $updateItine->bindValue(":ITINERARIO_ID_PAI", $ddr->ITINERARIO_ID_PAI);
                    $updateItine->bindValue(":LINHA_ID", $ddr->LINHA_ID);
                    $updateItine->bindValue(":ATIVO", $ddr->ATIVO);
                    $updateItine->bindValue(":TIPO", $ddr->TIPO);
                    $updateItine->bindValue(":SENTIDO", $ddr->SENTIDO);
                    $updateItine->bindValue(":TRECHO", $ddr->TRECHO);
                    $updateItine->bindValue(":TRAJETO_VEICULO_ID", $ddr->TRAJETO_VEICULO_ID);
                    $updateItine->bindValue(":CODIGO_INTEGRACAO", $ddr->CODIGO_INTEGRACAO);
                    $updateItine->bindValue(":DESCRICAO", $descLine);
                    $updateItine->bindValue(":CODIGO_INTEGRACAO_NB", $ddr->CODIGO_INTEGRACAO_NB);
                    $updateItine->bindValue(":ID_ORIGIN", $ddr->ID);
    
                    try {
                        $updateItine->execute();
                    } catch (\Throwable $th) {
                        $this->insertDebug($erroUpdateItine, addslashes($th));
                        return array('success' => false, 'msg' => $msgErrorAjax);
                    }
    
                } else {
                
                    $insertItine = $this->dbSys->prepare("INSERT INTO itinerarios SET ID_ORIGIN = :ID_ORIGIN, ITINERARIO_ID_PAI = :ITINERARIO_ID_PAI, LINHA_ID = :LINHA_ID, ATIVO = :ATIVO, TIPO = :TIPO, SENTIDO = :SENTIDO, TRECHO = :TRECHO, TRAJETO_VEICULO_ID = :TRAJETO_VEICULO_ID, CODIGO_INTEGRACAO = :CODIGO_INTEGRACAO, DESCRICAO = :DESCRICAO, CODIGO_INTEGRACAO_NB = :CODIGO_INTEGRACAO_NB, created_at = '$nowReg'");
                    $insertItine->bindValue(":ID_ORIGIN", $ddr->ID);
                    $insertItine->bindValue(":ITINERARIO_ID_PAI", $ddr->ITINERARIO_ID_PAI);
                    $insertItine->bindValue(":LINHA_ID", $ddr->LINHA_ID);
                    $insertItine->bindValue(":ATIVO", $ddr->ATIVO);
                    $insertItine->bindValue(":TIPO", $ddr->TIPO);
                    $insertItine->bindValue(":SENTIDO", $ddr->SENTIDO);
                    $insertItine->bindValue(":TRECHO", $ddr->TRECHO);
                    $insertItine->bindValue(":TRAJETO_VEICULO_ID", $ddr->TRAJETO_VEICULO_ID);
                    $insertItine->bindValue(":CODIGO_INTEGRACAO", $ddr->CODIGO_INTEGRACAO);
                    $insertItine->bindValue(":DESCRICAO", $descLine);
                    $insertItine->bindValue(":CODIGO_INTEGRACAO_NB", $ddr->CODIGO_INTEGRACAO_NB);
    
                    try {
                        $insertItine->execute();
                    } catch (\Throwable $th) {
                        $this->insertDebug($erroInsertItine, addslashes($th));
                        return array('success' => false, 'msg' => $msgErrorAjax);
                    }
                    
                }

                usleep(500);

            }

        } catch (\Throwable $th) {
            $this->insertDebug($errorAll, addslashes($th));
            return array('success' => false, 'msg' => $msgErrorAjax);
        }

        $this->insertDebug($motiveDebug, $contentEnd);

        return array('success' => true, 'msg' => $msgSuccessAjax);
    }

    ##################################################################################
    ####################### ATUALIZANDO VIAGENS DO DIA  ##############################
    ##################################################################################
    public function updateViagens()
    {
        $motiveDebug = $this->requestType;
        if($this->userId !== 0){
            $motiveDebug .= " - $this->userId";
        }

        $contentIni = "INICIO VIAGENS";
        $contentEnd = "FINAL VIAGENS";

        $errorAll = "$motiveDebug - ERRO VIAGENS";
        $erroUpdateViagem = "$motiveDebug - ERRO VIAGENS";
        $erroInsertViagem = "$motiveDebug - ERRO VIAGENS";

        $msgSuccessAjax = "Viagens atualizadas com sucesso!";
        $msgErrorAjax = "Erro ao atualizar Viagens, tente novamente.";

        $this->insertDebug($motiveDebug, $contentIni);

        try {
            
            $start              = date("Y-m-d") . " 00:00:00";
            $end                = date("Y-m-d") . " 23:59:59";
            $getViagemVeltrac  = "SELECT * FROM VIAGENS WHERE DATAHORA_INICIAL_PREVISTO > '{$start}' AND DATAHORA_FINAL_PREVISTO < '{$end}' AND DATAHORA_FINAL_REALIZADO IS NULL";
            $consulta           = $this->pdo->query($getViagemVeltrac);    
            $datas              = $consulta->fetchAll();

            foreach($datas AS $ddr)
            {
                $ddr = (Object) $ddr;

                $nowReg = date("Y-m-d H:i:s");

                $ORIGINID                   = $ddr->ID;
                $ITINERARIO_ID              = $ddr->ITINERARIO_ID ?? 0;
                $VEICULO_ID                 = $ddr->VEICULO_ID ?? 0;
                $MOTORISTA_ID               = $ddr->MOTORISTA_ID ?? 0;
                $DATAHORA_INICIAL_PREVISTO  = $ddr->DATAHORA_INICIAL_PREVISTO??null;
                $DATAHORA_INICIAL_REALIZADO = $ddr->DATAHORA_INICIAL_REALIZADO??null;
                $DATAHORA_FINAL_PREVISTO    = $ddr->DATAHORA_FINAL_PREVISTO??null;
                $DATAHORA_FINAL_REALIZADO   = $ddr->DATAHORA_FINAL_REALIZADO??null;
                $DISTANCIA_PERCORRIDA       = $ddr->DISTANCIA_PERCORRIDA ?? 0;
                $TIPO_AGENDAMENTO           = $ddr->TIPO_AGENDAMENTO ?? 0;
                $TIPO_EXEC_VIAGEM           = $ddr->TIPO_EXEC_VIAGEM ?? 0;
                $TIPO_EXEC_PONTOS           = $ddr->TIPO_EXEC_PONTOS ?? 0;
                $VIAGEM_ID_APOIO            = $ddr->VIAGEM_ID_APOIO ?? 0;
                $TROCA_AUTOMATICA           = $ddr->TROCA_AUTOMATICA ?? 0;
                $SUBSTITUIDA                = $ddr->SUBSTITUIDA ?? 0;
                $TIPO_VIAGEM                = $ddr->TIPO_VIAGEM ?? 0;

                $getViagem = $this->dbSys->prepare("SELECT * FROM viagems WHERE ID_ORIGIN = {$ORIGINID} ORDER BY id DESC LIMIT 1");
                $getViagem->execute();
                $hasViagem = $getViagem->fetch(PDO::FETCH_OBJ);

                if(!$hasViagem){

                    $insertViagem = $this->dbSys->prepare("INSERT INTO viagems (ID_ORIGIN, ITINERARIO_ID, VEICULO_ID, MOTORISTA_ID, DATAHORA_INICIAL_PREVISTO, DATAHORA_INICIAL_REALIZADO, DATAHORA_FINAL_PREVISTO, DATAHORA_FINAL_REALIZADOA, DISTANCIA_PERCORRIDA, TIPO_AGENDAMENTO, TIPO_EXEC_VIAGEM, TIPO_EXEC_PONTOS, VIAGEM_ID_APOIO, TROCA_AUTOMATICA, TIPO_VIAGEM, created_at) VALUES 
                    (:ID_ORIGIN, :ITINERARIO_ID, :VEICULO_ID, :MOTORISTA_ID, :DATAHORA_INICIAL_PREVISTO, :DATAHORA_INICIAL_REALIZADO, :DATAHORA_FINAL_PREVISTO, :DATAHORA_FINAL_REALIZADOA, :DISTANCIA_PERCORRIDA, :TIPO_AGENDAMENTO, :TIPO_EXEC_VIAGEM, :TIPO_EXEC_PONTOS, :VIAGEM_ID_APOIO, :TROCA_AUTOMATICA, :TIPO_VIAGEM, '$nowReg')");
    
                    $insertViagem->bindValue(":ID_ORIGIN", $ORIGINID);
                    $insertViagem->bindValue(":ITINERARIO_ID", $ITINERARIO_ID);
                    $insertViagem->bindValue(":VEICULO_ID", $VEICULO_ID);
                    $insertViagem->bindValue(":MOTORISTA_ID", $MOTORISTA_ID);
                    $insertViagem->bindValue(":DATAHORA_INICIAL_PREVISTO", $DATAHORA_INICIAL_PREVISTO??null);
                    $insertViagem->bindValue(":DATAHORA_INICIAL_REALIZADO", $DATAHORA_INICIAL_REALIZADO??null);
                    $insertViagem->bindValue(":DATAHORA_FINAL_PREVISTO", $DATAHORA_FINAL_PREVISTO??null);
                    $insertViagem->bindValue(":DATAHORA_FINAL_REALIZADOA", $DATAHORA_FINAL_REALIZADO??null);
                    $insertViagem->bindValue(":DISTANCIA_PERCORRIDA", $DISTANCIA_PERCORRIDA);
                    $insertViagem->bindValue(":TIPO_AGENDAMENTO", $TIPO_AGENDAMENTO);
                    $insertViagem->bindValue(":TIPO_EXEC_VIAGEM", $TIPO_EXEC_VIAGEM);
                    $insertViagem->bindValue(":TIPO_EXEC_PONTOS", $TIPO_EXEC_PONTOS);
                    $insertViagem->bindValue(":VIAGEM_ID_APOIO", $VIAGEM_ID_APOIO);
                    $insertViagem->bindValue(":TROCA_AUTOMATICA", $TROCA_AUTOMATICA);
                    $insertViagem->bindValue(":TIPO_VIAGEM", $TIPO_VIAGEM);
    
                    try {
                        $insertViagem->execute();
                    } catch (\Throwable $th) {
                        $this->insertDebug($erroInsertViagem, addslashes($th));
                        return array('success' => false, 'msg' => $msgErrorAjax);
                    }
    
                }

                usleep(500);

            }

        } catch (\Throwable $th) {
            $this->insertDebug($errorAll, addslashes($th));
            return array('success' => false, 'msg' => $msgErrorAjax);
        }

        $this->insertDebug($motiveDebug, $contentEnd);

        return array('success' => true, 'msg' => $msgSuccessAjax);
    }

    ##################################################################################
    ####################### ATUALIZANDO CONTROLE ACESSO ##############################
    ##################################################################################
    public function updateCA($byGroup = false)
    {
        $motiveDebug = $this->requestType;
        if($this->userId !== 0){
            $motiveDebug .= " - $this->userId";
        }

        $contentIni = "INICIO CONTROLE ACESSO";
        $contentEnd = "FINAL CONTROLE ACESSO";

        $errorAll = "$motiveDebug - ERRO CONTROLE ACESSO";
        $erroUpdateCA = "$motiveDebug - ERRO UPDATE CONTROLE ACESSO";
        $erroInsertCA = "$motiveDebug - ERRO INSERT CONTROLE ACESSO";

        $msgSuccessAjax = "Controles de Acesso atualizados com sucesso!";
        $msgErrorAjax = "Erro ao atualizar Controles de Acesso, tente novamente.";

        $this->insertDebug($motiveDebug, $contentIni);

        try {

            $where = "";

            if($byGroup && $byGroup != 0){

                $grupos = $this->acessoGrupoIn($byGroup);

                if(!$grupos){

                    return array('success' => false, 'msg' => "Não foram encontrados passageiros para o grupo selecionado!");
                    
                }

                $where = " WHERE CONTROLE_ACESSO_GRUPO_ID IN ({$grupos})";

            }
            
            $getCAVeltrac   = "SELECT * FROM CONTROLE_ACESSO {$where}";
            $consulta       = $this->pdo->query($getCAVeltrac);
            $datas          = $consulta->fetchAll();

            foreach($datas AS $ddr)
            {
                $ddr = (Object) $ddr;

                $nowReg = date("Y-m-d H:i:s");

                $name       = utf8_encode($ddr->NOME);
                $itiIda     = $ddr->ITINERARIO_ID_IDA ? $ddr->ITINERARIO_ID_IDA : NULL;
                $itiVolt    = $ddr->ITINERARIO_ID_VOLTA ? $ddr->ITINERARIO_ID_VOLTA : NULL;
                $cagID      = $ddr->CONTROLE_ACESSO_GRUPO_ID ? $ddr->CONTROLE_ACESSO_GRUPO_ID : NULL;
                $mf         = $ddr->MATRICULA_FUNCIONAL ? $ddr->MATRICULA_FUNCIONAL : "";
                $cc         = $ddr->centro_custo ? $ddr->centro_custo : "";
                $pols       = $ddr->centro_custo ? explode(",", $ddr->centro_custo) : "";
                $pid        = isset($pols[0]) ? (int) $pols[0] : "";
                $pvt        = isset($pols[1]) ? (int) $pols[1] : "";

                $getCA = $this->dbSys->prepare("SELECT * FROM controle_acessos WHERE ID_ORIGIN = {$ddr->ID} ORDER BY id DESC LIMIT 1");
                $getCA->execute();
                $hasCA = $getCA->fetch(PDO::FETCH_OBJ);

                if($hasCA){

                    $updateCA = $this->dbSys->prepare("UPDATE controle_acessos SET NOME = :NOME, ITINERARIO_ID_IDA = :ITINERARIO_ID_IDA, ITINERARIO_ID_VOLTA = :ITINERARIO_ID_VOLTA, CONTROLE_ACESSO_GRUPO_ID = :CONTROLE_ACESSO_GRUPO_ID, MATRICULA_FUNCIONAL = :MATRICULA_FUNCIONAL, ATIVO = :ATIVO, POLTRONAIDA = :POLTRONAIDA, POLTRONAVOLTA = :POLTRONAVOLTA, updated_at = '$nowReg' where ID_ORIGIN = :ID_ORIGIN");
                    $updateCA->bindValue(":NOME", $name);
                    $updateCA->bindValue(":ITINERARIO_ID_IDA", $itiIda);
                    $updateCA->bindValue(":ITINERARIO_ID_VOLTA", $itiVolt);
                    $updateCA->bindValue(":CONTROLE_ACESSO_GRUPO_ID", $cagID);
                    $updateCA->bindValue(":MATRICULA_FUNCIONAL", $mf);
                    $updateCA->bindValue(":ATIVO", $ddr->ATIVO);
                    $updateCA->bindValue(":POLTRONAIDA", $pid);
                    $updateCA->bindValue(":POLTRONAVOLTA", $pvt);
                    $updateCA->bindValue(":ID_ORIGIN", $ddr->ID);

                    try {
                        $updateCA->execute();
                    } catch (\Throwable $th) {
                        $this->insertDebug($erroUpdateCA, addslashes($th));
                        return array('success' => false, 'msg' => $msgErrorAjax);
                    }

                } else {

                    $insertCA = $this->dbSys->prepare("INSERT INTO controle_acessos SET ID_ORIGIN = :ID_ORIGIN, NOME = :NOME, ITINERARIO_ID_IDA = :ITINERARIO_ID_IDA, ITINERARIO_ID_VOLTA = :ITINERARIO_ID_VOLTA, CONTROLE_ACESSO_GRUPO_ID = :CONTROLE_ACESSO_GRUPO_ID, MATRICULA_FUNCIONAL = :MATRICULA_FUNCIONAL, ID_UNICO = :ID_UNICO, ATIVO = :ATIVO, TAG = :TAG, cpf = :cpf, centro_custo = :centro_custo, POLTRONAIDA = :POLTRONAIDA, POLTRONAVOLTA = :POLTRONAVOLTA, created_at = '$nowReg'");
                    $insertCA->bindValue(":ID_ORIGIN", $ddr->ID);
                    $insertCA->bindValue(":NOME", $name);
                    $insertCA->bindValue(":ITINERARIO_ID_IDA", $itiIda);
                    $insertCA->bindValue(":ITINERARIO_ID_VOLTA", $itiVolt);
                    $insertCA->bindValue(":CONTROLE_ACESSO_GRUPO_ID", $cagID);
                    $insertCA->bindValue(":MATRICULA_FUNCIONAL", $mf);
                    $insertCA->bindValue(":ID_UNICO", $ddr->ID_UNICO);
                    $insertCA->bindValue(":ATIVO", $ddr->ATIVO);
                    $insertCA->bindValue(":TAG", $ddr->TAG);
                    $insertCA->bindValue(":centro_custo", $cc);
                    $insertCA->bindValue(":cpf", $ddr->cpf);
                    $insertCA->bindValue(":POLTRONAIDA", $pid);
                    $insertCA->bindValue(":POLTRONAVOLTA", $pvt);
                    
                    try {
                        $insertCA->execute();
                    } catch (\Throwable $th) {
                        $this->insertDebug($erroInsertCA, addslashes($th));
                        return array('success' => false, 'msg' => $msgErrorAjax);
                    }
                    
                }

                usleep(500);

            }

        } catch (\Throwable $th) {
            $this->insertDebug($errorAll, addslashes($th));
            return array('success' => false, 'msg' => $msgErrorAjax);
        }

        $this->insertDebug($motiveDebug, $contentEnd);

        return array('success' => true, 'msg' => $msgSuccessAjax);
    }

    ##################################################################################
    ####################### ATUALIZANDO RFIDS E VIGENCIAS ############################
    ##################################################################################
    public function updateRfids($byGroup = 0)
    {

        if($byGroup == 0){
            
            return array('success' => false, 'msg' => "É obrigatório selecionar um Grupo!");

        }

        $grupos = $this->acessoGrupoIn($byGroup);

        if(!$grupos){

            return array('success' => false, 'msg' => "Não foram encontrados usuários para o grupo selecionado!");
            
        }

        $controle_acessos = $this->getTags($grupos);

        if(!$controle_acessos){

            return array('success' => false, 'msg' => "Não foram encontrados passageiros ativos para o grupo selecionado!");
            
        }

        $this->checkRfidVeltrac($controle_acessos);

        return array('success' => true, 'msg' => 'Tags e RFIDs atualizados com sucesso!');

    }


    ##################################################################################
    ############################## FUNÇÕES AUXILIARES ################################
    ##################################################################################
    private function getGroups()
    {
        try {

            $getGroups = $this->dbSys->prepare("SELECT id FROM grupo_linhas WHERE deleted_at IS NULL ORDER BY id");
            $getGroups->execute();

            if($getGroups->rowCount() == 0) {
                return array('success' => false, 'msg' => 'NÃO ENCONTROU GRUPOS');
            }

            $groups = $getGroups->fetchAll(PDO::FETCH_OBJ);

            foreach ($groups as $group) {
                $this->updateRfids($group->id);
                usleep(60000);
            }

            return array('success' => true);

        } catch (\Throwable $th) {
            return array('success' => false, 'msg' => 'ERRO AO CARREGAR GRUPOS');
        }
        
    }

    private function checkRfidVeltrac($controle_acessos)
    {

        try {
            
            foreach($controle_acessos AS $ca){

                $now = date("Y-m-d H:i:s");
                $ID_ORIGIN = $ca->ID_ORIGIN;
                $TAG = $ca->TAG;
    
                $getRfidVeltrac = "SELECT CODIGO FROM RFID WHERE CODIGO = {$TAG}";
                $consulta       = $this->pdo->query($getRfidVeltrac);
                $rfid           = $consulta->fetch();			
                
                // Se não Tiver em RFID, Cadastra
                if ( !isset($rfid['CODIGO']) )
                {
                    $this->pdo->query("INSERT INTO RFID (CODIGO, TIPO_ACESSO, TIPO_REPRESENTACAO) VALUES ({$TAG},2,0)");
                    usleep(500);
                }

                // Se ID_ORIGIN for 0 cria o ID_ORIGIN pegando o ultimo na Veltrac e acrecenta 1
                if($ID_ORIGIN == 0){

                    // Pega o último id de CONTROLE_ACESSO na Veltrac para poder incrementar
                    // e cadastrar na veltrac e atualizar no banco local
                    $getLastVeltracId   = "SELECT ID FROM CONTROLE_ACESSO order by ID DESC;";
                    $consulta 	        = $this->pdo->query($getLastVeltracId); 
                    $lastVeltracId 	    = $consulta->fetch();
                    $ID_ORIGIN          = $lastVeltracId['ID'] + 1;   

                }

                // Verifica se tem vigência
                $getVigenciaVeltrac = "SELECT TAG FROM CONTROLE_ACESSO_VIGENCIA WHERE TAG = {$TAG} ORDER BY DATA_INICIO DESC";
                $consulta           = $this->pdo->query($getVigenciaVeltrac); 
                $vigenciaVeltrac    = $consulta->fetch();

                if ( !isset($vigenciaVeltrac['TAG']) )
                {
                    // Se não tem Insere a Vigência na Veltrac
                    $this->pdo->query("INSERT INTO CONTROLE_ACESSO_VIGENCIA (TAG, DATA_TERMINO, CONTROLE_ACESSO_ID, DATA_INICIO) VALUES ('{$TAG}', null, {$ID_ORIGIN}, '{$now}')");

                }else{

                    // Se tem verifica se existe outro passageiro usando a TAG
                    $getVigenciaOldVeltrac  = "SELECT * FROM CONTROLE_ACESSO_VIGENCIA WHERE TAG = '{$TAG}' AND DATA_TERMINO IS NULL AND CONTROLE_ACESSO_ID <> {$ID_ORIGIN} ORDER BY DATA_INICIO DESC;";
                    $consulta               = $this->pdo->query($getVigenciaOldVeltrac); 
                    $vigenciaOldVeltrac     = $consulta->fetch();

                    // Se Tiver outro passageiro usando a TAG
                    if ( isset($vigenciaOldVeltrac['TAG']) )
                    {
    
                        $oldID = $vigenciaOldVeltrac['CONTROLE_ACESSO_ID'];
    
                        // Remove a Vigência do passageiro antigo
                        $this->pdo->query("UPDATE CONTROLE_ACESSO_VIGENCIA SET DATA_TERMINO = '{$now}' WHERE CONTROLE_ACESSO_ID = {$oldID} AND TAG = '{$TAG}'");
                        
                        // Insere a Vigência
                        $this->pdo->query("INSERT INTO CONTROLE_ACESSO_VIGENCIA (TAG, DATA_TERMINO, CONTROLE_ACESSO_ID, DATA_INICIO) VALUES ('{$TAG}', null, {$ID_ORIGIN}, '{$now}')");
    
                        // Inativa o passageiro antigo
                        $this->pdo->query("UPDATE CONTROLE_ACESSO SET ATIVO = 0, TAG = null WHERE ID = {$oldID} AND TAG = '{$TAG}'");
    
                        // Inativa o passageiro antigo no CGF 
                        $inactiveOldCgf = $this->dbSys->prepare("UPDATE controle_acessos SET ATIVO = 0, updated_at = '$now' WHERE ID_ORIGIN = {$oldID} AND TAG = '{$TAG}'");
                        $inactiveOldCgf->execute();
    
                    }else{
        
                        // Verfica se o passageiro tem Vigência
                        $getVigenciaVeltrac = "SELECT * FROM CONTROLE_ACESSO_VIGENCIA WHERE TAG = '{$TAG}' AND CONTROLE_ACESSO_ID = {$ID_ORIGIN} ORDER BY DATA_INICIO DESC;";
                        $consulta           = $this->pdo->query($getVigenciaVeltrac); 
                        $vigenciaVeltrac    = $consulta->fetch();	
                        
                        // Se não tem Vigência com TAG e ID_ORIGIN
                        if ( !isset($vigenciaVeltrac['TAG']) )
                        {
                            // Verifica se tem Vigência somente pelo ID_ORIGIN
                            $getVigenciaIdVeltrac   = "SELECT * FROM CONTROLE_ACESSO_VIGENCIA WHERE CONTROLE_ACESSO_ID = {$ID_ORIGIN} AND DATA_TERMINO IS NULL;";
                            $consulta               = $this->pdo->query($getVigenciaIdVeltrac); 
                            $vigenciaIdVeltrac      = $consulta->fetch();	
    
                            // Se encontra Vigência somente pelo ID_ORIGIN, finaliza ela
                            if ( isset($vigenciaIdVeltrac['TAG']) )
                            {
                                $this->pdo->query("UPDATE CONTROLE_ACESSO_VIGENCIA SET DATA_TERMINO = '{$now}' WHERE CONTROLE_ACESSO_ID = {$ID_ORIGIN} AND DATA_TERMINO IS NULL;");
                            }
                            
                            // Adiciona a Vigência 
                            $this->pdo->query("INSERT INTO CONTROLE_ACESSO_VIGENCIA (TAG, DATA_TERMINO, CONTROLE_ACESSO_ID, DATA_INICIO) VALUES ('{$TAG}', null, {$ID_ORIGIN}, '{$now}')");
                            
                        } else {
    
                            // Se tem a Vigência, atualiza ela
                            $this->pdo->query("UPDATE CONTROLE_ACESSO_VIGENCIA SET DATA_TERMINO = null WHERE CONTROLE_ACESSO_ID = {$ID_ORIGIN} AND TAG = '{$TAG}'");
    
                        }
    
                    }

                }

                usleep(500);

                // Verifica se tem cadastro
                $getCAVeltrac = "SELECT * FROM CONTROLE_ACESSO WHERE ID = {$ID_ORIGIN}";
                $consulta     = $this->pdo->query($getCAVeltrac); 
                $caVeltrac    = $consulta->fetch();	

                if ( isset($caVeltrac['TAG']) )
                {
                    // Se tem cadastro, Atualiza na Veltrac
                    $updateCAVeltrac = "UPDATE CONTROLE_ACESSO SET
                        NOME = '".$ca->NOME."', 
                        ITINERARIO_ID_IDA = {$ca->ITINERARIO_ID_IDA}, 
                        ITINERARIO_ID_VOLTA = {$ca->ITINERARIO_ID_VOLTA},
                        CONTROLE_ACESSO_GRUPO_ID = {$ca->CONTROLE_ACESSO_GRUPO_ID},
                        MATRICULA_FUNCIONAL = '{$ca->MATRICULA_FUNCIONAL}', 
                        ATIVO = 1,
                        TAG = '{$TAG}',
                        cpf = '{$ca->cpf}',
                        centro_custo = '{$ca->centro_custo}'
                        WHERE ID = {$ID_ORIGIN};";

                    $this->pdo->query($updateCAVeltrac); 

                }else{

                    // Se não tem cadastro, Insere na Veltrac
                    $insertCAVeltrac = "INSERT INTO CONTROLE_ACESSO (
                        NOME, 
                        ITINERARIO_ID_IDA, 
                        ITINERARIO_ID_VOLTA,
                        CONTROLE_ACESSO_GRUPO_ID, 
                        MATRICULA_FUNCIONAL, 
                        ID_UNICO, 
                        ATIVO,
                        TAG,
                        cpf,
                        centro_custo
                    ) VALUES (
                        '".$ca->NOME."', 
                        {$ca->ITINERARIO_ID_IDA}, 
                        {$ca->ITINERARIO_ID_VOLTA},
                        {$ca->CONTROLE_ACESSO_GRUPO_ID},
                        '{$ca->MATRICULA_FUNCIONAL}', 
                        {$ID_ORIGIN},
                        1,
                        '{$TAG}',
                        '{$ca->cpf}',
                        '{$ca->centro_custo}'
                    )";
                    
                    $this->pdo->query($insertCAVeltrac);

                }

                //Verificar se tem created_at
                if(!$ca->created_at || $ca->created_at == ""){
                    // Se não tem created_at, insere e atualiza o updated_at
                    $insertCreated = $this->dbSys->prepare("UPDATE controle_acessos SET created_at = '$now', updated_at = '$now' WHERE id = {$ca->id}");
                    $insertCreated->execute();
                }

                usleep(500);
                    
            }

            return true;

        } catch (\Throwable $th) {
            
            return false;
            
        }
    }

    private function getUsers($grupoLinha = false)
    {
        $users = false;

        $w = $grupoLinha ? " AND groupUserID = {$grupoLinha} " : "";
        
        $getUsers = $this->dbSys->prepare("SELECT * FROM users WHERE `type` = 2 {$w} AND deleted_at is null");
        $getUsers->execute();
        $users = $getUsers->fetchAll(PDO::FETCH_OBJ);

        return $users;
    }

    private function getTags($grupos = false){

        if(!$grupos){
            return false;
        }

        try {

            $tags = false;

            $getTags = $this->dbSys->prepare("SELECT * FROM controle_acessos WHERE CONTROLE_ACESSO_GRUPO_ID IN ({$grupos}) AND ATIVO = 1 AND TRIM(LEADING '0' FROM TAG) != '' AND TAG is not null ORDER BY ID_ORIGIN ASC");
            $getTags->execute();

            if($getTags->rowCount() > 0) {
                $tags = $getTags->fetchAll(PDO::FETCH_OBJ);
            }

            return $tags;
            
        } catch (\Throwable $th) {
            return false;
        }

    }

    private function acessoGrupoIn($byGroup)
	{

        try {

            $grupos = false;

            $sql = $this->dbSys->prepare("SELECT id FROM users WHERE groupUserID = {$byGroup} AND deleted_at is null");
            $sql->execute();
            $users = $sql->fetchAll();

            $usersIds = array_column($users, 'id');
            $usIn = implode(',', $usersIds);

            $sql = $this->dbSys->prepare("SELECT DISTINCT ID_ORIGIN FROM acesso_grupos 
                    WHERE id IN (
                    SELECT grupo_id FROM usuario_grupos WHERE usuario_id IN ({$usIn}) AND deleted_at is null
                ) ORDER BY ID_ORIGIN");
            $sql->execute();
            $array = $sql->fetchAll();

            if(count($array) > 0){
                $gruposIds = array_column($array, 'ID_ORIGIN');
                $grupos = implode(',', $gruposIds);
            }

            return $grupos;

        } catch (\Throwable $th) {
            
            return false;

        }
        
	}

    private function insertPermVeic($user_id, $carro_id, $motiveDebug)
    {

        try {

            $now = date("Y-m-d H:i:s");

            $erroInsertPerm = "$motiveDebug - ERRO INSERT PERM VEICULO";

            $getPerm = $this->dbSys->prepare("SELECT * FROM usuario_carros WHERE usuario_id = {$user_id} AND carro_id = {$carro_id}");
            $getPerm->execute();
            $perm = $getPerm->fetch(PDO::FETCH_OBJ);

        
            if (!$perm) {
                $insertPerm = $this->dbSys->prepare("INSERT INTO usuario_carros SET usuario_id = :usuario_id, carro_id = :carro_id, created_at = '$now'");
                $insertPerm->bindValue(":usuario_id", $user_id);
                $insertPerm->bindValue(":carro_id", $carro_id);
    
                try {
                    $insertPerm->execute();
                } catch (\Throwable $th) {
                    $this->insertDebug($erroInsertPerm, addslashes($th));
                    return false;
                }
            }

            return true;

        } catch (\Throwable $th) {
            $this->insertDebug($erroInsertPerm, addslashes($th));
            return false;
        }

    }

    private function removePermLinhas($linha_id, $motiveDebug)
    {
        $now = date("Y-m-d H:i:s");

        $erroRemovePerm = "$motiveDebug - ERRO REMOVE PERM LINHA";

        $removePermLinhas = $this->dbSys->prepare("UPDATE usuario_linhas SET deleted_at = '$now', updated_at = '$now') WHERE linha_id = {$linha_id}");
    
        try {
            $removePermLinhas->execute();
            return true;
        } catch (\Throwable $th) {
            $this->insertDebug($erroRemovePerm, addslashes($th));
            return false;
        }
    }

    private function insertPermLinhas($grupo_linha_id, $linha_id, $motiveDebug){

        $getGrupoLinha = $this->dbSys->prepare("SELECT * FROM grupo_linhas WHERE ID_ORIGIN = {$grupo_linha_id} AND deleted_at is null");
        $getGrupoLinha->execute();
        $grupoLinha = $getGrupoLinha->fetch(PDO::FETCH_OBJ);

        $now = date("Y-m-d H:i:s");

        $erroUpdatePerm = "$motiveDebug - ERRO UPDATE PERM LINHA";
        $erroInsertPerm = "$motiveDebug - ERRO INSERT PERM LINHA";

        if($grupoLinha){

            $users = $this->getUsers($grupoLinha->id);

            if($users){

                $inactiveGroups = $this->getInactiveGroups();

                foreach($users AS $user)
                {

                    /// DAR OU ATUALIZAR PERMISSÃO SE NÃO FOR DE GRUPO INATIVO \\\
                    if(!in_array($grupo_linha_id, $inactiveGroups)){

                        /// VERIFICA SE NÃO TEM A PERMISSÃO \\\
                        $getPerm = $this->dbSys->prepare("SELECT * FROM usuario_linhas WHERE usuario_id = {$user->id} AND linha_id = {$linha_id} LIMIT 1");
                        $getPerm->execute();
                        $perm = $getPerm->fetch(PDO::FETCH_OBJ);

                        // SE ENCONTRA PERMISSÃO, ATUALIZA \\\
                        if($perm) {

                            $updatePerm = $this->dbSys->prepare("UPDATE usuario_linhas SET deleted_at = null, updated_at = '$now' WHERE id = {$perm->id}");
                            
                            try {
                                $updatePerm->execute();
                            } catch (\Throwable $th) {
                                $this->insertDebug($erroUpdatePerm, addslashes($th));
                                return false;
                            }

                        } else {

                            //SE NÃO ENCONTRA PERMISSÃO ADICIONA \\\
                            $insertPerm = $this->dbSys->prepare("INSERT INTO usuario_linhas SET usuario_id = :usuario_id, linha_id = :linha_id, created_at = '$now'");
                            $insertPerm->bindValue(":usuario_id", $user->id);
                            $insertPerm->bindValue(":linha_id", $linha_id);
                            
                            try {
                                $insertPerm->execute();
                            } catch (\Throwable $th) {
                                $this->insertDebug($erroInsertPerm, addslashes($th));
                                return false;
                            }

                        }
                        
                    }   

                }
                
            }

        }

        return true;

    }

    private function insertDebug($motive, $content)
    {
        if ($this->saveDebug) {
            $now = date("Y-m-d H:i:s");
            $sqlDebug = $this->dbSys->prepare("INSERT INTO debug_geral (motive, content, created_at) VALUES ('$motive', '$content', '$now')");
            $sqlDebug->execute();
        }
    }

    private function getInactiveGroups() {

        $inactiveGroups = array();

        try {
            
            $getParameter = $this->dbSys->prepare("SELECT inactiveGroups FROM parametros WHERE id = 1");
            $getParameter->execute();
            $findInactiveGroups = $getParameter->fetch();

            if(isset($findInactiveGroups['inactiveGroups']) && $findInactiveGroups['inactiveGroups'] != ''){
                $inactiveGroups = explode(",", $findInactiveGroups['inactiveGroups']);
            }


        } catch (\Throwable $th) {}
        
        return $inactiveGroups;

    }
}

?>