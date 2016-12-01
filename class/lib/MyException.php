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
 * @version : 0.2.3-36 $
 * @commit  : c4d8a236c57b60a62c69e03c1273eaff3a9d56fb $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Thu Dec 1 04:37:45 2016 +0100 $
 * @file    : MyException.php $
 * 
 * @id      : MyException.php | Thu Dec 1 04:37:45 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

require_once 'LogFile.php';
class MyException extends \Exception
{
protected function _trigger_exception($message, $code = null, $previous = null)
{
if (defined(__NAMESPACE__.'\\PHP_DEBUG_ON') && PHP_DEBUG_ON)
if (defined(__NAMESPACE__.'\\TRACE_DEBUG_LOG')) {
global $settings;
$log_file = new LogFile(TRACE_DEBUG_LOG, $settings);
$log_file->writeSeparator();
$log_file->writelnLog(sprintf('[%s] %s (%s: %d)', date(DATETIME_FORMAT), $message, _esc('code'), $code));
$log_file->writeSeparator();
$trace_str = $this->getTraceAsString();
if (! empty($trace_str))
$log_file->writelnLog($trace_str);
} else
trigger_error(_esc('This should never happen. PHP_DEBUG_ON is on but TRACE_DEBUG_LOG is not defined. This is strange!'), E_USER_WARNING);
$this->message = $message;
$this->code = $code;
parent::__construct($message, $code, $previous);
}
public function __construct($message, $code = null, $previous = null)
{
$this->_trigger_exception($message, $code, $previous);
}
}
?>