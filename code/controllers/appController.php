<?php

class  AppController extends controller 
{

    private $urlTerms   = BASE_URLB . "app/terms?t=1";
    private $urlPrivacy = BASE_URLB . "app/terms?t=2";

    public function qrcodes()
	{
        $dados  = array();

        $apps               = new App();
		$dados              = $apps->list();
       
		$this->loadTemplate('appCgf/qrcodes/index', $dados);
		exit();
	}

    //FUNCÃO PARA PEGAR AS QUANTIDADES DE INSTALACOES E ACESSOS
    public function statistics(){

        $apps = new App();
		$getStatistics = $apps->getStatistics($_GET);

        if(is_array($getStatistics) && $getStatistics['status'] == true)
        {

            $this->loadTemplate('appCgf/qrcodes/statistics', $getStatistics);
		    exit();

        }else{

            $_SESSION['merr'] = "Ocorreu um erro ao carregar, tente novamente!";
            header("Location: " . BASE_URL . "app/qrcodes");

        }
		
		exit();
    }

    public function statisticsAppexcel()
    {
       
        ignore_user_abort(false);
        session_write_close();

        $req                = new \stdClass();
        $req->groupId    = $_GET['groupId'];
        $req->qrcode    = $_GET['qrcode'];
        $req->codigo    = $_GET['codigo'];
        $req->nomegr    = $_GET['nomegr'];
        $req->start     = $_GET['start'];
        $req->end       = $_GET['end'];
       
        $apps = new App();
		$getStatistics = $apps->getStatistics($_GET);

        $nomearquivo = utf8_decode($_GET['nomegr'])." - ".utf8_decode($getStatistics['pagetitle'])." - ".APP_NAME." PASS - ".utf8_decode($getStatistics['mesinfo']);
       
        $dadosXls = "<table border='1'>";
        $dadosXls .= "<tr>";
        $dadosXls .= "<td colspan='2' style='text-align: center' align='center'>".utf8_decode($_GET['nomegr'])."</td>";
        $dadosXls .= "</tr>";
        $dadosXls .= "<tr>";
        $dadosXls .= "<td>".utf8_decode("Mês/Ano")."</td>";
        $dadosXls .= "<td style='width:100px'>".utf8_decode($getStatistics['pagetitle'])."</td>";
        $dadosXls .= "</tr>";         

        if(count($getStatistics['grafico']) > 0){

            foreach($getStatistics['grafico'] as $gr){
                $dadosXls .= "<tr>";
                $dadosXls .= "<td>".utf8_decode($gr[0])."</td>";
                $dadosXls .= "<td>".utf8_decode($gr[1])."</td>";
                $dadosXls .= "</tr>";
            }

        }

        $dadosXls .= "</table>";

        setcookie('excelStatisticsApp', 'ready', -1, '/'); 
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment;filename=".$nomearquivo.".xls");
        header("Cache-Control:max-age=0");
        header("Cache-Control:max-age=1");

        echo $dadosXls;
        die();

    }

    public function create()
    {
        $dados = array();

		$param 			= new Totem();
		$dados['grLin'] = $param->getGrupoLinhas(true);

		if(count($dados['grLin'])>0){
			foreach ($dados['grLin'] as $k => $lin){
				$dados['grLin'][$k]['NOME'] = $lin['NOME'];
			}
		}

        $user 				= new Usuarios();
        $dados['grupos'] 	= $user->acessoGrupo();
        ################## TRATA #################
        if(count($dados['grupos'])>0){
            foreach ($dados['grupos'] as $k => $lin){
                $dados['grupos'][$k]['NOME'] = $lin['NOME'];
            }
        }
        #################################################

        $this->loadTemplate('appCgf/qrcodes/create', $dados);
		exit();
    }

    public function store()
    {

        $apps = new App();
		$save = $apps->save($_POST);

        if(is_array($save) && $save['status'] == false)
        {

            $_SESSION['merr'] = $save['message'];
            header("Location: " . BASE_URL . "app/create");

        } else if($save){

            $_SESSION['ms'] = "Cadastrado com sucesso!";
            unset($_POST);
            header("Location: " . BASE_URL . "app/qrcodes");

        }else{

            $_SESSION['merr'] = "Ocorreu um erro ao cadastrar, tente novamente!";
            header("Location: " . BASE_URL . "app/create");

        }
		
		exit();
    }

    public function edit()
    {
        $dados = array();

		$param 			= new Totem();
		$dados['grLin'] = $param->getGrupoLinhas(true);

		if(count($dados['grLin'])>0){
			foreach ($dados['grLin'] as $k => $lin){
				$dados['grLin'][$k]['NOME'] = $lin['NOME'];
			}
		}

        $user 				= new Usuarios();
        $dados['grupos'] 	= $user->acessoGrupo();
        ################## TRATA #################
        if(count($dados['grupos'])>0){
            foreach ($dados['grupos'] as $k => $lin){
                $dados['grupos'][$k]['NOME'] = $lin['NOME'];
            }
        }
        #################################################

        $apps 			  = new App();
		$dados['linkapp'] = $apps->get($_GET['id']);
        $dados['groups']  = isset($dados['linkapp']->groupAccess) ? explode(",", $dados['linkapp']->groupAccess) : [];

        $this->loadTemplate('appCgf/qrcodes/edit', $dados);
		exit();
    }

    public function update()
    {
  
        $apps = new App();
		$save = $apps->update($_POST);

        if(is_array($save) && $save['status'] == false)
        {
            $_SESSION['merr'] = $save['message'];

            unset($_POST);
            header("Location: " . BASE_URL . "app/edit?id=" . $_POST['id'] );

        } else if($save){

            $_SESSION['ms'] = "Editado com sucesso!";
            unset($_POST);
            header("Location: " . BASE_URL . "app/qrcodes");

        }else{

            $_SESSION['merr'] = "Ocorreu um erro ao editar, tente novamente!";

            unset($_POST);
            header("Location: " . BASE_URL . "app/edit?id=" . $_POST['id'] );

        }
		
		exit();
    }

