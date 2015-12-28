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
 * @file    : history.php $
 * 
 * @id      : history.php | Mon Dec 28 17:57:55 2015 +0100 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

function getJobsStatManager( $opts = null ) {
global $settings;
if ( null == $opts )
$opts = $settings;
if ( $opts['historydb'] == 'sqlite' )
$params = STATISTICS_LOGFILE;
else
$params = array( 
'host' => $opts['mysql_host'], 
'port' => $opts['mysql_port'], 
'db_name' => $opts['mysql_db'], 
'user' => $opts['mysql_user'], 
'pwd' => $opts['mysql_pwd'] );
return new StatisticsManager( $params, $opts );
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
$result['files'][$data['source_type']][$data['id']] = array( $data['filename'], $data['filesize'] );
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
$data = $stat_mngr->fetchArray( $rst );
$job_id = $data['id'];
return getJobInfo( $stat_mngr, $job_id );
}
function getJobStatusStr( $job_status, $started_time ) {
switch ( $job_status ) {
case JOB_STATUS_RUNNING :
if ( time() - $started_time > LONG_RUNNING_JOB_TIMEOUT ) {
$job_status = _esc( 'suspect' );
$job_status_style = 'red';
} else {
$job_status = _esc( 'running' );
$job_status_style = '#2EA2CC';
}
break;
case JOB_STATUS_ABORTED :
$job_status = _esc( 'aborted' );
$job_status_style = 'red';
break;
case JOB_STATUS_FINISHED :
$job_status = _esc( 'done' );
$job_status_style = 'green';
break;
case JOB_STATUS_SUSPENDED :
$job_status = _esc( 'suspended' );
$job_status_style = 'red';
break;
default :
$job_status = _esc( 'unknown' );
$job_status_style = 'red';
break;
}
return array( $job_status, $job_status_style );
}
function getJobStateStr( $job_state ) {
switch ( $job_state ) {
case JOB_STATE_COMPLETED :
$job_state = _esc( 'completed' );
$job_state_style = 'green';
break;
case JOB_STATE_PARTIAL :
$job_state = _esc( 'partial' );
$job_state_style = '#FF8000';
break;
case JOB_STATE_FAILED :
$job_state = _esc( 'failed' );
$job_state_style = 'red';
break;
default :
$job_state = _esc( 'unknwon' );
$job_state_style = 'red';
break;
}
return array( $job_state, $job_state_style );
}
?>