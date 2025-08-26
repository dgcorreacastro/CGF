<?php 

ini_set('memory_limit', '-1');
set_time_limit(0);
date_default_timezone_set('America/Sao_Paulo');

##################################################################################
#################### CRON PARA CRIAR RELATÓRIOS AGENDADOS ########################
##################################################################################
require_once '../config.php';

require_once  __DIR__ . '/../helpers/debug.php';

require_once  __DIR__ . '/../helpers/cnn.php';

require_once  __DIR__ . '/../models/Relatorios.php';

require_once  __DIR__ . '/../Services/TalentumNotification.php';


class createAgendamentos{

    private $dbSys;
    private $notify;
    private $relGet;
    private $portalName;

    private $sendNotify;
    private $checkNotify;
    private $timeBeforeCheckNotify;
    private $saveDebug;
    private $createAnalitico;
    private $createConsolidado;
    private $createSintetico;
    private $statusCreate;

    public function __construct() {

        global $dbSys, $portalName;
        $this->dbSys = $dbSys;
        $this->portalName = PORTAL_NAME;
        $this->notify = new TalentumNotification;
        $this->relGet = new Relatorios();
        $this->sendNotify = true;
        $this->checkNotify = true;
        $this->timeBeforeCheckNotify = 30;
        $this->saveDebug = true;
        $this->createAnalitico = true;
        $this->createConsolidado = true;
        $this->createSintetico = true;
        $this->statusCreate = "0,3";
    }

    public function executeCron() {

        $this->insertDebug('CRON', 'INICIO CRON RELS');

        if($this->createAnalitico){

            $this->createAgendaAnalitico();

        }

        if($this->createConsolidado){

            $this->createAgendaConsolidado();

        }

        if($this->createSintetico){

            $this->createAgendaSintetico();

        }

        $this->insertDebug('CRON', 'FINAL OK CRON RELS');

        if($this->checkNotify){
            sleep($this->timeBeforeCheckNotify);
            $this->notifyAgendas();
        }

        exit;
        
    }

    private function createAgendaAnalitico()
    {
        $this->insertDebug('CRON', 'INICIO REL ANALITICO');

        $agendamentos = $this->getAgendamentos(1);

        if($agendamentos){
            $this->insertAgendaAnalitico($agendamentos);
        }

        $this->insertDebug('CRON', 'FINAL REL ANALITICO');
    }

