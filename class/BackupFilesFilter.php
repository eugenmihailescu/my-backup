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
 * @file    : BackupFilesFilter.php $
 * 
 * @id      : BackupFilesFilter.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;
class BackupFilesFilter {
private $_md5_cache;
public $onAbortCallback;
public $onProgressCallback;
public $onOutputCallback;
private function _setCacheCallbacks() {
$this->_md5_cache->onAbortCallback = $this->onAbortCallback;
$this->_md5_cache->onProgressCallback = $this->onProgressCallback;
$this->_md5_cache->onOutputCallback = $this->onOutputCallback;
}
function __construct($log_filename, $ref_log_filename) {
$this->_md5_cache = new LocalFilesMD5 ( $log_filename, $ref_log_filename );
$this->setCallback ();
$this->_setCacheCallbacks ();
}
public function setCallback($clbk_abort = null, $clbk_progress = null, $clbk_output = null) {
$this->onAbortCallback = $clbk_abort;
$this->onProgressCallback = $clbk_progress;
$this->onOutputCallback = $clbk_output;
}
public function filter($filename, $timestamp) {
$this->_setCacheCallbacks ();
$result = $this->_md5_cache->diff ( $filename, $timestamp );
$this->_md5_cache->changed () && $this->_md5_cache->write ();
return $result;
}
}
?>
