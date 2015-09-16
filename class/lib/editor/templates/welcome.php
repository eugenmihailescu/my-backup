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
 * @version : 0.2.2 $
 * @commit  : 23a9968c44669fbb2b60bddf4a472d16c006c33c $
 * @author  : Eugen Mihailescu <eugenmihailescux@gmail.com> $
 * @date    : Wed Sep 16 11:33:37 2015 +0200 $
 * @file    : welcome.php $
 * 
 * @id      : welcome.php | Wed Sep 16 11:33:37 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;
?>
<table class="welcome">
<tr>
<td style="text-align: center"><img
src="<?php echo $this->getImgURL('mybackup-plugin-banner.png');?>">
<div>
<span><?php printf(_esc('Welcome to %s'),WPMYBACKUP);?></span>
</div></td>
</tr>
<tr>
<td><h3 style="margin-top: 2em"><?php _pesc('Thank you for choosing our software!');?></h3></td>
</tr>
<?php
if ($this->_init_error) {
?>
<tr>
<td style="border: 1px solid #C0C0C0; padding: 10px; border-radius: 5px;">
<?php
$err = error_get_last ();
$err = sprintf ( _esc ( 'An unexpected error occured while initializing the application: %s' ), $err ['message'] );
echo '<p style="color:red">', $err, '</p>';
if (! empty ( $this->_addons ))
printf ( '<p>' . _esc ( 'Click %s to continue.' ) . '</p>', '<input type="button" class="button" value="' . _esc ( 'this button' ) . '" onclick="' . htmlspecialchars ( str_replace ( 'parent.', 'js55f93aab8f090.', $this->_js_addon_install ) ) . '">' );
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
if ($issue_count = count ( $setup_issues )) {
echo '<tr><td class="highlight-box hintbox rounded-container"><p>';
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
printf ( _esc ( 'Use the %s on %s tab for an exhaustive report.' ), '<strong>' . _esc ( 'Check PHP setup' ) . '</strong>', getTabAnchor ( APP_SUPPORT ) );
echo '</p>';
echo '</td></tr>';
}
?>	
<tr>
<td>
<ol style="list-style-type: upper-roman;">
<li><a href="#requirements"><?php _pesc('Check if your system meets the requirements');?></a></li>
<li><a href="#configure"><?php _pesc('Configure the global options');?></a></li>
<li><a href="#define"><?php _pesc('Define the backup job');?></a></li>
<li><a href="#run"><?php _pesc('Run the backup job');?></a></li>
<li><a href="#restore"><?php _pesc('Restore a backup copy');?></a>
<ol style="list-style-type: decimal">
<li><a href="#full_restore"><?php _pesc('Full backup');?></a></li>
<li><a href="#diff_restore"><?php _pesc('Differential backup');?></a></li>
<li><a href="#inc_restore"><?php _pesc('Incremental backup');?></a></li>
</ol></li>
<li><a href="#multijob"><?php _pesc('Define multiple backup jobs');?></a></li>
<li><a href="#addon"><?php _pesc('Install an addon');?></a></li>
<li><a href="#debug"><?php _pesc('Troubleshooting');?></a></li>
</ol>
</td>
</tr>
<tr>
<td><p>You may also watch this video tutorial</p>
<div class="video-container">
<iframe
src="http://www.youtube.com/embed/<?php echo empty($this->_video_ids)?'':$this->_video_ids[0];?>?autoplay=0<?php echo count($this->_video_ids)>1?'&playlist='.implode(array_slice($this->_video_ids, 1)):'';?>">
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
<?php printf(_esc('Please click %s to start gathering the information about your system (like OS, web software, PHP version, other resources). It will display a table of the required PHP extensions (eg. curl, safe_mode, etc) and also will explain why they are used. Make sure they are tagged as OK/enabled (green color) with one exception - safe_mode - that could be red.'),'<input type="button" class="button" value="'._esc('this button').'" onclick="js55f93aab8f090.php_setup();">');?>
</p></td>
</tr>
<tr>
<td><a id="configure"></a>
<h4>II. <?php _pesc('Configure the global options');?></h4>
<p>
<?php  _pesc ( 'Before you do anything else make sure your set the following global options:' );?>
</p>
<ol style="list-style-type: decimal">
<li><?php _pesc('go to the <b>Backup</b> tab then set:');?>
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
<li><a id="define_source"></a><?php _pesc('go to the <b>Backup source</b> tab then set the directory you want to backup');?></li>
<li><a id="define_mysql"></a><?php _pesc('go to the <b>MySQL source</b> tab then check the <b>Enabled</b> checkbox');?></li>
<li><a id="define_target"></a><?php _pesc('go to the <b>Backup target</b> tab and for each destination (aka target) where you want to store your backup archives make sure that you:');?>
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
<li><?php _pesc('go to the <b>Backup</b> tab and click the button <b>Run Backup Now</b>');?></li>
<li><?php _pesc('watch its progress; a scrollable window will be shown containing the job events and messages');?></li>
</ol></li>
<li><a id="run_schedule"></a><?php _pesc('run the backup automatically at a scheduled time');?>
<ol style="list-style-type: decimal">
<li><?php _pesc('go to the <b>Schedule</b> tab and then select the <b>WP-Cron</b> or <b>OS-Cron</b> child tab, depending on what scheduler you want to use:');?>
<ul>
<li><b>WP-Cron</b>
<ol style="list-style-type: decimal">
<li><?php _pesc('check the <b>Enable scheduler</b> checkbox then select one of those predefined schedule options and set the <b>Next run</b> datetime value (in the past if you want to start immediately, otherwise in the future)');?>
</li>
</ol></li>
<li><b>OS-Cron</b>
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
<p>
<?php printf(_esc('If you don`t have installed the %s then restoring the backup assumes that (i) you have access to your website filesystem and/or MySQL database and (ii) you have a minimal knowledge of using SSH/FTP/mysqldump/phpMyAdmin or similar utilities that basically allow you to copy the files from your local system to the remote system:'),'<a href="'.APP_ADDONS_SHOP_URI.'shop/restore-wizard/" target="_blank">'._esc('Restore Addon').'</a>');?>
</p> <a id="full_restore"></a>
<h5>V.1 <?php _pesc('Full restore');?></h5>
<ol style="list-style-type: decimal">
<li><?php _pesc('download the backup archive from the remote storage location');?></li>
<li><?php _pesc('extract to a temporary directory the backup archive(s)');?></li>
<li><?php _pesc('connect via SSH/FTP/whatever the remote location and copy whatever file you want from the temporary directory to the remote location where your website files are located');?></li>
<li><?php printf(_esc('in case you want to restore also the database content then you may use an application like %s (see this %s) to import the .sql file from the *.sql.* archive extracted earlier at the temporary location.'),'<a href="https://en.wikipedia.org/wiki/PhpMyAdmin" target="_blank">phpMyAdmin</a>','<a href="https://www.youtube.com/watch?v=jW5lrS6EUPM" target="_blank">'._esc('video tutorial').'</a>');?></li>
</ol> <a id="diff_restore"></a>
<h5>V.2 <?php _pesc('Differential restore');?></h5>
<ol style="list-style-type: decimal">
<li><?php _pesc('find the last full backup (F) you are interested in');?></li>
<li><?php _pesc('find the last differenatial backup (D) created between the date of (F) and the date of the next full backup');?></li>
<li><?php printf(_esc('follow the same steps mentioned at %s for all found archives'),getAnchor('V.1', '#full_restore','_self'));?></li>
</ol> <a id="inc_restore"></a>
<h5>V.3 <?php _pesc('Incremental restore');?></h5>
<ol style="list-style-type: decimal">
<li><?php _pesc('find the last full backup (F) you are interested in');?></li>
<li><?php _pesc('find ALL the incremental backups (I) created between the date of (F) and the date of the next full backup');?></li>
<li><?php printf(_esc('follow the same steps mentioned at %s for all found archives'),getAnchor('V.1', '#full_restore','_self'));?></li>
</ol> <box class="highlight-box hintbox rounded-container"
style="display:block">
<p>
<?php printf(_esc('However, if you have installed the %s then restoring the backup is like shooting fish in a barrel:'),'<a href="'.APP_ADDONS_SHOP_URI.'shop/restore-wizard/" target="_blank">'._esc('Restore Addon').'</a>');?>
</p>
<ul style="list-style-position: inside;">
<li><?php _pesc('go to the <b>Restore</b> tab then follow the instruction provided there. Basically is just a "next-next-finish" 6-steps task assisted by Wizard.');?></li>
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
<li><?php _pesc('go to the <b>Target list</b> tab then start defining a new job by clicking the <b>Add new</b> button');?>
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
<li><?php _pesc('go to the <b>License</b> tab and expand the <b>Export settings</b> panel');?></li>
<li><?php _pesc('click the <b>Browse/Choose file</b> button then select the addon files (*.tar.bz2)');?></li>
<li><?php _pesc('click the <b>Install Add-on</b> button');?></li>
</ol></li>
<li><?php printf(_esc('Install multiple addons at once on non-%s'),$a3);?><ol
style="list-style-type: decimal">
<li><?php printf(_esc('copy the addon files (*.tar.bz2) to the %s folder'),$dropin_dir);?></li>
<li><?php printf(_esc('go to the <b>License</b> tab'));?></li>
<li><?php printf(_esc('the <b>DropIn Addons</b> page should be shown; follow the instructions there'));?></li>
</ol></li>
</ol></td>
</tr>
<tr>
<td><a id="debug"></a>
<h4>VIII. <?php _pesc('Troubleshooting');?></h4>
<p><?php _pesc('Hopefully you will never encounter a problem thus you will never need to know the instruction below. But we live in an imperfect world where imperfect people (like me) write imperfect software (like this one) so probably this chapter cannot be avoided forever.');?></p>
<p><?php _pesc('What to do when something doesn`t work as expected:');?></p>
<ol style="list-style-type: decimal">
<li><?php printf(_esc('make sure that %s'),'<a href="#requirements">'._esc('your system meets the requirements').'</a>');?></li>
<li><?php _pesc('if the application provides an error message or any other kind of output that seems related to the problem you encountered try to follow the instruction shown there (if any)');?></li>
<li><?php printf(_esc('check again the %s and %s; make sure they have the expected values'),'<a href="#configure">'._esc('global options').'</a>','<a href="#define">'._esc('the job settings').'</a>');?>
</li>
<li><?php printf(_esc('if the application shows a warning/error message (%s) that you suspect to be the root of the problem then we have to bring the heavy artillery:'),'<a href="http://php.net/manual/en/internals2.ze1.zendapi.php#internals2.ze1.zendapi.tab.error-messages" target="_blank">'._esc('see example').'</a>');?>
<ol style="list-style-type: decimal">
<li><?php _pesc('go to the <b>Support</b> tab then in the <b>Expert settings</b> panel make sure you set ON the following options:');?>
<ul>
<li><?php _pesc('Debug trace ON');?></li>
<li><?php _pesc('Curl debug ON');?></li>
<li><?php _pesc('Statistics debug ON');?></li>
<li><?php _pesc('SMTP debug ON');?></li>
</ul><?php _pesc('and unset/uncheck the following options:');?>
<ul>
<li><?php _pesc('Yayui optimize ON');?></li>
</ul></li>
<li><?php _pesc('Re-execute the job or whatever cause the problem you are debugging then check the following log(s) in the <b>Logs</b> tab:');?>
<ul>
<li><?php _pesc('if the problem seems to be related to network connection/authentication then check the <b>Curl Debug log</b>');?>
</li>
<li><?php _pesc('if the problem seems to be related to email (eg. email not sent) then check the <b>SMTP Debug log</b>');?>
</li>
<li><?php _pesc('if the problem seems to be related to some options not saved you may check the <b>Trace Action log</b>; this log traces all requests (like save, tab changed, etc) sent from your browser to this application; if you are a (former) sysadmin or coder you might eventually hack the problem');?>
</li>
<li><?php printf(_esc('if the problem seems to be more an unexpected warning/error thrown by the PHP/web server then probably something is rotten in the state of Denmark (I live in Sweden so I know what I am talking about). If that`s the case then open a support ticket by following the instruction found at the %s. Make sure you have downloaded all the log files mentioned earlier together with the <b>Jobs log</b> and the <b>Full log</b>. Moreover, the information provided by the <b>%s</b> button (in the <b>Support</b> tab) is also very useful when open a helpdesk ticket.'),'<a href="'.APP_ADDONS_SHOP_URI.'get-support/" target="_blank">'._esc('Support Center').'</a>','<a class="help" onclick="js55f93aab8f090.php_setup();">'._esc('Check PHP setup').'</a>');?>
</li>
</ul></li>
</ol></li>
</ol></td>
</tr>
<tr>
<td><p class="highlight-box hintbox rounded-container"><?php _pesc('As a software developer I did helpdesk, troubleshooting and support (level 1 to 3) for many, many years now. Hopefully I can help you finding the cause and fixing the problem.');?></p></td>
</tr>
</table>
<?php
if (! empty ( $this->_addons )) {
printf ( '<input type="hidden" name="dropin_files" value="%s">', implode ( ',', $this->_addons ) );
}
?>
