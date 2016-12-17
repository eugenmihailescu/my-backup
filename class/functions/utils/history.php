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
 * @file    : history.php $
 * 
 * @id      : history.php | Tue Dec 13 06:40:49 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

function getJobsStatManager( $opts = null ) {
global $settings;
$upgrade_db = function ( $instance, &$opts ) {
global $registered_db_upgrades;
ksort( $registered_db_upgrades );
$last_ver = '';
foreach ( $registered_db_upgrades as $new_version => $callbacks ) {
if ( ! isset( $opts['db_ver'] ) || version_compare( $opts['db_ver'], $new_version, '<' ) )
foreach ( $callbacks as $priority => $callback )
$instance->register_upgrade_callback( $callback, $new_version, $priority );
version_compare( $last_ver, $new_version, '<' ) && $last_ver = $new_version;
}
$success = $instance->upgrade_db();
if ( true === $success && ! ( empty( $last_ver ) || empty( $opts ) ) ) {
if ( ! ( isset( $opts['db_ver'] ) && ( $opts['db_ver'] == $last_ver ) ) ) {
$opts['db_ver'] = $last_ver;
submit_options( null, $opts );
}
} else {
global $java_scripts;
$java_scripts[] = sprintf( 
"jsMyBackup.popupError('%s','%s');", 
_esc( 'DB Upgrade Error' ), 
implode( '.<br>', array_unique( $success ) ) );
}
};
if ( null == $opts )
$opts = $settings;
if ( $opts['historydb'] == 'sqlite' ) {
$params = STATISTICS_LOGFILE;
} else {
$is_wp = is_wp();
$params = array( 
'host' => $is_wp ? DB_HOST : $opts['mysql_host'], 
'db_name' => $is_wp ? DB_NAME : $opts['mysql_db'], 
'user' => $is_wp ? DB_USER : $opts['mysql_user'], 
'pwd' => $is_wp ? DB_PASSWORD : $opts['mysql_pwd'], 
'charset' => $is_wp ? DB_CHARSET : 'utf8', 
'collate' => $is_wp ? DB_COLLATE : '' );
$is_wp || $params['port'] = $opts['mysql_port'];
}
$result = new StatisticsManager( $params, $opts );
$upgrade_db( $result, $opts );
return $result;
}
function getJobInfo( $stat_mngr, $job_id ) {
if ( ! ( $stat_mngr && $job_id ) )
return false;
$query = '';
$tbl_fields = array( 
TBL_JOBS => array( 'mode', 'job_status', 'job_state', 'started_time' ), 
TBL_FILES => array( 'filename', 'filesize', 'id' ), 
TBL_STATS => array( 'operation' ), 
TBL_SOURCES => array( 'source_type' ) );
foreach ( $tbl_fields as $table => $fields ) {
$query .= ( empty( $query ) ? '' : ',' ) . TBL_PREFIX . $table . '.' .
implode( ',' . TBL_PREFIX . $table . '.', $fields );
}
$query = 'SELECT ' . $query;
$from = ' FROM ' . TBL_PREFIX . TBL_JOBS;
$from .= ' LEFT OUTER JOIN ' . TBL_PREFIX . TBL_FILES . ' ON ' . TBL_PREFIX . TBL_JOBS . '.id=' . TBL_PREFIX .
TBL_FILES . '.jobs_id';
$from .= ' LEFT OUTER JOIN ' . TBL_PREFIX . TBL_SOURCES . ' ON ' . TBL_PREFIX . TBL_SOURCES . '.jobs_id=' .
TBL_PREFIX . TBL_FILES . '.jobs_id AND ' . TBL_PREFIX . TBL_SOURCES . '.id=' . TBL_PREFIX . TBL_FILES .
'.sources_id';
$from .= ' LEFT OUTER JOIN ' . TBL_PREFIX . TBL_STATS . ' ON ' . TBL_PREFIX . TBL_JOBS . '.id=' . TBL_PREFIX .
TBL_STATS . '.jobs_id AND ' . TBL_PREFIX . TBL_STATS . '.files_id=' . TBL_PREFIX . TBL_FILES . '.id AND ' .
TBL_PREFIX . TBL_STATS . '.action=1 AND ' . TBL_PREFIX . TBL_STATS . '.error IS NULL';
$where = ' WHERE ' . TBL_PREFIX . TBL_JOBS . '.id=' . $job_id;
$rst = $stat_mngr->queryData( $query . $from . $where );
$result = array( 'files' => array(), 'operation' => array(), 'source_type' => array() );
while ( $data = $stat_mngr->fetchArray( $rst ) ) {
if ( empty( $data['operation'] ) )
continue;
$operation = ceil( $data['operation'] / 2 );
$result['operation'][$operation] = $operation;
isset( $result['files'][$data['source_type']] ) || $result['files'][$data['source_type']] = array();
$result['files'][$data['source_type']][$data['id']] = array( $data['filename'], $data['filesize'], $operation );
isset( $result['mode'] ) || $result['mode'] = $data['mode'];
isset( $result['started_time'] ) || $result['started_time'] = $data['started_time'];
isset( $result['job_status'] ) || $result['job_status'] = $data['job_status'];
isset( $result['job_state'] ) || $result['job_state'] = $data['job_state'];
}
if ( ! empty( $result ) ) {
$result['id'] = $job_id;
$result['operation'] = array_unique( $result['operation'] );
$result['jobsize'] = array_reduce( 
$result['files'], 
function ( $carry, $item ) {
$result = $carry;
foreach ( $item as $array )
$result += $array[1];
return $result;
}, 
0 );
array_walk( 
$result['files'], 
function ( &$item, $key ) {
foreach ( $item as $file_id => $file_info )
$item[$file_id][1] = getHumanReadableSize( $file_info[1] );
} );
$result['source_type'] = array();
$rst = $stat_mngr->queryData( 
'select source_type FROM ' . TBL_PREFIX . TBL_SOURCES . ' WHERE jobs_id=' . $job_id .
' AND source_type IS NOT NULL GROUP BY source_type' );
while ( $data = $stat_mngr->fetchArray( $rst ) )
$result['source_type'][$data[0]] = $data[0];
return $result;
}
return false;
}
function getLastJobInfo( $stat_mngr, $mode = BACKUP_MODE_FULL, $job_type = JOB_BACKUP, $job_state = array(JOB_STATE_COMPLETED) ) {
if ( null == $stat_mngr )
return false;
$where = array();
( false !== $job_type ) && ( $where[] = 'job_type=' . $job_type );
( false !== $mode ) && ( $where[] = 'mode=' . $mode );
empty( $job_state ) || $where[] = TBL_PREFIX . TBL_JOBS . '.job_state IN (' . implode( ',', $job_state ) . ')';
$where = array_reduce( 
$where, 
function ( $carry, $item ) {
return ( empty( $carry ) ? '' : ( $carry . ' AND ' ) ) . ( preg_match( '/^\w+[\w\d_]=/', $item ) ? TBL_PREFIX .
TBL_JOBS . '.' : '' ) . $item;
}, 
'' );
empty( $where ) || $where = ' WHERE ' . $where;
$rst = $stat_mngr->queryData( 
'SELECT id FROM ' . TBL_PREFIX . TBL_JOBS . $where . ' ORDER BY started_time DESC LIMIT 1;' );
$data = $stat_mngr->fetchArray( $rst, 1 );
$job_id = $data['id'];
return array( $job_type => getJobInfo( $stat_mngr, $job_id ) );
}
function getJobStatusStr( $job_status, $started_time ) {
$job_status_fg_color = '#FFF';
switch ( $job_status ) {
case JOB_STATUS_RUNNING :
if ( time() - $started_time > LONG_RUNNING_JOB_TIMEOUT ) {
$job_status_str = _esc( 'suspect' );
$job_status_bg_color = 'tomato';
} else {
$job_status_str = _esc( 'running' );
$job_status_bg_color = '#2EA2CC';
}
break;
case JOB_STATUS_ABORTED :
$job_status_str = _esc( 'aborted' );
$job_status_bg_color = 'tomato';
break;
case JOB_STATUS_FINISHED :
$job_status_str = _esc( 'done' );
$job_status_bg_color = '#00BD46';
break;
case JOB_STATUS_SUSPENDED :
$job_status_str = _esc( 'suspended' );
$job_status_bg_color = 'tomato';
break;
default :
$job_status_str = _esc( 'unknown' );
$job_status_bg_color = 'tomato';
break;
}
return array( $job_status_str, $job_status_fg_color, $job_status, $job_status_bg_color );
}
function getJobStateStr( $job_state ) {
$job_state_fg_color = '#FFF';
switch ( $job_state ) {
case JOB_STATE_COMPLETED :
$job_state_str = _esc( 'completed' );
$job_state_bg_color = '#00BD46';
break;
case JOB_STATE_PARTIAL :
$job_state_str = _esc( 'partial' );
$job_state_bg_color = '#FFB600';
break;
case JOB_STATE_FAILED :
$job_state_str = _esc( 'failed' );
$job_state_bg_color = 'tomato';
break;
default :
$job_state_str = _esc( 'unknwon' );
$job_state_bg_color = 'tomato';
break;
}
return array( $job_state_str, $job_state_fg_color, $job_state, $job_state_bg_color );
}
function getJobsStatistics( $stat_mngr ) {
if ( null == $stat_mngr )
return false;
$tbl_jobs = TBL_PREFIX . TBL_JOBS;
$tbl_files = TBL_PREFIX . TBL_FILES;
$result = array();
foreach ( array( JOB_BACKUP, - 4 ) as $job_type ) {
$rst = $stat_mngr->queryData( 
'SELECT SUM(B.completed) AS completed, SUM(B.partial) AS partial, SUM(B.failed) AS failed, SUM(B.files_count) AS files_count, SUM(B.job_size) AS file_size, SUM(B.data_size) AS data_size, AVG(B.ratio) as ratio FROM (SELECT CASE WHEN A.job_status = 2 AND A.job_state = 0 THEN 1 ELSE 0 END AS completed, CASE WHEN A.job_status in (1,2) AND A.job_state = 1 THEN 1 ELSE 0 END AS partial, CASE WHEN A.job_status=3 OR A.job_state in (null,2) THEN 1 ELSE 0 END AS failed, A.files_count, A.job_size, A.ratio, (SELECT SUM(B.filesize) FROM ' .
$tbl_files . ' B WHERE B.jobs_id = A.id) AS data_size FROM ' . $tbl_jobs . ' A WHERE A.job_type = ' .
$job_type . ') B' );
$result[$job_type] = $stat_mngr->fetchArray( $rst, 1 );
}
return $result;
}
?>