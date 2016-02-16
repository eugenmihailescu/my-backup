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
 * @file    : default-target-tabs.php $
 * 
 * @id      : default-target-tabs.php | Tue Feb 16 21:41:51 2016 UTC | Eugen Mihailescu eugenmihailescux@gmail.com $
*/

namespace MyBackup;

require_once EDITOR_PATH . 'target-functions.php';
require_once UTILS_PATH . 'arrays.php';
include_once CONFIG_PATH . 'forward-target-tabs.php';
$is_multisite = is_multisite_wrapper();
$BACKUP_TARGETS = array( 
DISK_TARGET => 'disk', 
FTP_TARGET => 'ftp', 
SSH_TARGET => 'ssh', 
DROPBOX_TARGET => 'dropbox', 
WEBDAV_TARGET => 'webdav', 
MAIL_TARGET => 'email' );
$TARGET_NAMES = array( 
TMPFILE_SOURCE => 'temp files', 
MYSQL_SOURCE => 'mysql', 
APP_LOGS => 'logs', 
APP_SUPPORT => 'support', 
APP_CHANGELOG => 'changelog', 
APP_TABBED_TARGETS => 'target', 
APP_SCHEDULE => 'schedule', 
APP_BACKUP_JOB => 'backup', 
APP_WELCOME => 'welcome', 
APP_NOTIFICATION => 'notification' ) + $BACKUP_TARGETS;
$NOT_BACKUP_TARGETS = array( TMPFILE_SOURCE, MYSQL_SOURCE );
registerDefaultTab( 
APP_BACKUP_JOB, 
'BackupJobEditor', 
$is_multisite ? _esc( 'Site backup' ) : ( is_wp() ? _esc( 'WP backup' ) : _esc( 'Backup settings' ) ) );
registerTab( 
MYSQL_SOURCE, 
'MySQLSourceEditor', 
$is_multisite ? _esc( 'Site database' ) : ( is_wp() ? _esc( 'WP database' ) : _esc( 'MySQL database' ) ) );
registerTab( DISK_TARGET, 'DiskTargetEditor', _esc( 'File system' ), 'getDiskFiles', 'folder', 'drive-harddisk.png' );
registerTab( DROPBOX_TARGET, 'DropboxTargetEditor', _esc( 'Dropbox' ), 'getDropboxFiles', 'dropbox', 'dropbox.png' );
registerTab( APP_SUPPORT, 'SupportEditor', _esc( 'Support' ) );
registerTab( APP_CHANGELOG, 'ChangeLogEditor', _esc( 'Change log' ) );
registerTab( APP_TABBED_TARGETS, 'BackupTargetsEditor', _esc( 'Backup targets' ) );
registerTab( APP_SCHEDULE, 'ScheduleEditor', _esc( 'Backup Schedule' ) );
registerTab( WEBDAV_TARGET, 'WebDAVTargetEditor', _esc( 'WebDAV' ), 'getWebDAVFiles', 'folder', 'dav.png' );
registerTab( FTP_TARGET, 'FtpTargetEditor', _esc( 'FTP/FTPS' ), 'getFtpFiles', 'folder', 'folder-remote.png' );
registerTab( SSH_TARGET, 'SSHTargetEditor', _esc( 'SFTP/SCP' ), 'getSSHFiles', 'folder', 'ssh.png' );
registerTab( APP_LOGS, 'LogsEditor', _esc( 'Logs' ) );
registerTab( MAIL_TARGET, 'MailTargetEditor', _esc( 'E-mail' ) );
registerTab( APP_WELCOME, 'WelcomeEditor', _esc( 'Welcome' ) );
registerTab( APP_NOTIFICATION, 'NotificationEditor', _esc( 'Notifications' ) );
$dashboard_tabs = array( 
APP_BACKUP_JOB, 
MYSQL_SOURCE, 
APP_TABBED_TARGETS, 
APP_SCHEDULE, 
APP_LOGS, 
APP_CHANGELOG, 
APP_SUPPORT );
?>