    public function delete()
    {

        $retorno = array(
			"status" => true,
			"title" => "SUCESSO",
			"text" => "Usuário removido com sucesso!",
			"icon" => "success",
			"button" => "OK"
		);

		$esc = new App();

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
    
    public function qrcode()
    {
        $this->MethodValid($_SERVER, 'GET');

        if(isset( $_GET['mob'] ) && $_GET['mob'] == 1)
        {
            if (!isset($_GET['qr']) || $_GET['qr'] == '')
            {
                header('HTTP/1.0 400');
                http_response_code(400);
                echo json_encode(array('status'=> false, 'message'=>'Sem os parâmetros necessários!'));
                die;
            }

            $sentido = $_GET['sentido'] ?? 0;

            header('HTTP/1.0 200 OK');
            http_response_code(200);
            echo $this->getLines( $_GET['qr'], $_GET['uniqueID'], false,  $sentido );
            die;

        }else{
            die("Acesso negado");
        }
    }

    public function printqrcode()
    {
        
        if (!isset($_GET['qr']) || $_GET['qr'] == '')
            die("Sem os parâmetros necessários!");
            
            if(isset($_GET['type']) && $_GET['type'] == 'device'){

                $dados = [
                    'link' => $_GET['qr'],
                    'title' => 'Device Password: ' .$_GET['nomegr']
                ];

            }else{

                $dados = [
                    'link' => BASE_URLB . 'app/qrcode?qr=' . $_GET['qr'],
                    'title' => $_GET['nomegr']
                ];

            }

            $dados['show_print'] = $_GET['show_print'] ?? 0;
            
            
            $this->loadTemplateExterno('printqrcodes/index', $dados);
		    exit();
    }

    public function carqrcode()
    {
        $this->MethodValid($_SERVER, 'GET');

        if(isset( $_GET['mob'] ) && $_GET['mob'] == 1)
        {
            if (!isset($_GET['qr']) || $_GET['qr'] == '')
            {
                header('HTTP/1.0 400');
                http_response_code(400);
                echo json_encode(array('status'=> false, 'message'=>'Sem os parâmetros necessários!'));
                die;
            }

            $sentido = $_GET['sentido'] ?? 0;

            header('HTTP/1.0 200 OK');
            http_response_code(200);
            echo $this->getLines( $_GET['qr'], $_GET['uniqueID'], false,  $sentido );
            die;
        }else{
            die("Acesso negado");
        }
       
    }

    public function printcarqrcode()
    {
        if (!isset($_GET['qr']) || $_GET['qr'] == '')
            die("Sem os parâmetros necessários!");

        $dados = [
            'link' => BASE_URLB . 'app/readTagQrCode?veiculo_id=' . $_GET['qr'],
            'title' => $_GET['marca'] . ' - ' . $_GET['modelo'] . ' - ' . $_GET['placa']
        ];

        $dados['show_print'] = $_GET['show_print'] ?? 0;
        
        $this->loadTemplateExterno('printqrcodes/index', $dados);
        exit();
    }

    public function appCode()
    {

        $this->MethodValid($_SERVER, 'POST');

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);
        $sentido = $body->sentido ?? 0;

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo $this->getLines( $body->code, $body->uniqueID, true, $sentido );
        die;
    }

    public function allLines()
    {
        $this->MethodValid($_SERVER, 'POST');

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

        if (!isset($body->groupID) || $body->groupID == '')
        {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode(array('status'=> false, 'message'=>'Sem os parâmetros necessários!'));
            die;
        }

        $sentido = $body->sentido ?? 0;

        $apps       = new App();
        $allLines   = $apps->getAllLines( $body->groupID,  $sentido);
        $lines      = array();

        foreach($allLines AS $al)
        {
            $lines[] = array("label" => $al->PREFIXO . ' - ' . $al->LINHA , "value" => $al->ID_ORIGIN, "key" => $al->ID_ORIGIN);
        }

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode(array('status' => true, 'lines' => json_encode($lines) ));
        die;
    }

    public function boardingPoints()
    {
        $this->MethodValid($_SERVER, 'POST');

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

        if (!isset($body->groupID) || $body->groupID == '' 
            || !isset($body->line) || $body->line == '')
        {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode(array('status'=> false, 'message'=>'Sem os parâmetros necessários!'));
            die;
        }

        $sentido = $body->sentido ?? 0;

        $apps       = new App();
        $allPoints  = $apps->getAllPoints( $body->groupID, $body->line, $sentido );
        $points     = array();

        if (isset($allPoints['error']))
        {
            echo json_encode( $allPoints );
            die;
        }

        foreach($allPoints AS $index=>$al)
        {
            $order = null;

            if($index == 0){
                $order = 'first';
            }

            if($index == count($allPoints) - 1){
                $order = 'last';
            }
            
            $points[] = array("label" => $al['NOME'] . ' - ' . $al['LOCALIZACAO'] , "value" => $al['ID'], "key" => $al['ID'], "order" => $order);
        }

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode(array('status' => true, 'dots' => json_encode($points) ));
        die;

    }

    public function driverCar()
    {
        $this->MethodValid($_SERVER, 'POST');

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

        if (!isset($body->line) || $body->line == '')
        {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode(array('status'=> false, 'message'=>'Sem os parâmetros necessários!'));
            die;
        }

        $apps = new App();
        $data = $apps->getDriveAndCard($body->line);

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode($data);
        die;
    }

