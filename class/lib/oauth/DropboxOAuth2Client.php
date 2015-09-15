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
 * @file    : DropboxOAuth2Client.php $
 * 
 * @id      : DropboxOAuth2Client.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;
define ( 'DROPBOX_PROTOCOL', 'https' );
define ( "DROPBOX_DOMAIN", 'dropbox.com' );
define ( "DROPBOX_OAUTH_VERSION", '2' );
define ( 'DROPBOX_SERVICE_ID', 'dropbox' );
class DropboxOAuth2Client extends GenericOAuth2Client {
private $_SESSION_API = array (
'auth' => 'authorize',
'token' => 'token',
'accinfo' => 'account' 
);
protected function _getURI($function, $state = null) {
$oauth_version = '';
$path = '1/' . $function;
$params = 'auto';
$domain = 'api';
switch ($function) {
case $this->_SESSION_API ['auth'] :
$proxy_uri = $this->getProxyURI ();
if (! empty ( $proxy_uri ) && empty ( $state ))
return $this->_getProxyAuthURI ( DROPBOX_SERVICE_ID );
$domain = 'www';
$path = '1/oauth';
$oauth_version = DROPBOX_OAUTH_VERSION;
$params = array (
'redirect_uri' => $this->getRedirectUri (),
'response_type' => 'code',
'client_id' => $this->getClientId (),
'force_reapprove' => 'true'  // DEBUG mode only
);
if (! empty ( $state ))
$params = array_merge ( $params, array (
'state' => $state 
) );
$params = $function . '?' . http_build_query ( $params );
break;
case $this->_SESSION_API ['token'] :
$path = '1/oauth';
$oauth_version = DROPBOX_OAUTH_VERSION;
$params = $function;
break;
case $this->_SESSION_API ['accinfo'] :
$params = 'info';
break;
}
return sprintf ( '%s://%s.%s/%s%s/%s', DROPBOX_PROTOCOL, $domain, DROPBOX_DOMAIN, $path, $oauth_version, $params );
}
public function getAPIByServiceType($api) {
return $this->_SESSION_API [$api];
}
public function mergeAPIS($with_apis) {
$this->_SESSION_API = array_merge ( $this->_SESSION_API, $with_apis );
}
}
?>