    private function insertAgendaAnalitico($agendamentos)
    {
        foreach($agendamentos as $agendamento){
            
            $errorItens = 0;

            $dadosRel = $this->relGet->getDadosAnaliticoPassageiro($agendamento, 0, $this->dbSys, $agendamento->usuario_id);

            foreach ($dadosRel AS $relV) 
            {
                $rels = (Object) $relV;

                foreach($rels AS $rel)
                {

                    $rel = (Object) $rel;

                    $sql = $this->dbSys->prepare("INSERT INTO agenda_analitico_ready SET usuario_id = :usuario_id, agenda_id = :agenda_id, agenda_viagem_id = :agenda_viagem_id, viagemID = :viagemID, PREF = :PREF, PLACA = :PLACA, CODIGO = :CODIGO, GRUPO = :GRUPO, NOME = :NOME, MATRICULA = :MATRICULA, STATUS = :STATUS, LATITUDEEMB = :LATITUDEEMB, LONGITUDEEMB = :LONGITUDEEMB, PONTOREFEREMB = :PONTOREFEREMB, HORAMARCACAOEMB = :HORAMARCACAOEMB, LOGRADOUROEMB = :LOGRADOUROEMB, LOCALIZACAOEMB = :LOCALIZACAOEMB, ITIDAPREV = :ITIDAPREV, LATITUDEDESEMB = :LATITUDEDESEMB, LONGITUDEDESEMB = :LONGITUDEDESEMB, PONTOREFERDESEMB = :PONTOREFERDESEMB, HORAMARCACAODESEMB = :HORAMARCACAODESEMB, LOGRADOURODESEMB = :LOGRADOURODESEMB, LOCALIZACAODESEMB = :LOCALIZACAODESEMB, ITVOLTAPREV = :ITVOLTAPREV, ITIREALIZADOOK = :ITIREALIZADOOK, SENTREALIZADO = :SENTREALIZADO, DATAREALIZADO = :DATAREALIZADO, PREVOK = :PREVOK");

                    $sql->bindValue(":usuario_id", $agendamento->usuario_id);
                    $sql->bindValue(":agenda_id", $agendamento->id);
                    $sql->bindValue(":agenda_viagem_id", 0);
                    $sql->bindValue(":viagemID", $agendamento->viagemID);

                    //VEICULO
                    $sql->bindValue(":PREF", $rel->PREF);
                    $sql->bindValue(":PLACA", $rel->PLACA);

                    //PAX
                    $sql->bindValue(":CODIGO", $rel->CODIGO);
                    $sql->bindValue(":GRUPO", $rel->GRUPO);
                    $sql->bindValue(":NOME", $rel->NOME);
                    $sql->bindValue(":MATRICULA", $rel->MATRICULA);
                    $sql->bindValue(":STATUS", $rel->STATUS);

                    
                    //EMBARQUE
                    $sql->bindValue(":LATITUDEEMB", $rel->LATITUDEEMB);
                    $sql->bindValue(":LONGITUDEEMB", $rel->LONGITUDEEMB);
                    $sql->bindValue(":PONTOREFEREMB", $rel->PONTOREFEREMB);
                    $sql->bindValue(":HORAMARCACAOEMB", $rel->HORAMARCACAOEMB);
                    $sql->bindValue(":LOGRADOUROEMB", $rel->LOGRADOUROEMB);
                    $sql->bindValue(":LOCALIZACAOEMB", $rel->LOCALIZACAOEMB);
                    $sql->bindValue(":ITIDAPREV", $rel->ITIDAPREV);
                    
                    //DESEMBARQUE
                    $sql->bindValue(":LATITUDEDESEMB", $rel->LATITUDEDESEMB);
                    $sql->bindValue(":LONGITUDEDESEMB", $rel->LONGITUDEDESEMB);
                    $sql->bindValue(":PONTOREFERDESEMB", $rel->PONTOREFERDESEMB);
                    $sql->bindValue(":HORAMARCACAODESEMB", $rel->HORAMARCACAODESEMB);
                    $sql->bindValue(":LOGRADOURODESEMB", $rel->LOGRADOURODESEMB);
                    $sql->bindValue(":LOCALIZACAODESEMB", $rel->LOCALIZACAODESEMB);
                    $sql->bindValue(":ITVOLTAPREV", $rel->ITVOLTAPREV);
                    
                    //VIAGEM
                    $sql->bindValue(":ITIREALIZADOOK", $rel->ITIREALIZADOOK);
                    $sql->bindValue(":SENTREALIZADO", $rel->SENTREALIZADO);
                    $sql->bindValue(":DATAREALIZADO", $rel->DATAREALIZADO);
                    $sql->bindValue(":PREVOK", $rel->PREVOK);

                    try {

                        $sql->execute();

                    } catch (\Throwable $th) {

                        $errorItens += 1;                       

                    }


                }

                usleep( 200 );
            }

            
            if($errorItens == 0){
               
                $this->updateRel(1, 1, $agendamento->id, $agendamento->usuario_id);

            }else{

                $this->updateRel(1, 3, $agendamento->id);               

            }

            usleep( 1000 );
            
        }

        return true;
    }

    private function createAgendaConsolidado()
    {
        $this->insertDebug('CRON', 'INICIO REL CONSOLIDADO');

        $agendamentos = $this->getAgendamentos(2);

        if($agendamentos){
            $this->insertAgendaConsolidado($agendamentos);
        }

        $this->insertDebug('CRON', 'FINAL REL CONSOLIDADO');
    }

