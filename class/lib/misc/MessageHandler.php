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
 * @version : 0.2.2-10 $
 * @commit  : dd80d40c9c5cb45f5eda75d6213c678f0618cdf8 $
 * @author  : Eugen Mihailescu <eugenmihailescux@gmail.com> $
 * @date    : Mon Dec 28 17:57:55 2015 +0100 $
 * @file    : MessageHandler.php $
 * 
 * @id      : MessageHandler.php | Mon Dec 28 17:57:55 2015 +0100 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

define ( __NAMESPACE__.'\\MESSAGE_ITEM_UNREAD', 0 );
define ( __NAMESPACE__.'\\MESSAGE_ITEM_READ', 1 );
define ( __NAMESPACE__.'\\MESSAGE_TYPE_NORMAL', 0 );
define ( __NAMESPACE__.'\\MESSAGE_TYPE_WARNING', 1 );
define ( __NAMESPACE__.'\\MESSAGE_TYPE_ERROR', 2 );
class MessageHandler {
private $_message_types;
private $_messages;
private $_filename;
function __construct($filename = null) {
$this->_message_types = array ();
$this->_messages = array ();
$this->addMessageType ( MESSAGE_TYPE_NORMAL, 'normal' );
$this->addMessageType ( MESSAGE_TYPE_WARNING, 'warning' );
$this->addMessageType ( MESSAGE_TYPE_ERROR, 'error', 'red' );
$this->_filename = $filename;
$this->loadFromFile ( $filename );
}
private function _getMessageByKeys($type, $status, $timestamp, $text, $ref_id = null) {
$result = false;
foreach ( $this->_messages as $message )
if ($message->type == $type && $message->status = $status && $message->timestamp == $timestamp && $message->text == $text && (empty ( $ref_id ) || ! empty ( $ref_id ) && $message->ref_id == $ref_id)) {
$result = $message;
break;
}
return $result;
}
public function getMessageById($msg_id) {
$result = false;
if (isset ( $this->_messages [$msg_id] ))
$result = $this->_messages [$msg_id];
return $result;
}
private function _validateNullValue($key_name, $value) {
if (empty ( $value ))
throw new MyException ( "The key named '$key_name' cannot be empty" );
}
public function addMessageType($type_key, $type_name, $fg_color = null, $bg_color = null) {
$this->_validateNullValue ( '$type_name', $type_name );
if (isset ( $this->_message_types [$type_key] ))
throw new MyException ( "Message type '$type_name' already defined (see key=$type_key)" );
$this->_message_types [$type_key] = array (
'name' => $type_name,
'fg' => $fg_color,
'bg' => $bg_color 
);
}
public function addMessage($type, $text, $ref_id = null, $status = MESSAGE_ITEM_UNREAD) {
$this->_validateNullValue ( 'text', $text );
$timestamp = time ();
if (false !== ($message = $this->_getMessageByKeys ( $type, $status, $timestamp, $text, $ref_id ))) {
$msg = "Message with type='$type', status='$status', timestamp='$timestamp', text='$text'";
if (! empty ( $ref_id ))
$msg .= " and ref_id '$ref_id' already exist";
throw new MyException ( $msg . ' (see msg_id=' . $message->msg_id . ')' );
}
$result = new MessageItem ( $type, $text, $ref_id, $status );
$this->_messages [$result->msg_id] = $result;
$this->saveToFile ();
return $result;
}
public function delMessage($type, $status, $timestamp, $text, $ref_id) {
$result = false;
if (false !== ($message = $this->_getMessageByKeys ( $type, $status, $timestamp, $text, $ref_id ))) {
unset ( $this->_messages [$message->msg_id] );
$this->saveToFile ();
$result = true;
}
return $result;
}
public function setReadStatus($msg_id, $status = null) {
$array = array (
'read',
'unread' 
);
$result = true;
if (in_array ( $msg_id, $array ) || isset ( $this->_messages [$msg_id] )) {
if (in_array ( $msg_id, $array )) {
$alerts = $this->getMessagesByKeys ( array (
'status' 
), array (
'read' != $msg_id ? MESSAGE_ITEM_READ : MESSAGE_ITEM_UNREAD 
) );
foreach ( array_keys ( $alerts ) as $msg_id )
$this->_messages [$msg_id]->status = (MESSAGE_ITEM_UNREAD == $this->_messages [$msg_id]->status ? MESSAGE_ITEM_READ : MESSAGE_ITEM_UNREAD);
} else
$this->_messages [$msg_id]->status = (MESSAGE_ITEM_UNREAD != $status ? MESSAGE_ITEM_READ : MESSAGE_ITEM_UNREAD);
$this->saveToFile ();
} else
$result = false;
return $result;
}
public function getMessagesByKeys($key_name, $key_value, $top = 0) {
$result = array ();
foreach ( $this->_messages as $message ) {
$match = true;
foreach ( $key_name as $key => $prop_name ) {
if (false === ($match = $match && (is_array ( $key_value [$key] ) ? in_array ( $message->$prop_name, $key_value [$key] ) : ($message->$prop_name == $key_value [$key]))))
break;
}
if ($match) {
$result [$message->msg_id] = $message;
if (count ( $result ) == $top)
break;
}
}
return $result;
}
public function getMessagesByDate($timestamp, $newer = true) {
$result = array ();
foreach ( $this->_messages as $message ) {
if ((! $newer && $message->timestamp <= $timestamp) || ($newer && $message->timestamp >= $timestamp))
$result [$message->msg_id] = $message;
}
return $result;
}
public function getMessages() {
return $this->_messages;
}
public function getMessageTypes() {
return $this->_message_types;
}
public function getMessagesByType($exclude = null) {
$result = array ();
foreach ( array_keys ( $this->_message_types ) as $type )
if (empty ( $exclude ) || ! in_array ( $type, $exclude ))
$result = array_merge ( $result, $this->getMessagesByKeys ( array (
'type' 
), array (
$type 
) ) );
return $result;
}
public function count() {
return count ( $this->_messages );
}
public function delMessagesByDate($timestamp) {
$keys = array_keys ( $this->getMessagesByDate ( $timestamp, false ) );
$count = 0;
foreach ( $keys as $msg_id )
if (false !== $this->getMessageById ( $msg_id )) {
unset ( $this->_messages [$msg_id] );
$count ++;
}
if ($count > 0)
$this->saveToFile ();
}
public function loadFromFile($filename) {
$msg_props = array (
'type',
'status',
'text',
'ref_id',
'timestamp',
'msg_id' 
);
if ($result = (file_exists ( $filename ) && null !== ($data = json_decode ( file_get_contents ( $filename ), true )))) {
if (isset ( $data ['messages'] )) {
foreach ( $data ['messages'] as $item ) {
$msg_item = new MessageItem ( false, false );
foreach ( $msg_props as $prop )
$msg_item->$prop = $item [$prop];
$this->_messages [$item ['msg_id']] = $msg_item;
}
}
if (null !== $data && isset ( $data ['types'] ))
$this->_message_types = $data ['types'];
}
return $result;
}
public function saveToFile($filename = null) {
if (empty ( $filename ))
$filename = $this->_filename;
$array = array ();
$msg_props = array (
'type',
'status',
'text',
'ref_id',
'timestamp',
'msg_id' 
);
foreach ( $this->_messages as $message ) {
$item = array ();
foreach ( $msg_props as $prop )
$item [$prop] = $message->$prop;
$array [] = $item;
}
return file_put_contents ( $filename, json_encode ( array (
'messages' => $array,
'types' => $this->_message_types 
) ) );
}
}
?>