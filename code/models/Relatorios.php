<?php

ini_set('memory_limit', '-1');
set_time_limit(0);
date_default_timezone_set('America/Sao_Paulo');

require_once __DIR__ . '/../core/veltrac.php';

class Relatorios extends veltrac 
{

    public function getDadosListagemPassageiros($req)
    {

        $where= "";
        $and  = "";

        if(isset($req->nome) && $req->nome != ""){
            $where .= "controle_acessos.NOME LIKE '%{$req->nome}%'";
            $and    = " AND ";
        } 

        if(isset($req->matricula) && $req->matricula != ""){
            $where .= $and . " controle_acessos.MATRICULA_FUNCIONAL = {$req->matricula}";
            $and    = " AND ";
        } 

        if(isset($req->codigo) && $req->codigo != ""){
            $where .= $and . " controle_acessos.TAG = {$req->codigo}";
            $and    = " AND ";
        }

        if(isset($req->situacao)){
            if($req->situacao != 2){
                $where .= $and . " controle_acessos.ATIVO = {$req->situacao}";
                $and    = " AND ";
            }else{
                $where .= $and . " (controle_acessos.ATIVO = 1 OR controle_acessos.ATIVO = 0)";
                $and    = " AND ";
            }
            
        } else if (!isset($req->situacao))
        {
            $where .= $and . " controle_acessos.ATIVO = 1";
            $and    = " AND ";
        }

        $grupo = "";
        if(isset($req->grupo) && $req->grupo != ""){
            $grupo = $req->grupo;

            if(is_array($req->grupo))
                $grupo = implode(",", $req->grupo);
        } 

        if(isset($req->lns) && $req->lns != ""){
            $linhas = $req->lns;

            if(is_array($req->lns))
                $linhas = implode(",", $req->lns);

            $where .= $and . " ( LINHAIDA.ID_ORIGIN IN ({$linhas}) OR LINHAVOLTA.ID_ORIGIN IN ({$linhas}) )";
        } 
    
        $where .= $and . " controle_acessos.CONTROLE_ACESSO_GRUPO_ID IN ({$grupo})";

        $w   = ($where != "") ? " WHERE " . $where : "";

        $sql = "SELECT 
                    controle_acessos.id,
                    controle_acessos.ID_UNICO AS idUnico,
                    controle_acessos.NOME AS Nome,
                    TRIM(LEADING '0' FROM controle_acessos.TAG) AS Codigo,
                    acesso_grupos.NOME AS Grupo,
                    controle_acessos.cpf AS Cpf,
                    controle_acessos.MATRICULA_FUNCIONAL AS MatriculaFuncional,
                    controle_acessos.centro_custo AS CentroCusto,
                    controle_acessos.centro_custo AS Pol,
                    controle_acessos.ATIVO AS Status,
                    controle_acessos.residencia,
                    controle_acessos.monitor,
                    LINHAIDA.ID_ORIGIN AS idIda,
                    LINHAIDA.NOME AS NomeLinhaIda,
                    LINHAIDA.PREFIXO AS PrefixoIda,
                    INTIDA.TIPO AS IdaTipo,
                    INTIDA.SENTIDO AS IdaSentido,
                    INTIDA.TRECHO AS IdaTrecho,
                    INTIDA.DESCRICAO AS IdaDescricao,
                    LINHAVOLTA.ID_ORIGIN AS idVolta,
                    LINHAVOLTA.PREFIXO AS PrefixoVolta,
                    LINHAVOLTA.NOME AS NomeLinhaVolta,
                    INTVOLTA.TIPO AS VoltaTipo,
                    INTVOLTA.SENTIDO AS VoltaSentido,
                    INTVOLTA.TRECHO AS VoltaTrecho,
                    INTVOLTA.DESCRICAO AS VoltaDescricao,
                    (SELECT COUNT(*) AS tt FROM linhasAdicionais WHERE deleted_at is null AND controle_acesso_id = controle_acessos.id ) AS linhaAdd,
                
                    CASE
					    WHEN controle_acessos.ID_UNICO != 0 AND controle_acessos.ID_UNICO = controle_acessos.unidadeID
                        THEN 'SIM'
					    ELSE 'NÃO'
				    END AS CGFPASS

                FROM controle_acessos
                INNER JOIN acesso_grupos ON acesso_grupos.ID_ORIGIN = controle_acessos.CONTROLE_ACESSO_GRUPO_ID
                LEFT JOIN itinerarios AS INTIDA ON INTIDA.ID_ORIGIN = controle_acessos.ITINERARIO_ID_IDA
                LEFT JOIN linhas AS LINHAIDA ON LINHAIDA.ID_ORIGIN = INTIDA.LINHA_ID
                LEFT JOIN itinerarios AS INTVOLTA ON INTVOLTA.ID_ORIGIN = controle_acessos.ITINERARIO_ID_VOLTA
                LEFT JOIN linhas AS LINHAVOLTA ON LINHAVOLTA.ID_ORIGIN = INTVOLTA.LINHA_ID
                {$w} AND controle_acessos.deleted_at is null AND controle_acessos.user_type = 1 ORDER BY controle_acessos.Nome;";

            $sql2 = $this->db->prepare($sql);
            $sql2->execute();

            $array = $sql2->fetchAll();
            if (count($array) > 1)
            {
                $rep = array('Â°', 'Âº');
                foreach ($array as $k => $pax){
                    $array[$k]['Nome'] = (preg_match('!!u', utf8_decode($pax['Nome']))) ? utf8_decode($pax['Nome']) : $pax['Nome'];
                    $array[$k]['IdaDescricao'] = str_replace($rep, 'º', $pax['IdaDescricao']);
                    $array[$k]['VoltaDescricao'] = str_replace($rep, 'º', $pax['VoltaDescricao']);
                }
            }
        
        return $array;
    } 

    public function getDadosCartoesUtilizacao($req, $dash = false)
    {

        $data       = array();
        $datalimit  = date('Y-m-d', strtotime("- 7 days")) . " 00:00:00";
        $dtSt       = date('Y-m-d', strtotime("- 7 days"));
        $grupo      = "";

        if(isset($req->qtdDias) && $req->qtdDias != "")
            $datalimit = date('Y-m-d', strtotime("- " . $req->qtdDias . " days")) . " 00:00:00";

        if(isset($req->grupo) && $req->grupo != ""){
            $grupo = $req->grupo;

            if(is_array($req->grupo))
                $grupo = implode(",", $req->grupo);
        } 

        if(!$dash){

            // $sql = "SELECT 
            //             CA.TAG AS Codigo, 
            //             CA.NOME AS Nome, 
            //             CAG.NOME AS Grupo, 
            //             CA.cpf AS Cpf, 
            //             CA.MATRICULA_FUNCIONAL AS MatriculaFuncional, 
            //             CA.centro_custo AS CentroCusto, 
            //             CAE.DATAHORA AS DATAHORA
            //         FROM CONTROLE_ACESSO AS CA 
            //         LEFT JOIN CONTROLE_ACESSO_GRUPO AS CAG ON CAG.ID = CA.CONTROLE_ACESSO_GRUPO_ID 
            //         LEFT JOIN CONTROLE_ACESSO_EVENTOS AS CAE ON CAE.TAG = CA.TAG AND CAE.ID = (
            //             SELECT Max(ID) FROM CONTROLE_ACESSO_EVENTOS WHERE CONTROLE_ACESSO_EVENTOS.TAG = CA.TAG ) 
            //         WHERE CA.CONTROLE_ACESSO_GRUPO_ID IN ({$grupo})
            //         AND (CAE.DATAHORA < '{$datalimit}' OR CAE.DATAHORA IS NULL)
            //         AND CA.ATIVO = 1 ORDER BY CA.NOME, DATAHORA;";

            $sql = "
                WITH DistinctCA AS (
                    SELECT DISTINCT
                        TAG,
                        NOME,
                        CONTROLE_ACESSO_GRUPO_ID,
                        cpf,
                        MATRICULA_FUNCIONAL,
                        centro_custo,
                        ATIVO
                    FROM CONTROLE_ACESSO
                )
                SELECT 
                    CA.TAG AS Codigo, 
                    CA.NOME AS Nome, 
                    CAG.NOME AS Grupo, 
                    CA.cpf AS Cpf, 
                    CA.MATRICULA_FUNCIONAL AS MatriculaFuncional, 
                    CA.centro_custo AS CentroCusto, 
                    CAE.DATAHORA AS DATAHORA
                FROM DistinctCA AS CA 
                LEFT JOIN CONTROLE_ACESSO_GRUPO AS CAG ON CAG.ID = CA.CONTROLE_ACESSO_GRUPO_ID 
                LEFT JOIN CONTROLE_ACESSO_EVENTOS AS CAE ON CAE.TAG = CA.TAG AND CAE.ID = (
                    SELECT MAX(ID) 
                    FROM CONTROLE_ACESSO_EVENTOS 
                    WHERE CONTROLE_ACESSO_EVENTOS.TAG = CA.TAG
                ) 
                WHERE CA.CONTROLE_ACESSO_GRUPO_ID IN ({$grupo})
                AND (CAE.DATAHORA < '{$datalimit}' OR CAE.DATAHORA IS NULL)
                AND CA.ATIVO = 1 
                ORDER BY CA.NOME, DATAHORA;
            ";

            $consulta   =  $this->pdoSql->query($sql);
            $retorn     = $consulta->fetchAll();
            $data       = array();

            foreach($retorn AS $dts){
                $dts = (Object) $dts;
                $data[$dts->Codigo]['Codigo'] = $dts->Codigo;
                $data[$dts->Codigo]['Nome'] = $dts->Nome;
                $data[$dts->Codigo]['Grupo'] = $dts->Grupo;
                $data[$dts->Codigo]['Cpf'] = $dts->Cpf;
                $data[$dts->Codigo]['MatriculaFuncional'] = $dts->MatriculaFuncional;
                $data[$dts->Codigo]['CentroCusto'] = $dts->CentroCusto;
                $data[$dts->Codigo]['DATAHORA'] = $dts->DATAHORA;
            }

        } else {

            $hj   = date('Y-m-d');
            $data = array();
            $c    = 0;

            while($dtSt < $hj || $c < 7){

                $dataStart  = "{$dtSt} 00:00:00";
                $dataEnd    = "{$dtSt} 23:59:59";

                $sql = "SELECT 
                            (SELECT COUNT(*) AS T FROM CONTROLE_ACESSO_EVENTOS CAE WHERE CAE.DATAHORA BETWEEN '{$dataStart}' AND '{$dataEnd}' AND CAE.TAG = CA.TAG) AS MARK
                        FROM 
                        (SELECT DISTINCT TAG, CONTROLE_ACESSO_GRUPO_ID, ATIVO
                        FROM CONTROLE_ACESSO) AS CA
                        JOIN CONTROLE_ACESSO_GRUPO AS CAG ON CAG.ID = CA.CONTROLE_ACESSO_GRUPO_ID 
                        WHERE CA.CONTROLE_ACESSO_GRUPO_ID IN ({$grupo}) AND CA.ATIVO = 1;";
                
                $consulta   =  $this->pdoSql->query($sql);
                $count      = 0;

                $retorn = $consulta->fetchAll();

                foreach($retorn AS $ret) {
                    if ( $ret['MARK'] == 0) $count++;
                }
            
                $dtn        = date("d/m/Y", strtotime($dtSt));
                $data[$c]   = array($dtn, $count);

                $dtSt = date("Y-m-d", strtotime("+1 day", strtotime($dtSt)));
                $c++;
            }

        } 
       
        return $data;
    }

    public function getCardNotUsedGraphic($req)
    {

        $data       = array();
        $hj         = date('Y-m-d');
        $grupo      = $req->grupo;
        $dtSt       = date('Y-m-d', strtotime("- " . $req->qtdDias . " days"));
        $c          = 0;

        /**
         * Separa por dia
         */
        while($dtSt < $hj){

            $dataStart  = "{$dtSt} 00:00:00";
            $dataEnd    = "{$dtSt} 23:59:59";

            /**
             * Busca todas marcações do dia de cada Passageiro 
             * Busca todos passageiros para saber quem passou cartão e quem não passou
             */
            $sql = "SELECT 
                        (SELECT COUNT(*) AS T FROM CONTROLE_ACESSO_EVENTOS CAE WHERE CAE.DATAHORA BETWEEN '{$dataStart}' AND '{$dataEnd}' AND CAE.TAG = CA.TAG) AS MARK,
                        CA.TAG,
                        LIDA.ID AS IDLINHAIDA,
                        LIDA.NOME AS ITIDAPREV, 
                        LIDA.PREFIXO AS PREXIDA, 
                        LVOLTA.ID AS IDLINHAVOLTA,
                        LVOLTA.NOME AS ITVOLTAPREV, 
                        LVOLTA.PREFIXO AS PREXVOLTA 
                    FROM CONTROLE_ACESSO AS CA 
                    JOIN CONTROLE_ACESSO_GRUPO AS CAG ON CAG.ID = CA.CONTROLE_ACESSO_GRUPO_ID 
                    LEFT JOIN ITINERARIOS ITIDA ON ITIDA.ID = CA.ITINERARIO_ID_IDA 
                    LEFT JOIN LINHAS LIDA ON LIDA.ID = ITIDA.LINHA_ID 
                    LEFT JOIN ITINERARIOS ITVOLTA ON ITVOLTA.ID = CA.ITINERARIO_ID_VOLTA 
                    LEFT JOIN LINHAS LVOLTA ON LVOLTA.ID = ITVOLTA.LINHA_ID 
                    WHERE CA.CONTROLE_ACESSO_GRUPO_ID IN ({$grupo}) AND CA.ATIVO = 1;";
  
            $consulta = $this->pdoSql->query($sql);
            $retorn   = $consulta->fetchAll();

            $dtn      = date("d/m/Y", strtotime($dtSt));

            foreach($retorn AS $ret)
            {
                if ( $ret['MARK'] == 0) { 
                    /**
                     *  Se não teve marcação do passageiro no dia
                     */
                    if (isset($ret['IDLINHAIDA'])) {
                        $data[$dtn][$ret['IDLINHAIDA']]['NMLINHAIDA']  = $ret['PREXIDA'] . "  - " . $ret['ITIDAPREV'];
                        $data[$dtn][$ret['IDLINHAIDA']]['QTDLINHAIDA'] = 
                                isset($data[$dtn][$ret['IDLINHAIDA']]['QTDLINHAIDA']) ? ($data[$dtn][$ret['IDLINHAIDA']]['QTDLINHAIDA'] + 1) : 1;
                    }
                    
                    if (isset($ret['IDLINHAVOLTA'])) {
                        $data[$dtn][$ret['IDLINHAVOLTA']]['NMLINHAVOL']  = $ret['PREXVOLTA'] . "  - " . $ret['ITVOLTAPREV'];
                        $data[$dtn][$ret['IDLINHAVOLTA']]['QTDLINHAVOL'] = 
                                isset($data[$dtn][$ret['IDLINHAVOLTA']]['QTDLINHAVOL']) ? ($data[$dtn][$ret['IDLINHAVOLTA']]['QTDLINHAVOL'] + 1) : 1;
                    }

                } else  if ( $ret['MARK'] < 3) { 
                    /**
                     * Se a marcação for menor que 3 busca viagem ida e volta para ver qual das marcações não foram feitas
                     * Isso porque devem ser 4 marcações, mas o passageiro pode não marcar o embarque ou desembarque
                     */
                    $way = 0;

                    if (isset($ret['IDLINHAIDA']) || isset($ret['IDLINHAVOLTA']) )
                        $way = $this->getWayNotUseCard($ret['TAG'], $dtSt);

                    if ($way == 1) {

                        if (isset($ret['IDLINHAIDA'])) {
                            $data[$dtn][$ret['IDLINHAIDA']]['NMLINHAIDA']  = $ret['PREXIDA'] . "  - " . $ret['ITIDAPREV'];
                            $data[$dtn][$ret['IDLINHAIDA']]['QTDLINHAIDA'] = 
                                    isset($data[$dtn][$ret['IDLINHAIDA']]['QTDLINHAIDA']) ? ($data[$dtn][$ret['IDLINHAIDA']]['QTDLINHAIDA'] + 1) : 1;
                        }
                                
                    } else if ($way == 2) {

                        if (isset($ret['IDLINHAVOLTA'])) {
                            $data[$dtn][$ret['IDLINHAVOLTA']]['NMLINHAVOL']  = $ret['PREXVOLTA'] . "  - " . $ret['ITVOLTAPREV'];
                            $data[$dtn][$ret['IDLINHAVOLTA']]['QTDLINHAVOL'] = 
                                    isset($data[$dtn][$ret['IDLINHAVOLTA']]['QTDLINHAVOL']) ? ($data[$dtn][$ret['IDLINHAVOLTA']]['QTDLINHAVOL'] + 1) : 1;
                        }

                    }

                } 
                   
            }
        
            $dtSt = date("Y-m-d", strtotime("+1 day", strtotime($dtSt)));
        }

        return $data;
    }

    private function getWayNotUseCard($tag, $data)
    {
        /**
         * Verifica se teve 2
         */
        $dtI = $data . "00:00:00";
        $dtE = $data . "23:59:59";

        $sql = "SELECT * FROM BD_CLIENTE.dbo.CONTROLE_ACESSO_EVENTOS WHERE TAG = {$tag} AND DATAHORA BETWEEN '{$dtI}' AND '{$dtE}'";
        $con = $this->pdoSql->query($sql);
        $ret = $con->fetchAll();

        /**
         * Deve vir 1 ou 2 Resultados no máximo 
         */
        $car1 = isset($ret[0]['VEICULO_ID']) ? $ret[0]['VEICULO_ID'] : 0;
        $car2 = isset($ret[1]['VEICULO_ID']) ? $ret[1]['VEICULO_ID'] : 0;

        if ($car1 != $car2 || $car1 == 0) {
            /**
             * Significa que não bateu a entrada ou saída nos 2 veiculos
             */
            return 3;
        }

        /**
         * Se os 2 carros for iguais busca a viagem do carro e o sentido
         */
        $dtH = $ret[0]['DATAHORA'];

        $sql = "SELECT I.SENTIDO
                    FROM BD_CLIENTE.dbo.VIAGENS V
                    JOIN ITINERARIOS I ON I.ID = V.ITINERARIO_ID
                    WHERE V.VEICULO_ID = {$car1} AND '{$dtH}' BETWEEN V.DATAHORA_INICIAL_PREVISTO AND V.DATAHORA_FINAL_PREVISTO";

        $con = $this->pdoSql->query($sql);
        $ret = $con->fetchAll();

        if ($ret) // Se o Sentido for "0" no banco deles é ida, então retorna 1
            return $ret[0]['SENTIDO'] == 0 ? 1 : 2;
        
        return 0;
    }

