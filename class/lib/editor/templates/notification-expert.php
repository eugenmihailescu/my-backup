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
 * @file    : notification-expert.php $
 * 
 * @id      : notification-expert.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;
?>
<tr>
<td><label for="message_top"><?php _pesc('Show maximum');?></label></td>
<td><input id="message_top" name="message_top" type="number"
value="<?php echo $message_top;?>"> <?php _pesc('messages');?></td>
<td><a class='help' onclick=<?php
echoHelp ( $help_1 );
?>> [?]</a></td>
</tr>
<tr>
<td><label for="message_age"><?php _pesc('Remove messages');?> &lt;=</label></td>
<td><input id="message_age" name="message_age" type="number"
value="<?php echo $message_age;?>"> <?php _pesc('days');?></td>
<td><a class='help' onclick=<?php
echoHelp ( $help_2 );
?>> [?]</a></td>
</tr>
<?php if($this->_alerts_count>0){?>
<tr>
<td colspan="3"><input type="button" class="button"
value="<?php _pesc('Flush ALL messages');?>"
onclick="js55f82caaae905.popupConfirm('<?php _pesc('Confirm');?>','<?php _pesc('Are you sure you want to delete ALL messages?');?>',null,{'<?php _pesc('Yes, I`m damn sure');?>':'js55f82caaae905.asyncGetContent(js55f82caaae905.ajaxurl, \'action=read_folder&tab=notification&sender=notification&nonce=<?php echo wp_create_nonce_wrapper('read_folder');?>&flush=1\',\'message_list\',function(xmlhttp){if(\'1\'!=xmlhttp.responseText.trim())return;js55f82caaae905.read_alerts();js55f82caaae905.messages_scroll(1);js55f82caaae905.message_info();});js55f82caaae905.removePopupLast();','<?php _pesc('Cancel');?>':null});"><a
class='help' onclick=<?php
echoHelp ( $help_3 );
?>> [?]</a></td>
</tr>
<?php }?>