    public function pointsRef()
    {
        $this->MethodValid($_SERVER, 'GET');

        if (!isset($_GET['line']) || $_GET['line'] == '')
        {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode(array('status'=> false, 'message'=>'Sem os parâmetros necessários!'));
            die;
        }

        $apps = new App();
        $data = $apps->getPointsRef( $_GET['line'] );
   
        if (isset($data['error']))
        {
            echo json_encode( $data );
            die;
        }

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode( array('status' => true, 'points' => json_encode($data) ));
        die;
    }

    public function checkNameRegister()
    {
        // Verificar o nome completo e Matricula do Passageiro no primeiro acesso \\
        $this->MethodValid($_SERVER, 'POST');

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

        $apps   = new App();

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo $apps->hasAccess( $body );
        die;
    }

    public function infosAllDots()
    {
        /**
         * Retornar as lat e long de  todos os pontos da linha
         * Retornar lat e long do ponto de embarque
         */
        $this->MethodValid($_SERVER, 'POST');

        $body    = file_get_contents("php://input");
        $body    = json_decode($body);

        $apps    = new App();
        $allData = $apps->infosDotsLine( $body );

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode( $allData );
        die;
    }

    public function infosCar()
    {
        /**
         * Retornar todos 
         */
        $this->MethodValid($_SERVER, 'POST');

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

        $apps   = new App();

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo $apps->infosCar( $body );
        die;
    }

    public function pontualityLine()
    {
        $this->MethodValid($_SERVER, 'POST');

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

        $apps   = new App();

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo $apps->pontualityLine( $body );
        die;
    }

    public function termsPrivacy()
    {
       
        $this->MethodValid($_SERVER, 'GET');

        // Deixar dinamico para vir do banco \\
        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode( array( "status" => true, "terms" => $this->urlTerms, "privacy" => $this->urlPrivacy ) );
        die;
    }

    public function travelReview() 
    {
        $this->MethodValid($_SERVER, 'POST');

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

        $apps   = new App();

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo $apps->travelReview( $body );
        die;
    }

    public function apiGoogle() 
    {
        $parameter 	= new Parametro();
        $param    	= $parameter->getParametros();

        $api = $param['apiKey_app_active'] == 1 ? APPKEYGOOGLE : "xxxxxxxxxxxxxxxxxxxxxxxxx";

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode( array( "status" => true, "key" => $api ) );
        die;
    }

    public function terms()
    {
        $dados      = array();

        $parameter 	= new Parametro();
        $param    	= $parameter->getParametros();

        if (isset($_GET['t']) && $_GET['t'] == 1)
        {
            $dados['title']  = "Termos de Uso";
            $dados['content'] = $param['terms'];

        } else if (isset($_GET['t']) && $_GET['t'] == 2)
        {
            $dados['title'] = "Política de Privacidade";
            $dados['content'] = $param['privacy'];

        } else {

            $dados['title'] = "Termos";
            $dados['content'] = "Ocorreu um erro ao exibir o conteúdo, tente novamente!";
        }


        $this->loadView('appCgf/terms/show', $dados);
		exit;

    }

    public function isMobile() 
    {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
    }
    
    private function getLines( $qrCode, $uniqueID, $iscode = false, $sentido )
    {
        $apps  = new App();

        $apps->saveAllowTermsDevice($qrCode, $uniqueID);

        $ret            = $apps->getGroupByCode($qrCode, $iscode);
        $code           = $ret ? $ret->codigo : 0;
        $group          = $ret ? $ret->cliente_id : 0;
        $reg            = $ret ? $ret->register : 0;
        $embarqueQr     = $ret ? $ret->embarqueQr : 0;
        $mostraSentido  = $ret ? $ret->mostraSentido : 0;
        $isCard         = $ret ? $ret->isCard : 1;
        
        if ( !$iscode )
        {
            $sep  = explode("-", $qrCode);
            $group = $sep[0];
        }

        return json_encode( array("status" => true, "code" => $code, "groupID" => $group, "register" => $reg, "embarqueQr" => $embarqueQr, "mostraSentido" => $mostraSentido, "isCard" => $isCard, "lines"=> $apps->getLinesByQrCode($qrCode, $iscode, $sentido) ) );
    }
    
    private function MethodValid($SERVER, $method)
    {

        if ($SERVER['REQUEST_METHOD'] != $method) {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo "Method not allowed";
            die;
        }

        return true;
    }

    public function testMessage()
    {
        require_once  __DIR__ . '/../Services/TalentumNotification.php';

        $notify = new TalentumNotification;

        $title  = "ATENÇÃO";
        $body   = "Seu fretado vai atrasar hoje!";
        $topic  = "CGFPASS_L1128";
        $line   = 0;
        $dot    = 0; 
        $user   = 0;

        print_r($notify->sendMessage($title, $body, $topic, $line, $dot, $user));

        die;

    }

    public function readTagQrCode()
    {
        $this->MethodValid($_SERVER, 'GET');

        // se for facial
        if(isset( $_GET['face'] ) && $_GET['face'] == 1)
        {

            // se não tiver id do veiculo retorna erro
            if (!isset($_GET['veiculo_id']) || $_GET['veiculo_id'] == ''
                || !isset($_GET['device_id']) || $_GET['device_id'] == '')
            {
                header('HTTP/1.0 400');
                http_response_code(400);
                echo json_encode(array('status'=> false, 'message'=>'Sem os parâmetros necessários!'));
                die;
            }

            //tenta achar os dados do carro
            $apps = new App();
            $findCarFace = $apps->findCarFace($_GET['veiculo_id'], $_GET['device_id']);

            if(!$findCarFace['status']){

                header('HTTP/1.0 400');
                http_response_code(400);
                echo json_encode($findCarFace);
                die;

            }else{


                header('HTTP/1.0 200 OK');
                http_response_code(200);
                echo json_encode($findCarFace);
                die;

            }

        }else{

            if (!isset( $_GET['device_id']) || $_GET['device_id'] == '' 
            || !isset($_GET['veiculo_id'])|| $_GET['veiculo_id'] == ''
            || !isset($_GET['group_id']) || $_GET['group_id'] == '')
            {
                header('HTTP/1.0 400');
                http_response_code(400);
                echo json_encode(array('status'=> false, 'message'=>'Sem os parâmetros necessários!'));
                die;
            }
            // echo json_encode(array('status'=> true, 'message'=>'Recebi os paramentros'));
            $apps  = new App();
            $body = new \stdClass;
            $body->device_id=$_GET['device_id'];
            $body->veiculo_id=$_GET['veiculo_id'];
            $body->group_id=$_GET['group_id'];

            $data = $apps->checarAcessoQr($body);

            header('HTTP/1.0 200 OK');
            http_response_code(200);
            echo $data;
            die;

        }

    }

