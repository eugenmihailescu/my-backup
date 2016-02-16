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
 * @file    : wp-schedule.php $
 * 
 * @id      : wp-schedule.php | Tue Feb 16 21:44:02 2016 UTC | Eugen Mihailescu eugenmihailescux@gmail.com $
*/

namespace MyBackup;

function is_wpcron_disabled() {
return defined ( __NAMESPACE__.'\\DISABLE_WP_CRON' ) && DISABLE_WP_CRON;
}
function is_wpcron_alternated() {
return defined ( __NAMESPACE__.'\\ALTERNATE_WP_CRON' ) && ALTERNATE_WP_CRON;
}
function check_wpcron() {
if (is_wpcron_disabled ())
throw new MyException ( sprintf ( _esc ( 'WPCron is disabled. Check the %s constant in file %s<br>You may use the expert option <b>DISABLE_WP_CRON=false</b> to try enabling the WP Cron.' ), '<b>DISABLE_WP_CRON</b>', '<br>' . get_wp_config_path () ) );
}
function get_active_wpcrons($highlight_jobname = null) {
$cron_jobs = get_option ( 'cron' );
$result = '';
$style = "style='background-color:#f0f0f0;font-weight:bold'";
$uniq_id = uniqid ();
foreach ( $cron_jobs as $timestamp => $job ) {
if (! (is_array ( $job ) && is_numeric ( $timestamp )))
continue;
$timestamp = date ( DATETIME_FORMAT, $timestamp );
$result .= "<tr $style><td rowspan='" . (1 + count ( $job )) . "'>@timestamp@</td><td colspan='4' $style></td></tr>"; 
foreach ( $job as $hook_name => $job_def ) {
$row_style = null;
$our_job = ! empty ( $highlight_jobname ) && $highlight_jobname == $hook_name;
$our_job && $row_style = " style='background-color:yellow'";
$a_title = _esc ( 'Click to change the datetime' );
$a_click = "var a=[&quot;ref&quot;,&quot;img&quot;,&quot;edt&quot;];for(var i=0;i<a.length;i++)document.getElementById(a[i]+&quot;_$uniq_id&quot;).style.display=0==i?&quot;none&quot;:&quot;inline-block&quot;;";
$a = "<a id='ref_$uniq_id' class='' onclick='$a_click' title='$a_title' style='cursor:pointer'>" . $timestamp . "</a>";
$job_props = current ( $job_def );
$params = array (
'action' => 'set_wpcron_schedule',
'nonce' => wp_create_nonce_wrapper ( 'set_wpcron_schedule' ),
'schedule' => $job_props ['schedule'] 
);
$img_click = 'jsMyBackup.asyncGetContent(jsMyBackup.ajaxurl,&quot;' . http_build_query ( $params ) . "&time=&quot;+document.getElementById(&quot;edt_$uniq_id&quot;).value);";
$img_title = _esc ( 'Click to update the schedule datetime' );
$img = "<img id='img_$uniq_id' src='" . plugins_url_wrapper ( 'img/save.png', IMG_PATH ) . "' style='display:none;cursor:pointer;' onclick='$img_click' title='$img_title'>";
$input = "<input type='datetime' id='edt_$uniq_id' value='$timestamp' maxlength='19' size='19' style='display:none'>";
$our_job && $timestamp = $a . $img . $input;
$result = str_replace ( '@timestamp@', $timestamp, $result );
$result .= "<tr" . (! empty ( $row_style ) ? $row_style : '') . "><td>$hook_name</td>"; 
foreach ( $job_def as $job_id => $job_props )
if (isset ( $job_props ['interval'] ))
$result .= sprintf ( "<td>%s</td><td>%s</td><td>%s</td></tr>", $job_props ['schedule'], $job_props ['interval'] / 3600, implode ( ';', $job_props ['args'] ) );
}
}
$result = sprintf ( "<style>.schtbl{border: 1px solid #C0C0C0; border-radius: 5px} .schtbl tr td:nth-child(2),.schtbl tr td:nth-child(3){text-align:center;}</style><table class='schtbl'><tr style='background-color: #E0E0D0;'><th>%s</th><th>%s</th><th>%s</th><th>%s<br>(h)</th><th>%s</th></tr>$result</table>", _esc ( 'Next run' ), _esc ( 'Hook name' ), _esc ( 'Schedule' ), _esc ( 'Interval' ), _esc ( 'Args' ) );
return $result . '<table style="width:100%"><tr><td>' . readMoreHere ( 'http://theme.fm/2011/11/wordpress-internals-the-cron-2715' ) . '</td><td style="text-align:right">' . sprintf ( _esc ( 'Local time: %s' ), date ( DATETIME_FORMAT.' e' ) ) . '</td></tr></table>';
}
function get_schedule($schedule_name) {
$result = array ();
$schedules = wp_get_schedules ();
foreach ( $schedules as $schedule => $schedule_def )
if ($schedule == $schedule_name) {
$result = $schedule_def;
break;
}
return $result;
}
function get_schedule_list($message = null, $encode = true) {
return sprintf ( '<p>' . _esc ( '%sBelow is the table of the enabled WP-Cron schedules:%s' ) . '</p>', ! empty ( $message ) ? $message . '<br>' : '', str_replace ( "'", $encode ? "\'" : "'", get_active_wpcrons ( WPCRON_SCHEDULE_HOOK_NAME ) ) );
}
function change_schedule($_logfile, $cron_schedule, $activate = true, $timestamp = null, $return_js = true) {
check_wpcron ();
$opstr = $activate ? _esc ( 'scheduled' ) : (getSpanE ( _esc ( 'removed' ), 'red' ));
is_bool ( $_logfile ) && $_logfile->writeLog ( sprintf ( _esc ( "[%s] %s Cron schedule %s" ) . PHP_EOL, date ( DATETIME_FORMAT ), WPMYBACKUP, $opstr ) );
if (false !== ($timestamp = _call_user_func ( ($activate ? 'activate' : 'deactivate') . '_schedule', $_logfile, $cron_schedule, $timestamp ))) {
$message = sprintf ( _esc ( 'The WP Cron %s backup job was %s%s.' ), '<b>' . $cron_schedule . '</b>', $opstr, $activate ? _esc ( ' at ' ) . getSpanE ( date ( DATETIME_FORMAT, $timestamp ), null, null, 'yellow' ) : '' );
return $return_js ? sprintf ( "parent.popupWindow('%s','%s');", sprintf ( _esc ( 'Schedule %s' ), strip_tags ( $opstr ) ), get_schedule_list ( $message ) ) : str_replace ( "\'", "'", $message );
}
return false;
}
function deactivate_schedule($_logfile, $cron_schedule) {
wp_clear_scheduled_hook ( WPCRON_SCHEDULE_HOOK_NAME );
return true;
}
function activate_schedule($_logfile, $cron_schedule, $timestamp = null) {
false !== wp_next_scheduled ( WPCRON_SCHEDULE_HOOK_NAME ) && wp_clear_scheduled_hook ( WPCRON_SCHEDULE_HOOK_NAME );
$timestamp = null !== $timestamp ? $timestamp : time ();
if (false !== wp_schedule_event ( $timestamp, $cron_schedule, WPCRON_SCHEDULE_HOOK_NAME ))
return $timestamp;
throw new MyException ( sprintf ( _esc ( 'The schedule %s could not be activated.' ), '<b>' . $cron_schedule . '</b>' ) );
}
?>