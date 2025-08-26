<?php 

class ParameterGroup extends model 
{

    public function getParameters($id, $forceGraph = false)
    {

        $param = new Parametro;
        $param = $param->getParametros();
        $parameter = [
            'NOME' => $this->getGroupName($id),
            'Distancia' => null,
            'time_atualizar' => null,
            'ranger_dash' => null,
            'cad_pax_pics' => 0,
            'cad_pax_tag' => 1,
            'daily_info' => 0,
            'graphDefault' => 1,
            'graphPontualColor' => $param['graphPontualColor'],
            'graphAdiantadoColor' => $param['graphAdiantadoColor'],
            'graphAtrasadoColor' => $param['graphAtrasadoColor'],
            'graphNesColor' => $param['graphNesColor'],
            'graphAgendaColor' => $param['graphAgendaColor'],
            'graphReColor' => $param['graphReColor'],
            'graphSreColor' => $param['graphSreColor'],
            'graphBarraColor' => $param['graphBarraColor'],
            'graphPontualTxt' => $param['graphPontualTxt'],
            'graphAdiantadoTxt' => $param['graphAdiantadoTxt'],
            'graphAtrasadoTxt' => $param['graphAtrasadoTxt'],
            'graphNesTxt' => $param['graphNesTxt'],
            'graphAgendaTxt' => $param['graphAgendaTxt'],
            'graphReTxt' => $param['graphReTxt'],
            'graphSreTxt' => $param['graphSreTxt']
        ];

        $sql = $this->db->prepare("SELECT * FROM parameter_group WHERE group_id = {$id}");
		$sql->execute();

        if($sql->rowCount() == 1) {
            $par = $sql->fetch();
            $parameter['Distancia'] = $par['Distancia'];
            $parameter['time_atualizar'] = $par['time_atualizar'];
            $parameter['ranger_dash'] = $par['ranger_dash'];
            $parameter['cad_pax_pics'] = $par['cad_pax_pics'];
            $parameter['cad_pax_tag'] = $par['cad_pax_tag'];
            $parameter['daily_info'] = $par['daily_info'];
            $parameter['graphDefault'] = $par['graphDefault'];
            
            if($par['graphDefault'] == 0 || $forceGraph){
                $parameter['graphPontualColor'] = $par['graphPontualColor'];
                $parameter['graphAdiantadoColor'] = $par['graphAdiantadoColor'];
                $parameter['graphAtrasadoColor'] = $par['graphAtrasadoColor'];
                $parameter['graphNesColor'] = $par['graphNesColor'];
                $parameter['graphAgendaColor'] = $par['graphAgendaColor'];
                $parameter['graphReColor'] = $par['graphReColor'];
                $parameter['graphSreColor'] = $par['graphSreColor'];
                $parameter['graphBarraColor'] = $par['graphBarraColor'];
                $parameter['graphPontualTxt'] = $par['graphPontualTxt'];
                $parameter['graphAdiantadoTxt'] = $par['graphAdiantadoTxt'];
                $parameter['graphAtrasadoTxt'] = $par['graphAtrasadoTxt'];
                $parameter['graphNesTxt'] = $par['graphNesTxt'];
                $parameter['graphAgendaTxt'] = $par['graphAgendaTxt'];
                $parameter['graphReTxt'] = $par['graphReTxt'];
                $parameter['graphSreTxt'] = $par['graphSreTxt'];
            }
            
        }

        $rel = new Relatorios();

        $parameter['graphPontualTxtColor'] = $rel->getTextColorBasedOnBgColor($parameter['graphPontualColor']);
        $parameter['graphAdiantadoTxtColor'] = $rel->getTextColorBasedOnBgColor($parameter['graphAdiantadoColor']);
        $parameter['graphAtrasadoTxtColor'] = $rel->getTextColorBasedOnBgColor($parameter['graphAtrasadoColor']);
        $parameter['graphNesTxtColor'] = $rel->getTextColorBasedOnBgColor($parameter['graphNesColor']);
        $parameter['graphAgendaTxtColor'] = $rel->getTextColorBasedOnBgColor($parameter['graphAgendaColor']);

        $parameter['graphReTxtColor'] = $rel->getTextColorBasedOnBgColor($parameter['graphReColor']);
        $parameter['graphSreTxtColor'] = $rel->getTextColorBasedOnBgColor($parameter['graphSreColor']);
			
        return (object) $parameter;
    }