    private function insertAgendaConsolidado($agendamentos)
    {
        foreach($agendamentos as $agendamento){

            $hasPax     = array();
            $countPax   = array();
            $viagens    = array();
            $errorItens = 0;
                                        
            $dadosRel = $this->relGet->getDadosConsolidadoViagem($agendamento, 1, $this->dbSys, $agendamento->usuario_id);

            foreach($dadosRel AS $k => $rel)
            {
                $rel = (Object) $rel;

                $sql = $this->dbSys->prepare("INSERT INTO agenda_consolidado_ready SET usuario_id = :usuario_id, agenda_id = :agenda_id, GRUPO_LINHA_ID = :GRUPO_LINHA_ID, IDVIAGEM = :IDVIAGEM, IDLINHA = :IDLINHA, ITINERARIO_ID = :ITINERARIO_ID, NOMELINHA = :NOMELINHA, GRUPO = :GRUPO,  PREFIXO = :PREFIXO, TIPO = :TIPO, SENTIDO = :SENTIDO, TRECHO = :TRECHO, DESCRICAO = :DESCRICAO, DATAVIAGEM = :DATAVIAGEM, DATAINIPREVISTO = :DATAINIPREVISTO, DATAINIREAL = :DATAINIREAL, DATAFIMPREV = :DATAFIMPREV, DATAFIMREAL = :DATAFIMREAL, KMVIAGEM = :KMVIAGEM, IDVEIC = :IDVEIC, PLACA = :PLACA, PREFIXOVEIC = :PREFIXOVEIC, CAPACIDADEVEIC = :CAPACIDADEVEIC, LIMITEVEIC = :LIMITEVEIC, PAXCADASTRADO = :PAXCADASTRADO, PAXCADASTRADOV = :PAXCADASTRADOV, TAG = :TAG, TIPO_USUARIO = :TIPO_USUARIO");

                $sql->bindValue(":usuario_id", $agendamento->usuario_id);
                $sql->bindValue(":agenda_id", $agendamento->id);
                $sql->bindValue(":GRUPO_LINHA_ID", $rel->GRUPO_LINHA_ID);
                $sql->bindValue(":IDVIAGEM", $rel->IDVIAGEM);
                $sql->bindValue(":IDLINHA", $rel->IDLINHA);
                $sql->bindValue(":ITINERARIO_ID", $rel->ITINERARIO_ID);
                $sql->bindValue(":NOMELINHA", $rel->NOMELINHA);
                $sql->bindValue(":GRUPO", $rel->GRUPO);
                $sql->bindValue(":PREFIXO", $rel->PREFIXO);
                $sql->bindValue(":TIPO", $rel->TIPO);
                $sql->bindValue(":SENTIDO", $rel->SENTIDO);
                $sql->bindValue(":TRECHO", $rel->TRECHO);
                $sql->bindValue(":DESCRICAO", $rel->DESCRICAO);
                $sql->bindValue(":DATAVIAGEM", $rel->DATAVIAGEM);
                $sql->bindValue(":DATAINIPREVISTO", $rel->DATAINIPREVISTO);
                $sql->bindValue(":DATAINIREAL", $rel->DATAINIREAL);
                $sql->bindValue(":DATAFIMPREV", $rel->DATAFIMPREV);
                $sql->bindValue(":DATAFIMREAL", $rel->DATAFIMREAL);
                $sql->bindValue(":KMVIAGEM", $rel->KMVIAGEM);
                $sql->bindValue(":IDVEIC", $rel->IDVEIC);
                $sql->bindValue(":PLACA", $rel->PLACA);
                $sql->bindValue(":PREFIXOVEIC", $rel->PREFIXOVEIC);
                $sql->bindValue(":CAPACIDADEVEIC", $rel->CAPACIDADEVEIC);
                $sql->bindValue(":LIMITEVEIC", $rel->LIMITEVEIC);
                $sql->bindValue(":PAXCADASTRADO", $rel->PAXCADASTRADO);
                $sql->bindValue(":PAXCADASTRADOV", $rel->PAXCADASTRADOV);
                $sql->bindValue(":TAG", $rel->TAG);
                $sql->bindValue(":TIPO_USUARIO", $rel->TIPO_USUARIO);

                try {

                    $sql->execute();

                    $pax = 0;

                    if( (!isset($hasPax[$rel->IDVIAGEM]) || !in_array($rel->TAG, $hasPax[$rel->IDVIAGEM])) && $rel->TIPO_USUARIO == 2 ){

                        $hasPax[$rel->IDVIAGEM][] = $rel->TAG;

                        if(isset($countPax[$rel->IDVIAGEM]))
                            $countPax[$rel->IDVIAGEM] += 1;
                        else 
                            $countPax[$rel->IDVIAGEM] = 1;

                    }

                    if(isset($countPax[$rel->IDVIAGEM]))
                        $pax = $countPax[$rel->IDVIAGEM];

                    $viagens['V'][$rel->IDVIAGEM]['PAXEMBARCADO'] = $pax;
                    $viagens['V'][$rel->IDVIAGEM]['usuario_id'] = $agendamento->usuario_id;
                    $viagens['V'][$rel->IDVIAGEM]['agenda_viagem_id'] = 'consolidado-'.$agendamento->id;
                    $viagens['V'][$rel->IDVIAGEM]['data_inicio'] = $agendamento->data_inicio;
                    $viagens['V'][$rel->IDVIAGEM]['data_fim'] = $agendamento->data_fim;

                } catch (\Throwable $th) {

                    $errorItens += 1;                  

                }

                usleep( 200 );
            }

            $errorViagem = $this->createTrips($viagens, $errorItens = 0);
            
            if($errorItens == 0){

                $this->updateRel(2, 1, $agendamento->id, $agendamento->usuario_id, $errorViagem);
                
                $this->checkTripErros(2, $errorViagem, $agendamento->id);

            }else{

                $this->updateRel(2, 3, $agendamento->id);

            }

            usleep( 1000 );
            
        }
    }

