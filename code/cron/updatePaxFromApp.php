<?php 

set_time_limit(0);

require '../environment.php';

require_once  __DIR__ . '/../helpers/debug.php';

require_once  __DIR__ . '/../helpers/cnn.php';

$updateVeltrac = true;
/**
 * 
 * BUSCA TODOS EMBARQUES AINDA NÃO INTEGRADOS E SEM ERRO NA INTEGRAÇÃO 
 * 0 => NÃO INTEGRADO
 * 1 => INTEGRADO
 * 2 => ERRO INTEGRAÇÃO
 * 
 */
$sqlc = $dbSys->prepare("SELECT eqr.id AS IDEMBARQUE, eqr.veiculo_id AS VEICULOID, eqr.group_id as GROUPID, DATE_SUB(eqr.primeira_leitura, INTERVAL 3 HOUR) as FIRSTREAD, DATE_SUB(eqr.segunda_leitura, INTERVAL 3 HOUR) as SECONDREAD, ca.TAG, ca.id AS IDCA, ca.NOME, ca.CONTROLE_ACESSO_GRUPO_ID, ca.MATRICULA_FUNCIONAL
                        FROM piccolotur_rel.embarque_qr_code as eqr 
                        INNER JOIN controle_acessos as ca ON ca.embarqueQrcodeId = eqr.id
                        WHERE eqr.integrado = 0 
                        AND primeira_leitura IS NOT NULL 
                        AND segunda_leitura IS NOT NULL
                        AND eqr.deleted_at IS NULL");

$sqlc->execute();
$embarques = $sqlc->fetchAll(PDO::FETCH_OBJ);

foreach($embarques AS $emb) 
{
    $TAG = $emb->TAG;
    /**
     *  SE NÃO TIVER A TAG BUSCA NA BATIDA DA VELTRAC 
     */
    if (!$TAG) {
        /**
         * BUSCA NA VELTRAC SE ENCONTRA UM REGISTRO NESSE MEIO PERIODO
         */
        $sql      = "SELECT * FROM CONTROLE_ACESSO_EVENTOS WHERE DATAHORA BETWEEN '{$emb->FIRSTREAD}' AND '{$emb->SECONDREAD}' AND VEICULO_ID = {$emb->VEICULOID}";
        $cons     = $pdo->query($sql);    
        $evento   = $cons->fetch();
     
        // SE ENCONTROU A MARCAÇÃO DO PASSAGEIRO
        if ($evento) {
            $TAG = $evento['TAG'];
        } else {
            //CASO NÃO ENCONTRE A MARCAÇÃO, MARCA A INTEGRAÇÃO COMO COM PROBLEMA 
            $sql = $dbSys->prepare("UPDATE piccolotur_rel.embarque_qr_code SET integrado = :INTEGRA where id = :ID");
            $sql->bindValue(":INTEGRA", 2);
            $sql->bindValue(":ID", $emb->IDEMBARQUE);
            $sql->execute();

            continue;
        }
      
    }

    $originID = 0;

    if ($updateVeltrac) {
        /**
         * ATUALIZA NA VELTRAC
         */
        // VERIFICA SE TEM O CÓDIGO/TAG
        $sql = "SELECT CODIGO FROM BD_CLIENTE.dbo.RFID WHERE CODIGO = {$TAG};";
        $con = $pdo->query($sql); 
        $data= $con->fetch();
 
        // CASO NÃO TENHA CADASTRA
        if ( !isset($data['CODIGO']) )
            $pdo->query("INSERT INTO BD_CLIENTE.dbo.RFID (CODIGO, TIPO_ACESSO, TIPO_REPRESENTACAO) VALUES ({$TAG},2,0)"); 

        // VERIFICA SE TEM CADASTRO DE VIGÊNCIA, SE NÃO TIVER CADASTRA, SE TIVER ATUALIZA
        $sql = "SELECT * FROM BD_CLIENTE.dbo.CONTROLE_ACESSO_VIGENCIA WHERE TAG = '{$TAG}' ORDER BY DATA_INICIO DESC;";
        $con = $pdo->query($sql); 
        $datas= $con->fetch();

        $now = date("Y-m-d H:i:s");

        if ( isset($datas['TAG']) ){
            $oldID = $datas['CONTROLE_ACESSO_ID'];
            $pdo->query("UPDATE BD_CLIENTE.dbo.CONTROLE_ACESSO_VIGENCIA SET DATA_TERMINO = '{$now}' WHERE CONTROLE_ACESSO_ID = {$oldID} AND TAG = '{$TAG}'");

            // Inativa o outro Passageiro 
            $pdo->query("UPDATE BD_CLIENTE.dbo.CONTROLE_ACESSO SET ATIVO = 0, TAG = null WHERE ID = {$oldID} AND TAG = '{$TAG}'");

            // Inativa no CGF 
            $sql2 = $dbSys->prepare("UPDATE controle_acessos SET ATIVO = 0 WHERE ID_ORIGIN = {$oldID} AND TAG = '{$TAG}'");
            $sql2->execute();
        }

        // Busca o ultimo ID 
        $sql        = "SELECT ID FROM BD_CLIENTE.dbo.CONTROLE_ACESSO order by ID DESC;";
        $con        = $pdo->query($sql); 
        $data       = $con->fetch();
        $originID   = $data['ID'] + 1;

        $sqlIns     = "INSERT INTO BD_CLIENTE.dbo.CONTROLE_ACESSO (NOME, ITINERARIO_ID_IDA, ITINERARIO_ID_VOLTA, CONTROLE_ACESSO_GRUPO_ID, MATRICULA_FUNCIONAL, ID_UNICO, ATIVO, TAG, cpf, centro_custo) VALUES ('{$emb->NOME}', null, null, null, '{$emb->MATRICULA_FUNCIONAL}', {$originID}, 1, '{$TAG}', null, ' ')";
 
        $pdo->query($sqlIns); 
                
        sleep(1);
      
        // Insere o inicio da vigencia
        $qurt="INSERT INTO BD_CLIENTE.dbo.CONTROLE_ACESSO_VIGENCIA (TAG, DATA_TERMINO, CONTROLE_ACESSO_ID, DATA_INICIO) VALUES ('{$TAG}',null,{$originID},'{$now}')";
        $pdo->query($qurt);
    }
 
    /**
     * ATUALIZA A FLAG INTEGRAÇÃO NO CGF
     */
    $sql = $dbSys->prepare("UPDATE piccolotur_rel.embarque_qr_code SET integrado = :INTEGRA where id = :ID");
    $sql->bindValue(":INTEGRA", 1);
    $sql->bindValue(":ID", $emb->IDEMBARQUE);
    $sql->execute();

    /**
     * ATUALIZA O ORIGIN ID NO CGF  
     */
    $sql = $dbSys->prepare("UPDATE piccolotur_rel.controle_acessos SET TAG = :TAG, ID_ORIGIN = :ID_ORIGIN, ID_UNICO = :ID_UNICO where id = :ID");
    $sql->bindValue(":TAG", $TAG);
    $sql->bindValue(":ID_ORIGIN", $originID);
    $sql->bindValue(":ID_UNICO", $originID);
    $sql->bindValue(":ID", $emb->IDCA);
    $sql->execute();

    usleep( 1000 );
}

echo "FIM";
exit;
