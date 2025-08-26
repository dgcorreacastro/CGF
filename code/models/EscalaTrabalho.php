<?php

class EscalaTrabalho extends model 
{

    private $host   = ""; // TODO: POPULATE WITH DATABASE HOST ADDRESS
    private $port   = ""; // TODO: POPULATE WITH DATABASE PORT NUMBER
    private $user   = ""; // TODO: POPULATE WITH DATABASE USER
    private $pass   = ""; // TODO: POPULATE WITH DATABASE PASSWORD
    private $dbName = ""; // TODO: POPULATE WITH DATABASE NAME

	public function list($arr = '1,2,3,4', $pag = 1, $limit = 1, $unid = null, $setor = null, $gest = null, $mes = null, $ano = null, $stts = null)
	{
        $w = ""; 

        $groupID = $_SESSION['cGr'];
        $w = " AND s.grupoID = {$groupID}";

        if($unid != null && $unid > 0)
            $w .= " AND s.unidadeID = {$unid} ";

        if($setor != null && $setor > 0)
            $w .= " AND s.setor = {$setor} ";

        if($gest != null && $gest > 0)
            $w .= " AND s.liderID = {$gest} ";

        if($mes != null && $mes > 0)
            $w .= " AND s.mes = {$mes} ";

        if($ano != null && $ano > 0)
            $w .= " AND s.ano = {$ano} ";

        if($stts != null && $stts > 0)
            $w .= " AND s.efetivado = {$stts} ";

        #######################################################################
        ############################# GET TOTAL ###############################
        #######################################################################
        $sql="SELECT COUNT(*) AS total FROM escalaTrabalho s where s.deleted_at is null {$w} AND s.efetivado IN ({$arr})";
        $sql = $this->db->prepare($sql);
        $sql->execute();
        $tt = $sql->fetch(PDO::FETCH_OBJ);
        #######################################################################
        ######################### CONTINUE FILTERS ############################
        #######################################################################
        // Option 1 - 15 p/pag, 2 - 30 p/pag, 3 - 50 p/pag , 4 - 100 p/pag
        $limPag = 15;
   
        //switch ($limit) {
        //     case 2: $limPag = 30; break;
        //     case 3: $limPag = 50; break;
        //     case 4: $limPag = 100; break;
        //     default: $limPag = 1; break;
        // }

        $ttPages = intval( ceil($tt->total / $limPag) ); 
        //$of      = $limPag * ($ttPages - 1);
        $of      = $limPag * ($pag -1);

        $offset  = $of > 0 ? " OFFSET $of" : "";
        $groupID = $_SESSION['cGr'];
   
        // typeEscale: 1 rascunho, 2 enviado RH, 3 efetivado, 4 Negado
        $sql = "SELECT s.*, 
                    CASE
                        WHEN efetivado = 1 THEN 'Rascunho'
                        WHEN efetivado = 2 THEN 'Aguardando Aprovação'
                        WHEN efetivado = 3 THEN 'Efetivado'
                        WHEN efetivado = 4 THEN 'Negado'
                        ELSE ' - '
                    END AS statusEscala,
                    gr.nome as Lider,
                    un.descricao AS Unidade,
                    sete.descricao AS Setor
                FROM escalaTrabalho s
                LEFT JOIN userEscala gr ON gr.id = s.liderID
                LEFT JOIN unidades un ON un.id = s.unidadeID
                LEFT JOIN setores sete ON sete.id = s.setor AND sete.grupoID = {$groupID}
                where s.deleted_at is null {$w} AND s.efetivado IN ({$arr}) ORDER BY s.ano DESC, s.mes DESC, s.efetivado 
                LIMIT {$limPag} {$offset}";

        $sql = $this->db->prepare($sql);
        $sql->execute();

        return array ("escalas"=> $sql->fetchAll(PDO::FETCH_OBJ), "total"=> $ttPages );
	} 

