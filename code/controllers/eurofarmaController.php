<?php

class eurofarmaController extends controller 
{

	public function index()
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

		$this->loadTemplateExterno('passageiro/index', $dados);
		exit();
	}

	public function seach()
	{

     
        $dados          = array();
        $req     	    = new \stdClass();
        $req->nome 	    = addslashes(trim($_POST['name']));
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
                       $retorn['data'][$k]['NOME'] = utf8_encode($v['NOME']);

                    if(isset($v['NOMELINHAIDA']))
                        $retorn['data'][$k]['NOMELINHAIDA'] = utf8_encode($v['NOMELINHAIDA']);

                    if(isset($v['NOMELINHAVOL']))
                        $retorn['data'][$k]['NOMELINHAVOL'] = utf8_encode($v['NOMELINHAVOL']);

                    if(isset($v['DESCRICAOINTINERARIOIDA']))
                        $retorn['data'][$k]['DESCRICAOINTINERARIOIDA'] = utf8_encode($v['DESCRICAOINTINERARIOIDA']);

                    if(isset($v['PREFIXOLINHAIDA']))
                        $retorn['data'][$k]['PREFIXOLINHAIDA'] = utf8_encode($v['PREFIXOLINHAIDA']);

                    if(isset($v['PREFIXOLINHAVOL']))
                        $retorn['data'][$k]['PREFIXOLINHAVOL'] = utf8_encode($v['PREFIXOLINHAVOL']);

                    if(isset($v['DESCRICAOINTINERARIOVOL']))
                        $retorn['data'][$k]['DESCRICAOINTINERARIOVOL'] = utf8_encode($v['DESCRICAOINTINERARIOVOL']);

                    if(isset($v[4]))
                        $retorn['data'][$k][4] = utf8_encode($v[4]);

                    if(isset($v[6]))
                        $retorn['data'][$k][6] = utf8_encode($v[6]);

                    if(isset($v[7]))
                        $retorn['data'][$k][7] = utf8_encode($v[7]);

                    if(isset($v[8]))
                        $retorn['data'][$k][8] = utf8_encode($v[8]);

                    if(isset($v[10]))
                        $retorn['data'][$k][9] = utf8_encode($v[9]);

			         if(isset($v[10]))
                        $retorn['data'][$k][10] = utf8_encode($v[10]);

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
}
