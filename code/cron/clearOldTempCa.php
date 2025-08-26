<?php 

ini_set('memory_limit', '-1');
set_time_limit(0);
date_default_timezone_set('America/Sao_Paulo');

##################################################################################
################ CRON PARA LIMPAR CONTROLES DE ACESSO TEMPORÁRIOS ################
##################################################################################
require_once '../config.php';

require_once  __DIR__ . '/../helpers/cnn.php';


class clearOldTempCa{

    private $dbSys;

    public function __construct() {
        global $dbSys;
        $this->dbSys = $dbSys;
    }

    public function executeCron() {

        $now = date("Y-m-d H:i:s");

        $getTempCa = $this->dbSys->prepare("SELECT id FROM temp_ca WHERE created_at <= DATE_SUB(:now, INTERVAL 5 HOUR)");
        $getTempCa->execute([':now' => $now]);

        if($getTempCa->rowCount() > 0){

            $tempCas = $getTempCa->fetchAll(PDO::FETCH_OBJ);

            foreach($tempCas AS $tempCa){

                $controle_acesso_id = 'temp_ca_'.$tempCa->id;
                $tempCaId = $tempCa->id;
                
                $deleteTempCa = $this->dbSys->prepare("DELETE FROM temp_ca WHERE id = :tempCaId");
				$deleteTempCa->bindValue(":tempCaId", $tempCaId);
				$deleteTempCa->execute();

                $deleteDs = $this->dbSys->prepare("DELETE FROM controle_acessos_ds WHERE controle_acesso_id = :controle_acesso_id");
				$deleteDs->bindValue(":controle_acesso_id", $controle_acesso_id);
				$deleteDs->execute();

                $deleteImg = $this->dbSys->prepare("DELETE FROM controle_acessos_pics WHERE controle_acesso_id = :controle_acesso_id");
				$deleteImg->bindValue(":controle_acesso_id", $controle_acesso_id);
				$deleteImg->execute();

            }
        }

        exit;
        
    }

}

$cronInstance = new clearOldTempCa();
$cronInstance->executeCron();

?>