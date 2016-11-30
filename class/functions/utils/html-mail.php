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
 * @version : 0.2.3-34 $
 * @commit  : 433010d91adb8b1c49bace58fae6cd2ba4679447 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Wed Nov 30 15:38:35 2016 +0100 $
 * @file    : html-mail.php $
 * 
 * @id      : html-mail.php | Wed Nov 30 15:38:35 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

function sendHtmlFormattedMail( 
$from, 
$to, 
$subject, 
$body, 
$plain_text = null, 
$priority = 3, 
$settings = null, 
$attachment = null, 
$args = null ) {
global $TARGET_NAMES;
empty( $settings ) &&
$settings = array( 'backup2mail_smtp' => false, 'backup2mail_backend' => 'mail', 'backup2mail_auth' => false );
empty( $args ) && $args = array( 
'width' => '772px', 
'height' => 'auto', 
'img' => 'http://api.mynixworld.info/img/mybackup-email-header-772x180.png' );
$smtp_debug = defined( __NAMESPACE__.'\\SMTP_DEBUG' ) && SMTP_DEBUG;
$native_backend = ! strToBool( $settings['backup2mail_smtp'] );
$backend = $settings['backup2mail_backend'];
$backend_params = array( 'debug' => $smtp_debug, 'auth' => strToBool( $settings['backup2mail_auth'] ) );
if ( $backend_params['auth'] )
foreach ( array( 
'host' => 'backup2mail_host', 
'port' => 'backup2mail_port', 
'username' => 'backup2mail_user', 
'password' => 'backup2mail_pwd', 
'timeout' => 'request_timeout' ) as $key => $value ) {
$backend_params[$key] = $settings[$value];
}
$footer_links = array();
$is_ajax = preg_match( '/(ajax|regactions).php/', selfURL() );
foreach ( array( 
_esc( 'Settings' ) => getTabLink( $TARGET_NAMES[APP_NOTIFICATION], $is_ajax ), 
_esc( 'Help' ) => getTabLink( $TARGET_NAMES[APP_WELCOME] . '&nocheck', $is_ajax ), 
_esc( 'FAQ' ) => APP_PLUGIN_FAQ_URI ) as $caption => $link )
$footer_links[] = sprintf( '<a style="color:#00adee;text-decoration:none;" href="%s">%s</a>', $link, $caption );
$footer = '<table style="font-size:12px;width:100%;border:none;margin-top:20px;text-align:center;color:#666;"><tr style="font-weight:700"><td>';
$footer .= implode( ' | ', $footer_links ) . '</td></tr><tr><td>' .
sprintf( _esc( 'This email was generated for %s' ), $to );
$footer .= sprintf( 
'</td></tr><tr><td>%s v%s</td></tr></table>', 
getAnchor( WPMYBACKUP, APP_PLUGIN_URI ), 
APP_VERSION_ID );
$body_div = '<div style="border:none;font-size:16px;color:#000;font-family:Verdana,Georgia,Serif !important;background-color:#E1E8ED;padding:15px;">';
$inner_div = sprintf( 
'<div style="padding: 15px;background-color:#fff;border-radius:10px;position:relative;margin-left:auto;margin-right:auto;width:%s;"><img src="%s">', 
$args['width'], 
$args['img'] );
$style = '<meta name="viewport" content="width=device-width, initial-scale=1">';
$body = $style . $body_div . $inner_div . $body . $footer . '</div></div>';
try {
return sendMail( 
$from, 
$to, 
$subject, 
$body, 
$attachment, 
$plain_text, 
$priority, 
$native_backend ? null : $backend, 
$backend_params, 
$smtp_debug );
} catch ( MyException $e ) {
}
return false;
}
?>