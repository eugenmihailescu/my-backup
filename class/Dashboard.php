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
 * @version : 0.2.2-10 $
 * @commit  : dd80d40c9c5cb45f5eda75d6213c678f0618cdf8 $
 * @author  : Eugen Mihailescu <eugenmihailescux@gmail.com> $
 * @date    : Mon Dec 28 17:57:55 2015 +0100 $
 * @file    : Dashboard.php $
 * 
 * @id      : Dashboard.php | Mon Dec 28 17:57:55 2015 +0100 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

require_once INC_PATH . 'globals.php';
require_once FUNCTIONS_PATH . 'utils.php';
require_once FUNCTIONS_PATH . 'settings.php';
require_once INC_PATH . 'functions.php';
class Dashboard {
private $_dashboard_section;
private $_java_scripts;
protected $_settings;
function __construct() {
check_is_logged();
$this->_java_scripts = array();
$this->_dashboard_section = 'dashboard';
$this->_settings = null;
$this->_init();
}
private function _init() {
global $has_postbox, $settings;
if ( get_magic_quotes_gpc() ) {
$_POST = array_map( 'stripslashes_deep', $_POST );
$_GET = array_map( 'stripslashes_deep', $_GET );
$_REQUEST = array_map( 'stripslashes_deep', $_REQUEST );
}
$has_postbox = true;
$this->_settings = loadSettings();
afterSettingsLoad( $this->_settings );
$settings = $this->_settings;
$this->_cleanUp();
$this->initFeatures();
}
private function _insertDebugStatusbar( &$buffer ) {
if ( defined( __NAMESPACE__.'\\DEBUG_STATUSBAR' ) && DEBUG_STATUSBAR ) {
$buffer = preg_replace( 
'/<(span)\s+id="notification_debug"[^>]*>([\s\S]*?)<\/\1>/', 
'<span id="notification_debug" class="hintbox" style="padding:3px;border-radius:5px">$2(' .
$_SERVER['SERVER_SOFTWARE'] . ' load time:' . round( microtime( true ) - STARTPAGE_LOAD_TIME, 3 ) .
's)</span>', 
$buffer );
}
}
private function _insertFigletPlaceHolder() {
if ( file_exists( INC_PATH . 'banner.txt' ) ) {
echo PHP_EOL . '<!--' . PHP_EOL . file_get_contents( INC_PATH . 'banner.txt' ) . PHP_EOL . '-->' . PHP_EOL;
}
}
private function _sanitize( &$buffer ) {
if ( ! defined( __NAMESPACE__."\\YAYUI_HANDLER" ) && YAYUI_COMPRESS && ( empty( $_GET ) || empty( $_GET['noyayui'] ) ) ) {
$yayui = sanitizeYAYUI();
$buffer = $yayui->htmlCompress( $buffer );
}
}
protected function initFeatures() {
}
protected function _cleanUp() {
if ( 'THIS_HAS_NOT_BEEN_IMPLEMENTED_YET' ) {
$log = MESSAGES_LOGFILE;
$alert_message_obj = new MessageHandler( $log );
$alert_message_obj->delMessagesByDate( time() - SECDAY * intval( $this->_settings['message_age'] ) );
}
$found = isset( $_SESSION['clear_obsolete_signals'] );
! $found && add_session_var( 'clear_obsolete_signals', time() );
$found = time() - $_SESSION['clear_obsolete_signals'] < PROCESS_SIGNAL_TIMEOUT;
! $found && clearObsoleteProcessSignals();
}
protected function initTargetTabs() {
require_once CONFIG_PATH . 'default-target-tabs.php'; 
}
protected function getTitle() {
return WPMYBACKUP . ' ' . getSpan( 'Lite', 'gray', 'bold' );
}
protected function getTitleDesc() {
return _esc( 
"Why this name - %s?<br>Because all other were already taken :-)<br>It is the functionality what matters, right?" );
}
public function getBanner( $banner_key = '' ) {
$strip_ssl = function ( $str ) {
return preg_replace( '/(http)s(:[^:]+)(:\d+)?/', '\1\2/', $str );
};
$lang = getSelectedLangCode();
$cache = sprintf( LOG_PREFIX . '-banner%s.cache', empty( $lang ) ? '' : ( '-' . $lang ) );
if ( file_exists( $cache ) ) {
$result = file_get_contents( $cache );
if ( time() - intval( substr( $result, 0, 10 ) ) < SECDAY ) {
$banner = json_decode( substr( $result, 10 ), true );
if ( $banner_key && $banner )
return $banner[$banner_key];
return $banner;
}
}
$curl_wrapper = new CurlWrapper();
$curl_wrapper->setAllowCookies();
$curl_wrapper->setFollowLocation();
$params = array( 'action' => 'pro', 'system_id' => WPMYBACKUP_ID, 'info' => 'banner', 'wp' => is_wp() );
isset( $_GET['lang'] ) && $params['lang'] = $_GET['lang'];
try {
$result = $curl_wrapper->curlPOST( LICENSE_REGISTRAR_API, null, http_build_query( $params ) );
$ssl_info = $curl_wrapper->getSSLInfo();
if ( is_array( $ssl_info ) && isset( $ssl_info['certificate'] ) && isset( 
$ssl_info['certificate']['status'] ) && strpos( $ssl_info['certificate']['status'], '18' ) ) {
$result = $strip_ssl( $result );
}
file_put_contents( $cache, time() . $result );
} catch ( MyException $e ) {
$result = '';
}
$banner = json_decode( $result, true );
if ( $banner_key && $banner )
return $banner[$banner_key];
return $banner;
}
public function getJavaScripts() {
$this->_java_scripts[] = 'parent.read_alerts=function(){parent.asyncGetContent(parent.ajaxurl,"action=read_alert&nonce=' .
wp_create_nonce_wrapper( 'read_alert' ) . '","notification_msg", null, -1);};parent.read_alerts();';
return $this->_java_scripts;
}
public function show() {
global $has_postbox;
ob_start();
{
$this->_insertFigletPlaceHolder();
insertHTMLSection( $this->_dashboard_section );
if ( defined( __NAMESPACE__.'\\DEBUG_STATUSBAR' ) && DEBUG_STATUSBAR && ! defined( __NAMESPACE__.'\\INCLUDE_DEBUG_STATUSBAR' ) )
echo '<script type="text/javascript">Date.now = Date.now || function() { return +new Date; }; window.page_start_loading=Date.now();</script>';
checkJavaScriptAvailable(); 
if ( defined( __NAMESPACE__.'\\SANDBOX' ) && SANDBOX ) {
$sandbox_warning = array( 
sprintf( 
_esc( 
'This is an online environment where you may test the full version of %s (all addons installed). %s.' ), 
WPMYBACKUP, 
readMoreHere( selfURL( true ) . 'product/wp-mybackup' ) ), 
_esc( 
'In the Sandbox environment some parameters have fixed or masked values and cannot be changed for security reasons.' ), 
sprintf( 
_esc( 
'The Wordpress version looks/works exactly like this one, except the <i>Backup source</i> and <i>Schedule</i> tabs which are adapted to Wordpress. %s.' ), 
readMoreHere( selfURL( true ) . 'screenshot' ) ) );
$sandbox_exceed = sandboxLimitExceeds();
$sandbox_exceed && $sandbox_warning[] = sprintf( 
'<span style="color:red">' . _( 
'%s : the maximum allowed concurrent sessions exceeded. Please try later (like 5 minutes or so).' ), 
'<b>' . _esc( 'Note' ) . '</b>' ) . '</span>';
$cookie_name = 'sandbox_acknowledge';
echo insertWarningBox( 
$cookie_name, 
'Sandbox', 
implode( '<br>', $sandbox_warning ), 
plugins_url_wrapper( 'img/sandbox.png', IMG_PATH ), 
array( false => 'Dismiss', true => 'Got it' ), 
$sandbox_exceed || ( isset( $_COOKIE[$cookie_name] ) && 'false' == $_COOKIE[$cookie_name] ) );
}
insertWarningBox( 
'cookie_accept', 
_esc( 'Cookies' ), 
sprintf( 
_esc( 
'This site uses cookies to offer you a better browsing experience. Find out more how we use cookies and how you can change your settings. %s.' ), 
readMoreHere( 'http://ec.europa.eu/ipg/basics/legal/cookies/index_en.htm' ) ), 
plugins_url_wrapper( 'img/cookie-24.png', IMG_PATH ), 
array( false => _esc( 'I refuse cookies' ), true => _esc( 'I accept cookies' ) ) ); 
echo '<div id="' . WPMYBACKUP_LOGS . '" class="wrap">' . PHP_EOL;
insertHeaderBar( $this->getTitle(), $this->getTitleDesc() ); 
if ( ! ( defined( __NAMESPACE__.'\\SANDBOX' ) && SANDBOX && $sandbox_exceed ) ) {
$this->initTargetTabs();
$section_name = 'Tabbed-Menus';
insertHTMLSection( $section_name );
$visible_tabs = insertTabMenus( $this->getBanner( 'menu_banner' ) );
insertHTMLSection( $section_name, true );
insertTabContent( 47 * $visible_tabs );
$has_postbox && insertPostboxJS(); 
insertFooterBar(); 
! defined( __NAMESPACE__.'\\INCLUDE_DEBUG_STATUSBAR' ) && insertDebugScript();
$yayui = sanitizeYAYUI();
insertHTMLSection( $this->_dashboard_section, true );
}
}
$buffer = ob_get_clean();
$this->_sanitize( $buffer );
$this->_insertDebugStatusbar( $buffer );
echo $buffer; 
}
}
?>