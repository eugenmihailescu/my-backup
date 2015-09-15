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
 * @file    : MessageItem.php $
 * 
 * @id      : MessageItem.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;
class MessageItem {
public $type;
public $status; 
public $text;
public $ref_id;
public $timestamp;
public $msg_id;
function __construct($type, $text, $ref_id = null, $status = MESSAGE_ITEM_UNREAD) {
if ($status != MESSAGE_ITEM_UNREAD && $status != MESSAGE_ITEM_READ)
throw new MyException ( "Unknown status type '$status'. Allowed values are MESSAGE_ITEM_UNREAD(false/0),MESSAGE_ITEM_READ(true/1)" );
$this->type = $type;
$this->status = $status;
$this->text = $text;
$this->ref_id = $ref_id;
$this->timestamp = time ();
$this->msg_id = uniqid ( null, true );
}
}
?>
