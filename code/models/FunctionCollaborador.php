<?php

class FunctionCollaborador extends model 
{

	public function list( $gr = null)
	{
        $w = "";

        if($gr != null)
            $w = " AND s.grupoID = {$gr} ";

        if(isset($_SESSION['cFret']))
            $w = " AND s.grupoID = " . $_SESSION['cGr'];

        $sql = "SELECT s.*, gr.NOME as grupo  
                FROM funcoes s
                INNER JOIN grupo_linhas gr ON gr.ID_ORIGIN = s.grupoID
                where s.deleted_at is null {$w} ORDER BY s.descricao";

        $sql = $this->db->prepare($sql);
        $sql->execute();
        
        return $sql->fetchAll(PDO::FETCH_OBJ);
	} 

	public function get( $id )
	{
		$sql = $this->db->prepare("SELECT s.*, gr.NOME as grupo  
                                    FROM funcoes s
                                    INNER JOIN grupo_linhas gr ON gr.ID_ORIGIN = s.grupoID where s.deleted_at is null AND s.id = {$id}");
		$sql->execute();

        return $sql->fetch(PDO::FETCH_OBJ);
	} 

	public function save( $post )
	{
        if( $this->hasRegister( $post['descricao'], null, $post['grupo']) )
        {
            $arr = array('error'=> true, 'msg' => 'Já existe um registro com essa Descrição. Não foi possível salvar');
            return $arr;
        }

		$sql = $this->db->prepare("INSERT INTO funcoes (descricao, grupoID, created_at ) VALUE (:descricao, :grupoID, now())");
		$sql->bindValue(":descricao", $post['descricao']);
		$sql->bindValue(":grupoID", $post['grupo']);
		$sql->execute();

		if (!$sql)
			return false;
		
		return true;
	}

	public function update($post)
	{
        if( $this->hasRegister($post['descricao'], $post['id'], $post['grupo'] ) )
        {
            $arr = array('error'=> true, 'msg' => 'Já existe um registro com essa Descrição. Não foi possível salvar');
            return false;
        }

        $sql = $this->db->prepare("UPDATE funcoes SET descricao = :descricao, grupoID = :grupoID where id = :id");
        $sql->bindValue(":descricao", $post['descricao']);
        $sql->bindValue(":grupoID", $post['grupo']);
        $sql->bindValue(":id", $post['id']);
        $sql->execute();
		
        if (!$sql)
            return false;
    
        return true;
	}

	public function delete($id)
	{

		$sql = $this->db->prepare("UPDATE funcoes SET deleted_at = NOW() WHERE id = {$id}");
        $sql->execute();

        if (!$sql)
            return ["success" => false, "msg" => "Ocorreu um erro ao remover a função, tente novamente."];
    
        return ["success" => true, "msg" => "Função deletada com sucesso!"];
	}

    public function hasRegister( $descr, $id = null, $gr = null )
	{
        $w = "";

        if($id != null)
            $w = " AND id <> {$id}";

        if($gr != null)
            $w .= " AND grupoID = {$gr}";

        $sql = $this->db->prepare("SELECT * FROM funcoes where deleted_at is null AND descricao = '{$descr}' {$w}");

        $sql->execute();
        $has = $sql->fetch(PDO::FETCH_OBJ);

        if (!$has) 
            return false; 

        return true;
	} 


}