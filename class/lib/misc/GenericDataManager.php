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
 * @file    : GenericDataManager.php $
 * 
 * @id      : GenericDataManager.php | Fri Mar 18 17:18:30 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

abstract class GenericDataManager {
private $_columns;
private $_datamanager_filename;
private $_data;
private $_changed;
public $beforeSaveData;
function __construct($filename) {
$this->_changed = false;
$this->beforeSaveData = null;
$this->_datamanager_filename = $filename;
$this->loadData ();
}
function __destruct() {
$this->_changed && $this->saveData ();
}
private function _min($var1, $var2) {
if (is_numeric ( $var1 ) && is_numeric ( $var2 ))
return min ( $var1, $var2 );
return $var1 < $var2 ? $var1 : $var2;
}
private function _max($var1, $var2) {
if (is_numeric ( $var1 ) && is_numeric ( $var2 ))
return max ( $var1, $var2 );
return $var1 < $var2 ? $var2 : $var1;
}
private function _addNewRecord($columns, $filters = null) {
$result = array ();
$match = true;
for($i = 0; $i < count ( $this->_columns ); $i ++) {
$col_value = isset ( $columns [$i] ) ? $columns [$i] : null;
if (null != $filters && isset ( $filters [$i] )) {
$operator = $filters [$i] [0];
$operand = $filters [$i] [1];
switch ($operator) {
case '==' :
$match = $match && $col_value == $operand;
break;
case '===' :
$match = $match && $col_value === $operand;
break;
case '<' :
$match = $match && $col_value < $operand;
break;
case '>' :
$match = $match && $col_value > $operand;
break;
case '<=' :
$match = $match && $col_value <= $operand;
break;
case '>=' :
$match = $match && $col_value >= $operand;
break;
case '!=' :
$match = $match && $col_value != $operand;
break;
case '!==' :
$match = $match && $col_value !== $operand;
break;
case 'like' :
$match = $match && strpos ( $col_value, $operand ) == 0;
break;
case 'not like' :
$match = $match && strpos ( $col_value, $operand ) != 0;
break;
case 'in' :
$match = $match && array_key_exists ( $col_value, explode ( ',', $operand ) );
break;
}
if (! $match)
break;
}
$result [$i] = $col_value;
}
$this->_changed = $match;
return $match ? $result : null;
}
public function _groupRecordSet(&$recordset, $groups) {
$grouped_recordset = array ();
if (! is_array ( $groups ))
return $recordset;
$group_id = array_keys ( $groups );
$group_id = $group_id [0];
$group_fct = $groups [$group_id];
$last_grp_value = null;
foreach ( $recordset as $record ) {
$new_grp_record = 'group' == $group_fct && $last_grp_value !== $record [$group_id];
if ($new_grp_record) {
printf ( '%s<br>', _esc ( 'New group found' ) );
if (! empty ( $grouped_record ))
$grouped_recordset [] = $grouped_record;
$grouped_record = array_fill ( 0, count ( $this->_columns ) - 1, 0 );
$j = 0;
} else
$j ++;
dumpVar ( $record );
for($i = 0; $i < count ( $this->_columns ); $i ++) {
printf ( _esc ( "searcing to see if column <b>%s</b> defined as group..." ), $this->_col_names [$i] );
if (isset ( $groups [$i] )) {
echo getSpanE ( _esc ( 'found' ), 'green' ) . "; function=$group_fct<br>";
switch ($group_fct) {
case 'sum' :
$grouped_record [$i] += $record [$i];
break;
case 'avg' :
$grouped_record [$i] = ($grouped_record [$i] * ($j - 1) + $record [$i]) / $j;
break;
case 'min' :
$grouped_record [$i] = $this->_min ( $grouped_record [$i], $record [$i] );
break;
case 'max' :
$grouped_record [$i] = $this->_max ( $grouped_record [$i], $record [$i] );
break;
default :
$grouped_record [$i] ++;
break;
}
} else {
echo getSpanE ( _esc ( 'not found' ), 'red' ) . '<br>';
$grouped_record [$i] ++;
}
}
}
$grouped_recordset [] = $grouped_record;
return $grouped_recordset;
}
public function createView($from_date, $to_date, $filters = null, $exclude_keys = null) {
$dataset = array ();
$from_date = strtotime ( $from_date );
$to_date = strtotime ( $to_date );
$_data = &$this->getData ();
foreach ( $_data as $timestamp => $record ) {
if (! empty ( $exclude_keys ) && in_array ( $timestamp, $exclude_keys ) || ! ($timestamp >= $from_date && $timestamp <= $to_date))
continue;
$new_rec = $this->_addNewRecord ( $record, $filters );
if (null != $new_rec)
$dataset [$timestamp] = $new_rec;
}
return $dataset;
}
public function loadData() {
if (! _file_exists ( $this->_datamanager_filename ) || 0 == filesize ( $this->_datamanager_filename )) {
$this->_data = array ();
return true;
}
$buffer = file_get_contents ( $this->_datamanager_filename );
return null !== ($this->_data = json_decode ( $buffer, true ));
}
public function saveData() {
if (_is_callable ( $this->beforeSaveData ))
_call_user_func ( $this->beforeSaveData );
$data = json_encode ( $this->_data );
$result = file_put_contents ( $this->_datamanager_filename, $data ) !== FALSE;
$this->_changed = ! $result;
return $result;
}
public function getRawData() {
if (! _file_exists ( $this->_datamanager_filename ))
return null;
return file_get_contents ( $this->_datamanager_filename );
}
public function &getData() {
return $this->_data;
}
public function setColNames($names) {
$this->_columns = $names;
}
}
?>