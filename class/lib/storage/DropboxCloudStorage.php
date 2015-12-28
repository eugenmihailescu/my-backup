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
 * @file    : DropboxCloudStorage.php $
 * 
 * @id      : DropboxCloudStorage.php | Mon Dec 28 17:57:55 2015 +0100 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
define ( __NAMESPACE__."\\DROPBOX_UPLOAD_LIMIT", 150 * 1048576 );
define ( __NAMESPACE__.'\\DROPBOX_ROOT', 'dropbox' );
class DropboxCloudStorage extends GenericCloudStorage {
private $_root;
private function _getCachedMedia($filename) {
$key_id = md5 ( $filename );
if (isset( $_SESSION [ $key_id]))
return $_SESSION [$key_id];
return false;
}
private function _setCachedMedia($filename, $medatada) {
add_session_var ( md5 ( $filename ), $medatada );
}
private $_SERVICE_API = array (
'search' => 'search',
'download' => 'files',
'upload' => 'files_put',
'delete' => 'delete',
'metadata' => 'metadata',
'share' => 'shares',
'media' => 'media',
'rename' => 'move',
'mkdir' => 'create_folder' 
);
protected function _getServiceName() {
return 'dropbox';
}
protected function _getURI($function) {
$oauth_version = '';
$path = '1/' . $function;
$params = 'auto';
$domain = 'api';
switch ($function) {
case $this->_SERVICE_API ['download'] :
case $this->_SERVICE_API ['upload'] :
$domain = 'api-content';
break;
case $this->_SERVICE_API ['media'] :
$path = '1/media';
$params = 'auto';
break;
case $this->_SERVICE_API ['delete'] :
case $this->_SERVICE_API ['rename'] :
case $this->_SERVICE_API ['mkdir'] :
$path = '1/fileops';
$params = $function;
break;
}
return sprintf ( '%s://%s.%s/%s%s/%s', DROPBOX_PROTOCOL, $domain, DROPBOX_DOMAIN, $path, $oauth_version, $params );
}
function __construct($oauth_session) {
parent::__construct ( $oauth_session );
$oauth_session->mergeAPIS ( $this->_SERVICE_API );
$this->_root = DROPBOX_ROOT;
}
function metadata($path, $include_trash = false) {
$api = $this->_SERVICE_API ['metadata'];
try {
$response = $this->getOAuthSession ()->curlPOST ( $this->_getURI ( $api ) . '/' . $path, $this->_getHeader ( $api ), array (
'include_deleted' => $include_trash 
) );
} catch ( MyException $e ) {
$response = $this->_encodeError ( $e );
}
return json_decode ( $response, true );
}
function getFile($filename) {
return $this->metadata ( $filename );
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
return DROPBOX_UPLOAD_LIMIT;
}
function uploadFile($filename, $targetPath, $description = null) {
$this->_uploadFile ( $filename );
$api = $this->_SERVICE_API ['upload'];
$relative_path = '/' . $targetPath . '/' . basename ( $filename );
$url = $this->_getURI ( $api ) . str_replace ( '//', '/', $relative_path );
try {
$response = $this->getOAuthSession ()->curlPOST ( $url, $this->_getHeader ( $api ), null, null, $filename, 'POST', $this->onBytesSent, $this->onAbort );
} catch ( MyException $e ) {
$response = $this->_encodeError ( $e );
}
return json_decode ( $response, true );
}
function downloadFile($filename, $outStream = null, $api_key = null) {
$api = $this->_SERVICE_API ['download'];
try {
return $this->getOAuthSession ()->curlPOST ( $this->_getURI ( $api ) . '/' . $filename, $this->_getHeader ( $api ), null, $outStream, null, 'GET', $this->onBytesReceived, $this->onAbort );
} catch ( MyException $e ) {
return $this->_encodeError ( $e );
}
}
function downloadUrl($download_url, $outStream = null, $api_key = null) {
$this->_notSupported ();
}
function searchFileNames($basePath, $query, $include_trash = false, $cached = false) {
if ($cached && false !== ($result = $this->_getCachedMetadata ( $basePath, $query )))
return $result;
$api = 'search';
$result = $this->_genericPost ( $api, $this->_getURI ( $this->_SERVICE_API [$api] ) . '/' . $basePath, http_build_query ( array (
'query' => $query,
'include_deleted' => $include_trash 
) ) );
$this->_setCachedMetadata ( $basePath, $query, $result );
return $result;
}
function deleteFile($filename, $move2trash = true) {
return $this->_deleteFile ( $this->_getURI ( $this->_SERVICE_API ['delete'] ) . '?' . http_build_query ( array (
'root' => $this->_root,
'path' => $filename 
) ) );
}
public function shareFile($file_id, $api_key = null, $role = null, $type = null, $short_url = null) {
$this->_notSupported ();
}
public function getDirectDownloadURL($path) {
$response = $this->_getCachedMedia ( $path );
if (false === $response) {
$api = $this->_SERVICE_API ['media'];
try {
$response = $this->getOAuthSession ()->curlPOST ( $this->_getURI ( $api ) . '/' . $path, $this->_getHeader ( $api ) );
$this->_setCachedMedia ( $path, $response );
} catch ( MyException $e ) {
$response = $this->_encodeError ( $e );
}
}
return json_decode ( $response, true );
}
public function renameFile($file_id, $new_name) {
$api = 'rename';
return $this->_genericPost ( $api, $this->_getURI ( $this->_SERVICE_API [$api] ) . '?' . http_build_query ( array (
'root' => $this->_root,
'from_path' => $file_id,
'to_path' => addTrailingSlash ( _dirname ( $file_id ), '/' ) . $new_name 
) ) );
}
public function createFolder($path_id, $name) {
$api = 'mkdir';
return $this->_genericPost ( $api, $this->_getURI ( $this->_SERVICE_API [$api] ) . '?' . http_build_query ( array (
'root' => $this->_root,
'path' => addTrailingSlash ( $path_id, '/' ) . $name 
) ) );
}
}
?>