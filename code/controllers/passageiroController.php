<?php

class passageiroController extends controller 
{

	public function itinerario()
	{

        $_SESSION['forbidden'] = [
            "code" => "404",
            "msg" => "Página não encontrada.",
            "showLogin" => false
        ];
        header("Location: /");
        die();
		// $dados = array();

        // $url = $_SERVER['REQUEST_URI'];
        // $url = explode("/", $url);
        // $url = $url[count($url)-1];
        // $tot = $url;
        // $url = explode("-", $url);

        // $dados['ic'] = $url[0];

        // $tot = explode("?", $tot);

        // $param      = new TotemUser();

        // if(!$param->hasTotem($tot[0], "USER")){
        //     $_SESSION['forbidden'] = [
		// 		"code" => "404",
		// 		"msg" => "Página não encontrada.",
        //         "showLogin" => false
		// 	];
        //     header("Location: /");
        //     die();
        // }

        // // GRAVAR ACESSO \\
        // // { tipos 1 itinetário, 2 passageiro, 3 gerais, 4 PaxEspecial }
        // $this->createLog(2, $dados['ic']);

        // $parameter 	= new Parametro();
        // $dados['param'] = $parameter->getParametros();

		// $this->loadTemplateExterno('passageiro/index', $dados);
		// exit();
	}

    public function statisticsTotemPassageiro(){
        
        $apps = new TotemUser();
		$getStatistics = $apps->getStatisticsPassageiro($_GET);

        if(is_array($getStatistics) && $getStatistics['status'] == true)
        {

            $this->loadTemplate('configuracoes/totem/statisticsPassageiro', $getStatistics);
		    exit();

        }else{

            $_SESSION['merr'] = "Ocorreu um erro ao carregar, tente novamente!";
            header("Location: " . BASE_URL . "configuracoes/totem/totem");

        }
		
		exit();
    }

	public function statisticsTotemPassageiroExcel()
    {
       
        ignore_user_abort(false);
        session_write_close();

        $req                = new \stdClass();
        $req->groupId    = $_GET['groupId'];
        $req->nomegr    = $_GET['nomegr'];
        $req->start     = $_GET['start'];
        $req->end       = $_GET['end'];
       
        $apps = new TotemUser();
		$getStatistics = $apps->getStatisticsPassageiro($_GET);

        $nomearquivo = utf8_decode($_GET['nomegr'])." - ".utf8_decode($getStatistics['pagetitle'])." - ".APP_NAME." TOTEM - ".utf8_decode($getStatistics['mesinfo']);
       
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

        setcookie('excelStatisticsPassageiroTotem', 'ready', -1, '/'); 
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment;filename=".$nomearquivo.".xls");
        header("Cache-Control:max-age=0");
        header("Cache-Control:max-age=1");

        echo $dadosXls;
        die();

    }

    public function statisticsTotemPassageiroEspecial(){
        
        $apps = new TotemUser();
		$getStatistics = $apps->getStatisticsPassageiroEspecial($_GET);

        if(is_array($getStatistics) && $getStatistics['status'] == true)
        {

            $this->loadTemplate('configuracoes/totem/statisticsPassageiroEspecial', $getStatistics);
		    exit();

        }else{

            $_SESSION['merr'] = "Ocorreu um erro ao carregar, tente novamente!";
            header("Location: " . BASE_URL . "configuracoes/totem/totem");

        }
		
		exit();
    }

	public function statisticsTotemPassageiroEspecialExcel()
    {
       
        ignore_user_abort(false);
        session_write_close();

        $req                = new \stdClass();
        $req->groupId    = $_GET['groupId'];
        $req->nomegr    = $_GET['nomegr'];
        $req->start     = $_GET['start'];
        $req->end       = $_GET['end'];
       
        $apps = new TotemUser();
		$getStatistics = $apps->getStatisticsPassageiroEspecial($_GET);

        $nomearquivo = utf8_decode($_GET['nomegr'])." - ".utf8_decode($getStatistics['pagetitle'])." - ".APP_NAME." TOTEM - ".utf8_decode($getStatistics['mesinfo']);
       
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

        setcookie('excelStatisticsPassageiroEspecialTotem', 'ready', -1, '/'); 
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment;filename=".$nomearquivo.".xls");
        header("Cache-Control:max-age=0");
        header("Cache-Control:max-age=1");

        echo $dadosXls;
        die();

    }

