<?php
namespace app\connect\controller;

class Oauth2 {

	protected $oauth2;

	public function __construct() {
		$this->oauth2 = $this->oauth_server();
	}
	/**
	 * [oauth_server oauth服务初始化]
	 * @Author   kong|<iwhero@yeah.com>
	 * @DateTime 2017-12-28
	 * @return   [type]                 [description]
	 */
	protected function oauth_server($users = array()) {
		\OAuth2\Autoloader::register();
		// $dsn is the Data Source Name for your database, for exmaple "mysql:dbname=my_oauth2_db;host=localhost"
		$storage = new \OAuth2\Storage\Pdo(config('database.oauth'));
		// ===== token刷新 ======
		// Pass a storage object or array of storage objects to the OAuth2 server class
		$server = new \OAuth2\Server($storage, array(
			'always_issue_new_refresh_token' => true,
			'refresh_token_lifetime' => 2419200,
		));
		// create the grant type
		$grantType = new \OAuth2\GrantType\RefreshToken($storage);
		// add the grant type to your OAuth server
		$server->addGrantType($grantType);
		// ========token刷新end==========
		if (count($users) > 0) {
			# code...
			// ========密码登录==========
			// create some users in memory
			// $users = array('bshaffer' => array('password' => 'brent123', 'first_name' => 'Brent', 'last_name' => 'Shaffer'));

			// create a storage object
			$usersInfo = new \OAuth2\Storage\Memory(array('user_credentials' => $users));
			// create the grant type
			$grantTypeUser = new \OAuth2\GrantType\UserCredentials($usersInfo);
			// 存入mysql数据库
			// $storage->setUser($users['bshaffer']['username'], $users['bshaffer']['password']);
			// $grantTypeUser = new \OAuth2\GrantType\UserCredentials($storage);

			// add the grant type to your OAuth server
			$server->addGrantType($grantTypeUser);
			// ========密码登录end==========
		}

		// Add the "Client Credentials" grant type (it is the simplest of the grant types)
		$server->addGrantType(new \OAuth2\GrantType\ClientCredentials($storage));

		// Add the "Authorization Code" grant type (this is where the oauth magic happens)
		$server->addGrantType(new \OAuth2\GrantType\AuthorizationCode($storage));

		return $server;
	}
	public function index() {
		$url = 'http://oauth.4kb.cn/index/index/index';
		$state = substr(md5('testclient' . 'authorize'), 0, 8);
		$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/connect/Oauth2/authorize' . '/?response_type=code&client_id=testclient&state=' . $state . '&redirect_uri=' . urlencode($url);
		header('Location:' . $redirect_uri);
	}
	/**
	 * [authorize oauth授权获取code]
	 * @Author   kong|<iwhero@yeah.com>
	 * @DateTime 2017-12-28
	 * @return   [type]                 [description]
	 */
	public function authorize() {
		$request = \OAuth2\Request::createFromGlobals();
		$response = new \OAuth2\Response();
		$server = $this->oauth2;
		// validate the authorize request
		if (!$server->validateAuthorizeRequest($request, $response)) {
			$response->send();
			die();
		}
		$server->handleAuthorizeRequest($request, $response, true);
		$response->send();
	}
	/**
	 * [token oauth换取access_token]
	 * @Author   kong|<iwhero@yeah.com>
	 * @DateTime 2017-12-28
	 * @return   [type]                 [description]
	 */
	public function token() {
		$server = $this->oauth2;
		if (!isset($_POST['grant_type'])) {
			return json(['code' => 20005, 'msg' => 'grant_type没有填写']);
		}
		if (!isset($_POST['code'])) {
			return json(['code' => 20006, 'msg' => 'code不合法']);
		}
		$server->handleTokenRequest(\OAuth2\Request::createFromGlobals())->send();
	}
	/**
	 * [resource 判断access_token合法]
	 * @Author   kong|<iwhero@yeah.com>
	 * @DateTime 2017-12-29
	 * @return   [type]                 [description]
	 */
	public function resource() {
		$server = $this->oauth2;
		if (!$server->verifyResourceRequest(\OAuth2\Request::createFromGlobals())) {
			$server->getResponse()->send();
			die();
		}
		return json(['success' => true, 'message' => '合法的']);
	}
	/**
	 * [token 刷新access_token]
	 * @Author   kong|<iwhero@yeah.com>
	 * @DateTime 2017-12-28
	 * @return   [type]                 [description]
	 */
	public function refresh() {
		$server = $this->oauth2;
		if (!isset($_POST['grant_type'])) {
			return json(['code' => 20007, 'msg' => 'grant_type没有填写']);
		}
		if (!isset($_POST['refresh_token'])) {
			return json(['code' => 20006, 'msg' => 'refresh_token不合法']);
		}
		$server->handleTokenRequest(\OAuth2\Request::createFromGlobals())->send();
	}
	/**
	 * [user 用密码登录]
	 * @Author   kong|<iwhero@yeah.com>
	 * @DateTime 2018-01-02
	 * @return   [type]                 [description]
	 */
	public function user() {
		$users[$_POST['username']] = array('username' => $_POST['username'], 'password' => $_POST['password']);
		$this->oauth2 = $this->oauth_server($users);
		$server = $this->oauth2;
		$server->handleTokenRequest(\OAuth2\Request::createFromGlobals())->send();
	}
}
