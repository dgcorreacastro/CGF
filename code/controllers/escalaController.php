<?php

class escalaController extends controller 
{

    public $array = array("Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro");

	public function index()
	{
        // Preparate filters
        $pag   = 1;
        $limit = 1; // Option 1 - 15 p/pag, 2 - 30 p/pag, 3 - 50 p/pag , 4 - 100 p/pag
        $unid  = 0;
        $setor = 0;
        $gest  = 0;
        $mes   = 0;
        $ano   = 0;
        $stts  = 0;

        if( isset($_GET['p']) ) $pag   = $_GET['p']; // Pagina
        if( isset($_GET['l']) ) $limit = $_GET['l']; // Limit
        if( isset($_GET['u']) ) $unid  = $_GET['u']; // Unidade
        if( isset($_GET['u']) ) $setor = $_GET['s']; // Setor
        if( isset($_GET['u']) ) $gest  = $_GET['ld']; // Gestor
        if( isset($_GET['u']) ) $mes   = $_GET['mf']; // Mes
        if( isset($_GET['u']) ) $ano   = $_GET['af']; // Ano
        if( isset($_GET['u']) ) $stts  = $_GET['stts']; // Status

        $data               = array();
        $users 	            = new UserEscala();
        $escala             = new EscalaTrabalho();
		$dataRet            = $escala->list('1,2,3,4', $pag, $limit, $unid, $setor, $gest, $mes, $ano, $stts);
        $data['ret']        = $dataRet['escalas'];
        $data['ttPages']    = $dataRet['total'];
		$data['unidades']   = $users->getUnidades();
        $data['lider']      = $users->getLideres();
		$data['setores']    = $escala->getSetor();
        
		$this->loadTemplate('escalaTrabalho/escala/index', $data);
		exit();
	}

    public function create()
    {
        $data           = array();
        $daysMon        = array();
        $nowY           = date("Y");
        $last           = $nowY + 1;
        $ano            = array( $nowY, $last );

        $user           = new UserEscala();
        $setor 	        = new Setores();
        $parameters 	= new ParameterEscala();

        $allDays        = date("t", strtotime("Y-m"));
        $month          = date("m");
        $year           = date("Y");
        $parm           = $parameters->getByDate( ($month -1) );

        for($i = 1; $i <= $allDays; $i++)
            $daysMon[$i] = $this->getLetter($i, $month, $year);

        if(isset($_SESSION['cFret']) && $_SESSION['cType'] == 1)
            $data['disabled'] = 'disabled';

        $data['disabled']   = '';
        $data['meses']      = $this->array;
        $data['ano']        = $ano;
        $data['lider']      = $user->getLideres();
		$data['unidades']   = $user->getUnidades();
        $data['setores']    = $setor->list();
        $data['mActu']      = date("m");
        $data['aActu']      = date("Y");
        $data['daysMon']    = $daysMon;
        $data['mxfm']       = $parm ? $parm->maxFolgaMes  : "";
        $data['mxsf']       = $parm ? $parm->maxDiaSemFolga  : "";
        
        $this->loadTemplate('escalaTrabalho/escala/create', $data);
		exit();
    }

    public function monthYear()
    {
        $parameters = new ParameterEscala();
        $return     = array();
        $array      = array();
        $month      = $_POST['mes'];
        $year       = $_POST['ano'];
        $unid       = $_POST['unidade'];
        $allDays    = date("t", strtotime($year ."-" . $month));

        for($i = 1; $i <= $allDays; $i++)
        {
            $array[$i] = $this->getLetter($i, $month, $year);
        }

        $parm           = $parameters->getByDate( ($month -1 ), $unid);

        $return['days'] = $array;
        $return['tt']   = count($array);
        $return['mxfm'] = $parm ? $parm->maxFolgaMes  : "";
        $return['mxsf'] = $parm ? $parm->maxDiaSemFolga  : "";

        echo json_encode($return);

        return true;
    }

    private function getLetter($day, $mon, $year)
    {
        $arr        = array('letter' => " - ", 'color' => false);
        $descrDa    = date("D", strtotime($year .'-'. $mon .'-'. $day));

        switch ($descrDa) {
            case 'Sun': $arr['letter'] = "D"; $arr['color'] = true; break;
            case 'Mon': $arr['letter'] = "S"; break;
            case 'Tue': $arr['letter'] = "T"; break;
            case 'Wed': $arr['letter'] = "Q"; break;
            case 'Thu': $arr['letter'] = "Q"; break;
            case 'Fri': $arr['letter'] = "S"; break;
            case 'Sat': $arr['letter'] = "S";$arr['color'] = true; break;
        }

        return $arr;
    }

    public function collaboradorEscala()
    {

        $arr        = array();
        $Pax        = new EscalaTrabalho();
        $arr['pax'] = $Pax->paxEscala($_POST);
    
        echo json_encode($arr);
        exit;
    }

