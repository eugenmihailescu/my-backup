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
 * @version : 0.2.3-37 $
 * @commit  : 56326dc3eb5ad16989c976ec36817cab63bc12e7 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Wed Dec 7 18:54:23 2016 +0100 $
 * @file    : BackupFilesFilter.php $
 * 
 * @id      : BackupFilesFilter.php | Wed Dec 7 18:54:23 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
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
function __construct( $log_filename, $ref_log_filename ) {
$this->_md5_cache = new LocalFilesMD5( $log_filename, $ref_log_filename );
$this->setCallback();
$this->_setCacheCallbacks();
}
public function setCallback( $clbk_abort = null, $clbk_progress = null, $clbk_output = null ) {
$this->onAbortCallback = $clbk_abort;
$this->onProgressCallback = $clbk_progress;
$this->onOutputCallback = $clbk_output;
}
public function filter( $filename, $timestamp ) {
$this->_setCacheCallbacks();
$result = $this->_md5_cache->diff( $filename, $timestamp );
$this->_md5_cache->changed() && $this->_md5_cache->write();
return $result;
}
}
?>