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
 * @version : 1.0-2 $
 * @commit  : f8add2d67e5ecacdcf020e1de6236dda3573a7a6 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Tue Dec 13 06:40:49 2016 +0100 $
 * @file    : ActionHandler.php $
 * 
 * @id      : ActionHandler.php | Tue Dec 13 06:40:49 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

require_once CLASS_PATH . 'WPBackupHandler.php';
require_once LIB_PATH . 'LogFile.php';
define(__NAMESPACE__.'\\ADMIN_ASYNC_IFNAME', 'WP-Admin-Async');
class ActionHandler
{
private $functions;
public $method;
public $session;
public $storage;
public $settings;
public $logfile;
public $params;
function __construct($settings)
{
$this->functions = array();
$this->logfile = new LogFile(TRACE_ACTION_LOGFILE, $settings);
$this->settings = $settings;
$this->params = $this->settings;
$this->method = $_REQUEST;
$fixed_settings = getFixedSettings();
foreach ($this->method as $key => $value)
isset($fixed_settings[$key]) && $this->method[$key] = $fixed_settings[$key];
$this->session = null;
$this->storage = null;
if (! isset($_SERVER['REQUEST_URI'])) {
$_SERVER['REQUEST_URI'] = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
}
}
if (! isset($_SERVER['DOCUMENT_ROOT'])) {
$script_filename = str_replace(DIRECTORY_SEPARATOR, '/', realpath($_SERVER['SCRIPT_FILENAME']));
$doc_root = str_replace(realpath($_SERVER['SCRIPT_NAME']), '', $script_filename);
$_SERVER['DOCUMENT_ROOT'] = str_replace('/', DIRECTORY_SEPARATOR, $doc_root);
}
if (isset($this->method['action'])) {
$log_data = $this->method['action'] . PHP_EOL;
$klen = array_reduce(array_keys($this->method), function ($carry, $item) {
return max($carry, strlen($item));
}, '');
foreach ($this->method as $key => $value)
! (empty($key) || empty($value)) && 'action' != $key && $log_data .= sprintf("  %{$klen}s => %s", $key, is_array($value) ? print_r($value, true) : $value) . PHP_EOL;
} elseif (isset($this->method['tab']))
$log_data = 'tab load [' . $this->method['tab'] . ']' . (isset($this->method['gr']) ? '[' . $this->method['gr'] . ']' : '');
else
$log_data = print_r($this->method, true);
$this->logfile->writelnLog(sprintf("[%s] - %s", date(DATETIME_FORMAT), $log_data));
defined(__NAMESPACE__.'\\ADDONFUNC_PATH') && $this->_injectFunctions(ADDONFUNC_PATH . '*.php');
}
private function _injectFunctions($pattern)
{
foreach (glob($pattern) as $source_file) {
$name = preg_replace('/(.*)\.php/', '$1', basename($source_file));
$_this_ = &$this;
! isset($this->functions[$name]) && $this->functions[$name] = function () use ($source_file, &$_this_) {
$_ARGS_ = func_get_args(); 
$_RESULT_ = 0; 
include $source_file;
return $_RESULT_;
};
}
}
public function anonymousExec($func_name, $func_args = null)
{
if (! isset($this->functions[$func_name])) {
return false;
}
null === $func_args && $func_args = array();
is_array($func_args) || $func_args = array(
$func_args
);
return call_user_func_array($this->functions[$func_name], $func_args);
}
public function initStorage($service)
{
$session_class = null;
$storage_class = null;
switch ($service) {
case 'google':
$session_class = 'GoogleOAuth2Client';
$storage_class = 'GoogleCloudStorage';
break;
case 'dropbox':
$session_class = 'DropboxOAuth2Client';
$storage_class = 'DropboxCloudStorage';
break;
case 'webdav':
$this->storage = new WebDAVWebStorage($this->settings);
break;
}
if (! (empty($session_class) || empty($storage_class) || empty($service))) {
$service_auth_file = ROOT_OAUTH_FILE . $service . '.auth';
if (! _file_exists($service_auth_file))
return;
$authInfo = json_decode(file_get_contents($service_auth_file), true);
$session_class = __NAMESPACE__ . '\\' . $session_class;
$storage_class = __NAMESPACE__ . '\\' . $storage_class;
$this->session = new $session_class();
$this->session->curlInitFromArray($this->settings);
$this->session->setProxyURI(OAUTH_PROXY_URL, '');
$this->session->setTimeout($this->settings['request_timeout']);
$this->session->initFromArray($authInfo);
$this->storage = new $storage_class($this->session);
$this->storage->setTimeout($this->settings['request_timeout']);
}
}
function get_progress()
{
$_progress_manager = new ProgressManager(PROGRESS_LOGFILE);
$raw_data = $_progress_manager->getRawData();
$array = json_decode($raw_data, true);
null == $array && die($raw_data);
$array = array_filter($array, function (&$provider) {
$provider = array_filter($provider, function (&$file) {
return time() - $file['start'] > LONG_RUNNING_JOB_TIMEOUT ? false : $file;
});
return $provider;
});
header('Content-Type: application/json;charset=utf-8');
die(json_encode($array));
}
function cleanup_progress()
{
if (_file_exists(PROGRESS_LOGFILE))
@unlink(PROGRESS_LOGFILE);
}
function run_mysql_maint()
{
$wpbh = new WPBackupHandler($this->params, ADMIN_ASYNC_IFNAME);
$wpbh->runMySQLMaintenance();
}
function run_backup()
{
try {
$wpbh = new WPBackupHandler($this->params, ADMIN_ASYNC_IFNAME);
$wpbh->run();
} catch (MyException $e) {
echo $e->getMessage();
}
}
function chk_status()
{
$status = isJobRunning();
echo json_encode(array(
'status' => $status[0],
'message' => stripslashes($status[1]),
'last_job_id' => $status[2],
'nonce' => wp_create_nonce_wrapper('chk_status')
), JSON_FORCE_OBJECT);
}
function ftp_exec()
{
$is_sftp = isset($this->method['ssh']);
$ftp = getFtpObject($this->settings, $is_sftp);
$cmds = array();
foreach (explode(',', $this->method['ftp_cmd']) as $cmd_line) {
$pair = explode(' ', $cmd_line);
$cmds[0][] = $pair[0];
$cmds[1][] = count($pair) > 1 ? $pair[1] : null;
}
$result = $ftp->ftpExecRawCmds($cmds[0], $cmds[1]);
foreach ($cmds[0] as $cmd) {
echo getAnchor(getSpanE($cmd, 'cyan', 'bold'), lmgtfy(($is_sftp ? 's' : '') . 'ftp rfc ' . $cmd . ' command'));
echo getSpanE(_esc('returned the following result:'), 'yellow');
echo '<blockquote>';
if (is_array($result) && isset($result[$cmd]))
if (is_array($result[$cmd]))
echo implode('<br>', $result[$cmd]);
else
echo $result[$cmd];
echo '</blockquote>';
}
}
private function _log_read_validate()
{
isset($this->method['log_type']) && '' != ($log_type = $this->method['log_type']) || die("Internal error: no log type specified");
(! (($log = getLogfileByType($log_type)) && _file_exists($log))) && die(sprintf(_esc("Log file %s not found"), $log));
return $log;
}
function log_read()
{
$log = $this->_log_read_validate();
$sender = session_id();
$job_id = JOB_LOG_READ;
echo "<!--[job_id:$job_id]-->"; 
monitorFile($log, 1, function ($buffer) {
echo str_replace(array(
PHP_EOL,
'   ',
"\t",
' ',
str_repeat('-', 100)
), array(
'<br>',
TAB,
TAB,
'&nbsp;',
'<hr>'
), $buffer);
flush();
if (ob_get_level() > 0)
@ob_end_flush();
session_write_close(); 
}, function () use (&$log, &$sender) {
($abort_signal = false !== ($abort_signal_received = chkProcessSignal($log, $sender))) && ackProcessSignal($abort_signal_received[0], $abort_signal_received[1]);
return $abort_signal;
});
die(1);
}
function log_read_abort()
{
$log = $this->_log_read_validate();
addProcessSignal($log, session_id());
die(1);
}
function read_folder()
{
$this->anonymousExec('processFolderRequest');
}
function read_folder_info()
{
$this->anonymousExec('processFolderRequest');
}
function auto_save()
{
if (strToBool($this->settings['locked_settings']) && (! isset($this->method['locked_settings']) || strToBool($this->method['locked_settings']))) {
return;
}
$hijacked_action = isset($this->method['supersede_action']) && in_array($this->method['action'], explode(',', $this->method['supersede_action']));
if ($hijacked_action) {
$this->logfile->writelnLog(sprintf("%s - %s", date(DATETIME_FORMAT), _esc('[!] action doesn`t triggered ; it was hijacked')));
return;
}
$now = time();
if (isset($_SESSION['last_auto_save']) && $now == $_SESSION['last_auto_save'])
return;
add_session_var('last_auto_save', $now);
foreach ($this->method as $prop => $value) {
if ($prop == 'action' || $prop == 'nonce')
continue;
$this->settings[$prop] = urldecode($value); 
}
submit_options(null, $this->settings);
}
function php_setup($return_array = false)
{
require_once CLASS_PATH . 'CheckSetup.php';
$color_by_value = array(
true => 'green',
false => 'red'
);
$chksetup = new CheckSetup($this->settings, ! $return_array);
$array = $chksetup->getSetup();
$cpu_info = getCpuInfo();
$mem_info = getSystemMemoryInfo();
$version = APP_VERSION_ID;
if ($is_wp = is_wp()) {
$wp_version = get_bloginfo('version', 'display');
$wp_plugins = array();
foreach (wp_get_plugins() as $plugin_path => $plugin_props)
$wp_plugins[$plugin_path] = array(
'name' => $plugin_props['Name'],
'version' => $plugin_props['Version'],
'uri' => $plugin_props['PluginURI'],
'active' => is_plugin_active($plugin_path)
);
}
if (! $return_array) {
printf('<div style="text-align:center"><input type="button" class="button" value="%s" onclick="jsMyBackup.copy2clpb(this);" data-altcaption="%s"></div>', _esc('Copy to Clipboard'), _esc('<< Back'));
echo '<div class="check-setup-wrapper"><div>'; 
printf('<p style="color:blue"><b>' . WPMYBACKUP . '</b> (' . $version . ') %s ' . ($is_wp ? '(Wordpress ' . $wp_version . ')' : '') . ' %s:</p>', _esc('is running on'), _esc('the following system'));
echo '<table class="check-setup-system" style="border-spacing:0px;width:100%">';
echo '<tr><td style="font-weight:bold">' . _esc('OS') . '</td><td>:</td><td>' . sprintf('%s %s (%s)', php_uname('s'), php_uname('r'), php_uname('m')) . '</td></tr>';
echo '<tr><td style="font-weight:bold">PHP</td><td>:</td><td>' . PHP_VERSION . '</td></tr>';
echo '<tr><td style="font-weight:bold">' . _esc('Web server') . '</td><td>:</td><td>' . $_SERVER['SERVER_SOFTWARE'] . '</td></tr>';
if (! empty($cpu_info))
echo '<tr><td style="font-weight:bold">' . count($cpu_info) . 'x ' . _esc('CPU') . '</td><td>:</td><td style="white-space: nowrap;">' . $cpu_info[0]['model name'] . '</td></tr>';
if (! empty($mem_info)) {
$mem_info_keys = array(
'MemTotal' => _esc('Total RAM'),
'MemAvailable' => _esc('Free RAM'),
'SwapTotal' => _esc('Total swap'),
'SwapFree' => _esc('Free swap')
);
foreach ($mem_info_keys as $k => $v)
isset($mem_info[$k]) && printf('<tr><td style="font-weight:bold">%s</td><td>:</td><td>%s</td></tr>', $v, $k);
}
echo '<tr><td style="font-weight:bold">' . _esc('URL') . '</td><td>:</td><td>' . stripUrlParams($_SERVER['HTTP_REFERER'], array(
'tab'
)) . '</td></tr>';
echo '</table>';
echo '<p style="color:blue"><b>' . WPMYBACKUP . sprintf('</b> %s</p>', _esc('depends on the following PHP extensions or settings:<br>(make sure everything is green otherwise some feature might not work)'));
echo '<table class="check-setup-phpext" style="border-spacing:0px;width:100%">';
foreach ($array as $key => $value) {
if (in_array($key, array(
'open_basedir',
'disable_functions',
'safe_mode'
)))
$color = $color_by_value[! $value[CHKSETUP_ENABLED_KEY]];
else
$color = $color_by_value[$value[CHKSETUP_ENABLED_KEY]];
echo "<tr class='php_setup_tbl'><td><b>" . strtoupper($key) . "</b></td><td>:</td><td style='color:" . $color . "'>" . ($value[CHKSETUP_ENABLED_KEY] ? _esc("enabled") : _esc("disabled")) . '</td></tr>';
if (count($value) > 1) {
foreach ($value as $k => $v) {
if ($k == CHKSETUP_ENABLED_KEY || ! isset($v))
continue; 
if (CHKSETUP_ENABLED_HINT == $key) {
$key_str = '';
$x = "colspan='3' style='color:gray'";
} else {
$key_str = "<td> * $k</td><td>:</td>";
if (CHKSETUP_ENABLED_SETTINGS == $k || CHKSETUP_ENABLED_WRITABLE == $k) {
$val_style = "style='color:" . (1 == $v ? 'green' : 'red') . "'";
$val_str = 1 == $v ? (CHKSETUP_ENABLED_SETTINGS == $k ? _esc('ok') : _esc('passed')) : (CHKSETUP_ENABLED_SETTINGS == $k ? _esc('not working') : _esc('failed'));
} else {
$val_style = '';
$val_str = $v;
}
}
echo "<tr>" . $key_str . "<td " . $val_style . ">" . $val_str . "</td></tr>";
}
}
}
echo '</table>';
if ($is_wp) {
echo "<p style='font-weight:bold'>" . sprintf(_esc("Your WordPress (v%s) installation includes the following %d plugin(s) :"), $wp_version, count($wp_plugins)) . "</p>";
echo '<table class="check-setup-wpplugins"><tr class="php_setup_tbl" style="font-weight:bold"><td>' . _esc('Name') . '</td><td>' . _esc('Version') . '</td><td>' . _esc('Active') . '</td></tr>';
foreach ($wp_plugins as $plugin)
echo '<tr><td>' . getAnchor($plugin['name'], $plugin['uri']) . '</td><td>' . $plugin['version'] . '</td><td>' . boolToStr($plugin['active'], true) . '</td></tr>';
echo '</table>';
}
echo '</div><textarea rows="20" cols="80" readonly style="display:none;margin-top:10px"></textarea></div>'; 
} else {
$result = array(
'os' => PHP_OS,
'php_version' => PHP_VERSION,
'web_server' => $_SERVER['SERVER_SOFTWARE'],
'plugin_version' => $version
);
if ($is_wp) {
$result['wordpress'] = $wp_version;
$result['wp_plugins'] = $wp_plugins;
}
$result = array_merge($result, array(
'cpu_info' => $cpu_info,
'mem_info' => $mem_info,
'modules' => $array
));
return $result;
}
}
function read_alert()
{
$log = MESSAGES_LOGFILE;
$alert_message_obj = new MessageHandler($log);
$alerts = $alert_message_obj->getMessagesByKeys(array(
'status',
'type'
), array(
MESSAGE_ITEM_UNREAD,
array(
MESSAGE_TYPE_WARNING,
MESSAGE_TYPE_ERROR
)
));
if (count($alerts) > 0)
echo '<span onclick="location.href=\'' . replaceUrlParam(stripUrlParams($_SERVER['HTTP_REFERER'], 'installed'), 'tab', 'notification') . '\'" class="alert-span" id="notification_msg_span">' . sprintf(_esc('You have %d unread alert(s)'), count($alerts)) . '</span>';
}
function print_debug_sample()
{
$samples_path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'custom' . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR;
$buffer = '';
switch ($this->method['type']) {
case 'curl':
$buffer = file_get_contents($samples_path . 'support-expert-curldebug-sample.txt');
break;
case 'debug':
$buffer = file_get_contents($samples_path . 'support-expert-debugon-sample.txt');
break;
case 'stats':
$buffer = file_get_contents($samples_path . 'support-expert-statsdebug-sample.txt');
break;
}
echo htmlentities($buffer);
}
function export_settings()
{
global $factory_options;
$js = '<script>setTimeout(function(){history.back();},3000);</script>';
$err = false;
! isset($this->method['format']) || empty($this->method['format']) && $err = _esc('Unknown export format');
$result = array();
$array = getSettings();
foreach ($factory_options as $group => $options) {
$result[$group] = array();
foreach ($options as $option => $default_value)
$result[$group][$option] = isset($array[$option]) ? $array[$option] : $default_value[0];
}
empty($result) && $err = _esc('The settings are empty. That`s rather odd.');
if (false !== $err)
return printf($err . $js);
$file = addTrailingSlash($this->settings['wrkdir']) . WPMYBACKUP_LOGS . '-settings.' . $this->method['format'];
_is_file($file) && unlink($file);
switch ($this->method['format']) {
case 'xml':
$xml = new Array2XML();
$data = $xml->createXML(WPMYBACKUP_LOGS . '_options', $result)->saveXML();
break;
case 'ini':
$data = '';
foreach ($result as $group => $options) {
$data .= sprintf('[%s]', $group) . PHP_EOL;
foreach ($options as $key => $value) {
isset($factory_options[$group][$key][3]) && $data .= PHP_EOL . '# ' . $factory_options[$group][$key][3] . PHP_EOL;
if (is_array($value))
$data .= sprintf('%s = %s', $key, print_r($value, 1)) . PHP_EOL;
else
$data .= sprintf('%s = %s', $key, $value) . PHP_EOL;
}
$data .= PHP_EOL;
}
break;
default:
$greater_530 = version_compare(PHP_VERSION, '5.3.0-dev', '>');
$data = json_encode($result, $greater_530 ? JSON_PRETTY_PRINT : 0);
$greater_530 || $data = str_replace(array(
',"',
'{'
), array(
',' . PHP_EOL . '"',
'{' . PHP_EOL
), $data);
break;
}
file_put_contents($file, $data) && _file_exists($file) && redirectFileDownload($file, 'application/' . $this->method['format']);
@unlink($file);
}
function import_settings()
{
}
function submit_options()
{
if (strToBool($this->settings['locked_settings']) && (! isset($_POST['locked_settings']) || strToBool($_POST['locked_settings']))) {
return;
}
global $settings;
submit_options($this->logfile); 
$settings = loadSettings(); 
}
function dwl_sql_script()
{
$mysqlbkp = new MySQLBackupHandler($this->settings);
$mysqlbkp->downloadSqlScript($this->settings['wrkdir'], $this->method['tables'], $this->method['name'], $this->method['type'], $this->method['level']);
}
function dwl_file()
{
require_once FUNCTIONS_PATH . 'download.php';
downloadFile(urldecode($this->method['location']), $this->method['service'], $this->settings);
}
function test_dwl()
{
$_this_ = &$this;
include_once ADDONFUNC_PATH . 'test_dwl.php';
}
function mybackup_core_backup()
{
submit_options();
}
function del_file()
{
switch ($this->method['service']) {
case 'ssh':
case 'ftp':
$ftp = getFtpObject($this->settings, 'ssh' == $this->method['service']);
$dir_sep = 'u' == $this->settings['ftpdirsep'] ? '/' : '\\';
$file_parts = array_map(function ($e) use (&$dir_sep) {
return strpos($e, ' ') > 0 ? ('/' == $dir_sep ? str_replace(' ', '\\ ', $e) : '"' . $e . '"') : $e;
}, explode($dir_sep, $this->method['location']));
$output = $ftp->deleteFile(implode($dir_sep, $file_parts), 'del_dir' == $this->method['action'], true);
echo $output[0];
break;
case 'disk':
if (@_call_user_func('del_file' == $this->method['action'] ? 'unlink' : 'rmdir', $this->method['location']))
echo '1';
else {
$error = error_get_last();
echo $error['message'];
}
break;
case 'google':
case 'dropbox':
case 'webdav':
$this->initStorage($this->method['service']);
$result = $this->storage->deleteFile('google' == $this->method['service'] ? _basename($this->method['location']) : $this->method['location']);
if (is_array($result)) {
if (isset($result['message']) && isset($result['code']))
echo $result['message'] . ' (' . $result['code'] . ')';
elseif (isset($result['id']) || isset($result['is_deleted']))
echo 1;
else
dumpVar($result, true, true); 
} else
echo $result;
break;
default:
$this->rst_file();
break;
}
}
function rst_file()
{
printf(_esc('Method "%s" not yet implemented for service <b>%s</b>.<br>File location: %s'), $this->method['action'], $this->method['service'], $this->method['location']);
}
function ren_file()
{
$old_name = $this->method['location'];
$new_name = $this->method['new_name'];
switch ($this->method['service']) {
case 'ftp':
$ftp = getFtpObject($this->settings);
$dir_sep = 'u' == $this->settings['ftpdirsep'] ? '/' : '\\';
$ftp->ftpExecRawCmds(array(
'RNFR',
'RNTO'
), array(
$old_name,
_dirname($old_name, $dir_sep) . $dir_sep . $new_name
));
echo 1; 
break;
case 'ssh':
$ftp = getFtpObject($this->settings, true);
$dir_sep = 'u' == $this->settings['ftpdirsep'] ? '/' : '\\';
$ftp->ftpExecRawCmds('rename', $old_name . ' ' . _dirname($old_name, $dir_sep) . $dir_sep . $new_name);
echo 1; 
break;
case 'disk':
if (! @move_file($old_name, addTrailingSlash(dirname($old_name)) . $new_name)) {
$err = error_get_last();
throw new MyException($err['message'], isset($err['code']) ? $err['code'] : - 2);
} else
echo 1;
break;
case 'google':
case 'dropbox':
case 'webdav':
$this->initStorage($this->method['service']);
$result = $this->storage->renameFile('google' == $this->method['service'] ? _basename($this->method['location']) : $this->method['location'], $this->method['new_name']);
if (is_array($result)) {
if (isset($result['message']) && isset($result['code']))
echo $result['message'] . ' (' . $result['code'] . ')';
else
echo 1;
} else
echo $result;
break;
default:
$this->rst_file();
break;
}
}
function del_dir()
{
$this->del_file();
}
function mk_dir()
{
$new_name = _basename($this->method['location']);
$path_id = _dirname($this->method['location']);
switch ($this->method['service']) {
case 'ssh':
case 'ftp':
$ftp = getFtpObject($this->settings, 'ssh' == $this->method['service']);
$ftp->ftpExecRawCmds(array(
'ssh' == $this->method['service'] ? 'mkdir' : 'MKD'
), array(
$this->method['location']
));
echo 1; 
break;
case 'disk':
if (! @mkdir($this->method['location'], 0770, true)) {
$err = error_get_last();
throw new MyException($err['message'], isset($err['code']) ? $err['code'] : - 2);
} else
echo 1;
break;
case 'google':
case 'dropbox':
case 'webdav':
$this->initStorage($this->method['service']);
$result = $this->storage->createFolder('google' == $this->method['service'] ? _basename($path_id) : $path_id, $new_name);
if (is_array($result)) {
if (isset($result['message']) && isset($result['code']))
echo $result['message'] . ' (' . $result['code'] . ')';
else
echo 1;
} else
echo $result;
break;
default:
$this->rst_file();
break;
}
}
function clear_log($log_type = null)
{
global $java_scripts;
empty($log_type) && isset($_POST['log_type']) && $log_type = $_POST['log_type'];
if (($log = getLogfileByType($log_type)) && _file_exists($log)) {
$java_scripts[] = sprintf('jsMyBackup.popupWindow("%s","%s");', _esc('Notice'), sprintf(_esc('Log file <i>%s</i> %s'), normalize_path($log), @unlink($log) ? _esc('deleted successfully') : _esc('delete error')));
}
}
function reset_defaults($forcebly = false)
{
global $java_scripts;
$bak_str = '';
if (! is_wp()) {
$backup_filename = LOCAL_OPTION_DB_PATH . '.' . time();
(! $forcebly && $backup_settings = loadSettings()) || $backup_settings = array();
file_put_contents($backup_filename, json_encode(array(
WPMYBACKUP_OPTION_NAME => $backup_settings
)));
$bak_str = sprintf(_esc('I made a backup copy before reseting them (just in case). You may find it at:%s'), '<blockquote><a href=\'\' class=\'help\'>ROOT</a>/' . str_replace(ROOT_PATH, '', $backup_filename) . '</blockquote>');
}
$default_options = getFixedSettings() + getFactorySettings();
delete_option_wrapper(WPMYBACKUP_OPTION_NAME);
submit_options(null, $default_options);
defined(__NAMESPACE__.'\\TARGETLIST_DB_PATH') && _file_exists(TARGETLIST_DB_PATH) && @unlink(TARGETLIST_DB_PATH);
$java_scripts[] = sprintf('jsMyBackup.popupWindow("%s","%s",null,null,"#ffb600");', _esc('Confirmation'), sprintf(_esc('The application settings were reseted to their factory defaults.<br>%sPlease setup again the application in order to fit your needs.'), $bak_str));
unset($_SESSION['edit_step']);
}
function del_oauth()
{
$service_name = $this->method['service'];
$service_auth_file = ROOT_OAUTH_FILE . $service_name . '.auth';
if (true === @unlink($service_auth_file)) {
printf("%s is no longer linked with %s.<br>You can, however, authorize the %s access anytime", WPMYBACKUP, $service_name, $service_name);
} else {
$error = error_get_last();
echo $error['message'];
}
}
function abort_job()
{
if (! isset($this->method['id'])) {
_pesc('Cannot abort the job due to no job `id` found.');
return;
}
try {
$listen_processes = array(
PROCESS_BACKUP,
PROCESS_GUI_BACKUP,
PROCESS_TRANSFER,
PROCESS_MYSQL_MAINT
);
defined(__NAMESPACE__.'\\PROCESS_CUI_BACKUP') && $listen_processes[] = PROCESS_CUI_BACKUP;
defined(__NAMESPACE__.'\\PROCESS_BENCHMARK') && $listen_processes[] = PROCESS_BENCHMARK;
foreach ($listen_processes as $process_name) {
addProcessSignal($process_name, $this->method['id']);
}
$stat_mngr = getJobsStatManager($this->settings);
$stat_mngr->onJobEnds($this->method['id'], array(
'job_status' => empty($this->method['job_status']) ? 'JOB_STATUS_SUSPENDED' : $this->method['job_status']
));
echo 1;
} catch (MyException $e) {
echo $e->getMessage();
}
}
function edit_step()
{
$_SESSION['edit_step'] = $_POST;
}
function del_target()
{
$this->edit_step();
}
private function _chg_target_fields($fields, $callback)
{
try {
! is_array($fields) && $fields = array(
$fields
);
$ok = true;
$ok1 = true;
foreach ($fields as $name) {
$ok = $ok && isset($_POST[$name]);
$ok1 = $ok && $ok1 && ! empty($_POST[$name]);
if (! ($ok && $ok1))
break;
}
if (! (isset($_POST['id']) && $ok))
throw new MyException(_esc('Invalid id sent. Cannot change the item'));
if (isset($_POST['id']) && empty($_POST['id']) || $ok && ! $ok1)
throw new MyException(_esc('Invalid (empty) data sent. Cannot change the item'));
$target_list = new TargetCollection(TARGETLIST_DB_PATH);
$target_item = $target_list->getTargetItem($_POST['id']);
if (_is_callable($callback)) {
$target_item = _call_user_func($callback, $target_item);
$target_list->saveToFile();
echo 1;
} else
throw new MyException(sprintf(_esc('No save callback function defined. This should never happen.<br>Please %sreport this issue</a>.'), '<a href="' . getReportIssueURL() . '">'));
} catch (\Exception $e) {
echo $e->getMessage();
}
}
function enable_target()
{
$callback = function ($target_item) {
global $TARGET_NAMES;
$target_item->enabled = $_POST['enabled'];
$target_item->targetSettings[$TARGET_NAMES[$target_item->type] . '_enabled'] = $_POST['enabled'];
return $target_item;
};
$this->_chg_target_fields('enabled', $callback);
}
function save_target_desc()
{
$callback = function ($target_item) {
$target_item->description = $_POST['desc'];
return $target_item;
};
$this->_chg_target_fields('desc', $callback);
}
}
?>