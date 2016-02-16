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
 * @file    : signals.php $
 * 
 * @id      : signals.php | Tue Feb 16 21:41:51 2016 UTC | Eugen Mihailescu eugenmihailescux@gmail.com $
*/

namespace MyBackup;

function addProcessSignal($process_name, $sender = '') {
0 !== $process_name && empty ( $process_name ) && die ( _esc ( 'Internal error: cannot register a signal for an undefined process' ) );
if (! (file_exists ( SIGNALS_LOGFILE ) && filesize ( SIGNALS_LOGFILE ) > 0))
$signals = array ();
else
$signals = json_decode ( file_get_contents ( SIGNALS_LOGFILE ), true );
if (! isset( $signals [ $process_name])) {
$signals [$process_name] = array ();
}
$signals [$process_name] [$sender] = time (); 
file_put_contents ( SIGNALS_LOGFILE, json_encode ( $signals ) );
}
function ackProcessSignal($process_name, $sender = null) {
0 !== $process_name && empty ( $process_name ) && die ( _esc ( "Cannot acknowledge a signal for the specified (empty) process" ) );
if (! (file_exists ( SIGNALS_LOGFILE )))
return false;
$signals = json_decode ( file_get_contents ( SIGNALS_LOGFILE ), true );
$changed = false;
while ( isset( $signals [ $process_name]) && (empty ( $sender ) || isset( $signals [$process_name] [ $sender])) ) {
if (empty ( $sender ))
unset ( $signals [$process_name] );
else {
unset ( $signals [$process_name] [$sender] );
if (empty ( $signals [$process_name] ))
unset ( $signals [$process_name] );
}
$changed = true;
}
$changed && file_put_contents ( SIGNALS_LOGFILE, json_encode ( $signals ) );
}
function chkProcessSignal($process_name, $sender = null) {
if (! (file_exists ( SIGNALS_LOGFILE )))
return false;
$signals = json_decode ( file_get_contents ( SIGNALS_LOGFILE ), true );
if (is_array ( $signals ))
if (isset( $signals [ $process_name]) && (empty ( $sender ) || isset( $signals [$process_name] [ $sender])))
return array (
$process_name,
$sender 
);
return false;
}
function clearObsoleteProcessSignals() {
if (! (file_exists ( SIGNALS_LOGFILE )))
return;
$count = 0;
$signals = json_decode ( file_get_contents ( SIGNALS_LOGFILE ), true );
foreach ( $signals as $process_name => $senders )
foreach ( $senders as $sender => $timestamp )
if (time () - $timestamp > PROCESS_SIGNAL_TIMEOUT) {
unset ( $signals [$process_name] [$sender] );
$count ++;
}
if ($count > 0)
file_put_contents ( SIGNALS_LOGFILE, json_encode ( $signals ) );
}
?>