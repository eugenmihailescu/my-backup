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
 * @file    : GenericArchive.php $
 * 
 * @id      : GenericArchive.php | Wed Dec 7 18:54:23 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

require_once LIB_PATH . 'MyException.php';
abstract class GenericArchive {
private $progress_perc;
private $archive_files;
private $fileName;
private $options;
public $provider;
public $cpusleep;
public $onAbortCallback;
public $onProgressCallback;
public $onStdOutput;
protected function _getFilterMode( $method, $level = 0, $read = true ) {
global $COMPRESSION_FILTERS;
if ( isset( $COMPRESSION_FILTERS[$method] ) && count( $COMPRESSION_FILTERS[$method] ) > 1 ) {
$filter = $COMPRESSION_FILTERS[$method][0];
$mode = $COMPRESSION_FILTERS[$method][1];
} else {
$filter = '';
$mode = '';
}
$mode = $read ? 'r' . ( GZ == $method ? 'b' : '' ) : sprintf( $mode, $level );
return array( $filter, $mode );
}
protected function _stdOutput( $str ) {
foreach ( explode( '\n', str_replace( PHP_EOL, '\n', $str ) ) as $str )
if ( _is_callable( $this->onStdOutput ) )
_call_user_func( $this->onStdOutput, "<yellow>$str</yellow>" );
else
echo $str, PHP_EOL;
}
protected function _addTrailingSlash( $path ) {
return $path .
( substr( $path, - strlen( DIRECTORY_SEPARATOR ) ) != DIRECTORY_SEPARATOR ? DIRECTORY_SEPARATOR : '' );
}
protected function _mk_dir( $path, $dir_sep = DIRECTORY_SEPARATOR ) {
return _file_exists( $path ) || mkdir( $path, 0770, true );
}
protected function _pipeStreams( $in, $out, $maxlength = -1, $offset = -1 ) {
$size = 0;
( - 1 != $offset ) && ( 0 == fseek( $in, $offset, SEEK_SET ) ) || $offset < 0 || $maxlength = 0; 
while ( ! feof( $in ) && ( - 1 == $maxlength || $size + TAR_BUFFER_LENGTH < $maxlength ) ) {
if ( false !== ( $buff = fread( $in, TAR_BUFFER_LENGTH ) ) )
$size += fwrite( $out, $buff );
}
- 1 != $maxlength && $maxlength - $size > 0 && $size += fwrite( $out, fread( $in, $maxlength - $size ) );
return $size;
}
protected function onProgress( 
$filename, 
$bw, 
$fsize, 
&$obj, 
$ptype = PT_COMPRESS, 
$running = 1, 
$reset_timer = false, 
$threshold = MB ) {
$update = true;
$perc = $bw;
$max = $fsize;
if ( $fsize && PT_WRITE == $ptype && $fsize < $threshold ) {
$max = 100;
$perc = ceil( 100 * $bw / $fsize );
$update = $this->progress_perc < $perc;
$this->progress_perc = $perc;
$perc > $max && $perc = $max; 
}
if ( $update && _is_callable( $obj->onProgressCallback ) ) {
_call_user_func( 
$obj->onProgressCallback, 
isset( $obj->provider ) ? $obj->provider : $this->provider, 
$filename, 
$perc, 
$max, 
$ptype );
}
( $cpu_sleep = $obj->getCPUSleep() ) > 0 && _usleep( 1000 * $cpu_sleep );
}
function __construct( $filename, $provider = null, $auto_ext = true ) {
$this->skip_empty_files = false;
$this->fileName = $filename;
$this->provider = $provider;
$this->cpusleep = 0;
null == $provider && $this->provider = - 2;
$this->onAbortCallback = null;
$this->onProgressCallback = null;
$this->options = array( 'skip_empty_files' => $this->skip_empty_files );
$this->archive_files = array();
}
public function addFile( $filename, $name = null, $compress = true ) {
if ( ! _file_exists( $filename ) )
throw new MyException( sprintf( _esc( 'File %s does not exist.' ), $filename ) );
$fsize = filesize( $filename );
$options = $this->getOptions( 'skip_empty_files' );
if ( ! $fsize && strToBool( $this->getOptions( 'skip_empty_files' ) ) )
return false;
$this->archive_files[$filename] = array( $name, $compress, $fsize );
return true;
}
public function compress( $method, $level ) {
$this->progress_perc = 0;
}
public function decompress( $method = null, $uncompress_size = 0 ) {
$this->progress_perc = 0;
}
abstract public function getArchiveFiles( $filename = null );
abstract public function extract( $filename = null, $dst_path = null, $force_extrct = true );
protected function getOptions( $name = null ) {
return empty( $name ) ? $this->options : ( isset( $this->options ) && isset( $this->options[$name] ) ? $this->options[$name] : false );
}
protected function getProvider() {
return $this->provider;
}
protected function getCPUSleep() {
return $this->cpusleep;
}
public function setOptions( $options ) {
$this->options = $options;
}
public function setCPUSleep( $ms ) {
$this->cpusleep = $ms;
}
public function setFileName( $filename = null ) {
$this->fileName = $filename;
}
public function getFileName() {
return $this->fileName;
}
public function getFileSize() {
if ( _file_exists( $this->fileName ) )
return filesize( $this->fileName );
else
return 0;
}
public function getUncompressedSize() {
$fsize = 0;
foreach ( $this->archive_files as $filename => $stat )
$fsize += $stat[2];
return $fsize;
}
public function getFileCount() {
return count( $this->archive_files );
}
public function unlink() {
$this->close();
@unlink( $this->fileName );
$this->archive_files = array();
}
public function isValidArchive( $filename, $method = null ) {
global $COMPRESSION_HEADERS, $COMPRESSION_NAMES;
$result = true;
if ( null === $method &&
preg_match( '/\.((' . implode( '|', $COMPRESSION_NAMES ) . ')$)/i', $filename, $matches ) )
preg_match( '/\D*/', $matches[2], $matches ) && $filter = $matches[0];
null !== $method && list( $filter, $mode ) = $this->_getFilterMode( $method );
if ( empty( $filter ) )
throw new MyException( sprintf( _esc( 'Could not determine the archive type for %s.' ), $filename ) );
if ( in_array( $method, array( GZ, BZ2 ) ) && ! function_exists( $filter . 'open' ) )
throw new MyException( 
sprintf( 
_esc( '%s support is not enabled. Check your PHP configuration (php.ini).' ), 
strtoupper( $filter ) ) );
if ( ! empty( $method ) ) {
$hdr_len = 0;
if ( isset( $COMPRESSION_HEADERS[$method] ) ) {
$hdr_len = $COMPRESSION_HEADERS[$method][0];
$hdr_pattern = $COMPRESSION_HEADERS[$method][1];
}
if ( _file_exists( $filename ) && $hdr_len > 0 && false !== ( $fr = fopen( $filename, 'rb' ) ) ) {
$buff = fread( $fr, $hdr_len );
$result = false !== $buff && preg_match( '/' . $hdr_pattern . '/', $buff );
fclose( $fr );
}
}
return $result;
}
public function fixArchiveCRLF( $filename, $method = null ) {
if ( $this->isValidArchive( $filename, $method ) )
return true;
$result = false;
if ( _file_exists( $filename ) && false !== ( $fr = fopen( $filename, 'rb' ) ) ) {
$buff = fread( $fr, TAR_BUFFER_LENGTH );
$offset = 0;
while ( false !== $buff && in_array( ord( substr( $buff, $offset, 1 ) ), array( 10, 13 ) ) )
$offset++;
fclose( $fr );
if ( $offset > 0 ) {
$b = basename( $filename );
$newname = str_replace( $b, 'fixing-' . $b, $filename );
$in = fopen( $filename, 'rb' );
$out = fopen( $newname, 'wb' );
false !== $in && false !== $out && $this->_pipeStreams( $in, $out, - 1, $offset );
false !== $in && fclose( $in );
false !== $out && fclose( $out );
$result = $this->isValidArchive( $newname, $method );
$result && $result = move_file( $newname, $filename );
$result && $this->_stdOutput( 
sprintf( 
_esc( 
'[!] Archive %s has %d invalid bytes (0D|0A) prepended. I fixed it for now but this should never happen.' ), 
$filename, 
$offset ) );
}
}
return $result;
}
public function setArchiveComment( $comment ) {
return false;
}
protected function setDefaultComment() {
$comment = 'Created with ' . ( defined( __NAMESPACE__.'\\WPMYBACKUP' ) ? WPMYBACKUP : '' ) .
( defined( __NAMESPACE__.'\\APP_VERSION_NO' ) ? ' v' . APP_VERSION_NO : '' );
$this->setArchiveComment( $comment );
}
public function setArchivePassword( $password ) {
return false;
}
public function open( $filename = null ) {
$this->setFileName( $filename );
return true;
}
public function close() {
return true;
}
}
?>