    public function signIn()
    {
        $this->MethodValid($_SERVER, 'POST');

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

        if ( $body->email == '' || $body->pass == '' || $body->groupID == '') {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode(array('status'=> false, 'message'=>'Sem os parâmetros necessários!'));
            die;
        }

        $apps  = new App();
        $data = $apps->signIn($body);

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode($data);
        die;
    }

    public function checkTag()
    {
        $this->MethodValid($_SERVER, 'POST');

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

        $apps  = new App();
        $data = $apps->checkTag($body);

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode($data);
        die;
    }

    public function register()
    {
        $this->MethodValid($_SERVER, 'POST');

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

        $apps  = new App();
        $data = $apps->register($body);

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo $data;
        die;
    }

    public function saveDotAndLineUser()
    {
        $this->MethodValid($_SERVER, 'POST');

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

        $apps = new App();
        $data = $apps->saveDotAndLineUser($body);
        
        // $apps  = new App();
        // $apps->saveDebug($body);

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode(['ok']);
        die;
    }

    public function getInfosApp()
    {
        $this->MethodValid($_SERVER, 'POST');

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

        $apps = new App();
        $data = $apps->getInfosApp($body);
        
        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode($data);
        die;
    }

    public function getTimeToMyPoint()
    {
        $this->MethodValid($_SERVER, 'POST');

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

        $apps = new App();
        $data = $apps->getTimeToMyPoint($body);
        
        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode($data);
        die;
    }

    public function checarMatriculaSemRFId(){
        $this->MethodValid($_SERVER, 'GET');
        
        if (!isset( $_GET['register']) || $_GET['register'] == ''
            || !isset($_GET['groupID']) || $_GET['groupID'] == '')
        {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode(array('status'=> false, 'message'=>'Sem os parâmetros necessários!'));
            die;
        }

        $apps   = new App();
        $body = new \stdClass;
        $body->register=$_GET['register'];
        $body->groupID=$_GET['groupID'];

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo $apps->checarMatriculaSemRFId($body);
        die;
    }


    public function embarqueSemRFId(){
        $this->MethodValid($_SERVER, 'GET');
        
        if (!isset( $_GET['register']) || $_GET['register'] == ''
            || !isset($_GET['groupID']) || $_GET['groupID'] == ''
            || !isset($_GET['veiculo_id'])|| $_GET['veiculo_id'] == ''
            || !isset($_GET['linha_id']) || $_GET['linha_id'] == ''
            || !isset($_GET['ponto_id']) || $_GET['ponto_id'] == ''
            || !isset($_GET['device_id']) || $_GET['device_id'] == ''
            || !isset($_GET['id_embarque']) || $_GET['id_embarque'] == '')
        {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode(array('status'=> false, 'message'=>'Sem os parâmetros necessários!'));
            die;
        }

        $sentido = $_GET['sentido'] ?? 0;

        $apps = new App();
        $body = new \stdClass;
        $body->register=$_GET['register'];
        $body->groupID=$_GET['groupID'];
        $body->veiculo_id=$_GET['veiculo_id'];
        $body->linha_id=$_GET['linha_id'];
        $body->ponto_id=$_GET['ponto_id'];
        $body->ultimo_ponto_id=$_GET['ultimo_ponto_id'];
        $body->device_id=$_GET['device_id'];
        $body->id_embarque=$_GET['id_embarque'];
        $body->sentido=$sentido;
        $body->latitude=$_GET['latitude'];
        $body->longitude=$_GET['longitude'];
        $body->name=$_GET['name'];
        $body->motivo=$_GET['motivo'];

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo $apps->checarEmbarqueSemRFId($body);
        die;
    }

    public function recoverPassword()
    {
        $this->MethodValid($_SERVER, 'POST');

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

        $email  = $body->email;
        
        if (!isset($email) || $email == '') {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode(array('status'=> false, 'message'=>'Sem os parâmetros necessários!'));
            die;
        }

        // Check email exist
        $apps = new App();

        if (!$apps->existEmail($email)) {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode(array('status'=> false, 'message'=>'Email não encontrado!'));
            die;
        }
   
        /**
         *  Gera um token aleatório de 6 digitos
         *  Salva o código
         *  Envia por email 
         */
        $token = random_int(100000, 999999);
        $apps->saveTokenEmail($email, $token);
   
        // Token validade de 2 horas
        require_once  __DIR__ . '/../Services/TalentumNotification.php';
        
        $notify = new TalentumNotification;

        $title  = "Recuperação de Senha ".APP_NAME." Pass";
        $mailBody   = $this->templateEmailRecoverPassword($token);

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode($notify->sendMailGeneric([trim($email)], $title, $mailBody));
        die;
    }