    public function statisticsTotemEuro(){
        
        $apps = new TotemEuro();
		$getStatistics = $apps->getStatisticsEuro($_GET);

        if(is_array($getStatistics) && $getStatistics['status'] == true)
        {

            $this->loadTemplate('configuracoes/totem/statisticsEuro', $getStatistics);
		    exit();

        }else{

            $_SESSION['merr'] = "Ocorreu um erro ao carregar, tente novamente!";
            header("Location: " . BASE_URL . "configuracoes/totem/totem");

        }
		
		exit();
    }

	public function statisticsTotemEuroExcel()
    {
       
        ignore_user_abort(false);
        session_write_close();

        $req                = new \stdClass();
        $req->groupId    = $_GET['groupId'];
        $req->nomegr    = $_GET['nomegr'];
        $req->start     = $_GET['start'];
        $req->end       = $_GET['end'];
       
        $apps = new TotemEuro();
		$getStatistics = $apps->getStatisticsEuro($_GET);

        $nomearquivo = utf8_decode($_GET['nomegr'])." - ".utf8_decode($getStatistics['pagetitle'])." - ".APP_NAME." TOTEM EURO- ".utf8_decode($getStatistics['mesinfo']);
       
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

        setcookie('excelStatisticsEuroTotem', 'ready', -1, '/'); 
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment;filename=".$nomearquivo.".xls");
        header("Cache-Control:max-age=0");
        header("Cache-Control:max-age=1");

        echo $dadosXls;
        die();

    }

    public function seach()
    {
        
        $dados          = array();
        $req            = new \stdClass();
        $req->nome      = addslashes(trim($_POST['name']));
        $req->registro  = addslashes(trim($_POST['matricula']));
        $req->grupo     = addslashes($_POST['ic']);

        $param   = new TotemUser();
        $groupId = $param->getTotemByOriginCode($_POST['ic']);
        $req->grupoAcess= count($groupId[0]) > 0 ? $groupId[0]['GRUPOSUSER'] : 0;
      
        if($req->nome == "" && $req->registro == "" && $req->grupo == "") {
            $dados['error'] = "Ocorreu um erro inesperado. Favor tentar novamente.";
            echo json_encode($dados);
            die();
        }

        $rel                = new Relatorios();
        $retorn             = $rel->getDadosPassageiro($req);


        foreach ($retorn as $t => $ret) {

            if(is_array($ret)) {

                foreach ($ret as $k => $v) {
                    

                    if(isset($v['NOME']))
                       $retorn['data'][$k]['NOME'] = $v['NOME'];


                    if(isset($v['NOMELINHAIDA'])){
                        
                        $retorn['data'][$k]['NOMELINHAIDA'] = $v['NOMELINHAIDA'];
                    }

                    
                    if(isset($v['NOMELINHAVOL'])){
                        
                        $retorn['data'][$k]['NOMELINHAVOL'] = $v['NOMELINHAVOL'];
                    }

                    if(isset($v['DESCRICAOINTINERARIOIDA'])){
                       
                        $retorn['data'][$k]['DESCRICAOINTINERARIOIDA'] = $v['DESCRICAOINTINERARIOIDA'];
                    }

                    if(isset($v['PREFIXOLINHAIDA'])){
                      
                        $retorn['data'][$k]['PREFIXOLINHAIDA'] = $v['PREFIXOLINHAIDA'];
                    }

                    if(isset($v['PREFIXOLINHAVOL'])){
                       
                        $retorn['data'][$k]['PREFIXOLINHAVOL'] = $v['PREFIXOLINHAVOL'];
                    }

                    if(isset($v['DESCRICAOINTINERARIOVOL'])){
                      
                        $retorn['data'][$k]['DESCRICAOINTINERARIOVOL'] = $v['DESCRICAOINTINERARIOVOL'];
                    }

                    if (isset($v['ITINERARIO_ID_IDA']))
                        $retorn['data'][$k]['ITINERARIO_ID_IDA'] = $v['ITINERARIO_ID_IDA'];

                    if (isset($v['ITINERARIO_ID_VOLTA']))
                        $retorn['data'][$k]['ITINERARIO_ID_VOLTA'] = $v['ITINERARIO_ID_VOLTA'];

                    if (isset($v['POL'])){
                        $pol = explode(";", $v['POL']);

                        $retorn['data'][$k]['POL'] = isset($pol[0]) ? $pol[0] : "-";
                        $retorn['data'][$k]['POLVOLTA'] = isset($pol[1]) ? trim($pol[1]) : ( isset($v['POLVOLTA']) ? $v['POLVOLTA'] : "-");
                    }
                       
                    if(isset($v[4]))
                        $retorn['data'][$k][4] = $v[4];

                    if(isset($v[6]))
                        $retorn['data'][$k][6] = $v[6];

                    if(isset($v[7]))
                        $retorn['data'][$k][7] = $v[7];

                    if(isset($v[8]))
                        $retorn['data'][$k][8] = $v[8];

                    if(isset($v[10]))
                        $retorn['data'][$k][9] = $v[9];

                     if(isset($v[10]))
                        $retorn['data'][$k][10] = $v[10];

                }

            }
   
        }

        $dados['retorn']    = $retorn;
        $dados['cont']      = count($retorn);
    
        echo json_encode($dados);
        die();
    }

