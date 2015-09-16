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
 * @file    : index.php $
 * 
 * @id      : index.php | Wed Sep 16 11:33:37 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;

define ( "STARTPAGE_LOAD_TIME", microtime ( true ) );
define ( "INCLUDE_DEBUG_STATUSBAR", true );
require_once 'config.php';
require_once FUNCTIONS_PATH . 'utils.php';
require_once LOCALE_PATH . 'locale.php';
require_once FUNCTIONS_PATH . 'settings.php';
define ( "YAYUI_HANDLER", true );
function _do_password_recovery($login_obj) {
try {
$login_obj->recoverPassword () && die ( _esc ( "The password has been sent at user`s email address<br>only if the usename-secret pair successfuly matched." ) ) || die ( _esc ( "Send error: the e-mail could not be sent." ) );
} catch ( MyException $e ) {
die ( $e->getMessage () );
}
}
function _do_prepare_login_form($login_obj) {
$result = array ();
if (defined ( 'SANDBOX' ) && SANDBOX && defined ( 'SANDBOX_CALLBACK' ) && is_callable ( SANDBOX_CALLBACK )) {
$login_obj->java_scripts = $login_obj->java_scripts + call_user_func ( SANDBOX_CALLBACK );
}
$login_obj->loginForm ();
if (isSSL ()) {
$obj = new CurlWrapper ();
$obj->setProtocol ( CURLPROTO_HTTP | CURLPROTO_HTTPS );
$obj->setExcludeBody ();
$obj->setOKStatusCodes ( array (
200,
429 
) );
$obj->curlPOST ( selfURL () );
$ssl_info = $obj->getSSLInfo ();
$ssl_hint = _esc ( 'This certificate guarantes that the login information will be sent encrypted.' );
bindSSLInfo ( 'password', $ssl_info, $result, $ssl_hint );
}
return $result;
}
function _kill_too_many_requests($login_obj) {
$client_ip = isset ( $_SERVER ['REMOTE_ADDR'] ) ? $_SERVER ['REMOTE_ADDR'] : null;
$proxy_ip = isset ( $_SERVER ['HTTP_X_FORWARDED_FOR'] ) ? $_SERVER ['HTTP_X_FORWARDED_FOR'] : null;
if ($login_obj->checkIfBruteForce ( $client_ip, $proxy_ip )) {
header ( 'HTTP/1.1 429 Too Many Requests', true, 429 );
printf ( _esc ( 'Too many login requests within a short interval.<br>Automatically retry on %d seconds' ), SIMPLELOGIN_LOGIN_RETRY_LIMIT );
echo '<script type="text/javascript">setTimeout(function(){location.reload();},' . (1000 * SIMPLELOGIN_LOGIN_RETRY_LIMIT) . ');</script>';
exit ();
}
}
date_default_timezone_set ( wp_get_timezone_string () );
setLanguage ( getSelectedLangCode () ); 
$login_obj = new SimpleLogin ( defined ( 'LOG_PREFIX' ) ? dirname ( LOG_PREFIX ) : null, defined ( 'SSL_ENFORCE' ) && SSL_ENFORCE );
$login_obj->onCheckJavaScript = 'checkJavaScriptAvailable';
$login_obj->setEnforceStrongPassword ();
$login_obj->allowPasswordRecovery ( true, selfURL () );
$login_obj->setLoginTitle ( sprintf ( _esc ( 'Login into %s' ), WPMYBACKUP ) );
$login_obj->setOnPasswordStrength ( 'js55f93aab8f090.passwordEntropy' );
$is_logged = $login_obj->isLogged ();
! $is_logged && _kill_too_many_requests ( $login_obj );
! empty ( $_POST ['action'] ) && $_POST ['action'] == 'login_recovery' && _do_password_recovery ( $login_obj );
if (! (isset ( $_POST ) && isset ( $_POST ['action'] ) && $_POST ['action'] == 'dwl_file')) {
ob_start ();
include_once INC_PATH . 'header.php';
echo '<body>';
echo '<div class="wrap" id="wpmybackup_dashboard">' . PHP_EOL;
}
! defined ( "ALLOW_ONLY_WP" ) && define ( "ALLOW_ONLY_WP", false ); 
if (! $is_logged && isset ( $_POST ) && isset ( $_POST ['username'] ) && isset ( $_POST ['password'] )) {
$is_logged = $login_obj->loginUser ( $_POST ['username'], $_POST ['password'] );
}
if ($is_logged && isset ( $_POST ) && isset ( $_POST ['action'] ) && 'logout' == $_POST ['action']) {
$login_obj->logout ();
$is_logged = false;
}
$footer_banner = '';
if ($is_logged) {
if (isset ( $_SESSION [SIMPLELOGIN_SESSION_USERNAME] ))
$username = $_SESSION [SIMPLELOGIN_SESSION_USERNAME];
else {
$login_obj->logout ();
$is_logged = false;
}
if ($is_logged) {
define ( "WPMYBACKUP_LOGOFF", "<img src=\"" . plugins_url_wrapper ( 'img/avatar.png', IMG_PATH ) . "\"> <a style='cursor:pointer' onclick='js55f93aab8f090.post(js55f93aab8f090.this_url,{action:\"logout\"});'>" . sprintf ( _esc ( 'Logoff %s' ), $username ) . "</a>" );
define ( 'DO_NOT_AFTER_SETTINGS', true ); 
$settings = loadSettings ();
require_once CLASS_PATH . 'regactions.php'; 
$dashboard_class = 'ProDashboard';
$dashboard_file = CLASS_PATH . "$dashboard_class.php";
file_exists ( $dashboard_file ) || $dashboard_class = 'Dashboard';
require_once CLASS_PATH . "$dashboard_class.php";
$dashboard_class = __NAMESPACE__ . '\\' . $dashboard_class;
$dashboard = new $dashboard_class ();
$java_scripts = array_merge ( $java_scripts, $dashboard->getJavaScripts () );
$footer_banner = $dashboard->getBanner ( 'footer_banner' );
$dashboard->show ();
}
}
if (! $is_logged) {
$login_js = _do_prepare_login_form ( $login_obj );
$java_scripts = array_merge ( $java_scripts, $login_js );
if (! empty ( $login_js ))
echo '<script>' . implode ( PHP_EOL, $login_js ) . '</script>';
}
echo '</div>'; 
include_once INC_PATH . 'footer.php';
require_once INC_PATH . 'functions.php';
echo insertDebugScript ( true );
echo '</body>';
$buffer = ob_get_clean ();
if (defined ( 'YAYUI_COMPRESS' ) && YAYUI_COMPRESS && (empty ( $_GET ) || empty ( $_GET ['noyayui'] ))) {
$yayui = new YayuiCompressor ();
$buffer = $yayui->htmlCompress ( $buffer );
}
echo $buffer;
?>
