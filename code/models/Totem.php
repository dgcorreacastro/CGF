<?php

class Totem extends model 
{
    public $meses = array("Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro");

	public function getTotem($tipo = 0) 
	{
		$dtI = date("Y-m-") . "01 00:00:00";
		$dtF = date("Y-m-") . "31 23:59:59";

		$array 	= array();
		$sql 	= $this->db->prepare("SELECT *, 
									(SELECT COUNT(*) FROM access_logs WHERE groupID = grupo_linhas.ID_ORIGIN 
									AND typeTotem = '{$tipo}' AND hourAccess BETWEEN '{$dtI}' AND '{$dtF}'
									) AS acessos
									FROM grupo_linhas where deleted_at is null AND LINK is not null");
		$sql->execute();
		$array = $sql->fetchAll();

		return $array;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function getStatistics($post){
        
        $groupId = $post['groupId'];
        $nomegr = $post['nomegr'];
       
		// echo "groupId: $groupId nome: $nomegr";

        $dados = array();
        $grafico = array();

        $dados['status']    = true;
        $dados['groupId']   = $groupId;
        $dados['nomegr']    = $nomegr;

        //PARA INSTALAÇÕES
        if(isset($post['groupId'])){
            //PARA ACESSOS

            $sqlDataFim = $this->db->prepare("SELECT 
                month(hourAccess) as mes, year(hourAccess) as ano
                FROM access_logs
                WHERE groupID = '{$groupId}'
                AND typeTotem = '1'
                ORDER BY id DESC LIMIT 1");

            $sqlDataFim->execute();
            $dF = $sqlDataFim->fetch(PDO::FETCH_OBJ);

            //SE ENCONTRA MÊS E ANO FINAL USA, SE NÃO USA MÊS ANO ATUAL
            $end = $dF != '' ? date($dF->ano.'-'.sprintf("%02d", $dF->mes)) : date("Y-m");
            $maxEnd = $dF != '' ? date($dF->ano.'-'.sprintf("%02d", $dF->mes)) : date("Y-m");
            
            //SE NÃO VEM COM DATAS, TENTA ACHAR A DATA DO PRIMEIRO E DO ÚLTIMO MES/ANO
            $sqlDataIni = $this->db->prepare("SELECT 
                month(hourAccess) as mes, year(hourAccess) as ano
                FROM access_logs
                WHERE groupID = '{$groupId}'
                AND typeTotem = '1'
                ORDER BY id ASC LIMIT 1");    


            $sqlDataIni->execute();
            $dI = $sqlDataIni->fetch(PDO::FETCH_OBJ);

            //SE ENCONTRA MÊS E ANO INICIAL USA, SE NÃO USA MÊS ANO ATUAL
            $minStart = $dI != '' ? date($dI->ano.'-'.sprintf("%02d", $dI->mes)) : date("Y-m");
            $start = date('Y-m', strtotime("$end -1 year"));
            $start = $minStart > $start ? $minStart : $start;          

            //SE VEM COM DATAS, USA AS DATAS
            if(isset($post['start']) && isset($post['end'])){

                $start  = $post['start'];
                $end    = $post['end'];

            }

            $dtI = $start . "-01 00:00:00";
		    $dtF = $end . "-31 23:59:59";

            $dados['pagetitle'] = 'Acessos';

            $sqlAcessos = $this->db->prepare("SELECT 
                COUNT(ipAccess) as acessos, month(hourAccess) as mes, year(hourAccess) as ano
                FROM access_logs
                WHERE groupID = '{$groupId}'
                AND hourAccess BETWEEN '{$dtI}' AND '{$dtF}'
                GROUP BY month(hourAccess), year(hourAccess)
                ORDER BY ano, mes");

                $sqlAcessos->execute();

                $a = $sqlAcessos->fetchAll(PDO::FETCH_OBJ);

        }

        foreach($a as $b){
            array_push($grafico, array($this->meses[(ltrim($b->mes, '0') - 1)].'/'.$b->ano, $b->acessos));
        }

        $mesini = $this->meses[(ltrim(date("m", strtotime($start)), '0') - 1)];

        $anoini = date("Y", strtotime($start));
        $dados['mesinfo'] = $mesini.'/'.$anoini;

        if($start != $end){
            $dados['mesinfo'] .= ' - '.$this->meses[(ltrim(date("m", strtotime($end)), '0') - 1)].'/'.date("Y", strtotime($end));
        }

        $dados['start'] = $start;
        $dados['minStart'] = $minStart;
        $dados['end'] = $end;
        $dados['maxEnd'] = $maxEnd;

        $dados['grafico'] = $grafico;

        return $dados;
    }

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function getGrupoLinhas($all = false)
	{

		$array 	= array();

		if($all)
			$sql = $this->db->prepare("SELECT * FROM grupo_linhas where deleted_at is null ORDER BY NOME");
		else 
			$sql = $this->db->prepare("SELECT * FROM grupo_linhas where deleted_at is null AND LINK is null ORDER BY NOME");

		$sql->execute();
		$array = $sql->fetchAll();

		return $array;
	}

	public function getTotemEdit($id)
	{
		$array 	= array();
		$sql 	= $this->db->prepare("SELECT * FROM grupo_linhas where id = :id");
		$sql->bindValue(":id", $id);
		$sql->execute();
		$array = $sql->fetch();

		return $array;
	}


	public function atualizarTotem($id, $link)
	{
		$sql = $this->db->prepare("UPDATE grupo_linhas SET LINK = :LINK WHERE ID_ORIGIN = :ID_ORIGIN");
		$sql->bindValue(":LINK", $link);
		$sql->bindValue(":ID_ORIGIN", $id);
		$sql->execute();

		if (!$sql)
			return false;

		return true;
	}

	public function delLinkTotem($id)
	{
		$sql = $this->db->prepare("UPDATE grupo_linhas SET LINK = :LINK WHERE id = :id");
		$sql->bindValue(":LINK", null);
		$sql->bindValue(":id", $id);
		$sql->execute();

		if (!$sql)
			return ["success" => false, "msg" => "Ocorreu um erro ao remover o link, tente novamente."];

		return ["success" => true, "msg" => "Link deletado com sucesso!"];
	}


}
?>