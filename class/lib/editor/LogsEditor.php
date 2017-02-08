<?php
/**
 * ################################################################################
 * MyBackup
 * 
 * Copyright 2017 Eugen Mihailescu <eugenmihailescux@gmail.com>
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
 * @version : 1.0-3 $
 * @commit  : 1b3291b4703ba7104acb73f0a2dc19e3a99f1ac1 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Tue Feb 7 08:55:11 2017 +0100 $
 * @file    : LogsEditor.php $
 * 
 * @id      : LogsEditor.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
class LogsEditor extends AbstractTargetEditor
{
private $_is_running;
private $_index;
private $_branched_log;
private $_branched_logs;
private function _get_branched_log_file($logfile)
{
if (false === $this->_branched_log)
return $logfile;
return $this->_branched_log . DIRECTORY_SEPARATOR . basename($logfile) . '.gz';
}
private function _getLogfileRow($log_type, $log_name)
{
ob_start();
$spy_shown = $this->_is_running[0] ? 'block' : 'none';
$logfile = $this->_get_branched_log_file(getLogfileByType($log_type));
$log_exists = _is_file($logfile);
$view_id = 'view_' . $log_type . '_log';
$clear_id = 'clear_' . $log_type . '_log';
$help_1 = "'" . sprintf(_esc('The log file %s does not seem to exist. Make sure the log option is enabled by checking the appropiate box within the `%s` on %s tab.'), '<span style=\color:red\>' . basename($logfile) . '</span>', _esc('Expert settings'), getTabAnchorE(APP_SUPPORT)) . "'";
echo '<tr>';
echo "<td><label for='$view_id'>" . ucwords($log_name) . " log</label> : </td>";
echo '<td ' . ($log_exists ? '' : 'colspan="4"') . '>' . ($log_exists ? str_replace(LOG_DIR, '<span style="color:#00adee;cursor:help;border-width:1px;border-bottom-style:dotted;" onclick="jsMyBackup.helpROOT();">ROOT</span>' . DIRECTORY_SEPARATOR, $logfile) : sprintf('(%s) <a class="help" onclick=%s> [?]</a>', _esc('log file does not exist'), getHelpCall($help_1, true))) . '</td>';
if ($log_exists) {
echo "<td><input style='width: 100%;' type='button' name='$view_id' id='$view_id' value='View' class='button' onclick='jsMyBackup.post(jsMyBackup.this_url,{action:\"dwl_file\",service:\"disk\",location:\"" . (isWin() ? addslashes($logfile) : $logfile) . "\",nonce:\"" . wp_create_nonce_wrapper('dwl_file') . "\"});' title='Click to read this log file now'></td>";
printf("<td><input type='button' name='%s' value='%s' class='button' onclick='jsMyBackup.clearLog(\"%s\",\"%s\");' title='%s'></td>", $clear_id, _esc('Clear'), $log_type, $log_name, sprintf(_esc('Click to clear the %s log'), $log_name));
printf("<td style='color:#bbb;'>%s</td>", getHumanReadableSize(@filesize($logfile)));
echo "<td><input type='button' id='btn_monitor{$this->_index}' title='" . sprintf(_esc('Spy the %s log'), $log_name) . "' onclick='jsMyBackup.spy(\"log_read\",\"$log_type\",\"" . wp_create_nonce_wrapper('log_read') . "\",\"" . wp_create_nonce_wrapper('get_progress') . "\",\"" . wp_create_nonce_wrapper('clean_progress') . "\",\"" . wp_create_nonce_wrapper('log_read_abort') . "\");' class='button btn_monitor' style='display:$spy_shown'></td>";
$this->_index ++;
}
echo '</tr>';
$result = ob_get_contents();
ob_end_clean();
return $result;
}
private function _getDebugTemplate()
{
$template = $this->_getLogfileRow('errors', _esc('debug error'));
$template .= $this->_getLogfileRow('debug', _esc('debug trace'));
$template .= $this->_getLogfileRow('curldebug', _esc('Curl debug'));
$template .= $this->_getLogfileRow('statsdebug', _esc('sql+charts debug'));
$template .= $this->_getLogfileRow('traceaction', _esc('trace action'));
$template .= $this->_getLogfileRow('smtpdebug', _esc('SMTP debug'));
$template .= $this->_getLogfileRow('restoredebug', _esc('Restore debug'));
return $this->insertEditorTemplate(_esc('Debug logs'), $template, null, true);
}
private function _getJavaScripts()
{
global $PROGRESS_PROVIDER;
$this->java_scripts[] = "parent.plugin_dir='" . addslashes(dirname(realpath($_SERVER['SCRIPT_NAME']))) . "';";
$set_branched_log_action = 'set_branched_log';
$del_branched_log_action = 'del_branched_log';
$action = 'chk_status';
ob_start();
?>
parent.chk_status_nonce="<?php echo wp_create_nonce_wrapper( $action );?>";
parent.check_job_status=function(){
if(!parent.chk_status_nonce)
return;
var on_status_ready = function(xhr) {
var status = document.getElementById('td_job_status'),i,btn;
try {
var job_status = JSON.parse(xhr.responseText);
parent.chk_status_nonce=job_status.nonce;
if(status) status.innerHTML = job_status.message;
btn=document.querySelectorAll('.btn_monitor');
if(btn)
for(i=0;btn.length>i;i+=1)
btn[i].style.display=job_status.status?'inherit':'none';
} catch (e) {if(status) status.innerHTML = xhr.responseText;}
};
parent.asyncGetContent(parent.ajaxurl, 'action=<?php echo $action;?>&tab=logs&nonce='+parent.chk_status_nonce, parent.dummy, on_status_ready);
parent.chk_status_nonce=false;
};
parent.set_branched_log=function(sender){
parent.post(parent.this_url,{action:"<?php echo $set_branched_log_action;?>",log:sender.value,nonce:"<?php echo wp_create_nonce_wrapper($set_branched_log_action);?>"});
};
parent.del_branched_log=function(){
parent.post(parent.this_url,{action:"<?php echo $del_branched_log_action;?>",log:document.getElementById("branch_log_selector").value,nonce:"<?php echo wp_create_nonce_wrapper($del_branched_log_action);?>"});
};
setInterval(parent.check_job_status,<?php echo LOG_CHECK_TIMEOUT ;?>);
parent.helpROOT=function(){<?php echo getHelpCall( "'This is the site log folder, that is:<br><i>" . normalize_path( LOG_DIR ) . "</i>'", false );?>};
parent.clearLog=function(log_type, log_name){<?php
$branched_log='';
if ($this->_branched_log) {
$branched_log = sprintf(',log:\\\'%s\\\'', $this->_branched_log);
}
$clear_log_click = sprintf('jsMyBackup.post(jsMyBackup.this_url,{action:\\\'clear_log\\\',log_type:\\\'\'+log_type+\'\\\',nonce:\\\'%s\\\'%s});', wp_create_nonce_wrapper('clear_log'), $branched_log);
printf("parent.popupConfirm('%s', '%s', null, {'%s':'%s','%s':null});", _esc('Log clear confirmation'), sprintf(_esc('Are you sure you want to clear the %s log file?'), "<b>'+log_name+'</b>"), _esc('Yes, I`m damn sure'), $clear_log_click, _esc('No'));
?>
};
<?php
$this->java_scripts[] = ob_get_clean();
$this->java_scripts[] = getBackupSourcesJS($PROGRESS_PROVIDER);
}
protected function initTarget()
{
parent::initTarget();
$this->_index = 0;
$this->_is_running = isJobRunning();
$this->root = ROOT_PATH;
$this->_branched_log = false;
$this->_branched_logs = array();
if (defined(__NAMESPACE__.'\\BRANCHED_LOGS') && BRANCHED_LOGS) {
$this->_branched_logs = glob(LOG_DIR . 'job_*.*', GLOB_ONLYDIR);
empty($this->_branched_logs) || $this->_branched_log = $this->_branched_logs[0];
if (isset($_POST) && isset($_POST['action']) && 'set_branched_log' == $_POST['action']) {
$this->_branched_log = LOG_DIR . $_POST['log'];
}
}
$this->inBetweenContent = $this->_getDebugTemplate();
$this->_getJavaScripts();
}
protected function getEditorTemplate()
{
$help_1 = "'" . sprintf(_esc('The status is checked automatically (anyhow)<br>at each %s ms and displayed as such.'), LOG_CHECK_TIMEOUT) . "'";
if (defined(__NAMESPACE__.'\\BRANCHED_LOGS') && BRANCHED_LOGS) {
$options = '';
foreach ($this->_branched_logs as $branch_log_dir) {
$name = basename($branch_log_dir);
$selected = $name == basename($this->_branched_log) ? ' selected="selected"' : '';
$options .= sprintf('<option value="%s"%s>%s</option>', $name, $selected, $name);
}
?>
<tr>
<td><label for="branch_log_selector"><?php _pesc('Select log');?></label> :</td>
<td><select id="branch_log_selector" name="branch_log_selector" onchange="jsMyBackup.set_branched_log(this);"><?php echo $options;?></select></td>
<td colspan="4"><input type="button" class="button" value="<?php _pesc("Remove");?>" onclick="jsMyBackup.del_branched_log();"> <a
class='help' onclick=<?php echo echoHelp($help_1);?>>[?]</a></td>
</tr>
<?php
}
echo $this->_getLogfileRow('jobs', _esc('jobs'));
echo $this->_getLogfileRow('full', _esc('full'));
require_once $this->getTemplatePath('logs.php');
}
protected function getExpertEditorTemplate()
{
global $TARGET_NAMES;
$help_1 = "'" . _esc('Enter the path where all logs will be created and keept.') . "'";
$help_2 = "'" . _esc('Check this option if you want to rotate the logs when they reach a certain size.') . "'";
$help_3 = "'" . _esc('Enter the log maximum allowed size (in MiB).<br>They will be rotated as soon they reach this limit.') . "'";
$help_4 = "'" . _esc('This option allows you to isolate all the relevant log files per job instead to keep them globally in the same folder (`isolate` ie. they have their own folder) .');
$help_4 .= _esc(' The main advantage of this approach is that when you want to troubleshoot a job all you have to do is to read the job`s logs from its isolated folder instead of searching the whole log for a fragment corresponding to that job.');
$help_4 .= _esc(' Furthermore, you have Curl logs, you have debug trace logs and many, many other types. Their content will tell you smth. about that job and only that, which is cool.<br>For parallel backup jobs this is mandatory and default.<br>');
$help_4 .= _esc('This kind of isolated logs are written in GZip format so we don`t have to worry about rotating them because sometimes they are small but being so many they will start eating a lot of space on your disk.') . "'";
$log_dir = $this->settings['logdir'];
$logbranched = strToBool($this->settings['logbranched']);
require_once $this->getTemplatePath('logs-expert.php');
}
}
?>