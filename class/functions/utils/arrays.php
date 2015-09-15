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
 * @file    : arrays.php $
 * 
 * @id      : arrays.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;

function addArrays($array1, $array2) {
$result = array ();
foreach ( $array1 as $key => $value )
$result ["$key"] = $value;
foreach ( $array2 as $key => $value )
$result ["$key"] = $value;
return $result;
}
function objectToArray($d) {
if (is_object ( $d )) {
$d = get_object_vars ( $d );
}
if (is_array ( $d )) {
return array_map ( __FUNCTION__, $d );
} else {
return $d;
}
}
function insertArray(&$array, $position, $insert_value, $insert_key = null) {
$keep_key = ! empty ( $insert_key );
$prefix = array_slice ( $array, 0, $position, $keep_key );
$sufix = array_slice ( $array, $position, count ( $array ) - $position, $keep_key );
$new_item = array (
$keep_key ? $insert_key : $position => $insert_value 
);
$array = $keep_key ? $prefix + $new_item + $sufix : array_merge ( $prefix, $new_item, $sufix );
}
function insertArrayBefore(&$array, $before_value, $insert_value, $insert_key = null) {
insertArray ( $array, array_search ( $before_value, $array ), $insert_value, $insert_key );
}
function insertArrayBeforeK(&$array, $before_key, $insert_value, $insert_key = null) {
$position = array_search ( $before_key, array_keys ( $array ) );
insertArray ( $array, $position, $insert_value, $insert_key );
}
function arrayKeySort(&$array, $key_sort_order) {
$result = array ();
foreach ( $key_sort_order as $key )
$result [$key] = $array [$key];
$diff = array_diff_key ( $array, $result );
ksort ( $diff ); 
$array = $result + $diff;
}
function array_filter_recursive(&$array) {
foreach ( $array as $key => $item ) {
is_array ( $item ) && $array [$key] = filter_me ( $item );
if (empty ( $array [$key] ))
unset ( $array [$key] );
}
return $array;
}
?>