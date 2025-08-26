<?php 

class ParameterUser extends model 
{

    public function getParameters($id = 0)
    {

        $id  = $_SESSION['cType'] == 1 ? $id : $_SESSION['cLogin'];

        $sql = $this->db->prepare("SELECT * FROM parameter_user WHERE user_id = {$id}");
		$sql->execute();
			
        return $sql->fetch(PDO::FETCH_OBJ) ?? null;
    }

    public function updateParameters($post)
    {

        $id  = $_SESSION['cType'] == 1 ? $post['idUser'] : $_SESSION['cLogin'];

        $sql = $this->db->prepare("SELECT * FROM parameter_user WHERE user_id = {$id}");
		$sql->execute();
        $haspara = $sql->fetch(PDO::FETCH_OBJ);

        try {

            $emails = str_replace(",", ";", $post['emails_notify']);

            if ($haspara) {

                $sql = $this->db->prepare("UPDATE parameter_user SET ranger_minutes = :ranger, emails_notify = :emails, updated_at = NOW() where user_id = :user_id");
                $sql->bindValue(":ranger", ($post['ranger_minutes'] > 0 ? $post['ranger_minutes'] : 10));
                $sql->bindValue(":emails", addslashes($emails));
                $sql->bindValue(":user_id", $id);
                $sql->execute();

            } else {

                $sql = $this->db->prepare("INSERT INTO parameter_user (ranger_minutes, emails_notify, user_id, updated_at) VALUES (:ranger, :emails, :user_id, NOW())");
                $sql->bindValue(":ranger", ($post['ranger_minutes'] > 0 ? $post['ranger_minutes'] : 10));
                $sql->bindValue(":emails", addslashes($emails));
                $sql->bindValue(":user_id", $id);
                $sql->execute();

            }

        } catch (\Throwable $th) {
            //throw $th;
            return false;
        }

        return true;
    }

    
}