    private function templateEmailRecoverPassword($token)
    {

        $html = "<div style='width:60%;margin: auto;padding: 15px;background-color: white;color:#2a1e52'>";
        $html .= "<div style='text-align: center'>";
        $html .= "<img src='#URL#/assets/images/logoApp.png' width='150px'>"; // TODO: POPULATE WITH INDEX URL
        $html .= "</div>";
        $html .= "<h3 style='text-align: center'>" . utf8_decode("Recuperação de senha") . "</h3>";
        $html .= "<hr>";
        $html .= "<p style='text-align: center'>" . utf8_decode("Digite o código abaixo no APP do ".APP_NAME." PASS:") . "</p>";
        $html .= "<h2 style='text-align: center'><strong>$token</strong></h2>";
        $html .= "<hr>";
        $html .= "<p style='text-align: center'><strong>" . utf8_decode("ATENÇÃO:") . "</strong> " . utf8_decode("Código válido") . " por 2 horas.</p>";
        $html .= "</div>";
        
        return $html;
    }

    public function checkTokenRecoverPassword()
    {
        $this->MethodValid($_SERVER, 'POST');

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

        $email  = $body->email;
        $token  = $body->token;

        if (!isset($email) || $email == '' || !isset($token) || $token == '') {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode(array('status'=> false, 'message'=>'Sem os parâmetros necessários!'));
            die;
        }

        // Check email e token valid
        $apps = new App();

        if (!$apps->hasTokenValid($email, $token)) {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode(array('status'=> false, 'message'=>'Código Inválido ou Email não encontrado!'));
            die;
        }

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode(['status'=> true, 'message'=>'Código Válido! Redefina sua senha.']);
        die;
    }

    public function saveNewPassword()
    {
        $this->MethodValid($_SERVER, 'POST');

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

        $email      = $body->email;
        $password   = $body->password;
        $token      = $body->token;

        if (!isset($email) || $email == '' || !isset($password) || $password == ''|| 
            !isset($token) || $token == ''
        ) {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode(array('status'=> false, 'message'=>'Sem os parâmetros necessários!'));
            die;
        }

        // Check email e token valid
        $apps = new App();

        if (!$apps->hasTokenValid($email, $token)) {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode(array('status'=> false, 'message'=>'Código Inválido ou Email não encontrado!'));
            die;
        }

        if($apps->saveNewPasswordByEmail($email, $password)){

            header('HTTP/1.0 200 OK');
            http_response_code(200);
            echo json_encode(['status'=> true, 'message'=>'Nova senha cadastrada com sucesso!']);
            die;

        }else{

            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode(array('status'=> false, 'message'=>'Erro ao atualizar a senha, por favor tente novamente.'));
            die;

        }
    }

    //cehcar se o cadastro ainda está artivo
    public function checkAtivo(){

        $this->MethodValid($_SERVER, 'POST');

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

        $email = $body->email;
        $tag   = $body->tag;

        // Check email e token valid
        $apps = new App();

        if (!$apps->checkAtivo($email, $tag)) {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode(array('status'=> false, 'message'=>'Cadastro Inativo'));
            die;
        }

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode(['status'=> true, 'message'=>'Cadastro Ativo']);
        die;

    }

    public function usePerDayByGroup()
    {
        $this->MethodValid($_SERVER, 'GET');

        if (!isset( $_GET['device_id']) || $_GET['device_id'] == '' 
            || !isset($_GET['group_id']) || $_GET['group_id'] == '')
        {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode(array('status'=> false, 'message'=>'Sem os parâmetros necessários!'));
            die;
        }

        $device = $_GET['device_id'];
        $group = $_GET['group_id'];

        $apps = new App();

        $data = $apps->saveAppUseByDay($device, $group);

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo $data;
        die;

        
    }

    public function verifyExigeSemRfId(){

        $this->MethodValid($_SERVER, 'GET');

        if (!isset( $_GET['code']) || $_GET['code'] == '')
        {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode(array('status'=> false, 'message'=>'Sem os parâmetros necessários!'));
            die;
        }

        $code = $_GET['code'];

        $apps               = new App();
        $ret                = $apps->getGroupByCode($code, true);
        $exigeCad           = $ret ? $ret->exigeCad : 0;
        $exigeMotive        = $ret ? $ret->exigeMotive : 0;
        $beep_embarque      = $ret ? $ret->beep_embarque : 1;
        $beep_desembarque   = $ret ? $ret->beep_desembarque : 1;

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode(array("status" => true, "exigeCad" => $exigeCad, "exigeMotive" => $exigeMotive, "beep_embarque" => $beep_embarque, "beep_desembarque" => $beep_desembarque));
        die;

    }


    public function qrCodeCA(){

        $this->MethodValid($_SERVER, 'GET');

        if (!isset($_GET['qr']) || $_GET['qr'] == '')
        {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode(array('status'=> false, 'message'=>'Sem os parâmetros necessários!'));
            die;
        }

        $qrCode = $_GET['qr'];
        $apps   = new App();
        $ret    = $apps->getCAbyQrCode($qrCode, true);

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo $ret;
        die;

    }

    //pegar tópicos do dispositivo para desinscrever
    public function getTopics(){

        $this->MethodValid($_SERVER, 'POST');

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

        $deviceToken    = $body->deviceToken;

        $apps = new App();

        $getTopics = $apps->getTopics($deviceToken);
        
        if (!$getTopics['status']) {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode(['status'=> false, 'msg'=> $getTopics['msg'], 'topics' => []]);
            die;
        }

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode(['status'=> true, 'msg'=> $getTopics['msg'], 'topics' => $getTopics['topics']]);
        die;

    }

    //salvar dados de subinscrição e desinscrição de tópicos
    public function saveSubscribeStatus(){

        $this->MethodValid($_SERVER, 'POST');

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

        $apps = new App(); 

        $saveSubscribeStatus = $apps->saveSubscribeStatus($body);
        
        if (!$saveSubscribeStatus['status']) {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode(['status'=> false, 'msg'=> $saveSubscribeStatus['msg']]);
            die;
        }

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode(['status'=> true, 'msg'=> $saveSubscribeStatus['msg']]);
        die;

    }

