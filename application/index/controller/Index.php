<?php
namespace app\index\controller;
use Requests\Requests;

class Index {
	public function index() {
		$inputUri = 'http://oauth.4kb.cn/?a=b';
		$registered_uri = 'k';
		var_dump(strpos($inputUri, $registered_uri));
	}
	public function test() {
		Requests::register_autoloader();
		$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/connect/Oauth2/token';
		// Requests::register_autoloader();
		$headers = array('Accept' => 'application/json');
		$options = array('auth' => array('testclient', 'testpass'));
		$data = array('grant_type' => "authorization_code", "code" => "e0d591205f1fd90b5649c1cf88c00579a18c6863");
		$request = Requests::post('http://httpbin.org/post', $headers, $data, $options);

		// Check what we received
		var_dump($request);
	}
}
