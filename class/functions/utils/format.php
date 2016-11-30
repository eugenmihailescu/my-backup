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
 * @version : 0.2.3-33 $
 * @commit  : 8322fc3e4ca12a069f0821feb9324ea7cfa728bd $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Tue Nov 29 16:33:58 2016 +0100 $
 * @file    : format.php $
 * 
 * @id      : format.php | Tue Nov 29 16:33:58 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
function timeFormat( $timestamp ) {
$h = floor( $timestamp / 3600 );
$m = floor( ( $timestamp - 3600 * $h ) / 60 );
$s = $timestamp - 3600 * $h - 60 * $m;
return sprintf( '%02d:%02d:%02d', $h, $m, $s );
}
function getHumanReadableTime( $timestamp ) {
$h = floor( $timestamp / 3600 );
$m = floor( ( $timestamp - 3600 * $h ) / 60 );
$s = $timestamp - 3600 * $h - 60 * $m;
$result = array();
$h >= 1 && $result[] = sprintf( '%d hour', $h );
$m >= 1 && $result[] = sprintf( '%d min', $m );
$s >= 1 && $result[] = sprintf( '%d sec', $s );
return implode( ', ', $result );
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
function boolToStr( $val, $yesno = false ) {
$yesno = 1 === intval( $yesno );
$options = array( false => array( 'false', _esc( 'no' ) ), true => array( 'true', _esc( 'yes' ) ) );
return $options[$val][$yesno];
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
function html2Text( $str ) {
$callback = function ( $match ) {
$convertor = new HtmlTableConverter();
$array = $convertor->htmlTable2Ascii( $match[0] );
return $array[0];
};
$plain_str = str_replace( 
array( '<br>', TAB, '<hr>', '&nbsp;', '&lt;', '&gt;', '&amp;', '&quot;' ), 
array( PHP_EOL, chr( 9 ), str_repeat( BULLET, SEPARATOR_LEN ), ' ', '<', '>', '&', '"' ), 
$str );
foreach ( array( 'li' => BULLET . "$2" . PHP_EOL, 'p' => "$2" . PHP_EOL ) as $tag => $replacement )
$plain_str = preg_replace( '/<\b(' . $tag . ')\b[^>]*>(.*?)<\/\1>/is', $replacement, $plain_str );
$plain_str = preg_replace_callback( '/<\b(table)\b[^>]*>(.*?)<\/\1>/is', $callback, $plain_str );
$plain_str = preg_replace( '/<a[\s\S]*?href\s*=\s*[\'"](.*?)[\'"][\s\S]*?>([\s\S]*?)<\/a>/', '$2 ($1)', $plain_str );
$plain_str = strip_tags( $plain_str );
return str_replace( PHP_EOL . PHP_EOL, PHP_EOL, $plain_str );
}
?>