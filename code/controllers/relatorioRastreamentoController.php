<?php

class relatorioRastreamentoController extends controller 
{

	public function index() 
	{
		$dados = array();

		####################### MONTA O FILTRO #######################
		$user 				= new Usuarios();
        $dados['grupos'] 	= $user->acessoGrupo();
        ################## TRATA #################
        if(count($dados['grupos'])>0){
            foreach ($dados['grupos'] as $k => $lin){
                $dados['grupos'][$k]['NOME'] = $lin['NOME'];
            }
        }
        ##############################################################
		##############################################################
		
		$this->loadTemplate('relatorios/rastreamento/relatorioRastreamento', $dados);
		exit;
	}

	public function resultado()
	{
		$dados 			= array();
		$linhas     	= array();
        $error      	= array();
        $html 			= "";

        $req     		= new \stdClass();
        $req->nome 		= $_POST['nome'];
        $req->registro 	= $_POST['registro'];
        $req->grupo 	= $_POST['grupo'];
        $req->dias 		= $_POST['dias'];
        $relg 		 	= new Relatorios();
        $dadosRel   	= $relg->getDadosMapeamentoPassageiro($req);
      
        if(!isset($dadosRel['error']) && $req->registro == "")
        {
            $dados['paxs'] 		= $dadosRel;
            $dados['paxsLeng'] 	= count($dadosRel); 
            echo json_encode($dados);
	    	die();
        }
       
        if(!isset($dadosRel['error']))
        {
          
            foreach($dadosRel AS $k => $rel){
                $rel = (Object) $rel;

                $linhas[$k]['prefixo']      = $rel->PREFIXOLINHA;
                $linhas[$k]['linha']        = $rel->NOMELINHA;
                $linhas[$k]['sentido']      = $rel->SENTIDO == 0 ? "Ida" : "Volta";
                $linhas[$k]['data']         = date("d/m/Y H:i:s", strtotime($rel->HORAMARCACAO));
                $linhas[$k]['passageiros']  = $rel->passageiros;
                $linhas[$k]['idViagem']     = $rel->IDVIAGEM;

                ############### PROCURANDO NA TABELA INTERNA ############## 
                $paxSemCart = array();
                $veic       = $relg->getCarros($rel->IDVEIC);
                if($veic){
                	$veic    = (Object) $veic;
                    $dataIni = date("Y-m-d", strtotime($rel->DATAVIAGEMINIC));
                    $horaIni = date("H:i:s", strtotime($rel->DATAVIAGEMINIC));
                    $dataFim = date("Y-m-d", strtotime($rel->DATAVIAGEMFIM));
                    $horaFim = date("H:i:s", strtotime($rel->DATAVIAGEMFIM));

                    $embSemCart =  $relg->getEmbarquesSemCartao($veic->id, $dataIni, $dataFim, $horaIni, $horaFim);

                    foreach($embSemCart AS $emb){
                        $paxSemCart[] = $emb->nome_passageiro . " - Matricula: " . $emb->registro_passageiro . " - Data: ". date("d/m/Y", strtotime($rel->HORAMARCACAO)) . ' ' . $emb->horario_embarque;
                    }

                }

                $linhas[$k]['paxSemCart'] = $paxSemCart;
                ###########################################################
            }
        } else {
            $error['error'] = $dadosRel['msg'];
        }

        $dados["linhas"] 		= $linhas;
        $dados["linhasLeng"] 	= count($linhas);
        $dados["error"]  		= $error;

        echo json_encode($dados);
    	die();
	}

    public function excel()
    {
      
        $dadosXls = "<table border='1'>";
        $dadosXls .= "<tr>";
        $dadosXls .= "<td style='width:100px'>GRUPO</td>";
        $dadosXls .= "<td style='width:100px'>NOME</td>";
        $dadosXls .= "<td>PREFIXO</td>";
        $dadosXls .= "<td style='width:100px'>LINHA</td>";
        $dadosXls .= "<td>DATA</td>";
        $dadosXls .= "<td>POLTRONA</td>";
        $dadosXls .= "</tr>"; 

        $req            = new \stdClass();
        $req->nome      = $_GET['nome'];
        $req->registro  = $_GET['registro']; 
        $req->grupo     = $_GET['grupo'];
        $req->dias      = $_GET['dias'];
        $grupoName      = $_GET['grupoName'];

        $relg           = new Relatorios();
        $dadosRel       = $relg->getDadosMapeamentoPassageiro($req);

        $feitos     = array();
        $feitosSem  = array();
        $c          = 0;

        foreach($dadosRel as $result){

            $rel = (Object) $result;
            $pax = (Object) $rel->passageiros;
                
            foreach($pax AS $p){
                $p = (Object) $p;
                if(!in_array($p->CODIGO, $feitos)){
                    $feitos[] = $p->CODIGO;
                    $prefNom  = $rel->PREFIXOLINHA . ' - ' . $rel->NOMELINHA;
                    $pol      = isset($rel->pol) ? $rel->pol : "-";

                    $dadosXls .= "<tr>";
                    $dadosXls .= "<td style='width:100px'>".$grupoName."</td>";
                    $dadosXls .= "<td style='width:60px'>".$p->NOME."</td>";
                    $dadosXls .= "<td>".$p->MATRICULA_FUNCIONAL."</td>";
                    $dadosXls .= "<td>".$prefNom."</td>";
                    $dadosXls .= "<td>".date("d/m/Y H:i:s", strtotime($p->HORAMARCACAO))."</td>";
                    $dadosXls .= "<td>{$pol}</td>";
                    $dadosXls .= "</tr>";
                    $c++;
                }
            }  

            ############### PROCURANDO NA TABELA INTERNA ############## 
            $paxSemCart = array();
            $veic       = $relg->getCarros($rel->IDVEIC);
            if($veic){
                $veic    = (Object) $veic;
                $dataIni = date("Y-m-d", strtotime($rel->DATAVIAGEMINIC));
                $horaIni = date("H:i:s", strtotime($rel->DATAVIAGEMINIC));
                $dataFim = date("Y-m-d", strtotime($rel->DATAVIAGEMFIM));
                $horaFim = date("H:i:s", strtotime($rel->DATAVIAGEMFIM));

                $embSemCart =  $relg->getEmbarquesSemCartao($veic->id, $dataIni, $dataFim, $horaIni, $horaFim);

                foreach($embSemCart AS $emb){
                    if(!in_array($emb->registro_passageiro, $feitosSem)){
                        $feitosSem[] = $emb->registro_passageiro;
                        $prefNom = $rel->PREFIXOLINHA . ' - ' . $rel->NOMELINHA;
                        $dadosXls .= "<tr>";
                        $dadosXls .= "<td style='width:100px'>".$grupoName."</td>";
                        $dadosXls .= "<td style='width:60px'>".$emb->nome_passageiro."</td>";
                        $dadosXls .= "<td>".$emb->registro_passageiro."</td>";
                        $dadosXls .= "<td>".$prefNom."</td>";
                        $dadosXls .= "<td>".date("d/m/Y", strtotime($emb->data)) . ' ' . $emb->horario_embarque."</td>";
                        $dadosXls .= "<td>-</td>";
                        $dadosXls .= "</tr>";
                        $c++;
                    }
                }

            }
        }

        $dadosXls .= "</table>";

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment;filename=relatoriorastrea.xls");
        header("Cache-Control:max-age=0");
        header("Cache-Control:max-age=1");

        echo $dadosXls;
         die();
        echo "<script>window.close();</script>";
        die();
    }

}