    public function itinerarioEurofarma()
    {

        
        $dados = array();

        $url = $_SERVER['REQUEST_URI'];
        $url = explode("/", $url);
        $url = $url[count($url)-1];
        $tot = $url;
        $url = explode("-", $url);

        $tot = explode("?", $tot);         

        $hasTotem = new TotemEuro();

        $getTotem = $hasTotem->hasTotem($tot[0]);
        
        if(!$getTotem){
            $_SESSION['forbidden'] = [
				"code" => "404",
				"msg" => "Página não encontrada.",
                "showLogin" => false
			];
            header("Location: /");
            die();
        }

        $typeTotem = $getTotem['typeTotem'];

        // GRAVAR ACESSO \\
        // { tipos 1 itinetário, 2 passageiro, 3 gerais, 4 PaxEspecial }
        $this->createLog(3, $url[0]);
        
        if($typeTotem === 'INTERNO'){

            $this->loadTemplateExterno('passageiro/euro', $getTotem['geral']);

        }else{
            
            $dados['pontos'] = $getTotem['pontos'];
            $dados['vans'] = $getTotem['vans'];

            $parameter 	= new Parametro();
            $dados['param'] = $parameter->getParametros();
            $this->loadTemplateExterno('mapEuro/index', $dados);
        }
        
        exit();
    }

    public function itinerarioEurofarmaBus(){
        
        $vanId = $_POST['vanId'];
        $vanLocation = new TotemEuro();
        $getVanLocation = $vanLocation->getVanLocation($vanId);
        echo json_encode($getVanLocation);
        die();

    }

