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
 * @version : 0.2.3-8 $
 * @commit  : 010da912cb002abdf2f3ab5168bf8438b97133ea $
 * @author  : Eugen Mihailescu eugenmihailescux@gmail.com $
 * @date    : Tue Feb 16 21:44:02 2016 UTC $
 * @file    : functions.php $
 * 
 * @id      : functions.php | Tue Feb 16 21:44:02 2016 UTC | Eugen Mihailescu eugenmihailescux@gmail.com $
*/

namespace MyBackup;

define( 
__NAMESPACE__.'\\JS_INJECT_COMMENT', 
PHP_EOL . '/* inject our runtime generated local (%s) functions into the global application namespace */' . PHP_EOL );
! defined( __NAMESPACE__.'\\SORT_NATURAL' ) && define( __NAMESPACE__.'\\SORT_NATURAL', SORT_STRING );
function getDashboardTabs() {
global $dashboard_tabs, $TARGET_NAMES, $registered_targets;
$result = array();
foreach ( $dashboard_tabs as $target_id )
$result[$TARGET_NAMES[$target_id]] = isset( $registered_targets[$target_id] ) ? $registered_targets[$target_id]['title'] : $TARGET_NAMES[$target_id];
return $result;
}
function getSelectedTab() {
global $registered_tab_redirects;
$oauth_tabs = array( 'dropbox', 'google' );
foreach ( $oauth_tabs as $tab_name )
if ( isset( $_SESSION ) && isset( $_SESSION[$tab_name . '_auth'] ) && TRUE === $_SESSION[$tab_name . '_auth'] )
return $tab_name;
$tab = ! isset( $_GET['tab'] ) ? getDefaultTab() : $_GET['tab'];
ksort( $registered_tab_redirects );
foreach ( $registered_tab_redirects as $callable ) {
if ( is_callable( $callable ) ) {
$redirect_to = call_user_func( $callable, $tab );
if ( ! empty( $redirect_to ) )
return $redirect_to;
}
}
return $tab;
}
function getSelectedTabGrp( $default ) {
return isset( $_GET['tab'] ) ? ( ! isset( $_GET['gr'] ) ? $default : $_GET['gr'] ) : null;
}
function getLicenseId( &$license, $license_file ) {
global $java_scripts, $settings, $TARGET_NAMES;
if ( is_array( $license ) ) {
$license_id = array_keys( $license );
$license_id = $license_id[0];
if ( WPMYBACKUP_ID != $license_id ) {
if ( isset( $license['last_error'] ) && is_numeric( $license['last_error'] ) )
$last_error = intval( $license['last_error'] );
else {
$last_error = time();
$license['last_error'] = $last_error;
file_put_contents( $license_file, json_encode( $license ) );
}
$remainig_time = INVALID_LICENSE_LIFESPAN * SECDAY + $last_error - time();
if ( $remainig_time > 0 ) {
$title = _esc( 'Invalid license' );
$body = empty( $settings['email'] ) ? '' : sprintf( 
_esc( 'I have already sent a notification email to %s' ), 
sprintf( '<br><a href=\'mailto:%s\'>%s</a>', $settings['email'], $settings['email'] ) );
$body = _esc( 
'Your license is invalid. If you changed/reconfigured recently your<br>system then this is somehow expected.' ) .
$body . '<br>';
$body .= sprintf( 
_esc( 
'You have %s days (ie. until %s) to fix this problem.<br>After that the program is automatically deactivated without warning.' ), 
getSpanE( INVALID_LICENSE_LIFESPAN, 'red', 'bold' ), 
date( DATETIME_FORMAT, time() + $remainig_time ) ) . '<br>';
$body .= sprintf( 
'<p style=\'font-weight:bold\'>%s</p><div class=\'hintbox\' style=\'padding-bottom:0px\'>', 
_esc( 'How to fix it?' ) );
$unlink_str = '<ol><li><b>' . _esc( 'unlink' ) . '</b>';
$auth_str = '</li><li><b>' . _esc( 'authorize' ) . '</b>';
$thanks_str = '<br><p style=\'font-weight:bold\'>' . _esc( 'Thank you for using our product!' ) . '</p>';
$body .= sprintf( 
_esc( 
'All you have to do (now or then) is to reactivate your %s, ie.%s the license%s it again%sWe are sorry for this inconvenient and I hope we haven\' created<br>to much trouble for you.%s' ), 
getTabAnchor( APP_LICENSE ), 
$unlink_str, 
$auth_str, 
'</li></ol></div><br>', 
$thanks_str );
$java_scripts[] = sprintf( 'parent.popupError("%s","%s");', $title, $body );
} else {
if ( file_exists( $license_file ) )
unlink( $license_file );
del_session_var( 'license_activated' );
}
}
} else
$license_id = null;
return $license_id;
}
function sanitizeYAYUI() {
global $java_scripts, $java_scripts_load, $java_scripts_beforeunload, $java_scripts_unload, $chart_script;
file_put_contents(__FILE__.'.log', print_r($java_scripts,1).PHP_EOL);
$array_sort_unique = function ( &$array ) {
ksort( $array, SORT_NATURAL );
$array = array_unique( $array );
ksort( $array, SORT_NATURAL );
};
$array_sort_unique( $java_scripts );
$array_sort_unique( $java_scripts_load );
$array_sort_unique( $java_scripts_beforeunload );
$array_sort_unique( $java_scripts_unload );
$array_sort_unique( $chart_script );
file_put_contents(__FILE__.'.log', print_r($java_scripts,1).PHP_EOL,8);
$yayui = new YayuiCompressor();
if ( ! ( empty( $java_scripts ) && empty( $java_scripts_load ) && empty( $java_scripts_beforeunload ) &&
empty( $java_scripts_unload ) && empty( $chart_script ) ) ) {
$js = '("undefined"==typeof parent)&&window.location.reload(true);';
$js .= sprintf( 
PHP_EOL . 'parent.ajaxurl=%s;' . PHP_EOL, 
! is_wp() ? '"' . getAsyncRunURL() . '"' : 'window.ajaxurl' );
if ( ! empty( $chart_script ) ) {
$js .= implode( PHP_EOL, array_values( $chart_script ) ) . PHP_EOL;
}
if ( ! empty( $java_scripts ) )
$js .= implode( PHP_EOL, $java_scripts ) . PHP_EOL;
if ( ! empty( $java_scripts_load ) )
$js .= 'parent._addEventListener(window,(parent.isNull(parent.ie,10)<9?"on":"")+"load",function(){' .
implode( '', $java_scripts_load ) . '});' . PHP_EOL;
if ( ! empty( $java_scripts_beforeunload ) )
$js .= 'parent._addEventListener(window,parent.isNull(parent.ie,10)<9?"on":"")+"beforeunload",function(){' .
implode( '', $java_scripts_beforeunload ) . '});' . PHP_EOL;
if ( ! empty( $java_scripts_unload ) )
$js .= 'parent._addEventListener(window,parent.isNull(parent.ie,10)<9?"on":"")+"unload",function(){' .
implode( '', $java_scripts_unload ) . '});' . PHP_EOL;
$js = '<script>' . sprintf( JS_INJECT_COMMENT, 'page' ) .
'window.jsMyBackup.local=(function(window,parent,undefined){' . $js . '})(this,window.jsMyBackup);' . PHP_EOL .
'</script>' . PHP_EOL;
$js = ( ! empty( $chart_script ) ? "<script src=\"https://www.google.com/jsapi?autoload={'modules':[{'name':'visualization','version':'1','packages':['corechart','gauge']}]}\"></script>" .
PHP_EOL : '' ) . $js;
if ( ! defined( __NAMESPACE__."\\YAYUI_HANDLER" ) && YAYUI_COMPRESS && ( "YAYUI_HANDLER" ) &&
( empty( $_GET ) || empty( $_GET['noyayui'] ) ) ) {
$js = $yayui->streamCompress( $js );
}
$section_name = 'page JavaScript';
insertHTMLSection( $section_name );
echo $js;
insertHTMLSection( $section_name, true );
}
return $yayui;
}
function insertDebugScript( $enclosed_script = false ) {
if ( ! ( defined( __NAMESPACE__.'\\DEBUG_STATUSBAR' ) && DEBUG_STATUSBAR ) )
return '';
global $java_scripts;
$section_name = 'Debug Statusbar JavaScript';
$script = insertHTMLSection( $section_name, false, false, ! $enclosed_script );
$signature = sprintf( JS_INJECT_COMMENT, 'debug' );
$script .= $enclosed_script ? '<script type="text/javascript">' . $signature .
'window.jsMyBackup.local=(function(window,parent,undefined){' . PHP_EOL : $signature;
$script .= '("undefined"==typeof parent)&&window.location.reload(true);';
$script .= 'Date.now = Date.now || function() { return +new Date; };';
$script .= "(function(){var el=document.getElementById('notification_debug'),doc=document.documentElement.innerHTML,doc_len=doc.length,dom_ready_time=(Date.now()-window.page_start_loading)/1000,i,yayui='';if(document.children)for(i=0;i<document.children.length;i+=1)if(document.childNodes[i].textContent){var cmt=document.childNodes[i].textContent.match(/.*minified by YAYUI[^\d]+([\d\.]+%)\((\d+).*/);if(cmt){yayui='YAYUI: '+cmt[1]+'(ie. '+cmt[2]+' bytes); ';break;}}if(el)el.innerHTML='PHP debug:" .
( defined( __NAMESPACE__.'\\PHP_DEBUG_ON' ) && PHP_DEBUG_ON ? 'on' : 'off' ) . "; Curl debug:" .
( defined( __NAMESPACE__.'\\CURL_DEBUG' ) && CURL_DEBUG ? 'on' : 'off' ) . "; Charts debug:" .
( defined( __NAMESPACE__.'\\STATISTICS_DEBUG' ) && STATISTICS_DEBUG ? 'on' : 'off' ) .
"'+el.innerHTML.replace(/\((.*)\)/g,'($1; DOMlength: '+doc_len+' bytes; '+yayui+'DOMready: '+dom_ready_time.toFixed(3)+'s => '+(doc_len/dom_ready_time/1024).toFixed(1)+'KiB/s)');})();parent._addEventListener(window,'load',function(){var el=document.getElementById('notification_debug');if(el && el.innerHTML){var m=el.innerHTML.match(/\([\w\s]+:([\d\.]+)s/i); m=m?m[1]:'0';var server_load_time=parseFloat(m),window_load_time=(Date.now()-window.page_start_loading)/1000,total_time=server_load_time+window_load_time;el.innerHTML=el.innerHTML.replace(/\((.*)\)/,'($1; page loaded: '+window_load_time.toFixed(3)+'s => <b> total time:'+total_time.toFixed(3)+'s</b>)');el.style.display='';}},false);";
$enclosed_script &&
$script .= '})(this,window.jsMyBackup);' . PHP_EOL . 'window.jsnspace=window.jsMyBackup;' . PHP_EOL . '</script>';
$script .= insertHTMLSection( $section_name, true, false, ! $enclosed_script );
$java_scripts[] = $script;
return $script;
}
function insertPostboxJS() {
global $java_scripts;
$img_path = plugins_url_wrapper( 'img/', IMG_PATH );
$java_scripts[] = "var items = document.querySelectorAll('div.postbox'),
i, j, div;
for (i = 0; i < items.length; i += 1) {
var h3s = items[i].getElementsByTagName('h3'),
h4s = items[i].getElementsByTagName('h4');
parent.addHeaderToggle(h3s, false,'$img_path');
parent.addHeaderToggle(h4s, true,'$img_path');}";
}
function insertWarningBox( $cookie_name, $title, $message, $icon, $buttons, $force = false ) {
if ( isset( $_COOKIE[$cookie_name] ) && ! $force )
return;
$format = "jsMyBackup.setCookie('$cookie_name','%s',%d); var el=document.getElementById('{$cookie_name}_box');el.style.position='relative';el.style.top=-100+'px';setTimeout(function(){el.style.display='none';},750);";
$accept[false] = sprintf( $format, 'false', COOKIE_NOACCEPT_MAXAGE );
$accept[true] = sprintf( $format, 'true', COOKIE_ACCEPT_MAXAGE );
ob_start();
?>
<!-- Cookie warning (see http://ec.europa.eu/ipg/basics/legal/cookies/index_en.htm#section_2) -->
<div id="<?php echo $cookie_name;?>_box"
style="padding: 10px; top: 0; background-color: #ffc; border: 1px solid #c0c0c0; border-radius: 5px; transition: all 0.75s ease-in;">
<table style="width: 100%">
<tr>
<td style="width: 0"><img src="<?php echo $icon;?>"></td>
<td style="font-weight: bold; font-size: 1.5em;"><?php echo $title;?></td>
<td><?php echo $message;?></td>
<td style="text-align: right;"><input type="button" style="margin: 2px;"
class="button-primary" value="<?php echo $buttons[true];?>"
onclick="<?php echo $accept [true];?>"> <input type="button" class="button"
value="<?php echo $buttons[false];?>" style="margin: 2px;"
onclick="<?php echo $accept [false];?>"></td>
</tr>
</table>
</div>
<?php
return ob_get_clean();
}
function insertTabContent( $min_container_height ) {
global $registered_targets, $license, $license_id, $settings, $alert_message_obj, $has_postbox;
global $container_shape, $tab_orientation, $container_shape;
global $java_scripts, $java_scripts_load, $java_scripts_beforeunload, $java_scripts_unload, $chart_script;
global $PROGRESS_PROVIDER, $TARGET_NAMES, $REGISTERED_BACKUP_TABS;
echo "<div id='content-container' class='content-container $tab_orientation $container_shape' style='min-height:" .
( $min_container_height ) . "px;'>";
$outer_section_name = 'active menu content';
insertHTMLSection( $outer_section_name );
echo '<form method="POST" enctype="multipart/form-data" id="wpmybackup_admin_form" action="' .
$_SERVER['REQUEST_URI'] . '">' . PHP_EOL;
$active_tab = getSelectedTab();
if ( ! in_array( $active_tab, $TARGET_NAMES ) ) {
$include_tab_file = chkIncludeTab( getDashboardTabs(), $active_tab, 'custom' );
if ( false !== $include_tab_file )
include_once $include_tab_file;
} else
echoTargetEditor( $active_tab );
echo PHP_EOL;
echo '<input type="hidden" name="action" value="submit_options">' . PHP_EOL;
echo '<input type="hidden" name="nonce" value="' . wp_create_nonce_wrapper( 'submit_options' ) . '">' . PHP_EOL;
echo '<input type="hidden" name="locked_settings" id="locked_settings" value="' .
( strToBool( $settings['locked_settings'] ) ? 1 : 0 ) . '">' . PHP_EOL;
echo '</form></div>';
insertHTMLSection( $outer_section_name, true );
}
function insertTabMenus( $banner = '' ) {
global $tab_orientation, $tab_position, $menu_shape, $settings;
echo "<div id='tab-container' class='tab-container $tab_orientation $tab_position $menu_shape'>";
echo '<ul id="navlist">';
$sel_tab = getSelectedTab();
$visible_tabs = 0;
foreach ( getDashboardTabs() as $tab => $name ) {
$class = ( $tab == $sel_tab ) ? ' active' : '';
$href = stripUrlParams( 
getTabLink( $tab ), 
array( 
'gr', 
'paypal_cancel', 
'paypal_thanks', 
'wire_thanks', 
'card_thanks', 
'card_cancel', 
'access_token', 
'token_type', 
'expires_in', 
'refresh_token', 
'oauth_token_created', 
'state', 
'api_key', 
'error_message', 
'error_code', 
'dropbox_unlink', 
'google_unlink', 
'oautherror', 
'decrypt_status', 
'job_id', 
'nocheck', 
'installed' ) );
if ( $tab == 'stats' && ! $settings['history_enabled'] )
continue;
echo "<li" . ( empty( $class ) ? '' : " class='$class'" ) . ( 'notification' == $tab ? ' style="' .
( 'vertical' == $tab_orientation ? 'border-left-color' : 'border-top-color' ) . ': red;"' : '' ) .
"><a href='" . $href . "'>$name</a>";
strlen( $name ) > 16 && $visible_tabs += 16 / 38; 
$visible_tabs++;
}
echo $banner;
echo '</ul></div>';
return $visible_tabs;
}
function insertFooterBar() {
global $tab_orientation, $settings;
$export = "jsMyBackup.post(jsMyBackup.ajaxurl,{action:\\'export_settings\\',format:\\'%s\\',nonce:\\'" .
wp_create_nonce_wrapper( 'export_settings' ) . "\\'});";
$section_name = 'Update/Reset buttons';
insertHTMLSection( $section_name );
$locked = strToBool( $settings['locked_settings'] );
$disabled = $locked ? ' disabled ' : '';
?>
<table class=<?php echo "'btn-container $tab_orientation' ";?>
id='btn-container'>
<tr>
<td><input type="button" name='update_wpmybackup_option'
<?php echo $disabled;?> class="button-primary"
value="<?php _pesc('Save settings');?>"
onclick="jsMyBackup.submitOptions(this,0);"
title='<?php _pesc('Click to save these options now. It saves also when you click the Run Backup, Read or Download buttons');?>'></td>
<td><input type="button" name='reset_wpmybackup_option'
<?php echo $disabled;?> class="button-primary"
value="<?php _pesc('Reset defaults');?>"
onclick="<?php echo "jsMyBackup.popupConfirm('"._esc('Settings removal confirm')."','"._esc('Are you really,really sure you want to reset &lt;b&gt;ALL options from ALL TABS&lt;/b&gt; to their factory defaults?')."','#ff2c00',{'"._esc('Yes, reset them!')."':'window.onbeforeunload=null;jsMyBackup.post(jsMyBackup.this_url,{action:\'reset_defaults\',nonce:\'".wp_create_nonce_wrapper('reset_defaults')."\'});jsMyBackup.removePopupLast();','"._esc('Cancel')."':null});";?>"
title='<?php _pesc('Click to reset these options to factory defaults.');?>'></td>
<td><input type="button" name='dwl_wpmybackup_option' <?php echo $disabled;?>
class="button-primary" value="<?php _pesc('Export settings');?>"
onclick="<?php echo "jsMyBackup.popupPrompt('"._('Export settings')."','"._esc('Choose the format to export the current settings to a file on your local system.').'<br>'._esc('The .ini format prepends each option with a comment/description.')."',null,{'"._esc('XML format')."':'".sprintf($export,'xml')."','"._esc('JSON format')."':'".sprintf($export,'json')."','"._esc('.ini format')."':'".sprintf($export,'ini')."','"._esc('Cancel')."':null});";?>"></td>
<td><input type="submit" class="button-primary"
title="<?php _pesc('Allow/disallow changes of settings');?>"
id="<?php echo $locked?'btn_unlock_settings':'btn_lock_settings';?>"
onclick="document.getElementById('locked_settings').value='<?php echo !$locked?1:0;?>';jsMyBackup.submitOptions(this,0);"
value="<?php echo '&nbsp;&nbsp;&nbsp;'.($locked?_esc('Unlock'):_esc('Lock'));?>"></td>
<td>
<div class="spin" id="spin_save"></div>
</td>
<td><div class="spin_hint" id="hint_save"><?php _pesc('Please wait while loading...');?></div></td>
</tr>
</table>
</div>
<?php
insertHTMLSection( $section_name, true );
}
function insertHeaderBar( $title, $title_desc ) {
global $java_scripts, $TARGET_NAMES;
$title_desc = str_replace( "'", "\'", $title_desc );
include_once INC_PATH . 'header-bar.php';
}
?>