    public function getDadosMapeamentoPassageiro($req)
    {

        ########### VERIFICA SE NÃO PREENCHEU A MATRICULA ########################
        if((!isset($req->registro) || $req->registro == "") && $req->nome != "")
        {
            $wh = "WHERE CA.NOME LIKE '%{$req->nome}%' AND CA.CONTROLE_ACESSO_GRUPO_ID = {$req->grupo}";
            
            $sql = "SELECT * FROM CONTROLE_ACESSO CA {$wh};";
          
            $consulta   =  $this->pdoSql->query($sql);    
            $data       = $consulta->fetchAll();

            if (count($data) > 1)
            {
                foreach ($data AS $t => $dts)
                {
                    foreach ($dts AS $k => $r)
                    {
                        if($k === 'NOME')
                        {
                            $data[$t][$k] = $r;
                            $data[$t][1] = $r;
                            
                        }
                    
                    }
                }
            
                return $data;

            } else if(count($data) == 1) {

                $req->registro = isset($data[0]['MATRICULA_FUNCIONAL']) ? $data[0]['MATRICULA_FUNCIONAL'] : "-";
            } else {
                $dataret = array();
                $dataret['error'] = true;
                $dataret['msg']   = "Nenhum resultado encontrado para o filtro informado!";
                return $dataret;

            }
        } 
        ##########################################################################

        $data = array();
        $where= "";
        $and  = "";

        if(isset($req->registro) && $req->registro != "" && $req->registro != "-"){

            $where .= $and . "CA.MATRICULA_FUNCIONAL = '{$req->registro}'";
            $and    = " AND "; 

        } else if($req->registro == "-"){

            $where .= $and . "CA.NOME = '{$req->nome}'";
            $and    = " AND "; 

        }

        if(isset($req->grupo) && $req->grupo != ""){
            $where .= $and . "CAG.ID = {$req->grupo}";
            $and    = " AND ";
        } 

        $days = 1;
        if(isset($req->dias) && $req->dias != ""){
            $days = $req->dias;
        } 

        $dataEnd    = date("Y-m-d") . " 23:59:59";
        $dateStart  = date("Y-m-d", strtotime("- {$days} days")) . " 00:00:00";
        $where      .= $and . "VI.DATAHORA_INICIAL_REALIZADO BETWEEN '{$dateStart}' AND '{$dataEnd}'";
        $w          = ($where != "") ? " WHERE " . $where : "";

        $sql = "SELECT
                VEIC.NOME AS PREF,
                VEIC.PLACA,
                VEIC.ID AS IDVEIC,
                CAG.NOME AS GRUPO,
                CA.TAG AS CODIGO,
                CA.NOME AS NOME,
                CA.MATRICULA_FUNCIONAL,
                LI.PREFIXO AS PREFIXOLINHA,
                LI.NOME AS NOMELINHA,
                I.TIPO AS TIPO,
                I.SENTIDO AS SENTIDO,
                I.DESCRICAO AS DESCRICAOINTINERARIO,
                VI.ID AS IDVIAGEM,
                VI.DATAHORA_INICIAL_REALIZADO AS DATAVIAGEMINIC,
                VI.DATAHORA_FINAL_REALIZADO AS DATAVIAGEMFIM,
                CAE.DATAHORA AS HORAMARCACAO,
                CA.centro_custo AS POL
                FROM CONTROLE_ACESSO_EVENTOS CAE
                LEFT JOIN VIAGENS VI ON VI.VEICULO_ID = CAE.VEICULO_ID AND CAE.DATAHORA BETWEEN VI.DATAHORA_INICIAL_REALIZADO AND VI.DATAHORA_FINAL_REALIZADO
                LEFT JOIN VEICULO VEIC ON VEIC.ID = CAE.VEICULO_ID
                LEFT JOIN CONTROLE_ACESSO CA ON CA.TAG = CAE.TAG
                LEFT JOIN CONTROLE_ACESSO_GRUPO CAG ON CAG.ID = CA.CONTROLE_ACESSO_GRUPO_ID
                LEFT JOIN ITINERARIOS AS I ON I.ID = VI.ITINERARIO_ID
                LEFT JOIN LINHAS AS LI ON LI.ID = I.LINHA_ID
                {$w} ORDER BY VI.DATAHORA_INICIAL_REALIZADO;";
   
        $consulta   =  $this->pdoSql->query($sql);    
        $data       = $consulta->fetchAll();
       
        foreach($data AS $k => $rel){
            $data[$k]['passageiros'] = $this->getPassageirosDasLinhasMapeadas($rel['IDVIAGEM'], $rel['MATRICULA_FUNCIONAL'], $rel['NOME']);

            $polUse = " - ";

            if(isset($rel['POL'])) 
            {
                $swp     = explode(";", $rel['POL']);
                $polUse  = isset($swp[0]) && $rel['SENTIDO'] == 0 ? $swp[0] : ( isset($swp[1]) && $rel['SENTIDO'] == 1 ? $swp[1] : " - " );
            }

            $data[$k]['POL'] = $polUse;
        }
   
        return $data;
    } 

    public function getPassageirosDasLinhasMapeadas($id, $matric, $name)
    {   

        if ($matric) 
        {
            $sql = "SELECT
                CA.TAG AS CODIGO,
                CA.NOME AS NOME,
                CA.MATRICULA_FUNCIONAL,
                VI.DATAHORA_INICIAL_REALIZADO AS DATAVIAGEM,
                CAE.DATAHORA AS HORAMARCACAO,
                CA.centro_custo AS POL
                FROM CONTROLE_ACESSO_EVENTOS CAE
                LEFT JOIN VIAGENS VI ON VI.VEICULO_ID = CAE.VEICULO_ID AND CAE.DATAHORA BETWEEN VI.DATAHORA_INICIAL_REALIZADO AND VI.DATAHORA_FINAL_REALIZADO
                LEFT JOIN CONTROLE_ACESSO CA ON CA.TAG = CAE.TAG
                WHERE VI.ID = '{$id}' AND CA.TAG IS NOT NULL 
                AND CA.MATRICULA_FUNCIONAL <> '{$matric}'
                ORDER BY VI.DATAHORA_INICIAL_REALIZADO;";
        } else {
            $sql = "SELECT
                CA.TAG AS CODIGO,
                CA.NOME AS NOME,
                CA.MATRICULA_FUNCIONAL,
                VI.DATAHORA_INICIAL_REALIZADO AS DATAVIAGEM,
                CAE.DATAHORA AS HORAMARCACAO,
                CA.centro_custo AS POL
                FROM CONTROLE_ACESSO_EVENTOS CAE
                LEFT JOIN VIAGENS VI ON VI.VEICULO_ID = CAE.VEICULO_ID AND CAE.DATAHORA BETWEEN VI.DATAHORA_INICIAL_REALIZADO AND VI.DATAHORA_FINAL_REALIZADO
                LEFT JOIN CONTROLE_ACESSO CA ON CA.TAG = CAE.TAG
                WHERE VI.ID = '{$id}' AND CA.TAG IS NOT NULL 
                AND CA.NOME <> '{$name}'
                ORDER BY VI.DATAHORA_INICIAL_REALIZADO;";
        }

        $consulta   =  $this->pdoSql->query($sql);
        $data       = $consulta->fetchAll();

        $passageiros= array();
  
        foreach($data AS $k => $rel){
            $rel    = (Object) $rel;
            $nome   = $rel->NOME != "" ? $rel->NOME : " - ";
            $matric = $rel->MATRICULA_FUNCIONAL;

            $passageiros[$k]['CODIGO']              = $rel->CODIGO;
            $passageiros[$k]['NOME']                = $rel->NOME;
            $passageiros[$k]['MATRICULA_FUNCIONAL'] = $rel->MATRICULA_FUNCIONAL;
            $passageiros[$k]['HORAMARCACAO']        = $rel->HORAMARCACAO;
            $passageiros[$k]['geral']               = $nome . " - Matricula: " . $matric . " - Data: ". date("d/m/Y H:i:s", strtotime($rel->HORAMARCACAO)) . ". POLTRONA: " . ( $rel->POL ? $rel->POL : "-");
            $passageiros[$k]['POL']                 = $rel->POL;
            
        }
      
        return $passageiros;
    } 

    public function getDadosConsolidadoViagem($req, $cad_pax_tag = 1, $dbSys = false, $usuario_id = false)
    {
        $data = array();
        $where= "";
        $and  = "";

        if(isset($req->lns) && $req->lns != ""){

            if(!is_array($req->lns))
                $lns = $req->lns;
            else 
                $lns = implode(',', $req->lns);

            $where .= $and . "i.LINHA_ID IN ({$lns})";
            $and    = " AND "; 

        }

        if (isset($req->sentido) && ($req->sentido != "" && $req->sentido != 0)) {
            $where .= $and . " i.SENTIDO = " . ($req->sentido == 1 ? 0 : 1);
            $and    = " AND "; 
        }

        $dateStart  = $req->data_inicio . " 00:00:00";
        $dataEnd    = $req->data_fim . " 23:59:59";

        $where      .= $and . "DATAHORA_INICIAL_PREVISTO BETWEEN '{$dateStart}' AND '{$dataEnd}'";
        $w          = ($where != "") ? " WHERE " . $where : "";

        $sqlTag1 = "0 AS PAXCADASTRADO, 0 AS PAXCADASTRADOV, 0 AS TIPO_USUARIO";
        $sqlTag2 = "";

        if($cad_pax_tag == 1){

            $grupo = $this->getGruposLogado($dbSys, $usuario_id);
        
            $w .= " AND (CAG.ID IN ({$grupo}) OR CAG.ID IS NULL)";

            $sqlTag1 = "(SELECT COUNT(DISTINCT ca.TAG) FROM CONTROLE_ACESSO ca WHERE ca.ITINERARIO_ID_IDA IN(SELECT ID FROM ITINERARIOS WHERE LINHA_ID = l.ID) AND ca.ATIVO = 1) AS PAXCADASTRADO,
            (SELECT COUNT(DISTINCT ca.TAG) FROM CONTROLE_ACESSO ca WHERE ca.ITINERARIO_ID_VOLTA IN(SELECT ID FROM ITINERARIOS WHERE LINHA_ID = l.ID) AND ca.ATIVO = 1) AS PAXCADASTRADOV,
            ca.TAG,
            ca.TIPO_USUARIO";

            $sqlTag2 = "LEFT JOIN CONTROLE_ACESSO_EVENTOS ca WITH(nolock) ON ca.VEICULO_ID = vc.ID AND ca.MOTORISTA_ID IS NULL
            AND ca.DATAHORA BETWEEN DATEADD(minute, -15, v.DATAHORA_INICIAL_REALIZADO) AND DATEADD(minute, 15, v.DATAHORA_FINAL_REALIZADO) 
            LEFT JOIN CONTROLE_ACESSO CAGR ON CAGR.TAG = ca.TAG
            LEFT JOIN CONTROLE_ACESSO_GRUPO CAG ON CAG.ID = CAGR.CONTROLE_ACESSO_GRUPO_ID";

        }
        
        $sql = "SELECT 
                l.GRUPO_LINHA_ID,
                v.ID AS IDVIAGEM,
                l.ID AS IDLINHA,
                v.ITINERARIO_ID,
                l.NOME AS NOMELINHA,
                gp.NOME AS GRUPO,
                l.PREFIXO AS PREFIXO,
                i.TIPO AS TIPO,
                i.SENTIDO AS SENTIDO,
                i.TRECHO AS TRECHO,
                i.DESCRICAO AS DESCRICAO,
                v.DATAHORA_INICIAL_REALIZADO AS DATAVIAGEM,
                v.DATAHORA_INICIAL_PREVISTO AS DATAINIPREVISTO,
                v.DATAHORA_INICIAL_REALIZADO AS DATAINIREAL,
                v.DATAHORA_FINAL_PREVISTO AS DATAFIMPREV,
                v.DATAHORA_FINAL_REALIZADO AS DATAFIMREAL,
                vc.ID AS IDVEIC,
                vc.PLACA AS PLACA,
                vc.NOME AS PREFIXOVEIC,
                vc.CAPACIDADE_PASSAGEIROS AS CAPACIDADEVEIC,
                vc.CAPACIDADE_LIMIT_PASSAGEIROS AS LIMITEVEIC,
                COALESCE(v.DISTANCIA_PERCORRIDA, (v.DISTANCIA_PERCORRIDA / 1000), 0) AS KMVIAGEM,
                {$sqlTag1}
        FROM BD_CLIENTE.dbo.VIAGENS v
        JOIN ITINERARIOS i ON i.ID = v.ITINERARIO_ID
        JOIN LINHAS l ON l.ID = i.LINHA_ID
        JOIN VEICULO vc ON vc.ID = v.VEICULO_ID
        JOIN GRUPO_LINHAS gp ON gp.ID = l.GRUPO_LINHA_ID
        {$sqlTag2}
        {$w} 
        ORDER BY DATAHORA_INICIAL_PREVISTO;";
        
        $consulta = $this->pdoSql->query($sql);  

        if($consulta){

            $data = $consulta->fetchAll();

        }
            
        return $data;   
    }

    public function getDadosSintetico($req, $cad_pax_tag = 1, $dbSys = false, $usuario_id = false)
    {

        $data = array();

        $dateStart  = $req->data_inicio . " 00:00:00";
        $dataEnd    = $req->data_fim . " 23:59:59";
        
        $w   = " WHERE DATAHORA_INICIAL_PREVISTO BETWEEN '{$dateStart}' AND '{$dataEnd}' AND i.LINHA_ID IN ({$req->lns})";

        if (isset($req->sentido) && $req->sentido != "")
            $w .= " AND i.SENTIDO = " . ($req->sentido == 1 ? 0 : 1);

        
        $sqlTag1 = "0 AS PAXCADASTRADO, 0 AS PAXCADASTRADOV, 0 AS TIPO_USUARIO";
        $sqlTag2 = "";
        
        if($cad_pax_tag == 1){

            $grupo = $this->getGruposLogado($dbSys, $usuario_id);
    
            $w .= " AND (CAG.ID IN ({$grupo}) OR CAG.ID IS NULL)";

            // $sqlTag1 = "(SELECT COUNT(DISTINCT ca.TAG) FROM CONTROLE_ACESSO ca WHERE ca.ITINERARIO_ID_IDA = v.ITINERARIO_ID AND ca.ATIVO = 1) AS PAXCADASTRADO,
            // (SELECT COUNT(DISTINCT ca.TAG) FROM CONTROLE_ACESSO ca WHERE ca.ITINERARIO_ID_VOLTA = v.ITINERARIO_ID AND ca.ATIVO = 1) AS PAXCADASTRADOV,
            // ca.TAG,
            // ca.TIPO_USUARIO";

            $sqlTag1 = "(SELECT COUNT(DISTINCT ca.TAG) FROM CONTROLE_ACESSO ca WHERE ca.ITINERARIO_ID_IDA IN(SELECT ID FROM ITINERARIOS WHERE LINHA_ID = l.ID) AND ca.ATIVO = 1) AS PAXCADASTRADO,
            (SELECT COUNT(DISTINCT ca.TAG) FROM CONTROLE_ACESSO ca WHERE ca.ITINERARIO_ID_VOLTA IN(SELECT ID FROM ITINERARIOS WHERE LINHA_ID = l.ID) AND ca.ATIVO = 1) AS PAXCADASTRADOV,
            ca.TAG,
            ca.TIPO_USUARIO";

            $sqlTag2 = "LEFT JOIN CONTROLE_ACESSO_EVENTOS ca WITH(nolock) ON ca.VEICULO_ID = vc.ID AND ca.MOTORISTA_ID IS NULL
            AND ca.DATAHORA BETWEEN DATEADD(minute, -15, v.DATAHORA_INICIAL_REALIZADO) AND DATEADD(minute, 15, v.DATAHORA_FINAL_REALIZADO) 
            LEFT JOIN CONTROLE_ACESSO CAGR ON CAGR.TAG = ca.TAG
            LEFT JOIN CONTROLE_ACESSO_GRUPO CAG ON CAG.ID = CAGR.CONTROLE_ACESSO_GRUPO_ID";

        }
        

        $sql = "SELECT 
                l.GRUPO_LINHA_ID,
                v.ID AS IDVIAGEM,
                l.ID AS IDLINHA,
                v.ITINERARIO_ID,
                l.NOME AS NOMELINHA,
                gp.NOME AS GRUPO,
                l.PREFIXO AS PREFIXO,
                i.TIPO AS TIPO,
                i.SENTIDO AS SENTIDO,
                i.TRECHO AS TRECHO,
                i.DESCRICAO AS DESCRICAO,
                v.DATAHORA_INICIAL_REALIZADO AS DATAVIAGEM,
                v.DATAHORA_INICIAL_PREVISTO AS DATAINIPREVISTO,
                v.DATAHORA_INICIAL_REALIZADO AS DATAINIREAL,
                v.DATAHORA_FINAL_PREVISTO AS DATAFIMPREV,
                v.DATAHORA_FINAL_REALIZADO AS DATAFIMREAL,
                vc.ID AS IDVEIC,
                vc.PLACA AS PLACA,
                vc.NOME AS PREFIXOVEIC,
                vc.CAPACIDADE_PASSAGEIROS AS CAPACIDADEVEIC,
                vc.CAPACIDADE_LIMIT_PASSAGEIROS AS LIMITEVEIC,
                COALESCE(v.DISTANCIA_PERCORRIDA, (v.DISTANCIA_PERCORRIDA / 1000), 0) AS KMVIAGEM,
                {$sqlTag1}
        FROM BD_CLIENTE.dbo.VIAGENS v
        JOIN ITINERARIOS i ON i.ID = v.ITINERARIO_ID
        JOIN LINHAS l ON l.ID = i.LINHA_ID
        JOIN VEICULO vc ON vc.ID = v.VEICULO_ID
        JOIN GRUPO_LINHAS gp ON gp.ID = l.GRUPO_LINHA_ID
        {$sqlTag2}
        {$w} 
        ORDER BY DATAHORA_INICIAL_PREVISTO;";
  
        $consulta = $this->pdoSql->query($sql);  

        if($consulta){

            $data = $consulta->fetchAll();

        } 
            
        return $data;  
    }

    public function getDadosRotasUser($itiID)
    {

        try {
            $pdo = new \PDO ("dblib:host=$this->host:$this->port;dbname=$this->dbName;charset=utf8","$this->user","$this->pass");
        } catch (\Throwable $th) {
            $error =array('error' => true, 'msg'=>'Ocorreu um erro ao tentar conectar ao Banco de Dados.');
            return $error;
        }

        $sql = "SELECT DISTINCT pr.ID AS IDPONTOREF,
                    pr.LATITUDE AS LATITUDE,
                    pr.LONGITUDE AS LONGITUDE,
                    l.PREFIXO AS PREFLINHA,
                    l.NOME AS NOMELINHA,
                    l.ID AS IDLINHA,
                    l.GRUPO_LINHA_ID AS GRUPO_LINHA_ID,
                    i.DESCRICAO AS DESCRICAO,
                    pti.ITINERARIO_ID AS IDITIN,
                    i.CODIGO_INTEGRACAO
                    FROM PONTOS_REFERENCIA pr
                    INNER JOIN PONTOS_ITINERARIO pti ON pti.PONTO_REFERENCIA_ID = pr.ID
                    INNER JOIN ITINERARIOS i ON i.ID = pti.ITINERARIO_ID
                    INNER JOIN LINHAS l ON l.ID = i.LINHA_ID
                WHERE pti.ITINERARIO_ID = {$itiID};";
  
        $consulta   = $pdo->query($sql);
        $retorn     = $consulta->fetchAll();
        $data       = array();

        foreach($retorn AS $ret){
            $ret = (Object) $ret;

            $data[$ret->IDLINHA]['PREF']            = $ret->PREFLINHA;
            $data[$ret->IDLINHA]['LINHA']           = $ret->NOMELINHA;
            $data[$ret->IDLINHA]['ITINE']           = $ret->IDITIN;
            $data[$ret->IDLINHA]['DESCRICAO']       = $ret->DESCRICAO;
            $data[$ret->IDLINHA]['GRUPO_LINHA_ID']  = $ret->GRUPO_LINHA_ID;
            $data[$ret->IDLINHA]['CODIGO_INTEGRACAO'] = $ret->CODIGO_INTEGRACAO;
            $data[$ret->IDLINHA]['pontosIt']        = $this->getPontosItinerario($ret->CODIGO_INTEGRACAO);
        }

        foreach($data AS $k => $v){
            $v = (Object) $v;
            $data[$k]['PONTOS'] = $this->getAllPoints($v->ITINE, $pdo, $v->GRUPO_LINHA_ID);
        }

        return $data;
    }

