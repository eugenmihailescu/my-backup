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
 * @version : 0.2.2 $
 * @commit  : 23a9968c44669fbb2b60bddf4a472d16c006c33c $
 * @author  : Eugen Mihailescu <eugenmihailescux@gmail.com> $
 * @date    : Wed Sep 16 11:33:37 2015 +0200 $
 * @file    : AbstractOAuthClient.php $
 * 
 * @id      : AbstractOAuthClient.php | Wed Sep 16 11:33:37 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;

abstract class AbstractOAuthClient  {
const PARAM_SERVICE = 's';
const PARAM_REQUEST_URI = 'r';
const PARAM_PROXY = 'p';
const PARAM_STATE = 'state';
private $_authCode;
private $_oauthToken;
private $_secret;
private $_clientId;
private $_apiKey;
private $_tokenType;
private $_expiresIn;
private $_idToken;
private $_refreshToken;
private $_created;
private $_state;
private $_proxyURI;
private $_proxy_auth_params;
function getServiceType() {
return $this->service_type;
}
function setServiceType($service_type) {
$this->service_type = $service_type;
}
function setClientId($client_id) {
$this->_clientId = empty ( $client_id ) ? null : $client_id;
}
function getClientId() {
return $this->_clientId;
}
function getOauthToken() {
return $this->_oauthToken;
}
function setOauthToken($oauth_token) {
$this->_oauthToken = $oauth_token;
}
function getAPIKey() {
return $this->_apiKey;
}
function setAPIKey($api_key) {
$this->_apiKey = empty ( $api_key ) ? null : $api_key;
}
function getAuthCode() {
return $this->_authCode;
}
function setAuthCode($auth_code) {
$this->_authCode = $auth_code;
}
function getTokenType() {
return $this->_tokenType;
}
function setTokenType($token_type) {
$this->_tokenType = $token_type;
}
function getTokenExpiration() {
return $this->_expiresIn;
}
function setTokenExpiration($expires_in) {
$this->_expiresIn = $expires_in;
}
function getTokenId() {
return $this->_idToken;
}
function setTokenId($id_token) {
$this->_idToken = $id_token;
}
function getRefreshToken() {
return $this->_refreshToken;
}
function setRefreshToken($refresh_token) {
$this->_refreshToken = $refresh_token;
}
function getSecret() {
return $this->_secret;
}
function setSecret($secret) {
$this->_secret = empty ( $secret ) ? null : $secret;
}
function getTokenDate() {
return $this->_created;
}
function setTokenDate($created) {
$this->_created = $created;
}
function getState() {
return $this->_state;
}
function setState($state) {
$this->_state = $state;
}
public function getProxyURI() {
return $this->_proxyURI;
}
public function setProxyURI($proxy_uri, $proxy_auth_params = null) {
$this->_proxyURI = $proxy_uri;
$this->setProxyAuthParams ( $proxy_auth_params );
}
function getProxyAuthParams() {
return $this->_proxy_auth_params;
}
function setProxyAuthParams($proxy_auth_params) {
$this->_proxy_auth_params = $proxy_auth_params;
}
}
?>
