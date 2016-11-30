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
 * @version : 0.2.3-33 $
 * @commit  : 8322fc3e4ca12a069f0821feb9324ea7cfa728bd $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Tue Nov 29 16:33:58 2016 +0100 $
 * @file    : CurlErrorCodes.php $
 * 
 * @id      : CurlErrorCodes.php | Tue Nov 29 16:33:58 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

defined(__NAMESPACE__.'\\CURLE_OK') || define(__NAMESPACE__.'\\CURLE_OK',0);
defined(__NAMESPACE__.'\\CURLE_UNSUPPORTED_PROTOCOL') || define(__NAMESPACE__.'\\CURLE_UNSUPPORTED_PROTOCOL',1);
defined(__NAMESPACE__.'\\CURLE_FAILED_INIT') || define(__NAMESPACE__.'\\CURLE_FAILED_INIT',2);
defined(__NAMESPACE__.'\\CURLE_URL_MALFORMAT') || define(__NAMESPACE__.'\\CURLE_URL_MALFORMAT',3);
defined(__NAMESPACE__.'\\CURLE_NOT_BUILT_IN') || define(__NAMESPACE__.'\\CURLE_NOT_BUILT_IN',4);
defined(__NAMESPACE__.'\\CURLE_COULDNT_RESOLVE_PROXY') || define(__NAMESPACE__.'\\CURLE_COULDNT_RESOLVE_PROXY',5);
defined(__NAMESPACE__.'\\CURLE_COULDNT_RESOLVE_HOST') || define(__NAMESPACE__.'\\CURLE_COULDNT_RESOLVE_HOST',6);
defined(__NAMESPACE__.'\\CURLE_COULDNT_CONNECT') || define(__NAMESPACE__.'\\CURLE_COULDNT_CONNECT',7);
defined(__NAMESPACE__.'\\CURLE_FTP_WEIRD_SERVER_REPLY') || define(__NAMESPACE__.'\\CURLE_FTP_WEIRD_SERVER_REPLY',8);
defined(__NAMESPACE__.'\\CURLE_REMOTE_ACCESS_DENIED') || define(__NAMESPACE__.'\\CURLE_REMOTE_ACCESS_DENIED',9);
defined(__NAMESPACE__.'\\CURLE_FTP_ACCEPT_FAILED') || define(__NAMESPACE__.'\\CURLE_FTP_ACCEPT_FAILED',10);
defined(__NAMESPACE__.'\\CURLE_FTP_WEIRD_PASS_REPLY') || define(__NAMESPACE__.'\\CURLE_FTP_WEIRD_PASS_REPLY',11);
defined(__NAMESPACE__.'\\CURLE_FTP_ACCEPT_TIMEOUT') || define(__NAMESPACE__.'\\CURLE_FTP_ACCEPT_TIMEOUT',12);
defined(__NAMESPACE__.'\\CURLE_FTP_WEIRD_PASV_REPLY') || define(__NAMESPACE__.'\\CURLE_FTP_WEIRD_PASV_REPLY',13);
defined(__NAMESPACE__.'\\CURLE_FTP_WEIRD_227_FORMAT') || define(__NAMESPACE__.'\\CURLE_FTP_WEIRD_227_FORMAT',14);
defined(__NAMESPACE__.'\\CURLE_FTP_CANT_GET_HOST') || define(__NAMESPACE__.'\\CURLE_FTP_CANT_GET_HOST',15);
defined(__NAMESPACE__.'\\CURLE_HTTP2') || define(__NAMESPACE__.'\\CURLE_HTTP2',16);
defined(__NAMESPACE__.'\\CURLE_FTP_COULDNT_SET_TYPE') || define(__NAMESPACE__.'\\CURLE_FTP_COULDNT_SET_TYPE',17);
defined(__NAMESPACE__.'\\CURLE_PARTIAL_FILE') || define(__NAMESPACE__.'\\CURLE_PARTIAL_FILE',18);
defined(__NAMESPACE__.'\\CURLE_FTP_COULDNT_RETR_FILE') || define(__NAMESPACE__.'\\CURLE_FTP_COULDNT_RETR_FILE',19);
defined(__NAMESPACE__.'\\CURLE_QUOTE_ERROR') || define(__NAMESPACE__.'\\CURLE_QUOTE_ERROR',21);
defined(__NAMESPACE__.'\\CURLE_HTTP_RETURNED_ERROR') || define(__NAMESPACE__.'\\CURLE_HTTP_RETURNED_ERROR',22);
defined(__NAMESPACE__.'\\CURLE_WRITE_ERROR') || define(__NAMESPACE__.'\\CURLE_WRITE_ERROR',23);
defined(__NAMESPACE__.'\\CURLE_UPLOAD_FAILED') || define(__NAMESPACE__.'\\CURLE_UPLOAD_FAILED',25);
defined(__NAMESPACE__.'\\CURLE_READ_ERROR') || define(__NAMESPACE__.'\\CURLE_READ_ERROR',26);
defined(__NAMESPACE__.'\\CURLE_OUT_OF_MEMORY') || define(__NAMESPACE__.'\\CURLE_OUT_OF_MEMORY',27);
defined(__NAMESPACE__.'\\CURLE_OPERATION_TIMEDOUT') || define(__NAMESPACE__.'\\CURLE_OPERATION_TIMEDOUT',28);
defined(__NAMESPACE__.'\\CURLE_FTP_PORT_FAILED') || define(__NAMESPACE__.'\\CURLE_FTP_PORT_FAILED',30);
defined(__NAMESPACE__.'\\CURLE_FTP_COULDNT_USE_REST') || define(__NAMESPACE__.'\\CURLE_FTP_COULDNT_USE_REST',31);
defined(__NAMESPACE__.'\\CURLE_RANGE_ERROR') || define(__NAMESPACE__.'\\CURLE_RANGE_ERROR',33);
defined(__NAMESPACE__.'\\CURLE_HTTP_POST_ERROR') || define(__NAMESPACE__.'\\CURLE_HTTP_POST_ERROR',34);
defined(__NAMESPACE__.'\\CURLE_SSL_CONNECT_ERROR') || define(__NAMESPACE__.'\\CURLE_SSL_CONNECT_ERROR',35);
defined(__NAMESPACE__.'\\CURLE_BAD_DOWNLOAD_RESUME') || define(__NAMESPACE__.'\\CURLE_BAD_DOWNLOAD_RESUME',36);
defined(__NAMESPACE__.'\\CURLE_FILE_COULDNT_READ_FILE') || define(__NAMESPACE__.'\\CURLE_FILE_COULDNT_READ_FILE',37);
defined(__NAMESPACE__.'\\CURLE_LDAP_CANNOT_BIND') || define(__NAMESPACE__.'\\CURLE_LDAP_CANNOT_BIND',38);
defined(__NAMESPACE__.'\\CURLE_LDAP_SEARCH_FAILED') || define(__NAMESPACE__.'\\CURLE_LDAP_SEARCH_FAILED',39);
defined(__NAMESPACE__.'\\CURLE_FUNCTION_NOT_FOUND') || define(__NAMESPACE__.'\\CURLE_FUNCTION_NOT_FOUND',41);
defined(__NAMESPACE__.'\\CURLE_ABORTED_BY_CALLBACK') || define(__NAMESPACE__.'\\CURLE_ABORTED_BY_CALLBACK',42);
defined(__NAMESPACE__.'\\CURLE_BAD_FUNCTION_ARGUMENT') || define(__NAMESPACE__.'\\CURLE_BAD_FUNCTION_ARGUMENT',43);
defined(__NAMESPACE__.'\\CURLE_INTERFACE_FAILED') || define(__NAMESPACE__.'\\CURLE_INTERFACE_FAILED',45);
defined(__NAMESPACE__.'\\CURLE_TOO_MANY_REDIRECTS') || define(__NAMESPACE__.'\\CURLE_TOO_MANY_REDIRECTS',47);
defined(__NAMESPACE__.'\\CURLE_UNKNOWN_OPTION') || define(__NAMESPACE__.'\\CURLE_UNKNOWN_OPTION',48);
defined(__NAMESPACE__.'\\CURLE_TELNET_OPTION_SYNTAX') || define(__NAMESPACE__.'\\CURLE_TELNET_OPTION_SYNTAX',49);
defined(__NAMESPACE__.'\\CURLE_PEER_FAILED_VERIFICATION') || define(__NAMESPACE__.'\\CURLE_PEER_FAILED_VERIFICATION',51);
defined(__NAMESPACE__.'\\CURLE_GOT_NOTHING') || define(__NAMESPACE__.'\\CURLE_GOT_NOTHING',52);
defined(__NAMESPACE__.'\\CURLE_SSL_ENGINE_NOTFOUND') || define(__NAMESPACE__.'\\CURLE_SSL_ENGINE_NOTFOUND',53);
defined(__NAMESPACE__.'\\CURLE_SSL_ENGINE_SETFAILED') || define(__NAMESPACE__.'\\CURLE_SSL_ENGINE_SETFAILED',54);
defined(__NAMESPACE__.'\\CURLE_SEND_ERROR') || define(__NAMESPACE__.'\\CURLE_SEND_ERROR',55);
defined(__NAMESPACE__.'\\CURLE_RECV_ERROR') || define(__NAMESPACE__.'\\CURLE_RECV_ERROR',56);
defined(__NAMESPACE__.'\\CURLE_SSL_CERTPROBLEM') || define(__NAMESPACE__.'\\CURLE_SSL_CERTPROBLEM',58);
defined(__NAMESPACE__.'\\CURLE_SSL_CIPHER') || define(__NAMESPACE__.'\\CURLE_SSL_CIPHER',59);
defined(__NAMESPACE__.'\\CURLE_SSL_CACERT') || define(__NAMESPACE__.'\\CURLE_SSL_CACERT',60);
defined(__NAMESPACE__.'\\CURLE_BAD_CONTENT_ENCODING') || define(__NAMESPACE__.'\\CURLE_BAD_CONTENT_ENCODING',61);
defined(__NAMESPACE__.'\\CURLE_LDAP_INVALID_URL') || define(__NAMESPACE__.'\\CURLE_LDAP_INVALID_URL',62);
defined(__NAMESPACE__.'\\CURLE_FILESIZE_EXCEEDED') || define(__NAMESPACE__.'\\CURLE_FILESIZE_EXCEEDED',63);
defined(__NAMESPACE__.'\\CURLE_USE_SSL_FAILED') || define(__NAMESPACE__.'\\CURLE_USE_SSL_FAILED',64);
defined(__NAMESPACE__.'\\CURLE_SEND_FAIL_REWIND') || define(__NAMESPACE__.'\\CURLE_SEND_FAIL_REWIND',65);
defined(__NAMESPACE__.'\\CURLE_SSL_ENGINE_INITFAILED') || define(__NAMESPACE__.'\\CURLE_SSL_ENGINE_INITFAILED',66);
defined(__NAMESPACE__.'\\CURLE_LOGIN_DENIED') || define(__NAMESPACE__.'\\CURLE_LOGIN_DENIED',67);
defined(__NAMESPACE__.'\\CURLE_TFTP_NOTFOUND') || define(__NAMESPACE__.'\\CURLE_TFTP_NOTFOUND',68);
defined(__NAMESPACE__.'\\CURLE_TFTP_PERM') || define(__NAMESPACE__.'\\CURLE_TFTP_PERM',69);
defined(__NAMESPACE__.'\\CURLE_REMOTE_DISK_FULL') || define(__NAMESPACE__.'\\CURLE_REMOTE_DISK_FULL',70);
defined(__NAMESPACE__.'\\CURLE_TFTP_ILLEGAL') || define(__NAMESPACE__.'\\CURLE_TFTP_ILLEGAL',71);
defined(__NAMESPACE__.'\\CURLE_TFTP_UNKNOWNID') || define(__NAMESPACE__.'\\CURLE_TFTP_UNKNOWNID',72);
defined(__NAMESPACE__.'\\CURLE_REMOTE_FILE_EXISTS') || define(__NAMESPACE__.'\\CURLE_REMOTE_FILE_EXISTS',73);
defined(__NAMESPACE__.'\\CURLE_TFTP_NOSUCHUSER') || define(__NAMESPACE__.'\\CURLE_TFTP_NOSUCHUSER',74);
defined(__NAMESPACE__.'\\CURLE_CONV_FAILED') || define(__NAMESPACE__.'\\CURLE_CONV_FAILED',75);
defined(__NAMESPACE__.'\\CURLE_CONV_REQD') || define(__NAMESPACE__.'\\CURLE_CONV_REQD',76);
defined(__NAMESPACE__.'\\CURLE_SSL_CACERT_BADFILE') || define(__NAMESPACE__.'\\CURLE_SSL_CACERT_BADFILE',77);
defined(__NAMESPACE__.'\\CURLE_REMOTE_FILE_NOT_FOUND') || define(__NAMESPACE__.'\\CURLE_REMOTE_FILE_NOT_FOUND',78);
defined(__NAMESPACE__.'\\CURLE_SSH') || define(__NAMESPACE__.'\\CURLE_SSH',79);
defined(__NAMESPACE__.'\\CURLE_SSL_SHUTDOWN_FAILED') || define(__NAMESPACE__.'\\CURLE_SSL_SHUTDOWN_FAILED',80);
defined(__NAMESPACE__.'\\CURLE_AGAIN') || define(__NAMESPACE__.'\\CURLE_AGAIN',81);
defined(__NAMESPACE__.'\\CURLE_SSL_CRL_BADFILE') || define(__NAMESPACE__.'\\CURLE_SSL_CRL_BADFILE',82);
defined(__NAMESPACE__.'\\CURLE_SSL_ISSUER_ERROR') || define(__NAMESPACE__.'\\CURLE_SSL_ISSUER_ERROR',83);
defined(__NAMESPACE__.'\\CURLE_FTP_PRET_FAILED') || define(__NAMESPACE__.'\\CURLE_FTP_PRET_FAILED',84);
defined(__NAMESPACE__.'\\CURLE_RTSP_CSEQ_ERROR') || define(__NAMESPACE__.'\\CURLE_RTSP_CSEQ_ERROR',85);
defined(__NAMESPACE__.'\\CURLE_RTSP_SESSION_ERROR') || define(__NAMESPACE__.'\\CURLE_RTSP_SESSION_ERROR',86);
defined(__NAMESPACE__.'\\CURLE_FTP_BAD_FILE_LIST') || define(__NAMESPACE__.'\\CURLE_FTP_BAD_FILE_LIST',87);
defined(__NAMESPACE__.'\\CURLE_CHUNK_FAILED') || define(__NAMESPACE__.'\\CURLE_CHUNK_FAILED',88);
defined(__NAMESPACE__.'\\CURLE_NO_CONNECTION_AVAILABLE') || define(__NAMESPACE__.'\\CURLE_NO_CONNECTION_AVAILABLE',89);
defined(__NAMESPACE__.'\\CURLE_SSL_PINNEDPUBKEYNOTMATCH') || define(__NAMESPACE__.'\\CURLE_SSL_PINNEDPUBKEYNOTMATCH',90);
defined(__NAMESPACE__.'\\CURLE_SSL_INVALIDCERTSTATUS') || define(__NAMESPACE__.'\\CURLE_SSL_INVALIDCERTSTATUS',91);
?>