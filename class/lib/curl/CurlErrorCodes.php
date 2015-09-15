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
 * @file    : CurlErrorCodes.php $
 * 
 * @id      : CurlErrorCodes.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;

defined ( 'CURLSHE_OK' ) || define ( 'CURLSHE_OK', 0 );
defined ( 'CURLSHE_BAD_OPTION' ) || define ( 'CURLSHE_BAD_OPTION', 1 );
defined ( 'CURLSHE_IN_USE' ) || define ( 'CURLSHE_IN_USE', 2 );
defined ( 'CURLSHE_INVALID' ) || define ( 'CURLSHE_INVALID', 3 );
defined ( 'CURLSHE_NOMEM' ) || define ( 'CURLSHE_NOMEM', 4 );
defined ( 'CURLSHE_NOT_BUILT_IN' ) || define ( 'CURLSHE_NOT_BUILT_IN', 5 );
defined ( 'CURLM_UNKNOWN_OPTION' ) || define ( 'CURLM_UNKNOWN_OPTION', 6 );
defined ( 'CURLM_ADDED_ALREADY' ) || define ( 'CURLM_ADDED_ALREADY', 7 );
defined ( 'CURLE_FTP_WEIRD_SERVER_REPLY' ) || define ( 'CURLE_FTP_WEIRD_SERVER_REPLY', 8 );
defined ( 'CURLE_REMOTE_ACCESS_DENIED' ) || define ( 'CURLE_REMOTE_ACCESS_DENIED', 9 );
defined ( 'CURLE_FTP_ACCEPT_FAILED' ) || define ( 'CURLE_FTP_ACCEPT_FAILED', 10 );
defined ( 'CURLE_FTP_WEIRD_PASS_REPLY' ) || define ( 'CURLE_FTP_WEIRD_PASS_REPLY', 11 );
defined ( 'CURLE_FTP_ACCEPT_TIMEOUT' ) || define ( 'CURLE_FTP_ACCEPT_TIMEOUT', 12 );
defined ( 'CURLE_FTP_WEIRD_PASV_REPLY' ) || define ( 'CURLE_FTP_WEIRD_PASV_REPLY', 13 );
defined ( 'CURLE_FTP_WEIRD_227_FORMAT' ) || define ( 'CURLE_FTP_WEIRD_227_FORMAT', 14 );
defined ( 'CURLE_FTP_CANT_GET_HOST' ) || define ( 'CURLE_FTP_CANT_GET_HOST', 15 );
defined ( 'CURLE_HTTP2' ) || define ( 'CURLE_HTTP2', 16 );
defined ( 'CURLE_FTP_COULDNT_SET_TYPE' ) || define ( 'CURLE_FTP_COULDNT_SET_TYPE', 17 );
defined ( 'CURLE_PARTIAL_FILE' ) || define ( 'CURLE_PARTIAL_FILE', 18 );
defined ( 'CURLE_FTP_COULDNT_RETR_FILE' ) || define ( 'CURLE_FTP_COULDNT_RETR_FILE', 19 );
defined ( 'CURLE_QUOTE_ERROR' ) || define ( 'CURLE_QUOTE_ERROR', 21 );
defined ( 'CURLE_HTTP_RETURNED_ERROR' ) || define ( 'CURLE_HTTP_RETURNED_ERROR', 22 );
defined ( 'CURLE_WRITE_ERROR' ) || define ( 'CURLE_WRITE_ERROR', 23 );
defined ( 'CURLE_UPLOAD_FAILED' ) || define ( 'CURLE_UPLOAD_FAILED', 25 );
defined ( 'CURLE_READ_ERROR' ) || define ( 'CURLE_READ_ERROR', 26 );
defined ( 'CURLE_OUT_OF_MEMORY' ) || define ( 'CURLE_OUT_OF_MEMORY', 27 );
defined ( 'CURLE_OPERATION_TIMEDOUT' ) || define ( 'CURLE_OPERATION_TIMEDOUT', 28 );
defined ( 'CURLE_FTP_PORT_FAILED' ) || define ( 'CURLE_FTP_PORT_FAILED', 30 );
defined ( 'CURLE_FTP_COULDNT_USE_REST' ) || define ( 'CURLE_FTP_COULDNT_USE_REST', 31 );
defined ( 'CURLE_RANGE_ERROR' ) || define ( 'CURLE_RANGE_ERROR', 33 );
defined ( 'CURLE_HTTP_POST_ERROR' ) || define ( 'CURLE_HTTP_POST_ERROR', 34 );
defined ( 'CURLE_SSL_CONNECT_ERROR' ) || define ( 'CURLE_SSL_CONNECT_ERROR', 35 );
defined ( 'CURLE_BAD_DOWNLOAD_RESUME' ) || define ( 'CURLE_BAD_DOWNLOAD_RESUME', 36 );
defined ( 'CURLE_FILE_COULDNT_READ_FILE' ) || define ( 'CURLE_FILE_COULDNT_READ_FILE', 37 );
defined ( 'CURLE_LDAP_CANNOT_BIND' ) || define ( 'CURLE_LDAP_CANNOT_BIND', 38 );
defined ( 'CURLE_LDAP_SEARCH_FAILED' ) || define ( 'CURLE_LDAP_SEARCH_FAILED', 39 );
defined ( 'CURLE_FUNCTION_NOT_FOUND' ) || define ( 'CURLE_FUNCTION_NOT_FOUND', 41 );
defined ( 'CURLE_ABORTED_BY_CALLBACK' ) || define ( 'CURLE_ABORTED_BY_CALLBACK', 42 );
defined ( 'CURLE_BAD_FUNCTION_ARGUMENT' ) || define ( 'CURLE_BAD_FUNCTION_ARGUMENT', 43 );
defined ( 'CURLE_INTERFACE_FAILED' ) || define ( 'CURLE_INTERFACE_FAILED', 45 );
defined ( 'CURLE_TOO_MANY_REDIRECTS' ) || define ( 'CURLE_TOO_MANY_REDIRECTS', 47 );
defined ( 'CURLE_UNKNOWN_OPTION' ) || define ( 'CURLE_UNKNOWN_OPTION', 48 );
defined ( 'CURLE_TELNET_OPTION_SYNTAX' ) || define ( 'CURLE_TELNET_OPTION_SYNTAX', 49 );
defined ( 'CURLE_PEER_FAILED_VERIFICATION' ) || define ( 'CURLE_PEER_FAILED_VERIFICATION', 51 );
defined ( 'CURLE_GOT_NOTHING' ) || define ( 'CURLE_GOT_NOTHING', 52 );
defined ( 'CURLE_SSL_ENGINE_NOTFOUND' ) || define ( 'CURLE_SSL_ENGINE_NOTFOUND', 53 );
defined ( 'CURLE_SSL_ENGINE_SETFAILED' ) || define ( 'CURLE_SSL_ENGINE_SETFAILED', 54 );
defined ( 'CURLE_SEND_ERROR' ) || define ( 'CURLE_SEND_ERROR', 55 );
defined ( 'CURLE_RECV_ERROR' ) || define ( 'CURLE_RECV_ERROR', 56 );
defined ( 'CURLE_SSL_CERTPROBLEM' ) || define ( 'CURLE_SSL_CERTPROBLEM', 58 );
defined ( 'CURLE_SSL_CIPHER' ) || define ( 'CURLE_SSL_CIPHER', 59 );
defined ( 'CURLE_SSL_CACERT' ) || define ( 'CURLE_SSL_CACERT', 60 );
defined ( 'CURLE_BAD_CONTENT_ENCODING' ) || define ( 'CURLE_BAD_CONTENT_ENCODING', 61 );
defined ( 'CURLE_LDAP_INVALID_URL' ) || define ( 'CURLE_LDAP_INVALID_URL', 62 );
defined ( 'CURLE_FILESIZE_EXCEEDED' ) || define ( 'CURLE_FILESIZE_EXCEEDED', 63 );
defined ( 'CURLE_USE_SSL_FAILED' ) || define ( 'CURLE_USE_SSL_FAILED', 64 );
defined ( 'CURLE_SEND_FAIL_REWIND' ) || define ( 'CURLE_SEND_FAIL_REWIND', 65 );
defined ( 'CURLE_SSL_ENGINE_INITFAILED' ) || define ( 'CURLE_SSL_ENGINE_INITFAILED', 66 );
defined ( 'CURLE_LOGIN_DENIED' ) || define ( 'CURLE_LOGIN_DENIED', 67 );
defined ( 'CURLE_TFTP_NOTFOUND' ) || define ( 'CURLE_TFTP_NOTFOUND', 68 );
defined ( 'CURLE_TFTP_PERM' ) || define ( 'CURLE_TFTP_PERM', 69 );
defined ( 'CURLE_REMOTE_DISK_FULL' ) || define ( 'CURLE_REMOTE_DISK_FULL', 70 );
defined ( 'CURLE_TFTP_ILLEGAL' ) || define ( 'CURLE_TFTP_ILLEGAL', 71 );
defined ( 'CURLE_TFTP_UNKNOWNID' ) || define ( 'CURLE_TFTP_UNKNOWNID', 72 );
defined ( 'CURLE_REMOTE_FILE_EXISTS' ) || define ( 'CURLE_REMOTE_FILE_EXISTS', 73 );
defined ( 'CURLE_TFTP_NOSUCHUSER' ) || define ( 'CURLE_TFTP_NOSUCHUSER', 74 );
defined ( 'CURLE_CONV_FAILED' ) || define ( 'CURLE_CONV_FAILED', 75 );
defined ( 'CURLE_CONV_REQD' ) || define ( 'CURLE_CONV_REQD', 76 );
defined ( 'CURLE_SSL_CACERT_BADFILE' ) || define ( 'CURLE_SSL_CACERT_BADFILE', 77 );
defined ( 'CURLE_REMOTE_FILE_NOT_FOUND' ) || define ( 'CURLE_REMOTE_FILE_NOT_FOUND', 78 );
defined ( 'CURLE_SSH' ) || define ( 'CURLE_SSH', 79 );
defined ( 'CURLE_SSL_SHUTDOWN_FAILED' ) || define ( 'CURLE_SSL_SHUTDOWN_FAILED', 80 );
defined ( 'CURLE_AGAIN' ) || define ( 'CURLE_AGAIN', 81 );
defined ( 'CURLE_SSL_CRL_BADFILE' ) || define ( 'CURLE_SSL_CRL_BADFILE', 82 );
defined ( 'CURLE_SSL_ISSUER_ERROR' ) || define ( 'CURLE_SSL_ISSUER_ERROR', 83 );
defined ( 'CURLE_FTP_PRET_FAILED' ) || define ( 'CURLE_FTP_PRET_FAILED', 84 );
defined ( 'CURLE_RTSP_CSEQ_ERROR' ) || define ( 'CURLE_RTSP_CSEQ_ERROR', 85 );
defined ( 'CURLE_RTSP_SESSION_ERROR' ) || define ( 'CURLE_RTSP_SESSION_ERROR', 86 );
defined ( 'CURLE_FTP_BAD_FILE_LIST' ) || define ( 'CURLE_FTP_BAD_FILE_LIST', 87 );
defined ( 'CURLE_CHUNK_FAILED' ) || define ( 'CURLE_CHUNK_FAILED', 88 );
defined ( 'CURLE_NO_CONNECTION_AVAILABLE' ) || define ( 'CURLE_NO_CONNECTION_AVAILABLE', 89 );
?>