    //funções exclusivas do facial
    public function qrcodeFace()
    {
        $this->MethodValid($_SERVER, 'GET');

        if(isset($_GET['face'] ) && $_GET['face'] == 1)
        {
            if (!isset($_GET['qr']) || $_GET['qr'] == '' || !isset($_GET['device_id']))
            {
                header('HTTP/1.0 400');
                http_response_code(400);
                echo json_encode(array('status'=> false, 'message'=>'Sem os parâmetros necessários!'));
                die;
            }

            header('HTTP/1.0 200 OK');
            http_response_code(200);
            echo $this->checkValidQrFace($_GET['qr'], $_GET['device_id']);
            die;

        }else{
            die("Acesso negado");
        }
    }

    private function checkValidQrFace($qrCode, $device_id){
        $apps = new App();
        
        $checkAtivoFaceCad = $apps->checkAtivoFaceCad($device_id);

        if(!$checkAtivoFaceCad['status']){
            return json_encode(array('status'=> false, 'message'=>'Aparelho Inativo!', 'reload' => true));
        }

        $group = $apps->getGroupByCode($qrCode, false);

        if(!isset($group->cliente_id)){
            return json_encode(array('status'=> false, 'message'=>'Grupo não encontrado'));
        }

        $groupID    = $group->cliente_id;
        $groupName  = $apps->getGroupName($groupID);

        return json_encode(array('status'=> true, 'groupID' => $groupID, 'groupName' => $groupName));

    }

    public function getGroupDataFace()
    {

        $this->MethodValid($_SERVER, 'POST');

        $body       = file_get_contents("php://input");
        $body       = json_decode($body);

        $qrCode     = $body->qrCode;
        $device_id  = $body->device_id;

        $apps = new App();
        
        $checkAtivoFaceCad = $apps->checkAtivoFaceCad($device_id);

        if(!$checkAtivoFaceCad['status']){
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode(array('status'=> false, 'message'=>'Aparelho Inativo!', 'reload' => true));
            die;
        }

        $group = $apps->getGroupByCode($qrCode, false);

        if(!isset($group->cliente_id)){
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode(array('status'=> false, 'message'=>'Grupo não encontrado', 'back' => true));
            die;
        }

        $groupID        = $group->cliente_id;
        $groupAccess    = $group->groupAccess;
        
        $getLinhasIda       = $apps->getAllLines($groupID, 0);
        $getLinhasVolta     = $apps->getAllLines($groupID, 1);
        $linhasIda = array();
        $linhasVolta = array();
        $idaIds = array();
        $voltaIds = array();
        
        foreach($getLinhasIda as $alIda) {
            if (!isset($idaIds[$alIda->ID_ORIGIN])) {
                $linhasIda[] = array(
                    "label" => $alIda->PREFIXO . ' - ' . $alIda->LINHA,
                    "value" => $alIda->ID_ORIGIN,
                    "key" => $alIda->ID_ORIGIN
                );
                $idaIds[$alIda->ID_ORIGIN] = true;
            }
        }
        
        foreach($getLinhasVolta as $alVolta) {
            if (!isset($voltaIds[$alVolta->ID_ORIGIN])) {
                $linhasVolta[] = array(
                    "label" => $alVolta->PREFIXO . ' - ' . $alVolta->LINHA,
                    "value" => $alVolta->ID_ORIGIN,
                    "key" => $alVolta->ID_ORIGIN
                );
                $voltaIds[$alVolta->ID_ORIGIN] = true;
            }
        }
        

        $grupos = $apps->acessoGrupoIn($groupAccess);

        $groupPax = $apps->getCaByGroup($groupAccess);

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode(array('status' => true, 'groupPax' => $groupPax, 'groupGroups' => $grupos, 'linhasIda' => $linhasIda, 'linhasVolta' => $linhasVolta));
        die;
    }

    public function getPaxFace(){

        $this->MethodValid($_SERVER, 'POST');

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

        if (!isset($body->device_id) || $body->device_id == '' 
            || !isset($body->id) || $body->id == '')
        {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode(array('status'=> false, 'message'=>'Sem os parâmetros necessários!'));
            die;
        }

        $apps = new App();

        $checkAtivoFaceCad = $apps->checkAtivoFaceCad($body->device_id);

        if(!$checkAtivoFaceCad['status']){
            echo json_encode(array('status'=> false, 'message'=>'Aparelho Inativo!', 'reload' => true));
            die;
        }

        $getPaxFace = $apps->getPaxFace($body);

        if (!$getPaxFace['status']) {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode($getPaxFace);
            die;
        }

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode($getPaxFace);
        die;
    }

    public function savePaxFace(){

        $this->MethodValid($_SERVER, 'POST');

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

        if (!isset($body->device_id) || $body->device_id == '')
        {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode(array('status'=> false, 'message'=>'Sem os parâmetros necessários!'));
            die;
        }

        $apps = new App();

        $checkAtivoFaceCad = $apps->checkAtivoFaceCad($body->device_id);

        if(!$checkAtivoFaceCad['status']){
            echo json_encode(array('status'=> false, 'message'=>'Aparelho Inativo!', 'reload' => true));
            die;
        }

        $savePaxFace = $apps->savePaxFace($body);

        if (!$savePaxFace['status']) {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode($savePaxFace);
            die;
        }

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode($savePaxFace);
        die;
    }

    public function getInfosAppFace()
    {
        $this->MethodValid($_SERVER, 'POST');

        $apps = new App();
        $data = $apps->getInfosAppFace();
        
        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode($data);
        die;
    }

    public function createDetection(){

        $this->MethodValid($_SERVER, 'POST');

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

        //Adicionar a detecção no banco
        $apps = new App();
        $createDetection = $apps->createDetection($body);

        if (!$createDetection['status']) {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode($createDetection);
            die;
        }

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode($createDetection);
        die;

    }

