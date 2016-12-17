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
 * @version : 1.0-2 $
 * @commit  : f8add2d67e5ecacdcf020e1de6236dda3573a7a6 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Tue Dec 13 06:40:49 2016 +0100 $
 * @file    : download.php $
 * 
 * @id      : download.php | Tue Dec 13 06:40:49 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

include_once 'utils.php';
if ( defined( __NAMESPACE__.'\\DROPBOX_TARGET' ) ) {
}
if ( defined( __NAMESPACE__.'\\GOOGLE_TARGET' ) ) {
}
define( __NAMESPACE__.'\\DENY_DOWNLOAD_EXT', 'php,htaccess,config,conf,json,auth' );
function downloadAllowed( $filename ) {
$extensions = explode( ',', DENY_DOWNLOAD_EXT );
if ( empty( $extensions ) )
return true;
$ext = '';
preg_match( '/\.([^\.]*)$/', $filename, $matches ) && $ext = $matches[1];
return ! in_array( $ext, $extensions );
}
function redirectError( $message, $timeout ) {
echo "<!DOCTYPE html><html><body>$message<script>setTimeout(function(){window.history.go(-1);}, $timeout);</script></body></html>";
exit();
}
function downloadServiceFile( $service, $path, $outfile, $settings, $redirect_error = true ) {
if ( ! downloadAllowed( $path ) )
return;
$session = null;
switch ( $service ) {
case 'dropbox' :
$session = new DropboxOAuth2Client();
$api = new DropboxCloudStorage( $session );
$function = 'downloadFile';
$oauth_file = 'dropbox.auth';
break;
case 'google' :
$session = new GoogleOAuth2Client();
$api = new GoogleCloudStorage( $session );
$function = preg_match( '/^http:\/\//', $path ) ? 'downloadUrl' : 'downloadFile';
$oauth_file = 'google.auth';
$outfile = null;
break;
case 'webdav' :
$api = new WebDAVWebStorage( $settings );
$function = 'downloadFile';
break;
default :
throw new MyException( 'Unknown download file service' );
break;
}
if ( null != $session ) {
$session->setProxyURI( OAUTH_PROXY_URL, '' );
$session->setTimeout( $settings['request_timeout'] );
$session->initFromFile( ROOT_OAUTH_FILE . $oauth_file );
}
$err = null;
try {
$result = _call_user_func( array( $api, $function ), $path, $outfile );
if ( true !== $result )
$err = $result;
} catch ( MyException $e ) {
$err = $e->getMessage();
}
if ( $redirect_error && ! empty( $err ) )
echo redirectError( $err, 3000 );
return _call_user_func( array( $api, 'getFile' ), $path );
}
function downloadFile( $filename = null, $service = null, $settings = null, $download = true ) {
if ( ! downloadAllowed( $filename ) ) {
return;
}
if ( ! ( empty( $service ) || empty( $filename ) ) ) {
$unlink = in_array( $service, array( 'google', 'dropbox', 'ftp', 'webdav', 'test' ) );
$mime_type = 'application/octet-stream';
$tmpfile = ! empty( $filename ) ? addTrailingSlash( defined( __NAMESPACE__.'\\LOG_DIR' ) ? LOG_DIR : _sys_get_temp_dir() ) .
basename( $filename ) : null;
switch ( $service ) {
case 'dropbox' :
case 'google' :
case 'webdav' :
$mime_type = downloadServiceFile( $service, $filename, $tmpfile, $settings, $download );
$mime_type = ! empty( $mime_type['mime_type'] ) ? $mime_type['mime_type'] : null;
break;
case 'ssh' :
case 'ftp' :
$ftp = getFtpObject( $settings, 'ssh' == $service );
$ftp->ftpDownload( $filename, $tmpfile );
break;
case 'test' :
case 'disk' :
$tmpfile = $filename;
break;
default :
echo sprintf( _esc( "Download method '%s' not implemented" ), $service );
break;
}
if ( isset( $tmpfile ) && _file_exists( $tmpfile ) && ! empty( $mime_type ) ) {
if ( $download ) {
redirectFileDownload( $tmpfile, $mime_type );
if ( $unlink )
@unlink( $tmpfile );
} else
return $tmpfile;
}
}
$download && exit();
return false;
}
?>