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
 * @version : 1.0-2 $
 * @commit  : f8add2d67e5ecacdcf020e1de6236dda3573a7a6 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Tue Dec 13 06:40:49 2016 +0100 $
 * @file    : FileContextUrl.php $
 * 
 * @id      : FileContextUrl.php | Tue Dec 13 06:40:49 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

require_once LIB_PATH . 'MyException.php';
require_once LIB_PATH . 'LogFile.php';
include_once UTILS_PATH . 'url.php';
class FileContextUrl
{
private $_is_dwl;
private $_ok_status_codes;
private $_redirect_codes;
private $_options;
private $_callback_info;
private $_started;
protected $_logfile;
protected $_dwl_throttle;
protected $_upl_throttle;
protected $_debug_buffer;
function __construct()
{
$this->_logfile = new LogFile(defined(__NAMESPACE__.'\\CURL_DEBUG_LOG') ? CURL_DEBUG_LOG : null);
$this->_options = array();
$this->_ok_status_codes = array();
$this->_redirect_codes = array();
$this->_is_dwl = true; 
$this->_dwl_throttle = 0;
$this->_upl_throttle = 0;
$this->_started = 0;
$this->_debug_buffer = null;
$this->_callback_info = null;
$this->setOKStatusCodes();
$this->setRedirectStatusCodes();
}
private function _progress_callback($dltotal, $dlnow, $ultotal, $ulnow, &$callback_info = null)
{
if (! (is_array($callback_info) && count($callback_info) > 3))
return;
$callback_ptr = array(
$callback_info[2],
$callback_info[3]
);
if (_is_callable($callback_ptr)) {
$bytes = $this->_is_dwl ? $dlnow : $ulnow;
$total_bytes = $this->_is_dwl ? $dltotal : $ultotal;
_call_user_func($callback_ptr, $callback_info[0], $callback_info[1], $bytes, $total_bytes);
}
}
private function _parse_ftp_response_meta($meta)
{}
private function _parse_http_response_meta($meta)
{
$result = array();
if (isset($meta['wrapper_data']) && ! empty($meta['wrapper_data'])) {
foreach ($meta['wrapper_data'] as $key => $value) {
if (preg_match('/(.+)\s+(\d+)\s+(.+)/', $value, $matches) && ! isset($result['status']))
$result['status'] = array(
'code' => $matches[2],
'message' => $matches[3]
);
if (preg_match('/^Location:\s*(.+)/', $value, $matches) && ! isset($result['redirect']))
$result['redirect'] = $matches[1];
}
}
return $result;
}
private function _parse_response_meta($meta)
{
$result = array();
if (isset($meta['wrapper_type'])) {
switch ($meta['wrapper_type']) {
case 'http':
$result = $this->_parse_http_response_meta($meta);
break;
case 'ftp':
$result = $this->_parse_ftp_response_meta($meta);
break;
}
}
return $result;
}
private function _prepare_request($filename, $postfields = null)
{
list ($boundary_mixed, $boundary_alt_alt) = get_content_boundaries();
$fdata = '';
$header = '';
$data = '';
if (! empty($filename) && file_exists($filename)) {
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$name = basename($filename);
$mime_type = finfo_file($finfo, $filename);
(false == $mime_type) && $mime_type = 'application/octet-stream';
$data = file_get_contents($filename) . PHP_EOL;
$header = 'Content-Type: multipart/form-data; boundary=' . $boundary_mixed;
}
if (! empty($postfields)) {
$header = 'Content-Type: application/x-www-form-urlencoded';
$data = is_array($postfields) ? http_build_query($postfields) : $postfields;
}
return array(
'header' => $header,
'content' => $data
);
}
public function setOKStatusCodes($status_codes = array(200))
{
$this->_ok_status_codes = $status_codes;
}
public function getOKStatusCodes()
{
return $this->_ok_status_codes;
}
public function setRedirectStatusCodes($redirect_codes = array(307))
{
$this->_redirect_codes = $redirect_codes;
}
public function setOptions($options)
{
$this->_options = $options;
}
public function stream_notification_callback($notification_code, $severity, $message, $message_code, $bytes_transferred, $bytes_max)
{
$this->_debug_buffer .= $message . PHP_EOL;
switch ($notification_code) {
case STREAM_NOTIFY_RESOLVE:
$this->_debug_buffer .= 'Resolving, ' . $message . PHP_EOL;
break;
case STREAM_NOTIFY_AUTH_REQUIRED:
$this->_debug_buffer .= 'Authorization required...' . PHP_EOL;
break;
case STREAM_NOTIFY_COMPLETED:
$this->_debug_buffer .= sprintf('%s (code %s)', $message, $message_code) . PHP_EOL;
break;
case STREAM_NOTIFY_FAILURE:
$this->_debug_buffer .= sprintf('Failure: %s (%s)', $message, $message_code) . PHP_EOL;
break;
case STREAM_NOTIFY_AUTH_RESULT:
$this->_debug_buffer .= sprintf('Authentication result: %s (%s)', $message, $message_code) . PHP_EOL;
break;
case STREAM_NOTIFY_REDIRECTED:
$this->_debug_buffer .= "Redirected: " . $message . PHP_EOL;
break;
case STREAM_NOTIFY_CONNECT:
$this->_debug_buffer .= "Connected." . PHP_EOL;
break;
case STREAM_NOTIFY_FILE_SIZE_IS:
$this->_debug_buffer .= "The content-size: " . $bytes_max . PHP_EOL;
break;
case STREAM_NOTIFY_MIME_TYPE_IS:
$this->_debug_buffer .= "Response mime-type: " . $message . PHP_EOL;
break;
case STREAM_NOTIFY_PROGRESS:
$this->_progress_callback($this->_is_dwl ? $bytes_max : 0, $this->_is_dwl ? $bytes_transferred : 0, $this->_is_dwl ? 0 : $bytes_max, $this->_is_dwl ? 0 : $bytes_transferred, $this->_callback_info);
break;
}
if ($this->_upl_throttle && $bytes_transferred) {
$elapsed = time() - $this->_started;
$bps = $bytes_transferred / $elapsed;
$diff = $bps - 1024 * $this->_upl_throttle;
($diff > 0) && _sleep($diff * $elapsed);
}
}
public function exec($url, $options = null, $callback_info = null)
{
$this->_debug_buffer = '';
$this->_started = time();
$this->_callback_info = $callback_info;
try {
$options = empty($options) ? $this->_options : $options;
$wrapper = empty($options) ? '' : key($options);
$options['notification'] = array(
$this,
'stream_notification_callback'
);
$context = stream_context_create($options);
stream_context_set_params($context, $options);
$handle = @fopen($url, 'r', false, $context);
if (false !== $handle) {
$meta = stream_get_meta_data($handle);
$response_meta = $this->_parse_response_meta($meta);
}
} catch (MyException $e) {}
if (defined(__NAMESPACE__.'\\CURL_DEBUG') && CURL_DEBUG)
if (defined(__NAMESPACE__.'\\CURL_DEBUG_LOG')) {
$error = error_get_last();
$this->_logfile->writeSeparator();
$this->_logfile->writelnLog(sprintf('[%s] %s', date(DATETIME_FORMAT), sprintf(_esc('Curl not available; running via %s wrapper'), get_class())));
$this->_logfile->writelnLog(sprintf('[%s] URL=%s', date(DATETIME_FORMAT), $url));
$fname = 'Content-Transfer-Encoding: binary';
$exec_options = $options;
if (isset($options[$wrapper]['content']))
$exec_options[$wrapper]['content'] = ($pos = strpos($options[$wrapper]['content'], $fname)) ? substr($options[$wrapper]['content'], 0, $pos + strlen($fname)) : $options[$wrapper]['content'];
if (isset($options['notification']))
unset($exec_options['notification']);
$this->_logfile->writelnLog(sprintf('Options=%s', print_r($exec_options, true)));
! empty($this->_debug_buffer) && $this->_logfile->writelnLog($this->_debug_buffer);
isset($response_meta) && $this->_logfile->writelnLog(sprintf(_esc('[%s] Curl error code=%d, HTTP code=%d'), date(DATETIME_FORMAT), isset($error['code']) ? $error['code'] : 0, $response_meta['status']['code']));
$this->_logfile->writeSeparator();
} else
trigger_error(_esc('This should never happen. CURL_DEBUG is on but CURL_DEBUG_LOG is not defined. That is strange!'), E_USER_WARNING);
if (is_resource($handle) && ! empty($this->_redirect_codes)) {
if (in_array($response_meta['status']['code'], $this->_redirect_codes) && isset($response_meta['redirect'])) {
fclose($handle);
return $this->exec($response_meta['redirect'], $options);
}
}
return $handle;
}
public function post($url, $header = null, $postfields = null, $outfile = null, $infile = null, $method = 'POST', $callback_info = null, $options = null)
{
$this->_is_dwl = empty($infile);
$url_parts = parse_url($url);
$wrapper = $url_parts['scheme'];
('https' == $wrapper) && $wrapper = 'http';
('ftps' == $wrapper) && $wrapper = 'ftp';
if ('sftp' == $wrapper || 'scp' == $wrapper) {
if (extension_loaded('ssh2')) {
$ssh_conn = ssh2_connect($url_parts['host'], $url_parts['port']);
isset($url_parts['user']) && ssh2_auth_password($ssh_conn, $url_parts['user'], isset($url_parts['pass']) ? $url_parts['pass'] : '');
$sftp = ssh2_sftp($ssh_conn);
$url = sprintf("ssh2.%s://$ssh_conn/%s", $url_parts['scheme'], (isset($url_parts['path']) ? $url_parts['path'] : '') . (isset($url_parts['query']) ? $url_parts['query'] : ''));
} else {
throw new MyException(_esc('SSH2 extension not loaded'));
}
}
$default_options = array(
'follow_location' => true,
'max_redirects' => 20,
'protocol_version' => '1.0',
'timeout' => 30,
'ignore_errors' => false,
'user_agent' => get_class()
);
$options = empty($options) ? $default_options : $options;
! isset($options['method']) && $options['method'] = ! empty($infile) ? 'PUT' : (empty($postfields) ? 'GET' : $method);
$header = empty($header) ? array() : $header;
$postfields = empty($postfields) ? array() : $postfields;
$request = $this->_prepare_request($infile, $postfields);
isset($request['header']) && $header = implode(PHP_EOL, $header) . PHP_EOL . $request['header'];
isset($request['content']) && $postfields = $request['content'];
! empty($header) && $options['header'] = $header;
! empty($postfields) && $options['content'] = $postfields;
$options = array(
$wrapper => $options
);
$response = '';
$dltotal = 0;
$dlnow = 0;
try {
$handle = $this->exec($url, $options, $callback_info);
if (is_resource($handle)) {
$meta = stream_get_meta_data($handle);
$response_meta = $this->_parse_response_meta($meta);
$dltotal = $meta['unread_bytes'];
while (! feof($handle)) {
$buffer = fread($handle, 4096);
$response .= $buffer;
$dlnow = strlen($buffer);
}
if (! in_array($response_meta['status']['code'], $this->_ok_status_codes)) {
throw new \Exception($response_meta['status']['message'], $response_meta['status']['code']);
}
} else
return false;
} catch (\Exception $e) {}
return isset($outfile) ? file_put_contents($outfile, $response) : $response;
}
public function curlGetDebugOutput()
{
return $this->_debug_buffer;
}
}
?>