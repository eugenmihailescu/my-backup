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
 * @version : 1.0-2 $
 * @commit  : f8add2d67e5ecacdcf020e1de6236dda3573a7a6 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Tue Dec 13 06:40:49 2016 +0100 $
 * @file    : GenericCloudStorage.php $
 * 
 * @id      : GenericCloudStorage.php | Tue Dec 13 06:40:49 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
define( __NAMESPACE__.'\\FILE_BUFFER_SIZE', 8192 );
abstract class GenericCloudStorage {
private $_cached_metadata;
private $_oauthSession;
public $onBytesReceived;
public $onBytesSent;
public $onAbort;
private function _getFileMimeType( $filename ) {
if ( function_exists( '\\fileinfo' ) ) {
$finfo = finfo_open( FILEINFO_MIME_TYPE );
return finfo_file( $finfo, $filename );
} elseif ( function_exists( '\\mime_content_type' ) )
return mime_content_type( $filename );
return 'application/octet-stream'; 
}
protected function _genericPost( $api, $url, $postfields = null, $method = 'POST', $header = null, $error_callback = null ) {
if ( method_exists( $this->getOAuthSession(), 'getAPIByServiceType' ) ) {
if ( null != ( $hdr = $this->_getHeader( $this->getOAuthSession()->getAPIByServiceType( $api ) ) ) ) {
if ( null != $header )
$hdr = ( is_array( $header ) ? $header : array( $header ) ) + $hdr;
}
}
$hdr = ! isset( $hdr ) ? $header : $hdr;
try {
$response = $this->getOAuthSession()->curlPOST( $url, $hdr, $postfields, null, null, $method );
} catch ( MyException $e ) {
if ( null !== $error_callback )
if ( null != ( $result = _call_user_func( $error_callback, $e ) ) )
return $result;
throw new MyException( $e->getMessage(), $e->getCode(), $e->getPrevious() );
}
return json_decode( $response, true );
}
abstract protected function _getServiceName();
protected function _getFreeSpace( $total_bytes = null, $used_bytes = null, $cached = false ) {
if ( method_exists( $this, 'getAccountInfo' ) )
$metadata = $this->getAccountInfo( $cached );
else
$metadata = $this->getOAuthSession()->getAccountInfo( $cached );
if ( empty( $metadata ) )
return false;
$total = 0;
$used = 0;
if ( is_array( $total_bytes ) ) {
$ktb = array_keys( $total_bytes );
$vtb = array_values( $total_bytes );
if ( isset( $ktb[0] ) && isset( $vtb[0] ) && isset( $metadata[$ktb[0]][$vtb[0]] ) )
$total = $metadata[$ktb[0]][$vtb[0]];
} elseif ( isset( $metadata[$total_bytes] ) )
$total = $metadata[$total_bytes];
if ( is_array( $used_bytes ) ) {
$kub = array_keys( $used_bytes );
$vub = array_values( $used_bytes );
if ( isset( $kub[0] ) && isset( $vub[0] ) && isset( $metadata[$kub[0]][$vub[0]] ) )
$used = $metadata[$kub[0]][$vub[0]];
} elseif ( isset( $metadata[$used_bytes] ) )
$used = $metadata[$used_bytes];
return floatval( $total ) - floatval( $used );
}
protected function _getQuota( $propname = null, $cached = false ) {
if ( method_exists( $this, 'getAccountInfo' ) )
$metadata = $this->getAccountInfo( $cached );
else
$metadata = $this->getOAuthSession()->getAccountInfo( $cached );
if ( false === $metadata || empty( $metadata ) )
return false;
$result = 0;
if ( is_array( $propname ) ) {
$kpn = array_keys( $propname );
$vpn = array_values( $propname );
if ( isset( $metadata[$kpn[0]] ) && isset( $metadata[$kpn[0]][$vpn[0]] ) )
$result = $metadata[$kpn[0]][$vpn[0]];
} elseif ( isset( $metadata[$propname] ) )
$result = $metadata[$propname];
return floatval( $result );
}
protected function _uploadFile( $filename, $target_path_id = null ) {
$error = null;
$service_name = strtoupper( $this->_getServiceName() );
if ( ! _file_exists( $filename ) )
$error = sprintf( 
_esc( 'File "%s" does not exists on local disk. Cannot upload to %s.' ), 
$filename, 
$service_name );
elseif ( empty( $error ) && ! empty( $target_path_id ) ) {
$finfo = $this->getFile( $target_path_id );
if ( empty( $finfo ) )
$error = sprintf( "%s folder with id='%s' does not exists.", $service_name, $target_path_id );
} elseif ( empty( $error ) ) {
$upload_limit = $this->getUploadLimit( $this->_getFileMimeType( $filename ) );
if ( ( $file_size = filesize( $filename ) ) > $upload_limit )
$error = sprintf( 
_esc( 'The size of the file %s (%d bytes) exceeds the %s upload limit (%d bytes).' ), 
$filename, 
$file_size, 
$service_name, 
$upload_limit );
} elseif ( empty( $error ) )
if ( $file_size > ( $free_space = $this->_getFreeSpace() ) )
$error = sprintf( 
_esc( 'The size of the file %s (%d bytes) exceeds the %s free space (%d bytes).' ), 
$filename, 
$file_size, 
$service_name, 
$free_space );
if ( ! empty( $error ) )
throw new MyException( $error );
}
protected function _getHeader( $function = null ) {
if ( method_exists( $this->_oauthSession, 'getHeader' ) )
$hdr = $this->_oauthSession->getHeader( $function );
switch ( $function ) {
case $this->getOAuthSession()->getAPIByServiceType( 'auth' ) :
$hdr = 'Location:' . $this->_getURI( $function );
break;
case $this->getOAuthSession()->getAPIByServiceType( 'share' ) :
case $this->getOAuthSession()->getAPIByServiceType( 'rename' ) :
case $this->getOAuthSession()->getAPIByServiceType( 'mkdir' ) :
$hdr[] = 'Content-Type:application/json';
break;
case $this->getOAuthSession()->getAPIByServiceType( 'metadata' ) :
$hdr[] = 'Content-Length: 0';
break;
}
return $hdr;
}
protected function _deleteFile( $url, $postfields = null, $method = 'POST' ) {
return $this->_genericPost( 
'delete', 
$url, 
$postfields, 
$method, 
null, 
function ( $e ) {
if ( 204 == $e->getCode() )
return true;
return null;
} );
}
protected function _notSupported() {
throw new MyException( sprintf( _esc( 'Function not supported by %s' ), strtoupper( $this->_getServiceName() ) ) );
}
protected function _encodeError( $e ) {
return ! ( empty( $e ) ) ? json_encode( array( 'message' => $e->getMessage(), 'code' => $e->getCode() ) ) : null;
}
protected function _selfUrl( $include_path = true, $include_query = true ) {
return $this->getOAuthSession()->getRedirectUri( $include_path, $include_query );
}
protected function _stripUrlParams( $url, $varname ) {
foreach ( $varname as $item )
$url = preg_replace( '/([?&])' . $item . '(=)*[^&]*(&|$)/', '$1', $url );
while ( substr( $url, - 1, 1 ) == '&' )
$url = substr( $url, 0, strlen( $url ) - 1 );
return $url;
}
abstract protected function metadata( $path, $include_trash = false );
abstract protected function uploadFile( $filename, $targetPath, $description = null );
abstract protected function searchFileNames( $basePath, $query, $include_trash = false, $cached = false );
abstract protected function getQuota( $cached = false );
abstract protected function getFreeSpace( $cached = false );
abstract protected function downloadFile( $file_id, $outStream = null, $api_key = null );
abstract protected function deleteFile( $file_id, $move2trash = true );
abstract protected function _getURI( $function );
abstract protected function renameFile( $file_id, $new_name );
abstract protected function createFolder( $path_id, $name );
protected function _getCachedMetadata( $basePath, $query ) {
if ( isset( $this->_cached_metadata[$basePath] ) ) {
$keys = array_keys( $this->_cached_metadata[$basePath] );
$match = false;
foreach ( $keys as $key )
if ( $match = ( $query == $key ) )
break;
if ( ! $match )
foreach ( $keys as $key )
if ( $match = ( 0 === strpos( $query, $key ) ) )
break;
if ( $match )
return $this->_cached_metadata[$basePath][$key];
}
return false;
}
protected function _setCachedMetadata( $basePath, $query, $data ) {
$this->_cached_metadata[$basePath][$query] = $data;
}
abstract function getFile( $file_id );
abstract function getUploadLimit( $mime_type = null );
abstract function downloadUrl( $download_url, $outStream = null, $api_key = null );
abstract function shareFile( $file_id, $api_key = null, $role = null, $type = null, $short_url = null );
function __construct( $oauth_session ) {
$this->_oauthSession = $oauth_session;
$this->onAbort = null;
$this->onBytesReceived = null;
$this->onBytesSent = null;
$this->_cached_metadata = array();
}
public function getOAuthSession() {
return $this->_oauthSession;
}
public function curlAborted() {
return $this->_oauthSession->curlAborted();
}
public function setTimeout( $value ) {
$this->getOAuthSession()->setTimeout( $value );
}
public function isSecure() {
return $this->_oauthSession->isSecure();
}
}
?>