    public function getGroupName($id){

        $name = "Sem Nome";
        $sql = $this->db->prepare("SELECT NOME 
        FROM grupo_linhas
        WHERE id = {$id} LIMIT 1");
		$sql->execute();
		if($sql->rowCount() == 1) {
            $name = $sql->fetch()['NOME'];
        }
        return $name;
    }

    public function updateParameters($post)
    {

        $id  = $post['idGroup'];

        $sql = $this->db->prepare("SELECT * FROM parameter_group WHERE group_id = {$id}");
		$sql->execute();
        $haspara = $sql->fetch(PDO::FETCH_OBJ);

        $cad_pax_pics_pg = isset($post['cad_pax_pics_pg']) ? 1 : 0;
        
        $cad_pax_pics = isset($post['cad_pax_pics']) ? 1 : 0;
        $cad_pax_tag = isset($post['cad_pax_tag']) ? 1 : 0;
        $daily_info = isset($post['daily_info']) ? 1 : 0;
        $graphDefault = isset($post['graphDefault']) ? 1 : 0;

        $graphPontualColor 		= trim($_POST['graphPontualColor']) != "" ? trim($_POST['graphPontualColor']) : null;
		$graphAdiantadoColor 	= trim($_POST['graphAdiantadoColor']) != "" ? trim($_POST['graphAdiantadoColor']) : null;
		$graphAtrasadoColor 	= trim($_POST['graphAtrasadoColor']) != "" ? trim($_POST['graphAtrasadoColor']) : null;
		$graphNesColor 			= trim($_POST['graphNesColor']) != "" ? trim($_POST['graphNesColor']) : null;
		$graphAgendaColor 		= trim($_POST['graphAgendaColor']) != "" ? trim($_POST['graphAgendaColor']) : null;
		$graphReColor 			= trim($_POST['graphReColor']) != "" ? trim($_POST['graphReColor']) : null;
		$graphSreColor 			= trim($_POST['graphSreColor']) != "" ? trim($_POST['graphSreColor']) : null;
		$graphBarraColor 		= trim($_POST['graphBarraColor']) != "" ? trim($_POST['graphBarraColor']) : null;

        $graphPontualTxt 		= trim($post['graphPontualTxt']) != "" ? trim($post['graphPontualTxt']) : null;
		$graphAdiantadoTxt 		= trim($post['graphAdiantadoTxt']) != "" ? trim($post['graphAdiantadoTxt']) : null;
		$graphAtrasadoTxt 		= trim($post['graphAtrasadoTxt']) != "" ? trim( $post['graphAtrasadoTxt']) : null;
		$graphNesTxt 			= trim($post['graphNesTxt']) != "" ? trim($post['graphNesTxt']) : null;
		$graphAgendaTxt 		= trim($post['graphAgendaTxt']) != "" ? trim($post['graphAgendaTxt']) : null;
		$graphReTxt 			= trim($post['graphReTxt']) != "" ? trim($post['graphReTxt']) : null;
		$graphSreTxt 			= trim($post['graphSreTxt']) != "" ? trim($post['graphSreTxt']) : null;


        try {

            if ($haspara) {

                $sql = $this->db->prepare("UPDATE parameter_group SET Distancia = :Distancia, time_atualizar = :time_atualizar, ranger_dash = :ranger_dash, cad_pax_pics = :cad_pax_pics, cad_pax_tag = :cad_pax_tag, daily_info = :daily_info, graphPontualColor = :graphPontualColor, graphAdiantadoColor = :graphAdiantadoColor, graphAtrasadoColor = :graphAtrasadoColor, graphNesColor = :graphNesColor, graphAgendaColor = :graphAgendaColor, graphReColor = :graphReColor, graphSreColor = :graphSreColor, graphBarraColor = :graphBarraColor, graphPontualTxt = COALESCE(:graphPontualTxt, graphPontualTxt), graphAdiantadoTxt = COALESCE(:graphAdiantadoTxt, graphAdiantadoTxt), graphAtrasadoTxt = COALESCE(:graphAtrasadoTxt, graphAtrasadoTxt), graphNesTxt = COALESCE(:graphNesTxt, graphNesTxt), graphAgendaTxt = COALESCE(:graphAgendaTxt, graphAgendaTxt), graphReTxt = COALESCE(:graphReTxt, graphReTxt), graphSreTxt = COALESCE(:graphSreTxt, graphSreTxt), graphDefault = :graphDefault, updated_at = NOW() where group_id = :group_id");
                $sql->bindValue(":Distancia", ($post['Distancia'] > 0 ? $post['Distancia'] : null));
                $sql->bindValue(":time_atualizar", ($post['time_atualizar'] > 0 ? $post['time_atualizar'] : null));
                $sql->bindValue(":ranger_dash", ($post['ranger_dash'] > 0 ? $post['ranger_dash'] : null));
                $sql->bindValue(":cad_pax_pics", $cad_pax_pics);
                $sql->bindValue(":cad_pax_tag", $cad_pax_tag);
                $sql->bindValue(":daily_info", $daily_info);
                $sql->bindValue(":graphPontualColor", $graphPontualColor);
                $sql->bindValue(":graphAdiantadoColor", $graphAdiantadoColor);
                $sql->bindValue(":graphAtrasadoColor", $graphAtrasadoColor);
                $sql->bindValue(":graphNesColor", $graphNesColor);
                $sql->bindValue(":graphAgendaColor", $graphAgendaColor);
                $sql->bindValue(":graphReColor", $graphReColor);
                $sql->bindValue(":graphSreColor", $graphSreColor);
                $sql->bindValue(":graphBarraColor", $graphBarraColor);
                $sql->bindValue(":graphPontualTxt", $graphPontualTxt);
                $sql->bindValue(":graphAdiantadoTxt", $graphAdiantadoTxt);
                $sql->bindValue(":graphAtrasadoTxt", $graphAtrasadoTxt);
                $sql->bindValue(":graphNesTxt", $graphNesTxt);
                $sql->bindValue(":graphAgendaTxt", $graphAgendaTxt);
                $sql->bindValue(":graphReTxt", $graphReTxt);
                $sql->bindValue(":graphSreTxt", $graphSreTxt);
                $sql->bindValue(":graphDefault", $graphDefault);
                $sql->bindValue(":group_id", $id);
                $sql->execute();

            } else {

                $sql = $this->db->prepare("INSERT INTO parameter_group (Distancia, time_atualizar, ranger_dash, cad_pax_pics, cad_pax_tag, daily_info, graphPontualColor, graphAdiantadoColor, graphAtrasadoColor, graphNesColor, graphAgendaColor, graphReColor, graphSreColor, graphBarraColor, graphPontualTxt, graphAdiantadoTxt, graphAtrasadoTxt, graphNesTxt, graphAgendaTxt, graphReTxt, graphSreTxt, graphDefault, group_id, created_at) VALUES (:Distancia, :time_atualizar, :ranger_dash, :cad_pax_pics, :cad_pax_tag, :daily_info, :graphPontualColor, :graphAdiantadoColor, :graphAtrasadoColor, :graphNesColor, :graphAgendaColor, :graphReColor, :graphSreColor, :graphBarraColor, :graphPontualTxt, :graphAdiantadoTxt, :graphAtrasadoTxt, :graphNesTxt, :graphAgendaTxt, :graphReTxt, :graphSreTxt, :graphDefault, :group_id, NOW())");
                $sql->bindValue(":Distancia", ($post['Distancia'] > 0 ? $post['Distancia'] : null));
                $sql->bindValue(":time_atualizar", ($post['time_atualizar'] > 0 ? $post['time_atualizar'] : null));
                $sql->bindValue(":ranger_dash", ($post['ranger_dash'] > 0 ? $post['ranger_dash'] : null));
                $sql->bindValue(":cad_pax_pics", $cad_pax_pics);
                $sql->bindValue(":cad_pax_tag", $cad_pax_tag);
                $sql->bindValue(":daily_info", $daily_info);
                $sql->bindValue(":graphPontualColor", $graphPontualColor);
                $sql->bindValue(":graphAdiantadoColor", $graphAdiantadoColor);
                $sql->bindValue(":graphAtrasadoColor", $graphAtrasadoColor);
                $sql->bindValue(":graphNesColor", $graphNesColor);
                $sql->bindValue(":graphAgendaColor", $graphAgendaColor);
                $sql->bindValue(":graphReColor", $graphReColor);
                $sql->bindValue(":graphSreColor", $graphSreColor);
                $sql->bindValue(":graphBarraColor", $graphBarraColor);
                $sql->bindValue(":graphPontualTxt", $graphPontualTxt);
                $sql->bindValue(":graphAdiantadoTxt", $graphAdiantadoTxt);
                $sql->bindValue(":graphAtrasadoTxt", $graphAtrasadoTxt);
                $sql->bindValue(":graphNesTxt", $graphNesTxt);
                $sql->bindValue(":graphAgendaTxt", $graphAgendaTxt);
                $sql->bindValue(":graphReTxt", $graphReTxt);
                $sql->bindValue(":graphSreTxt", $graphSreTxt);
                $sql->bindValue(":graphDefault", $graphDefault);
                $sql->bindValue(":group_id", $id);
                $sql->execute();

            }

        } catch (\Throwable $th) {
            //throw $th;
            return false;
        }

        return true;
    }

    
}