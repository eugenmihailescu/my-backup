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
 * @file    : ui.php $
 * 
 * @id      : ui.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;

function insertHTMLSection($section_name, $ending = false, $default_echo = true, $js_comment = false) {
$start_tag = ($js_comment ? '//' : '<!--') . ' ';
$end_tag = ' ' . ($js_comment ? '' : '-->');
$section_name = WPMYBACKUP . ' ' . $section_name;
$separator = PHP_EOL . $start_tag . ($ending ? '' : ':-) ') . str_repeat ( '/', 40 ) . '  %s %s here ' . str_repeat ( '\\', 40 ) . ($ending ? ' :-(' : '') . $end_tag . PHP_EOL;
$section_separator = sprintf ( $separator, $section_name, $ending ? 'ends' : 'starts' );
if ($default_echo)
echo $section_separator;
return $section_separator;
}
function getBackupSourcesJS($progress_providers) {
$bak_src = array ();
array_walk ( $progress_providers, function ($value, $key) use(&$bak_src) {
$bak_src [] = "'$key':'$value'";
} );
return 'parent.backup_sources = {' . implode ( ',', $bak_src ) . '};';
}
function getBranchedFileName($filename) {
global $_branch_id_;
if (! isset ( $_branch_id_ ))
return $filename;
return dirname ( $filename ) . DIRECTORY_SEPARATOR . $_branch_id_ . DIRECTORY_SEPARATOR . basename ( $filename );
}
function getTabLink($tab) {
if (false !== strpos ( $_SERVER ['QUERY_STRING'], 'tab=' ))
$href = preg_replace ( '/(\btab=[^&]*)/', 'tab=' . $tab, $_SERVER ['QUERY_STRING'] );
else
$href = (strlen ( $_SERVER ['QUERY_STRING'] ) > 0 ? $_SERVER ['QUERY_STRING'] . '&' : '') . 'tab=' . $tab;
return $_SERVER ['PHP_SELF'] . '?' . $href;
}
function getTabAnchor($tab, $query = null, $target = null, $escape = false) {
global $registered_targets, $TARGET_NAMES;
$func = __NAMESPACE__ . '\\getAnchor' . ($escape ? 'E' : '');
return call_user_func ( $func, $registered_targets [$tab] ['title'], getTabLink ( $TARGET_NAMES [$tab] ) . (empty ( $query ) ? '' : $query), empty ( $target ) ? '_self' : $target );
}
function getTabAnchorE($tab, $query = null, $target = null) {
return getTabAnchor ( $tab, $query, $target, true );
}
function getSSLIcon() {
if (! isSSL ()) {
$icon = 'security-high.png';
$title = _esc ( 'Warning' );
$function = "js55f82caaae905.popupError";
$msg = sprintf ( "Password fields present on an insecure (http://) page. This is a security risk that allows user login credentials to be stolen.%s : %s", sprintf ( '<br><br><b>%s</b>', _esc ( 'Solution' ) ), sprintf ( _esc ( "connect the server by using its SSL certificate, ie. replace <i>http</i> with %s in the browser address bar. If your server does not have a SSL certificate yet then you may want to install one (if it does not already exist) in order to:%shide the information that you send/receive from server</li><li>make sure the computer you are talking to is the one<br>you trust" ) . '</li></ol>', 'http' . getSpanE ( 's', 'red', 'bold' ), escape_quotes ( '<ol type="i"><li>' ) ) );
} else {
$icon = 'security-low.png';
$title = _esc ( 'Notice' );
$function = "js55f82caaae905.popupWindow";
$msg = _esc ( "Password fields present on an secure (https://) page.<br>Thanks to the SSL your password is safe, nobody between this<br>PC and the web server can read/stole your password likewise<br>no other data send/received between these two machines." ) . '<br>';
}
$msg .= getAnchorE ( _esc ( 'The short story' ), 'https://www.youtube.com/watch?v=SJJmoDZ3il8' ) . '. ';
$msg .= getAnchorE ( _esc ( 'The long story' ), 'https://www.youtube.com/watch?v=iQsKdtjwtYI' ) . '. ';
$msg .= getAnchorE ( _esc ( 'Just read this' ), 'https://developer.mozilla.org/docs/Security/InsecurePasswords' ) . '.';
return "<img name='ssl_alert' style='vertical-align:middle;cursor:help' src='" . plugins_url_wrapper ( "img/$icon", IMG_PATH ) . "' onclick='$function(\"$title\",\"$msg\"," . DEFAULT_JSPOPUP_WIDTH . ");'/>";
}
function getReportIssueURL() {
return isset ( $_SERVER ['HTTP_REFERER'] ) ? replaceUrlParam ( $_SERVER ['HTTP_REFERER'], array (
'tab',
'support_category' 
), array (
'support',
'error' 
) ) : null;
}
function chkIncludeTab($tabs, $active_tab, $child_tabs_path = '', $tab_sufix = '-tab', $tab_filename = '') {
global $container_shape, $TARGET_NAMES;
if (! empty ( $child_tabs_path ))
$child_tabs_path = addTrailingSlash ( $child_tabs_path );
else
$child_tabs_path = '';
$tab_folder = ROOT_PATH . "$child_tabs_path";
$tab_file = $tab_folder . (empty ( $tab_filename ) ? (empty ( $active_tab ) ? '*' : $active_tab) : $tab_filename) . "$tab_sufix.php";
if (file_exists ( $tab_file )) {
echo PHP_EOL;
return $child_tabs_path . $active_tab . $tab_sufix . ".php";
}
$href = getTabLink ( $TARGET_NAMES [APP_SUPPORT] ) . '&support_category=error';
echo "<div class='hintbox $container_shape'>";
if (isset ( $tabs [$active_tab] ))
echo "<p style='color:red'>" . sprintf ( _esc ( "The tab you've mentioned '%s' is valid but its source file does not exist.</p>I have expected to find it at:<blockquote>%s</blockquote>If there is no good explanation for this issue then perhaps your installation is corrupted.<br>Please reinstall or update the %s. If the error persists please %s." ), $active_tab, $tab_file, WPMYBACKUP, "<a href='$href'>" . _esc ( 'send a bug report' ) . "</a>" );
else
echo "<p style='color:red'>" . sprintf ( _esc ( "The tab you've mentioned '%s' is not valid.</p>Are you just checking my vigilance? :-)<br>If you think this might be an error we appologieze for this inconvenience. In that case please %s." ), $active_tab, "<a href='$href'>" . _esc ( 'send a bug report' ) . "</a>" );
echo '</div>';
return false;
}
function bindInfo2JavaScript($element, $text, &$java_scripts) {
$java_scripts [] = "parent.globals.HOVER_HINT=null,el=document.getElementById('$element'),fct_prefix=parent.ie < 9 ? 'on' : '';";
$java_scripts [] = "if(el){el.style.cursor='help';el.style.backgroundColor='#00F25A';";
$java_scripts [] = "parent._addEventListener(el,fct_prefix+'mouseover',function() {if (null !== parent.globals.HOVER_HINT) {parent.globals.HOVER_HINT.style.visibility = 'visible';parent.globals.HOVER_HINT.style.position = 'absolute';return;}parent.globals.HOVER_HINT = parent.createDocElement(document.body, 'div', {'style': 'z-index:1001;position:absolute;background-color:rgb'+(parent.ie<9?'':'a')+'(255,255,196'+(parent.ie<9?'':',0.75')+');padding:10px;border:1px solid #c0c0c0;border-radius:10px;'},null,true);parent.globals.HOVER_HINT.innerHTML='" . str_replace ( PHP_EOL, '', $text ) . "';});";
$java_scripts [] = "parent._addEventListener(el,fct_prefix+'mouseout',function() {if (null !== parent.globals.HOVER_HINT)parent.globals.HOVER_HINT.style.visibility = 'hidden';});";
$java_scripts [] = "parent._addEventListener(el,fct_prefix+'mousemove',function(event) {var a = event || window.event;var clientY = a.clientY + 1;var clientX = a.clientX + 1;if (null !== parent.globals.HOVER_HINT){parent.globals.HOVER_HINT.style.left = clientX + 'px';parent.globals.HOVER_HINT.style.top = clientY + 'px';}});";
$java_scripts [] = "}";
}
function bindSSLInfo($element, $ssl_info, &$java_scripts, $ssl_hint = null) {
if (! (is_array ( $ssl_info ) && isset ( $ssl_info ['version'] ) && ! empty ( $ssl_info ['version'] )))
return;
$version = $ssl_info ['version'];
$certificate = $ssl_info ['certificate'];
$fields = array (
_esc ( 'Issued to' ) => null,
_esc ( 'Common Name' ) => array (
'subject',
'CN' 
),
_esc ( 'Organization' ) => array (
'subject',
'O' 
),
_esc ( 'Organization Unit' ) => array (
'subject',
'OU' 
),
_esc ( 'Issued by' ) => null,
_esc ( 'Common Name ' ) => array (
'issuer',
'CN' 
),
_esc ( 'Organization ' ) => array (
'issuer',
'O' 
),
_esc ( 'Organization Unit ' ) => array (
'issuer',
'OU' 
),
_esc ( 'Country' ) => array (
'issuer',
'C' 
),
_esc ( 'State' ) => array (
'issuer',
'ST' 
),
_esc ( 'Location' ) => array (
'issuer',
'L' 
),
_esc ( 'Period of valability' ) => null,
_esc ( 'Begins on' ) => 'start_date',
_esc ( 'Expires on' ) => 'expire_date',
_esc ( 'Status' ) => 'status' 
);
$ssl_text = '<table><tr><td><img src="' . plugins_url_wrapper ( "img/ssl-icon.png", IMG_PATH ) . '"></td><td style="font-weight: bold;font-size:1.5em">' . _esc ( 'SSL Server Certificate' ) . '</td></tr>' . (empty ( $ssl_hint ) ? '' : ('<tr><td colspan="2" style="color:blue">' . $ssl_hint . '</td></tr>')) . '</table>';
$ssl_text .= '<table>';
if (! empty ( $version ))
$ssl_text .= '<tr><td style="font-weight:bold" colspan="2">' . _esc ( 'SSL connection' ) . '</td><td>:</td><td>' . $version . '</td></tr>';
foreach ( $fields as $key => $value )
if (empty ( $value ))
$ssl_text .= '<tr><td colspan="4" style="font-weight: bold">' . $key . ':</td></tr>';
else {
if (is_array ( $value )) {
$sufix = ' (' . $value [1] . ')';
$data = isset ( $certificate [$value [0]] [$value [1]] ) ? $certificate [$value [0]] [$value [1]] : '';
} else {
$sufix = '';
$data = isset ( $certificate [$value] ) ? $certificate [$value] : '';
}
if (! empty ( $data ))
$ssl_text .= '<tr><td>&nbsp;</td><td>' . $key . $sufix . '</td><td>:</td><td>' . $data . '</td></tr>';
}
$ssl_text .= '</table>';
bindInfo2JavaScript ( $element, $ssl_text, $java_scripts );
}
function bindSSHInfo($element, $ssh_info, &$java_scripts, $ssh_hint = null) {
if (! (is_array ( $ssh_info ) && isset ( $ssh_info ['fingerprint'] ) && ! empty ( $ssh_info ['auth_method'] )))
return;
$fields = array (
'fingerprint' => _esc ( 'SSH MD5 fingerprint' ),
'auth_method' => _esc ( 'SSH authentication methods' ),
'public_key' => _esc ( 'SSH public key' ),
'private_key' => _esc ( 'SSH private key' ),
'status' => _esc ( 'Connection status' ) 
);
$ssh_text = '<table><tr><td><img src="' . plugins_url_wrapper ( "img/ssl-icon.png", IMG_PATH ) . '"></td><td style="font-weight: bold;font-size:1.5em">' . _esc ( 'SSH Server Connection' ) . '</td></tr>' . (empty ( $ssh_hint ) ? '' : ('<tr><td colspan="2" style="color:blue">' . $ssh_hint . '</td></tr>')) . '</table>';
$ssh_text .= '<table>';
foreach ( $fields as $key => $value ) {
$ssh_text .= '<tr><td colspan="2" style="font-weight: bold">' . $value . ':</td></tr>';
$ssh_text .= '<tr><td>&nbsp;</td><td>' . addslashes ( str_replace ( PHP_EOL, '<br>', $ssh_info [$key] ) ) . '</td></tr>';
}
$ssh_text .= '</table>';
bindInfo2JavaScript ( $element, $ssh_text, $java_scripts );
}
?>
