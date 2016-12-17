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
 * @file    : notification-expert.php $
 * 
 * @id      : notification-expert.php | Tue Dec 13 06:40:49 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
?>
<tr>
<td><label for="message_top"><?php _pesc('Show maximum');?></label></td>
<td><input id="message_top" name="message_top" type="number"
value="<?php echo $message_top;?>"> <?php _pesc('messages');?></td>
<td><a class='help' onclick=<?php
echoHelp( $help_1 );
?>> [?]</a></td>
</tr>
<tr>
<td><label for="message_age"><?php _pesc('Remove messages');?> &lt;=</label></td>
<td><input id="message_age" name="message_age" type="number"
value="<?php echo $message_age;?>" min="0"> <?php _pesc('days');?></td>
<td><a class='help' onclick=<?php
echoHelp( $help_2 );
?>> [?]</a></td>
</tr>
<?php if(defined(__NAMESPACE__.'\\NOTIFICATION_EMAIL')&&NOTIFICATION_EMAIL){?>
<tr>
<td><label for="message_email"><?php _pesc('Send to email');?></label></td>
<td colspan="2"><input id="message_email" name="message_email" type="checkbox"
value="1" <?php $message_email && print(' checked ');?>><input type="hidden"
value="0" name="message_email"> <a class='help'
onclick=<?php echoHelp( $help_4 ); ?>> [?]</a></td>
</tr>
<?php
}
if ( $this->_alerts_count > 0 ) {
?>
<tr>
<td colspan="3"><input type="button" class="button"
value="<?php _pesc('Flush ALL messages');?>"
onclick="jsMyBackup.popupConfirm('<?php _pesc('Confirm');?>','<?php _pesc('Are you sure you want to delete ALL messages?');?>',null,{'<?php _pesc('Yes, I`m damn sure');?>':'jsMyBackup.asyncGetContent(jsMyBackup.ajaxurl, \'action=read_folder&tab=notification&sender=notification&nonce=<?php echo wp_create_nonce_wrapper('read_folder');?>&flush=1\',\'message_list\',function(xmlhttp){if(\'1\'!=xmlhttp.responseText.trim())return jsMyBackup.clear_ra_cache();jsMyBackup.read_alerts();jsMyBackup.messages_scroll(1);jsMyBackup.message_info();});jsMyBackup.removePopupLast();','<?php _pesc('Cancel');?>':null});"><a
class='help' onclick=<?php
echoHelp( $help_3 );
?>> [?]</a></td>
</tr>
<?php }?>