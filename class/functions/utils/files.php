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
 * @date    : Tue Feb 16 21:44:02 2016 UTC $
 * @file    : files.php $
 * 
 * @id      : files.php | Tue Feb 16 21:44:02 2016 UTC | Eugen Mihailescu eugenmihailescux@gmail.com $
*/

namespace MyBackup;

define( __NAMESPACE__.'\\DIR_SIZE_CACHE_FILE', addTrailingSlash( LOG_DIR ) . "disk.cache" );
function getFileListByExt( $dir, $include_ext ) {
$dir = addTrailingSlash( $dir );
$dh = opendir( $dir );
$files = array();
while ( false !== ( $filename = readdir( $dh ) ) ) {
$fullPath = $dir . $filename;
if ( $filename != "." && $filename != ".." )
if ( ! is_dir( $fullPath ) ) {
$p = strrpos( $filename, "." );
if ( false !== $p && substr( $filename, $p + 1 ) === $include_ext )
$files[] = $fullPath;
}
}
closedir( $dh );
return $files;
}
function getFileListByPattern( 
$dir, 
$pattern, 
$recursively = false, 
$add_empty_dir = true, 
$tree = true, 
$output_style = false, 
$skip_files = null, 
$skip_links = true ) {
$dir .= DIRECTORY_SEPARATOR != substr( $dir, - 1 ) ? DIRECTORY_SEPARATOR : '';
if ( ! file_exists( $dir ) )
throw new \Exception( sprintf( _esc( "File or directory %s does not exists" ), $dir ) );
$dh = @opendir( $dir );
( $tree && $dirname = basename( $dir ) ) || $dirname = $dir;
( $tree && $files = array( $dirname => array() ) ) || ( $files = 2 == $output_style ? array() : array( $dirname ) );
if ( false !== $dh ) {
while ( false !== ( $filename = @readdir( $dh ) ) ) {
if ( $filename == "." || $filename == ".." )
continue;
$fullPath = $dir . $filename;
if ( ( is_array( $skip_files ) && in_array( $fullPath, $skip_files ) ) ||
( $skip_links && is_link( $fullPath ) ) )
continue;
if ( ! is_dir( $fullPath ) ) {
if ( 1 != $output_style && ( null == $pattern || preg_match( $pattern, $fullPath ) ) )
( $tree && $files[$dirname][] = $filename ) || $files[] = $dirname . $filename;
} elseif ( $recursively ) {
$children = getFileListByPattern( 
$fullPath, 
$pattern, 
$recursively, 
$add_empty_dir, 
$tree, 
$output_style, 
$skip_files, 
$skip_links );
if ( $add_empty_dir || 1 == $output_style || ! empty( $children ) )
( $tree && $files[$dirname] = array_merge( $children, $files[$dirname] ) ) ||
$files = array_merge( $children, $files );
}
}
closedir( $dh );
}
return $add_empty_dir || ! empty( $files ) ? $files : '';
}
function getFilesTime( $files, $pattern ) {
$dates = array();
if ( null != $files )
foreach ( $files as $filename ) {
$subject = formatRegEx( 
array( chr( 92 ), ".", "^", "$", "+", "-", "(", ")", "[", "]", "{", "}" ), 
"@", 
$pattern ) . "([0-9]{8,}\-[0-9]{6,}).*";
if ( preg_match( "@" . $subject . "@", $filename, $matches ) ) {
$date = \DateTime::createFromFormat( 'Ymd-His', $matches[1] )->getTimestamp();
if ( ! in_array( $date, $dates ) )
$dates[] = $date;
}
}
return $dates;
}
function getFilesSize( $files ) {
$result = 0;
foreach ( $files as $filename )
is_file( $filename ) && $result += @filesize( $filename );
return $result;
}
function createFileList( 
$temp_file, 
$dir, 
$exclude_dirs = null, 
$exclude_ext = null, 
$exclude_files = null, 
$exclude_links = true, 
$callback = null ) {
$fcount = 0;
if ( is_array( $dir ) ) {
$preg_separator = '/';
$excluded_prefixes = array();
$pattern = '';
$preg_esc = function ( &$array, $is_dir = false, $escape = false ) use(&$preg_separator ) {
$array = array_unique( $array );
array_walk( 
$array, 
function ( &$item ) use(&$preg_separator, &$is_dir, &$escape ) {
$is_dir && $item = addTrailingSlash( $item );
$escape && $item = preg_quote( $item, $preg_separator );
} );
return true;
};
$put_temp_file = function ( $path, $array ) use(&$temp_file, &$callback ) {
$count = count( $array );
if ( $count && file_put_contents( $temp_file, implode( PHP_EOL, $array ) . PHP_EOL, FILE_APPEND ) ) {
if ( is_array( $callback ) && _is_callable( $callback[0] ) )
_call_user_func( 
$callback[0], 
sprintf( _esc( "Added %s directory (%s files)" ), $path, $count ), 
BULLET, 
1 );
return $count;
}
return false;
};
$get_files_by_pattern = function ( $fullPath, $recursive = true ) use(
&$pattern, 
&$exclude_files, 
&$exclude_links, 
&$preg_separator, 
&$callback, 
&$put_temp_file ) {
$is_link = is_link( $fullPath );
$fullPath = realpath( $fullPath );
if ( ! $is_link || ! $exclude_links ) {
$file_list = getFileListByPattern( 
$fullPath, 
'.*' == $pattern ? null : ( $preg_separator . $pattern . $preg_separator ), 
$recursive, 
false, 
false, 
2, 
$exclude_files, 
$exclude_links );
$count = count( $file_list );
} elseif ( is_array( $callback ) && _is_callable( $callback[0] ) ) {
_call_user_func( 
$callback[0], 
'<yellow>' . sprintf( _esc( 'Skipping the file link %s' ), $fullPath ) . '</yellow>' );
return 0;
}
return ! empty( $file_list ) ? $put_temp_file( $fullPath, $file_list ) : 0;
};
$array_to_pattern = function ( $array ) use(&$preg_esc, &$preg_separator ) {
asort( $array );
$pattern = '';
$fitem = array_pop( $array );
$root = '';
foreach ( str_split( $fitem ) as $i => $c ) {
$changed = false;
foreach ( $array as $item ) {
if ( $changed = $c != $item[$i] )
break;
}
if ( $changed ) {
$root = substr( $fitem, 0, $i );
break;
}
}
! $root && $root = $fitem;
if ( $root ) {
array_walk( 
$array, 
function ( &$item ) use(&$root, &$preg_separator ) {
$item = preg_replace( 
$preg_separator . '^' . preg_quote( $root, $preg_separator ) . $preg_separator, 
'', 
$item );
} );
}
$preg_esc( $array, false, true );
return sprintf( 
'%s%s', 
preg_quote( $root, $preg_separator ), 
count( $array ) ? '(' . implode( '|', $array ) . ')' : '' );
};
if ( is_array( $exclude_dirs ) ) {
$filter_subfiles = function ( $array ) use(&$exclude_dirs ) {
return array_filter( 
$array, 
function ( $item ) use(&$exclude_dirs ) {
$keep = ! empty( $item ) && is_dir( $item );
if ( $keep )
foreach ( $exclude_dirs as $d )
if ( $item != $d && ! ( $keep = ( $p = strpos( $item, $d ) ) || false === $p ) )
break;
return $keep;
} );
};
$exclude_dirs = $filter_subfiles( $exclude_dirs );
is_array( $exclude_files ) && $exclude_files = $filter_subfiles( $exclude_files );
$preg_esc( $exclude_dirs, true ) && $excluded_prefixes = array_merge( $excluded_prefixes, $exclude_dirs );
}
is_array( $exclude_files ) && $preg_esc( $exclude_files ) &&
$excluded_prefixes = array_merge( $excluded_prefixes, $exclude_files );
count( $excluded_prefixes ) && $pattern .= '^(?!(' . $array_to_pattern( $excluded_prefixes ) . '))';
$pattern .= '.*';
is_array( $exclude_ext ) && ! empty( $exclude_ext ) && $preg_esc( $exclude_ext ) &&
$pattern .= '\..+(?<!.' . implode( '|', $exclude_ext ) . ')$';
$scan_dirs = array();
foreach ( $dir as $key => $dirname ) {
$has_children = false;
foreach ( $dir as $k => $d ) {
if ( ( $key != $k ) && ( 0 === strpos( $d, $dirname ) ) ) {
$has_children = true;
break;
}
}
$scan_dirs[$dirname] = $has_children;
}
foreach ( $scan_dirs as $fullPath => $has_children ) {
if ( is_array( $callback ) && _is_callable( $callback[1] ) && _call_user_func( $callback[1] ) ) 
break;
$fcount += $get_files_by_pattern( $fullPath, ! $has_children );
}
} else
throw new MyException( _esc( 'createFileList : $dir is not array. This should never happen.' ) );
return $fcount;
}
function createFileListBySections( $temp_file, $wp_components ) {
if ( ! createFileListSections( $temp_file, $wp_components ) )
return false;
if ( ! ( $fr = fopen( $temp_file, 'r' ) ) )
return false;
$processed = array();
$result = array();
array_unshift( $wp_components, '' );
rsort( $wp_components );
foreach ( $wp_components as $section ) {
if ( - 1 == fseek( $fr, 0, SEEK_SET ) )
break;
$section_temp_file = $temp_file . '.' . ( ! empty( $section ) ? basename( $section ) : uniqid() );
if ( ! ( $fw = fopen( $section_temp_file, 'w' ) ) )
continue;
$current_section = '';
$lines = 0;
while ( false !== ( $buff = fgets( $fr ) ) ) {
$filename = str_replace( PHP_EOL, '', $buff );
if ( empty( $filename ) )
continue;
$skip = false;
if ( preg_match( '/\[([^\]\/]+)\]/', $filename, $matches ) ) {
$current_section = $matches[1]; 
$skip = true;
}
if ( preg_match( '/\[\/([^\]\/]+)\]/', $filename, $matches ) ) {
$current_section = ''; 
$skip = true;
}
if ( $skip )
continue;
$_filename = is_file( $filename ) ? dirname( $filename ) : $filename;
if ( ( '' == $current_section && $section != '' ) || ( $section == '' && $current_section != '' ) ||
( $section != '' && $current_section != '' && false === strpos( $_filename, $section ) ) )
continue; 
$crc32 = crc32( $filename );
if ( in_array( $crc32, $processed ) ) 
continue;
$lines += false !== fwrite( $fw, $buff );
$processed[] = $crc32;
}
fclose( $fw );
$lines && ( $result[$section_temp_file] = array( 'section' => $section, 'lines' => $lines ) ) ||
unlink( $section_temp_file );
}
fclose( $fr );
return $result;
}
function createFileListSections( $temp_file, $wp_components ) {
if ( empty( $wp_components ) ) {
return $temp_file;
}
rsort( $wp_components );
$newname = $temp_file . '.section';
if ( ! file_exists( $temp_file ) || false === ( $fr = fopen( $temp_file, 'r' ) ) ||
false === ( $fw = fopen( $newname, 'w' ) ) )
return false;
$section_name = function ( $filename ) use(&$wp_components ) {
is_file( $filename ) && $filename = dirname( $filename );
$min_len = isWin() ? 3 : strlen( DIRECTORY_SEPARATOR );
$found = false;
while ( ! ( $found = in_array( $filename, $wp_components ) ) && strlen( $filename ) > $min_len )
$filename = dirname( $filename );
return $found ? $filename : null;
};
$current_section = null;
while ( false !== ( $filename = fgets( $fr ) ) ) {
$filename = str_replace( PHP_EOL, '', $filename );
if ( empty( $filename ) || ! file_exists( $filename ) ) {
continue;
}
$section = $section_name( $filename );
$new_section = ! empty( $section ) && $section != $current_section && in_array( $section, $wp_components ); 
$end_section = ! empty( $current_section ) && ( $new_section || ! in_array( $section, $wp_components ) ); 
$end_section && fwrite( $fw, '[/' . basename( $current_section ) . ']' . PHP_EOL ); 
$new_section && fwrite( $fw, '[' . basename( $section ) . ']' . PHP_EOL ); 
$end_section && ! $new_section && $current_section = null;
$new_section && $current_section = $section; 
fwrite( $fw, $filename . PHP_EOL );
}
! empty( $current_section ) && fwrite( $fw, '[/' . basename( $current_section ) . ']' . PHP_EOL ); 
fclose( $fr );
fclose( $fw );
return move_file( $newname, $temp_file );
}
function createDirList( $dir, $level = null ) {
$dir = addTrailingSlash( $dir );
$dh = @opendir( $dir );
if ( null != $level && $level < 0 || '/dev/' == $dir ) 
return 0;
$result = array();
if ( is_dir( $dir ) && $dh ) {
while ( false !== ( $filename = readdir( $dh ) ) ) {
$fullPath = $dir . $filename;
if ( $filename != "." && $filename != ".." )
if ( is_dir( $fullPath ) )
$result = array_merge( $result, createDirList( $fullPath, null != $level ? $level - 1 : $level ) );
}
empty( $result ) && $result[] = $dir;
closedir( $dh );
}
return $result;
}
function getAbstractDirSize( $folder, $excl_dirs = null, $recursive = true ) {
if ( ! is_readable( $folder ) )
return false;
$files = @scandir( $folder );
if ( ! is_array( $files ) )
return false;
$dir = addTrailingSlash( $folder );
$dir_size = 0;
foreach ( $files as $f )
if ( '.' != substr( $f, 0, 1 ) ) {
try {
$currentFile = $dir . $f;
if ( is_dir( $currentFile ) ) {
$match = false;
if ( is_array( $excl_dirs ) )
foreach ( $excl_dirs as $excl_dir )
$match = $match || false !== strpos( $currentFile, $excl_dir );
! $match && $dir_size += $recursive ? getAbstractDirSize( $currentFile, $excl_dirs ) : 0;
} else
$dir_size += filesize( $currentFile );
} catch ( MyException $e ) {
}
}
return $dir_size;
}
function unixGetDirSize( $folder, $excl_dirs = null ) {
$excl_pattern = getTarExclusionPattern( $folder, null, $excl_dirs );
$io = @popen( sprintf( 'du -sB1KB %s %s 2>/dev/null', $folder, $excl_pattern ), 'r' );
if ( ! $io )
return getAbstractDirSize( $folder, $excl_dirs );
$KB = 1000; 
$size = $KB * fgets( $io, 4096 );
pclose( $io );
if ( preg_match( '/^\d*/', $size, $matches ) )
return $matches[0];
else
return false;
}
function winGetDirSize( $folder, $excl_dirs = null ) {
if ( class_exists( '\\COM' ) ) {
try {
$fs = new \COM( 'Scripting.FileSystemObject' );
if ( is_object( $fs ) ) {
if ( is_array( $excl_dirs ) && in_array( $folder, $excl_dirs ) )
return 0;
$specs = $fs->GetFolder( $folder );
$size = $specs->Size;
$fs = null;
return $size;
}
} catch ( MyException $e ) {
}
}
return false;
}
function getDirSize( $folder, $excl_dirs = null ) {
if ( isWin() ) {
return class_exists( '\\COM' ) ? winGetDirSize( $folder, $excl_dirs ) : getAbstractDirSize( 
$folder, 
$excl_dirs );
} else
return unixGetDirSize( $folder, $excl_dirs );
}
function getDirCacheSize() {
if ( file_exists( DIR_SIZE_CACHE_FILE ) )
return filesize( DIR_SIZE_CACHE_FILE );
else
return 0;
}
function clearDirSizeCache() {
if ( file_exists( DIR_SIZE_CACHE_FILE ) )
unlink( DIR_SIZE_CACHE_FILE );
}
function getDirSizeFromCache( $folder ) {
if ( file_exists( DIR_SIZE_CACHE_FILE ) && $files = json_decode( file_get_contents( DIR_SIZE_CACHE_FILE ), true ) )
if ( is_array( $files ) && isset( $files[$folder] ) && $size = $files[$folder] )
return $size;
$size = getDirSize( $folder );
$files[$folder] = $size;
file_put_contents( DIR_SIZE_CACHE_FILE, json_encode( $files, 64 ) ); 
return $size;
}
function getDirSizeByFileList( $temp_file ) {
if ( ! file_exists( $temp_file ) || false === ( $fr = fopen( $temp_file, 'r' ) ) )
return false;
$result = 0;
while ( false !== ( $filename = fgets( $fr ) ) ) {
$filename = str_replace( PHP_EOL, '', $filename );
file_exists( $filename ) && $result += filesize( $filename );
}
fclose( $fr );
return $result;
}
function addTrailingSlash( $path, $separator = DIRECTORY_SEPARATOR, $escape_separator = false ) {
return ( empty( $path ) || substr( $path, - 1 ) != $separator ) ? $path .
( $escape_separator ? addslashes( $separator ) : $separator ) : $path;
}
function delTrailingSlash( $path, $separator = DIRECTORY_SEPARATOR ) {
if ( substr( $path, - 1 ) == $separator )
return substr( $path, 0, - 1 );
else
return $path;
}
function getFileRelativePath( $filename ) {
$relpath = str_replace( realpath( $_SERVER['DOCUMENT_ROOT'] ), '', $filename );
DIRECTORY_SEPARATOR != substr( $relpath, 0, 1 ) && $relpath = DIRECTORY_SEPARATOR . $relpath; 
return dirname( $relpath );
}
function tailFile( $filepath, $lines = 1 ) {
$f = @fopen( $filepath, "rb" );
if ( $f === false )
return false;
fseek( $f, - 1, SEEK_END );
if ( fread( $f, 1 ) != "\n" )
$lines -= 1;
$output = '';
$chunk = '';
while ( ftell( $f ) > 0 && $lines >= 0 ) {
$seek = min( ftell( $f ), 4096 );
fseek( $f, - $seek, SEEK_CUR );
$output = ( $chunk = fread( $f, $seek ) ) . $output;
fseek( $f, - strlen( $chunk ), SEEK_CUR );
$lines -= substr_count( $chunk, "\n" );
}
while ( $lines++ < 0 ) {
$output = substr( $output, strpos( $output, "\n" ) + 1 );
}
fclose( $f );
return trim( $output );
}
function monitorFile( $filepath, $interval = 1, $read_callback, $abort_callback = null ) {
_is_callable( $read_callback ) || die( _esc( 'Function monitorFile says: $callback is not a callable function' ) );
$size = 0;
while ( true ) {
if ( _is_callable( $abort_callback ) && _call_user_func( $abort_callback ) ) {
_pesc( 'Abort signal received. File monitoring aborted.' );
break;
}
clearstatcache();
file_exists( $filepath ) && $newSize = filesize( $filepath );
if ( $size == $newSize ) {
sleep( $interval );
continue;
}
$fh = fopen( $filepath, "rb" );
! $fh && ( $err = error_get_last() ) && die( $err['message'] );
fseek( $fh, $size );
while ( ! feof( $fh ) )
_call_user_func( $read_callback, fread( $fh, 8192 ) );
fclose( $fh );
$size = $newSize;
}
}
function _dirname( $dir, $sep = '/' ) {
$result = explode( $sep, $dir );
$result = array_filter( $result, function ( $e ) {
return ! empty( $e );
} );
count( $result ) > 0 && $result = array_slice( $result, 0, count( $result ) - 1 );
return ( '/' == $sep ? $sep : '' ) . implode( $sep, $result );
}
function _basename( $file, $sep = '/' ) {
$result = explode( $sep, $file );
$result = array_filter( $result, function ( $e ) {
return ! empty( $e );
} );
return end( $result );
}
function copy_folder( $src, $dst ) {
if ( ! file_exists( $src ) )
return false;
$src = addTrailingSlash( $src );
$dst = addTrailingSlash( $dst );
if ( $src == $dst )
return true;
$result = true;
! file_exists( $dst ) && @mkdir( $dst, 0770, true );
$dir = opendir( $src );
while ( false !== ( $file = readdir( $dir ) ) )
if ( ( $file != '.' ) && ( $file != '..' ) ) {
if ( is_dir( $src . $file ) ) {
if ( addTrailingSlash( $src . $file ) == $dst )
continue; 
$result = $result && copy_folder( $src . $file, $dst . $file );
} else {
$result = $result && copy( $src . $file, $dst . $file );
}
}
closedir( $dir );
return $result;
}
function normalize_path( $path, $reverse = false, $count = 1 ) {
$from = '\\';
$to = str_repeat( $from, ( $count + 1 ) );
$reverse && swap_items( $from, $to ) || $path = normalize_path( $path, true );
return str_replace( $from, $to, $path );
}
function getUserHomeDir() {
if ( function_exists( '\\posix_getpwuid' ) ) {
$uid = posix_getpwuid( posix_getuid() );
$home = $uid['dir'];
} else
$home = getenv( 'HOME' );
if ( empty( $home ) )
if ( ! empty( $_SERVER['HOMEDRIVE'] ) && ! empty( $_SERVER['HOMEPATH'] ) )
$home = $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'];
if ( empty( $home ) && ! empty( $_SERVER['USERPROFILE'] ) )
$home = $_SERVER['USERPROFILE'];
return $home;
}
function splitFile( $filename, $vol_size ) {
$volumes = array();
$buff_size = 8192;
if ( file_exists( $filename ) ) {
$remaining = filesize( $filename );
$fr = fopen( $filename, 'rb' );
if ( false !== $fr )
while ( $remaining > 0 ) {
$volumes[] = sprintf( '%s.split-%d', $filename, count( $volumes ) );
$fw = fopen( end( $volumes ), 'wb' );
$actual = 0;
while ( ! feof( $fr ) && $remaining > 0 && $actual + $buff_size < $vol_size ) {
$buff = fread( $fr, $buff_size );
$byte_read = strlen( $buff );
$actual += $byte_read;
$remaining -= $byte_read;
fwrite( $fw, $buff );
}
fclose( $fw );
}
fclose( $fr );
}
return $volumes;
}
function getFileLinesCount( $filename, $line_sep = PHP_EOL ) {
$linecount = 0;
if ( ! file_exists( $filename ) )
return $linecount;
$handle = fopen( $filename, "r" );
if ( false !== $handle )
while ( ! feof( $handle ) ) {
$buff = fread( $handle, 4096 );
false !== $buff && $linecount += substr_count( $buff, $line_sep );
}
fclose( $handle );
return $linecount + 1;
}
function file_checksum( $filename, $attempt_os_first = false ) {
$result = false;
$file_exist = is_file( $filename );
if ( $file_exist && $attempt_os_first && ! preg_match( '/^win/i', PHP_OS ) ) {
exec( "md5sum $filename", $output, $result );
if ( 0 == $result && count( $output ) > 0 && preg_match( '/([\d\w]+)?/', $output[0], $matches ) )
return $matches[1];
else {
exec( "md5 -q $filename", $output, $result );
if ( 0 == $result && count( $output ) > 0 && preg_match( '/([\d\w]+)?/', $output[0], $matches ) )
return $matches[1];
}
}
return $file_exist ? md5_file( $filename ) : false;
}
function move_file( $source, $dest ) {
$is_win = isWin();
$rename = ! $is_win || strtolower( substr( $source, 0, 1 ) ) == strtolower( substr( $dest, 0, 1 ) );
return $rename ? rename( $source, $dest ) : ( copy( $source, $dest ) && unlink( $source ) );
}
function _rmdir( $dir, $recursive = true ) {
if ( ! file_exists( $dir ) )
return;
$files = getFileListByPattern( $dir, '/.+/', $recursive, true, false );
rsort( $files );
array_walk( $files, function ( $item, $key ) {
is_dir( $item ) && rmdir( $item ) || unlink( $item );
} );
}
if ( ! function_exists( '\\sanitize_file_name' ) ) {
function sanitize_file_name( $filename ) {
$special_chars = array( 
"?", 
"[", 
"]", 
"/", 
"\\", 
"=", 
"<", 
">", 
":", 
";", 
",", 
"'", 
"\"", 
"&", 
"$", 
"#", 
"*", 
"(", 
")", 
"|", 
"~", 
"`", 
"!", 
"{", 
"}", 
"%", 
"+", 
chr( 0 ) );
$filename = preg_replace( "#\x{00a0}#siu", ' ', $filename );
$filename = str_replace( $special_chars, '', $filename );
$filename = str_replace( array( '%20', '+' ), '-', $filename );
$filename = preg_replace( '/[\r\n\t -]+/', '-', $filename );
$filename = trim( $filename, '.-_' );
$parts = explode( '.', $filename );
if ( count( $parts ) <= 2 ) {
return $filename;
}
$filename = array_shift( $parts );
$extension = array_pop( $parts );
foreach ( (array) $parts as $part ) {
$filename .= '.' . $part;
}
$filename .= '.' . $extension;
return $filename;
}
}
?>