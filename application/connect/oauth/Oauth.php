<?php

namespace app\connect\oauth;

class Oauth {
	/**
	 *  [$_dom 单例]
	 *  @var null
	 */
	private static $_oauth = null;
	public $storage, $server;

	public function __construct() {
		\OAuth2\Autoloader::register();
		// $dsn is the Data Source Name for your database, for exmaple "mysql:dbname=my_oauth2_db;host=localhost"
		$this->storage = new \OAuth2\Storage\Pdo(config('database.oauth'));
		// Pass a storage object or array of storage objects to the OAuth2 server class
		$this->server = new \OAuth2\Server($this->storage, array(
			'always_issue_new_refresh_token' => true,
			'refresh_token_lifetime' => 2419200, // 28天
			'access_lifetime' => 7200, //2小时
		));
		// Add the "Client Credentials" grant type (it is the simplest of the grant types)
		$this->server->addGrantType(new \OAuth2\GrantType\ClientCredentials($this->storage));

		// Add the "Authorization Code" grant type (this is where the oauth magic happens)
		$this->server->addGrantType(new \OAuth2\GrantType\AuthorizationCode($this->storage));
	}
	/**
	 *  [getInit 单例的初始化]
	 *  @author Sow
	 *  @DateTime 2017-06-17T15:58:13+0800
	 *  @return   [type]                   [description]
	 */
	public static function getInit() {
		if (self::$_oauth === null) {
			self::$_oauth = new self();
		}
		return self::$_oauth;
	}
	/**
	 * [userOauthPassword oauth密码认证]
	 * @Author   kong|<iwhero@yeah.com>
	 * @DateTime 2018-01-04
	 * @param    [type]                 $data [description]
	 * @return   [type]                       [description]
	 */
	public function userOauthPassword(array $data = array()) {
		$request = \app\connect\oauth\RequestOauth::createPost($data);
		$users[$data['username']] = array('username' => $data['username'], 'password' => $data['password']);
		# code...
		// ========密码登录==========
		// create some users in memory
		// $users = array('bshaffer' => array('password' => 'brent123', 'first_name' => 'Brent', 'last_name' => 'Shaffer'));
		$grantTypeUser = new \OAuth2\GrantType\UserCredentials(new \OAuth2\Storage\Memory(array('user_credentials' => $users)));
		// 存入mysql数据库
		// $this->storage->setUser($users['bshaffer']['username'], $users['bshaffer']['password']);
		// $grantTypeUser = new \OAuth2\GrantType\UserCredentials($this->storage);

		// add the grant type to your OAuth server
		$this->server->addGrantType($grantTypeUser);
		// ========密码登录end==========
		$body = $this->server->handleTokenRequest($request)->getResponseBody();
		$dataData = json_decode($body, true);
		if (isset($dataData['access_token'])) {
			return ['code' => true, 'message' => '获取access_token成功', 'data' => $dataData];
		}
		return ['code' => false, 'message' => '获取access_token失败', 'data' => $dataData];
	}
	/**
	 * [oauthRefresh token刷新]
	 * @Author   kong|<iwhero@yeah.com>
	 * @DateTime 2018-01-04
	 * @return   [type]                 [description]
	 */
	public function oauthRefresh($data, $new_refresh = false) {
		$request = \app\connect\oauth\RequestOauth::createPost($data);
		// ===== token刷新 ======
		// add the grant type to your OAuth server
		$this->server->addGrantType(new \OAuth2\GrantType\RefreshToken($this->storage, array(
			'always_issue_new_refresh_token' => $new_refresh,
			'unset_refresh_token_after_use' => $new_refresh,
		)));
		$body = $this->server->handleTokenRequest($request)->getResponseBody();
		$dataData = json_decode($body, true);
		if (isset($dataData['access_token'])) {
			!$new_refresh and $dataData['refresh_token'] = $data['refresh_token'];
			return ['code' => true, 'message' => '刷新成功', 'data' => $dataData];
		}
		return ['code' => false, 'message' => '刷新失败', 'data' => $dataData];
	}
	/**
	 * [oauthResource 判断access_token合法]
	 * @Author   kong|<iwhero@yeah.com>
	 * @DateTime 2018-01-04
	 * @return   [type]                 [description]
	 */
	public function oauthResource($data) {
		$request = \app\connect\oauth\RequestOauth::createPost($data);
		$request->server['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
		if (!$this->server->verifyResourceRequest($request)) {
			$body = $this->server->getResponse()->getResponseBody();
			$dataData = json_decode($body, true);
			return ['code' => false, 'message' => 'access_token不合法', 'data' => $dataData];
		}
		$user = $this->server->getAccessTokenData($request);
		if ($user['user_id'] != $data['user_id']) {
			return ['code' => false, 'message' => 'access_token不合法', 'data' => null];
		}
		return ['code' => true, 'message' => 'access_token合法', 'data' => null];
	}
}