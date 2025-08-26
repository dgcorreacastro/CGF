<?php

class Setores extends model 
{

	public function list( $gr = null )
	{
        $w = "";

        if(isset($_SESSION['cFret']))
            $w = " AND s.grupoID = " . $_SESSION['cGr'];

        $sql = "SELECT s.*, gr.NOME as grupo  
                FROM setores s
                INNER JOIN grupo_linhas gr ON gr.ID_ORIGIN = s.grupoID
                where s.deleted_at is null {$w} ORDER BY s.descricao";

        $sql = $this->db->prepare($sql);
        $sql->execute();
        
        return $sql->fetchAll(PDO::FETCH_OBJ);
	} 

	public function get( $id )
	{
		$sql = $this->db->prepare("SELECT s.*, gr.NOME as grupo  
                                    FROM setores s
                                    INNER JOIN grupo_linhas gr ON gr.ID_ORIGIN = s.grupoID where s.deleted_at is null AND s.id = {$id}");
		$sql->execute();

        return $sql->fetch(PDO::FETCH_OBJ);
	} 

    public function nameSetor($id)
    {

        $sql = $this->db->prepare("SELECT * FROM setores where deleted_at is null AND descricao = '{$id}'");
        $sql->execute();
        $re = $sql->fetch(PDO::FETCH_OBJ);

        return $re? $re->descricao : " - ";
    }

	public function save( $post )
	{
        if( $this->hasRegister( $post['descricao'], null, $_SESSION['cGr']) )
        {
            $arr = array('error'=> true, 'msg' => 'Já existe um registro com essa Descrição. Não foi possível salvar');
            return $arr;
        }

		$sql = $this->db->prepare("INSERT INTO setores (descricao, grupoID, userID, created_at ) VALUE (:descricao,:grupoID,:userID, now())");
		$sql->bindValue(":descricao", $post['descricao']);
		$sql->bindValue(":grupoID", $_SESSION['cGr']);
		$sql->bindValue(":userID", $post['userID']);
		$sql->execute();

		if (!$sql)
			return false;
		
		return true;
	}

	public function update($post)
	{
        if( $this->hasRegister($post['descricao'], $post['id'], $_SESSION['cGr'] ) )
        {
            $arr = array('error'=> true, 'msg' => 'Já existe um registro com essa Descrição. Não foi possível salvar');
            return false;
        }

        $sql = $this->db->prepare("UPDATE setores SET descricao = :descricao, grupoID = :grupoID where id = :id");
        $sql->bindValue(":descricao", $post['descricao']);
        $sql->bindValue(":grupoID", $_SESSION['cGr']);
        $sql->bindValue(":id", $post['id']);
        $sql->execute();
		
        if (!$sql)
            return false;
    
        return true;
	}

	public function delete($id)
	{

		$sql = $this->db->prepare("UPDATE setores SET deleted_at = NOW() WHERE id = {$id}");
        $sql->execute();

        if (!$sql)
            return ["success" => false, "msg" => "Ocorreu um erro ao remover o setor, tente novamente."];
    
        return ["success" => true, "msg" => "Setor deletado com sucesso!"];
	}

    public function hasRegister( $descr, $id = null, $gr = null )
	{
        $w = "";

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


}