    private function createAgendaSintetico()
    {
        $this->insertDebug('CRON', 'INICIO REL SINTETICO');

        $agendamentos = $this->getAgendamentos(3);

        if($agendamentos){
            $this->insertAgendaSintetico($agendamentos);
        }

        $this->insertDebug('CRON', 'FINAL REL SINTETICO');
    }

    private function insertAgendaSintetico($agendamentos)
    {
        foreach($agendamentos as $agendamento){

            $hasPax     = array();
            $countPax   = array();
            $viagens    = array();
            $errorItens = 0;

            $dadosRel = $this->relGet->getDadosSintetico($agendamento, 1, $this->dbSys, $agendamento->usuario_id);

            foreach($dadosRel AS $k => $rel)
            {
                $rel = (Object) $rel;

                $sql = $this->dbSys->prepare("INSERT INTO agenda_sintetico_ready SET usuario_id = :usuario_id, agenda_id = :agenda_id, GRUPO_LINHA_ID = :GRUPO_LINHA_ID, IDVIAGEM = :IDVIAGEM, IDLINHA = :IDLINHA, ITINERARIO_ID = :ITINERARIO_ID, NOMELINHA = :NOMELINHA, GRUPO = :GRUPO,  PREFIXO = :PREFIXO, TIPO = :TIPO, SENTIDO = :SENTIDO, TRECHO = :TRECHO, DESCRICAO = :DESCRICAO, DATAVIAGEM = :DATAVIAGEM, DATAINIPREVISTO = :DATAINIPREVISTO, DATAINIREAL = :DATAINIREAL, DATAFIMPREV = :DATAFIMPREV, DATAFIMREAL = :DATAFIMREAL, KMVIAGEM = :KMVIAGEM, IDVEIC = :IDVEIC, PLACA = :PLACA, PREFIXOVEIC = :PREFIXOVEIC, CAPACIDADEVEIC = :CAPACIDADEVEIC, LIMITEVEIC = :LIMITEVEIC, PAXCADASTRADO = :PAXCADASTRADO, PAXCADASTRADOV = :PAXCADASTRADOV, TAG = :TAG, TIPO_USUARIO = :TIPO_USUARIO");

                $sql->bindValue(":usuario_id", $agendamento->usuario_id);
                $sql->bindValue(":agenda_id", $agendamento->id);
                $sql->bindValue(":GRUPO_LINHA_ID", $rel->GRUPO_LINHA_ID);
                $sql->bindValue(":IDVIAGEM", $rel->IDVIAGEM);
                $sql->bindValue(":IDLINHA", $rel->IDLINHA);
                $sql->bindValue(":ITINERARIO_ID", $rel->ITINERARIO_ID);
                $sql->bindValue(":NOMELINHA", $rel->NOMELINHA);
                $sql->bindValue(":GRUPO", $rel->GRUPO);
                $sql->bindValue(":PREFIXO", $rel->PREFIXO);
                $sql->bindValue(":TIPO", $rel->TIPO);
                $sql->bindValue(":SENTIDO", $rel->SENTIDO);
                $sql->bindValue(":TRECHO", $rel->TRECHO);
                $sql->bindValue(":DESCRICAO", $rel->DESCRICAO);
                $sql->bindValue(":DATAVIAGEM", $rel->DATAVIAGEM);
                $sql->bindValue(":DATAINIPREVISTO", $rel->DATAINIPREVISTO);
                $sql->bindValue(":DATAINIREAL", $rel->DATAINIREAL);
                $sql->bindValue(":DATAFIMPREV", $rel->DATAFIMPREV);
                $sql->bindValue(":DATAFIMREAL", $rel->DATAFIMREAL);
                $sql->bindValue(":KMVIAGEM", $rel->KMVIAGEM);
                $sql->bindValue(":IDVEIC", $rel->IDVEIC);
                $sql->bindValue(":PLACA", $rel->PLACA);
                $sql->bindValue(":PREFIXOVEIC", $rel->PREFIXOVEIC);
                $sql->bindValue(":CAPACIDADEVEIC", $rel->CAPACIDADEVEIC);
                $sql->bindValue(":LIMITEVEIC", $rel->LIMITEVEIC);
                $sql->bindValue(":PAXCADASTRADO", $rel->PAXCADASTRADO);
                $sql->bindValue(":PAXCADASTRADOV", $rel->PAXCADASTRADOV);
                $sql->bindValue(":TAG", $rel->TAG);
                $sql->bindValue(":TIPO_USUARIO", $rel->TIPO_USUARIO);

                try {

                    $sql->execute();

                    $pax = 0;

                    if( (!isset($hasPax[$rel->IDVIAGEM]) || !in_array($rel->TAG, $hasPax[$rel->IDVIAGEM])) && $rel->TIPO_USUARIO == 2 ){

                        $hasPax[$rel->IDVIAGEM][] = $rel->TAG;

                        if(isset($countPax[$rel->IDVIAGEM]))
                            $countPax[$rel->IDVIAGEM] += 1;
                        else 
                            $countPax[$rel->IDVIAGEM] = 1;

                    }

                    if(isset($countPax[$rel->IDVIAGEM]))
                        $pax = $countPax[$rel->IDVIAGEM];

                    $viagens['V'][$rel->IDVIAGEM]['PAXEMBARCADO'] = $pax;
                    $viagens['V'][$rel->IDVIAGEM]['usuario_id'] = $agendamento->usuario_id;
                    $viagens['V'][$rel->IDVIAGEM]['agenda_viagem_id'] = 'sintetico-'.$agendamento->id;
                    $viagens['V'][$rel->IDVIAGEM]['data_inicio'] = $agendamento->data_inicio;
                    $viagens['V'][$rel->IDVIAGEM]['data_fim'] = $agendamento->data_fim;

                } catch (\Throwable $th) {

                    $errorItens += 1;

                }

                usleep( 200 );
            }

            $errorViagem = $this->createTrips($viagens, $errorItens = 0);
            
            if($errorItens == 0){

                $this->updateRel(3, 1, $agendamento->id, $agendamento->usuario_id, $errorViagem);
                
                $this->checkTripErros(3, $errorViagem, $agendamento->id);

            }else{

                $this->updateRel(3, 3, $agendamento->id);

            }

            usleep( 1000 );
            
        }
    }

