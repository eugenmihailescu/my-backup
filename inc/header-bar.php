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
 * @version : 0.2.3-8 $
 * @commit  : 010da912cb002abdf2f3ab5168bf8438b97133ea $
 * @author  : Eugen Mihailescu eugenmihailescux@gmail.com $
 * @date    : Tue Feb 16 21:41:51 2016 UTC $
 * @file    : header-bar.php $
 * 
 * @id      : header-bar.php | Tue Feb 16 21:41:51 2016 UTC | Eugen Mihailescu eugenmihailescux@gmail.com $
*/

namespace MyBackup;
?>
<table style="width: 100%" id="header_bar">
<tr>
<td style="white-space: nowrap; vertical-align: top; width: 0"><span
style='font-size: 1.5em; margin: .75em 0; font-weight: bold;'><?php echo $title; ?><a
class='help'
onclick=<?php
echoHelp( sprintf( $title_desc, escape_quotes( $title ) ) );
?>>*</a><?php echo defined(__NAMESPACE__.'\\SANDBOX')&&SANDBOX?(sprintf('%s <span style="font-size:0.75em;font-weight:normal">(%s)</span>',_esc('SANDBOX'),sprintf(_esc('%d active sessions'),getActiveSandboxes()))):'';?></span> 
<?php
if ( ! isSSL() ) {
echo getSSLIcon();
if ( ! isset( $java_scripts['ssl'] ) )
$java_scripts['ssl'] = "setInterval(parent.fadeSSLIcons," . SSL_ALERT_FADE_INTERVAL . ");";
}
?>
</td>
<td id="notification_bar" style="text-align: center; width: 100%;">
<?php if(defined(__NAMESPACE__.'\\DEBUG_STATUSBAR')&&DEBUG_STATUSBAR){?>
<div
style="display: inline-block; bottom: 0; position: fixed; left: 0; right: 0; z-index: 1000; opacity: 0.75"
id="notification_debug_div">
<span id="notification_debug" class="hintbox"
style="border-radius: 5px; display: none"> </span>
</div><?php }?>
<div id="notification_msg" style="display: inline-block"></div>
</td>
<td style="width: 0; text-align: right; white-space: nowrap;">
<?php echo getAnchor( _esc( 'How it works' ), getTabLink( $TARGET_NAMES[APP_WELCOME] ) . '&nocheck', '_self' );?>
</td>
<td style="width: 0; text-align: right; white-space: nowrap;"><?php
if ( defined( __NAMESPACE__.'\\WPMYBACKUP_LOGOFF' ) )
echo WPMYBACKUP_LOGOFF;
?></td>
</tr>
</table>