<?php
require_once('HTTP/OAuth/Consumer.php');
require_once('twitteroauth.php');
//TODO:registration, callbackを作る
//TODO:singletonをやめる
class OyafavTwitterAccess {
	private static $instance;
	private $oauth;
	private $twitteroauth;

	private function __construct ($con_key, $con_sec, $a_token = null, $a_token_secret = null) {
		//上書きできるようにした
		$this->oauth = new HTTP_OAuth_Consumer($con_key, $con_sec);
		$http_request = new HTTP_Request2();
		$http_request->setConfig('ssl_verify_peer', false);
		$consumer_request = new HTTP_OAuth_Consumer_Request;
		$consumer_request->accept($http_request);
		$this->oauth->accept($consumer_request);

		if ($a_token != null && $a_token_secret != null) {
			$this->oauth->setToken($a_token);
			$this->oauth->setTokenSecret($a_token_secret);
			$this->twitteroauth = new TwitterOAuth($con_key, $con_sec, $a_token, $a_token_secret);
		}
	}

	public function getInstance ($con_key, $con_sec, $a_token = null, $a_token_secret = null) {
		self::$instance = new OyafavTwitterAccess($con_key, $con_sec, $a_token, $a_token_secret);
		return self::$instance;
	}

	public function postApi ($api_url, $params) {
		if (!is_array($params) || empty($this->oauth)) {
			return false;
		}
		return $this->oauth->sendRequest($api_url, $params, 'POST');
	}

	public function getApi ($api_url, $params) {
		//Consumerの方だとなぜかGET系が使えないので
		//基本的にjsonでいいや
		if (!is_array($params) || empty($this->twitteroauth)) {
			return false;
		}
		$this->twitteroauth->format = "json";
		$req = $this->twitteroauth->OAuthRequest($api_url, 'GET', $params);
		return json_decode($req, true);
	}

	public function firstAccess ($callback) {
		//初回呼び出し時
		$this->oauth->getRequestToken('https://twitter.com/oauth/request_token', $callback);
		return $params = array (
			'request_token' => $this->oauth->getToken(),
			'request_token_secret' => $this->oauth->getTokenSecret(),
			'request_link' => $this->oauth->getAuthorizeURL('https://twitter.com/oauth/authorize')
		);
	}

	public function callback ($r_token, $r_token_secret, $oauth_verifier) {
		//コールバック時
		$this->oauth->setToken($r_token);
		$this->oauth->setTokenSecret($r_token_secret);
		$this->oauth->getAccessToken('https://twitter.com/oauth/access_token', $oauth_verifier);
		return $params = array (
			'a_token' => $this->oauth->getToken(),
			'a_token_secret' => $this->oauth->getTokenSecret(),
		);
	}

	public function createFavorites ($id_list, $name) {
		$api_url = 'http://api.twitter.com/1/favorites/create/';
//		$this->twitteroauth->format = "json";
		$method = "POST";
		if (is_array($id_list)) {
			foreach ($id_list as $id) {
				if ($id == $name['id']) {continue;}
				$this->twitteroauth->OAuthRequest($api_url . $id . '.json', $method, array());
			}
		} else {
			$this->twitteroauth->OAuthRequest($api_url . $id_list . '.json', $method, array());
		}
	}
}
