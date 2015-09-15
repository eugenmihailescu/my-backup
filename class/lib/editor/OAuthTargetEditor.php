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
 * @file    : OAuthTargetEditor.php $
 * 
 * @id      : OAuthTargetEditor.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;
class OAuthTargetEditor extends AbstractTargetEditor {
private $_authInfo;
protected $_path_id;
protected $on_read_folder_ready;
private $_session;
protected $_direct_dwl;
private function _stripOAuthFromURL() {
$unlink_params = array (
'access_token',
'token_type',
'expires_in',
'refresh_token',
'oauth_token_created',
'state',
'api_key' 
);
$this_url = stripUrlParams ( selfURL (), $unlink_params );
parse_str ( parse_url ( $this_url, PHP_URL_QUERY ), $array );
! isset ( $array ['tab'] ) && $this_url .= '&tab=' . $this->target_name;
return $this_url;
}
private function _getJavaScripts() {
$this->java_scripts [] = "var div=document.getElementById('{$this->target_name}_authorize_div');if(div){div.style='width:0px;height:0px;visibility:hidden';}" . "parent.plugin_dir='" . addslashes(dirname ( realpath($_SERVER ['SCRIPT_NAME'] ))) . "';";
}
protected function initTarget() {
parent::initTarget ();
$this->hasInfoBanner = defined('FILE_EXPLORER');
$this->_direct_dwl = strToBool ( $this->settings [$this->target_name . '_direct_dwl'] );
$this->_authInfo = null;
$this->root = addTrailingSlash ( $this->settings [$this->target_name], '/' );
$this->_path_id = $this->settings [$this->target_name . '_path_id'];
$this->on_read_folder_ready = null;
$this->_getJavaScripts ();
}
protected function hideEditorContent() {
return ! $this->enabled || null == $this->_authInfo;
}
protected function onGenerateEditorContent() {
$java_scripts = echoFolder ( $this->target_name, $this->root, $this->_path_id, $this->ext_filter, $this->function_name, '/', null, $this->target_name, false, null, $this->settings, $this->on_read_folder_ready );
false !== $java_scripts && $this->java_scripts = array_merge ( $java_scripts, $this->java_scripts );
try {
$metadata = $this->_session->getAccountInfo ( true );
$ssl_info = $this->_session->curlGetSSLInfo ();
if (! empty ( $ssl_info ))
add_session_var ( $this->target_name . '_ssl_cert_info', $ssl_info );
$ssl_hint = _esc ( 'This certificate guarantes that the communication is encrypted.' );
if (isset ( $_SESSION [$this->target_name . '_ssl_cert_info'] )) 
bindSSLInfo ( $this->target_name, $_SESSION [$this->target_name . '_ssl_cert_info'], $this->java_scripts, $ssl_hint );
} catch ( MyException $e ) {
}
}
protected function getEditorTemplate() {
$enabled_name = '"' . $this->target_name . '_enabled"';
$service_edit_name = '"' . $this->target_name . '"';
$service_age_name = '"' . $this->target_name . '_age"';
$service_name = ucwords ( $this->target_name );
require_once $this->getTemplatePath ( 'oauth.php' );
}
protected function getExpertEditorTemplate() {
return null != $this->_authInfo;
}
protected function validateEditor() {
global $TARGET_NAMES;
$this->target_name_name = ucwords ( $this->target_name );
$api = null;
$storage = null;
$this->_session = null;
$target_auth_file = ROOT_OAUTH_FILE . $this->target_name . '.auth';
if (isset ( $_GET [$this->target_name . '_unlink'] ) && file_exists ( $target_auth_file )) {
unlink ( $target_auth_file );
$this->java_scripts [] = "js55f846e1d1da3.popupWindow('" . _esc ( 'Success' ) . "','" . sprintf ( _esc ( "%s is no longer linked with %s.<br>You can, however, authorize the %s access anytime" ), WPMYBACKUP, $this->target_name_name, $this->target_name_name ) . "');";
}
$this->_authInfo = null;
if (file_exists ( $target_auth_file )) {
$this->_authInfo = json_decode ( file_get_contents ( $target_auth_file ), true );
}
if (null == $this->_authInfo && ! empty ( $_GET ['access_token'] )) {
$this->_authInfo = array (
'access_token' => $_GET ['access_token'],
'token_type' => $_GET ['token_type'],
'expires_in' => empty ( $_GET ['expires_in'] ) ? time () + 3600 * 24 * 90 : $_GET ['expires_in'],
'oauth_token_created' => ! empty ( $_GET ['oauth_token_created'] ) ? $_GET ['oauth_token_created'] : time (),
'state' => $_GET ['state'],
'refresh_token' => ! empty ( $_GET ['refresh_token'] ) ? $_GET ['refresh_token'] : '',
'api_key' => ! empty ( $_GET ['api_key'] ) ? $_GET ['api_key'] : null 
);
}
$session_class = null;
switch ($this->target_name) {
case 'dropbox' :
$session_class = 'DropboxOAuth2Client';
$storage_class = 'DropboxCloudStorage';
$this->function_name = $this->target_item->function_name;
break;
case 'google' :
$session_class = 'GoogleOAuth2Client';
$storage_class = 'GoogleCloudStorage';
$this->function_name = $this->target_item->function_name;
break;
}
if (null == $this->_authInfo) {
require_once OAUTH_PATH . $session_class . '.php';
echo "<!-- Storage-Cloud auth-box -->" . PHP_EOL;
echo '<div id="storage_authorize_div"><p class="redcaption">' . sprintf ( _esc ( '%s is not yet configured</p><p>Before using this option you have to authorize %s this application to upload files to your %s account :' ), $this->target_name_name, WPMYBACKUP, $this->target_name_name );
echo '<input type="button" class="button-primary" name="btnSubmit" value="' . _esc ( 'Authorize' ) . '" onclick="js55f846e1d1da3.send_oauthrequest(\'post\',\'' . $this->target_name . '\',\'' . htmlspecialchars ( PROXY_PARAMS ) . '\');">';
if ('google' == $this->target_name)
echo '<br>' . readMoreHere ( 'https://developers.google.com/accounts/docs/OAuth2WebServer#offline', sprintf ( _esc ( 'about %s authorization scope' ), $this->target_name_name ) );
echo '</div>' . PHP_EOL;
echo "<!-- Storage-Cloud auth-box -->" . PHP_EOL;
}
require_once OAUTH_PATH . $session_class . '.php';
require_once STORAGE_PATH . $storage_class . '.php';
$session_class = __NAMESPACE__ . '\\' . $session_class;
$storage_class = __NAMESPACE__ . '\\' . $storage_class;
$this->_session = new $session_class ();
$this->_session->setProxyURI ( OAUTH_PROXY_URL, '' );
$this->_session->setTimeout ( $this->settings ['request_timeout'] );
$storage = new $storage_class ( $this->_session );
try {
$this->_session->initFromArray ( $this->_authInfo );
if (! (null == $this->_authInfo || file_exists ( $target_auth_file ))) {
file_put_contents ( $target_auth_file, json_encode ( $this->_authInfo ) );
locationRedirect ( $this->_stripOAuthFromURL () );
}
} catch ( MyException $e ) {
$this->_authInfo = null;
echo "<div class='hintbox {$this->container_shape}' style='display:inline-block'>" . _esc ( 'An unexpected error occured while tried to authenticate at ' ) . ucwords ( $this->target_name ) . ":<br><p class='redcaption'>" . $e->getMessage () . '</p>';
echo '<b>' . _esc ( 'Solution:' ) . '</b>';
printf ( _esc ( '<p>Try again (eventually few more times at an interval of 60s or so). If it doesn`t work then %sclick here</a>.</p>' ), '<a href="#" onclick="js55f846e1d1da3.asyncGetContent(js55f846e1d1da3.ajaxurl,\'' . http_build_query ( array (
'action' => 'del_oauth',
'service' => $this->target_name,
'nonce' => wp_create_nonce_wrapper ( 'del_oauth' ) 
) ) . '\',null,function(){var p=location.href.indexOf(\'#\'),s=location.href;if(p)s=location.href.substring(0,p);location.href=s;});">' );
printf ( _esc ( '<p>If whatever you try doesn`t work then please %sreport this incident</a>.</p>' ), '<a href="' . getTabLink ( $TARGET_NAMES[APP_SUPPORT] ) . '&support_category=error">' );
echo '</div>';
}
return null != $this->_authInfo;
}
}
?>
