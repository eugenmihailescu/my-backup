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
 * @file    : dashboard.php $
 * 
 * @id      : dashboard.php | Tue Feb 16 21:41:51 2016 UTC | Eugen Mihailescu eugenmihailescux@gmail.com $
*/

namespace MyBackup;
?>
<tr>
<td colspan="3"><span id="job_info_title"
style="font-size: 1.3em; font-weight: 600"></span></td>
<td style="width: 100%" rowspan="10">
<div style="text-align: center; margin-bottom: 10px;">
<?php
echo _esc( 'Upload and restore an external/custom backup archive' );
echo ' (' . preg_replace( 
'/(href=)([\'"])([^\2]+?)(\2.*>)([^<]+)/', 
'\1\2\3#wp_full_restore_extern\4' . _esc( 'Read more' ), 
getTabAnchorByConstant( 'APP_WELCOME' ) ) . ')';
?>
</div>
<div id="drag_error" style="display: none;"></div>
<div class="restore_drag_container">
<?php
$btn = sprintf( 
'<input style="vertical-align:middle;" type="button" class="button button-choose-file" value="%s" onclick="jsMyBackup.uploader_obj.upload_select_files();">', 
_esc( 'select' ) );
$btn .= sprintf( 
'<input id="select_file_dialog" type="file" style="display:none" multiple="multiple" accept="%s">', 
'.' . implode( ',.', $COMPRESSION_NAMES ) );
printf( 
_esc( 'Drag & drop or %s a %s archive here. %s file size : %s' ), 
$btn, 
'.' . implode( '|.', $COMPRESSION_NAMES ), 
preg_replace( '/(?=)post_max_size/', _esc( 'Max' ), $this->_upload_constraint_link[1] ), 
getSpan( _esc( 'unknown' ), null, null, null, false, 'upload_max_size' ) );
?>
<table class="restore_drag_filelist"></table>
<div id="upload_restore_toolbar">
<input id="upload_restore_now"
style="display: none; margin-left: auto; margin-right: auto" type="button"
class="button-primary" onclick="<?php echo $on_restore_click1;?>">
</div>
</div>
</td>
</tr>
<tr>
<td colspan="3" style="vertical-align: top;">
<table class="<?echo $this->container_shape;?>">
<tr>
<td><label><?php _pesc('Date');?></label></td>
<td id="job_info_start"></td>
</tr>
<tr>
<td><label><?php _pesc('Status');?></label></td>
<td id="job_info_status"></td>
</tr>
<tr>
<td><label><?php _pesc('State');?></label></td>
<td id="job_info_state"></td>
</tr>
<tr>
<td><label><?php _pesc('Mode');?></label></td>
<td id="job_info_mode"></td>
</tr>
<tr>
<td><label><?php _pesc('Size');?></label></td>
<td id="job_info_size"></td>
</tr>
<tr>
<td><label><?php _pesc('Includes');?></label></td>
<td id="job_info_source"></td>
</tr>
<tr>
<td><label><?php _pesc('Location');?></label></td>
<td id="job_info_location"></td>
</tr>
<tr>
<td><label><?php _pesc('Next schedule');?></label></td>
<td><?php echo $next_schedule;?></td>
</tr>
</table>
</td>
</tr>
<tr>
<td><input type="button" class="button-primary"
value="<?php echo _esc('Run Backup');?>"
onclick="<?php echo $on_backup_click;?>"></td>
<td><input type="button" class="button-primary"
value="<?php echo _esc('Restore Backup');?>"
onclick="<?php echo $on_restore_click;?>" id="btn_restore_backup"
<?php echo $restore_disable;?>></td>
<td><input type="button" class="button-primary" id="btn_view_log"
value="<?php echo _esc('View Log');?>"
onclick="<?php echo $on_viewlog_click;?>"></td>
</tr>