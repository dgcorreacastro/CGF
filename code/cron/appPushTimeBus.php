
<?php 

ini_set('memory_limit', '-1');
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

date_default_timezone_set('America/Sao_Paulo');

set_time_limit(0);

require '../environment.php';

require_once  __DIR__ . '/../Services/TalentumNotification.php';

require_once  __DIR__ . '/../helpers/debug.php';

require_once  __DIR__ . '/../helpers/cnn.php';

$notify   = new TalentumNotification;

$am       = date("Y_m");

$sql      = "SELECT VIAGENS.*, ITI.LINHA_ID FROM VIAGENS 
JOIN ITINERARIOS ITI ON ITI.ID = VIAGENS.ITINERARIO_ID
WHERE ITI.SENTIDO = 0 AND DATAHORA_FINAL_REALIZADO IS NULL AND DATAHORA_INICIAL_PREVISTO BETWEEN DATEADD(HOUR, -1, DATAHORA_INICIAL_PREVISTO) AND DATEADD(HOUR, 1, DATAHORA_INICIAL_PREVISTO)";

$cons     = $pdo->query($sql);    
$viagens  = $cons->fetchAll();


foreach($viagens AS $ddr) {

    $ddr = (Object) $ddr;
 
    /**
     * VERIFICAO SE TEM DADOS DO CARRO 
     */
    $ajustHourStart = date("Y-m-d H:i:s", strtotime("+2 hours", strtotime($ddr->DATAHORA_INICIAL_PREVISTO)));
    $ajustHourEnd = date("Y-m-d H:i:s", strtotime("+4 hours", strtotime($ddr->DATAHORA_FINAL_PREVISTO)));

    // $ajustHourStart = date("Y-m-d H:i:s", strtotime($ddr->DATAHORA_INICIAL_PREVISTO));
    // $ajustHourEnd = date("Y-m-d H:i:s", strtotime($ddr->DATAHORA_FINAL_PREVISTO));
       
    $sqlc = $dbSys->prepare("SELECT * FROM piccolotur_rel.positions_{$am} WHERE veiculoId = {$ddr->VEICULO_ID} AND dataHora BETWEEN '$ajustHourStart' AND '$ajustHourEnd' ORDER BY dataHora DESC LIMIT 1");

    $sqlc->execute();
    $hasData = $sqlc->fetch(PDO::FETCH_OBJ);
  
    if ($hasData) {

        $viagemID = $ddr->ID;

        /**
         * PEGA TODOS OS PONTOS DE REF DA VIAGENS
         */
        $sql = "SELECT PR.ID, HV.DATAHORA_ENTRADA_PREVISTO, HV.DATAHORA_SAIDA_PREVISTO, HV.DATAHORA_SAIDA_REALIZADO, HV.PONTO_ITINERARIO_ID
        FROM PONTOS_REFERENCIA PR 
        JOIN PONTOS_ITINERARIO PTI ON PTI.PONTO_REFERENCIA_ID = PR.ID 
        JOIN HORARIOS_VIAGEM HV ON HV.PONTO_ITINERARIO_ID = PTI.ID 
        WHERE HV.VIAGEM_ID = {$viagemID} AND PR.CATEGORIA_ID IN (3, 4, 5)
        ORDER BY HV.PONTO_ITINERARIO_ID";

        $pontoRef = $pdo->query($sql);   
        $ptsRef   = $pontoRef->fetchAll();
        $lastReal = "";
        $lastPrev = "";
        $time     = 0;
        $auxTime  = 0;

        foreach($ptsRef AS $ref) {
            
            $ref    = (object) $ref;

            // Se já passou pelo ponto 
            if ($ref->DATAHORA_SAIDA_REALIZADO != null) {
                $lastReal = $ref->DATAHORA_SAIDA_REALIZADO;
                $lastPrev = $ref->DATAHORA_SAIDA_PREVISTO ?? $ref->DATAHORA_ENTRADA_PREVISTO;
                $auxTime  = 0;
                $time     = 0;
                
                $line   = $ddr->LINHA_ID;
                $dot    = $ref->ID; 
                $topic  = "CGFPASS_L{$line}P{$dot}";
                $title  = "ATENÇÃO";
                $body   = "Parece que o veículo já passou pelo seu ponto.";

                if (!$notify->alreadySentMessage($topic, $line, $dot, $time, $dbSys)) {
                    try {

                        $notify->sendMessage($title, $body, $topic, $line, $dot, 0, $time, $dbSys);
    
                    } catch (\Throwable $th) { }
                }

                continue;
            }

            if ($lastPrev == "") {
                $lastPrev = $ref->DATAHORA_SAIDA_PREVISTO ?? $ref->DATAHORA_ENTRADA_PREVISTO;
                $lastReal = $ref->DATAHORA_SAIDA_REALIZADO ?? "";
                continue;
            }

            $currentPrev = $ref->DATAHORA_SAIDA_PREVISTO ?? $ref->DATAHORA_ENTRADA_PREVISTO;
            $diffPrev    = intval( (strtotime($currentPrev) - strtotime($lastPrev)) / 60 );

            if ($diffPrev > 0){
                $auxTime += $diffPrev;
                $lastPrev = $currentPrev;
            }

            if ($lastReal) {
                $line            = $ddr->LINHA_ID;
                $dot             = $ref->ID; 
                $topic           = "CGFPASS_L{$line}P{$dot}";

                $now             = date("Y-m-d H:i:s");
                $hourPrevistReal = date("Y-m-d H:i:s", strtotime("+ $auxTime minutes", strtotime($lastReal)));
                $time            = $hourPrevistReal > $now ? intval( (strtotime($hourPrevistReal) - strtotime($now)) / 60 ) : 0;

                // Se o minuto for maior que 0 e menor que 11 e se não foi ainda enviado a mensagem
                if ($time > 0 && $time < 11 && !$notify->alreadySentMessage($topic, $line, $dot, $time, $dbSys)) {

                    $msg = $time == 1 ? "Falta 1 minuto" : "Faltam ".$time." minutos";

                    /**
                     * ENVIA A MENSAGEM 
                     */
                    $title  = "ATENÇÃO";
                    $body   = $msg." para chegada do veículo ao seu Ponto!";
            
                    try {

                        $notify->sendMessage($title, $body, $topic, $line, $dot, 0, $time, $dbSys);
                        echo `ENVIADO para o ponto {$dot} \n`;

                    } catch (\Throwable $th) {  echo `ERRO no envio do {$dot} \n`; }

                }

            }
        
            usleep(700);
        }

    }

}

echo "FIM";
exit;





