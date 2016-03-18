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
 * @version : 0.2.3-27 $
 * @commit  : 10d36477364718fdc9b9947e937be6078051e450 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Fri Mar 18 10:06:27 2016 +0100 $
 * @file    : LocalFilesMD5.php $
 * 
 * @id      : LocalFilesMD5.php | Fri Mar 18 10:06:27 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

require_once LIB_PATH . 'MyException.php';
require_once UTILS_PATH . 'files.php';
class LocalFilesMD5 {
private $_changed;
private $_log_filename;
private $_ref_log_filename;
private $_log_records;
public $onAbortCallback;
public $onProgressCallback;
public $onOutputCallback;
function __construct( $log_filename, $ref_log_filename = null ) {
if ( empty( $log_filename ) )
throw new MyException( _( 'An empty log filename is not acceptable.' ) );
empty( $ref_log_filename ) && $ref_log_filename = $log_filename;
$this->_changed = false;
$this->_log_filename = $log_filename;
$this->_ref_log_filename = $ref_log_filename;
$this->read();
}
function __destruct() {
$this->_changed && $this->write();
}
private function _checkFHandle( $fhandle ) {
if ( false === $fhandle ) {
$e = error_get_last();
throw new MyException( $e['message'], $e['type'] );
}
}
public function read() {
$this->_log_records = array();
if ( ! _file_exists( $this->_ref_log_filename ) )
return false;
$fr = fopen( $this->_ref_log_filename, 'r' );
$this->_checkFHandle( $fr );
while ( false !== ( $line = fgets( $fr ) ) ) {
$cols = explode( ',', $line );
if ( count( $cols ) < 3 )
continue;
$this->_log_records[$cols[0]] = array( $cols[1], 			
$cols[2] ); 
}
return fclose( $fr );
}
public function write() {
_is_callable( $this->onOutputCallback ) && _call_user_func( 
$this->onOutputCallback, 
_esc( 'writting the new MD5 signatures' ), 
BULLET, 
2 );
$fw = fopen( $this->_log_filename, 'w' );
$this->_checkFHandle( $fw );
foreach ( $this->_log_records as $filename => $cols )
fwrite( $fw, sprintf( '%s,%s,%d', $filename, $cols[0], $cols[1] ) . PHP_EOL );
$this->_changed = false;
return fclose( $fw );
}
public function sync( $dir, $pattern, $recursively = true, $add_empty_dir = true ) {
$abort_signal_received = false;
$array = getFileListByPattern( $dir, $pattern, $recursively, $add_empty_dir, false );
$i = 1;
$max = count( $array );
$timestamp = time();
foreach ( $array as $filename ) {
if ( _is_callable( $this->onAbortCallback ) &&
false !== ( $abort_signal_received = _call_user_func( $this->onAbortCallback ) ) )
break;
$this->file_sync( $filename );
_is_callable( $this->onProgressCallback ) &&
_call_user_func( $this->onProgressCallback, - 3, $dir, $i++, $max, 6 );
}
return $abort_signal_received ? false : $timestamp;
}
public function file_sync( $filename, $comp_timestamp ) {
$changed = false;
$current_md5 = _file_exists( $filename ) && _is_file( $filename ) ? md5_file( $filename ) : null;
if ( isset( $this->_log_records[$filename] ) ) {
$md5 = $this->_log_records[$filename][0];
$timestamp = $this->_log_records[$filename][1];
} else {
$md5 = false;
$timestamp = $comp_timestamp;
}
if ( ! $md5 || $md5 != $current_md5 ) {
$changed = true;
$timestamp = $timestamp <= $comp_timestamp ? time() : $timestamp;
$this->_log_records[$filename] = array( $current_md5, $timestamp );
}
$this->_changed = $this->_changed || $changed;
return $timestamp;
}
public function filter( $timestamp ) {
return array_filter( 
$this->_log_records, 
function ( $value ) use(&$timestamp ) {
return $timestamp == $value[1];
} );
}
public function diff( $filename, $comp_timestamp ) {
$tmp_file = tempnam( dirname( $this->_log_filename ), uniqid() );
if ( false === $tmp_file || false === ( $fr = fopen( $filename, 'r' ) ) )
return false;
if ( ! _file_exists( $filename ) )
throw new MyException( 
sprintf( _esc( 'Cannot compute the difference. File %s does not exist.' ), $filename ) );
$ftmp_file = fopen( $tmp_file, 'w' );
$i = 0;
$max = getFileLinesCount( $filename );
$files_added = 0;
while ( false !== ( $fname = fgets( $fr ) ) ) {
if ( _is_callable( $this->onAbortCallback ) &&
false !== ( $abort_signal_received = _call_user_func( $this->onAbortCallback ) ) )
break;
$fname = str_replace( PHP_EOL, '', $fname );
if ( empty( $fname ) )
continue;
if ( false !== ( $timestamp = $this->file_sync( $fname, $comp_timestamp ) ) && $timestamp > $comp_timestamp ) {
_is_callable( $this->onOutputCallback ) && _call_user_func( 
$this->onOutputCallback, 
sprintf( _esc( 'file %s changed' ), $fname, date( DATETIME_FORMAT, $comp_timestamp ) ), 
BULLET, 
2 );
fwrite( $ftmp_file, $fname . PHP_EOL );
$files_added++;
}
_is_callable( $this->onProgressCallback ) &&
_call_user_func( $this->onProgressCallback, - 2, $filename, $i++, $max, 2, 1 );
}
_is_callable( $this->onProgressCallback ) &&
_call_user_func( $this->onProgressCallback, - 2, $filename, $max, $max, 2, 1 );
fclose( $ftmp_file );
fclose( $fr );
$result = copy( $tmp_file, $filename );
unlink( $tmp_file );
return $result ? $files_added : false;
}
public function changed() {
return $this->_changed;
}
}
?>