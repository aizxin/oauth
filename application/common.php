<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
// 应用公共文件
if (!function_exists('oauth_server')) {
	/**
	 * [oauth_server oauth服务初始化]
	 * @Author   kong|<iwhero@yeah.com>
	 * @DateTime 2018-01-04
	 * @param    array                  $users [description]
	 * @return   [type]                        [description]
	 */
	function oauth_server($users = array(), $new_refresh = false) {
		\OAuth2\Autoloader::register();
		// $dsn is the Data Source Name for your database, for exmaple "mysql:dbname=my_oauth2_db;host=localhost"
		$storage = new \OAuth2\Storage\Pdo(config('database.oauth'));
		// Pass a storage object or array of storage objects to the OAuth2 server class
		$server = new \OAuth2\Server($storage, array(
			'always_issue_new_refresh_token' => true,
			'refresh_token_lifetime' => 2419200, // 28天
			'access_lifetime' => 7200, //2小时
		));
		// ===== token刷新 ======
		// add the grant type to your OAuth server
		$server->addGrantType(new \OAuth2\GrantType\RefreshToken($storage, array(
			'always_issue_new_refresh_token' => $new_refresh,
			'unset_refresh_token_after_use' => $new_refresh,
		)));
		// ========token刷新end==========
		if (count($users) > 0) {
			# code...
			// ========密码登录==========
			// create some users in memory
			// $users = array('bshaffer' => array('password' => 'brent123', 'first_name' => 'Brent', 'last_name' => 'Shaffer'));
			$grantTypeUser = new \OAuth2\GrantType\UserCredentials(new \OAuth2\Storage\Memory(array('user_credentials' => $users)));
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
}

if (!function_exists('userOauthPassword')) {
	/**
	 * [userOauthPassword oauth密码认证]
	 * @Author   kong|<iwhero@yeah.com>
	 * @DateTime 2018-01-04
	 * @param    [type]                 $data [description]
	 * @return   [type]                       [description]
	 */
	function userOauthPassword($data) {
		$request = \app\connect\oauth\RequestOauth::createPost($data);
		$users[$data['username']] = array('username' => $data['username'], 'password' => $data['password']);
		$body = oauth_server($users)->handleTokenRequest($request)->getResponseBody();
		$dataData = json_decode($body, true);
		if (isset($dataData['access_token'])) {
			return ['code' => true, 'message' => '获取access_token成功', 'data' => $dataData];
		}
		return ['code' => false, 'message' => '获取access_token失败', 'data' => $dataData];
	}
}

if (!function_exists('oauthRefresh')) {
	/**
	 * [oauthRefresh token刷新]
	 * @Author   kong|<iwhero@yeah.com>
	 * @DateTime 2018-01-04
	 * @return   [type]                 [description]
	 */
	function oauthRefresh($data, $new_refresh = false) {
		$request = \app\connect\oauth\RequestOauth::createPost($data);
		$body = oauth_server([], $new_refresh)->handleTokenRequest($request)->getResponseBody();
		$dataData = json_decode($body, true);
		if (isset($dataData['access_token'])) {
			!$new_refresh and $dataData['refresh_token'] = $data['refresh_token'];
			return ['code' => true, 'message' => '刷新成功', 'data' => $dataData];
		}
		return ['code' => false, 'message' => '刷新失败', 'data' => $dataData];
	}
}

if (!function_exists('oauthResource')) {
	/**
	 * [oauthResource 判断access_token合法]
	 * @Author   kong|<iwhero@yeah.com>
	 * @DateTime 2018-01-04
	 * @return   [type]                 [description]
	 */
	function oauthResource($data) {
		$server = oauth_server();
		$request = \app\connect\oauth\RequestOauth::createPost($data);
		$request->server['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
		if (!$server->verifyResourceRequest($request)) {
			$body = $server->getResponse()->getResponseBody();
			$dataData = json_decode($body, true);
			return ['code' => false, 'message' => 'access_token不合法', 'data' => $dataData];
		}
		$user = $server->getAccessTokenData($request);
		if ($user['user_id'] != $data['user_id']) {
			return ['code' => false, 'message' => 'access_token不合法', 'data' => null];
		}
		return ['code' => true, 'message' => 'access_token合法', 'data' => null];
	}
}