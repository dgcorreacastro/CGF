<?php 

ini_set('memory_limit', '-1');
set_time_limit(0);
date_default_timezone_set('America/Sao_Paulo');

try {
    
	$dbSys = new \PDO("mysql:dbname=#DATABASE_NAME#;charset=utf8;host=#DATABASE_ADDRESS#", '#DATABASE_USER#', '#DATABASE_PASSWORD#'); // TODO: POPULATE EACH VARIABLE WITH IT RESPECTIVE VALUE

    $currentYear = date("Y");
    $currentMonth = date("m");

    $tablesQuery = $dbSys->prepare("SHOW TABLES LIKE 'positions\_%'");
    $tablesQuery->execute();
    $tables = $tablesQuery->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {

        $tableName = substr($table, strpos($table, '_') + 1);
        $tableYear = substr($tableName, 0, 4);
        $tableMonth = substr($tableName, 5);

        if ($tableYear < $currentYear || ($tableYear == $currentYear && $tableMonth < $currentMonth)) {

            $table_logs = 'positions_logs_' . substr($table, strpos($table, '_') + 1);

            $dropQuery = $dbSys->prepare("DROP TABLE IF EXISTS `$table`");
            $dropQuery->execute();

            $dropQueryLogs = $dbSys->prepare("DROP TABLE IF EXISTS `$table_logs`");
            $dropQueryLogs->execute();

        }
    }

} catch(\PDOException $e) {
    echo 'Erro ao conectar no banco local';
    die;
}

?>