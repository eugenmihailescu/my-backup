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
 * @file    : HtmlTableConverter.php $
 * 
 * @id      : HtmlTableConverter.php | Tue Dec 13 06:40:49 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

class HtmlTableConverter {
private $_vBorder;
private $_hBorder;
private $_cLeft;
private $_cRight;
private $_cMiddle;
private function _initOptions($options) {
if (empty ( $options ))
return;
$this->_vBorder = $options [0];
$this->_hBorder = $options [1];
$this->_cLeft = $options [2];
$this->_cRight = $options [3];
$this->_cMiddle = $options [4];
}
function __construct() {
$this->_initOptions ( array (
'|',
'-',
'+',
'+',
'+' 
) );
}
private function _htmlTable2Array($html) {
$a_tables = array ();
if (false !== preg_match_all ( '/<(table)\b[^>]*>.*?<\/\1>/', $html, $tables ))
foreach ( $tables [0] as $table ) {
$new_table = array ();
if (false !== preg_match_all ( '/<(tr)\b[^>]*>.*?<\/\1>/', $table, $rows ))
foreach ( $rows [0] as $row ) {
$new_row = array ();
if (false !== preg_match_all ( '/<(td)\b[^>]*>.*?<\/\1>/', $row, $cols ))
foreach ( $cols [0] as $col ) {
$new_row [] = preg_replace ( '/<(td)\b[^>]*>(.*?)<\/\1>/', "$2", $col );
}
$new_table [] = $new_row;
}
$a_tables [] = $new_table;
}
return $a_tables;
}
private function _array2Ascii($array, $outer_border = true, $inner_border = true) {
$l_array = array ();
$c_array = array ();
foreach ( $array as $row )
$l_array [] = array_map ( 'strlen', $row );
foreach ( $l_array as $l_row )
foreach ( $l_row as $col_id => $l_col ) {
isset( $c_array [ $col_id]) || $c_array [] = 0;
$c_array [$col_id] = max ( $c_array [$col_id], $l_col + 2 );
}
$result = '';
$width = array_sum ( $c_array ) + 3;
$border = $this->_cLeft . str_repeat ( $this->_hBorder, $c_array [0] );
for($i = 1; $i < count ( $c_array ); $i ++)
$border .= $this->_cMiddle . str_repeat ( $this->_hBorder, $c_array [$i] );
$border .= $this->_cRight;
if ($outer_border)
$result .= $border . PHP_EOL;
foreach ( $array as $row ) {
$result .= $this->_vBorder;
$width_ = 0;
foreach ( $row as $col_id => $col ) {
$result .= sprintf ( ' %-' . ($c_array [$col_id] - 1) . 's%s', $col, $this->_vBorder );
$width_ += $c_array [$col_id] + 1;
}
if ($col_id + 1 < count ( $c_array )) {
$result .= str_repeat ( ' ', ($width - $width_) ) . $this->_vBorder;
}
$result .= PHP_EOL;
if ($inner_border)
$result .= $border . PHP_EOL;
}
if ($outer_border && ! $inner_border)
$result .= $border;
return $result;
}
public function htmlTable2Ascii($html, $options = null) {
$this->_initOptions ( $options );
$result = array ();
$arrays = $this->_htmlTable2Array ( $html );
foreach ( $arrays as $table )
$result [] = $this->_array2Ascii ( $table );
return $result;
}
}
?>