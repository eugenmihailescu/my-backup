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
 * @version : 0.2.3-37 $
 * @commit  : 56326dc3eb5ad16989c976ec36817cab63bc12e7 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Wed Dec 7 18:54:23 2016 +0100 $
 * @file    : CurlOptsCodes.php $
 * 
 * @id      : CurlOptsCodes.php | Wed Dec 7 18:54:23 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

class CurlOptsCodes{
static $_CURLOPT_CODES_=array(
1 => 'CURLOPT_SAFE_UPLOAD',
3 => 'CURLOPT_PORT',
13 => 'CURLOPT_TIMEOUT',
14 => 'CURLOPT_INFILESIZE',
19 => 'CURLOPT_LOW_SPEED_LIMIT',
20 => 'CURLOPT_LOW_SPEED_TIME',
21 => 'CURLOPT_RESUME_FROM',
27 => 'CURLOPT_CRLF',
32 => 'CURLOPT_SSLVERSION',
33 => 'CURLOPT_TIMECONDITION',
34 => 'CURLOPT_TIMEVALUE',
41 => 'CURLOPT_VERBOSE',
42 => 'CURLOPT_HEADER',
43 => 'CURLOPT_NOPROGRESS',
44 => 'CURLOPT_NOBODY',
45 => 'CURLOPT_FAILONERROR',
46 => 'CURLOPT_UPLOAD',
47 => 'CURLOPT_POST',
48 => 'CURLOPT_DIRLISTONLY',
48 => 'CURLOPT_FTPLISTONLY',
50 => 'CURLOPT_APPEND',
50 => 'CURLOPT_FTPAPPEND',
51 => 'CURLOPT_NETRC',
52 => 'CURLOPT_FOLLOWLOCATION',
53 => 'CURLOPT_TRANSFERTEXT',
54 => 'CURLOPT_PUT',
58 => 'CURLOPT_AUTOREFERER',
59 => 'CURLOPT_PROXYPORT',
61 => 'CURLOPT_HTTPPROXYTUNNEL',
64 => 'CURLOPT_SSL_VERIFYPEER',
68 => 'CURLOPT_MAXREDIRS',
69 => 'CURLOPT_FILETIME',
71 => 'CURLOPT_MAXCONNECTS',
72 => 'CURLOPT_CLOSEPOLICY',
74 => 'CURLOPT_FRESH_CONNECT',
75 => 'CURLOPT_FORBID_REUSE',
78 => 'CURLOPT_CONNECTTIMEOUT',
80 => 'CURLOPT_HTTPGET',
81 => 'CURLOPT_SSL_VERIFYHOST',
84 => 'CURLOPT_HTTP_VERSION',
85 => 'CURLOPT_FTP_USE_EPSV',
90 => 'CURLOPT_SSLENGINE_DEFAULT',
91 => 'CURLOPT_DNS_USE_GLOBAL_CACHE',
92 => 'CURLOPT_DNS_CACHE_TIMEOUT',
96 => 'CURLOPT_COOKIESESSION',
98 => 'CURLOPT_BUFFERSIZE',
99 => 'CURLOPT_NOSIGNAL',
101 => 'CURLOPT_PROXYTYPE',
105 => 'CURLOPT_UNRESTRICTED_AUTH',
106 => 'CURLOPT_FTP_USE_EPRT',
107 => 'CURLOPT_HTTPAUTH',
110 => 'CURLOPT_FTP_CREATE_MISSING_DIRS',
111 => 'CURLOPT_PROXYAUTH',
112 => 'CURLOPT_FTP_RESPONSE_TIMEOUT',
113 => 'CURLOPT_IPRESOLVE',
114 => 'CURLOPT_MAXFILESIZE',
119 => 'CURLOPT_USE_SSL',
119 => 'CURLOPT_FTP_SSL',
121 => 'CURLOPT_TCP_NODELAY',
129 => 'CURLOPT_FTPSSLAUTH',
136 => 'CURLOPT_IGNORE_CONTENT_LENGTH',
137 => 'CURLOPT_FTP_SKIP_PASV_IP',
138 => 'CURLOPT_FTP_FILEMETHOD',
139 => 'CURLOPT_LOCALPORT',
140 => 'CURLOPT_LOCALPORTRANGE',
141 => 'CURLOPT_CONNECT_ONLY',
150 => 'CURLOPT_SSL_SESSIONID_CACHE',
151 => 'CURLOPT_SSH_AUTH_TYPES',
154 => 'CURLOPT_FTP_SSL_CCC',
155 => 'CURLOPT_TIMEOUT_MS',
156 => 'CURLOPT_CONNECTTIMEOUT_MS',
157 => 'CURLOPT_HTTP_TRANSFER_DECODING',
158 => 'CURLOPT_HTTP_CONTENT_DECODING',
159 => 'CURLOPT_NEW_FILE_PERMS',
160 => 'CURLOPT_NEW_DIRECTORY_PERMS',
161 => 'CURLOPT_POSTREDIR',
166 => 'CURLOPT_PROXY_TRANSFER_MODE',
171 => 'CURLOPT_ADDRESS_SCOPE',
172 => 'CURLOPT_CERTINFO',
178 => 'CURLOPT_TFTP_BLKSIZE',
180 => 'CURLOPT_SOCKS5_GSSAPI_NEC',
181 => 'CURLOPT_PROTOCOLS',
182 => 'CURLOPT_REDIR_PROTOCOLS',
188 => 'CURLOPT_FTP_USE_PRET',
189 => 'CURLOPT_RTSP_REQUEST',
193 => 'CURLOPT_RTSP_CLIENT_CSEQ',
194 => 'CURLOPT_RTSP_SERVER_CSEQ',
197 => 'CURLOPT_WILDCARDMATCH',
207 => 'CURLOPT_TRANSFER_ENCODING',
210 => 'CURLOPT_GSSAPI_DELEGATION',
212 => 'CURLOPT_ACCEPTTIMEOUT_MS',
213 => 'CURLOPT_TCP_KEEPALIVE',
214 => 'CURLOPT_TCP_KEEPIDLE',
215 => 'CURLOPT_TCP_KEEPINTVL',
216 => 'CURLOPT_SSL_OPTIONS',
10001 => 'CURLOPT_FILE',
10002 => 'CURLOPT_URL',
10004 => 'CURLOPT_PROXY',
10005 => 'CURLOPT_USERPWD',
10006 => 'CURLOPT_PROXYUSERPWD',
10007 => 'CURLOPT_RANGE',
10009 => 'CURLOPT_INFILE',
10009 => 'CURLOPT_READDATA',
10015 => 'CURLOPT_POSTFIELDS',
10016 => 'CURLOPT_REFERER',
10017 => 'CURLOPT_FTPPORT',
10018 => 'CURLOPT_USERAGENT',
10022 => 'CURLOPT_COOKIE',
10023 => 'CURLOPT_HTTPHEADER',
10025 => 'CURLOPT_SSLCERT',
10026 => 'CURLOPT_SSLKEYPASSWD',
10026 => 'CURLOPT_KEYPASSWD',
10026 => 'CURLOPT_SSLCERTPASSWD',
10028 => 'CURLOPT_QUOTE',
10029 => 'CURLOPT_WRITEHEADER',
10031 => 'CURLOPT_COOKIEFILE',
10036 => 'CURLOPT_CUSTOMREQUEST',
10037 => 'CURLOPT_STDERR',
10039 => 'CURLOPT_POSTQUOTE',
10062 => 'CURLOPT_INTERFACE',
10063 => 'CURLOPT_KRB4LEVEL',
10063 => 'CURLOPT_KRBLEVEL',
10065 => 'CURLOPT_CAINFO',
10070 => 'CURLOPT_TELNETOPTIONS',
10076 => 'CURLOPT_RANDOM_FILE',
10077 => 'CURLOPT_EGDSOCKET',
10082 => 'CURLOPT_COOKIEJAR',
10083 => 'CURLOPT_SSL_CIPHER_LIST',
10086 => 'CURLOPT_SSLCERTTYPE',
10087 => 'CURLOPT_SSLKEY',
10088 => 'CURLOPT_SSLKEYTYPE',
10089 => 'CURLOPT_SSLENGINE',
10093 => 'CURLOPT_PREQUOTE',
10097 => 'CURLOPT_CAPATH',
10100 => 'CURLOPT_SHARE',
10102 => 'CURLOPT_ACCEPT_ENCODING',
10102 => 'CURLOPT_ENCODING',
10103 => 'CURLOPT_PRIVATE',
10104 => 'CURLOPT_HTTP200ALIASES',
10118 => 'CURLOPT_NETRC_FILE',
10134 => 'CURLOPT_FTP_ACCOUNT',
10135 => 'CURLOPT_COOKIELIST',
10147 => 'CURLOPT_FTP_ALTERNATIVE_TO_USER',
10152 => 'CURLOPT_SSH_PUBLIC_KEYFILE',
10153 => 'CURLOPT_SSH_PRIVATE_KEYFILE',
10162 => 'CURLOPT_SSH_HOST_PUBLIC_KEY_MD5',
10169 => 'CURLOPT_CRLFILE',
10170 => 'CURLOPT_ISSUERCERT',
10173 => 'CURLOPT_USERNAME',
10174 => 'CURLOPT_PASSWORD',
10175 => 'CURLOPT_PROXYUSERNAME',
10176 => 'CURLOPT_PROXYPASSWORD',
10177 => 'CURLOPT_NOPROXY',
10179 => 'CURLOPT_SOCKS5_GSSAPI_SERVICE',
10183 => 'CURLOPT_SSH_KNOWNHOSTS',
10186 => 'CURLOPT_MAIL_FROM',
10187 => 'CURLOPT_MAIL_RCPT',
10190 => 'CURLOPT_RTSP_SESSION_ID',
10191 => 'CURLOPT_RTSP_STREAM_URI',
10192 => 'CURLOPT_RTSP_TRANSPORT',
10203 => 'CURLOPT_RESOLVE',
10204 => 'CURLOPT_TLSAUTH_USERNAME',
10205 => 'CURLOPT_TLSAUTH_PASSWORD',
10206 => 'CURLOPT_TLSAUTH_TYPE',
10211 => 'CURLOPT_DNS_SERVERS',
10217 => 'CURLOPT_MAIL_AUTH',
19913 => 'CURLOPT_RETURNTRANSFER',
19914 => 'CURLOPT_BINARYTRANSFER',
20011 => 'CURLOPT_WRITEFUNCTION',
20012 => 'CURLOPT_READFUNCTION',
20056 => 'CURLOPT_PROGRESSFUNCTION',
20079 => 'CURLOPT_HEADERFUNCTION',
20200 => 'CURLOPT_FNMATCH_FUNCTION',
30145 => 'CURLOPT_MAX_SEND_SPEED_LARGE',
30146 => 'CURLOPT_MAX_RECV_SPEED_LARGE');
static function getCurlOptCodeById($mixed) {
if (! is_array ( $mixed )) return self::$_CURLOPT_CODES_ [$mixed];
else {$result = array ();
foreach ( $mixed as $key => $value )
$result [array_key_exists ( $key, self::$_CURLOPT_CODES_ ) ? self::$_CURLOPT_CODES_ [$key] : $key] = $value;
return $result;}}
}
?>