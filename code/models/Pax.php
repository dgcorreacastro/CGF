<?php

class Pax extends model 
{
    private $host   = ""; // TODO: POPULATE WITH DATABASE HOST ADDRESS
    private $port   = ""; // TODO: POPULATE WITH DATABASE PORT NUMBER
    private $user   = ""; // TODO: POPULATE WITH DATABASE USER
    private $pass   = ""; // TODO: POPULATE WITH DATABASE PASSWORD
    private $dbName = ""; // TODO: POPULATE WITH DATABASE NAME

	public function list($groups, $pag, $unid, $name, $mat, $cod, $int, $withoutGroups, $cgfid, $autocad, $wpic, $wnpic, $limPag)
	{
		$w = "";

		if (!isset($withoutGroups) || $withoutGroups !== "on") {

			if( isset($_SESSION['cGr']) ){

				$sql = $this->db->prepare("SELECT * FROM grupo_linhas WHERE id = {$_SESSION['cGr']}");
				$sql->execute();
				$gpr = $sql->fetch(PDO::FETCH_OBJ);

				$w .= " AND s.CONTROLE_ACESSO_GRUPO_ID = {$gpr->GRUPOSUSER} ";
				
			} else {

				$w .= " AND s.CONTROLE_ACESSO_GRUPO_ID IN ({$groups}) ";

			}

		}
		
        if($unid != null && $unid > 0)
            $w .= " AND s.unidadeID = {$unid} ";

		//FILTRAGEM FEITA PELA URL
		if(isset($name) && $name != "")
			$w .= " AND s.NOME LIKE '%{$name}%' ";
		
		if(isset($mat) && $mat != "")
			$w .= " AND s.MATRICULA_FUNCIONAL LIKE '%{$mat}%' ";

		if(isset($cod) && $cod != "")
			$w .= " AND s.TAG LIKE '%{$cod}%' ";

		if(isset($int) && $int == 1)
			$w .= " AND s.ATIVO = 1";

		if(isset($withoutGroups) && $withoutGroups === "on")
			$w .= " AND s.CONTROLE_ACESSO_GRUPO_ID = 0 AND unidadeID = " . $this->getIdOriginGroupLine();

		if(isset($cgfid) && $cgfid === "on")
			$w .= " AND (s.created_cgf_id IS NOT NULL OR s.updated_cgf_id IS NOT NULL)";

		if(isset($autocad) && $autocad === "on")
			$w .= " AND s.ID_UNICO != 0 AND s.ID_UNICO = s.unidadeID";

		$joinPics = (isset($wpic) && $wpic === "on") ? 'JOIN' : 'LEFT JOIN';

		if(isset($wnpic) && $wnpic === "on")
			$w .= " AND cap.img IS NULL";

		#######################################################################
        ############################# GET TOTAL ###############################
        #######################################################################
        $sql="SELECT s.id, cap.img AS picture
			FROM controle_acessos s 
			$joinPics controle_acessos_pics cap ON cap.controle_acesso_id = s.id AND cap.position = 'pic_front_smiling'
			WHERE s.deleted_at IS NULL AND s.user_type = 1 {$w}";
        $sql = $this->db->prepare($sql);
        $sql->execute();
        $tt = $sql->rowCount();
        #######################################################################
        ######################### CONTINUE FILTERS ############################
        #######################################################################
		
        $ttPages    = intval( ceil($tt / $limPag) ); 
		$of         = $limPag * ($pag - 1);
        $offset     = $of > 0 ? " OFFSET $of" : "";
		$ret 		= array();

		try {

			$sql = "SELECT *, TRIM(s.NOME) AS NOME, TRIM(LEADING '0' FROM s.TAG) AS TAG, un.descricao AS Unidade, cap.img AS picture, s.id AS id,
                
				CASE
					WHEN s.ID_UNICO != 0 AND s.ID_UNICO = s.unidadeID THEN 'SIM'
					ELSE 'NAO'
				END AS CGFPASS
				
				FROM controle_acessos s
                LEFT JOIN unidades un ON un.id = s.unidadeID
				$joinPics controle_acessos_pics cap ON cap.controle_acesso_id = s.id AND cap.position = 'pic_front_smiling'
				WHERE s.deleted_at IS NULL AND s.user_type = 1 {$w} 
				ORDER BY TRIM(s.NOME), s.ATIVO ASC
				LIMIT {$limPag} {$offset}";

			$sql = $this->db->prepare($sql);
			$sql->execute();
			$ret = $sql->fetchAll(PDO::FETCH_OBJ);

			foreach($ret as $key => $ca){

				if($ca->picture){
					$resize = $this->resizeImage($ca->picture);
					$ret[$key]->picture = $resize;
				}

			}

			$ttOnPage = $sql->rowCount();

		} catch (\Throwable $th) {
			$ttOnPage = 0;
		}
		
		return array ( "users"=> $ret, "ttPages"=> $ttPages, 'total' => $tt, 'ttOnPage' => $ttOnPage );
	}	

