<?php

class relatorioCircularEuroFarmaController extends controller 
{
    public function index()
    {
        $dados = array();
        $dados['html'] = "";
        $data_inicio = date("Y-m-d");
        $data_fim = date("Y-m-d"); 
        
        $dados['data_inicio'] = $data_inicio;
        $dados['data_fim'] = $data_fim;

        $rel = new TotemEuro();
        $req = new \stdClass();

        $req->data_inicio = $data_inicio;
        $req->data_fim 	= $data_fim;

        $dados['vans'] = $rel->getCarrosCirc();
        $relCirc = $rel->getDadosCirEuro($req);
        if($relCirc['status']){
            $dados['html'] = $this->itensCircular($relCirc['positions']);
        }

        $param                  = new Parametro();
        $param                  = $param->getParametros(true);
        $dados['timeAtualiza']  = $param['time_atualizar'] ? $param['time_atualizar'] : 20;
        $dados['showRelTimer']  = $param['show_rel_timer'] ?? 0;

        $this->loadTemplate('relatorios/circulareuro/index', $dados);
		exit;
    }

    private function itensCircular($relCirc){

        $html   = "";
        foreach($relCirc as $k => $dados){
            $nameVan = trim($k);
            $html .= "<tr class='trHeight'><td scope='col' colspan='2'><div class='nomeLinha'><span>Carro: <b>". $k ."</b> - Placa: <b>". $relCirc[$k][0]['PLACA'] ."</b></span></div><div class='nomeLinhaIn'></div></td></tr>";
            foreach($dados as $ponto){
                $ponto = (Object) $ponto;
                $html .= "<tr class='toMark'>";
                $html .= "<td>". $ponto->dataPos ."</td>";
                $html .= "<td class='tdBorder5'>". $ponto->pontoNome ."</td>";
                // $html .= "<td>". $ponto->distance ."</td>";
                // $html .= "<td class='tdBorder5'>". $ponto->device_id ."</td>";
                $html .= "</tr>";
                
            }
        }

        return $html;

    }

    public function getDadosCirEuro()
	{
		ignore_user_abort(false);
        session_write_close();

        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

		$req = new \stdClass();

		$rel = new TotemEuro();

		$req->data_inicio   = $body->data_inicio;
        $req->data_fim 	    = $body->data_fim;
        $req->carro 	    = $body->carro;
        $req->distancia 	= $body->distancia;

        $dados = array();
		$relCirc = $rel->getDadosCirEuro($req);
        if($relCirc['status']){
            $dados["html"] = $this->itensCircular($relCirc['positions']);
        }

		echo json_encode($dados);
		die();
	}
}