    private function getAgendamentos($type)
    {
        switch ($type) {
            case 1:
                $tableName = 'agenda_analitico';
                $noAgendaTxt = 'NENHUM REL ANALITICO';
                break;
            case 2:
                $tableName = 'agenda_consolidado';
                $noAgendaTxt = 'NENHUM REL CONSOLIDADO';
                break;
            case 3:
                $tableName = 'agenda_sintetico';
                $noAgendaTxt = 'NENHUM REL SINTETICO';
                break;
            default:
                $tableName = false;
                break;
        }

        if(!$tableName){
            return false;
        }

        $sqlAg = $this->dbSys->prepare("SELECT $tableName.*, users.ativo FROM $tableName JOIN users ON users.id = $tableName.usuario_id WHERE $tableName.status IN($this->statusCreate) AND $tableName.deleted_at is null AND users.ativo = 1 AND users.deleted_at is null ORDER BY $tableName.created_at ASC");

        $sqlAg->execute();

        if($sqlAg->rowCount() == 0) {

            $this->insertDebug('CRON', $noAgendaTxt);

            return false;
    
        }

        return $sqlAg->fetchAll(PDO::FETCH_OBJ);
        
    }

    private function updateRel($type, $status = 3, $agendamentoId = false, $usuarioId = 0, $errorViagem = null){

        switch ($type) {
            case 1:
                $tableName = 'agenda_analitico';
                $statusTxt = 'INSERIR REL ANALITICO';
                break;
            case 2:
                $tableName = 'agenda_consolidado';
                $statusTxt = 'INSERIR REL CONSOLIDADO';
                break;
            case 3:
                $tableName = 'agenda_sintetico';
                $statusTxt = 'INSERIR REL SINTETICO';
                break;
            default:
                $tableName = false;
                break;
        }

        if($tableName && $agendamentoId){

            $statusTxt .= " $agendamentoId";

            $now = date("Y-m-d H:i:s");

            $sql = $this->dbSys->prepare("UPDATE $tableName SET status = $status" . ($errorViagem !== null ? ", errorViagem = {$errorViagem}" : "") . ", updated_at = '$now' where id = {$agendamentoId}");

            $sql->execute();

            if($status == 1 && $usuarioId != 0 && $this->sendNotify){
                $this->sendEmailNotify($type, $agendamentoId, $usuarioId, $tableName);
            }

            if($status == 3){
                $tableReadyName = $tableName . '_ready';
                $sqlDelI = $this->dbSys->prepare("DELETE FROM $tableReadyName WHERE agenda_id = {$agendamentoId}");
                $sqlDelI->execute();
            }          

            $this->insertDebug(($status == 1) ? 'OK':'ERROR', $statusTxt);

        }

        return true;

    }

