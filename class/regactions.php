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
 * @version : 0.2.3-3 $
 * @commit  : 961115f51b7b32dcbd4a8853000e4f8cc9216bdf $
 * @author  : Eugen Mihailescu <eugenmihailescux@gmail.com> $
 * @date    : Tue Feb 16 15:27:30 2016 +0100 $
 * @file    : regactions.php $
 * 
 * @id      : regactions.php | Tue Feb 16 15:27:30 2016 +0100 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

require_once dirname( __DIR__ ) . '/config.php';
include_once FUNCTIONS_PATH . 'settings.php';
include_once LOCALE_PATH . 'locale.php';
ini_set( "error_log", ERROR_LOG );
define( __NAMESPACE__.'\\WP_AJAX_ACTION_PREFIX', 'wp_ajax_' );
define( __NAMESPACE__.'\\WP_MYBACKUP_ACTION_PREFIX', 'wpmybackup_' );
define( __NAMESPACE__.'\\AJAX_DISPATCH_FUNCTION', WP_MYBACKUP_ACTION_PREFIX . 'ajax' );
setLanguage( getSelectedLangCode() );
is_session_started(); 
check_is_logged(); 
$settings = loadSettings();
if ( ! defined( __NAMESPACE__.'\\DO_NOT_AFTER_SETTINGS' ) || ! DO_NOT_AFTER_SETTINGS )
afterSettingsLoad( $settings, true ); 
require_once CONFIG_PATH . 'default-target-tabs.php';
if ( isset( $_POST['tlid'] ) && defined( __NAMESPACE__.'\\TARGETLIST_DB_PATH' ) ) {
$target_list = new TargetCollection( TARGETLIST_DB_PATH );
if ( false !== ( $target_item = $target_list->getTargetItem( $_POST['tlid'] ) ) )
$settings = $target_item->targetSettings; 
elseif (( isset( $_SESSION['id'] ) && $_SESSION['id'] == $_POST['tlid'] ) ||
isset( $_SESSION['edit_step'] ) && isset( $_SESSION['edit_step']['step_data'] ) ) {
$array = json_decode( str_replace( '\"', '"', $_SESSION['edit_step']['step_data'] ), true );
is_array( $array ) && $settings = array_merge( $settings, $array );
}
}
$ajax_request = new AjaxRequests( $settings );
$actions = array();
$is_wp = is_wp();
if ( ! $is_wp ) {
function add_action( $action, $callback ) {
global $actions;
$action = str_replace( WP_AJAX_ACTION_PREFIX . '', '', $action );
if ( ! key_exists( $action, $actions ) )
$actions[$action] = $callback;
}
}
$ajax_actions = array( 
'flushhist', 
'get_chart', 
'get_progress', 
'cleanup_progress', 
'run_mysql_maint', 
'run_backup', 
'run_parallel_backup', 
'run_restore', 
'wp_restore', 
'compression_benchmark', 
'chk_status', 
'support_sender_send', 
'support_sender_validate', 
'support_sender_info', 
'ftp_exec', 
'log_read', 
'log_read_abort', 
'read_folder', 
'read_folder_info', 
'auto_save', 
'php_setup', 
'chk_lic', 
'del_file', 
'rst_file', 
'ren_file', 
'del_dir', 
'del_oauth', 
'mk_dir', 
'abort_job', 
'read_alert', 
'print_debug_sample', 
'enable_target', 
'save_target_desc', 
'search_rest_file', 
'set_wpcron_schedule', 
'get_wpcron_schedule', 
'encryption_info', 
'gen_encrypt_keys', 
'export_settings', 
'import_settings', 
'feat_table', 
'feat_lic', 
'eula', 
'redir_checkout', 
'check_vat', 
'braintree_proxy', 
'addon_install', 
'addon_uninstall', 
'addon_disable', 
'decrypt_file', 
'check_update', 
'update_info', 
'install_update', 
'test_dwl', 
'mybackup_core_backup', 
'last_bak_info', 
'wp_restore_job', 
'upload_restore_file' );
foreach ( $ajax_actions as $ajax_action ) {
add_action( WP_AJAX_ACTION_PREFIX . $ajax_action, array( $ajax_request, AJAX_DISPATCH_FUNCTION ) );
}
add_action( 'admin_init', array( $ajax_request, WP_MYBACKUP_ACTION_PREFIX . 'do_action' ) );
add_action( 'init', array( $ajax_request, WP_MYBACKUP_ACTION_PREFIX . 'do_action' ) );
if ( ! $is_wp && isset( $_POST ) && isset( $_POST['action'] ) ) {
$err_pattern = sprintf( 
_esc( "Action '%%s' %%s.<br>This should never happen. Please <a href='%s'>report this issue</a>" ), 
getReportIssueURL() ) . ".<br><a onclick='history.back();' style='cursor:pointer'>&lt;&lt; " . _esc( 'Back' ) .
"</a>";
$action_found = false;
foreach ( $actions as $action => $callback ) {
if ( $action == $_POST['action'] )
if ( method_exists( $callback[0], AJAX_DISPATCH_FUNCTION ) ) {
try {
$action_found = true;
_call_user_func( $callback );
} catch ( MyException $e ) {
die( $e->getMessage() );
}
} else {
die( 
sprintf( 
$err_pattern, 
$action, 
_esc( 'is badly constructed (where is ' ) . get_class( $callback[0] ) . '::' .
AJAX_DISPATCH_FUNCTION . ' ?)' ) );
}
}
if ( isset( $actions['init'] ) )
try {
$action_found = $action_found || _call_user_func( $actions['init'] );
} catch ( MyException $e ) {
die( $e->getMessage() );
}
if ( ! $action_found )
die( sprintf( $err_pattern, $_POST['action'], _esc( 'is not declared' ) ) );
}
?>