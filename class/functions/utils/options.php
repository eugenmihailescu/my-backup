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
 * @file    : options.php $
 * 
 * @id      : options.php | Mon Dec 28 17:57:55 2015 +0100 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

function getArgIndex( $arg, &$opt_array ) {
$found = - 1;
$allow_value = false;
for ( $i = 0; $i < count( $opt_array ); $i++ ) {
if ( substr( $opt_array[$i], 0, min( strlen( $opt_array[$i] ), strlen( $arg ) ) ) == $arg ) {
$found = $i;
$allow_value = substr( $opt_array[$i], - 1 ) == ':';
break;
}
}
return array( $found, $allow_value );
}
function getArg( $longName, $args ) {
global $short_opts, $long_opts;
$result = null;
$opt_name = $longName;
$key = array_search( $longName, str_replace( ':', '', $long_opts ) );
if ( false !== $key ) {
$optional = false !== strpos( $long_opts[$key], '::' );
$required = ! $optional && ( strpos( $long_opts[$key], ':' ) === strlen( $long_opts[$key] ) - 1 );
$noarg = ! ( $optional || $required );
if ( $key < count( $short_opts ) ) {
$shortName = str_replace( ':', '', $short_opts[$key] );
if ( isset( $args[$shortName] ) )
$opt_name = $shortName;
}
if ( isset( $args[$opt_name] ) )
$result = $args[$opt_name];
if ( $required && 0 === strlen( $result ) )
$result = null;
elseif ( $noarg && is_string( $result ) && 0 === strlen( $result ) )
$result = true;
}
return $result;
}
function getParam( $options, $longName, $default = null ) {
if ( empty( $options ) )
return $default;
if ( isset( $options[$longName] ) && is_array( $options[$longName] ) && count( $options[$longName] ) > 1 )
$options[$longName] = $options[$longName][0];
$value = isset( $options[$longName] ) ? $options[$longName] : $default;
$result = ( null !== $value ? $value : $default );
return $result;
}
function getArgFromOptions( $settings = null ) {
global $COMPRESSION_APPS;
if ( null == $settings )
$settings = get_option_wrapper( WPMYBACKUP_OPTION_NAME, false );
$args = array();
if ( is_array( $settings ) )
foreach ( $settings as $key => $value ) {
if ( 'compression_type' == $key ) {
if ( isset( $COMPRESSION_APPS[$value] ) )
$args[$COMPRESSION_APPS[$value]] = '';
else
$args[$COMPRESSION_APPS[BZ2]] = ''; 
} else {
if ( 'compression_level' == $key ) {
if ( ! isset( $args[$value] ) )
$args[$value] = "";
} 				
else {
if ( ! isset( $args[$key] ) )
$args[$key] = $value;
}
}
}
return $args;
}
function filterArgs( &$args, $short_options = true ) {
global $short_opts, $long_opts;
$pattern = array( true => "^-(\w)$", false => "^--(\w+)(=*)(.+)*$" );
$opt_array = $short_options ? $short_opts : $long_opts;
if ( ! isset( $args[0] ) )
$array = array_keys( $args );
else
$array = $args;
$i = 0;
$result = array();
while ( $i < count( $array ) ) {
$a = $array[$i];
if ( preg_match( '/' . $pattern[$short_options] . '/', $a, $matches ) ) {
$param = $matches[1];
$found = getArgIndex( $param, $opt_array );
if ( $found[0] >= 0 ) {
if ( $found[1] ) {
if ( $short_options ) {
$result["$param"] = $array[1 + $i];
$i++;
} else
$result["$param"] = $args["--$param"];
} else
$result["$param"] = true;
}
}
$i++;
}
return $result;
}
function buildArgs( &$args ) {
$short_args = filterArgs( $args, true );
$long_args = filterArgs( $args, false );
return addArrays( $short_args, $long_args ); 
}
function load_local_option() {
$result = array();
if ( file_exists( LOCAL_OPTION_DB_PATH ) )
$result = json_decode( file_get_contents( LOCAL_OPTION_DB_PATH ), true );
if ( ! is_array( $result ) )
throw new MyException( sprintf( _esc( 'File "%s" is corrupted.' ), LOCAL_OPTION_DB_PATH ) );
return $result;
}
function get_option_local_db( $option, $default ) {
$db = load_local_option();
if ( ! empty( $db ) && isset( $db[$option] ) )
return $db[$option];
else
return $default;
}
function update_option_local_db( $option, $new_value ) {
$db = load_local_option();
if ( $key = array_search( $option, $db ) )
$db[$key] = $new_value;
else
$db[$option] = $new_value;
$dir = _dirname( LOCAL_OPTION_DB_PATH );
is_dir( $dir ) || mkdir( $dir );
return true === file_put_contents( LOCAL_OPTION_DB_PATH, json_encode( $db ) );
}
function delete_option_local_db( $option ) {
$db = load_local_option();
if ( $key = array_search( $option, $db ) ) {
unset( $db[$key] );
return true === file_put_contents( LOCAL_OPTION_DB_PATH, json_encode( $db ) );
} else
return false;
}
function afterSettingsLoad( &$settings, $ajax = false ) {
global $java_scripts;
if ( defined( __NAMESPACE__.'\\PHP_DEBUG_ON' ) ) 
return;
define( __NAMESPACE__.'\\PHP_DEBUG_ON', strToBool( $settings['debug_on'] ) ); 
define( __NAMESPACE__.'\\CURL_DEBUG', strToBool( $settings['curl_debug_on'] ) ); 
define( __NAMESPACE__.'\\YAYUI_COMPRESS', strToBool( $settings['yayui_on'] ) ); 
define( __NAMESPACE__.'\\STATISTICS_DEBUG', strToBool( $settings['stats_debug_on'] ) ); 
define( __NAMESPACE__.'\\DEBUG_STATUSBAR', strToBool( $settings['debug_statusbar_on'] ) ); 
define( __NAMESPACE__.'\\SMTP_DEBUG', strToBool( $settings['smtp_debug_on'] ) ); 
if ( ! $ajax ) {
isset( $_COOKIE['cookie_accept'] ) && $settings['cookie_accept_on'] = strToBool( $_COOKIE['cookie_accept'] );
$format = "setCookie('cookie_accept','%s',%s,true);";
if ( isset( $_POST['cookie_accept_on'] ) && ! strToBool( $_POST['cookie_accept_on'] ) &&
isset( $_COOKIE['cookie_accept'] ) && strToBool( $_COOKIE['cookie_accept'] ) ) {
$settings['cookie_accept_on'] = false;
$java_scripts[] = sprintf( $format, 'false', COOKIE_NOACCEPT_MAXAGE );
}
if ( isset( $_POST['cookie_accept_on'] ) && strToBool( $_POST['cookie_accept_on'] ) &&
( ! isset( $_COOKIE['cookie_accept'] ) || ! strToBool( $_COOKIE['cookie_accept'] ) ) ) {
$settings['cookie_accept_on'] = true;
$java_scripts[] = sprintf( $format, 'true', COOKIE_ACCEPT_MAXAGE );
}
}
}
function get_param( $key, &$old_settings, &$new_settings, $default = null ) {
return isNull( $new_settings, $key, isNull( $old_settings, $key, $default ) );
}
function param_changed( $key, &$old_settings, &$new_settings ) {
$result = ! isset( $old_settings[$key] ) && isset( $new_settings[$key] ); 
return $result ||
isset( $new_settings[$key] ) && isset( $old_settings[$key] ) && $old_settings[$key] != $new_settings[$key]; 
}
function settings_changed( $keys, &$old_settings, &$new_settings ) {
$changed = false;
is_string( $keys ) && $keys = array( $keys );
foreach ( $keys as $key )
if ( $changed = $changed || param_changed( $key, $old_settings, $new_settings ) )
break;
return $changed;
}
function beforeCommitOptions( &$old_settings, &$new_settings ) {
global $java_scripts;
$triggers = array( '_reset_SSL_cache', '_update_wp_debug', '_update_backup_schedule' );
foreach ( $triggers as $callback )
_is_callable( $callback ) &&
$java_scripts = array_merge( $java_scripts, _call_user_func( $callback, $old_settings, $new_settings ) );
}
function _reset_SSL_cache( $old_settings, $new_settings ) {
$reset_cert_cache = array( 
'webdav_ssl_cert_info' => array( 
'webdavhost', 
'webdav_cainfo', 
'ssl_cainfo', 
'ssl_ver', 
'ssl_chk_peer', 
'ssl_chk_host' ), 
'ftp_ssl_cert_info' => array( 
'ftphost', 
'ftpport', 
'ftppasv', 
'ftpproto', 
'ftp_cainfo', 
'ftp_ssl_chk_peer', 
'ssl_cainfo', 
'ssl_ver', 
'ssl_chk_peer', 
'ssl_chk_host' ), 
'ftp_ssh_cert_info' => array( 
'sshhost', 
'sshproto', 
'sshport', 
'ssh_publickey_file', 
'ssh_privkey_file', 
'ssh_privkey_pwd' ) );
foreach ( $reset_cert_cache as $cache_key => $value ) {
if ( isset( $_SESSION[$cache_key] ) && settings_changed( $value, $old_settings, $new_settings ) )
del_session_var( $cache_key );
}
return array();
}
function _update_backup_schedule( $old_settings, $new_settings ) {
$js_script = array();
if ( is_wp() ) {
$settings_trigger = array( 'schedule_wp_cron', 'schedule_enabled', 'schedule_grp', 'schedule_wp_cron_time' );
$wpcron_force_changed = settings_changed( 'schedule_wpcron_force', $old_settings, $new_settings );
$wpcron_alt_changed = settings_changed( 'schedule_wpcron_alt', $old_settings, $new_settings );
if ( $wpcron_force_changed || $wpcron_alt_changed ||
settings_changed( $settings_trigger, $old_settings, $new_settings ) ) {
$cron_type = get_param( 'schedule_grp', $old_settings, $new_settings, 'os_cron' );
$cron_schedule = get_param( 'schedule_wp_cron', $old_settings, $new_settings, 'os_cron' );
$schedule_wpcron_force = get_param( 'schedule_wpcron_force', $old_settings, $new_settings, false );
$schedule_wpcron_alt = get_param( 'schedule_wpcron_alt', $old_settings, $new_settings, false );
try {
$wp_config_update = array();
$wpcron_force_changed && $wp_config_update['DISABLE_WP_CRON'] = boolToStr( ! $schedule_wpcron_force );
$wpcron_alt_changed && $wp_config_update['ALTERNATE_WP_CRON'] = boolToStr( $schedule_wpcron_alt );
if ( count( $wp_config_update ) > 0 )
$js_script[] = update_wp_config( $wp_config_update );
$_logfile = new LogFile( JOBS_LOGFILE, $new_settings );
$is_activated = 'os_cron' != $cron_type &&
strToBool( get_param( 'schedule_enabled', $old_settings, $new_settings, false ) );
if ( $is_activated || false !== wp_next_scheduled( WPCRON_SCHEDULE_HOOK_NAME ) )
$result = change_schedule( 
$_logfile, 
$cron_schedule, 
$is_activated, 
isset( $new_settings['schedule_wp_cron_time'] ) &&
! empty( $new_settings['schedule_wp_cron_time'] ) ? strtotime( 
$new_settings['schedule_wp_cron_time'] ) : null );
} catch ( MyException $e ) {
$result = sprintf( "parent.popupError('%s','%s');", _esc( 'Error' ), $e->getMessage() );
}
false !== $result && $js_script[] = $result;
}
}
return $js_script;
}
function _update_wp_debug( $old_settings, $new_settings ) {
$result = array();
$wp_debug_on_changed = settings_changed( 'wp_debug_on', $old_settings, $new_settings );
if ( $wp_debug_on_changed ) {
$wp_debug_on = get_param( 'wp_debug_on', $old_settings, $new_settings, false );
$result[] = update_wp_config( array( 'WP_DEBUG' => boolToStr( $wp_debug_on ) ) );
if ( $wp_debug_on ) {
$wp_debug_constants = array( 'WP_DEBUG', 'WP_DEBUG_LOG', 'WP_DEBUG_DISPLAY', 'SCRIPT_DEBUG' );
foreach ( $wp_debug_constants as $constant )
! defined( $constant ) && define( $constant, true );
}
}
return $result;
}
?>