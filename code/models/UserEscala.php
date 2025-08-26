<?php

class UserEscala extends model 
{

	public function list( $pag, $limit, $unid, $filtroNome )
	{
        $w = "";

        if(isset($_SESSION['cGr']) && isset($_SESSION['cFret']))
            $w .= " AND s.grupoID = {$_SESSION['cGr']} ";

        if($unid != null && $unid > 0)
            $w .= " AND s.unidadeID = {$unid} ";

        if($filtroNome != null && $filtroNome != "")
            $w .= " AND s.nome LIKE '%{$filtroNome}%' ";

        #######################################################################
        ############################# GET TOTAL ###############################
        #######################################################################
        $sql="SELECT COUNT(*) AS total FROM userEscala s where s.deleted_at is null {$w}";
        $sql = $this->db->prepare($sql);
        $sql->execute();
        $tt = $sql->fetch(PDO::FETCH_OBJ);
        #######################################################################
        ######################### CONTINUE FILTERS ############################
        #######################################################################

        if(isset($_SESSION['cFret']) && $_SESSION['cType'] == 1)
            $w .= " AND s.id = " . $_SESSION['cLogin'];

        $limPag     = 15;
        $ttPages    = intval( ceil($tt->total / $limPag) ); 
        $of         = $limPag * ($pag -1);
        $offset     = $of > 0 ? " OFFSET $of" : "";
   
        $sql = "SELECT s.*, un.descricao, gr.NOME as grupoName
                FROM userEscala s
                LEFT JOIN unidades un ON un.id = s.unidadeID
                LEFT JOIN grupo_linhas gr ON gr.id = s.grupoID
                where s.deleted_at is null {$w} ORDER BY s.nome LIMIT {$limPag} {$offset}";

        $sql = $this->db->prepare($sql);
        $sql->execute();
        
        return array ("users"=> $sql->fetchAll(PDO::FETCH_OBJ), "total"=> $ttPages );
	} 

    public function getLideres( $gr = null)
	{
        $w = "";

        if(isset($_SESSION['cFret']))
            $w = " AND s.grupoID = " . $_SESSION['cGr'];

        $sql = "SELECT s.*, gr.NOME as grupo  
                FROM userEscala s
                INNER JOIN grupo_linhas gr ON gr.ID_ORIGIN = s.grupoID
                where s.deleted_at is null AND s.type = 1 {$w} ORDER BY s.nome";

        $sql = $this->db->prepare($sql);
        $sql->execute();
        
        return $sql->fetchAll(PDO::FETCH_OBJ);
	} 

    public function getUnByLider($id){
        $sql = "SELECT unidadeID FROM userEscala WHERE id = $id LIMIT 1";
        $sql = $this->db->prepare($sql);
        $sql->execute();
        $rt = $sql->fetch(PDO::FETCH_OBJ);

        $txt = "";

        if( $rt ) $txt = $rt->unidadeID;

        return $txt;
    }

