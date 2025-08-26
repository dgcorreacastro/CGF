<?php

class Linhas extends model 
{

    private $host   = ""; // TODO: POPULATE WITH DATABASE HOST ADDRESS
    private $port   = ""; // TODO: POPULATE WITH DATABASE PORT NUMBER
    private $user   = ""; // TODO: POPULATE WITH DATABASE USER
    private $pass   = ""; // TODO: POPULATE WITH DATABASE PASSWORD
    private $dbName = ""; // TODO: POPULATE WITH DATABASE NAME

    public function paxCars($grAcesso)
    {
  
        $sql = "SELECT 
                    CA.NOME, 
                    CA.MATRICULA_FUNCIONAL, 
                    CA.TAG, 
                    CA.POLTRONAIDA, 
                    CA.POLTRONAVOLTA, 
                    CA.CONTROLE_ACESSO_GRUPO_ID AS GRUPO
                FROM controle_acessos CA
                WHERE CONTROLE_ACESSO_GRUPO_ID = '{$grAcesso}' AND ATIVO = 1 ORDER BY NOME ASC;";

            $sqlB = $this->db->prepare($sql);
            $sqlB->execute();

        return $sqlB->fetchAll();
    }

    public function isIntercalar($grAcesso)
    {
        $sql  = "SELECT * FROM acesso_grupos WHERE ID_ORIGIN = '{$grAcesso}' AND deleted_at IS NULL;";
        $sqlB = $this->db->prepare($sql);
        $sqlB->execute();

        return $sqlB->fetch();
    }

    public function paxDataLinha( $lnID )
    {
        $sql = "SELECT 
                CA.id, 
                CA.NOME, 
                CA.MATRICULA_FUNCIONAL, 
                CA.TAG, 
                (CA.POLTRONAIDA * 1) AS POLTRONA, 
                CA.CONTROLE_ACESSO_GRUPO_ID AS GRUPO, 
                ITIDA.SENTIDO, 
                'IDA' AS SENTID
            FROM controle_acessos CA
            INNER JOIN itinerarios ITIDA ON ITIDA.ID_ORIGIN = CA.ITINERARIO_ID_IDA
            WHERE ITIDA.LINHA_ID = '{$lnID}' AND CA.ATIVO = 1 
        UNION
            SELECT 
                CA.id, 
                CA.NOME, 
                CA.MATRICULA_FUNCIONAL, 
                CA.TAG, 
                (CA.POLTRONAVOLTA * 1) AS POLTRONA, 
                CA.CONTROLE_ACESSO_GRUPO_ID AS GRUPO, 
                ITIVOLT.SENTIDO, 
                'VOLT' AS SENTID
            FROM controle_acessos CA
            INNER JOIN itinerarios ITIVOLT ON ITIVOLT.ID_ORIGIN = CA.ITINERARIO_ID_VOLTA
            WHERE ITIVOLT.LINHA_ID = '{$lnID}' AND CA.ATIVO = 1
        ORDER BY NOME;";

        $sqlB = $this->db->prepare($sql);
        $sqlB->execute();

        return $sqlB->fetchAll();
    }

