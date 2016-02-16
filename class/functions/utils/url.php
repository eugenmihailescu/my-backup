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
 * @file    : url.php $
 * 
 * @id      : url.php | Tue Feb 16 15:27:30 2016 +0100 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

define( __NAMESPACE__.'\\LMGTFY_URL', 'http://lmgtfy.com/?q=' );
function is_cli() {
return ( ! ( empty( $_ENV['SHELL'] ) && empty( $_SERVER['argv'] ) ) && empty( $_SERVER["REMOTE_ADDR"] ) );
}
function redirectFileDownload( $filename, $type ) {
if ( file_exists( $filename ) )
$fsize = filesize( $filename );
else
die( sprintf( _esc( 'File "%s" does not exists and thus cannot be downloaded (is it hidden?)' ), $filename ) );
! headers_sent() || die( _esc( 'Cannot redirect to the download file. Headers already sent.' ) );
header( 'Expires: 0' );
header( "Cache-Control: no-store, no-cache, must-revalidate" );
header( "Cache-Control: post-check=0, pre-check=0", false );
header( "Pragma: no-cache" );
header( 'Content-Description: File Transfer' );
header( "Content-Type: $type" );
header( "Content-disposition: attachment; filename=\"" . basename( $filename ) . "\"" );
header( "Content-Transfer-Encoding: Binary" );
header( 'Content-Length: ' . $fsize );
header( 'content-md5: ' . md5_file( $filename ) );
try {
$chunksize = 1048576; 
if ( $fsize > $chunksize ) {
$handle = fopen( $filename, 'rb' );
while ( ! feof( $handle ) ) {
echo fread( $handle, $chunksize );
flush();
if ( ob_get_level() > 0 )
@ob_end_flush();
}
fclose( $handle );
} else {
readfile( $filename );
flush();
if ( ob_get_level() > 0 )
@ob_end_flush();
}
} catch ( MyException $e ) {
echo $e->getMessage();
}
}
function getDocumentRoot() {
return str_replace( 
str_replace( '/', DIRECTORY_SEPARATOR, realpath( $_SERVER['SCRIPT_NAME'] ) ), 
'', 
realpath( $_SERVER['SCRIPT_FILENAME'] ) );
}
function stripUrlParams( $url, $varname ) {
if ( empty( $varname ) )
return $url;
is_string( $varname ) && $varname = array( $varname );
foreach ( $varname as $item ) {
$re = '/([?&])' . $item . '(=)*[^&]*(&|$)/';
while ( $url != ( $new_url = preg_replace( $re, '$1', $url ) ) )
$url = $new_url;
}
while ( false !== array_search( substr( $url, - 1, 1 ), array( '&', '?' ) ) )
$url = substr( $url, 0, strlen( $url ) - 1 );
return $url;
}
function replaceUrlParam( $url, $param_name, $new_value ) {
if ( empty( $url ) )
return $url;
is_string( $param_name ) && $param_name = array( $param_name );
is_string( $new_value ) && $new_value = array( $new_value );
foreach ( $param_name as $key => $param )
if ( preg_match( '/[?&]tab=/', $url ) )
$url = preg_replace( "/([^\s\S]*)(" . $param . "=)[^&#]*/", "$2" . $new_value[$key], $url );
else
$url .= ( false !== strpos( $url, '?' ) ? '&' : '?' ) . $param . '=' . $new_value[$key];
return $url;
}
function addUrlParams( $url, $params, $overwrite = true ) {
$overwrite && $url = stripUrlParams( $url, array_keys( $params ) );
return $url . ( false === strpos( $url, '?' ) ? '?' : '&' ) . http_build_query( $params );
}
function locationRedirect( $url ) {
if ( ! headers_sent() ) {
header( 'Location: ' . $url );
exit();
}
echo '<script type="text/javascript">';
echo 'window.location.href="' . $url . '";';
echo '</script>';
echo '<noscript>';
echo '<meta http-equiv="refresh" content="0;url=' . $url . '" />';
echo '</noscript>';
}
function isSSL() {
$result = false;
if ( ! empty( $_SERVER['HTTPS'] ) && true == strToBool( $_SERVER['HTTPS'] ) ||
! empty( $_SERVER['SERVER_PORT_SECURE'] ) || 443 == $_SERVER['SERVER_PORT'] )
$result = true;
return $result;
}
function selfURL( $no_uri = false, $force_ssl = false ) {
if ( is_cli() ) {
global $argv;
return realpath( $argv[0] );
}
$server = $_SERVER['SERVER_NAME'];
$server = substr( $server, 0, strlen( $server ) - intval( '/' == substr( $server, - 1 ) ) ); 
$protocol = strtolower( $_SERVER['SERVER_PROTOCOL'] ); 
( $protocol = substr( $protocol, 0, strpos( $protocol, '/' ) ) ) && ( $force_ssl || isSSL() ) && $protocol .= 's';
$port = ( $_SERVER['SERVER_PORT'] == '80' ) ? '' : ( ':' . $_SERVER['SERVER_PORT'] );
$uri = $no_uri ? '' : ( isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '' );
return sprintf( 
'%s://%s%s/%s', 
$protocol, 
$server, 
$port, 
! empty( $uri ) && '/' == $uri[0] ? substr( $uri, 1, strlen( $uri ) - 1 ) : $uri );
}
function getClientIP() {
$client_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : null;
$proxy_ip = isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : null;
if ( ! empty( $client_ip ) )
$ip = $client_ip;
if ( ! empty( $proxy_ip ) )
$ip = $proxy_ip;
return empty( $ip ) ? getMyExtIP() : $ip;
}
function getMyExtIP() {
return file_get_contents( 'http://icanhazip.com/' );
}
function checkJavaScriptAvailable() {
$session_key = 'js_enabled_timestamp';
$last_checked = time();
if ( isset( $_SESSION[$session_key] ) && $last_checked - intval( $_SESSION[$session_key] ) <= JSENABLED_CHECK_TIMEOUT )
return;
add_session_var( $session_key, $last_checked );
?>
<!-- Check for JavaScript support -->
<noscript>
<div
style="padding: 5px 10px; text-align: center; background-color: #ffc; border: 1px solid #c0c0c0; border-radius: 5px;">
<table>
<tr>
<td><img src="<?php echo plugins_url_wrapper('img/js.png', IMG_PATH);?>"></td>
<td style="font-weight: bold; font-size: 1.5em;"><?php _pesc('JavaScript');?></td>
<td><?php printf(_esc('For full functionality of this site it is necessary to enable JavaScript. Here are the %s'),getAnchor('instructions how to enable JavaScript in your web browser', 'http://www.enable-javascript.com'));?>
</td>
</tr>
</table>
</div>
</noscript>
<?php
}
function getAsyncRunURL() {
$regaction_php = 'regactions.php';
$file_relpath = getFileRelativePath( CLASS_PATH . $regaction_php ); 
$file_relpath = str_replace( str_replace( '/', DIRECTORY_SEPARATOR, ALT_ABSPATH ), '', $file_relpath ); 
$self_url = selfURL( true );
$file_relpath = str_replace( DIRECTORY_SEPARATOR, '/', $file_relpath );
substr( $self_url, - 1 ) == substr( $file_relpath, 0, 1 ) &&
$self_url = substr( $self_url, 0, strlen( $self_url ) - 1 );
$lang_code = getSelectedLangCode();
$query = false !== $lang_code ? '?lang=' . $lang_code : '';
return sprintf( '%s%s/%s%s', $self_url, $file_relpath, $regaction_php, $query ); 
}
function lmgtfy( $string ) {
return LMGTFY_URL . urlencode( $string );
}
?>