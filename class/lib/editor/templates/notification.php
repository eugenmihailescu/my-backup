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
 * @version : 0.2.3-27 $
 * @commit  : 10d36477364718fdc9b9947e937be6078051e450 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Fri Mar 18 10:06:27 2016 +0100 $
 * @file    : notification.php $
 * 
 * @id      : notification.php | Fri Mar 18 10:06:27 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
?>
<tr>
<td>
<?php
if ( $this->_alerts_count > 0 ) {
?>
<b>Show : </b> <input type="radio" name="message_show" id="new_messages"
value="<?php echo $this->MESSAGE_ITEM_UNREAD;?>" checked
onchange="jsMyBackup.messages_scroll(0);document.getElementById('mark_msg_btn').value='<?php _pesc('Mark all as ');?>'+(1==jsMyBackup.getShowWhat()?'<?php _pesc('read');?>':'<?php _pesc('unread');?>');">
<label for="new_messages"><?php _pesc('unread messages');?></label> <input
type="radio" name="message_show" id="old_messages"
value="<?php echo $this->MESSAGE_ITEM_READ;?>"
onchange="jsMyBackup.messages_scroll(0);document.getElementById('mark_msg_btn').value='<?php _pesc('Mark all as ');?>'+(1==jsMyBackup.getShowWhat()?'<?php _pesc('read');?>':'<?php _pesc('unread');?>');">
<label for="old_messages"><?php _pesc('read messages');?></label> <input
id="mark_msg_btn" type="button" value="<?php _pesc('Mark all as read');?>"
class="button"
onclick="<?php printf("jsMyBackup.popupConfirm('%s','"._esc('Are you sure you want to mark all messages as %sYou may still find them later in the %s messages%s, though.')."',null,{'%s':'jsMyBackup.messages_scroll(0,1==jsMyBackup.getShowWhat()?\'%s\':\'%s\');jsMyBackup.read_alerts();jsMyBackup.removePopupLast();','%s':null});",_esc('Confirm'),"'+(1==jsMyBackup.getShowWhat()?'"._esc('read')."':'"._esc('unread')."')+'?&lt;br&gt;","&lt;b&gt;'+(1==jsMyBackup.getShowWhat()?'"._esc('read')."':'"._esc('unread')."')+'","&lt;/b&gt;",_esc("Yes, I`m pretty sure"),'unread','read',_esc("Cancel"));?>">
<p style="font-weight: bold"><?php echo $message_status_title;?></p>
<?php }?>
<div id="message_list"
class="files_wrapper notify-msg <?echo $this->container_shape;?>"
style="height: auto">
<?php if(0==$this->_alerts_count)_pesc('No item found :-(');?>
</div>
<div style="display: none" id="message_detail_container">
<p style="font-weight: bold"><?php _pesc('Detailed information');?></p>
<div id="message_detail"
class="files_wrapper <?php echo $this->container_shape;?>"
style="height: auto"></div>
</div>
</td>
</tr>