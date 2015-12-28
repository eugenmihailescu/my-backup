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
 * @version : 0.2.2-10 $
 * @commit  : dd80d40c9c5cb45f5eda75d6213c678f0618cdf8 $
 * @author  : Eugen Mihailescu <eugenmihailescux@gmail.com> $
 * @date    : Mon Dec 28 17:57:55 2015 +0100 $
 * @file    : job-history-functions.php $
 * 
 * @id      : job-history-functions.php | Mon Dec 28 17:57:55 2015 +0100 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

function echoHistoryContent( $method, $settings = null, $content = false ) {
global $BACKUP_MODE;
echo "<input type='hidden' id='history_provider_nonce' value='" . wp_create_nonce_wrapper( 'abort_job' ) . "'>";
$html_rows = '<table class="files history">';
$html_rows .= sprintf( 
'<th>%s</th><th>Id</th><th>%s</th><th>%s</th><th>%s<br>(x)</th><th>%s<br>(MiB)</th><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>%s<br>(sec)</th>', 
_esc( 'Type' ), 
_esc( 'Mode' ), 
_esc( 'Compressed' ), 
_esc( 'Ratio' ), 
_esc( 'Size' ), 
_esc( 'Volumes' ), 
_esc( 'Files' ), 
_esc( 'Status' ), 
_esc( 'State' ), 
_esc( 'Start time' ), 
_esc( 'End time' ), 
_esc( 'Duration' ) );
if ( $content ) {
$stat_mngr = getJobsStatManager( $settings );
$filter = array();
if ( $content ) {
$timestamp = time();
$limit = 0;
$period = isNull( $method, 'period', false );
$status = isNull( $method, 'status', false );
$state = isNull( $method, 'state', false );
$type = isNull( $method, 'type', false );
$job_id = isNull( $method, 'job_id', false );
$interval_from = isNull( $method, 'from', false );
$interval_to = isNull( $method, 'to', false );
if ( false !== $period ) {
if ( '100' == $period )
$limit = 100;
elseif ( '30' == $period )
$filter[] = 'started_time>=' . ( $timestamp - 30 * 24 * 3600 ) . ' AND started_time<=' . $timestamp;
elseif ( 'interval' == $period ) {
! empty( $interval_from ) && $filter[] = 'started_time>=' . strtotime( $interval_from );
! empty( $interval_to ) && $filter[] = 'started_time<=' . ( strtotime( $interval_to ) + 86399 ); 
}
}
if ( false !== $job_id && is_numeric( $job_id ) )
$filter[] = 'id=' . $job_id;
if ( ! ( false === $type || '' === $type ) )
$filter[] = 'job_type=' . $type;
if ( ! ( false === $status || '' === $status ) )
$filter[] = 'job_status=' . $status;
if ( ! ( false === $state || '' === $state ) )
$filter[] = 'job_state=' . $state;
$filter = implode( ' AND ', $filter );
}
$filter = empty( $filter ) ? '' : ( ' WHERE ' . $filter );
$sql = 'SELECT id,job_type,mode,compression_type,ratio,job_size,volumes_count,files_count,job_status,job_state,started_time,finish_time,duration, (SELECT count(*) as errors FROM ' .
TBL_PREFIX . TBL_STATS . ' WHERE jobs_id=' . TBL_PREFIX . TBL_JOBS .
'.id AND error IS NOT NULL) as errors FROM ' . TBL_PREFIX . TBL_JOBS . ' ' . $filter . ' ORDER BY id DESC';
$sql .= ! $content || 0 !== $limit ? ' LIMIT ' . ( 0 !== $limit ? $limit : 1 ) : '';
$sql .= ';';
$rst = $stat_mngr->queryData( $sql );
$i = 0;
while ( $row = $stat_mngr->fetchArray( $rst ) ) {
$onclick = "
js56816af34b4f1.asyncGetMediaInfo ( " . $row['id'] . ", null );";
$onclick .= "
js56816af34b4f1.asyncGetMediaInfo ( " . $row['id'] . ", 'd' );";
$onclick .= "
js56816af34b4f1.asyncGetMediaInfo ( " . $row['id'] . ", 'sysinfo' );";
$events = array( 'onclick="' . $onclick . '"' );
$bg = '';
switch ( $row['job_status'] ) {
case JOB_STATUS_RUNNING :
if ( time() - $row['started_time'] > LONG_RUNNING_JOB_TIMEOUT ) {
$status = 'suspect';
$bg = 'rgba(255,0,0,0.25)';
$events[] = "oncontextmenu='js56816af34b4f1.onHistoryListContextMenu(event);'";
} else
$bg = '#FFC';
break;
case JOB_STATUS_ABORTED :
$status = 'aborted';
break;
case JOB_STATUS_FINISHED :
$status = 'done';
break;
case JOB_STATUS_SUSPENDED :
$status = "<a class='help' onclick=" . getHelpCall( 
"'" . sprintf( 
_esc( 
'This job was manually suspended. Probably it was a long running job and you opted to abort it forcebly at %s.' ), 
date( DATETIME_FORMAT, $row['finish_time'] ) ) . "'" ) . ">" . _esc( 'suspended' ) . "</a>";
$bg = 'rgba(255,0,0,0.25)';
break;
default :
$status = 'unknown';
$events[] = "oncontextmenu='js56816af34b4f1.onHistoryListContextMenu(event);'";
break;
}
$style = empty( $bg ) ? '' : 'style="background-color:' . $bg . ';"';
$icon = array( JOB_BACKUP => 'backup.png' );
defined( __NAMESPACE__.'\\JOB_RESTORE' ) && $icon[JOB_RESTORE] = 'restore.png';
$html_rows .= '<tr ' . $style . ' ' . implode( ' ', $events ) . '>';
$cols = array( 
'job_type' => $row['job_type'], 
'id' => $row['id'], 
'mode' => $BACKUP_MODE[$row['mode']][0], 
( 0 !== $row['compression_type'] ? 'yes' : 'no' ), 
round( $row['ratio'], 2 ), 
round( $row['job_size'] / MB, 2 ), 
$row['volumes_count'], 
$row['files_count'], 
$status, 
( JOB_STATE_COMPLETED == $row['job_state'] ? 'completed' : ( JOB_STATE_PARTIAL == $row['job_state'] ? 'partial' : 'failed' ) ), 
empty( $row['started_time'] ) ? '' : date( DATETIME_FORMAT, $row['started_time'] ), 
empty( $row['finish_time'] ) ? '' : date( DATETIME_FORMAT, $row['finish_time'] ), 
$row['duration'], 
'errors' => $row['errors'] );
foreach ( $cols as $col_name => $value ) {
$td_title = null;
$style = $col_name === 'id' && $row['errors'] > 0 ? 'style="color:red"' : ( $col_name === 'job_type' ? 'style="background-image:url(' .
plugins_url_wrapper( 'img/' . $icon[$value], IMG_PATH ) .
');background-repeat:no-repeat;background-position:center;"' : '' );
if ( $col_name !== 'errors' ) {
if ( $col_name === 'job_type' ) {
$td_title = $value == JOB_BACKUP ? 'backup' : ( defined( __NAMESPACE__.'\\JOB_RESTORE' ) && $value == JOB_RESTORE ? 'restore' : '' );
$value = '&nbsp;';
}
$html_rows .= '<td ' . $style . ( ! empty( $td_title ) ? 'title="' . $td_title . '"' : '' ) . '>' .
$value . '</td>';
}
}
$html_rows .= '</tr>';
$i++;
}
$stat_mngr->freeResult( $rst );
if ( 0 == $i ) {
if ( false !== $job_id )
if ( $job_id > 0 )
$msg = sprintf( 
_esc( 
"The record you are looking for (job_id %s) does not exist. That`s rather odd!<br>My guess is that the job history was flushed so it doesn't contain anything about this event.<br>If that`s not the case and you suspect a code bug then please %s." ), 
$job_id, 
"<a href='" . getReportIssueURL() . "'>" . _esc( 'fill a report' ) . "</a>" );
else
$msg = _esc( "This was a non-backup job (eg. benchmark). I don't keep history of such things :-(" );
else
$msg = _esc( "No item found :-(" );
$html_rows .= "<tr><td colspan='12'>$msg</td></tr>";
}
}
$html_rows .= '</table>';
echo $html_rows;
}
function echoJobInfo( $params, $settings = null ) {
global $BACKUP_MODE, $COMPRESSION_APPS;
if ( isset( $params['media_info'] ) )
return echoJobMedia( $params, $settings );
if ( ! isset( $params['id'] ) )
return;
$stat_mngr = getJobsStatManager( $settings );
$sql = 'SELECT id,mode,result_code,compression_type,compression_level,toolchain,bzip_ver,cpu_sleep,ratio,job_size,volumes_count,files_count,job_status,job_state,started_time,finish_time,duration,avg_speed,avg_cpu,peak_cpu,peak_disk,peak_mem,unique_id FROM ' .
TBL_PREFIX . TBL_JOBS . ' where id=' . $params['id'];
$rst = $stat_mngr->queryData( $sql );
$row = $stat_mngr->fetchArray( $rst );
$stat_mngr->freeResult( $rst );
if ( false == $row )
return;
$job_status_array = getJobStatusStr( $row['job_status'], $row['started_time'] );
$job_status = $job_status_array[0];
$job_status_style = $job_status_array[1];
$job_state_array = getJobStateStr( $row['job_state'] );
$job_state = $job_state_array[0];
$job_state_style = $job_state_array[1];
$compression = 0 != $row['compression_type'] ? _esc( 'yes' ) : _esc( 'no' );
$compression_type = isset( $COMPRESSION_APPS[$row['compression_type']] ) ? $COMPRESSION_APPS[$row['compression_type']] : _esc( 
'unknown' );
$job_size = round( $row['job_size'] / MB, 2 );
$job_ratio = round( $row['ratio'], 2 );
$job_duration = date( "H:i:s", $row['duration'] );
$job_avg_speed = round( $row['avg_speed'] / MB, 2 );
$job_peak_mem = round( $row['peak_mem'] / MB, 2 );
$job_avg_cpu_style = $row['avg_cpu'] < 0.7 ? 'green' : ( $row['avg_cpu'] < 1 ? '#FF8000' : 'red' );
$job_peak_cpu_style = $row['peak_cpu'] < 0.7 ? 'green' : ( $row['peak_cpu'] < 1 ? '#FF8000' : 'red' );
$job_mode = $BACKUP_MODE[$row['mode']];
$help_1 = _esc( 'This factor gives you the CPU queue load.' );
$help_1 .= sprintf( 
_esc( '%s : your system does fine' ), 
'<ul><li>' . getSpanE( _esc( 'less than' ) . ' 0.7', 'green' ) );
$help_1 .= sprintf( 
_esc( '%s : the system needs your attention' ), 
sprintf( "</li><li>%s %s", _esc( 'between' ), getSpanE( "0.7 " . _esc( 'and' ) . " 1.00", '#FF8000' ) ) );
$help_1 .= sprintf( 
_esc( '%s - the system is busy =&gt; call the cavalry!' ), 
"</li><li>" . getSpanE( _esc( 'over' ) . ' 1.00', 'red' ) );
$help_1 .= sprintf( 
_esc( '%s (for experts) and/or %s (for layman).' ), 
'</li></ul>' . readMoreHereE( 
'http://en.wikipedia.org/wiki/Load_%28computing%29#Reckoning_CPU_load', 
_esc( 'CPU load' ) ), 
getAnchorE( '', 'http://blog.scoutapp.com/articles/2009/07/31/understanding-load-averages' ) );
$help_1 = "'" . $help_1 . sprintf( 
_esc( '%s: on Windows this info may not reflect the reality.' ), 
'<br><b>' . _esc( 'Note' ) . '</b>' ) . "'";
$help_2 = "'" . sprintf( 
_esc( 
"%s means that the backup has been successfuly copied to at<br>least one media target but not to all scheduled media targets.<br>%s means that the backup has not been copied to any media." ), 
getSpanE( _esc( 'Partial' ), 'FF8000' ), 
getSpanE( _esc( 'Failed' ), 'red' ) ) . "'";
$help_3 = "'" . _esc( 'The log files were stored within an isolated branch at:<blockquote>' ) .
str_replace( ROOT_PATH, "<a class=\\'help\\'>ROOT</a>/", LOGS_PATH ) . $row['unique_id'] . "</blockquote>'";
?>
<table>
<tr>
<td><label><?php _pesc('Job id');?></label></td>
<td>:</td>
<td><?php echo $row['id'];?></td>
<td><label><?php _pesc('Start time');?></label></td>
<td>:</td>
<td colspan="3"><?php echo date(DATETIME_FORMAT,$row['started_time']);?>
</td>
</tr>
<tr>
<td><label><?php _pesc('Status');?></label></td>
<td>:</td>
<td style=<?php echo '"color:'.$job_status_style.'"';?>><?php echo $job_status;?></td>
<td><label><?php _pesc('End time');?></label></td>
<td>:</td>
<td colspan="3"><?php echo date(DATETIME_FORMAT,$row['finish_time']);?></td>
</tr>
<tr>
<td><label><?php _pesc('State');?></label></td>
<td>:</td>
<td style=<?php echo '"color:'.$job_state_style.'"';?>><?php echo $job_state;?><a
class='help' onclick=<?php echoHelp ( $help_2 ); ?>>[?]</a></td>
<td><label><?php _pesc('Duration');?></label></td>
<td colspan="3">: <?php echo $job_duration;?></td>
</tr>
<tr>
<td><label><?php _pesc('Result code');?></label></td>
<td>:</td>
<td><?php echo $row['result_code'];?></td>
<td><label><?php _pesc('Log file');?></label></td>
<td>:</td>
<td colspan="4"><img class="help"
src="<?php echo plugins_url_wrapper('img/report.png', IMG_PATH);?>"
onclick="js56816af34b4f1.asyncGetJobLog('<?php echo strToBool($settings['logbranched'])&&!empty($row['unique_id'])?$row['unique_id']:$row['id'];?>')">
<a class="help" onclick=<?php echoHelp ( $help_3 ); ?>><?php echo $row['unique_id'];?></a></td>
</tr>
<tr>
<td><label><?php _pesc('Compressed');?></label></td>
<td>:</td>
<td><?php echo $compression;?></td>
<td><label><?php _pesc('Job size');?></label></td>
<td>:</td>
<td><?php echo $job_size;?> MB</td>
<td><label><?php _pesc('Ratio');?></label></td>
<td>:</td>
<td><?php echo $job_ratio;?>x</td>
</tr>
<tr>
<td><label><?php _pesc('Toolchain');?></label></td>
<td>:</td>
<td><?php echo $row['toolchain'];?></td>
<?php if('intern'==$row['toolchain']){?>
<td><label><?php _pesc('CPU sleep');?></label></td>
<td>:</td>
<td colspan="4"><?php echo $row['cpu_sleep'];?> ms</td>
<?php }?>
</tr>
<?php if(0!=$row ['compression_type']){?>	
<tr>
<td><label><?php _pesc('Method');?></label></td>
<td>:</td>
<td><?php echo $compression_type;?></td>
<?php if(2==$row['compression_type']&&'extern'==$row['toolchain']){?>
<td><label><?php _pesc('Version');?></label></td>
<td>:</td>
<td><?php echo $row['bzip_ver'];?></td>
<?php }?>
<td><label><?php _pesc('Level');?></label></td>
<td>:</td>
<td><?php echo $row['compression_level'];?></td>
</tr>
<?php }?>	
<tr>
<td><label><?php _pesc('# of volumes');?></label></td>
<td>:</td>
<td><?php echo $row['volumes_count'];?></td>
<td><label><?php _pesc('# of files');?></label></td>
<td>:</td>
<td colspan="4"><?php echo $row['files_count'];?></td>
</tr>
<tr>
<td><label><?php _pesc('Average speed');?></label></td>
<td>:</td>
<td><?php echo $job_avg_speed;?> MiBps</td>
<td><label><?php _pesc('Avg. CPU');?></label></td>
<td>:</td>
<td style=<?php echo '"color:'.$job_avg_cpu_style.'"';?>><?php echo $row ['avg_cpu'];?></td>
<td colspan="3" rowspan="2"><a class='help'
onclick=<?php echoHelp ( $help_1 ); ?>>[?]</a></td>
</tr>
<tr>
<td><label><?php _pesc('Peak RAM');?></label></td>
<td>:</td>
<td><?php echo $job_peak_mem;?> MiB</td>
<td><label><?php _pesc('Peak CPU');?></label></td>
<td>:</td>
<td style=<?php echo '"color:'.$job_peak_cpu_style.'"';?>><?php echo $row ['peak_cpu'];?></td>
</tr>
<?php if(!empty($job_mode)){?>
<tr>
<td><label><?php _pesc('Mode');?></label></td>
<td>:</td>
<td colspan="4"><?php echo $job_mode;?></td>
</tr>
<?php }?>
</table>
<?php
}
function echoJobMediaFiles( $params, $settings = null ) {
function getTargetNameByOperationId( $operation ) {
$result = '';
switch ( $operation ) {
case OPER_SEND_DISK :
case OPER_SENT_DISK :
$result = 'Disk';
break;
case OPER_SEND_FTP :
case OPER_SENT_FTP :
$result = 'Ftp';
break;
case OPER_SEND_DROPBOX :
case OPER_SENT_DROPBOX :
$result = 'Dropbox';
break;
case OPER_SEND_GOOGLE :
case OPER_SENT_GOOGLE :
$result = 'Google';
break;
case OPER_SEND_EMAIL :
case OPER_SENT_EMAIL :
$result = 'Mail';
break;
case OPER_SEND_WEBDAV :
case OPER_SENT_WEBDAV :
$result = 'WebDAV';
break;
case OPER_SEND_SSH :
case OPER_SENT_SSH :
$result = 'SSH';
break;
default :
$result = '?';
break;
}
return $result;
}
$stat_mngr = getJobsStatManager( $settings );
$paths_tbl = TBL_PREFIX . TBL_PATHS;
$stat_tbl = TBL_PREFIX . TBL_STATS;
$files_tbl = TBL_PREFIX . TBL_FILES;
$sources_tbl = TBL_PREFIX . TBL_SOURCES;
$path_sql_subquery = '(SELECT path FROM ' . $paths_tbl . ' WHERE ' . $paths_tbl . '.jobs_id = ' . $stat_tbl .
'.jobs_id AND cast(' . $paths_tbl . '.operation/2 as INT) = cast(' . $stat_tbl . '.operation/2 as INT))';
$sql = 'SELECT A.filename,' . $path_sql_subquery . ' as path,' . $stat_tbl . '.files_id,' . $stat_tbl .
'.operation_time AS transfer_time,A.filesize,A.uncompressed,A.ratio,A.compress_time,A.disk_free,A.script_mem_usage AS compress_mem_usage,A.checksum,' .
$stat_tbl . '.script_mem_usage AS transfer_mem_usage,A.source_type,A.source_path FROM ' . $stat_tbl .
' LEFT OUTER JOIN ' . $files_tbl . ' on ' . $stat_tbl . '.files_id=' . $files_tbl .
'.id LEFT OUTER JOIN (SELECT ' . $stat_tbl .
'.files_id,filename,filesize,uncompressed,ratio,operation_time AS compress_time,disk_free,script_mem_usage,checksum,' .
$sources_tbl . '.source_type,' . $sources_tbl . '.path as source_path  FROM ' . $stat_tbl . ' INNER JOIN ' .
$files_tbl . ' ON ' . $stat_tbl . '.files_id = ' . $files_tbl . '.id LEFT OUTER JOIN ' . $sources_tbl . ' ON ' .
$files_tbl . '.sources_id=' . $sources_tbl . '.id WHERE ' . $stat_tbl . '.jobs_id=' . $params['id'] .
' and action=' . METRIC_ACTION_COMPRESS . ')A ON ' . $files_tbl . '.filename=A.filename WHERE ' . $stat_tbl .
'.jobs_id=' . $params['id'] . ' AND ' . $stat_tbl . '.action=' . METRIC_ACTION_TRANSFER . ' AND ' .
sqlFloor( $stat_tbl . '.operation/2', $stat_mngr->isSQLite() ) . '=' . $params['media_info'] . ';';
$sql_err = 'SELECT id,error FROM ' . $stat_tbl . ' WHERE jobs_id=' . $params['id'] . ' AND action=%d AND ' .
sqlFloor( 'operation/2', $stat_mngr->isSQLite() ) . ' =' . $params['media_info'] .
' AND files_id=%d AND error IS NOT NULL;';
$rst = $stat_mngr->queryData( $sql );
$html_rows = sprintf( 
'<table class="files history"><tr><th rowspan="2">%s ' . getTargetNameByOperationId( $params['media_info'] ) .
'</th><th colspan="3">%s (MB)</th><th colspan="4">%s</th><th colspan="4">%s</th></tr>', 
_esc( 'File(s) on media' ), 
_esc( 'Size' ), 
_esc( 'Compression' ), 
_esc( 'Transfer' ) );
$html_rows .= sprintf( 
'<tr><th>%s</th><th>%s</th><th>%s</th><th>%s (s)</th><th>MiBps</th><th>RAM (MB)</th><th>%s</th><th>%s (s)</th><th>MiBps</th><th>RAM (MB)</th><th>%s</th></tr>', 
_esc( 'Original' ), 
_esc( 'Compressed' ), 
_esc( 'Ratio' ), 
_esc( 'Time' ), 
_esc( 'Error' ), 
_esc( 'Time' ), 
_esc( 'Error' ) );
$err_style = 'style="color:red;font-weight:bold;"';
while ( $row = $stat_mngr->fetchArray( $rst ) ) {
$compress_errs = array();
$transfer_errs = array();
$rst1 = $stat_mngr->queryData( sprintf( $sql_err, METRIC_ACTION_COMPRESS, $row['files_id'] ) );
while ( $row1 = $stat_mngr->fetchArray( $rst1 ) )
$compress_errs[$row1['id']] = $row1['error'];
$stat_mngr->freeResult( $rst1 );
$rst2 = $stat_mngr->queryData( sprintf( $sql_err, METRIC_ACTION_TRANSFER, $row['files_id'] ) );
while ( $row2 = $stat_mngr->fetchArray( $rst2 ) )
$transfer_errs[$row2['id']] = $row2['error'];
$stat_mngr->freeResult( $rst2 );
$errs_combined = $compress_errs + $transfer_errs;
$compress_err_style = count( $compress_errs ) > 0 ? $err_style : '';
$transfer_err_style = count( $transfer_errs ) > 0 ? $err_style : '';
$compress_speed = $row['compress_time'] > 0 ? round( $row['uncompressed'] / $row['compress_time'] / MB, 2 ) : 'inf';
$transfer_speed = $row['transfer_time'] > 0 ? round( $row['filesize'] / $row['transfer_time'] / MB, 2 ) : 'inf';
$source_type = ( SRCFILE_SOURCE == $row['source_type'] ? 'file' : ( MYSQL_SOURCE == $row['source_type'] ? 'MySQL database' : 'unknown' ) );
if ( MYSQL_SOURCE == $row['source_type'] ) {
extract( json_decode( str_replace( '""', '"', $row['source_path'] ), true ) );
$source_path = sprintf( 'mysql://%s:%s@%s:%s/%s', $mysql_user, '****', $mysql_host, $mysql_port, $mysql_db );
} else
$source_path = normalize_path( $row['source_path'] );
$checksum = 'The original file <a href=\\\'http://en.wikipedia.org/wiki/MD5\\\'>MD5 hash</a> was : <b>' .
$row['checksum'] .
'</b><br>We are going to use the file`s digital fingerprint later in case of restore.<br>If the source archive has different checksum than this one it means it was<br>alterated and restoring such a file will be regarded as a potential risk.';
$checksum .= '<br><br>We also know that this archive includes a backup copy of the following ' . $source_type .
' source:<p style=\\\'color:brown\\\'>' . $source_path .
'</p>This information may also be used to restore the content of the archive at its original location (upon request).';
$c = substr( $row['path'], - 1 );
$filename = ( ! empty( $row['checksum'] ) ? '<img style="vertical-align:middle" class="help" src="' .
plugins_url_wrapper( 'img/key.png', IMG_PATH ) . '" onclick=' . getHelpCall( "'$checksum'" ) . '> ' : '' ) . getspan( 
( ! empty( $row['filename'] ) ? $row['path'] . ( $c == '\\' || $c == '/' ? '' : '/' ) : '' ), 
'#1E8CBE' ) . basename( $row['filename'] );
$html_rows .= '<tr><td style="text-align:left">' . $filename . '</td><td>' .
round( $row['uncompressed'] / MB, 2 ) . '</td><td>' . round( $row['filesize'] / MB, 2 ) . '</td><td>' .
round( $row['ratio'], 2 ) . '</td><td>' . $row['compress_time'] . '</td><td>' . $compress_speed .
'</td><td>' . round( $row['compress_mem_usage'] / MB, 2 ) . '</td><td ' . $compress_err_style . '>' .
count( $compress_errs ) . '</td><td>' . $row['transfer_time'] . '</td><td>' . $transfer_speed . '</td><td>' .
round( $row['transfer_mem_usage'] / MB, 2 ) . '</td><td ' . $transfer_err_style . '>' .
count( $transfer_errs ) . '</td></tr>';
foreach ( $errs_combined as $id => $msg )
$html_rows .= '<tr><td colspan="12"><table><tr><td>(' . $id . ')</td><td style="text-align:left;color:red;">' .
$msg . '</td></tr></table></td></tr>';
}
$html_rows .= '</table>';
$stat_mngr->freeResult( $rst );
echo $html_rows;
}
function echoJobMedia( $params, $settings = null ) {
if ( "sysinfo" === $params['media_info'] )
return echoJobSysinfo( $params, $settings );
elseif ( "d" !== $params['media_info'] )
return echoJobMediaFiles( $params, $settings );
$media = array( 
OPER_SENT_DISK => array( 'disk', 'Disk' ), 
OPER_SENT_FTP => array( 'ftp', 'Ftp' ), 
OPER_SENT_DROPBOX => array( 'dropbox', 'Dropbox' ), 
OPER_SENT_GOOGLE => array( 'google', 'Google' ), 
OPER_SENT_EMAIL => array( 'default', 'E-mail' ), 
OPER_SENT_WEBDAV => array( 'webdav', 'WebDAV' ), 
OPER_SENT_SSH => array( 'ssh', 'SSH' ) );
$media[OPER_SEND_DISK] = $media[OPER_SENT_DISK];
$media[OPER_SEND_FTP] = $media[OPER_SENT_FTP];
$media[OPER_SEND_DROPBOX] = $media[OPER_SENT_DROPBOX];
$media[OPER_SEND_GOOGLE] = $media[OPER_SENT_GOOGLE];
$media[OPER_SEND_EMAIL] = $media[OPER_SENT_EMAIL];
$media[OPER_SEND_WEBDAV] = $media[OPER_SENT_WEBDAV];
$media[OPER_SEND_SSH] = $media[OPER_SENT_SSH];
$stat_mngr = getJobsStatManager( $settings );
$stat_tbl = TBL_PREFIX . TBL_STATS;
$sql = 'SELECT c.operation*2 AS operation, Sum(files_count) AS files_count, Sum(errors_count) AS errors_count FROM ( SELECT ' .
sqlFloor( 'operation/2', $stat_mngr->isSQLite() ) . ' AS operation, ( SELECT Count(*) FROM ' . $stat_tbl .
' i WHERE i.jobs_id=' . $params['id'] . ' AND i.operation=' . $stat_tbl . '.operation AND i.files_id=' .
$stat_tbl . '.files_id) AS files_count, ( SELECT count(*) FROM ' . $stat_tbl . ' a WHERE a.jobs_id=' .
$params['id'] . ' AND a.operation=' . $stat_tbl . '.operation AND a.files_id=' . $stat_tbl .
'.files_id AND a.error IS NOT NULL) AS errors_count FROM ' . $stat_tbl . ' WHERE jobs_id=' . $params['id'] .
' AND action=' . METRIC_ACTION_TRANSFER . ')c GROUP BY c.operation;';
$rst = $stat_mngr->queryData( $sql );
$html_media_row = '<th>' . _esc( 'Media name' ) . '</th>';
$html_count_row = '<th>' . _esc( 'Files' ) . '</th>';
$media_count = 0;
while ( $row = $stat_mngr->fetchArray( $rst ) ) {
if ( $row['operation'] < 0 )
continue;
if ( null !== $row['operation'] ) {
$onclick = "js56816af34b4f1.asyncGetMediaInfo(" . $params['id'] . "," . $row['operation'] / 2 . ");";
$onclick .= "document.getElementById('folder_info').style.display='inline-block';";
} else
$onclick = '';
if ( null !== $row['operation'] && isset( $media[intval( $row['operation'] )] ) )
$html_media_row .= '<th>' . $media[intval( $row['operation'] )][1] . '</th>';
$style = 'style="color:' .
( $row['errors_count'] > 0 ? ( ( $row['errors_count'] >= $row['files_count'] ? 'red' : '#FF8000' ) ) : 'green' ) .
'"';
$html_count_row .= '<td ' . $style . ( ! empty( $onclick ) ? 'onclick="' . $onclick . '"' : '' ) . '>' .
$row['files_count'] . '</td>';
$media_count++;
}
$html_rows = '<table class="files history">';
$html_rows .= '<tr>' . $html_media_row . '</tr>';
$html_rows .= '<tr id="no-hover">' . $html_count_row . '</tr>';
$html_rows .= '</table>';
$stat_mngr->freeResult( $rst );
echo $html_rows;
}
function echoJobSysinfo( $params, $settings = null ) {
$help_1 = "'" .
_esc( 
"<b>Free</b> is the physical free memory.</b><br><b>Available</b> is the former + the cached memory/buffers<br>which can be release in case it is need it.<br><b>Swap</b> is virtual memory on disk.<br>Note: it is possible that Available&gt;Free. That is perfectly fine." ) .
"'";
$help_2 = "'" . _esc( "This was the software version used when the job was run." ) . "'";
$help_3 = "'" . _esc( "This was the hardware used when the job was run." ) . "'";
$stat_mngr = getJobsStatManager( $settings );
$tbl_sysinfo = TBL_PREFIX . TBL_SYSINFO;
$tbl_sysinfo_cpu = TBL_PREFIX . TBL_SYSCPU;
$tbl_sysinfo_mem = TBL_PREFIX . TBL_SYSMEM;
$tbl_jobs = TBL_PREFIX . TBL_JOBS;
$rst = $stat_mngr->queryData( 
'select ' . $tbl_sysinfo . '.os,' . $tbl_sysinfo . '.php_ver,' . $tbl_sysinfo . '.server_ver from ' .
$tbl_sysinfo . ' inner join ' . $tbl_jobs . ' on ' . $tbl_sysinfo . '.timestamp<=' . $tbl_jobs .
'.finish_time where ' . $tbl_jobs . '.id=' . $params['id'] . ' order by ' . $tbl_sysinfo .
'.id desc limit 1;' );
$row = $stat_mngr->fetchArray( $rst );
$stat_mngr->freeResult( $rst );
?>
<table id='job_sysinfo' class="files history">
<tr>
<th>OS</th>
<td><?php echo $row ['os'];?></td>
<td rowspan="3"><a class='help' onclick=<?php echoHelp($help_2 );?>>[?]</a></td>
</tr>
<tr>
<th>PHP</th>
<td><?php echo $row ['php_ver'];?></td>
</tr>
<tr>
<th>Software</th>
<td><?php echo $row ['server_ver'];?></td>
</tr>
<?php
$cpus = array();
$sql = 'select min(vendor_id) as vendor_id, min(model_name) as model_name, min(cpu_MHz) as min_speed,max(cpu_MHz) as max_speed, core_id from (select ' .
$tbl_sysinfo_cpu . '.vendor_id,' . $tbl_sysinfo_cpu . '.model_name,' . $tbl_sysinfo_cpu . '.cpu_MHz,' .
$tbl_sysinfo_cpu . '.core_id from ' . $tbl_sysinfo_cpu . ' where jobs_id=' . $params['id'] . ' order by ' .
$tbl_sysinfo_cpu . '.id desc,' . $tbl_sysinfo_cpu .
'.cpu_MHz desc limit 16)A group by core_id order by core_id;';
$rst = $stat_mngr->queryData( $sql );
while ( $row = $stat_mngr->fetchArray( $rst ) ) {
if ( isset( $cpus[$row['core_id']] ) )
continue;
$cpus[$row['core_id']] = array( 
'vendor' => $row['vendor_id'], 
'model' => $row['model_name'], 
'min_speed' => $row['min_speed'], 
'max_speed' => $row['max_speed'] );
}
$stat_mngr->freeResult( $rst );
if ( count( $cpus ) > 0 ) {
$html_rows = '<tr><th colspan="3" style="border-color:#fff;background-color:#f4ce2d;color:#000">' .
$cpus[0]['model'] . '</th></tr>';
$first = true;
foreach ( $cpus as $core_id => $cpu_data ) {
$html_rows .= '<tr><th>core ' . $core_id . '</th><td>' . round( $cpu_data['min_speed'], 0 ) . 'MHz' .
( $cpu_data['min_speed'] != $cpu_data['max_speed'] ? ' -> ' . round( $cpu_data['max_speed'], 0 ) . 'MHz' : '' ) .
'</td>' .
( $first ? '<td rowspan="2"><a class="help" onclick=' . getHelpCall( $help_3 ) . '>[?]</a></td>' : '' ) .
'</tr>';
$first = false;
}
}
echo $html_rows;
$sql = 'select MemTotal,MemFree,MemAvailable,SwapTotal,SwapFree from ' . $tbl_sysinfo_mem . ' where jobs_id=' .
$params['id'] . ';';
$rst = $stat_mngr->queryData( $sql );
$row = $stat_mngr->fetchArray( $rst );
$stat_mngr->freeResult( $rst );
$html_rows = sprintf( 
'<tr><th colspan="3" style="border-color:#fff;background-color:#f4ce2d;color:#000">%s ' .
getHumanReadableSize( $row['MemTotal'] ) . ' RAM</th></tr>', 
_esc( 'System RAM' ) );
$html_rows .= '<tr><th>' . _esc( 'Free' ) . '</th><td>' . getHumanReadableSize( $row['MemFree'] ) .
"</td><td rowspan='4'><a class='help' onclick=" . getHelpCall( $help_1 ) . ">[?]</a></td></tr>";
$html_rows .= '<tr><th>' . _esc( 'Available' ) . '</th><td>' . getHumanReadableSize( $row['MemAvailable'] ) .
'</td></tr>';
$html_rows .= '<tr><th>' . _esc( 'Total swap' ) . '</th><td>' . getHumanReadableSize( $row['SwapTotal'] ) .
'</td></tr>';
$html_rows .= '<tr><th>' . _esc( 'Free swap' ) . '</th><td>' . getHumanReadableSize( $row['SwapFree'] ) .
'</td></tr>';
echo $html_rows;
?>	
</table>
<?php
}
function flushJobHistory( $settings ) {
$stat_mngr = getJobsStatManager( $settings );
$result = $stat_mngr->flushData();
if ( $result )
_pesc( 'Job history flushed successfuly.' );
else {
$last_err = error_get_last();
printf( '<red>%s.</b><br>%s', _esc( 'Could not flush job history' ), $last_err['message'] );
if ( $stat_mngr->isSQLite() )
echo '<br>' . _esc( 'Try do it manually by removing that file.' );
}
}
function echoHistoryJobLog( $params) {
global $COMPRESSION_NAMES;
$job_log_content = null;
$job_id = intval( $params['id'] );
$log_file = OUTPUT_LOGFILE;
if ( $job_id > 0 && ! file_exists( $log_file ) ) {
printf( 
'<red>%s:</red><pre>%s</pre>', 
_esc( 'Strange enough but the log file cannot be found at its path' ), 
str_replace( ROOT_PATH, '<a class="help" href="">ROOT</a>/', $log_file ) );
return false;
}
if ( 0 == $job_id ) {
$log_file = dirname( $log_file ) . DIRECTORY_SEPARATOR . $params['id'] . DIRECTORY_SEPARATOR .
basename( $log_file ) . '.' . $COMPRESSION_NAMES[GZ];
if ( file_exists( $log_file ) )
$job_log_content = implode( gzfile( $log_file ) );
} else {
$found = false;
$job_start_pattern = '^\[job_id:' . $job_id . '\]';
$job_toend_pattern = '^\[/job_id:' . $job_id . '\]';
$job_content_pattern = '@(' . $job_start_pattern . ')([\s\S]*?)(' . $job_toend_pattern . ')@m';
$chunksize = 1 * MB; 
if ( filesize( $log_file ) > $chunksize ) {
$handle = fopen( $log_file, 'rb' );
while ( ! feof( $handle ) ) {
$buffer = fread( $handle, $chunksize );
if ( ! $found ) {
if ( preg_match( $job_content_pattern, $buffer, $matches ) ) {
$job_log_content = $matches[2];
break;
}
if ( $found = preg_match( "@$job_start_pattern([\s\S]*)@m", $buffer, $matches ) )
$tmp_str = $matches[1];
} else {
if ( preg_match( "@([\s\S]*)$job_toend_pattern@m", $buffer, $matches ) ) {
$job_log_content = $tmp_str . $matches[1];
break;
}
$tmp_str .= $buffer;
}
}
fclose( $handle );
if ( empty( $job_log_content ) && $found )
if ( preg_match( $job_content_pattern, $tmp_str, $matches ) )
$job_log_content = $matches[2];
else
printf( 
_esc( 
'<blockquote style="background-color:yellow">If you read this message it means that the log of job #%d is a mess</blockquote>' ), 
$job_id );
} else {
$tmp_str = file_get_contents( $log_file );
if ( preg_match( $job_content_pattern, $tmp_str, $matches ) )
$job_log_content = $matches[2];
elseif ( preg_match( "@$job_toend_pattern@", $tmp_str, $matches ) )
$job_log_content = $matches[0];
}
}
if ( ! empty( $job_log_content ) ) {
$job_log_content = str_replace( 
array( str_repeat( BULLET, SEPARATOR_LEN ) ), 
array( '<hr>' ), 
$job_log_content );
dumpVar( "<div class='cui-console'>" . trim( $job_log_content ) . '</div>', true ); 
} else
printf( 
"<yellow>%s</yellow>.<br>%s<blockquote>%s</blockquote>", 
sprintf( _esc( 'Strange enough the log file does not contain the section for job# %s' ), $job_id ), 
_esc( 'If you are in doubt then please check the log file at :' ), 
$log_file );
}
?>