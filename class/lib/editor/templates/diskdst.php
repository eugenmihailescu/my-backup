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
 * @file    : diskdst.php $
 * 
 * @id      : diskdst.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;
?>
<tr>
<td><label for="disk_enabled"><?php _pesc('Enabled');?></label></td>
<td colspan="3"><table style="width: 100%">
<tr>
<td><input type="checkbox" name="disk_enabled" id="disk_enabled"
value="1" onclick='js55f82caaae905.submitOptions(this,0);'
<?php
echo $this->enabled ? 'checked' : '';
?>><input type="hidden" name="disk_enabled" value="0"></td>
<td style="text-align: right"><label for="disk_age"><?php _pesc('Retention time');?></label></td>
<td><input type="number" name="disk_age" id="disk_age"
value=<?php
echo "'" . $this->age . "'";
?> size="3"
<?php echo $this->enabled_tag; ?> min="0"><a class='help'
onclick=<?php
echoHelp ( $help_1 );
?>>[?]</a></td>
</tr>
</table></td>
</tr>
<tr>
<td><label for="disk"><?php _pesc('Backup destination');?></label></td>
<td><input type="text" name="disk" id="disk"
value=<?php
echo "'" . $this->root . "'";
?> size=40
<?php echo $this->enabled_tag; ?>><a class='help'
onclick=<?php echo '"js55f82caaae905.popupWindow(\''._esc('Help').'\',\''._esc('The location where to save the backup.<br>Leave it empty to disable this option.').'\');"'; ?>>[?]</a></td>
<td><input type="button" id='update_disk_dir' class="button"
value="<?php _pesc('Read disk');?>"
onclick="<?php echo $this->getRefreshFolderJS();?>"
title='<?php _pesc('Click to read this folder now');?>'
<?php echo $this->enabled_tag; ?>></td>
<td><input type="button" id="btn_folder"
onclick="<?php echo $this->getHomeFolderJS ();?>"
class="btn_folder button" title="<?php echo $this->getHomeDir();?>"
<?php echo $this->enabled_tag;?>></td>
</tr>
