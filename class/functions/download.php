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
 * @file    : download.php $
 * 
 * @id      : download.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;

include_once 'utils.php';
if (defined ( 'DROPBOX_TARGET' )) {
}
if (defined ( 'GOOGLE_TARGET' )) {
}
define ( 'DENY_DOWNLOAD_EXT', 'php,htaccess,config,conf,json,auth' );
function downloadAllowed($filename) {
$extensions = explode ( ',', DENY_DOWNLOAD_EXT );
if (empty ( $extensions ))
return true;
$ext = '';
preg_match ( '/\.([^\.]*)$/', $filename, $matches ) && $ext = $matches [1];
return ! in_array ( $ext, $extensions );
}
function redirectError($message, $timeout) {
echo "<!DOCTYPE html><html><body>$message<script>setTimeout(function(){window.history.go(-1);}, $timeout);</script></body></html>";
exit ();
}
function downloadServiceFile($service, $path, $outfile, $settings) {
if (! downloadAllowed ( $path ))
return;
$session = null;
switch ($service) {
case 'dropbox' :
$session = new DropboxOAuth2Client ();
$api = new DropboxCloudStorage ( $session );
$function = 'downloadFile';
$oauth_file = 'dropbox.auth';
break;
case 'google' :
$session = new GoogleOAuth2Client ();
$api = new GoogleCloudStorage ( $session );
$function = preg_match ( '/^http:\/\//', $path ) ? 'downloadUrl' : 'downloadFile';
$oauth_file = 'google.auth';
$outfile = null;
break;
case 'webdav' :
$api = new WebDAVWebStorage ( $settings );
$function = 'downloadFile';
break;
default :
throw new MyException ( 'Unknown download file service' );
break;
}
if (null != $session) {
$session->setProxyURI ( OAUTH_PROXY_URL, '' );
$session->setTimeout ( $settings ['request_timeout'] );
$session->initFromFile ( ROOT_OAUTH_FILE . $oauth_file );
}
$err = null;
try {
$result = _call_user_func ( array (
$api,
$function 
), $path, $outfile );
if (true !== $result)
$err = $result;
} catch ( MyException $e ) {
$err = $e->getMessage ();
}
if (! empty ( $err ))
echo redirectError ( $err, 3000 );
return _call_user_func ( array (
$api,
'getFile' 
), $path );
}
function downloadFile($filename = null, $service = null, $settings = null) {
if (! downloadAllowed ( $filename ))
return;
if (! (empty ( $service ) || empty ( $filename ))) {
$unlink = in_array ( $service, array (
'google',
'dropbox',
'ftp',
'webdav' 
) );
$mime_type = 'application/octet-stream';
$tmpfile = ! empty ( $filename ) ? addTrailingSlash ( sys_get_temp_dir () ) . basename ( $filename ) : null;
switch ($service) {
case 'dropbox' :
case 'google' :
case 'webdav' :
$mime_type = downloadServiceFile ( $service, $filename, $tmpfile, $settings );
$mime_type = ! empty ( $mime_type ['mime_type'] ) ? $mime_type ['mime_type'] : null;
break;
case 'ssh' :
case 'ftp' :
$ftp = getFtpObject ( $settings, 'ssh' == $service );
$ftp->ftpDownload ( $filename, $tmpfile );
break;
case 'disk' :
$tmpfile = $filename;
break;
default :
echo sprintf ( _esc ( "Download method '%s' not implemented" ), $service );
break;
}
if (isset ( $tmpfile ) && file_exists ( $tmpfile ) && ! empty ( $mime_type )) {
redirectFileDownload ( $tmpfile, $mime_type );
if ($unlink)
@unlink ( $tmpfile );
}
}
exit ();
}
?>