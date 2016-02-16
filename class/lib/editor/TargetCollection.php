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
 * @file    : TargetCollection.php $
 * 
 * @id      : TargetCollection.php | Tue Feb 16 21:41:51 2016 UTC | Eugen Mihailescu eugenmihailescux@gmail.com $
*/

namespace MyBackup;
class TargetCollection {
private $_items;
private $_filename;
function __construct($filename = null) {
$this->_items = array ();
$this->_filename = $filename;
if (file_exists ( $filename ))
$this->loadFromFile ( $filename );
}
public function addTargetItem($target_item, $uniq_id = null) {
(null == $uniq_id) && $uniq_id = uniqid ( null, true );
$target_item->uniq_id = $uniq_id;
$this->_items [$uniq_id] = $target_item;
return end ( $this->_items );
}
public function delTargetItem($item_id) {
if (! isset( $this->_items [ $item_id]))
throw new MyException ( "No item with id=$item_id found on target collection" );
unset ( $this->_items [$item_id] );
$this->saveToFile ();
}
public function getTargetItem($item_id) {
$found = isset( $this->_items [ $item_id]);
return $found ? $this->_items [$item_id] : false;
}
public function getCount() {
return count ( $this->_items );
}
public function saveToFile($filename = null) {
$fname = null == $filename ? $this->_filename : $filename;
$err = _esc ( "Cannot save target items due to file " ) . "'$fname' %s";
if (empty ( $fname ))
throw new MyException ( sprintf ( $err, _esc ( 'is empty' ) ) );
$array = array ();
foreach ( $this->_items as $item_id => $target_item )
$array [$item_id] = array (
'description' => $target_item->description,
'enabled' => $target_item->enabled,
'type' => $target_item->type,
'targetSettings' => $target_item->targetSettings 
);
file_put_contents ( $fname, json_encode ( $array ) );
}
public function loadFromFile($filename = null) {
$fname = null == $filename ? $this->_filename : $filename;
$err = _esc ( "Cannot load target items due to file " ) . "'$fname' %s";
if (empty ( $fname ))
throw new MyException ( sprintf ( $err, _esc ( 'is empty' ) ) );
$this->_items = array ();
if (file_exists ( $fname )) {
$array = json_decode ( file_get_contents ( $fname ), true );
foreach ( $array as $key => $target_item ) {
$item_def = array_merge ( $target_item, $this->getItemInfoByType ( $target_item ['type'] ) );
$item_obj = new TargetCollectionItem ( $item_def );
$target_item = $this->addTargetItem ( $item_obj, $key );
}
}
}
public function getItems() {
return $this->_items;
}
public function getFileName() {
return $this->_filename;
}
public function getItemInfoByType($type) {
global $registered_targets;
$target_info = $registered_targets [$type];
return array (
'folder_style' => $target_info ['folder_style'],
'function_name' => $target_info ['file_function'],
'icon' => $target_info ['logo'],
'title' => $target_info ['title'],
'type' => $type 
);
}
}
?>