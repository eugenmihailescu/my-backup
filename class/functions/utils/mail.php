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
 * @version : 0.2.3-36 $
 * @commit  : c4d8a236c57b60a62c69e03c1273eaff3a9d56fb $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Thu Dec 1 04:37:45 2016 +0100 $
 * @file    : mail.php $
 * 
 * @id      : mail.php | Thu Dec 1 04:37:45 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

function get_content_type( $mime, $as_array = false ) {
return $as_array ? array( 'Content-Type' => $mime ) : "Content-Type: $mime;";
}
function get_transfer_encoding( $encoding ) {
return "Content-Transfer-Encoding: $encoding";
}
function get_content_boundaries() {
$random_hash = md5( date( 'r', time() ) );
$boundary_signature = 'PHP';
$boundary_prefix = "--$boundary_signature";
$boundary_alt = "$boundary_prefix-alt-$random_hash";
$boundary_mixed = "$boundary_prefix-mixed-$random_hash";
return array( $boundary_mixed, $boundary_alt );
}
function sendMail( 
$from, 
$to, 
$subject, 
$body, 
$attachments = null, 
$plain_text = null, 
$priority = 3, 
$driver = null, 
$params = null, 
$debug = false ) {
$eol = "\r\n";
$deol = $eol . $eol;
$log_sep = PHP_EOL . str_repeat( '-', 80 ) . PHP_EOL;
$native_backend = true;
if ( ! empty( $driver ) && ! empty( $params ) ) {
include_once 'Mail2.php';
$native_backend = ! class_exists( '\\Mail2' );
}
list( $boundary_mixed, $boundary_alt ) = get_content_boundaries();
$charset = 'utf-8';
$charset_tag = 'charset="' . $charset . '";';
$transfer_encoding = get_transfer_encoding( '7bit' );
$html_mime = 'text/html';
$style_script_pattern = '/<((style|script|meta)[^>]*>)[\s\S]*?\/\2>/';
$headers = array( 'MIME-Version' => '1.0', 'From' => $from, 
'Subject' => $subject );
! empty( $priority ) && $headers['X-Priority'] = $priority;
$headers = $headers + get_content_type( "multipart/mixed;boundary=\"$boundary_mixed\"" . $eol, true );
$headers_str = '';
foreach ( $headers as $key => $value )
$headers_str .= "$key: $value" . $eol;
ob_start();
if ( $native_backend ) {
echo "--$boundary_mixed" . $eol;
echo get_content_type( 'multipart/alternative' ), "$charset_tag boundary=\"$boundary_alt\"", $deol;
echo "--$boundary_alt", $eol;
echo get_content_type( 'text/plain' ), $charset_tag, $eol;
echo $transfer_encoding, $deol;
}
$stripped_styles = preg_replace( $style_script_pattern, '', $body );
$plain_text = empty( $plain_text ) ? strip_tags( $stripped_styles, '<a></a>' ) : $plain_text;
$plain_text = preg_replace( 
'/<a[\s\S]*?href\s*=\s*[\'"](.*?)[\'"][\s\S]*?>([\s\S]*?)<\/a>/', 
'$2 ($1)', 
$plain_text );
echo $plain_text, $deol;
if ( $native_backend ) {
echo "--$boundary_alt", $eol;
echo get_content_type( $html_mime ), $charset_tag, $eol;
echo $transfer_encoding, $deol;
}
$scripts_style = '';
preg_match_all( $style_script_pattern, $body, $matches ) && $scripts_style = implode( PHP_EOL, $matches[0] );
$body = preg_replace( '/<((html|head|body)>)[\s\S]*<\/\1/', '', $body ); 
$body = preg_replace( '/<meta[^>]*>/', '', $body ); 
echo '<html>', $eol;
echo '<head><meta http-equiv="content-type" content="', $html_mime, '";', $charset_tag, '>' . $scripts_style .
'</head>', $eol;
echo '<body>', $eol;
echo $stripped_styles, $eol;
echo '</body></html>', $deol;
$native_backend && printf( "--$boundary_alt--" . $deol );
$attachment_size = 0;
if ( $native_backend && is_array( $attachments ) ) {
$finfo = finfo_open( FILEINFO_MIME_TYPE );
foreach ( $attachments as $key => $file_item )
if ( is_array( $file_item ) && _is_file( $file_item['tmp_name'] ) ) {
$attachment_size += filesize( $filename );
echo "--$boundary_mixed", $eol;
$name = $file_item['name'];
$filename = $file_item['tmp_name'];
$mime_type = empty( $file_item['type'] ) ? finfo_file( $finfo, $filename ) : $file_item['type'];
( false == $mime_type ) && $mime_type = 'application/octet-stream';
echo get_content_type( $mime_type ), 'name="', $name, '"', $eol;
echo get_transfer_encoding( 'base64' ), $eol;
echo 'Content-Disposition: attachment; filename="', $name, '"', $deol;
echo chunk_split( base64_encode( file_get_contents( $filename ) ) ), $eol;
}
finfo_close( $finfo );
}
if ( $native_backend )
echo "--$boundary_mixed--", $deol;
$html_message = ob_get_clean();
$result = true;
$debug && file_put_contents( 
SMTP_DEBUG_LOG, 
$log_sep . sprintf( 
'[%s] %s%s', 
date( DATETIME_FORMAT ), 
$to, 
is_array( $attachments ) ? ' (' . count( $attachments ) . ' attachments)' : '' ) . $log_sep, 
FILE_APPEND );
if ( ! $native_backend ) {
include_once 'Mail/mime.php';
$mime = new \Mail_mime( array( 'eol' => PHP_EOL ) );
$mime->setTXTBody( $plain_text );
$mime->setHTMLBody( $html_message );
if ( is_array( $attachments ) ) {
$finfo = finfo_open( FILEINFO_MIME_TYPE );
foreach ( $attachments as $key => $file_item )
if ( is_array( $file_item ) && _is_file( $file_item['tmp_name'] ) ) {
$attachment_size += filesize( $file_item['tmp_name'] );
$mime_type = empty( $file_item['type'] ) ? finfo_file( $finfo, $file_item['tmp_name'] ) : $file_item['type'];
$mime->addAttachment( $file_item['tmp_name'], $mime_type );
}
}
$body = $mime->get();
$headers = $mime->headers( $headers );
$obj = Mail2::factory( $driver, $params );
ob_start();
try {
$mail = $obj->send( $to, $headers, $body );
} catch ( \Exception $e ) {
$result = $e->getMessage();
}
$pear = new \PEAR();
if ( isset( $mail ) && $pear->isError( $mail ) ) {
$result = $mail->getMessage();
}
unset( $obj );
$output = ob_get_clean();
if ( $debug && preg_match_all( '/^(DEBUG:\s*)(.*)/m', $output, $matches ) ) {
file_put_contents( SMTP_DEBUG_LOG, implode( PHP_EOL, $matches[2] ) . PHP_EOL, FILE_APPEND );
}
} else {
$result = @mail( $to, $subject, $html_message, $headers_str );
}
if ( $debug ) {
! empty( $params ) && file_put_contents( 
SMTP_DEBUG_LOG, 
sprintf( 'Connection params : %s', print_r( $params, true ) ) . PHP_EOL, 
FILE_APPEND );
file_put_contents( 
SMTP_DEBUG_LOG, 
sprintf( 'Message size : %d bytes', strlen( $html_message ) ) . PHP_EOL, 
FILE_APPEND );
file_put_contents( 
SMTP_DEBUG_LOG, 
sprintf( 'Attachment size : %d bytes', $attachment_size ) . PHP_EOL, 
FILE_APPEND );
file_put_contents( 
SMTP_DEBUG_LOG, 
sprintf( PHP_EOL . '[%s] Status : %s', date( DATETIME_FORMAT ), true !== $result ? $result : 'success' ) .
$log_sep, 
FILE_APPEND );
}
if ( true !== $result ) {
throw new MyException( $result );
}
return $result;
}
?>