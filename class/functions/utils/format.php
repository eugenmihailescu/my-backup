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
 * @file    : format.php $
 * 
 * @id      : format.php | Tue Feb 16 21:41:51 2016 UTC | Eugen Mihailescu eugenmihailescux@gmail.com $
*/

namespace MyBackup;

function timeFormat( $timestamp ) {
$h = floor($timestamp / 3600);
$m = floor(( $timestamp - 3600 * $h ) / 60);
$s = $timestamp - 3600 * $h - 60 * $m;
return sprintf( '%02d:%02d:%02d', $h, $m, $s );
}
function isNull( $array, $key, $default = null ) {
if ( is_array( $array ) )
return isset( $array[$key] ) ? $array[$key] : $default;
else
return empty( $array ) ? $default : $array;
}
function sqlFloor( $expr, $is_sqlite ) {
return $is_sqlite ? 'CAST(' . $expr . ' AS INTEGER)' : 'floor(' . $expr . ')';
}
function strToBool( $str ) {
return 1 === preg_match( '/(true|on|1|yes)/i', $str );
}
function boolToStr( $val ) {
return $val ? 'true' : 'false';
}
function formatRegEx( $pattern, $separator, $subject ) {
$subject = str_replace( $separator, chr( 92 ) . $separator, $subject );
foreach ( $pattern as $p )
$subject = str_replace( $p, chr( 92 ) . $p, $subject );
return $subject;
}
function sign( $number ) {
return $number > 0 ? 1 : ( $number < 0 ? - 1 : 0 );
}
function strtohex( $str ) {
$result = '';
for ( $i = 0; $i < strlen( $str ); $i++ )
$result .= sprintf( "%02X", ord( $str[$i] ) );
return $result;
}
function hextostr( $str ) {
return pack( "H*", $str );
}
if ( ! _function_exists( '_esc' ) ) {
function _esc( $string ) {
return _( $string );
}
function _pesc( $string ) {
echo _esc( $string );
}
}
?>