	public function getIdOriginGroupLine()
	{
		$sql = "SELECT * FROM grupo_linhas where deleted_at is null AND id = " . $_SESSION['groupUserID'];
        $sql = $this->db->prepare($sql);
        $sql->execute();
        $tt = $sql->fetch(PDO::FETCH_OBJ);
		return $tt ? $tt->ID_ORIGIN : 0;
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

	public function getLinhasWithSenti($sentido = null)
    {

		$itSentido = "";

		if($sentido != null){
            $itSentido = "AND itinerarios.SENTIDO = {$sentido}";
        }
		
        if(isset($_SESSION['cType']) && $_SESSION['cType'] != 1)
		{

			$sql = $this->db->prepare("SELECT linhas.id, linhas.ID_ORIGIN, linhas.PREFIXO, linhas.NOME, itinerarios.DESCRICAO, itinerarios.SENTIDO
                                        FROM linhas 
									INNER JOIN itinerarios ON itinerarios.LINHA_ID = linhas.ID_ORIGIN 
									WHERE linhas.deleted_at is null AND linhas.ATIVO = 1 AND linhas.id IN ( SELECT linha_id FROM usuario_linhas WHERE usuario_id = {$_SESSION['cLogin']} AND deleted_at is null ) AND itinerarios.ATIVO = 1
									{$itSentido}
									order by linhas.NOME");
        } else {

			$sql = $this->db->prepare("SELECT linhas.id, linhas.ID_ORIGIN, linhas.PREFIXO, linhas.NOME, itinerarios.DESCRICAO, itinerarios.SENTIDO
                                            FROM linhas 
                                            INNER JOIN itinerarios ON itinerarios.LINHA_ID = linhas.ID_ORIGIN
                                            where linhas.deleted_at is null AND linhas.ATIVO = 1 AND itinerarios.ATIVO = 1
											{$itSentido}
											order by linhas.NOME");
		}

		$sql->execute();
        $array = $sql->fetchAll();

		if (count($array) > 1)
            {
                foreach ($array as $k => $linha){
                    $array[$k]['DESCRICAO'] = (preg_match('!!u', utf8_decode($linha['DESCRICAO']))) ? utf8_decode($linha['DESCRICAO']) : $linha['DESCRICAO'];
                }
            }

        return $array;
    }

	public function getLinhasExcelAdm($groupUser, $sentido){
		
		$sql = $this->db->prepare("SELECT id FROM users WHERE groupUserID = {$groupUser} AND deleted_at is null");
		$sql->execute();
		$users = $sql->fetchAll();

		$usersIds = array();

		if(count($users) > 0)
        {
            foreach ($users as $user)
            {
                $usersIds[] = $user['id'];
            }
        }

		$usIn = count($usersIds) > 0 ? implode(",", $usersIds) : 0; 

		$sql = $this->db->prepare("SELECT DISTINCT linhas.id, linhas.ID_ORIGIN, linhas.PREFIXO, linhas.NOME, itinerarios.DESCRICAO, itinerarios.SENTIDO
                                    FROM linhas 
									INNER JOIN itinerarios ON itinerarios.LINHA_ID = linhas.ID_ORIGIN 
									WHERE linhas.deleted_at is null AND linhas.ATIVO = 1 AND linhas.id IN ( SELECT linha_id FROM usuario_linhas WHERE usuario_id IN ({$usIn}) AND deleted_at is null ) AND itinerarios.ATIVO = 1
									AND itinerarios.SENTIDO = {$sentido}
									order by linhas.NOME");

		$sql->execute();
        $array = $sql->fetchAll();

		if (count($array) > 1)
            {
                foreach ($array as $k => $linha){
                    $array[$k]['DESCRICAO'] = (preg_match('!!u', utf8_decode($linha['DESCRICAO']))) ? utf8_decode($linha['DESCRICAO']) : $linha['DESCRICAO'];
                }
            }

        return $array;

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

		$w = " AND it.LINHA_ID = {$id} "; //AND it.SENTIDO = {$s}

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
		$sql = "SELECT pto.*, pr.NOME, pr.ID AS ID
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
		$grupo 			= 0;

		if(isset($post['polIda']) && $post['polIda'] != "") $centroCusto = $post['polIda'];
		if(isset($post['polVolta']) && $post['polVolta'] != "") $centroCusto .= ";" . $post['polVolta'];

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
		// $ccCGF      = isset($post['centroCusto']) ? $post['centroCusto'] : "";
		$descricaoCC= isset($post['descricaoCC']) ? $post['descricaoCC'] : "";
		$unidadeID 	= isset($post['unidadeID']) ? $post['unidadeID'] : 0;
		$usaFret 	= isset($post['usaFret']) ? $post['usaFret'] : 0;
		$residencia = isset($post['residencia']) ? $post['residencia'] : "";
		$monitor 	= isset($post['monitor']) ? $post['monitor'] : 0;
		$eyeglasses = isset($post['eyeglasses']) ? $post['eyeglasses'] : 0;

		############ SALVA NO BANCO DA VELTRAC E BUSCA O ID ###################
		############ CHECK SE EXISTE, SE NÃO EXISTIR CADASTRA #################

		if ( isset($codigo) && $codigo != null )
		{

			############ BUSCANDO O ULTIMO ID PARA INCREMENTAR ###################
			$sql = "SELECT ID FROM BD_CLIENTE.dbo.CONTROLE_ACESSO order by ID DESC;";
			$con = $pdo->query($sql); 
			$data= $con->fetch();

			$originID 	= $data['ID'] + 1;

			$sql = "SELECT CODIGO FROM BD_CLIENTE.dbo.RFID WHERE CODIGO = {$codigo};";
			$con = $pdo->query($sql); 
			$data= $con->fetch();
			// Se não Tiver Cadastra
			if ( !isset($data['CODIGO']) )
			{
				$pdo->query("INSERT INTO BD_CLIENTE.dbo.RFID (CODIGO, TIPO_ACESSO, TIPO_REPRESENTACAO) VALUES ({$codigo},2,0)"); 
			}

			$sql = "SELECT * FROM BD_CLIENTE.dbo.CONTROLE_ACESSO_VIGENCIA WHERE TAG = {$codigo} ORDER BY DATA_INICIO DESC;";
			$con = $pdo->query($sql); 
			$datas= $con->fetch();

			// Se não Tiver Cadastra
			$date = date("Y-m-d H:i:s");

			if ( isset($datas['TAG']) )
			{
				// Desativa o cadastro antigo e Insere o novo
				$oldID = $datas['CONTROLE_ACESSO_ID'];
				$pdo->query("UPDATE BD_CLIENTE.dbo.CONTROLE_ACESSO_VIGENCIA SET DATA_TERMINO = '{$date}' WHERE CONTROLE_ACESSO_ID = {$oldID} AND TAG = '{$codigo}'");

				// Inativa o outro Passageiro 
				$pdo->query("UPDATE BD_CLIENTE.dbo.CONTROLE_ACESSO SET ATIVO = 0, TAG = null WHERE ID = {$oldID} AND TAG = '{$codigo}'");

				// Inativa no CGF 
				$sql2 = $this->db->prepare("UPDATE controle_acessos SET ATIVO = 0 WHERE ID_ORIGIN = {$oldID} AND TAG = '{$codigo}'");
				$sql2->execute();
			}

			$sqlIns = 	"INSERT INTO BD_CLIENTE.dbo.CONTROLE_ACESSO (
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
					
			$pdo->query($sqlIns); 
			
			sleep(2);

			$qurt = "INSERT INTO BD_CLIENTE.dbo.CONTROLE_ACESSO_VIGENCIA (TAG, DATA_TERMINO, CONTROLE_ACESSO_ID, DATA_INICIO) VALUES ('{$codigo}', null, {$originID}, '{$date}')";
			$pdo->query($qurt);
		}

		############ SALVA NO BANCO DA LOCAL ###################
		$sqlPax = "INSERT INTO controle_acessos (ID_ORIGIN, NOME, ITINERARIO_ID_IDA, ITINERARIO_ID_VOLTA,CONTROLE_ACESSO_GRUPO_ID, MATRICULA_FUNCIONAL, ID_UNICO, ATIVO, TAG, cpf, centro_custo, created_at,POLTRONAIDA, POLTRONAVOLTA, funcao, descricaoCentro, unidadeID, usaFret, residencia, monitor, eyeglasses) VALUES (:ID_ORIGIN, :NOME, :ITINERARIO_ID_IDA, :ITINERARIO_ID_VOLTA, :CONTROLE_ACESSO_GRUPO_ID, :MATRICULA_FUNCIONAL, :ID_UNICO, :ATIVO, :TAG, :cpf, :centro_custo, NOW(), :POLTRONAIDA, :POLTRONAVOLTA, :funcao, :descricaoCentro, :unidadeID, :usaFret, :residencia, :monitor, :eyeglasses)";

		$sql = $this->db->prepare($sqlPax);
		$sql->bindValue(":ID_ORIGIN", $originID);
		$sql->bindValue(":NOME", $post['name']);
		$sql->bindValue(":ITINERARIO_ID_IDA", $itiIda);
		$sql->bindValue(":ITINERARIO_ID_VOLTA", $itiVolta);
		$sql->bindValue(":CONTROLE_ACESSO_GRUPO_ID", $grupo);
		$sql->bindValue(":MATRICULA_FUNCIONAL", $matricula);
		$sql->bindValue(":ID_UNICO", $originID);
		$sql->bindValue(":ATIVO", 1);
		$sql->bindValue(":TAG", $codigo);
		$sql->bindValue(":cpf", $cpf);
		$sql->bindValue(":centro_custo", $centroCusto);
		$sql->bindValue(":POLTRONAIDA", $polIda);
		$sql->bindValue(":POLTRONAVOLTA", $polVolta);
		$sql->bindValue(":funcao", $funcao);
		$sql->bindValue(":descricaoCentro", $descricaoCC);
		$sql->bindValue(":unidadeID", $unidadeID);
		$sql->bindValue(":usaFret", $usaFret);
		$sql->bindValue(":residencia", $residencia);
		$sql->bindValue(":monitor", $monitor);
		$sql->bindValue(":eyeglasses", $eyeglasses);

		try{

			$sql->execute();
			$idContAcc = $this->db->lastInsertId();

			$pontoEmbar 	= isset($post['pontoEmbar']) && $post['pontoEmbar'] != "" ? $post['pontoEmbar'] : 0;
			$pontoDesmbar 	= isset($post['pontoDesmbar']) && $post['pontoDesmbar'] != "" ? $post['pontoDesmbar'] : 0;
			$resEmbar 		= isset($post['resEmbar']) && $post['resEmbar'] != "" ? $post['resEmbar'] : 0;
			$resDesmbar 	= isset($post['resDesmbar']) && $post['resDesmbar'] != "" ? $post['resDesmbar'] : 0;

			$sql3 = $this->db->prepare("INSERT INTO pontos_controle_acesso (gerar_alerta, controle_acesso_id, ponto_referencia_id_embarque, ponto_referencia_id_desembarque, ponto_referencia_id_resid_embar, ponto_referencia_id_resid_desem, created_at) VALUE (:gerar_alerta, :controle_acesso_id,:ponto_referencia_id_embarque,:ponto_referencia_id_desembarque,:ponto_referencia_id_resid_embar,:ponto_referencia_id_resid_desem, NOW())");
			$sql3->bindValue(":gerar_alerta", 0);
			$sql3->bindValue(":controle_acesso_id", $idContAcc);
			$sql3->bindValue(":ponto_referencia_id_embarque", $pontoEmbar);
			$sql3->bindValue(":ponto_referencia_id_desembarque", $pontoDesmbar);
			$sql3->bindValue(":ponto_referencia_id_resid_embar", $resEmbar);
			$sql3->bindValue(":ponto_referencia_id_resid_desem", $resDesmbar);
			$sql3->execute();
			
			############################################################
			############### TRATANDO LINHAS ADICIONAIS #################
			############################################################
			if( isset($post['novaLinha']) )
			{
				try {

					foreach($post['novaLinha'] AS $line)
					{
						$sql2 = $this->db->prepare("SELECT * FROM linhasAdicionais WHERE deleted_at is null AND linha_id = {$line} AND controle_acesso_id = {$idContAcc}");
						$sql2->execute();
						$hasLin = $sql2->fetch(\PDO::FETCH_OBJ);
		
						if (!$hasLin)
						{
							$sql3 = $this->db->prepare("INSERT INTO linhasAdicionais (linha_id, controle_acesso_id, created_at) VALUE (:linha_id, :controle_acesso_id, NOW())");
							$sql3->bindValue(":linha_id", $line);
							$sql3->bindValue(":controle_acesso_id", $idContAcc);
							$sql3->execute();
						} 
					
					}
					
				} catch (\Throwable $th) {
					//throw $th;
				}

			}

			############################################################
			###################### SALVAR FOTOS ########################
			############################################################

			// $saveUserPics = $this->saveUserPics($idContAcc, $eyeglasses, $post, false);
			
			// return $saveUserPics;

			if (isset($post['tempCaId']) && $post['tempCaId'] != "" && isset($post['tempCa']) && $post['tempCa'] != ""){
				
				$tempCaId = $post['tempCaId'];
				$tempCa = $post['tempCa'];

				$deleteTempCa = $this->db->prepare("DELETE FROM temp_ca WHERE id = :tempCaId");
				$deleteTempCa->bindValue(":tempCaId", $tempCaId);
				$deleteTempCa->execute();

				$updateDs = $this->db->prepare("UPDATE controle_acessos_ds SET controle_acesso_id = :idContAcc WHERE controle_acesso_id = :tempCa");
				$updateDs->bindValue(":idContAcc", $idContAcc);
				$updateDs->bindValue(":tempCa", $tempCa);
				$updateDs->execute();

				$updateImg = $this->db->prepare("UPDATE controle_acessos_pics SET controle_acesso_id = :idContAcc WHERE controle_acesso_id = :tempCa");
				$updateImg->bindValue(":idContAcc", $idContAcc);
				$updateImg->bindValue(":tempCa", $tempCa);
				$updateImg->execute();
			}

			return true;

		} catch (\Throwable $th) {
			return false;
		}

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

		if(!$this->checkUserGroup($id)){
			$arr['status'] = false;
			return $arr;
		}
		

		$sq = "SELECT ca.*, TRIM(LEADING '0' FROM ca.TAG) AS TAG, pca.ponto_referencia_id_embarque, pca.ponto_referencia_id_desembarque, pca.ponto_referencia_id_resid_embar, pca.ponto_referencia_id_resid_desem, itiIDA.LINHA_ID AS LinhaIda, itiVol.LINHA_ID AS LinhaVolta
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
				$w = " AND ( it.ID = " . $ret['ITINERARIO_ID_IDA'] . " OR it.ITINERARIO_ID_PAI = " . $ret['ITINERARIO_ID_IDA'] . " )";

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

				$arr['itiIda']['ID'] = $ret['ITINERARIO_ID_IDA']; //$retur['ID'];

				if( isset($retur['ID']) ){
					$arr['itiIda']['DESCRICAO'] = "Tipo: " . $retur['TipoIda'] . " | Sentido: " . $retur['SentidoIda'] .  " | Trecho: " . $retur['TrechoIda'] ." | ";

					$prt = $this->getPontosItinerario($pdoSql, $retur['ID']);
					$arr['pontosEmb'] = $prt;
					$arr['itiIda']['DESCRICAO'] .= "De: " . $prt[0]['NOME'] . " | Para: " . $prt[count($prt) -1]['NOME']; 
				}
					
			}

			if(isset($ret['ITINERARIO_ID_VOLTA']))
			{
				$w = " AND ( it.ID = " . $ret['ITINERARIO_ID_VOLTA'] . " OR it.ITINERARIO_ID_PAI = " . $ret['ITINERARIO_ID_VOLTA'] . " )";

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

				$arr['itiVolta']['ID'] = $ret['ITINERARIO_ID_VOLTA']; //$retur['ID'];

				if( isset($retur['ID']) ){
					$arr['itiVolta']['DESCRICAO']= "Tipo: " . $retur['TipoVol'] . " | Sentido: " . $retur['SentidoVol'] .  " | Trecho: " . $retur['TrechoVol'] ." | ";
				
					$prt = $this->getPontosItinerario($pdoSql, $retur['ID']);
					$arr['pontosDEmb'] = $prt;
					$arr['itiVolta']['DESCRICAO'] .= "De: ".$prt[0]['NOME'] . " | Para: " . $prt[count($prt) -1]['NOME']; 
				}

			}

			##### BUSCA LINHAS ADICIONAIS #####
			$sql2 = $this->db->prepare("SELECT linhasAdicionais.*, itinerarios.SENTIDO AS sentido
			FROM linhasAdicionais
			INNER JOIN itinerarios ON itinerarios.LINHA_ID = linhasAdicionais.linha_id
			WHERE linhasAdicionais.deleted_at is null AND controle_acesso_id = {$id} AND itinerarios.ATIVO = 1");
			$sql2->execute();
			$arr['linhasAdic'] = $sql2->fetchAll(\PDO::FETCH_OBJ);



			##### BUSCA FOTOS DO USUÁRIOS #####

			try{

				$getUserPics = $this->getUserPics($id, true);

				if($getUserPics['ret']){

					$ret = array_merge($ret, $getUserPics['ret']);

					if($getUserPics['pictures']) {

						$pictures = $getUserPics['pictures'];
	
						foreach ($pictures AS $picture) {
	
							$ret[$picture->position.'_error'] = $picture->error;
	
							if($picture->error == 0 && $picture->img){

								$picDs = $this->db->prepare("SELECT id FROM controle_acessos_ds WHERE controle_acesso_id = {$id} AND position = '{$picture->position}' AND deleted_at IS NULL LIMIT 1");
								$picDs->execute();

								$ret[$picture->position.'_hasDs'] = $picDs->rowCount() == 1 ? 1 : 0;
	
								$ret[$picture->position] = $this->toBase64UserPic($picture->img) ?? 0;
								
							}
							
						}
	
					}
					
				}

			} catch (\Throwable $th) {}

		}

		$arr['ca'] = $ret;
		$arr['status'] = true;
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
		$centroCusto= "";
		$grupo 		= 0;

		if(isset($post['polIda'])) $centroCusto = $post['polIda'];
		if(isset($post['polVolta'])) $centroCusto .= ";" . $post['polVolta'];

		if (isset($post['grupo']) && $post['grupo'] != "") {
			$grupo 	= $post['grupo'];
		} 
		else if( isset($_SESSION['cGr']) ) {
			$sql = $this->db->prepare("SELECT * FROM grupo_linhas WHERE id = {$_SESSION['cGr']}");
			$sql->execute();
			$gpr = $sql->fetch(PDO::FETCH_OBJ);
			$grupo = $gpr->GRUPOSUSER;
		}

		$itiIda 	= isset($post['itiIda']) && !empty($post['itiIda']) ? $post['itiIda'] : 0;
		$itiVolta 	= isset($post['itiVolta']) && !empty($post['itiVolta']) ? $post['itiVolta'] : 0;
		$matricula 	= isset($post['matricula']) ? $post['matricula'] :  0;
		$codigo 	= isset($post['codigo']) ? $post['codigo'] : 0;
		$cpf 		= isset($post['cpf']) ? $post['cpf'] : 0;
		$polIda 	= isset($post['polIda']) ? $post['polIda'] : 0;
		$polVolta 	= isset($post['polVolta']) ? $post['polVolta'] : 0;
		$ativo 		= isset($post['ativo']) ? $post['ativo'] : 0;

		$funcao 	= isset($post['funcao']) && !empty($post['funcao'])? $post['funcao'] : "";
		// $ccCGF      = isset($post['centroCusto']) && !empty($post['centroCusto'])? $post['centroCusto'] : "";
		$descricaoCC= isset($post['descricaoCC']) && !empty($post['descricaoCC'])? $post['descricaoCC'] : "";
		$unidadeID 	= isset($post['unidadeID']) && !empty($post['unidadeID'])? $post['unidadeID'] : 0;
		$usaFret 	= isset($post['usaFret']) && !empty($post['usaFret'])? $post['usaFret'] : 0;
		$residencia = isset($post['residencia']) && !empty($post['residencia'])? $post['residencia'] : "";
		$monitor 	= isset($post['monitor']) ? $post['monitor'] : 0;
		$eyeglasses = isset($post['eyeglasses']) ? $post['eyeglasses'] : 0;
		
		$date = date("Y-m-d H:i:s");

		if ($codigo != null && $codigo != 0){
			
			$sql = "SELECT CODIGO FROM BD_CLIENTE.dbo.RFID WHERE CODIGO = {$codigo};";
			$con = $pdo->query($sql); 
			$rfid= $con->fetch();
			// Se não Tiver em RFID, Cadastra
			if ( !isset($rfid['CODIGO']) )
			{
				$pdo->query("INSERT INTO BD_CLIENTE.dbo.RFID (CODIGO, TIPO_ACESSO, TIPO_REPRESENTACAO) VALUES ({$codigo},2,0)"); 
			}

			usleep(500);

			///// Caso esteja como zero o OriginID cadastra o usuário pois possivelmente foi cadastrado sem código e por isso não replica na veltrac \\\\\
			if ( $idOrigin == 0 ){
				############ BUSCANDO O ULTIMO ID PARA INCREMENTAR ###################
				$sql 	  = "SELECT ID FROM BD_CLIENTE.dbo.CONTROLE_ACESSO order by ID DESC;";
				$con 	  = $pdo->query($sql); 
				$data 	  = $con->fetch();
				$idOrigin = $data['ID'] + 1;
			
				// Tratar vigencia do cartão \\
				$sql = "SELECT * FROM BD_CLIENTE.dbo.CONTROLE_ACESSO_VIGENCIA WHERE TAG = {$codigo} ORDER BY DATA_INICIO DESC;";
				$con = $pdo->query($sql); 
				$datas= $con->fetch();

				if ( !isset($datas['TAG']) )
				{
					$sqlIns = 	"INSERT INTO BD_CLIENTE.dbo.CONTROLE_ACESSO (
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
						{$idOrigin},
						1,
						'{$codigo}',
						'{$cpf}',
						'{$centroCusto}'
					)";
					
					$pdo->query($sqlIns); 
					
					usleep(500);

					$pdo->query("INSERT INTO BD_CLIENTE.dbo.CONTROLE_ACESSO_VIGENCIA (TAG, DATA_TERMINO, CONTROLE_ACESSO_ID, DATA_INICIO) VALUES ('{$codigo}', null, {$idOrigin}, '{$date}')");
				}

			} else {

				$sql = "SELECT * FROM BD_CLIENTE.dbo.CONTROLE_ACESSO_VIGENCIA WHERE TAG = '{$codigo}' AND DATA_TERMINO IS NULL AND CONTROLE_ACESSO_ID <> {$idOrigin} ORDER BY DATA_INICIO DESC;";
				$con = $pdo->query($sql); 
				$datas= $con->fetch();

				// Se não Tiver Cadastra
				if ( isset($datas['TAG']) )
				{
					// Desativa o cadastro antigo e Insere o novo
					$oldID = $datas['CONTROLE_ACESSO_ID'];
					$pdo->query("UPDATE BD_CLIENTE.dbo.CONTROLE_ACESSO_VIGENCIA SET DATA_TERMINO = '{$date}' WHERE CONTROLE_ACESSO_ID = {$oldID} AND TAG = '{$codigo}'");

					$pdo->query("INSERT INTO BD_CLIENTE.dbo.CONTROLE_ACESSO_VIGENCIA (TAG, DATA_TERMINO, CONTROLE_ACESSO_ID, DATA_INICIO) VALUES ('{$codigo}', null, {$idOrigin}, '{$date}')");

					// Inativa o outro Passageiro 
					$pdo->query("UPDATE BD_CLIENTE.dbo.CONTROLE_ACESSO SET ATIVO = 0, TAG = null WHERE ID = {$oldID} AND TAG = '{$codigo}'");

					// Inativa no CGF 
					$sql2 = $this->db->prepare("UPDATE controle_acessos SET ATIVO = 0 WHERE ID_ORIGIN = {$oldID} AND TAG = '{$codigo}'");
					$sql2->execute();
				} else {
					//// Verifica se ele tem registro 
					$sql = "SELECT * FROM BD_CLIENTE.dbo.CONTROLE_ACESSO_VIGENCIA WHERE TAG = '{$codigo}' AND CONTROLE_ACESSO_ID = {$idOrigin} ORDER BY DATA_INICIO DESC;";
					$con = $pdo->query($sql); 
					$datas= $con->fetch();
			
					if ( !isset($datas['TAG']) )
					{
						$sql = "SELECT * FROM BD_CLIENTE.dbo.CONTROLE_ACESSO_VIGENCIA WHERE CONTROLE_ACESSO_ID = {$idOrigin} AND DATA_TERMINO IS NULL;";
						$con = $pdo->query($sql); 
						$datas= $con->fetch();

						if ( isset($datas['TAG']) )
						{
							$pdo->query("UPDATE BD_CLIENTE.dbo.CONTROLE_ACESSO_VIGENCIA SET DATA_TERMINO = '{$date}' WHERE CONTROLE_ACESSO_ID = {$idOrigin} AND DATA_TERMINO IS NULL;");
						}
						
						$pdo->query("INSERT INTO BD_CLIENTE.dbo.CONTROLE_ACESSO_VIGENCIA (TAG, DATA_TERMINO, CONTROLE_ACESSO_ID, DATA_INICIO) VALUES ('{$codigo}', null, {$idOrigin}, '{$date}')");
						
					} else {
						$pdo->query("UPDATE BD_CLIENTE.dbo.CONTROLE_ACESSO_VIGENCIA SET DATA_TERMINO = null WHERE CONTROLE_ACESSO_ID = {$idOrigin} AND TAG = '{$codigo}'");
					}

				}

			}

			############ ATUALIZANDO NA VELTRAC ###################
			$sqlIns = "UPDATE BD_CLIENTE.dbo.CONTROLE_ACESSO SET
						NOME = '".$post['name']."', 
						ITINERARIO_ID_IDA = {$itiIda}, 
						ITINERARIO_ID_VOLTA = {$itiVolta},
						CONTROLE_ACESSO_GRUPO_ID = {$grupo}, 
						MATRICULA_FUNCIONAL = '{$matricula}', 
						ATIVO = {$ativo},
						TAG = '{$codigo}',
						centro_custo = '{$centroCusto}'
						WHERE ID = {$idOrigin};";
			$pdo->query($sqlIns); 
		}
	
		############ ATUALIZANDO OS DADOS NO BANCO ##################
		$sql = $this->db->prepare("UPDATE controle_acessos SET ID_ORIGIN = :ID_ORIGIN, NOME = :NOME, ITINERARIO_ID_IDA = :ITINERARIO_ID_IDA, ITINERARIO_ID_VOLTA = :ITINERARIO_ID_VOLTA, CONTROLE_ACESSO_GRUPO_ID = :CONTROLE_ACESSO_GRUPO_ID, MATRICULA_FUNCIONAL = :MATRICULA_FUNCIONAL, ATIVO = :ATIVO, TAG = :TAG, cpf = :cpf, centro_custo = :centro_custo, POLTRONAIDA = :POLTRONAIDA, POLTRONAVOLTA = :POLTRONAVOLTA, funcao = :funcao, descricaoCentro = :descricaoCentro, unidadeID = :unidadeID, usaFret = :usaFret, residencia = :residencia, monitor = :monitor, eyeglasses = :eyeglasses, updated_at = '{$date}' WHERE id = :id");
		$sql->bindValue(":ID_ORIGIN", $idOrigin);
		$sql->bindValue(":NOME", $post['name']);
		$sql->bindValue(":ITINERARIO_ID_IDA", $itiIda);
		$sql->bindValue(":ITINERARIO_ID_VOLTA", $itiVolta);
		$sql->bindValue(":CONTROLE_ACESSO_GRUPO_ID", $grupo);
		$sql->bindValue(":MATRICULA_FUNCIONAL", $matricula);
		$sql->bindValue(":ATIVO", $ativo);
		$sql->bindValue(":TAG", $codigo);
		$sql->bindValue(":cpf", $cpf);
		$sql->bindValue(":centro_custo", $centroCusto);
		$sql->bindValue(":POLTRONAIDA", $polIda);
		$sql->bindValue(":POLTRONAVOLTA", $polVolta);
		$sql->bindValue(":funcao", $funcao);
		$sql->bindValue(":descricaoCentro", $descricaoCC);
		$sql->bindValue(":unidadeID", $unidadeID);
		$sql->bindValue(":usaFret", $usaFret);
		$sql->bindValue(":residencia", $residencia);
		$sql->bindValue(":monitor", $monitor);
		$sql->bindValue(":eyeglasses", $eyeglasses);
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
			$sql3 = $this->db->prepare("UPDATE pontos_controle_acesso SET ponto_referencia_id_embarque = :ponto_referencia_id_embarque, ponto_referencia_id_desembarque = :ponto_referencia_id_desembarque, ponto_referencia_id_resid_embar = :ponto_referencia_id_resid_embar, ponto_referencia_id_resid_desem = :ponto_referencia_id_resid_desem, updated_at = '{$date}' WHERE controle_acesso_id = :controle_acesso_id");
			$sql3->bindValue(":ponto_referencia_id_embarque", $pontoEmbar);
			$sql3->bindValue(":ponto_referencia_id_desembarque", $pontoDesmbar);
			$sql3->bindValue(":ponto_referencia_id_resid_embar", $resEmbar);
			$sql3->bindValue(":ponto_referencia_id_resid_desem", $resDesmbar);
			$sql3->bindValue(":controle_acesso_id", $post['id']);
			$sql3->execute();

		} else { // Insert

			$sql3 = $this->db->prepare("INSERT INTO pontos_controle_acesso (gerar_alerta, controle_acesso_id, ponto_referencia_id_embarque, ponto_referencia_id_desembarque, ponto_referencia_id_resid_embar, ponto_referencia_id_resid_desem, created_at) VALUE (:gerar_alerta, :controle_acesso_id,:ponto_referencia_id_embarque,:ponto_referencia_id_desembarque,:ponto_referencia_id_resid_embar,:ponto_referencia_id_resid_desem, '{$date}')");
			$sql3->bindValue(":gerar_alerta", 0);
			$sql3->bindValue(":controle_acesso_id", $post['id']);
			$sql3->bindValue(":ponto_referencia_id_embarque", $pontoEmbar);
			$sql3->bindValue(":ponto_referencia_id_desembarque", $pontoDesmbar);
			$sql3->bindValue(":ponto_referencia_id_resid_embar", $resEmbar);
			$sql3->bindValue(":ponto_referencia_id_resid_desem", $resDesmbar);
			$sql3->execute();

		}

		$idContAcc = $post['id'];
		############################################################
		######### TRATANDO ATUALIZAR LINHAS ADICIONAIS #############
		############################################################
		if( isset($post['linhaExist']) )
		{

			try {
				
				foreach($post['linhaExist'] AS $lin)
				{
					$sql2 = $this->db->prepare("SELECT * FROM linhasAdicionais WHERE deleted_at is null AND linha_id = {$lin} AND controle_acesso_id = {$idContAcc}");
					$sql2->execute();
					$hasLin = $sql2->fetch(\PDO::FETCH_OBJ);
	
					if (!$hasLin)
					{
						$sql3 = $this->db->prepare("INSERT INTO linhasAdicionais (linha_id, controle_acesso_id, created_at) VALUE (:linha_id, :controle_acesso_id, '{$date}')");
						$sql3->bindValue(":linha_id", $lin);
						$sql3->bindValue(":controle_acesso_id", $idContAcc);
						$sql3->execute();
					} 
				
				}

				#### deletar os que não existir ainda ###
				$exist = implode(',', $post['linhaExist']);

				try {
					$sql2 = $this->db->prepare("UPDATE linhasAdicionais SET deleted_at = '{$date}' WHERE controle_acesso_id = {$idContAcc} AND linha_id NOT IN ($exist)");
					$sql2->execute();
				} catch (\Throwable $th) {
				
				}

			} catch (\Throwable $th) {
				//throw $th;
			}

		}

		############################################################
		############### TRATANDO LINHAS ADICIONAIS #################
		############################################################
		if( isset($post['novaLinha']) )
		{
			
			try {

				foreach($post['novaLinha'] AS $line)
				{
					$sql2 = $this->db->prepare("SELECT * FROM linhasAdicionais WHERE deleted_at is null AND linha_id = {$line} AND controle_acesso_id = {$idContAcc}");
					$sql2->execute();
					$hasLin = $sql2->fetch(\PDO::FETCH_OBJ);
	
					if (!$hasLin)
					{
						$sql3 = $this->db->prepare("INSERT INTO linhasAdicionais (linha_id, controle_acesso_id, created_at) VALUE (:linha_id, :controle_acesso_id, '{$date}')");
						$sql3->bindValue(":linha_id", $line);
						$sql3->bindValue(":controle_acesso_id", $idContAcc);
						$sql3->execute();
					} 
				
				}
				
			} catch (\Throwable $th) {
				//throw $th;
			}

		}

		############################################################
		###################### SALVAR FOTOS ########################
		############################################################

		// $saveUserPics = $this->saveUserPics($idContAcc, $eyeglasses, $post);
		
		// return $saveUserPics;

		return true;
	}

	private function saveUserPics($controle_acesso_id, $eyeglasses, $post, $oldUser = true){

		try{

			if ($oldUser) {
				$pictures = $this->getUserPics($controle_acesso_id);
				$pictures = $pictures ?? [];
			} else {
				$pictures = [];
			}

			$now = date("Y-m-d H:i:s");

			$picsTypes = [
				"pic_front_smiling",
				"pic_front_serious",
				"pic_right_perfil",
				"pic_left_perfil",
			];

			// Tipos adicionais de imagens se tiver óculos
			$eyeglassesPicTypes = [
				"pic_front_smiling_eg",
				"pic_front_serious_eg",
				"pic_right_perfil_eg",
				"pic_left_perfil_eg",
			];

			if ($eyeglasses == 1) {			
				// Adicionando os tipos adicionais de imagens à matriz $picsTypes
				$picsTypes = array_merge($picsTypes, $eyeglassesPicTypes);
			}

			foreach ($pictures AS $picture) {

				if (!empty($post[$picture->position]) && $post[$picture->position] != '0') {

					if ($eyeglasses == 0 && in_array($picture->position, $eyeglassesPicTypes)) {

						// Se não tiver óculos e a posição da imagem estiver em $eyeglassesPicTypes, exclua a imagem
						$deletePic = $this->db->prepare("DELETE FROM controle_acessos_pics WHERE id = {$picture->id}");
						$deletePic->execute();

						$deletePicDs = $this->db->prepare("DELETE FROM controle_acessos_ds WHERE position = '{$picture->position}' AND controle_acesso_id = {$controle_acesso_id}");
						$deletePicDs->execute();

					} else {

						// Atualize a imagem
						$imgUpdate = $this->resizeImage($post[$picture->position], 200, 200, false);
						$updatePic = $this->db->prepare("UPDATE controle_acessos_pics SET img = :img, error = :error, updated_at = '{$now}' WHERE id = :id");
						$updatePic->bindValue(":img", $imgUpdate);
						$updatePic->bindValue(":error", 0);
						$updatePic->bindValue(":id", $picture->id);
						$updatePic->execute();

					}

				}else{

					$deletePic = $this->db->prepare("DELETE FROM controle_acessos_pics WHERE id = {$picture->id}");
					$deletePic->execute();

					$deletePicDs = $this->db->prepare("DELETE FROM controle_acessos_ds WHERE position = '{$picture->position}' AND controle_acesso_id = {$controle_acesso_id}");
					$deletePicDs->execute();

				}

				if (!empty($post[$picture->position.'_ds']) && $post[$picture->position.'_ds'] != '0'){
					$this->checkHasDs($controle_acesso_id, "$picture->position", $post[$picture->position.'_ds']);
				}
				
				$picsTypes = array_diff($picsTypes, array($picture->position));
				
			}

			if(count($picsTypes) > 0){

				foreach($picsTypes AS $typeAdd){

					if (!empty($post[$typeAdd]) && $post[$typeAdd] != '0') {

						$imgAdd = $this->resizeImage($post[$typeAdd], 200, 220, false);

						$insertPic = $this->db->prepare("INSERT INTO controle_acessos_pics (controle_acesso_id, img, position, error, created_at) VALUE (:controle_acesso_id, :img, :position, :error, '{$now}')");

						$insertPic->bindValue(":controle_acesso_id", $controle_acesso_id);
						$insertPic->bindValue(":img", $imgAdd);
						$insertPic->bindValue(":position", $typeAdd);
						$insertPic->bindValue(":error", 0);
						$insertPic->execute();

						if (!empty($post[$typeAdd.'_ds']) && $post[$typeAdd.'_ds'] != '0'){
							$this->checkHasDs($controle_acesso_id, "$typeAdd", $post[$typeAdd.'_ds']);
						}

					}

				}

			}

			return true;

		} catch (\Throwable $th) {

			return false;

		}

	}

	private function checkHasDs($controle_acesso_id, $position, $ds){

		$userDs = $this->db->prepare("SELECT * FROM controle_acessos_ds WHERE controle_acesso_id = {$controle_acesso_id} AND position = '{$position}' AND deleted_at IS NULL LIMIT 1");

		$userDs->execute();

		$now = date("Y-m-d H:i:s");

		if($userDs->rowCount() == 1) {

			$dsFind = $userDs->fetch(\PDO::FETCH_OBJ);
			$updateDs = $this->db->prepare("UPDATE controle_acessos_ds SET ds = :ds, updated_at = '{$now}' WHERE id = :id");
			$updateDs->bindValue(":ds", $ds);
			$updateDs->bindValue(":id", $dsFind->id);
			$updateDs->execute();

		}else{

			$insertDs = $this->db->prepare("INSERT INTO controle_acessos_ds (controle_acesso_id, ds, position, created_at) VALUE (:controle_acesso_id, :ds, :position, '{$now}')");

			$insertDs->bindValue(":controle_acesso_id", $controle_acesso_id);
			$insertDs->bindValue(":ds", $ds);
			$insertDs->bindValue(":position", $position);
			$insertDs->execute();

		}


	}

	private function getUserPics($controle_acesso_id, $useRet = false){

		$userPics = $this->db->prepare("SELECT * FROM controle_acessos_pics WHERE controle_acesso_id = {$controle_acesso_id} AND deleted_at IS NULL");

		if($useRet){

			$ret['pic_front_smiling'] = 0;
			$ret['pic_front_smiling_error'] = 0;
			$ret['pic_front_serious'] = 0;
			$ret['pic_front_serious_error'] = 0;
			$ret['pic_right_perfil'] = 0;
			$ret['pic_right_perfil_error'] = 0;
			$ret['pic_left_perfil'] = 0;
			$ret['pic_left_perfil_error']= 0;
			
			$ret['pic_front_smiling_eg'] = 0;
			$ret['pic_front_smiling_eg_error'] = 0;
			$ret['pic_front_serious_eg'] = 0;
			$ret['pic_front_serious_eg_error'] = 0;
			$ret['pic_right_perfil_eg'] = 0;
			$ret['pic_right_perfil_eg_error'] = 0;
			$ret['pic_left_perfil_eg'] = 0;
			$ret['pic_left_perfil_eg_error'] = 0;

			$ret['pic_front_smiling_hasDs'] = 1;
			$ret['pic_front_serious_hasDs'] = 1;
			$ret['pic_right_perfil_hasDs'] = 1;
			$ret['pic_left_perfil_hasDs'] = 1;

			$ret['pic_front_smiling_eg_hasDs'] = 1;
			$ret['pic_front_serious_eg_hasDs'] = 1;
			$ret['pic_right_perfil_eg_hasDs'] = 1;
			$ret['pic_left_perfil_eg_hasDs'] = 1;

		}	

		try{		

			$userPics->execute();

			if($userPics->rowCount() > 0) {

				$pictures = $userPics->fetchAll(\PDO::FETCH_OBJ);

				if($useRet){

					return array("ret" => $ret, "pictures" => $pictures);

				}

				return $pictures;

			}else{

				if($useRet){

					return array("ret" => $ret, "pictures" => false);

				}

				return false;

			}



		} catch (\Throwable $th) {

			if($useRet){

				return array("ret" => $useRet, "pictures" => false);

			}

			return false;

		}

	}

	public function getLinesExtras($id)
	{
		$sql2 = $this->db->prepare("SELECT linhasAdicionais.*, linhas.PREFIXO, linhas.NOME, itinerarios.SENTIDO AS sentido
									FROM linhasAdicionais 
									INNER JOIN itinerarios ON itinerarios.LINHA_ID = linhasAdicionais.linha_id
									INNER JOIN linhas ON linhas.ID_ORIGIN = linhasAdicionais.linha_id
									WHERE linhasAdicionais.deleted_at is null AND controle_acesso_id = {$id} AND itinerarios.ATIVO = 1");
		$sql2->execute();
		return $sql2->fetchAll(\PDO::FETCH_OBJ);
	}

	public function deleteLineExist($id)
	{
		
		try {
			$sql2 = $this->db->prepare("UPDATE linhasAdicionais SET deleted_at = NOW() WHERE id = {$id}");
			$sql2->execute();
		} catch (\Throwable $th) {
			return false;
		}

		return true;
	}

	public function setInativePax($tag, $groupUser = 0){

		$sql = $this->db->prepare("SELECT * FROM controle_acessos WHERE TRIM(LEADING '0' FROM TAG) = TRIM(LEADING '0' FROM '{$tag}') AND ATIVO = 1 LIMIT 1");

		try {

            $sql->execute();
			
			if($sql->rowCount() == 0) {

				return array('success'=>false, 'msg'=> 'NÃO ENCONTRADO / JÁ ESTÁ INATIVO');

			}

			$ret = $sql->fetch();

			if(!$this->checkUserGroup($ret['id'], $groupUser)){
				
				return array('success'=>false, 'msg'=> 'NÃO É DO GRUPO');

			}
			
			$update = $this->db->prepare("UPDATE controle_acessos SET ATIVO = 0, updated_at = NOW() WHERE id = {$ret['id']}");
			
			try {
				$update->execute();

				if($update->rowCount()){

					//tenta inativar e remover vigência na veltrac se tiver ID_ORIGIN
					if(isset($ret['ID_ORIGIN']) && $ret['ID_ORIGIN'] != 0){

						$tag = $ret['TAG'];
	
						try{

							$pdo = new \PDO ("dblib:host=$this->host:$this->port;dbname=$this->dbName;charset=utf8","$this->user","$this->pass");
							$selVeltrac = "SELECT * FROM BD_CLIENTE.dbo.CONTROLE_ACESSO_VIGENCIA WHERE TAG = '{$tag}' AND CONTROLE_ACESSO_ID = {$ret['ID_ORIGIN']} AND DATA_TERMINO IS NULL;";
							$con 	= $pdo->query($selVeltrac); 
							$data 	= $con->fetch();

							if ( isset($data['TAG']) ){

								$date = date("Y-m-d H:i:s");
								$oldID = $data['CONTROLE_ACESSO_ID'];
								$removeVigencia = $pdo->query("UPDATE BD_CLIENTE.dbo.CONTROLE_ACESSO_VIGENCIA SET DATA_TERMINO = '{$date}' WHERE CONTROLE_ACESSO_ID = {$oldID} AND TAG = '{$tag}'");
								
								if($removeVigencia->rowCount()){
									$pdo->query("UPDATE BD_CLIENTE.dbo.CONTROLE_ACESSO SET ATIVO = 0, TAG = null WHERE ID = {$oldID}");
								}
					
							}
						}

						catch (\Throwable $th) {
							
						}
	
					}

					$deletePic = $this->db->prepare("DELETE FROM controle_acessos_pics WHERE controle_acesso_id = {$id}");
					$deletePic->execute();

					$deleteDs = $this->db->prepare("DELETE FROM controle_acessos_ds WHERE controle_acesso_id = {$id}");
					$deleteDs->execute();

					return array('success'=>true, 'msg'=> 'INATIVADO COM SUCESSO');

				}else{

					return array('success'=>false, 'msg'=> 'ERRO AO INATIVAR');

				}
				
			} catch (\Throwable $th) {

				return array('success'=>false, 'msg'=> 'ERRO AO INATIVAR');

			}
			
				
        } catch (\Throwable $th) {

            return array('success'=>false, 'msg'=> 'NÃO ENCONTRADO');

        }
	}

	public function importPax($req, $subAuto = 0, $groupUser = 0){

		$tag 	= $req->tag;
		$nome	= $req->nome;

		$sql = $this->db->prepare("SELECT * FROM controle_acessos WHERE TRIM(LEADING '0' FROM TAG) = TRIM(LEADING '0' FROM '{$tag}') AND ATIVO = 1 LIMIT 1");

		try{

			$sql->execute();

			if($sql->rowCount() == 1) {

				$pax = $sql->fetch();

				if(!$this->checkUserGroup($pax['id'], $groupUser)){
				
					return array('success'=>false, 'msg'=> 'CÓD. CARTÃO PERTENCE A OUTRO GRUPO');
	
				}

				if(trim($pax['NOME']) == $nome){

					return array('success'=>false, 'msg'=> 'JÁ TEM CADASTRO', 'paxId' => $pax['id']);

				}else{

					$paxNome = (preg_match('!!u', utf8_decode($pax['NOME']))) ? utf8_decode($pax['NOME']) : $pax['NOME'];
					
					if($subAuto == 0){

						return array('success'=>false, 'msg'=> 'CÓD. CARTÃO PERTENCE AO PASSAGEIRO(A): '.$paxNome.'', 'paxId' => $pax['id'], 'oldPaxName' => $paxNome, 'paxIdOrigin' => $pax['ID_ORIGIN'], 'askChange' => true);
					
					}else{

						$req->oldID = $pax['id'];
						$req->paxIdOrigin = $pax['ID_ORIGIN'];
						$req->oldName = $paxNome;

						return $this->insertImportedPax($req, true);

					}
					
				}

			}else{

				return $this->insertImportedPax($req);

			}

		} catch (\Throwable $th) {

			return array('success'=>false, 'msg'=> 'ERRO AO CHECAR CADASTRO');

		}
	}

	public function insertImportedPax($pax, $changePax = false){

		$tag 			= $pax->tag;

		$nome          	= $pax->nome;
		
		$matricula     	= $pax->matricula;
		$end           	= $pax->end;
		$grupoID       	= $pax->grupoID;

		$linhaIdaID    	= $pax->linhaIdaID;
		$itinIda 		= 0;

		$linhaVoltaID  	= $pax->linhaVoltaID;
		$itinVol 		= 0;

		$poltronaIda   	= isset($pax->poltronaIda) ? $pax->poltronaIda : "";
		$poltronaVolta 	= isset($pax->poltronaVolta) ? $pax->poltronaVolta : "";
		
		// $centroCusto	= "$poltronaIda;$poltronaVolta";
		$centroCusto 	= "";

		if(isset($poltronaIda) && $poltronaIda != "") $centroCusto = $poltronaIda;
		if(isset($poltronaVolta) && $poltronaVolta != "") $centroCusto .= ";" . $poltronaVolta;

		$cpf			= null;

		//// //// //// //// //// //// //// //// //// //// //
		//// BUSCA E SALVA ID ITINERÁRIOS IDA E VOLTA \\\\
		//// //// //// //// //// //// //// //// //// //// //
		if($linhaIdaID){

			$sql = $this->db->prepare("SELECT * FROM linhas WHERE id = '{$linhaIdaID}' AND ATIVO = 1 LIMIT 1");
			$sql->execute();
			$linIda = $sql->fetch(PDO::FETCH_OBJ);

			if($linIda)
			{
				$sql = $this->db->prepare("SELECT * FROM itinerarios WHERE LINHA_ID = {$linIda->ID_ORIGIN} AND ATIVO = 1 LIMIT 1");
				$sql->execute();
				$itinID = $sql->fetch(PDO::FETCH_OBJ);
				if($itinID)
					$itinIda = $itinID->ID_ORIGIN;
			}

		}

		if($linhaVoltaID){

			$sql = $this->db->prepare("SELECT * FROM linhas WHERE id = '{$linhaVoltaID}' AND ATIVO = 1  LIMIT 1");
			$sql->execute();
			$linVol = $sql->fetch(PDO::FETCH_OBJ);

			if($linVol)
			{
				$sql = $this->db->prepare("SELECT * FROM itinerarios WHERE LINHA_ID = '{$linVol->ID_ORIGIN}' AND ATIVO = 1 LIMIT 1");
				$sql->execute();
				$itinID = $sql->fetch(PDO::FETCH_OBJ);
				if($itinID)
					$itinVol = $itinID->ID_ORIGIN;
			}

		}
	
		//// //// //// //// //// //// //// //// //// //// //
		////   END BUSCA ID ITINERÁRIOS IDA E VOLTA \\\\
		//// //// //// //// //// //// //// //// //// //// //

		$originID = 0;
		$date = date("Y-m-d H:i:s");

		############ BUSCANDO O ULTIMO ID PARA INCREMENTAR ###################
		try {

            $pdo = new \PDO ("dblib:host=$this->host:$this->port;dbname=$this->dbName;charset=utf8","$this->user","$this->pass");

			$sql = "SELECT ID FROM BD_CLIENTE.dbo.CONTROLE_ACESSO order by ID DESC;";
			$con = $pdo->query($sql); 
			$data= $con->fetch();

			$originID 	= $data['ID'] + 1;

			$sql = "SELECT CODIGO FROM BD_CLIENTE.dbo.RFID WHERE CODIGO = {$tag};";
			$con = $pdo->query($sql); 
			$data= $con->fetch();
			// Se não Tiver Cadastra
			if ( !isset($data['CODIGO']) )
			{
				$pdo->query("INSERT INTO BD_CLIENTE.dbo.RFID (CODIGO, TIPO_ACESSO, TIPO_REPRESENTACAO) VALUES ({$tag},2,0)"); 
			}

			$sql = "SELECT * FROM BD_CLIENTE.dbo.CONTROLE_ACESSO_VIGENCIA WHERE TAG = {$tag} ORDER BY DATA_INICIO DESC;";
			$con = $pdo->query($sql); 
			$datas= $con->fetch();

			// Se não Tiver Cadastra

			if ( isset($datas['TAG']) )
			{
				// Desativa o cadastro antigo e Insere o novo
				$oldID = $datas['CONTROLE_ACESSO_ID'];
				$pdo->query("UPDATE BD_CLIENTE.dbo.CONTROLE_ACESSO_VIGENCIA SET DATA_TERMINO = '{$date}' WHERE CONTROLE_ACESSO_ID = {$oldID} AND TAG = '{$tag}'");

				// Inativa o outro Passageiro 
				$pdo->query("UPDATE BD_CLIENTE.dbo.CONTROLE_ACESSO SET ATIVO = 0, TAG = null WHERE ID = {$oldID} AND TAG = '{$tag}'");

			}

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
				'{$nome}', 
				{$itinIda}, 
				{$itinVol},
				{$grupoID},
				'{$matricula}', 
				{$originID},
				1,
				'{$tag}',
				'{$cpf}',
				'{$centroCusto}'
			)";

			$pdo->query($sqlIns); 
			
			sleep(2);

			$qurt = "INSERT INTO BD_CLIENTE.dbo.CONTROLE_ACESSO_VIGENCIA (TAG, DATA_TERMINO, CONTROLE_ACESSO_ID, DATA_INICIO) VALUES ('{$tag}', null, {$originID}, '{$date}')";
			$pdo->query($qurt);

        } catch (\Throwable $th) {
            
        }

		//se for subistituição, primeiro tenta inativar no CGF antes de adicionar
		if($changePax){

				$oldID = $pax->oldID;
				$paxIdOrigin = $pax->paxIdOrigin;
				$oldName = $pax->oldName;

				// Inativa no CGF 
				$inactiveOld = $this->db->prepare("UPDATE controle_acessos SET ATIVO = 0, updated_at = NOW() WHERE id = {$oldID} AND ID_ORIGIN = {$paxIdOrigin}");

				try{

					$inactiveOld->execute();

				}

				catch (\Throwable $th) {
					return array('success'=>false, 'msg'=> 'ERRO AO INATIVAR '.$oldName);
				}

		}

		$sql = $this->db->prepare("INSERT INTO controle_acessos 
							(   ID_ORIGIN, 
								NOME, 
								ITINERARIO_ID_IDA, 
								ITINERARIO_ID_VOLTA, 
								CONTROLE_ACESSO_GRUPO_ID, 
								MATRICULA_FUNCIONAL, 
								ID_UNICO, 
								ATIVO, 
								TAG, 
								centro_custo, 
								created_at,
								POLTRONAIDA, 
								POLTRONAVOLTA, 
								residencia
							) VALUE (
								:ID_ORIGIN, 
								:NOME, 
								:ITINERARIO_ID_IDA, 
								:ITINERARIO_ID_VOLTA, 
								:CONTROLE_ACESSO_GRUPO_ID, 
								:MATRICULA_FUNCIONAL, 
								:ID_UNICO, 
								:ATIVO, 
								:TAG, 
								:centro_custo, 
								'{$date}',
								:POLTRONAIDA, 
								:POLTRONAVOLTA, 
								:residencia
							)");

			$sql->bindValue(":ID_ORIGIN", $originID);
			$sql->bindValue(":NOME", $nome);
			$sql->bindValue(":ITINERARIO_ID_IDA", $itinIda);
			$sql->bindValue(":ITINERARIO_ID_VOLTA", $itinVol);
			$sql->bindValue(":CONTROLE_ACESSO_GRUPO_ID", $grupoID);
			$sql->bindValue(":MATRICULA_FUNCIONAL", $matricula);
			$sql->bindValue(":ID_UNICO", $originID);
			$sql->bindValue(":ATIVO", 1);
			$sql->bindValue(":TAG", $tag);					
			$sql->bindValue(":centro_custo", $centroCusto);
			$sql->bindValue(":POLTRONAIDA", $poltronaIda);
			$sql->bindValue(":POLTRONAVOLTA", $poltronaVolta);
			$sql->bindValue(":residencia", $end);

		try{

			$sql->execute();
			$idNewPax = $this->db->lastInsertId();

			if($changePax){

				if(isset($_SESSION['cType']) && $_SESSION['cType'] != 1){
					return array('success'=>true, 'msg'=> 'CADASTRADO COM SUCESSO - (Substituiu: '.$oldName.')', 'paxId' => $idNewPax);
				}else{
					return array('success'=>true, 'msg'=> 'CADASTRADO COM SUCESSO - (Substituiu: '.$oldName.')');
				}

			}else{

				if(isset($_SESSION['cType']) && $_SESSION['cType'] != 1){
					return array('success'=>true, 'msg'=> 'CADASTRADO COM SUCESSO', 'paxId' => $idNewPax);
				}else{
					return array('success'=>true, 'msg'=> 'CADASTRADO COM SUCESSO');
				}

			}
			

		} catch (\Throwable $th) {

			return array('success'=>false, 'msg'=> 'ERRO AO CADASTRAR');

		}

	}

	public function inactiveImportPax($paxs){
		try {
			$pdo = new \PDO ("dblib:host=$this->host:$this->port;dbname=$this->dbName;charset=utf8","$this->user","$this->pass");
		} catch (\Throwable $th) {
				$error =array('error'=>true,'msg'=>'Ocorreu um erro ao tentar conectar ao Banco de Dados, tente novamente.');
				return $error;
		}

		$arr = array();
		$nome = $paxs[0];
		$codCart = $paxs[1];
		$matricula = $paxs[2];
	}

	public function insertImportPax( $paxs, $idGrp = 0 )
	{
		
		try {
            $pdo = new \PDO ("dblib:host=$this->host:$this->port;dbname=$this->dbName;charset=utf8","$this->user","$this->pass");
        } catch (\Throwable $th) {
            $error =array('error'=>true,'msg'=>'Ocorreu um erro ao tentar conectar ao Banco de Dados, tente novamente.');
            return $error;
        }

		$arr 		= array();
		$nome 		= $paxs[0];
		$grupo 	    = trim($paxs[1]);
		$codCart 	= isset($paxs[2]) ? $paxs[2] : 0;
		$matricula  = isset($paxs[3]) ? $paxs[3] : null;
		$prefIda 	= isset($paxs[4]) ? trim($paxs[4]) : null;
		//$descLnIda 	= isset($paxs[5]) ? trim($paxs[5]) : null;
		$polIda 	= isset($paxs[5]) ? $paxs[5] : "";
		$prefVol	= isset($paxs[6]) ? trim($paxs[6]) : null;
		//$descLnVol 	= isset($paxs[8]) ? trim($paxs[8]) : null;
		$polVol 	= isset($paxs[7]) ? $paxs[7] : "";
		$endResi 	= isset($paxs[8]) ? $paxs[8] : "";
		$itinIda 	= null;
		$itinVol 	= null;
		$centroCusto= "$polIda;$polVol";

		//// //// //// //// //// //// //// //// //// //// //
		//// BUSCA E SALVA O ID ITINERÁRIOS IDA E VOLTA \\\\
		//// //// //// //// //// //// //// //// //// //// //
		$sql = $this->db->prepare("SELECT * FROM linhas WHERE PREFIXO = '{$prefIda}' AND ATIVO = 1 LIMIT 1;");
		$sql->execute();
		$linIda = $sql->fetch(PDO::FETCH_OBJ);

		if($linIda)
		{
			$sql = $this->db->prepare("SELECT * FROM itinerarios WHERE LINHA_ID = {$linIda->ID_ORIGIN} AND ATIVO = 1 LIMIT 1;");
			$sql->execute();
			$itinID = $sql->fetch(PDO::FETCH_OBJ);
			if($itinID)
				$itinIda = $itinID->ID_ORIGIN;
		}
	
		$sql = $this->db->prepare("SELECT * FROM linhas WHERE PREFIXO = '{$prefVol}' AND ATIVO = 1  LIMIT 1;");
		$sql->execute();
		$linVol = $sql->fetch(PDO::FETCH_OBJ);

		if($linVol)
		{
			$sql = $this->db->prepare("SELECT * FROM itinerarios WHERE LINHA_ID = '{$linVol->ID_ORIGIN}' AND ATIVO = 1 LIMIT 1;");
			$sql->execute();
			$itinID = $sql->fetch(PDO::FETCH_OBJ);
			if($itinID)
				$itinVol = $itinID->ID_ORIGIN;
		}

		//// //// //// //// //// //// //// //// //// //// //
		////   END BUSCA  dO ID ITINERÁRIOS IDA E VOLTA \\\\
		//// //// //// //// //// //// //// //// //// //// //
		$originID  = 0; 
		$grupoID   = null;

		////// VERIFICA SE EXISTE O GRUPO DE ACESSO \\\\\\
		$sql = $this->db->prepare("SELECT * FROM acesso_grupos WHERE NOME = '{$grupo}'");
		$sql->execute();
		$hgr = $sql->fetch(PDO::FETCH_OBJ);

		if ($hgr)
		{
			// CASO TENHA O GRUPO COM O MESMO NOME \\
			$nameGroup = $grupo;

		} else {

			$idGrf  = $idGrp > 0 ? $idGrp : $_SESSION['groupUserID'];
			$sql = $this->db->prepare("SELECT * FROM grupo_linhas WHERE id = {$idGrf}");
			$sql->execute();
			$grp = $sql->fetch(PDO::FETCH_OBJ);

			if ($grp)
			{
				$nam = explode("-", $grp->NOME);
				
				if ( strtolower(trim($grupo)) != strtolower(trim($nam[0])) )
					$nameGroup = trim($nam[0]) . "-" . $grupo;
				else 
					$nameGroup = trim($nam[0]);
	
			} else {
				$nameGroup = $grupo;
			}

		}
		
		$sql = "SELECT * FROM BD_CLIENTE.dbo.CONTROLE_ACESSO_GRUPO WHERE NOME = '{$nameGroup}';";
		$con = $pdo->query($sql); 
		$cag = $con->fetch();

		if ( !isset($cag['NOME']) )
		{
			$pdo->query("INSERT INTO BD_CLIENTE.dbo.CONTROLE_ACESSO_GRUPO (NOME) VALUES ('{$nameGroup}')");
			$sql = "SELECT * FROM BD_CLIENTE.dbo.CONTROLE_ACESSO_GRUPO WHERE NOME = '{$nameGroup}';";
			$con = $pdo->query($sql); 
			$cagr= $con->fetch();
			$grupoID   = $cagr['ID'];

			/// INSERINDO NO BANCO LOCAL \\\
			$sql = $this->db->prepare("INSERT INTO acesso_grupos (ID_ORIGIN, NOME, created_at) VALUE (:ID_ORIGIN,:NOME, now())");
			$sql->bindValue(":ID_ORIGIN", $grupoID);
			$sql->bindValue(":NOME", $nameGroup);
			$sql->execute();

			$igNewGr = $this->db->lastInsertId();

			########################################################################################
			########### DANDO AUTORIZAÇÃO AO USER LOGADO PARA ESSE NOVO GRUPO DE ACESSO ############
			########################################################################################
			$sql = $this->db->prepare("INSERT INTO usuario_grupos SET usuario_id = :usuario_id, grupo_id = :grupo_id, created_at = NOW()");
			$sql->bindValue(":usuario_id", $_SESSION['cLogin']);
			$sql->bindValue(":grupo_id", $igNewGr);
			$sql->execute();

			########################################################################################
			################# DANDO AUTORIZAÇÃO AO USER LOGADO PARA LINK TOTEM #####################
			########################################################################################
			$idGrf  = $idGrp > 0 ? $idGrp : $_SESSION['groupUserID'];
			$sql 	= $this->db->prepare("SELECT * FROM grupo_linhas WHERE id = {$idGrf}");
			$sql->execute();
			$grp 	= $sql->fetch(PDO::FETCH_OBJ);

			if ($grp)
			{
				$grpsUsernews = $grp->GRUPOSUSER ? ($grp->GRUPOSUSER . ',' . $igNewGr) : $igNewGr;

				$sql = $this->db->prepare("UPDATE grupo_linhas SET GRUPOSUSER = :GRUPOSUSER, updated_at = NOW() WHERE id = :id");
                $sql->bindValue(":GRUPOSUSER", $grpsUsernews);
                $sql->bindValue(":id", $idGrf);
                $sql->execute();

			}

		} else {
			$grupoID   = $cag['ID'];
		}
	
		if ( $grupoID == null )
			return array('success'=>false, 'msg'=> 'Grupo não encontrato e não foi possível cadastrar.');

		//////////////////////////////////////////////////////////////////////////////////
		///// Salvar primeiro na veltrac para preencher o id se for usar fretamento \\\\\\
		//////////////////////////////////////////////////////////////////////////////////
		$hasPax = null;

		if ($codCart && $codCart > 0)
		{
		
			$sql 		= "SELECT ID, NOME, CONTROLE_ACESSO_GRUPO_ID FROM BD_CLIENTE.dbo.CONTROLE_ACESSO WHERE TAG='{$codCart}' AND ATIVO = 1 order by ID DESC;";
			$con 		= $pdo->query($sql); 
			$hasPax		= $con->fetch();
			$sql 		= "SELECT ID FROM BD_CLIENTE.dbo.CONTROLE_ACESSO order by ID DESC;";
			$con 		= $pdo->query($sql); 
			$data		= $con->fetch();
			$originID 	= $data['ID'] + 1;

			############ SALVA NO BANCO DA VELTRAC E BUSCA O ID ###################
			############ CHECK SE EXISTE, SE NÃO EXISTIR CADASTRA #################
			$sql = "SELECT CODIGO FROM BD_CLIENTE.dbo.RFID WHERE CODIGO = {$codCart};";
			$con = $pdo->query($sql); 
			$data= $con->fetch();
			
			if ( !isset($data['CODIGO']) )
				$pdo->query("INSERT INTO BD_CLIENTE.dbo.RFID (CODIGO, TIPO_ACESSO,TIPO_REPRESENTACAO) VALUES ({$codCart},2,0)"); 

			/**
			 * Condições para inserir novo Passageiro
			 * Se não existir o passageiro na Veltrac
			 * Se o Nome do Passageiro da Veltrac for diferente do nome da Planilha mas o código do cartão for o mesmo
			 */
			if ( !$hasPax || $hasPax['NOME'] != $nome || $hasPax['CONTROLE_ACESSO_GRUPO_ID'] != $grupoID )
			{
				// Se não tiver passageiro ou se tiver mas for diferente do novo que vai usar a tag, inativa e zera a tag \\
				if ( isset( $hasPax['NOME'] ) && ( $hasPax['NOME'] != $nome || $hasPax['CONTROLE_ACESSO_GRUPO_ID'] != $grupoID ) )
				{
					$idOldPax = $hasPax['ID'];
					// Inativa o outro Passageiro e tira a TAG
					$pdo->query("UPDATE BD_CLIENTE.dbo.CONTROLE_ACESSO SET ATIVO = 0, TAG = null WHERE ID = {$idOldPax}");
				}

				sleep(1);

				$itinIda = $itinIda ? $itinIda : "NULL";
				$itinVol = $itinVol ? $itinVol : "NULL";

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
									'{$nome}', 
									{$itinIda}, 
									{$itinVol},
									{$grupoID},
									'{$matricula}', 
									{$originID},
									1,
									'{$codCart}',
									'',
									'{$centroCusto}'
							)";
					
				$pdo->query($sqlIns); 

				sleep(1);

				$date = date("Y-m-d H:i:s");
				
				############################## VERIFICA SE A TAG NÃO ESTÁ EM USO ######################
				$sql = "SELECT * FROM BD_CLIENTE.dbo.CONTROLE_ACESSO_VIGENCIA WHERE TAG = '{$codCart}' AND DATA_TERMINO IS NULL AND CONTROLE_ACESSO_ID <> {$originID} ORDER BY DATA_INICIO DESC;";

				$con = $pdo->query($sql); 
				$datas= $con->fetch();

				if ( isset($datas['TAG']) )
				{
					$oldID = $datas['CONTROLE_ACESSO_ID'];
					$pdo->query("UPDATE BD_CLIENTE.dbo.CONTROLE_ACESSO_VIGENCIA SET DATA_TERMINO = '{$date}' WHERE CONTROLE_ACESSO_ID = {$oldID} AND TAG = '{$codCart}'");

				}

				// Inativa no CGF \\
				$sql2 = $this->db->prepare("UPDATE controle_acessos SET ATIVO = 0, TAG = null WHERE TAG = '{$codCart}'");
				$sql2->execute();

				############################## VERIFICA SE A TAG JÁ ESTÁ COM ELE ######################
				$sql = "SELECT * FROM BD_CLIENTE.dbo.CONTROLE_ACESSO_VIGENCIA WHERE TAG = '{$codCart}' AND DATA_TERMINO IS NULL AND CONTROLE_ACESSO_ID = {$originID} ORDER BY DATA_INICIO DESC;";
				$con = $pdo->query($sql); 
				$datas= $con->fetch();

				if ( !isset($datas['TAG']) )
				{
					
					$qurt = "INSERT INTO BD_CLIENTE.dbo.CONTROLE_ACESSO_VIGENCIA (TAG, DATA_TERMINO, CONTROLE_ACESSO_ID, DATA_INICIO) VALUES ('{$codCart}', null, {$originID}, '{$date}')";
					$pdo->query($qurt);

				}

			} else if ($hasPax)
			{
				/**
				 * Se tiver o pax e for o mesmo nome e controle de acesso, só dá update dos dados
				 */
				$originID 	= $hasPax['ID'];
			
				try {
					$pdo->query("UPDATE BD_CLIENTE.dbo.CONTROLE_ACESSO SET 
									ITINERARIO_ID_IDA = {$itinIda}, 
									ITINERARIO_ID_VOLTA = {$itinVol},
									MATRICULA_FUNCIONAL = '{$matricula}'
									WHERE ID = {$originID}");
				} catch (\Throwable $th) {
					//throw $th;
				}

			}

		}

		$sql = $this->db->prepare("SELECT * FROM controle_acessos WHERE ID_UNICO = {$originID} LIMIT 1;");
		$sql->execute();
		$hasPaxIn = $sql->fetch(PDO::FETCH_OBJ);

		if ( !$hasPaxIn )
		{
			$sql = $this->db->prepare("INSERT INTO controle_acessos 
							(   ID_ORIGIN, 
								NOME, 
								ITINERARIO_ID_IDA, 
								ITINERARIO_ID_VOLTA, 
								CONTROLE_ACESSO_GRUPO_ID, 
								MATRICULA_FUNCIONAL, 
								ID_UNICO, 
								ATIVO, 
								TAG, 
								cpf, 
								centro_custo, 
								created_at,
								POLTRONAIDA, 
								POLTRONAVOLTA, 
								residencia
							) VALUE (
								:ID_ORIGIN, 
								:NOME, 
								:ITINERARIO_ID_IDA, 
								:ITINERARIO_ID_VOLTA, 
								:CONTROLE_ACESSO_GRUPO_ID, 
								:MATRICULA_FUNCIONAL, 
								:ID_UNICO, 
								:ATIVO, 
								:TAG, 
								:cpf, 
								:centro_custo, 
								now(),
								:POLTRONAIDA, 
								:POLTRONAVOLTA, 
								:residencia
							)");

			$sql->bindValue(":ID_ORIGIN", $originID);
			$sql->bindValue(":NOME", $nome);
			$sql->bindValue(":ITINERARIO_ID_IDA", $itinIda);
			$sql->bindValue(":ITINERARIO_ID_VOLTA", $itinVol);
			$sql->bindValue(":CONTROLE_ACESSO_GRUPO_ID", $grupoID);
			$sql->bindValue(":MATRICULA_FUNCIONAL", $matricula);
			$sql->bindValue(":ID_UNICO", $originID);
			$sql->bindValue(":ATIVO", 1);
			$sql->bindValue(":TAG", $codCart);
			$sql->bindValue(":cpf", null);						
			$sql->bindValue(":centro_custo", $centroCusto);
			$sql->bindValue(":POLTRONAIDA", $polIda);
			$sql->bindValue(":POLTRONAVOLTA", $polVol);
			$sql->bindValue(":residencia", $endResi);
			$sql->execute();

		} else if ( $hasPax['NOME'] != $nome || $hasPax['CONTROLE_ACESSO_GRUPO_ID'] != $grupoID )
		{
			/**
			 * Se tiver o pax no CGF mas o nome ou controle de acesso grupo for diferente desativa no CGF e cadastro o novo
			 */
			// Inativa no CGF \\
			$sql2 = $this->db->prepare("UPDATE controle_acessos SET ATIVO = 0, TAG = null AND ATIVO = 1 WHERE TAG = '{$codCart}'");
			$sql2->execute();

			$sql = $this->db->prepare("INSERT INTO controle_acessos 
							(   ID_ORIGIN, 
								NOME, 
								ITINERARIO_ID_IDA, 
								ITINERARIO_ID_VOLTA, 
								CONTROLE_ACESSO_GRUPO_ID, 
								MATRICULA_FUNCIONAL, 
								ID_UNICO, 
								ATIVO, 
								TAG, 
								cpf, 
								centro_custo, 
								created_at,
								POLTRONAIDA, 
								POLTRONAVOLTA, 
								residencia
							) VALUE (
								:ID_ORIGIN, 
								:NOME, 
								:ITINERARIO_ID_IDA, 
								:ITINERARIO_ID_VOLTA, 
								:CONTROLE_ACESSO_GRUPO_ID, 
								:MATRICULA_FUNCIONAL, 
								:ID_UNICO, 
								:ATIVO, 
								:TAG, 
								:cpf, 
								:centro_custo, 
								now(),
								:POLTRONAIDA, 
								:POLTRONAVOLTA, 
								:residencia
							)");

			$sql->bindValue(":ID_ORIGIN", $originID);
			$sql->bindValue(":NOME", $nome);
			$sql->bindValue(":ITINERARIO_ID_IDA", $itinIda);
			$sql->bindValue(":ITINERARIO_ID_VOLTA", $itinVol);
			$sql->bindValue(":CONTROLE_ACESSO_GRUPO_ID", $grupoID);
			$sql->bindValue(":MATRICULA_FUNCIONAL", $matricula);
			$sql->bindValue(":ID_UNICO", $originID);
			$sql->bindValue(":ATIVO", 1);
			$sql->bindValue(":TAG", $codCart);
			$sql->bindValue(":cpf", null);						
			$sql->bindValue(":centro_custo", $centroCusto);
			$sql->bindValue(":POLTRONAIDA", $polIda);
			$sql->bindValue(":POLTRONAVOLTA", $polVol);
			$sql->bindValue(":residencia", $endResi);
			$sql->execute();
		} else {
			/**
			 * Se tiver o pax e for o mesmo nome e controle de acesso, só dá update dos dados
			 */
			try {
				$sql2 = $this->db->prepare("UPDATE controle_acessos SET 
											ITINERARIO_ID_IDA	= '{$itinIda}',
											ITINERARIO_ID_VOLTA	= '{$itinVol}',
											MATRICULA_FUNCIONAL	= '{$matricula}',
											centro_custo		= '{$centroCusto}',
											updated_at			= NOW(),
											POLTRONAIDA			= '{$polIda}',
											POLTRONAVOLTA		= '{$polVol}',
											residencia 			= '{$endResi}'
										WHERE TAG = '{$codCart}' AND ATIVO = 1");
			$sql2->execute();
			} catch (\Throwable $th) {
				//throw $th;
			}

		}

		return array('success'=>true, 'codigo' => $codCart, 'group'=> $grupoID );
	}

	public function inativeWithNotHas( $ids )
	{

		foreach ( $ids AS $gr => $i )
		{
			$ins = implode("," , $i);
			$sql = $this->db->prepare("UPDATE controle_acessos SET ATIVO = 0 WHERE CONTROLE_ACESSO_GRUPO_ID = {$gr} AND deleted_at is null AND ATIVO = 1 AND (MATRICULA_FUNCIONAL NOT IN ($ins) OR MATRICULA_FUNCIONAL is null )");
			$sql->execute();
		}

		return true;
	}

	public function saveNewGroup($post)
	{

		////// VERIFICA SE EXISTE O GRUPO DE ACESSO \\\\\\
		$check = $post['groupNew'];
		$sql   = $this->db->prepare("SELECT * FROM acesso_grupos WHERE NOME = '{$check}'");
		$sql->execute();
		$hgr = $sql->fetch(PDO::FETCH_OBJ);

		if ($hgr)
		{
			// CASO TENHA O GRUPO COM O MESMO NOME RETORNA COM O ERRO \\
			$arr['success'] = false;
			$arr['msg'] 	= "Grupo de Acesso já possui cadastro com esse nome!";
			return $arr;
		} 

		$id = $post['groupUserID'];
		$sql = $this->db->prepare("SELECT * FROM grupo_linhas WHERE id = {$id}");
		$sql->execute();
		$grp = $sql->fetch(PDO::FETCH_OBJ);
		$arr = array();

		if ($grp)
		{
			$nam = explode("-", $grp->NOME);

			if ( strtolower(trim($post['groupNew'])) != strtolower(trim($nam[0])) )
				$nameGroup = trim($nam[0]) . "-" . $post['groupNew'];
			else 
				$nameGroup = trim($nam[0]);

			///// BUSCAR SE TEM IGUAL REGISTRO COM ESSE NOME \\\\\
			$sql = $this->db->prepare("SELECT * FROM acesso_grupos WHERE NOME = '{$nameGroup}'");
			$sql->execute();
			$grpNew = $sql->fetch(PDO::FETCH_OBJ);

			if (!$grpNew)
			{ // SE NÃO TIVER CADASTRO, CRIA UM \\\

				try {
					$pdo = new \PDO ("dblib:host=$this->host:$this->port;dbname=$this->dbName;charset=utf8","$this->user","$this->pass");
				} catch (\Throwable $th) {
					$arr['success'] = false;
					$arr['msg'] 	= "Erro ao acessar o banco da Veltrac!";
					exit;
				}

				$sql = "SELECT * FROM BD_CLIENTE.dbo.CONTROLE_ACESSO_GRUPO WHERE NOME = '{$nameGroup}'";
				$con = $pdo->query($sql); 
				$cag= $con->fetch();

				if ( !isset($cag['NOME']) )
				{

					$pdo->query("INSERT INTO BD_CLIENTE.dbo.CONTROLE_ACESSO_GRUPO (NOME) VALUES ('{$nameGroup}')");
					$sql = "SELECT * FROM BD_CLIENTE.dbo.CONTROLE_ACESSO_GRUPO WHERE NOME = '{$nameGroup}'";
					$con = $pdo->query($sql); 
					$cagr= $con->fetch();
					$grupoID   = $cagr['ID'];

					/// INSERINDO NO BANCO LOCAL \\\
					$sql = $this->db->prepare("INSERT INTO acesso_grupos (ID_ORIGIN, NOME, created_at) VALUE (:ID_ORIGIN,:NOME, now())");
					$sql->bindValue(":ID_ORIGIN", $grupoID);
					$sql->bindValue(":NOME", $nameGroup);
					$sql->execute();

					$igNewGr = $this->db->lastInsertId();

					$arr['success'] = true;
					$arr['msg'] 	= "Novo Grupo de acesso criado com sucesso!";
					$arr['id'] 		= $igNewGr;
					$arr['nome'] 	= $nameGroup;

					########################################################################################
					########### DANDO AUTORIZAÇÃO AO USER LOGADO PARA ESSE NOVO GRUPO DE ACESSO ############
					########################################################################################
					$sql = $this->db->prepare("INSERT INTO usuario_grupos SET usuario_id = :usuario_id, grupo_id = :grupo_id, created_at = NOW()");
					$sql->bindValue(":usuario_id", $_SESSION['cLogin']);
					$sql->bindValue(":grupo_id", $igNewGr);
					$sql->execute();

					########################################################################################
					################# DANDO AUTORIZAÇÃO AO USER LOGADO PARA LINK TOTEM #####################
					########################################################################################
					$idGrf  = $post['groupUserID'];
					$sql 	= $this->db->prepare("SELECT * FROM grupo_linhas WHERE id = {$idGrf}");
					$sql->execute();
					$grp 	= $sql->fetch(PDO::FETCH_OBJ);

					if ($grp)
					{
						$grpsUsernews = $grp->GRUPOSUSER ? ($grp->GRUPOSUSER . ',' . $igNewGr) : $igNewGr;

						$sql = $this->db->prepare("UPDATE grupo_linhas SET GRUPOSUSER = :GRUPOSUSER, updated_at = NOW() WHERE id = :id");
						$sql->bindValue(":GRUPOSUSER", $grpsUsernews);
						$sql->bindValue(":id", $idGrf);
						$sql->execute();

					}

				} else {

					$arr['success'] = false;
					$arr['msg'] 	= "Grupo de Acesso já possui cadastro com esse nome!";
				}

			} else {
				$arr['success'] = false;
				$arr['msg'] 	= "Grupo de Acesso já possui cadastro com esse nome!";
			}

		} else {
			$arr['success'] = false;
			$arr['msg'] 	= "Grupo de Usuário não encontrado!";
		}

		return $arr;
	}

	public function existTag($post)
	{

		$cod = $post['cod'];
		$id = $post['id'];
		
		$retornoArr = array(
			"status" => false,
			"pax" => false 
		);

		$w = " AND TRIM(LEADING '0' FROM TAG) = TRIM(LEADING '0' FROM '{$cod}')";

		if($id != 0){
			$w .= " AND id <> {$id}";
		}

		try {

			$sql = $this->db->prepare("SELECT * FROM controle_acessos WHERE ATIVO = 1 {$w} LIMIT 1");
			$sql->execute();

			$retornoArr['status'] = true;			

			$pax = $sql->fetch();

			if($pax){

				if($this->checkUserGroup($pax['id'])){
				
					$retornoArr['pax'] = $pax;
	
				}

			}else{

				$retornoArr['pax'] = true;

			}

		} catch (\Throwable $th) {
		
		}		

		return $retornoArr;
	}

	private function toBlobUserPic($base64Data)
    {

		try{

			$base64Data = trim($base64Data);
			$base64Data = preg_replace('#^data:image/\w+;base64,#i', '', $base64Data);
			$blobData 	= base64_decode($base64Data);
			return $blobData;

		} catch (\Throwable $th) {
			return false;
		}

    }

	private function toBase64UserPic($blobData)
	{
		try {
			
			$base64Data = base64_encode($blobData);

			if ($base64Data != false) {
				$result = 'data:image/png;base64,' . $base64Data;
				return $result;
			}

			return false;

		} catch (\Throwable $th) {
			return false;
		}
	}

	private function resizeImage($blobImage, $nova_largura = 100, $nova_altura = 120, $view = true) {

		if (strpos($blobImage, 'data:image/png;base64,') === 0) {
			$blobImage = substr($blobImage, strlen('data:image/png;base64,'));
			$blobImage = base64_decode($blobImage);
		}
	
		$imagem_original = imagecreatefromstring($blobImage);
	
		$largura_original = imagesx($imagem_original);
		$altura_original = imagesy($imagem_original);
	
		$ratio = $largura_original / $altura_original;
	
		if ($nova_largura / $nova_altura > $ratio) {
			$nova_largura = $nova_altura * $ratio;
		} else {
			$nova_altura = $nova_largura / $ratio;
		}
	
		$nova_imagem = imagecreatetruecolor($nova_largura, $nova_altura);
	
		imagecopyresampled($nova_imagem, $imagem_original, 0, 0, 0, 0, $nova_largura, $nova_altura, $largura_original, $altura_original);
	
		if ($view) {
			
			ob_start();
			imagejpeg($nova_imagem);
			$imagem_base64 = base64_encode(ob_get_clean());
	
			
			imagedestroy($nova_imagem);
			imagedestroy($imagem_original);
	
			return 'data:image/png;base64,' . $imagem_base64;

		} else {
			
			ob_start();
			imagejpeg($nova_imagem);
			$imagem_blob = ob_get_clean();
	
			
			imagedestroy($nova_imagem);
			imagedestroy($imagem_original);
	
			return $imagem_blob;
		}
	}
	
	public function checkUserGroup($id, $groupUser = 0){


		$user 				= new Usuarios();
        $grupos 	        = $groupUser > 0 ? $this->getGroupMultiUser($groupUser) : $user->acessoGrupo();

        $grUs               = array();

		$unidadeID = $groupUser > 0 ? $groupUser : $this->getIdOriginGroupLine();

        if(count($grupos) > 0)
        {
            foreach ($grupos as $gr)
            {
                $grUs[] = $gr['ID_ORIGIN'];
            }
        }
        
        $grIn = count($grUs) > 0 ? implode(",", $grUs) : 0; 

		$sql = "SELECT CONTROLE_ACESSO_GRUPO_ID, unidadeID FROM controle_acessos
            WHERE id = {$id} AND
			(CONTROLE_ACESSO_GRUPO_ID <> 0 AND CONTROLE_ACESSO_GRUPO_ID IN ({$grIn})
			OR
			CONTROLE_ACESSO_GRUPO_ID = 0 AND unidadeID = {$unidadeID})
			AND deleted_at IS NULL
			LIMIT 1";

        $sql = $this->db->prepare($sql);
        $sql->execute();

		if($sql->rowCount() == 1) {
			return true;
		}else{
			return false;
		}

	}

	public function getGroupMultiUser($groupUser){

		$sql = $this->db->prepare("SELECT id FROM users WHERE groupUserID = {$groupUser} AND deleted_at is null");
		$sql->execute();
		$users = $sql->fetchAll();

		$usersIds = array();

		if(count($users) > 0)
        {
            foreach ($users as $user)
            {
                $usersIds[] = $user['id'];
            }
        }

		$usIn = count($usersIds) > 0 ? implode(",", $usersIds) : 0; 

		$sql = $this->db->prepare("SELECT DISTINCT ID_ORIGIN, NOME FROM acesso_grupos 
				WHERE id IN (
                SELECT grupo_id FROM usuario_grupos WHERE usuario_id IN ({$usIn}) AND deleted_at is null
            ) ORDER BY NOME");
		$sql->execute();
		$array = $sql->fetchAll();

		return $array;

	}

	public function getPaxToClean($groupUser){

		$retorno = array('success'=>false, 'msg' => 'Erro ao carregar base de passageiros para o Grupo');

		$grupos = $this->getGroupMultiUser($groupUser);

        $grUs 	= array();

        if(count($grupos) > 0)
        {
            foreach ($grupos as $gr)
            {
                $grUs[] = $gr['ID_ORIGIN'];
            }
        }
        
        $grIn = count($grUs) > 0 ? implode(",", $grUs) : 0; 

		$sql = "SELECT DISTINCT id, ID_ORIGIN, NOME, TAG FROM controle_acessos
            WHERE 
			(CONTROLE_ACESSO_GRUPO_ID <> 0 AND CONTROLE_ACESSO_GRUPO_ID IN ({$grIn})
			OR
			CONTROLE_ACESSO_GRUPO_ID = 0 AND unidadeID = {$groupUser})
			AND ATIVO = 1 AND deleted_at IS NULL";

		
		try {

			$sql = $this->db->prepare($sql);
			$sql->execute();

			if($sql->rowCount() > 0) {

				$retorno = array('success'=>true, 'pax' => $sql->fetchAll());

			}else{

				$retorno = array('success'=>false, 'msg' => 'Nenhum passageiro encontrado para o Grupo');

			}

			

		} catch (\Throwable $th) {
			$retorno = array('success'=>false, 'msg' => 'Erro ao carregar base de passageiros para o Grupo');
		}	


		return $retorno;

	}

	public function erasePax($id, $idOrigin, $tag){

		$update = $this->db->prepare("UPDATE controle_acessos SET ATIVO = 0, updated_at = NOW(), deleted_at = NOW() WHERE id = {$id}");
			
			try {
				$update->execute();

				if($update->rowCount()){

					//tenta inativar e remover vigência na veltrac se tiver ID_ORIGIN
					if(isset($idOrigin) && $idOrigin != 0){
	
						try{

							$pdo = new \PDO ("dblib:host=$this->host:$this->port;dbname=$this->dbName;charset=utf8","$this->user","$this->pass");
							$selVeltrac = "SELECT * FROM BD_CLIENTE.dbo.CONTROLE_ACESSO_VIGENCIA WHERE TAG = '{$tag}' AND CONTROLE_ACESSO_ID = {$idOrigin} AND DATA_TERMINO IS NULL;";
							$con 	= $pdo->query($selVeltrac); 
							$data 	= $con->fetch();

							if ( isset($data['TAG']) ){

								$date = date("Y-m-d H:i:s");
								$oldID = $data['CONTROLE_ACESSO_ID'];
								$removeVigencia = $pdo->query("UPDATE BD_CLIENTE.dbo.CONTROLE_ACESSO_VIGENCIA SET DATA_TERMINO = '{$date}' WHERE CONTROLE_ACESSO_ID = {$oldID} AND TAG = '{$tag}'");
								
								if($removeVigencia->rowCount()){
									$pdo->query("UPDATE BD_CLIENTE.dbo.CONTROLE_ACESSO SET ATIVO = 0, TAG = null WHERE ID = {$oldID}");
								}
					
							}
						}

						catch (\Throwable $th) {
							
						}
	
					}

					$deletePic = $this->db->prepare("DELETE FROM controle_acessos_pics WHERE controle_acesso_id = {$id}");
					$deletePic->execute();

					$deleteDs = $this->db->prepare("DELETE FROM controle_acessos_ds WHERE controle_acesso_id = {$id}");
					$deleteDs->execute();

					return array('success'=>true, 'msg'=> 'INATIVADO COM SUCESSO');

				}else{

					return array('success'=>false, 'msg'=> 'ERRO AO INATIVAR');

				}
				
			} catch (\Throwable $th) {

				return array('success'=>false, 'msg'=> 'ERRO AO INATIVAR');

			}
	}


	public function removeUserPhoto($post)
	{
		$controle_acesso_id = $post['controle_acesso_id'];
		$position = $post['position'];

		try {

			$deleteDs = $this->db->prepare("DELETE FROM controle_acessos_ds WHERE controle_acesso_id = :controle_acesso_id AND position = :position");
			$deleteDs->bindValue(":controle_acesso_id", $controle_acesso_id);
			$deleteDs->bindValue(":position", $position);
			$deleteDs->execute();

			$deleteImg = $this->db->prepare("DELETE FROM controle_acessos_pics WHERE controle_acesso_id = :controle_acesso_id AND position = :position");
			$deleteImg->bindValue(":controle_acesso_id", $controle_acesso_id);
			$deleteImg->bindValue(":position", $position);
			$deleteImg->execute();

			return ['status' => true];
		} catch (\Throwable $th) {
			return ['status' => false];
		}
	}

	public function createTempCa(){

		$now = date("Y-m-d H:i:s");

		$createTempCa = $this->db->prepare("INSERT INTO temp_ca (created_at) VALUES ('{$now}')");
		$createTempCa->execute();
		$tempCaId = $this->db->lastInsertId();

		return ['tempCaId' => $tempCaId, 'tempCa' => 'temp_ca_'.$tempCaId];

	}

}