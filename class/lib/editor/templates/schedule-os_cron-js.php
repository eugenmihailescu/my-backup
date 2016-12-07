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
 * @version : 0.2.3-37 $
 * @commit  : 56326dc3eb5ad16989c976ec36817cab63bc12e7 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Wed Dec 7 18:54:23 2016 +0100 $
 * @file    : schedule-os_cron-js.php $
 * 
 * @id      : schedule-os_cron-js.php | Wed Dec 7 18:54:23 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

global $COMPRESSION_APPS;
ob_start ();
$i = array ();
foreach ( $this->_schedules as $s => $v )
$i [] = "['$s'," . $v ['interval'] . "]";
echo 'parent.globals.root="' . normalize_path ( $this->settings ['dir'] ) . '";';
echo 'parent.globals.schedule=[' . implode ( ',', $i ) . '];';
echo 'parent.globals.OS_CRON_STR=';
$php_path = isWin () ? addslashes ( dirname ( php_ini_loaded_file () ) . DIRECTORY_SEPARATOR . 'php.exe' ) : 'php';
echo '"<b>' . $php_path . '</b> ' . addslashes ( CLASS_PATH . 'cli-backup.php ' );
$this->_echoParam ( 'url' );
$ctype = $COMPRESSION_APPS [$this->settings ['compression_type']];
if (! empty ( $ctype ))
echo ' <b>--' . $this->_encloseHelpLink ( $ctype ) . '</b>';
if (0 != $this->settings ['compression_type'])
echo ' <b>--' . $this->_encloseHelpLink ( $this->settings ['compression_level'] ) . "</b>";
if ('intern' != $this->settings ['toolchain']) {
if ('bzip' == $ctype)
$this->_echoParam ( 'bzipver' );
$this->_echoParam ( 'toolchain' );
if (isWin ())
$this->_echoParam ( 'cygwin', false, null, false, null, true );
}
$this->_echoParam ( 'dir', false, WPMYBACKUP_ROOT );
$this->_echoParam ( 'wrkdir', false, '&lt;wrkdir&gt;' );
$this->_echoParam ( 'verbose' );
$this->_echoParam ( 'size' );
$this->_echoParam ( 'email' );
$this->_echoParam ( 'dropbox', false, null, false, 'dropbox_enabled' );
$this->_echoParam ( 'dropbox_age', false, null, false, 'dropbox_enabled' );
$this->_echoParam ( 'dropbox_root', false, null, false, 'dropbox_enabled' );
$old_google = $this->settings ['google'];
if (preg_match ( '/(\w)*$/', $this->settings ['google_path_id'], $file_id ))
$this->settings ['google'] = $file_id [0];
$this->_echoParam ( 'google', false, null, false, 'google_enabled' );
$this->settings ['google'] = $old_google;
$this->_echoParam ( 'google_age', false, null, false, 'google_enabled' );
$this->_echoParam ( 'disk', false, null, false, 'disk_enabled' );
$this->_echoParam ( 'disk_age', false, null, false, 'disk_enabled' );
$this->_echoParam ( 'tables', false, null, false, 'mysql_enabled' );
$this->_echoParam ( 'excludeext' );
$this->_echoParam ( 'nocompress' );
$this->_echoParam ( 'excludefiles' );
if (! empty ( $this->settings ['ftphost'] ) && ! empty ( $this->settings ['ftpuser'] )) {
$this->_echoParam ( 'ftphost', false, null, false, 'ftp_enabled' );
$this->_echoParam ( 'ftpport', false, null, false, 'ftp_enabled' );
$this->_echoParam ( 'ftpuser', false, null, false, 'ftp_enabled' );
$this->_echoParam ( 'ftppwd', false, null, false, 'ftp_enabled', false, '#f00' );
$this->_echoParam ( 'ftp', false, null, false, 'ftp_enabled' );
$this->_echoParam ( 'ftppasv', false, null, true, 'ftp_enabled' );
$this->_echoParam ( 'ftpdirsep', false, null, false, 'ftp_enabled' );
}
if (! empty ( $this->settings ['cpusleep'] ) && $this->settings ['cpusleep'] > 0)
$this->_echoParam ( 'cpusleep' );
$this->_echoParam ( 'logdir' );
if ($this->settings ['logrotate']) {
$this->_echoParam ( 'logrotate', false, null, true );
$this->_echoParam ( 'logsize' );
}
echo '";'; 
echo "parent.toggle_enabled('schedule_grp',document.getElementById('schedule_enabled').checked);";
echo "if(document.getElementById('schedule_wp_cron')!==null){parent.showScheduleInterval(document.getElementById('schedule_wp_cron'));";
echo "parent.toggle_wp_cron(!document.getElementById('schedule_grp_os_cron').checked);}";
$this->java_scripts [] = ob_get_contents ();
@ob_end_clean ();
?>