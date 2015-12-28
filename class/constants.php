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
 * @version : 0.2.2-10 $
 * @commit  : dd80d40c9c5cb45f5eda75d6213c678f0618cdf8 $
 * @author  : Eugen Mihailescu <eugenmihailescux@gmail.com> $
 * @date    : Mon Dec 28 17:57:55 2015 +0100 $
 * @file    : constants.php $
 * 
 * @id      : constants.php | Mon Dec 28 17:57:55 2015 +0100 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

include_once INC_PATH . 'globals.php';
$server_software = isset( $_SERVER['SERVER_SOFTWARE'] ) && isset( $_SERVER['SERVER_NAME'] ) ? $_SERVER['SERVER_SOFTWARE'] .
$_SERVER['SERVER_NAME'] : '';
define( __NAMESPACE__.'\\WPMYBACKUP_ID', md5( $server_software . PHP_OS . PHP_MAJOR_VERSION . PHP_PREFIX ) );
define( __NAMESPACE__.'\\SUPPORT_MIN_WP', '3.0' );
define( __NAMESPACE__.'\\SUPPORT_MIN_PHP', '5.3' );
define( __NAMESPACE__.'\\SUPPORT_MIN_MYSQL', '5.0' );
define( __NAMESPACE__.'\\OAUTH_PROXY_URL', 'https://oauth2.mynixworld.info/' );
define( __NAMESPACE__.'\\PROXY_APP_ID', 'oauth_proxy_demo' );
define( __NAMESPACE__.'\\PROXY_UNIQ_ID', '6dbe1adbddf324dd5e6d4dff15b65bc0' );
define( __NAMESPACE__.'\\PROXY_HOST_ID', '127.0.0.1' );
define( __NAMESPACE__.'\\PROXY_OPEN_ID', 'any' );
define( __NAMESPACE__.'\\PROXY_REGISTER', 'register' );
define( 
__NAMESPACE__.'\\PROXY_PARAMS', 
sprintf( 'a=%s&h=%s&o=%s&u=%s&%s', PROXY_APP_ID, PROXY_HOST_ID, PROXY_OPEN_ID, PROXY_UNIQ_ID, PROXY_REGISTER ) );
define( __NAMESPACE__.'\\WPMYBACKUP', 'WP MyBackup' );
! defined( __NAMESPACE__.'\\PLUGIN_EDITION' ) && define( __NAMESPACE__."\\PLUGIN_EDITION", WPMYBACKUP . '%EDITION%' );
define( __NAMESPACE__.'\\APP_ADDONS_SHOP_URI', 'http://mynixworld.info/shop/' );
define( __NAMESPACE__.'\\APP_ADDONS_URI', APP_ADDONS_SHOP_URI . 'product-category/addons' );
define( __NAMESPACE__."\\WPMYBACKUP_LOGS", strtolower( preg_replace( '/[^\w]/', '', WPMYBACKUP ) ) );
define( __NAMESPACE__.'\\MB', 1048576 );
define( __NAMESPACE__.'\\SECDAY', 86400 );
define( __NAMESPACE__.'\\TAB', '&nbsp;&nbsp;&nbsp;' );
define( __NAMESPACE__.'\\SEPARATOR_LEN', 100 );
define( __NAMESPACE__.'\\BULLET', '-' );
define( __NAMESPACE__.'\\NONE', 0 );
define( __NAMESPACE__.'\\GZ', 1 );
define( __NAMESPACE__.'\\BZ2', 2 );
file_exists( CLASS_PATH . 'MyPclZipArchive.php' ) && define( __NAMESPACE__.'\\PCLZIP', 6 );
$COMPRESSION_NAMES = array( NONE => 'tar', GZ => 'gz', BZ2 => 'bz2' );
defined( __NAMESPACE__.'\\PCLZIP' ) && $COMPRESSION_NAMES[PCLZIP] = 'zip';
$COMPRESSION_FILTERS = array( NONE => array(), GZ => array( 'gz', 'wb%d' ), BZ2 => array( 'bz', 'w' ) );
defined( __NAMESPACE__.'\\PCLZIP' ) && $COMPRESSION_FILTERS[PCLZIP] = array( 'zip', 'w' );
$COMPRESSION_HEADERS = array( 
BZ2 => array( 10, 'BZh\d1AY&SY' ), 
GZ => array( 10, '\x1f\x8b\d{8}' ) );
defined( __NAMESPACE__.'\\PCLZIP' ) && $COMPRESSION_HEADERS[PCLZIP] = array( 4, '(PK(\x03|\x05|\x07)(\x04|\x06|\x08))' );
$COMPRESSION_APPS = array( NONE => '', GZ => 'gzip', BZ2 => 'bzip2' );
defined( __NAMESPACE__.'\\PCLZIP' ) && $COMPRESSION_APPS[PCLZIP] = 'zip';
$COMPRESSION_LIBS = array( GZ => 'zlib', BZ2 => 'bz2' );
defined( __NAMESPACE__.'\\PCLZIP' ) && $COMPRESSION_LIBS[PCLZIP] = 'zlib';
$COMPRESSION_ARCHIVE = array( NONE => 'TarArchive', GZ => 'TarArchive', BZ2 => 'TarArchive' );
defined( __NAMESPACE__.'\\PCLZIP' ) && $COMPRESSION_ARCHIVE[PCLZIP] = 'MyPclZipArchive';
$COMPRESSION_LEVEL_SUPPORT = array( NONE => false, GZ => true, BZ2 => true );
defined( __NAMESPACE__.'\\PCLZIP' ) && $COMPRESSION_LEVEL_SUPPORT[PCLZIP] = false;
define( __NAMESPACE__.'\\VERBOSE_MINIMAL', 0 );
define( __NAMESPACE__.'\\VERBOSE_COMPACT', 1 );
define( __NAMESPACE__.'\\VERBOSE_FULL', 2 );
$VERBOSITY_MODES = array( 
VERBOSE_MINIMAL => _( 'Minimal' ), 
VERBOSE_COMPACT => _( 'Compact' ), 
VERBOSE_FULL => _( 'Full' ) );
define( __NAMESPACE__.'\\BACKUP_MODE_FULL', 0 );
$BACKUP_MODE = array( BACKUP_MODE_FULL => _( 'Full' ) ); 
define( __NAMESPACE__.'\\TMPFILE_SOURCE', - 2 );
define( __NAMESPACE__.'\\MYSQL_SOURCE', - 1 );
define( __NAMESPACE__.'\\DISK_TARGET', 1 );
define( __NAMESPACE__.'\\FTP_TARGET', 2 );
define( __NAMESPACE__.'\\DROPBOX_TARGET', 3 );
define( __NAMESPACE__.'\\MAIL_TARGET', 5 );
define( __NAMESPACE__.'\\WEBDAV_TARGET', 6 );
define( __NAMESPACE__.'\\SSH_TARGET', 7 );
define( __NAMESPACE__.'\\APP_LOGS', 9 );
define( __NAMESPACE__.'\\APP_SUPPORT', 11 );
define( __NAMESPACE__.'\\APP_NOTIFICATION', 13 );
define( __NAMESPACE__.'\\APP_CHANGELOG', 14 );
define( __NAMESPACE__.'\\APP_TABBED_TARGETS', 15 );
define( __NAMESPACE__.'\\APP_SCHEDULE', 17 );
define( __NAMESPACE__.'\\APP_BACKUP_JOB', 19 );
define( __NAMESPACE__.'\\APP_WELCOME', 25 );
define( __NAMESPACE__.'\\NEWEST', 1 ); 
define( __NAMESPACE__.'\\OLDEST', - 1 ); 
define( __NAMESPACE__.'\\OPER_MAINT_MYSQL', - 6 );
define( __NAMESPACE__.'\\OPER_COMPRESS_INTERN', - 5 );
define( __NAMESPACE__.'\\OPER_SRCFILE_BACKUP', - 3 );
define( __NAMESPACE__.'\\OPER_CLEANUP_OLDIES', - 2 );
define( __NAMESPACE__.'\\OPER_CLEANUP_ORPHAN', - 1 );
define( __NAMESPACE__.'\\OPER_SEND_DISK', 0 );
define( __NAMESPACE__.'\\OPER_SENT_DISK', 1 );
define( __NAMESPACE__.'\\OPER_SEND_FTP', 2 );
define( __NAMESPACE__.'\\OPER_SENT_FTP', 3 );
define( __NAMESPACE__.'\\OPER_SEND_DROPBOX', 4 );
define( __NAMESPACE__.'\\OPER_SENT_DROPBOX', 5 );
define( __NAMESPACE__.'\\OPER_SEND_GOOGLE', 6 );
define( __NAMESPACE__.'\\OPER_SENT_GOOGLE', 7 );
define( __NAMESPACE__.'\\OPER_SEND_EMAIL', 8 );
define( __NAMESPACE__.'\\OPER_SENT_EMAIL', 9 );
define( __NAMESPACE__.'\\OPER_SEND_WEBDAV', 10 );
define( __NAMESPACE__.'\\OPER_SENT_WEBDAV', 11 );
define( __NAMESPACE__.'\\OPER_SEND_SSH', 12 );
define( __NAMESPACE__.'\\OPER_SENT_SSH', 13 );
define( __NAMESPACE__.'\\OPER_GET_DISK', 100 );
define( __NAMESPACE__.'\\OPER_GOT_DISK', 101 );
define( __NAMESPACE__.'\\OPER_GET_FTP', 102 );
define( __NAMESPACE__.'\\OPER_GOT_FTP', 103 );
define( __NAMESPACE__.'\\OPER_GET_DROPBOX', 104 );
define( __NAMESPACE__.'\\OPER_GOT_DROPBOX', 105 );
define( __NAMESPACE__.'\\OPER_GET_GOOGLE', 106 );
define( __NAMESPACE__.'\\OPER_GOT_GOOGLE', 107 );
define( __NAMESPACE__.'\\OPER_GET_WEBDAV', 110 );
define( __NAMESPACE__.'\\OPER_GOT_WEBDAV', 111 );
define( __NAMESPACE__.'\\OPER_GET_SSH', 112 );
define( __NAMESPACE__.'\\OPER_GOT_SSH', 113 );
$logs_path = LOGS_PATH;
$multisite = defined( __NAMESPACE__.'\\SANDBOX' ) && SANDBOX; 
$multisite = $multisite || function_exists( '\\is_multisite' ) ? \is_multisite() : false;
$site_id = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
if ( function_exists( '\\get_current_blog_id' ) )
$current_site_id = \get_current_blog_id();
else {
global $blog_id;
$current_site_id = $blog_id;
}
if ( isset( $current_site_id ) ) {
$site_id = WPMYBACKUP_LOGS;
if ( $multisite ) {
switch_to_blog( $current_site_id );
$logs_path = wp_upload_dir();
$logs_path = implode( DIRECTORY_SEPARATOR, array( $logs_path['basedir'], WPMYBACKUP_LOGS, 'tmp', 'logs', '' ) );
restore_current_blog();
}
}
define( __NAMESPACE__.'\\IS_MULTISITE', $multisite );
define( __NAMESPACE__.'\\SITE_ID', $multisite ? $site_id : '' );
define( __NAMESPACE__.'\\LOG_DIR', $logs_path );
! file_exists( LOG_DIR ) && mkdir( LOG_DIR, 0770, true );
define( __NAMESPACE__."\\LOG_PREFIX", LOG_DIR . WPMYBACKUP_LOGS ); 
define( __NAMESPACE__."\\FILES_MD5_LOG", LOG_PREFIX . "-files-md5.log" ); 
define( __NAMESPACE__."\\BACKUP_FILTER_LOG", LOG_PREFIX . "-backup-filter.log" ); 
define( __NAMESPACE__."\\CURL_DEBUG_LOG", LOG_PREFIX . "-curl-debug.log" ); 
define( __NAMESPACE__."\\STATISTICS_DEBUG_LOG", LOG_PREFIX . "-statistics-debug.log" ); 
define( __NAMESPACE__."\\TRACE_DEBUG_LOG", LOG_PREFIX . "-trace-debug.log" ); 
define( __NAMESPACE__."\\ERROR_LOG", LOG_PREFIX . "-errors.log" ); 
define( __NAMESPACE__."\\JOBS_LOGFILE", LOG_PREFIX . "-jobs.log" ); 
define( __NAMESPACE__."\\OUTPUT_LOGFILE", LOG_PREFIX . "-output.log" ); 
define( __NAMESPACE__."\\NONCE_LOGFILE", LOG_PREFIX . "-nonces.log" ); 
define( __NAMESPACE__."\\MESSAGES_LOGFILE", LOG_PREFIX . "-messages.log" ); 
define( __NAMESPACE__."\\TRACE_ACTION_LOGFILE", LOG_PREFIX . "-trace-action.log" ); 
define( __NAMESPACE__."\\SMTP_DEBUG_LOG", LOG_PREFIX . "-smtp-debug.log" ); 
define( __NAMESPACE__."\\CURL_COOKIES_LOG", LOG_PREFIX . "-curl-cookies.log" ); 
define( __NAMESPACE__."\\CURL_COOKIES_JAR", LOG_PREFIX . "-curl-cookies.jar" ); 
define( __NAMESPACE__."\\JOBS_LOCK_FILE", LOG_PREFIX . "-jobs.lock" ); 
define( __NAMESPACE__."\\PROGRESS_LOGFILE", LOG_PREFIX . "-progress.log" ); 
define( __NAMESPACE__."\\STATISTICS_LOGFILE", LOG_PREFIX . "-stats.log" ); 
define( __NAMESPACE__."\\LOCAL_OPTION_DB_PATH", LOG_PREFIX . "-options.json" ); 
define( __NAMESPACE__."\\SIGNALS_LOGFILE", LOG_PREFIX . "-signals.log" ); 
define( __NAMESPACE__."\\SYS_NETUSAGE_LOG", LOG_PREFIX . "-net-usage.log" ); 
define( __NAMESPACE__."\\SSL_CACERT_FILE", SSL_PATH . 'cacert.pem' ); 
define( __NAMESPACE__.'\\SSL_CERTTYPE_PEM', 'PEM' );
define( __NAMESPACE__.'\\SSL_CERTTYPE_DER', 'DER' );
define( __NAMESPACE__.'\\TAB_ORIENTATION', 1 ); 
define( __NAMESPACE__.'\\TAB_POSITION', 0 ); 
define( __NAMESPACE__.'\\CORNER_SHAPE', 1 ); 
if ( ! defined( __NAMESPACE__."\\SIMPLELOGIN_SESSION_LOGGED" ) )
define( __NAMESPACE__."\\SIMPLELOGIN_SESSION_LOGGED", 'simple_login_is_logged' );
define( __NAMESPACE__.'\\SSL_ALERT_FADE_INTERVAL', 3000 ); 
define( __NAMESPACE__.'\\JSENABLED_CHECK_TIMEOUT', 3600 ); 
define( __NAMESPACE__."\\PROCESS_BACKUP", 0 );
define( __NAMESPACE__."\\PROCESS_GUI_BACKUP", 1 );
define( __NAMESPACE__."\\PROCESS_TRANSFER", 4 );
define( __NAMESPACE__."\\PROCESS_MYSQL_MAINT", 5 );
define( __NAMESPACE__.'\\JOB_BACKUP', 0 );
define( __NAMESPACE__.'\\JOB_MYSQL_MAINT', - 2 );
define( __NAMESPACE__.'\\JOB_LOG_READ', - 3 );
define( __NAMESPACE__.'\\PROCESS_SIGNAL_TIMEOUT', 3600 ); 
define( __NAMESPACE__.'\\SESSION_VARLIST_KEY', WPMYBACKUP_LOGS . '_session_list' ); 
define( __NAMESPACE__.'\\COOKIE_ACCEPT_MAXAGE', 365 ); 
define( __NAMESPACE__.'\\COOKIE_NOACCEPT_MAXAGE', 30 ); 
define( __NAMESPACE__.'\\MAIL_TEST_ACCOUNT', 'test.wpmybackup@mynixworld.info' ); 
define( __NAMESPACE__."\\ROOT_OAUTH_FILE", LOG_DIR );
?>