    private function sendEmailNotify($type, $agendamentoId, $usuarioId, $tableName)
    {

        switch ($type) {
            case 1:
                $title = 'Analítico por Passageiros';
                $notifyTxt = "ENVIADO NOTIFY REL ANALITICO";
                break;
            case 2:
                $title = 'Analítico por Viagem';
                $notifyTxt = "ENVIADO NOTIFY REL CONSOLIDADO";
                break;
            case 3:
                $title = 'Sintético Viagem';
                $notifyTxt = "ENVIADO NOTIFY REL SINTETICO";
                break;
            default:
                $title = false;
                break;
        }

        if($title){

            $notifyTxt .= " $agendamentoId";

            $sql= $this->dbSys->prepare("SELECT email FROM users WHERE id = {$usuarioId} AND deleted_at is null LIMIT 1");
            $sql->execute();

            if($sql->rowCount() == 1) {

                $email = $sql->fetch();
                $body = $this->relGet->templateEmailRels($title, $agendamentoId);
                $titleEmail = $this->portalName." - Agendamento ".$title." # ".$agendamentoId." está pronto!";
                $email = trim($email[0]);

                try{

                    $this->notify->sendMailGeneric([$email], $titleEmail, $body);
                    $sql = $this->dbSys->prepare("UPDATE $tableName SET notify = 1 where id = {$agendamentoId}");
                    $sql->execute();

                    $this->insertDebug('OK', $notifyTxt);
                
                }catch (\Throwable $th) {
                
                    $this->insertDebug('ERROR', $notifyTxt);
                    
                }
            }

        }

        return true;

    }

