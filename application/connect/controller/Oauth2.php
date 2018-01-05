<?php
namespace app\connect\controller;
use app\connect\oauth\Oauth;

class Oauth2 {

	protected $oauth2;

	public function __construct() {
		$this->oauth2 = oauth_server();
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
		if (!isset($_POST['grant_type'])) {
			return json(['code' => false, 'msg' => 'grant_type没有填写']);
		}
		if (!isset($_POST['code'])) {
			return json(['code' => false, 'msg' => 'code不合法']);
		}
		$this->oauth2->handleTokenRequest(\OAuth2\Request::createFromGlobals())->send();
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
		$this->oauth2 = oauth_server([]);
		if (!isset($_POST['grant_type'])) {
			return json(['code' => false, 'msg' => 'grant_type没有填写']);
		}
		if (!isset($_POST['refresh_token'])) {
			return json(['code' => false, 'msg' => 'refresh_token不合法']);
		}
		$body = $this->oauth2->handleTokenRequest($request)->getResponseBody();
		$dataData = json_decode($body, true);
		if (isset($dataData['access_token'])) {
			!$new_refresh and $dataData['refresh_token'] = $data['refresh_token'];
			return ['code' => true, 'message' => '刷新成功', 'data' => $dataData];
		}
		return ['code' => false, 'message' => '刷新失败', 'data' => $dataData];
	}
	/**
	 * [user 用密码登录]
	 * @Author   kong|<iwhero@yeah.com>
	 * @DateTime 2018-01-02
	 * @return   [type]                 [description]
	 */
	public function user() {
		$users[$_POST['username']] = array('username' => $_POST['username'], 'password' => $_POST['password']);
		$this->oauth2 = oauth_server($users);
		$this->oauth2->handleTokenRequest(\OAuth2\Request::createFromGlobals())->send();
	}
	/**
	 * [testUser description]
	 * @Author   kong|<iwhero@yeah.com>
	 * @DateTime 2018-01-04
	 * @return   [type]                 [description]
	 */
	public function testUser() {
		$data = array(
			'grant_type' => 'password', // valid grant type
			'client_id' => 'testclient', // valid client id
			'client_secret' => 'testpass', // valid client secret
			'username' => '1', // valid user_id
			'password' => 'brent123', // valid 任意密码
		);
		$oauth = Oauth::getInit();
		var_dump($oauth->userOauthPassword($data));
	}
	/**
	 * [testResource description]
	 * @Author   kong|<iwhero@yeah.com>
	 * @DateTime 2018-01-04
	 * @return   [type]                 [description]
	 */
	public function testResource() {
		$data = array(
			'access_token' => 'a1970ada829a648e390e0aec1bf64c546e035441', // valid access_token
			'user_id' => 1,
		);
		$oauth = Oauth::getInit();
		var_dump($oauth->oauthResource($data));
	}
	/**
	 * [testRefresh description]
	 * @Author   kong|<iwhero@yeah.com>
	 * @DateTime 2018-01-04
	 * @return   [type]                 [description]
	 */
	public function testRefresh() {
		$data = array(
			'grant_type' => 'refresh_token', // valid grant type
			'client_id' => 'testclient', // valid client id
			'client_secret' => 'testpass', // valid client secret
			'refresh_token' => '38e0ef880235654b87588cb03c19c49f435bb460', // valid refresh_token
		);
		$oauth = Oauth::getInit();
		var_dump($oauth->oauthRefresh($data, true));
	}
}
