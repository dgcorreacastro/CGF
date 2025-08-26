<?php

require_once __DIR__ . '/../core/model.php';

class Usuarios extends model 
{

	public function login($email, $senha) 
	{

		$sql = $this->db->prepare("SELECT * FROM users WHERE email = :email AND password = :senha AND deleted_at is null");
		$sql->bindValue(":email", $email);
		$sql->bindValue(":senha", md5($senha));
		$sql->execute();

		if($sql->rowCount() > 0) {
			$dado 						= $sql->fetch();
			$_SESSION['cLogin'] 		= $dado['id'];
			$_SESSION['cName'] 			= $dado['name'];
			$_SESSION['cType'] 			= $dado['type'];
			$_SESSION['groupUserID'] 	= $dado['groupUserID'];
			return true;
		} else {
			return false;
		}
	}

	public function loginEscala($email, $senha) 
	{

		$sql = $this->db->prepare("SELECT * FROM userEscala WHERE email = :email AND pass = :senha AND deleted_at is null");
		$sql->bindValue(":email", $email); 
		$sql->bindValue(":senha", md5($senha));
		$sql->execute();

		if($sql->rowCount() > 0) 
		{
			$dado = $sql->fetch();
			$_SESSION['cLogin'] = $dado['id'];
			$_SESSION['cName'] 	= $dado['nome'];
			$_SESSION['cType'] 	= $dado['type']; // 1 LIDER 2 RH
			$_SESSION['cGr'] 	= $dado['grupoID'];
			$_SESSION['cFret'] 	= true; 
			return true;
		} else {
			return false;
		}
	}