    public function itinerarioEspecial()
	{
		
		$dados = array();

        $url = $_SERVER['REQUEST_URI'];
        $url = explode("/", $url);
        $url = $url[count($url)-1];
        $tot = $url;
        $url = explode("-", $url);

        $dados['ic'] = $url[0];

        $tot = explode("?", $tot);

        $param      = new TotemUser();

        if(!$param->hasTotem($tot[0], "USER")){
            $_SESSION['forbidden'] = [
				"code" => "404",
				"msg" => "Página não encontrada.",
                "showLogin" => false
			];
            header("Location: /");
            die();
        }

        // GRAVAR ACESSO \\
        // { tipos 1 itinetário, 2 passageiro, 3 gerais, 4 PaxEspecial }
        $this->createLog(4, $dados['ic']);

		$this->loadTemplateExterno('passageiro/itinerarioEspecial', $dados);
		exit();
	}

    public function seachEspecial()
    {
        
        $dados          = array();
        $req            = new \stdClass();
        $req->nome      = addslashes(trim($_POST['name']));
        $req->registro  = addslashes(trim($_POST['matricula']));
        $req->grupo     = addslashes($_POST['ic']);

        $param          = new TotemUser();
        $groupId        = $param->getTotemByOriginCode($_POST['ic']);
        $req->grupoAcess= count($groupId[0]) > 0 ? $groupId[0]['GRUPOSUSER'] : 0;
        $req->GrouID    = count($groupId[0]) > 0 ? $groupId[0]['id'] : 0;
      
        if($req->nome == "" && $req->registro == "" && $req->grupo == "") {
            $dados['error'] = "Ocorreu um erro inesperado. Favor tentar novamente.";
            echo json_encode($dados);
            die();
        }

        $pax                = new Pax();
        $retorn             = $pax->getDadosPassageiro( $req );

        foreach ($retorn as $t => $ret) {

            if(is_array($ret)) {

                foreach ($ret as $k => $v) {
                    $retorn['data'][$k]['CODIGO'] = $v['CODIGO'];
                    $retorn['data'][$k]['NOME'] = $v['NOME'];
                    $retorn['data'][$k]['MATRICULA_FUNCIONAL'] = $v['MATRICULA_FUNCIONAL'];
                    $retorn['data'][$k]['PREFIXOLINHAIDA'] = $v['PREFIXOLINHAIDA'];
                    $retorn['data'][$k]['NOMELINHAIDA'] = $v['NOMELINHAIDA'];
                    $retorn['data'][$k]['SENTIDOIDA'] = $v['SENTIDOIDA'];
                    $retorn['data'][$k]['DESCRICAOINTINERARIOIDA'] = $v['DESCRICAOINTINERARIOIDA'];
                    $retorn['data'][$k]['PREFIXOLINHAVOL'] = $v['PREFIXOLINHAVOL'];
                    $retorn['data'][$k]['NOMELINHAVOL'] = $v['NOMELINHAVOL'];
                    $retorn['data'][$k]['SENTIDOVOL'] = $v['SENTIDOVOL'];
                    $retorn['data'][$k]['DESCRICAOINTINERARIOVOL'] = $v['DESCRICAOINTINERARIOVOL'];
                    $retorn['data'][$k]['POLIDA'] = $v['POLIDA'];
                    $retorn['data'][$k]['POLVOLTA'] = $v['POLVOLTA'];
                }
             
            }
   
        }

        $dados['retorn']    = $retorn;
        $dados['cont']      = count($retorn);
       
        echo json_encode($dados);
        die();

    }

    public function notfound()
    {

        $this->loadTemplateExterno('passageiro/notfound');
        exit();
    }

    public function mapsUser()
    {
        $dados  = array();
        $rel 	= new Relatorios();
        $retorn = $rel->getDadosRotasUser($_POST['id']);
        
        $dados['html'] = array_values($retorn);
        $dados['cont'] = count($retorn);

       // $dados['pontosIt'] = $rel->getPontosItinerario($_POST['id']);

        echo json_encode($dados);
        die();
    }

    public function mapsUserItinerario()
    {
        $dados  = array();
        $rel 	= new Relatorios();
        $retorn = $rel->getPontosItinerario($_POST['codIntegra']);
        
        $dados['html'] = array_values($retorn);

        echo json_encode($dados);
        die();
    }

}
