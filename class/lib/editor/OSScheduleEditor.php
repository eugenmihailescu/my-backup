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
 * @date    : Tue Feb 16 21:41:51 2016 UTC $
 * @file    : OSScheduleEditor.php $
 * 
 * @id      : OSScheduleEditor.php | Tue Feb 16 21:41:51 2016 UTC | Eugen Mihailescu eugenmihailescux@gmail.com $
*/

namespace MyBackup;
class OSScheduleEditor extends ScheduleEditor {
private $_cpanel;
private $_task_win;
private $_cron;
private function _getJavaScripts() {
global $COMPRESSION_APPS, $factory_options;
$get_group_enabled = function (&$group_options, $enabled_str = '_enabled') {
foreach ( $group_options as $option_name => $option_params )
if (false !== strpos ( $option_name, $enabled_str ))
return $option_name;
return false;
};
$get_group_pwd = function (&$group_options, $pwd_pattern = '/pwd$/') {
foreach ( $group_options as $option_name => $option_params )
if (preg_match ( $pwd_pattern, $option_name ))
return '#f00';
return false;
};
ob_start ();
printf ( '<p>' . _esc ( 'If you want to run the backup using your host OS scheduler (eg. %s, %s, %s) then define a new %s within your %s host system. It should call the PHP CLI application (ie. php -f) with the following possible arguments:' ) . '</p>', $this->_task_win, $this->_cron, $this->_cpanel, '<b>' . (isUnix () ? _esc ( 'cron job' ) : _esc ( 'task schedule' )) . '</b>', PHP_OS );
$this->_options_help = printHelp ( true );
$help = str_replace ( array (
PHP_EOL,
"<red>",
"</red>" 
), array (
"<br>",
"<span style='style:red'>",
"</span>" 
), ob_get_contents () );
@ob_end_clean ();
$this->java_scripts [] = "jsMyBackup.globals.help='" . str_replace ( "'", '"', $help ) . "';";
ob_start ();
echo 'parent.globals.root="' . normalize_path ( $this->settings ['dir'] ) . '";';
echo 'parent.globals.OS_CRON_STR=';
$php_path = isWin () ? addslashes ( dirname ( php_ini_loaded_file () ) . DIRECTORY_SEPARATOR . 'php.exe' ) : 'php';
echo '"<b>' . $php_path . '</b> ' . addslashes ( CLASS_PATH . 'cli-backup.php ' );
foreach ( $factory_options as $group => $group_options ) {
foreach ( $group_options as $_param_name => $param_options ) {
if (! empty ( $param_options [1] ) && isset ( $this->settings [$_param_name] ) && $this->settings [$_param_name] != $param_options [0]) {
$depends_on = $get_group_enabled ( $group_options );
$skip_value = $_param_name == $depends_on || isset ( $param_options [4] );
$this->_echoParam ( $_param_name, str_replace ( ':', '', $param_options [1] ), false, $this->settings [$_param_name], $skip_value, $depends_on, false, preg_match ( '/pwd$/', $_param_name ) ? '#f00' : null );
}
}
}
echo '";'; 
echo "parent.toggle_enabled('schedule_grp',document.getElementById('schedule_enabled').checked);";
echo "if(document.getElementById('schedule_wp_cron')!==null){parent.showScheduleInterval(document.getElementById('schedule_wp_cron'));";
echo "parent.toggle_wp_cron(!document.getElementById('schedule_grp_os_cron').checked);}";
$this->java_scripts [] = ob_get_contents ();
@ob_end_clean ();
}
protected function initTarget() {
parent::initTarget ();
$this->hasCustomFrame = false;
$this->_cpanel = getAnchor ( _esc ( 'cPanel' ), 'https://www.drupal.org/node/369267' );
$this->_task_win = getAnchor ( _esc ( 'Task scheduler' ), 'http://windows.microsoft.com/en-au/windows/schedule-task#1TC=windows-7' );
$this->_cron = getAnchor ( _esc ( 'cron' ), 'https://help.ubuntu.com/community/CronHowto' );
$this->_getJavaScripts ();
}
protected function getEditorTemplate() {
$cpanel_note = sprintf ( _esc ( 'You may also be interested on how to define a cron job in %s.' ), $this->_cpanel );
require_once $this->getTemplatePath ( 'schedule.php' );
require_once $this->getTemplatePath ( 'schedule-os_cron.php' );
echo '<tr id="schedule_cron_row4"><td colspan="3">';
include_once $this->getTemplatePath ( 'schedule-os_cron-note.php' );
echo '</td></tr>';
echo '<tr><td colspan="3"><input type="hidden" name="excludedirs" id="excludedirs" value="' . $this->settings ['excludedirs'] . '"></td></tr>';
}
}
?>