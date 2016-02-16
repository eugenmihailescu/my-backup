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
 * @version : 0.2.3-8 $
 * @commit  : 010da912cb002abdf2f3ab5168bf8438b97133ea $
 * @author  : Eugen Mihailescu eugenmihailescux@gmail.com $
 * @date    : Tue Feb 16 21:44:02 2016 UTC $
 * @file    : last_bak_info.php $
 * 
 * @id      : last_bak_info.php | Tue Feb 16 21:44:02 2016 UTC | Eugen Mihailescu eugenmihailescux@gmail.com $
*/

namespace MyBackup;

global $BACKUP_MODE;
$unknown = _esc( 'unknown' );
$url = $_this_->method['url'];
$stat_mngr = getJobsStatManager( $_this_->settings );
if ( false === ( $job_info = getLastJobInfo( 
$stat_mngr, 
false, 
JOB_BACKUP, 
array( JOB_STATE_COMPLETED, JOB_STATE_PARTIAL ) ) ) ) {
$job_info = array( 
'title' => $unknown, 
'id' => 0, 
'mode' => $unknown, 
'started_time' => 0, 
'job_status' => $unknown, 
'job_state' => $unknown, 
'files' => $unknown, 
'jobsize' => 0, 
'operation' => array( $unknown ), 
'source_type' => array( $unknown ) );
}
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
$update_url( $job_info['source_type'] );
$update_url( $job_info['operation'] );
$job_info['job_status'] = getJobStatusStr( $job_info['job_status'], $job_info['started_time'] );
$job_info['job_state'] = getJobStateStr( $job_info['job_state'] );
$job_info['mode'] = isset( $BACKUP_MODE[$job_info['mode']] ) ? $BACKUP_MODE[$job_info['mode']] : $unknown;
isset( $job_info['operation'] ) || $job_info['operation'] = array( $unknown );
$job_info['title'] = sprintf( _esc( 'Last %s backup' ), $job_info['job_state'][0] );
$job_info['started_time'] = ! $job_info['started_time'] ? $unknown : $format_date( $job_info['started_time'] );
$job_info['jobsize'] = getHumanReadableSize( $job_info['jobsize'] );
$next_schedule = is_wp() ? wp_next_scheduled( WPCRON_SCHEDULE_HOOK_NAME ) : 'TBD';
echo json_encode( $job_info, JSON_FORCE_OBJECT );
?>