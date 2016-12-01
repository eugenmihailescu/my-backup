<?php
/**
 * ################################################################################
 * MyBackup
 * 
 * Copyright 2016 Eugen Mihailescu <eugenmihailescux@gmail.com>
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
 * @version : 0.2.3-36 $
 * @commit  : c4d8a236c57b60a62c69e03c1273eaff3a9d56fb $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Thu Dec 1 04:37:45 2016 +0100 $
 * @file    : DropboxOAuth2Client.php $
 * 
 * @id      : DropboxOAuth2Client.php | Thu Dec 1 04:37:45 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
define ( __NAMESPACE__.'\\DROPBOX_PROTOCOL', 'https' );
define ( __NAMESPACE__."\\DROPBOX_DOMAIN", 'dropbox.com' );
define ( __NAMESPACE__."\\DROPBOX_OAUTH_VERSION", '2' );
define ( __NAMESPACE__.'\\DROPBOX_SERVICE_ID', 'dropbox' );
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