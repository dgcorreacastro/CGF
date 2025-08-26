<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class cadastroPaxController extends controller 
{

	public function index()
	{

        unset($_SESSION['prev']);
        $dados  = array();
        $dados['recId'] = false;
        if(isset($_GET['recId']) && $_GET['recId'] != "" && isset($_SESSION['cType']) && $_SESSION['cType'] == 1){
            $dados['recId'] = $_GET['recId'];
        }
        $limPag = 20;
        $pag    = 1;
        $unid   = 0;
        $name   = "";
        $gr     = "";
        $cod    = "";
        $mat    = "";
        $int    = 1;
        $withoutGroups = "";
        $cgfid = "";
        $autocad = "";
        $wpic = "";
        $wnpic = "";

        if( isset($_GET['p']) ) $pag   = $_GET['p'];
        if( isset($_GET['u']) ) $unid  = $_GET['u'];
        if( isset($_GET['n']) ) $name  = $_GET['n'];
        if( isset($_GET['gr']) ) $gr  = $_GET['gr'];
        if( isset($_GET['mat']) ) $mat  = $_GET['mat'];
        if( isset($_GET['cod']) ) $cod  = $_GET['cod'];
        if( isset($_GET['int']) ) $int = 0;

        // Para os cadastros vindo do APP que não tem Grupo de Acesso associado
        if ( isset($_GET['withoutGroups']) ) $withoutGroups = $_GET['withoutGroups'];

        //Para mostrar somente cadastrados ou editados pelo APP CGF ID
        if ( isset($_GET['cgfid']) ) $cgfid = $_GET['cgfid'];

        //Para mostrar somente com autocadastro do APP CGF PASS
        if ( isset($_GET['autocad']) ) $autocad = $_GET['autocad'];

        //Para mostrar somente cadastros com fotos
        if ( isset($_GET['wpic']) ) $wpic = $_GET['wpic'];

        //Para mostrar somente cadastros sem fotos
        if ( isset($_GET['wnpic']) ) $wnpic = $_GET['wnpic'];

        $user               = new UserEscala();
		$dados['unidades']  = $user->getUnidades();
        $grIn               = $gr;

        $user 				= new Usuarios();
        $dados['grupos'] 	= $user->acessoGrupo();

        if(!isset($_SESSION['cFret']) && $gr == "")
        { 
            // Em casos de Não Fretamento \\
            $dados['gruposUser']= $user->grupoUsers(true);
            $dados['gruposUserClean']= $user->grupoUsers();
            $grUs               = array();

            if(count($dados['grupos']) > 0)
			{
                foreach ($dados['grupos'] as $grs)
				{
                    $grUs[] = $grs['ID_ORIGIN'];
                }
            }

            $grIn = count($grUs) > 0 ? implode(",", $grUs) : 0; 
        }

        $param 				    = new Parametro();
        $param 				    = $param->getParametros(true);
        $dados['cad_pax_tag']   = $param['cad_pax_tag'] ?? 1;
        $dados['cad_pax_pics']  = $param['cad_pax_pics'] ?? 0;

        $rel                = new Pax();
        $paxs               = $rel->list($grIn, $pag, $unid, $name, $mat, $cod, $int, $withoutGroups, $cgfid, $autocad, $wpic, $wnpic, $limPag);
        $dados['paxs']      = $paxs['users'];
        $dados['ttPages']   = $paxs['ttPages'];
        $dados['total']     = $paxs['total'];
        $dados['ttOnPage']  = $paxs['ttOnPage'];
        $dados['gr']        = $gr;
		$this->loadTemplate('pax/cadastroPax', $dados);
		exit();
	}

    public function create()
    {

        $_SESSION['prev'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/cadastroPax';

        if(isset($_SESSION['cType']) && $_SESSION['cType'] != 1){

            $dados = array();

            $user 				= new Usuarios();
            $dados['grupos'] 	= $user->acessoGrupo();
            $dados['gruposUser']= $user->grupoUsers();

            $Pax 		 		    = new Pax();
            $dados['linhasIda']	    = $Pax->getLinhasWithSenti('0');
            $dados['linhasVolta']	= $Pax->getLinhasWithSenti('1');

            //$rel 		 		= new Relatorios();
            $dados['residEmb'] 	= []; //$rel->residenciaEmbar();

            $user              = new UserEscala();
            $dados['unidades'] = $user->getUnidades();

            $param 				    = new Parametro();
            $param 				    = $param->getParametros(true);
            $dados['cad_pax_pics']  = $param['cad_pax_pics'] ?? 0;
            $dados['cad_pax_tag']   = $param['cad_pax_tag'] ?? 1;

            $this->loadTemplate('pax/createPax', $dados);

        }else{
            header("Location: ".$_SESSION['prev']);
        }

		exit();
    }

    public function salvar()
    {
        $Pax = new Pax();
        $ret = $Pax->saveNewPax($_POST);

        if ( $ret )
        {
            $_SESSION['ms'] = "Cadastro Salvo com sucesso!";
        } else {
            $_SESSION['merr'] = "Ocorreu um erro, tente novamente!";
        }

        header("Location: ".$_SESSION['prev']);
		exit();
    }

    public function itiByLine()
    {

        $dados = array();

        $pax 			= new Pax();
        $dados['itin'] 	= $pax->itinerarioByLine($_POST);
        
        echo json_encode($dados);
        die;
    }

    public function seachPax()
    {

        $dados      = array();
        $pax 	    = new Pax();
        $dadosAnal  = $pax->seachPax($_POST);
        $html       = "";

        foreach($dadosAnal AS $dda)
        {
            $html .= "<tr>
                        <td>" . utf8_encode($dda['NOME']) . "</td>
                        <td>" . utf8_encode($dda['MATRICULA_FUNCIONAL']) . "</td>
                        <td>" . $dda['TAG'] . "</td>
                        <td class='text-center'>
                            <a title='Editar' href='/cadastroPax/edit?id=".$dda['id']."' class='btn btn-primary editIcon'><i class='fas fa-edit'></i></a>
                        </td>
                    </tr>";
        }

        $dados['html'] = $html;
        echo json_encode($dados);
        die;
    }

    public function edit()
    {

        $_SESSION['prev'] = (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'importPax') === false) ? $_SERVER['HTTP_REFERER'] : '/cadastroPax';

        if(isset($_SESSION['cType']) && $_SESSION['cType'] != 1){

            $dados = array();

            $Pax 		 		= new Pax();
            $dados['pax']	    = $Pax->getPax($_GET['id']);

            if(!$dados['pax']['status']){
                $_SESSION['forbidden'] = [
                    "code" => "403",
                    "msg" => "Passageiro não encontrado.",
                ];
                header("Location: /");
                die();
            }

            $user 				= new Usuarios();
            $dados['grupos'] 	= $user->acessoGrupo();

            $dados['linhasIda']	    = $Pax->getLinhasWithSenti('0');
            $dados['linhasVolta']	= $Pax->getLinhasWithSenti('1');
            
        
            //$rel 		 		= new Relatorios();
            $dados['residEmb'] 	= []; // $rel->residenciaEmbar();

            $user              = new UserEscala();
            $dados['unidades'] = $user->getUnidades();

            $param 				    = new Parametro();
            $param 				    = $param->getParametros(true);
            $dados['cad_pax_pics']  = $param['cad_pax_pics'] ?? 0;
            $dados['cad_pax_tag']   = $param['cad_pax_tag'] ?? 1;

            $this->loadTemplate('pax/editPax', $dados);

        }else{
            header("Location: ".$_SESSION['prev']);
        }
        
		exit();
    }

    public function salvarEdit()
    {
    
        $Pax = new Pax();
        $ret = $Pax->saveEditPax($_POST);

        if ( $ret )
        {
            $_SESSION['ms'] = "Edição Salva com sucesso!";
        } else {
            $_SESSION['merr'] = "Ocorreu um erro, tente novamente!";
        }

        header("Location: ".$_SESSION['prev']);
		exit();
    }

    // public function sendFilePax() 
    // {

    //     if ( isset($_FILES['filePax']) && $_FILES['filePax'] != "" && $_FILES['filePax']['name'] != "")
	// 	{
    //         $pax    = new Pax();
    //         $error  = false;
	// 		$ext    = explode(".", $_FILES['filePax']['name']);
	// 		$ext    = $ext[ count($ext) - 1 ];

	// 		if ($ext == "xlsx")
	// 			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
	// 		else if ($ext == "xls")
	// 			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
	// 		else if ($ext == "csv")
	// 			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
	// 		else{
    //             $error = true;
    //             $_SESSION['merr'] = "Nenhum Arquivo Carregado com a extensão permitida: XLSX, XLS, CSV!";
	// 		}

    //         if($error == false)
    //         {
    //             $file 		 = $_FILES['filePax']['tmp_name'];
    //             $reader->setReadDataOnly(1);
    //             $spreadsheet = $reader->load($file);
    //             $sheetData   = $spreadsheet->getActiveSheet()->toArray();
    //             $arrErros    = array();
    //             $arrErroCad  = array();
    //             $ids 	     = array();

    //             foreach ($sheetData as $k => $t) 
    //             {

    //                 if ($k > 0)
    //                 { 
    //                     $linha = $k + 1;
    //                     $no = false;
    //                     // Salva dos dados no banco
    //                     if( !isset($t[0]) || $t[0] == "" ){
    //                         $arrErros[$linha]["desc"][] = "Nome";
    //                         $arrErros[$linha]["has"] = true;
    //                         $arrErros[$linha]["line"] = $linha;
    //                         $no = true;
    //                     }

    //                     if( !isset($t[1]) || $t[1] == "" )
    //                     {
    //                         // Se não for Fretamento é Obrigatório o Grupo
    //                         // Se for fretamento já tem o grupo associado ao usuário
    //                         $arrErros[$linha]["desc"][] = "Grupo";
    //                         $arrErros[$linha]["has"] = true;
    //                         $arrErros[$linha]["line"] = $linha;
    //                         $no = true;
    //                     }

    //                     if($no == false)
    //                     {
    //                         $group = isset($_POST['groupID']) ? $_POST['groupID'] : 0;
    //                         $result = $pax->insertImportPax( $t, $group);
                            
    //                         if ( $result['success'] )
    //                         {

    //                             $ids[ $result['group'] ][] = $result['re'];

    //                         } else {

    //                             if ( isset($result['cad']) )
    //                             {
    //                                 $arrErroCad[$linha]["desc"][] = $result['msg'];
    //                                 $arrErroCad[$linha]["has"]    = true;
    //                             } else {
    //                                 $arrErros[$linha]["desc"][] = $result['msg'];
    //                                 $arrErros[$linha]["has"]    = true;
    //                                 $arrErros[$linha]["line"]   = $linha;
    //                             }

    //                         }
                           
    //                     }
                       
    //                 }
    //             } // End foreach 

    //             if (count($ids) > 0)
    //             {
    //                 // Inativa os que não estão na planilha \\
    //                 $pax->inativeWithNotHas( $ids );
    //             }

    //             $_SESSION['ms'] = "Arquivo Carregado com sucesso!";
    //             $_SESSION['merrary'] = $arrErros;
    //             $_SESSION['merrcad'] = $arrErroCad;
    //             if(count($arrErros) != 0 || count($arrErroCad) != 0){
    //                 $_SESSION['checkCadPax'] = true;
    //             }
    //         }

	// 	} else {

    //         $_SESSION['merr'] = "Nenhum Arquivo Carregado!";
    
	// 	}

    //     header("Location: " . BASE_URL . "cadastroPax");
    //     exit();

    // }

    public function importPax(){

        echo '<script type="text/javascript" src="'.BASE_URL.'assets/js/jquery.min.js"></script>';
        echo '<script type="text/javascript" src="'.BASE_URL.'assets/js/paxProgress.js"></script>';
        echo '<link rel="stylesheet" type="text/css" href="'.BASE_URL.'assets/css/pax.css" />';
        echo '<link rel="stylesheet" type="text/css" href="'.BASE_URL.'assets/css/bootstrap.min.css" />';
        echo '<script src="'.BASE_URL.'assets/js/sweetalert.min.js"></script>';

        if ( isset($_FILES['filePax']) && $_FILES['filePax'] != "" && $_FILES['filePax']['name'] != "")
		{

            $subAuto = (isset($_POST['subAll']) && $_POST['subAll'] == 'on') ? 1 : 0;
            $groupUser = isset($_POST['groupID']) ? $_POST['groupID'] : 0;
            
            $pax = new Pax();
            $ext = explode(".", $_FILES['filePax']['name']);
			$ext = $ext[ count($ext) - 1 ];
                        
            if ($ext == "xlsx")
				$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
			else if ($ext == "xls")
				$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();

            $file = $_FILES['filePax']['tmp_name'];
            $reader->setReadDataOnly(1);
            $spreadsheet = $reader->load($file);
            $sheetData = $spreadsheet->getActiveSheet()->toArray();

            $counter = 0;
            $erros = 0;
            $sucesso = 0;
            $change = 0;
            
            $totalRows = (count($sheetData) - 1);

            $req = new \stdClass();

            $tagsChecked = array();
            
            if(isset($_POST['groupID'])){
                echo '<div class="importPaxGroup">GRUPO USUÁRIO: '.$_POST['groupIDName'].'</div>';
            }

            echo '<div id="listInactive"></div>';
            echo '<div id="percentageInactive"><b id="percentageInactiveTxt">0%</b></div>';
            echo '<div class="percentageInactiveBackdrop"></div>';
            echo '<table id="statusImport" style="display:none;"><thead id="thead"><tr class="headExcel"><th scope="col">Nome</th><th scope="col">Cód Cartão</th><th scope="col">Status</th></tr></thead><tbody id="bodyStatusImport"></tbody></table>';

            foreach ($sheetData as $k => $t) 
            {
                if ($k > 0)
                {

                    //se não tiver nome não irá adicionar
                    //se não tiver a tag não irá adicionar (só aceita números)
                    $add = isset($t[0]) && trim($t[0]) != '' && isset($t[2]) && trim($t[2]) != '' && is_numeric($t[2]) && !in_array(trim($t[2]), $tagsChecked) ? true : false;

                    $tag = trim($t[2]);

                    if($add){

                        //nome obrigatório e se chegou aqui é pq tem
                        $nome = trim($t[0]);

                        array_push($tagsChecked, $tag);
                        
                        //checa se tem id do grupo, se não tiver não irá adicionar
                        //(só aceita números)
                        if(strpos($t[1], "@") !== FALSE){

                            $grupo = $t[1];
                            $grupoID = trim(substr($grupo, strpos($grupo, "@") + 1));

                            if(isset($grupoID) && $grupoID != '' && is_numeric($grupoID)){

                                $grupoName = trim(substr($grupo, 0, strpos($grupo, '@')));

                            }else{

                                $add = false;

                            }


                        }else{

                            $add = false;

                        }

                        //checa se tem id da linha de IDA, se não tiver seta como null
                        //(só aceita números)
                        if(strpos($t[4], "@") !== FALSE){

                            $linhaIda = $t[4];
                            $linhaIdaID = trim(substr($linhaIda, strpos($linhaIda, "@") + 1));
                            $linhaIdaID = (isset($linhaIdaID) && $linhaIdaID != '' && is_numeric($linhaIdaID)) ? $linhaIdaID : null; 
                            // $add = (isset($linhaIdaID) && $linhaIdaID != '' && is_numeric($linhaIdaID));


                        }else{

                            $linhaIdaID = null;
                            // $add = false;

                        }

                        //checa se tem id da linha de VOLTA, se não tiver seta como null
                        if(strpos($t[6], "@") !== FALSE){

                            $linhaVolta = $t[6];
                            $linhaVoltaID = trim(substr($linhaVolta, strpos($linhaVolta, "@") + 1));
                            $linhaVoltaID = (isset($linhaVoltaID) && $linhaVoltaID != '' && is_numeric($linhaVoltaID)) ? $linhaVoltaID : null;

                            // $add = (isset($linhaVoltaID) && $linhaVoltaID != '' && is_numeric($linhaVoltaID));


                        }else{

                            $linhaVoltaID = null;
                            // $add = false;

                        }

                        // matrícula não é obrigatória
                        $matricula = trim($t[3]);

                        //poltrona IDA não é obrigatória
                        $poltronaIda = trim($t[5]);
                        
                        //poltrona VOLTA não é obrigatória
                        $poltronaVolta = trim($t[7]);

                        //endereço não é obrigatório
                        $end = trim($t[8]);

                    }

                    $counter++;

                    $percentage = floor(round( (($counter / $totalRows) * 100), 1 ));
                    
                    if($add){

                        $req->nome          = $nome;
                        $req->tag           = $tag;
                        $req->grupoID       = $grupoID;
                        $req->linhaIdaID    = $linhaIdaID;
                        $req->linhaVoltaID  = $linhaVoltaID;
                        $req->matricula     = $matricula;
                        $req->poltronaIda   = $poltronaIda;
                        $req->poltronaVolta = $poltronaVolta;
                        $req->end           = $end;

                        $ret = $pax->importPax($req, $subAuto, $groupUser);

                        $background = "";
                        $li = "";
                        $color = "white";

                        if(isset($ret['askChange'])){

                            $background = '#ffc107!important';
                            $li = "li-change";
                            $color = "black";
                            $change += 1;


                        }else{

                            $background = $ret['success'] ? '#28a745!important' : '#dc3545!important';
                            $li = $ret['success'] ? 'li-success' : 'li-error';

                            if($ret['success']){
                                $sucesso += 1;
                            }else{
                                $erros += 1;
                            }

                        }
                        
                        echo '<script>$("#listInactive").append(`<div class="paxIten '.$li.'" id="line-'.$k.'" style="background: '.$background.'; color: '.$color.'">
                        <span>Nome: <b>'.$nome.'</b> - </span><span>Cód. Cartão: <b>'.$tag.'</b> - </span><span><b>'.$ret['msg'].'</b></span>
                        </div>`)</script>';
                        echo '<script>$("#bodyStatusImport").append(`<tr><td>'.$nome.'</td><td>'.$tag.'</td><td id="tdstatus-'.$k.'">'.$ret['msg'].'</td></tr>`)</script>';

                        if(isset($ret['paxId']) && isset($_SESSION['cType']) && $_SESSION['cType'] != 1){
                            
                            echo '<script>$("#line-'.$k.'").append(` - <a href="/cadastroPax/edit?id='.$ret['paxId'].'" target="_blank" class="btn btn-primary border-white">Ver Cadastro</a>`)</script>';

                        }

                        if(isset($ret['askChange'])){

                            echo '<script>$("#line-'.$k.'").append(` - <span class="btn btn-primary border-white" title="Encerrar a vigência atual do código: '.$tag.' de '.$ret['oldPaxName'].' (mantendo o histórico de batidas), e criar uma nova para '.$nome.'" onclick="changePax('.$k.')">Substituir Vigência</span>
                            <div id="line-'.$k.'-changePax" style="display:none">
                                <input type="hidden" id="'.$k.'-oldID" value="'.$ret['paxId'].'">
                                <input type="hidden" id="'.$k.'-paxIdOrigin" value="'.$ret['paxIdOrigin'].'">
                                <input type="hidden" id="'.$k.'-oldName" value="'.$ret['oldPaxName'].'">
                                <input type="hidden" id="'.$k.'-nome" value="'.$nome.'">
                                <input type="hidden" id="'.$k.'-tag" value="'.$tag.'">
                                <input type="hidden" id="'.$k.'-grupoID" value="'.$grupoID.'">
                                <input type="hidden" id="'.$k.'-linhaIdaID" value="'.$linhaIdaID.'">
                                <input type="hidden" id="'.$k.'-linhaVoltaID" value="'.$linhaVoltaID.'">
                                <input type="hidden" id="'.$k.'-matricula" value="'.$matricula.'">
                                <input type="hidden" id="'.$k.'-poltronaIda" value="'.$poltronaIda.'">
                                <input type="hidden" id="'.$k.'-poltronaVolta" value="'.$poltronaVolta.'">
                                <input type="hidden" id="'.$k.'-end" value="'.$end.'">
                            </div>`)</script>';

                        }

                        echo '<script>$("html, body").animate({scrollTop:$(document).height()}, "fast")</script>';

                    }

                    echo '<script>$("#percentageInactive").css("width","'.$percentage.'%")</script>';
                    echo '<script>$("#percentageInactiveTxt").html("'.$percentage.'%")</script>';

                    if($percentage == 100){

                        echo '<script>$("#listInactive").append(`<div class="footerPax">
                            <span class="btn btn-success mt-4 align-self-center successPax">SUCESSO: <b>'.$sucesso.'</b></span>
                            <span class="btn btn-danger mt-4 align-self-center errorPax">FALHA: <b>'.$erros.'</b></span>
                            <span class="btn btn-primary mt-4 align-self-center fecharPax" onclick="removeProgressImportPax()">FECHAR</span>
                            </div>`)
                        </script>';

                        if($subAuto == 0){
                            echo '<script>$(".fecharPax").before(`<span class="btn btn-warning mt-4 align-self-center subPax">SUBSTITUIR: <b>'.$change.'</b></span>`)</script>';
                        }

                        if($sucesso > 0 || $erros > 0 || $change > 0){
                            echo '<script>$(".footerPax").prepend(`<span class="btn btn-info mt-4 align-self-center" title="Download em Excel do Resultado da Importação" onclick="downloadImportStatus()"><b>DOWNLOAD EXCEL</b></span>`)</script>';
                        }
                        
                    }

                }

                flush();
                ob_flush();

                if($k > 1){
                    usleep(500000);
                }
            }

        }
    }

    public function changePax(){

        $Pax = new Pax();

        $post = (Object) $_POST;
        
        $save = $Pax->insertImportedPax($post, true);

        echo json_encode($save);
		die();

    }

    public function inactivePax(){

        echo '<script type="text/javascript" src="'.BASE_URL.'assets/js/jquery.min.js"></script>';
        echo '<script type="text/javascript" src="'.BASE_URL.'assets/js/paxProgress.js"></script>';
        echo '<link rel="stylesheet" type="text/css" href="'.BASE_URL.'assets/css/pax.css" />';
        echo '<link rel="stylesheet" type="text/css" href="'.BASE_URL.'assets/css/bootstrap.min.css" />';
        
        if ( isset($_FILES['fileInactivePax']) && $_FILES['fileInactivePax'] != "" && $_FILES['fileInactivePax']['name'] != "")
		{
            $pax = new Pax();
            $ext = explode(".", $_FILES['fileInactivePax']['name']);
			$ext = $ext[ count($ext) - 1 ];

            $groupUser = isset($_POST['groupIDdesativa']) ? $_POST['groupIDdesativa'] : 0;

            if ($ext == "xlsx")
				$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
			else if ($ext == "xls")
				$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
				
            $file = $_FILES['fileInactivePax']['tmp_name'];
            $reader->setReadDataOnly(1);
            $spreadsheet = $reader->load($file);
            $sheetData   = $spreadsheet->getActiveSheet()->toArray();
            
            $counter = 0;
            $erros = 0;
            $sucesso = 0;

            $totalRows = (count($sheetData) - 1);

            $tagsChecked = array();

            if(isset($_POST['groupIDdesativa'])){
                echo '<div class="importPaxGroup">GRUPO USUÁRIO: '.$_POST['groupIDNameDesativa'].'</div>';
            }

            echo '<div id="listInactive"></div>';
            echo '<div id="percentageInactive"><b id="percentageInactiveTxt">0%</b></div>';
            echo '<div class="percentageInactiveBackdrop"></div>';

			foreach ($sheetData as $k => $t) 
            {
                if ($k > 0)
                { 
                    $tag = trim($t[0]);

                    $counter++;

                    $percentage = floor(round( (($counter / $totalRows) * 100), 1 ));

                    if(!isset($tag) || $tag == "" || !is_numeric($tag) || in_array($tag, $tagsChecked)){

                        echo '<script>$("#percentageInactive").css("width","'.$percentage.'%")</script>';
                        echo '<script>$("#percentageInactiveTxt").html("'.$percentage.'%")</script>';

                        if($percentage == 100){

                            echo '<script>$("#listInactive").append(`<div class="footerPax">
                                <span class="btn btn-success mt-4 align-self-center successPax">SUCESSO: <b>'.$sucesso.'</b></span>
                                <span class="btn btn-danger mt-4 align-self-center errorPax">FALHA: <b>'.$erros.'</b></span>
                                <span class="btn btn-primary mt-4 align-self-center" onclick="removeProgressInactivePax()">FECHAR</span>
                                </div>`)
                            </script>';
                            
                        }

                        continue;
                    }                    

                    array_push($tagsChecked, $tag);
                    $ret = $pax->setInativePax($tag, $groupUser);
                    $background = $ret['success'] ? '#28a745!important' : '#dc3545!important';
                    $li = $ret['success'] ? 'li-success' : 'li-error';

                    if($ret['success']){
                        $sucesso += 1;
                    }else{
                        $erros += 1;
                    }
                    
                    echo '<script>$("#listInactive").append(`<div class="paxIten '.$li.'" style="background: '.$background.'"><span>Cód. Cartão: <b>'.$tag.'</b> - </span><span><b>'.$ret['msg'].'</b></span></div>`)</script>';
                    echo '<script>$("#percentageInactive").css("width","'.$percentage.'%")</script>';
                    echo '<script>$("#percentageInactiveTxt").html("'.$percentage.'%")</script>';
                    echo '<script>$("html, body").animate({scrollTop:$(document).height()}, "fast")</script>';
                    
                    if($percentage == 100){

                        echo '<script>$("#listInactive").append(`<div class="footerPax">
                            <span class="btn btn-success mt-4 align-self-center successPax">SUCESSO: <b>'.$sucesso.'</b></span>
                            <span class="btn btn-danger mt-4 align-self-center errorPax">FALHA: <b>'.$erros.'</b></span>
                            <span class="btn btn-primary mt-4 align-self-center" onclick="removeProgressInactivePax()">FECHAR</span>
                            </div>`)
                        </script>';
                        
                    }
                }

                flush();
                ob_flush();

                if($k > 1){
                    usleep(500000);
                }
                 
            }            

        }

        die();
    }

    public function eraseBasePax(){

        echo '<script type="text/javascript" src="'.BASE_URL.'assets/js/jquery.min.js"></script>';
        echo '<script type="text/javascript" src="'.BASE_URL.'assets/js/paxProgress.js"></script>';
        echo '<link rel="stylesheet" type="text/css" href="'.BASE_URL.'assets/css/pax.css" />';
        echo '<link rel="stylesheet" type="text/css" href="'.BASE_URL.'assets/css/bootstrap.min.css" />';
        echo '<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>';

        if ( isset($_POST['groupIDerase']) && $_POST['groupIDerase'] != 0){

            $groupUser = $_POST['groupIDerase'];
            $groupName = $_POST['groupIDNameErase'];

            echo '<div class="importPaxGroup">LIMPAR BASE GRUPO: '.$groupName.'</div>';

            echo '<div id="listInactive">
                <div id="loadingPaxErase" class="show">
                <div class="loader">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                    <b>Carregando base de passageiros do grupo '.$groupName.'</b>
                </div>
            </div>';
            
            $pax = new Pax();

            $paxToClean = $pax->getPaxToClean($groupUser);

            if(!$paxToClean['success']){

                echo '<script>

                        $("#listInactive").append(`<div class="errorReadErasePax bg-danger">'.$paxToClean['msg'].': '.$groupName.'</div>
                        <div class="footerPax">
                            <span class="btn btn-primary mt-4 align-self-center" onclick="removeProgressErasePax()">FECHAR</span>
                        </div>`);
                        $("#loadingPaxErase").remove();
                    
                    </script>';

                    die();
            }

            echo '<script>$("#loadingPaxErase").remove()</script>';

            echo '<div id="percentageInactive"><b id="percentageInactiveTxt">0%</b></div>';
            echo '<div class="percentageInactiveBackdrop"></div>';

            $toClean = $paxToClean['pax'];

            $erros = 0;
            $sucesso = 0;

            $total = count($toClean);

            foreach($toClean as $k => $p){

                $counter = ($k + 1);
                $percentage = floor(round( (($counter / $total) * 100), 1 ));

                $ret = $pax->erasePax($p['id'], $p['ID_ORIGIN'], $p['TAG']);

                $background = $ret['success'] ? '#28a745!important' : '#dc3545!important';
                $li = $ret['success'] ? 'li-success' : 'li-error';

                if($ret['success']){
                    $sucesso += 1;
                }else{
                    $erros += 1;
                }
                       
                echo '<script>$("#listInactive").append(`<div class="paxIten '.$li.'" id="'.$p['id'].'" idOrigin="'.$p['ID_ORIGIN'].'" tag="'.$p['TAG'].'" nome="'.$p['NOME'].'" style="background: '.$background.'"><span>Nome: <b>'.$p['NOME'].'</b> - </span><span>Cód. Cartão: <b>'.$p['TAG'].'</b> - </span><span><b>'.$ret['msg'].'</b></span></div>`)</script>';
                echo '<script>$("#percentageInactive").css("width","'.$percentage.'%")</script>';
                echo '<script>$("#percentageInactiveTxt").html("'.$percentage.'%")</script>';

                if($percentage == 100){

                    echo '<script>$("#listInactive").append(`<div class="footerPax">
                        <span class="btn btn-success mt-4 align-self-center successPax">SUCESSO: <b>'.$sucesso.'</b></span>
                        <span class="btn btn-danger mt-4 align-self-center errorPax">FALHA: <b>'.$erros.'</b></span>
                        <span class="btn btn-primary mt-4 align-self-center" onclick="removeProgressErasePax()">FECHAR</span>
                        </div>`)
                    </script>';
                    
                }

                flush();
                ob_flush();

                if($k > 1){
                    usleep(100000);
                }

            }

        }
        
        die();
    }

    public function excelImportPax()
    {

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        $sheet = $spreadsheet->getActiveSheet(0);

        ############ MONTANDO O HEADER ###################
            ##################################################
            $sheet->setCellValue('A1', 'Nome Completo');
            $sheet->setCellValue('B1', 'Grupo');
            $sheet->setCellValue('C1', 'Cód. Cartão');
            $sheet->setCellValue('D1', 'Matrícula');
            $sheet->setCellValue('E1', 'Pref. Ida');
            $sheet->setCellValue('F1', 'Poltrona Ida');
            $sheet->setCellValue('G1', 'Pref. Volta');
            $sheet->setCellValue('H1', 'Poltrona Volta');
            $sheet->setCellValue('I1', 'End. Residência');
        ############### FIM DO HEADER ######################
        ###################################################

        ############ MONTANDO O BODY ###################
            ##################################################
            $sheet->setCellValue('A2', '');
            $sheet->setCellValue('B2', '');
            $sheet->setCellValue('C2', '');
            $sheet->setCellValue('D2', '');
            $sheet->setCellValue('E2', '');
            $sheet->setCellValue('F2', '');
            $sheet->setCellValue('G2', '');
            $sheet->setCellValue('H2', '');
            $sheet->setCellValue('I2', '');
        ############### FIM DO BODY ######################
        ###################################################

        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(35);

        $styleArray = array(
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => array('argb' => '00000000'),
                ),
            ),
            'fill' => array(
                'fillType' => Fill::FILL_SOLID,
                'startColor' => array('argb' => 'FFFFFF00')
            )
        );

        $sheet->setTitle("Passageiros");
        $sheet->getStyle('A1:I1')->applyFromArray($styleArray);

        $dados = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Seletores');
        $spreadsheet->addSheet($dados);
        $dados->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_VERYHIDDEN);

        $groupUser  = $_GET['groupID'];
        $user       = new Usuarios();
        $Pax        = new Pax();
        
        //grupos
        $grupos 	= $groupUser > 0 ? $Pax->getGroupMultiUser($groupUser) : $user->acessoGrupo();
    
        $gruposExel = array();

        foreach($grupos as $gr){
            $grupo = trim($gr['NOME'].' @'.$gr['ID_ORIGIN']);
            array_push($gruposExel, $grupo);
        }
         
        if(count($gruposExel) > 0){
            
            foreach($gruposExel as $k => $gr){

                $dados->setCellValue('A'.($k+1), $gr);
                
            }

            $validation = $sheet->getCell('B2')->getDataValidation();
            $validation->setType( DataValidation::TYPE_LIST );
            $validation->setErrorStyle( DataValidation::STYLE_INFORMATION );
            $validation->setAllowBlank(false);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Erro');
            $validation->setError('Grupo não está na lista.');
            $validation->setPromptTitle('Escolha da lista');
            $validation->setPrompt('Escolha um Grupo na lista');
            $validation->setFormula1('\'Seletores\'!$A$1:$A$'.count($gruposExel).'');
            
        }

        //linhas ida
        $linhasIda = $groupUser > 0 ? $Pax->getLinhasExcelAdm($groupUser, '0') : $Pax->getLinhasWithSenti('0');
        
        $linhasIdaExel = array();

        foreach($linhasIda as $lIda){
            $idalinha = trim($lIda['PREFIXO'].' @'.$lIda['id']);
            array_push($linhasIdaExel, $idalinha);
        }

        if(count($linhasIdaExel) > 0){
            
            foreach($linhasIdaExel as $k => $lida){

                $dados->setCellValue('B'.($k+1), $lida);
                
            }

            $validation = $sheet->getCell('E2')->getDataValidation();
            $validation->setType( DataValidation::TYPE_LIST );
            $validation->setErrorStyle( DataValidation::STYLE_INFORMATION );
            $validation->setAllowBlank(false);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Erro');
            $validation->setError('Linha não está na lista.');
            $validation->setPromptTitle('Escolha da lista');
            $validation->setPrompt('Escolha uma Linha de Ida');
            $validation->setFormula1('\'Seletores\'!$B$1:$B$'.count($linhasIdaExel).'');
            
        }

        //linhas volta
        $linhasVolta = $groupUser > 0 ? $Pax->getLinhasExcelAdm($groupUser, '1') : $Pax->getLinhasWithSenti('1');
        
        $linhasVoltaExel = array();

        foreach($linhasVolta as $lVolta){
            $voltalinha = trim($lVolta['PREFIXO'].' @'.$lVolta['id']);
            array_push($linhasVoltaExel, $voltalinha);
        }

        if(count($linhasVoltaExel) > 0){
            
            foreach($linhasVoltaExel as $k => $lvolta){

                $dados->setCellValue('C'.($k+1), $lvolta);
                
            }

            $validation = $sheet->getCell('G2')->getDataValidation();
            $validation->setType( DataValidation::TYPE_LIST );
            $validation->setErrorStyle( DataValidation::STYLE_INFORMATION );
            $validation->setAllowBlank(false);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Erro');
            $validation->setError('Linha não está na lista.');
            $validation->setPromptTitle('Escolha da lista');
            $validation->setPrompt('Escolha uma Linha de Volta');
            $validation->setFormula1('\'Seletores\'!$C$1:$C$'.count($linhasVoltaExel).'');
            
        }

        setcookie('importPaxExcel', 'ready', -1, '/'); 
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment;filename=MODELOCGFIMPORT.xls");
        header("Cache-Control:max-age=0");
        header("Cache-Control:max-age=1");

        $writer = new Xls($spreadsheet);
        $writer->save('php://output');
        die;

    }

    public function newgroup()
    {
        $arr = array();

        if(isset($_SESSION['cType']) && $_SESSION['cType'] == 1)
        {
            if ( isset($_POST['groupUserID']) && $_POST['groupUserID'] == 0 )
            {
                $arr['success'] = false;
                $arr['msg']     = "Selecione um Grupo de Usuário!";
                echo json_encode($arr);

                exit();
            }

        } else {
            // Se não for usuário  ADMIN pega o code user
            $_POST['groupUserID'] = $_SESSION['groupUserID'];
        }

        $Pax = new Pax();
        $ret = $Pax->saveNewGroup($_POST);

        if ( $ret['success'] )
        {
            $arr['success'] = true;
            $arr['msg']     = $ret['msg'];
            $arr['id']      = $ret['id'];
            $arr['nome']    = $ret['nome'];
        } else {
            $arr['success'] = false;
            $arr['msg']     = isset($ret['msg']) ? $ret['msg'] : "Ocorreu um erro, atualize a pagina e tente novamente!";
        }

        echo json_encode($arr);

		exit();
    }

    public function deleteLineExist()
    {

        $Pax = new Pax();
        $ret = $Pax->deleteLineExist($_POST['id']);
        $arr = array();
        
        if ( $ret )
        {
            $arr['success'] = true;
            $arr['msg']     = "Deletado com sucesso!";
        } else {
            $arr['success'] = false;
            $arr['msg'] = "Ocorreu um erro, tente novamente!";
        }

       echo json_encode($arr);
       die;
    }

    public function getLinesExtras()
    {
        $Pax = new Pax();
        $ret = $Pax->getLinesExtras($_POST['id']);
        $arr = array();
        
        if ( $ret )
        {
            $arr['success'] = true;
            $arr['data']    = $ret;
        } else {
            $arr['success'] = false;
            $arr['msg']     = "Ocorreu um erro, tente novamente!";
        }

       echo json_encode($arr);
       die;
    }

    public function existTag()
    {
        $pax        = new Pax();
        $checkPax   = $pax->existTag($_POST);

        echo json_encode($checkPax);
        die;
    }

    public function linhas()
	{
		$dados = array();	
		
		$linhas 			= new Relatorios();
		$dados['linhas']	= $linhas->getLinhas();

		################## TRATA LINHAS #################
		if(count($dados['linhas'])>0){
			foreach ($dados['linhas'] as $k => $lin){
				$dados['linhas'][$k]['NOME'] = $lin['NOME'];
			}
		}
		#################################################

		$this->loadTemplate('linhas/linhas', $dados);
		exit();
	}

    public function paxqrcode()
    {
        if (!isset($_GET['idpax']) || $_GET['idpax'] == '')
            die("Sem os parâmetros necessários!");
        
        if(isset($_SESSION['cType']) && $_SESSION['cType'] != 1){

            if(is_numeric($_GET['idpax'])){
                $Pax 	= new Pax();
                $Pax    = $Pax->getPax($_GET['idpax']);

                if(!$Pax['status']){
                    die("Passageiro não encontrado.");
                }
            }

            $dados = [
                'link' => 'id=' .$_GET['idpax']. '&pos=' .$_GET['position'],
                'title' => $_GET['position'],
                'show_print' => 0
            ];

            $this->loadTemplateExterno('printqrcodes/index', $dados);

        }else{
            die("Passageiro não encontrado.");
        }
        
		exit();
    }

    public function appTakePicture()
    {
        $app            = new App();
        $appTakePicture = $app->appTakePicture($_POST);

        echo json_encode($appTakePicture);
        die;
    }
    
    public function removeAppTakePicture(){
        $app                    = new App();
        $removeAppTakePicture   = $app->removeAppTakePicture($_POST);

        echo json_encode($removeAppTakePicture);
        die;
    }


    public function removeUserPhoto()
    {
        $pax                = new Pax();
        $removeUserPhoto    = $pax->removeUserPhoto($_POST);

        echo json_encode($removeUserPhoto);
        die;
    }   


}