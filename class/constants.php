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
 * @file    : constants.php $
 * 
 * @id      : constants.php | Wed Sep 16 11:33:37 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;

include_once INC_PATH . 'globals.php';
$server_software = isset ( $_SERVER ['SERVER_SOFTWARE'] ) && isset ( $_SERVER ['SERVER_NAME'] ) ? $_SERVER ['SERVER_SOFTWARE'] . $_SERVER ['SERVER_NAME'] : '';
define ( 'WPMYBACKUP_ID', md5 ( $server_software . PHP_OS . PHP_MAJOR_VERSION . PHP_PREFIX ) );
define ( 'SUPPORT_MIN_WP', '3.0' );
define ( 'SUPPORT_MIN_PHP', '5.3' );
define ( 'SUPPORT_MIN_MYSQL', '5.0' );
define ( 'OAUTH_PROXY_URL', 'https://oauth2.mynixworld.info/' );
define ( 'PROXY_APP_ID', 'oauth_proxy_demo' );
define ( 'PROXY_UNIQ_ID', '6dbe1adbddf324dd5e6d4dff15b65bc0' );
define ( 'PROXY_HOST_ID', '127.0.0.1' );
define ( 'PROXY_OPEN_ID', 'any' );
define ( 'PROXY_REGISTER', 'register' );
define ( 'PROXY_PARAMS', sprintf ( 'a=%s&h=%s&o=%s&u=%s&%s', PROXY_APP_ID, PROXY_HOST_ID, PROXY_OPEN_ID, PROXY_UNIQ_ID, PROXY_REGISTER ) );
define ( 'WPMYBACKUP', 'WP MyBackup' );
define ( 'APP_ADDONS_SHOP_URI', 'http://mynixworld.info/shop/' );
define ( 'APP_ADDONS_URI', APP_ADDONS_SHOP_URI . 'product-category/addons' );
define ( "WPMYBACKUP_LOGS", strtolower ( preg_replace ( '/[^\w]/', '', WPMYBACKUP ) ) );
define ( 'MB', 1048576 );
define ( 'SECDAY', 86400 );
define ( 'TAB', '&nbsp;&nbsp;&nbsp;' );
define ( 'SEPARATOR_LEN', 100 );
define ( 'BULLET', '-' );
define ( 'NONE', 0 );
define ( 'GZ', 1 );
define ( 'BZ2', 2 );
$COMPRESSION_NAMES = array (
NONE => 'tar',
GZ => 'gz',
BZ2 => 'bz2' 
);
$COMPRESSION_FILTERS = array (
NONE => array (),
GZ => array (
'gz',
'wb%d' 
),
BZ2 => array (
'bz',
'w' 
) 
);
$COMPRESSION_HEADERS = array (
BZ2 => array (
10,
'BZh\d1AY&SY' 
),
GZ => array (
10,
'\x1f\x8b\d{8}' 
) 
);
$COMPRESSION_APPS = array (
NONE => '',
GZ => 'gzip',
BZ2 => 'bzip2' 
);
$COMPRESSION_LIBS = array (
GZ => 'zlib',
BZ2 => 'bz2' 
);
$COMPRESSION_ARCHIVE = array (
NONE => 'TarArchive',
GZ => 'TarArchive',
BZ2 => 'TarArchive' 
);
define ( 'VERBOSE_MINIMAL', 0 );
define ( 'VERBOSE_COMPACT', 1 );
define ( 'VERBOSE_FULL', 2 );
$VERBOSITY_MODES = array (
VERBOSE_MINIMAL => _ ( 'Minimal' ),
VERBOSE_COMPACT => _ ( 'Compact' ),
VERBOSE_FULL => _ ( 'Full' ) 
);
define ( 'BACKUP_MODE_FULL', 0 );
$BACKUP_MODE = array (
BACKUP_MODE_FULL => _ ( 'Full' )  
);
define ( 'TMPFILE_SOURCE', - 2 );
define ( 'MYSQL_SOURCE', - 1 );
define ( 'DISK_TARGET', 1 );
define ( 'FTP_TARGET', 2 );
define ( 'DROPBOX_TARGET', 3 );
define ( 'MAIL_TARGET', 5 );
define ( 'WEBDAV_TARGET', 6 );
define ( 'SSH_TARGET', 7 );
define ( 'APP_LOGS', 9 );
define ( 'APP_SUPPORT', 11 );
define ( 'APP_NOTIFICATION', 13 );
define ( 'APP_CHANGELOG', 14 );
define ( 'APP_TABBED_TARGETS', 15 );
define ( 'APP_SCHEDULE', 17 );
define ( 'APP_BACKUP_JOB', 19 );
define ( 'APP_WELCOME', 25 );
define ( 'NEWEST', 1 ); 
define ( 'OLDEST', - 1 ); 
define ( 'OPER_MAINT_MYSQL', - 6 );
define ( 'OPER_COMPRESS_INTERN', - 5 );
define ( 'OPER_SRCFILE_BACKUP', - 3 );
define ( 'OPER_CLEANUP_OLDIES', - 2 );
define ( 'OPER_CLEANUP_ORPHAN', - 1 );
define ( 'OPER_SEND_DISK', 0 );
define ( 'OPER_SENT_DISK', 1 );
define ( 'OPER_SEND_FTP', 2 );
define ( 'OPER_SENT_FTP', 3 );
define ( 'OPER_SEND_DROPBOX', 4 );
define ( 'OPER_SENT_DROPBOX', 5 );
define ( 'OPER_SEND_GOOGLE', 6 );
define ( 'OPER_SENT_GOOGLE', 7 );
define ( 'OPER_SEND_EMAIL', 8 );
define ( 'OPER_SENT_EMAIL', 9 );
define ( 'OPER_SEND_WEBDAV', 10 );
define ( 'OPER_SENT_WEBDAV', 11 );
define ( 'OPER_SEND_SSH', 12 );
define ( 'OPER_SENT_SSH', 13 );
define ( 'OPER_GET_DISK', 100 );
define ( 'OPER_GOT_DISK', 101 );
define ( 'OPER_GET_FTP', 102 );
define ( 'OPER_GOT_FTP', 103 );
define ( 'OPER_GET_DROPBOX', 104 );
define ( 'OPER_GOT_DROPBOX', 105 );
define ( 'OPER_GET_GOOGLE', 106 );
define ( 'OPER_GOT_GOOGLE', 107 );
define ( 'OPER_GET_WEBDAV', 110 );
define ( 'OPER_GOT_WEBDAV', 111 );
define ( 'OPER_GET_SSH', 112 );
define ( 'OPER_GOT_SSH', 113 );
defined ( 'SANDBOX' ) && SANDBOX && (defined ( 'IS_MULTISITE' ) || (define ( 'IS_MULTISITE', true ) && define ( "SITE_ID", isset ( $_SERVER ['REMOTE_ADDR'] ) ? $_SERVER ['REMOTE_ADDR'] : 'unknown' ))); 
defined ( "IS_MULTISITE" ) && defined ( "SITE_ID" ) && define ( 'LOG_DIR', LOGS_PATH . SITE_ID . DIRECTORY_SEPARATOR ) || define ( 'LOG_DIR', LOGS_PATH );
! file_exists ( LOG_DIR ) && mkdir ( LOG_DIR, 0770, true );
define ( "LOG_PREFIX", LOG_DIR . WPMYBACKUP_LOGS ); 
define ( "FILES_MD5_LOG", LOG_PREFIX . "-files-md5.log" ); 
define ( "BACKUP_FILTER_LOG", LOG_PREFIX . "-backup-filter.log" ); 
define ( "CURL_DEBUG_LOG", LOG_PREFIX . "-curl-debug.log" ); 
define ( "STATISTICS_DEBUG_LOG", LOG_PREFIX . "-statistics-debug.log" ); 
define ( "TRACE_DEBUG_LOG", LOG_PREFIX . "-trace-debug.log" ); 
define ( "ERROR_LOG", LOG_PREFIX . "-errors.log" ); 
define ( "JOBS_LOGFILE", LOG_PREFIX . "-jobs.log" ); 
define ( "OUTPUT_LOGFILE", LOG_PREFIX . "-output.log" ); 
define ( "NONCE_LOGFILE", LOG_PREFIX . "-nonces.log" ); 
define ( "MESSAGES_LOGFILE", LOG_PREFIX . "-messages.log" ); 
define ( "TRACE_ACTION_LOGFILE", LOG_PREFIX . "-trace-action.log" ); 
define ( "SMTP_DEBUG_LOG", LOG_PREFIX . "-smtp-debug.log" ); 
define ( "CURL_COOKIES_LOG", LOG_PREFIX . "-curl-cookies.log" ); 
define ( "CURL_COOKIES_JAR", LOG_PREFIX . "-curl-cookies.jar" ); 
define ( "JOBS_LOCK_FILE", LOG_PREFIX . "-jobs.lock" ); 
define ( "PROGRESS_LOGFILE", LOG_PREFIX . "-progress.log" ); 
define ( "STATISTICS_LOGFILE", LOG_PREFIX . "-stats.log" ); 
define ( "LOCAL_OPTION_DB_PATH", LOG_PREFIX . "-options.json" ); 
define ( "SIGNALS_LOGFILE", LOG_PREFIX . "-signals.log" ); 
define ( "SYS_NETUSAGE_LOG", LOG_PREFIX . "-net-usage.log" ); 
define ( "SSL_CACERT_FILE", SSL_PATH . 'cacert.pem' ); 
define ( 'SSL_CERTTYPE_PEM', 'PEM' );
define ( 'SSL_CERTTYPE_DER', 'DER' );
define ( 'TAB_ORIENTATION', 1 ); 
define ( 'TAB_POSITION', 0 ); 
define ( 'CORNER_SHAPE', 1 ); 
if (! defined ( "SIMPLELOGIN_SESSION_LOGGED" ))
define ( "SIMPLELOGIN_SESSION_LOGGED", 'simple_login_is_logged' );
define ( 'SSL_ALERT_FADE_INTERVAL', 3000 ); 
define ( 'JSENABLED_CHECK_TIMEOUT', 3600 ); 
define ( "PROCESS_BACKUP", 0 );
define ( "PROCESS_GUI_BACKUP", 1 );
define ( "PROCESS_TRANSFER", 4 );
define ( "PROCESS_MYSQL_MAINT", 5 );
define ( 'JOB_BACKUP', 0 );
define ( 'JOB_MYSQL_MAINT', - 2 );
define ( 'JOB_LOG_READ', - 3 );
define ( 'PROCESS_SIGNAL_TIMEOUT', 3600 ); 
define ( 'SESSION_VARLIST_KEY', WPMYBACKUP_LOGS . '_session_list' ); 
define ( 'COOKIE_ACCEPT_MAXAGE', 365 ); 
define ( 'COOKIE_NOACCEPT_MAXAGE', 30 ); 
define ( 'MAIL_TEST_ACCOUNT', 'test.wpmybackup@mynixworld.info' ); 
define ( "ROOT_OAUTH_FILE", LOG_DIR );
?>
