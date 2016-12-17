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
 * @file    : TarArchive.php $
 * 
 * @id      : TarArchive.php | Tue Dec 13 06:40:49 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

define( __NAMESPACE__.'\\TAR_MAGIC', 'ustar ' );
define( __NAMESPACE__.'\\TAR_VERSION', '00' );
define( __NAMESPACE__.'\\TAR_VERSION_EX', " \0" );
define( __NAMESPACE__.'\\TAR_MAGIC_OFFSET', 257 );
define( __NAMESPACE__.'\\TAR_EXTHEADER_LEN', 512 );
define( __NAMESPACE__.'\\TAR_BUFFER_LENGTH', 8192 );
define( __NAMESPACE__.'\\TAR_LONGLINK', '././@LongLink' );
define( __NAMESPACE__.'\\BZ_OK', 0 );
require_once LIB_PATH . 'MyException.php';
class TarArchive extends GenericArchive {
function __construct( $filename, $provider = null, $auto_ext = true ) {
parent::__construct( $filename . ( $auto_ext ? '.tar' : '' ), $provider );
$this->setDefaultComment();
}
private function _isValidHeader( $header ) {
$tar_maginc_len = strlen( TAR_MAGIC );
$ext_header = TAR_LONGLINK == substr( $header, 0, strlen( TAR_LONGLINK ) );
$offset = $ext_header ? 2 * TAR_EXTHEADER_LEN : 0;
$tar_magic_ok = TAR_MAGIC == substr( $header, $offset + TAR_MAGIC_OFFSET, $tar_maginc_len );
$tar_version_ok = TAR_VERSION ==
substr( $header, $offset + TAR_MAGIC_OFFSET + $tar_maginc_len, strlen( TAR_VERSION ) );
$tar_version_ext_ok = TAR_VERSION_EX ==
substr( $header, $offset + TAR_MAGIC_OFFSET + $tar_maginc_len, strlen( TAR_VERSION_EX ) );
return $tar_magic_ok && ( $tar_version_ok || $tar_version_ext_ok );
}
private function _getFileHeader( $filename, $name = null ) {
$finfo = stat( $filename );
$falias = null == $name ? $filename : $name;
if ( strlen( $falias ) > 0 && DIRECTORY_SEPARATOR == substr( $falias, 0, 1 ) )
$falias = substr( $falias, 1 );
if ( strlen( $falias ) > 512 )
return false;
preg_match( '/^(\w:)?\\' . DIRECTORY_SEPARATOR . '(.*)/', $falias, $matches ) && $falias = $matches[2];
$falias = str_replace( '\\', '/', $falias ); 
$bigheader = $header = '';
if ( function_exists( '\\posix_getpwuid' ) ) {
$posix_getpwuid = posix_getpwuid( $finfo['uid'] );
$posix_getpwuid = posix_getpwuid( $finfo['gid'] );
$user_name = $posix_getpwuid["name"];
$group_name = $posix_getpwuid["name"];
} else {
$user_name = getenv( 'USERNAME' );
$group_name = $user_name;
}
if ( strlen( $falias ) > 100 ) {
$bigheader = pack( 
"a100a8a8a8a12a12a8a1a100a6a2a32a32a8a8a155a12", 
TAR_LONGLINK, 
sprintf( '%08o', $finfo['mode'] ), 
'0000000', 
'0000000', 
sprintf( "%011o", strlen( $falias ) ), 
'00000000000', 
str_repeat( ' ', 8 ), 'L', 
'', 
TAR_MAGIC, 
TAR_VERSION, 
$user_name, 
$group_name, 
'', 
'', 
'', 
'' );
$bigheader .= pack( "a512", $falias );
$checksum = 0;
for ( $i = 0; $i < TAR_EXTHEADER_LEN; $i++ )
$checksum += ord( substr( $bigheader, $i, 1 ) );
$bigheader = substr_replace( $bigheader, sprintf( "%06o", $checksum ) . "\0 ", 148, 8 );
}
switch ( filetype( $filename ) ) {
case 'dir' :
$typeflag = 5;
break;
case 'link' :
$typeflag = 1;
break;
case 'fifo' :
$typeflag = 6;
break;
case 'char' :
$typeflag = 3;
break;
case 'block' :
$typeflag = 4;
break;
default :
$typeflag = 0; 
break;
}
$header = pack( "a100a8a8a8a12a12a8a1a100a6a2a32a32a8a8a155a12", 		
substr( $falias, 0, 100 ), 		
sprintf( '%08o', $finfo['mode'] ), 		
sprintf( "%08o", $finfo['uid'] ), 		
sprintf( "%08o", $finfo['gid'] ), 		
sprintf( "%012o", $finfo['size'] ), 		
sprintf( "%012o", $finfo['mtime'] ), 		
str_repeat( ' ', 8 ), 		
$typeflag, 		
is_link( $filename ) ? readlink( $filename ) : '', 		
TAR_MAGIC, 		
TAR_VERSION, 		
$user_name, 		
$group_name, 		
'', 		
'', 		
substr( $falias, 100, 155 ), 		
'' );
$checksum = 0;
for ( $i = 0; $i < TAR_EXTHEADER_LEN; $i++ )
$checksum += ord( substr( $header, $i, 1 ) );
$header = substr_replace( $header, sprintf( "%06o", $checksum ) . "\0 ", 148, 8 );
return $bigheader . $header; 
}
private function _extractHeader( $buffer ) {
$blen = strlen( $buffer );
if ( $blen < TAR_EXTHEADER_LEN )
return false;
$trim = function ( $var ) {
return rtrim( ltrim( str_replace( "\0", '', $var ) ) );
};
if ( ! $this->_isValidHeader( $buffer ) )
return false;
if ( $ext_header = TAR_LONGLINK == substr( $buffer, 0, strlen( TAR_LONGLINK ) ) ) {
$fname = substr( $buffer, TAR_EXTHEADER_LEN, TAR_EXTHEADER_LEN );
$buffer = substr( $buffer, 2 * TAR_EXTHEADER_LEN );
} else
$fname = substr( $buffer, 0, 100 );
$fmode = substr( $buffer, 100, 8 );
$uid = substr( $buffer, 108, 8 );
$gid = substr( $buffer, 116, 8 );
$fsize = substr( $buffer, 124, 12 );
$mtime = substr( $buffer, 136, 12 );
$chksum = substr( $buffer, 148, 8 );
$is_link = substr( $buffer, 156, 1 );
$linkname = substr( $buffer, 157, 100 );
$result = array( 
'mode' => octdec( $fmode ), 
'uid' => octdec( $uid ), 
'gid' => octdec( $gid ), 
'size' => octdec( $fsize ), 
'time' => octdec( $mtime ), 
'chksum' => octdec( $chksum ), 
'link' => $is_link, 
'linkname' => $trim( $linkname ) );
$prefix = '';
if ( $ext_header ) {
$uname = substr( $buffer, 265, 32 );
$gname = substr( $buffer, 297, 32 );
$dev_major = substr( $buffer, 329, 8 );
$dev_minor = substr( $buffer, 337, 8 );
$result += array( 
'uname' => $trim( $uname ), 
'gname' => $trim( $gname ), 
'major' => $trim( $dev_major ), 
'minor' => $trim( $dev_minor ) );
}
$result['name'] = $trim( $fname );
return $result;
}
public function addFile( $filename, $name = null, $compress = true ) {
if ( $abort_signal_received = ! parent::addFile( $filename, $name, $compress ) )
return false;
$fsize = filesize( $filename );
$err = false;
if ( false === ( $header = $this->_getFileHeader( $filename, $name ) ) )
return false;
$fw = fopen( $this->getFileName(), 'ab' );
if ( false !== $fw )
fwrite( $fw, $header );
else
throw new MyException( sprintf( _esc( 'Cannot open file %s in write-mode' ), $this->getFileName() ) );
try {
$chunksize = MB; 
if ( $fsize > $chunksize ) {
$bw = 0;
$fr = fopen( $filename, 'rb' );
if ( flock( $fr, LOCK_EX ) ) {
while ( ! feof( $fr ) && false === $abort_signal_received ) {
if ( _is_callable( $this->onAbortCallback ) && ( $abort_signal_received = $abort_signal_received ||
false !== _call_user_func( $this->onAbortCallback ) ) )
break;
$bw += fwrite( $fw, fread( $fr, $chunksize ) );
$this->onProgress( $filename, $bw, $fsize, $this, PT_WRITE );
}
flock( $fr, LOCK_UN );
} else {
$this->_stdOutput( _esc( 'Could not gain exclusive access for ' ) . $filename );
fseek( $fw, - strlen( $header ), SEEK_END ); 
}
fclose( $fr );
} else {
fwrite( $fw, file_get_contents( $filename ) );
}
$pad_len = ceil( ( $fsize ) / TAR_EXTHEADER_LEN ) * TAR_EXTHEADER_LEN - $fsize;
$pad_len > 0 && fwrite( $fw, str_repeat( chr( 0 ), $pad_len ) );
} catch ( \Exception $err ) {
}
fclose( $fw );
if ( false !== $err )
throw new MyException( $err->getMessage(), $err->getCode(), $err->getPrevious() );
$this->onProgress( $filename, $fsize, $fsize, $this, PT_WRITE );
return false === $abort_signal_received;
}
public function compress( $method, $level ) {
parent::compress( $method, $level );
if ( NONE == $method )
return $this->getFileName();
global $COMPRESSION_NAMES;
$abort_signal_received = false;
$ext = '.' . $COMPRESSION_NAMES[$method];
$filter = '';
list( $filter, $mode ) = $this->_getFilterMode( $method, $level, false );
if ( in_array( $method, array( GZ, BZ2 ) ) && ! _function_exists( $filter . 'open' ) )
throw new MyException( 
sprintf( 
_esc( 
'%s support is not enabled. Check your PHP configuration (php.ini) or contact your hosting provider.' ), 
strtoupper( $filter ) ) );
$output_file = $this->getFileName() . $ext;
_file_exists( $output_file ) && @unlink( $output_file ); 
if ( ! _file_exists( $this->getFileName() ) )
throw new MyException( 
sprintf( _esc( "Cannot compress the file %s due to it does not exist" ), $this->getFileName() ) );
if ( '' != $filter ) {
$fsize = filesize( $this->getFileName() );
$fw = _call_user_func( $filter . 'open', $output_file, $mode );
$fr = fopen( $this->getFileName(), 'rb' );
if ( false !== $fr ) {
$bw = 0;
while ( ! feof( $fr ) && false === $abort_signal_received ) {
if ( _is_callable( $this->onAbortCallback ) && ( $abort_signal_received = $abort_signal_received ||
false !== _call_user_func( $this->onAbortCallback) ) )
break;
$bw += _call_user_func( $filter . 'write', $fw, fread( $fr, MB ) );
$this->onProgress( $output_file, $bw, $fsize, $this, PT_COMPRESS );
}
fclose( $fr );
}
_call_user_func( $filter . 'close', $fw );
$this->onProgress( $output_file, $fsize, $fsize, $this, PT_COMPRESS );
}
return false === $abort_signal_received ? $this->getFileName() . $ext : false;
}
public function decompress( $method = null, $uncompressed_size = 0, $new_name = null ) {
parent::decompress( $method, $uncompressed_size );
global $COMPRESSION_NAMES;
$get_gz_uncompressed = function ( $filename ) {
$result = 0;
$fr = fopen( $filename, 'rb' );
if ( false !== $fr ) {
fseek( $fr, - 4, SEEK_END );
$buff = fread( $fr, 4 );
$array = unpack( 'V', $buff );
$result = end( $array );
fclose( $fr );
}
return $result;
};
$result = false;
$abort_signal_received = false;
$ext = '';
if ( null === $method ) {
if ( preg_match( '/\.((' . implode( '|', $COMPRESSION_NAMES ) . ')$)/i', $this->getFileName(), $matches ) ) {
( $filter = $matches[2] ) && $method = array_search( $filter, $COMPRESSION_NAMES );
$mode = 'r';
$ext = strtolower( $filter );
}
}
list( $filter, $mode ) = $this->_getFilterMode( $method );
if ( ( empty( $filter ) && 'tar' != $ext ) ||
( in_array( $method, array( GZ, BZ2 ) ) && ! _function_exists( $filter . 'open' ) ) )
throw new MyException( 
sprintf( 
_esc( 
'%s support is not enabled. Check your PHP configuration (php.ini) or contact your hosting provider.' ), 
strtoupper( $filter ) ) );
if ( ! empty( $filter ) || 'tar' == $ext ) {
if ( ! $this->fixArchiveCRLF( $this->getFileName(), $method ) ) 
throw new MyException( 
sprintf( 
_esc( 'Archive %s has a bad signature. Unsupported format.' ), 
shorten_path( $this->getFileName() ) ) );
if ( ( 0 == $uncompressed_size ) && ( GZ == $method ) )
$uncompressed_size = $get_gz_uncompressed( $this->getFileName() );
if ( ! empty( $new_name ) )
$result = $new_name;
else
$result = preg_replace( 
'/(\.(' . implode( '|', array_diff( $COMPRESSION_NAMES, array( NONE => 'tar' ) ) ) . '))$/i', 
'', 
$this->getFileName() );
if ( empty( $filter ) || 'tar' == $ext )
return $result;
$fr = _call_user_func( $filter . 'open', $this->getFileName(), $mode );
$eof_func = ( GZ == $method ? 'gz' : 'f' ) . 'eof';
$fw = fopen( $result, 'wb' );
$bw = 0;
0 < $uncompressed_size || $uncompressed_size = filesize( $this->getFileName() );
$error = false;
if ( false !== $fr ) {
while ( ! _call_user_func( $eof_func, $fr ) && false === $abort_signal_received ) {
if ( _is_callable( $this->onAbortCallback ) && ( $abort_signal_received = $abort_signal_received ||
false !== _call_user_func( $this->onAbortCallback) ) )
break;
$str = _call_user_func( $filter . 'read', $fr, TAR_BUFFER_LENGTH );
$bw += strlen( $str );
$uncompressed_size < 0 && $bw > $uncompressed_size && $bw = $uncompressed_size; 
if ( false === $str )
$error = sprintf( _esc( '%s problem' ), $filter );
elseif ( BZ2 == $method && BZ_OK !== bzerrno( $fr ) )
$error = bzerrstr( $fr );
if ( false !== $error )
break;
fwrite( $fw, $str );
$this->onProgress( $result, $bw, $uncompressed_size, $this, PT_WRITE );
}
$this->onProgress( $result, $uncompressed_size, $uncompressed_size, $this, PT_WRITE );
}
fclose( $fw );
_call_user_func( $filter . 'close', $fr );
if ( $error )
throw new MyException( $error );
$bw < $uncompressed_size && $this->_stdOutput( 
sprintf( _esc( '[!] Expected %d bytes to decompress but I got only %d' ), $uncompressed_size, $bw ) );
}
return $result;
}
public function getArchiveFiles( $filename = null ) {
$filename = empty( $filename ) ? $this->getFileName() : $filename;
$max_offset = filesize( $filename );
if ( ! ( $result = _file_exists( $filename ) && $this->isValidTar( $filename ) ) )
return false;
$result = array();
if ( ! ( $fr = fopen( $filename, 'rb' ) ) )
return false;
$offset = 0;
while ( $offset < $max_offset && - 1 != fseek( $fr, $offset, SEEK_SET ) ) {
$buffer = fread( $fr, TAR_BUFFER_LENGTH );
if ( false === $buffer )
continue;
if ( false === ( $array = $this->_extractHeader( $buffer ) ) ) {
$found = false;
$this->_stdOutput( 
sprintf( 
_esc( '[!] Invalid TAR header at offset %d %sSkipping to the next header...' ), 
$offset, 
count( $result ) > 0 ? sprintf( 
_esc( 'Probably the file %s has a truncated content.' ), 
$result[count( $result ) - 1]['name'] ) . PHP_EOL : '.' ) );
$old_offset = $offset;
if ( false !== ( $p = strpos( $buffer, TAR_MAGIC . TAR_VERSION ) ) ) {
$offset += $p - TAR_MAGIC_OFFSET;
$found = true;
} else
while ( $offset < $max_offset && ! feof( $fr ) ) {
if ( false !== ( $buffer = fread( $fr, TAR_BUFFER_LENGTH ) ) &&
false !== ( $p = strpos( $buffer, TAR_MAGIC . TAR_VERSION ) ) ) {
$offset = ftell( $fr ) - strlen( $buffer );
$found = true;
break; 
}
}
$this->_stdOutput( 
$found ? sprintf( 
_esc( 'Found the next header at offset %d (%d bytes away)' ), 
$offset, 
$offset - $old_offset ) : _esc( 'No other header found. I give up....' ) );
if ( $found )
continue;
else
break;
}
$fsize = ceil( $array['size'] / TAR_EXTHEADER_LEN ) * TAR_EXTHEADER_LEN;
$pos = ( count( $array ) > 9 ? 3 : 1 ) * TAR_EXTHEADER_LEN;
$array['offset'] = $offset + $pos;
$result[] = $array;
$offset += $pos + $fsize; 
}
fclose( $fr );
return $result;
}
public function extract( $filename = null, $dst_path = null, $force_extrct = true ) {
$filename = empty( $filename ) ? $this->getFileName() : $filename;
if ( $result = false !== ( $tar_files = $this->getArchiveFiles( $filename ) ) )
! ( empty( $dst_path ) || _file_exists( $dst_path ) ) && $result = mkdir( $dst_path, 0770, true );
if ( ! $result )
return false;
if ( ! ( $fr = fopen( $filename, 'rb' ) ) )
return false;
$result = array();
$abort_signal_received = false;
$max = count( $tar_files );
$i = 1;
$is_win = preg_match( '/^win/i', PHP_OS );
foreach ( $tar_files as $file_header ) {
if ( _is_callable( $this->onAbortCallback ) && ( $abort_signal_received = $abort_signal_received ||
false !== _call_user_func( $this->onAbortCallback) ) )
break;
if ( ! empty( $dst_path ) || ! $is_win )
$output_file = $this->_addTrailingSlash( $dst_path );
$orig_filename = $file_header['name'];
! empty( $dst_path ) && $is_win && $orig_filename = preg_replace( '@\w*:[\\\/]@', '', $orig_filename ); 
$output_file .= str_replace( array( '\\', '/' ), DIRECTORY_SEPARATOR, $orig_filename );
if ( ( $file_header['mode'] & 16384 ) && ! empty( $file_header['name'] ) ) {
$this->_mk_dir( $output_file );
$this->onProgress( $filename, $i++, $max, $this, PT_EXTRACTFILE );
continue;
} else {
$this->_mk_dir( dirname( $output_file ) );
}
$error = false;
$fw = fopen( $output_file, 'wb' );
if ( false !== $fw ) {
if ( $error = ( $file_header['size'] !=
( $bw = $this->_pipeStreams( $fr, $fw, $file_header['size'], $file_header['offset'] ) ) ) ) {
$this->_stdOutput( 
sprintf( 
_esc( '[!] Wrote only %d of %d bytes to %s' ), 
$bw, 
$file_header['size'], 
$output_file ) );
$fsize = filesize( $filename ) / ( 1000 * 100 ); 
$fsize > 0 && $fsize < 10 && $this->_stdOutput( 
sprintf( 
_esc( 
'The TAR archive has odd filesize of %d*100k. Was it initially compressed with PBzip2 with -%d flag?\nIf that is the case then the %s is probably truncated thus unreliable.' ), 
$fsize, 
$fsize, 
basename( $filename ) ) );
}
fclose( $fw );
}
$this->onProgress( $filename, $i++, $max, $this, PT_EXTRACTFILE );
if ( $error && $force_extrct )
$this->_stdOutput( 
sprintf( 
_esc( '[!] Extracting the file %s forcebly (its content may be truncated)' ), 
$output_file ) );
( ! $error || $force_extrct ) && $result[$file_header['name']] = $output_file; 
}
fclose( $fr );
return $result;
}
public function isValidTar( $filename ) {
if ( ! _file_exists( $filename ) )
return false;
$result = false;
$fr = fopen( $filename, 'rb' );
if ( false !== $fr ) {
$header = fread( $fr, 3 * TAR_EXTHEADER_LEN );
$result = $this->_isValidHeader( $header );
fclose( $fr );
}
return $result;
}
public function isValidArchive( $filename, $method = null ) {
if ( NONE == $method )
return $this->isValidTar( $filename );
else
return parent::isValidArchive( $filename, $method );
}
}
?>