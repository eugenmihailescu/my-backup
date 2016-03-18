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
 * @version : 0.2.3-30 $
 * @commit  : 11b68819d76b3ad1fed1c955cefe675ac23d8def $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Fri Mar 18 17:18:30 2016 +0100 $
 * @file    : sys-tools.php $
 * 
 * @id      : sys-tools.php | Fri Mar 18 17:18:30 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

function getTarExclusionPattern( $root_dir, $exclude_files = null, $exclude_dirs = null, $exclude_ext = null ) {
$pattern = ' ';
$inerquot = "'";
if ( is_array( $exclude_files ) ) {
$items = array();
foreach ( $exclude_files as $f )
if ( null == $exclude_dirs ||
( is_array( $exclude_dirs ) && ! empty( $f ) && ! array_search( dirname( $f ), $exclude_dirs ) ) ) {
$items[] = $f;
}
empty( $items ) || $pattern .= sprintf( "--exclude=%s%s%s ", $inerquot, gcdArrayGlob( $items ), $inerquot );
}
if ( is_array( $exclude_ext ) ) {
$items = array();
foreach ( $exclude_ext as $e )
if ( ! empty( $e ) ) {
$items[] = $e;
}
empty( $items ) || $pattern .= sprintf( "--exclude=%s*.%s%s ", $inerquot, gcdArrayGlob( $items ), $inerquot );
}
if ( is_array( $exclude_dirs ) ) {
$items = array();
foreach ( $exclude_dirs as $d )
if ( ! empty( $d ) && false !== strpos( $d, $root_dir ) ) {
$items[] = delTrailingSlash( $d );
}
empty( $items ) || $pattern .= sprintf( "--exclude=%s%s%s ", $inerquot, gcdArrayGlob( $items ), $inerquot );
}
return $pattern;
}
function escapeCygwinPath( $str ) {
if ( preg_match_all( '/([a-z]:)([^\'"]+)/i', $str, $matches ) ) {
foreach ( $matches[1] as $match )
$str = str_replace( $match, '/cygdrive/' . strtolower( $match[0] ), $str );
foreach ( $matches[2] as $match )
$str = str_replace( 
$match, 
str_replace( array( DIRECTORY_SEPARATOR, ' ' ), array( '/', '\\ ' ), $match ), 
$str );
}
return $str;
}
function getTarNZipCmd( 
$src_filename, 
$arc_filename, 
$method = 2, 
$level = 9, 
$vol_size = 0, 
$exclude_files = null, 
$exclude_dirs = null, 
$exclude_ext = null, 
$bzip_version = null, 
$cygwin = null ) {
return getUnixCmd( 
$src_filename, 
$arc_filename, 
$method, 
$level, 
$vol_size, 
$exclude_files, 
$exclude_dirs, 
$exclude_ext, 
$bzip_version, 
$cygwin );
}
function getUnixCmd( 
$src_filename, 
$arc_filename, 
$method = 2, 
$level = 9, 
$vol_size = 0, 
$exclude_files = null, 
$exclude_dirs = null, 
$exclude_ext = null, 
$bzip_version = null, 
$cygwin = null, 
$html = false ) {
global $COMPRESSION_APPS, $COMPRESSION_NAMES;
if ( ! empty( $bzip_version ) && 'pbzip2' == $bzip_version && isWin() )
$bzip_version .= '.exe';
$arc = createTarNZipFilename( $arc_filename, $method );
$arc = isWin() ? escapeCygwinPath( $arc ) : $arc;
$arc_filename = isWin() ? escapeCygwinPath( $arc_filename ) : $arc_filename;
$vol_size /= 1024; 
$cmd = null;
if ( $method <= BZ2 ) {
$cmd = "tar";
if ( $vol_size >= 1 )
$cmd .= " -ML " . $vol_size;
$excl_pattern = getTarExclusionPattern( 
_is_dir( $src_filename ) ? $src_filename : dirname( $src_filename ), 
$exclude_files, 
$exclude_dirs, 
$exclude_ext );
$cmd .= isWin() ? escapeCygwinPath( $excl_pattern ) : $excl_pattern;
$cmd .= " -cf " . ( $vol_size >= 1 ? $arc_filename . ".tar" : "-" ) . " " .
( isWin() ? escapeCygwinPath( $src_filename ) : $src_filename ) . " 2>/dev/null " .
( $vol_size >= 1 ? "&& for f in `ls " . $arc_filename . "*.tar`;do cat \$f" : '' ) . '|';
if ( NONE != $method ) {
$cmd .= BZ2 == $method ? $bzip_version : $COMPRESSION_APPS[$method];
if ( $level > 0 )
$cmd .= " -" . $level;
$redirect = ">" . $arc_filename . ".tar." . $COMPRESSION_NAMES[$method];
$cmd .= " -fq" . ( BZ2 == $method ? 'k' : '' ) . " " .
( $vol_size >= 1 ? ">\$f." . $COMPRESSION_NAMES[$method] . ";done" : ( $redirect ) );
}
$cmd = str_replace( "%arcname%", NONE != $method ? '-' : $arc, $cmd );
if ( $vol_size >= 1 && ( _file_exists( $src_filename ) || _is_dir( $src_filename ) ) ) {
$fs = _is_file( $src_filename ) ? filesize( $src_filename ) : getDirSize( $src_filename );
$vol_count = ceil( $fs / ( $vol_size * 1024 ) );
if ( $vol_count > 1 )
$cmd = "printf 'n " . $arc_filename . "-%d.tar\\n' {1.." . ( $vol_count - 1 ) . "} | " . $cmd;
}
} else 
if ( BZ2 + 1 == $method ) { // ZIP
$cmd = $COMPRESSION_APPS[$method];
if ( $vol_size >= 1 )
$cmd .= " -s " . ( $vol_size ) . "k";
$cmd .= " -" . $level . " -q -r " . $arc . " " .
( isWin() ? escapeCygwinPath( $src_filename ) : $src_filename ) . " 2>/dev/null";
}
$cygwin_cmd = sprintf( '%s "%s"', ( ! empty( $cygwin ) ? $cygwin : CYGWIN_PATH ) . ' --login -c', $cmd );
return ! empty( $cmd ) ? ( isWin() ? $cygwin_cmd : $cmd ) : false;
}
function unixTarNZip( 
$src_filename, 
$dst_filename, 
$method = 2, 
$level = 9, 
$vol_size = 0, 
$remove_source = false, 
$exclude_files = null, 
$exclude_dirs = null, 
$exclude_ext = null, 
$bzip_version = null, 
$cygwin = null ) {
global $COMPRESSION_NAMES;
$result = false;
if ( defined( __NAMESPACE__.'\\OPER_COMPRESS_EXTERN' ) ) {
$arc = createTarNZipFilename( $dst_filename, $method );
if ( _file_exists( $arc ) )
unlink( $arc );
$cmd = getTarNZipCmd( 
$src_filename, 
$dst_filename, 
$method, 
$level, 
$vol_size, 
$exclude_files, 
$exclude_dirs, 
$exclude_ext, 
$bzip_version, 
$cygwin );
$io = @popen( $cmd, 'r' );
if ( $io ) {
while ( ( fgets( $io, 4096 ) ) !== false )
; 
$result = _file_exists( $arc );
pclose( $io );
}
}
if ( $remove_source && _file_exists( $src_filename ) && ! _is_dir( $src_filename ) )
unlink( $src_filename );
if ( $result )
if ( 0 < $vol_size ) {
$ext = $COMPRESSION_NAMES[$method];
$pattern = '/' . str_replace( 
'/', 
'\/', 
preg_replace( "/\\\\.tar\\\\.$ext$/", ".*\\.tar\\.$ext", preg_quote( $arc ) ) ) . '/';
$result = getFileListByPattern( dirname( $dst_filename ), $pattern, false, false, false, 2 ); 
return $result;
} else
return array( $arc );
else
return false;
}
function createTarNZipFilename( $file, $method = 2 ) {
global $COMPRESSION_NAMES;
$ext = $method != NONE ? '.' . $COMPRESSION_NAMES[$method] : '';
return sprintf( '%s.%s%s', delTrailingSlash( $file ), $method <= BZ2 ? 'tar' : '', $ext );
}
function testOSTools( 
$workdir = null, 
$method = 2, 
$level = 9, 
$vol_size = 0, 
$exclude_files = null, 
$exclude_dirs = null, 
$exclude_ext = null, 
$bzip_version = null, 
$cygwin = null ) {
$src_file = tempnam( 
addTrailingSlash( 
empty( $workdir ) || ! _file_exists( $workdir ) ? ( defined( __NAMESPACE__.'\\LOG_DIR' ) ? LOG_DIR : _sys_get_temp_dir() ) : $workdir ), 
'tmp_' );
$result = false;
if ( file_put_contents( $src_file, str_repeat( "0", 100 ) ) )
$result = unixTarNZip( 
$src_file, 
$src_file, 
$method, 
$level, 
$vol_size, 
true, 
$exclude_files, 
$exclude_dirs, 
$exclude_ext, 
$bzip_version, 
$cygwin );
if ( is_array( $result ) && count( $result ) > 0 ) {
$result = end( $result ); 
unlink( $result );
return $result;
} else
return false;
}
?>