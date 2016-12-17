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
 * @file    : disksrc.php $
 * 
 * @id      : disksrc.php | Tue Dec 13 06:40:49 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
?>
<tr>
<td><label for="dir_show_size"><?php _pesc('Show file size');?></label></td>
<td><input type='checkbox' name='dir_show_size' id="dir_show_size" value='1'
onclick="<?php echo $show_file_size_toggle;?>"
<?php
echo $this->_dir_show_size ? 'checked="true"' : '';
?>><a class='help' onclick=<?php echoHelp($help_1); ?>>[?]</a><input
type="hidden" name="dir_show_size" value="0"></td>
<?php
if ( $this->_dir_show_size ) {
?>
<td><input type="button" name='btn_disk_cache' class="button"
value="<?php _pesc('Clear cache');?>"
onclick="<?php echo $clear_cache_click;?>"
title="<?php _pesc('Click to read this folder now');?>"><input type="hidden"
name="clear_disk_cache"></td>
<td style="color: #bbb;"><?php
echo getHumanReadableSize( getDirCacheSize() );
?></td>
<?php } ?>																																																																																																																													 
</tr>
<tr>
<td><label for="dir"><?php _pesc('Backup directory');?></label></td>
<td><input type="text" name="dir" id="dir"
value=<?php echo "'" . $this->root . "'"; ?> size=40
<?php echo $this->_readonly;?>><a class='help'
onclick=<?php
echo '"jsMyBackup.popupWindow(\'' . _esc( 'Help' ) . '\',\'' . sprintf( 
_esc( 'The root directory to backup. When left empty then<br><b>%s</b>' ), 
addslashes( WPMYBACKUP_ROOT ) ) . '\');"';
?>>[?]</a></td>
<td><input type="button" style='width: 100%' name='btn_wpmybackup_dir'
class="button" value="<?php _pesc('Read disk');?>"
onclick="<?php echo $reload_file_list;?>"
title='<?php _pesc('Click to read this folder now');?>'></td>
<?php if($this->_show_dir_buttons){?>
<td><input type="button" name="folder_home"
id="<?php echo $this->is_wp?'btn_wp_folder':'btn_folder';?>"
onclick="<?php
echo 'document.getElementById(\'dir\').value=\'' . addslashes( WPMYBACKUP_ROOT ) . '\';' . $reload_file_list;
?>"
class="<?php echo 'button '.($this->is_wp?'btn_wp_folder':'btn_folder');?>"
title=<?php
echo '\'' . WPMYBACKUP_ROOT . '\'';
?>></td>
<?php } if($this->is_wp &&$this->_show_dir_buttons){$plugin_dir=ROOT_PATH;;?>
<!-- <td>
<input type="button" name="folder_plugin" id="btn_plugin"
onclick="<?php
echo 'document.getElementsByName(\'dir\')[0].value=\'' . addslashes( $plugin_dir ) . '\';' .
$reload_file_list;
?>"
class="button" title="<?php echo $plugin_dir;?>"></td> -->
<?php }?>
</tr>