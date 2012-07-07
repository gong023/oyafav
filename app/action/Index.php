<?php
/**
 *  Index.php
 *
 *  @author	{$author}
 *  @package   Torabot
 *  @version   $Id$
 */

/**
 *  Index form implementation
 *
 *  @author	{$author}
 *  @access	public
 *  @package   Torabot
 */

class Oyafav_Form_Index extends Oyafav_ActionForm
{
	/**
	 *  @access   private
	 *  @var	  array   form definition.
	 */
	var $form = array(
		'test' => array(
			'type'		=> VAR_TYPE_TEXT,	// Input type
			'name'		=> 'test',		// Display name
			'required'	=> false,			// Required Option(true/false)
			),
		'oauth_token' => array(
			'type'		=> VAR_TYPE_TEXT,	// Input type
			'name'		=> 'oauth_token',		// Display name
			'required'	=> false,			// Required Option(true/false)
			),
		'cook' => array(
			'type'		=> VAR_TYPE_TEXT,	// Input type
			'name'		=> 'test',		// Display name
			'required'	=> false,			// Required Option(true/false)
			),
	);
}

/**
 *  Index action implementation.
 *
 *  @author	 {$author}
 *  @access	 public
 *  @package	Torabot
 */
class Oyafav_Action_Index extends Oyafav_ActionClass
{
	/**
	 *  preprocess Index action.
	 *
	 *  @access	public
	 *  @return	string  Forward name (null if no errors.)
	 */
	function prepare()
	{
		if ($this->af->validate() > 0) {
			$this->af->setApp('error', 'sorry,top page error');
			return 'error';
		}
		session_start();
		if ($this->af->get('test') == 'clear') {
			session_destroy();
			session_start();
		}
		$this->setOAuth();
		$oauth_token = $this->af->get('oauth_token');
		//callbackしたのになぜかこっち来ちゃった時
		if ($oauth_token && $this->oauth_state === 'start') {
			$this->jumpToPage('callback');
		}
		$test = $this->af->get('cook');
		if (isset($test) && $test == 'pad') {
			$this->forceRun();
		}
		return null;
	}

	/**
	 *  Index action implementation.
	 *
	 *  @access	public
	 *  @return	string  Forward Name.
	 */
	function perform()
	{
		$count_query = 'SELECT count(*) FROM user';
		$count_user = $this->runQuery($count_query, 'fetch_assoc');
		$this->af->setApp('count_user', $count_user['count(*)']);
		// -- 初回呼び出し時
		$this->oauth->getRequestToken('https://twitter.com/oauth/request_token', CALLBACK);
		$_SESSION['request_token'] = $this->oauth->getToken();
		$_SESSION['request_token_secret'] = $this->oauth->getTokenSecret();
		$this->oauth_state = "start";
		/* authorization URL を取得 */
		$request_link = $this->oauth->getAuthorizeURL('https://twitter.com/oauth/authorize');
		/* authorization URLのリンクを作成 */
		$this->af->setApp('request_link', $request_link);
		return 'index';
	}

	private function forceRun () {
		define('CONNECT_URL','localhost');
		define('CONNECT_USER','root');
		define('CONNECT_PASWD','mylocal');
		$api_url = array (
				//ユーザーのタイムラインを出す
				'st_usertimeline' => 'http://api.twitter.com/1/statuses/user_timeline.json',
				//ログイン中のユーザー情報
				'account_credit' => 'https://api.twitter.com/1/account/verify_credentials.json',
				//ふぁぼる
				'fav_create' => 'http://api.twitter.com/1/favorites/create/',
				);
		$_dsn = array(
				'phptype'  => 'mysqli',
				'username' => 'root',
				'password' => 'mylocal',
				'hostspec' => 'localhost',
				'database' => 'oyafav'
				);
		$db = DB::connect($_dsn);
		$query = "select * from user";
		$res = $db->query($query);
		$oyafav_user = array();
		while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
			if (PEAR::isError($row)) {
				error_log('torabot DB error');
			}
			$oyafav_user[] = $row;
		}
		$tweets = array();
		$oyasumi_member = array();
		$word = array ('添い寝', 'そいね', 'おやすみ');
		foreach ($oyafav_user as $user) {
			$oauth = OyafavTwitterAccess::getInstance(CON_KEY, CON_SEC, $user['token'], $user['token_secret']);
			$tweets = $oauth->getApi($api_url['st_usertimeline'], array('count' => 10));
			foreach ($tweets as $tweet) {
				foreach ($word as $w) {
					if (preg_match('/' . $w . '/', $tweet['text'])) {
						$oyasumi_member[] = $tweet['id'];
					}
				}
			}
		}
		foreach ($oyafav_user as $user) {
			$oauth = OyafavTwitterAccess::getInstance(CON_KEY, CON_SEC, $user['token'], $user['token_secret']);
			$name = $oauth->getApi($api_url['account_credit'], array());
			$oauth->createFavorites($oyasumi_member, $name);
		}
	}
}

?>
