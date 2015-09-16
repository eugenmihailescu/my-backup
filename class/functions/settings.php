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
 * @file    : settings.php $
 * 
 * @id      : settings.php | Wed Sep 16 11:33:37 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;

if (! defined ( 'WPMYBACKUP_OPTION_NAME' ))
define ( "WPMYBACKUP_OPTION_NAME", 'wpmybackup_options' );
if (! defined ( "ALLOW_ONLY_WP" )) 
define ( "ALLOW_ONLY_WP", true ); 
if (! defined ( 'WPMYBACKUP_ROOT' ))
define ( 'WPMYBACKUP_ROOT', ALT_ABSPATH );
require_once FUNCTIONS_PATH . 'utils.php';
$tab_orientation = 0 == TAB_ORIENTATION ? 'horizontal' : 'vertical';
$tab_position = 'vertical' == $tab_orientation || 0 == TAB_POSITION ? '' : 'bottom';
$container_shape = 0 == CORNER_SHAPE ? '' : 'rounded-container';
$menu_shape = 0 == CORNER_SHAPE ? '' : ('vertical' == $tab_orientation ? 'vrounded' : ('bottom' == $tab_position ? 'hrounded-bottom' : 'hrounded-top'));
$java_scripts [] = 'parent._addEventListener(window,"beforeunload",function (e) {if(!e) e = window.event;if (true==parent.globals.JOB_RUNNING){e.cancelBubble = true;e.returnValue = "The backup (or alike) is running. Are you sure you want to abort and leave?";if (e.stopPropagation) {e.stopPropagation();if (e.preventDefault)e.preventDefault();else e.returnValue = false;}}var params=parent.getAsyncSubmitFields(false,false,true);if(parent.globals.FORM_SAVING||0===params.length||parent.locked_settings())return;var ssa="supersede_action",ss=document.getElementsByName(ssa);if(ss && ss.length>0)params+="&"+ssa+"="+ss[0].value;params+="&action=auto_save&nonce=' . wp_create_nonce_wrapper ( 'auto_save' ) . '";if(params.length>0)parent.asyncRunJob(parent.ajaxurl, params, null, null, null, 3, "__dummy__", -1, null);});'; 
$java_scripts [] = 'parent._addEventListener(window,"unload",function(e){if (true==parent.globals.JOB_RUNNING){parent.asyncRunJob(parent.ajaxurl, "action=abort_job&nonce=' . wp_create_nonce_wrapper ( 'abort_job' ) . '", null, null, null, 3, "__dummy__", -1, null);}});';
$java_scripts_load = array (
'parent.globals.INITIAL_FIELDS=parent.getFieldValues();parent.globals.INITIAL_FIELDS["locked_settings"]=document.getElementById("locked_settings").value;parent.globals.PAGELOAD_INITIAL_FIELDS=parent.globals.INITIAL_FIELDS;' 
); 
$chart_script = array ();
$registered_targets = array ();
$registered_settings = array ();
$registered_ciphres = array ();
$registered_tab_redirects = array ();
function register_ciphres($cipher_def) {
global $registered_ciphres;
$registered_ciphres [$cipher_def ['class']] = array (
'name' => $cipher_def ['name'],
'items' => $cipher_def ['items'] 
);
}
function register_settings($callback) {
global $registered_settings;
_is_callable ( $callback ) && $registered_settings [] = $callback;
}
function getFactorySettings() {
global $factory_options;
$result = array (
'current_user_id' => get_current_user_id_wrapper () 
);
foreach ( $factory_options as $group => $group_options ) {
foreach ( $group_options as $key => $value )
$result = array_merge ( $result, array (
$key => $value [0] 
) );
}
return $result;
}
function getFixedSettings() {
global $fixed_options;
$result = array ();
if (isset ( $fixed_options ))
foreach ( $fixed_options as $group => $group_options )
$result = array_merge ( $result, $group_options );
return $result;
}
function loadSettings($settings = null) {
global $registered_settings;
$factory_defaults = getFactorySettings ();
$fixed_defaults = getFixedSettings ();
$registered_defaults = array ();
$settings = isset ( $settings ) ? $settings : get_option_wrapper ( WPMYBACKUP_OPTION_NAME, $factory_defaults );
$settings = $fixed_defaults + $settings;
foreach ( $registered_settings as $callback )
_is_callable ( $callback ) && $registered_defaults = _call_user_func ( $callback, $settings ) + $registered_defaults;
$settings = $registered_defaults + $settings; 
$settings = $settings + $factory_defaults; 
count ( $registered_defaults ) > 0 && submit_options ( null, $settings );
return $settings;
}
function getSettings($group = null) {
global $settings, $factory_options;
if (! isset ( $settings ))
$settings = loadSettings ();
if (null == $group)
return $settings;
$result = array ();
if (isset ( $factory_options [$group] ))
foreach ( $factory_options [$group] as $key => $value )
$result [$key] = isset ( $settings [$key] ) ? $settings [$key] : $value [0];
return $result;
}
function submit_options($log_file = null, $settings = null, $forcebly = false) {
$default_settings = getFixedSettings () + getFactorySettings ();
$hijacked_action = isset ( $_POST ['supersede_action'] ) && in_array ( $_POST ['action'], explode ( ',', $_POST ['supersede_action'] ) );
if (null !== $log_file && ! $forcebly && (empty ( $_POST ) || $hijacked_action)) {
$log_file->writeLog ( sprintf ( "%s - %s\n", date ( DATETIME_FORMAT ), _esc ( '[!] action won`t trigger ; it`s probably hijacked' ) ) );
return;
}
$fixPathBackslashes = function ($names) use(&$settings) {
foreach ( $names as $name )
if (isset ( $settings [$name] ))
$settings [$name] = normalize_path ( $settings [$name], true );
};
$resetProperties = function ($names) use(&$settings) {
foreach ( $names as $prop_name )
if (isset ( $settings [$prop_name] ))
unset ( $settings [$prop_name] );
};
$settings = empty ( $settings ) ? $_POST : $settings; 
foreach ( array (
'nonce',
'action' 
) as $garbage )
if (isset ( $settings [$garbage] ))
unset ( $settings [$garbage] );
if (isset ( $settings ['dir'] ) && (empty ( $settings ['dir'] ) || ! file_exists ( $settings ['dir'] )))
$settings ['dir'] = $default_settings ['dir'];
$fixPathBackslashes ( array (
'wrkdir',
'cygwin',
'excludedirs',
'logdir' 
) );
$resetProperties ( array (
'run_backup',
'compression_benchmark' 
) );
$old_settings = get_option_wrapper ( WPMYBACKUP_OPTION_NAME, $default_settings );
beforeCommitOptions ( $old_settings, $settings );
$settings = array_merge ( $old_settings, $settings );
update_option_wrapper ( WPMYBACKUP_OPTION_NAME, $settings );
}
require_once CONFIG_PATH . 'default-target-tabs.php';
require_once CONFIG_PATH . 'factory-config.php';
require_once CONFIG_PATH . 'post-config.php';
?>