    public function getPontosItinerario($codInteg)
    {

        $conn = false;
    
        try {
          $tns = "(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(HOST = {$this->hostGl})(PORT = {$this->portGl})) (CONNECT_DATA = (SID = orcl)))";
          $conn = new \PDO("oci:dbname=".$tns . ';charset=UTF8', $this->userGl, $this->passGl);
          $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\Throwable $th) {
            $error =array('error' => true, 'msg'=>'Ocorreu um erro ao tentar conectar ao Banco de Dados, tente novamente.');
            //$this->error($th);
            return false;
        }

        if ($conn)
        {
            $sth= $conn->prepare("SELECT TRAJETO FROM GLOBUS.FREM_ITINERARIOS WHERE CODIGO = '{$codInteg}' AND ROWNUM <= 2");
            $sth->execute();
            $rows = $sth->fetchAll();

            foreach( $rows AS $k => $r )
            {
                $rows[$k]['TRAJETO'] = str_replace("\n", "</br>",  $r['TRAJETO']);

            }
    
            return $rows;
        } 
        
        return false;
    }

    public function getDadosRotas($req, $lat, $lng, $lat2, $lon2, $all = false)
    {

        try {
            $pdo = new \PDO ("dblib:host=$this->host:$this->port;dbname=$this->dbName;charset=utf8","$this->user","$this->pass");
        } catch (\Throwable $th) {
            $error =array('error' => true, 'msg'=>'Ocorreu um erro ao tentar conectar ao Banco de Dados, tente novamente.');
            return $error;
        }

        $w = "l.GRUPO_LINHA_ID = {$req->ic} ";

        if (!$all)
        {
            $w .= " AND pr.LATITUDE BETWEEN '{$lat}' AND '{$lat2}' AND pr.LONGITUDE BETWEEN '{$lng}' AND '{$lon2}'";
        }

        $sql = "SELECT DISTINCT pr.ID AS IDPONTOREF,
                    pr.LATITUDE AS LATITUDE,
                    pr.LONGITUDE AS LONGITUDE,
                    l.PREFIXO AS PREFLINHA,
                    l.NOME AS NOMELINHA,
                    l.ID AS IDLINHA,
                    l.GRUPO_LINHA_ID AS GRUPO_LINHA_ID,
                    i.DESCRICAO AS DESCRICAO,
                    i.SENTIDO,
                    pti.ITINERARIO_ID AS IDITIN,
                    i.CODIGO_INTEGRACAO
                    FROM PONTOS_REFERENCIA pr
                    INNER JOIN PONTOS_ITINERARIO pti ON pti.PONTO_REFERENCIA_ID = pr.ID
                    INNER JOIN ITINERARIOS i ON i.ID = pti.ITINERARIO_ID
                    INNER JOIN LINHAS l ON l.ID = i.LINHA_ID 
                WHERE {$w} AND i.ATIVO = 1 AND l.ATIVO = 1;";
  
        $consulta   = $pdo->query($sql);
        $retorn     = $consulta->fetchAll();
        $data     = array();

        foreach($retorn AS $ret){
            $ret = (Object) $ret;

            $data[$ret->IDLINHA]['PREF']  = $ret->PREFLINHA;
            $data[$ret->IDLINHA]['LINHA'] = $ret->NOMELINHA . ( $ret->SENTIDO == 0 ? ' - ENTRADA' : ' - RETORNO' );
            $data[$ret->IDLINHA]['ITINE'] = $ret->IDITIN;
            $data[$ret->IDLINHA]['DESCRICAO'] = $ret->DESCRICAO;
            $data[$ret->IDLINHA]['CODIGO_INTEGRACAO'] = $ret->CODIGO_INTEGRACAO;
        }

        foreach($data AS $k => $v){
            $v = (Object) $v;
            $data[$k]['PONTOS'] = $this->getAllPoints($v->ITINE,$pdo, $req->ic);
            $data[$k]['TRAJETO'] = $this->getTrajeto($v->ITINE,$pdo, $req->ic);
        }

        return $data;
    }

    private function getTrajeto($idIti, $pdo, $ic){
        $sql = "SELECT tra.trajeto
        FROM itinerario_trajeto tra
        INNER JOIN ITINERARIOS i ON i.ID = {$idIti}
        INNER JOIN LINHAS l ON l.ID = i.LINHA_ID
        WHERE tra.itinerario_id = {$idIti} AND l.GRUPO_LINHA_ID = {$ic}
        AND i.ATIVO = 1 AND l.ATIVO = 1;";
  
        $consulta   = $pdo->query($sql);
        $retorn     = $consulta->fetchAll();
        return $retorn;
    }

    public function getAllPoints($idIti, $pdo, $ic)
    {
  
        $sql = "SELECT 
        pr.ID AS IDPONTOREF,
        pr.LATITUDE AS LATITUDE,
        pr.LONGITUDE AS LONGITUDE,
        pr.NOME
        FROM PONTOS_REFERENCIA pr
        INNER JOIN PONTOS_ITINERARIO pti ON pti.PONTO_REFERENCIA_ID = pr.ID
        INNER JOIN ITINERARIOS i ON i.ID = pti.ITINERARIO_ID
        INNER JOIN LINHAS l ON l.ID = i.LINHA_ID
        WHERE pti.ITINERARIO_ID = {$idIti} AND l.GRUPO_LINHA_ID = {$ic}
        AND i.ATIVO = 1 AND l.ATIVO = 1
        ORDER BY pti.SEQUENCIA ASC;";
  
        $consulta   = $pdo->query($sql);
        $retorn     = $consulta->fetchAll();
        return $retorn;
    }

