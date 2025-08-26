<?php 

ini_set('memory_limit', '-1');
set_time_limit(0);
date_default_timezone_set('America/Sao_Paulo');

require_once '../config.php';

require_once  __DIR__ . '/../helpers/debug.php';

require_once  __DIR__ . '/../helpers/cnn.php';

require_once  __DIR__ . '/../Services/TalentumNotification.php';

class sendInfosMail{

    private $dbSys;
    private $notify;
    private $infoTitulo;
    private $baseUrl;
    private $saveDebug;
    private $sendInfos;
    private $testing;
    private $testUsers;

    public function __construct() {

        global $dbSys, $infoTitulo, $baseUrl;
        $this->dbSys = $dbSys;
        $this->infoTitulo = INFO_TITULO;
        $this->baseUrl = BASE_URLB;
        $this->notify = new TalentumNotification;
        $this->saveDebug = true;
        $this->toSendInfos = true;

        //para testes deixar como true
        $this->testing = false;
        //coloque separado por vírgula os ids dos usuários para testes
        $this->testUsers = "210, 211";

    }

    public function executeCron() {

        $this->insertDebug('CRON', 'SEND INFOS MAIL');

        if($this->toSendInfos){

            $this->sendInfos();

        }

        $this->insertDebug('CRON', 'FINAL OK CRON SEND INFOS MAIL');

        exit;
        
    }

    private function sendInfos()
    {
        $created_at     = date("Y-m-d H:i:s", strtotime("-1 day"));
        $groupsToSend   = $this->getGroups();
        $titulo         = $this->infoTitulo;
        $titulo2        = $this->infoTitulo;

        if($groupsToSend){

            foreach($groupsToSend as $k => $group){

                $grupoNome = $group['grupoNome'];
                $emails = $group['emails'];

                if (empty($emails)){
                    $this->insertDebug('CRON', 'NÃO ENCONTROU E-MAILS PARA ENVIAR INFOS '. $grupoNome);
                    continue;
                }

                $titulo2 = $grupoNome ? $titulo2. " - " . $grupoNome : $titulo2;

                foreach($emails as $email){
                    $emailSend = $email['email'];
                    $createToken = $this->createToken($emailSend, $k, $created_at);
                    if(!$createToken){
                        $this->insertDebug('CRON', 'ERRO AO CRIAR O TOKEN '. $emailSend);
                        continue;
                    }

                    $sendEmail = $this->sendEmail($titulo, $titulo2, $emailSend, $createToken, $created_at);
                    if (!$sendEmail){
                        $this->insertDebug('CRON', 'ERRO AO ENVIAR E-MAIL '. $emailSend);
                        continue;
                    }

                }

                usleep(1000);

            }

        }
    }

    private function getGroups()
    {

        $joinUsers = $this->testing ? "US.id IN ({$this->testUsers})" : "US.groupUserID = PG.group_id";
        $sqlGroups = $this->dbSys->prepare("SELECT PG.group_id, US.email, US.id AS usuario_id, GL.NOME AS grupoNome, GL.ID_ORIGIN AS grupoID 
            FROM parameter_group PG
            INNER JOIN users US ON {$joinUsers}
            INNER JOIN grupo_linhas GL ON GL.id = PG.group_id
            WHERE PG.daily_info = 1 AND PG.deleted_at IS NULL AND US.deleted_at IS NULL");
        $sqlGroups->execute();

        if($sqlGroups->rowCount() == 0) {
            $this->insertDebug('CRON', 'NENHUM GRUPO HABILITADO PARA ENVIAR INFOS');
            return false;
        }

        $grupos = [];
        $arrayGroups = $sqlGroups->fetchAll();

        foreach ($arrayGroups as $group) {
            $groupId = $group['group_id'];
            if (!isset($grupos[$groupId])) {
                $grupos[$groupId] = [
                    "grupoNome" => $group['grupoNome'],
                    "emails" => []
                ];
            }
            
            $grupos[$groupId]['emails'][] = [
                "email" => $group['email']
            ]; 
        }

        return $grupos;

    }

    public function createToken($email, $group_id, $created_at) {
        
        $data1 = random_int(100000, 999999) . '|' . $email . '|' . $group_id;
        $token = base64_encode($data1);

        $insertToken = $this->dbSys->prepare("INSERT INTO daily_news_tk (token, created_at) VALUES (:token, :created_at)");
		$insertToken->bindValue(":token", $token);
		$insertToken->bindValue(":created_at", $created_at);

        try {

            $insertToken->execute();
			$idToken = $this->dbSys->lastInsertId();

            $data = $data1 . '|' . $idToken;
            $token = base64_encode($data);
            $updated_at = date("Y-m-d H:i:s");

            $updateToken = $this->dbSys->prepare("UPDATE daily_news_tk SET token = :token, updated_at = :updated_at WHERE id = :id");
            $updateToken->bindValue(":token", $token);
            $updateToken->bindValue(":updated_at", $updated_at);
            $updateToken->bindValue(":id", $idToken);

            try {

                $updateToken->execute();
                return $token;
               
            } catch (\Throwable $th) {
                return false;
            }

        } catch (\Throwable $th) {
            return false;
        }
    }

    private function sendEmail($titulo, $titulo2, $emailSend, $token, $created_at)
    {
        
        $data = date("d/m/Y",strtotime($created_at));

        $titulo = "$titulo $data";

        $body = $this->templateDailly($token, $titulo2, $data);

        $retorno = $this->notify->sendMailGeneric([$emailSend], $titulo, $body);
        if(isset($retorno['status'])){
            return $retorno['status'];
        }else{
            return false;
        }

    }

    private function templateDailly($token, $titulo2, $data)
    {

        $html = "<div style='width:60%;margin: auto;padding: 15px;background-color: white;color:#2a1e52'>";
        $html .= "<div style='text-align: center'>";
        $html .= "<img src='" . $this->baseUrl . "assets/images/logoApp.png' width='150px'>";
        $html .= "</div>";
        $html .= "<h3 style='text-align: center'>" . utf8_decode($titulo2) . "</h3>";
        $html .= "<h4 style='text-align: center'>" . $data . "</h4>";
        $html .= "<hr>";
        $html .= "<p style='text-align: center'>" . utf8_decode("Clique no botão abaixo para ver o relatório:") . "</p>";
        $html .= "<div style='text-align: center;'>";
        $html .= "<a style='display: inline-block; padding: 6px 12px; background-color: #ffc107; color: #000000; text-decoration: none; font-size: 16px;' href='" . $this->baseUrl . "news/daily?token=" . $token . "' target='_blank'>" . utf8_decode("Ver Relatório") . "</a>";
        $html .= "</div>";
        $html .= "<hr>";
        $html .= "</div>";
        
        return $html;
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

$cronInstance = new sendInfosMail();
$cronInstance->executeCron();

?>