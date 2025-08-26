<?php 

ini_set('memory_limit', '-1');
set_time_limit(0);

##################################################################################
########## CRON PARA ATUALIZAR REGISTROS VINDO DA VELTRAC ########################
############## ATUALIZAR O COLABORADOR MODULO ESCALA #############################
##################################################################################
require '../environment.php';

require_once  __DIR__ . '/../helpers/debug.php';

require_once  __DIR__ . '/../helpers/cnn.php';

##################################################################################
############## BUSCA TODOS RES INTERNOS E BUSCA NA VELTRAC #######################
##################################################################################
    $gru  = 11; // por enquanto só tem a Eurofarma. Se precisar adicionar para outros grupos será necessário deixar dinamico e em loop
    $sqlc = $dbSys->prepare("SELECT re FROM colaboradores WHERE deleted_at IS NULL AND grupoID = {$gru}");
    $sqlc->execute();
    $allc = $sqlc->fetchAll(PDO::FETCH_OBJ);

    $allRes = array();

    foreach( $allc AS $col)
    {
        $allRes[] = $col->re;
    }
  
    $in = implode(",", $allRes);

    // MATRICULA_FUNCIONAL
    // Relacionado o CONTROLE_ACESSO
    $am  = date("Y_m", strtotime("- 1 day"));
    $grps= 359; // por enquanto só tem a Eurofarma. Se precisar adicionar para outros grupos será necessário deixar dinamico e em loop
    $start = date("Y-m-d", strtotime("-15 days")) . " 00:00:00";
    $end = date("Y-m-d") . " 23:23:23";

    $sql        = "SELECT  
                        CA.MATRICULA_FUNCIONAL,
                        CAE.LATITUDE,
                        CAE.LONGITUDE,
                        CI.ESTADO
                    FROM CONTROLE_ACESSO AS CA WITH(nolock) 
                    LEFT JOIN CONTROLE_ACESSO_EVENTOS AS CAE WITH(nolock) ON CAE.TAG = CA.TAG AND CAE.DATAHORA BETWEEN '{$start}' AND '{$end}'
                    LEFT JOIN POSICOES_{$am} AS POS WITH(nolock) ON POS.COMUNICACAO_ID = CAE.POSICAO_ID AND POS.DATAHORA BETWEEN '{$start}' AND '{$end}'
                    LEFT JOIN CIDADES CI ON CI.ID = POS.CIDADE 
                    WHERE CA.MATRICULA_FUNCIONAL IN ({$in}) AND CA.ATIVO = 1 AND CA.CONTROLE_ACESSO_GRUPO_ID = {$grps}
                    GROUP BY CA.MATRICULA_FUNCIONAL, CAE.LATITUDE, CAE.LONGITUDE, CI.ESTADO
                ";

    $consulta   = $pdo->query($sql);
    $datas      = $consulta->fetchAll();

    foreach($datas AS $ddr)
    {

        $ddr  = (Object) $ddr;

        $sql = $dbSys->prepare("UPDATE colaboradores SET longitude = :longitude, latitude = :latitude, uf = :uf, fretado = 1, updated_at = NOW() WHERE re = :re AND grupoID = {$gru}");
        $sql->bindValue(":longitude", $ddr->LONGITUDE);
        $sql->bindValue(":latitude", $ddr->LATITUDE);
        $sql->bindValue(":uf", $ddr->ESTADO);
        $sql->bindValue(":re", $ddr->MATRICULA_FUNCIONAL);

        try {
            $sql->execute();
        } catch (\Throwable $th) {
            $th = addslashes($th);
            $sql = $dbSys->prepare("INSERT INTO debug_geral (motive, content, created_at) VALUE ('ERROR UPDATE COLAB', '{$th}', NOW())");
            $sql->execute();
        }

    }

##################################################################################