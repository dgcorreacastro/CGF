<?php

require 'vendor/autoload.php';

class Posicoes extends model 
{

    public function save($body)
    {
        $ym = date("Y_m");

        /**
         * Criar Table se não existir
         */
        $tpl = $this->db->prepare("CREATE TABLE `positions_logs_{$ym}` LIKE positions_logs;");
        $tpl->execute();

        $tp = $this->db->prepare("CREATE TABLE `positions_{$ym}` LIKE positions;");
        $tp->execute();
        /**
         * FIM Criação
         */

        $log = $this->db->prepare("INSERT INTO `positions_logs_{$ym}` (types, datas, created_at) VALUES ('Entrance', '".base64_encode($body)."', NOW())");
        $log->execute();

        $body = json_decode($body);

        foreach($body AS $item)
        {

            try {

                $id = $item->uuid;

                $date   = date("Y-m-d H:i:s", strtotime($item->datahoraPosicao));
        
                $sql = $this->db->prepare("INSERT INTO `positions_{$ym}` (id, veiculoId, prefixo, placa, dataHora, latitude, longitude, velocidade, odometro, direcao, ignicao, statusGps, created_at) VALUES ('{$id}', {$item->veiculoId}, '{$item->veiculoPrefixo}', '{$item->veiculoPlaca}', '{$date}', '{$item->latitude}', '{$item->longitude}', {$item->velocidade}, {$item->hodometro}, {$item->direcao}, {$item->ignicao}, '{$item->statusGps}', NOW())");

                $sql->execute();

            } catch (\Throwable $th) {

                $log =$this->db->prepare("INSERT INTO `positions_logs_{$ym}` (types, datas, created_at) VALUES ('Error', '".base64_encode(json_encode($th))."', NOW())");
                $log->execute();

            }

        }

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



}
?>
