<?php
/**
 * ################################################################################
 * MyBackup
 * 
 * Copyright 2017 Eugen Mihailescu <eugenmihailescux@gmail.com>
 * 
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any later
 * version.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * ################################################################################
 * 
 * Short description:
 * URL: http://wpmybackup.mynixworld.info
 * 
 * Git revision information:
 * 
 * @version : 1.0-3 $
 * @commit  : 1b3291b4703ba7104acb73f0a2dc19e3a99f1ac1 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Tue Feb 7 08:55:11 2017 +0100 $
 * @file    : GenericOAuth2Client.php $
 * 
 * @id      : GenericOAuth2Client.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
require_once CURL_PATH . 'CurlWrapper.php';
abstract class GenericOAuth2Client extends AbstractOAuthClient {
private $_curl_wrapper;
private function _isNull($array, $param, $default = null) {
return isset ( $array [$param] ) ? $array [$param] : $default;
}
private function _setIfNotNull($array, $param, $function) {
if (isset ( $array [$param] ))
_call_user_func ( array (
$this,
$function 
), $array [$param] );
}
private function _requestAuthCode ($state = null) {
$this->locationRedirect ( $this->_getURI ( $this->getAPIByServiceType ( 'auth' ), $state ) );
}
private function _isJSON($string) {
json_decode ( $string );
return json_last_error () == JSON_ERROR_NONE;
}
private function _requestToken($refresh_token = false) {
$url = $this->_getURI ( $this->getAPIByServiceType ( 'token' ) );
if (! $refresh_token)
$post_fields = array (
'code' => $this->getAuthCode (),
'redirect_uri' => $this->getRedirectUri (),
'grant_type' => 'authorization_code' 
);
else
$post_fields = array (
'refresh_token' => $this->getRefreshToken (),
'grant_type' => 'refresh_token' 
);
$post_fields = array_merge ( $post_fields, array (
'client_id' => $this->getClientId (),
'client_secret' => $this->getSecret () 
) );
$result = $this->curlPOST ( $url, null, http_build_query ( $post_fields ) );
if ($this->_isJSON ( $result ))
$result = json_decode ( $result, true );
elseif (strpos ( $result, 'access_token' ) >= 0) {
parse_str ( $result, $fields );
$result = array (
'access_token' => $fields ['access_token'],
'expires_in' => $fields ['expires'],
'state' => $_GET [self::PARAM_STATE],
'oauth_client_id' => $this->getClientId (),
'oauth_secret' => $this->getSecret (),
'id_token' => $this->getTokenId (),
'token_type' => $this->getTokenType () 
);
} else
$result = null;
$this->initFromArray ( $result );
return $result;
}
public function isTokenExpired() {
$expire_in = $this->getTokenExpiration ();
$expire_date = $this->getTokenDate ();
if (! empty ( $expire_in ))
$expire_date += $expire_in;
return time () > $expire_date;
}
private function _isSSL() {
$result = false;
if (! empty ( $_SERVER ['HTTPS'] ) && $_SERVER ['HTTPS'] == 'on')
$result = true;
return $result;
}
private function _getServerURI($include_path = true, $include_query = false) {
if (isset ( $_SERVER ['SERVER_NAME'] ))
$url = 'http' . ($this->_isSSL () ? 's' : '') . '://' . urldecode ( @$_SERVER ['SERVER_NAME'] );
else
$url = '';
if ($include_path && ! empty ( $_SERVER ['SCRIPT_NAME'] ))
$url .= urldecode ( realpath(@$_SERVER ['SCRIPT_NAME'] ));
if ($include_query && ! empty ( $_SERVER ['QUERY_STRING'] ))
$url .= '?' . urldecode ( $_SERVER ['QUERY_STRING'] );
return $url;
}
private function _getAccInfoCache() {
if (isset ( $_SESSION ) && isset ( $_SESSION [$this->getOauthToken ()] ))
return $_SESSION [$this->getOauthToken ()];
return false;
}
private function _setAccInfoCache($info) {
isset ( $_SESSION ) && add_session_var ( $this->getOauthToken (), $info );
}
abstract protected function _getURI($function, $state = null);
protected function _getProxyAuthURI($service) {
$proxy_auth = '';
$proxy_auth_params = $this->getProxyAuthParams ();
if (! empty ( $proxy_auth_params ))
$proxy_auth = '&proxy=' . urlencode ( http_build_query ( $proxy_auth_params ) );
$params = '?' . AbstractOAuthClient::PARAM_SERVICE . '=' . urlencode ( $service );
$request_uri = '';
if (isset ( $_SERVER ['SERVER_NAME'] )) {
$request_uri = $this->_getServerURI ( true, false );
$request_uri = '&request_uri=' . urlencode ( $request_uri );
}
return $this->getProxyURI () . $params . $proxy_auth . $request_uri;
}
public function getHeader($function = null) {
$auth = 'Authorization: Bearer ' . $this->getOauthToken ();
$hdr = array (
$auth 
);
return $hdr;
}
protected function _getScopes() {
$scope = $this->_getURI ( $this->getAPIByServiceType ( 'scope' ) );
return empty ( $scope ) ? null : explode ( ',', $scope );
}
public function setCurlOptions($cmd_options) {
$this->_curl_wrapper->setCurlOptions ( $cmd_options );
}
public function getRedirectUri($include_path = true, $include_query = false) {
$url = $this->getProxyURI ();
if (empty ( $url ))
$url = $this->_getServerURI ( $include_path, $include_query );
$url .= ('/' != substr ( $url, - 1 ) ? '/' : ''); 
return $url;
}
public function refreshToken($force_refresh = false) {
if ($this->isTokenExpired () || $force_refresh)
return $this->_requestToken ( true );
else
return false;
}
public function curlPOST($url, $header, $postfields = null, $outfile = null, $infile = null, $method = 'POST', $callback_info = null, $onabort_callback = null) {
$this->_curl_wrapper->onAbortCallback = $onabort_callback;
return $this->_curl_wrapper->curlPOST ( $url, $header, $postfields, $outfile, $infile, $method, $callback_info );
}
abstract public function getAPIByServiceType($api);
abstract public function mergeAPIS($with_apis);
function __construct($key = null, $secret = null) {
if (! extension_loaded ( 'curl' ))
throw new MyException ( sprintf ( _esc ( '%s extension not enabled.' ), 'Curl' ) );
$this->setClientId ( $key );
$this->setSecret ( $secret );
$this->_curl_wrapper = new CurlWrapper ();
}
public function isTokenEmpty() {
$oauth_token = $this->getOauthToken ();
return empty ( $oauth_token );
}
public function getAccountInfo($cached = false) {
$response = $this->_getAccInfoCache ();
if (! $cached || false === $response) {
$api = $this->getAPIByServiceType ( 'accinfo' );
try {
$response = $this->curlPOST ( $this->_getURI ( $api ), $this->getHeader ( $api ) );
$this->_setAccInfoCache ( $response );
} catch ( \Exception $e ) {
$response = ! (empty ( $e )) ? json_encode ( array (
'message' => $e->getMessage (),
'code' => $e->getCode () 
) ) : null; 
}
}
return json_decode ( $response, true );
}
public function startAuthentication($auth_code = null, $state = null) {
$this->setAuthCode ( $auth_code );
if (empty ( $auth_code ))
$this->_requestAuthCode($state );
if ($this->isTokenEmpty ())
$this->_requestToken ();
}
public function locationRedirect($url) {
if (! headers_sent ()) {
if (strpos ( $url, $this->getAPIByServiceType ( 'auth' ) ) !== false) {
$hdr = $this->getHeader ();
if (! empty ( $hdr ))
header ( $hdr [0] );
}
header ( 'Location: ' . $url );
exit ();
}
echo '<script type="text/javascript">';
echo 'window.location.href="' . $url . '";';
echo '</script>';
echo '<noscript>';
echo '<meta http-equiv="refresh" content="0;url=' . $url . '" />';
echo '</noscript>';
}
public function getErrorArray($e = null) {
if (empty ( $e )) {
$error = error_get_last ();
$msg = $error ['message'];
$code = $error ['type'];
} else {
$msg = $e->getMessage ();
$code = $e->getCode ();
}
return array (
'error_message' => $msg,
'error_code' => $code 
);
}
public function curlGetDebugOutput() {
return $this->_curl_wrapper->curlGetDebugOutput ();
}
public function curlGetSSLInfo() {
return $this->_curl_wrapper->getSSLInfo ();
}
public function initFromArray($authInfo) {
if (! isset ( $authInfo ))
return false;
$this->setClientId ( $this->_isNull ( $authInfo, 'oauth_client_id' ) );
$this->setSecret ( $this->_isNull ( $authInfo, 'oauth_secret' ) );
$this->setOauthToken ( $this->_isNull ( $authInfo, 'access_token' ) );
$this->setTokenId ( $this->_isNull ( $authInfo, 'id_token' ) );
$this->setTokenType ( $this->_isNull ( $authInfo, 'token_type' ) );
$this->setTokenExpiration ( $this->_isNull ( $authInfo, 'expires_in', 3600 * 24 * 90 ) );
$this->setState ( $this->_isNull ( $authInfo, 'state' ) );
$this->_setIfNotNull ( $authInfo, 'refresh_token', 'setRefreshToken' );
$this->_setIfNotNull ( $authInfo, 'api_key', 'setAPIKey' );
$this->setTokenDate ( $this->_isNull ( $authInfo, 'oauth_token_created', time () ) );
$this->refreshToken ();
return true;
}
public function initFromFile($filename) {
if (! file_exists ( $filename ))
return false;
$authInfo = json_decode ( file_get_contents ( $filename ), true );
return $this->initFromArray ( $authInfo );
}
public function curlAborted() {
return $this->_curl_wrapper->curlAborted ();
}
public function setTimeout($value) {
$this->_curl_wrapper->setTimeout ( $value );
}
public function curlInitFromArray($settings) {
$this->_curl_wrapper->initFromArray ( $settings );
}
public function getServerName($cached = true) {
$session_key = $this->_getURI ( $this->getAPIByServiceType ( 'accinfo' ) );
if ($cached && isset ( $_SESSION [$session_key] ))
return $_SESSION [$session_key];
return $this->_curl_wrapper->getServerName ( $session_key, $cached );
}
public function isSecure() {
return $this->_curl_wrapper->isSecure ();
}
}
?>