	public function get( $id )
	{
        $arr = array();

		$sql = $this->db->prepare("SELECT s.*, un.descricao AS Unidade, se.descricao as setor 
                        FROM escalaTrabalho s
                        LEFT JOIN unidades un ON un.id = s.unidadeID 
                        LEFT JOIN setores AS se ON se.id = s.setor 
                        WHERE s.deleted_at IS NULL AND s.id = {$id}");

		$sql->execute();
        $escala = $sql->fetch(PDO::FETCH_OBJ);

        $q = "SELECT DISTINCT i.id, i.*, 
                    CASE
                        WHEN i.turnoID = 1 THEN '1º Turno - ESCALA'
                        WHEN i.turnoID = 2 THEN '2º Turno - ESCALA'
                        WHEN i.turnoID = 3 THEN '3º Turno - ESCALA'
                        WHEN i.turnoID = 4 THEN 'ADM - ESCALA'
                        ELSE ' - '
                    END AS TURNO,
                    ca.nome, ca.funcao
                    FROM itensEscalaTrabalho AS i
                    INNER JOIN colaboradores AS ca ON ca.id = i.colaboratorID
                    WHERE i.deleted_at IS NULL AND escalaTrabalhoID = {$escala->id} ";

        $sql2 = $this->db->prepare($q);
		$sql2->execute();
        $itenEsc = $sql2->fetchAll();

        $arr['escala']      = $escala;
        $arr['itemEscala']  = $itenEsc;

        return $arr;
	} 

    public function getRestaurante( $mes, $ano, $unid, $gestor )
    {
        $arr    = array();
        $w      = "";

        if ($unid != "0" && $unid != 0)
            $w .= " AND unidadeID = {$unid}";

        if ($gestor != "0" && $gestor != 0)
            $w .= " AND liderID = {$gestor}";

        $idgroup = $_SESSION['cGr'];

		$sql = $this->db->prepare("SELECT * 
                                    FROM escalaTrabalho 
                                WHERE deleted_at is null  AND efetivado = 3 
                                AND mes = {$mes} AND ano = {$ano} AND grupoID = {$idgroup} {$w}
                                ");
		$sql->execute();
        $escalas = $sql->fetchAll(PDO::FETCH_OBJ);
     
        foreach($escalas AS $esc)
        {
            $q = "SELECT DISTINCT i.id, i.*, 
            CASE
                WHEN i.turnoID = 1 THEN '1º Turno - ESCALA'
                WHEN i.turnoID = 2 THEN '2º Turno - ESCALA'
                WHEN i.turnoID = 3 THEN '3º Turno - ESCALA'
                WHEN i.turnoID = 4 THEN 'ADM - ESCALA'
                ELSE ' - '
            END AS TURNO
            FROM itensEscalaTrabalho AS i
            INNER JOIN colaboradores AS ca ON ca.id = i.colaboratorID
            WHERE i.deleted_at IS NULL AND escalaTrabalhoID = {$esc->id} order by turnoID";
            $sql2 = $this->db->prepare($q);
            $sql2->execute();
            
            $esc->itensEscalas = $sql2->fetchAll();
        }

        $arr['escala'] = $escalas;

        return $arr;
    }

    public function getSapData($mes, $ano, $unid, $gestor )
    {
 
        $w      = "";

        if ($unid != "0" && $unid != 0)
            $w .= " AND esc.unidadeID = {$unid}";

        if ($gestor != "0" && $gestor != 0)
            $w .= " AND esc.liderID = {$gestor}";

        $idgroup = $_SESSION['cGr'];

        #####################################
        ############ CONSULTAS ##############
        #####################################
        $q = "SELECT i.*
                FROM itensEscalaTrabalho AS i
                INNER JOIN escalaTrabalho AS esc ON esc.id = i.escalaTrabalhoID
                WHERE i.deleted_at IS NULL 
                AND esc.deleted_at IS NULL AND esc.efetivado = 3 
                AND esc.mes = {$mes} AND esc.ano = {$ano} AND esc.grupoID = {$idgroup} {$w}
                ORDER BY ABS(i.re), esc.ano, esc.mes";

            $sql2 = $this->db->prepare($q);
            $sql2->execute();
            
        return $sql2->fetchAll();
    }

    public function getFretamentoData($mes, $ano, $unid, $gestor, $turno )
    {
 
        $w  = "";

        if ($unid != "0" && $unid != 0)
            $w .= " AND esc.unidadeID = {$unid}";

        if ($gestor != "0" && $gestor != 0)
            $w .= " AND esc.liderID = {$gestor}";

        $idgroup = $_SESSION['cGr'];

        #####################################
        ############ CONSULTAS ##############
        #####################################
        $q = "SELECT 
                    col.re AS `RE`,
                    col.nome AS `NOME`,
                    longitude AS `LONG`,
                    latitude AS `LAT`,
                    uf AS `UF`
                FROM itensEscalaTrabalho AS i
                INNER JOIN escalaTrabalho AS esc ON esc.id = i.escalaTrabalhoID
                INNER JOIN colaboradores AS col ON col.re = i.re
                WHERE i.deleted_at IS NULL 
                AND esc.deleted_at IS NULL 
                AND col.deleted_at IS NULL
                AND esc.efetivado = 3 
                AND esc.mes = {$mes} AND esc.ano = {$ano} AND esc.grupoID = {$idgroup} {$w}
                AND i.turnoID = {$turno} AND col.fretado = 1
                ORDER BY ABS(i.re), esc.ano, esc.mes";

            $sql2 = $this->db->prepare($q);
            $sql2->execute();
            
        return $sql2->fetchAll(PDO::FETCH_OBJ);
    }

	public function save( $post )
	{

        ////////// SALVA A ESCALA \\\\\\\\\\\
        if(isset($_SESSION['cFret']) && $_SESSION['cType'] == 1)
        {
            $lider = $_SESSION['cLogin'];
        } else {
            $lider = $post['lider'];
        }

        $groupID = $_SESSION['cGr'];

        $setor = 0;

        // CHECK SE TEM SETOR CADASTRADO \\ 
        $sql2 = $this->db->prepare("SELECT * FROM setores WHERE grupoID = {$groupID} AND descricao = '".$post['setor']."' ORDER BY id DESC LIMIT 1");
        $sql2->execute();
        $m = $sql2->fetch(PDO::FETCH_OBJ);
   
        if( !$m )
        {
            $idUser = $_SESSION['cLogin'];

            $sql = $this->db->prepare("INSERT INTO setores (descricao, grupoID, userID, created_at) VALUE ('".$post['setor']."',{$groupID},{$idUser}, now())");
            $sql->execute();
            $setor = $this->db->lastInsertId();

        } else {
            $setor = $m->id;
        }

        // typeEscale: 1 rascunho, 2 enviado RH, 3 efetivado, 4 Negado
    
            $sql = $this->db->prepare("INSERT INTO escalaTrabalho (grupoID, liderID, unidadeID, mes, ano, setor,centro,descCC, efetivado, created_at ) VALUE (:grupoID,:liderID,:unidadeID,:mes,:ano,:setor,:centro,:descCC,:efetivado, now())");
            $sql->bindValue(":grupoID", $_SESSION['cGr']);
            $sql->bindValue(":liderID", $lider);
            $sql->bindValue(":unidadeID", $post['unidadeID']);
            $sql->bindValue(":mes", $post['mes']);
            $sql->bindValue(":ano", $post['ano']);
            $sql->bindValue(":setor", $setor);
            $sql->bindValue(":centro", $post['centroCusto']);
            $sql->bindValue(":descCC", $post['descricaoCC']);
            $sql->bindValue(":efetivado", $post['typeEscale']);
            $sql->execute();
     
        if (!$sql)
            return false;
        $novaEscalaId = $this->db->lastInsertId();
        $sql2 = $this->db->prepare("SELECT * FROM escalaTrabalho ORDER BY id DESC LIMIT 1");
        $sql2->execute();
        $m = $sql2->fetch(PDO::FETCH_OBJ);

        //////// SALVA ITENS ESCALA \\\\\\\\\
        foreach( $post['re'] AS $k => $re )
        {
            //////// GET ID DO COLLAB ATIVO COM AQUELE RE \\\\\\\\\
            $sql="SELECT id FROM colaboradores where deleted_at is null AND grupoID = {$groupID} AND re = " . $post['re'][$k];
            $sql = $this->db->prepare($sql);
            $sql->execute();
            $tt = $sql->fetch(PDO::FETCH_OBJ);

            $idCol = isset($tt->id) ? $tt->id : 0;

            $sql3 = "INSERT INTO itensEscalaTrabalho (escalaTrabalhoID, re, colaboratorID, turnoID, t1, t2, t3, t4, t5, t6, t7, t8, t9, t10, t11, t12, t13, t14, t15, t16, t17, t18, t19, t20, t21, t22, t23, t24, t25, t26, t27, t28, t29, t30, t31, created_at) VALUE (:escalaTrabalhoID, :re, :colaboratorID, :turnoID, :t1, :t2, :t3, :t4, :t5, :t6, :t7, :t8, :t9, :t10, :t11, :t12, :t13, :t14, :t15, :t16, :t17, :t18, :t19, :t20, :t21, :t22, :t23, :t24, :t25, :t26, :t27, :t28, :t29, :t30, :t31, now())";
            
            $sql4 = $this->db->prepare($sql3);
            $sql4->bindValue(":escalaTrabalhoID", $m->id);
            $sql4->bindValue(":re", $post['re'][$k]);
            $sql4->bindValue(":colaboratorID", $idCol);
            $sql4->bindValue(":turnoID", $post['hour'][$k]);
            $sql4->bindValue(":t1",  $post['t1-1'][$k]);
            $sql4->bindValue(":t2",  $post['t2-1'][$k]);
            $sql4->bindValue(":t3",  $post['t3-1'][$k]);
            $sql4->bindValue(":t4",  $post['t4-1'][$k]);
            $sql4->bindValue(":t5",  $post['t5-1'][$k]);
            $sql4->bindValue(":t6",  $post['t6-1'][$k]);
            $sql4->bindValue(":t7",  $post['t7-1'][$k]);
            $sql4->bindValue(":t8",  $post['t8-1'][$k]);
            $sql4->bindValue(":t9",  $post['t9-1'][$k]);
            $sql4->bindValue(":t10", $post['t10-1'][$k]);
            $sql4->bindValue(":t11", $post['t11-1'][$k]);
            $sql4->bindValue(":t12", $post['t12-1'][$k]);
            $sql4->bindValue(":t13", $post['t13-1'][$k]);
            $sql4->bindValue(":t14", $post['t14-1'][$k]);
            $sql4->bindValue(":t15", $post['t15-1'][$k]);
            $sql4->bindValue(":t16", $post['t16-1'][$k]);
            $sql4->bindValue(":t17", $post['t17-1'][$k]);
            $sql4->bindValue(":t18", $post['t18-1'][$k]);
            $sql4->bindValue(":t19", $post['t19-1'][$k]);
            $sql4->bindValue(":t20", $post['t20-1'][$k]);
            $sql4->bindValue(":t21", $post['t21-1'][$k]);
            $sql4->bindValue(":t22", $post['t22-1'][$k]);
            $sql4->bindValue(":t23", $post['t23-1'][$k]);
            $sql4->bindValue(":t24", $post['t24-1'][$k]);
            $sql4->bindValue(":t25", $post['t25-1'][$k]);
            $sql4->bindValue(":t26", $post['t26-1'][$k]);
            $sql4->bindValue(":t27", $post['t27-1'][$k]);
            $sql4->bindValue(":t28", $post['t28-1'][$k]);
            $sql4->bindValue(":t29", $post['t29-1'][$k]);
            $sql4->bindValue(":t30", $post['t30-1'][$k]);
            $sql4->bindValue(":t31", $post['t31-1'][$k]);
            $sql4->execute();
        }
		
		return array ("error"=> false, "novaEscalaId"=> $novaEscalaId );
	}


    private function getValuesCol($vl)
    {
        if ( isset($vl))
        switch ($vl) {
            case 'n': return 0;
            case 'y': return 1;
            case 'w': return 2;
            case 'v': return 3;
            case 't': return 4;
        }
    }

    // private function getValuesCol($vl)
    // {
    //     if ( isset($vl) && $vl == 'y' )
    //         return 1;
        
    //     return 0;
    // }

	public function update($post)
	{
       
        if(isset($_SESSION['cFret']) && $_SESSION['cType'] == 1)
        {
            $lider = $_SESSION['cLogin'];
        } else {
            $lider = $post['lider'];
        }

        $setor = 0;
        $groupID = $_SESSION['cGr'];

        // CHECK SE TEM SETOR CADASTRADO \\ 
        $sql2 = $this->db->prepare("SELECT * FROM setores WHERE grupoID = {$groupID} AND descricao = '".$post['setor']."' ORDER BY id DESC LIMIT 1");
        $sql2->execute();
        $m = $sql2->fetch(PDO::FETCH_OBJ);
    
        if( !$m )
        {
            $idUser = $_SESSION['cLogin'];
    
            $sql = $this->db->prepare("INSERT INTO setores (descricao, grupoID, userID, created_at) VALUE ('".$post['setor']."',{$groupID},{$idUser}, now() )");
            $sql->execute();
            $setor = $this->db->lastInsertId();
    
        } else {
            $setor = $m->id;
        }

        $sql = $this->db->prepare("UPDATE escalaTrabalho SET liderID = :liderID, mes = :mes, ano = :ano, setor = :setor, centro = :centro, descCC = :descCC, efetivado = :efetivado, updated_at = NOW() where id = :id");
		$sql->bindValue(":liderID", $lider);
		$sql->bindValue(":mes", $post['mes']);
		$sql->bindValue(":ano", $post['ano']);
		$sql->bindValue(":setor", $setor);
		$sql->bindValue(":centro", $post['centroCusto']);
		$sql->bindValue(":descCC", $post['descricaoCC']);
		$sql->bindValue(":efetivado", $post['typeEscale']);
		$sql->bindValue(":id", $post['id']);
		$sql->execute();
		
        if (!$sql)
            return false;

        //////// SALVA ITENS ESCALA \\\\\\\\\
        foreach( $post['re'] AS $k => $re )
        {

            //////// GET ID DO COLLAB ATIVO COM AQUELE RE \\\\\\\\\
            $sql="SELECT id FROM colaboradores where deleted_at is null AND grupoID = {$groupID} AND re = " . $post['re'][$k];
            $sql = $this->db->prepare($sql);
            $sql->execute();
            $tt = $sql->fetch(PDO::FETCH_OBJ);

            $idCol = isset($tt->id) ? $tt->id : 0;

            $sql3 = "INSERT INTO itensEscalaTrabalho (escalaTrabalhoID, re, colaboratorID, turnoID, t1, t2, t3, t4, t5, t6, t7, t8, t9, t10, t11, t12, t13, t14, t15, t16, t17, t18, t19, t20, t21, t22, t23, t24, t25, t26, t27, t28, t29, t30, t31, created_at) 
                                               VALUE (:escalaTrabalhoID, :re, :colaboratorID, :turnoID, :t1, :t2, :t3, :t4, :t5, :t6, :t7, :t8, :t9, :t10, :t11, :t12, :t13, :t14, :t15, :t16, :t17, :t18, :t19, :t20, :t21, :t22, :t23, :t24, :t25, :t26, :t27, :t28, :t29, :t30, :t31, now())";
            
            $sql4 = $this->db->prepare($sql3);
            $sql4->bindValue(":escalaTrabalhoID", $post['id']);
            $sql4->bindValue(":re", $post['re'][$k]);
            $sql4->bindValue(":colaboratorID", $idCol);
            $sql4->bindValue(":turnoID", $post['hour'][$k]);
            $sql4->bindValue(":t1",  $post['t1-1'][$k]);
            $sql4->bindValue(":t2",  $post['t2-1'][$k]);
            $sql4->bindValue(":t3",  $post['t3-1'][$k]);
            $sql4->bindValue(":t4",  $post['t4-1'][$k]);
            $sql4->bindValue(":t5",  $post['t5-1'][$k]);
            $sql4->bindValue(":t6",  $post['t6-1'][$k]);
            $sql4->bindValue(":t7",  $post['t7-1'][$k]);
            $sql4->bindValue(":t8",  $post['t8-1'][$k]);
            $sql4->bindValue(":t9",  $post['t9-1'][$k]);
            $sql4->bindValue(":t10", $post['t10-1'][$k]);
            $sql4->bindValue(":t11", $post['t11-1'][$k]);
            $sql4->bindValue(":t12", $post['t12-1'][$k]);
            $sql4->bindValue(":t13", $post['t13-1'][$k]);
            $sql4->bindValue(":t14", $post['t14-1'][$k]);
            $sql4->bindValue(":t15", $post['t15-1'][$k]);
            $sql4->bindValue(":t16", $post['t16-1'][$k]);
            $sql4->bindValue(":t17", $post['t17-1'][$k]);
            $sql4->bindValue(":t18", $post['t18-1'][$k]);
            $sql4->bindValue(":t19", $post['t19-1'][$k]);
            $sql4->bindValue(":t20", $post['t20-1'][$k]);
            $sql4->bindValue(":t21", $post['t21-1'][$k]);
            $sql4->bindValue(":t22", $post['t22-1'][$k]);
            $sql4->bindValue(":t23", $post['t23-1'][$k]);
            $sql4->bindValue(":t24", $post['t24-1'][$k]);
            $sql4->bindValue(":t25", $post['t25-1'][$k]);
            $sql4->bindValue(":t26", $post['t26-1'][$k]);
            $sql4->bindValue(":t27", $post['t27-1'][$k]);
            $sql4->bindValue(":t28", $post['t28-1'][$k]);
            $sql4->bindValue(":t29", $post['t29-1'][$k]);
            $sql4->bindValue(":t30", $post['t30-1'][$k]);
            $sql4->bindValue(":t31", $post['t31-1'][$k]);
            $sql4->execute();
        }
		
		return true;
	}

    public function updateRH($id, $tp, $motive = "")
	{
        $ef  = $tp == 1 ? 3 : 4;

        $sql = $this->db->prepare("UPDATE escalaTrabalho SET efetivado = :efetivado, motive = :motive, updated_at = NOW() where id = :id");
		$sql->bindValue(":efetivado", $ef);
		$sql->bindValue(":motive", $motive);
		$sql->bindValue(":id", $id);
		$sql->execute();
		
        if (!$sql)
            return false;

		return true;
	}

    public function getMailLider($id)
    {
        $q = "SELECT u.email
                FROM escalaTrabalho AS e
                INNER JOIN userEscala AS u ON u.id = e.liderID
               LIMIT 1";

        $sql2 = $this->db->prepare($q);
        $sql2->execute();
 
        return $sql2->fetch(PDO::FETCH_OBJ);
    }

	public function delete($id)
	{

		$sql = $this->db->prepare("UPDATE escalaTrabalho SET deleted_at = NOW() WHERE id = {$id}");
        $sql->execute();

        if (!$sql)
            return ["success" => false, "msg" => "Ocorreu um erro ao remover a escala, tente novamente."];

        return ["success" => true, "msg" => "Escala deletada com sucesso!"];
	}

    public function paxEscalaDelete($id)
    {
        $sql = $this->db->prepare("UPDATE itensEscalaTrabalho SET deleted_at = NOW() WHERE id = {$id}");
        $sql->execute();

        if (!$sql)
            return false;
    
        return true;
    }

    public function itemUpdateEscala($id, $c, $v)
    {
        $sql = $this->db->prepare("UPDATE itensEscalaTrabalho SET {$c} = {$v} WHERE id = {$id}");
        $sql->execute();

        if (!$sql)
            return false;
    
        return true;
    }

    public function hasRegister( $descr, $id = null, $gr = null )
	{
        $w  = "";
        $gr = $_SESSION['cGr'];

        if($id != null)
            $w = " AND id <> {$id}";

        if($gr != null)
            $w .= " AND grupoID = {$gr}";

        $sql = $this->db->prepare("SELECT * FROM setores where deleted_at is null AND descricao = '{$descr}' {$w}");

        $sql->execute();
        $has = $sql->fetch(PDO::FETCH_OBJ);

        if (!$has) 
            return false; 

        return true;
	} 

    public function getSetor()
	{
     
        $w = "";
       
        if(isset($_SESSION['cGr']))
            $w = " AND grupoID = {$_SESSION['cGr']} ";

        $sql = "SELECT * FROM setores where deleted_at is null {$w} ORDER BY descricao";
     
        $sql = $this->db->prepare($sql);
        $sql->execute();
        
        return $sql->fetchAll(PDO::FETCH_OBJ);
	} 

    /////////// GET PAX \\\\\\\\\\\
    public function paxEscala($post)
    {
      
        $re = $post['re'];
        $gr = $_SESSION['cGr'];

        $sql = $this->db->prepare("SELECT * FROM colaboradores WHERE re = '{$re}' AND deleted_at is null AND grupoID = {$gr}");
		$sql->execute();
        return $sql->fetch(PDO::FETCH_OBJ);
    }

    public function paxSetor($post)
    {
        $st = $post['st'];
        $gr = $_SESSION['cGr'];

        $sql = $this->db->prepare("
                    SELECT ca.*
                        FROM controle_acessos as ca
                    WHERE ca.CONTROLE_ACESSO_GRUPO_ID = {$gr} AND ca.setorID = '{$st}'");
		$sql->execute();

        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    public function getCentroCusto($cc)
    {

        $sql = $this->db->prepare("SELECT * FROM colaboradores WHERE centroCusto = '{$cc}' AND deleted_at is null ORDER BY id DESC LIMIT 1");
		$sql->execute();
        $rt = $sql->fetch(PDO::FETCH_OBJ);

        $txt = "";

        if( $rt ) $txt = $rt->nomeCentroCusto;

        return $txt;
    }

}