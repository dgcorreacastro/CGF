<?php

class  ApiController extends controller 
{

    private $passJwt = "F!yb7ztN1UtQ08a@eRNnHa7@Tal2022!";

    public function generateToken()
    {
        $this->MethodValid($_SERVER, 'POST');

        // VALIDAR CREDENCIAIS \\
        $body   = file_get_contents("php://input");
        $body   = json_decode($body);
        $this->ValidCredentials($body);

        //Header Token
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

        //Payload - Content
        $payload = [
            'iss' => '', // TODO: POPULATE WITH INDEX URL
            'sub' => 'Cgf',
            'aud' => 'private',
            'exp' => strtotime(date("Y-m-d H:i:s", strtotime("+1 hour"))),
            'uid' => 1,
            'name' => 'Veltrac',
        ];

        //JSON
        $header = json_encode($header);
        $payload = json_encode($payload);

        //Base 64
        $header = base64_encode($header);
        $payload = base64_encode($payload);

        //Sign
        $sign = hash_hmac('sha256', $header . "." . $payload, $this->passJwt, true);
        $sign = base64_encode($sign);

        //Token
        $token = $header . '.' . $payload . '.' . $sign;

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode(array("status"=>true, "token"=>$token));
        //echo $token;
        exit;
    }

    public function sendingPositions()
    { // Receber os dados da Veltrac \\

        $this->MethodValid($_SERVER, 'POST');

        $body   = file_get_contents("php://input");

        /**
         * VERIFICAR SE O TOKEN ESTÁ VALIDO
         */
        $isValid = $this->isValidToken(getallheaders());

        if (!$isValid['status'])
        {
            // Retornando mensagem com o erro
            header('HTTP/1.0 400');
            http_response_code(400);
            echo json_encode($isValid);
            die;
        }

        /**
         * SALVA OS DADOS NO DB
         */
        $pos = new Posicoes();
        $pos->save($body);

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode(  array("status" => true, "message" => "Dados recebidos com sucesso!") );
        exit;
    }

    public function loginApp()
    {
        /**
         * CLIENTE VAI LOGAR COM O QRCODE OU UM CÓDIGO DE VALIDAÇÃO
         * APÓS VALIDAR, VAI PUXAR TODAS AS LINHAS DO CLIENTE NO CGF
         */

        $lines = array();

        $ret = array("status" => true, "data" => $lines);

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode($ret);
        exit;
    }

    public function getLines()
    {
        /**
         * RETORNA TODOS OS PONTOS DE EMBARQUE DAQUELA LINHA
         */

        $pontos = array();

        $ret = array("status" => true, "data" => $pontos);

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode($ret);
        exit;
    }

    public function getPontosEmbarques()
    {
        /**
         * RETORNA TODOS OS PONTOS DE EMBARQUE DAQUELA LINHA
         */

        $pontos = array();

        $ret = array("status" => true, "data" => $pontos);

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode($ret);
        exit;
    }

    public function getDataVehicle()
    {
        /**
         * RETORNA DADOS DO VEÍCULO VINDO DA API DA VELTRAC
         */

        $vehicle = array();

        $ret = array("status" => true, "data" => $vehicle);

        header('HTTP/1.0 200 OK');
        http_response_code(200);
        echo json_encode($ret);
        exit;
    }

    public function testToken()
    {
        $body   = file_get_contents("php://input");
        $body   = json_decode($body);

        if ($body->status)
        {

            header('HTTP/1.0 200 OK');
            http_response_code(200);
            print_r($body->token);
            exit;

        }

        header('HTTP/1.0 400');
        http_response_code(400);
        echo "Error";
        die;

    }

    /**
     * 
     * FUNCTIONS GERAIS PARA API
     * 
     */
    private function isValidToken($headers)
    {
        $arr = array();
     
        if(isset($headers['Authorization'])){

            $tok = explode('Bearer ', $headers['Authorization']);
      
            if(isset($tok[1])){

                $token      = $tok[1];
                $part       = explode(".",$token);
                $header     = $part[0];
                $payload    = $part[1];
                $signature  = $part[2];
                $valid      = hash_hmac('sha256',"$header.$payload", $this->passJwt,true);
                $valid      = base64_encode($valid);
               
                if($signature == $valid){

                    $pay = json_decode( base64_decode( str_replace('_', '/', str_replace('-','+',$payload))));
    
                    // Verifica se não expirou
                    if (date('Y-m-d H:i:s', $pay->exp) < date('Y-m-d H:i:s')){
                        return array("status" => false, "message" => "Expired token");
                    } 

                    return array("status" => true, "message" => "Valid token");

                } else{
                    return array("status" => false, "message" => "Invalid Token");
                }

            } else{
                return array("status" => false, "message" => "Invalid Token Bearer");
            }

        }

        return array("status" => false, "message" => "Token not sent");

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

    private function ValidCredentials($body)
    {
        
        if ( !isset($body->email) || !isset($body->password) )
        {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo "Email and password are required";
            die;
        }

        if ( $body->email != "ti@taipastur.com.br" )
        {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo "Invalid email";
            die;
        }

        if ( $body->password != "153710fab692a899fb6be2f8824d4430" ) // taipastur2022!@
        {
            header('HTTP/1.0 400');
            http_response_code(400);
            echo "Invalid password";
            die;
        }

        return true;
    }
    
}