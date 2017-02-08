<?php
/**
 * ################################################################################
 * MyBackup
 * 
 * Copyright 2017 Eugen Mihailescu <eugenmihailescux@gmail.com>
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
 * @version : 1.0-3 $
 * @commit  : 1b3291b4703ba7104acb73f0a2dc19e3a99f1ac1 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Tue Feb 7 08:55:11 2017 +0100 $
 * @file    : welcome.php $
 * 
 * @id      : welcome.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

defined ( __NAMESPACE__.'\\LMGTFY_URL' ) || define ( __NAMESPACE__.'\\LMGTFY_URL', 'http://lmgtfy.com/?q=' );
defined ( __NAMESPACE__.'\\APP_PLUGIN_URI' ) || define ( __NAMESPACE__.'\\APP_PLUGIN_URI', 'https://wordpress.org/plugins/wp-mybackup' );
defined ( __NAMESPACE__.'\\APP_ADDONS_SHOP_URI' ) || define ( __NAMESPACE__.'\\APP_ADDONS_SHOP_URI', 'http://mynixworld.info/shop/' );
defined ( __NAMESPACE__.'\\WPMYBACKUP_LOGS' ) || define ( __NAMESPACE__.'\\WPMYBACKUP_LOGS', strtolower ( preg_replace ( '/[^\w]/', '', WPMYBACKUP ) ) );
defined ( __NAMESPACE__.'\\APP_PLUGIN_FAQ_URI' ) || define ( __NAMESPACE__.'\\APP_PLUGIN_FAQ_URI', APP_ADDONS_SHOP_URI . 'faq-mybackup' );
defined ( __NAMESPACE__.'\\PHP_HOME_URL' ) || define ( __NAMESPACE__.'\\PHP_HOME_URL', 'http://php.net/' );
defined ( __NAMESPACE__.'\\PHP_MANUAL_URL' ) || define ( __NAMESPACE__.'\\PHP_MANUAL_URL', PHP_HOME_URL . 'manual/en/' );
if (! function_exists ( __NAMESPACE__ . '\\lmgtfy' )) {
function lmgtfy($string) {
return LMGTFY_URL . urlencode ( $string );
}
}
isset ( $COMPRESSION_NAMES ) || $COMPRESSION_NAMES = array (0 => 'tar',1 => 'gz',2 => 'bz2' );
if (! isset ( $registered_forward_map )) {
global $registered_forward_map, $forward_compatible_targets;
$registered_forward_map = array ('APP_BACKUP_JOB' => array (_esc ( 'WP backup job' ),null,19 ),'MYSQL_SOURCE' => array (_esc ( 'WP database' ),null,- 1 ),'APP_TABBED_TARGETS' => array (_esc ( 'Copy backup to' ),null,15 ),'APP_SCHEDULE' => array (_esc ( 'Backup Scheduler' ),null,17 ),
'APP_SUPPORT' => array (_esc ( 'Support' ),null,11 ),'APP_LOGS' => array (_esc ( 'Log files' ),null,9 ),'WP_SOURCE' => array (_esc ( 'WP files' ),null,- 4 ),'SRCFILE_SOURCE' => array (sprintf ( _esc ( '%s files' ), PHP_OS ),'any-file-visible-to-the-wp',- 3 ),
'APP_JOB_HISTORY' => array (_esc ( 'Job history' ),'query-job-history',8 ),'APP_STATISTICS' => array (_esc ( 'Statistics' ),'backup-statistics',10 ),'APP_LICENSE' => array (_esc ( 'License' ),null,12 ),'APP_LISTVIEW_TARGETS' => array (_esc ( 'Backup jobs++' ),'backup-wizard',16 ),
'BACKUP_SETTINGS' => array (_esc ( 'Settings' ),'advanced-network-settings',18 ),'APP_EULA' => array (_esc ( 'EULA' ),null,20 ),'APP_RESTORE' => array (_esc ( 'Restore' ),'restore-wizard',21 ),'APP_OS_SCHEDULE' => array (PHP_OS . '-Cron','wp-schedule-the-backup-via-os',22 ),
'APP_WP_SCHEDULE' => array ('WP-Cron',null,23 ),'APP_ADDONDROPIN' => array (_esc ( 'Addons Drop-in' ),'product-category/addons',24 ),'APP_DASHBOARD' => array (_esc ( 'Dashboard' ),null,26 ) );
foreach ( $registered_forward_map as $constant => $tab_info )
$forward_compatible_targets [$tab_info [2]] = array ('title' => $tab_info [0],'link' => null == $tab_info [1] ? '#' : APP_ADDONS_SHOP_URI . $tab_info [1] );
}
$wpmybackup_plugin_link = getAnchor ( WPMYBACKUP, APP_PLUGIN_URI );
$restore_addon_link = getAnchor ( _esc ( 'Restore Addon' ), APP_ADDONS_SHOP_URI . 'shop/restore-wizard' );
$diff_restore_addon_link = getAnchor ( _esc ( 'Differential backup' ), APP_ADDONS_SHOP_URI . 'shop/differential-backup-support' );
$inc_restore_addon_link = getAnchor ( _esc ( 'Incremental backup' ), APP_ADDONS_SHOP_URI . 'shop/incremental-backup-support' );
$arcname_pattern = '[a-z0-9\-\.]';
$gz_bz2_pre = '<pre>' . implode ( '|', array ('gz','bz2' ) ) . '</pre>';
isset ( $on_plugin ) || $on_plugin = false;
isset ( $wp_components ) || $wp_components = array ();
isset ( $dashboard_link ) || $dashboard_link = _esc ( 'Dashboard' );
isset ( $is_wp ) || $is_wp = true;
isset ( $_addons ) || $_addons = array ();
isset ( $_nocheck ) || $_nocheck = true;
isset ( $_init_error ) || $_init_error = false;
isset ( $_video_ids ) || $_video_ids = array ('CmOLBfBRnrE' );
isset ( $_upload_constraint_link ) || $_upload_constraint_link = array ('','' );
isset ( $getImgURL ) || $getImgURL = function ($filename) {
$getFileRelativePath = function ($filename) {
$relpath = str_replace ( realpath ( $_SERVER ['DOCUMENT_ROOT'] ), '', $filename );
DIRECTORY_SEPARATOR != substr ( $relpath, 0, 1 ) && $relpath = DIRECTORY_SEPARATOR . $relpath; 
return dirname ( $relpath );
};
$plugins_url_wrapper = function ($path, $plugin) use (&$getFileRelativePath) {
$plugin_root = str_replace ( DIRECTORY_SEPARATOR, '/', $getFileRelativePath ( $plugin ) );
'/' != substr ( $path, 0, 1 ) && '/' != substr ( $plugin_root, - 1 ) && ($plugin_root .= '/') || ('/' == substr ( $plugin_root, - 1 ) && ($plugin_root = substr ( $plugin_root, 0, - 1 )));
$result = $plugin_root . $path;
return $result;
};
return $plugins_url_wrapper ( 'img/' . $filename, dirname ( __DIR__ ) . DIRECTORY_SEPARATOR . 'img' );
};
isset ( $getTabAnchor ) || $getTabAnchor = function ($tab, $array = null) {
if (isset ( $tab )) {
global $registered_targets;
$tabs = isset ( $array ) ? $array : $registered_targets;
return isset ( $tabs [$tab] ) && isset ( $tabs [$tab] ['title'] ) ? $tabs [$tab] ['title'] : null;
}
return '???';
};
isset ( $getTabAnchorByConstant ) || $getTabAnchorByConstant = function ($constant) use (&$getTabAnchor) {
global $forward_compatible_targets, $registered_forward_map;
$nconstant = 0 !== strpos ( $constant, __NAMESPACE__ . '\\' ) ? __NAMESPACE__ . '\\' . $constant : $constant;
$tab = null !== @constant ( $nconstant ) ? @constant ( $nconstant ) : (isset ( $registered_forward_map [$constant] ) ? $registered_forward_map [$constant] [2] : null);
return $getTabAnchor ( $tab, @constant ( $nconstant ) ? null : $forward_compatible_targets );
};
isset ( $getHumanReadableSize ) || $getHumanReadableSize = function ($size, $precision = 2, $return_what = 0) {
if (PHP_INT_MAX == $size)
return _esc ( 'unknown' );
$units = array ('B','KiB','MiB','GiB','TiB','PiB' );
for($i = 0; abs ( $size ) >= 1024; $i ++)
$size /= 1024;
$i = $i + 1 > count ( $units ) ? count ( $units ) - 1 : $i;
if ($return_what == 1)
return $i;
elseif ($return_what == 2)
return $units [$i];
else
return sprintf ( '%.' . $precision . 'f %s', $size, $units [$i] );
};
isset ( $getUploadLimit ) || $getUploadLimit = function () {
$value = ini_get ( 'upload_max_filesize' );
$units = array ('K','M','G','T','P' );
$multiply = 1;
if (preg_match ( "/[KMGTP]/i", $value, $matches ))
$multiply = pow ( 1024, 1 + array_search ( strtoupper ( $matches [0] ), $units ) );
if (preg_match ( "/\d*/", $value, $matches ) && strlen ( $matches [0] ) > 0)
return intval ( $matches [0] ) * $multiply;
else
return false;
};
?>
<style>
.vertical ul li:active, a:FOCUS {
-moz-box-shadow: none;
-webkit-box-shadow: none;
box-shadow: none;
}
.welcome ul {
list-style: inside none square;
}
.welcome pre {
display: inline-block;
background-color: #E6F7FD;
padding: 1px;
overflow: visible;
}
</style>
<table class="welcome">
<tr>
<td style="text-align: center"><img
src="<?php echo $getImgURL('mybackup-plugin-banner.png');?>">
<div>
<span><?php printf(_esc('Welcome to %s'),WPMYBACKUP);?></span>
</div></td>
</tr>
<tr>
<td><h3 style="margin-top: 2em"><?php _pesc('Thank you for choosing our software!');?></h3></td>
</tr>
<?php
if ($_init_error) {
?>
<tr>
<td
style="border: 1px solid #C0C0C0; padding: 10px; border-radius: 5px;">
<?php
$err = error_get_last ();
$err = sprintf ( _esc ( 'An unexpected error occurred while initializing the application: %s' ), $err ['message'] );
echo '<p style="color:red">', $err, '</p>';
if (! empty ( $_addons ))
printf ( '<p>' . _esc ( 'Click %s to continue.' ) . '</p>', '<input type="button" class="button" value="' . _esc ( 'this button' ) . '" onclick="' . htmlspecialchars ( str_replace ( 'parent.', 'jsMyBackup.', $this->_js_addon_install ) ) . '">' );
?>
</td>
</tr>
<?php
}
?>
<tr>
<td><p><?php
_pesc ( 'Before using the application for the first time please read the instructions below. They will help understand how it works. The more you know about its features and functionalities the happier you will be later.' );
?></p></td>
</tr>
<?php
$schedule_tabs = array ();
defined ( __NAMESPACE__.'\\APP_WP_SCHEDULE' ) && $schedule_tabs [] = $getTabAnchorByConstant ( 'APP_WP_SCHEDULE' );
defined ( __NAMESPACE__.'\\APP_OS_SCHEDULE' ) && $schedule_tabs [] = $getTabAnchorByConstant ( 'APP_OS_SCHEDULE' );
$format_adv_li = function ($option, $hint = '') {
return sprintf ( '<i>%s</i>%s', $option, empty ( $hint ) ? '' : (' (' . $hint . ')') );
};
$fwh = _esc ( 'Free Web Hosting' );
$lmgfy = function ($str) use (&$fwh) {
return '`' . getAnchor ( $str, lmgtfy ( $fwh . ' ' . $str ) ) . '`';
};
echo '<tr><td>';
echo '<p class="highlight-box hintbox rounded-container" style="padding-left: 5px;border-left: 4px solid #1E8CBE;">', sprintf ( _esc ( 'If you are using a %s provider then %s may reach some of their system limitations like %s or %s. Moreover, some %s providers will (temporarely) suspend your website if you exceed these limitation frequently.' ), '<strong>' . $fwh . '</strong>', WPMYBACKUP, $lmgfy ( _esc ( 'CPU Limit Exceeded' ) ), $lmgfy ( _esc ( 'Script Timeout' ) ), $fwh ), '<br>';
echo sprintf ( _esc ( 'In order to overcome that situation you might want to tune the %s.' ), WPMYBACKUP . ' ' . getAnchor ( _esc ( 'Expert settings' ), '#advanced', '_self' ) ), '<br>';
echo sprintf ( _esc ( 'Check also the question %s, %s, %s and %s on %s page.' ), getAnchor ( '#10', APP_PLUGIN_FAQ_URI . '/#q10' ), getAnchor ( '#18', APP_PLUGIN_FAQ_URI . '/#q18' ), getAnchor ( '#20', APP_PLUGIN_FAQ_URI . '/#q20' ), getAnchor ( '#22', APP_PLUGIN_FAQ_URI . '/#q22' ), getAnchor ( _esc ( 'FAQ' ), APP_PLUGIN_FAQ_URI ) ), '</p>';
if (! $_nocheck && $issue_count = count ( $setup_issues )) {
echo '<p>';
printf ( _esc ( 'The following %s issues were found while checked if your system supports this application:' ), '<strong>' . $issue_count . '</strong>' );
echo '<ol style="list-style-type:decimal">';
foreach ( $setup_issues as $ext => $issue ) {
printf ( '<li><strong>%s</strong> - %s</li><ul>', $ext, $issue [CHKSETUP_ENABLED_HINT] );
foreach ( $issue as $k => $v ) {
if (CHKSETUP_ENABLED_HINT == $k)
continue; 
if (CHKSETUP_ENABLED_SETTINGS == $k || CHKSETUP_ENABLED_WRITABLE == $k || CHKSETUP_ENABLED_KEY == $k) {
$val_style = " style='color:" . (1 == $v ? 'green' : 'red') . "'";
$val_str = 1 == $v ? (CHKSETUP_ENABLED_SETTINGS == $k ? _esc ( 'ok' ) : _esc ( 'passed' )) : (CHKSETUP_ENABLED_SETTINGS == $k ? _esc ( 'not working' ) : _esc ( 'failed' ));
} else {
$val_style = '';
$val_str = $v;
}
printf ( '<li>%s : %s</li>', $k, $val_style ? sprintf ( '<issue%s>%s</issue>', $val_style, $val_str ) : $val_str );
}
echo '</ul></li>';
}
echo '</ol>';
printf ( _esc ( 'Use the %s on %s tab for an exhaustive report.' ), '<strong>' . _esc ( 'Check PHP setup' ) . '</strong>', $getTabAnchorByConstant ( 'APP_SUPPORT' ) );
echo '</p>';
}
echo '</td></tr>';
?>	
<tr>
<td>
<ol style="list-style-type: upper-roman;">
<li><a href="#requirements"><?php _pesc('Check if your system meets the requirements');?></a></li>
<li><a href="#configure"><?php _pesc('Configure the global options');?></a></li>
<li><a href="#define"><?php _pesc('Define the backup job');?></a></li>
<li><a href="#run"><?php _pesc('Run the backup job');?></a></li>
<li><a href="#restore"><?php _pesc('Restore a backup copy');?></a>
<ol>
<?php if($is_wp){?>
<li><?php printf(_esc('if using the %s plug-in then:'),$wpmybackup_plugin_link)?><ol>
<li><a href="#wp_full_restore"><?php _pesc('Full backup');?></a></li>
</ol></li>
<?php }?>
<li><?php printf(_esc('if NOT having the %s then:'),$restore_addon_link);?>
<ol style="list-style-type: decimal">
<li><a href="#full_restore"><?php _pesc('Full backup');?></a></li>
<li><a href="#diff_restore"><?php _pesc('Differential backup');?></a></li>
<li><a href="#inc_restore"><?php _pesc('Incremental backup');?></a></li>
</ol></li>
<li><?php printf(_esc('if HAVING the %s then:'),$restore_addon_link);?>
<ol style="list-style-type: decimal">
<li><a href="#addon_restore"><?php _pesc('Restore via Addon');?></a></li>
</ol></li>
</ol></li>
<li><a href="#multijob"><?php _pesc('Define multiple backup jobs');?></a></li>
<li><a href="#addon"><?php _pesc('Install an addon');?></a></li>
<li><a href="#advanced"><?php _pesc('Advanced options');?></a></li>
<li><a href="#debug"><?php _pesc('Troubleshooting');?></a></li>
</ol>
</td>
</tr>
<tr>
<td><p>You may also watch this video tutorial</p>
<div class="video-container">
<iframe
src="http://www.youtube.com/embed/<?php echo empty($_video_ids)?'':$_video_ids[0];?>?autoplay=0<?php echo count($_video_ids)>1?'&playlist='.implode(array_slice($_video_ids, 1)):'';?>">
</iframe>
</div></td>
</tr>
<tr>
<td><br>
<p class="highlight-box hintbox rounded-container"
style="display: inline-block;"><?php printf(_esc('For a more comprehensive tuturial visit the %s page.'),getAnchor(_esc('Tutorials'), APP_ADDONS_SHOP_URI.'tutorials','_self'));?>
</p></td>
</tr>
<tr>
<td><a id="requirements"></a>
<h4>I. <?php _pesc('Check if your system meets the requirements');?></h4>
<p>
<?php printf(_esc('Please click %s%s to start gathering the information about your system (like OS, web software, PHP version, other resources). It will display a table of the required PHP extensions (eg. curl, safe_mode, etc) and also will explain why they are used. Make sure they are tagged as OK/enabled (green color) with one exception - safe_mode - that could be red.'),'<input type="button" class="button" value="'.( $on_plugin ? _esc( 'this button' ) : _esc( 'Check PHP setup' ) ).'"'.($on_plugin?' onclick="jsMyBackup.php_setup();"':'').'>',$on_plugin?'':_esc(' on Support page'));?>
</p></td>
</tr>
<tr>
<td><a id="configure"></a>
<h4>II. <?php _pesc('Configure the global options');?></h4>
<p>
<?php  _pesc ( 'Before you do anything else make sure your set the following global options:' );?>
</p>
<?php
if ($is_wp)
printf ( _esc ( 'If your site is running on a multi-network|multisite installation then make sure your Network Admin customizes the `Global working directory` option on %s page.' ), getAnchor ( _esc ( 'Network Settings' ), network_admin_url ( 'settings.php' ) . '#' . WPMYBACKUP_LOGS ) );
?>
<ol style="list-style-type: decimal">
<li><?php echo sprintf(_esc('go to the %s tab then set:'),$getTabAnchorByConstant('APP_BACKUP_JOB'));?>
<ul>
<li><?php _pesc('either the backup name or the backup prefix');?></li>
<li><?php _pesc('the temporary working directory (make sure you are read/write access)');?></li>
<li><?php _pesc('your email address (where the notification will be sent)');?></li>
</ul></li>
</ol>	
<?php _pesc('Normally these steps should be done only once (ie. at install time).');?></td>
</tr>
<tr>
<td><a id="define"></a>
<h4>III. <?php _pesc('Define the backup job');?></h4>
<p><?php _pesc('A backup job represents the totality of those options that together instruct the application what to backup, how to backup and where to copy the backup. Normally these steps should be done only once (at install time) but also when you decide to change the backup source/destination:');?></p>
<ol style="list-style-type: decimal">
<li><a id="define_source"></a><?php echo sprintf(_esc('go to the %s tab then set the directory you want to backup'),$getTabAnchorByConstant($is_wp?'WP_SOURCE':'SRCFILE_SOURCE'));?></li>
<li><a id="define_mysql"></a><?php echo sprintf(_esc('go to the %s tab then check the <b>Enabled</b> checkbox'),$getTabAnchorByConstant('MYSQL_SOURCE'));?></li>
<li><a id="define_target"></a><?php echo sprintf(_esc('go to the %s tab and for each destination (aka target) where you want to store your backup archives make sure that you:'),$getTabAnchorByConstant('APP_TABBED_TARGETS'));?>
<ul>
<li><?php _pesc('check the <b>Enabled</b> checkbox to enable the usage of that target or uncheck it if you don`t want to use it');?>
</li>
<li><?php _pesc('set the appropiate connection parameters (if any) and more important the remote directory where the backups will be stored');?></li>
<li><?php _pesc('set the backup retention time');?></li>
</ul></li>
</ol></td>
</tr>
<tr>
<td><a id="run"></a>
<h4>IV. <?php _pesc('Run the backup job');?></h4>
<p><?php _pesc('Once your global options and the backup job are defined all that must to be done is the backup itself. It can be launched manually (one-click backup) or it can be scheduled to be automatically executed (by WordPress or your OS) at a specific time in the future:');?></p>
<ul>
<li><?php _pesc('run the backup at your request');?>
<ol style="list-style-type: decimal">
<li><?php echo sprintf(_esc('go to the %s tab and click the button <b>Run Backup Now</b>'),$getTabAnchorByConstant('APP_BACKUP_JOB'));?></li>
<li><?php _pesc('watch its progress; a scrollable window will be shown containing the job events and messages');?></li>
</ol></li>
<li><a id="run_schedule"></a><?php _pesc('run the backup automatically at a scheduled time');?>
<ol style="list-style-type: decimal">
<li><?php echo sprintf(_esc('go to the %s tab and then select the %s child tab, depending on what scheduler you want to use:'),$getTabAnchorByConstant('APP_SCHEDULE'),implode(_esc('or'), $schedule_tabs));?>
<ul>
<?php if($is_wp){?>
<li><b>WP-Cron</b>
<ol style="list-style-type: decimal">
<li><?php _pesc('check the <b>Enable scheduler</b> checkbox then select one of those predefined schedule options and set the <b>Next run</b> datetime value (in the past if you want to start immediately, otherwise in the future)');?>
</li>
</ol></li>
<?php }?>
<li><b>OS-Cron</b><?php $is_wp&&printf(' ('._esc('requires the %s addon').')',getAnchor(_esc('Schedule the WP backup via OS'), APP_ADDONS_SHOP_URI.'wp-schedule-the-backup-via-os'));?>
<ol style="list-style-type: decimal">
<li><?php _pesc('check the <b>Enable scheduler</b> checkbox then copy the command-line (that was automatically created) content in clipboard');?>
</li>
<li><?php printf(_esc('open your OS scheduler (eg. %s in Windows, %s on Linux or %s in your web hoster cPanel), set the appropiate execution time/frequency and nonetheless the command to be run as the command you copied earlier in clipboard'),'<a href="http://windows.microsoft.com/en-au/windows/schedule-task#1TC=windows-7" target="_blank">'._esc('Task Scheduler').'</a>','<a href="https://help.ubuntu.com/community/CronHowto" target="_blank">cron</a>','<a href="https://www.drupal.org/node/369267" target="_blank">'._esc('something similar').'</a>');?>
</li>
</ol></li>
</ul></li>
</ol></li>
</ul></td>
</tr>
<tr>
<td><a id="restore"></a>
<h4>V. <?php _pesc('Restore a backup copy');?></h4>
<?php if($is_wp){?>
<p>
<?php printf(_esc('If you are using the WordPress version of this software (%s) then restoring a backup can be done as described below:'),$wpmybackup_plugin_link);?>
</p> <a id="wp_full_restore"></a>
<h5>V.1.1 <?php _pesc('Full restore');?></h5>
<ol style="list-style-type: lower-latin">
<li><?php printf(_esc('restore the last successful backup created with %s'),WPMYBACKUP);?><ol>
<li><?php printf(_esc('go to %s tab'),$dashboard_link);?></li>
<li><?php printf(_esc('if a completed (ie. successful) backup is found then a "Last completed backup" widget should be visible; click the %s, where XXX is the last successful backup job Id'),'<pre>'._esc('Restore Backup #XXX').'</pre>');?></li>
</ol></li>
<li id="wp_full_restore_extern"><?php printf(_esc('restore a backup stored externally created (or not) by %s'),$wpmybackup_plugin_link);?>
<ol>
<li><?php printf(_esc('go to %s tab'),$dashboard_link);?></li>
<li><?php printf(_esc('drag & drop (or %s) some backup archives into the dotted area shown on the dashboard page, with regards to:'),'<pre>'._esc('Select').'</pre>');?>
<ul>
<li><?php printf(_esc('supported formats (%s) are: %s'),'<i>EXT</i>',implode('|', $COMPRESSION_NAMES));?></li>
<li><?php printf(_esc('%s archives are expected to be tar\'ed before compression (ie. .tar.%s)'),$gz_bz2_pre,$gz_bz2_pre);?></li>
<li><?php printf(_esc('MySQL database backup archive should have the following pattern: %s'),$arcname_pattern.'<strong>.sql</strong>.<i>EXT</i>');?></li>
<li><?php printf(_esc('WordPress files backup archive should have the following pattern: %s, where XXX={%s}'),$arcname_pattern.'<strong>-XXX</strong>.<i>EXT</i>',implode(',', $wp_components));?></li>
<li><?php
printf ( _esc ( 'The uploaded file size should not be larger than your php.ini %s.' ), implode ( '|', $_upload_constraint_link ) );
! (defined ( __NAMESPACE__.'\\IMPORT_PAGE' ) && IMPORT_PAGE) && printf ( ' ' . _esc ( 'Your current php.ini is configured such that the uploaded file size cannot be larger than %s.' ) . ' ' . _esc ( 'You may overcome this by using %s option (see Expert settings).' ), $getHumanReadableSize ( $getUploadLimit () ), '<strong>' . _esc ( 'Upload files in chunks' ) . '</strong>' );
?>
</li>
</ul></li>
<li><?php printf(_esc('click the %s button under the selected file area (N is the number of selected files)'),'<pre>'._esc('Restore N files').'</pre>');?></li>
</ol></li>
</ol>
<?php }?>
<p>
<?php printf(_esc('If not using the WP version and don`t have installed the %s then restoring the backup assumes that (i) you have access to your website filesystem and/or MySQL database and (ii) you have a minimal knowledge of using SSH/FTP/mysqldump/phpMyAdmin or similar utilities that basically allow you to copy the files from your local system to the remote system.'),$restore_addon_link);?>
</p> <a id="full_restore"></a>
<h5>V.2.1 <?php _pesc('Full restore');?></h5>
<ol style="list-style-type: decimal">
<li><?php _pesc('download the backup archive from the remote storage location');?></li>
<li><?php _pesc('extract to a temporary directory the backup archive(s)');?></li>
<li><?php _pesc('connect via SSH/FTP/whatever the remote location and copy whatever file you want from the temporary directory to the remote location where your website files are located');?></li>
<li><?php printf(_esc('in case you want to restore also the database content then you may use an application like %s (see this %s) to import the .sql file from the *.sql.* archive extracted earlier at the temporary location.'),'<a href="https://en.wikipedia.org/wiki/PhpMyAdmin" target="_blank">phpMyAdmin</a>','<a href="https://www.youtube.com/watch?v=jW5lrS6EUPM" target="_blank">'._esc('video tutorial').'</a>');?></li>
</ol> <a id="diff_restore"></a>
<h5>V.2.2 <?php printf(_esc('Differential restore (requires %s)'),$diff_restore_addon_link);?></h5>
<ol style="list-style-type: decimal">
<li><?php _pesc('find the last full backup (F) you are interested in');?></li>
<li><?php _pesc('find the last differenatial backup (D) created between the date of (F) and the date of the next full backup');?></li>
<li><?php printf(_esc('follow the same steps mentioned at %s for all found archives'),getAnchor('V.2.1', '#full_restore','_self'));?></li>
</ol> <a id="inc_restore"></a>
<h5>V.2.3 <?php printf(_esc('Incremental restore (requires %s)'),$inc_restore_addon_link);?></h5>
<ol style="list-style-type: decimal">
<li><?php _pesc('find the last full backup (F) you are interested in');?></li>
<li><?php _pesc('find ALL the incremental backups (I) created between the date of (F) and the date of the next full backup');?></li>
<li><?php printf(_esc('follow the same steps mentioned at %s for all found archives'),getAnchor('V.2.1', '#full_restore','_self'));?></li>
</ol> <a id="addon_restore"></a>
<h5>V.2.4 <?php _pesc('Restore via Addon');?></h5> <box
class="highlight-box hintbox rounded-container"
style="display:block">
<p>
<?php printf(_esc('However, if you have installed the %s then restoring the backup is like shooting fish in a barrel:'),$restore_addon_link);?>
</p>
<ul style="list-style-position: inside;">
<li><?php echo sprintf(_esc('go to the %s tab then follow the instruction provided there. Basically is just a "next-next-finish" 6-steps task assisted by Wizard.'),$getTabAnchorByConstant('APP_RESTORE'));?></li>
<li><?php printf(_esc('for restoring an incremental or differential backup make sure you select the restore point %s option and not %s (see step 3 of 6)'),'<strong>'._esc('from a backup target').'</strong>','<strong>'._esc('from a date interval').'</strong>');?></li>
</ul>
</box></td>
</tr>
<tr>
<td><a id="multijob"></a>
<h4>VI. <?php _pesc('Define multiple backup jobs');?></h4>
<p>
<?php printf(_esc('This assumes that you have installed the %s addon.'),'<a href="'.APP_ADDONS_SHOP_URI.'shop/backup-wizard/" target="_blank">'._esc('Advanced backup Wizard').'</a>');?>
</p>
<p><?php printf(_esc('Sometimes you want to create different backup jobs for different purposes. For instance a job that will pack your images into a ZIP archive then upload it to your Google Drive, another job that will pack with GZIP compression and encrypt your MySQL database and then upload it to your Dropbox drive, a job that will pack with BZip2 compresssion some other directory then upload it to a FTP server.</p>Using the backup procedure(s) described above this wouldn`t work. It requires a tool that allows you to define and run in a batch all these different backup jobs. The %s is just what we need. Defining such a job is just a 6-steps task where you are assisted by a Wizard:'),'<a href="'.APP_ADDONS_SHOP_URI.'shop/backup-wizard/" target="_blank">'._esc('Advanced backup Wizard').'</a>');?></p>
<ol style="list-style-type: decimal">
<li><?php echo sprintf(_esc('go to the %s tab then start defining a new job by clicking the <b>Add new</b> button'),$getTabAnchorByConstant('APP_LISTVIEW_TARGETS'));?>
</li>
<li><?php printf(_esc('select the target type (ie. disk,FTP,Dropbox,etc) and also set a short description of your job (like "%s")'),'domain.com PNGs @ Dropbox');?></li>
<li><?php printf(_esc('set the job global options (like %s)'),'<a href="#configure">I '._esc('above').'</a>');?>
</li>
<li><?php printf(_esc('set the job backup source (like %s)'),'<a href="#define_source">II.1 '._esc('above').'</a>');?>
</li>
<li><?php printf(_esc('eventually set the job MySQL source (like %s)'),'<a href="#define_mysql">II.2 '._esc('above').'</a>');?>
</li>
<li><?php printf(_esc('set the job destination folder (like %s)'),'<a href="#define_target">II.3 '._esc('above').'</a>');?>
</li>
<li><?php printf(_esc('eventually schedule the job (like %s)'),'<a href="#run_schedule">III '._esc('above').'</a>');?>
</li>
</ol><?php _pesc('Once you have defined all necessary jobs you may run each one at a time or any of them in the same batch by clicking the <b>Run Backup</b> button. To exclude some jobs from running in batch make sure you uncheck their <b>Enabled</b> checkbox.');?></td>
</tr>
<tr>
<td><a id="addon"></a>
<h4>VII. <?php _pesc('Install an addon');?></h4>
<p>
<?php
$a1 = getAnchor ( WPMYBACKUP . ' Pro', '' . APP_ADDONS_SHOP_URI . 'shop/wp-mybackup-pro' );
$a2 = getAnchor ( _esc ( '20+ available addons' ), APP_ADDONS_SHOP_URI . 'product-category/addons/' );
$a3 = getAnchor ( _esc ( 'HTML5 compatible browsers only' ), 'http://www.w3schools.com/tags/att_input_multiple.asp' );
printf ( _esc ( 'The %s version allows you to extend its core functionality by installing any of those %s.' ), $a1, $a2 );
_pesc ( 'The installation procees is straightforward:' );
?>
</p>
<ol style="list-style-type: decimal">
<li><?php printf(_esc('Install one or multiple addons at once (multiple available on %s)'),$a3);?><ol
style="list-style-type: decimal">
<li><?php echo sprintf(_esc('go to the %s tab and expand the <b>Export settings</b> panel'),$getTabAnchorByConstant('APP_LICENSE'));?></li>
<li><?php _pesc('click the <b>Browse/Choose file</b> button then select the addon files (*.tar.bz2)');?></li>
<li><?php _pesc('click the <b>Install Add-on</b> button');?></li>
</ol></li>
<li><?php printf(_esc('Install multiple addons at once on non-%s'),$a3);?><ol
style="list-style-type: decimal">
<li><?php printf(_esc('copy the addon files (*.tar.bz2) to the %s folder'),$dropin_dir);?></li>
<li><?php echo sprintf(_esc('go to the %s tab'),$getTabAnchorByConstant('APP_LICENSE'));?></li>
<li><?php echo sprintf(_esc('the %s page should be shown; follow the instructions there'),$getTabAnchorByConstant('APP_ADDONDROPIN'));?></li>
</ol></li>
</ol></td>
</tr>
<tr>
<td><a id="advanced"></a>
<h4>VIII. <?php _pesc('Advanced options');?></h4>
<p><?php printf(_esc('By default the %s settings are tunned to fit everyone expectations. However, you might want to tune these settings in order to fit your needs. Here are few options you way want to consider:'),WPMYBACKUP);?></p>
<ol>
<li><?php printf('%s '._esc('Expert settings'),$getTabAnchorByConstant('APP_BACKUP_JOB'));?><ul>
<li><?php echo $format_adv_li(_esc('Use file relative path'),_esc('important for restoring the backup at other location'));?></li>
<li><?php echo $format_adv_li(_esc('Script memory limit'),_esc('inline with hosting limitations'));?></li>
<li><?php echo $format_adv_li(_esc('Max. execution time'),_esc('inline with hosting limitations'));?></li>
<li><?php echo $format_adv_li(_esc('CPU throttling'),_esc('overcomes `CPU Limit Exceeded`'));?></li>
</ul></li>
<li><?php printf('%s '._esc('Expert settings'),$getTabAnchorByConstant($is_wp?'WP_SOURCE':'SRCFILE_SOURCE'));?><ul>
<li><?php echo $format_adv_li(_esc('Do not compress files by extension'),_esc('spare the CPU when possible'));?></li>
<li><?php echo $format_adv_li(_esc('Exclude files by extension'),_esc('eg. do not backup some huge files'));?></li>
<li><?php echo $format_adv_li(_esc('Exclude file links'));?></li>
</ul></li>
</ol></td>
</tr>
<tr>
<td><a id="debug"></a>
<h4>VIII. <?php _pesc('Troubleshooting');?></h4>
<p><?php _pesc('Hopefully you will never encounter a problem thus you will never have to follow the instructions below. But we live in an imperfect world where imperfect people (like me) write imperfect software (like this one) so probably this chapter cannot be avoided forever.');?></p>
<p><?php _pesc('What to do when something doesn`t work as expected:');?></p>
<ol style="list-style-type: decimal">
<li><?php printf(_esc('You are not the only one who might encounter a specific issue => check the %s. If it does not help then continue to read below.'),getAnchor('FAQ', APP_PLUGIN_FAQ_URI));?></li>
<li><?php printf(_esc('make sure that %s'),'<a href="#requirements">'._esc('your system meets the requirements').'</a>');?></li>
<li><?php _pesc('if the application provides an error message or any other kind of output that seems related to the problem you encountered try to follow the instruction shown there (if any)');?></li>
<li><?php printf(_esc('check again the %s and %s; make sure they have the expected values'),'<a href="#configure">'._esc('global options').'</a>','<a href="#define">'._esc('the job settings').'</a>');?>
</li>
<li><?php printf(_esc('if the application shows a warning/error message (%s) that you suspect to be the root of the problem then we have to bring the heavy artillery:'),'<a href="'.PHP_MANUAL_URL.'internals2.ze1.zendapi.php#internals2.ze1.zendapi.tab.error-messages" target="_blank">'._esc('see example').'</a>');?>
<ol style="list-style-type: decimal">
<li><?php echo sprintf(_esc('go to the %s tab then in the <b>Expert settings</b> panel make sure you set ON the following options:'),$getTabAnchorByConstant('APP_SUPPORT'));?>
<ul>
<li><?php _pesc('Debug trace ON');?></li>
<li><?php _pesc('Curl debug ON');?></li>
<li><?php _pesc('Statistics debug ON');?></li>
<li><?php _pesc('SMTP debug ON');?></li>
<li><?php _pesc('Restore debug ON');?></li>
</ul><?php _pesc('and unset/uncheck the following options:');?>
<ul>
<li><?php _pesc('Yayui optimize ON');?></li>
</ul></li>
<li><?php echo sprintf(_esc('Re-execute the job or whatever cause the problem you are debugging then check the following log(s) in the %s tab:'),$getTabAnchorByConstant('APP_LOGS'));?>
<ul>
<li><?php _pesc('if the problem seems to be related to network connection/authentication then check the <b>Curl Debug log</b>');?>
</li>
<li><?php _pesc('if the problem seems to be related to email (eg. email not sent) then check the <b>SMTP Debug log</b>');?>
</li>
<li><?php _pesc('if the problem seems to be related to some options not saved you may check the <b>Trace Action log</b>; this log traces all requests (like save, tab changed, etc) sent from your browser to this application; if you are a (former) sysadmin or coder you might eventually hack the problem');?>
</li>
<li><?php printf(_esc('if the problem seems to be more an unexpected warning/error thrown by the PHP/web server then probably something is rotten in the state of Denmark (I live in Sweden so I know what I am talking about). If that`s the case then open a support ticket by following the instruction found at the %s. Make sure you have downloaded all the log files mentioned earlier together with the <b>Jobs log</b> and the <b>Full log</b>. Moreover, the information provided by the <b>%s</b> button (in the %s tab) is also very useful when open a helpdesk ticket.'),'<a href="'.APP_ADDONS_SHOP_URI.'get-support/" target="_blank">'._esc('Support Center').'</a>','<a class="help" onclick="jsMyBackup.php_setup();">'._esc('Check PHP setup').'</a>',$getTabAnchorByConstant('APP_SUPPORT'));?>
</li>
</ul></li>
</ol></li>
</ol></td>
</tr>
<tr>
<td><p class="highlight-box hintbox rounded-container"><?php _pesc('As a software developer I did helpdesk, troubleshooting and support (level 1 to 3) for many, many years now. Hopefully I can help you finding the cause and fix the problem.');?></p></td>
</tr>
</table>
<?php
if (! empty ( $_addons )) {
printf ( '<input type="hidden" name="dropin_files" value="%s">', implode ( ',', $_addons ) );
}
?>