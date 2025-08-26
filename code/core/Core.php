<?php
class Core 
{

	public function run() 
	{

		$url = '/';
		if(isset($_GET['url'])) {
			$url .= $_GET['url'];
		}

		$params = array();

		if(!empty($url) && $url != '/') {
			$url = explode('/', $url);
			array_shift($url);

			if (strtolower($url[0]) == "api")
				$url[0] = "Api";

			$currentController = $url[0].'Controller';
			array_shift($url);

			if(isset($url[0]) && !empty($url[0])) {
				$currentAction = $url[0];
				array_shift($url);
			} else {
				$currentAction = 'index';
			}

			if(count($url) > 0) {
				$params = $url;
			}

			unset($_SESSION['forbidden']);

		} else {
			$currentController = 'homeController';
			$currentAction = 'index';
		}

		if(!file_exists('controllers/'.$currentController.'.php') || !method_exists($currentController, $currentAction)) {
			$currentController = 'homeController';
			$currentAction = 'index';
			
			$_SESSION['forbidden'] = [
				"code" => "404",
				"msg" => "Página não encontrada.",
				"showLogin" => true
			];
		}

		$c = new $currentController();

		call_user_func_array(array($c, $currentAction), $params);
		
	}

}