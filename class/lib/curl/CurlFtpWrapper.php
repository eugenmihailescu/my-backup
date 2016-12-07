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
 * @file    : CurlFtpWrapper.php $
 * 
 * @id      : CurlFtpWrapper.php | Wed Dec 7 18:54:23 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

require_once 'CurlWrapper.php';
class CurlFtpWrapper extends CurlWrapper {
private $_cached_metadata;
protected $_date_format;
protected $_cached;
protected $_dir;
protected $_port;
protected $_host;
protected $_user;
protected $_password;
protected $_dir_separator;
public $onBytesReceived;
public $onBytesSent;
public $onAbortCallback;
function __construct() {
$this->_protocol = CURLPROTO_FTP | CURLPROTO_FTPS;
parent::__construct ();
$this->_protocol_status_codes = FtpStatusCodes::getStatusCodes ();
$this->_dir_separator = '/';
$this->_use_pasv = true;
$this->_force_ssl = false;
$this->_ssl_control_only = false;
$this->_port = 21;
$this->_active_port = null;
$this->_host = "127.0.0.1"; 
$this->_user = "anonymous";
$this->_password = null;
$this->_dir = $this->_dir_separator;
$this->_date_format = null;
$this->_cached = true;
$this->onBytesSent = null;
$this->onBytesReceived = null;
$this->_cached_metadata = array ();
$codes = array ();
foreach ( array_keys ( $this->_protocol_status_codes ) as $status_code )
if ($status_code < 300)
$codes [] = $status_code;
$this->setOKStatusCodes ( $codes );
}
protected function _getUrl($filename = '', $host_only = false) {
switch ($this->_protocol) {
case CURLPROTO_SFTP :
$proto = 'sftp';
break;
case CURLPROTO_FTP :
case CURLPROTO_FTPS :
case CURLPROTO_FTP | CURLPROTO_FTPS :
$proto = 'ftp';
break;
case CURLPROTO_SCP :
$proto = 'scp';
break;
default :
$proto = $this->_protocol;
break;
}
$host = $this->_host . ('/' != substr ( $this->_host, - 1, 1 ) ? '/' : '');
$url = $proto . '://' . (empty ( $this->_user ) ? '' : (urlencode ( $this->_user ) . (empty ( $this->_password ) ? '' : (':' . urlencode ( $this->_password ))) . '@')) . $host;
if ($host_only)
return $url;
$dir = str_replace ( $this->_dir_separator, '/', $this->_dir );
$dir .= '/' == substr ( $dir, - 1, 1 ) ? '' : '/'; 
return $url . $dir . $filename;
}
protected function _getCacheInfo($type) {
$cache_id = sprintf ( '%d:%s://%s', $this->_protocol, $type, $this->_host );
if (isset ( $_SESSION ) && isset ( $_SESSION [$cache_id] ))
return $_SESSION [$cache_id];
return false;
}
protected function _setCacheInfo($type, $info) {
isset ( $_SESSION ) && add_session_var ( sprintf ( '%d:%s://%s', $this->_protocol, $type, $this->_host ), $info );
}
protected function _getFtpServerType($cached = true) {
$server_type = $this->_getCacheInfo ( 'ftp' );
if ($cached && false !== $server_type) {
return $server_type;
}
$raw_cmd = array (
'SYST',
'STAT' 
);
$server_type = $this->ftpExecRawCmds ( $raw_cmd );
$ssl_info = $this->getSSLInfo ( false );
$this->_setCacheInfo ( 'ftp', $server_type );
$this->_setCacheInfo ( 'ssl', $ssl_info );
return $server_type;
}
protected function _parseUnixFilesOutput($files) {
$items = array ();
foreach ( $files as $file ) {
if (empty ( $file ))
continue;
$parts = preg_split ( "/\s+/", $file );
if (count ( $parts ) < 8)
continue;
$item = array_slice ( $parts, 0, 5 );
$date = $parts [5] . ' ' . $parts [6] . ' ' . $parts [7];
$item [] = null !== $this->_date_format ? date_parse_from_format ( $this->_date_format, $date ) : strtotime ( $date ); 
$item [] = $parts [0] {0} === 'd' ? true : false;
$fname = implode ( ' ', array_slice ( $parts, 8 ) );
$items [$fname] = $item;
}
return $items;
}
protected function _parseWinFilesOutput($files) {
$items = array ();
foreach ( $files as $file ) {
if (empty ( $file ))
continue;
$parts = preg_split ( "/\s+/", $file );
$date = $parts [0] . ' ' . $parts [1];
$obj = new \DateTime ();
$time = $obj->createFromFormat ( $this->_date_format, $date );
$size = preg_match ( '/[^\d]/', $parts [2] ) ? false : intval ( $parts [2] );
$item = array_fill ( 0, 4, null );
$item [] = $size;
$item [] = null !== $this->_date_format ? $time->getTimestamp () : strtotime ( $date ); 
$item [] = false === $size;
$fname = implode ( ' ', array_slice ( $parts, 3 ) );
$items [$fname] = $item;
}
return $items;
}
protected function _getCurlOptions() {
$options = array (
CURLOPT_PORT => $this->_port,
CURLOPT_PROTOCOLS => $this->_protocol 
);
return $options + parent::_getCurlOptions ();
}
public function getSSLInfo($cached = true) {
if (! $cached)
return parent::getSSLInfo ();
return $this->_getCacheInfo ( 'ssl' );
}
public function setFtpParams($array_settings) {
if (! is_array ( $array_settings ))
throw new MyException ( 'Internal error. Invalid $settings param' );
$this->initFromArray ( $array_settings ); 
$ftp_options = array (
'ftphost' => '_host',
'ftpport' => '_port',
'ftp_active_port' => '_active_port',
'ftpuser' => '_user',
'ftppwd' => '_password',
'ftppasv' => '_use_pasv',
'ftpproto' => '_protocol',
'ftp' => '_dir',
'ftp_ssl_ver' => '_ssl_ver',
'ftp_ssl_chk_peer' => '_ssl_chk_peer',
'ftp_throttle' => '_upl_throttle' 
);
foreach ( $ftp_options as $key => $value )
isset ( $array_settings [$key] ) && $this->$value = $array_settings [$key];
if (! empty ( $array_settings ['ftp_cainfo'] ))
$this->_ssl_cainfo = $array_settings ['ftp_cainfo'];
}
public function getFtpFiles($path = '') {
$this->_initConnHandle ();
if ($this->_protocol & (CURLPROTO_FTP | CURLPROTO_FTPS) > 0) {
$syst = $this->getFtpInfo ();
$unix_style = preg_match ( '/unix/i', $syst ['systype'] ); 
} else
$unix_style = true;
$this->_dir = addslashes ( $path );
$cmd_options = array (
CURLOPT_URL => $this->_getUrl (),
CURLOPT_RETURNTRANSFER => true 
);
$this->setCurlOptions ( $cmd_options );
$ok = $this->_execCurl ();
$files = preg_split ( '/' . "\n" . '/', $ok );
if (count ( $files ) > 0 && count ( preg_split ( '/\s+/', $files [0] ) ) > 8) 
$unix_style = true;
return $unix_style ? $this->_parseUnixFilesOutput ( $files ) : $this->_parseWinFilesOutput ( $files );
}
public function getFtpInfo($cached = true) {
if ('CurlFtpWrapper' == get_class ( $this ))
$this->_initConnHandle ();
$syst = $this->_getFtpServerType ( $cached );
$keys = array_keys ( $syst );
foreach ( $syst as $key => $value )
if (is_array ( $value ))
$syst [$key] = implode ( ' ', $value );
$systype = count ( $syst ) > 0 ? $syst [$keys [0]] : null;
$sysname = count ( $syst ) > 1 ? (preg_match ( '/(?=[\w]).*(?<=[\w\d])/', $syst [$keys [1]], $matches ) ? $matches [0] : '') : null;
if (preg_match ( '/status of(.*)/i', $sysname, $matches ))
$sysname = trim ( $matches [1] );
return array (
'systype' => $systype,
'sysname' => $sysname 
);
}
public function setDateFormat($format) {
$this->_date_format = $format;
}
public function setDirSeparator($separator) {
$this->_dir_separator = $separator;
}
public function ftpDownload($remote_file, $local_file) {
$this->_dir = dirname ( $remote_file );
return $this->curlPOST ( $this->_getUrl ( basename ( $remote_file ) ), null, null, $local_file, null, null, $this->onBytesReceived );
}
public function uploadFile($local_file, $remote_file) {
$this->_dir = '/' == dirname ( $remote_file ) ? '' : dirname ( $remote_file );
$this->curlPOST ( $this->_getUrl ( basename ( $remote_file ) ), null, null, null, $local_file, null, $this->onBytesSent );
return true;
}
public function ftpExecRawCmds($raw_cmd, $cmd_params = null) {
$parse_result = function ($buffer, $array) {
$result = array ();
foreach ( $array as $key => $cmd )
if (preg_match ( '/>\s' . $cmd . '([^>]+)/i', $buffer, $matches ))
if (preg_match_all ( '/^<\s+\d*(.+)/m', $matches [1], $matches2 ))
$result = array_merge ( $result, array (
$cmd => $matches2 [1] 
) );
else
$result [$cmd] = $matches [1];
return $result;
};
$prepare_cmd = function ($raw_cmd, $cmd_params) {
$result = array ();
foreach ( $raw_cmd as $key => $cmd )
$result [] = $cmd . (! empty ( $cmd_params ) & is_array ( $cmd_params ) && isset ( $cmd_params [$key] ) && ! empty ( $cmd_params [$key] ) ? ' ' . $cmd_params [$key] : '');
return $result;
};
$this->_initConnHandle ();
if (! is_array ( $raw_cmd )) {
$raw_cmd = explode ( ',', $raw_cmd );
if (! (empty ( $cmd_params ) || is_array ( $cmd_params )))
$cmd_params = explode ( ',', $cmd_params );
}
$cmd_options = array (
CURLOPT_QUOTE => $prepare_cmd ( $raw_cmd, $cmd_params ),
CURLOPT_RETURNTRANSFER => true,
CURLOPT_URL => $this->_getUrl ( null, true ) 
);
$this->setExcludeBody ( true );
$this->setCurlOptions ( $cmd_options );
$this->_execCurl ();
return $parse_result ( $this->curlGetDebugOutput (), $raw_cmd );
}
public function deleteFile($filename, $_is_dir = false, $bool_output = true) {
if (! (empty ( $filename ) || is_array ( $filename )))
$filename = array (
$filename 
);
foreach ( $filename as $name ) {
try {
$output = "Deleting $name..";
$this->ftpExecRawCmds ( CURLPROTO_SFTP == $this->_protocol ? ($_is_dir ? 'rmdir' : 'rm') : ($_is_dir ? 'RMD' : 'DELE'), $name );
$output .= 'successfully :-)';
$success = 1;
} catch ( MyException $e ) {
$output .= 'unsuccessfully :-(';
$success = 0;
}
$result [] = ! $bool_output ? $output : $success;
}
return $result;
}
protected function _getCachedMetadata($basePath, $query) {
if (isset ( $this->_cached_metadata [$basePath] )) {
$keys = array_keys ( $this->_cached_metadata [$basePath] );
$match = false;
foreach ( $keys as $key )
if ($match = ($query == $key))
break;
if (! $match)
foreach ( $keys as $key )
if ($match = (0 === strpos ( $query, $key )))
break;
if ($match)
return $this->_cached_metadata [$basePath] [$key];
}
return false;
}
protected function _setCachedMetadata($basePath, $query, $data) {
$this->_cached_metadata [$basePath] [$query] = $data;
}
function searchFileNames($basePath, $query, $cached = false) {
if ($cached && false !== ($result = $this->_getCachedMetadata ( $basePath, $query )))
return $result;
$result = array ();
$files = $this->getFtpFiles ( $basePath );
if (is_array ( $files ))
foreach ( $files as $filename => $file_info ) {
if (0 === strpos ( $filename, $query ))
$result [$filename] = $file_info;
}
$this->_setCachedMetadata ( $basePath, $query, $result );
return $result;
}
public function getQuota() {
return 0;
}
public function getFreeSpace() {
return PHP_INT_MAX;
}
public function getUploadLimit() {
return PHP_INT_MAX;
}
public function downloadFile($remote_file, $local_file) {
return $this->ftpDownload ( $remote_file, $local_file );
}
}
?>