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
 * @file    : CheckSetup.php $
 * 
 * @id      : CheckSetup.php | Mon Dec 28 17:57:55 2015 +0100 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

include_once FUNCTIONS_PATH . 'utils.php';
define( __NAMESPACE__.'\\CHKSETUP_ENABLED_KEY', _esc( 'enabled' ) );
define( __NAMESPACE__.'\\CHKSETUP_ENABLED_VERSION', _esc( 'version' ) );
define( __NAMESPACE__.'\\CHKSETUP_ENABLED_HINT', _esc( 'hint' ) );
define( __NAMESPACE__.'\\CHKSETUP_ENABLED_VALUE', _esc( 'value' ) );
define( __NAMESPACE__.'\\CHKSETUP_ENABLED_SETTINGS', _esc( 'settings' ) );
define( __NAMESPACE__.'\\CHKSETUP_ENABLED_PATH', _esc( 'path' ) );
define( __NAMESPACE__.'\\CHKSETUP_ENABLED_WRITABLE', _esc( 'writable' ) );
class CheckSetup {
private $_nest_level;
private $_settings;
private $_result;
private $_hints;
private $_return_hints;
private $_loaded_extensions;
function __construct( $settings, $return_hints = true ) {
$this->_hints = array( 
'bz2' => _esc( 'Used for compressing files with BZ2 filter' ), 
'zlib' => _esc( 'Used for compressing files with GZip filter' ), 
'zip' => _esc( 'Used for compressing files with Zip filter' ), 
'lzf' => _esc( 'Used for compressing files with LZF filter' ), 
'rar' => _esc( 'Used for compressing files with RAR filter' ), 
'curl' => _esc( 'Used for transfering files via HTTP (eg: Google, Dropbox, Ftp)' ), 
'mysql' => _esc( 'Used for backuping MySQL database and/or to store settings' ), 
'sqlite3' => _esc( 'Used for storing settings and/or app statistics' ), 
'ftp' => _esc( 'Used for backuping your files to FTP media' ), 
'date' => _esc( 'Used for manipulating date/time info' ), 
'openssl' => _esc( 'Used for data encryption and SSL transfer (eg: Google, Dropbox)' ), 
'mcrypt' => _esc( 'Used for data encryption' ), 
'pcre' => _esc( 'Used for various Regex string manipulations' ), 
'fileinfo' => _esc( 'Used for reading file info (eg: mime-type,attributes,etc)' ), 
'hash' => _esc( 'Used for encryption (likewise openssl)' ), 
'json' => _esc( 'Used for internal storage and/or HTTP data exchange' ), 
'session' => _esc( 'Used for session management' ), 
'email' => _esc( 'Used for sending email notification or backup2mail' ), 
'gettext' => _esc( 'Used for multi-language/internationalization support' ), 
'memory_limit' => _esc( 'The maximum amount of RAM that a script is allowed to allocate' ), 
'upload_max_filesize' => _esc( 'The maximum size of an uploaded file' ), 
'post_max_size' => _esc( 'To upload large files, this value must be larger than upload_max_filesize' ), 
'ext_toolchain' => sprintf( _esc( 'The %s toolchain used for compression' ), PHP_OS ), 
'logdir' => _esc( 'The path used for application logging' ), 
'wrkdir' => _esc( 'The path used for creating temporary files' ), 
'safe_mode' => _esc( 
'This option attempts to solve the shared-server security problem.<br>When this is <b>enabled</b> make sure the PHP has read/write permission to <b>WRKDIR-PATH</b>' ), 
'extra_whitespace' => sprintf( 
_esc( 
'This option checks if while downloading a file in browser the file gets prepended with some undesired extra whitespaces. %s' ), 
readMoreHere( APP_ADDONS_SHOP_URI . 'faq-mybackup/#q7' ) ) );
if ( isWin() ) {
$this->_hints['com_dotnet'] = _esc( 'Used for getting the host system resource usage' );
$this->_hints['cygwin'] = _esc( 'Allow to run Linux compression utilities on Windows' );
} else
$this->_hints['posix'] = _esc( 'Used for some Unix specific calls' );
$this->_return_hints = $return_hints;
$this->_loaded_extensions = get_loaded_extensions();
$this->_result = array();
$this->_settings = $settings;
$this->_nest_level = 0;
}
private function _checkExtensions( $names, $extra_hints = null ) {
foreach ( $names as $key => $name )
$this->_checkExtension( $name, isset( $extra_hints[$key] ) ? $extra_hints[$key] : '' );
}
private function _checkExtension( $name, $extra_hint = '' ) {
if ( empty( $name ) )
throw new MyException( 'Extension name should not be empty' );
$this->_result[$name] = array();
$this->_result[$name][CHKSETUP_ENABLED_KEY] = in_array( $name, $this->_loaded_extensions );
if ( $this->_result[$name][CHKSETUP_ENABLED_KEY] )
$this->_result[$name][CHKSETUP_ENABLED_VERSION] = phpversion( $name );
if ( $this->_return_hints && isset( $this->_hints[$name] ) )
$this->_result[$name][CHKSETUP_ENABLED_HINT] = $this->_hints[$name] . $extra_hint;
}
private function _checkCompressionLib() {
global $COMPRESSION_LIBS;
$lzf_optimized_for = function_exists( '\\lzf_optimized_for' ) ? lzf_optimized_for() : false;
foreach ( $COMPRESSION_LIBS as $ext => $name )
$this->_checkExtension( 
$name, 4 == $ext && false !== $lzf_optimized_for ? sprintf( 
_esc( '(optimized for %s)' ), 
0 == $lzf_optimized_for ? _esc( 'compression' ) : _esc( 'speed' ) ) : '' );
}
private function _checkMySql() {
$this->_checkExtension( 'mysql' );
if ( $this->_result['mysql'][CHKSETUP_ENABLED_KEY] ) {
$this->_result['mysql']['mysqldump'] = 'yes';
$mysql_host = isNull( $this->_settings, 'mysql_host', DB_HOST );
$mysql_port = isNull( $this->_settings, 'mysql_port', 3306 );
$mysql_user = isNull( $this->_settings, 'mysql_user', DB_USER );
$mysql_pwd = isNull( $this->_settings, 'mysql_pwd', DB_PASSWORD );
$mysql_db = isNull( $this->_settings, 'mysql_db', DB_NAME );
$mysql_charset = isNull( $this->_settings, 'mysql_charset', DB_CHARSET );
try {
$link = @\mysql_connect( $mysql_host . ':' . $mysql_port, $mysql_user, $mysql_pwd );
$err = false === $link;
if ( ! $err ) {
! empty( $mysql_charset ) && $err = ! @\mysql_set_charset( $mysql_charset, $link );
! ( $err || empty( $mysql_db ) ) && $err = ! @\mysql_select_db( $mysql_db, $link );
}
( false === $link ) || @\mysql_close( $link );
} catch ( MyException $e ) {
$err = true;
}
$this->_result['mysql'][CHKSETUP_ENABLED_SETTINGS] = ! $err;
}
}
private function _checkMail() {
if ( $this->_nest_level > 0 )
return;
$this->_nest_level++;
$name = 'email';
$mail = $this->_settings[$name];
$mail = empty( $mail ) ? MAIL_TEST_ACCOUNT : $mail;
$body = '<style>.chkmailtbl tr:first-child{background-color:#00adee;color:white;font-weight:bold;height:2em;}.chkmail{border-bottom:1px solid #c0c0c0;}' .
PHP_EOL;
$body .= '.chkmail{border-bottom:1px solid #c0c0c0;color:#00adee;}' . PHP_EOL;
$body .= '.chkmailtbl{border:1px solid #c0c0c0;background-color:#fafafa;}' . PHP_EOL;
$body .= '.chkmailtbl td{padding:0px 5px;}' . PHP_EOL;
$body .= '.chkmailtbl tr:first-child{background-color:#00adee;color:white;font-weight:bold;height:2em;}' .
PHP_EOL;
$body .= '.chkmailtbl td:nth-child(2),td:nth-child(3){text-align:center;}';
$body .= '.chkmailtbl td:nth-child(3){color:green;}';
$body .= '</style>' . PHP_EOL;
$body .= '<table class="chkmailtbl"><tr><th>PHP extension</th><th>Version</th><th>Enabled</th></tr>';
foreach ( $this->getSetup() as $ext => $extnfo ) {
$body .= sprintf( 
'<tr><td><b>%s</b></td><td>%s</td><td%s>%s</td></tr>', 
strtoupper( $ext ), 
isset( $extnfo['version'] ) ? $extnfo['version'] : '', 
! $extnfo['enabled'] ? ' style="color:red"' : '', 
$extnfo['enabled'] ? _esc( 'yes' ) : _esc( 'no' ) );
$s = array();
foreach ( $extnfo as $key => $value )
if ( ! in_array( $key, array( 'version', 'enabled', 'hint' ) ) )
$s[] = sprintf( '%s = %s', $key, $value );
$body .= sprintf( 
'<tr><td colspan="3"%s>%s</td></tr>', 
empty( $s ) ? ' class="chkmail"' : '', 
$extnfo['hint'] );
! empty( $s ) && $body .= sprintf( 
'<tr><td colspan="3" class="chkmail">%s</td></tr>', 
implode( ' ; ', $s ) );
}
$body .= '</table>';
$body = '<p>' . _esc( 
sprintf( 
'This message (triggered by %s) confirms that your web server mail support is enabled.<br>Below is a list of the installed PHP extensions (raw format)', 
getClientIP() ) ) . '</p>' . PHP_EOL . $body;
$native_backend = ! strToBool( $this->_settings['backup2mail_smtp'] );
$backend = $this->_settings['backup2mail_backend'];
$smtp_debug = defined( __NAMESPACE__.'\\SMTP_DEBUG' ) && SMTP_DEBUG;
$backend_params = array( 'debug' => $smtp_debug, 'auth' => strToBool( $this->_settings['backup2mail_auth'] ) );
foreach ( array( 
'host' => 'backup2mail_host', 
'port' => 'backup2mail_port', 
'username' => 'backup2mail_user', 
'password' => 'backup2mail_pwd', 
'timeout' => 'request_timeout' ) as $key => $value )
$backend_params[$key] = $this->_settings[$value];
try {
$hasMail = sendMail( 
$mail, 
$mail, 
WPMYBACKUP . ' setup check', 
$body, 
null, 
null, 
3, 
$native_backend ? null : $backend, 
$backend_params, 
$smtp_debug );
} catch ( MyException $e ) {
$hasMail = false;
}
$this->_result[$name] = array();
$this->_result[$name][CHKSETUP_ENABLED_KEY] = $hasMail;
if ( $this->_return_hints ) {
$this->_result[$name][CHKSETUP_ENABLED_HINT] = $this->_hints[$name];
}
}
private function _checkFtp() {
$this->_checkExtension( 'ftp' );
if ( $this->_result['ftp'][CHKSETUP_ENABLED_KEY] ) {
try {
$ftp = getFtpObject( $this->_settings );
$ftp->ftpExecRawCmds( 'SYST' );
$err = false;
} catch ( MyException $e ) {
$err = true;
}
$this->_result['ftp'][CHKSETUP_ENABLED_SETTINGS] = ! $err;
}
}
private function _checkCygWin() {
$pname = 'cygwin';
$this->_result[$pname] = array( 
CHKSETUP_ENABLED_KEY => file_exists( CYGWIN_PATH ), 
CHKSETUP_ENABLED_PATH => CYGWIN_PATH, 
CHKSETUP_ENABLED_HINT => $this->_hints[$pname] );
}
private function _checkOSToolchain() {
global $exclude_files_factory;
$excl_files = explode( ',', $this->_settings['excludefiles'] );
foreach ( $excl_files as $key => $value )
if ( in_array( $value, $exclude_files_factory ) )
$excl_files[$key] = @constant( __NAMESPACE__ . '\\' . substr( $value, 1, strlen( $value ) - 2 ) );
$os_tool_ok = testOSTools( 
$this->_settings['wrkdir'], 
$this->_settings['compression_type'], 
$this->_settings['compression_level'], 
$this->_settings['size'], 
$excl_files, 
explode( ',', $this->_settings['excludedirs'] ), 
explode( ',', $this->_settings['excludeext'] ), 
$this->_settings['bzipver'], 
$this->_settings['cygwin'] );
$pname = 'ext_toolchain';
$this->_result[$pname] = array( 
CHKSETUP_ENABLED_KEY => $os_tool_ok, 
CHKSETUP_ENABLED_HINT => $this->_hints[$pname] );
}
private function _checkPathByName( $prop_name ) {
$key = $prop_name . '-path';
$this->_result[$key] = array( CHKSETUP_ENABLED_KEY => isset( $this->_settings[$prop_name] ) );
if ( isset( $this->_settings[$prop_name] ) ) {
$this->_result[$key][CHKSETUP_ENABLED_PATH] = $this->_settings[$prop_name];
$this->_result[$key][CHKSETUP_ENABLED_WRITABLE] = is_writable( $this->_settings[$prop_name] );
$this->_result[$key][CHKSETUP_ENABLED_HINT] = $this->_hints[$prop_name];
$this->_result[$key][CHKSETUP_ENABLED_VALUE] = getHumanReadableSize( 
file_exists( $this->_settings[$prop_name] ) ? @disk_free_space( $this->_settings[$prop_name] ) : 0 );
}
}
private function _checkPath() {
$paths = array( 'logdir', 'wrkdir' );
foreach ( $paths as $value ) {
$this->_checkPathByName( $value );
}
}
private function _checkMaxExecTime() {
$pname = 'max_execution_time';
$pval = intval( ini_get( $pname ) );
$this->_result[$pname] = array( 
CHKSETUP_ENABLED_KEY => ! empty( $pval ), 
CHKSETUP_ENABLED_VALUE => $pval, 
CHKSETUP_ENABLED_HINT => sprintf( 
_esc( 
'The max time (s) a script may run before it is terminated by the PHP parser.<br>Make sure it allows the scripts to finish their job%s.' ), 
$pval < 600 ? ' ' . _esc( '(recommended >= 600s)' ) : '' ) );
}
private function _checkPHPMemLimit() {
$pname = 'memory_limit';
$pval = getMemoryLimit();
$this->_result[$pname] = array( 
CHKSETUP_ENABLED_KEY => $pval > 0, 
CHKSETUP_ENABLED_VALUE => getHumanReadableSize( $pval ), 
CHKSETUP_ENABLED_HINT => $this->_hints[$pname] );
}
private function _checkPHPUploadLimit() {
$pname = 'upload_max_filesize';
$pval = getUploadLimit();
$this->_result[$pname] = array( 
CHKSETUP_ENABLED_KEY => $pval > 0, 
CHKSETUP_ENABLED_VALUE => getHumanReadableSize( $pval ), 
CHKSETUP_ENABLED_HINT => $this->_hints[$pname] );
}
private function _checkPHPPostMaxSize() {
$pname = 'post_max_size';
$pval = php_inivalu2int( ini_get( $pname ) );
$this->_result[$pname] = array( 
CHKSETUP_ENABLED_KEY => false !== $pval, 
CHKSETUP_ENABLED_VALUE => getHumanReadableSize( $pval ), 
CHKSETUP_ENABLED_HINT => $this->_hints[$pname] );
}
private function _checkSafeMode() {
$pname = 'safe_mode';
$val = ini_get( $pname );
$this->_result[$pname] = array( 
CHKSETUP_ENABLED_KEY => ! empty( $val ) && strToBool( $val ), 
CHKSETUP_ENABLED_HINT => $this->_hints[$pname], 
CHKSETUP_ENABLED_SETTINGS => empty( $val ) || ! strToBool( $val ) );
}
private function _checkDownloadWhiteSpace() {
$curl_wrapper = new CurlWrapper();
$curl_wrapper->setAllowCookies();
$curl_wrapper->setFollowLocation();
$action = 'test_dwl';
$service = 'test';
$params = array( 'action' => $action, 'service' => $service, 'nonce' => create_nonce( $action ) );
$url = getAsyncRunURL(); 
$response = $curl_wrapper->curlPOST( $url, null, http_build_query( $params ) );
$pname = 'extra_whitespace';
$this->_result[$pname] = array( 
CHKSETUP_ENABLED_KEY => ! empty( $response ), 
CHKSETUP_ENABLED_HINT => $this->_hints[$pname], 
CHKSETUP_ENABLED_SETTINGS => empty( $response ) || ( $service == $response ) );
}
private function _checkSetup() {
$this->_checkCompressionLib();
$this->_checkMySql();
$this->_checkMail();
$this->_checkFtp();
$this->_checkPath();
$this->_checkMaxExecTime();
$this->_checkPHPMemLimit();
$this->_checkPHPUploadLimit();
$this->_checkPHPPostMaxSize();
$this->_checkSafeMode();
$this->_checkExtensions( 
array( 
'curl', 
'sqlite3', 
'date', 
'openssl', 
'mcrypt', 
'pcre', 
'fileinfo', 
'hash', 
'json', 
'session', 
'gettext' ) );
if ( isWin() ) {
$this->_checkExtension( 'com_dotnet' );
$this->_checkCygWin();
} else {
$this->_checkExtension( 'posix' );
}
defined( __NAMESPACE__.'\\OPER_COMPRESS_EXTERN' ) && $this->_checkOSToolchain();
$this->_checkDownloadWhiteSpace();
}
public function getSetup() {
$this->_checkSetup();
ksort( $this->_result );
array_walk( $this->_result, function ( &$value, $key ) {
ksort( $value );
} );
$this->_nest_level = 0;
return $this->_result;
}
}
?>