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
 * @file    : BackupSettingsEditor.php $
 * 
 * @id      : BackupSettingsEditor.php | Wed Sep 16 11:33:37 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;
class BackupSettingsEditor extends AbstractTargetEditor {
protected function getEditorTemplate() {
$help_1 = "'" . sprintf ( _esc ( 'Specify the full path of the file holding one or more CA root certificates to verify the peer with (a .pem file). So your private certificate and the server (you are going to connect) certificate is supposed to be signed by an Certificate Authority (CA) that exists in that .pem file. If it`s happening that your CA certificate is not in the .pem format (but instead is in - for instance - .crt format) you can %s. Remember: independent of their format (.pem,.crt,whatever) they all will contain the same info : a list of one/more CA root certificates that are used to validate the server SSL certificate you are going to use.<br>Btw: you can enter here either the .pem file or the folder name where I will search for a &amp;lt;any-name-is-good&amp;gt;.pem file. In that case make sure the %s.' ), getAnchorE(_esc ( 'easily convert the .crt to .pem format' ), 'http://lmgtfy.com/?q=.crt+to+.pem'), getAnchorE(_esc ( 'certificate directory is prepared using the openssl c_rehash utility' ), 'http://curl.haxx.se/libcurl/c/CURLOPT_CAPATH.html'));
$help_1 .= '<p style=\\\'font-weight:bold\\\'>' . sprintf ( _esc ( 'When to use this option?</p>Normally the certificates are issued by some known trustworthy CA roots (like Verisign,Thawte,Digicert,etc). We trust in the identity of who is written on the SSL certificates that were issued by these CA because we know they don`t fool around. Sometimes you don`t have/use this kind of expensive certificates, instead you issue and sign yourselves a SSL certificate and you trust its identity (it`s issued by you and if you don`t trust yourself then who will?).%sYour company`s IT department has issued a %s. Of course, if you`ll try to navigate a website where the SSL certificate is installed you`ll get warned that %s. But we don`t care about this issue in this example. It is a certificate issued by our IT manager so we trust him.' ), '<p style=\\\'font-weight:bold\\\'>' . _esc ( 'Example' ) . '</p>', getAnchorE(_esc ( 'self-signed certificate' ), 'https://www.madboa.com/geek/openssl'), getAnchorE(_esc ( 'The site`s security certificate is not trusted!' ), 'https://support.google.com/chrome/answer/98884?hl=en') );
$help_1 .= sprintf ( _esc ( ' If you want to make sure you will connect securely that server only then (1) get the CA root certificate and enter the path to it in <i>CA PEM path/file</i> and (2) make sure you check the option <i>Check peers SSL identity</i> as well.%sIf you don`t check the later (2) you are going to connect that server anyway, it doesn`t matter whos certificate is installed there. If someone is playing with your DNS table you might end up connecting the bad guys server instead yours. That`s why we use SSL certificates in the first place, to make sure that the server we are talking to is the one that it pretends to be. If you don`t have the CA root certificate (1) but you check the later option (2) then, because your certificate was issued by a non-trustworthy Certificate Authority, you won`t be able to establish a SSL connection because `Peer certificate cannot be authenticated with known CA certificates`.<br>You may %s.' ), '<p style=\\\'font-weight:bold\\\'>' . _esc ( 'Troubleshooting' ) . '</p>', getAnchorE(_esc ( 'read more about this option here' ), 'http://curl.haxx.se/libcurl/c/CURLOPT_CAINFO.html') ) . "'";
$help_2 = "'" . _esc ( 'Not yet available' ) . "'";
$help_3 = "'" . _esc ( 'This option defines the maximum time the request is allowed to take. Ususally any decent web server (from Google, Dropbox, etc) should respond in maximum 1-2 seconds. In my experience they respond even faster, ultimately it comes to money, right? Anyway, for those situations when for unknown reasons the servers you are working with do not respond in a timely fashion you may increase this request timeout option accordingly.<Br>By default it is set to 30 seconds (which is huge!) but you know, it is here just in case ;-)' ) . "'";
$help_4 = "'" . _esc ( 'If you are using Curl library and you connect the Internet through a cache proxy server then specify the proxy server IP/name and its respective port (like 3128).' ) . "'";
$help_5 = "'" . _esc ( 'If you have specified a proxy server above and if the server allows only authenticated users to connect it then please enter the user/password for proxy. Your system administrator should help you with these.' ) . "'";
$help_6 = "'" . _esc ( 'With Curl library there are only two proxy authentication methods supported: the Basic and Windows NTLM.' ) . "'";
$help_7 = "'" . _esc ( 'If you choose to connect the Internet via proxy then specify the proxy type you connect. If in doubt then choose HTTP proxy. For more info ask your system administrator.' ) . "'";
$help_8 = "'" . sprintf ( _esc ( 'This is a quote from %sWhen negotiating a TLS or SSL connection, the server sends a certificate indicating its identity. Curl verifies whether the certificate is authentic, i.e. that you can trust that the server is who the certificate says it is. This trust is based on a chain of digital signatures, rooted in certification authority (CA) certificates you supply </i>(see CA PEM path/file parameter). <i>When this option is checked, and the verification fails to prove that the certificate is authentic, the connection fails. When the option is unchecked, the peer certificate verification succeeds regardless.%s: disabling verification of the certificate allows bad guys to %s the communication without you knowing it.</div>' ),' '.getAnchorE(_esc ( 'libcurl manual' ), 'http://curl.haxx.se/libcurl/c/CURLOPT_SSL_VERIFYPEER.html') .':<blockquote><i>', '</i></blockquote><div class=\\\'hintbox ' . $this->container_shape . '\\\'><b>' . _esc ( 'WARNING' ) . '</b>', getAnchorE(_esc ( 'man-in-the-middle (MitM)' ), 'http://en.wikipedia.org/wiki/Man-in-the-middle_attack') ) . "'";
$help_9 = "'" . _esc ( 'Select the version of SSL version the server uses. This option is useful if by some reason the SSL library cannot detect automatically the peer SSL version and the only fix is to tell by yourself.<br>The most users should choose <i>Let me choose</i>.' ) . "'";
$help_10 = "'" . _esc ( 'If you set this option then, when using SSL, it is checked (1) the existence of a common name on the SSL certificate and also (2) verify that it matches the hostname provided. The most users should set this option ON.' ) . "'";
$help_11 = "'" . _esc ( 'The name of the outgoing network interface to use. This can be an interface name, an IP address or a host name. By default the system`s default interface is used.' ) . "'";
$http_proxy_pwd = $this->settings ['http_proxy_pwd'];
$selected = 'selected';
require_once $this->getTemplatePath ( 'backup-settings.php' );
}
}
?>
