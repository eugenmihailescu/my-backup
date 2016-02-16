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
 * @file    : LogFile.php $
 * 
 * @id      : LogFile.php | Tue Feb 16 21:41:51 2016 UTC | Eugen Mihailescu eugenmihailescux@gmail.com $
*/

namespace MyBackup;
class LogFile {
private $_logrotate;
private $_logsize;
private $_logdir;
private $_log_filename;
private $_filter;
private $_branched;
private $_branch_id;
private $_rw_filter;
private $_rw_mode;
private function _getLogfile() {
if ( empty( $this->_log_filename ) )
return false;
return $this->_log_filename;
}
private function _validateFilename( $logfile ) {
if ( false === $logfile )
throw new MyException( 'The log filename is empty' );
$path = dirname( $logfile );
! file_exists( $path ) && mkdir( $path, 0770, true );
}
function __construct( $log_file = null, $settings = null ) {
global $_branch_id_;
$this->_logrotate = false;
$this->_logsize = 1;
$this->_logdir = ! empty( $log_file ) ? dirname( $log_file ) : ( defined( __NAMESPACE__.'\\LOG_DIR' ) ? LOG_DIR : sys_get_temp_dir() );
$this->_log_filename = ! empty( $log_file ) ? $log_file : null;
is_array( $settings ) && $this->initFromArray( $settings );
if ( defined( __NAMESPACE__.'\\BRANCHED_LOGS' ) && BRANCHED_LOGS && ! empty( $_branch_id_ ) ) {
$this->_setBranched();
} else {
$this->_branched = false;
$this->_branch_id = null;
$this->_rw_filter = null;
$this->_rw_mode = null;
}
}
private function _setBranched() {
global $COMPRESSION_FILTERS;
$this->_branched = true;
$this->_rw_filter = $COMPRESSION_FILTERS[GZ][0];
$this->_rw_mode = sprintf( $COMPRESSION_FILTERS[GZ][1], 9 );
$this->_setBranchId(); 
}
private function _setBranchId() {
global $_branch_id_;
$log_file = $this->_getLogfile();
$this->_branch_id = $_branch_id_;
$fname = basename( $log_file );
if ( ! $this->_branched )
$this->_log_filename = preg_replace( 
"@{$this->_branch_id}" . normalize_path( DIRECTORY_SEPARATOR ) . "$fname$@", 
$fname, 
$log_file ); 
else
$this->_log_filename = getBranchedFileName( $log_file ); 
! empty( $this->_rw_filter ) && $this->_log_filename .= '.' . $this->_rw_filter;
}
private function _rotateLog( $log_file ) {
if ( ! $this->_logrotate )
return false;
if ( NONE == $this->_filter ) {
$new_name = sprintf( '%s.%s', $log_file, date( 'Ymd-His' ) );
$success = move_file( $log_file, $new_name );
if ( ! $success )
return false;
else
return $new_name;
}
global $COMPRESSION_NAMES, $COMPRESSION_FILTERS;
$filter = '';
$ext = '.' . $COMPRESSION_NAMES[$this->_filter];
$filter = $COMPRESSION_FILTERS[$this->_filter][0];
$mode = sprintf( $COMPRESSION_FILTERS[$this->_filter][1], 9 ); 
if ( in_array( $this->_filter, array( GZ, BZ2 ) ) &&! _function_exists( $filter . 'open' ) )
throw new MyException( 
sprintf( 
_esc( 
'%s support is not enabled. Check your PHP configuration (php.ini) or contact your hosting provider.' ), 
strtoupper( $filter ) ) );
$output_file = $log_file . $ext;
$i = 0;
while ( file_exists( $output_file ) )
$output_file = sprintf( '%s-%d', $log_file . $ext, $i++ );
if ( ! file_exists( $log_file ) )
throw new MyException( sprintf( _esc( "Cannot rotate log file %s due to it doesn't exist" ), $log_file ) );
if ( '' != $filter ) {
$fw = _call_user_func( $filter . 'open', $output_file, $mode );
$fr = fopen( $log_file, 'rb' );
if ( false !== $fr ) {
while ( ! feof( $fr ) )
_call_user_func( $filter . 'write', $fw, fread( $fr, MB ) );
fclose( $fr );
}
_call_user_func( $filter . 'close', $fw );
}
return $output_file;
}
public function initFromArray( $array ) {
$options = array( 
'logdir' => '_logdir', 
'logrotate' => '_logrotate', 
'logsize' => '_logsize', 
'method' => '_filter', 
'current_user_id' => '_current_user_id' );
foreach ( $options as $key => $prop )
isset( $array[$key] ) && $this->$prop = $array[$key];
}
public function writeLog( $str ) {
$logfile = $this->_getLogfile();
$this->_validateFilename( $logfile );
if ( $this->_logrotate && file_exists( $logfile ) &&
filesize( $logfile ) + strlen( $str ) > $this->_logsize * MB )
if ( false !== $this->_rotateLog( $logfile ) )
@unlink( $logfile );
$line = is_string( $str ) ? $str : obsafe_print_r( $str, true );
if ( null == $this->_rw_filter ) {
if ( false === file_put_contents( $logfile, $line, FILE_APPEND ) )
trigger_error( _esc( 'Cannot write to the log file' ) . ' "' . $logfile . '"', E_USER_WARNING );
} else {
if ( file_exists( $logfile ) ) {
$log_data = gzfile( $logfile );
$log_data[] = $line;
$log_data = implode( $log_data );
} else
$log_data = $line;
$fw = _call_user_func( $this->_rw_filter . 'open', $logfile, $this->_rw_mode );
if ( flock( $fw, LOCK_EX ) ) {
_call_user_func( $this->_rw_filter . 'write', $fw, $log_data, strlen( $log_data ) );
flock( $fw, LOCK_UN );
_call_user_func( $this->_rw_filter . 'close', $fw );
} else
throw new MyException( sprintf( _esc( 'Cannot aquire exclusive lock for writting to %s' ), $logfile ) );
}
}
public function readLog() {
$logfile = $this->_getLogfile();
if ( null == $this->_rw_filter )
return file_get_contents( $logfile );
else {
$log_data = '';
$fw = _call_user_func( $this->_rw_filter . 'open', $logfile, $this->_rw_mode );
while ( ! feof( $fw ) )
$log_data .= _call_user_func( $this->_rw_filter . 'read', $fw, 4096 );
_call_user_func( $this->_rw_filter . 'close', $fw );
return $log_data;
}
}
public function getLastJobId() {
$result = false;
if ( false !== ( $fr = fopen( $this->_log_filename, 'r' ) ) ) {
$buff_len = min( 4096, filesize( $this->_log_filename ) );
if ( 0 == fseek( $fr, - $buff_len, SEEK_END ) && false !== ( $buff = fread( $fr, $buff_len ) ) ) {
$key = 'job_id:';
$p = strrpos( $buff, $key );
false !== $p && ( $p = strrpos( substr( $buff, 0, $p ), PHP_EOL ) );
$buff = substr( $buff, $p ); 
if ( preg_match( '/\[([\d\-\s\:]+)\][^\(]+\(' . $key . '\s*([\-\d]+)\)/', $buff, $matches ) )
$result = $matches;
}
fclose( $fr );
}
return $result;
}
}
?>