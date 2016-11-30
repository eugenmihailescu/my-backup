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
 * @version : 0.2.3-33 $
 * @commit  : 8322fc3e4ca12a069f0821feb9324ea7cfa728bd $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Tue Nov 29 16:33:58 2016 +0100 $
 * @file    : notification-functions.php $
 * 
 * @id      : notification-functions.php | Tue Nov 29 16:33:58 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
function echoMessageList( $method, $settings = null ) {
$get_scrollbar_row = function ( $i, $count, $max_top ) {
$percent = $i * 100 / $count;
return ceil( $percent * ( $max_top - 2 ) / 100 );
};
$show_scrollbar = true; 
$log = MESSAGES_LOGFILE;
$alert_message_obj = new MessageHandler( $log );
if ( isset( $method['msg_id'] ) ) {
if ( in_array( $method['msg_id'], array( 'read', 'unread' ) ) )
$alert_message_obj->setReadStatus( $method['msg_id'] );
elseif ( false !== ( $message = $alert_message_obj->getMessageById( $method['msg_id'] ) ) )
$alert_message_obj->setReadStatus( 
$message->msg_id, 
MESSAGE_ITEM_UNREAD == $message->status ? MESSAGE_ITEM_READ : MESSAGE_ITEM_UNREAD );
}
$types = $alert_message_obj->getMessageTypes();
$icons = array( 
MESSAGE_TYPE_NORMAL => 'img/msg_normal.png', 
MESSAGE_TYPE_WARNING => 'img/msg_warning.png', 
MESSAGE_TYPE_ERROR => 'img/msg_error.png' );
$alerts = $alert_message_obj->getMessagesByKeys( 
array( 'status', 'type' ), 
array( 
isset( $method['show'] ) ? $method['show'] : MESSAGE_ITEM_UNREAD, 
array( MESSAGE_TYPE_NORMAL, MESSAGE_TYPE_WARNING, MESSAGE_TYPE_ERROR ) ) );
$count = count( $alerts );
$max_top = $settings['message_top'];
$row_slider_offset = isset( $method['row_id'] ) ? intval( $method['row_id'] ) : 0;
$offset = isset( $method['pos'] ) ? intval( $method['pos'] ) : 0;
$offset += isset( $method['dir'] ) ? intval( $method['dir'] ) : 0;
if ( $row_slider_offset > 0 )
$offset = ceil( ( $row_slider_offset - 1 ) * $count / $max_top );
if ( $offset > $count - $max_top && $count - $max_top >= 0 )
$offset = $count - $max_top;
echo "<table id='message_list_tbl' style='width: 100%;border-spacing:0;margin-top:2px;current_offset:$offset'>";
ob_start();
$i = 0;
$last_ref_id = null;
foreach ( $alerts as $msg_id => $msg_item ) {
if ( $i >= $offset ) {
echo '<tr id="' . $msg_id . '">';
$history_link = defined( __NAMESPACE__.'\\APP_JOB_HISTORY' ) ? replaceUrlParam( 
$_SERVER['HTTP_REFERER'], 
'tab', 
'history&job_id=' . $msg_item->ref_id ) : '';
echo ( $new_job = ( $msg_item->ref_id !== $last_ref_id ) ) ? ( '<th><a href="' . $history_link . '">' .
( $last_ref_id = $msg_item->ref_id ) . '</a></th>' ) : '<td></td>';
echo '<td ' . ( $new_job ? 'class="_mlt"' : '' ) . '><img src="' .
plugins_url_wrapper( $icons[$msg_item->type], IMG_PATH ) . '"></td>';
$style = array();
if ( ! empty( $types[$msg_item->type]['fg'] ) )
$style[] = 'color:' . $types[$msg_item->type]['fg'];
if ( ! empty( $types[$msg_item->type]['bg'] ) )
$style[] = 'background-color:' . $types[$msg_item->type]['bg'];
$style = implode( ';', $style );
$percent = $i * 100 / $count;
$slider_row = $get_scrollbar_row( $i, $count, $max_top );
echo '<td' . ( $new_job ? ' class="_mlt"' : '' ) . ( empty( $style ) ? '' : " style=\"$style\"" ) . ">". $msg_item->text . '</td>';
if ( $count <= $max_top || ! $show_scrollbar && $i - $offset > 1 && $i - $offset < $max_top - 1 ) {
$i++;
echo '</tr>';
continue;
}
$draw_slider = false;
$rowspan = ( ! $show_scrollbar && $offset + 1 == $i ) ? "rowspan='" . ( $max_top - 2 ) . "'" : '';
$arrow = $offset == $i ? '&#x25B2;' : ( $i - $offset == $max_top - 1 ? '&#x25BC;' : '' );
$show_arrow = $i > 0 && $i  < $count - 1;
$style = '';
$onclick = '';
$bg = '';
$tooltip = _esc( 'Click to scroll the slider here' );
$inner_html = $show_arrow ? $arrow : '';
if ( empty( $arrow ) && $show_scrollbar && $i - $offset == $slider_row &&
$i + 1 - $offset != $get_scrollbar_row( $i + 1, $count, $max_top ) ) {
$draw_slider = true;
$bg = ';background-color:#00ADEE';
$tooltip = _esc( 'Click the scrollbar to move the slider' );
} else 
if ( $show_arrow && ! empty( $arrow ) )
$onclick = "onclick='jsMyBackup.messages_scroll(" . ( $offset == $i ? '-1' : ( $i > $count ? '0' : '1' ) ) .
");' onmousedown='var sender=this;jsMyBackup.globals.slider_down=setInterval(sender.onclick,200);' onmouseup='clearInterval(jsMyBackup.globals.slider_down);'";
elseif ( $count > $max_top && $i != $offset && ! $draw_slider )
$style .= 'row_id:' . ( $i - $offset );
$style = "$style$bg";
if ( ! empty( $style ) )
$style = "style='$style'";
echo "<td $rowspan $style $onclick title='$tooltip'>$inner_html</td>";
echo '</tr>';
}
if ( $i - $offset > $max_top - 2 )
break;
$i++;
}
$buffer = ob_get_contents();
if ( empty( $buffer ) )
echo ( $buffer = sprintf( '<tr><td>%s</td></tr>', _esc( 'No item found :-(' ) ) );
flush();
if ( ob_get_level() > 0 )
@ob_end_flush();
echo '</table>';
}
function echoMessageDetail( $method) {
$log = MESSAGES_LOGFILE;
$alert_message_obj = new MessageHandler( $log );
$fill_bug_report = sprintf( 
_esc( "If that`s not the case and you suspect a software bug then please %s." ), 
"<a href='" . getReportIssueURL() . "'>" . _esc( 'fill a report' ) . "</a>" );
if ( intval( $method['msg_id'] ) > 0 ) {
if ( false === ( $msg_item = $alert_message_obj->getMessageById( $method['msg_id'] ) ) )
die( 
sprintf( 
_esc( "No detail found. That`s rather odd! Please try again by %s.<br>%s" ), 
"<a href='#' onclick='location.reload(true);'>" . _esc( 'reloading the page' ) . "</a>", 
$fill_bug_report ) );
} else
return; 
$types = $alert_message_obj->getMessageTypes();
$help_1 = "'" .
_esc( 
"Normally here you should have the job id that had generated this message.<br>My guess is that the job history was flushed so this record does not contain<br>anything about this event." ) .
"<br>" . _esc( 'However, there might exist alerts which are not related to backup/restore jobs. If that is the case then this is normal.' ) .
'<br>' . str_replace( "'", "\\'", $fill_bug_report ) . "'";
$help_2 = "'" . sprintf( 
_esc( 
"A message is marked as read/unread only when you explicitly click the<br>icon (eg: %s) that precedes the message.<br>Note that the messages that are read are not deleted permanently. Instead<br>they are filtered out such that you can find them in <i>read messages</i>." ), 
"<img src=\\'" . plugins_url_wrapper( 'img/msg_error.png', IMG_PATH ) . "\\'>" ) . "'";
$style = array();
if ( ! empty( $types[$msg_item->type]['fg'] ) )
$style[] = '
color:' . $types[$msg_item->type]['fg'];
if ( ! empty( $types[$msg_item->type]['bg'] ) )
$style[] = 'background-color:' . $types[$msg_item->type]['bg
'];
$style = implode( ';', $style );
$ref_id = $msg_item->ref_id;
?>
<table class="files history">
<tr>
<th><?php _pesc('Message Id');?></th>
<th><?php _pesc('Job Id');?></th>
<th><?php _pesc('Date/Time');?></th>
<th><?php _pesc('Type');?></th>
<th><?php _pesc('Status');?></th>
</tr>
<tr>
<td><?php echo $msg_item->msg_id;?></td>
<td><?php echo empty($ref_id)?'<a class="help" onclick='.getHelpCall($help_1).'>[?]</a>':'<a href="'.(defined(__NAMESPACE__.'\\APP_JOB_HISTORY')?addUrlParams($_SERVER ['HTTP_REFERER'], array('tab'=>'history', 'job_id'=>$ref_id)):'').'">'.$ref_id.'</a>';?></td>
<td><?php echo date ( DATETIME_FORMAT, $msg_item->timestamp );?></td>
<td style="<?php echo $style;?>"><?php echo $types[$msg_item->type]['name'];?></td>
<td style="text-align: center"><a class="help"
onclick="<?php echo getHelpCall($help_2,false);?>"><?php echo $msg_item->status ? 'read' : 'unread'; ?></a></td>
</tr>
</table>
<?php
}
function flushMessageList() {
$log = MESSAGES_LOGFILE;
$result = @unlink( $log );
echo $result;
}
?>