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
	protected function oauth_server() {
		\OAuth2\Autoloader::register();
		// $dsn is the Data Source Name for your database, for exmaple "mysql:dbname=my_oauth2_db;host=localhost"
		$storage = new \OAuth2\Storage\Pdo(config('database.oauth'));

		// Pass a storage object or array of storage objects to the OAuth2 server class
		$server = new \OAuth2\Server($storage);

		// Add the "Client Credentials" grant type (it is the simplest of the grant types)
		$server->addGrantType(new \OAuth2\GrantType\ClientCredentials($storage));

		// Add the "Authorization Code" grant type (this is where the oauth magic happens)
		$server->addGrantType(new \OAuth2\GrantType\AuthorizationCode($storage));
		return $server;
	}
	public function index() {
		$url = 'http://oauth.4kb.cn/?a=b';
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
		if (isset($_GET['response_type']) && $_GET['response_type'] != 'code') {
			return json(['code' => 20001, 'msg' => '不支持的RESPONSE_TYPE类型']);
		}
		if (!isset($_GET['client_id'])) {
			return json(['code' => 20002, 'msg' => 'client_id没有填写']);
		}
		if (!isset($_GET['redirect_uri'])) {
			return json(['code' => 20003, 'msg' => 'redirect_uri错误']);
		}
		$request = \OAuth2\Request::createFromGlobals();
		$response = new \OAuth2\Response();
		$server = $this->oauth2;
		// validate the authorize request
		if (!$server->validateAuthorizeRequest($request, $response)) {
			$response->send();
			die();
			// return json(['code' => $response->getResponseBody(), 'msg' => 'client_id不合法']);
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
}
