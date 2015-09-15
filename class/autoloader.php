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
 * @version : 0.2.0-10 $
 * @commit  : bc79573e2975a220cb1cfbb08b16615f721a68c5 $
 * @author  : Eugen Mihailescu <eugenmihailescux@gmail.com> $
 * @date    : Mon Sep 14 21:14:57 2015 +0200 $
 * @file    : autoloader.php $
 * 
 * @id      : autoloader.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;

global $classes_path;
$classes_path = array (
'AbstractJob' => CLASS_PATH . 'AbstractJob.php',
'AbstractOAuthClient' => OAUTH_PATH . 'AbstractOAuthClient.php',
'AbstractTarget' => CLASS_PATH . 'AbstractTarget.php',
'AbstractTargetEditor' => EDITOR_PATH . 'AbstractTargetEditor.php',
'ActionHandler' => CLASS_PATH . 'ActionHandler.php',
'AjaxRequests' => CLASS_PATH . 'AjaxRequests.php',
'Array2XML' => MISC_PATH . 'Array2XML.php',
'BackupFilesFilter' => CLASS_PATH . 'BackupFilesFilter.php',
'BackupJobEditor' => EDITOR_PATH . 'BackupJobEditor.php',
'BackupSettingsEditor' => EDITOR_PATH . 'BackupSettingsEditor.php',
'BackupTargetsEditor' => EDITOR_PATH . 'BackupTargetsEditor.php',
'ChangeLogEditor' => EDITOR_PATH . 'ChangeLogEditor.php',
'CheckSetup' => CLASS_PATH . 'CheckSetup.php',
'CurlFtpWrapper' => CURL_PATH . 'CurlFtpWrapper.php',
'CurlOptsCodes' => CURL_PATH . 'CurlOptsCodes.php',
'CurlOptsParamsCodes' => CURL_PATH . 'CurlOptsParamsCodes.php',
'CurlSSHWrapper' => CURL_PATH . 'CurlSSHWrapper.php',
'CurlWrapper' => CURL_PATH . 'CurlWrapper.php',
'Dashboard' => CLASS_PATH . 'Dashboard.php',
'DiskSourceEditor' => EDITOR_PATH . 'DiskSourceEditor.php',
'DiskTargetEditor' => EDITOR_PATH . 'DiskTargetEditor.php',
'DropboxCloudStorage' => STORAGE_PATH . 'DropboxCloudStorage.php',
'DropboxOAuth2Client' => OAUTH_PATH . 'DropboxOAuth2Client.php',
'DropboxTargetEditor' => EDITOR_PATH . 'DropboxTargetEditor.php',
'FacebookOAuth2Client' => OAUTH_PATH . 'FacebookOAuth2Client.php',
'FileContextUrl' => CURL_PATH . 'FileContextUrl.php',
'FtpStatusCodes' => CURL_PATH . 'FtpStatusCodes.php',
'FtpTargetEditor' => EDITOR_PATH . 'FtpTargetEditor.php',
'GenericArchive' => CLASS_PATH . 'GenericArchive.php',
'GenericCloudStorage' => STORAGE_PATH . 'GenericCloudStorage.php',
'GenericDataManager' => MISC_PATH . 'GenericDataManager.php',
'GenericOAuth2Client' => OAUTH_PATH . 'GenericOAuth2Client.php',
'GoogleCloudStorage' => STORAGE_PATH . 'GoogleCloudStorage.php',
'GoogleOAuth2Client' => OAUTH_PATH . 'GoogleOAuth2Client.php',
'GoogleTargetEditor' => EDITOR_PATH . 'GoogleTargetEditor.php',
'HtmlTableConverter' => MISC_PATH . 'HtmlTableConverter.php',
'HttpStatusCodes' => CURL_PATH . 'HttpStatusCodes.php',
'LocalFilesMD5' => CLASS_PATH . 'LocalFilesMD5.php',
'LogFile' => LIB_PATH . 'LogFile.php',
'LogsEditor' => EDITOR_PATH . 'LogsEditor.php',
'MailTargetEditor' => EDITOR_PATH . 'MailTargetEditor.php',
'MessageHandler' => MISC_PATH . 'MessageHandler.php',
'MessageItem' => MISC_PATH . 'MessageItem.php',
'MyException' => LIB_PATH . 'MyException.php',
'MyFtpWrapper' => CURL_PATH . 'MyFtpWrapper.php',
'MySQLBackupHandler' => CLASS_PATH . 'MySQLBackupHandler.php',
'MySQLSourceEditor' => EDITOR_PATH . 'MySQLSourceEditor.php',
'NonceLib' => MISC_PATH . 'NonceLib.php',
'NotificationEditor' => EDITOR_PATH . 'NotificationEditor.php',
'OAuthTargetEditor' => EDITOR_PATH . 'OAuthTargetEditor.php',
'OSScheduleEditor' => EDITOR_PATH . 'OSScheduleEditor.php',
'ProgressManager' => MISC_PATH . 'ProgressManager.php',
'SSHTargetEditor' => EDITOR_PATH . 'SSHTargetEditor.php',
'ScheduleEditor' => EDITOR_PATH . 'ScheduleEditor.php',
'SimpleLogin' => MISC_PATH . 'SimpleLogin.php',
'StatisticsManager' => MISC_PATH . 'StatisticsManager.php',
'SupportCategories' => STORAGE_PATH . 'SupportCategories.php',
'SupportEditor' => EDITOR_PATH . 'SupportEditor.php',
'TarArchive' => CLASS_PATH . 'TarArchive.php',
'TargetCollection' => EDITOR_PATH . 'TargetCollection.php',
'TargetCollectionItem' => EDITOR_PATH . 'TargetCollectionItem.php',
'WPBackupHandler' => CLASS_PATH . 'WPBackupHandler.php',
'WPSourceEditor' => EDITOR_PATH . 'WPSourceEditor.php',
'WebDAVParser' => STORAGE_PATH . 'WebDAVParser.php',
'WebDAVResource' => STORAGE_PATH . 'WebDAVResource.php',
'WebDAVResponse' => STORAGE_PATH . 'WebDAVResponse.php',
'WebDAVTargetEditor' => EDITOR_PATH . 'WebDAVTargetEditor.php',
'WebDAVWebStorage' => STORAGE_PATH . 'WebDAVWebStorage.php',
'WebDavLock' => STORAGE_PATH . 'WebDavLock.php',
'WelcomeEditor' => EDITOR_PATH . 'WelcomeEditor.php',
'Xml2Array' => MISC_PATH . 'Xml2Array.php',
'YayuiCompressor' => MISC_PATH . 'YayuiCompressor.php'
);
spl_autoload_register ( function ($class_name) {
global $classes_path;
$class_name = preg_replace ( "/" . __NAMESPACE__ . "\\\\/", "", $class_name );
isset ( $classes_path [$class_name] ) && include_once $classes_path [$class_name];});
?>