	public function acessoGrupo()
	{
		$array = array();

		if(isset($_SESSION['cType']) && $_SESSION['cType'] == 1){

			$sql = $this->db->prepare("SELECT * FROM acesso_grupos ORDER BY NOME");
			$sql->execute();
			$array = $sql->fetchAll();

		} else {
			$sql = $this->db->prepare("SELECT * FROM acesso_grupos 
				WHERE id IN (
                SELECT grupo_id FROM usuario_grupos WHERE usuario_id = {$_SESSION['cLogin']} AND deleted_at is null
            ) ORDER BY NOME");
			$sql->execute();
			$array = $sql->fetchAll();
		}

		return $array;
	}

	public function grupoUsers($tagCadOnly = false)
	{
		if ($tagCadOnly) {
			$sql = $this->db->prepare("
				SELECT grupo_linhas.* 
				FROM grupo_linhas 
				LEFT JOIN parameter_group ON parameter_group.group_id = grupo_linhas.id
				WHERE grupo_linhas.deleted_at IS NULL 
					AND (parameter_group.cad_pax_tag = 1 OR parameter_group.group_id IS NULL) 
				ORDER BY grupo_linhas.NOME
			");
		} else {
			// Caso contrário, trazer todos os grupos
			$sql = $this->db->prepare("
				SELECT * 
				FROM grupo_linhas 
				WHERE deleted_at IS NULL 
				ORDER BY NOME
			");
		}
		$sql->execute();
		return $sql->fetchAll();
	}

	public function acessoGrupoNot($id)
	{
		$array = array();
		$sql = $this->db->prepare("SELECT * FROM acesso_grupos 
				WHERE id NOT IN (
                SELECT grupo_id FROM usuario_grupos WHERE usuario_id = {$id} AND deleted_at is null AND grupo_id is not null
            ) AND deleted_at is null ORDER BY NOME");
			$sql->execute();
			$array = $sql->fetchAll();

		return $array;
	}

	public function acessoGrupoIn($id)
	{
		$array = array();
		$sql = $this->db->prepare("SELECT * FROM acesso_grupos 
				WHERE id IN (
                SELECT grupo_id FROM usuario_grupos WHERE usuario_id = {$id} AND deleted_at is null
            ) AND deleted_at is null ORDER BY NOME");
			$sql->execute();
			$array = $sql->fetchAll();

		return $array;
	}

	public function veiculo()
	{
		$array = array();

		if(isset($_SESSION['cType']) && $_SESSION['cType'] == 1){

			$sql = $this->db->prepare("SELECT * FROM veiculos ORDER BY NOME");
			$sql->execute();
			$array = $sql->fetchAll();
			
		} else {
			$sql = $this->db->prepare("SELECT * FROM veiculos 
				WHERE id IN (
                SELECT carro_id FROM usuario_carros WHERE usuario_id = {$_SESSION['cLogin']} AND deleted_at is null
            	) AND deleted_at is null ORDER BY NOME");
			$sql->execute();
			$array = $sql->fetchAll();
		}

		return $array;
	}

	public function getAllUsers()
	{
		$array = array();
		$sql = $this->db->prepare("SELECT id, name, email, ativo, type FROM users WHERE deleted_at is null AND id <> 1 order by name");
		$sql->execute();
		$array = $sql->fetchAll();

		foreach($array as $k => $user){

			$sql = $this->db->prepare("SELECT * FROM permissionsMenu WHERE userID = {$user['id']} AND menuID = 41 AND deleted_at is null LIMIT 1");
			$sql->execute();

			$array[$k]['alterar_dados'] = $sql->rowCount();
		}

		return $array;
	}

	public function salvarUsuario($post)
	{

		$retorno = array(
			"status" => true,
			"title" => "SUCESSO",
			"text" => "Adicionado com sucesso!",
			"icon" => "success",
			"button" => "OK"
		);


		//checar e-mail
		$sql = $this->db->prepare("SELECT * FROM users WHERE email = :email AND deleted_at is null LIMIT 1");
		$sql->bindValue(":email", $post['email']);
		$sql->execute();

		if($sql->rowCount() != 0) {
			$retorno['status'] = false;
			$retorno['title'] =  "ATENÇÃO";
			$retorno['text'] = "Já existe outro usuário com o e-mail informado!";
			$retorno['icon'] = "warning";
			return $retorno;
		}

		$sql = $this->db->prepare("INSERT INTO users SET name = :name, email = :email, password = :password, type = :type, ativo = :ativo, groupUserID = :groupUserID, created_at = NOW()");
		$sql->bindValue(":name", $post['name']);
		$sql->bindValue(":email", $post['email']);
		$sql->bindValue(":password", md5($post['password']));
		$sql->bindValue(":type", $post['type']);
		$sql->bindValue(":ativo", $post['ativo']);
		$sql->bindValue(":groupUserID", $post['groupUserID']);
		$sql->execute(); 

		if (!$sql){
			$retorno['status'] = false;
			$retorno['title'] =  "ERRO";
			$retorno['text'] = "Ocorreu um erro ao adicionar, tente novamente!";
			$retorno['icon'] = "error";
		}

		if($post['type'] == 3 || $post['type'] == 1){
			return $retorno;
		}

		$idUser = $this->db->lastInsertId();

		$idsCar   = explode(",", $post['idsCardPermission']);
        $idsLinha = explode(",", $post['idsLinhaPermission']);
        $idsGrupo = explode(",", $post['idsGrupoPermission']);

		if (count($idsCar) > 0)
		{

			foreach($idsCar AS $car){
				$sql = $this->db->prepare("INSERT INTO usuario_carros SET usuario_id = :usuario_id, carro_id = :carro_id");
				$sql->bindValue(":usuario_id", $idUser);
				$sql->bindValue(":carro_id", $car);
				$sql->execute();
			}

		}
      
		if (count($idsLinha) > 0)
		{
			foreach($idsLinha AS $linha){
				$sql = $this->db->prepare("INSERT INTO usuario_linhas SET usuario_id = :usuario_id, linha_id = :linha_id");
				$sql->bindValue(":usuario_id", $idUser);
				$sql->bindValue(":linha_id", $linha);
				$sql->execute();
			}
		}

		if (count($idsGrupo) > 0)
		{
			foreach($idsGrupo AS $gr){
				$sql = $this->db->prepare("INSERT INTO usuario_grupos SET usuario_id = :usuario_id, grupo_id = :grupo_id");
				$sql->bindValue(":usuario_id", $idUser);
				$sql->bindValue(":grupo_id", $gr);
				$sql->execute();
			}
		}

		########################## INCLUINDO PERMISSÕES PARA O USUÁRIO ########################
		$sql = $this->db->prepare("INSERT INTO permissionsMenu SET menuID = :menuID, userID = :userID, created_at = NOW()");
			$sql->bindValue(":menuID", 1);
			$sql->bindValue(":userID", $idUser);
			$sql->execute();

		if (isset($post['menusUser']))
		{
			foreach($post['menusUser'] AS $mu){
				$sql = $this->db->prepare("INSERT INTO permissionsMenu SET menuID = :menuID, userID = :userID, created_at = NOW()");
				$sql->bindValue(":menuID", $mu);
				$sql->bindValue(":userID", $idUser);
				$sql->execute();
			}
		}

		return $retorno;
	}


	public function atualizarUsuario($post)
	{

		$retorno = array(
			"status" => true,
			"title" => "SUCESSO",
			"text" => "Edição Salva com sucesso!",
			"icon" => "success",
			"button" => "OK"
		);
		
		//checar e-mail
		$sql = $this->db->prepare("SELECT * FROM users WHERE email = :email AND id != :id AND deleted_at is null LIMIT 1");
		$sql->bindValue(":email", $post['email']);
		$sql->bindValue(":id", $post['idUser']);
		$sql->execute();

		if($sql->rowCount() != 0) {
			$retorno['status'] = false;
			$retorno['title'] =  "ATENÇÃO";
			$retorno['text'] = "Já existe outro usuário com o e-mail informado!";
			$retorno['icon'] = "warning";
			return $retorno;
		}

		if(isset($post['password']) && $post['password'] != ""){
			$sql = $this->db->prepare("UPDATE users SET name = :name, email = :email, password = :password, type = :type, ativo = :ativo, groupUserID = :groupUserID, updated_at = NOW() where id = :id");
			$sql->bindValue(":name", $post['name']);
			$sql->bindValue(":email", $post['email']);
			$sql->bindValue(":password", md5($post['password']));
			$sql->bindValue(":type", $post['type']);
			$sql->bindValue(":ativo", $post['ativo']);
			$sql->bindValue(":groupUserID", $post['groupUserID']);
			$sql->bindValue(":id", $post['idUser']);
			$sql->execute();
		} else {
			$sql = $this->db->prepare("UPDATE users SET name = :name, email = :email, type = :type, ativo = :ativo, groupUserID = :groupUserID, updated_at = NOW() where id = :id");
			$sql->bindValue(":name", $post['name']);
			$sql->bindValue(":email", $post['email']);
			$sql->bindValue(":type", $post['type']);
			$sql->bindValue(":ativo", $post['ativo']);
			$sql->bindValue(":groupUserID", $post['groupUserID']);
			$sql->bindValue(":id", $post['idUser']);
			$sql->execute();
		}
		
		if (!$sql){
			$retorno['status'] = false;
			$retorno['title'] =  "ERRO";
			$retorno['text'] = "Ocorreu um erro ao atualizar, tente novamente!";
			$retorno['icon'] = "error";
		}

		if($post['type'] == 3 || $post['type'] == 1){
			return $retorno;
		}

		$idUser   	= $post['idUser'];
		$now		= date("Y-m-d H:i:s");


		//atualiza menus somente se necessário
		if($post['updateMenus']){

			$allIdsMenus = array();

			$allIdsMenus[] = 1;

			if (isset($post['menusUser']))
			{
				foreach($post['menusUser'] AS $mu)
				{
					$allIdsMenus[] = $mu;

					$sql = $this->db->prepare("SELECT * FROM permissionsMenu WHERE userID = {$idUser} AND menuID = {$mu} LIMIT 1");

					$sql->execute();

					if($sql->rowCount() > 0) {

						$sql = $this->db->prepare("UPDATE permissionsMenu SET deleted_at = :deleted_at WHERE userID = {$idUser} AND menuID = {$mu}");
						$sql->bindValue(":deleted_at", null);
						$sql->execute();

					} else {

						$sql = $this->db->prepare("INSERT INTO permissionsMenu SET menuID = :menuID, userID = :userID, created_at = NOW()");
						$sql->bindValue(":menuID", $mu);
						$sql->bindValue(":userID", $idUser);
						$sql->execute();

					}
					
				}
			}

			$idsMenu = implode(",", $allIdsMenus);

			if ($idsMenu != "")
			{
				$sql = $this->db->prepare("UPDATE permissionsMenu SET deleted_at = :deleted_at WHERE userID = {$idUser} AND menuID NOT IN ({$idsMenu})");
				$sql->bindValue(":deleted_at", $now);
				$sql->execute();
			}

		}

		//atualiza carros somente se necessário
		if($post['updateCarros']){

			$idsCar		= explode(",", $post['idsCardPermission']);
			$allIdsCar	= array();

			if (count($idsCar) > 0)
			{
				
				foreach($idsCar AS $car){

					if (!isset($car) || $car == "")
						continue;

					$allIdsCar[] = $car;
					
					$sql = $this->db->prepare("SELECT carro_id FROM usuario_carros WHERE usuario_id = {$idUser} AND carro_id = {$car} LIMIT 1");
					$sql->execute();

					if($sql->rowCount() > 0) {
						$sql = $this->db->prepare("UPDATE usuario_carros SET deleted_at = :deleted_at WHERE usuario_id = {$idUser} AND carro_id = {$car}");
						$sql->bindValue(":deleted_at", null);
						$sql->execute();
					} else {
						$sql = $this->db->prepare("INSERT INTO usuario_carros SET usuario_id = :usuario_id, carro_id = :carro_id");
						$sql->bindValue(":usuario_id", $idUser);
						$sql->bindValue(":carro_id", $car);
						$sql->execute();
					}
				}
			}

			$idsCars = implode(",", $allIdsCar);

			if ($idsCars != "")
			{
				$sql = $this->db->prepare("UPDATE usuario_carros SET deleted_at = :deleted_at WHERE usuario_id = {$idUser} AND carro_id NOT IN ({$idsCars})");
				$sql->bindValue(":deleted_at", $now);
				$sql->execute();
			}

		}

		//atualiza linhas somente se necessário
		if($post['updateLinhas']){

			$idsLinha 		= explode(",", $post['idsLinhaPermission']);
			$allIdsLinha	= array();

			if (count($idsLinha) > 0)
			{
				foreach($idsLinha AS $linha){

					if (!isset($linha) || $linha == "")
						continue;

					$allIdsLinha[] = $linha;

					$sql = $this->db->prepare("SELECT linha_id FROM usuario_linhas WHERE usuario_id = {$idUser} AND linha_id = {$linha} LIMIT 1");
					$sql->execute();

					if($sql->rowCount() > 0) {
						$sql = $this->db->prepare("UPDATE usuario_linhas SET deleted_at = :deleted_at WHERE usuario_id = {$idUser} AND linha_id = {$linha}");
						$sql->bindValue(":deleted_at", null);
						$sql->execute();
					} else {
						$sql = $this->db->prepare("INSERT INTO usuario_linhas SET usuario_id = :usuario_id, linha_id = :linha_id");
						$sql->bindValue(":usuario_id", $idUser);
						$sql->bindValue(":linha_id", $linha);
						$sql->execute();
					}
				}
			}

			$idsLinh = implode(",", $allIdsLinha);

			if ($idsLinh != "")
			{
				$sql = $this->db->prepare("UPDATE usuario_linhas SET deleted_at = :deleted_at WHERE usuario_id = {$idUser} AND linha_id NOT IN ({$idsLinh})");
				$sql->bindValue(":deleted_at", $now);
				$sql->execute();
			}

		}

		//atualiza grupos somente se necessário
		if($post['updateGrupos']){

			$idsGrupo 		= explode(",", $post['idsGrupoPermission']);
			$allIdsGrupo	= array();

			if (count($idsGrupo) > 0)
			{
				foreach($idsGrupo AS $gr){

					if (!isset($gr) || $gr == "")
						continue;

					$allIdsGrupo[] = $gr;

					$sql = $this->db->prepare("SELECT grupo_id FROM usuario_grupos WHERE usuario_id = {$idUser} AND grupo_id = {$gr} LIMIT 1");
					$sql->execute();

					if($sql->rowCount() > 0) {
						$sql = $this->db->prepare("UPDATE usuario_grupos SET deleted_at = :deleted_at WHERE usuario_id = {$idUser} AND grupo_id = {$gr}");
						$sql->bindValue(":deleted_at", null);
						$sql->execute();
					} else {
						$sql = $this->db->prepare("INSERT INTO usuario_grupos SET usuario_id = :usuario_id, grupo_id = :grupo_id");
						$sql->bindValue(":usuario_id", $idUser);
						$sql->bindValue(":grupo_id", $gr);
						$sql->execute();
					}
				}
			}

			$idsGrup = implode(",", $allIdsGrupo);

			if ($idsGrup != "")
			{
				$sql = $this->db->prepare("UPDATE usuario_grupos SET deleted_at = :deleted_at WHERE usuario_id = {$idUser} AND grupo_id NOT IN ({$idsGrup})");
				$sql->bindValue(":deleted_at", $now);
				$sql->execute();
			}

		}

		return $retorno;
	}

	public function usuarioDados($post)
	{
		$retorno = array(
			"status" => true,
			"title" => "SUCESSO",
			"text" => "Edição Salva com sucesso!",
			"icon" => "success",
			"button" => "OK"
		);
		
		//checar e-mail
		$sql = $this->db->prepare("SELECT * FROM users WHERE email = :email AND id != :id AND deleted_at is null LIMIT 1");
		$sql->bindValue(":email", $post['email']);
		$sql->bindValue(":id", $post['idUser']);
		$sql->execute();

		if($sql->rowCount() != 0) {
			$retorno['status'] = false;
			$retorno['title'] =  "ATENÇÃO";
			$retorno['text'] = "Já existe outro usuário com o e-mail informado!";
			$retorno['icon'] = "warning";
			return $retorno;
		}

		if(isset($post['password']) && $post['password'] != ""){

			//checar senha
			$sql = $this->db->prepare("SELECT * FROM users WHERE id = :id AND password = :passwordAtual AND deleted_at is null LIMIT 1");
			$sql->bindValue(":passwordAtual", md5($post['passwordAtual']));
			$sql->bindValue(":id", $post['idUser']);
			$sql->execute();

			if($sql->rowCount() == 0) {
				$retorno['status'] = false;
				$retorno['title'] =  "ATENÇÃO";
				$retorno['text'] = "A Senha Atual está incorreta!";
				$retorno['icon'] = "warning";
				return $retorno;

			}

			$sql = $this->db->prepare("UPDATE users SET name = :name, email = :email, password = :password, updated_at = NOW() where id = :id");
			$sql->bindValue(":name", $post['name']);
			$sql->bindValue(":email", $post['email']);
			$sql->bindValue(":password", md5($post['password']));
			$sql->bindValue(":id", $post['idUser']);
			$sql->execute();

			$retorno['text'] = "Edição Salva com sucesso! <br> <b>Senha alterada com sucesso!</b>";

		}else {
			$sql = $this->db->prepare("UPDATE users SET name = :name, email = :email, updated_at = NOW() where id = :id");
			$sql->bindValue(":name", $post['name']);
			$sql->bindValue(":email", $post['email']);
			$sql->bindValue(":id", $post['idUser']);
			$sql->execute();
		}
		
		if (!$sql){
			$retorno['status'] = false;
			$retorno['title'] =  "ERRO";
			$retorno['text'] = "Ocorreu um erro ao atualizar, tente novamente!";
			$retorno['icon'] = "error";

		}else{

			$_SESSION['cName'] = $post['name'];

		}

		return $retorno;
	}

	public function passwordReset($post){

		//se tiver senha enta salvar aqui
		if(isset($post['password']) && $post['password'] != ""){

			$retorno = array(
				"status" => true,
				"title" => "SUCESSO",
				"text" => "Senha alterada com sucesso!",
				"icon" => "success",
				"button" => "OK"
			);

			$sql = $this->db->prepare("UPDATE users SET password = :password, updated_at = NOW() where email = :email");
			$sql->bindValue(":password", md5($post['password']));
			$sql->bindValue(":email", $post['email']);
			$sql->execute();

			if (!$sql){
				$retorno['status'] = false;
				$retorno['title'] =  "ERRO";
				$retorno['text'] = "Ocorreu um erro ao atualizar, tente novamente!";
				$retorno['icon'] = "error";

			}else{

				$sql = $this->db->prepare("UPDATE password_resets SET deleted_at = :deleted_at WHERE id = :id");
				$sql->bindValue(":deleted_at", date("Y-m-d H:i:s"));
				$sql->bindValue(":id", $_SESSION['idTokenPass']);
				unset($_SESSION['idTokenPass']);
				$sql->execute();

			}			

			return $retorno;
			
		}

		//se já tiver o código trata aqui
		if(isset($post['code']) && $post['code'] != ""){

			$retorno = array(
				"status" => true,
			);

			//checar código
			$sql = $this->db->prepare("SELECT * FROM password_resets WHERE email = :email AND token = :token AND created_at > :validToken AND deleted_at is null LIMIT 1");
			$sql->bindValue(":email", $post['email']);
			$sql->bindValue(":token", $post['code']);
			$sql->bindValue(":validToken", date("Y-m-d H:i:s", strtotime('- 2 hours')));
			$sql->execute();


			if($sql->rowCount() == 0) {
				$retorno['status'] = false;
				$retorno['title'] =  "ERRO";
				$retorno['text'] = "Código Inválido";
				$retorno['icon'] = "error";
				$retorno['button'] = "OK";
				return $retorno;
			}

			$getToken = $sql->fetch(PDO::FETCH_OBJ);
			$idToken = $getToken->id;
			$_SESSION['idTokenPass'] = $idToken;
			
			return $retorno;

		}

		$retorno = array(
			"status" => true,
			"title" => "Código Enviado",
			"text" => "Foi enviado um código para o e-mail:<br><b>{$post['email']}</b>",
			"icon" => "success",
			"button" => "Inserir Código"
		);

		//checar e-mail
		$sql = $this->db->prepare("SELECT * FROM users WHERE email = :email AND deleted_at is null LIMIT 1");
		$sql->bindValue(":email", $post['email']);
		$sql->execute();

		if($sql->rowCount() == 0) {
			$retorno['status'] = false;
			$retorno['title'] =  "ERRO";
			$retorno['text'] = "E-mail não encontrado!";
			$retorno['icon'] = "error";
			$retorno['button'] = "OK";
			return $retorno;
		}

		$now = date("Y-m-d H:i:s");
		$token = random_int(100000, 999999);
		$sql = $this->db->prepare("INSERT INTO password_resets SET email = :email, token = :token, created_at = :created_at");
		$sql->bindValue(":email", $post['email']);
		$sql->bindValue(":token", $token);
		$sql->bindValue(":created_at", $now);
		$sql->execute();

		if (!$sql){
			$retorno['status'] = false;
			$retorno['title'] =  "ERRO";
			$retorno['text'] = "Ocorreu um erro ao criar um código, tente novamente!";
			$retorno['icon'] = "error";
			$retorno['button'] = "OK";
		}

		require_once  __DIR__ . '/../Services/TalentumNotification.php';
        
        $notify = new TalentumNotification;

        $title  = "Redefinição de Senha ".PORTAL_NAME;
        $body   = $this->templateEmailRecoverPassword($token);

		try{
			
			$notify->sendMailGeneric([trim($_POST['email'])], $title, $body);

		}catch (\Throwable $th) {

			$retorno['status'] = false;
			$retorno['title'] =  "ERRO";
			$retorno['text'] = "Ocorreu um erro ao enviar o código, tente novamente!";
			$retorno['icon'] = "error";
			$retorno['button'] = "OK";
			
		}

		return $retorno;

	}

	private function templateEmailRecoverPassword($token)
    {

        $html = "<div style='width:60%;margin: auto;padding: 15px;background-color: white;color:#2a1e52'>";
        $html .= "<div style='text-align: center'>";
        $html .= "<img src='https://#URL#/assets/images/logoApp.png' width='150px'>"; // TODO: POPULATE WITH INDEX URL
        $html .= "</div>";
        $html .= "<h3 style='text-align: center'>" . utf8_decode("Redefinição de senha") . "</h3>";
        $html .= "<hr>";
        $html .= "<p style='text-align: center'>" . utf8_decode("Digite o código abaixo no ".PORTAL_NAME.":") . "</p>";
        $html .= "<h2 style='text-align: center'><strong>$token</strong></h2>";
        $html .= "<hr>";
        $html .= "<p style='text-align: center'><strong>" . utf8_decode("ATENÇÃO:") . "</strong> " . utf8_decode("Código válido") . " por 2 horas.</p>";
        $html .= "</div>";
        
        return $html;
    }

	public function delUser($id)
	{
		$sql = $this->db->prepare("UPDATE users SET deleted_at = :deleted_at WHERE id = :id");
		$sql->bindValue(":deleted_at", date("Y-m-d H:i:s"));
		$sql->bindValue(":id", $id);
		$sql->execute();

		if (!$sql){
			return ["success" => false, "msg" => "Ocorreu um erro ao remover o usuário, tente novamente."];
		}

		return ["success" => true, "msg" => "Usuário deletado com sucesso!"];
	}

	public function getUser($id)
	{
		$array = array();
		$sql = $this->db->prepare("SELECT * FROM users WHERE id = {$id} AND deleted_at is null");
		$sql->execute();
		$array = $sql->fetch();
		return $array;
	}

	public function canAltDados($post){

		$addRemove = $post['addRemove'];
		$users = $post['userIDs'];

		try{
			
			foreach($users as $user){

				//checa se já tem permissão
				$sql = $this->db->prepare("SELECT * FROM permissionsMenu WHERE userID = $user AND menuID = 41 LIMIT 1");
				$sql->execute();

				if($sql->rowCount() == 1) {

					$deleted_at = ($addRemove == 1) ? 'null' : 'NOW()';
					$idMenu = $sql->fetch();
					$idMenu = $idMenu['id'];
					$sql = $this->db->prepare("UPDATE permissionsMenu SET deleted_at = $deleted_at where id = $idMenu");
					$sql->execute();
		
				}else{
		
					if($addRemove == 1){
		
						$sql = $this->db->prepare("INSERT INTO permissionsMenu SET menuID = 41, userID = $user, created_at = NOW()");
						$sql->execute();
		
					}
		
				}
	
			}

			return true;

		}catch (\Throwable $th) {

			return false;
			
		}

	}

	public function canAltDadosSingle($post){

		$addRemove = $post['addRemove'];
		$userID = $post['userID'];

		//checa se já tem permissão
		$sql = $this->db->prepare("SELECT * FROM permissionsMenu WHERE userID = $userID AND menuID = 41 LIMIT 1");
		$sql->execute();

		if($sql->rowCount() == 1) {

			$deleted_at = ($addRemove == 1) ? 'null' : 'NOW()';
			$idMenu = $sql->fetch();
			$idMenu = $idMenu['id'];
			$sql = $this->db->prepare("UPDATE permissionsMenu SET deleted_at = $deleted_at where id = $idMenu");
			$sql->execute();

		}else{

			if($addRemove == 1){

				$sql = $this->db->prepare("INSERT INTO permissionsMenu SET menuID = 41, userID = $userID, created_at = NOW()");
				$sql->execute();

			}else{

				$sql = true;

			}

		}

		if (!$sql){

			return false;
		}

		return true;
		
	}

	public function allMenus($concat = false)
	{
		// Puxa Todos os Menus
		if ($concat)
		{
			$sql = "SELECT id, link, icon,
					IF (fatherID > 0, 
					( SELECT CONCAT( `m`.`description`, ' > ', `Menu`.`description`) FROM Menu m where m.id = Menu.fatherID LIMIT 1),
					`description`) AS descrip
					FROM Menu
					WHERE deleted_at IS NULL
					ORDER BY `descrip`, `fatherID`, `order`
					";

			$sql = $this->db->prepare($sql);
			$sql->execute();
			$array = $sql->fetchAll();

			return $array;

		} else {
			// Para tela do Menu
			$sql = "SELECT *
					FROM Menu
					WHERE deleted_at IS NULL AND `fatherID` IS NULL
					ORDER BY `order`";

			$sql = $this->db->prepare($sql);
			$sql->execute();
			$array = $sql->fetchAll();

			$menuSub = array();

			foreach($array AS $k => $arr)
			{
				$menuSub[$k]['id'] 	 = $arr['id'];
				$menuSub[$k]['name'] = $arr['description'];
				$menuSub[$k]['icon'] = $arr['icon'];
				$menuSub[$k]['link'] = $arr['link'];
				$menuSub[$k]['sub']  = $this->getSubsMenus($arr['id']);

			}
			
			return $menuSub;
		}

	}

	public function allMenusFretamento()
	{
		// Para tela do Menu
		if($_SESSION['cType'] == 2)
		{
			$wi = "AND id IN (1, 23, 24, 26, 27, 28, 29, 30, 31, 32, 33)";
			$ni = " AND id NOT IN (9)";
		} else {
			$wi = "AND id IN (1, 23, 24, 26, 27, 28, 29)";
			$ni = " AND id NOT IN (6, 9, 24, 28, 29, 30, 31, 32, 33)";
		}

		$sql = "SELECT *
				FROM Menu
				WHERE deleted_at IS NULL AND `fatherID` IS NULL
				{$wi}
				ORDER BY `order`";

		$sql = $this->db->prepare($sql);
		$sql->execute();
		$array = $sql->fetchAll();

		$menuSub = array();

		foreach($array AS $k => $arr)
		{
			$menuSub[$k]['id'] 	 = $arr['id'];
			$menuSub[$k]['name'] = $arr['description'];
			$menuSub[$k]['icon'] = $arr['icon'];
			$menuSub[$k]['link'] = $arr['link'];
			$menuSub[$k]['sub']  = $this->getSubsMenus($arr['id'], $ni);
		}

		return $menuSub;
	}

	private function getSubsMenus($id, $ni = "")
	{
		$sql = "SELECT *
					FROM Menu
					WHERE deleted_at IS NULL AND `fatherID` = {$id} {$ni}
					ORDER BY `order`";

		$sql = $this->db->prepare($sql);

		$sql->execute();

		$array = $sql->fetchAll();

		return $array;
	}

	public function userMenus($idUser, $tp)
	{
		$sql = "SELECT menuID, fatherID, link
				FROM permissionsMenu
				INNER JOIN Menu ON Menu.id = permissionsMenu.menuID
				WHERE permissionsMenu.deleted_at IS NULL
				AND userID = {$idUser}";

		$sql = $this->db->prepare($sql);
		$sql->execute();
		$array = $sql->fetchAll();

		$arrUser = array();

		if( $tp == 1)
		{
			foreach($array as $sp)
			{
				$arrUser[] = $sp['menuID'];

				if( isset( $sp['fatherID'] ) && $sp['fatherID'] != null ){
					$arrUser[] = $sp['fatherID'];
				}

			}

			if(count($arrUser) == 0 && $_SESSION['cType'] != 3){
				// Para os cadastrados antigos
				$sql = $this->db->prepare("INSERT INTO permissionsMenu (menuID, userID, created_at) VALUES (1, {$idUser}, NOW()), (3, {$idUser}, NOW()), (4, {$idUser}, NOW()), (5, {$idUser}, NOW()), (6, {$idUser}, NOW()), (7, {$idUser}, NOW()) ");
				$sql->execute();
	
				$arrUser = array(1,2,3,4,5,6,7);
			}
			
		} else {

			foreach($array as $sp)
			{
				// $arrUser[] = $sp['menuID'];
				$arrUser[] = $sp['link'];
				// if( isset( $sp['fatherID'] ) && $sp['fatherID'] != null ){
				// 	$arrUser[] = $sp['link'];
				// }

			}

			if(count($arrUser) == 0  && $_SESSION['cType'] != 3){
				// Para os cadastrados antigos
				$sql = $this->db->prepare("INSERT INTO permissionsMenu (menuID, userID, created_at) VALUES (1, {$idUser}, NOW()), (3, {$idUser}, NOW()), (4, {$idUser}, NOW()), (5, {$idUser}, NOW()), (6, {$idUser}, NOW()), (7, {$idUser}, NOW()) ");
				$sql->execute();
	
				$arrUser = array("/", "/relatorioAnalitico/", "/relatorioConsolidado/", "/relatorioCartaoUtilizacao/", "/relatorioListagem/", "/relatorioRastreamento/");
			}

		}

		return $arrUser;
	}


	public function menusMonitoramento(){

		$menusMonitoramento = '34,36,44';
	
		$array = array();
	
		$sql = "SELECT 
					m.icon, 
					CONCAT(IF(mp.description IS NOT NULL, CONCAT(mp.description, ' - '), ''), m.description) AS description,
					m.link 
				FROM 
					Menu AS m
				LEFT JOIN 
					Menu AS mp ON m.fatherID = mp.id
				WHERE 
					m.id IN ($menusMonitoramento) AND m.deleted_at IS NULL
				ORDER BY 
					m.description, m.fatherID, m.order";
	
		$sql = $this->db->prepare($sql);
		$sql->execute();
		$array = $sql->fetchAll();
	
		$userMenuLink = array();
	
		foreach($array as $mL){
			array_push($userMenuLink, $mL['link']);
		}
	
		$_SESSION['userMenuLink'] = $userMenuLink;
	
		return $array;
	}
	

	/****** PARA TRATAMENTOS DO PAX *****/
	

	public function itinerarioUserUser($sentido = 0)
	{
		$array = array();

		if(isset($_SESSION['cType']) && $_SESSION['cType'] == 1){

			$sql = $this->db->prepare("SELECT * FROM acesso_grupos ORDER BY NOME");
			$sql->execute();
			$array = $sql->fetchAll();

		} else {
			$sql = $this->db->prepare("SELECT * FROM acesso_grupos 
				WHERE id IN (
                SELECT grupo_id FROM usuario_grupos WHERE usuario_id = {$_SESSION['cLogin']} AND deleted_at is null
            ) ORDER BY NOME");
			$sql->execute();
			$array = $sql->fetchAll();
		}

		return $array;
	}



	public function pontosEmbarque()
	{
		$array = array();

		if(isset($_SESSION['cType']) && $_SESSION['cType'] == 1){

			$sql = $this->db->prepare("SELECT * FROM acesso_grupos ORDER BY NOME");
			$sql->execute();
			$array = $sql->fetchAll();

		} else {
			$sql = $this->db->prepare("SELECT * FROM acesso_grupos 
				WHERE id IN (
                SELECT grupo_id FROM usuario_grupos WHERE usuario_id = {$_SESSION['cLogin']} AND deleted_at is null
            ) ORDER BY NOME");
			$sql->execute();
			$array = $sql->fetchAll();
		}

		return $array;
	}	

}
?>