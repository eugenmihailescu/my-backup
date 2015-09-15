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
 * @file    : GoogleCloudStorage.php $
 * 
 * @id      : GoogleCloudStorage.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;
define ( 'GOOGLE_SOCKET_TIMEOUT', 30 ); 
define ( 'GOOGLE_UPLOAD_LIMIT', 5 * (1024 * 1073741824) ); 
define ( 'GOOGLE_ROOT', 'root' );
class GoogleCloudStorage extends GenericCloudStorage {
const EOL = "\r\n";
private $_SERVICE_API = array (
'search' => 'search',
'download' => 'download',
'upload' => 'upload',
'delete' => 'trash',
'fileget' => 'files',
'share' => 'permissions',
'sharedfile' => 'host',
'rename' => 'rename',
'mkdir' => 'mkdir' 
);
private $_root;
private function _isValidUrl($url) {
return preg_match ( "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $url );
}
private function _downloadSharedFile($file_id, $api_key = null, $outStream = null) {
$outStream = empty ( $outStream ) ? 'php://output' : $outStream;
$url = $this->_getURI ( $this->_SERVICE_API ['sharedfile'] );
return $this->downloadUrl ( $url . $file_id, $api_key, $outStream );
}
protected function _getServiceName() {
return 'google';
}
protected function _getURI($function) {
$domain = GOOGLE_APIS_URL;
$oauth_version = GOOGLE_OAUTH_VERSION;
$path = 'drive/v';
switch ($function) {
case $this->_SERVICE_API ['upload'] :
$path = 'upload/drive/v';
$params = 'files?uploadType=multipart';
break;
case $this->_SERVICE_API ['search'] :
$params = 'files?q=';
break;
case $this->_SERVICE_API ['sharedfile'] :
$domain = 'googledrive.com';
$path = $function;
$oauth_version = '';
$params = '';
break;
case $this->_SERVICE_API ['fileget'] :
case $this->_SERVICE_API ['delete'] :
case $this->_SERVICE_API ['share'] :
case $this->_SERVICE_API ['rename'] :
case $this->_SERVICE_API ['mkdir'] :
$params = 'files';
break;
}
return sprintf ( '%s://%s/%s%s/%s', GOOGLE_PROTOCOL, $domain, $path, $oauth_version, $params );
}
function __construct($oauth_session) {
parent::__construct ( $oauth_session );
$oauth_session->mergeAPIS ( $this->_SERVICE_API );
$this->_root = GOOGLE_ROOT;
}
function getFile($file_id) {
$api = $this->_SERVICE_API ['fileget'];
$api_key = $this->getOAuthSession ()->getAPIKey ();
$url = $this->_getURI ( $api ) . '/' . $file_id . (! empty ( $api_key ) ? '?key=' . $api_key : '');
try {
$response = $this->getOAuthSession ()->curlPOST ( $url, $this->_getHeader ( $api ) );
} catch ( MyException $e ) {
$response = $this->_encodeError ( $e );
}
return json_decode ( $response, true );
}
function getFreeSpace($cached = false) {
return $this->_getFreeSpace ( 'quotaBytesTotal', 'quotaBytesUsed', $cached );
}
function getQuota($cached = false) {
return $this->_getQuota ( 'quotaBytesTotal', $cached );
}
function getUploadLimit($mime_type = null) {
$all_others = GOOGLE_UPLOAD_LIMIT;
$result = - 1;
$acc_info = $this->getOAuthSession ()->getAccountInfo ();
if (! empty ( $acc_info ) && isset ( $acc_info ['maxUploadSizes'] )) {
$maxUploadSizes = $acc_info ['maxUploadSizes'];
foreach ( $maxUploadSizes as $item )
if (0 == strcmp ( $mime_type, $item ['type'] )) {
$result = $item ['size'];
break;
} elseif (0 == strcmp ( '*', $item ['type'] )) {
$all_others = $item ['size'];
}
}
return $result < 0 ? $all_others : $result;
}
function metadata($path, $include_trash = false) {
return $this->searchFileNames ( $path, null, $include_trash );
}
private function _socketWriteMultiparts($socketHandle, $mime_boundary, $filename, $description, $target_path_id) {
if (class_exists ( 'finfo' )) {
$finfo = new \finfo ( FILEINFO_MIME_TYPE );
$file_mime_type = print_r ( $finfo->file ( $filename ), true );
} else
$file_mime_type = 'application/octet-stream';
$google_file = new \stdClass ();
$google_file->title = _basename ( $filename, DIRECTORY_SEPARATOR );
$google_file->description = $description;
$google_file->labesl = array (
'starred' => true 
);
$google_file->parents = array (
! empty ( $target_path_id ) ? array (
'id' => $target_path_id 
) : null 
);
$metadata = array (
'--' . $mime_boundary,
'Content-Type: application/json; charset=UTF-8' . self::EOL,
json_encode ( $google_file ) . self::EOL,
'--' . $mime_boundary,
'Content-Type: ' . $file_mime_type . self::EOL 
);
$metadataFooter = self::EOL . "--" . $mime_boundary . "--" . self::EOL . self::EOL;
$metadataHeader = '';
foreach ( $metadata as $data )
$metadataHeader .= $data . self::EOL;
$header = array (
'POST /upload/drive/v' . GOOGLE_OAUTH_VERSION . '/files?uploadType=multipart HTTP/1.1',
'Host: ' . GOOGLE_APIS_URL,
'Authorization: Bearer ' . $this->getOAuthSession ()->getOauthToken (),
"Content-Type: multipart/related; boundary=\"$mime_boundary\"",
'Content-Length:' . strval ( strlen ( $metadataHeader ) + filesize ( $filename ) + strlen ( $metadataFooter ) ),
'Connection: close',
self::EOL,
$metadataHeader  // the POST metadata/media's part
);
$total_bytes = strlen ( implode ( self::EOL, $header ) ) + 1 + filesize ( $filename ) + strlen ( $metadataFooter );
$bytes = 0;
$callback = null;
if (is_array ( $this->onBytesSent ))
$callback = array (
$this->onBytesSent [2],
$this->onBytesSent [3] 
);
foreach ( $header as $hdr ) {
$buffer = $hdr . self::EOL;
fputs ( $socketHandle, $buffer );
$bytes += strlen ( $buffer );
if (_is_callable ( $callback ))
_call_user_func ( $callback, GOOGLE_TARGET, $filename, $bytes, $total_bytes );
}
if ($fin = fopen ( $filename, 'rb' )) {
while ( ! feof ( $fin ) ) {
$buffer = fread ( $fin, FILE_BUFFER_SIZE );
if (false === $buffer)
throw new MyException ( sprintf ( 'Error while reading the file %s', $filename ) );
fputs ( $socketHandle, $buffer );
$bytes += strlen ( $buffer );
if (_is_callable ( $callback ))
_call_user_func ( $callback, GOOGLE_TARGET, $filename, $bytes, $total_bytes );
}
fclose ( $fin );
}
fputs ( $socketHandle, $metadataFooter );
$bytes += strlen ( $metadataFooter );
if (_is_callable ( $callback ))
_call_user_func ( $callback, GOOGLE_TARGET, $filename, $bytes, $total_bytes );
}
function uploadFile($filename, $target_path_id = null, $description = null) {
$this->_uploadFile ( $filename, $target_path_id );
$response = '';
$mime_boundary = md5 ( time () );
$proto = '';
$port = 80;
if ('https' == GOOGLE_PROTOCOL) {
if (! extension_loaded ( 'openssl' ))
throw new MyException ( sprintf ( _esc ( '%s extension not enabled.' ), 'OpenSSL' ) );
$proto = 'ssl://';
$port = 443;
}
if ($socketHandle = fsockopen ( $proto . GOOGLE_APIS_URL, $port, $errno, $errstr, GOOGLE_SOCKET_TIMEOUT )) {
$description = empty ( $description ) ? sprintf ( _esc ( 'Backup created automatically by %s v%s (%s)' ), @WPMYBACKUP, APP_VERSION_ID, $this->_selfUrl ( false, false ) ) : $description;
$this->_socketWriteMultiparts ( $socketHandle, $mime_boundary, $filename, $description, $target_path_id );
while ( ! feof ( $socketHandle ) )
$response .= fgets ( $socketHandle, FILE_BUFFER_SIZE );
fclose ( $socketHandle );
} else
throw new MyException ( $errstr, $errno );
return json_decode ( substr ( $response, strpos ( $response, '{' ) ), true );
}
public function downloadFile($file_id, $outStream = null, $api_key = null) {
$keys = array (
'exportLinks',
'downloadUrl' 
);
if (! empty ( $file_id )) {
$parts = explode ( '/', $file_id );
$file_meta = $this->getFile ( end ( $parts ) );
if (is_array ( $file_meta ))
foreach ( $keys as $key )
if (isset ( $file_meta [$key] )) {
$download_url = $file_meta [$key];
break;
}
if (isset ( $download_url ))
return $this->downloadUrl ( $download_url, $outStream, $api_key );
}
throw new MyException ( 'Invalid file_id', 404 );
}
function downloadUrl($download_url, $outStream = null, $api_key = null) {
if (false === $this->_isValidUrl ( $download_url ))
return print_r ( $this->getOAuthSession ()->getErrorArray ( new MyException ( sprintf ( _esc ( 'Invalid URL: %s' ), $download_url ), 400 ) ), true );
if (empty ( $api_key ))
$api_key = $this->getOAuthSession ()->getAPIKey ();
$download_url = $this->_stripUrlParams ( $download_url, array (
'gd',
'key' 
) ) . '&key=' . $api_key;
if (empty ( $outStream )) {
return $this->getOAuthSession ()->locationRedirect ( $download_url );
}
try {
$api = $this->_SERVICE_API ['fileget'];
return $this->getOAuthSession ()->curlPOST ( $download_url, $this->_getHeader ( $api ), null, $outStream, null, 'GET', $this->onBytesReceived, $this->onAbort );
} catch ( MyException $e ) {
$old_result = $this->_encodeError ( $e );
}
if (preg_match ( '/\/(\w*)\?/si', $download_url, $matches )) {
$file_id = $matches [1];
if (! ($result = $this->_downloadSharedFile ( $file_id, $api_key, $outStream )))
$result = $this->getOAuthSession ()->locationRedirect ( $download_url );
return $result;
}
return $old_result;
}
function searchFileNames($basePath, $query, $include_trash = false, $cached = false) {
if ($cached && false !== ($result = $this->_getCachedMetadata ( $basePath, $query )))
return $result;
$api = 'search';
$url = $this->_getURI ( $this->_SERVICE_API [$api] );
$qry = array ();
if (! empty ( $basePath ))
$qry [] = "'$basePath' in parents";
if (! $include_trash)
$qry [] = 'trashed=false';
if (! empty ( $query ))
$qry [] = $query;
$result = $this->_genericPost ( $api, $url . urlencode ( implode ( ' and ', $qry ) ) );
$this->_setCachedMetadata ( $basePath, $query, $result );
return $result;
}
function deleteFile($file_id, $move2trash = true) {
$api = $this->_SERVICE_API ['delete'];
$url = $this->_getURI ( $api ) . '/' . $file_id . ($move2trash ? '/' . $api : '');
return $this->_deleteFile ( $url, '',$move2trash ? 'POST' : 'DELETE' );
}
function shareFile($file_id, $api_key = null, $role = null, $type = null, $short_url = null) {
$api = $this->_SERVICE_API ['share'];
$api_key = $this->getOAuthSession ()->getAPIKey ();
$url = $this->_getURI ( $api ) . '/' . $file_id . '/' . $api . (! empty ( $api_key ) ? '&key=' . $api_key : $this->getOAuthSession ()->getAPIKey ());
return $this->_genericPost ( 'share', $url, json_encode ( array (
'role' => $role,
'type' => $type 
) ) );
}
public function renameFile($file_id, $new_name) {
$api = 'rename';
return $this->_genericPost ( $api, $this->_getURI ( $this->_SERVICE_API [$api] ) . '/' . $file_id, json_encode ( array (
'title' => $new_name 
) ), 'PUT' );
}
public function createFolder($path_id, $name) {
$google_folder = new \stdClass ();
$google_folder->title = $name;
$google_folder->mimeType = 'application/vnd.google-apps.folder';
$google_folder->parents = array (
array (
'id' => $path_id 
) 
);
$api = 'mkdir';
return $this->_genericPost ( $api, $this->_getURI ( $this->_SERVICE_API [$api] ), json_encode ( $google_folder ) );
}
}
?>
