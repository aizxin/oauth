<?php
namespace app\index\controller;

class Index {
	public function index() {
		var_dump(1514516192 < 1514517406);
// 		redirect_uriredirect_uri{"authorization_code":"d725c457915fcddfccdb02c97dde14147c8404dc","client_id":"testclient","user_id":null,"redirect_uri":"http:\/\/oauth.4kb.cn\/index\/index\/index","expires":1514516192,"scope":null,"id_token":null}
		// [ info ] redirect_uriredirect_uri
		// set_time_limit(0);
		// $curl = new Curl();
		// $curl->setBasicAuthentication('testclient', 'testpass');
		// $curl->setHeader('X-Requested-With', 'XMLHttpRequest');
		// $url = 'http://oauth.4kb.cn/connect/Oauth2/token';
		// $curl->post($url, array("grant_type" => "authorization_code", "code" => $_GET['code']));
		// if ($curl->error) {
		// 	echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
		// } else {
		// 	echo 'Response:' . "\n";
		// 	var_dump($curl->response);
		// }
		var_dump($_GET['code']);
		// $client = new \GuzzleHttp\Client();
		// $res = $client->request('POST', 'http://oauth.4kb.cn/connect/Oauth2/token', ['auth' => ['testclient', 'testpass'], 'form_params' => ["grant_type" => "authorization_code", "code" => $_GET['code']]]);
		// $res = $client->request('GET', 'https://baidu.com');
		// echo $res->getStatusCode();
		// // 200
		// echo $res->getHeaderLine('content-type');
		// // 'application/json; charset=utf8'
		// echo $res->getBody();
	}
}
