<?php

$dbnameL = ENVIRONMENT == 'development' ? 'piccolotur_relTeste' : 'piccolotur_rel';

try {
	$dbSys = new \PDO("mysql:dbname=#DATABASE_NAME#;charset=utf8;host=#DATABASE_ADDRESS#", '#DATABASE_USER#', '#DATABASE_PASSWORD#'); // TODO: POPULATE EACH VARIABLE WITH IT RESPECTIVE VALUE
} catch(\PDOException $e) {
    echo 'Erro ao conectar no banco local';
    die;
}

try {
    $pdo = new \PDO ("dblib:host=#DATABASE_ADDRESS#;dbname=#DATABASE_NAME#;charset=utf8","#DATABASE_USER#","#DATABASE_PASSWORD#"); // TODO: POPULATE EACH VARIABLE WITH IT RESPECTIVE VALUE
} catch (\Throwable $th) {
    $th = addslashes($th);
    $sql = $dbSys->prepare("INSERT INTO debug_geral (motive, content, created_at) VALUE ('ERROR CRON PUSH', '{$th}', NOW())");
	$sql->execute();
    die;
}
