<?php

class Embarque extends model 
{

	public function getAllEmbarques()
	{
		$array = array();

		$sql = $this->db->prepare('SELECT * FROM embarque_sem_cartaos where deleted_at is null order by id desc');
		$sql->execute();

		if($sql->rowCount() > 0) {
			$array = $sql->fetchAll();
		}

		return $array;
	} 

	public function salvarEmbarque($post)
	{
	
		$sql = $this->db->prepare("INSERT INTO embarque_sem_cartaos SET data = :data, horario_embarque = :horario_embarque, numero_talao = :numero_talao, prefixo_veiculo_id = :prefixo_veiculo_id, cliente_id = :cliente_id, nome_passageiro = :nome_passageiro, linha_id = :linha_id, registro_passageiro = :registro_passageiro, grupo_acesso_id = :grupo_acesso_id");
		$sql->bindValue(":data", $post['data']);
		$sql->bindValue(":horario_embarque", $post['horario_embarque']);
		$sql->bindValue(":numero_talao", $post['numero_talao']);
		$sql->bindValue(":prefixo_veiculo_id", $post['prefixo_veiculo_id']);
		$sql->bindValue(":cliente_id", $post['cliente_id']);
		$sql->bindValue(":nome_passageiro", $post['nome_passageiro']);
		$sql->bindValue(":linha_id", $post['linha_id']);
		$sql->bindValue(":registro_passageiro", $post['registro_passageiro']);
		$sql->bindValue(":grupo_acesso_id", $post['grupo_acesso_id']);
		$sql->execute();

		if (!$sql)
			return false;
		
		return true;
	}

	public function getEmbarque($id)
	{

		$array = array();
		$sql = $this->db->prepare("SELECT * FROM embarque_sem_cartaos where id = {$id}");
		$sql->execute();
		$array = $sql->fetch();
		return $array;
	}

	public function atualizarEmbarque($post)
	{

		$sql = $this->db->prepare("UPDATE embarque_sem_cartaos SET data = :data, horario_embarque = :horario_embarque, numero_talao = :numero_talao, prefixo_veiculo_id = :prefixo_veiculo_id, cliente_id = :cliente_id, nome_passageiro = :nome_passageiro, linha_id = :linha_id, registro_passageiro = :registro_passageiro, grupo_acesso_id = :grupo_acesso_id where id = :id");
		$sql->bindValue(":data", $post['data']);
		$sql->bindValue(":horario_embarque", $post['horario_embarque']);
		$sql->bindValue(":numero_talao", $post['numero_talao']);
		$sql->bindValue(":prefixo_veiculo_id", $post['prefixo_veiculo_id']);
		$sql->bindValue(":cliente_id", $post['cliente_id']);
		$sql->bindValue(":nome_passageiro", $post['nome_passageiro']);
		$sql->bindValue(":linha_id", $post['linha_id']);
		$sql->bindValue(":registro_passageiro", $post['registro_passageiro']);
		$sql->bindValue(":grupo_acesso_id", $post['grupo_acesso_id']);
		$sql->bindValue(":id", $post['id']);
		$sql->execute();

		if (!$sql)
			return false;

		return true;
	}

	public function delEmbarque($id)
	{

		$sql = $this->db->prepare("UPDATE embarque_sem_cartaos SET deleted_at = :deleted_at WHERE id = :id");
		$sql->bindValue(":deleted_at", date("Y-m-d H:i:s"));
		$sql->bindValue(":id", $id);
		$sql->execute();

		if (!$sql)
			return false;

		return true;
	}


}