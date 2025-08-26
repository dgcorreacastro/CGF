<?php

class rotasController extends controller 
{

	public function itinerario()
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

        if(!$param->hasTotem($tot[0], "COMP")){
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
        $this->createLog(1, $dados['ic']);

        $parameter 	= new Parametro();
        $dados['param'] = $parameter->getParametros();
        
		$this->loadTemplateExterno('rotas/index', $dados);
		exit();
	}

	public function seach()
	{

        $dados  = array();
        $req     	= new \stdClass();
        $req->end 	= $_POST['end'];
        $req->ic 	= $_POST['ic'];
        $req->all 	= $_POST['all'];

        $parameter 	= new Parametro();
        $param    	= $parameter->getParametros($_POST['ic']);

        if ($req->all == 0) {
            
            $apiKey = ($param['apiKey_active'] == 1) ? BACKKEYGOOGLE : 'xxxxxxxxxxxxxxxxxxxxx'; 

            $url    = 'https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($req->end).'&sensor=false&key='.$apiKey;
            $ch     = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            $result=curl_exec($ch);
            curl_close($ch);
            $geo 	= json_decode($result, true); 

            if (isset($geo['status']) && ($geo['status'] == 'OK')) 
            {
                $lat = $geo['results'][0]['geometry']['location']['lat']; 
                $lon = $geo['results'][0]['geometry']['location']['lng']; 
                $dist 		= $param;
                $distance   = $dist['Distancia'] ? intval($dist['Distancia']) / 1000 : 2000;
                $R          = 6371; 
                $maxLat     = $lat + rad2deg($distance/$R);
                $minLat     = $lat - rad2deg($distance/$R);
                $maxLon     = $lon + rad2deg(asin($distance/$R) / cos(deg2rad($lat)));
                $minLon     = $lon - rad2deg(asin($distance/$R) / cos(deg2rad($lat)));
    
                $rel 		= new Relatorios();
                $retorn     = $rel->getDadosRotas($req, $minLat, $minLon, $maxLat, $maxLon);
    
                $dados['success'] = true;
                $dados['html']    = array_values($retorn);
                $dados['cont']    = count($retorn);
                $dados['lat']     = $lat;
                $dados['lon']     = $lon;
    
            } else {
                $dados['error'] = true;
                $dados['msg']   = utf8_decode("Ocorreu um erro ao pesquisar o endereco. Tente digitar o endereco completo.");
            }

        } else {
            /**
             * PESQUISA TODAS AS LINHAS 
             */
            $rel 		= new Relatorios();
            $retorn     = $rel->getDadosRotas($req, 0, 0, 0, 0, true);

            $dados['success'] = true;
            $dados['html']    = array_values($retorn);
            $dados['cont']    = count($retorn);
            $dados['lat']     = 0;
            $dados['lon']     = 0;
        }

        echo json_encode($dados);
        die();
    }


}
