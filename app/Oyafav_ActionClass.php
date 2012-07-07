<?php
// vim: foldmethod=marker
/**
 *  Torabot_ActionClass.php
 *
 *  @author     {$author}
 *  @package    Torabot
 *  @version    $Id$
 */
require_once('HTTP/OAuth/Consumer.php');
require_once('define.php');
require_once('DB.php');
require_once('DB.php');
require_once(dirname(__file__) . '/TwitterApiAccess.php');

// {{{ Torabot_ActionClass
/**
 *  action execution class
 *
 *  @author     {$author}
 *  @package    Torabot
 *  @access     public
 */
class Oyafav_ActionClass extends Ethna_ActionClass
{
	//本当はメンバ関数で管理しなくていいのも多い
	var $oauth;
	var $http_request;
	var $consumer_request;
	var $oauth_state;
	var $request_token;
	var $request_token_secret;
	var $access_token;
	var $access_token_secret;
	var $_dsn = array(
		'phptype'  => 'mysqli',
		'username' => 'root',
		'password' => 'mylocal',
		'hostspec' => 'localhost',
		'database' => 'oyafav'
	);
    /**
     *  authenticate before executing action.
     *
     *  @access public
     *  @return string  Forward name.
     *                  (null if no errors. false if we have something wrong.)
     */
    function authenticate()
    {
        return parent::authenticate();
    }

    /**
     *  Preparation for executing action. (Form input check, etc.)
     *
     *  @access public
     *  @return string  Forward name.
     *                  (null if no errors. false if we have something wrong.)
     */
    function prepare()
    {
        return parent::prepare();
    }

    /**
     *  execute action.
     *
     *  @access public
     *  @return string  Forward name.
     *                  (we does not forward if returns null.)
     */
    function perform()
    {
        return parent::perform();
    }
	function setOAuth () {
		if (empty($this->oauth)) {
			$this->oauth = new HTTP_OAuth_Consumer(CON_KEY, CON_SEC);
			$this->http_request = new HTTP_Request2();
			$this->http_request->setConfig('ssl_verify_peer', false);
			$this->consumer_request = new HTTP_OAuth_Consumer_Request;
			$this->consumer_request->accept($this->http_request);
			$this->oauth->accept($this->consumer_request);
		}
	}
	function jumpToPage ($action) {
		$action_url = SITE_URL .'?action_'.$action.'=true';
		header('Location:'.$action_url);
	}
	function runQuery ($query, $mode = null) {
		$db = DB::connect($this->_dsn);
		if (PEAR::isError($db)) {
			die('connect failed');
		}
		$res = $db->query($query);
		if ($ret = PEAR::isError($query)) {
			die('query failed');
		}
		if (!is_null($mode) && $mode == 'fetch_assoc') {
			$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
			if (PEAR::isError($row)) {
				die('fetch assoc failed');
			}
			$db->disconnect();
			return $row;
		} else if (!is_null($mode) && $mode == 'fetch_object') {
			$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
			if (PEAR::isError($row)) {
				die('fetch object failed');
			}
			$db->disconnect();
			return $row;
		}
		$db->disconnect();
		return $res;
	}

	function insertUser () {
		if (empty($this->access_token)) {
			return null;
		}
		$query = 'INSERT INTO user VALUES (null,"'.$this->access_token.'","'.$this->access_token_secret.'")';
		$this->runQuery($query);
	}
}
// }}}

?>