    public function resgiterDeviceFace(){
        $this->MethodValid($_SERVER, 'POST');

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

        $apps = new App();
        $resgiterDeviceFace = $apps->resgiterDeviceFace($body);

        if (!$resgiterDeviceFace['status']) {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode($resgiterDeviceFace);
            die;
        }

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode($resgiterDeviceFace);
        die;
    }

    public function checkConfigFace(){

        $this->MethodValid($_SERVER, 'GET');

        if (!isset($_GET['device_id']) || $_GET['device_id'] == ''
            || !isset($_GET['config_type']) || $_GET['config_type'] == '')
        {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode(array('status'=> false, 'msg'=>'Sem os parâmetros necessários!'));
            die;
        }

        $device_id = $_GET['device_id'];
        $config_type = $_GET['config_type'];
        $apps   = new App();
        $checkConfigFace = $apps->checkConfigFace($device_id, $config_type);

        if (!$checkConfigFace['status']) {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode($checkConfigFace);
            die;
        }

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode($checkConfigFace);
        die;
    }

    public function checkChangeCarFace(){

        $this->MethodValid($_SERVER, 'GET');

        if (!isset($_GET['veiculo_id']) || $_GET['veiculo_id'] == ''
            || !isset($_GET['device_id']) || $_GET['device_id'] == '')
        {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode(array('status'=> false, 'msg'=>'Sem os parâmetros necessários!'));
            die;
        }

        $veiculo_id = $_GET['veiculo_id'];
        $device_id = $_GET['device_id'];
        $apps   = new App();
        $checkChangeCarFace = $apps->checkChangeCarFace($veiculo_id, $device_id);

        if (!$checkChangeCarFace['status']) {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode($checkChangeCarFace);
            die;
        }

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode($checkChangeCarFace);
        die;

    }

    public function checkAtivoFace(){

        $this->MethodValid($_SERVER, 'GET');

        if (!isset($_GET['device_id']) || $_GET['device_id'] == ''
            || !isset($_GET['app_version']) || $_GET['app_version'] == '')
        {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode(array('status'=> false, 'msg'=>'Sem os parâmetros necessários!'));
            die;
        }

        $device_id = $_GET['device_id'];
        $app_version = $_GET['app_version'];
        $veiculo_id = $_GET['veiculo_id'];
        $apps   = new App();
        $checkAtivoFace = $apps->checkAtivoFace($device_id, $app_version, $veiculo_id);

        if (!$checkAtivoFace['status']) {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode($checkAtivoFace);
            die;
        }

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode($checkAtivoFace);
        die;
    }

    public function setAppFaceType(){

        $this->MethodValid($_SERVER, 'POST');

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

        $apps = new App();
        $setAppFaceType = $apps->setAppFaceType($body);

        if (!$setAppFaceType['status']) {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode($setAppFaceType);
            die;
        }

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode($setAppFaceType);
        die;
    }

    public function iniAppFaceTakePic(){

        $this->MethodValid($_SERVER, 'POST');

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

        $apps = new App();
        $iniAppFaceTakePic = $apps->iniAppFaceTakePic($body);

        if (!$iniAppFaceTakePic['status']) {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode($iniAppFaceTakePic);
            die;
        }

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode($iniAppFaceTakePic);
        die;
    }

    public function calcelAppFaceTakePic(){

        $this->MethodValid($_SERVER, 'POST');

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

        $apps = new App();
        $calcelAppFaceTakePic = $apps->calcelAppFaceTakePic($body);

        if (!$calcelAppFaceTakePic['status']) {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode($calcelAppFaceTakePic);
            die;
        }

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode($calcelAppFaceTakePic);
        die;
    }

    public function checkTakingPicFace(){

        $this->MethodValid($_SERVER, 'POST');

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

        $apps = new App();
        $checkTakingPicFace = $apps->checkTakingPicFace($body);

        if (!$checkTakingPicFace['status']) {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode($checkTakingPicFace);
            die;
        }

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode($checkTakingPicFace);
        die;
    }

    public function sendTakingPicFace(){

        $this->MethodValid($_SERVER, 'POST');

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

        $apps = new App();
        $sendTakingPicFace = $apps->sendTakingPicFace($body);

        if (!$sendTakingPicFace['status']) {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode($sendTakingPicFace);
            die;
        }

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode($sendTakingPicFace);
        die;
    }

    public function warningSendInfosFace(){

        $this->MethodValid($_SERVER, 'GET');

        if (!isset($_GET['device_id']) || $_GET['device_id'] == ''
            || !isset($_GET['request_id']) || $_GET['request_id'] == '')
        {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode(array('status'=> false, 'msg'=>'Sem os parâmetros necessários!'));
            die;
        }

        $device_id = $_GET['device_id'];
        $request_id = $_GET['request_id'];
        $type = $_GET['type'];
        $apps   = new App();
        $warningSendInfosFace = $apps->warningSendInfosFace($device_id, $request_id, $type);

        if (!$warningSendInfosFace['status']) {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode($warningSendInfosFace);
            die;
        }

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode($warningSendInfosFace);
        die;
    }

    public function checkSendInfosFace(){

        $this->MethodValid($_SERVER, 'GET');

        if (!isset($_GET['device_id']) || $_GET['device_id'] == ''
            || !isset($_GET['type']))
        {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode(array('status'=> false, 'msg'=>'Sem os parâmetros necessários!'));
            die;
        }

        $device_id  = $_GET['device_id'];
        $type       = $_GET['type'];
        $recCount   = $_GET['recCount'] ?? 0;
        $apps   = new App();
        $checkSendInfosFace = $apps->checkSendInfosFace($device_id, $type, $recCount);

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode($checkSendInfosFace);
        die;

    }

