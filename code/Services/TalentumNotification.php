<?php 

class TalentumNotification 
{
    private $key        = "AAAAbtEC4lM:APA91bHIREcoxubr171VoIlxh4lAQdihtpleMQUQfnUh8NfbOaT5DUhJRKnkvA_vEmnze79NVCiNWIRLHFcCSgFkU-buyEP9RSl0RkHDez_Du5rE3tI6nD3_69JfjTaftcfcgKp92awo";
    private $url        = 'https://fcm.googleapis.com/fcm/send';
    private $baseURL    = 'https://iid.googleapis.com/iid/v1/';

    public function sendMessage($title, $body, $topic, $line = 0, $dot = 0, $user = 0, $timeSend = 0, $dbSys = null)
    {
     
        $headers = array (
            'Content-Type: application/json',
            'Authorization: key=' . $this->key
        );
        
        $notifyData = array (
            'title'         => $title,
            'body'          => $body,
            'click_action'  => "OPEN_ACTIVITY_1",
            "sound"         => "default"
        );
        
        $apiBody = [
            'notification'  => $notifyData,
            'data'          => $notifyData,
            'to'            => "/topics/{$topic}" 
        ];
        
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $this->url );
        curl_setopt ($ch, CURLOPT_POST, true );
        curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ($ch, CURLOPT_POSTFIELDS, json_encode($apiBody));
        
        $result = curl_exec ( $ch );
        
        curl_close ( $ch );

        return $this->saveReturnMessage($result, $title, $body, $topic, $line, $dot, $user, $timeSend, $dbSys);
    }

    public function saveReturnMessage($result, $title, $bodyMessage, $topic, $line, $dot, $user, $timeSend, $dbSys)
    {
        require_once __DIR__ . "/../models/App.php";

        if ($result){

            $data = json_decode($result);

            $body               = array();
            $body['return']     = $result;
            $body['messageId']  = isset($data->message_id) ? $data->message_id : "";
            $body['title']      = $title;
            $body['body']       = $bodyMessage;
            $body['topic']      = $topic;
            $body['line']       = $line;
            $body['dot']        = $dot;
            $body['user']       = $user;
            $body['timeSend']   = $timeSend;

            $app  = new App();
            $app->saveReturnMessage($body, $dbSys);

            if (!isset($data->message_id))
                return false;

        } else {
            return false;
        }

        return true;
    }

    public function getTopics($deviceToken)
    {
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: key=' . $this->key,
        ];

        $url = $this->baseURL . 'info/' . $deviceToken. '?details=true';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($result, true);

        $topicsReturn = [];

        $msg = $result ? 'Sem tópicos para remover' : 'Erro ao encontras os Tópicos';

        if (isset($response['rel']['topics'])) {

            $topics = $response['rel']['topics'];

            foreach ($topics as $topicName => $topic) {$topicsReturn[] = $topicName;}

            $msg = count($topicsReturn) == 0 ? 'Nenhum tópico para remover' : 'Tópicos encontrados';

            
        }

        return array('status' => $result, 'msg' => $msg, 'topics' => $topicsReturn);
    }
        
    public function alreadySentMessage($topic, $line, $dot, $timeSend, $pdoMessage = null)
    {
        require_once __DIR__ . "/../models/App.php";

        $app  = new App();
        return $app->alreadySentMessage($topic, $line, $dot, $timeSend, $pdoMessage);
    }
    
    public function sendMailGeneric($emails, $title, $body, $attachments = []) 
    {

        if(empty($emails)) 
           return array("status" => false, "message" => "Email em Branco");

        $data = array(
            "message" 		=> utf8_encode($body),
            "subject" 		=> $title,
            "to" 			=> $emails,
            "attachments" 	=> $attachments,
            "client" 		=> 0,
            "application" 	=> 9,
            "customTemplate" => true
        );

        $json    = json_encode($data);
        $headers = array('Content-Type: application/json');

        try {

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, ''); // TODO: POPULATE WITH URL THAT SENDS E-MAILS
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($ch);

            if (curl_errno($ch)) 
                $return = array("status" => false, "message" => "Erro na comunicação");
            else 
                $return = array("status" => true, "message" => $result);

            curl_close($ch);

            return $return;

        } catch (Exception $e) {

            curl_close($ch);
            return array("status" => false, "message" => "Erro ao processar - " . $e->getMessage());
            
        }

    }

}

