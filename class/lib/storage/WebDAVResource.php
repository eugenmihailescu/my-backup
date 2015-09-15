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
 * @file    : WebDAVResource.php $
 * 
 * @id      : WebDAVResource.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;

class WebDAVResource {
public $creation_date;
public $modified_date;
public $supported_locks;
public $content_length;
public $content_type;
public $executable;
public $status;
public $tag;
public $quota_available_bytes;
public $quota_used_bytes;
function __construct() {
$this->creation_date = null;
$this->modified_date = null;
$this->supported_locks = null;
$this->content_length = null;
$this->content_type = null;
$this->executable = null;
$this->status = null;
$this->tag = null;
$this->quota_available_bytes = null;
$this->quota_used_bytes = null;
}
}
?>