    public function paxPolSave($post)
    {
        $pol = $post['polt'];
        $gr  = $post['grupo'];
        $ln  = $post['lines'];
        $paxW= "";
        $wr  = "";
        
        // Verifica se tem matricula para salvar pela matricula
        if ( isset($post['matric']) )
        { /// Atualiza pela Matricula
            $paxW = "MATRICULA_FUNCIONAL = '". $post['matric'] ."'";
        } else { /// Atualiza pelo Nome 
            $paxW = "NOME = '". $post['pax'] ."'";
        }

        $colUPdt = "POLTRONAVOLTA";
        
        /// Analisar se ficará correto, no grupo THYSEEN a IDA ficou como 1
        if($post['sent'] == 1)
        { // 1 IDA , 0 VOLTA
            $colUPdt = "POLTRONAIDA";
        }

        $sql  = "UPDATE controle_acessos SET {$colUPdt} = '{$pol}' WHERE CONTROLE_ACESSO_GRUPO_ID= '{$gr}' AND {$paxW};";
   
        $sqlB = $this->db->prepare($sql);
        $ret  = $sqlB->execute();

        /// ATUALIZANDO O ITINERÁRIO USUÁRIO \\\
        $sql  = "SELECT * FROM itinerarios WHERE LINHA_ID = {$ln} AND deleted_at is null;";
        $sqlB = $this->db->prepare($sql);
        $sqlB->execute();
        $lin  = $sqlB->fetch();

        if ($lin)
        {
            $itn = $lin['ID_ORIGIN'];

            if($post['sent'] == 1)
            {
                $sql  = "UPDATE controle_acessos SET ITINERARIO_ID_IDA = '{$itn}' WHERE CONTROLE_ACESSO_GRUPO_ID= '{$gr}' AND {$paxW};";

                $wr   = " ITINERARIO_ID_IDA = '{$itn}' ";
            } else {
                $sql  = "UPDATE controle_acessos SET ITINERARIO_ID_VOLTA = '{$itn}' WHERE CONTROLE_ACESSO_GRUPO_ID= '{$gr}' AND {$paxW};";

                $wr   = " ITINERARIO_ID_VOLTA = '{$itn}' ";
            }

            $sqlB = $this->db->prepare($sql);
            $ret  = $sqlB->execute();
        }

        //////////// ATUALIZAR NA VELTRAC \\\\\\\\\\\\\
        if ( $ret ){
            // Atualiza o Centro de custo na Veltrac 
            // AJUSTAR PARA ATUALIZAR A LINHA E AS POLTRONAS IDA E VOLTA
            try {
                $pd = new \PDO ("dblib:host=$this->host:$this->port;dbname=$this->dbName;charset=utf8","$this->user","$this->pass");

                // $wr - Para o where e alterar na veltrac
                $sqlU = "UPDATE BD_CLIENTE.dbo.CONTROLE_ACESSO SET centro_custo = '{$pol}', {$wr} WHERE CONTROLE_ACESSO_GRUPO_ID= '{$gr}' AND {$paxW}";
        
                $pd->query($sqlU);

            } catch (\Throwable $th) {
                $error = array('error' => true, 'msg'=>'Ocorreu um erro ao tentar conectar ao Banco de Dados, tente novamente.');
                return $error;
            }
            
            return true; 
        }
        ////////////////////////////////////////////////

        return false;
    }

    public function paxPolRemove($post)
    {
        $pol = $post['poltro'];
        $gr  = $post['grupo'];
   
        $paxW= "";

        // Verifica se tem matricula para salvar pela matricula 
        if ( isset($post['matric']) )
        { /// Atualiza pela Matricula
            $paxW = "MATRICULA_FUNCIONAL = '". $post['matric'] ."'";
        } else { /// Atualiza pelo Nome 
            $paxW = "NOME = '". $post['nome'] ."'";
        }
    
        $colUPdt = "POLTRONAVOLTA";
        /// Analisar se ficará correto, no grupo THYSEEN a IDA ficou como 1
        if($post['sent'] == 1)
        { // 0 IDA , 1 VOLTA
            $colUPdt = "POLTRONAIDA";
        }

        $sql  = "UPDATE controle_acessos SET {$colUPdt} = '' WHERE CONTROLE_ACESSO_GRUPO_ID= '{$gr}' AND {$paxW} AND {$colUPdt} = '{$pol}';";
        $sqlB = $this->db->prepare($sql);
        $ret  = $sqlB->execute();

        //////////// ATUALIZAR NA VELTRAC \\\\\\\\\\\\\
        ////////////////////////////////////////////////

        if ( $ret )
            return true; 

        return false;
    }

    public function nameGrAndLine($gr, $ln)
    {
        $arr = array();

        $sql = "SELECT * FROM linhas ln WHERE ln.ID_ORIGIN = {$ln} AND deleted_at is null;";
        $sqlB = $this->db->prepare($sql);
        $sqlB->execute();
        $arr['line'] = $sqlB->fetch();

        $sql2 = "SELECT * FROM acesso_grupos ac WHERE ac.ID_ORIGIN = {$gr} AND deleted_at is null;";
        $sqlc = $this->db->prepare($sql2);
        $sqlc->execute();
        $arr['ac'] = $sqlc->fetch();
        
        return $arr;
    }

}
?>