	public function get( $id )
	{
		$sql = $this->db->prepare("SELECT s.*, gr.NOME as grupo  
                                    FROM userEscala s
                                    INNER JOIN grupo_linhas gr ON gr.ID_ORIGIN = s.grupoID where s.deleted_at is null AND s.id = {$id}");
		$sql->execute();

        return $sql->fetch(PDO::FETCH_OBJ);
	} 

	public function save( $post )
	{

        $groupID = $_SESSION['cGr'];

        if ( !isset($_SESSION['cFret']) && $_SESSION['cType'] == 1)
            $groupID = $post['groupID'];

        if( $this->hasRegister( $post['email'], null, $groupID) )
        {
            $arr = array('error'=> true, 'msg' => 'Já existe um registro com esse email. Não foi possível salvar');
            return $arr;
        }
       
        $pass  = isset($post['pass']) ? md5($post['pass']) : md5($post['email']);
        $undID = isset($post['unidadeID']) && $post['unidadeID'] != 'Selecione' ? $post['unidadeID'] : 0; 

		$sql = $this->db->prepare("INSERT INTO userEscala (nome, email, pass, grupoID, `type`, userID, unidadeID, created_at ) VALUE (:nome, :email, :pass, :grupoID, :type, :userID, :unidadeID, now())");
		$sql->bindValue(":nome", $post['nome']);
		$sql->bindValue(":email", $post['email']);
		$sql->bindValue(":pass", $pass);
		$sql->bindValue(":grupoID", $groupID);
		$sql->bindValue(":type", $post['type']);
		$sql->bindValue(":userID", $post['userID']);
		$sql->bindValue(":unidadeID", $undID);
		$sql->execute();
        
		if (!$sql)
			return false;
		
		return true;
	}

	public function update($post)
	{

        if(isset($_SESSION['cFret']) && $_SESSION['cType'] == 1)
        {
            if($post['id'] == $_SESSION['cLogin'] && $post['pass'] != "")
            {
                $sql = $this->db->prepare("UPDATE userEscala SET pass = :pass, updated_at = NOW() where id = :id");
                $sql->bindValue(":pass", md5($post['pass']));
                $sql->bindValue(":id", $post['id']);
                $sql->execute();
            }

            return true;

        } else {

            if( $this->hasRegister($post['email'], $post['id'], $_SESSION['cGr'] ) )
            {
                $arr = array('error'=> true, 'msg' => 'Já existe um registro com esse email. Não foi possível salvar');
                return false;
            }

            if ( isset($post['pass']) && $post['pass'] != "" )
            {

                $sql = $this->db->prepare("UPDATE userEscala SET nome = :nome, email = :email, pass = :pass, grupoID = :grupoID, unidadeID = :unidadeID, `type` = :type, updated_at = NOW() where id = :id");

                $sql->bindValue(":pass", md5($post['pass']));

            } else {

                $sql = $this->db->prepare("UPDATE userEscala SET nome = :nome, email = :email, grupoID = :grupoID, unidadeID = :unidadeID, `type` = :type, updated_at = NOW() where id = :id");

            }
            
            $un = $post['unidadeID'] != "Selecione" ? $post['unidadeID'] : 0;
            
            $sql->bindValue(":nome", $post['nome']);
            $sql->bindValue(":email", $post['email']);
            $sql->bindValue(":grupoID", $_SESSION['cGr']);
            $sql->bindValue(":unidadeID", $un);
            $sql->bindValue(":type", $post['type']);
            $sql->bindValue(":id", $post['id']);
            $sql->execute();
            
            if (!$sql)
                return false;
        
            return true;
        }
	}

	public function delete($id)
	{

		$sql = $this->db->prepare("UPDATE userEscala SET deleted_at = NOW() WHERE id = {$id}");
        $sql->execute();

        if (!$sql)
            return ["success" => false, "msg" => "Ocorreu um erro ao remover o usuário/líder, tente novamente."];
    
        return ["success" => true, "msg" => "Usuário/Líder deletado com sucesso!"];
	}

    public function hasRegister( $email, $id = null, $gr = null )
	{
        $w = "";

        if($id != null)
            $w = " AND id <> {$id}";

        // if($gr != null)
        //     $w .= " AND grupoID = {$gr}";

        $sql = $this->db->prepare("SELECT * FROM userEscala where deleted_at is null AND email = '{$email}' {$w}");

        $sql->execute();
        $has = $sql->fetch(PDO::FETCH_OBJ);

        if (!$has) 
            return false; 

        return true;
	} 

    public function getUnidades()
	{
        $w = "";
       
        if(isset($_SESSION['cGr']))
            $w = " AND grupoID = {$_SESSION['cGr']} ";

        $sql = "SELECT * FROM unidades where deleted_at is null {$w} ORDER BY descricao";
    
        $sql = $this->db->prepare($sql);
        $sql->execute();

        return $sql->fetchAll(PDO::FETCH_OBJ);
    } 

    public function importUser( $paxs )
	{
		$arr 	= array();
		$nome 	= isset($paxs[0]) ? $paxs[0] : "";
		$unid 	= isset($paxs[1]) ? $paxs[1] : ""; 
		$email 	= $paxs[2];
		$senha 	= isset($paxs[3]) ? md5($paxs[3]) : md5("123456");
		$tipo 	= isset($paxs[4]) ? $paxs[4] : 1;
        $grp    = $_SESSION['cGr'];

        // Check se tem Email, se tiver atualiza se não tiver remove acesso \\
        $sql = $this->db->prepare("SELECT * FROM userEscala 
                                    WHERE deleted_at is null AND email = '{$email}' AND grupoID = {$grp}");
		$sql->execute();
        $has = $sql->fetch(PDO::FETCH_OBJ);

        if ( !$has )
        { // Cadastrar se não tiver
            $idUnid = 0;
            if ($unid != "")
            {
                $sql = $this->db->prepare("SELECT * FROM unidades WHERE deleted_at is null AND descricao = '".trim($unid)."' AND grupoID = {$grp}");
                $sql->execute();
                $hasUni = $sql->fetch(PDO::FETCH_OBJ);

                if($hasUni){
                    $idUnid = $hasUni->id;
                } else {
                    $sql = $this->db->prepare("INSERT INTO unidades 
                                                (
                                                    grupoID, 
                                                    descricao, 
                                                    created_at 
                                                ) VALUE (
                                                    :grupoID, 
                                                    :descricao, 
                                                    now())"
                                                );

                    $sql->bindValue(":grupoID", $_SESSION['cGr']);
                    $sql->bindValue(":descricao", trim($unid));
                    $sql->execute();

                    $idUnid = $this->db->lastInsertId();
                }

            }

            $sql = $this->db->prepare("INSERT INTO userEscala 
                                        (
                                            nome, 
                                            email, 
                                            pass, 
                                            grupoID, 
                                            unidadeID, 
                                            `type`, 
                                            userID, 
                                            created_at 
                                        ) VALUE (
                                            :nome, 
                                            :email, 
                                            :pass, 
                                            :grupoID, 
                                            :unidadeID, 
                                            :type, 
                                            :userID, 
                                            now())"
                                        );

            $sql->bindValue(":nome", $nome);
            $sql->bindValue(":email", $email);
            $sql->bindValue(":pass", $senha);
            $sql->bindValue(":grupoID", $_SESSION['cGr']);
            $sql->bindValue(":type", $tipo);
            $sql->bindValue(":userID", $_SESSION['cLogin']);
            $sql->bindValue(":unidadeID", $idUnid);
            $sql->execute();

            return $this->db->lastInsertId();
        } else {
            // Update 
            $idUnid = 0;
            if ($unid != "")
            {
                $sql = $this->db->prepare("SELECT * FROM unidades WHERE deleted_at is null AND descricao = '".trim($unid)."' AND grupoID = {$grp}");
                $sql->execute();
                $hasUni = $sql->fetch(PDO::FETCH_OBJ);

                if($hasUni){
                    $idUnid = $hasUni->id;
                } else {
                    $sql = $this->db->prepare("INSERT INTO unidades 
                                                (
                                                    grupoID, 
                                                    descricao, 
                                                    created_at 
                                                ) VALUE (
                                                    :grupoID, 
                                                    :descricao, 
                                                    now())"
                                                );

                    $sql->bindValue(":grupoID", $_SESSION['cGr']);
                    $sql->bindValue(":descricao", trim($unid));
                    $sql->execute();

                    $idUnid = $this->db->lastInsertId();
                }

            }

            $sql = $this->db->prepare("UPDATE userEscala SET 
                                        nome = :nome,
                                        unidadeID = :unidadeID,
                                        `type` = :type
                                    WHERE id = :id");

            $sql->bindValue(":nome", $nome);
            $sql->bindValue(":unidadeID", $idUnid);
            $sql->bindValue(":type", $tipo);
            $sql->bindValue(":id", $has->id);
            $sql->execute();
        }
        
        return $has->id;
	}

    public function updateWithNotHas( $ids )
    {
        $ids = implode(",", $ids);

        $sql = $this->db->prepare("UPDATE userEscala SET deleted_at = NOW() where grupoID = :grupoID AND deleted_at is null AND id NOT IN ($ids)");
        $sql->bindValue(":grupoID", $_SESSION['cGr']);
        $sql->execute();
        
        return true;
    }

}