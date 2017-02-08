<?php
/**
 * ################################################################################
 * MyBackup
 * 
 * Copyright 2017 Eugen Mihailescu <eugenmihailescux@gmail.com>
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
 * @version : 1.0-3 $
 * @commit  : 1b3291b4703ba7104acb73f0a2dc19e3a99f1ac1 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Tue Feb 7 08:55:11 2017 +0100 $
 * @file    : RegExBuilder.php $
 * 
 * @id      : RegExBuilder.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

class RegExBuilder {
private static function letters_to_regex_range( $str ) {
return $str;
}
private static function digits_to_regex_range( $str ) {
return $str;
}
private static function to_regex_range( $array ) {
asort( $array );
return self::digits_to_regex_range( self::letters_to_regex_range( implode( '', $array ) ) );
}
private static function get_common_char_at_pos( $array, $index ) {
$tuples = array();
$tcount = 0;
$keys = array();
foreach ( $array as $item ) {
if ( $index < strlen( $item ) ) {
$key = $item[$index];
$keys[] = $key;
if ( isset( $tuples[$key] ) )
$tuples[$key] ++;
else {
$tuples[$key] = 1;
$tcount++;
}
}
}
$q = count( $keys ) < count( $array ) ? '?' : '';
if ( 0 === $tcount )
return null; 
else 
if ( $tcount > 1 ) { // index in range or partially in range
$keys = array_unique( $keys );
return ( count( $keys ) > 1 ? '[' . self::to_regex_range( $keys ) . ']' : current( $keys ) ) . $q;
} else { // all elements have the same digit at index
return current( $keys ) . $q;
}
}
public static function create( $array ) {
if ( ! is_array( $array ) || empty( $array ) )
return '';
$i = 0; 
$p = 1; 
$prev_digit = false; 
$pattern = ''; 
while ( true ) {
$d = self::get_common_char_at_pos( $array, $i );
if ( null === $d )
break;
if ( $i ) {
if ( $prev_digit === $d ) {
$p++;
} else {
if ( false === $prev_digit ) {
$c = '.'; 
} else {
$c = $prev_digit;
}
$pattern .= $c;
if ( $p > 1 ) {
$q = ( '.' == $c ) ? '0,' : '';
$pattern .= '{' . $q . $p . '}';
}
$p = 0; 
}
}
$prev_digit = $d;
$i++;
}
$pattern .= $prev_digit . ( $p > 1 ? '{' . $p . '}' : '' );
return $pattern;
}
}
?>