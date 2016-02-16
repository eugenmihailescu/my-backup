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
 * @date    : Tue Feb 16 21:41:51 2016 UTC $
 * @file    : WebDAVWebStorage.php $
 * 
 * @id      : WebDAVWebStorage.php | Tue Feb 16 21:41:51 2016 UTC | Eugen Mihailescu eugenmihailescux@gmail.com $
*/

namespace MyBackup;
define ( __NAMESPACE__."\\WEBDAV_UPLOAD_LIMIT", PHP_INT_MAX );
define ( __NAMESPACE__.'\\WEBDAV_ROOT', '/' );
class WebDAVWebStorage extends GenericCloudStorage {
private $_root;
private $_curl_wrapper;
private $_url;
private $_host;
private $_accinfo_cache;
private function _getWebDAVDir($path) {
$dir = '/' == substr ( $path, - 1, 1 ) ? substr ( $path, 0, strlen ( $path ) - 1 ) : $path;
$dir = '/' == substr ( $dir, 0, 1 ) ? substr ( $dir, 1, strlen ( $dir ) - 1 ) : $dir;
return $dir;
}
private function _getWebDAVUrl($path) {
$dir = $this->_getWebDAVDir ( $path );
return $this->_url . (! empty ( $dir ) ? '/' . $dir : '');
}
private $_SERVICE_API = array (
'search' => 'search',
'download' => 'files',
'upload' => 'files_put',
'delete' => 'delete',
'metadata' => 'metadata',
'share' => 'shares',
'rename' => 'rename',
'mkdir' => 'mkdir' 
);
protected function _getServiceName() {
return 'webdav';
}
protected function _getURI($function) {
return $this->_getWebDAVUrl ( WEBDAV_ROOT );
}
protected function _getHeader($function = null) {
switch ($function) {
case 'metadata' :
$hdr = array (
'Content-Type: text/xml',
'Content-length: 0',
'Depth: 1' 
);
break;
default :
$hdr = null;
break;
}
return $hdr;
}
function __construct($settings) {
$this->_curl_wrapper = new CurlWrapper ();
$this->_curl_wrapper->initFromArray ( $settings );
parent::__construct ( $this->_curl_wrapper );
$this->_root = WEBDAV_ROOT;
$this->_accinfo_cache = null;
$this->_curl_wrapper->setProtocol ( CURLPROTO_HTTP | CURLPROTO_HTTPS );
$this->_curl_wrapper->setFollowLocation ();
$this->_curl_wrapper->setHTTPAuthType ( $settings ['webdav_authtype'] );
$this->_curl_wrapper->setHTTPAuthCredential ( $settings ['webdavuser'] . ':' . $settings ['webdavpwd'] );
if (! empty ( $settings ['webdav_cainfo'] ))
$this->_curl_wrapper->setSSLCAFile ( $settings ['webdav_cainfo'] );
$this->_host = $settings ['webdavhost'];
$this->_url = '/' == substr ( $this->_host, - 1, 1 ) ? substr ( $this->_host, 0, strlen ( $this->_host ) - 1 ) : $this->_host;
}
function metadata($path, $include_trash = false) {
$api = $this->_SERVICE_API ['metadata'];
$post_fields = null;
$this->_curl_wrapper->setOKStatusCodes ( array (
207 
) ); 
$result = $this->_curl_wrapper->curlPOST ( $this->_getWebDAVUrl ( $path ), $this->_getHeader ( $api ), $post_fields, null, null, 'PROPFIND' );
$obj = new WebDAVParser ( $result );
$responses = $obj->parse ();
$result = array ();
foreach ( $responses as $data ) {
$is_dir = false !== strpos ( $data->props->content_type, 'directory' );
$parts = explode ( '/', $this->_host );
if ($is_dir && $data->href == '/' . end ( $parts ) . $path)
continue;
$result [] = array (
'name' => $data->href,
'is_dir' => $is_dir,
'size' => $data->props->content_length,
'mime_type' => $data->props->content_type,
'time' => strtotime ( $data->props->modified_date ),
'crtime' => strtotime ( $data->props->creation_date ),
'executable' => $data->props->executable,
'status' => $data->props->status,
'tag' => $data->props->tag,
'quota_info' => array ( // Requirements:
'quota' => $data->props->quota_available_bytes,
'normal' => $data->props->quota_used_bytes 
) 
);
}
return $result;
}
function getFile($filename) {
$metadata = $this->metadata ( $filename );
if (is_array ( $metadata ) && count ( $metadata ) > 0)
return $metadata [0];
}
function getFreeSpace($cached = false) {
return $this->_getFreeSpace ( array (
'quota_info' => 'quota' 
), array (
'quota_info' => 'normal' 
), $cached );
}
function getQuota($cached = false) {
return $this->_getQuota ( array (
'quota_info' => 'quota' 
), $cached );
}
function getUploadLimit($mime_type = null) {
return WEBDAV_UPLOAD_LIMIT;
}
function uploadFile($filename, $targetPath, $description = null) {
$this->_uploadFile ( $filename );
$api = $this->_SERVICE_API ['upload'];
$relative_path = '/' . $targetPath . '/' . _basename ( $filename );
$relative_path = str_replace ( '//', '/', $relative_path );
$url = $this->_getURI ( $api ) . $relative_path;
$this->_curl_wrapper->setOKStatusCodes ( array (
200,
201,
204 
) );
$this->_curl_wrapper->onAbortCallback = $this->onAbort;
$result = $this->_curl_wrapper->curlPOST ( $url, $this->_getHeader ( $api ), null, null, $filename, 'PUT', $this->onBytesSent );
$response = $this->getFile ( $relative_path );
return $response;
}
function downloadFile($filename, $outStream = null, $api_key = null) {
$api = $this->_SERVICE_API ['download'];
$this->_curl_wrapper->setOKStatusCodes ( array (
200 
) ); 
$this->_curl_wrapper->onAbortCallback = $this->onAbort;
return $this->_curl_wrapper->curlPOST ( $this->_getWebDAVUrl ( $filename ), $this->_getHeader ( $api ), null, $outStream, null, 'GET', $this->onBytesReceived );
}
function downloadUrl($download_url, $outStream = null, $api_key = null) {
$this->_notSupported ();
}
function searchFileNames($basePath, $query, $include_trash = false, $cached = false) {
if ($cached && false !== ($result = $this->_getCachedMetadata ( $basePath, $query )))
return $result;
$result = array ();
$files = $this->metadata ( $basePath, $include_trash );
foreach ( $files as $file_info )
if (0 === strpos ( basename ( $file_info ['name'] ), $query ))
$result [] = $file_info;
$this->_setCachedMetadata ( $basePath, $query, $result );
return $result;
}
function deleteFile($filename, $move2trash = true) {
$basePath = _dirname ( $filename );
return $this->_deleteFile ( $this->_getWebDAVUrl ( $basePath ) . '/' . _basename ( $filename ), null, 'DELETE' );
}
public function shareFile($file_id, $api_key = null, $role = null, $type = null, $short_url = null) {
$this->_notSupported ();
}
public function getAccountInfo($cached = false) {
$response = array ();
if ($cached && ! empty ( $this->_accinfo_cache ))
$response = $this->_accinfo_cache;
else {
$metadata = $this->getFile ( WEBDAV_ROOT );
if (is_array ( $metadata ) && isset ( $metadata ['quota_info'] )) {
$this->_accinfo_cache = $metadata;
$response = $metadata;
}
}
return $response;
}
public function renameFile($file_id, $new_name) {
$basePath = _dirname ( $file_id );
$src = $this->_getWebDAVUrl ( $basePath ) . '/' . _basename ( $file_id );
$dst = $this->_getWebDAVUrl ( $basePath ) . '/' . _basename ( $new_name );
$this->_curl_wrapper->setOKStatusCodes ( array (
201, 
204 
) );
$this->_genericPost ( 'rename', $src, null, 'MOVE', array (
'Destination:' . $dst,
'Overwrite: F'  // prevent overwriting existent files
), function ($e) {
if (412 == $e->getCode ())
throw new MyException ( 'Cannot overwrite an existent resource.', 412 );
} );
return 1;
}
public function createFolder($path_id, $name) {
$dst = $this->_getWebDAVUrl ( $path_id . '/' . $name );
$this->_curl_wrapper->setOKStatusCodes ( array (
201 
) ); 
$this->_genericPost ( 'mkdir', $dst, null, 'MKCOL' );
return 1;
}
public function getSSLInfo() {
return $this->_curl_wrapper->getSSLInfo ();
}
public function isSecure() {
return 'HTTPS' == strtoupper ( parse_url ( $this->_url, PHP_URL_SCHEME ) );
}
}
?>