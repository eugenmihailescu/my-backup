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
 * @version : 0.2.3-30 $
 * @commit  : 11b68819d76b3ad1fed1c955cefe675ac23d8def $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Fri Mar 18 17:18:30 2016 +0100 $
 * @file    : AbstractJob.php $
 * 
 * @id      : AbstractJob.php | Fri Mar 18 17:18:30 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

include_once FUNCTIONS_PATH . 'utils.php';
class AbstractJob {
private $dropbox, $ftp, $disk, $mysql, $google, $webdav, $ssh, $email;
private $_request_timeout;
private $size;
private $dir, $exclude_dirs, $exclude_ext, $exclude_files, $exclude_links, $cygwin, $ftpdirsep, $extractforcebly;
private $mysqldump;
private $_email;
private $_name;
private $_name_pattern;
private $_url;
private $_wrkdir;
private $_logfile;
private $log_buffer;
private $show_files;
private $show_dirs;
private $show_output;
private $quiet;
private $bzipver;
private $method;
private $level;
private $toolchain;
private $cpusleep;
private $_process_signal;
private $_volumes_count;
private $_files_count;
private $_failed_count;
private $_added_targets;
private $_target_count;
private $error;
private $transfer_start;
private $_progress_manager;
private $flock, $flock_file, $can_unlock;
private $_mode;
protected $_job_starttime;
protected $_history_enabled;
protected $_cpu_peak;
protected $_statistics_manager;
protected $sender;
protected $options;
protected $is_cli;
protected $_job_type;
protected $_jobs_id;
protected $_job_state;
protected $_job_status;
protected $process_data;
protected $nocompress;
function __construct( $opts = null, $sender = null ) {
$this->_job_state = 2;
$this->_job_status = 0;
$this->_job_starttime = time();
$this->quiet = false;
$this->options = $opts;
$this->sender = $sender;
$this->flock_file = JOBS_LOCK_FILE;
$this->process_data = null;
$this->_volumes_count = 0;
$this->_files_count = 0;
$this->_failed_count = 0;
$this->_added_targets = array();
$this->_cpu_peak = 0;
$this->error = null;
$this->log_buffer = '';
$this->_logfile = new LogFile( OUTPUT_LOGFILE, $opts );
$this->show_files = getParam( $opts, 'verbose', null ) == VERBOSE_FULL;
$this->show_dirs = $this->show_files || getParam( $opts, 'verbose', null ) == VERBOSE_COMPACT;
$this->show_output = $this->show_dirs || getParam( $opts, "verbose", null ) == VERBOSE_MINIMAL;
$this->is_cli = is_cli();
$this->_jobs_id = null;
$this->_mode = getParam( $opts, 'mode', BACKUP_MODE_FULL );
$this->_wrkdir = getParam( $opts, 'wrkdir', _sys_get_temp_dir() );
$this->_wrkdir = addTrailingSlash( $this->_wrkdir );
$this->bzipver = getParam( $opts, 'bzipver' );
$this->method = $this->_getTarFilter();
$this->level =  $this->_getLevel( 9 ); 
$this->toolchain = ! defined( __NAMESPACE__.'\\OPER_COMPRESS_EXTERN' ) ? 'intern' : getParam( $opts, 'toolchain', 'intern' );
$this->cpusleep = defined( __NAMESPACE__.'\\CPU_THROTTLING' ) && CPU_THROTTLING ? getParam( $opts, 'cpusleep', 0 ) : 0;
$this->_email = getParam( $opts, "email" );
$this->_url = getParam( $opts, 'url', 'backup' );
$this->_name = getParam( $opts, 'name' );
if ( null == $this->_name || TRUE === $this->_name ) {
$this->_name_pattern = $this->_url . '-';
$this->_name = $this->getWorkDir() . sprintf( "%s%s", $this->_name_pattern, date( "Ymd-His" ) );
} else {
$this->_name_pattern = $this->_name;
$this->_name = $this->getWorkDir() . $this->_name_pattern;
}
$this->_process_signal = array();
$this->_history_enabled = ! defined( __NAMESPACE__.'\\APP_JOB_HISTORY' ) ? true : getParam( $opts, 'history_enabled', false );
if ( $this->_history_enabled ) {
$this->_statistics_manager = getJobsStatManager( $opts );
} else
$this->_statistics_manager = null;
$this->_progress_manager = new ProgressManager( PROGRESS_LOGFILE );
$this->_progress_manager->setLazyWrite( PROGRESS_LAZYWRITE );
$this->setOptions( $opts );
@set_time_limit( $opts['max_exec_time'] );
$this->_lockSession( $this->flock_file );
if ( getMemoryLimit() < $opts['memory_limit'] * MB ) {
ini_set( 'memory_limit', $opts['memory_limit'] . 'M' ); 
}
}
function __destruct() {
$this->un_lockSession();
}
protected function getEnabledTargets( $targets ) {
$enabled = false;
foreach ( $targets as $target )
if ( - 100 != ( $c = $this->getTargetConstant( $target ) ) )
$enabled = $enabled || $this->getTarget( $c )->isEnabled();
return $enabled;
}
public function getTargetConstant( $target_name ) {
$result = - 100;
$constants = get_defined_constants();
if ( isset( $constants[__NAMESPACE__ . '\\' . $target_name] ) )
$result = $constants[__NAMESPACE__ . '\\' . $target_name];
else 
if ( isset( $constants[$target_name] ) )
$result = $constants[$target_name];
return $result;
}
private function setOptions( $opts ) {
global $COMPRESSION_NAMES, $exclude_files_factory;
$this->_request_timeout = isNull( $opts, 'request_timeout', 30 );
$this->dir = getParam( $opts, 'dir', __DIR__ );
$this->dir = addTrailingSlash( $this->dir );
$this->exclude_dirs = ( $o = getParam( $opts, "excludedirs" ) ) ? explode( ",", $o ) : array();
$this->exclude_dirs = array_map( __NAMESPACE__ . '\\delTrailingSlash', $this->exclude_dirs );
$this->exclude_dirs = array_filter( $this->exclude_dirs, function ( $item ) {
return ! empty( $item );
} );
$this->exclude_ext = ( $o = getParam( $opts, "excludeext", implode( ',', $COMPRESSION_NAMES ) ) ) ? explode( 
",", 
$o ) : array();
$this->nocompress = ( $o = getParam( $opts, "nocompress", null ) ) ? explode( ",", $o ) : array();
$this->exclude_files = ( $o = getParam( $opts, "excludefiles", null ) ) ? explode( ",", $o ) : array();
$this->exclude_links = strToBool( getParam( $opts, "excludelinks" ) );
foreach ( $this->exclude_files as $key => $value )
if ( in_array( $value, $exclude_files_factory ) )
$this->exclude_files[$key] = @constant( 
__NAMESPACE__ . '\\' . substr( $value, 1, strlen( $value ) - 2 ) );
$this->extractforcebly = strToBool( getParam( $opts, 'extractforcebly' ) );
$this->size = getParam( $opts, 'size', 0 );
if ( true === $this->size )
$this->size = 0;
if ( isset( $opts['google_path_id'] ) && preg_match_all( '/[\w\d]+/', $opts['google_path_id'], $file_id ) )
$opts['google'] = end( $file_id[0] );
$target_defs = array( 
'mysql' => array( 'tables', 'mysql_format', 'mysql_db' ), 
'dropbox' => null, 
'google' => null, 
'disk' => null, 
'ftp' => array( 'ftphost', 'ftpport', 'ftpuser', 'ftppwd', 'ftppasv' ), 
'webdav' => null, 
'ssh' => array( 
'sshhost', 
'sshport', 
'sshuser', 
'sshpwd', 
'sshproto', 
'ssh_publickey_file', 
'ssh_privkey_file', 
'ssh_privkey_pwd' ), 
'email' => null );
foreach ( $target_defs as $target_name => $target_params ) {
$this->$target_name = new AbstractTarget( $target_name, $opts, $target_params );
$this->$target_name->setEnabled( strToBool( getParam( $opts, $target_name . '_enabled', false ) ) );
if ( 'email' == $target_name ) {
$this->$target_name->setSizeLimit( getUploadLimit() );
$this->$target_name->setEnabled( strToBool( getParam( $opts, 'backup2mail', false ) ) );
$this->$target_name->setPath( getParam( $opts, 'backup2mail_address', $this->getNotificationEmail() ) );
}
}
if ( $this->is_cli ) {
$this->mysql->setEnabled( ! empty( $opts['tables'] ) );
$this->ftp->setEnabled( ! empty( $opts['ftphost'] ) && ! empty( $opts['ftpuser'] ) );
$this->ssh->setEnabled( ! empty( $opts['sshhost'] ) && ! empty( $opts['sshuser'] ) );
}
$options_params = array( 
'mysqldump' => false, 
'cygwin' => null, 
'ftpdirsep' => null, 
'logdir' => defined( __NAMESPACE__.'\\LOG_DIR' ) ? LOG_DIR : _sys_get_temp_dir(), 
'logrotate' => false, 
'logsize' => 1 );
foreach ( $options_params as $op_key => $op_value )
$this->$op_key = getParam( $opts, $op_key, $op_value );
}
private function getTransferStartStr( $operation, $filename, $path, $protocol = '', $secure = false, $filesize = -1 ) {
$secure_str = $secure ? _esc( 'securely' ) : '';
$secure_proto = $secure ? 's' : '';
$download = false;
switch ( $operation ) {
case OPER_CLEANUP_OLDIES :
$opstr = _esc( 'cleaning-up' );
break;
case OPER_SEND_FTP :
$opstr = _esc( 'uploading' );
$protocol = 'ftp' . $secure_proto;
break;
case OPER_GET_FTP :
$opstr = _esc( 'downloading' );
$protocol = 'ftp' . $secure_proto;
$download = true;
break;
case OPER_SEND_SSH :
$opstr = _esc( 'copying' );
$protocol = 'ssh';
break;
case OPER_GET_SSH :
$opstr = _esc( 'downloading' );
$protocol = 'ssh';
$download = true;
break;
case OPER_SEND_WEBDAV :
$opstr = _esc( 'uploading' );
$protocol = 'webdav';
break;
case OPER_GET_WEBDAV :
$opstr = _esc( 'downloading' );
$protocol = 'webdav';
$download = true;
break;
case OPER_SEND_DROPBOX :
$opstr = _esc( 'uploading' );
$protocol = 'dropbox';
break;
case OPER_GET_DROPBOX :
$opstr = _esc( 'downloading' );
$protocol = 'dropbox';
$download = true;
break;
case OPER_SEND_GOOGLE :
$opstr = _esc( 'uploading' );
$protocol = 'google';
break;
case OPER_GET_GOOGLE :
$opstr = _esc( 'downloading' );
$protocol = 'google';
$download = true;
break;
case OPER_SEND_EMAIL :
$opstr = _esc( 'sending' );
$protocol = 'smtp';
break;
case OPER_GET_DISK :
$download = true;
default :
$opstr = _esc( 'copying' );
$protocol = 'disk';
break;
}
if ( 4 == ( $operation % 100 & 4 ) || 6 == ( $operation % 100 & 6 ) ) 
$secure_str = _esc( 'securely' );
if ( $operation < 0 )
return sprintf( 
'%s %s %s ' . _esc( 'on' ) . ' %s%s', 
$opstr, 
$secure_str, 
getSpan( basename( $filename ), 'cyan' ), 
getSpan( $protocol . '://', 'mangenta' ), 
shorten_path( $path ) );
return sprintf( 
'%s %s %s (%s) %s %s%s', 
$opstr, 
$secure_str, 
getSpan( basename( $filename ), 'cyan' ), 
getHumanReadableSize( $filesize < 0 ? filesize( $filename ) : $filesize ), 
$download ? 'from' : 'to', 
getSpan( $protocol . '://', 'magenta' ), 
shorten_path( $path ) );
}
private function getTransferEndStr( $operation, $filename, $filesize = -1, $start, $end ) {
$sec = $end - $start;
$filesize < 0 && _file_exists( $filename ) && $filesize = filesize( $filename );
$rate = $sec > 0 ? $filesize / $sec : - 1;
switch ( $operation ) {
case OPER_CLEANUP_OLDIES :
$opstr = _esc( "cleaned-up" );
break;
case OPER_SENT_EMAIL :
$opstr = _esc( "sent" );
break;
case OPER_SENT_FTP :
case OPER_SENT_SSH :
case OPER_SENT_DROPBOX :
case OPER_SENT_GOOGLE :
case OPER_SENT_WEBDAV :
$opstr = _esc( "uploaded" );
break;
case OPER_GOT_FTP :
case OPER_GOT_SSH :
case OPER_GOT_DROPBOX :
case OPER_GOT_GOOGLE :
case OPER_GOT_WEBDAV :
$opstr = _esc( "downloaded" );
break;
default :
$opstr = _esc( "copied" );
break;
}
if ( $operation < 0 )
return sprintf( _esc( 'file %s successfully' ), $opstr );
$duration = $rate < 0 ? ( _esc( 'less than' ) . ' 1s' ) : ( getHumanReadableSize( $rate ) . '/s' );
return sprintf( _esc( 'file %s successfully (%s)' ), $opstr, $duration );
}
private function _getLevel( $default = 9 ) {
for ( $i = 0; $i < 10; $i++ ) {
$found = getParam( $this->options, "$i", null );
if ( TRUE === $found )
break;
}
return TRUE === $found ? $i : $default;
}
private function _getLastJobId() {
$jobs_log = new LogFile( JOBS_LOGFILE, $this->options );
return $jobs_log->getLastJobId();
}
private function _lockSession() {
$this->can_unlock = false;
if ( _file_exists( $this->flock_file ) )
$ftime = filemtime( $this->flock_file );
else
$ftime = 0;
$this->flock = fopen( $this->flock_file, 'wb' ) or ( file_put_contents( 
JOBS_LOGFILE, 
sprintf( _esc( "[%s] Cannot create lock file" ) . PHP_EOL, date( DATETIME_FORMAT ), BULLET, 2 ), 
FILE_APPEND ) and die( _esc( 'Cannot create lock file' ) ) );
if ( ! $this->flock || ! flock( $this->flock, LOCK_EX | LOCK_NB, $eWouldBlock ) || $eWouldBlock ) {
$last_job_id = $this->_getLastJobId();
$last_job_str = false !== $last_job_id ? 'job id #' . $last_job_id[2] : _esc( 'last job' );
$last_job_timestamp = false !== $last_job_id ? strtotime( $last_job_id[1] ) : $ftime;
$msg = sprintf( 
_esc( "Access denied due to concurrent job (%s started %d seconds ago)" ), 
$last_job_str, 
time() - $last_job_timestamp );
file_put_contents( JOBS_LOGFILE, sprintf( "[%s] $msg" . PHP_EOL, date( DATETIME_FORMAT ) ), FILE_APPEND );
echo $msg;
exit( - 1 );
}
$this->can_unlock = true;
}
protected function getLastJob( $mode = BACKUP_MODE_FULL, $job_type = JOB_BACKUP ) {
if ( null !== $this->_statistics_manager ) {
if ( $rst = $this->_statistics_manager->queryData( 
'SELECT id,started_time FROM ' . TBL_PREFIX . TBL_JOBS . ' WHERE job_type=' . $job_type .
( false !== $mode ? ' AND mode=' . $mode : '' ) . ' ORDER BY started_time DESC LIMIT 1;' ) ) {
$row = $this->_statistics_manager->fetchArray( $rst );
$this->_statistics_manager->freeResult( $rst );
return $row[0];
}
}
return false;
}
protected function flushBuffer() {
flush();
while ( ob_get_level() > 0 && @ob_end_flush() )
;
_usleep( 50000 ); 
}
protected function getSearchFilter( $target, $filter, $exact_match = false ) {
switch ( $target ) {
default :
$result = $filter;
break;
case $this->getTargetConstant( 'GOOGLE_TARGET' ) :
$result = 'title ' . ( $exact_match ? '=' : 'contains' ) . ' \'' . $filter . '\'';
break;
}
return $result;
}
protected function getVolumeSize() {
return $this->size;
}
protected function getExcludedDirs() {
return $this->exclude_dirs;
}
protected function getExcludedExt() {
return $this->exclude_ext;
}
protected function getExcludedFiles() {
return $this->exclude_files;
}
protected function getExcludedLinks() {
return $this->exclude_links;
}
protected function getExtractForcebly() {
return $this->extractforcebly;
}
protected function getTarget( $name ) {
$result = null;
switch ( $name ) {
case FTP_TARGET :
$result = $this->ftp;
break;
case SSH_TARGET :
$result = $this->ssh;
break;
case DISK_TARGET :
$result = $this->disk;
break;
case $this->getTargetConstant( 'DROPBOX_TARGET' ) :
$result = $this->dropbox;
break;
case $this->getTargetConstant( 'GOOGLE_TARGET' ) :
$result = $this->google;
break;
case $this->getTargetConstant( 'WEBDAV_TARGET' ) :
$result = $this->webdav;
break;
case MAIL_TARGET :
$result = $this->email;
break;
case MYSQL_SOURCE :
$result = $this->mysql;
break;
}
return $result;
}
protected function getCygwin() {
return defined( __NAMESPACE__.'\\OPER_COMPRESS_EXTERN' ) ? $this->cygwin : false;
}
protected function _getRequestTimeout() {
return $this->_request_timeout;
}
protected function getCompressionName() {
global $COMPRESSION_APPS;
return $COMPRESSION_APPS[$this->getCompressionMethod()];
}
protected function getEncryption() {
return isset( $this->options['encryption'] ) ? $this->options['encryption'] : '';
}
protected function getMySqlDump() {
return defined( __NAMESPACE__.'\\MYSQL_DUMP' ) ? $this->mysqldump : false;
}
protected function getBackupName() {
return $this->_name;
}
protected function getBackupMode() {
return $this->_mode;
}
protected function getNotificationEmail() {
return $this->_email;
}
protected function getNamePattern() {
return $this->_name_pattern;
}
protected function logSeparator() {
$this->logOutput( "<hr>" );
}
protected function _getBackupMode() {
global $BACKUP_MODE;
return $BACKUP_MODE[$this->_mode];
}
protected function getTool() {
return $this->toolchain;
}
protected function _getToolchain() {
return sprintf( "%s (%s)", 'intern' == $this->getTool() ? WPMYBACKUP : PHP_OS, $this->getTool() );
}
protected function getCPUSleep() {
return defined( __NAMESPACE__.'\\CPU_THROTTLING' ) && CPU_THROTTLING ? $this->cpusleep : 0;
}
protected function _initProcessSignal( $sender = null ) {
$listen_signals = array( PROCESS_BACKUP, PROCESS_GUI_BACKUP, PROCESS_TRANSFER, PROCESS_MYSQL_MAINT );
defined( __NAMESPACE__.'\\PROCESS_CUI_BACKUP' ) && $listen_signals[] = PROCESS_CUI_BACKUP;
defined( __NAMESPACE__.'\\PROCESS_BENCHMARK' ) && $listen_signals[] = PROCESS_BENCHMARK;
foreach ( $listen_signals as $process_name )
ackProcessSignal( $process_name, $sender );
}
protected function ackProcessSignal( $sender = null ) {
$this->_initProcessSignal( $sender );
}
protected function _beforeRun() {
if ( get_system_load() > SYST_USAGE_THRESOLD ) {
die( _esc( 'Server too busy. Please try again later.' ) );
}
$this->_initProcessSignal();
}
protected function _onNewJobStarts( $job_type = JOB_BACKUP ) {
$job_id = 0;
if ( ( 0 == $job_type % 4 ) && null !== $this->_statistics_manager ) {
$keys = array();
isset( $this->options['encryption'] ) && $keys['cipher'] = $this->options['encryption'];
isset( $this->options['encryption_key'] ) && $keys['key'] = $this->options['encryption_key'];
isset( $this->options['encryption_iv'] ) && $keys['iv'] = $this->options['encryption_iv'];
$this->setJobId( 
$this->_statistics_manager->onNewJobStarts( $job_type, $this->_mode, $keys, $this->_job_starttime ) );
$job_id = $this->_jobs_id;
}
file_put_contents( 
JOBS_LOGFILE, 
sprintf( 
_esc( "[%s] Running backup %s (job_id: %d)" ) . PHP_EOL, 
date( DATETIME_FORMAT ), 
$this->sender, 
$job_id ), 
FILE_APPEND );
$this->printJobSection();
$this->printJobSettings( $job_type, $this->_mode );
}
protected function stopCompress( $filename, $uncompressed, $ratio, $vol_no = 0, $decompress = false ) {
$duration = time() - $this->compress_start;
$this->_cpu_peak = max( $this->_cpu_peak, get_system_load( $duration ) );
$fs = getHumanReadableSize( @filesize( $filename ) );
$us = $uncompressed > 0 ? getHumanReadableSize( $uncompressed ) : _esc( 'unknown bytes' );
$this->logOutputTimestamp( 
sprintf( 
"<yellow><b>" . _esc( 'TAR volume(#%d) %s successfully (%s @ ratio %.2f => %s)' ) . "</b></yellow>", 
$vol_no, 
$decompress ? _esc( 'decompressed' ) : _esc( 'compressed' ), 
$decompress ? $fs : $us, 
$ratio > 0 ? sprintf( '%.2f', $ratio ) : _esc( 'unknown' ), 
$decompress ? $us : $fs ), 
BULLET, 
2 );
if ( null !== $this->_statistics_manager ) {
$stat_data = array( 
'METRIC_ACTION' => $decompress ? 'METRIC_ACTION_UNCOMPRESS' : 'METRIC_ACTION_COMPRESS', 
'METRIC_OPERATION' => 'intern' == $this->getTool() ? OPER_COMPRESS_INTERN : OPER_COMPRESS_EXTERN, 
'METRIC_FILENAME' => $filename, 
'METRIC_UNCOMPRESSED' => $uncompressed, 
'METRIC_RATIO' => $ratio, 
'METRIC_TIME' => $duration, 
'METRIC_SIZE' => ( _is_file( $filename ) ? @filesize( $filename ) : 0 ), 
'METRIC_DISK_FREE' => _disk_free_space( $this->getWorkDir() ), 
'JOBTBL_FILE_CHECKSUM' => file_checksum( $filename ), 
'METRIC_SOURCE' => SRCFILE_SOURCE, 
'METRIC_SOURCEPATH' => $decompress ? dirname( $filename ) : $this->getSourceDir() );
is_array( $this->process_data ) && $stat_data = array_merge( $stat_data, $this->process_data );
$this->_statistics_manager->addJobData( $this->_jobs_id, $stat_data );
}
return $duration;
}
protected function parseResponse( $response ) {
$is_error = function ( $array ) {
return count( $array ) == 2 && isset( $array['code'] ) && isset( $array['message'] );
};
if ( is_string( $response ) && null != ( $json = json_decode( $response, true ) ) && $is_error( $json ) )
$response = $json;
if ( is_array( $response ) && $is_error( $response ) )
throw new MyException( $response['message'], $response['code'] );
return true;
}
protected function getLogBuffer() {
return $this->log_buffer;
}
protected function getVerbosity( $what ) {
$result = false;
switch ( $what ) {
case VERBOSE_FULL :
$result = $this->show_files;
break;
case VERBOSE_COMPACT :
$result = $this->show_dirs;
break;
case VERBOSE_MINIMAL :
$result = $this->show_output;
break;
}
return $result;
}
protected function _getVerbosityStr() {
global $VERBOSITY_MODES;
$array = $VERBOSITY_MODES;
krsort( $array );
foreach ( $array as $mode => $str )
if ( $this->getVerbosity( $mode ) ) {
$result = $str;
break;
}
return $result;
}
protected function _getTarFilter() {
global $COMPRESSION_APPS;
foreach ( $COMPRESSION_APPS as $filter => $name ) {
$opt = getParam( $this->options, 'compression_type' );
if ( ! empty( $name ) && ( null === $opt && null !== getParam( $this->options, $name ) ) ||
( null !== $opt && $filter == $opt ) )
return $filter;
}
return NONE;
}
protected function onJobEnds( $array ) {
$params = array( 
'result_code' => 0, 
'METRIC_COMPRESS_TYPE' => $this->getCompressionMethod(), 
'METRIC_COMPRESS_LEVEL' => $this->getCompressionLevel(), 
'METRIC_CPU_SLEEP' => $this->getCPUSleep(), 
'METRIC_TOOLCHAIN' => $this->getTool(), 
'METRIC_BZIP_VER' => $this->getBZipVersion(), 
'job_status' => 'JOB_STATUS_FINISHED', 
'job_state' => 0 == $this->_failed_count ? 'JOB_STATE_COMPLETED' : ( 0 == $this->getTargetCount() ? 'JOB_STATE_FAILED' : 'JOB_STATE_PARTIAL' ), 
'volumes_count' => $this->getVolCount(), 
'files_count' => $this->_files_count, 
'peak_cpu' => $this->_cpu_peak );
$params = array_merge( $params, $array );
if ( null !== $this->_statistics_manager ) {
$this->_statistics_manager->onJobEnds( $this->_jobs_id, $params );
}
$ps = $this->getProcessSignal();
if ( ! empty( $ps ) )
$this->_initProcessSignal( $ps[0] );
}
protected function addtFileCount( $count = 1 ) {
$this->_files_count += $count;
}
protected function addVolCount( $count = 1 ) {
$this->_volumes_count += $count;
}
protected function getVolCount() {
return $this->_volumes_count;
}
protected function addFailedCount( $success = true ) {
$this->_failed_count += $success ? 0 : 1;
}
protected function getFailedCount() {
return $this->_failed_count;
}
protected function addTargetCount( $target, $count = 1 ) {
isset( $this->_added_targets[$target] ) && ( $this->_added_targets[$target] += $count ) ||
$this->_added_targets[$target] = $count;
}
protected function getTargetCount( $unique = false ) {
return $unique ? count( $this->_added_targets ) : array_sum( $this->_added_targets );
}
protected function getAddedTarget() {
return $this->_added_targets;
}
protected function setError( $error ) {
$this->error = $error;
}
protected function getOptions( $option_name = '', $default = '' ) {
return empty( $option_name ) ? $this->options : isNull( $this->options, $option_name, $default );
}
protected function stopTransfer( 
$operation, 
$filename, 
$filesize, 
$uncompressed = 0, 
$action = 'METRIC_ACTION_TRANSFER' ) {
$end = time();
$duration = $end - $this->transfer_start;
$this->logOutputTimestamp( 
$this->getTransferEndStr( $operation, $filename, $filesize, $this->transfer_start, $end ), 
BULLET, 
2 );
if ( null !== $this->_statistics_manager ) {
$stat_data = array( 
'METRIC_ACTION' => $action, 
'METRIC_OPERATION' => $operation, 
'METRIC_FILENAME' => $filename, 
'METRIC_TIME' => $duration, 
'METRIC_SIZE' => $filesize, 
'METRIC_DISK_FREE' => _disk_free_space( $this->getWorkDir() ) );
$filesize && $stat_data['METRIC_RATIO'] = $uncompressed / $filesize;
$uncompressed && $stat_data['METRIC_UNCOMPRESSED'] = $uncompressed;
isset( $stat_data['JOBTBL_FILE_CHECKSUM'] ) || $stat_data['JOBTBL_FILE_CHECKSUM'] = file_checksum( 
$filename );
is_array( $this->process_data ) && $stat_data = array_merge( $stat_data, $this->process_data );
$this->_statistics_manager->addJobData( $this->_jobs_id, $stat_data );
}
return $duration;
}
public function getProgressManager() {
return $this->_progress_manager;
}
protected function _is_job_aborted( $aborted = false, $target = TMPFILE_SOURCE, $operation = OPER_RESTORE ) {
if ( $aborted = $aborted || $this->chkProcessSignal() )
$this->processAbortSignal( $target, $operation );
return $aborted;
}
protected function _chkOAuthSession( $target_name, &$session, $err_params, $bullet = null, $multiplier = 1 ) {
$auth_path = ROOT_OAUTH_FILE . "$target_name.auth";
if ( _file_exists( $auth_path ) ) {
if ( ! $session->initFromFile( $auth_path ) ) {
$this->outputError( 
sprintf( 
_esc( "<red>[!] Cannot load the%s authentication credentials from %s%s" ), 
ucwords( $target_name ), 
$auth_path, 
"</red>" ), 
false, 
$err_params, 
$bullet, 
$multiplier );
return false;
}
} else {
$this->outputError( 
"<red>[!] " . ucwords( $target_name ) .
_esc( " account not linked yet. Please authenticate within the web interface then try again." ) .
"</red>", 
false, 
$err_params, 
$bullet, 
$multiplier );
return false;
}
return true;
}
protected function getTargetOperConsts( $target, $download = false ) {
global $TARGET_NAMES;
$target_name = $TARGET_NAMES[$target];
$oper_send = @constant( 
__NAMESPACE__ . '\\OPER_' . ( $download ? 'GET' : 'SEND' ) . '_' . strtoupper( $target_name ) );
$oper_sent = @constant( 
__NAMESPACE__ . '\\OPER_' . ( $download ? 'GOT' : 'SENT' ) . '_' . strtoupper( $target_name ) );
return array( $target_name, $oper_send, $oper_sent );
}
protected function getOperErrParams( 
$filename, 
$operation, 
$filesize = -1, 
$download = false, 
$action = 'METRIC_ACTION_TRANSFER' ) {
$result = array( 'METRIC_FILENAME' => $filename, 'METRIC_ACTION' => $action, 'METRIC_OPERATION' => $operation );
( ! $download || $filesize >= 0 ) &&
$result['METRIC_SIZE'] = $filesize >= 0 ? $filesize : ( _file_exists( $filename ) ? filesize( $filename ) : 0 );
return $result;
}
protected function initCloudStorage( $target, $filename, $filesize = -1, $download = false ) {
list( $target_name, $oper_send, $oper_sent ) = $this->getTargetOperConsts( $target, $download );
if ( $this->chkProcessSignal() )
return $this->processAbortSignal( $target, $oper_send );
if ( ! $this->getTarget( $target )->isEnabled() || empty( $filename ) ||
! ( $download || _file_exists( $filename ) ) )
return true;
! $download && $filesize = filesize( $filename );
$metadata = array();
$session_class = null;
$session = null;
$upload_path = $this->getTarget( $target )->getPath();
$session_option = array();
switch ( $target ) {
case $this->getTargetConstant( 'DROPBOX_TARGET' ) :
$session_class = 'DropboxOAuth2Client';
$storage_class = 'DropboxCloudStorage';
$upl_throttle = 'dropbox_throttle';
$upload_path = addTrailingSlash( $upload_path, '/' );
break;
case $this->getTargetConstant( 'GOOGLE_TARGET' ) :
$session_class = 'GoogleOAuth2Client';
$storage_class = 'GoogleCloudStorage';
$upl_throttle = 'google_throttle';
break;
case $this->getTargetConstant( 'WEBDAV_TARGET' ) :
$storage_class = 'WebDAVWebStorage';
$upl_throttle = 'webdav_throttle';
$session = $this->getOptions();
$upload_path = addTrailingSlash( $upload_path, '/' );
break;
}
isset( $this->options[$upl_throttle] ) && $session_option['upl_throttle'] = $this->options[$upl_throttle];
$err_params = $this->getOperErrParams( $filename, $oper_send, $filesize, $download );
if ( null != $session_class ) {
$session_class = __NAMESPACE__ . '\\' . $session_class;
$session = new $session_class();
$session->setProxyURI( OAUTH_PROXY_URL, '' );
$session->setTimeout( $this->_getRequestTimeout() );
$session->curlInitFromArray( $session_option );
if ( ! $this->_chkOAuthSession( $target_name, $session, $err_params ) )
return false;
}
$storage_class = __NAMESPACE__ . '\\' . $storage_class;
$api = new $storage_class( $session );
$api->onBytesSent = array( $target, $filename, $this, 'onBytesSent' );
$api->onBytesReceived = array( $target, $filename, $this, 'onBytesReceived' );
$api->onAbort = array( $this, 'chkProcessSignal' );
return $api;
}
protected function initRemoteStorage( $target, $filename, $filesize = -1, $download = false ) {
list( $target_name, $oper_send, $oper_sent ) = $this->getTargetOperConsts( $target, $download );
if ( $this->chkProcessSignal() )
return $this->processAbortSignal( $target, $oper_send );
if ( ! $this->getTarget( $target )->isEnabled() || empty( $filename ) ||
! ( $download || _file_exists( $filename ) ) )
return true;
$obj_params = $this->getTarget( $target )->getParams();
$path = $this->getTarget( $target )->getPath();
if ( empty( $path ) ||
( isset( $obj_params[$target_name . 'host'] ) && empty( $obj_params[$target_name . 'host'] ) ) ||
( isset( $obj_params[$target_name . 'user'] ) && empty( $obj_params[$target_name . 'user'] ) ) )
return true;
$filesize = ( $filesize >= 0 || $download ) ? $filesize : filesize( $filename );
$err_params = $this->getOperErrParams( $filename, $oper_sent, $filesize, $download );
$path = normalize_path( $path, true );
$api = getFtpObject( $this->getOptions(), SSH_TARGET == $target );
$api->onBytesSent = array( $target, $filename, $this, 'onBytesSent' );
$api->onAbortCallback = array( $this, 'chkProcessSignal' );
return $api;
}
protected function run( $job_type = JOB_BACKUP ) {
$this->_job_type = $job_type;
if ( empty( $this->options ) ) {
ob_start();
printHelp();
$cli_help = ob_get_clean();
throw new MyException( 
sprintf( 
_esc( 'Your settings are empty.%s' ), 
is_cli() ? $cli_help : _esc( 'This should never happen.' ) ) );
}
$this->_added_targets = array();
$this->_beforeRun();
$this->_onNewJobStarts( $job_type );
}
protected function getSender() {
return $this->sender;
}
private function _getCryptor() {
global $registered_ciphres;
$class = '';
foreach ( $registered_ciphres as $cipher_class => $cipher_def )
if ( isset( $cipher_def['items'][$this->options['encryption']] ) ) {
$class = $cipher_class;
break;
}
_file_exists( CRYPT_PATH . "$class.php" ) && include_once CRYPT_PATH . "$class.php";
$class = __NAMESPACE__ . '\\' . $class;
if ( class_exists( $class ) &&
preg_match( '/([a-z]\w*)(-(\w*))?(-(\w*))?/i', $this->options['encryption'], $matches ) ) {
$cipher = $matches[1];
$mode = $matches[5];
try {
$enc = new $class( $cipher, $mode );
! empty( $this->options['encryption_key'] ) &&
$enc->setKey( hextostr( $this->options['encryption_key'] ) );
! empty( $this->options['encryption_iv'] ) && $enc->setIv( hextostr( $this->options['encryption_iv'] ) );
return $enc;
} catch ( MyException $e ) {
$this->outputError( '<red>[!] ' . $e->getMessage() . '</red>', false, null, BULLET, 1 );
}
}
return false;
}
public function decrypt( $filename ) {
return $this->encrypt( $filename, false );
}
protected function encrypt( $filename, $encrypt = true ) {
if ( ! isset( $this->options['encryption'] ) || empty( $this->options['encryption'] ) ||
! ( $enc = $this->_getCryptor() ) )
return false;
$this->logOutputTimestamp( 
sprintf( 
_esc( '%s the file %s with %s' ), 
$encrypt ? _esc( 'encrypting' ) : _esc( 'decrypting' ), 
$filename, 
str_replace( __NAMESPACE__ . '\\', '', get_class( $enc ) ) ), 
BULLET );
$out = $enc->encryptFile( $filename, null, $encrypt );
$this->logOutputTimestamp( false !== $out ? _esc( 'succeeded' ) : _esc( 'failed' ), BULLET, 2 );
return $out;
}
public function getCurrentJobId() {
return $this->_jobs_id;
}
public function getSourceDir() {
return $this->dir;
}
public function setSourceDir( $dir ) {
$this->dir = addTrailingSlash( $dir );
}
public function getError() {
return $this->error;
}
public function logOutput( $str, $bullet = null, $multiplier = 1, $new_line = true ) {
$colors = array( 'white', 'red', 'green', 'yellow', 'black' );
if ( ! $this->quiet && $this->getVerbosity( VERBOSE_MINIMAL ) ) {
if ( null != $bullet )
$str = str_repeat( TAB, $multiplier ) . $bullet . " " . $str;
if ( $new_line )
$str .= "<br>";
foreach ( $colors as $color )
$str = preg_replace( '/<(' . $color . '*)\b[^>]*>(.*?)<\/\1>/i', getSpan( '$2', $color ), $str );
$plain_str = html2Text( $str );
if ( $this->is_cli )
$str = $plain_str;
$this->_logfile->writeLog( $plain_str );
if ( ! isset( $this->options['plugin_backup'] ) || true !== $this->options['plugin_backup'] )
echo $str;
$this->log_buffer .= $plain_str;
$this->flushBuffer();
}
! empty( $plain_str ) && strpos( $plain_str, '[!]' ) &&
$this->addMessage( MESSAGE_TYPE_WARNING, $plain_str, empty( $this->_jobs_id ) ? 0 : $this->_jobs_id );
return true;
}
public function logOutputTimestamp( $str, $bullet = null, $multiplier = 1 ) {
if ( ! is_array( $str ) )
$str = array( $str );
foreach ( $str as $s ) {
if ( null != $bullet )
$s = str_repeat( TAB, $multiplier ) . $bullet . " " . $s;
$this->logOutput( sprintf( '[%s] %s', date( DATETIME_FORMAT ), $s ) );
}
return true;
}
public function setQuiet( $quiet ) {
$this->quiet = $quiet;
}
public function printJobSection( $html_comment = true, $end_section = false ) {
$job_id = null === $this->_jobs_id ? 0 : $this->_jobs_id;
$section = sprintf( "[%sjob_id:%d]", $end_section ? '/' : '', $job_id );
if ( $html_comment )
printf( "<!--%s-->", $section ); 
$this->logOutput( 
sprintf( ( $end_section ? '' : PHP_EOL ) . '%s' . PHP_EOL . ( $end_section ? PHP_EOL : '' ), $section ), 
null, 
1, 
true, 
true ); 
}
public function getWorkDir() {
return $this->_wrkdir;
}
public function getBZipVersion() {
return $this->bzipver;
}
public function getCompressionMethod() {
return intval( $this->method );
}
public function setCompressionMethod( $method ) {
$this->method = $method;
}
public function getCompressionLevel() {
return $this->level;
}
public function getJobTypeStr() {
return JOB_BACKUP == $this->_job_type ? _esc( 'Backup' ) : ( - 4 == $this->_job_type ? _esc( 'Restore' ) : _esc( 
'unknown' ) );
}
public function printJobSettings( $job_type = JOB_BACKUP ) {
global $COMPRESSION_NAMES;
$memory_limit = getMemoryLimit();
$memory_usage = memory_get_usage( true );
if ( _is_dir( $this->getWorkDir() ) )
$disk_free_space = _disk_free_space( $this->getWorkDir() );
else
$disk_free_space = - 1;
$bzipver = $this->getBZipVersion();
$mysq_ext = $this->getOptions( 'mysql_ext', 'mysql' );
$this->logOutputTimestamp( 
sprintf( 
'<b><yellow>' . _esc( "%s job started with %s interface (%s)" ) . '</yellow></b>', 
$this->getJobTypeStr(), 
! empty( $this->sender ) ? $this->sender : ( is_cli() ? "CLI" : "WP-Admin" ), 
APP_VERSION_ID ) );
$this->logOutputTimestamp( _esc( 'OS/PHP' ) . " : " . PHP_OS . '/' . PHP_VERSION, '*', 1 );
$this->logOutputTimestamp( _esc( "ROOT" ) . " : " . ALT_ABSPATH, '*', 1 );
$this->logOutputTimestamp( _esc( "Website URL" ) . " : " . selfURL( true ), '*', 1 );
$this->logOutputTimestamp( _esc( "Working directory" ) . " : " . shorten_path( $this->getWorkDir() ), '*', 1 );
$this->logOutputTimestamp( 
_esc( "Log directory" ) . " : " . shorten_path( getBranchedFileName( LOG_DIR ) ), 
'*', 
1 );
if ( JOB_BACKUP == $job_type ) {
$this->logOutputTimestamp( _esc( "Backup mode" ) . " : " . $this->_getBackupMode(), '*', 1 );
$this->logOutputTimestamp( _esc( "Toolchain" ) . " : " . $this->_getToolchain(), '*', 1 );
$this->logOutputTimestamp( _esc( "Compression type" ) . " : " . $this->getCompressionName(), '*', 1 );
if ( BZ2 === $this->getCompressionMethod() && 'intern' != $this->getTool() )
$this->logOutputTimestamp( _esc( 'version' ) . " : " . $bzipver, BULLET, 2 );
if ( defined( __NAMESPACE__.'\\PCLZIP' ) && PCLZIP === $this->getCompressionMethod() )
$this->logOutputTimestamp( _esc( 'version' ) . " : " . $COMPRESSION_NAMES[PCLZIP], BULLET, 2 );
if ( defined( __NAMESPACE__.'\\ZIP' ) && ZIP === $this->getCompressionMethod() )
$this->logOutputTimestamp( _esc( 'version' ) . " : " . $COMPRESSION_NAMES[ZIP], BULLET, 2 );
$this->logOutputTimestamp( _esc( "Compression level" ) . " : " . $this->getCompressionLevel(), '*', 1 );
$enc = $this->getEncryption();
$this->logOutputTimestamp( 
_esc( "Encryption type" ) . " : " . ( ! empty( $enc ) ? $enc : _esc( 'none' ) ), 
'*', 
1 );
}
if ( 'intern' == $this->getTool() )
$this->logOutputTimestamp( _esc( "CPU sleep" ) . " : " . $this->getCPUSleep() . 'ms', '*', 1 );
$this->logOutputTimestamp( 
_esc( "PHP memory limit" ) . " : " .
( 0 >= $memory_limit ? _esc( 'OS available' ) : getHumanReadableSize( $memory_limit ) ), 
'*', 
1 );
$this->logOutputTimestamp( 
sprintf( 
_esc( "PHP memory footprint" ) . " : %s (~%s => %s %s)", 
getHumanReadableSize( $memory_usage ), 
0 >= $memory_limit ? _esc( 'infinitesimal' ) : sprintf( '%.2f %%', 100 * $memory_usage / $memory_limit ), 
0 >= $memory_limit ? 'OS' : sprintf( '%.2f %%', 100 * ( 1 - $memory_usage / $memory_limit ) ), 
_esc( 'available' ) ), 
'*', 
1 );
$this->logOutputTimestamp( _esc( "Exec time limit" ) . " : " . ini_get( "max_execution_time" ) . 's', '*', 1 );
$this->logOutputTimestamp( 
_esc( "MySQL extension" ) . " : " . ( empty( $mysq_ext ) ? _esc( 'best available' ) : $mysq_ext ), 
'*', 
1 );
$this->logOutputTimestamp( 
_esc( "Disk free space" ) . " : " . getHumanReadableSize( $disk_free_space ), 
'*', 
1 );
$this->logOutputTimestamp( _esc( "Timezone" ) . " : " . wp_get_timezone_string(), '*', 1 );
$this->logOutputTimestamp( _esc( "Verbosity" ) . " : " . $this->_getVerbosityStr(), '*', 1 );
}
public function chkProcessSignal( $sender = null ) {
$listen_signals = array( PROCESS_BACKUP, PROCESS_GUI_BACKUP, PROCESS_TRANSFER, PROCESS_MYSQL_MAINT );
defined( __NAMESPACE__.'\\PROCESS_CUI_BACKUP' ) && $listen_signals[] = PROCESS_CUI_BACKUP;
defined( __NAMESPACE__.'\\PROCESS_BENCHMARK' ) && $listen_signals[] = PROCESS_BENCHMARK;
foreach ( $listen_signals as $signal_id )
if ( false !== ( $signal = chkProcessSignal( $signal_id, $sender ) ) ) {
$this->_process_signal = $signal;
break;
}
return ! empty( $this->_process_signal ) ? $this->_process_signal : false;
}
public function getProcessSignal() {
return $this->_process_signal;
}
public function processAbortSignal( $target, $operation = null ) {
global $TARGET_NAMES;
if ( isset( $TARGET_NAMES[$target] ) )
$target_str = $TARGET_NAMES[$target];
else
$target_str = _esc( 'unknown' );
switch ( $operation ) {
case OPER_COMPRESS_INTERN :
$opstr = _esc( 'compression (internal toolchain)' );
break;
case defined( __NAMESPACE__.'\\OPER_COMPRESS_EXTERN' ) && OPER_COMPRESS_EXTERN :
$opstr = sprintf( _esc( 'compression (%s toolchain)' ), PHP_OS );
break;
case OPER_SRCFILE_BACKUP :
$opstr = _esc( 'backup' );
break;
case OPER_CLEANUP_ORPHAN :
$opstr = _esc( 'orphan clean-up' );
break;
case OPER_SEND_EMAIL :
$opstr = _esc( 'email sending' );
break;
case OPER_MAINT_MYSQL :
$opstr = _esc( 'table maintenance' );
break;
case OPER_RESTORE :
$opstr = _esc( 'restore' );
break;
default :
$opstr = _esc( 'transfer' );
break;
}
$this->logOutputTimestamp( 
"<yellow>[!] $target_str $opstr " . _esc( 'operation canceled due to job abort signal' ) . ".</yellow>" );
return false;
}
public function setJobId( $job_id ) {
$this->_jobs_id = $job_id;
}
public function outputError( $str = null, $no_timestamp = false, $params = null, $bullet = BULLET, $multiplier = 2 ) {
$error_get_last = error_get_last();
if ( empty( $str ) ) {
if ( ! empty( $error_get_last ) )
$msg = sprintf( 
'<red>[!] : %s</red>' . ( defined( __NAMESPACE__.'\\PHP_DEBUG_ON' && PHP_DEBUG_ON ) ? ' (%s:%d)' : '' ), 
$error_get_last['message'], 
basename( $error_get_last['file'] ), 
$error_get_last['line'] );
else
return;
} else
$msg = $str;
if ( ! $no_timestamp )
$this->logOutputTimestamp( $msg, $bullet, $multiplier );
else
$this->logOutput( $msg, $bullet, $multiplier );
$metrics = array( 'METRIC_ERROR' => strip_tags( str_replace( '"', '""', $msg ) ) );
if ( ! empty( $params ) )
$metrics = array_merge( $metrics, $params );
if ( null !== $this->_statistics_manager )
$this->_statistics_manager->addJobData( $this->_jobs_id, $metrics );
$this->addMessage( 
false !== strpos( $str, '<yellow>' ) ? MESSAGE_TYPE_WARNING : MESSAGE_TYPE_ERROR, 
html2Text( $msg ), 
empty( $this->_jobs_id ) ? 0 : $this->_jobs_id );
}
public function startTransfer( $operation, $filename, $path, $protocol = '', $secure = false, $filesize = -1, $array = null ) {
$this->transfer_start = time();
$this->process_data = $array;
$this->logOutputTimestamp( 
$this->getTransferStartStr( $operation, $filename, $path, $protocol, $secure, $filesize ), 
BULLET );
if ( null !== $this->_statistics_manager )
$this->_statistics_manager->addJobPaths( 
$this->_jobs_id, 
array( 'METRIC_OPERATION' => $operation, 'METRIC_MEDIAPATH' => $path ) );
}
public function onBytesSent( $provider, $filename, $bytes, $total_bytes ) {
$this->_progress_manager->setProgress( $provider, $filename, $bytes, $total_bytes, PT_UPLOAD );
}
public function onBytesReceived( $provider, $filename, $bytes, $total_bytes ) {
$this->_progress_manager->setProgress( $provider, $filename, $bytes, $total_bytes, PT_DOWNLOAD );
}
public function un_lockSession() {
if ( $this->can_unlock && null != $this->flock ) {
flock( $this->flock, LOCK_UN );
_file_exists( $this->flock_file ) && @unlink( $this->flock_file );
}
}
public function sendEmailReport( $msg = null ) {
$format_datetime = function ( $str ) {
return preg_replace( 
'/(\[\d{4}\-\d{2}\-\d{2} \d{2}:\d{2}:\d{2}\])/', 
'<span style="color:#777">$1</span>', 
$str );
};
$format_hr = function ( $str ) {
return preg_replace( '/^(\-+)$/m', '<hr>', $str );
};
$format_warnings = function ( $str ) {
return preg_replace( 
'/(' . _esc( 'WARNING' ) . '\s*:)(.*)/mi', 
'<span style="background-color:#FFB600;color:#FFF;font-weight:bold">$1</span>$2', 
$str );
};
$format_errors = function ( $str ) {
return preg_replace( '/(\[!\].+)$/m', '<span style="background-color:#FFC0B5">$1</span>', $str );
};
$format_totals = function ( $str ) {
return preg_replace( 
'/((' . _esc( 'SUBTOTAL' ) . '|' . _esc( 'GRAND TOTAL' ) . ')\s*:)(.*)/im', 
'<span style="background-color:#FCFCAF;color:#000"><strong>$1</strong>$3</span>', 
$str );
};
$format_steps = function ( $str ) {
return preg_replace( 
'/(\[\d+\/\d+\])/', 
'<span style="background-color:#00ADEE;color:#FFF">$1</span>', 
$str );
};
$format_target = function ( $str ) {
global $BACKUP_TARGETS;
return preg_replace( 
'/((' . implode( '|', $BACKUP_TARGETS ) . '):\/\/)/', 
'<span style="background-color:#FFB2FF">$1</span>', 
$str );
};
$format_crlf = function ( $str ) {
return preg_replace( '/^(.*)(?<!<hr>)$/m', '$1<br>', $str );
};
$format_job = function ( $str ) {
return preg_replace( '/(\[\/?job_id:\d+\])/', '<strong>$1</strong>', $str );
};
$job_alerts = function ( $job_id ) {
$result = array();
$mhdl = new MessageHandler( MESSAGES_LOGFILE );
$items = $mhdl->getMessagesByKeys( 
array( 'ref_id', 'type' ), 
array( $job_id, array( MESSAGE_TYPE_WARNING, MESSAGE_TYPE_ERROR ) ) );
foreach ( $items as $msg_id => $msg_item ) {
$value = preg_replace( '/\[[\d\-\s:]+\][\s\-]*/', '', str_replace( PHP_EOL, '', $msg_item->text ) );
in_array( $value, $result ) || $result[] = $value;
}
if ( $count = count( $result ) )
return '<table style="font-family:fixed;font-size:10px;color:#000;width:100%;border:1px solid tomato;border-radius:5px;margin-top:5px;margin-bottom:5px"><tr style="background-color:tomato;color:#FFF"><td style="padding:5px">' .
sprintf( _esc( 'The job generated the following %d alert messages' ), $count ) .
'</td></tr><tr><td>' . implode( '</td></tr><tr><td>', $result ) . '</td></tr></table>';
return '';
};
$sender = str_replace( ' ', '', strtolower( WPMYBACKUP ) );
if ( null !== $this->getNotificationEmail() )
if ( preg_match( "/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6}$/i", $this->getNotificationEmail() ) ) {
$this->logOutput( 
sprintf( _esc( 'Sending this report via e-mail to [%s]' ), $this->getNotificationEmail() ) );
$status_color = '#00BD46';
$status = _esc( 'SUCCESS' );
if ( 1 == $this->_job_state ) {
$status_color = '#FFB600';
$status = _esc( 'PARTIAL' );
} elseif ( 2 == $this->_job_state || 1 == $this->_job_status ) {
$status_color = 'tomato';
$status = 2 == $this->_job_state ? _esc( 'FAILED' ) : _esc( 'ABORTED' );
}
$from = sprintf( '%s@%s', $sender, $this->getBackupName() );
$to = $this->getNotificationEmail();
$subject = sprintf( 
'%s - %s %s', 
WPMYBACKUP, 
strtolower( $this->getJobTypeStr() ), 
preg_replace( '/.*(\/)\1([^\1]+)/', '$2', get_home_url_wrapper() ) );
$plain_text = empty( $msg ) ? $this->getLogBuffer() : $msg;
$title = sprintf( 
_esc( 'Below is the %s log for the %s job #%s (%s)' ), 
WPMYBACKUP, 
strtolower( $this->getJobTypeStr() ), 
$this->_jobs_id, 
selfURL( true ) );
$body = '<div>';
$body .= '<p style="font-size:16px;font-family: Verdana,Georgia,serif !important;">' . $title . '</p>';
$body .= '<p><span style="background-color:' . $status_color .
';color:white;font-weight:bold;padding:3px;">' . _esc( 'Status' ) . ': ' . $status . '</span></p>';
$body_text = ltrim( $plain_text );
foreach ( array( 
$format_job, 
$format_steps, 
$format_hr, 
$format_warnings, 
$format_errors, 
$format_totals, 
$format_target, 
$format_datetime, 
$format_crlf ) as $lambda ) {
$body_text = call_user_func( $lambda, $body_text );
}
$body .= $job_alerts( $this->_jobs_id ) .
'<table style="font-family:fixed;font-size:10px;color:#000;border:1px solid #00adee;border-radius:5px"><tr><td>' .
$body_text . '</td></tr></table>';
$body .= '</div>';
if ( ! sendHtmlFormattedMail( $from, $to, $subject, $body, $plain_text, 3, $this->getOptions() ) )
$this->outputError( sprintf( '<red>%s.</red>', _esc( 'Mail send failed' ) ), true );
} else
$this->outputError( 
sprintf( 
'<red>' . _esc( 'Cannot send e-mail notification due to invalid e-mail address' ) . '</red> [%s]', 
$this->getNotificationEmail() ), 
true );
}
public function onProgress( $provider, $filename, $bytes, $total_bytes, $ptype = PT_DOWNLOAD, $running = 1, $reset_timer = false ) {
$this->getProgressManager()->setProgress( 
$provider, 
$filename, 
$bytes, 
$total_bytes, 
$ptype, 
$running, 
$reset_timer );
}
public function startCompress( $filename, $array = null, $decompress = false ) {
$this->compress_start = time();
$this->process_data = $array;
$this->logOutputTimestamp( 
sprintf( 
_esc( "%s %s with %s filter" ), 
$decompress ? _esc( 'decompressing' ) : _esc( 'compressing' ), 
getSpan( basename( $filename ), 'cyan' ), 
strtoupper( $this->getCompressionName() ) ), 
BULLET );
}
public function addMessage( $type, $text, $ref_id = null, $status = MESSAGE_ITEM_UNREAD ) {
add_alert_message( $text, $ref_id, $type, $status );
}
public function onShutdown() {
$this->un_lockSession();
}
}
?>