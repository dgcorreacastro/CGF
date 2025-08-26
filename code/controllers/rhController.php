<?php

class rhController extends controller 
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

        $data           = array();
        $escala         = new EscalaTrabalho();
        $dataRet        = $escala->list("2, 3", $pag, $limit, $unid, $setor, $gest, $mes, $ano, $stts); // "2, 3" array status
		$data['ret']    = $dataRet['escalas'];
        $data['ttPages']= $dataRet['total'];

        $users 	            = new UserEscala();
		$data['unidades']   = $users->getUnidades();
        $data['lider']      = $users->getLideres();
		$data['setores']    = $escala->getSetor();
    
		$this->loadTemplate('escalaTrabalho/rh/index', $data);
		exit();
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

    public function isWeekend()
    {
        $ret     = array('isWeek' => "N");
        $descrDa = date("D", strtotime($_POST['pro']));

        switch ($descrDa) 
        {
            case 'Sun': $ret['isWeek'] = "D"; break;
            case 'Sat': $ret['isWeek'] = "S"; break;
        }

        echo json_encode($ret);
        die;
    }

    public function show()
    {
        $setor 	        = new Setores();
        $escalaTrab     = new EscalaTrabalho();
        $users 	        = new UserEscala();
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
        
        $data['meses']   = $this->array;
        $data['ano']     = $ano;
		$data['unidades']= $users->getUnidades();
        $data['lider']   = $users->getLideres();
        $data['escal']   = $dtEscala;
        $data['setores'] = $setor->list();
        $data['mActu']   = $month;
        $data['aActu']   = $nowY;
        $data['daysMon'] = $daysMon;
    
        $this->loadTemplate('escalaTrabalho/rh/show', $data);
		exit();
    }

    public function sendResponseRh()
    {   
        $arr        = array();
        $Pax        = new EscalaTrabalho();
        $m          = isset($_POST['motive']) ? $_POST['motive'] : "";
    
        $arr['pax'] = $Pax->updateRH($_POST['sdfe'], $_POST['tp'], $m);

        if( $arr['pax'] )
        {
            $st = "";

            if ( $_POST['tp'] == 1 ){
                $st = " APROVADO";
            } else { 
                $st = " RECUSADO. MOTIVO: " . $m ;
            }
        
            $mail     = $Pax->getMailLider($_POST['sdfe']);
            $subject  = "Interação com o RH";
            $mensagem = utf8_decode("Olá, houve interação do RH com a sua Escala.");
            $mensagem .= " Entre no ".PORTAL_NAME." para uma melhor analise.";
            $mensagem .= " STATUS: ". $st;
            
            if( $mail )
            {
                $e      = $mail->email;
                $para   = array($e);
                $this->sendMail($para, $mensagem, $subject);
            } 
           
        }

        echo json_encode($arr);
        exit;
    }

    public function printRes()
    {

        if ( !isset($_GET['id']) || $_GET['id'] == "" )
        {
            $_SESSION['merr'] = "Ocorreu um erro, tente novamente!";
            header("Location: " . BASE_URL . "escala/");
        }

        ///// Busca os dados da escala \\\\\
        $data           = array();
        $daysMon        = array();
        $escalaTrab     = new EscalaTrabalho();
        $dtEscala       = $escalaTrab->getRestaurante($_GET['id']);

        // Caso não encontre a Escala
        if (!$dtEscala)
        {
            $_SESSION['merr'] = "Ocorreu um erro, tente novamente!";
            header("Location: " . BASE_URL . "escala/");
        }
        
        $nowY           = $dtEscala['escala']->ano;
        $month          = $dtEscala['escala']->mes;
        $allDays        = date("t", strtotime($nowY . "-" . $month));
        $ttPerTurno     = array();
        $itensEscalas   = $dtEscala['itemEscala'];

        for($i = 1; $i <= $allDays; $i++)
        {
            $daysMon[$i] = $this->getLetter($i, $month, $nowY);
        }

        foreach( $itensEscalas AS $itens )
        {
 
            for($i = 1; $i <= $allDays; $i++)
            {
                if (isset($ttPerTurno[$itens['turnoID']]) && isset($ttPerTurno[$itens['turnoID']]['t'.$i]))
                {
                    if($itens['t'.$i] == 0 || $itens['t'.$i] == 4)
                        $ttPerTurno[$itens['turnoID']]['t'.$i] = $ttPerTurno[$itens['turnoID']]['t'.$i] + 1;
                } else {
                    if($itens['t'.$i] == 0 || $itens['t'.$i] == 4)
                        $ttPerTurno[$itens['turnoID']]['t'.$i] = 1;
                    else
                        $ttPerTurno[$itens['turnoID']]['t'.$i] = 0;
                }
            }

        }
    
        $data['ttPerTurno'] = $ttPerTurno;
        $data['infoMes']= $this->array[$dtEscala['escala']->mes - 1];
        $data['infoAno']= $dtEscala['escala']->ano;
        $data['infoMesAno'] = $this->array[$dtEscala['escala']->mes - 1] . ' / ' . $dtEscala['escala']->ano;
        $data['daysMon']    = $daysMon;

        $this->loadTemplateExterno('escalaTrabalho/escala/printRes', $data);
		exit();
    }

}