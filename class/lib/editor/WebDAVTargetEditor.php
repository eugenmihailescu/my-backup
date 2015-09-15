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
 * @file    : WebDAVTargetEditor.php $
 * 
 * @id      : WebDAVTargetEditor.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;
class WebDAVTargetEditor extends AbstractTargetEditor {
private $_webdavhost;
private $_webdavuser;
private $_webdavpwd;
private $_direct_dwl;
protected function initTarget() {
parent::initTarget ();
$this->hasInfoBanner = defined('FILE_EXPLORER');
$this->_webdavhost = $this->settings ['webdavhost'];
$this->_webdavuser = $this->settings ['webdavuser'];
$this->_webdavpwd = $this->settings ['webdavpwd'];
$this->_direct_dwl = strToBool ( $this->settings ['webdav_direct_dwl'] );
$this->root = addTrailingSlash ( $this->root, '/' );
$this->infoBannerRoot = $this->_webdavhost;
}
protected function hideEditorContent() {
return empty ( $this->_webdavhost ) || empty ( $this->_webdavuser ) || empty ( $this->root ) || ! $this->enabled;
}
protected function onGenerateEditorContent() {
try {
$storage = new WebDAVWebStorage ( $this->target_item->targetSettings );
$java_scripts = echoFolder ( $this->target_name, $this->root, $this->root, $this->ext_filter, $this->function_name, '/', $this->_direct_dwl ? 'http://<webdavuser>:<webdavpwd>@<webdavhost>/<webdav>' : null, $this->folder_style, false, $storage, $this->settings ); 
$this->java_scripts = array_merge ( $this->java_scripts, $java_scripts );
if (! isset ( $_SESSION [$this->target_name . '_ssl_cert_info'] )) {
$storage->getAccountInfo ( false );
$ssl_info = $storage->getSSLInfo ();
if (! empty ( $ssl_info ))
add_session_var ( $this->target_name . '_ssl_cert_info', $ssl_info );
}
if (isset ( $_SESSION [$this->target_name . '_ssl_cert_info'] )) {
$ssl_hint = 'This certificate guarantes that the data will be sent encrypted.';
bindSSLInfo ( 'webdavhost', $_SESSION [$this->target_name . '_ssl_cert_info'], $this->java_scripts, $ssl_hint );
}
} catch ( MyException $e ) {
}
}
protected function getEditorTemplate() {
$help_1 = "'" . _esc ( 'Keep only the last n-days backups on WebDAV' ) . "'";
$help_2 = "'" . _esc ( 'The HTTP address of the WebDAV root (eg. http://webdav.local/share)' ) . "'";
$help_3 = "'" . sprintf ( _esc ( 'Specify the user name and password to use for server authentication.<p style=\\\'font-weight:bold\\\'>Quote from %s</p><ul><li>When using Kerberos V5 with a Windows based server you should include the Windows domain name in the user name, in order for the server to succesfully obtain a Kerberos Ticket. If you don`t then the initial authentication handshake may fail.</li><li>When using NTLM, the user name can be specified simply as the user name, without the domain, if there is a single domain and forest in your setup for example. To specify the domain name use either Down-Level Logon Name or UPN (User Principal Name) formats. For example, EXAMPLE\\\\user and user@example.com respectively.</li><li>If you use a Windows SSPI-enabled curl binary and perform Kerberos V5, Negotiate, NTLM or Digest authentication then you can tell curl to select the user name and password from your environment, leave the user/password empty.</li>' ), getAnchorE ( 'Curl manpage', 'http://curl.haxx.se/docs/manpage.html' ) ) . "'";
require_once $this->getTemplatePath ( 'webdav.php' );
}
protected function getExpertEditorTemplate() {
$help_1 = "'" . _esc ( 'Not yet enabled' ) . "'";
$help_2 = "'" . _esc ( 'Since the WebDAV is just a superior layer that works over HTTP it will<br>allow us to download a file right from your browser. It`t like typing the<br>file URL in address bar.<br>This feature is a plus since the transfer chain doesn`t imply to download<br>the file from the WebDAV to this webserver then from the webserver to you.<br>This method requires also to send (within the URL address) the user name<br>and the password. Usually if you don`t access this page over a SSL<br>connection it might represent a security risk. Otherwise you are fine :-)<br>' );
$help_2 .= sprintf ( _esc ( 'So by <i>direct download</i> we mean that you get a direct link to the WebDAV file<br>so the webserver (where %s is installed) has nothing to do<br>with your download. It is just a matter between you and the WebDAV server.' ), WPMYBACKUP ) . "'";
$help_3 = "'" . _esc ( 'Specify the full path of the file holding one or more certificates to verify the peer with.' ) . ' ' . readMoreHereE ( 'http://curl.haxx.se/libcurl/c/CURLOPT_CAINFO.html' ) . "'";
$help_4 = "'" . _esc ( 'Choose one of the following authentication methods to try when connecting the WebDAV HTTP server.<ul>' );
$help_4 .= _esc ( '<li><b>Any available</b> - an alias for Basic|Digest|GSS-API</li>' );
$help_4 .= _esc ( '<li><b>Any safe</b> - an alias for Digest|GSS-API|NTLM</li>' );
$help_4 .= _esc ( '<li><b>None</b> - it should be clear like the crystal, isn`t it?</li>' );
$help_4 .= _esc ( '<li><b>Basic</b> - HTTP Basic authentication. This is the default choice, and the only method that is in wide-spread use and supported virtually everywhere. This sends the user name and password over the network <span style=\\\'border-bottom:1px dashed red;\\\'>in plain text, easily captured by others</span>.</li>' );
$help_4 .= sprintf ( _esc ( '<li><b>Digest</b> - HTTP Digest authentication. Digest authentication is defined in %s and is a more secure way to do authentication over public networks than the regular old-fashioned Basic method.</li>' ), getAnchorE ( 'RFC 2617', 'http://www.ietf.org/rfc/rfc2617.txt' ) );
$help_4 .= sprintf ( _esc ( '<li><b>GSS-API</b> - HTTP Negotiate (SPNEGO) authentication. Negotiate authentication is defined in %s and is the most secure way to perform authentication over HTTP. %s.</li>' ), getAnchorE ( 'RFC 4559', 'http://www.ietf.org/rfc/rfc4559.txt' ), readMoreHereE ( 'http://curl.haxx.se/libcurl/c/CURLOPT_HTTPAUTH.html' ) );
$help_4 .= _esc ( '<li><b>NTLM</b> - HTTP NTLM authentication. A proprietary protocol invented and used by Microsoft. It uses a challenge-response and hash concept similar to Digest, to prevent the password from being eavesdropped.' ) . ' ' . readMoreHereE ( 'http://curl.haxx.se/libcurl/c/CURLOPT_HTTPAUTH.html' ) . '</li>';
$help_4 .= _esc ( '</ol>When going through proxy only the Basic and NTLM authetication methods are currently supported.' ) . "'";
$http_auths = array (
CURLAUTH_ANY => 'Any available',
CURLAUTH_ANYSAFE => 'Any safe',
0 => 'None',
CURLAUTH_BASIC => 'Basic',
CURLAUTH_DIGEST => 'Digest',
CURLAUTH_GSSNEGOTIATE => 'GSS-API', 
CURLAUTH_NTLM => 'NTLM' 
);
$auth_options = '';
foreach ( $http_auths as $auth_id => $auth_type )
$auth_options .= '<option value="' . $auth_id . '"' . ($auth_id == $this->settings ['webdav_authtype'] ? ' selected' : '') . '>' . $auth_type . '</option>';
require_once $this->getTemplatePath ( 'webdav-expert.php' );
}
}
?>
