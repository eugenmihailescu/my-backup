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
 * @version : 0.2.3-3 $
 * @commit  : 961115f51b7b32dcbd4a8853000e4f8cc9216bdf $
 * @author  : Eugen Mihailescu <eugenmihailescux@gmail.com> $
 * @date    : Tue Feb 16 15:27:30 2016 +0100 $
 * @file    : LogsEditor.php $
 * 
 * @id      : LogsEditor.php | Tue Feb 16 15:27:30 2016 +0100 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
class LogsEditor extends AbstractTargetEditor {
private $_is_running;
private $_fct_chk_status;
private $_index;
private function _getLogfileRow( $log_type, $log_name ) {
ob_start();
$spy_shown = $this->_is_running[0] ? 'block' : 'none';
$logfile = getLogfileByType( $log_type );
$log_exists = is_file( $logfile );
$view_id = 'view_' . $log_type . '_log';
$clear_id = 'clear_' . $log_type . '_log';
echo '<tr>';
echo "<td><label for='$view_id'>" . ucwords( $log_name ) . " log</label></td>";
echo '<td>:</td>';
echo '<td ' . ( $log_exists ? '' : 'colspan="4"' ) . '>' . ( $log_exists ? str_replace( 
LOG_DIR, 
'<span style="color:#00adee;cursor:help;border-width:1px;border-bottom-style:dotted;" onclick="jsMyBackup.helpROOT();">ROOT</span>' .
DIRECTORY_SEPARATOR, 
$logfile ) : '(log file does not exist)' ) . '</td>';
if ( $log_exists ) {
echo "<td><input style='width: 100%;' type='button' name='$view_id' id='$view_id' value='View' class='button' onclick='jsMyBackup.post(jsMyBackup.this_url,{action:\"dwl_file\",service:\"disk\",location:\"" .
( isWin() ? addslashes( $logfile ) : $logfile ) . "\",nonce:\"" . wp_create_nonce_wrapper( 'dwl_file' ) .
"\"});' title='Click to read this log file now'></td>";
printf( 
"<td><input type='button' name='%s' value='%s' class='button' onclick='jsMyBackup.clearLog(\"%s\",\"%s\");' title='%s'></td>", 
$clear_id, 
_esc( 'Clear' ), 
$log_type, 
$log_name, 
sprintf( _esc( 'Click to clear the %s log' ), $log_name ) );
printf( "<td style='color:#bbb;'>%s</td>", getHumanReadableSize( @filesize( $logfile ) ) );
echo "<td><input type='button' id='btn_monitor{$this->_index}' title='" .
sprintf( _esc( 'Spy the %s log' ), $log_name ) . "' onclick='jsMyBackup.spy(\"log_read\",\"$log_type\",\"" .
wp_create_nonce_wrapper( 'log_read' ) . "\",\"" . wp_create_nonce_wrapper( 'get_progress' ) . "\",\"" .
wp_create_nonce_wrapper( 'clean_progress' ) . "\",\"" . wp_create_nonce_wrapper( 'log_read_abort' ) .
"\");' class='button btn_monitor' style='display:$spy_shown'></td>";
$this->_index++;
}
echo '</tr>';
$result = ob_get_contents();
ob_end_clean();
return $result;
}
private function _getDebugTemplate() {
$template = $this->_getLogfileRow( 'errors', _esc( 'debug error' ) );
$template .= $this->_getLogfileRow( 'debug', _esc( 'debug trace' ) );
$template .= $this->_getLogfileRow( 'curldebug', _esc( 'Curl debug' ) );
$template .= $this->_getLogfileRow( 'statsdebug', _esc( 'sql+charts debug' ) );
$template .= $this->_getLogfileRow( 'traceaction', _esc( 'trace action' ) );
$template .= $this->_getLogfileRow( 'smtpdebug', _esc( 'SMTP debug' ) );
return $this->insertEditorTemplate( _esc( 'Debug logs' ), $template, null, true );
}
private function _getJavaScripts() {
global $PROGRESS_PROVIDER;
$this->java_scripts[] = "parent.plugin_dir='" . addslashes( dirname( realpath( $_SERVER['SCRIPT_NAME'] ) ) ) .
"';";
$this->java_scripts[] = "var d = document.getElementById('monitor_job'),
spy_status = document.getElementById('td_job_status').innerHTML,
callback = function () {
jsMyBackup.or = function () {
var status = document.getElementById('td_job_status').innerHTML;
if (status && status != spy_status) {
d = d && d.style && d.style.display == 'none' ? 'block' : 'none';
var el=document.getElementById('monitor_job');
if(el)el.style.display = d;
el=document.getElementById('monitor_log');
if(el)style.display = d;
spy_status = status;
}
};" . $this->_fct_chk_status . "}";
$this->java_scripts[] = "setInterval(callback," . LOG_CHECK_TIMEOUT . ");";
$this->java_scripts[] = "parent.helpROOT=function(){" .
getHelpCall( "'This is the site log folder, that is:<br><i>" . normalize_path( LOG_DIR ) . "</i>'", false ) .
"}";
$clear_log_click = sprintf( 
'jsMyBackup.post(jsMyBackup.this_url,{action:\\\'clear_log\\\',log_type:\\\'\'+log_type+\'\\\',nonce:\\\'%s\\\'});', 
wp_create_nonce_wrapper( 'clear_log' ) );
$this->java_scripts[] = "parent.clearLog=function(log_type, log_name){" . sprintf( 
"parent.popupConfirm('%s', '%s', null, {'%s':'%s','%s':null});", 
_esc( 'Log clear confirmation' ), 
sprintf( _esc( 'Are you sure you want to clear the %s log file?' ), "<b>'+log_name+'</b>" ), 
_esc( 'Yes' ), 
$clear_log_click, 
_esc( 'No' ) ) . "}";
$this->java_scripts[] = getBackupSourcesJS( $PROGRESS_PROVIDER );
}
protected function initTarget() {
parent::initTarget();
$this->_index = 0;
$this->_is_running = isJobRunning();
$this->root = ROOT_PATH;
$this->_fct_chk_status = "jsMyBackup.asyncGetContent(jsMyBackup.ajaxurl,'action=chk_status&tab=logs&nonce=" .
wp_create_nonce_wrapper( 'chk_status' ) . "','td_job_status',jsMyBackup.or);";
$this->inBetweenContent = $this->_getDebugTemplate();
$this->_getJavaScripts();
}
protected function getEditorTemplate() {
$help_1 = "'" . sprintf( 
_esc( 'The status is checked automatically (anyhow)<br>at each %s ms and displayed as such.' ), 
LOG_CHECK_TIMEOUT ) . "'";
echo $this->_getLogfileRow( 'jobs', _esc( 'jobs' ) );
echo $this->_getLogfileRow( 'full', _esc( 'full' ) );
require_once $this->getTemplatePath( 'logs.php' );
}
protected function getExpertEditorTemplate() {
global $TARGET_NAMES;
$help_1 = "'" . _esc( 'Enter the path where all logs will be created and keept.' ) . "'";
$help_2 = "'" . _esc( 'Check this option if you want to rotate the logs when they reach a certain size.' ) . "'";
$help_3 = "'" . _esc( 
'Enter the log maximum allowed size (in MiB).<br>They will be rotated as soon they reach this limit.' ) . "'";
$help_4 = "'" .
_esc( 
'This option allows you to isolate all the relevant log files per job instead to keep them globally in the same folder (`isolate` ie. they have their own folder) .' );
$help_4 .= _esc( 
' The main advantage of this approach is that when you want to troubleshoot a job all you have to do is to read the job`s logs from its isolated folder instead of searching the whole log for a fragment corresponding to that job.' );
$help_4 .= _esc( 
' Furthermore, you have Curl logs, you have debug trace logs and many, many other types. Their content will tell you smth. about that job and only that, which is cool.<br>For parallel backup jobs this is mandatory and default.<br>' );
$help_4 .= _esc( 
'This kind of isolated logs are written in GZip format so we don`t have to worry about rotating them because sometimes they are small but being so many they will start eating a lot of space on your disk.' ) .
"'";
$log_dir = $this->settings['logdir'];
$logbranched = strToBool( $this->settings['logbranched'] );
require_once $this->getTemplatePath( 'logs-expert.php' );
}
}
?>