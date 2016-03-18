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
 * @version : 0.2.3-30 $
 * @commit  : 11b68819d76b3ad1fed1c955cefe675ac23d8def $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Fri Mar 18 17:18:30 2016 +0100 $
 * @file    : FtpTargetEditor.php $
 * 
 * @id      : FtpTargetEditor.php | Fri Mar 18 17:18:30 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
class FtpTargetEditor extends AbstractTargetEditor {
private $_ftphost;
private $_ftpuser;
private $_ftpproto;
private $_ftppasv;
private $_dirsep;
private function _getExpertJavaScripts() {
$this->java_scripts[] = 'parent.validateSSLCAInfo=function(sender){if(!sender)return;var b=sender.options[sender.selectedIndex].value,c=document.getElementById("ftp_lib"),t1=document.getElementById("ftp_cainfo_table");if(c){t1.setAttribute("style","display:"+("' .
( CURLPROTO_FTP | CURLPROTO_FTPS ) .
'"== b && "curl"==c.options[c.selectedIndex].value?"inline-block":"none"));jsMyBackup.toggle_passive(document.getElementById("ftppasv"));}};jsMyBackup.validateSSLCAInfo(document.getElementById("ftpproto"));';
if ( isWin() )
$this->java_scripts[] = 'parent.validateSSLonWin=function(sender){if(!sender)return;var b=sender.options[sender.selectedIndex].value,c=document.getElementById("ftp_lib");if(c)if("' .
( CURLPROTO_FTP | CURLPROTO_FTPS ) .
'"==b && c.options[c.selectedIndex].value=="php"){parent.popupError("Warning","The official PHP build does not support out of the box the FTP over SSL functionality (' .
readMoreHere( PHP_MANUAL_URL . 'function.ftp-ssl-connect.php', null, '_blank', true ) .
').<br>However, you may opt for Curl library which supports all kind of stuff, including FTPS :-)");}};';
}
private function _getJavaScripts() {
$this->java_scripts[] = 'parent.toggle_passive=function(sender){if(!sender)return;var c=document.getElementById("ftp_active_port_tbl"),d=document.getElementById("ftp_lib");if(c&&d)c.setAttribute("style","display:"+(sender.checked||"curl"!=d.options[d.selectedIndex].value?"none":"inline-block"));};jsMyBackup.toggle_passive(document.getElementById("ftppasv"));';
$this->_getExpertJavaScripts();
}
protected function initTarget() {
parent::initTarget();
$this->hasInfoBanner = defined( __NAMESPACE__.'\\FILE_EXPLORER' );
$this->_ftphost = $this->settings['ftphost'];
$this->_ftpuser = $this->settings['ftpuser'];
$this->_ftppasv = strToBool( $this->settings['ftppasv'] );
$this->_ftpproto = $this->settings['ftpproto'];
$this->_dirsep = 'u' == $this->settings['ftpdirsep'] ? '/' : '\\';
$this->root = empty( $this->root ) ? $this->_dirsep : $this->root;
$this->root = addTrailingSlash( $this->root, $this->_dirsep );
if ( '/' != $this->_dirsep )
$this->root = str_replace( $this->_dirsep, $this->_dirsep . $this->_dirsep, $this->root );
$this->_getJavaScripts();
}
protected function hideEditorContent() {
return empty( $this->_ftphost ) || empty( $this->_ftpuser ) || empty( $this->root ) || ! $this->enabled ||
'sftp' == $this->_ftpproto;
}
protected function onGenerateEditorContent() {
global $TARGET_NAMES;
try {
$direct_dwl = strToBool( $this->settings['ftp_direct_dwl'] );
$info = getFtpInfo( $this->settings );
if ( $info )
$systype = $info['systype'];
else
$systype = 'unix';
$java_scripts = echoFolder( 
$this->target_name, 
$this->root, 
$this->root, 
$this->ext_filter, 
$this->function_name, 
getFtpDirSeparator( $systype, $this->_dirsep ), 
preg_match( '/^win/i', $systype ) ? null : ( $direct_dwl ? 'ftp://<ftpuser>:<ftppwd>@<ftphost>:<ftpport>/<ftp>' : null ), 
$this->folder_style, 
false, 
null, 
$this->settings ); 
$this->java_scripts = array_merge( $this->java_scripts, $java_scripts );
if ( ( $this->_ftpproto & CURLPROTO_FTPS ) > 0 && isset( $_SESSION['ftp_ssl_cert_info'] ) ) {
$ssl_hint = _esc( 'This certificate guarantes that the data will be sent encrypted.' );
bindSSLInfo( 'ftphost', $_SESSION['ftp_ssl_cert_info'], $this->java_scripts, $ssl_hint );
}
} catch ( MyException $e ) {
echo $e->getMessage();
if ( false === $this->_ftppasv && CURLE_OPERATION_TIMEDOUT == $e->getCode() ) {
$help_3 = sprintf( 
_esc( 
'You are using an active FTP connection (the <i>Passive mode</i> option is off). If this was not done by porpose then read my final thoughts at bottom. Otherwise please continue reading. Although I`m thousands km away you are only one click away from my help :-)<br>You may get timed-out also when your webserver`s firewall is not corectly configured for this connection type (active FTP connection). The way that the webserver`s (%s) firewall should be configured is out of this scope. Anyway %s as always ;-)<br>If you still have not found anything helpfull then you might want to %s and/or %s.<br>' ), 
PHP_OS, 
$this->_ftppasv ? 'passive' : 'active', 
getAnchorE( 'GIYF', lmgtfy( '%s ftp firewall' ) ), 
getAnchorE( 'read this', 'http://www.mdjnet.dk/ftp.html' ), 
getAnchorE( 'this', 'http://slacksite.com/other/ftp.html' ) );
$help_3 .= ( defined( __NAMESPACE__.'\\CURL_DEBUG' ) && CURL_DEBUG ? sprintf( 
_esc( 
'It seems that the Curl debug option is ON. Good!!! So go and %s, it might give you useful information.' ), 
getAnchorE( 'check the Curl debug log', getTabLink( $TARGET_NAMES[APP_LOGS] ) ) ) : sprintf( 
_esc( 
'I see that %s. You might want to set the Curl debug ON then try again. If the problem still persist then you can read the Curl debug log and trace the problem. Sometimes it helps.' ), 
getAnchorE( 'your Curl debug option is off', getTabLink( $TARGET_NAMES[APP_LOGS] ) ) ) ) .
_esc( 
'<p style=\\\'font-weight:bold\\\'>Final thoughts</p>If you haven`t choose the active type FTP connection by purpose then you might want to try the passive one. It will certainly work. Why? <a href=\\\'#\\\' onclick=\\\'document.getElementById(&quot;passive_ftp_help&quot;).click();\\\'>Read here</a>.' );
echo '<a class="help" onclick=' . getHelpCall( "'$help_3'" ) . '>' . _esc( 'Read this' ) . '</a>';
}
}
}
protected function getEditorTemplate() {
$help_1 = "'" .
_esc( 
'Enter the host name or the IP address of the FTP server (eg. my.domain.com). Do not prepend the protocol name (ie. ftp://) or anything else.' ) .
"'";
$help_2 = "'" . sprintf( 
_esc( 
'The FTP supports 2 connection modes: active and passive (PASV). No matter what mode you choose you will communicate with the FTP server via two different channels/ports: one for data and one for commands/control. Traditionally these are port 21 for commands and 20 (depending on mode active/passive) for data.<ul><li>In active mode we connect the FTP server on port %s by telling the FTP server to connect back to us on a random port N>1023 (which means you have such ports opened in the firewall. The most users don`t and that for a good reason.</li><li>In passive mode we connect the FTP server on port %s by telling it to open for us an extra port (N>1023). We connect both these ports when sending/receiving data to/from FTP server. The difference from active is that we don`t need to open any port on our system, is the FTP server the one that have to. Of course, the FTP server should have its firewall configured such that it will allow this kind of communication.</li></ul><b>When to use passive mode?</b><ul><li>When you are behind a router/firewall that has the most port closed then the passive mode is the solution.</li></ul>Do you want to grow faster and smarter? %s :-). Btw: for Windows 2003 Server you might setup your firewll like %s.' ), 
$this->settings['ftpport'], 
$this->settings['ftpport'], 
readMoreHereE( 'http://slacksite.com/other/ftp.html' ), 
getAnchorE( 'this', 'http://support.microsoft.com/kb/555022' ) ) . "'";
$help_3 = "'" . _esc( 'Keep only the last n-days backups on FTP.<br>Leave it empty to disable this option' ) .
"'";
$help_4 = "'" .
_esc( 
'You are trying to connect the FTP server in active mode. By doing this the FTP transfer will be made actively via a specified address:port you give the FTP server. Usually this should be your external interface address and a port that is open and that your server can listen to. If you are behind a router/firewall you can use this option by specifying the external IP address and a port (eg. 30000) that is open at the router/firewall level and which forwards all request toward your webserver. You may use the symbol `-` to let the system use your system`s default IP address. Of course, on easier way would be to use the passive mode.' ) .
'<br>' . readMoreHereE( 'http://curl.haxx.se/libcurl/c/CURLOPT_FTPPORT.html' ) . "'";
$is_curl_ftp = 'curl' == $this->settings['ftp_lib'];
require_once $this->getTemplatePath( 'ftp.php' );
}
protected function getExpertEditorTemplate() {
$help_1 = "'" .
_esc( 
'Specify the directory listing type used by the remote FTP server.<br>Microsoft IIS FTP Server can be configured to list both style Windows/Unix.<br>This file list does not depend on that. Default is Unix-like style.' ) .
'<br>' . readMoreHereE( 'http://support.microsoft.com/kb/256312' ) . "'";
$help_2 = "'" . sprintf( 
_esc( 
'<b>What is what</b><ol><li>FTP - the %s that has been around since 1970s. It usually runs over TCP port 21</li><li>%s - an extension to the FTP that adds support for the TLS and SSL. FTPS should not be confused with the SSH File Transfer Protocol (SFTP).</li><li>%s - another, completely different file transfer protocol that has nothing to do with FTP. SFTP runs over an SSH session, usually on TCP port 22. It has been around since late 1990s.</li><li>%s - is a means of securely transferring computer files between a local host and a remote host or between two remote hosts. It is based on the SSH protocol</li></ol>' ), 
getAnchorE( 'plain old FTP protocol', 'http://en.wikipedia.org/wiki/File_Transfer_Protocol' ), 
getAnchorE( 'FTPS=FTP+SSL', 'http://en.wikipedia.org/wiki/FTPS' ), 
getAnchorE( 'SFTP=SSH+FTP', 'http://en.wikipedia.org/wiki/File_Transfer_Protocol#SFTP' ), 
getAnchorE( 'SCP (Secure Copy Protocol)', 'http://en.wikipedia.org/wiki/Secure_copy' ) ) . "'";
$help_3 = "'" . _esc( 'Not yet enabled' ) . "'";
$help_4 = "'" .
_esc( 
'FTP is an Internet protocol well supported by all web browser and thus<br>they allows us to download a file right from your browser. It`t like<br>typing the file URL in address bar.<br>This feature is a plus since the transfer chain doesn`t imply to download<br>the file from the FTP to this webserver then from the webserver to you.<br>You download the file directly from the FTP server locally to your system.<br>This method requires also to send (within the URL address) the user name<br>and the password. Usually if you don`t access this page over a SSL<br>connection it might represent a security risk. Otherwise you are fine :-)<br>' );
$help_4 .= sprintf( 
_esc( 
'So by <i>direct download</i> we mean that you get a direct link to the FTP file<br>so the webserver (where %s is installed) has nothing to do<br>with your download. It is just a matter between you and the FTP server.' ), 
WPMYBACKUP ) . "'";
$help_5 = "'" .
_esc( 
'There are two libraries that can be used:<ul><li>PHP built-in FTP : it provides basic FTP functionality. Does not have verbose/debug functionality like Curl library. Does have SLL support.</li><li>Curl library : this is a versatile file transfer library which supports a large spectrum of file transfer protocols (including FTP, FTPS, SFTP, SCP). Does include verbose/debug functionalities and many, many other functionalities. So if you are going to use SFTP/SCP you MUST use this library. Regarding the FTP(S) you may opt for any of the two.</li></ul><b>Curl library</b> is my favorite choice between these two.' ) .
"'";
$help_6 = sprintf( 
_esc( 
'Specify the full path of the file holding one or more CA root certificates to verify the peer with (a .pem file). So your private certificate and the server (you are going to connect) certificate is supposed to be signed by an Certificate Authority (CA) that exists in that .pem file. If it`s happening that your CA certificate is not in the .pem format (but instead is in - for instance - .crt format) you can %s. Remember: independent of their format (.pem,.crt,whatever) they all will contain the same info : a list of one/more CA root certificates that are used to validate the server SSL certificate you are going to use.<br>Btw: you can enter here either the .pem file or the folder name where I will search for a &amp;lt;any-name-is-good&amp;gt;.pem file. In that case make sure the %s.' ), 
getAnchorE( 'easily convert the .crt to .pem format', lmgtfy( '.crt to .pem' ) ), 
getAnchorE( 
'certificate directory is prepared using the openssl c_rehash utility', 
'http://curl.haxx.se/libcurl/c/CURLOPT_CAPATH.html' ) );
$help_6 .= sprintf( 
_esc( 
'<p style=\\\'font-weight:bold\\\'>When to use this option?</p>Normally the certificates are issued by some known trustworthy CA roots (like Verisign,Thawte,Digicert,etc). We trust in the identity of who is written on the SSL certificates that were issued by these CA because we know they don`t fool around. Sometimes you don`t have/use this kind of expensive certificates, instead you issue and sign yourselves a SSL certificate and you trust its identity (it`s issued by you and if you don`t trust yourself then who will?).<p style=\\\'font-weight:bold\\\'>Example</p>Your company`s IT department has issued a %s. Of course, if you`ll try to navigate a website where the SSL certificate is installed you`ll get warned that %s. But we don`t care about this issue in this example. It is a certificate issued by our IT manager so we trust him.' ), 
getAnchorE( 'self-signed certificate', 'https://www.madboa.com/geek/openssl' ), 
getAnchorE( 
'The site`s security certificate is not trusted!', 
'https://support.google.com/chrome/answer/98884?hl=en' ) );
$help_6 .= _esc( 
' If you want to make sure you will connect securely that server only then (1) get the CA root certificate and enter the path to it in <i>CA PEM path/file</i> and (2) make sure you check the option <i>Check peers SSL identity</i> as well.<p style=\\\'font-weight:bold\\\'>Troubleshooting</p>If you don`t check the later (2) you are going to connect that server anyway, it doesn`t matter whos certificate is installed there. If someone is playing with your DNS table you might end up connecting the bad guys server instead yours. That`s why we use SSL certificates in the first place, to make sure that the server we are talking to is the one that it pretends to be. If you don`t have the CA root certificate (1) but you check the later option (2) then, because your certificate was issued by a non-trustworthy Certificate Authority, you won`t be able to establish a SSL connection because `Peer certificate cannot be authenticated with known CA certificates`.' ) .
'<br>' . readMoreHereE( 'http://curl.haxx.se/libcurl/c/CURLOPT_CAINFO.html' );
$help_6 = "'$help_6'";
$help_9 = "'" .
_esc( 
'Backup is important and the fact that we can deliver it to a remote storage container is really cool. But first of all we should make sure that what we transfer over Internet is done securely. And here comes the SSL in play.<br><p style=\\\'font-weight:bold\\\'>How does SSL works</p>SSL uses three kinds of cryptographic techniques: public-private key, symetric key, digial signature.<br>In the public-private key scenario encryption|decryption si performed using a pair of public-private keys. The server holds the private key and sends the public key to the client via its SSL certificate:' );
$help_9 .= '<ol><li>' .
_esc( 
'the client requests content from the server via SSL</li><li>the server responds with a digital certificate which includes its public key (the client is going to use this for encryption/decryption)</li><li>the client checks to see if the certificate it`s all right (hasn`t expired, it`s signed by a trustworthy Certificate Authority (CA), the server domain name matches the one presented in certificate)</li><li>if this preliminary check is done and OK then we have a handshake and we start talking SSL' ) .
'</li></ol>';
$help_9 .= sprintf( 
_esc( 
'As you can see we need to check if the certificate was issued by a trustworthy autority. We do this by specifying the file/path where this application finds a CA root certificate bundle. Your server perhaps contains such a file (the most web server do, for instance on Linux Apache it is usually stored at ../apache2/conf/ssl.key/). Anyway, just to make sure that you have this file  %s is shipped by default with such a bundle and the <i>CA PEM path/file</i> parameter is pointing already to that .pem file:<blockquote>%s</blockquote>Make sure you read all the help associated with the above options. It helps! You may want to read also %s.' ), 
WPMYBACKUP, 
getAnchorE( SSL_CACERT_FILE, 'http://curl.haxx.se/docs/caextract.html' ), 
getAnchorE( 
'implementing and using SSL to sercure HTTP traffic', 
'http://www.tldp.org/HOWTO/Apache-WebDAV-LDAP-HOWTO/ssl.html' ) ) . "'";
$help_10 = "'" . sprintf( 
_esc( 
'This is a quote from %s:<blockquote><i>When negotiating a TLS or SSL connection, the server sends a certificate indicating its identity. Curl verifies whether the certificate is authentic, i.e. that you can trust that the server is who the certificate says it is. This trust is based on a chain of digital signatures, rooted in certification authority (CA) certificates you supply </i>(see CA PEM path/file parameter). <i>When this option is checked, and the verification fails to prove that the certificate is authentic, the connection fails. When the option is unchecked, the peer certificate verification succeeds regardless.</i></blockquote><div class=\\\'hintbox {$this->container_shape}\\\'><b>WARNING</b>: disabling verification of the certificate allows bad guys to %s the communication without you knowing it.</div>' ), 
getAnchorE( 'libcurl manual', 'http://curl.haxx.se/libcurl/c/CURLOPT_SSL_VERIFYPEER.html' ), 
getAnchorE( 'man-in-the-middle (MitM)', 'http://en.wikipedia.org/wiki/Man-in-the-middle_attack' ) ) . "'";
$help_11 = "'" .
_esc( 
'Select the version of SSL version the server uses. This option is useful if by some reason the SSL library cannot detect automatically the peer SSL version and the only fix is to tell by yourself.<br>The most users should choose <i>Let me choose</i>.' ) .
"'";
$selected = 'selected';
$ftp_ssl_ver = $this->settings['ftp_ssl_ver'];
require_once $this->getTemplatePath( 'ftp-expert.php' );
}
}
?>