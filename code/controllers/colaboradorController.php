<?php

ini_set('memory_limit', '-1');

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;

class colaboradorController extends controller 
{

	public function index()
	{

        $pag   = 1;
        $limit = 1; // Option 1 - 15 p/pag, 2 - 30 p/pag, 3 - 50 p/pag , 4 - 100 p/pag
        $unid  = 0;
        $name  = "";

        if( isset($_GET['p']) ) $pag   = $_GET['p'];
        if( isset($_GET['l']) ) $limit = $_GET['l'];
        if( isset($_GET['u']) ) $unid  = $_GET['u'];
        if( isset($_GET['n']) ) $name  = $_GET['n'];

		$dados = array();

        $users 	            = new UserEscala();
        $col                = new Colaborador();
        $paxs               = $col->list( $pag, $limit, $unid, $name );
        $dados['paxs']      = $paxs['users'];
        $dados['ttPages']   = $paxs['total'];
		$dados['unidades']  = $users->getUnidades();
		$this->loadTemplate('colaborador/cadastroPax', $dados);
		exit();
	}

    public function sendFilePax()
    {
      
        if ( isset($_FILES['filePax']) && $_FILES['filePax'] != "" && $_FILES['filePax']['name'] != "")
		{
            $col    = new Colaborador();
            $error  = false;
			$ext    = explode(".", $_FILES['filePax']['name']);
			$ext    = $ext[1];

			if ($ext == "xlsx")
				$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
			else if ($ext == "xls")
				$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
			else if ($ext == "csv")
				$reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
			else{
                $error = true;
                $_SESSION['merr'] = "Nenhum Arquivo Carregado com a extensão permitida: XLSX, XLS, CSV!";
			}

            if($error == false)
            {
                $file 		 = $_FILES['filePax']['tmp_name'];
                $reader->setReadDataOnly(1);
                $spreadsheet = $reader->load($file);
                $sheetData   = $spreadsheet->getActiveSheet()->toArray();
                $arrErros    = array();
                $ids 	     = array();

                foreach ($sheetData as $k => $t) 
                {
                    if ($k > 0)
                    { 
                        $linha = $k + 1;
                        $no = false;

                        if( !isset($t[0]) || $t[0] == "" )
                        {
                            $arrErros[$linha]["desc"][] = "RE";
                            $arrErros[$linha]["has"] = true;
                            $arrErros[$linha]["line"] = $linha;
                            $no = true;
                        }

                        // Salva dos dados no banco
                        if( !isset($t[1]) || $t[1] == "" )
                        {
                            $arrErros[$linha]["desc"][] = "Nome";
                            $arrErros[$linha]["has"] = true;
                            $arrErros[$linha]["line"] = $linha;
                            $no = true;
                        }

                        if($no == false)
                        {
                            $result = $col->insertImportPax( $t );
                            
                            if ( $result['success'] )
                            {
                                $ids[] = $result['id'];
                            } else {

                                $arrErros[$linha]["desc"][] = $result['msg'];
                                $arrErros[$linha]["has"]    = true;
                                $arrErros[$linha]["line"]   = $linha;

                            }
                           
                        }
                       
                    }
                } // End foreach 

                if (count($ids) > 0)
                {
                    // Inativa os que não estão na planilha \\
                    $col->inativeWithNotHas( $ids );
                }

                $_SESSION['ms'] = "Arquivo Carregado com sucesso!";
                $_SESSION['merrary'] = $arrErros;
                if(count($arrErros) != 0){
                    $_SESSION['checkCadPax'] = true;
                }
            }

		} else {

            $_SESSION['merr'] = "Nenhum Arquivo Carregado!";
    
		}

        header("Location: " . BASE_URL . "colaborador");
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

        $dados = array();

        $user 				= new Usuarios();
        $dados['grupos'] 	= $user->acessoGrupo();

        $Pax 		 		= new Pax();
        $dados['linhas']	= $Pax->getLinhasWithSenti();
        $dados['pax']	    = $Pax->getPax($_GET['id']);
     
        $rel 		 		= new Relatorios();
        $dados['residEmb'] 	= $rel->residenciaEmbar();

        $user              = new UserEscala();
		$dados['unidades'] = $user->getUnidades();

        $this->loadTemplate('colaborador/editPax', $dados);
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

        header("Location: " . BASE_URL . "colaborador");
		exit();
    }

 


}