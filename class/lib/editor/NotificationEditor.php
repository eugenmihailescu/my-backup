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
 * @version : 0.2.3-3 $
 * @commit  : 961115f51b7b32dcbd4a8853000e4f8cc9216bdf $
 * @author  : Eugen Mihailescu <eugenmihailescux@gmail.com> $
 * @date    : Tue Feb 16 15:27:30 2016 +0100 $
 * @file    : NotificationEditor.php $
 * 
 * @id      : NotificationEditor.php | Tue Feb 16 15:27:30 2016 +0100 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
class NotificationEditor extends AbstractTargetEditor {
private $_alerts_count;
private function _getJavaScripts() {
$this->java_scripts [] = "parent.getShowWhat=function(){var i,show=0,els=document.getElementsByName('message_show');for(i=0;i<els.length;i++)if(els[i].checked){show=els[i].value;break;}return show;};";
$this->java_scripts [] = "parent.message_info=function(sender){parent.asyncGetContent(parent.ajaxurl,'" . http_build_query ( array (
'action' => 'read_folder',
'tab' => $this->target_name,
'sender' => $this->target_name,
'detail' => 1,
'nonce' => wp_create_nonce_wrapper ( 'read_folder' ) 
) ) . "&msg_id='+(sender?sender.parentNode.id:-1),'message_detail',function(){document.getElementById('message_detail_container').style.display='inherit';});};";
$this->java_scripts [] = "parent.globals.slider_down=false;var last_pos=-1,last_dir=-2,last_show='';";
$this->java_scripts [] = "parent.messages_scroll=function(direction,toggle_sender,row_id){var msg_id=null,pos=0,el=document.getElementById('message_list_tbl'),i,show=parent.getShowWhat(),els;if(el)pos=parent.getFakeAttrFromStyle(el,'current_offset');if(0==pos&&direction<0||last_pos===pos&&last_dir===direction&&null===parent.isNull(toggle_sender,null)&&last_show===show){if(parent.globals.slider_down)clearInterval(parent.globals.slider_down);return;}last_pos=pos;last_dir=direction;last_show=show;if(toggle_sender)msg_id=('read'==toggle_sender||'unread'==toggle_sender?toggle_sender:toggle_sender.id);parent.asyncGetContent(parent.ajaxurl,'" . http_build_query ( array (
'action' => 'read_folder',
'tab' => $this->target_name,
'sender' => $this->target_name,
'nonce' => wp_create_nonce_wrapper ( 'read_folder' ) 
) ) . "&pos='+pos+'&dir='+direction+'&show='+show+(null!==msg_id?'&msg_id='+msg_id:'')+(null!==parent.isNull(row_id,null)?'&row_id='+row_id:''),'message_list',function(xhtml){var i,els,el=document.getElementById('message_list_tbl');if(!el)return;els=el.getElementsByTagName('IMG');if(els)for(i=0;i<els.length;i+=1){els[i].setAttribute('onclick','jsMyBackup.messages_scroll(0,this.parentNode.parentNode);jsMyBackup.read_alerts();');els[i].title='Click me to mark this message as '+(1==show?'unread':'read');}els = el.getElementsByTagName('TR');if(els)for (i = 0; i < els.length; i += 1){if(els[i].children.length>1){els[i].children[2].setAttribute('onclick','jsMyBackup.message_info(this);');if(els[i].children.length>3 && 0==els[i].lastChild.innerHTML.length)els[i].lastChild.setAttribute('onclick', 'jsMyBackup.scroll2page(this)');}}});};";
$this->java_scripts [] = 'parent.messages_scroll(0);'; 
$this->java_scripts [] = 'parent.scroll2page=function(sender){var tmp=parent.getFakeAttrFromStyle(sender,"row_id");if(tmp!==parent.globals.slider_row_id){parent.globals.slider_row_id=tmp;jsMyBackup.messages_scroll(1,null,tmp);}};';
}
protected function initTarget() {
parent::initTarget ();
$this->customTitle = _esc ( 'Notification messages' );
}
protected function getEditorTemplate() {
$log = MESSAGES_LOGFILE;
$alert_message_obj = new MessageHandler ( $log );
$alerts = $alert_message_obj->getMessagesByKeys ( array (
'status',
'type' 
), array (
MESSAGE_ITEM_UNREAD,
array (
MESSAGE_TYPE_WARNING,
MESSAGE_TYPE_ERROR 
) 
) );
$types = $alert_message_obj->getMessageTypes ();
$message_status_title = array ();
foreach ( $alerts as $msg_item )
$message_status_title [$types [$msg_item->type] ['name']] = (isset( $message_status_title [ $types [$msg_item->type] ['name']]) ? $message_status_title [$types [$msg_item->type] ['name']] : 0) + 1;
$message_status_title = _esc ( 'You have ' ) . implode ( _esc ( ' and ' ), array_map ( function ($v, $k) {
return "$v $k";
}, array_values ( $message_status_title ), array_keys ( $message_status_title ) ) );
$this->_alerts_count = count ( $alerts );
require_once $this->getTemplatePath ( 'notification.php' );
if ($this->_alerts_count > 0)
$this->_getJavaScripts ();
}
protected function getExpertEditorTemplate() {
$help_1 = "'" . _esc ( 'Set the maximum number of messages to show in the message list.<br>When there are more messages than this number then an &#x25B2; &#x25BC; scroll<br>button will be shown. You still may read the other messages by<br>scrolling down-up the message list.' ) . "'";
$help_2 = "'" . _esc ( 'All <u>read messages</u> older than this number of days will be automatically removed.<br>To disable this option either set 0 (zero) or a big enough number (eg: 365).' ) . "'";
$help_3 = "'" . _esc ( 'By flushing the messages you remove them permanently from history.<br>That is OK if you already acknoledged their content and you do not think<br>they might be helpfull in the future. You are not alone, I often do this.' ) . "'";
$message_age = $this->settings ['message_age'];
$message_top = $this->settings ['message_top'];
require_once $this->getTemplatePath ( 'notification-expert.php' );
}
}
?>