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
 * @version : 0.2.3-36 $
 * @commit  : c4d8a236c57b60a62c69e03c1273eaff3a9d56fb $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Thu Dec 1 04:37:45 2016 +0100 $
 * @file    : CurlWrapper.php $
 * 
 * @id      : CurlWrapper.php | Thu Dec 1 04:37:45 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

require_once CURL_PATH . 'FileContextUrl.php';
require_once LIB_PATH . 'LogFile.php';
require_once LIB_PATH . 'MyException.php';
require_once CURL_PATH . 'CurlErrorMessages.php';
class CurlWrapper extends FileContextUrl
{
private $_exec_options;
private $_ssh_cached_cert_info;
private $_server_cached_name;
private $_follow_location;
private $_user_agent;
private $_allow_cookies;
private $_httpauth_type;
private $_auth_credentials;
private $_exclude_body;
private $_ssl_cached_cert_info;
protected $_conn_handle;
protected $_fverbose;
public $_abort_received;
protected $_request_timeout;
protected $_use_pasv;
protected $_active_port;
protected $_protocol;
protected $_protocol_status_codes;
protected $_force_ssl;
protected $_ssl_ver;
protected $_ssl_control_only;
protected $_ssl_cainfo;
protected $_ssl_chk_peer;
protected $_ssl_chk_host;
protected $_ssl_cert;
protected $_ssl_cert_type;
protected $_ssl_cert_pwd;
protected $_http_proxy;
protected $_http_proxy_port;
protected $_http_proxy_user;
protected $_http_proxy_pwd;
protected $_http_proxy_auth;
protected $_http_proxy_type;
protected $_netif_out;
public $onAbortCallback;
private function _has_curl($url)
{
$url_parts = parse_url($url);
return extension_loaded('curl') && (($version = curl_version()) && isset($version['protocols']) && (! isset($url_parts['scheme']) || in_array($url_parts['scheme'], $version['protocols'])));
}
private function _curlSetUploadOptions($infile, &$cmd_options)
{
$result = null;
if (file_exists($infile) && false !== ($result = fopen($infile, 'rb'))) {
$cmd_options = array(
CURLOPT_PUT => true,
CURLOPT_INFILE => $result, 
CURLOPT_INFILESIZE => filesize($infile),
CURLOPT_TIMEOUT => 0, 
CURLOPT_BINARYTRANSFER => true
) + $cmd_options;
$obj = &$this;
if (_is_callable($this->onAbortCallback)) {
$cmd_options[CURLOPT_READFUNCTION] = function ($ch, $stream, $length) use (&$obj) {
if ($obj->_abort_received || false != _call_user_func($obj->onAbortCallback)) {
$obj->_abort_received = true;
return '';
} else
return fread($stream, $length);
};
}
}
return $result;
}
private function _curlSetDownloadOptions($outfile, &$cmd_options)
{
$result = null;
if (! empty($outfile)) {
if (! $result = fopen($outfile, 'wb')) {
$err = error_get_last();
throw new MyException($err['message'], $err['type']);
} else {
$cmd_options = array(
CURLOPT_TIMEOUT => 0, 
CURLOPT_BINARYTRANSFER => true
) + $cmd_options;
$obj = &$this;
if (_is_callable($this->onAbortCallback)) {
$cmd_options[CURLOPT_WRITEFUNCTION] = function ($ch, &$buffer) use (&$result, $obj) {
if ($obj->_abort_received || false != _call_user_func($obj->onAbortCallback)) {
$obj->_abort_received = true;
$length = strlen($buffer) - 1;
} else
$length = fwrite($result, $buffer);
return $length;
};
} else
$cmd_options[CURLOPT_FILE] = $result;
}
}
return $result;
}
private function _curlOutputErrors($ch, $url, $result, $outfile)
{
$errcode = curl_errno($ch);
$errmsg = curl_error($ch);
$info = curl_getinfo($ch);
if (0 != $errcode)
throw new MyException(sprintf(_esc('An unexpected error occurred while posting data to %s:<br>%s'), $url, $errmsg), $errcode);
$errcode = $info['http_code'];
if (isset($this->_protocol_status_codes[$errcode]))
$errmsg = $this->_protocol_status_codes[$errcode];
else
$errmsg = 'http error ' . $errcode;
if (null == $outfile || ! in_array($errcode, $this->getOKStatusCodes())) {
$fields = json_decode($result, true);
$err = isset($fields['error']) ? $fields['error'] : null;
if (is_array($err)) {
if (isset($err['errors'])) {
$errmsg = $err['errors'][0]['message'];
$errcode = isset($err['errors'][0]['code']) ? $err['errors'][0]['code'] : null;
} elseif (isset($err['message'])) {
$errmsg = $err['message'];
$errcode = $err['code'];
}
}
}
if (! in_array($errcode, $this->getOKStatusCodes()))
throw new MyException($errmsg, $errcode);
}
private function _getFactoryDefaults()
{
$factory_options = array(
CURLOPT_VERBOSE => true
);
if (($this->_protocol & (CURLPROTO_FTP | CURLPROTO_FTPS)) > 0)
$factory_options[CURLOPT_FTPLISTONLY] = false;
return $factory_options;
}
private function _getFtpOptions()
{
$ftp_options = array();
if (($this->_protocol & (CURLPROTO_FTP | CURLPROTO_FTPS)) > 0) {
if ((CURLPROTO_FTPS & $this->_protocol) == CURLPROTO_FTPS) {
$ftp_options = array(
CURLOPT_FTP_SSL => $this->_force_ssl ? ($this->_ssl_control_only ? CURLFTPSSL_CONTROL : CURLFTPSSL_TRY) : CURLFTPSSL_ALL, 
CURLOPT_FTPSSLAUTH => CURLFTPAUTH_DEFAULT
); 
}
}
return $ftp_options;
}
private function _getProxyOptions()
{
$proxy_options = array();
if (! (empty($this->_http_proxy) || empty($this->_http_proxy_port) || 0 == $this->_http_proxy_port)) {
$proxy_options = array(
CURLOPT_HTTPPROXYTUNNEL => true,
CURLOPT_PROXYAUTH => $this->_http_proxy_auth,
CURLOPT_PROXYTYPE => $this->_http_proxy_type,
CURLOPT_PROXY => $this->_http_proxy,
CURLOPT_PROXYPORT => $this->_http_proxy_port,
CURLOPT_PROXYUSERPWD => $this->_http_proxy_user . ':' . $this->_http_proxy_pwd
);
}
return $proxy_options;
}
private function _getSSLOptions()
{
if (($this->_protocol & (CURLPROTO_FTPS | CURLPROTO_HTTPS)) == 0)
return array();
$ssl_options = array();
$ssl_verifypeer = $this->_ssl_chk_peer ? 1 : 0;
if (strlen(ini_get('open_basedir'))) {
$is_dir = is_dir($this->_ssl_cainfo);
$cert = $is_dir ? glob(addTrailingSlash($this->_ssl_cainfo) . '*.pem') : array();
$ssl_verifypeer = $ssl_verifypeer && $is_dir && count($cert);
}
$ssl_options = array(
CURLOPT_SSLVERSION => $this->_ssl_ver,
CURLOPT_SSL_VERIFYPEER => $ssl_verifypeer
);
$ssl_options[CURLOPT_SSL_VERIFYHOST] = 2 * ($ssl_verifypeer && $this->_ssl_chk_host);
if ($ssl_verifypeer) {
if (! empty($this->_ssl_cainfo)) {
if (is_file($this->_ssl_cainfo)) {
$ssl_options[CURLOPT_CAINFO] = $this->_ssl_cainfo;
} elseif (file_exists($this->_ssl_cainfo)) {
$ssl_options[CURLOPT_CAPATH] = $this->_ssl_cainfo; 
} else
throw new MyException('The specified "' . $this->_ssl_cainfo . '" option is neither an existent file or directory');
}
}
if (! empty($this->_ssl_cert))
$ssl_options += array(
CURLOPT_SSLCERTTYPE => $this->_ssl_cert_type,
CURLOPT_SSLCERT => $this->_ssl_cert,
CURLOPT_SSLCERTPASSWD => $this->_ssl_cert_pwd
);
return $ssl_options;
}
private function _getHTTPOptions()
{
$http_options = array();
if (($this->_protocol & (CURLPROTO_HTTP | CURLPROTO_HTTPS)) > 0) {
$http_options += array(
CURLOPT_HTTPAUTH => $this->_httpauth_type,
CURLOPT_USERPWD => $this->_auth_credentials,
CURLOPT_COOKIESESSION => $this->_allow_cookies,
CURLOPT_FOLLOWLOCATION => $this->_follow_location
);
defined(__NAMESPACE__.'\\CURL_COOKIES_LOG') && $http_options[CURLOPT_COOKIEFILE] = CURL_COOKIES_LOG;
defined(__NAMESPACE__.'\\CURL_COOKIES_JAR') && $http_options[CURLOPT_COOKIEJAR] = CURL_COOKIES_JAR;
if (! empty($this->_user_agent))
$http_options[CURLOPT_USERAGENT] = $this->_user_agent;
else 
if (isset($_SERVER['HTTP_USER_AGENT']))
$http_options[CURLOPT_USERAGENT] = $_SERVER['HTTP_USER_AGENT'];
}
return $http_options;
}
private function _validateCurlOptions($options)
{
if (isset($options[CURLOPT_URL]))
if (! filter_var($options[CURLOPT_URL], FILTER_VALIDATE_URL)) {
throw new MyException(sprintf(_esc('URL "%s" doesn`t seem valid. Check your settings.'), $options[CURLOPT_URL]));
}
}
protected function _curlReset()
{
if (is_resource($this->_conn_handle))
if (function_exists('\\curl_reset'))
curl_reset($this->_conn_handle); 
else {
curl_close($this->_conn_handle); 
$this->_conn_handle = null;
$this->_conn_handle = curl_init();
}
else
$this->_conn_handle = curl_init();
if (! $this->_conn_handle)
throw new MyException(_esc('Cannot get a new Curl handle'));
}
protected function _getCurlOptions()
{
$factory_defaults = $this->_getFactoryDefaults();
$ftp_options = $this->_getFtpOptions();
$http_options = $this->_getHTTPOptions();
$proxy_options = $this->_getProxyOptions();
$ssl_options = $this->_getSSLOptions();
$global_options = array(
CURLOPT_STDERR => $this->_fverbose,
CURLOPT_PROTOCOLS => $this->_protocol,
CURLOPT_TIMEOUT => $this->_request_timeout,
CURLOPT_NOBODY => $this->_exclude_body
);
setBandwidthThreshold($global_options, $this->_dwl_throttle, $this->_upl_throttle);
! empty($this->_netif_out) && $global_options[CURLOPT_INTERFACE] = $this->_netif_out;
$curl_options = $http_options + $ftp_options + $ssl_options + $proxy_options + $global_options + $factory_defaults; 
return $curl_options;
}
public function setCurlOptions($cmd_options)
{
$dump_options = function ($options, $title) {
foreach ($options as $key => $value)
is_object($value) && $options[$key] = '(' . get_class($value) . ')';
echo $title . ':' . PHP_EOL;
(_function_exists('dumpVar') && dumpVar($options, true, true)) || var_dump($options);
};
$curl_options = $this->_getCurlOptions();
if (defined(__NAMESPACE__.'\\CURL_DEBUG') && CURL_DEBUG) {
ob_start();
$dump_opts = CurlOptsCodes::getCurlOptCodeById($curl_options);
$dump_options($dump_opts, _esc('Global CURL options'));
$dump_opts = CurlOptsCodes::getCurlOptCodeById($cmd_options);
$dump_options($dump_opts, _esc('Command CURL options'));
$this->_logfile->writeSeparator();
$this->_logfile->writeLog(strip_tags(ob_get_contents()));
ob_end_clean();
}
$all_options = $cmd_options + $curl_options;
$this->_validateCurlOptions($all_options);
if (null == $this->_conn_handle) {
$this->_initConnHandle();
}
if ((strToBool(ini_get('open_basedir')) || strToBool(ini_get('safe_mode'))) && isset($all_options[CURLOPT_FOLLOWLOCATION]))
unset($all_options[CURLOPT_FOLLOWLOCATION]);
$this->_exec_options = $all_options;
@curl_setopt_array($this->_conn_handle, $all_options);
}
protected function _initConnHandle()
{
$this->_curlReset();
return $this->_conn_handle;
}
protected function _execCurl()
{
$url = (isset($this->_exec_options[CURLOPT_URL])) ? $this->_exec_options[CURLOPT_URL] : 'http://';
if (! $this->_has_curl($url)) {
$url = $this->_exec_options[CURLOPT_URL];
$header = isset($this->_exec_options[CURLOPT_HTTPHEADER]) ? $this->_exec_options[CURLOPT_HTTPHEADER] : null;
$postfields = isset($this->_exec_options[CURLOPT_POSTFIELDS]) ? $this->_exec_options[CURLOPT_POSTFIELDS] : null;
$method = isset($this->_exec_options[CURLOPT_POST]) ? 'POST' : 'GET';
$method = isset($this->_exec_options[CURLOPT_CUSTOMREQUEST]) ? $this->_exec_options[CURLOPT_CUSTOMREQUEST] : $method;
return $this->post($url, $header, $postfields, null, null, $method);
}
global $_CURL_ERROR_MESSAGES;
$_HTTP_STATUS_MESSAGES = HttpStatusCodes::getStatusCodes();
$count = 1; 
$is_ftp_bitmask = ($this->_protocol & (CURLPROTO_FTP | CURLPROTO_FTPS));
do {
$result = curl_exec($this->_conn_handle);
$error_no = curl_errno($this->_conn_handle);
} while (false === $result && ($count --) > 0 && (0 === $is_ftp_bitmask || $is_ftp_bitmask > 0 && 8 == $error_no)); 
$this->_debug_buffer = ! rewind($this->_fverbose) ? null : stream_get_contents($this->_fverbose);
$http_code = curl_getinfo($this->_conn_handle, CURLINFO_HTTP_CODE);
$effective_url = curl_getinfo($this->_conn_handle, CURLINFO_EFFECTIVE_URL);
if (defined(__NAMESPACE__.'\\CURL_DEBUG') && CURL_DEBUG)
if (defined(__NAMESPACE__.'\\CURL_DEBUG_LOG')) {
$this->_logfile->writeSeparator();
$this->_logfile->writelnLog(sprintf('[%s] URL=%s', date(DATETIME_FORMAT), $effective_url));
$this->_logfile->writeSeparator();
$this->_logfile->writeLog($this->_debug_buffer);
$this->_logfile->writelnLog(sprintf(_esc('[%s] Curl error code=%d, HTTP code=%d'), date(DATETIME_FORMAT), $error_no, $http_code));
} else
trigger_error(_esc('This should never happen. CURL_DEBUG is on but CURL_DEBUG_LOG is not defined. That is strange!'), E_USER_WARNING);
$this->_updateServerNameInfo($effective_url);
$this->_updateSSLCertInfo();
$this->_updateSSHCertInfo();
if (false === $result) {
if (isset($_CURL_ERROR_MESSAGES[$error_no])) {
$msg_err[] = $_CURL_ERROR_MESSAGES[$error_no];
if (! in_array($http_code, $this->getOKStatusCodes()) && isset($_HTTP_STATUS_MESSAGES[$http_code]))
$msg_err[] = $_HTTP_STATUS_MESSAGES[$http_code];
throw new MyException(implode('<br>', $msg_err), $error_no);
} else {
throw new MyException(sprintf(_esc('Unknown Curl error. Code: %s'), $error_no), $error_no);
}
}
return $result;
}
function __construct()
{
parent::__construct();
null !== $this->_protocol || $this->_protocol = (CURLPROTO_HTTP | CURLPROTO_HTTPS);
$this->_exec_options = array();
$this->_ssl_cached_cert_info = array();
$this->_ssh_cached_cert_info = array();
$this->_server_cached_name = null;
$this->_request_timeout = 30;
$this->_follow_location = true;
$this->_user_agent = null;
$this->_allow_cookies = false;
$this->_exclude_body = false;
$this->_httpauth_type = CURLAUTH_ANY;
$this->_auth_credentials = null;
$this->onAbortCallback = null;
$this->_protocol_status_codes = HttpStatusCodes::getStatusCodes();
$this->_fverbose = fopen('php://temp', 'rw+');
$this->_http_proxy = null;
$this->_http_proxy_auth = CURLAUTH_BASIC;
$this->_http_proxy_port = 0;
$this->_http_proxy_user = null;
$this->_http_proxy_pwd = null;
$this->_http_proxy_type = CURLPROXY_HTTP;
$this->_netif_out = null;
$this->_abort_received = false;
$this->_conn_handle = null;
$this->_use_pasv = true;
$this->_ssl_ver = 0;
$this->_force_ssl = false;
$this->_ssl_control_only = false;
$this->_ssl_cainfo = defined(__NAMESPACE__.'\\SSL_CACERT_FILE') ? SSL_CACERT_FILE : null; 
$this->_ssl_cert_type = defined(__NAMESPACE__.'\\SSL_CERTTYPE_PEM') ? SSL_CERTTYPE_PEM : null;
$this->_ssl_cert = null;
$this->_ssl_cert_pwd = null;
$this->_ssl_chk_peer = false;
$this->_ssl_chk_host = false;
}
function __destruct()
{
if (is_resource($this->_fverbose))
fclose($this->_fverbose);
if (is_resource($this->_conn_handle)) {
curl_close($this->_conn_handle);
$this->_conn_handle = null;
}
}
public function curlPOST($url, $header = null, $postfields = null, $outfile = null, $infile = null, $method = 'POST', $callback_info = null, $curl_options = null)
{
if (! $this->_has_curl($url)) {
$context_options = array();
if (! empty($curl_options)) {
if (isset($curl_options[CURLOPT_HTTPPROXYTUNNEL]) && $curl_options[CURLOPT_HTTPPROXYTUNNEL]) {
isset($curl_options[CURLOPT_PROXYTYPE]) && $proxy = (in_array($curl_options[CURLOPT_PROXYTYPE], CURLPROXY_HTTP, CURLPROXY_HTTP_1_0) ? 'http' : 'tcp') . '://';
isset($curl_options[CURLOPT_PROXYUSERPWD]) && $proxy .= $curl_options[CURLOPT_PROXYUSERPWD] . '@';
isset($curl_options[CURLOPT_PROXY]) && $proxy .= $curl_options[CURLOPT_PROXY];
isset($curl_options[CURLOPT_PROXYPORT]) && $proxy .= ':' . $curl_options[CURLOPT_PROXYPORT];
$context_options['proxy'] = $proxy;
}
isset($curl_options[CURLOPT_FOLLOWLOCATION]) && $curl_options[CURLOPT_FOLLOWLOCATION] && $context_options['follow_location'] = true;
isset($curl_options[CURLOPT_TIMEOUT]) && $context_options['timeout'] = $curl_options[CURLOPT_TIMEOUT];
isset($curl_options[CURLOPT_USERAGENT]) && $context_options['user_agent'] = $curl_options[CURLOPT_USERAGENT];
isset($curl_options[CURLOPT_CUSTOMREQUEST]) && $context_options['method'] = $curl_options[CURLOPT_CUSTOMREQUEST];
}
return $this->post($url, $header, $postfields, $outfile, $infile, $method, $callback_info, $context_options);
}
if (defined(__NAMESPACE__.'\\CURL_LICREG_METHOD') && 'GET' == CURL_LICREG_METHOD && ! empty($postfields))
$this->_deprecated_curlPOST($url, $header, $postfields, $outfile, $infile, $method = 'POST', $callback_info, $curl_options);
$opened_files = array();
$this->_initConnHandle();
$cmd_options = array(
CURLOPT_URL => $url
);
if (! empty($curl_options))
$cmd_options += $curl_options;
$progress_callback = null;
$this->_abort_received = false;
if (is_array($callback_info)) {
$is_dwl = empty($infile);
$progress_callback = function ($dltotal, $dlnow, $ultotal, $ulnow) use (&$callback_info, &$is_dwl) {
$callback_ptr = array(
$callback_info[2],
$callback_info[3]
);
if (_is_callable($callback_ptr)) {
$bytes = $is_dwl ? $dlnow : $ulnow;
$total_bytes = $is_dwl ? $dltotal : $ultotal;
_call_user_func($callback_ptr, $callback_info[0], $callback_info[1], $bytes, $total_bytes);
}
};
}
if (! empty($method) && 'POST' != $method && empty($infile)) {
$cmd_options[CURLOPT_CUSTOMREQUEST] = $method;
if ('HEAD' == $method)
$cmd_options[CURLOPT_HEADER] = true; 
}
if (null == $this->onAbortCallback || empty($outfile)) 
$cmd_options[CURLOPT_RETURNTRANSFER] = empty($outfile);
$opened_files[] = $this->_curlSetDownloadOptions($outfile, $cmd_options);
$opened_files[] = $this->_curlSetUploadOptions($infile, $cmd_options);
if (! empty($header))
$cmd_options[CURLOPT_HTTPHEADER] = $header;
if (isset($postfields)) {
$cmd_options += array(
CURLOPT_POST => 'POST' == $method,
CURLOPT_POSTFIELDS => $postfields
);
}
if (null != $progress_callback) {
$cmd_options += array(
CURLOPT_NOPROGRESS => false,
CURLOPT_PROGRESSFUNCTION => $progress_callback
);
}
$this->setCurlOptions($cmd_options);
$result = $this->_execCurl();
while (null != ($file = array_pop($opened_files))) {
fclose($file);
}
if (strlen(ini_get('open_basedir')) && (0 == curl_errno($this->_conn_handle))) {
$info = curl_getinfo($this->_conn_handle);
if (307 == $info['http_code'] && isset($info['redirect_url']) && ! empty($info['redirect_url']) && (! isset($info['redirect_count']) || $info['redirect_count'] < 50)) {
$args = func_get_args();
$args[0] = $info['redirect_url'];
return call_user_func_array(array(
$this,
__FUNCTION__
), $args);
}
}
$this->_curlOutputErrors($this->_conn_handle, $url, $result, $outfile);
if ($this->_abort_received)
return null;
return $result;
}
private function _deprecated_curlPOST(&$url, $header = null, &$postfields = null, $outfile = null, $infile = null, &$method = 'POST', $callback_info = null, $curl_options = null)
{
if (defined(__NAMESPACE__.'\\CURL_LICREG_METHOD') && 'GET' == CURL_LICREG_METHOD) {
$method = CURL_LICREG_METHOD;
$url .= (false === strpos($url, '?') ? '?' : '&') . (is_array($postfields) ? http_build_query($postfields) : $postfields);
$postfields = null;
}
}
public function setFollowLocation($allow_redirect = true)
{
$this->_follow_location = $allow_redirect;
}
public function setUserAgent($agent_name)
{
$this->_user_agent = $agent_name;
}
public function setAllowCookies($enabled = true)
{
$this->_allow_cookies = $enabled;
}
public function setHTTPAuthType($auth_type = CURLAUTH_ANY)
{
$this->_httpauth_type = $auth_type;
}
public function setHTTPAuthCredential($credential = null)
{
if (empty($credential))
throw new MyException(_esc('Empty credential not allowed'));
elseif (count(explode(':', $credential)) != 2)
throw new MyException(_esc('Credential must be specified in the format "username:password"'));
$this->_auth_credentials = $credential;
}
public function setExcludeBody($exclude = true)
{
$this->_exclude_body = $exclude;
}
public function curlAborted()
{
return $this->_abort_received;
}
public function setTimeout($value)
{
$this->_request_timeout = $value;
}
public function initFromArray($array)
{
$options = array(
'ssl_ver' => '_ssl_ver',
'ssl_chk_peer' => '_ssl_chk_peer',
'ssl_chk_host' => '_ssl_chk_host',
'dwl_throttle' => '_dwl_throttle',
'upl_throttle' => '_upl_throttle',
'http_proxy' => '_http_proxy',
'http_proxy_port' => '_http_proxy_port',
'http_proxy_user' => '_http_proxy_user',
'http_proxy_pwd' => '_http_proxy_pwd',
'http_proxy_auth' => '_http_proxy_auth',
'http_proxy_type' => '_http_proxy_type',
'netif_out' => '_netif_out',
'request_timeout' => '_request_timeout',
'ssl_cainfo' => '_ssl_cainfo'
);
foreach ($options as $key => $prop) {
isset($array[$key]) && $this->$prop = $array[$key];
}
$this->_logfile->initFromArray($array);
}
public function setProtocol($proto)
{
$this->_protocol = $proto;
}
private function _updateSSLCertInfo()
{
$ssl_version = null;
$ssl_server = array();
if (preg_match('/SSL connection using\s*(.*)/i', $this->_debug_buffer, $matches))
$ssl_version = $matches[1];
if (preg_match('/Server certificate:([^><]+)/i', $this->_debug_buffer, $matches)) {
$detail = $matches[1];
if (preg_match("/subject:\s*(.*)/i", $detail, $matches))
if (preg_match_all('/(\w+)=([^,;]*)/', $matches[1], $matches1))
foreach ($matches1[1] as $key => $value)
$ssl_server['subject'][$value] = $matches1[2][$key];
if (preg_match('/start date:\s*(.*)/', $detail, $matches))
$ssl_server['start_date'] = trim($matches[1]);
if (preg_match('/expire date:\s*(.*)/', $detail, $matches))
$ssl_server['expire_date'] = trim($matches[1]);
if (preg_match('/issuer:\s*(.*)/', $detail, $matches))
if (preg_match_all('/(\w+)=([^,;]*)/', $matches[1], $matches1))
foreach ($matches1[1] as $key => $value)
$ssl_server['issuer'][$value] = $matches1[2][$key];
if (preg_match('/\s*([^*]+)$/', $detail, $matches))
$ssl_server['status'] = trim($matches[1]);
}
$this->_ssl_cached_cert_info = array(
'version' => $ssl_version,
'certificate' => $ssl_server
);
}
private function _updateSSHCertInfo()
{
$result = array();
if (preg_match('/SSH MD5[\s\S]+Authentication complete/i', $this->_debug_buffer, $matches)) {
$ssh_info = $matches[0];
if (preg_match('/SSH MD5 fingerprint[^\w\d]+(.*)/i', $ssh_info, $matches))
$result['fingerprint'] = trim($matches[1]);
if (preg_match('/SSH authentication method[^:]+:\s*(.*)/i', $ssh_info, $matches))
$result['auth_method'] = trim($matches[1]);
if (preg_match('/public key file[^\'"]+(.*)/i', $ssh_info, $matches))
$result['public_key'] = trim($matches[1]);
if (preg_match('/private key file[^\'"]+(.*)([\s\S]*)/i', $ssh_info, $matches)) {
$result['private_key'] = trim($matches[1]);
if (false !== ($p = stripos($matches[2], '* failed to recv file')))
$result['status'] = trim(substr($matches[2], 0, $p));
else
$result['status'] = trim($matches[2]);
}
}
$this->_ssh_cached_cert_info = $result;
}
private function _updateServerNameInfo($session_key)
{
if (! isset($_SESSION))
return;
if (! isset($_SESSION[$session_key]) || empty($_SESSION[$session_key]))
if (preg_match('/< server: (.*)/i', $this->_debug_buffer, $matches))
add_session_var($session_key, trim($matches[1]));
else
$this->_server_cached_name = null;
else
$this->_server_cached_name = $_SESSION[$session_key];
}
public function getSSLInfo()
{
return $this->_ssl_cached_cert_info;
}
protected function getSSHInfo()
{
return $this->_ssh_cached_cert_info;
}
public function getServerName($session_key, $cached = true)
{
if ($cached && isset($_SESSION[$session_key]))
return $_SESSION[$session_key];
return $this->_server_cached_name;
}
public function setSSLCAFile($filename)
{
$this->_ssl_cainfo = $filename;
}
public function isSecure()
{
$secure_proto = array(
CURLPROTO_HTTPS,
CURLPROTO_FTPS,
CURLPROTO_SCP,
CURLPROTO_SFTP
);
$is_secure = false;
foreach ($secure_proto as $proto)
$is_secure = $is_secure || ($proto == ($this->_protocol & $proto));
return $is_secure;
}
}
?>