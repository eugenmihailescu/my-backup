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
 * @version : 0.2.3-3 $
 * @commit  : 961115f51b7b32dcbd4a8853000e4f8cc9216bdf $
 * @author  : Eugen Mihailescu <eugenmihailescux@gmail.com> $
 * @date    : Tue Feb 16 15:27:30 2016 +0100 $
 * @file    : CurlErrorMessages.php $
 * 
 * @id      : CurlErrorMessages.php | Tue Feb 16 15:27:30 2016 +0100 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

include_once "CurlErrorCodes.php";
global $_CURL_ERROR_MESSAGES;
$_CURL_ERROR_MESSAGES = array (
CURLSHE_OK => 'All fine. Proceed as usual.',
CURLSHE_BAD_OPTION => 'An invalid option was passed to the function.',
CURLSHE_IN_USE => 'The share object is currently in use.',
CURLSHE_INVALID => 'An invalid share object was passed to the function.',
CURLSHE_NOMEM => 'Not enough memory was available. (Added in 7.12.0)',
CURLSHE_NOT_BUILT_IN => 'The requested sharing could not be done because the library you use don`t have that particular feature enabled. (Added in 7.23.0)',
CURLM_UNKNOWN_OPTION => 'curl_multi_setopt() with unsupported option (Added in 7.15.4)',
CURLM_ADDED_ALREADY => 'An easy handle already added to a multi handle was attempted to get added a second time. (Added in 7.32.1)',
CURLE_FTP_WEIRD_SERVER_REPLY => 'After connecting to a FTP server, libcurl expects to get a certain reply back. This error code implies that it got a strange or bad reply. The given remote server is probably not an OK FTP server.',
CURLE_REMOTE_ACCESS_DENIED => 'We were denied access to the resource given in the URL.  For FTP, this occurs while trying to change to the remote directory.',
CURLE_FTP_ACCEPT_FAILED => 'While waiting for the server to connect back when an active FTP session is used, an error code was sent over the control connection or similar.',
CURLE_FTP_WEIRD_PASS_REPLY => 'After having sent the FTP password to the server, libcurl expects a proper reply. This error code indicates that an unexpected code was returned.',
CURLE_FTP_ACCEPT_TIMEOUT => 'During an active FTP session while waiting for the server to connect, the CURLOPT_ACCEPTTIMOUT_MS(3) (or the internal default) timeout expired.',
CURLE_FTP_WEIRD_PASV_REPLY => 'libcurl failed to get a sensible result back from the server as a response to either a PASV or a EPSV command. The server is flawed.',
CURLE_FTP_WEIRD_227_FORMAT => 'FTP servers return a 227-line as a response to a PASV command. If libcurl fails to parse that line, this return code is passed back.',
CURLE_FTP_CANT_GET_HOST => 'An internal failure to lookup the host used for the new connection.',
CURLE_HTTP2 => 'A problem was detected in the HTTP2 framing layer. This is somewhat generic and can be one out of several problems, see the error buffer for details.',
CURLE_FTP_COULDNT_SET_TYPE => 'Received an error when trying to set the transfer mode to binary or ASCII.',
CURLE_PARTIAL_FILE => 'A file transfer was shorter or larger than expected. This happens when the server first reports an expected transfer size, and then delivers data that doesn`t match the previously given size.',
CURLE_FTP_COULDNT_RETR_FILE => 'This was either a weird reply to a \'RETR\' command or a zero byte transfer complete.',
CURLE_QUOTE_ERROR => 'When sending custom \"QUOTE\" commands to the remote server, one of the commands returned an error code that was 400 or higher (for FTP) or otherwise indicated unsuccessful completion of the command.',
CURLE_HTTP_RETURNED_ERROR => 'This is returned if CURLOPT_FAILONERROR is set TRUE and the HTTP server returns an error code that is >= 400.',
CURLE_WRITE_ERROR => 'An error occurred when writing received data to a local file, or an error was returned to libcurl from a write callback.',
CURLE_UPLOAD_FAILED => 'Failed starting the upload. For FTP, the server typically denied the STOR command. The error buffer usually contains the server`s explanation for this.',
CURLE_READ_ERROR => 'There was a problem reading a local file or an error returned by the read callback.',
CURLE_OUT_OF_MEMORY => 'A memory allocation request failed. This is serious badness and things are severely screwed up if this ever occurs.',
CURLE_OPERATION_TIMEDOUT => 'Operation timeout. The specified time-out period was reached according to the conditions.',
CURLE_FTP_PORT_FAILED => 'The FTP PORT command returned error. This mostly happens when you haven`t specified a good enough address for libcurl to use. See CURLOPT_FTPPORT.',
CURLE_FTP_COULDNT_USE_REST => 'The FTP REST command returned error. This should never happen if the server is sane.',
CURLE_RANGE_ERROR => 'The server does not support or accept range requests.',
CURLE_HTTP_POST_ERROR => 'This is an odd error that mainly occurs due to internal confusion.',
CURLE_SSL_CONNECT_ERROR => 'A problem occurred somewhere in the SSL/TLS handshake. You really want the error buffer and read the message there as it pinpoints the problem slightly more. Could be certificates (file formats, paths, permissions), passwords, and others.',
CURLE_BAD_DOWNLOAD_RESUME => 'The download could not be resumed because the specified offset was out of the file boundary.',
CURLE_FILE_COULDNT_READ_FILE => 'A file given with FILE:// couldn`t be opened. Most likely because the file path doesn`t identify an existing file. Did you check file permissions?',
CURLE_LDAP_CANNOT_BIND => 'LDAP cannot bind. LDAP bind operation failed.',
CURLE_LDAP_SEARCH_FAILED => 'LDAP search failed.',
CURLE_FUNCTION_NOT_FOUND => 'Function not found. A required zlib function was not found.',
CURLE_ABORTED_BY_CALLBACK => 'Aborted by callback. A callback returned \"abort\" to libcurl.',
CURLE_BAD_FUNCTION_ARGUMENT => 'Internal error. A function was called with a bad parameter.',
CURLE_INTERFACE_FAILED => 'Interface error. A specified outgoing interface could not be used. Set which interface to use for outgoing connections\' source IP address with CURLOPT_INTERFACE.',
CURLE_TOO_MANY_REDIRECTS => 'Too many redirects. When following redirects, libcurl hit the maximum amount. Set your limit with CURLOPT_MAXREDIRS.',
CURLE_UNKNOWN_OPTION => 'An option passed to libcurl is not recognized/known. Refer to the appropriate documentation. This is most likely a problem in the program that uses libcurl. The error buffer might contain more specific information about which exact option it concerns.',
CURLE_TELNET_OPTION_SYNTAX => 'A telnet option string was Illegally formatted.',
CURLE_PEER_FAILED_VERIFICATION => 'The remote server`s SSL certificate or SSH md5 fingerprint was deemed not OK.',
CURLE_GOT_NOTHING => 'Nothing was returned from the server, and under the circumstances, getting nothing is considered an error.',
CURLE_SSL_ENGINE_NOTFOUND => 'The specified crypto engine wasn`t found.',
CURLE_SSL_ENGINE_SETFAILED => 'Failed setting the selected SSL crypto engine as default!',
CURLE_SEND_ERROR => 'Failed sending network data.',
CURLE_RECV_ERROR => 'Failure with receiving network data.',
CURLE_SSL_CERTPROBLEM => 'problem with the local client certificate.',
CURLE_SSL_CIPHER => 'Couldn`t use specified cipher.',
CURLE_SSL_CACERT => 'Peer certificate cannot be authenticated with known CA certificates.',
CURLE_BAD_CONTENT_ENCODING => 'Unrecognized transfer encoding.',
CURLE_LDAP_INVALID_URL => 'Invalid LDAP URL.',
CURLE_FILESIZE_EXCEEDED => 'Maximum file size exceeded.',
CURLE_USE_SSL_FAILED => 'Requested FTP SSL level failed.',
CURLE_SEND_FAIL_REWIND => 'When doing a send operation curl had to rewind the data to retransmit, but the rewinding operation failed.',
CURLE_SSL_ENGINE_INITFAILED => 'Initiating the SSL Engine failed.',
CURLE_LOGIN_DENIED => 'The remote server denied curl to login (Added in 7.13.1)',
CURLE_TFTP_NOTFOUND => 'File not found on TFTP server.',
CURLE_TFTP_PERM => 'Permission problem on TFTP server.',
CURLE_REMOTE_DISK_FULL => 'Out of disk space on the server.',
CURLE_TFTP_ILLEGAL => 'Illegal TFTP operation.',
CURLE_TFTP_UNKNOWNID => 'Unknown TFTP transfer ID.',
CURLE_REMOTE_FILE_EXISTS => 'File already exists and will not be overwritten.',
CURLE_TFTP_NOSUCHUSER => 'This error should never be returned by a properly functioning TFTP server.',
CURLE_CONV_FAILED => 'Character conversion failed.',
CURLE_CONV_REQD => 'Caller must register conversion callbacks.',
CURLE_SSL_CACERT_BADFILE => 'Problem with reading the SSL CA cert (path? access rights?)',
CURLE_REMOTE_FILE_NOT_FOUND => 'The resource referenced in the URL does not exist.',
CURLE_SSH => 'An unspecified error occurred during the SSH session.',
CURLE_SSL_SHUTDOWN_FAILED => 'Failed to shut down the SSL connection.',
CURLE_AGAIN => 'Socket is not ready for send/recv wait till it`s ready and try again. This return code is only returned from curl_easy_recv and curl_easy_send (Added in 7.18.2)',
CURLE_SSL_CRL_BADFILE => 'Failed to load CRL file (Added in 7.19.0)',
CURLE_SSL_ISSUER_ERROR => 'Issuer check failed (Added in 7.19.0)',
CURLE_FTP_PRET_FAILED => 'The FTP server does not understand the PRET command at all or does not support the given argument. Be careful when using CURLOPT_CUSTOMREQUEST, a custom LIST command will be sent with PRET CMD before PASV as well. (Added in 7.20.0)',
CURLE_RTSP_CSEQ_ERROR => 'Mismatch of RTSP CSeq numbers.',
CURLE_RTSP_SESSION_ERROR => 'Mismatch of RTSP Session Identifiers.',
CURLE_FTP_BAD_FILE_LIST => 'Unable to parse FTP file list (during FTP wildcard downloading).',
CURLE_CHUNK_FAILED => 'Chunk callback reported error.',
CURLE_NO_CONNECTION_AVAILABLE => 'These error codes will never be returned. They were used in an old libcurl version and are currently unused.' 
);
?>