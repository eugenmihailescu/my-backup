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
 * @version : 0.2.3-34 $
 * @commit  : 433010d91adb8b1c49bace58fae6cd2ba4679447 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Wed Nov 30 15:38:35 2016 +0100 $
 * @file    : wp-schedule.php $
 * 
 * @id      : wp-schedule.php | Wed Nov 30 15:38:35 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

function is_wpcron_disabled() {
return defined( __NAMESPACE__.'\\DISABLE_WP_CRON' ) && DISABLE_WP_CRON;
}
function is_wpcron_alternated() {
return defined( __NAMESPACE__.'\\ALTERNATE_WP_CRON' ) && ALTERNATE_WP_CRON;
}
function check_wpcron() {
if ( is_wpcron_disabled() )
throw new MyException( 
sprintf( 
_esc( 
'WPCron is disabled. Check the %s constant in file %s<br>You may use the expert option <b>DISABLE_WP_CRON=false</b> to try enabling the WP Cron.' ), 
'<b>DISABLE_WP_CRON</b>', 
'<br>' . get_wp_config_path() ) );
}
function get_active_wpcrons( $highlight_cron_pattern = null ) {
$cron_jobs = get_option( 'cron' );
$result = '';
$style = "style='background-color:#f0f0f0;font-weight:bold'";
$uniq_id = uniqid();
foreach ( $cron_jobs as $timestamp => $job ) {
if ( ! ( is_array( $job ) && is_numeric( $timestamp ) ) )
continue;
$timestamp = date( DATETIME_FORMAT, $timestamp );
$result .= "<tr $style><td rowspan='" . ( 1 + count( $job ) ) .
"' style='text-align:center'>@timestamp@</td><td colspan='4' $style></td></tr>"; 
foreach ( $job as $hook_name => $job_def ) {
$row_style = null;
$our_job = ! empty( $highlight_cron_pattern ) && preg_match( $highlight_cron_pattern, $hook_name );
$our_job && $row_style = " style='background-color:yellow'";
$a_title = _esc( 'Click to change the datetime' );
$a_click = "var a=[&quot;imgrm&quot;,&quot;ref&quot;,&quot;img&quot;,&quot;edt&quot;];for(var i=0;i<a.length;i++)document.getElementById(a[i]+&quot;_$uniq_id&quot;).style.display=i<2?&quot;none&quot;:&quot;inline-block&quot;;";
$a = "<a id='ref_$uniq_id' class='' onclick='$a_click' title='$a_title' style='cursor:pointer'>" . $timestamp .
"</a>";
$job_props = current( $job_def );
$params = array( 
'action' => 'set_wpcron_schedule', 
'nonce' => wp_create_nonce_wrapper( 'set_wpcron_schedule' ), 
'schedule' => $job_props['schedule'] );
$img_click = 'jsMyBackup.asyncGetContent(jsMyBackup.ajaxurl,&quot;' . http_build_query( $params ) .
"&time=&quot;+document.getElementById(&quot;edt_$uniq_id&quot;).value);";
$img_title = _esc( 'Click to update the schedule datetime' );
$img = sprintf( 
"<img id='img_%s' src='%s' style='display:none;cursor:pointer;' onclick='%s' title='%s'>", 
$uniq_id, 
plugins_url_wrapper( 'img/save.png', IMG_PATH ), 
$img_click, 
$img_title );
$input = "<input type='datetime' id='edt_$uniq_id' value='$timestamp' maxlength='19' size='19' style='display:none'>";
if ( $our_job ) {
$timestamp = $a . $img . $input;
$img_title = _esc( 'Click to remove the schedule entry' );
$timestamp = sprintf( 
"<img id='imgrm_%s' style='cursor:pointer' src='%s' onclick='jsMyBackup.remove_schedule(\"%s\")' title='%s'/>", 
$uniq_id, 
plugins_url_wrapper( 'img/user-trash.png', IMG_PATH ), 
$hook_name, 
$img_title ) . $timestamp;
}
$result = str_replace( '@timestamp@', $timestamp, $result );
$result .= "<tr" . ( ! empty( $row_style ) ? $row_style : '' ) . "><td>$hook_name</td>"; 
foreach ( $job_def as $job_id => $job_props )
if ( isset( $job_props['interval'] ) )
$result .= sprintf( 
"<td>%s</td><td>%s</td><td>%s</td></tr>", 
$job_props['schedule'], 
$job_props['interval'] / 3600, 
implode( ';', $job_props['args'] ) );
}
}
ob_start();
?>
<table class='schtbl'>
<tr style='background-color: #E0E0D0;'>
<th><?php echo _esc( 'Next run' );?></th>
<th><?php echo _esc( 'Hook name' );?></th>
<th><?php echo _esc( 'Schedule' );?></th>
<th><?php echo _esc( 'Interval' );?><br>(h)</th>
<th><?php echo _esc( 'Args' );?></th>
</tr>
<?php echo $result;?>
</table>
<?php
return str_replace( array("\n",PHP_EOL), '', ob_get_clean() ) . '<table style="width:100%"><tr><td>' .
readMoreHere( 'http://theme.fm/2011/11/wordpress-internals-the-cron-2715' ) .
'</td><td style="text-align:right">' . sprintf( _esc( 'Local time: %s' ), date( DATETIME_FORMAT . ' e' ) ) .
'</td></tr></table>';
}
function get_schedule_list( $message = null, $encode = true ) {
return sprintf( 
'<p>' . _esc( '%sBelow is the table of the enabled WP-Cron schedules:%s' ) . '</p>', 
! empty( $message ) ? $message . '<br>' : '', 
str_replace( 
"'", 
$encode ? "\'" : "'", 
get_active_wpcrons( '/' . WPCRON_SCHEDULE_HOOK_NAME . '|' . WPMYBACKUP_LOGS . '_\d+/' ) ) );
}
function change_schedule( $_logfile, $recurrance, $activate = true, $cron_hook = WPCRON_SCHEDULE_HOOK_NAME, $timestamp = null, $return_js = true ) {
check_wpcron();
$opstr = $activate ? _esc( 'scheduled' ) : ( getSpanE( _esc( 'removed' ), 'red' ) );
$_logfile instanceof LogFile && $_logfile->writelnLog( 
sprintf( _esc( "[%s] %s Cron schedule %s" ) , date( DATETIME_FORMAT ), WPMYBACKUP, $opstr ) );
if ( false !== ( $timestamp = _call_user_func( 
( $activate ? 'activate' : 'deactivate' ) . '_schedule', 
$_logfile, 
$recurrance, 
$cron_hook, 
$timestamp ) ) ) {
$message = sprintf( 
_esc( 'The WP Cron %s backup job was %s%s.' ), 
'<b>' . $recurrance . '</b>', 
$opstr, 
$activate ? _esc( ' at ' ) . getSpanE( date( DATETIME_FORMAT, $timestamp ), null, null, 'yellow' ) : '' );
return $return_js ? sprintf( 
"parent.popupWindow('%s','%s');", 
sprintf( _esc( 'Schedule %s' ), strip_tags( $opstr ) ), 
get_schedule_list( $message ) ) : str_replace( "\'", "'", $message );
}
return false;
}
function deactivate_schedule( $_logfile, $recurrance, $cron_hook = WPCRON_SCHEDULE_HOOK_NAME ) {
wp_clear_scheduled_hook( $cron_hook );
return true;
}
function activate_schedule( $_logfile, $recurrance, $cron_hook = WPCRON_SCHEDULE_HOOK_NAME, $timestamp = null ) {
false !== wp_next_scheduled( $cron_hook ) && wp_clear_scheduled_hook( $cron_hook );
$timestamp = null !== $timestamp ? $timestamp : time();
if ( false !== wp_schedule_event( $timestamp, $recurrance, $cron_hook ) )
return $timestamp;
throw new MyException( sprintf( _esc( 'The schedule %s could not be activated.' ), '<b>' . $recurrance . '</b>' ) );
}
?>