    private function notifyAgendas()
    {
        $notifynalitico     = true;
        $notifyConsolidado  = true;
        $notifySintetico    = true;

        $this->insertDebug('CHECK NOTIFY', 'INICIO RELS');

        if($notifynalitico){
            
            $this->getAgendasToNotify(1);

        }

        if($notifyConsolidado){
            
            $this->getAgendasToNotify(2);

        }

        if($notifySintetico){
            
            $this->getAgendasToNotify(3);

        }

        $this->insertDebug('CHECK NOTIFY', 'FINAL OK RELS');
    }

    private function getAgendasToNotify($type)
    {
        switch ($type) {
            case 1:
                $tableName = 'agenda_analitico';
                $notifyStartTxt = "INICIO NOTIFY REL ANALITICO";
                $notifyEndTxt = "FINAL NOTIFY REL ANALITICO";
                break;
            case 2:
                $tableName = 'agenda_consolidado';
                $notifyStartTxt = "INICIO NOTIFY REL CONSOLIDADO";
                $notifyEndTxt = "FINAL NOTIFY REL CONSOLIDADO";
                break;
            case 3:
                $tableName = 'agenda_sintetico';
                $notifyStartTxt = "INICIO NOTIFY REL SINTETICO";
                $notifyEndTxt = "FINAL NOTIFY REL SINTETICO";
                break;
            default:
                $tableName = false;
                break;
        }

        if($tableName){

            $this->insertDebug('CHECK NOTIFY', $notifyStartTxt);

            $sql= $this->dbSys->prepare("SELECT $tableName.id, $tableName.usuario_id, users.email FROM $tableName JOIN users ON users.id = $tableName.usuario_id WHERE $tableName.status = 1 AND $tableName.notify = 0 AND $tableName.deleted_at is null AND users.deleted_at is null ORDER BY $tableName.updated_at ASC");
            $sql->execute();

            $allAgendas = $sql->fetchAll(PDO::FETCH_OBJ);

            foreach($allAgendas as $agenda){
                
                $this->sendEmailNotify($type, $agenda->id, $agenda->usuario_id, $tableName);

                usleep(1000);
            }

            $this->insertDebug('CHECK NOTIFY', $notifyEndTxt);

        }

        return true;

    }

