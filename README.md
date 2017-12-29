ThinkPHP 5.0
===============

section-4.1.3  OAuth2\GrantType\AuthorizationCode 的validateRequest验证方法redirect_uri的验证
// $response->setError(400, 'redirect_uri_mismatch', "The redirect URI is missing or do not match", "#section-4.1.3");
// return true;
return true; // 放回的url不做判断
section-3.1.2 OAuth2\Controller\AuthorizationCode 的validateRedirectUri验证方法

// the input uri is validated against the registered uri using exact match
// if (strcmp($inputUri, $registered_uri) === 0) {
//  return true;
// }
if (strpos($inputUri, $registered_uri) !== false) {
	return true;
} else {
	return false;
}   
有这两个问题