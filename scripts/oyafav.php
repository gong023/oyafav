<?php
define('CON_KEY', 'H9ilxFk9DVHJLxgEvO5Csg');
define('CON_SEC', 'BX7oGxwIZUG5w4ImLZyky7I1ztZreDwv6XWkOJ0mis');
define('CONNECT_URL','localhost');
define('CONNECT_USER','root');
define('CONNECT_PASWD','mylocal');
require_once('DB.php');
require_once(dirname(__file__) . '/TwitterApiAccess.php');
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
$word = array ('添い寝', 'そいね');
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
