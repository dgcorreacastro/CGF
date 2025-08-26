<?php

class veltrac {

	protected $db;

    /**
     * Para Conexão Veltrac
     */
    protected $host   = ""; // TODO: POPULATE WITH DATABASE HOST ADDRESS
    protected $port   = ""; // TODO: POPULATE WITH DATABASE PORT NUMBER
    protected $user   = ""; // TODO: POPULATE WITH DATABASE USER
    protected $pass   = ""; // TODO: POPULATE WITH DATABASE PASSWORD
    protected $dbName = ""; // TODO: POPULATE WITH DATABASE NAME
    protected $pdoSql;
    
    /**
     * Para Conexão com a Globus
     */
    protected $hostGl = ""; // TODO: POPULATE WITH DATABASE HOST ADDRESS
    protected $portGl = ""; // TODO: POPULATE WITH DATABASE PORT NUMBER
    protected $userGl = ""; // TODO: POPULATE WITH DATABASE USER
    protected $passGl = ""; // TODO: POPULATE WITH DATABASE PASSWORD
    protected $dbGl   = ""; // TODO: POPULATE WITH DATABASE NAME



	public function __construct() {

		global $db;
		$this->db = $db;

        try {
            $this->pdoSql = new \PDO ("dblib:host=$this->host:$this->port;dbname=$this->dbName;charset=utf8","$this->user","$this->pass");
        } catch (\Throwable $th) {
            $error =array('error' => true, 'msg'=>'Ocorreu um erro ao tentar conectar ao Banco de Dados, tente novamente.');
            return $error;
        }

	}

}