    public function getGruposLogado($dbSys = false, $usuario_id = false){

        $dbConn = $dbSys ? $dbSys : $this->db;
        $cLogin = $usuario_id ? $usuario_id : $_SESSION['cLogin'];

        $sqlGrupo = $dbConn->prepare("SELECT acesso_grupos.ID_ORIGIN
                                        FROM  acesso_grupos
                                        INNER JOIN usuario_grupos ON grupo_id = acesso_grupos.id
                                        WHERE usuario_grupos.usuario_id = {$cLogin}");
        $sqlGrupo->execute();
        $grupoOk = $sqlGrupo->fetchAll();
        $grupoOkIn  = array();

        foreach($grupoOk AS $grOk)
        {
            $grupoOkIn[] = $grOk['ID_ORIGIN'];
        }

        return implode(",", $grupoOkIn);
    }

    public function checkTagGroup($dbSys = false, $usuario_id = false, $tag){

        $paxOk = array('inGroup' => false, 'pax' => false);

        $dbConn = $dbSys ? $dbSys : $this->db;

        $grupoConfirm = $this->getGruposLogado($dbSys, $usuario_id);
        $grupoConfirm = explode(",", $grupoConfirm);

        $sql = $dbConn->prepare("SELECT controle_acessos.*, acesso_grupos.NOME AS NOMEGRUPO
            FROM controle_acessos 
            LEFT JOIN acesso_grupos ON acesso_grupos.ID_ORIGIN = controle_acessos.CONTROLE_ACESSO_GRUPO_ID
            WHERE TRIM(LEADING '0' FROM TAG) = TRIM(LEADING '0' FROM '{$tag}') 
            AND controle_acessos.deleted_at IS NULL 
            ORDER BY controle_acessos.created_at DESC
            LIMIT 1");


        $sql->execute();
        $pax = $sql->fetch(PDO::FETCH_OBJ);

        if($pax){

            $paxOk['inGroup'] = in_array($pax->CONTROLE_ACESSO_GRUPO_ID, $grupoConfirm) ? true : false;
            $paxOk['pax'] = $pax;
                       
        }

        return $paxOk;

    }

    public function checkTagGroupEventos($dbSys = false, $usuario_id = false, $tag){

        $inGroup = true;

        if(trim($tag) == '' || trim($tag) == null){
            return $inGroup;
        }

        $dbConn = $dbSys ? $dbSys : $this->db;

        $grupoConfirm = $this->getGruposLogado($dbSys, $usuario_id);
        $grupoConfirm = explode(",", $grupoConfirm);

        $sql = $dbConn->prepare("SELECT controle_acessos.*, acesso_grupos.NOME AS NOMEGRUPO
        FROM controle_acessos 
        LEFT JOIN acesso_grupos ON acesso_grupos.ID_ORIGIN = controle_acessos.CONTROLE_ACESSO_GRUPO_ID
        WHERE TAG IS NOT NULL AND TRIM(LEADING '0' FROM TAG) = TRIM(LEADING '0' FROM '{$tag}') AND controle_acessos.ATIVO = 1 AND controle_acessos.deleted_at IS NULL LIMIT 1");

        $sql->execute();
        $pax = $sql->fetch(PDO::FETCH_OBJ);
        
        if($pax){

            $inGroup = in_array($pax->CONTROLE_ACESSO_GRUPO_ID, $grupoConfirm) ? true : false;
                       
        }

        return $inGroup;

    }

    public function getTagByMatricula($dbSys = false, $usuario_id = false, $matricula){

        $tag = false;

        $dbConn = $dbSys ? $dbSys : $this->db;

        $grupoConfirm = $this->getGruposLogado($dbSys, $usuario_id);

        $sql = $dbConn->prepare("SELECT controle_acessos.TAG FROM controle_acessos 
        WHERE (TRIM(LEADING '0' FROM MATRICULA_FUNCIONAL) = TRIM(LEADING '0' FROM '{$matricula}')
        OR TRIM(LEADING '0' FROM TAG) = TRIM(LEADING '0' FROM '{$matricula}'))
        AND CONTROLE_ACESSO_GRUPO_ID IN($grupoConfirm)
        AND controle_acessos.ATIVO = 1 
        AND controle_acessos.deleted_at IS NULL LIMIT 1");

        $sql->execute();
        $pax = $sql->fetch(PDO::FETCH_OBJ);

        if($pax){
            $tag = $pax->TAG;
        }

        return $tag;

    }

    // public function getDadosAnaliticoPassageiro($req, $viagemID, $dbSys = false, $usuario_id = false)
    // {

    //     $dbConn = $dbSys ? $dbSys : $this->db;

    //     //inicia variáveis do sql
    //     $grupo          = "";
    //     $matric         = "";
    //     $todosGrupos    = "";
    //     $lns            = "";
    //     $v              = "";

    //     if(isset($req->grupo) && $req->grupo != ""){
    //         $grupo = $req->grupo;

    //         if(is_array($req->grupo))
    //             $grupo = implode(",", $req->grupo);

    //     }else{

    //         $grupo = $this->getGruposLogado($dbSys, $usuario_id);
            
    //     } 

    //     if(isset($req->todosGrupos) && $req->todosGrupos == 1){
    //         $todosGrupos = "OR CAG.ID IS NULL";     
    //     }

    //     if(isset($req->matricula) && $req->matricula != ""){

    //         $tag = $this->getTagByMatricula($dbSys, $usuario_id, $req->matricula);
            
    //         if($tag){
    //             $matric = " AND CAE.TAG = '{$tag}'";
    //         }else{
    //             $matric = " AND CA.MATRICULA_FUNCIONAL = '{$req->matricula}'";
    //         }

    //     } 
        
    //     if(isset($req->lns) && $req->lns != "")
    //     {
    //         $lns = " AND LI.ID IN ({$req->lns})";

    //         if(is_array($req->lns))
    //             $lns = " AND LI.ID IN (".implode(",", $req->lns).")";

    //     }

    //     if ($viagemID > 0){

    //         $v = " AND VI.ID = {$viagemID}";

    //         $grupo = $this->getGruposLogado($dbSys, $usuario_id);
            
    //     }
            
    //     $allData = array();

    //     $data_inicio = new DateTime($req->data_inicio);
    //     $data_fim = new DateTime($req->data_fim);

    //     $intervalo = new DateInterval('P1D');

    //     $periodo = new DatePeriod($data_inicio, $intervalo, $data_fim->modify('+1 day'));

    //     foreach ($periodo as $data) {

    //         $positions = "POSICOES_".$data->format('Y_m');
    //         $start = $data->format('Y-m-d 00:00:00');
    //         $end = $data->format('Y-m-d 23:59:00');
            
    //         if ($v != "")
    //             $wp = "CAE.TIPO_USUARIO = 2 {$matric} {$v} {$lns} AND (CAG.ID IN ({$grupo}) {$todosGrupos})";
    //         else 
    //             $wp = "CAE.TIPO_USUARIO = 2 AND VI.DATAHORA_INICIAL_PREVISTO BETWEEN '{$start}' AND '{$end}' {$matric} {$lns} AND (CAG.ID IN ({$grupo}) {$todosGrupos})";

    //         $sql = "SELECT 
    //             VEIC.NOME AS PREF, 
    //             VEIC.PLACA,
    //             CAG.NOME AS GRUPO, 
    //             CAE.TAG AS CODIGO, 
    //             CA.NOME, 
    //             CA.MATRICULA_FUNCIONAL AS MATRICULA, 
    //             CA.ATIVO, 
    //             CA.CONTROLE_ACESSO_GRUPO_ID AS HASGROUP,
    //             CAE.LATITUDE,
    //             CAE.LONGITUDE, 
    //             CAE.DATAHORA AS DATAHORACAE, 
    //             POS.LOGRADOURO AS LOGRADOURO, 
    //             CONCAT(CI.MUNICIPIO, ' - ', CI.ESTADO) AS LOCALIZACAO, 
    //             LIDA.NOME AS ITIDAPREV, 
    //             LIDA.PREFIXO AS PREXIDA, 
    //             ITIDA.DESCRICAO AS DESCIDAPREV, 
    //             LVOLTA.NOME AS ITVOLTAPREV, 
    //             LVOLTA.PREFIXO AS PREXVOLTA, 
    //             ITVOLTA.DESCRICAO AS DESCVOLTAPREV, 
    //             LI.NOME AS ITIREALIZADO, 
    //             LI.PREFIXO AS LIREALIZADA, 
    //             IT.SENTIDO AS SENTREALIZADO, 
    //             IT.DESCRICAO AS DESCRICAOREAL, 
    //             VI.DATAHORA_INICIAL_REALIZADO AS DATAREALIZADO, 
    //             CA.ITINERARIO_ID_IDA AS IDAITINEIDA, 
    //             CA.ITINERARIO_ID_VOLTA AS IDAITINEVOLTA, 
    //             VI.ID AS IDVIAGEM, 
    //             VI.ITINERARIO_ID AS IDTINEREAL, 
    //             VI.VEICULO_ID, 
    //             IT.SENTIDO,LI.ID as IDLINHA,
    //             '0' AS DistanciaKM, 
    //             PTSR.NOME AS PontoRef
    //         FROM CONTROLE_ACESSO_EVENTOS AS CAE WITH(nolock) 
    //         JOIN VEICULO VEIC ON VEIC.ID = CAE.VEICULO_ID 
    //         LEFT JOIN CONTROLE_ACESSO CA ON CA.TAG = CAE.TAG
    //         JOIN $positions POS WITH(nolock) ON POS.COMUNICACAO_ID = CAE.POSICAO_ID
    //         LEFT JOIN CONTROLE_ACESSO_GRUPO CAG ON CAG.ID = CA.CONTROLE_ACESSO_GRUPO_ID 
    //         JOIN VIAGENS VI ON VI.VEICULO_ID = CAE.VEICULO_ID AND CAE.DATAHORA BETWEEN DATEADD(minute, -15, VI.DATAHORA_INICIAL_REALIZADO) AND DATEADD(minute, 15, VI.DATAHORA_FINAL_REALIZADO) 
    //         JOIN ITINERARIOS IT ON IT.ID = VI.ITINERARIO_ID 
    //         JOIN LINHAS LI ON LI.ID = IT.LINHA_ID 
    //         LEFT JOIN ITINERARIOS ITIDA ON ITIDA.ID = CA.ITINERARIO_ID_IDA 
    //         LEFT JOIN LINHAS LIDA ON LIDA.ID = ITIDA.LINHA_ID 
    //         LEFT JOIN ITINERARIOS ITVOLTA ON ITVOLTA.ID = CA.ITINERARIO_ID_VOLTA 
    //         LEFT JOIN LINHAS LVOLTA ON LVOLTA.ID = ITVOLTA.LINHA_ID 
    //         LEFT JOIN CIDADES CI ON CI.ID = POS.CIDADE 
    //         LEFT JOIN HORARIOS_VIAGEM HV ON HV.VIAGEM_ID = VI.ID AND HV.PONTO_ITINERARIO_ID = ( 
    //         CASE WHEN 
    //             (SELECT TOP 1 PONTO_ITINERARIO_ID FROM HORARIOS_VIAGEM WHERE VIAGEM_ID = VI.ID AND CAE.DATAHORA > DATAHORA_ENTRADA_REALIZADO ORDER BY DATAHORA_ENTRADA_REALIZADO DESC) > 0
    //         THEN 
    //             (SELECT TOP 1 PONTO_ITINERARIO_ID FROM HORARIOS_VIAGEM WHERE VIAGEM_ID = VI.ID AND CAE.DATAHORA > DATAHORA_ENTRADA_REALIZADO ORDER BY DATAHORA_ENTRADA_REALIZADO DESC)
    //         ELSE 
    //             (SELECT TOP 1 PONTO_ITINERARIO_ID FROM HORARIOS_VIAGEM WHERE VIAGEM_ID = VI.ID ORDER BY PONTO_ITINERARIO_ID)
    //         END)
    //         LEFT JOIN PONTOS_ITINERARIO PTSI ON PTSI.ID = HV.PONTO_ITINERARIO_ID 
    //         LEFT JOIN PONTOS_REFERENCIA PTSR ON PTSR.ID = PTSI.PONTO_REFERENCIA_ID 
    //         WHERE {$wp}
    //         ORDER BY CAE.DATAHORA";

    //         $consulta   = $this->pdoSql->query($sql);   
    //         $datas      = $consulta->fetchAll();
            
    //         foreach($datas AS $dat){

    //             $dat = (object) $dat;

    //             $dat->PREVOK = 'NREALIZADO';
                
    //             $dat->ITIREALIZADOOK = $dat->ITIREALIZADO != $dat->DESCRICAOREAL ? 
    //             $dat->ITIREALIZADO .' - '. $dat->DESCRICAOREAL : 
    //             $dat->ITIREALIZADO;
                
    //             $dat->GRUPO = $dat->HASGROUP ? $dat->GRUPO : "-";

    //             if(!$dat->NOME){
                   
    //                 $pax = $this->checkTagGroup($dbSys, $usuario_id, $dat->CODIGO);

    //                 if($pax['pax']){
                        
    //                     if(!$pax['inGroup']){

    //                         continue;
    
    //                     }else{
    
    //                         $dat->NOME = $pax['pax']->NOME;
    //                         $dat->ATIVO = $pax['pax']->ATIVO;
    //                         $dat->MATRICULA = $pax['pax']->MATRICULA_FUNCIONAL;
    //                         $dat->CENTROCUSTO = $pax['pax']->centro_custo;
    
    //                         if($pax['pax']->NOMEGRUPO){
    //                             $dat->GRUPO = $pax['pax']->NOMEGRUPO;
    //                         }
    
    //                     } 

    //                 }
                    
    //             }

    //             $dat->STATUSCAD = isset($dat->ATIVO) ? ($dat->ATIVO ? "ATIVO" : "INATIVO") : "SEM CADASTRO";

    //             $rep = array('Â°', 'Âº');

    //             if($dat->SENTREALIZADO == 0){

    //                 $idItiIda = 0;

    //                 if(!$dat->IDAITINEIDA){

    //                     $sql = $dbConn->prepare("SELECT ITINERARIO_ID_IDA FROM controle_acessos 
    //                     WHERE TRIM(LEADING '0' FROM TAG) = TRIM(LEADING '0' FROM '{$dat->CODIGO}')");
    
    //                     $sql->execute();
    //                     $itIdaId = $sql->fetch(PDO::FETCH_OBJ);

    //                     if($itIdaId){
    //                         $idItiIda = $itIdaId->ITINERARIO_ID_IDA;
    //                         $sql = $dbConn->prepare("SELECT DESCRICAO FROM itinerarios 
    //                         WHERE ID_ORIGIN = '{$idItiIda}'");
    //                         $sql->execute();
    //                         $descIda = $sql->fetch(PDO::FETCH_OBJ);

    //                         if($descIda){
    
    //                             $descIdaOk = str_replace($rep, 'º', $descIda->DESCRICAO);
    //                             $dat->ITIDAPREV = $descIdaOk;
    
    //                         }
                            
    //                     }
                        

    //                 }else{

    //                     $descIdaOk = str_replace($rep, 'º', $dat->DESCIDAPREV);
    //                     $dat->ITIDAPREV = $descIdaOk;
    //                     $idItiIda = $dat->IDAITINEIDA;

    //                 }

    //                 if($idItiIda != 0){

    //                     if($idItiIda != $dat->IDTINEREAL){
    //                         $sql = $dbConn->prepare("SELECT ITINERARIO_ID_PAI FROM itinerarios 
    //                         WHERE ID_ORIGIN = '{$dat->IDTINEREAL}'");
    //                         $sql->execute();
    //                         $idPai = $sql->fetch(PDO::FETCH_OBJ);
                            
    //                         if($idPai){
    //                             $dat->IDTINEREAL = $idPai->ITINERARIO_ID_PAI;
    //                         }
    //                     }
                        
    //                     $dat->PREVOK = $idItiIda==$dat->IDTINEREAL ? "PREV" : "NPREV";

    //                     if($dat->PREVOK == 'PREV'){
    //                         $dat->ITIDAPREV = $dat->ITIREALIZADOOK;
    //                     }
    //                 }                    

    //             }

    //             if($dat->SENTREALIZADO == 1){

    //                 $idItiVolta = 0;
                
    //                 if(!$dat->IDAITINEVOLTA){
                
    //                     $sql = $dbConn->prepare("SELECT ITINERARIO_ID_VOLTA FROM controle_acessos 
    //                     WHERE TRIM(LEADING '0' FROM TAG) = TRIM(LEADING '0' FROM '{$dat->CODIGO}')");
                
    //                     $sql->execute();
    //                     $itVoltaId = $sql->fetch(PDO::FETCH_OBJ);
                
    //                     if($itVoltaId){
    //                         $idItiVolta = $itVoltaId->ITINERARIO_ID_VOLTA;
    //                         $sql = $dbConn->prepare("SELECT DESCRICAO FROM itinerarios 
    //                         WHERE ID_ORIGIN = '{$idItiVolta}'");
    //                         $sql->execute();
    //                         $descVolta = $sql->fetch(PDO::FETCH_OBJ);
                
    //                         if($descVolta){
                
    //                             $descVoltaOk = str_replace($rep, 'º', $descVolta->DESCRICAO);
    //                             $dat->ITVOLTAPREV = $descVoltaOk;
                
    //                         }
                            
    //                     }
                        
                
    //                 }else{
                
    //                     $descVoltaOk = str_replace($rep, 'º', $dat->DESCVOLTAPREV);
    //                     $dat->ITVOLTAPREV = $descVoltaOk;
    //                     $idItiVolta = $dat->IDAITINEVOLTA;
                
    //                 }
                
    //                 if($idItiVolta != 0){

    //                     if($idItiVolta != $dat->IDTINEREAL){
    //                         $sql = $dbConn->prepare("SELECT ITINERARIO_ID_PAI FROM itinerarios 
    //                         WHERE ID_ORIGIN = '{$dat->IDTINEREAL}'");
    //                         $sql->execute();
    //                         $idPai = $sql->fetch(PDO::FETCH_OBJ);
                            
    //                         if($idPai){
    //                             $dat->IDTINEREAL = $idPai->ITINERARIO_ID_PAI;
    //                         }
    //                     }

    //                     $dat->PREVOK = $idItiVolta==$dat->IDTINEREAL ? "PREV" : "NPREV";

    //                     if($dat->PREVOK == 'PREV'){
    //                         $dat->ITVOLTAPREV = $dat->ITIREALIZADOOK;
    //                     }
    //                 }
                    
                
    //             }

    //             if(isset($req->previsto) && $req->previsto != ""){

    //                 if($req->previsto == 1 && ($dat->PREVOK == 'NPREV' || $dat->PREVOK == 'NREALIZADO')){
    //                     continue;
    //                 }

    //                 if($req->previsto == 2 && ($dat->PREVOK == 'PREV' || $dat->PREVOK == 'NREALIZADO')){
    //                     continue;
    //                 }

    //             }

    //             if( !isset($allData[$dat->IDVIAGEM][$dat->CODIGO]) )
    //             {

    //                 //VEICULO
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['PREF']                  = $dat->PREF;
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['PLACA']                 = $dat->PLACA;

    //                 //PAX
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['GRUPO']                 = $dat->GRUPO;
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['CODIGO']                = $dat->CODIGO;
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['NOME']                  = $dat->NOME;
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['MATRICULA']             = $dat->MATRICULA;
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['STATUS']                = $dat->STATUSCAD;

    //                 //VIAGEM
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['DATAREALIZADO']         = $dat->DATAREALIZADO ? date("d/m/Y H:i:s", strtotime($dat->DATAREALIZADO)) : "-";
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['SENTREALIZADO']         = $dat->SENTREALIZADO;
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['ITIREALIZADOOK']        = $dat->ITIREALIZADOOK;
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['PREVOK']                = $dat->PREVOK;
                    
    //                 //EMBARQUE
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['ITIDAPREV']             = $dat->ITIDAPREV;
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['LATITUDEEMB']           = $dat->LATITUDE;
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['LONGITUDEEMB']          = $dat->LONGITUDE;
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['PONTOREFEREMB']         = $dat->PontoRef;
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['HORAMARCACAOEMB']       = date("d/m/Y H:i:s",strtotime($dat->DATAHORACAE));
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['LOGRADOUROEMB']         = $dat->LOGRADOURO;
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['LOCALIZACAOEMB']        = $dat->LOCALIZACAO;
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['IMGS']                  = "";

    //                 //DESEMBARQUE
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['ITVOLTAPREV']           = $dat->ITVOLTAPREV; 
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['LATITUDEDESEMB']        = 0;
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['LONGITUDEDESEMB']       = 0;
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['PONTOREFERDESEMB']      = "";
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['HORAMARCACAODESEMB']    = "";
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['LOGRADOURODESEMB']      = "";
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['LOCALIZACAODESEMB']     = "";
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['IMGS']                  = "";

    //                 //SORT
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['EMBSORT']               = strtotime($dat->DATAHORACAE);


    //             } else {

    //                 //VEICULO
    //                 if (!isset($allData[$dat->IDVIAGEM][$dat->CODIGO]['PREF'])) {
    //                     $allData[$dat->IDVIAGEM][$dat->CODIGO]['PREF'] = $dat->PREF;
    //                 }

    //                 if (!isset($allData[$dat->IDVIAGEM][$dat->CODIGO]['PLACA'])) {
    //                     $allData[$dat->IDVIAGEM][$dat->CODIGO]['PLACA'] = $dat->PLACA;
    //                 }

    //                 //PAX
    //                 if (!isset($allData[$dat->IDVIAGEM][$dat->CODIGO]['GRUPO'])) {
    //                     $allData[$dat->IDVIAGEM][$dat->CODIGO]['GRUPO'] = $dat->GRUPO;
    //                 }

    //                 if (!isset($allData[$dat->IDVIAGEM][$dat->CODIGO]['CODIGO'])) {
    //                     $allData[$dat->IDVIAGEM][$dat->CODIGO]['CODIGO'] = $dat->CODIGO;
    //                 }

    //                 if (!isset($allData[$dat->IDVIAGEM][$dat->CODIGO]['NOME'])) {
    //                     $allData[$dat->IDVIAGEM][$dat->CODIGO]['NOME'] = $dat->NOME;
    //                 }

    //                 if (!isset($allData[$dat->IDVIAGEM][$dat->CODIGO]['MATRICULA'])) {
    //                     $allData[$dat->IDVIAGEM][$dat->CODIGO]['MATRICULA'] = $dat->MATRICULA;
    //                 }

    //                 if (!isset($allData[$dat->IDVIAGEM][$dat->CODIGO]['STATUS'])) {
    //                     $allData[$dat->IDVIAGEM][$dat->CODIGO]['STATUS'] = $dat->STATUSCAD;
    //                 }

    //                 //VIAGEM
    //                 if (!isset($allData[$dat->IDVIAGEM][$dat->CODIGO]['DATAREALIZADO'])) {
    //                     $allData[$dat->IDVIAGEM][$dat->CODIGO]['DATAREALIZADO'] = $dat->DATAREALIZADO ? date("d/m/Y H:i:s", strtotime($dat->DATAREALIZADO)) : "-";
    //                 }

    //                 if (!isset($allData[$dat->IDVIAGEM][$dat->CODIGO]['SENTREALIZADO'])) {
    //                     $allData[$dat->IDVIAGEM][$dat->CODIGO]['SENTREALIZADO'] = $dat->SENTREALIZADO;
    //                 }

    //                 if (!isset($allData[$dat->IDVIAGEM][$dat->CODIGO]['ITIREALIZADOOK'])) {
    //                     $allData[$dat->IDVIAGEM][$dat->CODIGO]['ITIREALIZADOOK'] = $dat->ITIREALIZADOOK;
    //                 }

    //                 if (!isset($allData[$dat->IDVIAGEM][$dat->CODIGO]['PREVOK'])) {
    //                     $allData[$dat->IDVIAGEM][$dat->CODIGO]['PREVOK'] = $dat->PREVOK;
    //                 }

    //                 //DESEMBARQUE
    //                 if (!isset($allData[$dat->IDVIAGEM][$dat->CODIGO]['ITVOLTAPREV'])) {
    //                     $allData[$dat->IDVIAGEM][$dat->CODIGO]['ITVOLTAPREV'] = $dat->ITVOLTAPREV;
    //                 }

    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['LATITUDEDESEMB']  = $dat->LATITUDE;
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['LONGITUDEDESEMB'] = $dat->LONGITUDE;
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['PONTOREFERDESEMB']= $dat->PontoRef; 
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['HORAMARCACAODESEMB']= $dat->DATAHORACAE ? date("d/m/Y H:i:s",strtotime($dat->DATAHORACAE)) : ""; // date("d/m/Y H:i:s",strtotime($dat->DATAHORACAE))
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['LOGRADOURODESEMB'] = $dat->LOGRADOURO;
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['LOCALIZACAODESEMB']= $dat->LOCALIZACAO;
    //                 $allData[$dat->IDVIAGEM][$dat->CODIGO]['IMGS'] = "";
                    
    //             }
    //         }

    //     }

    //     return $allData;
    // }

    public function getDadosAnaliticoPassageiro($req, $viagemID, $dbSys = false, $usuario_id = false)
    {

        $dbConn = $dbSys ? $dbSys : $this->db;
        $allData = array();

        $dateStart  = $req->data_inicio . " 00:00:00";
        $dataEnd    = $req->data_fim . " 23:59:59";

        //variáveis do sql cae
        $matric         = "";
        $grupo          = "";
        $todosGrupos    = "";
        $grupoConfirm   = $this->getGruposLogado($dbSys, $usuario_id);

        if(isset($req->grupo) && $req->grupo != ""){
            $grupo = $req->grupo;

            if(is_array($req->grupo))
                $grupo = implode(",", $req->grupo);

        }else{

            $grupo = $grupoConfirm;
            
        } 

        if(isset($req->todosGrupos) && $req->todosGrupos == 1){
            $todosGrupos = "OR CA.CONTROLE_ACESSO_GRUPO_ID IS NULL";     
        }

        if(isset($req->matricula) && $req->matricula != ""){

            $tag = $this->getTagByMatricula($dbSys, $usuario_id, $req->matricula);
            
            if($tag){
                $matric = " AND CAE.TAG = '{$tag}'";
            }else{
                $matric = " AND CA.MATRICULA_FUNCIONAL = '{$req->matricula}'";
            }

        } 

        //where do sql cae
        $wp = "{$matric} AND (CA.CONTROLE_ACESSO_GRUPO_ID IN ({$grupo}) {$todosGrupos})";


        //variáveis do sql viagens
        $lns    = "";
        $v      = "";

        if(isset($req->lns) && $req->lns != "")
        {
            $lns = " AND i.LINHA_ID IN ({$req->lns})";

            if(is_array($req->lns))
                $lns = " AND i.LINHA_ID IN (".implode(",", $req->lns).")";

        }

        if ($viagemID > 0){

            $v = " AND v.ID = {$viagemID}";
            
        }

        $sql = "SELECT 
                l.GRUPO_LINHA_ID,
                v.ID AS IDVIAGEM,
                l.ID AS IDLINHA,
                v.ITINERARIO_ID AS IDTINEREAL,
                l.NOME AS NOMELINHA,
                gp.NOME AS GRUPO,
                gp.ID AS GRUPOLINHAID,
                l.PREFIXO AS PREFIXO,
                i.TIPO AS TIPO,
                i.SENTIDO AS SENTIDO,
                i.TRECHO AS TRECHO,
                i.DESCRICAO AS DESCRICAO,
                v.DATAHORA_INICIAL_PREVISTO AS DATAINIPREVISTO,
                v.DATAHORA_INICIAL_REALIZADO AS DATAINIREAL,
                v.DATAHORA_FINAL_PREVISTO AS DATAFIMPREV,
                v.DATAHORA_FINAL_REALIZADO AS DATAFIMREAL,
                vc.ID AS IDVEIC,
                vc.PLACA AS PLACA,
                vc.NOME AS PREFIXOVEIC
        FROM BD_CLIENTE.dbo.VIAGENS v
        JOIN ITINERARIOS i ON i.ID = v.ITINERARIO_ID
        JOIN LINHAS l ON l.ID = i.LINHA_ID
        JOIN VEICULO vc ON vc.ID = v.VEICULO_ID
        JOIN GRUPO_LINHAS gp ON gp.ID = l.GRUPO_LINHA_ID
        WHERE v.DATAHORA_INICIAL_PREVISTO BETWEEN '{$dateStart}' AND '{$dataEnd}' {$v} {$lns}
        ORDER BY DATAHORA_INICIAL_PREVISTO;";

        $consulta = $this->pdoSql->query($sql); 

        if($consulta){

            $data = $consulta->fetchAll();
            foreach($data as $dat){

                $dat = (object) $dat;

                $ITIREALIZADOOK = "$dat->NOMELINHA - $dat->DESCRICAO";

                $veiculo_id = $dat->IDVEIC;
                
                $sqlcae = "SELECT 
                        CAE.ID AS CAEID,
                        CAE.DATAHORA AS DATAHORACAE,
                        CAE.TAG AS CODIGO,
                        CAE.LATITUDE,
                        CAE.LONGITUDE,
                        CA.MATRICULA_FUNCIONAL AS MATRICULA,
                        CASE 
                            WHEN CA.CONTROLE_ACESSO_GRUPO_ID IN ({$grupoConfirm}) THEN CAG.NOME
                            WHEN CA.CONTROLE_ACESSO_GRUPO_ID IS NULL THEN ' - '
                            ELSE 'De Outro Grupo'
                        END AS GRUPO,
                        CASE 
                            WHEN CA.CONTROLE_ACESSO_GRUPO_ID IN ({$grupoConfirm}) THEN CA.NOME
                            WHEN CA.CONTROLE_ACESSO_GRUPO_ID IS NULL THEN ' - '
                            ELSE 'De Outro Grupo'
                        END AS NOME,
                        CASE
                            WHEN CA.ATIVO = 1 THEN 'ATIVO'
                            WHEN CA.ATIVO = 0 THEN 'INATIVO'
                            ELSE 'SEM CADASTRO'
                        END AS STATUSCAD,
                        CASE
                            WHEN $dat->SENTIDO = 0 AND CA.ITINERARIO_ID_IDA = {$dat->IDTINEREAL} THEN 'PREV'
                            WHEN $dat->SENTIDO = 1 AND CA.ITINERARIO_ID_VOLTA = {$dat->IDTINEREAL} THEN 'PREV'
                            WHEN $dat->SENTIDO = 0 AND CA.ITINERARIO_ID_IDA != {$dat->IDTINEREAL} THEN 'NPREV'
                            WHEN $dat->SENTIDO = 1 AND CA.ITINERARIO_ID_VOLTA != {$dat->IDTINEREAL} THEN 'NPREV'
                            ELSE 'NREALIZADO'
                        END AS PREVOK,
                        PR.ID AS ID_REFERENCIA,
                        PR.NOME AS NOME_REFERENCIA,
                        PR.LOGRADOURO AS LOGRADOURO_REFERENCIA,
                        PR.LOCALIZACAO AS LOCALIZACAO_REFERENCIA,
                        PR.LATITUDE AS LATITUDE_REFERENCIA,
                        PR.LONGITUDE AS LONGITUDE_REFERENCIA,
                        CASE
                            WHEN ITIDA.DESCRICAO IS NOT NULL AND LIDA.NOME IS NOT NULL THEN CONCAT(LIDA.NOME, ' - ', ITIDA.DESCRICAO)
                            WHEN ITIDA.DESCRICAO IS NOT NULL AND LIDA.NOME IS NULL THEN ITIDA.DESCRICAO
                            ELSE '-'
                        END AS ITIDAPREV,
                        CASE
                            WHEN ITVOLTA.DESCRICAO IS NOT NULL AND LVOLTA.NOME IS NOT NULL THEN CONCAT(LVOLTA.NOME, ' - ', ITVOLTA.DESCRICAO)
                            WHEN ITVOLTA.DESCRICAO IS NOT NULL AND LVOLTA.NOME IS NULL THEN ITVOLTA.DESCRICAO
                            ELSE '-'
                        END AS ITVOLTAPREV
                    FROM 
                        CONTROLE_ACESSO_EVENTOS AS CAE WITH(nolock)
                    LEFT JOIN 
                        CONTROLE_ACESSO CA ON CA.TAG = CAE.TAG
                    LEFT JOIN 
                        CONTROLE_ACESSO_GRUPO CAG ON CAG.ID = CA.CONTROLE_ACESSO_GRUPO_ID
                    LEFT JOIN 
                        PONTOS_REFERENCIA PR ON 1=1
                    LEFT JOIN ITINERARIOS ITIDA ON ITIDA.ID = CA.ITINERARIO_ID_IDA 
                    LEFT JOIN ITINERARIOS ITVOLTA ON ITVOLTA.ID = CA.ITINERARIO_ID_VOLTA 
                    LEFT JOIN LINHAS LIDA ON LIDA.ID = ITIDA.LINHA_ID 
                    LEFT JOIN LINHAS LVOLTA ON LVOLTA.ID = ITVOLTA.LINHA_ID 
                    WHERE 
                        CAE.DATAHORA BETWEEN DATEADD(minute, -15, '{$dat->DATAINIREAL}') AND DATEADD(minute, 15, '{$dat->DATAFIMREAL}') 
                        AND CAE.VEICULO_ID = {$veiculo_id} AND CAE.TIPO_USUARIO = 2 {$wp}
                        AND PR.ID = (
                            SELECT TOP 1 ID
                            FROM PONTOS_REFERENCIA
                            ORDER BY ( 3960 * acos( cos( radians( CAE.LATITUDE ) ) *
                                            cos( radians( LATITUDE ) ) * cos( radians(  LONGITUDE  ) - radians( CAE.LONGITUDE ) ) +
                                            sin( radians( CAE.LATITUDE ) ) * sin( radians(  LATITUDE  ) ) ) )
                        )
                ORDER BY CAE.DATAHORA";
                
                $consultacae = $this->pdoSql->query($sqlcae);
                if($consultacae){
                    $caeventos = $consultacae->fetchAll();

                    foreach ($caeventos as $key => $cae) {

                        $cae = (object) $cae;

                        if(isset($req->previsto) && $req->previsto != ""){

                            if($req->previsto == 1 && ($cae->PREVOK == 'NPREV' || $cae->PREVOK == 'NREALIZADO')){
                                continue;
                            }
        
                            if($req->previsto == 2 && ($cae->PREVOK == 'PREV' || $cae->PREVOK == 'NREALIZADO')){
                                continue;
                            }
        
                        }

                        if( !isset($allData[$dat->IDVIAGEM][$cae->CODIGO]) )
                        {

                            //VEICULO
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['PREF']                  = $dat->PREFIXOVEIC;
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['PLACA']                 = $dat->PLACA;

                            //PAX
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['GRUPO']                 = $cae->GRUPO;
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['CODIGO']                = $cae->CODIGO;
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['NOME']                  = $cae->NOME;
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['MATRICULA']             = $cae->MATRICULA;
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['STATUS']                = $cae->STATUSCAD;

                            //VIAGEM
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['DATAREALIZADO']         = $dat->DATAINIREAL ? date("d/m/Y\nH:i:s", strtotime($dat->DATAINIREAL)) : "-";
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['SENTREALIZADO']         = $dat->SENTIDO;
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['ITIREALIZADOOK']        = $ITIREALIZADOOK;
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['PREVOK']                = $cae->PREVOK;
                            
                            //EMBARQUE
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['ITIDAPREV']             = $cae->ITIDAPREV;
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['LATITUDEEMB']           = $cae->LATITUDE;
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['LONGITUDEEMB']          = $cae->LONGITUDE;
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['PONTOREFEREMB']         = $cae->NOME_REFERENCIA;
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['HORAMARCACAOEMB']       = date("d/m/Y\nH:i:s",strtotime($cae->DATAHORACAE));
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['LOGRADOUROEMB']         = $cae->LOGRADOURO_REFERENCIA;
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['LOCALIZACAOEMB']        = $cae->LOCALIZACAO_REFERENCIA;
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['IMGS']                  = "";

                            //DESEMBARQUE
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['ITVOLTAPREV']           = $cae->ITVOLTAPREV; 
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['LATITUDEDESEMB']        = 0;
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['LONGITUDEDESEMB']       = 0;
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['PONTOREFERDESEMB']      = "";
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['HORAMARCACAODESEMB']    = "";
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['LOGRADOURODESEMB']      = "";
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['LOCALIZACAODESEMB']     = "";
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['IMGS']                  = "";

                            //SORT
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['EMBSORT']               = strtotime($cae->DATAHORACAE);


                        } else {

                            //VEICULO
                            if (!isset($allData[$dat->IDVIAGEM][$cae->CODIGO]['PREF'])) {
                                $allData[$dat->IDVIAGEM][$cae->CODIGO]['PREF'] = $dat->PREFIXOVEIC;
                            }

                            if (!isset($allData[$dat->IDVIAGEM][$cae->CODIGO]['PLACA'])) {
                                $allData[$dat->IDVIAGEM][$cae->CODIGO]['PLACA'] = $dat->PLACA;
                            }

                            //PAX
                            if (!isset($allData[$dat->IDVIAGEM][$cae->CODIGO]['GRUPO'])) {
                                $allData[$dat->IDVIAGEM][$cae->CODIGO]['GRUPO'] = $cae->GRUPO;
                            }

                            if (!isset($allData[$dat->IDVIAGEM][$cae->CODIGO]['CODIGO'])) {
                                $allData[$dat->IDVIAGEM][$cae->CODIGO]['CODIGO'] = $cae->CODIGO;
                            }

                            if (!isset($allData[$dat->IDVIAGEM][$cae->CODIGO]['NOME'])) {
                                $allData[$dat->IDVIAGEM][$cae->CODIGO]['NOME'] = $cae->NOME;
                            }

                            if (!isset($allData[$dat->IDVIAGEM][$cae->CODIGO]['MATRICULA'])) {
                                $allData[$dat->IDVIAGEM][$cae->CODIGO]['MATRICULA'] = $cae->MATRICULA;
                            }

                            if (!isset($allData[$dat->IDVIAGEM][$cae->CODIGO]['STATUS'])) {
                                $allData[$dat->IDVIAGEM][$cae->CODIGO]['STATUS'] = $cae->STATUSCAD;
                            }

                            //VIAGEM
                            if (!isset($allData[$dat->IDVIAGEM][$cae->CODIGO]['DATAREALIZADO'])) {
                                $allData[$dat->IDVIAGEM][$cae->CODIGO]['DATAREALIZADO'] = $dat->DATAINIREAL ? date("d/m/Y H:i:s", strtotime($dat->DATAINIREAL)) : "-";
                            }

                            if (!isset($allData[$dat->IDVIAGEM][$cae->CODIGO]['SENTREALIZADO'])) {
                                $allData[$dat->IDVIAGEM][$cae->CODIGO]['SENTREALIZADO'] = $dat->SENTIDO;
                            }

                            if (!isset($allData[$dat->IDVIAGEM][$cae->CODIGO]['ITIREALIZADOOK'])) {
                                $allData[$dat->IDVIAGEM][$cae->CODIGO]['ITIREALIZADOOK'] = $ITIREALIZADOOK;
                            }

                            if (!isset($allData[$dat->IDVIAGEM][$cae->CODIGO]['PREVOK'])) {
                                $allData[$dat->IDVIAGEM][$cae->CODIGO]['PREVOK'] = $cae->PREVOK;
                            }

                            //DESEMBARQUE
                            if (!isset($allData[$dat->IDVIAGEM][$cae->CODIGO]['ITVOLTAPREV'])) {
                                $allData[$dat->IDVIAGEM][$cae->CODIGO]['ITVOLTAPREV'] = $cae->ITVOLTAPREV;
                            }

                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['LATITUDEDESEMB']  = $cae->LATITUDE;
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['LONGITUDEDESEMB'] = $cae->LONGITUDE;
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['PONTOREFERDESEMB']= $cae->NOME_REFERENCIA; 
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['HORAMARCACAODESEMB']= $cae->DATAHORACAE ? date("d/m/Y\nH:i:s",strtotime($cae->DATAHORACAE)) : "";
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['LOGRADOURODESEMB'] = $cae->LOGRADOURO_REFERENCIA;
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['LOCALIZACAODESEMB']= $cae->LOCALIZACAO_REFERENCIA;
                            $allData[$dat->IDVIAGEM][$cae->CODIGO]['IMGS'] = "";
                            
                        }                        
                        
                    }
                }
            }
        }
        return $allData;
    }

    public function getDadosAnaliticoPassageiroFace($req, $viagemID, $cad_pax_tag = 1)
    {

        $allData = array();
        $grupoConfirm  = $this->getGruposLogado();

        $recFilters['dateStart']    = $req->data_inicio . " 00:00:00";
        $recFilters['dataEnd']      = $req->data_fim . " 23:59:59";
        $recFilters['lns']          = isset($req->lns) && $req->lns != "" ? $req->lns :  false;
        $recFilters['v']            = $viagemID;

        $recFilters['grupoConfirm'] = $grupoConfirm;
        $recFilters['previsto']     = isset($req->previsto) && $req->previsto != "" ? $req->previsto : false;

        $recFilters['matricula']    = isset($req->matricula) && $req->matricula != "" ? $req->matricula : false;
        $recFilters['grupo']        = isset($req->grupo) && $req->grupo != "" ? $req->grupo : $grupoConfirm;
        $recFilters['todosGrupos']  = (isset($req->todosGrupos) && $req->todosGrupos == 1 )|| $viagemID > 0 ? true : false;

        $get = $this->treatRecognitions($recFilters);
        if($get['status'] === true){
            $allData = $get['allData'];
        }
        
        if($cad_pax_tag == 0){
            foreach ($allData as &$subarrays) {
                
                $embsort = array_column($subarrays, 'EMBSORT');
                array_multisort($embsort, SORT_ASC, $subarrays);
            }
            return $allData;
        }else{
            $getWithTags = $this->getDadosAnaliticoPassageiro($req, $viagemID);
            $mergedData = array_replace_recursive($allData, $getWithTags);
            foreach ($mergedData as &$subarrays) {
                
                $embsort = array_column($subarrays, 'EMBSORT');
                array_multisort($embsort, SORT_ASC, $subarrays);
            }
            return $mergedData;
        }
        
    }

    public function treatRecognitions($recFilters){

        $url = 'http://localhost:3000/treatRecognitions';
        $ch = curl_init($url);

        $payload = json_encode(['data' => $recFilters]);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload)
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response === false) {
            return array('status' => false, 'msg' => 'Erro ao tratar com node.');
        }

        $responseData = json_decode($response, true);

        return $responseData;
    }

    // private function treatRecognitions($recognitions, $dbConn){

    //     $now = date("Y-m-d H:i:s");

    //     $usersFaceNew = $this->getUsersFaceNew($dbConn);

    //     foreach($recognitions as $key => $recognition){

    //         $notRecognized = array_filter($recognitions, function($notRecognizedItem, $nritemKey) use($key) {
    //             return $notRecognizedItem->controle_acesso_id == 0 && $nritemKey != $key;
    //         }, ARRAY_FILTER_USE_BOTH);

    //         $recognized = array_filter($recognitions, function($recognizedItem, $ritemKey) use($key) {
    //             return $recognizedItem->controle_acesso_id != 0 && $ritemKey != $key;
    //         }, ARRAY_FILTER_USE_BOTH);

    //         $recognitionTime = strtotime($recognition->real_time);
    //         $recognitionId = $recognition->recId;
    //         $recognitionCA = $recognition->controle_acesso_id;
    //         $recognitionDs = $this->dsToFloatArray($recognition->ds);

    //         //tratando não reconhecidos
    //         if($recognitionCA == '0'){

    //             $toRemove = false;
    //             $toUpadate = false;

    //             //primeiro tenta remover desconhecidos duplicados
    //             foreach ($notRecognized as $nRitem) {
    //                 $nRitemTime = strtotime($nRitem->real_time);
    //                 $timeDifference = abs($recognitionTime - $nRitemTime);
    //                 if ($timeDifference <= 10) {
    //                     $toRemove = true; 
    //                     break;
    //                 }
    //             }

    //             //se toRemove ainda for false
    //             //tenta ver se tem algum reconhecido muito perto, se tiver remove pois deve ser o mesmo
    //             if(!$toRemove){
    //                 foreach ($recognized as $ritem) {
    //                     $ritemTime = strtotime($ritem->real_time);
    //                     $timeDifference = abs($recognitionTime - $ritemTime);
    //                     if ($timeDifference <= 10) {
    //                         $toRemove = true; 
    //                         break;
    //                     }
    //                 }
    //             }

    //             //se toRemove ainda for false
    //             //tenta achar correspondência com um reconhecido e se encontra atualiza o desconhecido
    //             if(!$toRemove){
    //                 foreach ($recognized as $ritem) {
    //                     $recognizedDs = $this->dsToFloatArray($ritem->ds);
    //                     $similaridade = $this->calcularSimilaridade($recognizedDs, $recognitionDs);
    //                     if($similaridade >= 0.9){
    //                         $toUpadate = $ritem->controle_acesso_id;
    //                         break;
    //                     }
    //                 }
    //             }

    //             // se ainda não tiver nem removido nem encontrado similaridade
    //             // e tiver os descritores do usuários tenta achar similaridade neles
    //             if(!$toRemove && !$toUpadate && $usersFaceNew){
    //                 foreach ($usersFaceNew as $user) {
    //                     $userDs = $this->dsToFloatArray($user->ds);
    //                     $userCa = $user->controle_acesso_id;
    //                     $similaridade = $this->calcularSimilaridade($userDs, $recognitionDs);
    //                     if($similaridade >= 0.9){
    //                         $toUpadate = $userCa;
    //                         break;
    //                     }
    //                 }
    //             }

    //             // se toRemove for true tira da array $recognitions e remove no banco
    //             if($toRemove){
    //                 unset($recognitions[$key]);
    //                 // try {
    //                 //     $deteleRec = $dbConn->prepare("UPDATE face_recognitions SET updated_at = '$now', deleted_at = '$now' WHERE id = :id");
    //                 //     $deteleRec->bindParam(':id', $recognitionId);
    //                 //     $deteleRec->execute();
    //                 // } catch (\Throwable $th) {}
    //             }if($toUpadate){
    //                 //se encontrou algum reconhecido com similaridade atualiaza o controle_acesso_id
    //                 $recognitions[$key]->controle_acesso_id = $toUpadate;
    //                 // try {
    //                 //     $updateRec = $dbConn->prepare("UPDATE face_recognitions SET updated_at = '$now', controle_acesso_id = :controle_acesso_id WHERE id = :id");
    //                 //     $updateRec->bindParam(':controle_acesso_id', $toUpadate);
    //                 //     $updateRec->bindParam(':id', $recognitionId);
    //                 //     $updateRec->execute();
    //                 // } catch (\Throwable $th) {}
                   
    //             }else{
    //                 //se não for nem toRemove nem toUpdate
    //                 //tenta achar correspondência com outro não reconhecido 
    //                 foreach ($notRecognized as $nRKey => $nRitem) {
    //                     $nRitemDs = $this->dsToFloatArray($nRitem->ds);
    //                     $nRitemId = $nRitem->recId;
    //                     $similaridade = $this->calcularSimilaridade($nRitemDs, $recognitionDs);

    //                     if($similaridade >= 0.9){
                            
    //                         $newRecId = "rec-$recognitionId-$nRitemId";

    //                         $recognitions[$key]->controle_acesso_id = $newRecId;
    //                         $recognitions[$nRKey]->controle_acesso_id = $newRecId;

    //                         // try {
    //                         //     $updateRec = $dbConn->prepare("UPDATE face_recognitions SET controle_acesso_id = :controle_acesso_id, updated_at = '$now' WHERE id = :id");
    //                         //     $updateRec->bindParam(':controle_acesso_id', $newRecId);
    //                         //     $updateRec->bindParam(':id', $recognitionId);
    //                         //     $updateRec->execute();
    //                         //     $updateRec2 = $dbConn->prepare("UPDATE face_recognitions SET controle_acesso_id = :controle_acesso_id, updated_at = '$now' WHERE id = :id");
    //                         //     $updateRec2->bindParam(':controle_acesso_id', $newRecId);
    //                         //     $updateRec2->bindParam(':id', $nRitemId);
    //                         //     $updateRec2->execute();
    //                         // } catch (\Throwable $th) {}

    //                         break;

    //                     }
    //                 }
    //             }

    //         }else{
    //             //quando for reconhecido verifica se tem outro reconhecido com meu
    //             //controle_acesso_id muito perto e remove o que está inteirando
    //             foreach ($recognized as $ritem) {
    //                 $ritemTime = strtotime($ritem->real_time);
    //                 $ritemCA = $ritem->controle_acesso_id;
    //                 $timeDifference = abs($recognitionTime - $ritemTime);
    //                 if ($timeDifference <= 10 && $recognitionCA == $ritemCA) {
    //                     unset($recognitions[$key]);
    //                     try {
    //                         $deteleRec = $dbConn->prepare("UPDATE face_recognitions SET updated_at = '$now', deleted_at = '$now' WHERE id = :id");
    //                         $deteleRec->bindParam(':id', $recognitionId);
    //                         $deteleRec->execute();
    //                     } catch (\Throwable $th) {}
    //                     break;
    //                 }
    //             }
    //         }

    //     }

    //     return $recognitions;
    // }

    public function getUsersFaceNew(){

        $sql = $this->db->prepare("SELECT cads.controle_acesso_id, cads.ds, ca.ITINERARIO_ID_IDA, ca.ITINERARIO_ID_VOLTA FROM controle_acessos_ds cads JOIN controle_acessos ca ON ca.id = cads.controle_acesso_id WHERE ca.ATIVO = 1 AND ca.user_type = 1");
    
        try {

            $sql->execute();

            if ($sql->rowCount() > 0) {

                $users = $sql->fetchAll(PDO::FETCH_OBJ);
                return $users;

            }else {
                return false;
            }
            

        } catch (\Throwable $th) {
            return false;
        }
    }

    // private function getUsersFaceNew($dbConn){

    //     $sql = $dbConn->prepare("SELECT cads.controle_acesso_id, cads.ds FROM controle_acessos_ds cads JOIN controle_acessos ca ON ca.id = cads.controle_acesso_id WHERE ca.ATIVO = 1 AND ca.user_type = 1");
    
    //     try {

    //         $sql->execute();

    //         if ($sql->rowCount() > 0) {

    //             $users = $sql->fetchAll(PDO::FETCH_OBJ);
    //             return $users;

    //         }else {
    //             return false;
    //         }
            

    //     } catch (\Throwable $th) {
    //         return false;
    //     }
    // }

    private function dsToFloatArray($ds) {

        $valores_string = explode(',', $ds);
        $valores_float = array_map('floatval', $valores_string);
        
        $spl_array = new SplFixedArray(count($valores_float));
    
        foreach ($valores_float as $index => $valor) {
            $spl_array[$index] = $valor;
        }
    
        return $spl_array;
    }

    private function imgToShow($inGroup, $blobImage, $coordinates, $recognized)
    {
        
        $image = imagecreatefromstring($blobImage);

        if ($recognized) {
            $x = $coordinates['x'];
            $y = $coordinates['y'];
            $width = $coordinates['width'];
            $height = $coordinates['height'];

            if ($inGroup) {
                $borderColor = imagecolorallocate($image, 0, 255, 0);
                $borderWidth = 20;
                imagerectangle($image, $x, $y, $x + $width, $y + $height, $borderColor);
            } else {
                $fillColor = imagecolorallocate($image, 0, 0, 0);
                imagefilledrectangle($image, $x, $y, $x + $width, $y + $height, $fillColor);
            }
        }

        ob_start();
        imagejpeg($image);
        $image_data = ob_get_contents();
        ob_end_clean();

        $base64_image = 'data:image/jpeg;base64,' . base64_encode($image_data);

        imagedestroy($image);

        return $base64_image;
    }

    
    private function getLastDay($year, $month)
    {
        $num_dias = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        return $num_dias;
    }

    private function getDadosAnalitico($id, $tipo, $pdoSqr)
    {
    	$retDat = array('DISTANCIA' => 0, 'NOME' => '-');
        if($tipo == 1 && $id != ""){

            $sql = "SELECT 
                FROM BD_CLIENTE.dbo.PONTOS_ITINERARIO PTIDA
                LEFT JOIN PONTOS_REFERENCIA PTREFIDA ON PTREFIDA.ID = PTIDA.PONTO_REFERENCIA_ID
                PTIDA.ITINERARIO_ID = {$id};";

            $consulta = $pdoSqr->query($sql);   

            if($consulta){
                $datas = $consulta->fetchAll();
                $retDat = $datas[0];
                //$ret = $retDat['DISTANCIA'];
            }
            
        } 

        return $retDat;
    }

    public function getDadosPassageiro($req)
    {
    
        $data = array('success' => false, 'choise' => false);

        if($req->registro == ""){

            $wh = "WHERE CA.NOME LIKE '{$req->nome}%' AND CA.CONTROLE_ACESSO_GRUPO_ID IN ({$req->grupoAcess}) AND CA.ATIVO = 1";
            
            $sql = "SELECT * FROM CONTROLE_ACESSO CA {$wh};";
            
            $consulta   =  $this->pdoSql->query($sql);    
            $retorB     = $consulta->fetchAll();

            if(count($retorB) > 1) {

                $data['success'] = true;
                $data['data']    = $retorB;
                $data['choise']  = true;

                return $data;

            } else if(count($retorB) == 1) {

                $req->registro = isset($retorB[0]['MATRICULA_FUNCIONAL']) ? $retorB[0]['MATRICULA_FUNCIONAL'] : "-";

            } else {

                /*
                * PROCURA NA TABLE INTERNA PARA VER SE ESTÁ SEM TAG (CÓDIGO) 
                */
                $sql    = $this->db->prepare("SELECT * FROM controle_acessos AS CA {$wh} AND CA.deleted_at is null order by NOME");
                $sql->execute();
                $newChek = $sql->fetchAll();

                if(count($newChek) > 1) {

                    $data['success'] = true;
                    $data['data']    = $newChek;
                    $data['choise']  = true;
    
                    return $data;
    
                } else if(count($newChek) == 1) {
    
                    $req->registro = isset($newChek[0]['MATRICULA_FUNCIONAL']) ? $newChek[0]['MATRICULA_FUNCIONAL'] : "-";
                    
                } else {

                    $dataret = array();
                    $dataret['success'] = false;
                    $dataret['msg']     = "Nenhum resultado encontrado com as informações fornecida!";
    
                    return $dataret;
                
                }

            }

        }
  
        ###############################################################################
        ############### CASO PASSE PELO FILTRO IRÁ BUSCAR OS DADOS ####################
        ###############################################################################
        $where= "";
        $and  = "";

        if(isset($req->registro) && $req->registro != "" && $req->registro != "-"){

            $where .= $and . "CA.MATRICULA_FUNCIONAL = '{$req->registro}'";
            $and    = " AND "; 

            if($req->nome != ""){
                $where .= $and . "CA.NOME LIKE '%{$req->nome}%'";
                $and    = " AND "; 
            }

        } else if($req->registro == "-"){

            $where .= $and . "CA.NOME = '{$req->nome}'";
            $and    = " AND "; 

        }

        if(isset($req->grupo) && $req->grupo != ""){

            $where .= $and . " CA.CONTROLE_ACESSO_GRUPO_ID IN ({$req->grupoAcess})";
            $and    = " AND ";

        } 

        $w = ($where != "") ? " WHERE {$where} AND CA.ATIVO = 1" : "";

        if($w == ""){
            $data['msg']     = "Faltou usar algum filtros.";
            $data['success'] = false;
            $data['data']    = [];
            return $data;
        }

        $sql = "SELECT
                    CA.TAG AS CODIGO,
                    CA.NOME AS NOME,
                    CA.MATRICULA_FUNCIONAL,
                    LI.PREFIXO AS PREFIXOLINHAIDA,
                    LI.NOME AS NOMELINHAIDA,
                    IDA.SENTIDO AS SENTIDOIDA,
                    IDA.DESCRICAO AS DESCRICAOINTINERARIOIDA,
                    LV.PREFIXO AS PREFIXOLINHAVOL,
                    LV.NOME AS NOMELINHAVOL,
                    IVO.SENTIDO AS SENTIDOVOL,
                    IVO.DESCRICAO AS DESCRICAOINTINERARIOVOL,
                    CA.centro_custo AS POL,
                    CA.ITINERARIO_ID_IDA,
                    CA.ITINERARIO_ID_VOLTA
                    FROM CONTROLE_ACESSO CA
                    LEFT JOIN ITINERARIOS AS IDA ON IDA.ID = CA.ITINERARIO_ID_IDA
                    LEFT JOIN LINHAS AS LI ON LI.ID = IDA.LINHA_ID
                    LEFT JOIN ITINERARIOS AS IVO ON IVO.ID = CA.ITINERARIO_ID_VOLTA
                    LEFT JOIN LINHAS AS LV ON LV.ID = IVO.LINHA_ID {$w};";

        $consulta   =  $this->pdoSql->query($sql);    
        $retorB     = $consulta->fetchAll();

        if(count($retorB) == 0) 
        {
            /* Puxa da tabela interna */
            $q = "SELECT
                        CA.TAG AS CODIGO,
                        CA.NOME AS NOME,
                        CA.MATRICULA_FUNCIONAL,
                        LI.PREFIXO AS PREFIXOLINHAIDA,
                        LI.NOME AS NOMELINHAIDA,
                        IDA.SENTIDO AS SENTIDOIDA,
                        IDA.DESCRICAO AS DESCRICAOINTINERARIOIDA,
                        LV.PREFIXO AS PREFIXOLINHAVOL,
                        LV.NOME AS NOMELINHAVOL,
                        IVO.SENTIDO AS SENTIDOVOL,
                        IVO.DESCRICAO AS DESCRICAOINTINERARIOVOL,
                        CA.centro_custo AS POL,
                        CA.ITINERARIO_ID_IDA,
                        CA.ITINERARIO_ID_VOLTA
                        FROM controle_acessos CA
                        LEFT JOIN itinerarios AS IDA ON IDA.ID = CA.ITINERARIO_ID_IDA
                        LEFT JOIN linhas AS LI ON LI.ID = IDA.LINHA_ID
                        LEFT JOIN itinerarios AS IVO ON IVO.ID = CA.ITINERARIO_ID_VOLTA
                        LEFT JOIN linhas AS LV ON LV.ID = IVO.LINHA_ID {$w};";

            $sql    = $this->db->prepare($q);
            $sql->execute();
            $retorB = $sql->fetchAll();

        }
        
        $data['success'] = true;
        $data['data']    = $retorB;

        return $data;
    }

    ################# PEGANDO VALORES LOCAIS ########################
    public function getGruposAcesso()
    {
        $array  = array();
        
        if(isset($_SESSION['cType']) && $_SESSION['cType'] == 1){
            $sql    = $this->db->prepare("SELECT * FROM acesso_grupos where deleted_at is null order by NOME");
        } else {
            $sql = $this->db->prepare("SELECT acesso_grupos.* FROM acesso_grupos where deleted_at is null AND id IN (
                SELECT grupo_id FROM usuario_grupos WHERE usuario_id = {$_SESSION['cLogin']} AND deleted_at is null
            ) order by NOME");
        }
        
        $sql->execute();
        $array = $sql->fetchAll();

        return $array;
    }

    public function getGrupos()
    {
        $w = "";

        if(isset($_SESSION['cFret']))
            $w = " AND id = " . $_SESSION['cGr'];

        $array  = array();
        $sql    = $this->db->prepare("SELECT * FROM grupo_linhas where deleted_at is null {$w} ORDER BY NOME");
        $sql->execute();
        $array = $sql->fetchAll();

        return $array;
    }

    public function getLinhas($gr = null, $limit = null, $sentido = null)
    {
        $array  = array();

        $itSentido = "";

        if($sentido != null){
            $itSentido = "AND itinerarios.SENTIDO = {$sentido}";
        }

        if(isset($_SESSION['cType']) && ($_SESSION['cType'] == 1 OR $_SESSION['cType'] == 3)){

            if($gr != null && $limit != null)
            {
                $sql = $this->db->prepare("SELECT linhas.id, linhas.ID_ORIGIN, linhas.PREFIXO, linhas.NOME, linhas.GRUPO_LINHA_ID, linhas.CODIGO_INTEGRACAO, itinerarios.DESCRICAO, itinerarios.SENTIDO,
                                            grupo_linhas.NOME AS nomeGrupo
                                            FROM linhas 
                                            INNER JOIN itinerarios ON itinerarios.LINHA_ID = linhas.ID_ORIGIN
                                            LEFT JOIN grupo_linhas ON grupo_linhas.ID_ORIGIN = linhas.GRUPO_LINHA_ID
                                            where linhas.deleted_at is null AND itinerarios.ATIVO = 1 AND GRUPO_LINHA_ID = {$gr} {$itSentido} order by grupo_linhas.NOME, linhas.NOME LIMIT {$limit}");
            }else {
                $sql = $this->db->prepare("SELECT linhas.id, linhas.ID_ORIGIN, linhas.PREFIXO, linhas.NOME, linhas.GRUPO_LINHA_ID, linhas.CODIGO_INTEGRACAO, itinerarios.DESCRICAO, itinerarios.SENTIDO,
                                            grupo_linhas.NOME AS nomeGrupo
                                            FROM linhas 
                                            INNER JOIN itinerarios ON itinerarios.LINHA_ID = linhas.ID_ORIGIN
                                            LEFT JOIN grupo_linhas ON grupo_linhas.ID_ORIGIN = linhas.GRUPO_LINHA_ID
                                            where linhas.deleted_at is null AND itinerarios.ATIVO = 1 {$itSentido} order by grupo_linhas.NOME, linhas.NOME");
            }
        
        }else{
            $sql = $this->db->prepare("SELECT linhas.id, linhas.ID_ORIGIN, linhas.PREFIXO, linhas.NOME, linhas.CODIGO_INTEGRACAO, itinerarios.DESCRICAO, itinerarios.SENTIDO,
                                        grupo_linhas.NOME AS nomeGrupo
                                        FROM linhas 
                                        INNER JOIN itinerarios ON itinerarios.LINHA_ID = linhas.ID_ORIGIN
                                        LEFT JOIN grupo_linhas ON grupo_linhas.ID_ORIGIN = linhas.GRUPO_LINHA_ID
                                        where linhas.deleted_at is null AND linhas.id IN ( SELECT linha_id FROM usuario_linhas WHERE usuario_id = {$_SESSION['cLogin']} AND deleted_at is null ) AND itinerarios.ATIVO = 1 {$itSentido} order by grupo_linhas.NOME, linhas.NOME");
        }

        $sql->execute();
        $array = $sql->fetchAll();
        $rep = array('Â°', 'Âº');
        
        if (count($array) > 1)
            {
                foreach ($array as $k => $lin){
                    $array[$k]['DESCRICAO'] = str_replace($rep, 'º', $lin['DESCRICAO']);
                }
            }
            
        return $array;
    }

    public function residenciaEmbar()
	{

        $sql = "SELECT * FROM PONTOS_REFERENCIA ORDER BY NOME;";

        $consulta   =  $this->pdoSql->query($sql);    
        $retor      = $consulta->fetchAll();

		return $retor;
	}

    public function getLinhasNot($id)
    {
        $array  = array();

        $sql = $this->db->prepare("SELECT linhas.*, grupo_linhas.NOME AS nomeGrupo
        FROM linhas
        INNER JOIN itinerarios ON itinerarios.LINHA_ID = linhas.ID_ORIGIN
        LEFT JOIN grupo_linhas ON grupo_linhas.ID_ORIGIN = linhas.GRUPO_LINHA_ID
        WHERE linhas.deleted_at is null AND linhas.ATIVO = 1
        AND linhas.id NOT IN 
        (SELECT linha_id FROM usuario_linhas WHERE usuario_id = {$id} AND deleted_at is null)
        AND itinerarios.ATIVO = 1 order by grupo_linhas.NOME, linhas.NOME");

        $sql->execute();
        $array = $sql->fetchAll();

        return $array;
    }

    public function getLinhasIn($id)
    {
        $array  = array();

        $sql = $this->db->prepare("SELECT linhas.*, grupo_linhas.NOME AS nomeGrupo
        FROM linhas
        INNER JOIN itinerarios ON itinerarios.LINHA_ID = linhas.ID_ORIGIN
        LEFT JOIN grupo_linhas ON grupo_linhas.ID_ORIGIN = linhas.GRUPO_LINHA_ID
        WHERE linhas.deleted_at is null AND linhas.ATIVO = 1
        AND linhas.id IN 
        (SELECT linha_id FROM usuario_linhas WHERE usuario_id = {$id} AND deleted_at is null)
        AND itinerarios.ATIVO = 1 order by grupo_linhas.NOME, linhas.NOME");

        $sql->execute();
        $array = $sql->fetchAll();

        return $array;
    }

    public function getCarros($id = 0)
    {
        if($id == 0) {
            $array  = array();
            $sql    = $this->db->prepare("SELECT * FROM veiculos where deleted_at is null order by NOME");
            $sql->execute();
            $array = $sql->fetchAll();

            return $array;

        } else {
            $sql = $this->db->prepare("SELECT * FROM veiculos where ID_ORIGIN = {$id}");
            $sql->execute();
            $car = $sql->fetch();

            return $car;
        }  
    }

    public function getCarrosNot($id)
    {

        $array  = array();
        $sql    = $this->db->prepare("SELECT * FROM veiculos where deleted_at is null AND id NOT IN (
                SELECT carro_id FROM usuario_carros WHERE usuario_id = {$id} AND deleted_at is null
            ) order by NOME");
        $sql->execute();
        $array = $sql->fetchAll();

        return $array;
    }

    public function getCarrosIn($id)
    {

        $array  = array();
        $sql    = $this->db->prepare("SELECT * FROM veiculos where deleted_at is null AND id IN (
                SELECT carro_id FROM usuario_carros WHERE usuario_id = {$id} AND deleted_at is null
            ) order by NOME");
        $sql->execute();
        $array = $sql->fetchAll();

        return $array;
    }

    public function getEmbarquesSemCartao($id, $dataIni, $dataFim, $horaIni, $horaFim)
    {
        $array = array();

        $sql = $this->db->prepare("SELECT * FROM embarque_sem_cartaos WHERE prefixo_veiculo_id = :prefixo_veiculo_id AND `data` BETWEEN :dataInicio AND :dataFim AND horario_embarque BETWEEN :horaInicio AND :horaFim");
        $sql->bindValue(":prefixo_veiculo_id", $id);
        $sql->bindValue(":dataInicio", $dataIni);
        $sql->bindValue(":dataFim", $dataFim);
        $sql->bindValue(":horaInicio", $horaIni);
        $sql->bindValue(":horaFim", $horaFim);
        $sql->execute();

        if($sql->rowCount() > 0) {
            $array = $sql->fetchAll();
        }

        return $array;
    }
    ################################################################


    //EMBARQUE SEM RFID

    public function getEmbarquesSemRfId($req){

        $grupo = "";
        $lns = "";

        $dateStart  = $req->data_inicio . " 00:00:00";
        $dataEnd    = $req->data_fim . " 23:59:59";

        $w          = " AND EMB.data_hora BETWEEN '{$dateStart}' AND '{$dataEnd}'";

        if(isset($req->grupo) && $req->grupo != ""){
            $grupo = $req->grupo;

            if(is_array($req->grupo))
                $grupo = implode(",", $req->grupo);

        }else{

            $grupo = $this->getGruposLogado();

        } 

        if(isset($req->matricula) && $req->matricula != ""){

        	$w .= " AND TRIM(LEADING '0' FROM EMB.matricula) = TRIM(LEADING '0' FROM '{$req->matricula}')";

        } 

        if(isset($req->lns) && $req->lns != "")
        {
            $lns = " AND EMB.linha_id IN ({$req->lns})";

            if(is_array($req->lns))
                $lns = " AND EMB.linha_id IN (".implode(",", $req->lns).")";

        }

		$embarquesOk = array();

        // PEGA OS DE TIPO 0 = EMBARQUE
		$embarquesSql = $this->db->prepare("SELECT 
		EMB.*, 
		VEIC.NOME AS PREFIXOVEIC, 
		VEIC.PLACA,
		LINHA.NOME AS NOMELINHA,
        LINHA.PREFIXO AS PREFIXOLINHA,
        GRUPO.NOME AS NOMEGRUPO,
        (CASE WHEN EMB.sentido = 'I' THEN 'Ida' WHEN EMB.sentido = 'V' THEN 'Volta' ELSE 'Ida' END) AS embsentido  
		FROM embarque_sem_RFID EMB
		LEFT JOIN veiculos VEIC ON VEIC.ID_ORIGIN = EMB.veiculo_id
		LEFT JOIN linhas LINHA ON LINHA.ID_ORIGIN = EMB.linha_id
        JOIN app_links APP ON APP.cliente_id = EMB.group_id
        LEFT JOIN acesso_grupos GRUPO ON GRUPO.ID_ORIGIN = APP.groupDefault
        WHERE EMB.tipo = 0 AND APP.groupAccess IN ($grupo) {$w} {$lns} ORDER BY EMB.data_hora ASC");

		$embarquesSql->execute();

        //SE ENCONTRA ADICIONA NA ARRAY embarquesOk COM A KEY SENDO O id_embarque
		if($embarquesSql->rowCount() > 0) {

			$embarques = $embarquesSql->fetchAll(PDO::FETCH_OBJ);

            foreach ($embarques as $emb){

                //PEGAR DADOS DA VIAGEM E O ITINERARIO NA VELTRAC
                $sql = "SELECT
                VI.DATAHORA_INICIAL_PREVISTO, 
                VI.DATAHORA_INICIAL_REALIZADO,
                VI.DATAHORA_FINAL_PREVISTO,
                VI.DATAHORA_FINAL_REALIZADO,
                ITI.DESCRICAO AS ITIDESC
                FROM VIAGENS VI
                LEFT JOIN ITINERARIOS ITI ON ITI.ID = VI.ITINERARIO_ID
                WHERE VI.ID = {$emb->viagem_id}";
    
                $consulta   = $this->pdoSql->query($sql);   
                $viagem     = $consulta->fetch(PDO::FETCH_OBJ); 

                $viagem->DATAHORA_INICIAL_PREVISTO = 
                $viagem->DATAHORA_INICIAL_PREVISTO ? date("d/m/Y H:i:s", strtotime($viagem->DATAHORA_INICIAL_PREVISTO)) : " - ";

                $viagem->DATAHORA_INICIAL_REALIZADO = 
                $viagem->DATAHORA_INICIAL_REALIZADO ? date("d/m/Y H:i:s", strtotime($viagem->DATAHORA_INICIAL_REALIZADO)) : " - ";

                $viagem->DATAHORA_FINAL_PREVISTO = 
                $viagem->DATAHORA_FINAL_PREVISTO ? date("d/m/Y H:i:s", strtotime($viagem->DATAHORA_FINAL_PREVISTO)) : " - ";

                $viagem->DATAHORA_FINAL_REALIZADO = 
                $viagem->DATAHORA_FINAL_REALIZADO ? date("d/m/Y H:i:s", strtotime($viagem->DATAHORA_FINAL_REALIZADO)) : " - ";

                //PEGAR OS DADOS DO PONTO DE EMBARQUE SELECIONADO NA VELTRAC
                $pe = "SELECT NOME, LOGRADOURO, LOCALIZACAO, LATITUDE, LONGITUDE
                FROM PONTOS_REFERENCIA
                WHERE ID = {$emb->ponto_id}";

                $consPontoEmb = $this->pdoSql->query($pe);  
                $pontoEmb = $consPontoEmb->fetch(PDO::FETCH_OBJ); 

                //TENTA ACHAR O PONTO DE REF ATRAVES DA LAT LONG DO EMBARQUE
                $pEmb = "SELECT NOME, LOGRADOURO, LOCALIZACAO, LATITUDE, LONGITUDE FROM (SELECT *, ( 3960 * acos( cos( radians( $emb->latitude ) ) *
                cos( radians( LATITUDE ) ) * cos( radians(  LONGITUDE  ) - radians( $emb->longitude ) ) +
                sin( radians( $emb->latitude ) ) * sin( radians(  LATITUDE  ) ) ) ) AS Distance 
                FROM PONTOS_REFERENCIA) as T WHERE T.Distance < 0.5 ORDER BY T.Distance ASC";
                
                $consPemb = $this->pdoSql->query($pEmb);  
                $pontoRefEmb = $consPemb->fetch(PDO::FETCH_OBJ);

                //TENTA ACHAR O DESEMBARQUE
                $dadosDesembarque = "";
                $consDesembarque = $this->db->prepare("SELECT id_embarque, data_hora, latitude, longitude FROM embarque_sem_RFID WHERE tipo = 1 AND id_embarque = $emb->id_embarque LIMIT 1");
                $consDesembarque->execute();

                $hasDesembarque = false;
                if($consDesembarque->rowCount() == 1) {

                    $desembarque = $consDesembarque->fetch(PDO::FETCH_OBJ);

                    //TENTA ACHAR O PONTO DE REF ATRAVES DA LAT LONG DO DESEMBARQUE
                    $pDesemb = "SELECT NOME, LOGRADOURO, LOCALIZACAO, LATITUDE, LONGITUDE FROM (SELECT *, ( 3960 * acos( cos( radians( $desembarque->latitude ) ) *
                    cos( radians( LATITUDE ) ) * cos( radians(  LONGITUDE  ) - radians( $desembarque->longitude ) ) +
                    sin( radians( $desembarque->latitude ) ) * sin( radians(  LATITUDE  ) ) ) ) AS Distance 
                    FROM PONTOS_REFERENCIA) as T WHERE T.Distance < 0.5 ORDER BY T.Distance ASC";

                    $consPdesemb = $this->pdoSql->query($pDesemb);  
                    $pontoRefDesemb = $consPdesemb->fetch(PDO::FETCH_OBJ); 

                    $dadosDesembarque = (object) [
                        "lat" => $desembarque->latitude,
                        "long" => $desembarque->longitude,
                        "data_hora" => $desembarque->data_hora,
                        "pontoRef" => $pontoRefDesemb
                    ];

                    $hasDesembarque = true;

                }
                
                $embarquesOk[$emb->id_embarque] = [
                    "nomeGrupo" => $emb->NOMEGRUPO,
                    "id_embarque" => $emb->id_embarque,
                    "sentido" => $emb->embsentido,
                    "pontoEmb" => $pontoEmb,
                    "passageiro" => (object) [
                        "matricula" => $emb->matricula,
                        "nome" => $emb->nome,
                        "motivo" => $emb->motivo
                    ],
                    "veiculo" => (object) [
                        "prefixo" => $emb->PREFIXOVEIC,
                        "placa" => $emb->PLACA
                    ],
                    "linha" => (object) [
                        "prefixo" => $emb->PREFIXOLINHA,
                        "nome" => $emb->NOMELINHA
                    ],
                    "viagem" => $viagem,
                    "dados_embarque" => (object) [
                        "lat" => $emb->latitude,
                        "long" => $emb->longitude,
                        "data_hora" => $emb->data_hora,
                        "pontoRef" => $pontoRefEmb
                    ],
                    "dados_desembarque" => $dadosDesembarque,
                    "hasDesembarque" => $hasDesembarque
                ];
                
            }
		}

		return $embarquesOk;

	}

    //AGENDAMENTOS

    //PARA TODOS
    public function getAgendamentos($agenda, $idAgenda = false){


        $select = "";
        $and = "";

        if($agenda == "agenda_analitico"){

            $select = ", (CASE WHEN previsto = '1' THEN 'Sim' WHEN previsto = '2' THEN 'Não' ELSE 'Todos' END) AS previstotxt";

        }

        if($agenda == "agenda_consolidado"){

            $select = ", (CASE WHEN sentido = '1' THEN 'Ida' WHEN sentido = '2' THEN 'Volta' ELSE 'Todos' END) AS sentidotxt, (CASE WHEN pontual = '1' THEN 'Pontual' WHEN pontual = '2' THEN 'Atrasado' WHEN pontual = '3' THEN 'Adiantado' ELSE 'Todos' END) AS pontualtxt";

        }

        if($agenda == "agenda_sintetico"){

            $select = ", (CASE WHEN pontual = '1' THEN 'Pontual' WHEN pontual = '2' THEN 'Atrasado' WHEN pontual = '3' THEN 'Adiantado' ELSE 'Todos' END) AS pontualtxt";

        }

        $select .= ", (CASE WHEN status = '1' THEN '1' ELSE '0' END) AS isready";

        if($idAgenda){

            $and = " AND id = $idAgenda";

        }

        $array  = array();
        $sql = $this->db->prepare("SELECT {$agenda}.* {$select}
            FROM {$agenda} WHERE usuario_id = :usuario_id {$and}
            AND deleted_at is null 
            ORDER BY created_at DESC");

        $sql->bindValue(":usuario_id", $_SESSION['cLogin']);
        $sql->execute();
        if($sql->rowCount() > 0) {
            $array = $sql->fetchAll(PDO::FETCH_OBJ);
            foreach ($array as $k => $ag){
                $array[$k]->linhas = $this->getLinhasAgendadas($ag->lns);

                if($agenda == "agenda_analitico"){
                    $array[$k]->grupos = $this->getGrupoAgendados($ag->grupo);
                }
            }
        }

        return $array;
    }

    public function getAgLeft($agenda){

        $qtdL = 0;
        $param = new Parametro();
        $param = $param->getParametros(false);

        $dateStart  = date("Y-m-d") . " 00:00:00";
        $dateEnd    = date("Y-m-d") . " 23:59:59";

        $sql = $this->db->prepare("SELECT id FROM {$agenda} WHERE usuario_id = {$_SESSION['cLogin']} AND status = 0 AND created_at BETWEEN '{$dateStart}' AND '{$dateEnd}' AND deleted_at is null");

        $sql->execute();
        $qtd = $sql->fetchAll();

        $qtdL = $param['qtd_agendas'] - count($qtd);

        return $qtdL;

    }

    public function delAgenda($agenda, $id){

        $now = date("Y-m-d H:i:s");

        $retorno = array(
			"status" => true,
			"title" => "SUCESSO",
			"text" => "Removido com sucesso!",
			"icon" => "success",
			"button" => "OK"
		);

        $sql = $this->db->prepare("UPDATE {$agenda} SET deleted_at = '{$now}' WHERE id = {$id} AND status = 0");
        $sql->execute();

        if (!$sql){
			$retorno['status'] = false;
			$retorno['title'] =  "ERRO";
			$retorno['text'] = "Ocorreu um erro ao remover, tente novamente!";
			$retorno['icon'] = "error";

            return $retorno;
		}

        $retorno['today'] = $this->getAgLeft($agenda);
        
        return $retorno;
		
	}

    public function getLinhasAgendadas($lns)
    {
        $array  = array();

        $sql = $this->db->prepare("SELECT linhas.id, linhas.ID_ORIGIN, linhas.PREFIXO, linhas.NOME, itinerarios.SENTIDO FROM linhas
        INNER JOIN itinerarios ON itinerarios.LINHA_ID = linhas.ID_ORIGIN
        WHERE linhas.ID_ORIGIN IN ($lns) AND linhas.deleted_at is null AND linhas.ATIVO = 1 AND itinerarios.ATIVO = 1 ORDER BY linhas.NOME");

        $sql->execute();
        $array = $sql->fetchAll();

        ################## TRATA LINHAS #################
        if(count($array)>0){
            foreach ($array as $k => $lin){
                $array[$k] = $lin['PREFIXO'] . " - " . $lin['NOME'] . " - " . ( $lin['SENTIDO'] == 0 ? "ENTRADA" : "RETORNO");
            }
        }

        return $array;
    }

    public function getGrupoAgendados($gr)
	{
		$array = array();

		$sql = $this->db->prepare("SELECT NOME FROM acesso_grupos WHERE ID_ORIGIN IN ($gr) ORDER BY NOME");
        $sql->execute();
        $array = $sql->fetchAll();

		return $array;
	}

    public function getDadosAgendado($agenda, $idAgenda, $viagemID = 0, $agenda_viagem_id = 0){

        $array = array();

        $and = "";

        if($viagemID == 0 && $agenda_viagem_id == 0){

            $and = " AND agenda_id = {$idAgenda}";

        }else{

            $and = " AND viagemID = {$viagemID} AND agenda_viagem_id = '{$agenda_viagem_id}'";

        }
        
        $sql = $this->db->prepare("SELECT * FROM {$agenda} 
        WHERE usuario_id = {$_SESSION['cLogin']} {$and}");
        $sql->execute();

        $array = $sql->fetchAll(PDO::FETCH_OBJ);

        return $array;
        
    }

    //ANALÍTICO
    public function agendarAnaliticoPassageiro($post){

        $now = date("Y-m-d H:i:s");

        $retorno = array(
			"status" => true,
			"title" => "SUCESSO",
			"text" => "Agendado com sucesso!",
			"icon" => "success",
			"button" => "OK"
		);

        if (!isset($_SESSION['cType']) || isset($_SESSION['cType']) && $_SESSION['cType'] == 1){
			$retorno['status'] = false;
			$retorno['title'] =  "ERRO";
			$retorno['text'] = "Ocorreu um erro ao agendar, tente novamente!";
			$retorno['icon'] = "error";
            return $retorno;
		}

        //checar se ainda pode realizar agendamentos no dia
        if ($this->getAgLeft('agenda_analitico') == 0){
			$retorno['status'] = false;
			$retorno['title'] =  "ATENÇÃO";
			$retorno['text'] = "Você já atingiu número máximo de agendamentos para hoje.";
			$retorno['icon'] = "warning";
            return $retorno;
		}

        // //checar se existe agendamento pendente para o usuário e com os mesmos filtros
		$sql = $this->db->prepare("SELECT * FROM agenda_analitico WHERE usuario_id = :usuario_id AND data_inicio = :data_inicio AND data_fim = :data_fim AND todosGrupos = :todosGrupos AND matricula = :matricula AND previsto = :previsto AND viagemID = :viagemID AND grupo = :grupo AND lns = :lns AND status = 0 AND deleted_at is null LIMIT 1");
		$sql->bindValue(":usuario_id", $_SESSION['cLogin']);
        $sql->bindValue(":data_inicio", $post['data_inicio']);
        $sql->bindValue(":data_fim", $post['data_fim']);
        $sql->bindValue(":todosGrupos", $post['todosGrupos']);
        $sql->bindValue(":matricula", $post['matricula']);
        $sql->bindValue(":previsto", $post['previsto']);
        $sql->bindValue(":viagemID", $post['viagemID']);
        $sql->bindValue(":grupo", $post['grupo']);
        $sql->bindValue(":lns", $post['lns']);
        $sql->execute();

		if($sql->rowCount() != 0) {
			$retorno['status'] = false;
			$retorno['title'] =  "ATENÇÃO";
			$retorno['text'] = "Já existe um agendamento pendente com os filtros informados!";
			$retorno['icon'] = "warning";
			return $retorno;
		}

        $sql = $this->db->prepare("INSERT INTO agenda_analitico SET usuario_id = :usuario_id, data_inicio = :data_inicio, data_fim = :data_fim, grupo = :grupo, todosGrupos = :todosGrupos, matricula = :matricula, previsto = :previsto, viagemID = :viagemID, lns = :lns, created_at = :agora");
		$sql->bindValue(":usuario_id", $_SESSION['cLogin']);
        $sql->bindValue(":data_inicio", $post['data_inicio']);
        $sql->bindValue(":data_fim", $post['data_fim']);
        $sql->bindValue(":grupo", $post['grupo']);
        $sql->bindValue(":todosGrupos", $post['todosGrupos']);
        $sql->bindValue(":matricula", $post['matricula']);
        $sql->bindValue(":previsto", $post['previsto']);
        $sql->bindValue(":viagemID", $post['viagemID']);
        $sql->bindValue(":lns", $post['lns']);
        $sql->bindValue(":agora", $now);
        $sql->execute();

        if (!$sql){
			$retorno['status'] = false;
			$retorno['title'] =  "ERRO";
			$retorno['text'] = "Ocorreu um erro ao agendar, tente novamente!";
			$retorno['icon'] = "error";

            return $retorno;
		}

        $retorno['novoAgendamento'] = false;

        $getNewAgenda = $this->getAgendamentos('agenda_analitico', $this->db->lastInsertId());
        
        if(count($getNewAgenda) == 1){

            $agendamento = $getNewAgenda[0];

            $html = "<li title='Relatório ainda não está disponível' class='rounded show btn-warning agPendente'>";
            $html .= "<div class='row bg-primary text-white mx-0 mb-1 p-1 w-100 d-flex align-items-center'>
            <div class='col col-6 p-0 m-0 text-left'>
              Agendamento # <b>".$agendamento->id."</b>
            </div>
            <div class='btnsAgenda'>
              <i title='Excluir Agendamento' class='fas fa-trash-alt bg-danger p-1 delDate-".date('d-m-Y', strtotime($agendamento->created_at))."' onclick='excluirAgenda(".$agendamento->id.", \"".date('d-m-Y', strtotime($agendamento->created_at))."\", this)'></i>
              <i title='Expandir Detalhes' class='fas fa-expand-alt detalhaAgenda p-1' onclick='expandAgenda(this)'></i>
            </div>
            </div>";
            $html .= "<div class='px-2 w-100 text-dark'>";
            $html .= "<span><b>Agendado em: </b>".date("d/m/Y - H:i", strtotime($agendamento->created_at))."</span>";
            $html .= "<hr class='w-100 mt-0 mb-0'><span><b>Data Início: </b>".date("d/m/Y", strtotime($agendamento->data_inicio))." - <b>Data Fim: </b>".date("d/m/Y", strtotime($agendamento->data_fim))."</span>";
            $html .= "<hr class='w-100 mb-0'>";
            $html .= "<span><b>Previsto: </b>".$agendamento->previstotxt."</span>";

            if($agendamento->matricula){
                $html .= "<hr class='w-100 mt-0 mb-0'><span><b>Matrícula: </b>".$agendamento->matricula."</span>";
            }

            $html .= "<hr class='w-100 mt-0 mb-0'>";
            $html .= "<span><b>Incluir sem Grupo: </b>";
            $html .= $agendamento->todosGrupos == 1 ? "Sim" : "Não";
            $html .= "</span>";

            if(count($agendamento->linhas) > 0){
                $html .= "<hr class='w-100 mt-0 mb-0'>";
                $html .= "<span><b>";
                $html .= count($agendamento->linhas) == 1 ? "Linha" : "Linhas";
                $html .= ":</b></span>";
                $html .= "<div class='linhasAgenda'>";

                foreach($agendamento->linhas as $linha){
                    $html .= "<i>".$linha."</i>";
                }

                $html .= "</div>";
            }

            if(count($agendamento->grupos) > 0){
                $html .= "<hr class='w-100 mt-0 mb-0'>";
                $html .= "<span><b>";
                $html .= count($agendamento->grupos) == 1 ? "Grupo" : "Grupos";
                $html .= ":</b></span>";
                $html .= "<div class='gruposAgenda'>";

                foreach($agendamento->grupos as $grupo){
                    $html .= "<i>".$grupo['NOME']."</i>";
                }

                $html .= "</div>";
            }

            $html .= "</div>";

            $html .= "</li>";

            $retorno['novoAgendamento'] = $html;
        }

        return $retorno;

    }

    //CONSOLIDADO
    public function agendarConsolidado($post){

        $now = date("Y-m-d H:i:s");

        $sentido = $post['sentido'] != "" ? $post['sentido'] : 0;
        $pontual = $post['pontual'] != "" ? $post['pontual'] : 0;

        $retorno = array(
			"status" => true,
			"title" => "SUCESSO",
			"text" => "Agendado com sucesso!",
			"icon" => "success",
			"button" => "OK"
		);

        if (!isset($_SESSION['cType']) || isset($_SESSION['cType']) && $_SESSION['cType'] == 1){
			$retorno['status'] = false;
			$retorno['title'] =  "ERRO";
			$retorno['text'] = "Ocorreu um erro ao agendar, tente novamente!";
			$retorno['icon'] = "error";
            return $retorno;
		}

        //checar se ainda pode realizar agendamentos no dia
        if ($this->getAgLeft('agenda_consolidado') == 0){
			$retorno['status'] = false;
			$retorno['title'] =  "ATENÇÃO";
			$retorno['text'] = "Você já atingiu número máximo de agendamentos para hoje.";
			$retorno['icon'] = "warning";
            return $retorno;
		}

        // //checar se existe agendamento pendente para o usuário e com os mesmos filtros
		$sql = $this->db->prepare("SELECT * FROM agenda_consolidado WHERE usuario_id = :usuario_id AND data_inicio = :data_inicio AND data_fim = :data_fim AND sentido = :sentido AND pontual = :pontual AND lns = :lns AND status = 0 AND deleted_at is null LIMIT 1");
		$sql->bindValue(":usuario_id", $_SESSION['cLogin']);
        $sql->bindValue(":data_inicio", $post['data_inicio']);
        $sql->bindValue(":data_fim", $post['data_fim']);
        $sql->bindValue(":sentido", $sentido);
        $sql->bindValue(":pontual", $pontual);
        $sql->bindValue(":lns", $post['lns']);
        $sql->execute();

		if($sql->rowCount() != 0) {
			$retorno['status'] = false;
			$retorno['title'] =  "ATENÇÃO";
			$retorno['text'] = "Já existe um agendamento pendente com os filtros informados!";
			$retorno['icon'] = "warning";
			return $retorno;
		}

        $sql = $this->db->prepare("INSERT INTO agenda_consolidado SET usuario_id = :usuario_id, data_inicio = :data_inicio, data_fim = :data_fim, sentido = :sentido, pontual = :pontual, lns = :lns, created_at = :agora");
		$sql->bindValue(":usuario_id", $_SESSION['cLogin']);
        $sql->bindValue(":data_inicio", $post['data_inicio']);
        $sql->bindValue(":data_fim", $post['data_fim']);
        $sql->bindValue(":sentido", $sentido);
        $sql->bindValue(":pontual", $pontual);
        $sql->bindValue(":lns", $post['lns']);
        $sql->bindValue(":agora", $now);
        $sql->execute();

        if (!$sql){
			$retorno['status'] = false;
			$retorno['title'] =  "ERRO";
			$retorno['text'] = "Ocorreu um erro ao agendar, tente novamente!";
			$retorno['icon'] = "error";

            return $retorno;
		}

        $retorno['novoAgendamento'] = false;

        $getNewAgenda = $this->getAgendamentos('agenda_consolidado', $this->db->lastInsertId());
        
        if(count($getNewAgenda) == 1){

            $agendamento = $getNewAgenda[0];

            $html = "<li title='Relatório ainda não está disponível' class='rounded show btn-warning agPendente'>";
            $html .= "<div class='row bg-primary text-white mx-0 mb-1 p-1 w-100 d-flex align-items-center'>
            <div class='col col-6 p-0 m-0 text-left'>
              Agendamento # <b>".$agendamento->id."</b>
            </div>
            <div class='btnsAgenda'>
              <i title='Excluir Agendamento' class='fas fa-trash-alt bg-danger p-1 delDate-".date('d-m-Y', strtotime($agendamento->created_at))."' onclick='excluirAgenda(".$agendamento->id.", \"".date('d-m-Y', strtotime($agendamento->created_at))."\", this)'></i>
              <i title='Expandir Detalhes' class='fas fa-expand-alt detalhaAgenda p-1' onclick='expandAgenda(this)'></i>
            </div>
            </div>";
            $html .= "<div class='px-2 w-100 text-dark'>";
            $html .= "<span><b>Agendado em: </b>".date("d/m/Y - H:i", strtotime($agendamento->created_at))."</span>";
            $html .= "<hr class='w-100 mt-0 mb-0'><span><b>Data Início: </b>".date("d/m/Y", strtotime($agendamento->data_inicio))." - <b>Data Fim: </b>".date("d/m/Y", strtotime($agendamento->data_fim))."</span>";
            $html .= "<hr class='w-100 mb-0'>";
            $html .= "<span><b>Sentido: </b>".$agendamento->sentidotxt."</span>";
            $html .= "<hr class='w-100 mt-0 mb-0'>";
            $html .= "<span><b>Pontualidade: </b>".$agendamento->pontualtxt."</span>";

            if(count($agendamento->linhas) > 0){
                $html .= "<hr class='w-100 mt-0 mb-0'>";
                $html .= "<span><b>";
                $html .= count($agendamento->linhas) == 1 ? "Linha" : "Linhas";
                $html .= ":</b></span>";
                $html .= "<div class='linhasAgenda'>";

                foreach($agendamento->linhas as $linha){
                    $html .= "<i>".$linha."</i>";
                }

                $html .= "</div>";
            }

            $html .= "</div>";

            $html .= "</li>";

            $retorno['novoAgendamento'] = $html;
        }

        return $retorno;

    }

    //SINTÉTICO
    public function agendarSintetico($post){

        $now = date("Y-m-d H:i:s");

        $pontual = $post['pontual'] != "" ? $post['pontual'] : 0;

        $retorno = array(
			"status" => true,
			"title" => "SUCESSO",
			"text" => "Agendado com sucesso!",
			"icon" => "success",
			"button" => "OK"
		);

        if (!isset($_SESSION['cType']) || isset($_SESSION['cType']) && $_SESSION['cType'] == 1){
			$retorno['status'] = false;
			$retorno['title'] =  "ERRO";
			$retorno['text'] = "Ocorreu um erro ao agendar, tente novamente!";
			$retorno['icon'] = "error";
            return $retorno;
		}

        //checar se ainda pode realizar agendamentos no dia
        if ($this->getAgLeft('agenda_sintetico') == 0){
			$retorno['status'] = false;
			$retorno['title'] =  "ATENÇÃO";
			$retorno['text'] = "Você já atingiu número máximo de agendamentos para hoje.";
			$retorno['icon'] = "warning";
            return $retorno;
		}

        // //checar se existe agendamento pendente para o usuário e com os mesmos filtros
		$sql = $this->db->prepare("SELECT * FROM agenda_sintetico WHERE usuario_id = :usuario_id AND data_inicio = :data_inicio AND data_fim = :data_fim AND pontual = :pontual AND lns = :lns AND status = 0 AND deleted_at is null LIMIT 1");
		$sql->bindValue(":usuario_id", $_SESSION['cLogin']);
        $sql->bindValue(":data_inicio", $post['data_inicio']);
        $sql->bindValue(":data_fim", $post['data_fim']);
        $sql->bindValue(":pontual", $pontual);
        $sql->bindValue(":lns", $post['lns']);
        $sql->execute();

		if($sql->rowCount() != 0) {
			$retorno['status'] = false;
			$retorno['title'] =  "ATENÇÃO";
			$retorno['text'] = "Já existe um agendamento pendente com os filtros informados!";
			$retorno['icon'] = "warning";
			return $retorno;
		}

        $sql = $this->db->prepare("INSERT INTO agenda_sintetico SET usuario_id = :usuario_id, data_inicio = :data_inicio, data_fim = :data_fim, pontual = :pontual, lns = :lns, created_at = :agora");
		$sql->bindValue(":usuario_id", $_SESSION['cLogin']);
        $sql->bindValue(":data_inicio", $post['data_inicio']);
        $sql->bindValue(":data_fim", $post['data_fim']);
        $sql->bindValue(":pontual", $pontual);
        $sql->bindValue(":lns", $post['lns']);
        $sql->bindValue(":agora", $now);
        $sql->execute();

        if (!$sql){
			$retorno['status'] = false;
			$retorno['title'] =  "ERRO";
			$retorno['text'] = "Ocorreu um erro ao agendar, tente novamente!";
			$retorno['icon'] = "error";

            return $retorno;
		}

        $retorno['novoAgendamento'] = false;

        $getNewAgenda = $this->getAgendamentos('agenda_sintetico', $this->db->lastInsertId());
        
        if(count($getNewAgenda) == 1){

            $agendamento = $getNewAgenda[0];

            $html = "<li title='Relatório ainda não está disponível' class='rounded show btn-warning agPendente'>";
            $html .= "<div class='row bg-primary text-white mx-0 mb-1 p-1 w-100 d-flex align-items-center'>
            <div class='col col-6 p-0 m-0 text-left'>
              Agendamento # <b>".$agendamento->id."</b>
            </div>
            <div class='btnsAgenda'>
              <i title='Excluir Agendamento' class='fas fa-trash-alt bg-danger p-1 delDate-".date('d-m-Y', strtotime($agendamento->created_at))."' onclick='excluirAgenda(".$agendamento->id.", \"".date('d-m-Y', strtotime($agendamento->created_at))."\", this)'></i>
              <i title='Expandir Detalhes' class='fas fa-expand-alt detalhaAgenda p-1' onclick='expandAgenda(this)'></i>
            </div>
            </div>";
            $html .= "<div class='px-2 w-100 text-dark'>";
            $html .= "<span><b>Agendado em: </b>".date("d/m/Y - H:i", strtotime($agendamento->created_at))."</span>";
            $html .= "<hr class='w-100 mt-0 mb-0'><span><b>Data Início: </b>".date("d/m/Y", strtotime($agendamento->data_inicio))." - <b>Data Fim: </b>".date("d/m/Y", strtotime($agendamento->data_fim))."</span>";
            $html .= "<hr class='w-100 mb-0'>";
            $html .= "<span><b>Pontualidade: </b>".$agendamento->pontualtxt."</span>";

            if(count($agendamento->linhas) > 0){
                $html .= "<hr class='w-100 mt-0 mb-0'>";
                $html .= "<span><b>";
                $html .= count($agendamento->linhas) == 1 ? "Linha" : "Linhas";
                $html .= ":</b></span>";
                $html .= "<div class='linhasAgenda'>";

                foreach($agendamento->linhas as $linha){
                    $html .= "<i>".$linha."</i>";
                }

                $html .= "</div>";
            }

            $html .= "</div>";

            $html .= "</li>";

            $retorno['novoAgendamento'] = $html;
        }

        return $retorno;

    }

    //BODYS NOTIFICAÇÃO
    public function templateEmailRels($agenda, $idAgenda)
    {

        $html = "";
        $html .= "<div style='width:60%;margin: auto;padding: 15px;background-color: white;color:#2a1e52'>";
        $html .= "<div style='text-align: center'>";
        $html .= "<img src='#URL#/assets/images/logoApp.png' width='150px'>"; // TODO: POPULATE WITH INDEX URL
        $html .= "</div>";
        $html .= "<h3 style='text-align: center'>" . utf8_decode("Agendamento Relatório ".$agenda) . "</h3>";
        $html .= "<hr>";
        $html .= "<h2 style='text-align: center'>" . utf8_decode("Agendamento") . "<strong> # $idAgenda</strong></h2>";
        $html .= "<p style='text-align: left'>" . utf8_decode("Entre no ".PORTAL_NAME.", em Relatórios > $agenda e clique na Aba Agendamentos para visualizar o relatório.") . "</p>";
        $html .= "<hr>";
        $html .= "</div>";
        
        return $html;
    }

    public function trataPontualidade($ranger = 10, $req = null, $real = null, $prev = null){

        $now = date("Y-m-d H:i:s");

        if($req){

            try {
                $pdo = new \PDO ("dblib:host=$this->host:$this->port;dbname=$this->dbName;charset=utf8","$this->user","$this->pass");
            } catch (\Throwable $th) {
                return false;
            }

            $dataRet = array();
            $where= "";
            $and  = "";
            $data = array();

            if(isset($req->lns) && $req->lns != ""){

                if(!is_array($req->lns))
                    $linhas = $req->lns;
                else 
                    $linhas = implode(',', $req->lns);
    
                $where .= $and . "i.LINHA_ID IN ({$linhas})";
                $and    = " AND "; 
    
            }

            $dateStart  = $req->data_inicio . " 00:00:00";
            $dataEnd    = $req->data_fim . " 23:59:59";

            $where      .= $and . "DATAHORA_INICIAL_PREVISTO BETWEEN '{$dateStart}' AND '{$dataEnd}'";
            $w          = ($where != "") ? " WHERE " . $where : "";

            $sql = "SELECT 
                v.ID AS IDVIAGEM,
                v.DATAHORA_INICIAL_PREVISTO AS DATAINIPREV,
                v.DATAHORA_INICIAL_REALIZADO AS DATAINIREAL,
                v.DATAHORA_FINAL_PREVISTO AS DATAFIMPREV,
                v.DATAHORA_FINAL_REALIZADO AS DATAFIMREAL,
                i.SENTIDO AS SENTIDO
            FROM BD_CLIENTE.dbo.VIAGENS v
            JOIN ITINERARIOS i ON i.ID = v.ITINERARIO_ID 
            {$w} ORDER BY DATAHORA_INICIAL_PREVISTO;";
            $consulta = $pdo->query($sql);
            if($consulta)   
                $data = $consulta->fetchAll();

            $pontualIda = 0;
            $adiantadoIda = 0;
            $atrasadoIda = 0;
            $nesIda = 0;
            $agendaIda = 0;

            $pontualVolta = 0;
            $adiantadoVolta = 0;
            $atrasadoVolta = 0;
            $nesVolta = 0;
            $agendaVolta = 0;

            foreach ($data as $ret) {

                $ret = (Object) $ret;
                $sentido = $ret->SENTIDO;
                $real = $sentido == 0 ? $ret->DATAFIMREAL : $ret->DATAINIREAL;
                $prev = $sentido == 0 ? $ret->DATAFIMPREV : $ret->DATAINIPREV;
    
                $intRang = date("Y-m-d H:i:00", strtotime("- {$ranger} minutes", strtotime($prev)));
                $fimRang = date("Y-m-d H:i:59", strtotime("+ {$ranger} minutes", strtotime($prev)));

                if(is_null($real) || $real == "0000-00-00"){

                    if(strtotime($now) < strtotime($prev)){
                        #### AGENDADA
                        if($sentido == 0){
                            ##ida
                            $agendaIda += 1; 
                        }else{
                            ##volta
                            $agendaVolta += 1; 
                        }
                    }else{
                        #### NÃO REALIZADA
                        if($sentido == 0){
                            ##ida
                            $nesIda += 1; 
                        }else{
                            ##volta
                            $nesVolta += 1; 
                        }
                    }

                }else{

                    if ($real > $intRang && $real < $fimRang) {
                        #### FEZ DENTRO DO HORARIO
                        if($sentido == 0){
                            ##ida
                            $pontualIda += 1; 
                        }else{
                            ##volta
                            $pontualVolta += 1; 
                        }
                
                    } else if ($real < $intRang) {
                        #### FEZ ADIANTADO DO HORARIO
                        if($sentido == 0){
                            ##ida
                            $adiantadoIda += 1; 
                        }else{
                            ##volta
                            $atrasadoVolta += 1; 
                        }
                
                    } else if ($real > $fimRang) {
                        #### FEZ ATRASADO DO HORARIO
                        if($sentido == 0){
                            ##ida
                            $atrasadoIda += 1; 
                        }else{
                            ##volta
                            $atrasadoVolta += 1; 
                        }
                    }
                }
                
            }

            $graphParams = $this->getGraphParams(true);

            $dataRet['ida'] = base64_encode(json_encode(array(
                [$graphParams[1]['txt'], $pontualIda, $graphParams[1]['bg']], 
                [$graphParams[2]['txt'], $adiantadoIda, $graphParams[2]['bg']], 
                [$graphParams[3]['txt'], $atrasadoIda, $graphParams[3]['bg']],
                [$graphParams[4]['txt'], $nesIda, $graphParams[4]['bg']]
                )));
            $dataRet['volta'] = base64_encode(json_encode(array(
                [$graphParams[1]['txt'], $pontualVolta, $graphParams[1]['bg']], 
                [$graphParams[2]['txt'], $adiantadoVolta, $graphParams[2]['bg']], 
                [$graphParams[3]['txt'], $atrasadoVolta, $graphParams[3]['bg']],
                [$graphParams[4]['txt'], $nesVolta, $graphParams[4]['bg']]
                )));
     
            return $dataRet;
        }

        if(is_null($real) || $real == "0000-00-00"){
            
            if(strtotime($now) < strtotime($prev)){
                #### AGENDADA
                return 8;
            }else{
                #### NÃO REALIZADA
                return 4;
            }
            
        }

        $intRang = date("Y-m-d H:i:00", strtotime("- {$ranger} minutes", strtotime($prev)));
        $fimRang = date("Y-m-d H:i:59", strtotime("+ {$ranger} minutes", strtotime($prev)));

        if ($real > $intRang && $real < $fimRang) {
            #### FEZ DENTRO DO HORARIO
            $retorno = 1;
    
        } else if ($real < $intRang) {
            #### FEZ ADIANTADO DO HORARIO
            $retorno = 2;
    
        } else if ($real > $fimRang) {
            #### FEZ ATRASADO DO HORARIO
            $retorno = 3;
        }

        return $retorno;
        
    }


    public function getGraphParams($group = false)
    {
        // $sql = $this->db->prepare("SELECT 
        //     graphPontualColor, graphAdiantadoColor, graphAtrasadoColor, graphNesColor, graphReColor, graphSreColor, graphBarraColor, graphAgendaColor,
        //     graphPontualTxt, graphAdiantadoTxt, graphAtrasadoTxt, graphNesTxt, graphAgendaTxt, graphReTxt, graphSreTxt
        //     FROM parametros");
        // $sql->execute();
        $param = new Parametro;
        $grapParams = $param->getParametros($group);
        // $grapParams = $sql->fetch(PDO::FETCH_ASSOC);

        $prefixMap = array(
            'Pontual' => 1,
            'Adiantado' => 2,
            'Atrasado' => 3,
            'Nes' => 4,
            'Re' => 5,
            'Sre' => 6,
            'Barra' => 7,
            'Agenda' => 8
        );
    
        $groupedParams = array();
        foreach ($grapParams as $key => $value) {
            
            if (preg_match('/^graph([A-Z][a-z]*)/', $key, $matches)) {
                $prefix = $matches[1];
                if (isset($prefixMap[$prefix])) {
                    $numericKey = $prefixMap[$prefix];
                    if (!isset($groupedParams[$numericKey])) {
                        $groupedParams[$numericKey] = array("bg" => "", "txt"=> "", "txtColor" => "");
                    }

                    if (strpos($key, 'Color') !== false) {
                        $groupedParams[$numericKey]['bg'] = $value;
                        $groupedParams[$numericKey]['txtColor'] = $this->getTextColorBasedOnBgColor($value);                        
                    } elseif (strpos($key, 'Txt') !== false) {
                        $groupedParams[$numericKey]['txt'] = $value;
                    }
                }
            }
        }
    
        return $groupedParams;
    }

    public function getTextColorBasedOnBgColor($bgColor) {

        $rgb = $this->hexToRgb($bgColor);
        $r = $rgb['r'];
        $g = $rgb['g'];
        $b = $rgb['b'];
    
        $bright = round(($r * 299 + $g * 587 + $b * 114) / 1000);
        return ($bright > 125) ? 'black' : 'white';
    }

    private function hexToRgb($hex) {
        
        $hex = str_replace('#', '', $hex);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    
        return ['r' => $r, 'g' => $g, 'b' => $b];
    }

}
?>