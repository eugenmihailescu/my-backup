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
 * @version : 0.2.3-27 $
 * @commit  : 10d36477364718fdc9b9947e937be6078051e450 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Fri Mar 18 10:06:27 2016 +0100 $
 * @file    : MessageItem.php $
 * 
 * @id      : MessageItem.php | Fri Mar 18 10:06:27 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
class MessageItem {
public $type;
public $status; 
public $text;
public $ref_id;
public $timestamp;
public $msg_id;
public $email_sent;
function __construct( $type, $text, $ref_id = null, $status = MESSAGE_ITEM_UNREAD ) {
if ( ! in_array( $status, array( MESSAGE_ITEM_UNREAD, MESSAGE_ITEM_READ ) ) )
throw new MyException( 
sprintf( 
"Unknown status type '$status'. Allowed values are MESSAGE_ITEM_UNREAD(%d),MESSAGE_ITEM_READ(%d)", 
MESSAGE_ITEM_UNREAD, 
MESSAGE_ITEM_READ ) );
$this->email_sent = false;
$this->type = $type;
$this->status = $status;
$this->text = $text;
$this->ref_id = $ref_id;
$this->timestamp = time();
$this->msg_id = uniqid( null, true );
}
}
?>