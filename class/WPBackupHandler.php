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
 * @file    : WPBackupHandler.php $
 * 
 * @id      : WPBackupHandler.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;
include_once EDITOR_PATH . 'file-functions.php';
if (defined ( 'GOOGLE_TARGET' )) {
}
if (defined ( 'DROPBOX_TARGET' )) {
}
defined ( 'WEBDAV_TARGET' ) && require_once STORAGE_PATH . 'WebDAVWebStorage.php';
defined ( 'MYSQL_SOURCE' ) && require_once CLASS_PATH . 'MySQLBackupHandler.php';
if (getMemoryLimit () < 128 * MB) {
ini_set ( 'memory_limit', "128M" ); 
}
ini_set ( "error_log", ERROR_LOG );
class WPBackupHandler extends AbstractJob {
private $uncompressed;
private $compressed;
private $compressed_files;
function __construct($opts = null, $sender = '') {
parent::__construct ( $opts, $sender );
$this->compressed_files = array ();
date_default_timezone_set ( wp_get_timezone_string () );
if (null != getArg ( 'help', $opts )) {
printHelp ();
exit ();
}
if (! is_session_started ( false ) && ! headers_sent ())
session_start ();
session_write_close ();
}
private function _getFileParts($api, $target, $oper_send, $filename) {
global $TARGET_NAMES;
$target_name = $TARGET_NAMES [$target];
$err_params = $this->getOperErrParams ( $filename, $oper_send );
$parts = array ();
try {
$filesize = filesize ( $filename );
$quota = $api->getQuota ();
$free_space = $api->getFreeSpace ();
$upload_limit = $api->getUploadLimit ();
if ($filesize > $free_space && $quota > 0) {
$this->outputError ( sprintf ( _esc ( "<red>[!] file %s cannot be uploaded to %s due to insufficient disk space (free: %s, needs: %s)</red>" ), $filename, $target_name, getHumanReadableSize ( $free_space ), getHumanReadableSize ( $filesize ) ), false, $err_params );
} 			
else if ($filesize > $upload_limit) {
$this->logOutputTimestamp ( sprintf ( _esc ( "<yellow>file %s (%s) exceeds %s limit of %s</yellow>" ), basename ( $filename ), getHumanReadableSize ( $filesize ), $target_name, getHumanReadableSize ( $upload_limit ) ), BULLET );
$parts = splitFile ( $filename, $upload_limit );
$this->outputError ( sprintf ( _esc ( "file %s was splitted into %d parts" ), basename ( $filename ), count ( $parts ) ), false, $err_params );
} else
$parts = array (
$filename 
);
} catch ( MyException $e ) {
$err_params = $this->getOperErrParams ( $filename, $oper_send );
$this->outputError ( formatErrMsg ( $e, $target_name ), false, $err_params );
}
return $parts;
}
private function _upload2Storage($filename, $target, $add_filename = false) {
list ( $target_name, $oper_send, $oper_sent ) = $this->getTargetOperConsts ( $target );
$metadata = array ();
if (in_array ( $target, array (
FTP_TARGET,
SSH_TARGET 
) ))
$func_name = 'initRemoteStorage';
else
$func_name = 'initCloudStorage';
try {
$api = $this->$func_name ( $target, $filename );
if (! is_object ( $api ))
return $api;
$parts = $this->_getFileParts ( $api, $target, $oper_send, $filename );
$path = $this->getTarget ( $target )->getPath ();
foreach ( $parts as $part ) {
if (! file_exists ( $part ))
continue;
$this->startTransfer ( $oper_send, $part, $path, null, $api->isSecure () );
$result = $api->uploadFile ( $part, $add_filename ? addTrailingSlash ( $path, '/' ) . basename ( $part ) : $path );
if (! $api->curlAborted ()) {
$this->parseResponse ( $result );
$metadata [] = $result;
$this->stopTransfer ( $oper_sent, $part, filesize ( $part ) );
if (count ( $parts ) > 1)
unlink ( $part );
}
}
} catch ( MyException $e ) {
$err_params = $this->getOperErrParams ( $filename, $oper_send );
$this->outputError ( formatErrMsg ( $e ), false, $err_params );
$metadata = false;
$this->getProgressManager ()->setProgress ( $target, isset ( $part ) ? $part : null, 0, 0, 1 );
}
return $metadata;
}
private function _move2Disk($arc = null) {
if ($this->chkProcessSignal ())
return $this->processAbortSignal ( DISK_TARGET, OPER_SEND_DISK );
if ($this->getTarget ( DISK_TARGET )->isEnabled () && null !== $this->getTarget ( DISK_TARGET )->getPath ()) {
if (! empty ( $arc )) {
$this->startTransfer ( OPER_SEND_DISK, $arc, $this->getTarget ( DISK_TARGET )->getPath () );
$fsize = filesize ( $arc );
$err_params = $this->getOperErrParams ( $arc, OPER_SEND_DISK );
if (! file_exists ( $this->getTarget ( DISK_TARGET )->getPath () )) {
if (! mkdir ( $this->getTarget ( DISK_TARGET )->getPath (), 0770, true ))
$this->outputError ( null, false, $err_params );
}
try {
$free = disk_free_space ( dirname ( $arc ) );
if ($fsize <= $free) {
$success = move_file ( $arc, addTrailingSlash ( $this->getTarget ( DISK_TARGET )->getPath () ) . basename ( $arc ) );
$this->onBytesSent ( DISK_TARGET, $arc, $fsize, $fsize );
if ($success)
$this->stopTransfer ( OPER_SENT_DISK, $arc, $fsize );
return $success;
} else {
$this->outputError ( sprintf ( _esc ( "<red>[!] file %s cannot be moved to disk due to insufficient disk space (free: %s, required: %s)</red>" ), $arc, getHumanReadableSize ( $free ), getHumanReadableSize ( $fsize ) ), false, $err_params );
}
} catch ( MyException $e ) {
$this->outputError ( formatErrMsg ( $e ), false, $err_params );
}
}
}
return true;
}
private function _createMySqlBackup() {
if ($this->chkProcessSignal ())
return $this->processAbortSignal ( MYSQL_SOURCE );
$target = $this->getTarget ( MYSQL_SOURCE );
if ($target->isEnabled ()) {
$params = $target->getParams ();
if (! empty ( $params ['tables'] )) {
global $wpdb;
$db_prefix = is_wp () ? $wpdb->base_prefix : '';
$patterns = explode ( ',', $params ['tables'] );
array_walk ( $patterns, function (&$item) use(&$db_prefix) {
$item = $db_prefix . $item;
} );
$this->logSeparator ();
$this->logOutputTimestamp ( sprintf ( _esc ( '<b>Backing up the MySQL database %s tables</b>' ), getSpan ( $target->getOption ( 'mysql_db' ), 'cyan' ) ) );
$this->logOutputTimestamp ( sprintf ( _esc ( 'tables pattern: %s' ), implode ( ',', $patterns ) ), BULLET );
$this->addtFileCount ( 1 );
$mysqlbkp = new MySQLBackupHandler ( $target->getOptions () );
$arcs = $mysqlbkp->compressMySQLScript ( $this->getBackupName () . '.' . $params ['mysql_format'], $this->getCompressionMethod (), $this->getCompressionLevel (), $this->getVolumeSize (), implode ( ',', $patterns ), $this->getMySqlDump (), $this->getTool (), $this->getBZipVersion (), $this->getCygwin (), $this->getCPUSleep (), array (
array (
$this,
'onNewArc' 
),
array (
$this,
'startCompress' 
),
array (
$this,
'onMySqlMaint' 
)
,null,
array (
$this,
'chkProcessSignal' 
),
array (
$this,
'onProgress' 
) 
) );
$this->addVolCount ( count ( $arcs ) );
return $arcs;
}
}
return true;
}
private function _upload2Email($filename, $target, $add_filename = false) {
if ($this->chkProcessSignal ())
return $this->processAbortSignal ( MAIL_TARGET, OPER_SEND_EMAIL );
if (null == $target) {
return false;
}
$to = $this->getTarget ( $target )->getOption ( 'backup2mail_address' );
empty ( $to ) && $to = $this->getNotificationEmail ();
$fsize = filesize ( $filename );
$err_params = $this->getOperErrParams ( $filename, OPER_SEND_EMAIL );
if (! empty ( $to ) && preg_match ( "/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6}$/i", $to )) {
$err_msg = null;
$from = sprintf ( 'From: %s <%s@%s>', WPMYBACKUP, 'backup2mail', $_SERVER ['SERVER_NAME'] );
$subject = sprintf ( _esc ( '%s backup of %s' ), WPMYBACKUP, $this->getBackupName () );
$body = _esc ( 'Backup enclosed as attachment' );
$key = uniqid ( 'php' );
$attachments [$key] = array (
'name' => basename ( $filename ),
'type' => null,
'tmp_name' => $filename,
'error' => 0,
'size' => $fsize 
);
$this->startTransfer ( OPER_SEND_EMAIL, $filename, $to );
try {
$result = false;
$upload_limit = getUploadLimit ();
$memory_limit = getMemoryLimit ();
$memory_usage = memory_get_usage ( true );
$mail_size = strlen ( $from . $to . $subject . $body ) + filesize ( $filename );
$mail_est_size = $mail_size * 1.33; 
if ($memory_limit - $memory_usage < $mail_est_size) 
$this->outputError ( sprintf ( '<yellow>' . _esc ( 'Warning: mail size : ~ %s (*1.33 => %s), PHP memory_limit : %s, mem usage: %s' ) . '</yellow>', getHumanReadableSize ( $mail_size ), getHumanReadableSize ( $mail_est_size ), getHumanReadableSize ( $memory_limit ), getHumanReadableSize ( $memory_usage ) ), false, $err_params );
elseif ($upload_limit > 0 && $upload_limit < $mail_est_size) 
$this->outputError ( sprintf ( '<yellow>' . _esc ( 'Warning: mail size : ~ %s (*1.33 => %s), PHP upload_max_filesize : %s' ) . '</yellow>', getHumanReadableSize ( $mail_size ), getHumanReadableSize ( $mail_est_size ), getHumanReadableSize ( $upload_limit ) ), false, $err_params );
else {
$native_backend = ! strToBool ( $this->getTarget ( $target )->getOption ( 'backup2mail_smtp' ) );
$backend = $this->getTarget ( $target )->getOption ( 'backup2mail_backend' );
$smtp_debug = defined ( 'SMTP_DEBUG' ) && SMTP_DEBUG;
$backend_params = array (
'debug' => $smtp_debug,
'auth' => strToBool ( $this->getTarget ( $target )->getOption ( 'backup2mail_auth' ) ) 
);
foreach ( array (
'host' => 'backup2mail_host',
'port' => 'backup2mail_port',
'username' => 'backup2mail_user',
'password' => 'backup2mail_pwd',
'timeout' => 'request_timeout' 
) as $key => $value )
$backend_params [$key] = $this->getTarget ( $target )->getOption ( $value );
$result = sendMail ( $from, $to, $subject, $body, $attachments, null, 3, $native_backend ? null : $backend, $backend_params, $smtp_debug );
}
} catch ( MyException $err ) {
$err_msg = $err->getMessage ();
}
if ($result) {
$this->onBytesSent ( MAIL_TARGET, $filename, $fsize, $fsize );
$this->stopTransfer ( OPER_SENT_EMAIL, $filename, $fsize );
} else
$this->outputError ( '<red>' . _esc ( 'Mail send failed' ) . (null == $err_msg ? '' : (': ' . $err_msg)) . '.</red>', true, $err_params );
return $result;
} else {
$this->outputError ( sprintf ( '<red>' . _esc ( 'Cannot send backup via e-mail due to invalid e-mail address' ) . '</red> [%s]', empty ( $to ) ? _esc ( 'empty' ) : $to ), false, $err_params );
}
}
private function _getMySqlMaintenanceStatus($array) {
$msg = '';
if (! empty ( $array )) {
foreach ( $array as $table_name => $cmds ) {
$err_msg = array ();
foreach ( $cmds as $cmd => $info )
if ('error' == $info [0] || 'warning' == $info [0])
$err_msg [] = sprintf ( _esc ( '%s on %s : %s' ), $info [0], $cmd, $info [1] );
if (count ( $err_msg ) > 0)
$msg .= sprintf ( _esc ( "%s found the following %d problems when checking the table %s:<br>%s<br>" ), WPMYBACKUP, count ( $err_msg ), $table_name, implode ( PHP_EOL, $err_msg ) );
}
}
return $msg;
}
private function _getTargetMaxAge($targets) {
$max = 0;
foreach ( $targets as $target )
if (- 100 != ($c = $this->getTargetConstant ( $target )))
$max = max ( $max, $this->getTarget ( $c )->getAge () );
return $max;
}
private function _cleanupOrphans() {
$ext = $this->getCompressionName ();
$files = getFileListByExt ( $this->getWorkDir (), $ext );
$dates = getDatesByAge ( getFilesTime ( $files, $this->getBackupName () . '-' ), $this->_getTargetMaxAge ( array (
'DROPBOX_TARGET',
'GOOGLE_TARGET',
'WEBDAV_TARGET' 
) ), OLDEST );
$orphans = array ();
foreach ( $dates as $d )
foreach ( $files as $file )
if (false !== strpos ( $this->getBackupName () . '-' . date ( 'Ymd-His', $d ), $file ))
$orphans [] = $file;
if (count ( $orphans ) > 0)
$this->logOutputTimestamp ( sprintf ( _esc ( "Cleaning up %d orphans archieves" ), count ( $orphans ) ) );
foreach ( $orphans as $o ) {
if (! $this->chkProcessSignal ())
@unlink ( $o );
else {
$this->processAbortSignal ( TMPFILE_SOURCE, OPER_CLEANUP_ORPHAN );
break;
}
}
}
private function _cleanUpOldArchives($target) {
$get_search_filter = function ($filter) use(&$target) {
switch ($target) {
default :
$result = $filter;
break;
case $this->getTargetConstant ( 'GOOGLE_TARGET' ) :
$result = 'title contains \'' . $filter . '\'';
break;
}
return $result;
};
$get_metadata = function ($metadata) use(&$target) {
switch ($target) {
case $this->getTargetConstant ( 'GOOGLE_TARGET' ) :
$result = $metadata ['items'];
break;
default :
$result = $metadata;
break;
}
return $result;
};
$get_fileid = function ($file_item, $file_index, $file_path) use(&$target) {
switch ($target) {
case $this->getTargetConstant ( 'DROPBOX_TARGET' ) :
$result = $file_item ['path'];
break;
case $this->getTargetConstant ( 'GOOGLE_TARGET' ) :
$result = $file_item ['id'];
break;
case $this->getTargetConstant ( 'WEBDAV_TARGET' ) :
$result = addTrailingSlash ( $file_path, '/' ) . _basename ( $file_item ['name'] );
break;
case FTP_TARGET :
case SSH_TARGET :
$result = addTrailingSlash ( $file_path, '/' ) . $file_index;
break;
case DISK_TARGET :
$result = $file_item;
break;
}
return $result;
};
$get_isdir = function ($file_item) use(&$target) {
switch ($target) {
case $this->getTargetConstant ( 'DROPBOX_TARGET' ) :
case $this->getTargetConstant ( 'WEBDAV_TARGET' ) :
$result = $file_item ['is_dir'];
break;
case $this->getTargetConstant ( 'GOOGLE_TARGET' ) :
$result = strpos ( $file_item ["mimeType"], 'application/vnd.google-apps.folder' ) !== false;
break;
case FTP_TARGET :
case SSH_TARGET :
$result = $file_item [6];
break;
case DISK_TARGET :
$result = is_dir ( $file_item );
break;
}
return $result;
};
$get_filename = function ($file_item, $file_id) use(&$target) {
switch ($target) {
case $this->getTargetConstant ( 'DROPBOX_TARGET' ) :
$result = $file_item ['path'];
break;
case $this->getTargetConstant ( 'GOOGLE_TARGET' ) :
$result = $file_item ['title'];
break;
case $this->getTargetConstant ( 'WEBDAV_TARGET' ) :
$result = $file_item ['name'];
break;
case FTP_TARGET :
case SSH_TARGET :
$result = $file_id;
break;
case DISK_TARGET :
$result = $file_item;
break;
}
return $result;
};
$get_filesize = function ($file_item) use(&$target) {
switch ($target) {
case $this->getTargetConstant ( 'DROPBOX_TARGET' ) :
case $this->getTargetConstant ( 'WEBDAV_TARGET' ) :
$result = $file_item ['size'];
break;
case $this->getTargetConstant ( 'GOOGLE_TARGET' ) :
$result = $file_item ['fileSize'];
break;
case FTP_TARGET :
case SSH_TARGET :
$result = $file_item [4];
break;
case DISK_TARGET :
$result = filesize ( $file_item );
break;
}
return $result;
};
global $TARGET_NAMES, $COMPRESSION_NAMES;
if ($this->chkProcessSignal ())
return $this->processAbortSignal ( $target, OPER_CLEANUP_OLDIES );
if (! $this->getTarget ( $target )->isEnabled ())
return false;
$days = $this->getTarget ( $target )->getAge ();
if ($days < 0)
return false;
$pattern = $this->getNamePattern ();
$targetPath = $this->getTarget ( $target )->getPath ();
$api = null;
$session = $this->getOptions ();
$metadata = false;
$storage_class = null;
switch ($target) {
case $this->getTargetConstant ( 'DROPBOX_TARGET' ) :
$session = new DropboxOAuth2Client ();
$storage_class = 'DropboxCloudStorage';
break;
case $this->getTargetConstant ( 'GOOGLE_TARGET' ) :
$session = new GoogleOAuth2Client ();
$storage_class = 'GoogleCloudStorage';
break;
case $this->getTargetConstant ( 'WEBDAV_TARGET' ) :
$storage_class = 'WebDAVWebStorage';
break;
case FTP_TARGET :
case SSH_TARGET :
$api = getFtpObject ( $this->getOptions (), SSH_TARGET == $target );
break;
case DISK_TARGET :
$api = false;
$metadata = glob ( addTrailingSlash ( $targetPath ) . $pattern . "*." . $COMPRESSION_NAMES [$this->getCompressionMethod ()] . "*" );
$delete_func = 'unlink';
break;
}
$err_params = $this->getOperErrParams ( null, OPER_CLEANUP_OLDIES, false, 'METRIC_ACTION_CLEANUP' );
if (null != $session && method_exists ( $session, 'setProxyURI' )) {
if (array_key_exists ( 'METRIC_FILENAME', $err_params ))
unset ( $err_params ['METRIC_FILENAME'] );
if (! $this->_chkOAuthSession ( $TARGET_NAMES [$target], $session, $err_params, null, 0 ))
return false;
$session->setProxyURI ( OAUTH_PROXY_URL, '' );
$session->setTimeout ( $this->_getRequestTimeout () );
}
$deleted_files = 0;
$this->logOutputTimestamp ( sprintf ( _esc ( "Cleaning-up %s files older than %d days" ), ucwords ( $TARGET_NAMES [$target] ), $days ) );
try {
if (null === $api && null != $storage_class) {
$storage_class = __NAMESPACE__ . '\\' . $storage_class;
$api = new $storage_class ( $session );
}
$is_secure = false;
if (is_object ( $api )) {
$is_secure = $api->isSecure ();
$metadata = $api->searchFileNames ( $targetPath, $this->getSearchFilter ( $target, $pattern ) );
$delete_func = array (
$api,
'deleteFile' 
);
}
if (! is_array ( $metadata ))
throw new MyException ( sprintf ( _esc ( 'Clean-up old archives not implemented for %s' ), $TARGET_NAMES [$target] ) );
$files = array ();
$target_metadata = $get_metadata ( $metadata );
if (is_array ( $target_metadata ))
foreach ( $target_metadata as $file_id => $file ) {
$filename = $get_filename ( $file, $file_id );
if (! (empty ( $filename ) || $get_isdir ( $file )))
$files [] = $filename;
}
$dates = getDatesByAge ( getFilesTime ( $files, $pattern ), $days, OLDEST );
asort ( $dates );
$i = 1;
$j = count ( $dates );
foreach ( $dates as $d ) {
$file_pattern = $pattern . date ( 'Ymd-His', $d );
foreach ( $target_metadata as $file_id => $file ) {
$filename = _basename ( $get_filename ( $file, $file_id ) );
if (! $this->chkProcessSignal ()) {
if (0 !== strpos ( $filename, $file_pattern ))
continue;
$filesize = $get_filesize ( $file );
$err_params ['METRIC_FILENAME'] = $file;
$err_params ['METRIC_SIZE'] = $filesize;
$this->startTransfer ( OPER_CLEANUP_OLDIES, $filename, $targetPath, $TARGET_NAMES [$target], $is_secure );
$del_result = @_call_user_func ( $delete_func, $get_fileid ( $file, $file_id, $targetPath ) );
if (is_array ( $del_result ))
$del_result = isset ( $del_result [0] ) ? array_sum ( $del_result ) : (isset ( $del_result ['message'] ) ? 0 : 1);
$deleted_files += $del_result;
$this->stopTransfer ( OPER_CLEANUP_OLDIES, $filename, $filesize, 'METRIC_ACTION_CLEANUP' );
} else {
$this->processAbortSignal ( $target, OPER_CLEANUP_OLDIES );
break;
}
}
$this->onProgress ( $target, $filename, $i ++, $j, 5 );
}
} catch ( MyException $e ) {
$this->outputError ( formatErrMsg ( $e ), false, $err_params );
}
return $deleted_files;
}
private function _uploadOrphans() {
$targetsEnabled = function ($targets) {
$enabled = false;
foreach ( $targets as $target )
if (- 100 != ($c = $this->getTargetConstant ( $target )))
$enabled = $enabled || $this->getTarget ( $c )->isEnabled ();
return $enabled;
};
if (! $targetsEnabled ( array (
'DROPBOX_TARGET',
'GOOGLE_TARGET',
'WEBDAV_TARGET' 
) ))
return false;
$ext = $this->getCompressionName ();
$files = getFileListByExt ( $this->getWorkDir (), $ext );
$dates = getDatesByAge ( getFilesTime ( $files, $this->getBackupName () . '-' ), $this->_getTargetMaxAge ( array (
'DROPBOX_TARGET',
'GOOGLE_TARGET',
'WEBDAV_TARGET' 
) ), NEWEST );
$orphans = array ();
foreach ( $dates as $d )
foreach ( $files as $file )
if (preg_match ( '@.*' . $this->getBackupName () . '-' . date ( 'Ymd-His', $d ) . '.*@', $file ))
$orphans [] = $file;
if (count ( $orphans ) > 0)
$this->logOutputTimestamp ( sprintf ( _esc ( "Uploading %d orphans archieves" ), count ( $orphans ) ) );
$i = 1;
$j = count ( $orphans );
foreach ( $orphans as $o ) {
$done = false;
if (defined ( 'DROPBOX_TARGET' ) && $this->getTarget ( DROPBOX_TARGET )->isEnabled ())
$done = $done && null !== $this->_upload2Storage ( $o, DROPBOX_TARGET );
if (defined ( 'GOOGLE_TARGET' ) && $this->getTarget ( GOOGLE_TARGET )->isEnabled ())
$done = $done && null !== $this->_upload2Storage ( $o, GOOGLE_TARGET );
if (defined ( 'WEBDAV_TARGET' ) && $this->getTarget ( WEBDAV_TARGET )->isEnabled ())
$done = $done && null !== $this->_upload2Storage ( $o, WEBDAV_TARGET );
if ($done)
@unlink ( $o );
$this->onProgress ( TMPFILE_SOURCE, $o, $i ++, $j, 1 );
}
}
private function _createFileBackup() {
if ($this->chkProcessSignal ())
return $this->processAbortSignal ( SRCFILE_SOURCE, OPER_SRCFILE_BACKUP );
$arclist = array ();
$tar_size_limit = getParam ( $this->getOptions (), "size", 0 );
if (true !== $tar_size_limit)
$tar_limit = $tar_size_limit * MB;
else
$tar_limit = 0;
$src_dir = $this->getSourceDir ();
$total_fcount = 0;
$total_fsize = 0;
$temp_file = tempnam ( sys_get_temp_dir (), WPMYBACKUP_LOGS . '_' );
$excl_dirs = $this->getExcludedDirs ();
$this->logSeparator ();
$this->logOutputTimestamp ( sprintf ( _esc ( "<b>Creating the backup file list </b> (%s)" ), $src_dir ) );
if (is_wp ()) {
$wp_components = array_keys ( getWPSourceDirList ( WPMYBACKUP_ROOT ) );
array_walk ( $wp_components, function (&$item, $key) {
! empty ( $item ) && $item = WPMYBACKUP_ROOT . $item;
} );
} else
$wp_components = array ();
foreach ( $excl_dirs as $excl_dir ) {
$this->logOutputTimestamp ( _esc ( 'excluding directory ' ) . $excl_dir, BULLET );
foreach ( $wp_components as $key => $wp_comp ) {
if (strlen ( delTrailingSlash ( $excl_dir ) ) && false !== strpos ( delTrailingSlash ( $wp_comp ), delTrailingSlash ( $excl_dir ) ))
unset ( $wp_components [$key] );
}
}
$file_count = createFileList ( $temp_file, $src_dir, $excl_dirs, $this->getExcludedExt (), $this->getExcludedFiles (), $this->getExcludedLinks (), $this->getVerbosity ( VERBOSE_COMPACT ) ? array (
array (
$this,
'logOutputTimestamp' 
),
array (
$this,
'chkProcessSignal' 
) 
) : null );
$mode = defined ( 'BACKUP_MODE_DIFF' ) && BACKUP_MODE_DIFF == $this->getBackupMode () ? BACKUP_MODE_FULL : false;
$last_timestamp = 0;
if (BACKUP_MODE_FULL != $this->getBackupMode ()) {
$last_timestamp = $this->getLastJob ( $mode );
$diff_mode = defined ( 'BACKUP_MODE_DIFF' ) && BACKUP_MODE_DIFF == $this->getBackupMode () ? _esc ( 'differentially' ) : (defined ( 'BACKUP_MODE_DIFF' ) && BACKUP_MODE_INC == $this->getBackupMode () ? _esc ( 'incrementally' ) : '');
$this->logOutputTimestamp ( sprintf ( _esc ( 'Filtering out %s those files not modifed since %s' ), $diff_mode, date ( DATETIME_FORMAT, $last_timestamp ) ), BULLET, 1 );
}
$log_filename = preg_replace ( '/(.+)(\..+)$/', '$1-' . $this->getBackupMode () . '$2', BACKUP_FILTER_LOG );
$ref_log_filename = preg_replace ( '/(.+)(\..+)$/', '$1-' . (false === $mode ? $this->getBackupMode () : $mode) . '$2', BACKUP_FILTER_LOG );
! file_exists ( $ref_log_filename ) && $ref_log_filename = preg_replace ( '/(.+)(\..+)$/', '$1-' . BACKUP_MODE_FULL . '$2', BACKUP_FILTER_LOG );
$this->logOutputTimestamp ( sprintf ( _esc ( 'Logging the MD5 hash value of the files into %s' ), 'ROOT' . DIRECTORY_SEPARATOR . str_replace ( ROOT_PATH, '', $log_filename ) ), BULLET, 1 );
$bf = new BackupFilesFilter ( $log_filename, $ref_log_filename );
$callbacks = array (
array (
$this,
'chkProcessSignal' 
),
array (
$this,
'onProgress' 
),
array (
$this,
'logOutputTimestamp' 
) 
);
$bf->setCallback ( $callbacks [0], $callbacks [1], BACKUP_MODE_FULL != $this->getBackupMode () ? $callbacks [2] : null );
if (false === ($file_count = $bf->filter ( $temp_file, $last_timestamp, $mode ))) {
$e = error_get_last ();
unlink ( $temp_file );
throw new MyException ( $e ['message'], $e ['type'] );
}
if (! ($sections_temp_file = createFileListBySections ( $temp_file, $wp_components ))) {
$this->outputError ();
} else {
$this->logOutputTimestamp ( sprintf ( _esc ( "%d file(s) scheduled to be backed up" ), $file_count ), BULLET );
$this->logOutputTimestamp ( sprintf ( _esc ( "%d archive(s) scheduled to be created" ), $file_count ? count ( $sections_temp_file ) : 0 ), BULLET );
$section_done = 0;
if ($file_count) {
foreach ( $sections_temp_file as $section_temp_file => $file_section_info ) {
if (file_exists ( $section_temp_file )) {
if (! $this->chkProcessSignal ()) {
$archive_name = sprintf ( "%s%s", $this->getBackupName (), ! empty ( $file_section_info ['section'] ) ? '-' . basename ( $file_section_info ['section'] ) : '' );
file_exists ( $archive_name ) && @unlink ( $archive_name ); 
$fcount = 0;
$fsize = 0;
if ($tar_limit > 0)
$multi_vol = sprintf ( _esc ( ", multi-volume @ max %s/volume" ), getHumanReadableSize ( $tar_limit ) );
else
$multi_vol = '';
$dir_hint = '<b>' . _esc ( 'Directory path' ) . '</b> : ';
if (empty ( $file_section_info ['section'] )) {
$dir = $src_dir;
$dir_hint .= normalize_path ( $dir );
$dir_path = $src_dir;
} else {
$filter = str_replace ( $src_dir, '', $file_section_info ['section'] );
$dir = getWPSourceDirList ( $src_dir, $filter );
isset ( $dir [$filter] ) && $dir_hint = '<blockquote>' . $dir [$filter] [1] . '</blockquote>';
$dir_hint .= $file_section_info ['section'];
if (isset ( $dir [$filter] )) {
$dir = $dir [$filter] [0];
$dir_hint = "<b>" . normalize_path ( $dir ) . "</b><br>" . $dir_hint;
}
$dir_path = $file_section_info ['section'];
}
if (in_array ( addTrailingSlash ( $dir_path ), $excl_dirs )) {
$this->logOutputTimestamp ( sprintf ( '<yellow>[%d/%d] ' . _esc ( 'Skipping source folder' ) . ' %s</yellow>', ++ $section_done, count ( $sections_temp_file ), $dir_path ) );
0 != $section_done && $this->logSeparator ();
file_exists ( $section_temp_file ) && @unlink ( $section_temp_file );
continue;
}
0 == $section_done && $this->logSeparator ();
$this->logOutputTimestamp ( sprintf ( _esc ( "<b>[%d/%d] Backing up the source folder</b> (%s%s)" ), $section_done + 1, count ( $sections_temp_file ), "<a class='help' onclick=" . getHelpCall ( "'$dir_hint'" ) . ">" . $dir . "</a>", $multi_vol ) );
if ('extern' == $this->getTool () && false !== testOSTools ( $this->getWorkDir (), $this->getCompressionMethod (), $this->getCompressionLevel (), $this->getVolumeSize (), $this->getExcludedFiles (), $this->getExcludedDirs (), $this->getExcludedExt (), $this->getBZipVersion (), $this->getCygwin () )) {
if (is_array ( $wp_components ))
foreach ( $wp_components as $wp_comp ) {
if (delTrailingSlash ( $dir_path ) != delTrailingSlash ( $wp_comp ) && false !== strpos ( $wp_comp, $dir_path ))
! in_array ( $wp_comp, $excl_dirs ) && $excl_dirs [] = $wp_comp;
}
$arcs = $this->_runExternalCompressTool ( $archive_name, $tar_limit, $dir_path, $section_temp_file, $file_section_info ['lines'], $excl_dirs );
} else
$arcs = $this->_runInternCompressTool ( $archive_name, $tar_limit, $section_temp_file, $file_section_info ['lines'] );
$this->getProgressManager ()->cleanUp ();
if (is_array ( $arcs )) {
$i = 1;
$this->addVolCount ( count ( $arcs ) );
asort ( $arcs );
foreach ( $arcs as $arc )
if (file_exists ( $arc ['name'] )) {
$this->onNewArc ( $arc ['name'], $arc ['arcsize'], filesize ( $arc ['name'] ), $i ++ );
$arclist [] = $arc ['name'];
$fcount += $arc ['count'];
$fsize += $arc ['bytes'];
}
}
if (file_exists ( $section_temp_file )) {
$this->addtFileCount ( getFileLinesCount ( $section_temp_file ) );
$this->logOutput ( sprintf ( _esc ( "<br><b>SUBTOTAL</b> : %d files added (%s) from %s" ), $fcount, getHumanReadableSize ( $fsize ), $dir_path ) );
$this->logSeparator ();
}
$total_fcount += $fcount;
$total_fsize += $fsize;
}
@unlink ( $section_temp_file );
$section_done ++;
}
}
}
$this->logOutput ( sprintf ( "<white><b>" . _esc ( 'GRAND TOTAL' ) . "</b></white> : " . _esc ( '%d files added (%s) out of %d scheduled from %s' ), $total_fcount, getHumanReadableSize ( $total_fsize ), $file_count, $src_dir ) );
if ($total_fcount != $file_count && $total_fcount > 0) {
$diff_hint = _esc ( 'The estimated/scheduled file count may include some reference to folders, links or other items that should normally be discarded. This is rather a bug although it is NOT harmfull.<br>Don`t worry, be happy!' );
$msg = _esc ( '(the added files count may be different than the estimated/scheduled file count; this is a known behaviour)' );
$this->logOutput ( "<a class='help' onclick=" . getHelpCall ( "'$diff_hint'" ) . ">$msg</a>" );
}
$this->logSeparator ();
unlink ( $temp_file );
}
return $arclist;
}
private function _runExternalCompressTool($archive_path, $archive_limit, $src_dir, $temp_file, $file_count, $excl_dirs = null) {
$arcs = array ();
null == $excl_dirs && $excl_dirs = $this->getExcludedDirs ();
$archive_size = getDirSizeByFileList ( $temp_file ); 
! $archive_size && $archive_size = getDirSize ( $src_dir, $excl_dirs ); 
$fcount = getFileLinesCount ( $temp_file );
$vol_count = $archive_limit > 0 ? ceil ( $archive_size / $archive_limit ) : 0;
$this->startCompress ( $archive_path );
if ($this->chkProcessSignal ())
return $this->processAbortSignal ( SRCFILE_SOURCE, OPER_COMPRESS_EXTERN );
$this->onProgress ( TMPFILE_SOURCE, $this->getSourceDir (), 0, $archive_size, 3, - 1 ); 
$result = unixTarNZip ( $src_dir, $archive_path, $this->getCompressionMethod (), $this->getCompressionLevel (), $this->getVolumeSize () * MB, false, $this->getExcludedFiles (), $excl_dirs, $this->getExcludedExt (), $this->getBZipVersion (), $this->getCygwin () );
$this->onProgress ( TMPFILE_SOURCE, $this->getSourceDir (), $archive_size, $archive_size, 3, - 1 );
if (is_array ( $result ) && count ( $result ) > 0)
foreach ( $result as $arc )
if (! is_dir ( $arc )) {
$fs = filesize ( $arc );
if (preg_match ( '/(.+\.tar)\.[^.]+$/', $arc, $matches )) {
$tar = $matches [1];
if (file_exists ( $tar )) {
$fs = filesize ( $tar );
unlink ( $tar );
}
}
if (0 == $this->getVolumeSize ())
$fs = $archive_size;
$arcs [] = array (
'name' => $arc,
'arcsize' => $fs,
'count' => (0 == count ( $arcs ) ? $fcount : 0),
'bytes' => $fs 
);
}
if ($vol_count > 1)
$this->logOutputTimestamp ( sprintf ( _esc ( "archiving %s to %s" ), getHumanReadableSize ( $archive_size ), preg_replace ( "/.tar$/", '.*.tar', basename ( $archive_path ) ) ), "[*]" );
else
$this->logOutputTimestamp ( sprintf ( _esc ( "archive %s created successfully (%d files, %d volumes)" ), getSpan ( basename ( $archive_path ), 'cyan' ), $fcount, count ( $vol_count ) ), BULLET );
return $arcs;
}
private function _runInternCompressTool($archive_path, $archive_limit, $temp_file, $file_count) {
global $COMPRESSION_ARCHIVE;
$arcs = array ();
$fcount = 0;
$fsize = 0;
$archive_classname = __NAMESPACE__ . '\\' . $COMPRESSION_ARCHIVE [$this->getCompressionMethod ()];
$archive = new $archive_classname ( $archive_path, TMPFILE_SOURCE );
$archive->setCPUSleep ( $this->getCPUSleep () );
$archive->onAbortCallback = array (
$this,
'chkProcessSignal' 
);
$archive->onProgressCallback = array (
$this,
'onProgress' 
);
$archive->onStdOutput = array (
$this,
'logOutputTimestamp' 
);
$volumes = array ();
$volumes [] = $archive_path;
$arcname = null;
$handle = fopen ( $temp_file, "r" );
while ( ($file = fgets ( $handle )) !== false ) {
$reset_timer = false;
$file = str_replace ( array (
PHP_EOL,
'\n' 
), '', $file );
if (! file_exists ( $file ) || $this->chkProcessSignal ()) 
continue;
$archive_size = $archive->getFileSize ();
$fs = filesize ( $file );
if ($archive_limit > 0 && $archive_size + $fs > $archive_limit && $fcount > 0) {
$this->onProgress ( SRCFILE_SOURCE, $temp_file, $fcount, $file_count, 2, 0 ); 
$this->logOutputTimestamp ( sprintf ( _esc ( "dumping %s of buffered stream to %s" ), getHumanReadableSize ( $archive_size ), basename ( $archive_path ) ), "[" . count ( $volumes ) . "]" );
$this->startCompress ( $archive_path );
$arcname = $archive->compress ( $this->getCompressionMethod (), $this->getCompressionLevel () );
$archive->unlink ();
if (! (false === $arcname || empty ( $arcname )))
$this->onNewArc ( $arcname, $archive_size, filesize ( $arcname ), count ( $volumes ) + 1 );
$archive_path = sprintf ( "%s-%d", $this->getBackupName (), count ( $volumes ) );
$volumes [] = $archive_path;
$archive->setFileName ( $archive_path );
$reset_timer = true;
}
if (false !== $arcname && DIRECTORY_SEPARATOR != substr ( $file, - 1 )) {
$this->getVerbosity ( VERBOSE_FULL ) && $this->logOutputTimestamp ( sprintf ( _esc ( "adding %s" ), $file ), BULLET );
if (false !== $archive->addFile ( $file, str_replace ( '..' . DIRECTORY_SEPARATOR, '', $file ) )) {
$fcount ++;
$fsize += $fs;
}
}
$this->onProgress ( SRCFILE_SOURCE, $temp_file, $fcount, $file_count, 2, 1, $reset_timer );
}
isset ( $reset_timer ) && $this->onProgress ( SRCFILE_SOURCE, $temp_file, $file_count, $file_count, 2, 1, $reset_timer ); 
fclose ( $handle );
if (false !== $arcname) {
if (count ( $volumes ) > 1) {
$this->logOutputTimestamp ( sprintf ( _esc ( "dumping %s of buffered stream to %s" ), getHumanReadableSize ( $archive_size ), basename ( $archive_path ) ), "[" . count ( $volumes ) . "]" );
} else
$this->logOutputTimestamp ( sprintf ( _esc ( "archive %s created successfully (%d files, %d volumes)" ), getSpan ( basename ( $archive_path ), 'cyan' ), $fcount, count ( $volumes ) ), BULLET );
$this->startCompress ( $archive_path );
$arcname = $archive->compress ( $this->getCompressionMethod (), $this->getCompressionLevel () );
if (false !== $arcname)
$arcs [] = array (
'name' => $arcname,
'arcsize' => $archive->getFileSize (),
'count' => $fcount,
'bytes' => $fsize 
);
}
if (NONE != $this->getCompressionMethod ())
$archive->unlink ();
if ($this->chkProcessSignal ())
return $this->processAbortSignal ( SRCFILE_SOURCE, OPER_COMPRESS_INTERN );
return $arcs;
}
public function onNewArc($arc = null, $uncompressed_size = 0, $compressed_size = 0, $vol_no = 0) {
$this->compressed_files [$vol_no] = array (
'name' => $arc,
'uncompressed' => $uncompressed_size,
'compressed' => $compressed_size 
);
$err_params = $this->getOperErrParams ( $arc, null );
$targets_priority = array (
'FTP_TARGET' => '_upload2Storage',
'DROPBOX_TARGET' => '_upload2Storage', 
'GOOGLE_TARGET' => '_upload2Storage', 
'WEBDAV_TARGET' => '_upload2Storage', 
'SSH_TARGET' => '_upload2Storage', 
'MAIL_TARGET' => '_upload2Email', 
'DISK_TARGET' => '_move2Disk'  // disk has LAST priority because we don't copy but moving the whole temp file
);
if (! empty ( $arc )) {
if (file_exists ( $arc ))
$fs = filesize ( $arc );
else {
$this->outputError ( sprintf ( '<red>' . _esc ( '%s does not exists. Why???' ) . '</red>', basename ( $arc ) ), false, $err_params );
return;
}
try {
if ($fs > 0) {
$options = $this->getOptions ();
! empty ( $options ['encryption'] ) && ($out = $this->encrypt ( $arc )) && unlink ( $arc ) && $arc = $out;
$this->uncompressed += $uncompressed_size;
$this->compressed += $fs;
$ratio = $uncompressed_size / $fs;
$err_params ['METRIC_ACTION'] = 'METRIC_ACTION_COMPRESS';
$this->stopCompress ( $arc, $uncompressed_size, $ratio, $vol_no );
$saved = false;
$err_params ['METRIC_ACTION'] = 'METRIC_ACTION_TRANSFER';
foreach ( $targets_priority as $target => $target_func ) {
if ((- 100 != ($target = $this->getTargetConstant ( $target ))) && null != ($found_target = $this->getTarget ( $target )) && $found_target->isEnabled ()) {
$this->addTargetCount ();
list ( $target_name, $oper_send, $oper_sent ) = $this->getTargetOperConsts ( $target );
$err_params ['METRIC_OPERATION'] = $oper_send;
$sent = $this->$target_func ( $arc, $target, ! in_array ( $target, array (
$this->getTargetConstant ( 'WEBDAV_TARGET' ),
$this->getTargetConstant ( 'DROPBOX_TARGET' ),
$this->getTargetConstant ( 'GOOGLE_TARGET' ) 
) ) );
$this->addFailedCount ( $sent );
$saved = $saved || $sent;
if (! $sent) {
$errmsg = error_get_last ();
if (null != $errmsg)
$this->outputError ( sprintf ( '<red>%s</red>', $errmsg ['message'] ), false, $err_params );
}
}
}
$sufix = $saved ? _esc ( 'although you have enabled at least one' ) : _esc ( 'due to no backup target selected' );
if (! $saved && (null !== ($job_id = $this->getCurrentJobId ()) || $job_id >= 0) && 0 == $this->getTargetCount ())
$this->outputError ( '<red>' . sprintf ( _esc ( "It seems that the file %s has not been copied to any location %s." ), $arc, $sufix ) . '</red> Please do it manually.' );
else if (file_exists ( $arc ) && NONE != $this->getCompressionMethod ())
unlink ( $arc );
} else
$this->outputError ( sprintf ( _esc ( "%s skipped due to null filesize" ), basename ( $arc ) ), false, $err_params );
} catch ( MyException $e ) {
$this->outputError ( formatErrMsg ( $e ), false, $err_params );
}
} else
$this->outputError ( sprintf ( _esc ( "<red>Internal error: onNewArc() called without arguments.</red>" ), basename ( $arc ) ), false, $err_params );
}
public function onMySqlMaint($table_name, $cmd, $msg_type, $msg_text) {
$prefix = '';
$sufix = '';
if ('function' == $msg_type) {
$multiplier = 1;
$msg = sprintf ( _esc ( 'Executing %s for the tables %s' ), $cmd, $table_name );
} elseif ($this->getVerbosity ( VERBOSE_FULL )) {
if ('status' == $msg_type && 'prepare' == $msg_text) {
$multiplier = 2;
$msg = sprintf ( _esc ( 'Preparing to %s the table %s' ), $cmd, $table_name );
} elseif ('prepare' != $msg_text) {
$multiplier = 3;
if ('note' == $msg_type || 'warning' == $msg_type) {
$prefix = '<yellow>';
$sufix = '</yellow>';
} elseif ('error' == $msg_type) {
$prefix = '<red>';
$sufix = '</red>';
} else {
$prefix = _esc ( 'status => ' );
}
$msg = $prefix . $msg_text . $sufix;
}
} else
return;
$this->logOutputTimestamp ( $msg, BULLET, $multiplier );
}
public function run($job_type = JOB_BACKUP) {
parent::run ( $job_type );
$arclist = array ();
$start = time ();
$cleaned = 0;
$this->uncompressed = 0;
$this->compressed = 0;
$ok_status = _esc ( 'successfully' );
$status = _esc ( 'unknown' );
$prefix = '';
$sufix = '';
if (is_cli ())
$this->logSeparator ();
try {
if ($this->getTarget ( MYSQL_SOURCE )->isEnabled ()) {
$arc = $this->_createMySqlBackup ();
if (is_array ( $arc ))
$arclist = array_merge ( $arclist, $arc );
$this->getProgressManager ()->cleanUp ();
}
$arc = $this->_createFileBackup ();
if (false !== $arc) {
$arclist = array_merge ( $arclist, $arc );
$do_cleanup = false;
for($i = DISK_TARGET; $i <= SSH_TARGET; $i ++)
$do_cleanup = $do_cleanup || (null !== ($target = $this->getTarget ( $i )) && $target->isEnabled ());
if ($do_cleanup) {
$this->_uploadOrphans ();
$this->_cleanupOrphans ();
global $BACKUP_TARGETS;
foreach ( array_keys ( $BACKUP_TARGETS ) as $target )
null != ($target_obj = $this->getTarget ( $target )) && $target_obj->isEnabled () && $cleaned += $this->_cleanUpOldArchives ( $target );
}
$status = $ok_status;
$this->setError ( null );
}
} catch ( MyException $e ) {
$status = _esc ( 'with errors:<br>' ) . $e->getMessage ();
$this->setError ( $e );
$arclist = false;
}
$elapsed_time = time () - $start;
if (count ( $arclist ) > 0) {
$this->onJobEnds ( array (
'duration' => $elapsed_time,
'avg_cpu' => get_system_load ( $elapsed_time ) 
) );
if ($ok_status != $status) {
$prefix = '<red>';
$sufix = '</red>';
} else {
$prefix = '<white>';
$sufix = '</white>';
}
}
if ($this->chkProcessSignal ()) {
$status = _esc ( 'with abort signal.' );
$prefix = '<yellow>';
$sufix = '</yellow>';
$this->ackProcessSignal ();
}
$cleaned > 0 && $this->logOutput ( sprintf ( _esc ( "<br><b>TOTAL</b> : %d old files cleaned-up" ), $cleaned ) );
$this->logSeparator ();
$job_summary_str = $prefix . sprintf ( _esc ( '<b>Job finished %s</b>.<br>Total elapsed time %s' ), $status, date ( 'H:i:s', $elapsed_time ) );
$ok_status == $status && $job_summary_str .= sprintf ( _esc ( '<br>Average compression ratio %.2fx' ), 0 != $this->compressed ? $this->uncompressed / $this->compressed : 0 );
$job_summary_str .= $sufix;
$this->logOutput ( $job_summary_str );
$this->logSeparator ();
$this->sendEmailReport ();
$this->logSeparator ();
$this->logOutput ( sprintf ( '<div class="hintbox rounded-container"><black>' . _esc ( 'Do not forget to test your backups regularly. This step is as important as the backup itself.' ) . ' ' . readMoreHere ( 'http://www.taobackup.com/testing_info.html' ) . '.</black></div>' ) );
$this->logSeparator ();
$this->printJobSection ( false, true ); 
$job_id = $this->getCurrentJobId ();
$this->addMessage ( $ok_status == $status ? MESSAGE_TYPE_NORMAL : MESSAGE_TYPE_WARNING, sprintf ( _esc ( 'New backup job run by %s (%s)' ), $this->getSender (), $status ), empty ( $job_id ) ? 0 : $job_id );
return 0 === count ( $arclist ) ? false : $arclist;
}
public function runMySQLMaintenance() {
global $wpdb;
$db_prefix = is_wp () ? $wpdb->base_prefix : '';
$job_id = JOB_MYSQL_MAINT;
$this->setJobId ( $job_id );
if ($this->chkProcessSignal ( $job_id ))
return $this->processAbortSignal ( OPER_MAINT_MYSQL, $job_id ); 
echo "<!--[job_id:$job_id]-->"; 
$this->logOutputTimestamp ( _esc ( "<b>Running MySQL maintenance task</b>" ) );
$target = $this->getTarget ( MYSQL_SOURCE );
$params = $target->getParams ();
$pattern = $db_prefix . $params ['tables'];
$mysqlbkp = new MySQLBackupHandler ( $target->getOptions () );
$tables = $mysqlbkp->getTableNameFromPattern ( $pattern );
$this->onMySqlMaint ( $pattern, 'table maintenance', 'function', 'prepare' );
$result = $mysqlbkp->execTableMaintenance ( $tables, array (
$this,
'onMySqlMaint' 
), array (
$this,
'onProgress' 
), array (
$this,
'chkProcessSignal' 
) );
$this->logOutputTimestamp ( sprintf ( _esc ( 'In total %d tables have been checked.' ), count ( $result ) ), BULLET );
$msg = $this->_getMySqlMaintenanceStatus ( $result );
if (! empty ( $msg ))
$this->logOutput ( $msg );
$status = _esc ( 'successfully' );
$prefix = '';
$sufix = '';
if (0 == count ( $result ) || ! empty ( $msg )) {
$status = _esc ( 'with errors' );
$prefix = '<red>';
$sufix = '</red>';
}
if ($this->chkProcessSignal ( $job_id )) {
$status = _esc ( 'with abort signal' );
$prefix = '<yellow>';
$sufix = '</yellow>';
$this->ackProcessSignal ( $job_id );
}
$this->getProgressManager ()->cleanUp ();
$this->logOutputTimestamp ( sprintf ( _esc ( '%s<b>Task completed %s</b>.%s' ), $prefix, $status, $sufix ), BULLET, 0 );
}
public function getCompressedFiles() {
return $this->compressed_files;
}
public function setBackupMode($mode) {
}
}
?>