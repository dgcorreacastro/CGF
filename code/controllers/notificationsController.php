<?php

class notificationsController extends controller 
{

	public function index() 
	{

        $dados           = array();
        $rel 	         = new Relatorios();
        $msgs            = new MsgNotification();
        $carros 		 = new Relatorios();
		$dados['carros'] = $carros->getCarros();
        $dados['msgs']   = $msgs->getMsgsNotification();
        $linhas	= $rel->getLinhas();

        ################## TRATA LINHAS #################
        foreach($linhas AS $k => $lin){
            if ($lin['SENTIDO'] == 0){
                $dados['linhas'][$k]['name'] = $lin['PREFIXO'] . " - " . $lin['NOME'] . " - " . $lin['DESCRICAO'];
                $dados['linhas'][$k]['ID']   = $lin['ID_ORIGIN'];
            }
        }

        $this->loadTemplate('appCgf/notifications/index', $dados);
		exit;
	}

    public function boardingPoints()
    {
       
        $apps    = new App();
        $points  = $apps->getPointsRef( $_POST['vl'] );
      
        $html = '<option value="">Selecione</option>';

        foreach($points AS $pts){

            $html .= '<option value="'.$pts['ID'].'">'.$pts['NOME'] . '-' . $pts['LOCALIZACAO'] . '</option>';

        }
        
        echo json_encode(array('html' => $html));
        die();
    }

    public function byPush()
    {

        require_once  __DIR__ . '/../Services/TalentumNotification.php';

        $notify = new TalentumNotification;

        $p = isset($_POST['dot']) && $_POST['dot'] != "" ? "P".$_POST['dot'] : "";

        $title  = $_POST['title'] ?? '';
        $body   = $_POST['message'];
        $topic  = "CGFPASS_L" . $_POST['lines'] . $p;
        $line   = $_POST['lines'];
        $dot    = $_POST['dot'] ?? 0; 
        $user   = $_SESSION['cLogin'];

        if($notify->sendMessage($title, $body, $topic, $line, $dot, $user)){
            $_SESSION['ms'] = "Mensagem enviada com sucesso!";
        } else {
            $_SESSION['merr'] = "Ocorreu um erro no envio, tente novamente!";
        }

        header("Location: " . BASE_URL . "notifications");
		exit;
    }


}