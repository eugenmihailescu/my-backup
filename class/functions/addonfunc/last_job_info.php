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
 * @version : 0.2.3-27 $
 * @commit  : 10d36477364718fdc9b9947e937be6078051e450 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Fri Mar 18 10:06:27 2016 +0100 $
 * @file    : last_job_info.php $
 * 
 * @id      : last_job_info.php | Fri Mar 18 10:06:27 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

global $BACKUP_MODE;
$unknown = _esc( 'unknown' );
$url = $_this_->method['url'];
$stat_mngr = getJobsStatManager( $_this_->settings );
$job_info = array();
foreach ( array( JOB_BACKUP, - 4 ) as $job_type ) {
if ( false === ( $last_job_info = getLastJobInfo( 
$stat_mngr, 
false, 
$job_type, 
array( JOB_STATE_COMPLETED, JOB_STATE_PARTIAL ) ) ) ) {
$job_info[$job_type] = array( 
'title' => $unknown, 
'id' => 0, 
'mode' => $unknown, 
'started_time' => 0, 
'job_status' => getJobStatusStr( - 1, 0 ), 
'job_state' => getJobStateStr( - 1 ), 
'files' => $unknown, 
'jobsize' => 0, 
'operation' => array( $unknown ), 
'source_type' => array( $unknown ) );
} else
$job_info = $job_info + $last_job_info;
$update_url = function ( &$array ) use(&$url ) {
global $TARGET_NAMES;
foreach ( $array as $key => $value ) {
isset( $TARGET_NAMES[$key] ) && $array[$key] = preg_replace( 
'/(?<=href=)([\'"])([^\1]*?)\1/', 
'$1' . replaceUrlParam( $url, 'tab', $TARGET_NAMES[$key] ) . '$1', 
getTabAnchor( $key ) );
}
};
$format_date = function ( $date ) {
$diff = time() - $date;
if ( $diff < SECDAY )
return _esc( 'today' ) . ', ' . date( TIME_FORMAT, $date );
elseif ( $diff < 2 * SECDAY )
return _esc( 'yesterday' ) . ', ' . date( TIME_FORMAT, $date );
return date( DATETIME_FORMAT, $date );
};
isset( $job_info[$job_type]['source_type'] ) && $update_url( $job_info[$job_type]['source_type'] );
isset( $job_info[$job_type]['operation'] ) && $update_url( $job_info[$job_type]['operation'] );
$job_info[$job_type]['job_status'] = isset( $job_info[$job_type]['job_status'] ) ? getJobStatusStr( 
$job_info[$job_type]['job_status'], 
$job_info[$job_type]['started_time'] ) : array( $unknown, '#FFF', 1, 'tomato' );
$job_info[$job_type]['job_state'] = isset( $job_info[$job_type]['job_state'] ) ? getJobStateStr( 
$job_info[$job_type]['job_state'] ) : array( $unknown, '#FFF', 2, 'tomato' );
$job_info[$job_type]['mode'] = isset( $job_info[$job_type]['mode'] ) &&
isset( $BACKUP_MODE[$job_info[$job_type]['mode']] ) ? $BACKUP_MODE[$job_info[$job_type]['mode']] : $unknown;
isset( $job_info[$job_type]['operation'] ) || $job_info[$job_type]['operation'] = array( $unknown );
$job_info[$job_type]['title'] = sprintf( 
_esc( 'Last %s %s (#%s)' ), 
isset( $job_info[$job_type]['job_state'] ) ? $job_info[$job_type]['job_state'][0] : $unknown, 
JOB_BACKUP == $job_type ? _esc( 'backup' ) : _esc( 'restore' ), 
isset( $job_info[$job_type]['id'] ) ? $job_info[$job_type]['id'] : $unknown );
$job_info[$job_type]['started_time'] = ! ( isset( $job_info[$job_type]['started_time'] ) &&
$job_info[$job_type]['started_time'] ) ? $unknown : $format_date( $job_info[$job_type]['started_time'] );
$job_info[$job_type]['jobsize'] = getHumanReadableSize( 
isset( $job_info[$job_type]['jobsize'] ) ? $job_info[$job_type]['jobsize'] : 0 );
$next_schedule = is_wp() ? wp_next_scheduled( WPCRON_SCHEDULE_HOOK_NAME ) : 'TBD';
}
echo json_encode( $job_info, JSON_FORCE_OBJECT );
?>