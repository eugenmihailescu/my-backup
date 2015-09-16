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
 * @version : 0.2.2 $
 * @commit  : 23a9968c44669fbb2b60bddf4a472d16c006c33c $
 * @author  : Eugen Mihailescu <eugenmihailescux@gmail.com> $
 * @date    : Wed Sep 16 11:33:37 2015 +0200 $
 * @file    : MyException.php $
 * 
 * @id      : MyException.php | Wed Sep 16 11:33:37 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;
class MyException extends \Exception {
protected function _trigger_exception($message, $code = null, $previous = null) {
if (defined ( 'PHP_DEBUG_ON' ) && PHP_DEBUG_ON)
if (defined ( 'TRACE_DEBUG_LOG' )) {
global $settings;
$log_file = new LogFile ( TRACE_DEBUG_LOG, $settings );
$log_file->writeLog ( str_repeat ( '-', 80 ) . PHP_EOL );
$log_file->writeLog ( sprintf ( '[%s] %s (%s: %d)' . PHP_EOL, date ( DATETIME_FORMAT ), $message, _esc ( 'code' ), $code ) );
$log_file->writeLog ( str_repeat ( '-', 80 ) . PHP_EOL );
$trace_str = $this->getTraceAsString ();
if (! empty ( $trace_str ))
$log_file->writeLog ( $trace_str . PHP_EOL );
} else
trigger_error ( _esc ( 'This should never happen. PHP_DEBUG_ON is on but TRACE_DEBUG_LOG is not defined. This is strange!' ), E_USER_WARNING );
$this->message = $message;
$this->code = $code;
parent::__construct ( $message, $code, $previous );
}
public function __construct($message, $code = null, $previous = null) {
$this->_trigger_exception ( $message, $code, $previous );
}
}
?>
