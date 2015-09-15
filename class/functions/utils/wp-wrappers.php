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
 * @file    : wp-wrappers.php $
 * 
 * @id      : wp-wrappers.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;

function plugins_url_wrapper($path, $plugin) {
$plugin_root = str_replace ( DIRECTORY_SEPARATOR, '/', getFileRelativePath ( $plugin ) );
'/' != substr ( $path, 0, 1 ) && '/' != substr ( $plugin_root, - 1 ) && ($plugin_root .= '/') || ('/' == substr ( $plugin_root, - 1 ) && ($plugin_root = substr ( $plugin_root, 0, - 1 )));
$result = function_exists ( 'plugins_url' ) ? plugins_url ( $path, $plugin ) : ($plugin_root . $path);
return $result;
}
function plugin_dir_path_wrapper($file) {
return function_exists ( 'plugin_dir_path' ) ? plugin_dir_path ( $file ) : dirname ( $file );
}
function get_option_wrapper($option, $default) {
return function_exists ( 'get_option' ) ? get_option ( $option, $default ) : get_option_local_db ( $option, $default );
}
function get_ABSPATH_wrapper() {
return defined ( 'ALT_ABSPATH' ) ? '/' : DIRECTORY_SEPARATOR == substr ( __DIR__, - 1 ) ? substr ( __DIR__, 0, strlen ( __DIR__ ) ) : __DIR__;
}
function get_home_url_wrapper($blog_id = null, $path = null, $scheme = null) {
if (function_exists ( 'get_home_url' ))
return get_home_url ( $blog_id, $path, $scheme );
else {
$url = selfURL ( true );
if ('/' == substr ( $url, - 1 ))
return substr ( $url, 0, strlen ( $url ) - 1 );
else
return $url;
}
}
function update_option_wrapper($option, $new_value) {
return function_exists ( 'update_option' ) ? update_option ( $option, $new_value ) : update_option_local_db ( $option, $new_value );
}
function delete_option_wrapper($option) {
return function_exists ( 'delete_option' ) ? delete_option ( $option ) : delete_option_local_db ( $option );
}
function wp_get_schedules_wrapper() {
return function_exists ( 'wp_get_schedules' ) ? wp_get_schedules () : array ();
}
function wp_create_nonce_wrapper($action) {
return function_exists ( 'wp_create_nonce' ) ? wp_create_nonce ( $action ) : create_nonce ( $action );
}
function wp_verify_nonce_wrapper($nonce, $action) {
return function_exists ( 'wp_verify_nonce' ) ? wp_verify_nonce ( $nonce, $action ) : verify_nonce ( $nonce, $action );
}
function auth_redirect_wrapper() {
if ($_SESSION ['login_redirect'])
return;
$wp_pluggable = wp_get_pluggable_path ();
if (is_wp () && file_exists ( $wp_pluggable ) && function_exists ( 'is_user_logged_in' ) && ! is_user_logged_in ()) {
add_session_var ( 'login_redirect', true );
include_once $wp_pluggable;
auth_redirect (); 
} else {
locationRedirect ( selfURL ( false ) ); 
}
}
function wp_get_pluggable_path() {
return dirname ( dirname ( dirname ( ROOT_PATH ) ) ) . '/wp-includes/pluggable.php';
}
function is_multisite_wrapper() {
if (function_exists ( 'is_multisite' ))
return is_multisite ();
else
return false;
}
function get_current_user_id_wrapper() {
if (is_wp ()) {
if (function_exists ( 'get_current_user_id' ))
return get_current_user_id ();
} else {
if (isset ( $_SESSION ) && isset ( $_SESSION ['simple_login_username'] ))
return $_SESSION ['simple_login_username'];
}
return false;
}
function is_wp() {
return function_exists ( 'add_management_page' );
}
function wp_get_timezone_string() {
if (function_exists ( 'get_option' ) && $timezone = get_option ( 'timezone_string' ))
return $timezone;
if (0 === (function_exists ( 'get_option' ) && $utc_offset = get_option ( 'gmt_offset', 0 )))
return 'UTC';
else
$utc_offset = 0;
$utc_offset *= 3600;
$timezone = timezone_name_from_abbr ( '', $utc_offset );
if (false === $timezone) {
$is_dst = date ( 'I' );
$timezone = null;
foreach ( timezone_abbreviations_list () as $abbr ) {
foreach ( $abbr as $city ) {
if ($city ['dst'] == $is_dst && $city ['offset'] == $utc_offset) {
$timezone = $city ['timezone_id'];
break;
}
}
}
if (! empty ( $timezone ))
return $timezone;
}
return 'UTC';
}
function get_wp_config_path() {
return ABSPATH . 'wp-config.php';
}
function update_wp_config($array) {
global $container_shape;
if (! empty ( $array )) {
$filename = get_wp_config_path ();
$backup = $filename . '.' . time ();
$buffer = file_get_contents ( $filename );
$comment = ' by ' . WPMYBACKUP . ' @ ' . date ( 'Y-m-d H:i:s e' );
$summary = array ();
foreach ( $array as $key => $value ) {
$pattern = '/((define\s*\(\s*[\'"]' . $key . '[\'"]\s*,\s*)([\w]+)([^;]+;)).*/';
if (preg_match ( $pattern, $buffer ))
$buffer = preg_replace ( $pattern, '$2' . $value . '$4 // changed' . $comment . ' << $1', $buffer );
else
$buffer .= PHP_EOL . "define('$key' , $value); // added" . $comment . PHP_EOL;
$summary [] = "<b>$key</b> = <i>$value</i>";
}
if (copy ( $filename, $backup ) && file_put_contents ( $filename, $buffer )) {
$summary = ! empty ( $summary ) ? "<ul style=\'list-style: square inside none;\'><li>" . implode ( '</li><li>', $summary ) . '</li></ul>' : '';
return sprintf ( "parent.popupWindow('%s','%s');", _esc ( 'Confirmation' ), sprintf ( _esc ( 'The file %s has been updated successfully.%s: the old file has been copied at %s' ), addslashes ( $filename ), '<br><br><b>' . _esc ( 'What was changed' ) . "</b><blockquote class=\'hintbox $container_shape\'>$summary</blockquote><b>" . _esc ( 'Note' ) . '</b>', addslashes ( $backup ) ) );
}
}
$err = error_get_last ();
$err_str = sprintf ( _esc ( 'Could not update the %s.%s' ), addslashes ( $filename ), '<br>' . $err ['message'] );
return sprintf ( "parent.popupError('%s','%s');", _esc ( 'Error' ), $err_str );
}
?>
