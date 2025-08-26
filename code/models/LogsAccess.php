<?php

class LogsAccess extends model 
{

    public function create($tipo, $idGrupo)
    {
        $ip =  $this->getUserIP();
        if ($this->moreThanFive($tipo, $idGrupo, $ip))
        {

            $sql = $this->db->prepare("INSERT INTO access_logs SET ipAccess = :ipAccess, hourAccess = now(), typeTotem = :typeTotem, groupID = :groupID");
            $sql->bindValue(":ipAccess", $ip);
            $sql->bindValue(":typeTotem", $tipo);
            $sql->bindValue(":groupID", $idGrupo);
            $sql->execute();

            if (!$sql)
                return false;

        }
		 // { tipos 1 itinetário, 2 passageiro, 3 gerais, 4 PaxEspecial }
		return true;
    }

    public function moreThanFive($tipo, $idGrupo, $ip)
    {

		$sql = $this->db->prepare("SELECT * FROM access_logs where ipAccess = '{$ip}' AND groupID = '{$idGrupo}' AND typeTotem = '{$tipo}' AND DATE_ADD(hourAccess, INTERVAL 5 MINUTE) > now()");
		$sql->execute();
		$array = $sql->fetchAll();

        if (count($array) > 0)
            return false;

        return true;
    }

    public function getUserIP() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }



}
?>