    public function paxSetor()
    {
        $arr        = array();
        $Pax        = new EscalaTrabalho();
        $arr['pax'] = $Pax->paxSetor($_POST);

        echo json_encode($arr);
        exit;
    }

    public function store()
    {
        /// Remove unset gerais \\\
		unset($_SESSION['old']);
        $save = false;
        $setor              = new EscalaTrabalho();

        $_POST['userID']    = $_SESSION['cLogin'];
		$save 	            = $setor->save($_POST);

        if(is_array($save) && $save['error'] == true)
        {
            $_SESSION['merr'] = $save['msg'];
            $_SESSION['old']  = $_POST;

            unset($_POST);
            header("Location: " . BASE_URL . "escala/create");

        } else if(is_array($save) && $save['error'] == false){

            if($_POST['typeEscale'] == 1){
                $_SESSION['ms'] = "Cadastrado com sucesso!";
                $location = BASE_URL . "escala/edit?id=" . $save['novaEscalaId'];
            }
            else{
                $_SESSION['ms'] = "Enviado com sucesso!";
                $location = BASE_URL . "escala/";
            }
            
            unset($_POST);
            header('Location: ' . $location);

        }else{

            $_SESSION['merr'] = "Ocorreu um erro ao cadastrar, tente novamente!";
            $_SESSION['old']  = $_POST;

            unset($_POST);
            header("Location: " . BASE_URL . "escala/create");

        }
		
		exit();
    }

    public function show()
    {
        $user           = new UserEscala();
        $grupos 	    = new Relatorios();
        $setor 	        = new Setores();
        $escalaTrab     = new EscalaTrabalho();
        $dtEscala       = $escalaTrab->get($_GET['id']);

        $data           = array();
        $daysMon        = array();
        $nowY           = $dtEscala['escala']->ano;
        $month          = $dtEscala['escala']->mes;
        $ano            = array( $nowY );
     
        $allDays        = date("t", strtotime($nowY . "-" . $month));

        for($i = 1; $i <= $allDays; $i++)
        {
            $daysMon[$i] = $this->getLetter($i, $month, $nowY);
        }

		$data['grupos']     = $grupos->getGrupos();
        $data['meses']      = $this->array;
        $data['ano']        = $ano;
        $data['lider']      = $user->getLideres();
		$data['unidades']   = $user->getUnidades();
        $data['escal']      = $dtEscala;
        $data['setores']    = $setor->list();
        $data['mActu']      = $month;
        $data['aActu']      = $nowY;
        $data['daysMon']    = $daysMon;
    
        $this->loadTemplate('escalaTrabalho/escala/show', $data);
		exit();
    }

    public function edit()
    {
        $user           = new UserEscala();
        $grupos 	    = new Relatorios();
        $setor 	        = new Setores();
        $escalaTrab     = new EscalaTrabalho();
        $dtEscala       = $escalaTrab->get($_GET['id']);

        $data           = array();
        $daysMon        = array();
        $nowY           = $dtEscala['escala']->ano;
        $month          = $dtEscala['escala']->mes;
        $last           = $nowY + 1;
        $ano            = array( $nowY, $last );
        $parameters     = new ParameterEscala();
        $parm           = $parameters->getByDate( ($month -1 ), $dtEscala['escala']->unidadeID);
     
        $allDays        = date("t", strtotime($nowY . "-" . $month));

        for($i = 1; $i <= $allDays; $i++)
        {
            $daysMon[$i] = $this->getLetter($i, $month, $nowY);
        }

		$data['grupos']     = $grupos->getGrupos();
        $data['meses']      = $this->array;
        $data['ano']        = $ano;
        $data['lider']      = $user->getLideres();
		$data['unidades']   = $user->getUnidades();
        $data['escal']      = $dtEscala;
        $data['setores']    = $setor->list();
        $data['mActu']      = $month;
        $data['aActu']      = $nowY;
        $data['daysMon']    = $daysMon;
        $data['mxfm']       = $parm ? $parm->maxFolgaMes  : "";
        $data['mxsf']       = $parm ? $parm->maxDiaSemFolga  : "";
    
        $this->loadTemplate('escalaTrabalho/escala/edit', $data);
		exit();
    }

