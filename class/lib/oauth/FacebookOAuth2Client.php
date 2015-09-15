<?php
/**
 * ################################################################################
 * MyBackup
 * 
 * Copyright 2015 Eugen Mihailescu <eugenmihailescux@gmail.com>
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
 * @version : 0.2.0-10 $
 * @commit  : bc79573e2975a220cb1cfbb08b16615f721a68c5 $
 * @author  : Eugen Mihailescu <eugenmihailescux@gmail.com> $
 * @date    : Mon Sep 14 21:14:57 2015 +0200 $
 * @file    : FacebookOAuth2Client.php $
 * 
 * @id      : FacebookOAuth2Client.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;
define ( 'FACEBOOK_SERVICE_ID', 'facebook' );
define ( 'FACEBOOK_OAUTH_URL', 'www.facebook.com' );
define ( 'FACEBOOK_GRAPH_URL', 'graph.facebook.com' );
define ( 'FACEBOOK_PROTOCOL', 'https' ); 
define ( 'FACEBOOK_OAUTH_VERSION', '2' );
define ( 'FACEBOOK_ACCESS_TYPE', 'offline' ); 
define ( 'FACEBOOK_APPROVAL_PROMPT', 'reauthenticate' ); 
class FacebookOAuth2Client extends GenericOAuth2Client {
private $_SESSION_API = array (
'auth' => 'oauth',
'token' => 'access_token',
'scope' => 'scope',
'accinfo' => 'me' 
);
public function getAPIByServiceType($api) {
return $this->_SESSION_API [$api];
}
protected function _getURI($function, $state = null) {
$domain = FACEBOOK_GRAPH_URL;
$path = '/v2.3';
$params = '';
switch ($function) {
case $this->_SESSION_API ['auth'] :
$proxy_uri = $this->getProxyURI ();
if (! empty ( $proxy_uri ) && empty ( $state ))
return $this->_getProxyAuthURI ( FACEBOOK_SERVICE_ID );
$path = 'dialog';
$domain = FACEBOOK_OAUTH_URL;
$scope = $this->_getScopes ();
$params = array (
'redirect_uri' => $this->getRedirectUri (),
'response_type' => 'code',
'client_id' => $this->getClientId (),
'auth_type' => FACEBOOK_APPROVAL_PROMPT 
);
! empty ( $scope ) && $params ['scope'] = implode ( ',', $scope );
! empty ( $state ) && $params ['state'] = $state;
$params = $function . '?' . http_build_query ( $params );
break;
case $this->_SESSION_API ['token'] :
$domain = FACEBOOK_GRAPH_URL;
$path = 'oauth';
$params = array (
'redirect_uri' => $this->getRedirectUri (),
'client_id' => $this->getClientId (),
'client_secret' => $this->getSecret (),
'code' => $this->getAuthCode () 
);
$params = $function . '?' . http_build_query ( $params );
break;
case $this->_SESSION_API ['scope'] :
return 'email'; 
break;
case $this->_SESSION_API ['accinfo'] :
$path .= '/' . $function;
$params = '?fields=id,name,email,first_name,last_name';
break;
}
return sprintf ( '%s://%s/%s/%s', FACEBOOK_PROTOCOL, $domain, $path, $params );
}
public function mergeAPIS($with_apis) {
$this->_SESSION_API = array_merge ( $this->_SESSION_API, $with_apis );
}
}
?>