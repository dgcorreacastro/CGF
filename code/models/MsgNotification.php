<?php

class MsgNotification extends model 
{

	public function getMsgsNotification()
	{
		$array = array();

		$sql = $this->db->prepare('SELECT * FROM app_notification_msgs WHERE ativa = 1');
		$sql->execute();

		if($sql->rowCount() > 0) {
			$array = $sql->fetchAll();
		}

		return $array;
	}
}