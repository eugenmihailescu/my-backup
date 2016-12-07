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
 * @version : 0.2.3-37 $
 * @commit  : 56326dc3eb5ad16989c976ec36817cab63bc12e7 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Wed Dec 7 18:54:23 2016 +0100 $
 * @file    : settings.php $
 * 
 * @id      : settings.php | Wed Dec 7 18:54:23 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

if ( ! defined( __NAMESPACE__.'\\WPMYBACKUP_OPTION_NAME' ) )
define( __NAMESPACE__."\\WPMYBACKUP_OPTION_NAME", strtolower( str_replace( ' ', '_', PLUGIN_EDITION ) ) . '_options' );
if ( ! defined( __NAMESPACE__."\\ALLOW_ONLY_WP" ) ) 
define( __NAMESPACE__."\\ALLOW_ONLY_WP", true ); 
if ( ! defined( __NAMESPACE__.'\\WPMYBACKUP_ROOT' ) )
define( __NAMESPACE__.'\\WPMYBACKUP_ROOT', ALT_ABSPATH );
require_once FUNCTIONS_PATH . 'utils.php';
$tab_orientation = 0 == TAB_ORIENTATION ? 'horizontal' : 'vertical';
$tab_position = 'vertical' == $tab_orientation || 0 == TAB_POSITION ? '' : 'bottom';
$container_shape = 0 == CORNER_SHAPE ? '' : 'rounded-container';
$menu_shape = 0 == CORNER_SHAPE ? '' : ( 'vertical' == $tab_orientation ? 'vrounded' : ( 'bottom' == $tab_position ? 'hrounded-bottom' : 'hrounded-top' ) );
$java_scripts[] = 'parent._addEventListener(window,"beforeunload",function (e) {if(!e) e = window.event;if (true==parent.globals.JOB_RUNNING){e.cancelBubble = true;e.returnValue = "The backup (or alike) is running. Are you sure you want to abort and leave?";if (e.stopPropagation) {e.stopPropagation();if (e.preventDefault)e.preventDefault();else e.returnValue = false;}}var params=parent.getAsyncSubmitFields(false,false,true);if(parent.globals.FORM_SAVING||0===params.length||parent.locked_settings())return;var ssa="supersede_action",ss=document.getElementsByName(ssa);if(ss && ss.length>0)params+="&"+ssa+"="+ss[0].value;params+="&action=auto_save&nonce=' .
wp_create_nonce_wrapper( 'auto_save' ) .
'";if(params.length>0)parent.asyncRunJob(parent.ajaxurl, params, null, null, null, 3, parent.dummy, -1, null);});'; 
$java_scripts[] = 'parent._addEventListener(window,"unload",function(e){if (true==parent.globals.JOB_RUNNING){parent.asyncRunJob(parent.ajaxurl, "action=abort_job&nonce=' .
wp_create_nonce_wrapper( 'abort_job' ) . '", null, null, null, 3, parent.dummy, -1, null);}});';
$java_scripts_load = array( 
'parent.globals.INITIAL_FIELDS=parent.getFieldValues();parent.globals.INITIAL_FIELDS["locked_settings"]=document.getElementById("locked_settings").value;parent.globals.PAGELOAD_INITIAL_FIELDS=parent.globals.INITIAL_FIELDS;' );
$chart_script = array();
$registered_targets = array();
$registered_settings = array();
$registered_ciphres = array();
$registered_tab_redirects = array();
function register_ciphres( $cipher_def ) {
global $registered_ciphres;
$registered_ciphres[$cipher_def['class']] = array( 'name' => $cipher_def['name'], 'items' => $cipher_def['items'] );
}
function register_settings( $callback ) {
global $registered_settings;
_is_callable( $callback ) && $registered_settings[] = $callback;
}
function getFactorySettings() {
global $factory_options;
$result = array( 'current_user_id' => get_current_user_id_wrapper() );
foreach ( $factory_options as $group => $group_options ) {
foreach ( $group_options as $key => $value )
$result = array_merge( $result, array( $key => $value[0] ) );
}
return $result;
}
function getFixedSettings() {
global $fixed_options;
$result = array();
if ( isset( $fixed_options ) )
foreach ( $fixed_options as $group => $group_options )
$result = array_merge( $result, $group_options );
return $result;
}
function repairSettings( $settings, $default_settings ) {
global $BACKUP_MODE;
$is_wp = is_wp();
$fixPathBackslashes = function ( $names ) use(&$settings ) {
foreach ( $names as $name )
if ( isset( $settings[$name] ) )
$settings[$name] = normalize_path( $settings[$name], true );
};
$resetProperties = function ( $names ) use(&$settings ) {
foreach ( $names as $prop_name )
if ( isset( $settings[$prop_name] ) )
unset( $settings[$prop_name] );
};
$fixRestrictedPaths = function ( $array ) use(&$settings ) {
$invalid = array();
foreach ( $array as $default => $names ) {
foreach ( $names as $name ) {
! isset( $settings[$name] ) || _dir_in_allowed_path( $settings[$name] ) || $invalid[$default][] = $name;
}
}
foreach ( $invalid as $default => $names ) {
foreach ( $names as $name ) {
$settings[$name] = $default;
}
}
return $invalid;
};
if ( isset( $settings['dir'] ) && ( empty( $settings['dir'] ) || ! _file_exists( $settings['dir'] ) ) )
$settings['dir'] = $default_settings['dir'];
$fixPathBackslashes( 
array( 'dir', 'wrkdir', 'cygwin', 'excludedirs', 'logdir', 'ftp_cainfo', 'ssl_cainfo', 'disk', 'disk_path_id' ) );
$resetProperties( array( 'run_backup', 'compression_benchmark', 'nonce', 'action' ) );
{
$safe_temp_path = dirname( LOG_DIR ) . DIRECTORY_SEPARATOR . 'systemp';
$chk_invalid_paths = array( 
$safe_temp_path => array( 'dir', 'wrkdir', 'cygwin' ), 
LOG_DIR => array( 'logdir', 'ftp_cainfo', 'ssl_cainfo' ) );
if ( $is_wp ) {
$upload_path = wp_get_upload_dir();
$upload_path = $upload_path['basedir'];
} else {
$upload_path = $safe_temp_path;
}
$upload_path .= DIRECTORY_SEPARATOR . WPMYBACKUP_LOGS . '_backups';
_is_dir( $upload_path ) || mkdir( $upload_path, 0770, true );
$chk_invalid_paths[$upload_path] = array( 'disk', 'disk_path_id' );
$invalid_paths = $fixRestrictedPaths( $chk_invalid_paths, $settings );
}
if ( ! empty( $invalid_paths ) ) {
_is_dir( $safe_temp_path ) || mkdir( $safe_temp_path, 0770, true );
$message = _esc( 
'The following options which are pointing to restricted paths (open_basedir is in effect) were automatically changed to:' ) .
'<ol class="alert-list">';
foreach ( $invalid_paths as $new_path => $affected_options ) {
if ( empty( $new_path ) )
continue;
$message .= '<li><strong>' . shorten_path( $new_path ) . '</strong><ol>';
foreach ( $affected_options as $option )
$message .= '<li>' . $option . '</li>';
$message .= '</ol></li>';
}
$message .= '</ol>';
$message .= readMoreHere( PHP_MANUAL_URL . 'ini.core.php#ini.open-basedir' );
add_alert_message( $message );
}
if ( ! isset( $settings['upload_max_chunk_size'] ) || intval( $settings['upload_max_chunk_size'] ) < 1 ) {
$min_chunk_size = 256; 
$settings['upload_max_chunk_size'] = $min_chunk_size;
$message = sprintf( 
_esc( 'The `Upload max chunk size` option has an invalid value. It was set automatically to %s' ), 
getHumanReadableSize( $min_chunk_size * 1024 ) );
add_alert_message( $message );
}
if ( $is_wp ) {
$settings['mysql_host'] = @constant( 'DB_HOST' ) ? DB_HOST : $settings['mysql_host'];
$settings['mysql_port'] = 3306;
$settings['mysql_user'] = @constant( 'DB_USER' ) ? DB_USER : $settings['mysql_user'];
$settings['mysql_pwd'] = @constant( 'DB_PASSWORD' ) ? DB_PASSWORD : $settings['mysql_pwd'];
$settings['mysql_db'] = @constant( 'DB_NAME' ) ? DB_NAME : $settings['mysql_db'];
$settings['mysql_charset'] = @constant( 'DB_CHARSET' ) ? DB_CHARSET : $settings['mysql_charset'];
$settings['mysql_collate'] = @constant( 'DB_COLLATE' ) ? DB_COLLATE : $settings['mysql_collate'];
$settings['mysql_host'] = @constant( 'DB_HOST' ) ? DB_HOST : $settings['mysql_host'];
$settings['mysql_host'] = @constant( 'DB_HOST' ) ? DB_HOST : $settings['mysql_host'];
}
if ( $port_found = preg_match( '/([^:]*):(\d+)/', $settings['mysql_host'], $matches ) ) {
$settings['mysql_host'] = $matches[1];
$settings['mysql_port'] = $matches[2];
$settings['mysql_socket'] = false;
} elseif ( $socket_found = preg_match( '/([^:]*):([._\-\d\w\\/\\\\]+)/', $settings['mysql_host'], $matches ) ) {
$settings['mysql_host'] = $matches[1];
$settings['mysql_socket'] = $matches[2];
$settings['mysql_port'] = false;
}
isset( $BACKUP_MODE[$settings['mode']] ) || $settings['mode'] = BACKUP_MODE_FULL;
return $settings;
}
function loadSettings( $settings = null ) {
global $registered_settings;
$factory_defaults = getFactorySettings();
$fixed_defaults = getFixedSettings();
$registered_defaults = array();
$settings = isset( $settings ) ? $settings : get_option_wrapper( WPMYBACKUP_OPTION_NAME, $factory_defaults );
$old_settings = $settings;
is_array( $old_settings ) || $old_settings = array();
empty( $settings ) && $settings = array();
$settings = $fixed_defaults + $settings;
foreach ( $registered_settings as $callback )
_is_callable( $callback ) &&
$registered_defaults = _call_user_func( $callback, $settings ) + $registered_defaults;
$settings = $registered_defaults + $settings; 
$settings = $settings + $factory_defaults; 
$settings = repairSettings( $settings, $fixed_defaults + $factory_defaults ); 
$changes = array_diff_assoc( $old_settings, $settings );
if ( ! empty( $changes ) ) {
$ignore_changes = array( 'action' => null, 'nonce' => null );
$remaining_changes = array_diff_key( $changes, $ignore_changes );
if ( ! empty( $remaining_changes ) ) {
submit_options( null, $settings );
}
}
return $settings;
}
function getSettings( $group = null ) {
global $settings, $factory_options;
if ( ! isset( $settings ) )
$settings = loadSettings();
if ( null == $group )
return $settings;
$result = array();
if ( isset( $factory_options[$group] ) )
foreach ( $factory_options[$group] as $key => $value )
$result[$key] = isset( $settings[$key] ) ? $settings[$key] : $value[0];
return $result;
}
function submit_options( $log_file = null, $settings = null, $forcebly = false ) {
if ( isset( $_REQUEST['action'] ) && 'test_dwl' == $_REQUEST['action'] ) {
return;
}
global $invalid_paths, $java_scripts;
$default_settings = getFixedSettings() + getFactorySettings();
$hijacked_action = isset( $_POST['supersede_action'] ) &&
in_array( $_POST['action'], explode( ',', $_POST['supersede_action'] ) );
if ( null !== $log_file && ! $forcebly && ( empty( $_POST ) || $hijacked_action ) ) {
$log_file->writelnLog( 
sprintf( "%s - %s", date( DATETIME_FORMAT ), _esc( '[!] action won`t trigger ; it`s probably hijacked' ) ) );
return;
}
$settings = empty( $settings ) ? ( empty( $_POST ) ? array() : $_POST ) : $settings; 
$default_settings = getFixedSettings() + getFactorySettings();
$old_settings = get_option_wrapper( WPMYBACKUP_OPTION_NAME, $default_settings );
is_array( $old_settings ) || $old_settings = array();
$java_scripts = array_merge( $java_scripts, beforeCommitOptions( $old_settings, $settings ) );
$settings = array_merge( $old_settings, $settings );
update_option_wrapper( WPMYBACKUP_OPTION_NAME, $settings );
}
require_once CONFIG_PATH . 'default-target-tabs.php';
require_once CONFIG_PATH . 'factory-config.php';
require_once CONFIG_PATH . 'post-config.php';
?>