<?php

class Colaborador extends model 
{

    private $host   = ""; // TODO: POPULATE WITH DATABASE HOST ADDRESS
    private $port   = ""; // TODO: POPULATE WITH DATABASE PORT NUMBER
    private $user   = ""; // TODO: POPULATE WITH DATABASE USER
    private $pass   = ""; // TODO: POPULATE WITH DATABASE PASSWORD
    private $dbName = ""; // TODO: POPULATE WITH DATABASE NAME

	public function list( $pag, $limit, $unid, $name )
	{
		$w = "";

        if($unid != null && $unid != "")
            $w .= " AND s.unidade = {$unid} ";

		if(isset($name) && $name != "")
            $w .= " AND s.nome LIKE '%{$name}%' ";


		$groupID = $_SESSION['cGr'];

		#######################################################################
        ############################# GET TOTAL ###############################
        #######################################################################
        $sql="SELECT COUNT(*) AS total FROM colaboradores s where s.deleted_at is null {$w} AND s.grupoID = {$groupID}";
        $sql = $this->db->prepare($sql);
        $sql->execute();
        $tt = $sql->fetch(PDO::FETCH_OBJ);
        #######################################################################
        ######################### CONTINUE FILTERS ############################
        #######################################################################
		$limPag     = 30;
        $ttPages    = intval( ceil($tt->total / $limPag) ); 
		$of         = $limPag * ($pag - 1);
        $offset     = $of > 0 ? " OFFSET $of" : "";

		$sql = "SELECT s.*, TRIM(s.nome) as nome, un.descricao
                FROM colaboradores s
                LEFT JOIN unidades un ON un.id = s.unidade
                where s.deleted_at is null {$w} AND s.grupoID = {$groupID} ORDER BY TRIM(nome) LIMIT {$limPag} {$offset}";
	
 		$sql = $this->db->prepare($sql);
		$sql->execute();

		return array ( "users"=> $sql->fetchAll(PDO::FETCH_OBJ), "total"=> $ttPages );
	} 

