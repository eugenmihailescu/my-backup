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
 * @version : 0.2.3-34 $
 * @commit  : 433010d91adb8b1c49bace58fae6cd2ba4679447 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Wed Nov 30 15:38:35 2016 +0100 $
 * @file    : MyFtpWrapper.php $
 * 
 * @id      : MyFtpWrapper.php | Wed Nov 30 15:38:35 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
class MyFtpWrapper extends CurlFtpWrapper {
function __construct() {
parent::__construct ();
$this->_fverbose = false;
}
function __destruct() {
if (null != $this->_conn_handle)
@ftp_close ( $this->_conn_handle );
}
private function _throwException($message = null, $code = 0) {
$plugin_root = plugin_dir_path_wrapper ( dirname ( __DIR__ ) );
$err = error_get_last ();
if (null != $err && (0 === strpos ( $err ['file'], $plugin_root ) || ! in_array ( $err ['type'], array (
2,
8 
) ))) 
throw new MyException ( $err ['message'], $err ['type'] );
elseif (! empty ( $message ))
throw new MyException ( $message, $code );
}
private function _ftpConnect() {
if (is_resource ( $this->_conn_handle ))
return true; 
$ssl_func = 'ftp_ssl_connect';
$ftp_func = CURLPROTO_FTP | CURLPROTO_FTPS == $this->_protocol && function_exists ( $ssl_func ) ? $ssl_func : 'ftp_connect';
if (false == ($this->_conn_handle = @_call_user_func ( $ftp_func, $this->_host, $this->_port, $this->_request_timeout ))) {
$this->_conn_handle = null;
$this->_throwException ( sprintf ( _esc ( 'Could not connect the FTP server %s:%d' ), $this->_host, $this->_port ) );
}
if (false == @ftp_login ( $this->_conn_handle, $this->_user, $this->_password ))
$this->_throwException ( sprintf ( _esc ( 'Could not login with user "%s". Probably bad user or password' ), $this->_user ) );
if (false == ftp_pasv ( $this->_conn_handle, $this->_use_pasv ))
$this->_throwException ( _esc ( 'Could not turn the FTP connection into %s' ) . ($this->_use_pasv ? _esc ( 'passive' ) : _esc ( 'active' )) );
return is_resource ( $this->_conn_handle );
}
public function getFtpInfo($cached = true) {
return parent::getFtpInfo ();
}
public function getFtpFiles($path = '') {
if (! $this->_ftpConnect ())
return false;
$syst = $this->_getFtpServerType ();
$unix_style = isset ( $syst [0] ) && isset ( $syst [0] [0] ) ? preg_match ( '/unix/i', $syst [0] [0] ) : true; 
$this->_dir = addslashes ( $path );
$files = @ftp_rawlist ( $this->_conn_handle, $this->_dir );
$this->_throwException (); 
if (count ( $files ) > 0 && count ( preg_split ( '/\s+/', $files [0] ) ) > 8) 
$unix_style = true;
if (! is_array ( $files )) {
throw new MyException ( _esc ( 'Unknown error while fetching file list' ) );
}
return $unix_style ? $this->_parseUnixFilesOutput ( $files ) : $this->_parseWinFilesOutput ( $files );
}
public function ftpDownload($remote_file, $local_file) {
if (null != $this->onBytesReceived) {
$callback_ptr = array (
$this->onBytesReceived [2],
$this->onBytesReceived [3] 
);
$target = $this->onBytesReceived [0];
} else
$callback_ptr = null;
if (! $this->_ftpConnect ())
return false;
$fhandle = fopen ( $local_file, 'wb' );
$result = @ftp_nb_fget ( $this->_conn_handle, $fhandle, $remote_file, FTP_BINARY );
while ( $result == FTP_MOREDATA ) {
if (_is_callable ( $this->onAbortCallback ) && _call_user_func ( $this->onAbortCallback ))
break;
$foffset = ftell ( $fhandle );
null != $callback_ptr && _call_user_func ( $callback_ptr, $target, $local_file, $foffset, $fsize );
$result = @ftp_nb_continue ( $this->_conn_handle );
}
$foffset = ftell ( $fhandle );
null != $callback_ptr && _call_user_func ( $callback_ptr, $target, $local_file, $foffset, $fsize );
fclose ( $fhandle );
return FTP_FINISHED == $result ? $local_file : false;
}
public function uploadFile($local_file, $remote_file) {
if (null != $this->onBytesSent) {
$callback_ptr = array (
$this->onBytesSent [2],
$this->onBytesSent [3] 
);
$target = $this->onBytesSent [0];
} else
$callback_ptr = null;
if (! $this->_ftpConnect ())
return false;
$fsize = filesize ( $local_file );
$fhandle = fopen ( $local_file, 'rb' );
$result = @ftp_nb_fput ( $this->_conn_handle, $remote_file, $fhandle, FTP_BINARY );
while ( $result == FTP_MOREDATA && null == $this->_abort_received ) {
if (null != $this->onAbortCallback)
if ($this->_abort_received || false !== _call_user_func ( $this->onAbortCallback )) {
$this->_abort_received = true;
break;
}
$foffset = ftell ( $fhandle );
null != $callback_ptr && _call_user_func ( $callback_ptr, $target, $local_file, $foffset, $fsize );
$result = @ftp_nb_continue ( $this->_conn_handle );
}
$foffset = ftell ( $fhandle );
null != $callback_ptr && _call_user_func ( $callback_ptr, $target, $local_file, $foffset, $fsize );
fclose ( $fhandle );
if (FTP_FINISHED == $result)
return $result;
$this->_throwException ( sprintf ( _esc ( 'Ftp upload %s' ), ($this->_abort_received ? _esc ( 'aborted' ) : _esc ( 'failed' )) ) );
}
public function ftpExecRawCmds($raw_cmd, $cmd_params = null) {
$ok = $this->_ftpConnect ();
$result = array ();
$prepare_cmd = function ($raw_cmd, $cmd_params) {
$result = array ();
foreach ( $raw_cmd as $key => $cmd )
$result [] = $cmd . (! empty ( $cmd_params ) & is_array ( $cmd_params ) && isset ( $cmd_params [$key] ) ? ' ' . $cmd_params [$key] : '');
return $result;
};
if (! is_array ( $raw_cmd )) {
$raw_cmd = explode ( ',', $raw_cmd );
if (! (empty ( $cmd_params ) || is_array ( $cmd_params )))
$cmd_params = explode ( ',', $cmd_params );
}
if ($ok)
foreach ( $prepare_cmd ( $raw_cmd, $cmd_params ) as $cmd )
$result [] = ftp_raw ( $this->_conn_handle, $cmd );
return $result;
}
public function deleteFile($filename, $_is_dir = false, $bool_output = true) {
if ($this->_ftpConnect ())
return false;
if (! (empty ( $filename ) || is_array ( $filename )))
$filename = array (
$filename 
);
$fct_name = $_is_dir ? 'ftp_rmdir' : 'ftp_delete';
foreach ( $filename as $name ) {
$output = sprintf ( _esc ( "Deleting %s.." ), $name );
if (_call_user_func ( $fct_name, $this->_conn_handle, $name )) {
$output .= _esc ( 'successfully :-)' );
$success = 1;
} else {
$output .= _esc ( 'unsuccessfully :-(' );
$success = 0;
}
$result [] = ! $bool_output ? $output : $success;
}
return $result;
}
}
?>