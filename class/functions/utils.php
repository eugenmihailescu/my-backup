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
 * @file    : utils.php $
 * 
 * @id      : utils.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;

date_default_timezone_set ( "UTC" );
define ( "WPMYBACKUP_AUTHOR", '@author:		Eugen Mihailescu <eugenmihailescux@gmail.com> $' );
$utils_includes = array (
'wp-schedule.php',
'cli-options.php',
'options.php',
'wp-wrappers.php',
'files.php',
'random.php',
'url.php',
'mail.php',
'nonce.php',
'signals.php',
'session.php',
'sys.php',
'php.php',
'sys-tools.php',
'help.php',
'ui.php',
'history.php',
'ftp.php',
'format.php',
'vat.php' 
);
foreach ( $utils_includes as $include_file )
file_exists ( UTILS_PATH . $include_file ) && include_once UTILS_PATH . $include_file;
function getDatesByAge($dates, $days, $filter_by) {
$compare = strtotime ( "-$days days", time () );
$result = array ();
foreach ( $dates as $d )
if (array_search ( $filter_by, array (
0,
sign ( $d - $compare ) 
) ))
$result [] = $d;
return $result;
}
function getHumanReadableSize($size, $precision = 2, $return_what = 0) {
$units = array (
'B',
'KiB',
'MiB',
'GiB',
'TiB',
'PiB' 
);
for($i = 0; abs ( $size ) >= 1024; $i ++)
$size /= 1024;
$i = $i + 1 > count ( $units ) ? count ( $units ) - 1 : $i;
if ($return_what == 1)
return $i;
elseif ($return_what == 2)
return $units [$i];
else
return sprintf ( '%.' . $precision . 'f %s', $size, $units [$i] );
}
function getTransferSpeed($start, $end) {
$sec = $start->diff ( $end )->format ( '%s' );
if ($sec > 0 && file_exists ( $part ))
$rate = filesize ( $part ) / $sec;
else
$rate = - 1;
return sprintf ( '%s/s', getHumanReadableSize ( $rate ) );
}
function getPluginAuthorEmail() {
$result = preg_match ( '/^@author:\s*[\w\s]*\s*\<([\w\@\.]*)\>\s*\$$/', WPMYBACKUP_AUTHOR, $author_email_matches ) ? $author_email_matches [1] : '';
return trim ( $result );
}
function getPluginAuthorName() {
$result = preg_match ( '/^@author:\s*([\w\s]*)\s*/', WPMYBACKUP_AUTHOR, $author_matches ) ? $author_matches [1] : '';
return trim ( $result );
}
function getPluginVersion($full_version = true) {
return sprintf ( '%s%s', APP_VERSION_ID, $full_version ? ', ' . APP_VERSION_DATE : '' );
}
function isJobRunning($settings = null) {
global $_branch_id;
$lock_file = JOBS_LOCK_FILE;
$last_job_id = false;
$is_running = false;
$f = fopen ( $lock_file, 'wb' );
if (! $f || ! flock ( $f, LOCK_EX | LOCK_NB )) {
$is_running = true;
if (null != $settings) {
$jobs_log = new LogFile ( JOBS_LOGFILE, $settings );
$last_job_id = $jobs_log->getLastJobId ();
}
}
if (! $is_running)
flock ( $f, LOCK_UN );
fclose ( $f );
return array (
$is_running,
getSpanE ( sprintf ( _esc ( 'Backup seems to be %s now' ), $is_running ? "RUNNING" : "IDLE" ), $is_running ? 'red' : '#2ea2cc', $is_running ? 'bold' : 'normal' ),
$last_job_id 
);
}
function getLogfileByType($log_type) {
switch ($log_type) {
case 'jobs' :
$log = JOBS_LOGFILE;
break;
case 'full' :
$log = OUTPUT_LOGFILE;
break;
case 'debug' :
$log = TRACE_DEBUG_LOG;
break;
case 'curldebug' :
$log = CURL_DEBUG_LOG;
break;
case 'statsdebug' :
$log = STATISTICS_DEBUG_LOG;
break;
case 'traceaction' :
$log = TRACE_ACTION_LOGFILE;
break;
case 'errors' :
$log = ERROR_LOG;
break;
case 'smtpdebug' :
$log = SMTP_DEBUG_LOG;
break;
default :
$log = false;
break;
}
return $log;
}
function std_dev($array) {
$n = count ( $array );
if (0 == $n)
return 0;
$mean = array_sum ( $array ) / $n;
$carry = 0.0;
foreach ( $array as $val )
$carry += ($d = (( double ) $val) - $mean) * $d;
return array (
sqrt ( $carry / $n ),
$mean 
);
}
function swap_items(&$item1, &$item2) {
$tmp = $item1;
$item1 = $item2;
$item2 = $tmp;
}
?>