    public function copy()
    {
        $user           = new UserEscala();
        $grupos 	    = new Relatorios();
        $setor 	        = new Setores();
        $escalaTrab     = new EscalaTrabalho();
        $dtEscala       = $escalaTrab->get($_GET['id']);

        $data           = array();
        $daysMon        = array();
        $nowY           = $dtEscala['escala']->ano;
        $month          = $dtEscala['escala']->mes;
        $last           = $nowY + 1;
        $ano            = array( $nowY, $last );
        $parameters     = new ParameterEscala();
        $parm           = $parameters->getByDate( ($month -1 ), $dtEscala['escala']->unidadeID);
     
        $allDays        = date("t", strtotime($nowY . "-" . $month));

        for($i = 1; $i <= $allDays; $i++)
        {
            $daysMon[$i] = $this->getLetter($i, $month, $nowY);
        }

		$data['grupos']     = $grupos->getGrupos();
        $data['meses']      = $this->array;
        $data['ano']        = $ano;
        $data['lider']      = $user->getLideres();
		$data['unidades']   = $user->getUnidades();
        $data['escal']      = $dtEscala;
        $data['setores']    = $setor->list();
        $data['mActu']      = $month;
        $data['aActu']      = $nowY;
        $data['daysMon']    = $daysMon;
        $data['mxfm']       = $parm ? $parm->maxFolgaMes  : "";
        $data['mxsf']       = $parm ? $parm->maxDiaSemFolga  : "";
        $data['copy']       = true;
        $this->loadTemplate('escalaTrabalho/escala/copy', $data);
		exit();
    }

    public function update()
    {
  
        $setor  = new EscalaTrabalho();
		$save 	= $setor->update($_POST);

        if(is_array($save) && $save['error'] == true)
        {
            $_SESSION['merr'] = $save['msg'];

            unset($_POST);
            header("Location: " . BASE_URL . "escala/edit?id=" . $_POST['id'] );

        } else if($save){

            if($_POST['typeEscale'] == 1){
                $_SESSION['ms'] = "Editado com sucesso!";
                $location = $_SERVER['HTTP_REFERER'];
            }
            else{
                $_SESSION['ms'] = "Enviado com sucesso!";
                $location = BASE_URL . "escala/";
            }
            
            unset($_POST);
            header('Location: ' . $location);

        }else{

            $_SESSION['merr'] = "Ocorreu um erro ao editar, tente novamente!";

            unset($_POST);
            header("Location: " . BASE_URL . "escala/edit?id=" . $_POST['id'] );

        }
		
		exit();
    }
    
    public function delete()
    {
        $retorno = array(
			"status" => true,
			"title" => "SUCESSO",
			"text" => "Usuário com sucesso!",
			"icon" => "success",
			"button" => "OK"
		);

		$esc = new EscalaTrabalho();

		$delete = $esc->delete($_GET['id']);

		if(!$delete['success']){
			$retorno['title'] =  "ERRO";
			$retorno['icon'] = "error";
		}

		$retorno['status'] = $delete['success'];
		$retorno['text'] = $delete['msg'];

		echo json_encode($retorno);
        die();
        
    }

    public function collaboradorEscalaDelete()
    {
        $arr        = array();
        $Pax        = new EscalaTrabalho();
        $arr['pax'] = $Pax->paxEscalaDelete($_POST['id']);

        echo json_encode($arr);
        exit;
    }

    public function updateItemEscala()
    {
        $arr        = array();
        $Pax        = new EscalaTrabalho();
        $arr['pax'] = $Pax->itemUpdateEscala($_POST['id'], $_POST['c'], $_POST['v']);

        echo json_encode($arr);
        exit;
    }

    public function print()
    {
        if ( !isset($_GET['id']) || $_GET['id'] == "" )
        {
            $_SESSION['merr'] = "Ocorreu um erro, tente novamente!";
            header("Location: " . BASE_URL . "escala/");
        }

        ///// Busca os dados da escala \\\\\
        $data           = array();
        $daysMon        = array();
        $setor 	        = new Setores();
        $escalaTrab     = new EscalaTrabalho();
        $dtEscala       = $escalaTrab->get($_GET['id']);

        // Caso não encontre a Escala
        if (!$dtEscala)
        {
            $_SESSION['merr'] = "Ocorreu um erro, tente novamente!";
            header("Location: " . BASE_URL . "escala/");
        }
        
        $nowY           = $dtEscala['escala']->ano;
        $month          = $dtEscala['escala']->mes;
        $allDays        = date("t", strtotime($nowY . "-" . $month));

        for($i = 1; $i <= $allDays; $i++)
        {
            $daysMon[$i] = $this->getLetter($i, $month, $nowY);
        }

        $data['escal']  = $dtEscala;
        $data['setor']  = isset($dtEscala['escala']->setor) ? $setor->nameSetor( $dtEscala['escala']->setor ) : "-";
        $data['infoMes']= $this->array[$dtEscala['escala']->mes - 1];
        $data['infoAno']= $dtEscala['escala']->ano;
        $data['daysMon']= $daysMon;

        $this->loadTemplateExterno('escalaTrabalho/escala/printEscala', $data);
		exit();
    }

    public function centroCustoDescr()
    {
        $arr = array('txt' => "");

        $escala     = new EscalaTrabalho();
		$arr['txt'] = $escala->getCentroCusto($_POST['cc']);

        echo json_encode($arr);
        die;
    }

    public function getUnByLider()
    {
        $user = new UserEscala();
        echo json_encode($user->getUnByLider($_POST['id']));
        die;
    }

}