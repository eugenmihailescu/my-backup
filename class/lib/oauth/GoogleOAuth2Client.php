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
 * @file    : GoogleOAuth2Client.php $
 * 
 * @id      : GoogleOAuth2Client.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;
define ( 'GOOGLE_SERVICE_ID', 'google' );
define ( 'GOOGLE_APIS_URL', 'www.googleapis.com' );
define ( 'GOOGLE_OAUTH_URL', 'accounts.google.com' );
define ( 'GOOGLE_PROTOCOL', 'https' ); 
define ( 'GOOGLE_OAUTH_VERSION', '2' );
define ( 'GOOGLE_ACCESS_TYPE', 'offline' ); 
define ( 'GOOGLE_APPROVAL_PROMPT', 'force' ); 
class GoogleOAuth2Client extends GenericOAuth2Client {
private $_SESSION_API = array (
'auth' => 'auth',
'token' => 'token',
'scope' => 'scope',
'accinfo' => 'about',
'metadata' => 'metadata' 
);
public function getAPIByServiceType($api) {
return $this->_SESSION_API [$api];
}
protected function _getURI($function, $state = null) {
$domain = GOOGLE_APIS_URL;
$oauth_version = GOOGLE_OAUTH_VERSION;
$path = 'drive/v';
switch ($function) {
case $this->_SESSION_API ['auth'] :
$proxy_uri = $this->getProxyURI ();
if (! empty ( $proxy_uri ) && empty ( $state ))
return $this->_getProxyAuthURI ( GOOGLE_SERVICE_ID );
$path = 'o/oauth';
$domain = GOOGLE_OAUTH_URL;
$params = array (
'scope' => implode ( ' ', $this->_getScopes () ),
'redirect_uri' => $this->getRedirectUri (),
'response_type' => 'code',
'client_id' => $this->getClientId (),
'approval_prompt' => GOOGLE_APPROVAL_PROMPT,
'access_type' => GOOGLE_ACCESS_TYPE 
);
if (! empty ( $state ))
$params = array_merge ( $params, array (
'state' => $state 
) );
$params = $function . '?' . http_build_query ( $params );
break;
case $this->_SESSION_API ['token'] :
$domain = GOOGLE_OAUTH_URL;
$path = 'o/oauth';
$params = $function;
break;
case $this->_SESSION_API ['scope'] :
$path = 'auth';
$oauth_version = '';
$params = 'drive';
break;
case $this->_SESSION_API ['accinfo'] :
$params = $function;
break;
}
return sprintf ( '%s://%s/%s%s/%s', GOOGLE_PROTOCOL, $domain, $path, $oauth_version, $params );
}
public function mergeAPIS($with_apis) {
$this->_SESSION_API = array_merge ( $this->_SESSION_API, $with_apis );
}
}
?>