    private function createTrips($viagens, $errorItens = 0)
    {
        
        $errorViagem = 0;

        if(count($viagens) > 0 && $errorItens == 0){

            $viagens = (Object) $viagens;

            foreach($viagens->V AS $k => $viagem)
            {
                if($errorViagem != 0){break;}

                $viagem = (Object) $viagem;

                if ($viagem->PAXEMBARCADO > 0){

                    $viagem->todosGrupos = 1;

                    $dadosRelViagem = $this->relGet->getDadosAnaliticoPassageiro($viagem, $k, $this->dbSys, $viagem->usuario_id);

                    foreach ($dadosRelViagem AS $relViagem) 
                    {
                        $relsV = (Object) $relViagem;

                        foreach($relsV AS $relV)
                        {

                            $relV = (Object) $relV;

                            $sql = $this->dbSys->prepare("INSERT INTO agenda_analitico_ready SET usuario_id = :usuario_id, agenda_id = :agenda_id, agenda_viagem_id = :agenda_viagem_id, viagemID = :viagemID, PREF = :PREF, PLACA = :PLACA, CODIGO = :CODIGO, GRUPO = :GRUPO, NOME = :NOME, MATRICULA = :MATRICULA, STATUS = :STATUS, LATITUDEEMB = :LATITUDEEMB, LONGITUDEEMB = :LONGITUDEEMB, PONTOREFEREMB = :PONTOREFEREMB, HORAMARCACAOEMB = :HORAMARCACAOEMB, LOGRADOUROEMB = :LOGRADOUROEMB, LOCALIZACAOEMB = :LOCALIZACAOEMB, ITIDAPREV = :ITIDAPREV, LATITUDEDESEMB = :LATITUDEDESEMB, LONGITUDEDESEMB = :LONGITUDEDESEMB, PONTOREFERDESEMB = :PONTOREFERDESEMB, HORAMARCACAODESEMB = :HORAMARCACAODESEMB, LOGRADOURODESEMB = :LOGRADOURODESEMB, LOCALIZACAODESEMB = :LOCALIZACAODESEMB, ITVOLTAPREV = :ITVOLTAPREV, ITIREALIZADOOK = :ITIREALIZADOOK, SENTREALIZADO = :SENTREALIZADO, DATAREALIZADO = :DATAREALIZADO, PREVOK = :PREVOK");

                            $sql->bindValue(":usuario_id", $viagem->usuario_id);
                            $sql->bindValue(":agenda_id", 0);
                            $sql->bindValue(":agenda_viagem_id", $viagem->agenda_viagem_id);
                            $sql->bindValue(":viagemID", $k);

                            //VEICULO
                            $sql->bindValue(":PREF", $relV->PREF);
                            $sql->bindValue(":PLACA", $relV->PLACA);

                            //PAX
                            $sql->bindValue(":CODIGO", $relV->CODIGO);
                            $sql->bindValue(":GRUPO", $relV->GRUPO);
                            $sql->bindValue(":NOME", $relV->NOME);
                            $sql->bindValue(":MATRICULA", $relV->MATRICULA);
                            $sql->bindValue(":STATUS", $relV->STATUS);

                            //EMBARQUE
                            $sql->bindValue(":LATITUDEEMB", $relV->LATITUDEEMB);
                            $sql->bindValue(":LONGITUDEEMB", $relV->LONGITUDEEMB);
                            $sql->bindValue(":PONTOREFEREMB", $relV->PONTOREFEREMB);
                            $sql->bindValue(":HORAMARCACAOEMB", $relV->HORAMARCACAOEMB);
                            $sql->bindValue(":LOGRADOUROEMB", $relV->LOGRADOUROEMB);
                            $sql->bindValue(":LOCALIZACAOEMB", $relV->LOCALIZACAOEMB);
                            $sql->bindValue(":ITIDAPREV", $relV->ITIDAPREV);
                            
                            //DESEMBARQUE
                            $sql->bindValue(":LATITUDEDESEMB", $relV->LATITUDEDESEMB);
                            $sql->bindValue(":LONGITUDEDESEMB", $relV->LONGITUDEDESEMB);
                            $sql->bindValue(":PONTOREFERDESEMB", $relV->PONTOREFERDESEMB);
                            $sql->bindValue(":HORAMARCACAODESEMB", $relV->HORAMARCACAODESEMB);
                            $sql->bindValue(":LOGRADOURODESEMB", $relV->LOGRADOURODESEMB);
                            $sql->bindValue(":LOCALIZACAODESEMB", $relV->LOCALIZACAODESEMB);
                            $sql->bindValue(":ITVOLTAPREV", $relV->ITVOLTAPREV);
                            
                            //VIAGEM
                            $sql->bindValue(":ITIREALIZADOOK", $relV->ITIREALIZADOOK);
                            $sql->bindValue(":SENTREALIZADO", $relV->SENTREALIZADO);
                            $sql->bindValue(":DATAREALIZADO", $relV->DATAREALIZADO);
                            $sql->bindValue(":PREVOK", $relV->PREVOK);

                            try {

                                $sql->execute();

                            } catch (\Throwable $th) {

                                $errorViagem += 1;

                            }

                        }

                        usleep( 200 );
                    }

                }
                
            }
        }

        return $errorViagem;
    }

    private function checkTripErros($type, $errorViagem, $agendamentoId)
    {
        
        if($errorViagem != 0){
            switch ($type) {
                case 2:
                    $todel = "consolidado-$agendamentoId";
                    break;
                case 3:
                    $todel = "sintetico-$agendamentoId";
                    break;
                default:
                    $todel = false;
                    break;
            }
    
            if($todel){
                $sqlDelV = $this->dbSys->prepare("DELETE FROM agenda_analitico_ready WHERE agenda_id = 0 AND agenda_viagem_id = '{$todel}'");
                $sqlDelV->execute();
            }
        }
        
    }

    private function insertDebug($motive, $content)
    {
        if ($this->saveDebug) {
            $now = date("Y-m-d H:i:s");
            $sqlDebug = $this->dbSys->prepare("INSERT INTO debug_geral (motive, content, created_at) VALUES ('$motive', '$content', '$now')");
            $sqlDebug->execute();
        }
    }

}

$cronInstance = new createAgendamentos();
$cronInstance->executeCron();

?>