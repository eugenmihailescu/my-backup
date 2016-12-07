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
 * @file    : WPBackupHandler.php $
 * 
 * @id      : WPBackupHandler.php | Wed Dec 7 18:54:23 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

require_once CLASS_PATH . 'AbstractJob.php';
include_once EDITOR_PATH . 'file-functions.php';
if (defined(__NAMESPACE__.'\\GOOGLE_TARGET')) {
}
if (defined(__NAMESPACE__.'\\DROPBOX_TARGET')) {
}
defined(__NAMESPACE__.'\\WEBDAV_TARGET') && require_once STORAGE_PATH . 'WebDAVWebStorage.php';
defined(__NAMESPACE__.'\\MYSQL_SOURCE') && require_once CLASS_PATH . 'MySQLBackupHandler.php';
ini_set("error_log", ERROR_LOG);
class WPBackupHandler extends AbstractJob
{
private $uploaded;
private $uncompressed;
private $compressed;
private $compressed_files;
function __construct($opts = null, $sender = '')
{
parent::__construct($opts, $sender);
$this->compressed_files = array();
date_default_timezone_set(wp_get_timezone_string());
if (null != getArg('help', $opts)) {
printHelp();
exit();
}
if (! is_session_started(false) && ! headers_sent())
session_start();
session_write_close();
}
private function _getFileParts($api, $target, $oper_send, $filename)
{
global $TARGET_NAMES;
$target_name = $TARGET_NAMES[$target];
$err_params = $this->getOperErrParams($filename, $oper_send);
$parts = array();
try {
$filesize = filesize($filename);
$quota = $api->getQuota();
$free_space = $api->getFreeSpace();
$upload_limit = $api->getUploadLimit();
if ($filesize > $free_space && $quota > 0) {
$this->outputError(sprintf(_esc("<red>[!] file %s cannot be uploaded to %s due to insufficient disk space (free: %s, needs: %s)</red>"), $filename, $target_name, getHumanReadableSize($free_space), getHumanReadableSize($filesize)), false, $err_params);
}             
else 
if ($filesize > $upload_limit) {
$this->logOutputTimestamp(sprintf(_esc("<yellow>file %s (%s) exceeds %s limit of %s</yellow>"), basename($filename), getHumanReadableSize($filesize), $target_name, getHumanReadableSize($upload_limit)), BULLET);
$parts = splitFile($filename, $upload_limit);
$this->outputError(sprintf(_esc("file %s was splitted into %d parts"), basename($filename), count($parts)), false, $err_params);
} else
$parts = array(
$filename
);
} catch (MyException $e) {
$err_params = $this->getOperErrParams($filename, $oper_send);
$this->outputError(formatErrMsg($e, $target_name), false, $err_params);
}
return $parts;
}
private function _upload2Storage($filename, $target, $uncompressed_size = 0, $add_filename = false)
{
list ($target_name, $oper_send, $oper_sent) = $this->getTargetOperConsts($target);
$metadata = array();
if (in_array($target, array(
FTP_TARGET,
SSH_TARGET
)))
$func_name = 'initRemoteStorage';
else
$func_name = 'initCloudStorage';
try {
$api = $this->$func_name($target, $filename);
if (! is_object($api))
return $api;
$parts = $this->_getFileParts($api, $target, $oper_send, $filename);
$path = $this->getTarget($target)->getPath();
foreach ($parts as $part) {
if (! _file_exists($part))
continue;
$this->startTransfer($oper_send, $part, $path, null, $api->isSecure());
$result = $api->uploadFile($part, $add_filename ? addTrailingSlash($path, '/') . basename($part) : $path);
if (! $api->curlAborted()) {
$this->parseResponse($result);
$metadata[] = $result;
$this->stopTransfer($oper_sent, $part, filesize($part), $uncompressed_size);
if (count($parts) > 1)
unlink($part);
}
}
} catch (MyException $e) {
$err_params = $this->getOperErrParams($filename, $oper_send);
$this->outputError(formatErrMsg($e), false, $err_params);
$metadata = false;
$this->getProgressManager()->setProgress($target, isset($part) ? $part : null, 0, 0, PT_UPLOAD);
}
return $metadata;
}
private function _move2Disk($arc = null, $target = DISK_TARGET, $uncompressed_size = 0)
{
if ($this->chkProcessSignal())
return $this->processAbortSignal($target, OPER_SEND_DISK);
if ($this->getTarget($target)->isEnabled() && null !== $this->getTarget($target)->getPath()) {
if (_dir_in_allowed_path($this->getTarget($target)->getPath())) {
if (! empty($arc)) {
$this->startTransfer(OPER_SEND_DISK, $arc, $this->getTarget($target)
->getPath());
$fsize = filesize($arc);
$err_params = $this->getOperErrParams($arc, OPER_SEND_DISK);
if (! _file_exists($this->getTarget($target)->getPath())) {
if (! mkdir($this->getTarget($target)->getPath(), 0770, true))
$this->outputError(null, false, $err_params);
}
try {
$free = _disk_free_space(dirname($arc));
if ($fsize <= $free) {
$dest = addTrailingSlash($this->getTarget($target)->getPath()) . basename($arc);
if (dirname($arc) == dirname($dest)) {
$this->outputError(sprintf(_esc('<red>[!] file %s cannot be moved; working dir = destination dir</red>'), basename($arc)));
$success = false;
} else {
$success = move_file($arc, $dest);
$this->onBytesSent($target, $arc, $fsize, $fsize);
}
$success && $this->stopTransfer(OPER_SENT_DISK, $dest, $fsize, $uncompressed_size);
return $success;
} else {
$this->outputError(sprintf(_esc("<red>[!] file %s cannot be moved to disk due to insufficient disk space (free: %s, required: %s)</red>"), $arc, getHumanReadableSize($free), getHumanReadableSize($fsize)), false, $err_params);
}
} catch (MyException $e) {
$this->outputError(formatErrMsg($e), false, $err_params);
return false;
}
}
} else {
$this->outputError('<red>[!] ' . sprintf(_esc('The target path %s is not within allowed path (see %s).'), $this->getTarget($target)
->getPath(), getAnchor('open_basedir', PHP_MANUAL_URL . 'ini.core.php#ini.open-basedir')) . '</red>');
return false;
}
}
return true;
}
private function _createMySqlBackup()
{
if ($this->chkProcessSignal())
return $this->processAbortSignal(MYSQL_SOURCE);
$target = $this->getTarget(MYSQL_SOURCE);
if ($target->isEnabled()) {
$params = $target->getParams();
if (! empty($params['tables'])) {
if (is_wp()) {
$obj = new MySQLWrapper($this->getOptions());
if ($link = $obj->connect()) {
$obj->query(sprintf("DELETE FROM %soptions where option_name like '_transient_%%';", wp_get_db_prefix()));
}
$obj->disconnect();
$obj = null;
}
$db_prefix = count(wp_get_user_blogs_prefixes()) < 2 ? wp_get_db_prefix() : '';
$pattern = $db_prefix . preg_replace('/([,|])/', '\1' . $db_prefix, $params['tables']);
$this->logSeparator();
$this->logOutputTimestamp('<b>' . sprintf(_esc('Backing up the MySQL database %s tables'), getSpan($target->getOption('mysql_db'), 'cyan')) . '</b>');
$this->logOutputTimestamp(sprintf(_esc('tables pattern: %s'), $params['tables']), BULLET);
$this->addtFileCount(1);
$mysqlbkp = new MySQLBackupHandler($target->getOptions());
$arcs = $mysqlbkp->compressMySQLScript($this->getBackupName() . '.' . $params['mysql_format'], $this->getCompressionMethod(), $this->getCompressionLevel(), $this->getVolumeSize(), $params['tables'], $this->getMySqlDump(), $this->getTool(), $this->getBZipVersion(), $this->getCygwin(), $this->getCPUSleep(), array(
array(
$this,
'onNewArc'
),
array(
$this,
'startCompress'
),
array(
$this,
'onMySqlMaint'
)
,null,
array(
$this,
'chkProcessSignal'
),
array(
$this,
'onProgress'
)
));
$this->addVolCount(count($arcs));
$this->logOutput(sprintf("<br><b>%s</b> : %s", _esc('SUBTOTAL'), sprintf('%s files added (%s%s) from %s', getSpan($arcs[0]['count'], '#fff', 'bold'), getHumanReadableSize($arcs[0]['bytes']), count($arcs) > 1 ? sprintf(', %d vols', count($arcs)) : '', 'MySQL::' . $params['mysql_db'] . '.' . $db_prefix)));
return $arcs;
}
}
return true;
}
private function _upload2Email($filename, $target, $uncompressed_size = 0, $add_filename = false)
{
if ($this->chkProcessSignal())
return $this->processAbortSignal(MAIL_TARGET, OPER_SEND_EMAIL);
if (null == $target) {
return false;
}
$to = $this->getTarget($target)->getOption('backup2mail_address');
empty($to) && $to = $this->getNotificationEmail();
$fsize = filesize($filename);
$err_params = $this->getOperErrParams($filename, OPER_SEND_EMAIL);
if (! empty($to) && preg_match("/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6}$/i", $to)) {
$err_msg = null;
$from = sprintf('From: %s <%s@%s>', WPMYBACKUP, 'backup2mail', $_SERVER['SERVER_NAME']);
$subject = sprintf(_esc('%s backup of %s'), WPMYBACKUP, $this->getBackupName());
$body = _esc('Backup enclosed as attachment');
$key = uniqid('php');
$attachments[$key] = array(
'name' => basename($filename),
'type' => null,
'tmp_name' => $filename,
'error' => 0,
'size' => $fsize
);
$this->startTransfer(OPER_SEND_EMAIL, $filename, $to);
try {
$result = false;
$upload_limit = getUploadLimit();
$memory_limit = getMemoryLimit();
$memory_usage = memory_get_usage(true);
$mail_size = strlen($from . $to . $subject . $body) + filesize($filename);
$mail_est_size = $mail_size * 1.33; 
if ($memory_limit - $memory_usage < $mail_est_size) 
$this->outputError(sprintf('<yellow>' . _esc('Warning : mail size : ~ %s (*1.33 => %s), PHP memory_limit : %s, mem usage: %s') . '</yellow>', getHumanReadableSize($mail_size), getHumanReadableSize($mail_est_size), getHumanReadableSize($memory_limit), getHumanReadableSize($memory_usage)), false, $err_params);
elseif ($upload_limit > 0 && $upload_limit < $mail_est_size) 
$this->outputError(sprintf('<yellow>' . _esc('Warning : mail size : ~ %s (*1.33 => %s), PHP upload_maxfilesize : %s') . '</yellow>', getHumanReadableSize($mail_size), getHumanReadableSize($mail_est_size), getHumanReadableSize($upload_limit)), false, $err_params);
else {
$native_backend = ! strToBool($this->getTarget($target)->getOption('backup2mail_smtp'));
$backend = $this->getTarget($target)->getOption('backup2mail_backend');
$smtp_debug = defined(__NAMESPACE__.'\\SMTP_DEBUG') && SMTP_DEBUG;
$backend_params = array(
'debug' => $smtp_debug,
'auth' => strToBool($this->getTarget($target)->getOption('backup2mail_auth'))
);
if ($backend_params['auth'])
foreach (array(
'host' => 'backup2mail_host',
'port' => 'backup2mail_port',
'username' => 'backup2mail_user',
'password' => 'backup2mail_pwd',
'timeout' => 'request_timeout'
) as $key => $value)
$backend_params[$key] = $this->getTarget($target)->getOption($value);
$result = sendMail($from, $to, $subject, $body, $attachments, null, 3, $native_backend ? null : $backend, $backend_params, $smtp_debug);
}
} catch (MyException $err) {
$err_msg = $err->getMessage();
}
if ($result) {
$this->onBytesSent(MAIL_TARGET, $filename, $fsize, $fsize);
$this->stopTransfer(OPER_SENT_EMAIL, $filename, $fsize, $uncompressed_size);
} else
$this->outputError('<red>' . _esc('Mail send failed') . (null == $err_msg ? '' : (': ' . $err_msg)) . '.</red>', true, $err_params);
return $result;
} else {
$this->outputError(sprintf('<red>' . _esc('Cannot send backup via e-mail due to invalid e-mail address') . '</red> [%s]', empty($to) ? _esc('empty') : $to), false, $err_params);
}
}
private function _getMySqlMaintenanceStatus($array)
{
$msg = '';
if (! empty($array)) {
foreach ($array as $table_name => $cmds) {
$err_msg = array();
foreach ($cmds as $cmd => $info)
if ('error' == $info[0] || 'warning' == $info[0])
$err_msg[] = sprintf(_esc('%s on %s : %s'), $info[0], $cmd, $info[1]);
if (count($err_msg) > 0)
$msg .= sprintf(_esc("%s found the following %d problems while checking the table %s:%s"), WPMYBACKUP, count($err_msg), getSpan($table_name, 'cyan'), '<ul><li>' . implode('</li><li>', $err_msg) . '</li></ul>');
}
}
return $msg;
}
private function _getTargetMaxAge($targets)
{
$max = 0;
foreach ($targets as $target)
if (- 100 != ($c = $this->getTargetConstant($target)))
$max = max($max, $this->getTarget($c)->getAge());
return $max;
}
private function _cleanupOrphans()
{
$ext = $this->getCompressionName();
$files = getFileListByExt($this->getWorkDir(), $ext);
$dates = getDatesByAge(getFilesTime($files, $this->getBackupName() . '-'), $this->_getTargetMaxAge(array(
'DROPBOX_TARGET',
'GOOGLE_TARGET',
'WEBDAV_TARGET'
)), OLDEST);
$orphans = array();
foreach ($dates as $d)
foreach ($files as $file)
if (false !== strpos($this->getBackupName() . '-' . date('Ymd-His', $d), $file))
$orphans[] = $file;
if (count($orphans) > 0)
$this->logOutputTimestamp(sprintf(_esc("Cleaning up %d orphans archieves"), count($orphans)));
foreach ($orphans as $o) {
if (! $this->chkProcessSignal())
@unlink($o);
else {
$this->processAbortSignal(TMPFILE_SOURCE, OPER_CLEANUP_ORPHAN);
break;
}
}
}
private function _cleanUpOldArchives($target)
{
$obj = &$this;
$get_search_filter = function ($filter) use(&$target, &$obj)
{
switch ($target) {
default:
$result = $filter;
break;
case $obj->getTargetConstant('GOOGLE_TARGET'):
$result = 'title contains \'' . $filter . '\'';
break;
}
return $result;
};
$get_metadata = function ($metadata) use(&$target, &$obj)
{
switch ($target) {
case $obj->getTargetConstant('GOOGLE_TARGET'):
$result = $metadata['items'];
break;
default:
$result = $metadata;
break;
}
return $result;
};
$get_fileid = function ($file_item, $file_index, $file_path) use(&$target, &$obj)
{
switch ($target) {
case $obj->getTargetConstant('DROPBOX_TARGET'):
$result = $file_item['path'];
break;
case $obj->getTargetConstant('GOOGLE_TARGET'):
$result = $file_item['id'];
break;
case $obj->getTargetConstant('WEBDAV_TARGET'):
$result = addTrailingSlash($file_path, '/') . _basename($file_item['name']);
break;
case FTP_TARGET:
case SSH_TARGET:
$result = addTrailingSlash($file_path, '/') . $file_index;
break;
case DISK_TARGET:
$result = $file_item;
break;
}
return $result;
};
$get_isdir = function ($file_item) use(&$target, &$obj)
{
switch ($target) {
case $obj->getTargetConstant('DROPBOX_TARGET'):
case $obj->getTargetConstant('WEBDAV_TARGET'):
$result = $file_item['is_dir'];
break;
case $obj->getTargetConstant('GOOGLE_TARGET'):
$result = strpos($file_item["mimeType"], 'application/vnd.google-apps.folder') !== false;
break;
case FTP_TARGET:
case SSH_TARGET:
$result = $file_item[6];
break;
case DISK_TARGET:
$result = _is_dir($file_item);
break;
}
return $result;
};
$get_filename = function ($file_item, $file_id) use(&$target, &$obj)
{
switch ($target) {
case $obj->getTargetConstant('DROPBOX_TARGET'):
$result = $file_item['path'];
break;
case $obj->getTargetConstant('GOOGLE_TARGET'):
$result = $file_item['title'];
break;
case $obj->getTargetConstant('WEBDAV_TARGET'):
$result = $file_item['name'];
break;
case FTP_TARGET:
case SSH_TARGET:
$result = $file_id;
break;
case DISK_TARGET:
$result = $file_item;
break;
}
return $result;
};
$getfilesize = function ($file_item) use(&$target, &$obj)
{
switch ($target) {
case $obj->getTargetConstant('DROPBOX_TARGET'):
case $obj->getTargetConstant('WEBDAV_TARGET'):
$result = $file_item['size'];
break;
case $obj->getTargetConstant('GOOGLE_TARGET'):
$result = $file_item['fileSize'];
break;
case FTP_TARGET:
case SSH_TARGET:
$result = $file_item[4];
break;
case DISK_TARGET:
$result = filesize($file_item);
break;
}
return $result;
};
global $TARGET_NAMES, $COMPRESSION_NAMES;
if ($this->chkProcessSignal())
return $this->processAbortSignal($target, OPER_CLEANUP_OLDIES);
if (! $this->getTarget($target)->isEnabled())
return false;
$days = $this->getTarget($target)->getAge();
if ($days < 0)
return false;
$pattern = $this->getNamePattern();
$targetPath = $this->getTarget($target)->getPath();
$api = null;
$session = $this->getOptions();
$metadata = false;
$storage_class = null;
switch ($target) {
case $this->getTargetConstant('DROPBOX_TARGET'):
$session = new DropboxOAuth2Client();
$storage_class = 'DropboxCloudStorage';
break;
case $this->getTargetConstant('GOOGLE_TARGET'):
$session = new GoogleOAuth2Client();
$storage_class = 'GoogleCloudStorage';
break;
case $this->getTargetConstant('WEBDAV_TARGET'):
$storage_class = 'WebDAVWebStorage';
break;
case FTP_TARGET:
case SSH_TARGET:
$api = getFtpObject($this->getOptions(), SSH_TARGET == $target);
break;
case DISK_TARGET:
$api = false;
$metadata = array();
if (_dir_in_allowed_path($targetPath))
$metadata = glob(addTrailingSlash($targetPath) . $pattern . "*." . $COMPRESSION_NAMES[$this->getCompressionMethod()] . "*");
$delete_func = 'unlink';
break;
}
$err_params = $this->getOperErrParams(null, OPER_CLEANUP_OLDIES, false, 'METRIC_ACTION_CLEANUP');
if (null != $session && method_exists($session, 'setProxyURI')) {
if (array_key_exists('METRIC_FILENAME', $err_params))
unset($err_params['METRIC_FILENAME']);
if (! $this->_chkOAuthSession($TARGET_NAMES[$target], $session, $err_params, null, 0))
return false;
$session->setProxyURI(OAUTH_PROXY_URL, '');
$session->setTimeout($this->_getRequestTimeout());
}
$deleted_files = 0;
$this->logOutputTimestamp(sprintf(_esc("Cleaning-up %s files older than %d days"), ucwords($TARGET_NAMES[$target]), $days));
try {
if (null === $api && null != $storage_class) {
$storage_class = __NAMESPACE__ . '\\' . $storage_class;
$api = new $storage_class($session);
}
$is_secure = false;
if (is_object($api)) {
$is_secure = $api->isSecure();
$metadata = $api->searchFileNames($targetPath, $this->getSearchFilter($target, $pattern));
$delete_func = array(
$api,
'deleteFile'
);
}
if (! is_array($metadata))
throw new MyException(sprintf(_esc('Clean-up old archives not implemented for %s'), $TARGET_NAMES[$target]));
$files = array();
$target_metadata = $get_metadata($metadata);
if (is_array($target_metadata))
foreach ($target_metadata as $file_id => $file) {
$filename = $get_filename($file, $file_id);
if (! (empty($filename) || $get_isdir($file)))
$files[] = $filename;
}
$dates = getDatesByAge(getFilesTime($files, $pattern), $days, OLDEST);
asort($dates);
$i = 1;
$j = count($dates);
foreach ($dates as $d) {
$file_pattern = $pattern . date('Ymd-His', $d);
foreach ($target_metadata as $file_id => $file) {
$filename = _basename($get_filename($file, $file_id));
if (! $this->chkProcessSignal()) {
if (0 !== strpos($filename, $file_pattern))
continue;
$filesize = $getfilesize($file);
$err_params['METRIC_FILENAME'] = $file;
$err_params['METRIC_SIZE'] = $filesize;
$this->startTransfer(OPER_CLEANUP_OLDIES, $filename, $targetPath, $TARGET_NAMES[$target], $is_secure);
$del_result = @_call_user_func($delete_func, $get_fileid($file, $file_id, $targetPath));
if (is_array($del_result))
$del_result = isset($del_result[0]) ? array_sum($del_result) : (isset($del_result['message']) ? 0 : 1);
$deleted_files += $del_result;
$this->stopTransfer(OPER_CLEANUP_OLDIES, $filename, $filesize, 0, 'METRIC_ACTION_CLEANUP');
} else {
$this->processAbortSignal($target, OPER_CLEANUP_OLDIES);
break;
}
}
$this->onProgress($target, $filename, $i ++, $j, PT_DELETE);
}
} catch (MyException $e) {
$this->outputError(formatErrMsg($e), false, $err_params);
}
return $deleted_files;
}
private function _uploadOrphans()
{
if (! $this->getEnabledTargets(array(
'DROPBOX_TARGET',
'GOOGLE_TARGET',
'WEBDAV_TARGET'
)))
return false;
$ext = $this->getCompressionName();
$files = getFileListByExt($this->getWorkDir(), $ext);
$dates = getDatesByAge(getFilesTime($files, $this->getBackupName() . '-'), $this->_getTargetMaxAge(array(
'DROPBOX_TARGET',
'GOOGLE_TARGET',
'WEBDAV_TARGET'
)), NEWEST);
$orphans = array();
foreach ($dates as $d)
foreach ($files as $file)
if (preg_match('@.*' . $this->getBackupName() . '-' . date('Ymd-His', $d) . '.*@', $file))
$orphans[] = $file;
if (count($orphans) > 0)
$this->logOutputTimestamp(sprintf(_esc("Uploading %d orphans archieves"), count($orphans)));
$i = 1;
$j = count($orphans);
foreach ($orphans as $o) {
$done = false;
if (defined(__NAMESPACE__.'\\DROPBOX_TARGET') && $this->getTarget(DROPBOX_TARGET)->isEnabled())
$done = $done && null !== $this->_upload2Storage($o, DROPBOX_TARGET);
if (defined(__NAMESPACE__.'\\GOOGLE_TARGET') && $this->getTarget(GOOGLE_TARGET)->isEnabled())
$done = $done && null !== $this->_upload2Storage($o, GOOGLE_TARGET);
if (defined(__NAMESPACE__.'\\WEBDAV_TARGET') && $this->getTarget(WEBDAV_TARGET)->isEnabled())
$done = $done && null !== $this->_upload2Storage($o, WEBDAV_TARGET);
if ($done)
@unlink($o);
$this->onProgress(TMPFILE_SOURCE, $o, $i ++, $j, PT_UPLOAD);
}
}
public function calcBackupReqDiskSpace($temp_file)
{
$this->logOutputTimestamp(_esc('Calculating the necessary disk space on working directory'));
if ($files = file($temp_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)) {
$necessary_space = getFilesSize($files);
$free_space = _disk_free_space($this->getWorkDir());
$free_space_str = getHumanReadableSize($free_space);
$necessary_space_str = getHumanReadableSize($necessary_space);
if ($free_space < $necessary_space) {
$msg = sprintf(_esc('[!] The free disk space (%s) on %s is smaller than the necessary disk space (%s)'), $free_space_str, $this->getWorkDir(), $necessary_space_str);
$this->logOutputTimestamp('<yellow>' . $msg . '</yellow>', BULLET);
$this->addMessage(MESSAGE_TYPE_WARNING, $msg, $this->getCurrentJobId());
} else
$this->logOutputTimestamp(sprintf(_esc('disk space OK: space required : %s, free disk space : %s'), $necessary_space_str, $free_space_str), BULLET);
}
}
public function updateBackupFilesFilter($temp_file, $file_count, $callbacks)
{
$mode = defined(__NAMESPACE__.'\\BACKUP_MODE_DIFF') && BACKUP_MODE_DIFF == $this->getBackupMode() ? BACKUP_MODE_FULL : false;
if (BACKUP_MODE_FULL != $this->getBackupMode()) {
$last_timestamp = 0;
$last_timestamp = $this->getLastJob($mode);
$diff_mode = defined(__NAMESPACE__.'\\BACKUP_MODE_DIFF') && BACKUP_MODE_DIFF == $this->getBackupMode() ? _esc('differentially') : (defined(__NAMESPACE__.'\\BACKUP_MODE_DIFF') && BACKUP_MODE_INC == $this->getBackupMode() ? _esc('incrementally') : '');
$this->logOutputTimestamp(sprintf(_esc('Filtering out %s those files not modifed since %s'), $diff_mode, date(DATETIME_FORMAT, $last_timestamp)), BULLET, 1);
$log_filename = preg_replace('/(.+)(\..+)$/', '$1-' . $this->getBackupMode() . '$2', BACKUP_FILTER_LOG);
$ref_log_filename = preg_replace('/(.+)(\..+)$/', '$1-' . (false === $mode ? $this->getBackupMode() : $mode) . '$2', BACKUP_FILTER_LOG);
! _file_exists($ref_log_filename) && $ref_log_filename = preg_replace('/(.+)(\..+)$/', '$1-' . BACKUP_MODE_FULL . '$2', BACKUP_FILTER_LOG);
$this->logOutputTimestamp(sprintf(_esc('Logging the MD5 hash value of the files into %s'), str_replace(array(
ROOT_PATH,
LOG_DIR
), array(
'ROOT' . DIRECTORY_SEPARATOR,
'LOGDIR' . DIRECTORY_SEPARATOR
), $log_filename)), BULLET, 1);
$bf = new BackupFilesFilter($log_filename, $ref_log_filename);
$bf->setCallback($callbacks['abort'], $callbacks['progress'], $this->getVerbosity(VERBOSE_COMPACT) ? $callbacks['output'] : null);
if (false === ($file_count = $bf->filter($temp_file, $last_timestamp, $mode))) {
$e = error_get_last();
unlink($temp_file);
throw new MyException($e['message'], $e['type']);
}
}
}
private function _loadFileList($temp_file, $src_dir, $excl_dirs)
{
$_this_ = &$this;
$before_section_callback = function ($temp_file, $file_count, $callbacks) use(&$_this_)
{
$_this_->getProgressManager()->cleanUp();
$_this_->calcBackupReqDiskSpace($temp_file);
$_this_->updateBackupFilesFilter($temp_file, $file_count, $callbacks);
$_this_->getProgressManager()->cleanUp();
};
$callbacks = array(
'abort' => array(
$this,
'chkProcessSignal'
),
'progress' => array(
$this,
'onProgress'
),
'output' => array(
$this,
'logOutputTimestamp'
),
'before_section' => $before_section_callback
);
$use_cache_preloader = BACKUP_MODE_FULL == $this->getOptions('mode', BACKUP_MODE_FULL) && strToBool($this->getOptions('use_cache_preload', false));
$use_cache_preloader = $use_cache_preloader && (- 1 == $this->getJobType());
$cache_file = LOG_PREFIX . '-srcfiles.cache';
$array = false;
$cache_data = false;
if ($use_cache_preloader && _is_file($cache_file)) {
if (($cache_data = json_decode(file_get_contents($cache_file), true)) && $cache_data['done']) {
if ($cache_data['running'] || ! ($cache_data['done'] && isset($cache_data['filename']) && isset($cache_data['files_count']) && $cache_data['files_count'] && (time() - $cache_data['timestamp'] < 60 * $this->getOptions('cache_preload_age', 1440)))) {
$array = false;
} else {
$array = array(
$cache_data['files_count'],
$cache_data['filename']
);
foreach (array_keys($array[1]) as $filename)
if (! _is_file($filename)) {
$array = false;
break;
}
$array && $this->logOutputTimestamp(sprintf(_esc("loaded from a cache file built %s ago"), getHumanReadableTime(time() - $cache_data['timestamp'])), BULLET);
}
}
}
if (! ($use_cache_preloader && $array && $cache_data)) {
$array = buildFileList($temp_file, $this->getOptions(), $src_dir, $excl_dirs, $this->getExcludedFiles(), $this->getExcludedExt(), $this->getExcludedLinks(), $this->getVerbosity(VERBOSE_COMPACT), $callbacks, - 1 == $this->getJobType());
if (! (isset($array[1]) && $array[1])) {
$this->outputError();
$cache_data = false;
} else {
$cache_data = array(
'done' => true,
'running' => false,
'files_count' => $array[0],
'filename' => $array[1],
'timestamp' => time()
);
$use_cache_preloader && file_put_contents($cache_file, json_encode($cache_data));
}
}
if ($cache_data) {
if ($use_cache_preloader) {
$files = array();
$ext = uniqid();
foreach ($cache_data['filename'] as $section_filename => $section_file_data) {
$key = $section_filename . '.' . $ext;
if (copy($section_filename, $key))
$files[$key] = $section_file_data;
else
$files[$section_filename] = $section_file_data;
}
} else {
$files = $cache_data['filename'];
}
return array(
$cache_data['files_count'],
$files
);
}
return false;
}
private function _createFileBackup()
{
if ($this->chkProcessSignal())
return $this->processAbortSignal(SRCFILE_SOURCE, OPER_SRCFILE_BACKUP);
$arclist = array();
$tar_size_limit = getParam($this->getOptions(), "size", 0);
if (true !== $tar_size_limit)
$tar_limit = $tar_size_limit * MB;
else
$tar_limit = 0;
$src_dir = $this->getSourceDir();
$total_fcount = 0;
$total_fsize = 0;
$temp_file = tempnam($this->getWorkDir(), WPMYBACKUP_LOGS . '_');
$excl_dirs = $this->getExcludedDirs();
$file_count = 0;
$this->logSeparator();
$this->logOutputTimestamp(sprintf(_esc("<b>Creating the backup file list </b> (%s)"), shorten_path($src_dir)));
if ($array = $this->_loadFileList($temp_file, $src_dir, $excl_dirs)) {
$file_count = $array[0];
$sections_temp_file = $array[1];
$this->logOutputTimestamp(sprintf(_esc("%d file(s) scheduled to be backed up"), $file_count), BULLET);
$this->logOutputTimestamp(sprintf(_esc("%d archive(s) scheduled to be created"), $file_count ? count($sections_temp_file) : 0), BULLET);
$this->getProgressManager()->cleanUp();
$section_done = 0;
if ($file_count) {
$os_tool_status = testOSTools($this->getWorkDir(), $this->getCompressionMethod(), $this->getCompressionLevel(), $this->getVolumeSize(), $this->getExcludedFiles(), $this->getExcludedDirs(), $this->getExcludedExt(), $this->getBZipVersion(), $this->getCygwin());
foreach ($sections_temp_file as $section_temp_file => $file_section_info) {
if (_file_exists($section_temp_file)) {
if (! $this->chkProcessSignal()) {
$archive_name = sprintf("%s%s", $this->getBackupName(), ! empty($file_section_info['section']) ? '-' . basename($file_section_info['section']) : '');
_file_exists($archive_name) && @unlink($archive_name);
$fcount = 0;
$fsize = 0;
if ($tar_limit > 0)
$multi_vol = sprintf(_esc(", multi-volume @ max %s/volume"), getHumanReadableSize($tar_limit));
else
$multi_vol = '';
$dir_hint = '<b>' . _esc('Directory path') . '</b> : ';
if (empty($file_section_info['section'])) {
$dir = $src_dir;
$dir_hint .= normalize_path($dir);
$dir_path = $src_dir;
} else {
$filter = str_replace($src_dir, '', $file_section_info['section']);
$dir = getWPSourceDirList($src_dir, $filter);
isset($dir[$filter]) && $dir_hint = '<blockquote>' . $dir[$filter][1] . '</blockquote>';
$dir_hint .= normalize_path($file_section_info['section']);
if (isset($dir[$filter])) {
$dir = $dir[$filter][0];
$dir_hint = "<b>" . $dir . "</b><br>" . $dir_hint;
} else
$dir = '';
empty($dir) && $dir = $src_dir;
$dir_path = $file_section_info['section'];
}
$dir_size = getDirSizeFromCache($dir_path, true);
$dir_hint .= '<p>' . _esc('Directory size') . ': ' . getHumanReadableSize($dir_size ? $dir_size : PHP_INT_MAX) . '</p>';
if (in_array(addTrailingSlash($dir_path), $excl_dirs)) {
$this->logOutputTimestamp(sprintf('<yellow>[%d/%d] ' . _esc('Skipping source folder') . ' %s</yellow>', ++ $section_done, count($sections_temp_file), $dir_path));
0 != $section_done && $this->logSeparator();
_file_exists($section_temp_file) && @unlink($section_temp_file);
continue;
}
0 == $section_done && $this->logSeparator();
$this->logOutputTimestamp(sprintf(_esc("<b>[%d/%d] Backing up the source folder</b> (%s%s)"), $section_done + 1, count($sections_temp_file), "<a class='help' onclick=" . getHelpCall("'$dir_hint'") . ">" . shorten_path($dir) . "</a>", $multi_vol));
if ($file_section_info['lines']) {
if ('extern' == $this->getTool() && false !== $os_tool_status) {
$arcs = $this->_runExternalCompressTool($archive_name, $tar_limit, $dir_path, $section_temp_file, $file_section_info['lines'], $excl_dirs);
} else
$arcs = $this->_runInternCompressTool($archive_name, $tar_limit, $section_temp_file, $file_section_info['lines']);
} else
$arcs = null;
$this->getProgressManager()->cleanUp();
if (is_array($arcs)) {
$i = 1;
$this->addVolCount(count($arcs));
asort($arcs);
foreach ($arcs as $arc) {
if (! $arc['queued'] && _file_exists($arc['name'])) {
$this->onNewArc($arc['name'], $arc['bytes'], $arc['arcsize'], $i ++);
$arc['queued'] = true;
}
$arclist[] = $arc['name'];
$fcount += $arc['count'];
$fsize += $arc['bytes'];
}
}
if (_file_exists($section_temp_file)) {
$this->addtFileCount(getFileLinesCount($section_temp_file));
$this->logOutput(sprintf(_esc("<br><b>%s</b> : %s"), _esc('SUBTOTAL'), sprintf('%s files added (%s%s) from %s', getSpan($fcount, '#fff', 'bold'), getHumanReadableSize($fsize), count($arcs) > 1 ? sprintf(', %d vols', count($arcs)) : '', shorten_path($dir_path))));
$this->logSeparator();
}
$total_fcount += $fcount;
$total_fsize += $fsize;
}
@unlink($section_temp_file);
$section_done ++;
}
}
}
$this->logOutput(sprintf("<white><b>" . _esc('GRAND TOTAL') . "</b></white> : " . _esc('%s files added (%s) to %d archive(s) out of %d scheduled files from %s'), getSpan($total_fcount, '#fff', 'bold'), getHumanReadableSize($total_fsize), $section_done, $file_count, shorten_path($src_dir)));
if ($total_fcount != $file_count && $total_fcount > 0) {
$diff_hint = _esc('The estimated/scheduled file count may include some reference to folders, links or other items that should normally be discarded. This is rather a bug although it is NOT harmfull.<br>Don`t worry, be happy!');
$msg = _esc('(the added files count may be different than the estimated/scheduled file count; this is a known behaviour)');
$this->logOutput("<a class='help' onclick=" . getHelpCall("'$diff_hint'") . ">$msg</a>");
}
if (! $this->getTargetCount() || ($this->uploaded < $section_done * $this->getTargetCount(true))) {
$this->logOutput('<red>' . sprintf(sprintf(_esc('WARNING : %s. Check the messages above and try to fix the cause.'), ! $this->uploaded ? _esc('Not even a single archive was uploaded') : _esc('Only %d archives were successfully uploaded (of %d sections x %d targets)')), $this->uploaded, $section_done, $this->getTargetCount(true)) . '</red>');
}
$this->logSeparator();
unlink($temp_file);
}
return $this->getTargetCount() ? $arclist : false;
}
private function _runExternalCompressTool($archive_path, $archive_limit, $src_dir, $temp_file, $file_count, $excl_dirs = null)
{
$arcs = array();
null == $excl_dirs && $excl_dirs = $this->getExcludedDirs();
$archive_size = getDirSizeByFileList($temp_file); 
! $archive_size && $archive_size = getDirSize($src_dir, $excl_dirs); 
$fcount = getFileLinesCount($temp_file);
$vol_count = $archive_limit > 0 ? ceil($archive_size / $archive_limit) : 0;
$this->startCompress($archive_path);
if ($this->chkProcessSignal())
return $this->processAbortSignal(SRCFILE_SOURCE, OPER_COMPRESS_EXTERN);
$this->onProgress(TMPFILE_SOURCE, $this->getSourceDir(), 0, $archive_size, PT_COMPRESS, - 1);
$result = unixTarNZip($src_dir, $archive_path, $this->getCompressionMethod(), $this->getCompressionLevel(), $this->getVolumeSize() * MB, false, $this->getExcludedFiles(), $excl_dirs, $this->getExcludedExt(), $this->getExcludedLinks(), $this->getBZipVersion(), $this->getCygwin());
$this->onProgress(TMPFILE_SOURCE, $this->getSourceDir(), $archive_size, $archive_size, PT_COMPRESS, - 1);
if (is_array($result) && count($result) > 0)
foreach ($result as $arc)
if (! _is_dir($arc)) {
$fs = filesize($arc);
if (preg_match('/(.+\.tar)\.[^.]+$/', $arc, $matches)) {
$tar = $matches[1];
if (_file_exists($tar)) {
$fs = filesize($tar);
unlink($tar);
}
}
if (0 == $this->getVolumeSize())
$fs = $archive_size;
$arcs[] = array(
'name' => $arc,
'arcsize' => $fs,
'count' => (0 == count($arcs) ? $fcount : 0),
'bytes' => $fs,
'queued' => false
);
}
if ($vol_count > 1)
$this->logOutputTimestamp(sprintf(_esc("archiving %s to %s"), getHumanReadableSize($archive_size), preg_replace("/.tar$/", '.*.tar', basename($archive_path))), "[*]");
else
$this->logOutputTimestamp(sprintf(_esc("archive %s created successfully (%d files, %d volumes)"), getSpan(basename($archive_path), 'cyan'), $fcount, count($vol_count)), BULLET);
return $arcs;
}
private function _runInternCompressTool($archive_path, $archive_limit, $temp_file, $file_count)
{
global $COMPRESSION_ARCHIVE;
$arcs = array();
$fcount = 0;
$arc_class = $COMPRESSION_ARCHIVE[$this->getCompressionMethod()];
$archive_classname = __NAMESPACE__ . '\\' . $arc_class;
if (! class_exists($archive_classname) && file_exists(CLASS_PATH . $arc_class . '.php'))
include_once CLASS_PATH . $arc_class . '.php';
if (! class_exists($archive_classname))
throw new MyException(sprintf(_esc('Cannot compress using the archive type %s.') . '<br>' . _esc('Class %s does not exist'), $this->getCompressionName(), $archive_classname));
$ptype = 'MyPclZipArchive' == $arc_class ? PT_ENQUEUE : PT_ADDFILE;
$archive = new $archive_classname($archive_path, TMPFILE_SOURCE);
$archive->setCPUSleep($this->getCPUSleep());
$archive->onAbortCallback = array(
$this,
'chkProcessSignal'
);
$archive->onProgressCallback = array(
$this,
'onProgress'
);
$archive->onStdOutput = array(
$this,
'logOutputTimestamp'
);
$volumes = array();
$volumes[] = $archive_path;
$arcname = null;
$eol_len = strlen(PHP_EOL);
$handle = fopen($temp_file, "r");
while (($file = fgets($handle)) !== false) {
if ($this->chkProcessSignal()) 
continue;
$reset_timer = false;
$has_eol = PHP_EOL == substr($file, - $eol_len);
$has_eol && $file = substr($file, 0, - $eol_len);
if (! _file_exists($file)) {
$use_cache_preloader = BACKUP_MODE_FULL == $this->getOptions('mode', BACKUP_MODE_FULL) && strToBool($this->getOptions('use_cache_preload', false));
$err_msg = sprintf(_esc('[!] Skipping file %s due to it does not exist.'), $file);
$use_cache_preloader && $err_msg .= ' ' . _esc('Perhaps the file list cache is obsolete.');
$this->logOutputTimestamp('<yellow>' . $err_msg . '</yellow>');
continue;
}
$archive_size = $archive->getFileSize();
$fs = filesize($file);
if ($archive_limit > 0 && $archive_size + $fs > $archive_limit && $fcount > 0) {
$this->onProgress(SRCFILE_SOURCE, $temp_file, $fcount, $file_count, $ptype, 0);
$this->logOutputTimestamp(sprintf(_esc("dumping %s of buffered stream to %s"), getHumanReadableSize($archive_size), basename($archive_path)), "[" . count($volumes) . "]");
$this->startCompress($archive_path);
$arcname = $archive->compress($this->getCompressionMethod(), $this->getCompressionLevel());
$archive->close(); 
$arcs[] = array(
'name' => $arcname,
'arcsize' => filesize($arcname),
'count' => $archive->getFileCount(),
'bytes' => $archive->getFileSize(),
'queued' => false
);
if (NONE != $this->getCompressionMethod())
$archive->unlink();
if (! (false === $arcname || empty($arcname))) {
$this->onNewArc($arcname, $archive_size, filesize($arcname), count($volumes) + 1);
$arcs[count($arcs) - 1]['queued'] = true;
}
$archive_path = sprintf("%s-%d", $this->getBackupName(), count($volumes));
$volumes[] = $archive_path;
$archive->setFileName($archive_path);
$reset_timer = true;
}
if (false !== $arcname && DIRECTORY_SEPARATOR != substr($file, - 1)) {
$alias = str_replace('..' . DIRECTORY_SEPARATOR, '', $file);
strToBool($this->options['relative_path']) && $alias = str_replace(delTrailingSlash(@constant('ABSPATH') ? ABSPATH : ROOT_PATH, '/'), '', $alias);
$this->getVerbosity(VERBOSE_FULL) && $this->logOutputTimestamp(sprintf(_esc("adding %s"), $file), BULLET);
if (false !== $archive->addFile($file, $alias, empty($this->nocompress) || ! preg_match('/.*\.(' . implode('|', $this->nocompress) . ')$/', $file))) {
$fcount ++;
}
}
$this->onProgress(SRCFILE_SOURCE, $temp_file, $fcount, $file_count, $ptype, 1, $reset_timer);
}
isset($reset_timer) && $this->onProgress(SRCFILE_SOURCE, $temp_file, $file_count, $file_count, $ptype, 1, $reset_timer);
fclose($handle);
if (false !== $arcname) {
if (count($volumes) > 1) {
$this->logOutputTimestamp(sprintf(_esc("dumping %s of buffered stream (%d files) to %s"), getHumanReadableSize($archive_size), $fcount, basename($archive_path)), "[" . count($volumes) . "]");
} else
$this->logOutputTimestamp(sprintf(_esc("archive %s created successfully (%d files, %d volume)"), getSpan(basename($archive_path), 'cyan'), $fcount, count($volumes)), BULLET);
$this->startCompress($archive_path);
$arcname = $archive->compress($this->getCompressionMethod(), $this->getCompressionLevel());
if (false !== $arcname) {
$archive->close();
$arcs[] = array(
'name' => $arcname,
'arcsize' => filesize($arcname),
'count' => $archive->getFileCount(),
'bytes' => $archive->getFileSize(),
'queued' => false
);
}
}
$archive->close(); 
if (NONE != $this->getCompressionMethod())
$archive->unlink();
if ($this->chkProcessSignal())
return $this->processAbortSignal(SRCFILE_SOURCE, OPER_COMPRESS_INTERN);
return $arcs;
}
public function onNewArc($arc = null, $uncompressed_size = 0, $compressed_size = 0, $vol_no = 0)
{
$result = false;
$this->compressed_files[$vol_no] = array(
'name' => $arc,
'uncompressed' => $uncompressed_size,
'compressed' => $compressed_size
);
$err_params = $this->getOperErrParams($arc, null);
$targets_priority = array(
'FTP_TARGET' => '_upload2Storage',
'DROPBOX_TARGET' => '_upload2Storage', 
'GOOGLE_TARGET' => '_upload2Storage', 
'WEBDAV_TARGET' => '_upload2Storage', 
'SSH_TARGET' => '_upload2Storage', 
'MAIL_TARGET' => '_upload2Email', 
'DISK_TARGET' => '_move2Disk'
); 
if (! empty($arc)) {
$fs = _is_file($arc) ? filesize($arc) : false;
if (false === $fs) {
$this->outputError(sprintf('<red>' . _esc('%s does not exists. This should never happen.') . '</red>', basename($arc)), false, $err_params);
return false;
}
try {
if ($fs) {
$encryption = $this->getOptions('encryption', null);
if (! empty($encryption) && ($out = $this->encrypt($arc))) {
unlink($arc); 
$arc = $out;
$fs = filesize($arc);
}
$this->uncompressed += $uncompressed_size;
$this->compressed += $fs;
$ratio = $uncompressed_size / $fs;
$err_params['METRIC_ACTION'] = 'METRIC_ACTION_COMPRESS';
$this->stopCompress($arc, $uncompressed_size, $ratio, $vol_no);
$saved = false;
$err_params['METRIC_ACTION'] = 'METRIC_ACTION_TRANSFER';
foreach ($targets_priority as $target => $target_func) {
if ((- 100 != ($target = $this->getTargetConstant($target))) && null != ($found_target = $this->getTarget($target)) && $found_target->isEnabled()) {
list ($target_name, $oper_send, $oper_sent) = $this->getTargetOperConsts($target);
$err_params['METRIC_OPERATION'] = $oper_send;
$sent = $this->$target_func($arc, $target, $uncompressed_size, ! in_array($target, array(
$this->getTargetConstant('WEBDAV_TARGET'),
$this->getTargetConstant('DROPBOX_TARGET'),
$this->getTargetConstant('GOOGLE_TARGET')
)));
$sent && $this->addTargetCount($target);
$this->addFailedCount($sent);
$saved = $saved || $sent;
if (! $sent) {
$errmsg = error_get_last();
if (null != $errmsg)
$this->outputError(sprintf('<red>%s</red>', $errmsg['message']), false, $err_params);
}
}
$this->uploaded += $saved;
}
$sufix = $saved ? _esc('although you have enabled at least one') : _esc('(no valid target specified)');
if (! $saved && (null !== ($job_id = $this->getCurrentJobId()) || $job_id >= 0) && 0 == $this->getTargetCount())
$this->outputError('<red>' . sprintf(_esc("[!] %s has not been copied to any location %s."), basename($arc), $sufix) . '</red> ' . _esc('Please do it manually.'));
else 
if (_file_exists($arc) && NONE != $this->getCompressionMethod()) {
$result = true;
unlink($arc);
}
} else
$this->outputError(sprintf(_esc("%s skipped due to null file size"), basename($arc)), false, $err_params);
} catch (MyException $e) {
$this->outputError(formatErrMsg($e), false, $err_params);
}
} else
$this->outputError(sprintf(_esc("<red>Internal error: onNewArc() called without arguments.</red>"), basename($arc)), false, $err_params);
return $result;
}
public function onMySqlMaint($table_name, $cmd, $msg_type, $msg_text)
{
$prefix = '';
$sufix = '';
if ('function' == $msg_type) {
$multiplier = 1;
$msg = sprintf(_esc('Executing %s for the tables %s'), $cmd, $table_name);
} elseif ($this->getVerbosity(VERBOSE_FULL)) {
if ('status' == $msg_type && 'prepare' == $msg_text) {
$multiplier = 2;
$msg = sprintf(_esc('Preparing to %s the table %s'), $cmd, $table_name);
} elseif ('prepare' != $msg_text) {
$multiplier = 3;
if ('note' == $msg_type || 'warning' == $msg_type) {
$prefix = '<yellow>';
$sufix = '</yellow>';
} elseif ('error' == $msg_type) {
$prefix = '<red>';
$sufix = '</red>';
} else {
$prefix = _esc('status => ');
}
$msg = $prefix . $msg_text . $sufix;
}
} else
return;
$this->logOutputTimestamp($msg, BULLET, $multiplier);
}
public function run($job_type = JOB_BACKUP)
{
parent::run($job_type);
$arclist = array();
$start = time();
$cleaned = 0;
$this->uploaded = 0;
$this->uncompressed = 0;
$this->compressed = 0;
$ok_status = _esc('successfully');
$status = _esc('unknown');
$prefix = '';
$sufix = '';
$aborted = false;
if (is_cli())
$this->logSeparator();
$file_ok = true;
$sql_ok = true;
$exit_unexpectedly = false;
try {
if ($this->getTarget(MYSQL_SOURCE)->isEnabled()) {
$arc = $this->_createMySqlBackup();
if (is_array($arc))
$arclist = array_merge($arclist, $arc);
else
$sql_ok = false;
$this->getProgressManager()->cleanUp();
}
$arc = $this->_createFileBackup();
if (! ($aborted = $this->_is_job_aborted($aborted)) && false !== $arc) {
$arclist = array_merge($arclist, $arc);
$do_cleanup = false;
for ($i = DISK_TARGET; $i <= SSH_TARGET; $i ++)
$do_cleanup = $do_cleanup || (null !== ($target = $this->getTarget($i)) && $target->isEnabled());
if ($do_cleanup) {
$this->_uploadOrphans();
$this->_cleanupOrphans();
global $BACKUP_TARGETS;
foreach (array_keys($BACKUP_TARGETS) as $target)
null != ($target_obj = $this->getTarget($target)) && $target_obj->isEnabled() && $cleaned += $this->_cleanUpOldArchives($target);
}
$status = $ok_status;
$this->setError(null);
} else
$file_ok = false;
} catch (MyException $e) {
$status = _esc('with errors:<br>') . $e->getMessage();
$this->setError($e);
$arclist = false;
$exit_unexpectedly = true;
}
$file_count = count($arclist);
$aborted = $this->_is_job_aborted($aborted);
$elapsed_time = time() - $start;
$job_status = 'JOB_STATUS_FINISHED';
if ($sql_ok && $file_ok) {
$aborted && $job_status = 'JOB_STATUS_ABORTED';
$job_state = ! ($aborted || $exit_unexpectedly) && $file_count ? 'JOB_STATE_COMPLETED' : 'JOB_STATE_PARTIAL';
} else {
$job_state = $file_count && ! $exit_unexpectedly ? 'JOB_STATE_PARTIAL' : 'JOB_STATE_FAILED';
}
$this->_job_state = @constant(__NAMESPACE__ . '\\' . $job_state);
$this->_job_status = @constant(__NAMESPACE__ . '\\' . $job_status);
if ($file_count) {
- 1 == $job_type || $this->onJobEnds(array(
'duration' => $elapsed_time,
'avg_cpu' => get_system_load($elapsed_time),
'job_status' => $job_status,
'job_state' => $job_state,
'files_count' => $file_count
));
if ($ok_status != $status) {
$prefix = '<red>';
$sufix = '</red>';
} else {
$prefix = '<white>';
$sufix = '</white>';
}
}
if ($aborted) {
$status = _esc('with abort signal.');
$prefix = '<yellow>';
$sufix = '</yellow>';
$this->ackProcessSignal();
}
$cleaned > 0 && $this->logOutput(sprintf(_esc("<br><b>TOTAL</b> : %d old files cleaned-up"), $cleaned));
$this->logSeparator();
$job_summary_str = $prefix . sprintf(_esc('<b>Job finished %s</b>.<br>Total elapsed time %s'), $status, timeFormat($elapsed_time));
$ok_status == $status && $job_summary_str .= sprintf(_esc('<br>Average compression ratio %.2fx'), 0 != $this->compressed ? $this->uncompressed / $this->compressed : 0);
$job_summary_str .= $sufix;
$this->logOutput($job_summary_str);
$this->logSeparator();
(- 1 == $job_type) || $this->sendEmailReport();
$this->logSeparator();
$this->logOutput(sprintf('<div class="hintbox rounded-container"><black>' . _esc('Do not forget to test your backups regularly. This step is as important as the backup itself.') . ' ' . readMoreHere('http://www.taobackup.com/testing_info.html') . '.</black></div>'));
$this->logSeparator();
$this->printJobSection(false, true); 
$job_id = $this->getCurrentJobId();
$this->addMessage($ok_status == $status ? MESSAGE_TYPE_NORMAL : MESSAGE_TYPE_WARNING, sprintf(_esc('New backup job run by %s (%s)'), $this->getSender(), $status), empty($job_id) ? 0 : $job_id);
return 0 === $file_count ? false : $arclist;
}
public function runMySQLMaintenance()
{
$job_id = JOB_MYSQL_MAINT;
$this->setJobId($job_id);
if ($this->chkProcessSignal($job_id))
return $this->processAbortSignal(OPER_MAINT_MYSQL, $job_id); 
echo "<!--[job_id:$job_id]-->"; 
$this->logOutputTimestamp(_esc("<b>Running MySQL maintenance task</b>"));
$target = $this->getTarget(MYSQL_SOURCE);
$params = $target->getParams();
$db_prefix = count(wp_get_user_blogs_prefixes()) < 2 ? wp_get_db_prefix() : '';
$pattern = $db_prefix . preg_replace('/([,|])/', '\1' . $db_prefix, $params['tables']);
$mysqlbkp = new MySQLBackupHandler($target->getOptions());
$tables = $mysqlbkp->getTableNameFromPattern($pattern);
$this->onMySqlMaint($pattern, 'table maintenance', 'function', 'prepare');
$result = $mysqlbkp->execTableMaintenance($tables, array(
$this,
'onMySqlMaint'
), array(
$this,
'onProgress'
), array(
$this,
'chkProcessSignal'
));
$this->logOutputTimestamp(sprintf(_esc('In total %d tables have been checked.'), count($result)), BULLET);
$msg = $this->_getMySqlMaintenanceStatus($result);
if (! empty($msg))
$this->logOutput($msg);
$status = _esc('successfully');
$prefix = '';
$sufix = '';
if (0 == count($result) || ! empty($msg)) {
$status = _esc('with errors');
$prefix = '<red>';
$sufix = '</red>';
}
if ($this->chkProcessSignal($job_id)) {
$status = _esc('with abort signal');
$prefix = '<yellow>';
$sufix = '</yellow>';
$this->ackProcessSignal($job_id);
}
$this->getProgressManager()->cleanUp();
$this->logOutputTimestamp(sprintf(_esc('%s<b>Task completed %s</b>.%s'), $prefix, $status, $sufix), BULLET, 0);
}
public function getCompressedFiles()
{
return $this->compressed_files;
}
public function setBackupMode($mode)
{}
}
?>