<?php

class ParameterEscala extends model 
{

	public function get( $uniID = null )
	{
        if (isset($_SESSION['cGr']))
            $id  = $_SESSION['cGr'];
        else 
            $id  = 11; // Eurofarma como Default para o Master
   
        if($uniID)
        {
            $sql = $this->db->prepare("SELECT s.*, gr.NOME as grupo  
            FROM parameterEscalaMes s
            INNER JOIN grupo_linhas gr ON gr.ID_ORIGIN = s.grupoID 
            WHERE s.deleted_at is null AND s.grupoID = {$id} AND s.unidadeID = {$uniID} order by s.mes");
            $sql->execute();

            return $sql->fetchAll(PDO::FETCH_OBJ);

        } else {

            $sql=$this->db->prepare("SELECT * FROM parameterEscalaMes WHERE deleted_at is null AND grupoID = {$id} LIMIT 1");
            $sql->execute();
            $has = $sql->fetch(PDO::FETCH_OBJ);
            
            if($has)
            {
                $sql = $this->db->prepare("SELECT s.*, gr.NOME as grupo  
                                        FROM parameterEscalaMes s
                                        INNER JOIN grupo_linhas gr ON gr.ID_ORIGIN = s.grupoID 
                                        WHERE s.deleted_at is null AND s.grupoID = {$id} AND s.unidadeID = {$has->unidadeID} order by s.mes");
                $sql->execute();
        
                return $sql->fetchAll(PDO::FETCH_OBJ);
            }
        }
		
        return false;
	}

    public function getByDate( $mes, $unID = 0 )
	{

        if (isset($_SESSION['cGr']))
            $id  = $_SESSION['cGr'];
        else 
            $id  = 11; // Eurofarma como Default para o Master

		$sql = $this->db->prepare("SELECT maxFolgaMes, maxDiaSemFolga FROM parameterEscalaMes
                                WHERE deleted_at is null AND grupoID = {$id} AND mes = {$mes} AND unidadeID = {$unID}");
		$sql->execute();

        return $sql->fetch(PDO::FETCH_OBJ);
	}

	public function update($post)
	{
        if ( !isset($_SESSION['cGr']) || $_SESSION['cGr'] == "" )
        {
            return false;
        }

        foreach($post['mes'] AS $k => $mes)
        {
     
            $sql = $this->db->prepare("SELECT * FROM parameterEscalaMes s 
                                        WHERE s.grupoID = ".$_SESSION['cGr']." AND s.mes = {$k}
                                        AND s.unidadeID = ".$post['unidadeID']);
		    $sql->execute(); 

            if( $sql->fetch(PDO::FETCH_OBJ) )
            {

                $sqw = "UPDATE 
                        parameterEscalaMes 
                    SET 
                        maxFolgaMes = :maxFolgaMes,
                        maxDiaSemFolga = :maxDiaSemFolga, 
                        created_at = NOW()
                    where mes = :mes AND grupoID = :grupoID AND unidadeID = :unidadeID";

                $sql = $this->db->prepare($sqw);
                $sql->bindValue(":maxFolgaMes", $post['maxFolga'][$k]);
                $sql->bindValue(":maxDiaSemFolga", $post['maxSemFolga'][$k]);
                $sql->bindValue(":grupoID", $_SESSION['cGr']);
                $sql->bindValue(":unidadeID", $post['unidadeID']);
                $sql->bindValue(":mes", $k);
                $sql->execute();

            } else {

                $sqw = "INSERT INTO 
                                parameterEscalaMes 
                            (mes, maxFolgaMes, maxDiaSemFolga, grupoID, created_at, unidadeID)
                        VALUE
                            (:mes, :maxFolgaMes, :maxDiaSemFolga, :grupoID, NOW(), :unidadeID )";

                $sql = $this->db->prepare($sqw);
                $sql->bindValue(":mes", $k);
                $sql->bindValue(":maxFolgaMes", $post['maxFolga'][$k]);
                $sql->bindValue(":maxDiaSemFolga", $post['maxSemFolga'][$k]);
                $sql->bindValue(":grupoID", $_SESSION['cGr']);
                $sql->bindValue(":unidadeID", $post['unidadeID']);
                $sql->execute();

            }

        }
        
        return true;
	}


}