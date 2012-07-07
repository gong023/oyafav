<?php
/**
 *  Callback.php
 *
 *  @author     {$author}
 *  @package    Torabot
 *  @version    $Id$
 */

/**
 *  callback Form implementation.
 *
 *  @author     {$author}
 *  @access     public
 *  @package    Torabot
 */
class Oyafav_Form_Callback extends Oyafav_ActionForm
{
	/**
	 *  @access private
	 *  @var	array   form definition.
	 */
	var $form = array(
			'oauth_token' => array(
				'type'	=> VAR_TYPE_TEXT,	// Input type
				'name'		=> 'token',		// Display name
				'required'	=> true,			// Required Option(true/false)
			),
			'oauth_verifier' => array(
				'type'		=> VAR_TYPE_TEXT,	// Input type
				'name'		=> 'verifier',		// Display name
				'required'	=> true,			// Required Option(true/false)
			),
	);

	/**
	 *  Form input value convert filter : sample
	 *
	 *  @access protected
	 *  @param  mixed   $value  Form Input Value
	 *  @return mixed		   Converted result.
	 */
}

/**
 *  callback action implementation.
 *
 *  @author	 {$author}
 *  @access	 public
 *  @package	Torabot
 */
class Oyafav_Action_Callback extends Oyafav_ActionClass
{
	/**
	 *  preprocess of callback Action.
	 *
	 *  @access public
	 *  @return string	forward name(null: success.
	 *								false: in case you want to exit.)
	 */
	function prepare()
	{
		if ($this->af->validate() > 0) {
			$this->af->setApp('error', 'sorry,callback page error');
			return 'error';
		}
		$this->setOAuth();
		if (empty($this->access_token) || empty($this->access_token_secret)) {
			$this->oauth->setToken($_SESSION['request_token']);
			$this->oauth->setTokenSecret($_SESSION['request_token_secret']);
			$oauth_verifier = $this->af->get('oauth_verifier');
			$this->oauth->getAccessToken('https://twitter.com/oauth/access_token', $oauth_verifier);
			$this->access_token = $this->oauth->getToken();
			$this->access_token_secret = $this->oauth->getTokenSecret();
			$this->oauth->setToken($this->access_token);
			$this->oauth->setTokenSecret($this->access_token_secret);
			//このあとDB追加処理
			$ret = $this->insertUser();
			if (PEAR::isError($ret)) {
				$this->af->setApp('message', '既に登録されているかも');
				return 'callback';
			}
		}
		return null;
	}

	/**
	 *  callback action implementation.
	 *
	 *  @access public
	 *  @return string  forward name.
	 */
	function perform()
	{
		$this->af->setApp('message', '登録されました');
		$tweet = "添い寝favに登録しました。 http://gong023.com/oyafav/www/?action_index=true";
		//TODO:完成したら外す
		$this->oauth->sendRequest('https://twitter.com/statuses/update.xml', array('status' => $tweet), 'POST');
		return 'callback';
	}
}

?>
