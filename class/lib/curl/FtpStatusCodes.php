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
 * @file    : FtpStatusCodes.php $
 * 
 * @id      : FtpStatusCodes.php | Wed Sep 16 11:33:37 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;

class FtpStatusCodes {
private static $_FTP_STATUS_CODE = array (
110 => "Restart marker replay.",
120 => "Service ready in nnn minutes.",
125 => "Data connection already open; transfer starting.",
150 => "File status okay; about to open data connection.",
200 => "The requested action has been successfully completed.",
202 => "Command not implemented, superfluous at this site.",
211 => "System status, or system help reply.",
212 => "Directory status.",
213 => "File status.",
214 => "Help message.On how to use the server or the meaning of a particular non-standard command. This reply is useful only to the human user.",
215 => "NAME system type. Where NAME is an official system name from the registry kept by IANA.",
220 => "Service ready for new user.",
221 => "Service closing control connection.",
225 => "Data connection open; no transfer in progress.",
226 => "Closing data connection. Requested file action successful (for example, file transfer or file abort).",
227 => "Entering Passive Mode (h1,h2,h3,h4,p1,p2).",
228 => "Entering Long Passive Mode (long address, port).",
229 => "Entering Extended Passive Mode (|||port|).",
230 => "User logged in, proceed. Logged out if appropriate.",
231 => "User logged out; service terminated.",
232 => "Logout command noted, will complete when transfer done.",
250 => "Requested file action okay, completed.",
257 => "PATHNAME created.",
300 => "The command has been accepted, but the requested action is on hold, pending receipt of further information.",
331 => "User name okay, need password.",
332 => "Need account for login.",
350 => "Requested file action pending further information",
400 => "The command was not accepted and the requested action did not take place, but the error condition is temporary and the action may be requested again.",
421 => "Service not available, closing control connection. This may be a reply to any command if the service knows it must shut down.",
425 => "Can't open data connection.",
426 => "Connection closed; transfer aborted.",
430 => "Invalid username or password",
434 => "Requested host unavailable.",
450 => "Requested file action not taken.",
451 => "Requested action aborted. Local error in processing.",
452 => "Requested action not taken. Insufficient storage space in system.File unavailable (e.g., file busy).",
500 => "Syntax error, command unrecognized and the requested action did not take place. This may include errors such as command line too long.",
501 => "Syntax error in parameters or arguments.",
502 => "Command not implemented.",
503 => "Bad sequence of commands.",
504 => "Command not implemented for that parameter.",
530 => "Not logged in.",
532 => "Need account for storing files.",
550 => "Requested action not taken. File unavailable (e.g., file not found, no access).",
551 => "Requested action aborted. Page type unknown.",
552 => "Requested file action aborted. Exceeded storage allocation (for current directory or dataset).",
553 => "Requested action not taken. File name not allowed.",
600 => "Replies regarding confidentiality and integrity",
631 => "Integrity protected reply.",
632 => "Confidentiality and integrity protected reply.",
633 => "Confidentiality protected reply.",
10000 => "Common Winsock Error Codes",
10054 => "Connection reset by peer. The connection was forcibly closed by the remote host.",
10060 => "Cannot connect to remote server.",
10061 => "Cannot connect to remote server. The connection is actively refused by the server.",
10066 => "Directory not empty.",
10068 => "Too many users, server is full." 
);
static public function getStatusCodes() {
return self::$_FTP_STATUS_CODE;
}
}
?>