    public function sendCurrentLocation(){

        $this->MethodValid($_SERVER, 'POST');

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

        $apps = new App();
        $sendCurrentLocation = $apps->sendCurrentLocation($body);

        if (!$sendCurrentLocation['status']) {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode($sendCurrentLocation);
            die;
        }

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode($sendCurrentLocation);
        die;
    }

    public function sendBatteryStatus(){

        $this->MethodValid($_SERVER, 'POST');

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

        $apps = new App();
        $sendBatteryStatus = $apps->sendBatteryStatus($body);

        if (!$sendBatteryStatus['status']) {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode($sendBatteryStatus);
            die;
        }

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode($sendBatteryStatus);
        die;
    }

    public function faceRecognition()
    {

        $dados = array();

        if (!isset($_GET['mob']) || $_GET['mob'] != '1'){

            $dados['status'] = false;
            $dados['message'] = 'Sem os parâmetros necessários!';

        }
            
        $this->loadTemplateExterno('facerecognition/index', $dados);
        exit();
    }

    public function getUsersFace(){

        $this->MethodValid($_SERVER, 'POST');
        $body   = file_get_contents("php://input");
        $body   = json_decode($body);
        $mob    =  $body->mob;

        if(isset($mob) && $mob == 1){

            //pegar os usuários que tenham foto
            $apps = new App();
            $getUsersFace = $apps->getUsersFace($body);
            echo json_encode($getUsersFace);
            die();

        }else{
            echo json_encode(array('status' => false, 'message' => 'Acesso Negado!'));
            die();
        }

    }

    public function getUsersFaceNew(){

        $this->MethodValid($_SERVER, 'POST');
        $body   = file_get_contents("php://input");
        $body   = json_decode($body);
        $mob    =  $body->mob;

        if(isset($mob) && $mob == 1){

            //pegar os usuários que tenham foto
            $apps = new App();
            $getUsersFaceNew = $apps->getUsersFaceNew($body);
            echo json_encode($getUsersFaceNew);
            die();

        }else{
            echo json_encode(array('status' => false, 'message' => 'Acesso Negado!'));
            die();
        }

    }

    public function removeUserPicture(){

        $this->MethodValid($_SERVER, 'POST');

        $body       = file_get_contents("php://input");
        $body       = json_decode($body);
        $mob        = $body->mob;
        $user_id    = $body->user_id;

        if(isset($mob) && $mob == 1 && isset($user_id) && is_numeric($user_id)){

            //pegar os usuários que tenham foto
            $apps = new App();
            $removeUserPicture = $apps->removeUserPicture($user_id);
            echo json_encode($removeUserPicture);
            die();

        }else{
            echo json_encode(array('status' => false, 'message' => 'Acesso Negado!'));
            die();
        }
    }

    public function getDetectionsFace(){

        $this->MethodValid($_SERVER, 'POST');

        $body       = file_get_contents("php://input");
        $body       = json_decode($body);
        $mob        = $body->mob;
        $veiculo_id = $body->veiculo_id;
        $device_id  = $body->device_id;

        if(isset($mob) && $mob == 1
            && isset($veiculo_id ) && $veiculo_id  != ""
            && isset($device_id) && $device_id != ""){
            $apps = new App();
            $getDetectionsFace = $apps->getDetectionsFace($body);
            echo json_encode($getDetectionsFace);
            die();

        }else{
            echo json_encode(array('status' => false, 'message' => 'Acesso Negado!'));
            die();
        }

    }

    public function doRecognationsFace(){

        $this->MethodValid($_SERVER, 'POST');

        $body       = file_get_contents("php://input");
        $body       = json_decode($body);
        $mob        = $body->mob;
        $veiculo_id = $body->veiculo_id;
        $device_id  = $body->device_id;

        if(isset($mob) && $mob == 1
            && isset($veiculo_id ) && $veiculo_id  != ""
            && isset($device_id) && $device_id != ""){
            $apps = new App();
            $doRecognationsFace = $apps->doRecognationsFace($body);
            echo json_encode($doRecognationsFace);
            die();

        }else{
            echo json_encode(array('status' => false, 'message' => 'Acesso Negado!'));
            die();
        }

    }

    public function getRecognitionsFace(){

        $this->MethodValid($_SERVER, 'POST');

        $body       = file_get_contents("php://input");
        $body       = json_decode($body);
        $mob        = $body->mob;
        $veiculo_id = $body->veiculo_id;
        $device_id  = $body->device_id;

        if(isset($mob) && $mob == 1
            && isset($veiculo_id ) && $veiculo_id  != ""
            && isset($device_id) && $device_id != ""){
            $apps = new App();
            $getRecognitionsFace = $apps->getRecognitionsFace($body);
            echo json_encode($getRecognitionsFace);
            die();

        }else{
            echo json_encode(array('status' => false, 'message' => 'Acesso Negado!'));
            die();
        }

    }

    public function updateRecogntionsFace(){

        $this->MethodValid($_SERVER, 'POST');

        $body       = file_get_contents("php://input");
        $body       = json_decode($body);
        $mob        = $body->mob;
        $veiculo_id = $body->veiculo_id;
        $device_id  = $body->device_id;

        if(isset($mob) && $mob == 1
            && isset($veiculo_id ) && $veiculo_id  != ""
            && isset($device_id) && $device_id != ""){
            $apps = new App();
            $updateRecogntionsFace = $apps->updateRecogntionsFace($body);
            echo json_encode($updateRecogntionsFace);
            die();

        }else{
            echo json_encode(array('status' => false, 'message' => 'Acesso Negado!'));
            die();
        }

    }

    public function createRecoginationsFace(){

        $this->MethodValid($_SERVER, 'POST');

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

        $apps = new App();
        $createRecoginationsFace = $apps->createRecoginationsFace($body);

        if (!$createRecoginationsFace['status']) {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode($createRecoginationsFace);
            die;
        }

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode($createRecoginationsFace);
        die;

    }

}
