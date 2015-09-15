<?php
/**
 * ################################################################################
 * MyBackup
 * 
 * Copyright 2015 Eugen Mihailescu <eugenmihailescux@gmail.com>
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
 * @version : 0.2.0-10 $
 * @commit  : bc79573e2975a220cb1cfbb08b16615f721a68c5 $
 * @author  : Eugen Mihailescu <eugenmihailescux@gmail.com> $
 * @date    : Mon Sep 14 21:14:57 2015 +0200 $
 * @file    : format.php $
 * 
 * @id      : format.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;

function isNull($array, $key, $default = null) {
if (is_array ( $array ))
return isset ( $array [$key] ) ? $array [$key] : $default;
else
return empty ( $array ) ? $default : $array;
}
function sqlFloor($expr, $is_sqlite) {
return $is_sqlite ? 'cast(' . $expr . ' AS INTEGER)' : 'floor(' . $expr . ')';
}
function strToBool($str) {
return 1 === preg_match ( '/(true|on|1|yes)/i', $str );
}
function boolToStr($val) {
return $val ? 'true' : 'false';
}
function formatRegEx($pattern, $separator, $subject) {
$subject = str_replace ( $separator, chr ( 92 ) . $separator, $subject );
foreach ( $pattern as $p )
$subject = str_replace ( $p, chr ( 92 ) . $p, $subject );
return $subject;
}
function sign($number) {
return $number > 0 ? 1 : ($number < 0 ? - 1 : 0);
}
function strtohex($str) {
$result = '';
for($i = 0; $i < strlen ( $str ); $i ++)
$result .= sprintf ( "%02X", ord ( $str [$i] ) );
return $result;
}
function hextostr($str) {
return pack ( "H*", $str );
}
if (! function_exists ( '_esc' )) {
function _esc($string) {
return _ ( $string );
}
function _pesc($string) {
echo _esc ( $string );
}
}
?>