    public function insertImportPax( $paxs )
	{
		$arr 		= array();
		$re 		= $paxs[0];
		$nome 		= $paxs[1];
		$unid 		= isset($paxs[2]) ? $paxs[2] : "";
		$centroCust = isset($paxs[3]) ? $paxs[3] : "";
		$nomeCC 	= isset($paxs[4]) ? $paxs[4] : "";
		$funcao 	= isset($paxs[5]) ? $paxs[5] : "";
		$grp        = $_SESSION['cGr'];

		// Verifica se tem usuário com esse re \\
		$sql = $this->db->prepare("SELECT * FROM colaboradores WHERE deleted_at is null AND re = '{$re}' AND grupoID = {$grp}");
		$sql->execute();
		$has = $sql->fetch(PDO::FETCH_OBJ);

		if ( !$has )
		{
			$idUnid = 0;
            if ($unid != "")
            {
                $sql = $this->db->prepare("SELECT * FROM unidades WHERE deleted_at is null AND descricao = '".trim($unid)."' AND grupoID = {$grp}");
                $sql->execute();
                $hasUni = $sql->fetch(PDO::FETCH_OBJ);

                if($hasUni){
                    $idUnid = $hasUni->id;
                } else {
                    $sql = $this->db->prepare("INSERT INTO unidades (grupoID, descricao, created_at) VALUE (:grupoID, 
                                                    :descricao, now())" );

                    $sql->bindValue(":grupoID", $_SESSION['cGr']);
                    $sql->bindValue(":descricao", trim($unid));
                    $sql->execute();
                    $idUnid = $this->db->lastInsertId();
                }

            }

			$sql = $this->db->prepare("INSERT INTO colaboradores 
										(   re, 
											nome, 
											unidade, 
											centroCusto, 
											nomeCentroCusto, 
											funcao, 
											created_at,
											grupoID
										) VALUE (
											:re, 
											:nome, 
											:unidade, 
											:centroCusto, 
											:nomeCentroCusto, 
											:funcao, 
											now(),
											:grupoID
										)");

			$sql->bindValue(":re", $re);
			$sql->bindValue(":nome", $nome);
			$sql->bindValue(":unidade", $idUnid);
			$sql->bindValue(":centroCusto", $centroCust);
			$sql->bindValue(":nomeCentroCusto", $nomeCC);
			$sql->bindValue(":funcao", $funcao);
			$sql->bindValue(":grupoID", $grp);
			$sql->execute();

			return array('success'=>true, 'id'=> $this->db->lastInsertId() );
		}
		
		return array('success'=>true, 'id' => $has->id );
	}

    public function inativeWithNotHas( $ids )
	{
		$grp = $_SESSION['cGr'];
        $ins = implode("," , $ids);
        $sql = $this->db->prepare("UPDATE colaboradores SET deleted_at = NOW() WHERE id NOT IN ($ins) AND grupoID = {$grp}");
        $ret = $sql->execute();

		//print_r("UPDATE colaboradores SET deleted_at = null WHERE re NOT IN ($ins) ");die;

		return true;
	}


















	public function get( $idClient )
	{
		$array = array();

        /// GET NAME CLIENTE \\\
		$sql 	= $this->db->prepare("SELECT * FROM grupo_linhas where id = :id");
		$sql->bindValue(":id", $idClient);
		$sql->execute();
		$cli = $sql->fetch();
        $array['name'] = $cli['NOME'];

        /// GET DADOS PAX \\\
		$sql = $this->db->prepare("SELECT * FROM pax_especial where deleted_at is null AND client_id = {$idClient} ORDER BY NamePax");
		$sql->execute();

		if($sql->rowCount() > 0) {
			$array['pax'] = $sql->fetchAll();
		}

		return $array;
	} 

	public function deletePaxEspecial( $id )
	{

		$sql = $this->db->prepare("UPDATE pax_especial SET deleted_at = now() where client_id = :id");
		$sql->bindValue(":id", $id);
		$sql->execute();

		if (!$sql)
			return false;

		return true;
	}

	public function insertPaxEspecial($id, $line)
	{
		// 0 NOME // 1 POLTRONA // 2 CARTAO // 3 MATRICULA // 4 PREF IDA // 5 DESC IDA 
		// 6 PREF VOLTA // 7 DESC VOLTA
	
		$sql = $this->db->prepare("INSERT INTO pax_especial SET client_id = :client_id, NamePax = :NamePax, PoltronaIDA = :PoltronaIDA, PrefixoIda = :PrefixoIda, DescricaoIda = :DescricaoIda, PrefixoVolta = :PrefixoVolta, DescricaoVolta = :DescricaoVolta, PoltronaVOLTA = :PoltronaVOLTA, CodCartao = :CodCartao, Matricula = :Matricula, created_at = now()");
		$sql->bindValue(":client_id", $id);
		$sql->bindValue(":NamePax", $line[0]);
		$sql->bindValue(":PoltronaIDA", $line[5]);
		$sql->bindValue(":PrefixoIda", $line[3]);
		$sql->bindValue(":DescricaoIda", $line[4]);
		$sql->bindValue(":PrefixoVolta", $line[6]);
		$sql->bindValue(":DescricaoVolta", $line[7]);
		$sql->bindValue(":PoltronaVOLTA", $line[8]);
		$sql->bindValue(":CodCartao", $line[1]);
		$sql->bindValue(":Matricula", $line[2]);
		$sql->execute();

		if (!$sql)
			return false;
		
		return true;
	}

	public function getGrupoLinhas()
	{

		$array 	= array();

		$sql = $this->db->prepare("SELECT *, 
		(SELECT COUNT(*) FROM pax_especial p WHERE p.client_id = grupo_linhas.id AND p.deleted_at is null) AS ttPax
		 FROM grupo_linhas where deleted_at is null ORDER BY NOME");
		$sql->execute();
		$array = $sql->fetchAll();

		return $array;

	}

	public function getDadosPassageiro($req)
    {
          
		$data = array('success' => false, 'choise' => false);

		if($req->registro == "")
		{
			$wh = "WHERE NamePax LIKE '{$req->nome}%' AND client_id = {$req->GrouID}";

			$sql = "SELECT 
						CodCartao AS CODIGO,
						NamePax AS NOME,
						Matricula AS MATRICULA_FUNCIONAL,
						PrefixoIda AS PREFIXOLINHAIDA,
						DescricaoIda AS NOMELINHAIDA,
						'' AS SENTIDOIDA,
						'' AS DESCRICAOINTINERARIOIDA,
						PrefixoVolta AS PREFIXOLINHAVOL,
						DescricaoVolta AS NOMELINHAVOL,
						'' AS SENTIDOVOL,
						'' AS DESCRICAOINTINERARIOVOL,
						PoltronaIDA AS POLIDA,
						PoltronaVOLTA AS POLVOLTA
					FROM pax_especial {$wh} AND deleted_at is null;";

			$sqlEx 	= $this->db->prepare($sql);
			$sqlEx->execute();
			$retorB = $sqlEx->fetchAll();
			
			if(count($retorB) > 1) {

				$data['success'] = true;
				$data['data']    = $retorB;
				$data['choise']  = true;

				return $data;

			} else if(count($retorB) == 1) {

				$req->registro = isset($retorB[0]['MATRICULA_FUNCIONAL']) ? $retorB[0]['MATRICULA_FUNCIONAL'] : "-";

			} else {

				$dataret = array();
				$dataret['success'] = false;
				$dataret['msg']     = "Nenhum resultado encontrado com as informações fornecida!";

				return $dataret;
			}
		}
		###############################################################################
		############### CASO PASSE PELO FILTRO IRÁ BUSCAR OS DADOS ####################
		###############################################################################
		$where= "";
		$and  = "";

		if(isset($req->registro) && $req->registro != "" && $req->registro != "-"){

			$where .= $and . "Matricula = '{$req->registro}'";
			$and    = " AND "; 

			if($req->nome != ""){
				$where .= $and . "NamePax LIKE '%{$req->nome}%'";
				$and    = " AND "; 
			}

		
		} else if($req->registro == "-"){

			$where .= $and . "NamePax = '{$req->nome}'";
			$and    = " AND "; 

		}
	
		$w = ($where != "") ? "WHERE {$where} AND client_id = {$req->GrouID} AND deleted_at is null" : "WHERE deleted_at is null";

		if($w == ""){
			$data['msg']     = "Faltou usar algum filtros.";
			$data['success'] = false;
			$data['data']    = [];
			return $data;
		}

		$sql = "SELECT 
						CodCartao AS CODIGO,
						NamePax AS NOME,
						Matricula AS MATRICULA_FUNCIONAL,
						PrefixoIda AS PREFIXOLINHAIDA,
						DescricaoIda AS NOMELINHAIDA,
						'' AS SENTIDOIDA,
						'' AS DESCRICAOINTINERARIOIDA,
						PrefixoVolta AS PREFIXOLINHAVOL,
						DescricaoVolta AS NOMELINHAVOL,
						'' AS SENTIDOVOL,
						'' AS DESCRICAOINTINERARIOVOL,
						PoltronaIDA AS POLIDA,
						PoltronaVOLTA AS POLVOLTA
					FROM pax_especial {$w};"
				;

		$sqlEx 	= $this->db->prepare($sql);
		$sqlEx->execute();
		$retorB = $sqlEx->fetchAll();
		
		$data['success'] = true;
		$data['data']    = $retorB;

		return $data;
    }

	public function getLinhasWithSenti()
    {
        try {
            $pdoSql = new \PDO ("dblib:host=$this->host:$this->port;dbname=$this->dbName;charset=utf8","$this->user","$this->pass");
        } catch (\Throwable $th) {
            $error = array('error'=>true, 'msg'=>'Ocorreu um erro ao tentar conectar ao Banco de Dados, tente novamente.');
            return $error;
        }

		$arr = array();

        if(isset($_SESSION['cType']) && $_SESSION['cType'] != 1)
		{

			$sql = $this->db->prepare("SELECT ID_ORIGIN 
								FROM usuario_linhas  
								INNER JOIN linhas ON linhas.id = usuario_linhas.linha_id
								WHERE usuario_id = {$_SESSION['cLogin']} AND usuario_linhas.deleted_at is null");
			
			$sql->execute();
			$array = $sql->fetchAll();
		
			foreach($array as $ar)
			{
				$arr[] = $ar['ID_ORIGIN'];
			}
        }

		$w = count($arr) > 0 ? "AND l.ID IN (". implode(",", $arr) .")" : "";

		$sql2 = "SELECT l.*, 
					it.SENTIDO
				FROM LINHAS as l
				JOIN ITINERARIOS it ON it.LINHA_ID = l.ID
				WHERE it.ATIVO = 1 $w ORDER BY l.NOME;";

		$consulta   = $pdoSql->query($sql2);
		$retorn = $consulta->fetchAll();

		return $retorn;
    }

	public function itinerarioByLine($req)
	{
		try {
            $pdoSql = new \PDO ("dblib:host=$this->host:$this->port;dbname=$this->dbName;charset=utf8","$this->user","$this->pass");
        } catch (\Throwable $th) {
            $error = array('error'=>true, 'msg'=>'Ocorreu um erro ao tentar conectar ao Banco de Dados, tente novamente.');
            return $error;
        }

		$array = array();

		$id = isset($req) ? $req['id'] : 0;
		$s  = isset($req) ? $req['sen'] : 0;

		$w = " AND it.LINHA_ID = {$id} AND it.SENTIDO = {$s}"; 

		$sql = "SELECT it.ID, it.ATIVO, it.TIPO, it.TRECHO, it.DESCRICAO, it.SENTIDO
				FROM ITINERARIOS it WHERE it.ATIVO = 1 {$w};";

		$consulta   = $pdoSql->query($sql);
		$retur = $consulta->fetch();

		$array['itid'] = $retur;

		if(isset($retur['ID']) )
			$array['pontosEmb'] = $this->getPontosItinerario($pdoSql, $retur['ID']);

		return $array;
	}

	public function getPontosItinerario($pdo, $id)
	{
		$sql = "SELECT pr.NOME, pto.*
					FROM BD_CLIENTE.dbo.PONTOS_ITINERARIO pto
					JOIN PONTOS_REFERENCIA pr ON pr.ID = pto.PONTO_REFERENCIA_ID
					WHERE pto.ITINERARIO_ID = {$id};";

		$cons  = $pdo->query($sql);
		$retur = $cons->fetchAll();

		return $retur;
	}

	public function saveNewPax($post)
	{

		try {
            $pdo = new \PDO ("dblib:host=$this->host:$this->port;dbname=$this->dbName;charset=utf8","$this->user","$this->pass");
        } catch (\Throwable $th) {
            $error =array('error'=>true,'msg'=>'Ocorreu um erro ao tentar conectar ao Banco de Dados, tente novamente.');
            return $error;
        }

		$originID 		= 0;
		$centroCusto 	= "";

		if(isset($post['polIda']) && $post['polIda'] != "") $centroCusto = $post['polIda'];
		if(isset($post['polVolta']) && $post['polVolta'] != "") $centroCusto .= ";" . $post['polVolta'];

		############ BUSCANDO O ULTIMO ID PARA INCREMENTAR ###################
		$sql = "SELECT ID FROM BD_CLIENTE.dbo.CONTROLE_ACESSO order by ID DESC;";
		$con = $pdo->query($sql); 
		$data= $con->fetch();

		$originID 	= $data['ID'] + 1;
		$grupo 		= 0;

		if (isset($post['grupo']) && $post['grupo'] != "")
		{
			$grupo 	= $post['grupo'];
		} else if( isset($_SESSION['cGr']) )
		{
			$sql = $this->db->prepare("SELECT * FROM grupo_linhas WHERE id = {$_SESSION['cGr']}");
			$sql->execute();
			$gpr = $sql->fetch(\PDO::FETCH_OBJ);
			$grupo = $gpr->GRUPOSUSER;
		}
		
		$itiIda 	= isset($post['itiIda']) && $post['itiIda'] != "" ? $post['itiIda'] : 0;
		$itiVolta 	= isset($post['itiVolta']) && $post['itiVolta'] != "" ? $post['itiVolta'] :  0;
		$matricula 	= isset($post['matricula']) && $post['matricula'] != "" ? $post['matricula'] : 0;
		$codigo 	= isset($post['codigo']) && $post['codigo'] != "" ? $post['codigo'] : null;
		$cpf 		= isset($post['cpf']) && $post['cpf'] != "" ? $post['cpf'] :  null;
		$polIda 	= isset($post['polIda']) && $post['polIda'] != "" ? $post['polIda'] : 0;
		$polVolta 	= isset($post['polVolta']) && $post['polVolta'] != "" ? $post['polVolta'] : 0;
		$setorID 	= isset($post['setorID']) && $post['setorID'] != "" ? $post['setorID'] : 0;

		$funcao 	= isset($post['funcao']) ? $post['funcao'] : "";
		$ccCGF      = isset($post['centroCusto']) ? $post['centroCusto'] : "";
		$descricaoCC= isset($post['descricaoCC']) ? $post['descricaoCC'] : "";
		$unidadeID 	= isset($post['unidadeID']) ? $post['unidadeID'] : 0;
		$usaFret 	= isset($post['usaFret']) ? $post['usaFret'] : 0;

		############ SALVA NO BANCO DA VELTRAC E BUSCA O ID ###################
		$pdo->query("INSERT INTO BD_CLIENTE.dbo.RFID (CODIGO, TIPO_ACESSO, TIPO_REPRESENTACAO) VALUES ({$codigo}, 2, 0)"); 
		
		$sqlIns = "INSERT INTO BD_CLIENTE.dbo.CONTROLE_ACESSO (
								NOME, 
								ITINERARIO_ID_IDA, 
								ITINERARIO_ID_VOLTA,
								CONTROLE_ACESSO_GRUPO_ID, 
								MATRICULA_FUNCIONAL, 
								ID_UNICO, 
								ATIVO,
								TAG,
								cpf,
								centro_custo
							) VALUES (
								'".$post['name']."', 
								{$itiIda}, 
								{$itiVolta},
								{$grupo},
								'{$matricula}', 
								{$originID},
								1,
								'{$codigo}',
								'{$cpf}',
								'{$centroCusto}'
							)";
								
		$rep =$pdo->query($sqlIns); 
		print_r($rep);die;

		############ SALVA NO BANCO DA LOCAL ###################
		$sqlIns = "INSERT INTO controle_acessos (ID_ORIGIN, NOME, ITINERARIO_ID_IDA, ITINERARIO_ID_VOLTA,CONTROLE_ACESSO_GRUPO_ID, MATRICULA_FUNCIONAL, ID_UNICO, ATIVO,TAG,cpf,centro_custo,created_at,POLTRONAIDA,POLTRONAVOLTA, funcao, descricaoCentro, unidadeID, usaFret) VALUE ({$originID}, '".$post['name']."', {$itiIda}, {$itiVolta}, {$grupo}, '{$matricula}', {$originID}, 1, '{$codigo}', '{$cpf}','".$ccCGF."', NOW(), {$polIda}, {$polVolta}, '{$funcao}', '{$descricaoCC}', {$unidadeID}, '{$usaFret}' )";

		$sql = $this->db->prepare($sqlIns);
		$sql->execute();

		if (!$sql)
			return false;

		//// Salvando / atualizando dados de Embarque \\\\
		$sql2 = $this->db->prepare("SELECT id FROM controle_acessos WHERE deleted_at is null order by id desc" );
		$sql2->execute();
		$ret = $sql2->fetch();

		if($ret)
		{ 
			$pontoEmbar 	= isset($post['pontoEmbar']) && $post['pontoEmbar'] != "" ? $post['pontoEmbar'] : 0;
			$pontoDesmbar 	= isset($post['pontoDesmbar']) && $post['pontoDesmbar'] != "" ? $post['pontoDesmbar'] : 0;
			$resEmbar 		= isset($post['resEmbar']) && $post['resEmbar'] != "" ? $post['resEmbar'] : 0;
			$resDesmbar 	= isset($post['resDesmbar']) && $post['resDesmbar'] != "" ? $post['resDesmbar'] : 0;

			$sql3 = $this->db->prepare("INSERT INTO pontos_controle_acesso (gerar_alerta, controle_acesso_id, ponto_referencia_id_embarque, ponto_referencia_id_desembarque, ponto_referencia_id_resid_embar, ponto_referencia_id_resid_desem, created_at) VALUE (:gerar_alerta, :controle_acesso_id,:ponto_referencia_id_embarque,:ponto_referencia_id_desembarque,:ponto_referencia_id_resid_embar,:ponto_referencia_id_resid_desem, NOW())");
			$sql3->bindValue(":gerar_alerta", 0);
			$sql3->bindValue(":controle_acesso_id", $ret['id']);
			$sql3->bindValue(":ponto_referencia_id_embarque", $pontoEmbar);
			$sql3->bindValue(":ponto_referencia_id_desembarque", $pontoDesmbar);
			$sql3->bindValue(":ponto_referencia_id_resid_embar", $resEmbar);
			$sql3->bindValue(":ponto_referencia_id_resid_desem", $resDesmbar);
			$sql3->execute();
		}

		return true;
	}

	public function seachPax($post)
	{

		$array = array();

		if(isset($_SESSION['cType']) && $_SESSION['cType'] == 1)
		{

			$where = " AND controle_acessos.CONTROLE_ACESSO_GRUPO_ID IN (708, 709)";

			if( isset($post['fbr']) && $post['fbr'] != "")
				$where = " AND controle_acessos.CONTROLE_ACESSO_GRUPO_ID = " . $post['fbr'];

			if( isset($post['name']) && $post['name'] != "")
			{
				$where .= " AND controle_acessos.NOME LIKE '%" . $post['name'] . "%'";
			}

			$sql = $this->db->prepare("SELECT *, TRIM(NOME) as NOME FROM controle_acessos where deleted_at is null {$where} ORDER BY TRIM(NOME)");

		} else {

			if( isset($post['fbr']) ){
				$where = " AND controle_acessos.CONTROLE_ACESSO_GRUPO_ID = " . $post['fbr'];
			}
			else {
				$where = " AND controle_acessos.CONTROLE_ACESSO_GRUPO_ID IN (SELECT ID_ORIGIN FROM acesso_grupos 
				WHERE id IN ( SELECT grupo_id FROM usuario_grupos WHERE usuario_id = ".$_SESSION['cLogin']." AND deleted_at is null) )";
			}
				
			if( isset($post['name']) && $post['name'] != "")
			{
				$where .= " AND controle_acessos.NOME LIKE '%" . $post['name'] . "%'";
			}

			$sql = $this->db->prepare("SELECT *, TRIM(NOME) as NOME FROM controle_acessos where deleted_at is null {$where} ORDER BY TRIM(NOME)");
		}

		$sql->execute();

		if($sql->rowCount() > 0) {
			$array = $sql->fetchAll();
		}

		return $array;
	}

	public function getPax($id)
	{

		try {
            $pdoSql = new \PDO ("dblib:host=$this->host:$this->port;dbname=$this->dbName;charset=utf8","$this->user","$this->pass");
        } catch (\Throwable $th) {
            $error = array('error'=>true, 'msg'=>'Ocorreu um erro ao tentar conectar ao Banco de Dados, tente novamente.');
            return $error;
        }

		$arr = array();

		$sq = "SELECT ca.*, pca.ponto_referencia_id_embarque, pca.ponto_referencia_id_desembarque, pca.ponto_referencia_id_resid_embar, pca.ponto_referencia_id_resid_desem, itiIDA.LINHA_ID AS LinhaIda, itiVol.LINHA_ID AS LinhaVolta
			FROM controle_acessos ca
			LEFT JOIN pontos_controle_acesso pca ON pca.controle_acesso_id = ca.id
			LEFT JOIN itinerarios itiIDA ON itiIDA.ID_ORIGIN = ca.ITINERARIO_ID_IDA
			LEFT JOIN itinerarios itiVol ON itiVol.ID_ORIGIN = ca.ITINERARIO_ID_VOLTA
			where ca.deleted_at is null AND ca.id = {$id}";

		$sql = $this->db->prepare($sq);

		$sql->execute();
		$ret = $sql->fetch();

		$arr["itiIda"] 		= [];
		$arr["itiVolta"] 	= [];
		$arr["pontosEmb"] 	= [];
		$arr["pontosDEmb"] 	= [];
		
		if($ret)
		{
			if(isset($ret['ITINERARIO_ID_IDA']))
			{
				$w = " AND it.ID = " . $ret['ITINERARIO_ID_IDA'];

				$sql = "SELECT it.ID,
						it.ATIVO AS AtivoIda, 
						CASE
							WHEN it.TIPO = 0 THEN 'Soltura'
							WHEN it.TIPO = 1 THEN 'Recolhimento'
							WHEN it.TIPO = 2 THEN 'Viagem'
							WHEN it.TIPO = 3 THEN 'Extra'
							WHEN it.TIPO = 4 THEN 'Turismo'
							ELSE ''
						END AS TipoIda,
						it.TRECHO as TrechoIda, 
						it.DESCRICAO as DescIda, 
						CASE
							WHEN it.SENTIDO = 0 THEN 'Ida'
							WHEN it.SENTIDO = 1 THEN 'Volta'
							WHEN it.SENTIDO = 2 THEN 'Unico'
							ELSE ''
						END AS SentidoIda
						FROM ITINERARIOS it WHERE it.ATIVO = 1 {$w};";

				$consulta   	= $pdoSql->query($sql);
				$retur 			= $consulta->fetch();

				if( isset($retur['ID']) ){
					$arr['itiIda']['ID'] = $retur['ID'];
					$arr['itiIda']['DESCRICAO'] = "Tipo: " . $retur['TipoIda'] . " | Sentido: " . $retur['SentidoIda'] .  " | Trecho: " . $retur['TrechoIda'] ." | ";

					$prt = $this->getPontosItinerario($pdoSql, $retur['ID']);
					$arr['pontosEmb'] = $prt;
					$arr['itiIda']['DESCRICAO'] .= "De: " . $prt[0]['NOME'] . " | Para: " . $prt[count($prt) -1]['NOME']; 
				}
					
			}

			if(isset($ret['ITINERARIO_ID_VOLTA']))
			{
				$w = " AND it.ID = " . $ret['ITINERARIO_ID_VOLTA'];

				$sql = "SELECT it.ID,
							it.ATIVO as AtivoVol, 
						CASE
							WHEN it.TIPO = 0 THEN 'Soltura'
							WHEN it.TIPO = 1 THEN 'Recolhimento'
							WHEN it.TIPO = 2 THEN 'Viagem'
							WHEN it.TIPO = 3 THEN 'Extra'
							WHEN it.TIPO = 4 THEN 'Turismo'
							ELSE ''
						END AS TipoVol,
						it.TRECHO as TrechoVol, 
						it.DESCRICAO as DescVol, 
						CASE
							WHEN it.SENTIDO = 0 THEN 'Ida'
							WHEN it.SENTIDO = 1 THEN 'Volta'
							WHEN it.SENTIDO = 2 THEN 'Unico'
							ELSE ''
						END AS SentidoVol
						FROM ITINERARIOS it WHERE it.ATIVO = 1 {$w};";

				$consulta   	= $pdoSql->query($sql);
				$retur 			= $consulta->fetch();

				if( isset($retur['ID']) ){
					$arr['itiVolta']['ID'] = $retur['ID'];
					$arr['itiVolta']['DESCRICAO']= "Tipo: " . $retur['TipoVol'] . " | Sentido: " . $retur['SentidoVol'] .  " | Trecho: " . $retur['TrechoVol'] ." | ";
				
					$prt = $this->getPontosItinerario($pdoSql, $retur['ID']);
					$arr['pontosDEmb'] = $prt;
					$arr['itiVolta']['DESCRICAO'] .= "De: ".$prt[0]['NOME'] . " | Para: " . $prt[count($prt) -1]['NOME']; 
				}

			}
		}

		$arr['ca'] = $ret;

		return $arr;
	}

	public function saveEditPax($post)
	{

		try {
            $pdo = new \PDO ("dblib:host=$this->host:$this->port;dbname=$this->dbName;charset=utf8","$this->user","$this->pass");
        } catch (\Throwable $th) {
            $error =array('error'=>true,'msg'=>'Ocorreu um erro ao tentar conectar ao Banco de Dados, tente novamente.');
            return $error;
        }

		////// BUSCANDO ID UNICO \\\\\\
		$sql2 = $this->db->prepare("SELECT ID_ORIGIN FROM controle_acessos WHERE deleted_at is null AND id = " . $post['id'] );
		$sql2->execute();
		$ret  		= $sql2->fetch();
		$idOrigin 	= $ret['ID_ORIGIN'];

		$centroCusto = "";

		if(isset($post['polIda'])) $centroCusto = $post['polIda'];
		if(isset($post['polVolta'])) $centroCusto .= ";" . $post['polVolta'];

		$grupo 		= 0;

		if (isset($post['grupo']) && $post['grupo'] != "")
		{
			$grupo 	= $post['grupo'];
		} else if( isset($_SESSION['cGr']) )
		{
			$sql = $this->db->prepare("SELECT * FROM grupo_linhas WHERE id = {$_SESSION['cGr']}");
			$sql->execute();
			$gpr = $sql->fetch(PDO::FETCH_OBJ);
			$grupo = $gpr->GRUPOSUSER;
		}

		$itiIda 	= isset($post['itiIda']) ? $post['itiIda'] : 0;
		$itiVolta 	= isset($post['itiVolta']) ? $post['itiVolta'] : 0;
		$matricula 	= isset($post['matricula']) ? $post['matricula'] :  0;
		$codigo 	= isset($post['codigo']) ? $post['codigo'] :  0;
		$cpf 		= isset($post['cpf']) ? $post['cpf'] : 0;
		$polIda 	= isset($post['polIda']) ? $post['polIda'] :  0;
		$polVolta 	= isset($post['polVolta']) ? $post['polVolta'] : 0;
		$ativo 		= isset($post['ativo']) ? $post['ativo'] : 1;

		$funcao 	= isset($post['funcao']) ? $post['funcao'] : "";
		$ccCGF      = isset($post['centroCusto']) ? $post['centroCusto'] : "";
		$descricaoCC= isset($post['descricaoCC']) ? $post['descricaoCC'] : "";
		$unidadeID 	= isset($post['unidadeID']) ? $post['unidadeID'] : 0;
		$usaFret 	= isset($post['usaFret']) ? $post['usaFret'] : 0;

		############ ATUALIZANDO NA VELTRAC ###################
		// $sqlIns = "UPDATE BD_CLIENTE.dbo.CONTROLE_ACESSO SET
			// 							NOME = '".$post['name']."', 
			// 							ITINERARIO_ID_IDA = {$itiIda}, 
			// 							ITINERARIO_ID_VOLTA = {$itiVolta},
			// 							CONTROLE_ACESSO_GRUPO_ID = {$grupo}, 
			// 							MATRICULA_FUNCIONAL = '{$matricula}', 
			// 							ATIVO = {$ativo},
			// 							TAG = '{$codigo}',
			// 							cpf = '{$cpf}',
			// 							centro_custo = '{$centroCusto}'
			// 							WHERE ID = {$idOrigin};";
					
			// $pdo->query($sqlIns); 

		############ ATUALIZANDO OS ADOS NO BANCO ##################
		$sql = $this->db->prepare("UPDATE controle_acessos SET NOME = :NOME, ITINERARIO_ID_IDA = :ITINERARIO_ID_IDA, ITINERARIO_ID_VOLTA = :ITINERARIO_ID_VOLTA, CONTROLE_ACESSO_GRUPO_ID = :CONTROLE_ACESSO_GRUPO_ID, MATRICULA_FUNCIONAL = :MATRICULA_FUNCIONAL, ATIVO = :ATIVO, TAG = :TAG, cpf = :cpf, centro_custo = :centro_custo, POLTRONAIDA = :POLTRONAIDA, POLTRONAVOLTA = :POLTRONAVOLTA, funcao = :funcao, descricaoCentro = :descricaoCentro, unidadeID = :unidadeID, usaFret = :usaFret, updated_at = NOW() WHERE id = :id");
		$sql->bindValue(":NOME", $post['name']);
		$sql->bindValue(":ITINERARIO_ID_IDA", $itiIda);
		$sql->bindValue(":ITINERARIO_ID_VOLTA", $itiVolta);
		$sql->bindValue(":CONTROLE_ACESSO_GRUPO_ID", $grupo);
		$sql->bindValue(":MATRICULA_FUNCIONAL", $matricula);
		$sql->bindValue(":ATIVO", $ativo);
		$sql->bindValue(":TAG", $codigo);
		$sql->bindValue(":cpf", $cpf);
		$sql->bindValue(":centro_custo", $ccCGF);
		$sql->bindValue(":POLTRONAIDA", $polIda);
		$sql->bindValue(":POLTRONAVOLTA", $polVolta);
		$sql->bindValue(":funcao", $funcao);
		$sql->bindValue(":descricaoCentro", $descricaoCC);
		$sql->bindValue(":unidadeID", $unidadeID);
		$sql->bindValue(":usaFret", $usaFret);
		$sql->bindValue(":id", $post['id']);
		$sql->execute();
	
		if (!$sql)
			return false;

		//// Salvando / atualizando dados de Embarque \\\\
		$sql2 = $this->db->prepare("SELECT * FROM pontos_controle_acesso WHERE deleted_at is null AND controle_acesso_id = " . $post['id'] );
		$sql2->execute();
		$ret = $sql2->fetch();

		$pontoEmbar 	= isset($post['pontoEmbar']) && $post['pontoEmbar'] != "" ? $post['pontoEmbar'] : 0;
		$pontoDesmbar 	= isset($post['pontoDesmbar']) && $post['pontoDesmbar'] != "" ? $post['pontoDesmbar'] : 0;
		$resEmbar 		= isset($post['resEmbar']) && $post['resEmbar'] != "" ? $post['resEmbar'] : 0;
		$resDesmbar 	= isset($post['resDesmbar']) && $post['resDesmbar'] != "" ? $post['resDesmbar'] : 0;

		if($ret)
		{ // Update
			$sql3 = $this->db->prepare("UPDATE pontos_controle_acesso SET ponto_referencia_id_embarque = :ponto_referencia_id_embarque, ponto_referencia_id_desembarque = :ponto_referencia_id_desembarque, ponto_referencia_id_resid_embar = :ponto_referencia_id_resid_embar, ponto_referencia_id_resid_desem = :ponto_referencia_id_resid_desem WHERE controle_acesso_id = :controle_acesso_id");
			$sql3->bindValue(":ponto_referencia_id_embarque", $pontoEmbar);
			$sql3->bindValue(":ponto_referencia_id_desembarque", $pontoDesmbar);
			$sql3->bindValue(":ponto_referencia_id_resid_embar", $resEmbar);
			$sql3->bindValue(":ponto_referencia_id_resid_desem", $resDesmbar);
			$sql3->bindValue(":controle_acesso_id", $post['id']);
			$sql3->execute();

		} else { // Insert

			$sql3 = $this->db->prepare("INSERT INTO pontos_controle_acesso (gerar_alerta, controle_acesso_id, ponto_referencia_id_embarque, ponto_referencia_id_desembarque, ponto_referencia_id_resid_embar, ponto_referencia_id_resid_desem, created_at) VALUE (:gerar_alerta, :controle_acesso_id,:ponto_referencia_id_embarque,:ponto_referencia_id_desembarque,:ponto_referencia_id_resid_embar,:ponto_referencia_id_resid_desem, NOW())");
			$sql3->bindValue(":gerar_alerta", 0);
			$sql3->bindValue(":controle_acesso_id", $post['id']);
			$sql3->bindValue(":ponto_referencia_id_embarque", $pontoEmbar);
			$sql3->bindValue(":ponto_referencia_id_desembarque", $pontoDesmbar);
			$sql3->bindValue(":ponto_referencia_id_resid_embar", $resEmbar);
			$sql3->bindValue(":ponto_referencia_id_resid_desem", $resDesmbar);
			$sql3->execute();

		}
		
		return true;
	}



}