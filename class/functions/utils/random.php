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
 * @file    : random.php $
 * 
 * @id      : random.php | Tue Feb 16 21:44:02 2016 UTC | Eugen Mihailescu eugenmihailescux@gmail.com $
*/

namespace MyBackup;

function isRandomWordsFile() {
return defined( __NAMESPACE__.'\\BENCHMARK_RANDWORDS_FILE' ) && file_exists( BENCHMARK_RANDWORDS_FILE ) &&
filesize( BENCHMARK_RANDWORDS_FILE );
}
function createRandomFile( $path, $size, $cbk_progress = null, $cbk_abort = null ) {
if ( $size <= 0 )
return false;
$bs = $size > MB ? MB : ( $size > 8192 ? 8192 : 1 );
if ( null == $path )
$path = defined( __NAMESPACE__.'\\LOG_DIR' ) ? LOG_DIR : sys_get_temp_dir();
if ( ! file_exists( $path ) || ( file_exists( $path ) && ! is_dir( $path ) ) )
mkdir( $path, 0770, true );
$tmp_file = tempnam( addTrailingSlash( $path ), 'tmp_' );
if ( isRandomWordsFile() )
$f = createRandomWordsFile( $tmp_file, $size, $bs, $cbk_progress, $cbk_abort );
elseif ( isWin() )
$f = createRandomFileWin( $tmp_file, $size, $bs, $cbk_progress, $cbk_abort );
else
$f = createRandomFileUnix( $tmp_file, $size, $bs, $cbk_progress );
return file_exists( $f ) ? $f : false;
}
function createRandomWordsFile( $tmp_file = null, $size = 0, $bs = 0, $cbk_progress = null, $cbk_abort = null ) {
$rw = file( BENCHMARK_RANDWORDS_FILE );
$wc = count( $rw );
$fs = 0;
$fh = fopen( $tmp_file, 'wb' );
$abort_signal_received = false;
if ( $fh ) {
while ( $fs < $size ) {
if ( is_array( $cbk_abort ) && _is_callable( $cbk_abort[0] ) &&
( $abort_signal_received = $abort_signal_received ||
false !== _call_user_func( $cbk_abort[0], $cbk_abort[1], $cbk_abort[2] ) ) )
break;
$bfs = 0;
$bw = '';
while ( $bfs < $bs ) {
$w = $rw[rand( 0, $wc - 1 )];
$bw .= "$w ";
$bfs += strlen( $w ) + 1;
}
fwrite( $fh, $bw ) && $fs += $bfs;
_is_callable( $cbk_progress ) && _call_user_func( $cbk_progress, TMPFILE_SOURCE, $tmp_file, $fs, $size, 4 );
}
fclose( $fh );
}
return $tmp_file;
}
function createRandomFileUnix( $tmp_file = null, $size = 0, $bs = 0 ) {
$cmd = 'dd if=/dev/urandom of=' . $tmp_file . ' bs=' . $bs . ' count=' . ceil( $size / $bs );
( $io = popen( $cmd, 'r' ) ) && fgets( $io, 4096 ) && pclose( $io );
return $tmp_file;
}
function createRandomFileWin( $tmp_file = null, $size = 0, $bs = 0, $cbk_progress = null, $cbk_abort = null ) {
$buffer = '';
$abort_signal_received = false;
for ( $i = 0; $i < $bs; $i++ )
$buffer .= chr( rand( 1, 255 ) );
$remaining = $size;
$handle = fopen( $tmp_file, 'wb' );
while ( $remaining > 0 ) {
if ( is_array( $cbk_abort ) && _is_callable( $cbk_abort[0] ) &&
( $abort_signal_received = $abort_signal_received ||
false !== _call_user_func( $cbk_abort[0], $cbk_abort[1], $cbk_abort[2] ) ) )
break;
$remaining -= fwrite( $handle, str_shuffle( $buffer ) );
_is_callable( $cbk_progress ) &&
_call_user_func( $cbk_progress, TMPFILE_SOURCE, $tmp_file, $size - $remaining, $size, 4 );
}
$handle && fclose( $handle );
return $tmp_file;
}
function create_alphabet( $alphabet_start = 1, $alphabet_end = 255 ) {
$alphabet = '';
for ( $i = $alphabet_start; $i <= $alphabet_end; $i++ )
$alphabet .= chr( $i );
return $alphabet;
}
function random_pseudo_bytes( $key_len ) {
$alphabet = str_shuffle( create_alphabet() );
$alphabet_len = strlen( $alphabet );
$key = '';
for ( $i = 0; $i < $key_len; $i++ )
$key .= $alphabet[rand( 0, $alphabet_len - 1 )];
return str_shuffle( $key );
}
function password_strength( $password, $alphabet_len = 255 ) {
$l = strlen( $password );
$n = 1 + $alphabet_len;
$total = pow( $n, $l );
$h = $l * log10( $n ) / log( 2 );
return $h;
}
function password_strength_str( $password, $alphabet_len = 255 ) {
$strength = password_strength( $password, $alphabet_len );
if ( $strength < 25 )
return 'very weak';
elseif ( $strength < 35 )
return 'weak';
elseif ( $strength < 55 )
return 'fair';
elseif ( $strength < 127 )
return 'strong';
return 'very strong';
}
?>