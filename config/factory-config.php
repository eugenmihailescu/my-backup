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
 * @file    : factory-config.php $
 * 
 * @id      : factory-config.php | Wed Sep 16 11:33:37 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;

include_once LOCALE_PATH . 'locale.php';
$factory_options = array ();
$is_wp = is_wp ();
$url = get_home_url_wrapper ();
$ciphers = array ();
foreach ( array_values ( $registered_ciphres ) as $cipher )
$ciphers = array_merge ( $ciphers, $cipher ['items'] );
$dir_pattern = _esc ( 'The %s folder where to upload the backup' );
$age_pattern = _esc ( 'Keep only the last n-days backups on %s' );
$host_pattern = _esc ( 'The %s server ip/address:port/path' );
$port_pattern = _esc ( 'The %s server port' );
$user_pattern = _esc ( 'The %s server user name' );
$pwd_pattern = _esc ( 'The %s server password' );
$local_disk = _esc ( 'local disk' );
$compfilter_pattern = _esc ( 'Use this option to filter the archive through %s' );
$mysql_maint_pattern = _esc ( 'Use this option if you want to run the MySQL %s maintenance' );
$throttle_pattern = _esc ( 'Specify the %s upload throttle size in KB' );
$upload_dest_pattern = _esc ( 'Set this option if you want to upload the backup to %s' );
$factory_options ['global'] = array (
'locked_settings' => array (
false,
'',
'' 
) 
);
$factory_options ['backup'] = array (
'name' => array (
'',
'name:',
'n:',
_esc ( 'The archive name' ) 
),
'url' => array (
str_replace ( DIRECTORY_SEPARATOR, '-', preg_replace ( '/http(s*):\/\//', '', is_cli () ? basename ( $url ) : $url ) ),
'url:',
'u:',
_esc ( 'The domain name used as prefix for backup archive' ) 
),
'wrkdir' => array (
sys_get_temp_dir (),
'wrkdir:',
'w:',
_esc ( 'Temporary working directory' ) 
),
'compression_type' => array (
2,
'comptype:',
'',
_esc ( 'Use this option to specify the compression algorithm' ) . ' (none=0,gzip=1,bzip=2)' 
),
'compression_level' => array (
9,
'complevel:',
'',
_esc ( 'The compression level [0-9], the higher the better' ) 
),
'toolchain' => array (
'intern',
'toolchain:',
'',
sprintf ( _esc ( 'intern (i.e. %s) or extern (i.e. local %s)' ), WPMYBACKUP, PHP_OS ) 
),
'size' => array (
150,
'size:',
's:',
_esc ( 'The max limit of TAR media spanning (0 no volume limit)' ) 
),
'verbose' => array (
VERBOSE_MINIMAL,
'verbose::',
'v::',
_esc ( 'Set the verbosity (0=minimal|1=compact|2=full)' ) 
),
'email' => array (
function_exists ( 'get_bloginfo' ) ? get_bloginfo ( 'admin_email' ) : '',
'email:',
'm:',
_esc ( 'The email address used for notification and/or backup2mail' ) 
),
'cygwin' => array (
CYGWIN_PATH,
'cygwin:',
'',
_esc ( 'The CygWin path that may be used for the external compression toolchain' ) 
),
'bzipver' => array (
'bzip2',
'bzipver:',
'',
sprintf ( _esc ( 'Either bzip2|pbzip2. Use PBZip2 instead of BZip2 if available on your local %s' ), PHP_OS ) 
),
'cpusleep' => array (
1000,
'cpusleep:',
'',
_esc ( 'The number of miliseconds to sleep the CPU between compression cycles' ) 
),
'retry' => array (
5,
'retry',
'' 
),
'retrywait' => array (
300,
'retrywait:',
'' 
),
'email_smtp' => array (
false,
'',
'' 
),
'max_exec_time' => array (
600,
'exec_time:',
'',
_esc ( 'Set the number of seconds a script is allowed to run' ) 
),
'mode' => array (
BACKUP_MODE_FULL,
'mode:',
'',
_esc ( 'The backup mode: 0=full backup (if available 1=incremental, 2=differential)' ) 
),
'encryption' => array (
null,
'encalg:',
'',
sprintf ( _esc ( 'The encryption algorithm used to encrypt/decrypt the backup, if any: %s' ), implode ( ',', $ciphers ) ) 
),
'encryption_key' => array (
'',
'enckey:',
'',
_esc ( 'The key used by the encryption cipher to encrypt/decrypt the backup' ) 
),
'encryption_key_stength' => array (
'',
'',
'' 
),
'encryption_iv' => array (
'',
'enciv:',
'',
_esc ( 'The vector used by the encryption cipher to encrypt/decrypt the backup' ) 
),
'encryption_iv_strength' => array (
'',
'',
'' 
) 
);
$factory_options ['target'] = array (
'ssl_ver' => array (
0,
'',
'' 
),
'ssl_cainfo' => array (
SSL_CACERT_FILE,
'',
'' 
),
'ssl_chk_peer' => array (
false,
'',
'',
true 
),
'ssl_chk_host' => array (
false,
'',
'' 
),
'dwl_throttle' => array (
0,
'',
'' 
),
'http_proxy' => array (
'',
'proxy:',
'',
_esc ( 'The proxy host name/ip used for http communication' ) 
),
'http_proxy_port' => array (
0,
'proxyport:',
'',
_esc ( 'The proxy port' ) 
),
'http_proxy_user' => array (
'',
'proxyuser:',
'',
_esc ( 'The proxy authentication user, if any' ) 
),
'http_proxy_pwd' => array (
'',
'proxypwd:',
'',
_esc ( 'The proxy authentication password, if any' ) 
),
'http_proxy_auth' => array (
CURLAUTH_BASIC,
'proxyauth:',
'',
_esc ( 'The proxy authentication method (basic=1,ntlm=8,any=-17)' ) 
),
'http_proxy_type' => array (
CURLPROXY_HTTP,
'proxytype:',
'',
_esc ( 'The proxy type (http=0,socks4=4,socks5=5)' ) 
),
'netif_out' => array (
'',
'',
'',
_esc ( 'The output network interface name (eg. eth0)' ) 
),
'request_timeout' => array (
30,
'timeout:',
'',
_esc ( 'The http request timeout' ) 
)  
);
$factory_options ['mysql'] = array (
'mysql_enabled' => array (
false,
'mysql',
'',
_esc ( 'Set this option if you want to enable MySQL backup' ),
true 
),
'mysql_format' => array (
'sql',
'sqlformat',
'' 
),
'mysql_host' => array (
defined ( DB_HOST ) ? DB_HOST : 'localhost',
'mysqlhost:',
'',
_esc ( 'The MySQL host name/IP' ) 
),
'mysql_port' => array (
3306,
'mysqlport:',
'',
_esc ( 'The MySQL port' ) 
),
'mysql_user' => array (
DB_USER,
'mysqluser:',
'',
_esc ( 'The MySQL authentication user' ) 
),
'mysql_pwd' => array (
DB_PASSWORD,
'mysqlpwd:',
'',
_esc ( 'The MySQL authentication password' ) 
),
'mysql_db' => array (
DB_NAME,
'mysqldb:',
'',
_esc ( 'The MySQL authentication database name' ) 
),
'mysql_charset' => array (
DB_CHARSET,
'mysqlcs:',
'',
_esc ( 'The MySQL character set' ) 
),
'mysql_collate' => array (
DB_COLLATE,
'mysqlcl:',
'',
_esc ( 'The MySQL collation name' ) 
),
'tables' => array (
'.+',
'mysqltbl:',
'',
_esc ( 'Backup the MySQL tables (comma-delimited table list or * for all)' ) 
),
'mysqldump' => array (
false,
'mysqldump',
'',
_esc ( 'Use this option if you want to generate the MySQL backup via mysqldump' ),
true 
),
'mysqldump_opts' => array (
null,
'mysqldumpopt:',
'',
_esc ( 'The mysqldump arguments' ) 
),
'mysql_maint' => array (
false,
'mysqlmnt',
'',
_esc ( 'Use this option if you want to run the MySQL database maintenance before backup' ),
true 
),
'mysql_maint_analyze' => array (
false,
'mysqlmntan',
'',
sprintf ( $mysql_maint_pattern, _esc ( 'analyze' ) ),
true 
),
'mysql_maint_check' => array (
false,
'mysqlmntck',
'',
sprintf ( $mysql_maint_pattern, _esc ( 'check' ) ),
true 
),
'mysql_maint_optimize' => array (
false,
'mysqlmntop',
'',
sprintf ( $mysql_maint_pattern, _esc ( 'optimize' ) ),
true 
),
'mysql_maint_repair' => array (
false,
'mysqlmntrp',
'',
sprintf ( $mysql_maint_pattern, _esc ( 'repair' ) ),
true 
),
'mysql_maint_notify' => array (
false,
'mysqlmntno',
'',
_esc ( 'Use this option if you want to print-out the MySQL maintenance alerts' ),
true 
) 
);
$exclude_files_factory = array (
'%NONCE_LOGFILE%',
'%JOBS_LOGFILE%',
'%SIGNALS_LOGFILE%',
'%PROGRESS_LOGFILE%',
'%JOBS_LOCK_FILE%',
'%TRACE_ACTION_LOGFILE%',
'%OUTPUT_LOGFILE%' 
);
$extra_exclude_ext = array (
'iso',
'lz',
'lzma',
'lzo',
'xz',
'7z',
'ace',
'arj',
'cab',
'jar',
'lxz',
'pak',
'rar',
'tgz',
'zip',
'enc' 
);
$extra_exclude_ext = $extra_exclude_ext + $COMPRESSION_NAMES;
asort ( $extra_exclude_ext );
$factory_options ['fssource'] = array (
'dir' => array (
WPMYBACKUP_ROOT,
'dir:',
'',
_esc ( 'Path of the folder to backup' ) 
),
'dir_show_size' => array (
false,
'',
'' 
),
'excludedirs' => array (
'',
'direxcl:',
'',
_esc ( 'Exclude directories (comma-delimited list)' ) 
),
'excludefiles' => array (
implode ( ',', $exclude_files_factory ),
'filexcl:',
'',
_esc ( 'Exlude files (comma-delimited list)' ) 
),
'excludeext' => array (
implode ( ',', $extra_exclude_ext ),
'extexcl:',
'',
_esc ( 'Exclude extensions (comma-delimited list)' ) 
),
'excludelinks' => array (
true,
'linkexcl',
'',
_esc ( 'Use this option to exclude the file links' ),
true 
) 
);
$factory_options ['restore'] = array (
'extractforcebly' => array (
false,
'',
'' 
),
'restore_method' => array (
'wizard',
'',
'' 
), 
'restore_components_files' => array (
true,
'',
'' 
),
'restore_components_mysql' => array (
true,
'',
'' 
),
'restore_components_wp' => array (
true,
'',
'' 
),
'restore_point' => array (
'date',
'',
'' 
), 
'restore_date' => array (
'recent',
'',
'' 
), 
'restore_target' => array (
DISK_TARGET,
'',
'' 
),
'restore_path' => array (
'original',
'',
'' 
), 
'restore_reconcile' => array (
'rename',
'',
'' 
)  
);
$factory_options ['disk'] = array (
'disk_enabled' => array (
false,
'disk',
'',
sprintf ( $upload_dest_pattern, 'disk' ),
true 
),
'disk' => array (
getUserHomeDir (),
'diskpath:',
'',
sprintf ( $dir_pattern, $local_disk ) 
),
'disk_path_id' => array (
getUserHomeDir (),
'',
'' 
),
'disk_age' => array (
DEFAULT_BACKUP_LIFESPAN,
'diskage:',
'',
sprintf ( $age_pattern, $local_disk ) 
) 
);
$factory_options ['ftp'] = array (
'ftp_enabled' => array (
false,
'ftp',
'',
sprintf ( $upload_dest_pattern, 'ftp' ),
true 
),
'ftphost' => array (
NULL,
'ftphost:',
'',
sprintf ( $host_pattern, 'FTP' ) 
),
'ftpport' => array (
21,
'ftpport:',
'',
sprintf ( $port_pattern, 'FTP' ) 
),
'ftp_active_port' => array (
'-',
'ftpactiv:',
'' 
),
'ftpuser' => array (
NULL,
'ftpuser:',
'',
sprintf ( $user_pattern, 'FTP' ) 
),
'ftppwd' => array (
NULL,
'ftppwd:',
'',
sprintf ( $pwd_pattern, 'FTP' ) 
),
'ftp' => array (
'/',
'ftpdir:',
'',
sprintf ( $dir_pattern, 'FTP' ) 
),
'ftp_path_id' => array (
'/',
'',
'' 
),
'ftppasv' => array (
true,
'ftppsv',
'',
_esc ( 'Turns FTP passive mode on or off' ),
true 
),
'ftp_age' => array (
DEFAULT_BACKUP_LIFESPAN,
'ftpage:',
'',
sprintf ( $age_pattern, 'FTP' ) 
),
'ftpproto' => array (
CURLPROTO_FTP,
'ftpproto:',
'' 
),
'ftpdirsep' => array (
'u',
'',
'',
_esc ( 'The FTP listing style (Unix/Windows)' ) 
), 
'ftp_throttle' => array (
0,
'ftpthr:',
'',
sprintf ( $throttle_pattern, 'ftp' ) 
),
'ftp_direct_dwl' => array (
true,
'',
'' 
),
'ftp_lib' => array (
'php',
'ftplib:',
'',
_esc ( 'The FTP library to use (php|curl)' ) 
),
'ftp_cainfo' => array (
SSL_CACERT_FILE,
'ftpca:',
'',
_esc ( 'The full path to the cacert.pem certificate' ) 
),
'ftp_ssl_chk_peer' => array (
false,
'ftpckper',
'',
_esc ( 'Use this option to enforce the SSL peer check' ) 
),
'ftp_ssl_ver' => array (
0,
'ftpsslver:',
'',
_esc ( 'Specify the SSL version to use (auto=0,v1.x=1,v1.0=4,v1.1=5,v1.2=6,v2=2,v3=3)' ) 
) 
);
$factory_options ['ssh'] = array (
'ssh_enabled' => array (
false,
'ssh',
'',
sprintf ( $upload_dest_pattern, 'ssh' ),
true 
),
'sshhost' => array (
'',
'sshhost:',
'',
sprintf ( $host_pattern, 'SSH' ) 
),
'sshport' => array (
22,
'sshport:',
'',
sprintf ( $port_pattern, 'SSH' ) 
),
'sshuser' => array (
'',
'sshuser:',
'',
sprintf ( $user_pattern, 'SSH' ) 
),
'sshpwd' => array (
'',
'sshpwd:',
'',
sprintf ( $pwd_pattern, 'SSH' ) 
),
'ssh' => array (
'/',
'sshdir:',
'',
sprintf ( $dir_pattern, 'SSH' ) 
),
'ssh_path_id' => array (
'/',
'',
'' 
),
'ssh_age' => array (
DEFAULT_BACKUP_LIFESPAN,
'sshage:',
'',
sprintf ( $age_pattern, 'SSH' ) 
),
'sshproto' => array (
CURLPROTO_SFTP,
'sshproto:',
'',
_esc ( 'Either `sftp` or `scp`' ) 
),
'ssh_throttle' => array (
0,
'sshthr:',
'',
sprintf ( $throttle_pattern, 'ssh' ) 
),
'ssh_publickey_file' => array (
'',
'sshpubkey:',
'',
_esc ( 'The file containing the public SSH key' ) 
),
'ssh_privkey_file' => array (
'',
'sshprivkey:',
'',
_esc ( 'The file containing the private SSH key' ) 
),
'ssh_privkey_pwd' => array (
'',
'sshprivpwd:',
'',
_esc ( 'If the private SSH file is password-protected then specify that password' ) 
) 
);
$factory_options ['dropbox'] = array (
'dropbox_enabled' => array (
false,
'dropbox',
'',
sprintf ( $upload_dest_pattern, 'Dropbox' ),
true 
),
'dropbox' => array (
'/',
'dropboxdir:',
'',
sprintf ( $dir_pattern, 'Dropbox' ) 
),
'dropbox_path_id' => array (
'/',
'',
'' 
),
'dropbox_age' => array (
DEFAULT_BACKUP_LIFESPAN,
'dropboxage:',
'',
sprintf ( $age_pattern, 'Dropbox' ) 
),
'dropbox_root' => array (
'dropbox',
'',
'',
_esc ( 'Either `dropbox` or `sandbox`' ) 
),
'dropbox_throttle' => array (
0,
'dropboxthr:',
'',
sprintf ( $throttle_pattern, 'Dropbox' ) 
),
'dropbox_direct_dwl' => array (
true,
'',
'' 
) 
);
$factory_options ['google'] = array (
'google_enabled' => array (
false,
'google',
'',
sprintf ( $upload_dest_pattern, 'Google' ),
true 
),
'google' => array (
'/',
'googledir:',
'',
_esc ( 'The Google Drive folder where to upload the backup' ) 
),
'google_root' => array (
'root',
'',
'',
_esc ( 'Valid values are root or the Google folder`s file_id' ) 
),
'google_path_id' => array (
'root',
'',
'' 
),
'google_age' => array (
DEFAULT_BACKUP_LIFESPAN,
'googleage:',
'',
_esc ( 'Keep only the last n-days backups on Google Drive' ) 
),
'google_throttle' => array (
0,
'googlethr:',
'',
sprintf ( $throttle_pattern, 'Google' ) 
),
'google_direct_dwl' => array (
true,
'',
'' 
) 
);
$factory_options ['webdav'] = array (
'webdav_enabled' => array (
false,
'webdav',
'',
sprintf ( $upload_dest_pattern, 'WebDAV' ),
true 
),
'webdavhost' => array (
'',
'webdavhost:',
'',
sprintf ( $host_pattern, 'WebDAV' ) 
),
'webdavuser' => array (
'',
'webdavuser:',
'',
sprintf ( $user_pattern, 'WebDAV' ) 
),
'webdavpwd' => array (
'',
'webdavpwd:',
'',
sprintf ( $pwd_pattern, 'WebDAV' ) 
),
'webdav' => array (
'/',
'webdavdir:',
'',
sprintf ( $dir_pattern, 'WebDAV' ) 
),
'webdav_path_id' => array (
'/',
'',
'' 
),
'webdav_age' => array (
DEFAULT_BACKUP_LIFESPAN,
'webdavage:',
'',
sprintf ( $age_pattern, 'WebDAV' ) 
),
'webdav_throttle' => array (
0,
'webdavthr:',
'',
sprintf ( $throttle_pattern, 'WebDAV' ) 
),
'webdav_direct_dwl' => array (
true,
'',
'' 
),
'webdav_cainfo' => array (
'',
'webdavca:',
'' 
),
'webdav_authtype' => array (
CURLAUTH_ANY,
'webdavauth:',
'' 
) 
);
$factory_options ['backup2mail'] = array (
'backup2mail' => array (
false,
'bak2mail',
'',
_esc ( 'Use this option if you want to send the backup as email attachment' ),
true 
),
'backup2mail_maxsize' => array (
getUploadLimit (),
'',
'' 
),
'backup2mail_address' => array (
function_exists ( 'get_bloginfo' ) ? get_bloginfo ( 'admin_email' ) : '',
'bak2mailaddr:',
'',
_esc ( 'The email address used to send the backup via email' ) 
),
'backup2mail_smtp' => array (
false,
'bak2mailpear',
'',
_esc ( 'Use this option to PEAR::Mail instead SMTP' ) 
),
'backup2mail_backend' => array (
'sendmail',
'bak2mailtyp:',
'',
_esc ( 'Specify the mail backend to use (mail|smtp|sendmail)' ) 
), 
'backup2mail_host' => array (
'localhost',
'bak2mailhost:',
'',
_esc ( 'Specify the email host server name' ) 
),
'backup2mail_port' => array (
25,
'bak2mailport:',
'',
_esc ( 'Specify the email server port' ) 
),
'backup2mail_auth' => array (
false,
'bak2mailauth:',
'',
_esc ( 'Use this option if you want to authenticate to the email server' ),
true 
),
'backup2mail_user' => array (
null,
'bak2mailuser:',
'',
_esc ( 'The email server authentication user name' ) 
),
'backup2mail_pwd' => array (
null,
'bak2mailpwd:',
'',
_esc ( 'The email server authentication password' ) 
) 
);
$schedules = wp_get_schedules_wrapper ();
$factory_options ['schedule'] = array (
'schedule_enabled' => array (
false,
'',
'' 
),
'schedule_grp' => array (
$is_wp ? 'wp_cron' : 'os_cron',
'',
'' 
),
'schedule_wp_cron' => array (
$is_wp ? (isset ( $schedules ['daily'] ) ? 'daily' : '') : '',
'',
'' 
),
'schedule_wp_cron_time' => array (
false,
'',
'' 
),
'schedule_wpcron_alt' => array (
false,
'',
'' 
) 
);
$factory_options ['history'] = array (
'history_enabled' => array (
true,
'',
'',
_esc ( 'Use this option to enable the job history' ),
true 
),
'historydb' => array (
'sqlite',
'',
'' 
),
'historybackup' => array (
true,
'',
'' 
) 
);
$factory_options ['stats'] = array (
'anonymoususage' => array (
false,
'',
'' 
),
'stats_agree_google' => array (
false,
'',
'' 
) 
);
$factory_options ['logs'] = array (
'logdir' => array (
LOG_DIR,
'',
'',
_esc ( 'The path where to create the logs' ) 
),
'logrotate' => array (
false,
'',
'',
_esc ( 'Rotate logs when they reach a maximum size' ) 
),
'logsize' => array (
1,
'',
'',
_esc ( 'The maximum allowed size for the logs' ) 
),
'logbranched' => array (
defined ( 'BRANCHED_LOGS' ) && BRANCHED_LOGS,
'',
'' 
) 
);
$factory_options ['notification'] = array (
'message_top' => array (
15,
'',
'' 
),
'message_age' => array (
90,
'',
'' 
) 
);
$factory_options ['support'] = array (
'help' => array (
'',
'',
'',
_esc ( 'Show this help message' ) 
),
'debug_on' => array (
false,
'',
'' 
),
'curl_debug_on' => array (
false,
'',
'' 
),
'yayui_on' => array (
false,
'',
'' 
),
'stats_debug_on' => array (
false,
'',
'' 
),
'debug_statusbar_on' => array (
false,
'',
'' 
),
'cookie_accept_on' => array (
false,
'',
'' 
),
'wp_debug_on' => array (
false,
'',
'' 
),
'smtp_debug_on' => array (
false,
'',
'' 
) 
);
$short_opts = array ();
$long_opts = array ();
foreach ( $factory_options as $group => $group_options ) {
foreach ( $group_options as $option_name => $option_params ) {
$long_opts [] = $option_params [1];
$short_opts [] = $option_params [2];
}
}
$_config_files_ = array ();
foreach ( array (
RULES_PATH . '*.php',
RULES_PATH . '*.php.fix',
ADDONS_PATH . '*.php',
ADDONS_PATH . '*.php.fix' 
) as $pattern ) {
$_files_ = glob ( $pattern );
asort ( $_files_ );
foreach ( $_files_ as $_config_file_ )
include_once $_config_file_;
}
?>
