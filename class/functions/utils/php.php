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
 * @version : 0.2.3-33 $
 * @commit  : 8322fc3e4ca12a069f0821feb9324ea7cfa728bd $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Tue Nov 29 16:33:58 2016 +0100 $
 * @file    : php.php $
 * 
 * @id      : php.php | Tue Nov 29 16:33:58 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

function php_inivalu2int( $value ) {
$units = array( 'K', 'M', 'G', 'T', 'P' );
$multiply = 1;
if ( preg_match( "/[KMGTP]/i", $value, $matches ) )
$multiply = pow( 1024, 1 + array_search( strtoupper( $matches[0] ), $units ) );
if ( preg_match( "/\d*/", $value, $matches ) && strlen( $matches[0] ) > 0 )
return intval( $matches[0] ) * $multiply;
else
return false;
}
function getMemoryLimit() {
$memory_limit = ini_get( 'memory_limit' );
$result = php_inivalu2int( $memory_limit );
return false === $result ? - 1 : $result;
}
function getPostMaxSize() {
$post_max_size = ini_get( 'post_max_size' );
$result = php_inivalu2int( $post_max_size );
return false === $result ? - 1 : $result;
}
function getUploadLimit() {
$upload_limit = ini_get( 'upload_max_filesize' );
$post_max_size = getPostMaxSize();
$result = php_inivalu2int( $upload_limit );
return false === $result ? - 1 : ( false === $post_max_size ? $result : min( $result, $post_max_size ) );
}
function get_object_owner() {
$result = false;
$trace = debug_backtrace();
$class = $trace[1]['class'];
for ( $i = 1; $i < count( $trace ); $i++ )
if ( $class != $trace[$i]['class'] ) {
$result = $trace[$i]['class'];
break;
}
return $result;
}
function _get_ns_function_name( $function_name ) {
return function_exists( $function_name ) ? $function_name : ( __NAMESPACE__ . '\\' . $function_name );
}
function _function_exists( $function_name ) {
return function_exists( $function_name ) || function_exists( _get_ns_function_name( $function_name ) );
}
function _is_callable( $function_name ) {
return is_callable( is_string( $function_name ) ? _get_ns_function_name( $function_name ) : $function_name );
}
function _call_user_func_array( $function_name, $args ) {
return call_user_func_array( 
is_string( $function_name ) ? _get_ns_function_name( $function_name ) : $function_name, 
$args );
}
function _call_user_func( $function_name ) {
$args = array_slice( func_get_args(), 1 ); 
$result = call_user_func_array( 
is_string( $function_name ) ? _get_ns_function_name( $function_name ) : $function_name, 
$args );
return $result;
}
function obsafe_print_r( $var, $return = false, $html = false, $level = 0 ) {
$spaces = "";
$space = $html ? "&nbsp;" : " ";
$newline = $html ? "<br />" : "\n";
for ( $i = 1; $i <= 6; $i++ ) {
$spaces .= $space;
}
$tabs = $spaces;
for ( $i = 1; $i <= $level; $i++ ) {
$tabs .= $spaces;
}
if ( is_array( $var ) ) {
$title = "Array";
} elseif ( is_object( $var ) ) {
$title = get_class( $var ) . " Object";
}
$output = $title . $newline . $newline;
foreach ( $var as $key => $value ) {
if ( is_array( $value ) || is_object( $value ) ) {
$level++;
$value = obsafe_print_r( $value, true, $html, $level );
$level--;
}
$output .= $tabs . "[" . $key . "] => " . $value . $newline;
}
if ( $return )
return $output;
else
echo $output;
}
if ( ! _function_exists( 'getallheaders' ) ) {
function getallheaders() {
$headers = array();
$copy_server = array( 
'CONTENT_TYPE' => 'Content-Type', 
'CONTENT_LENGTH' => 'Content-Length', 
'CONTENT_MD5' => 'Content-Md5' );
foreach ( $_SERVER as $key => $value ) {
if ( substr( $key, 0, 5 ) === 'HTTP_' ) {
$key = substr( $key, 5 );
if ( ! isset( $copy_server[$key] ) || ! isset( $_SERVER[$key] ) ) {
$key = str_replace( ' ', '-', ucwords( strtolower( str_replace( '_', ' ', $key ) ) ) );
$headers[$key] = $value;
}
} elseif ( isset( $copy_server[$key] ) ) {
$headers[$copy_server[$key]] = $value;
}
}
if ( ! isset( $headers['Authorization'] ) ) {
if ( isset( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) ) {
$headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
} elseif ( isset( $_SERVER['PHP_AUTH_USER'] ) ) {
$basic_pass = isset( $_SERVER['PHP_AUTH_PW'] ) ? $_SERVER['PHP_AUTH_PW'] : '';
$headers['Authorization'] = 'Basic ' . base64_encode( $_SERVER['PHP_AUTH_USER'] . ':' . $basic_pass );
} elseif ( isset( $_SERVER['PHP_AUTH_DIGEST'] ) ) {
$headers['Authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
}
}
return $headers;
}
}
?>