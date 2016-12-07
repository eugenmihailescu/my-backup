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
 * @file    : ftp.php $
 * 
 * @id      : ftp.php | Wed Dec 7 18:54:23 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

include_once CURL_PATH . 'CurlWrapper.php';
function getFtpDirSeparator($systype, $ftp_dir_sep) {
return preg_match ( '/unix/i', $systype ) ? '/' : $ftp_dir_sep;
}
function getFtpInfo($settings) {
$ftp = getFtpObject ( $settings );
try {
$result = $ftp->getFtpInfo ();
$ssl_info = $ftp->getSSLInfo ();
if (! empty ( $ssl_info ))
add_session_var ( 'ftp_ssl_cert_info', $ssl_info );
} catch ( MyException $e ) {
$result = false;
}
return $result;
}
function getSSHInfo($settings) {
$ftp = getFtpObject ( $settings, true );
try {
$result = false;
$ssh_info = $ftp->getSSHInfo ();
if (! empty ( $ssh_info ))
add_session_var ( 'ftp_ssh_cert_info', $ssh_info );
} catch ( MyException $e ) {
$result = false;
}
return $result;
}
function getFtpObject($settings, $is_sftp = false) {
if ($is_sftp)
$ftp_class = 'CurlSSHWrapper';
elseif ('curl' == $settings ['ftp_lib'])
$ftp_class = 'CurlFtpWrapper';
else
$ftp_class = 'MyFtpWrapper';
require_once CURL_PATH . "$ftp_class.php";
$ftp_class = __NAMESPACE__ . '\\' . $ftp_class;
$ftp = new $ftp_class ();
$ftp->setFtpParams ( $settings );
return $ftp;
}
?>