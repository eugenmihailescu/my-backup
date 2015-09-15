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
 * @file    : oauth.php $
 * 
 * @id      : oauth.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;
?>
<tr>
<td><label for=<?php echo $enabled_name;?>><?php _pesc('Enabled');?></label></td>
<td><input type="checkbox" name=<?php echo $enabled_name;?>
id=<?php echo $enabled_name;?> value='1'
onclick='js55f82caaae905.submitOptions(this,0);'
<?php
echo $this->enabled ? 'checked' : '';
?>><input type="hidden" value="0" name=<?php echo $enabled_name;?>></td>
<td><input id="unlinkacc" type="button" class="button-primary"
value="<?php _pesc('Unlink account');?>"
onclick="<?php
printf ( "js55f82caaae905.popupConfirm('%s','%s &lt;b&gt;%s&lt/b&gt; %s &lt;b&gt;%s&lt;/b&gt;?&lt;br&gt;&lt;b&gt;%s&lt;/b&gt;%s',null,{'%s':'window.location.assign(\'%s&%s_unlink\');js55f82caaae905.removePopupLast();','%s':null});", _esc ( 'Confirm' ), _esc ( 'Are you sure you want to unlink' ), WPMYBACKUP, _esc ( 'from' ), $service_name, _esc ( 'Note:' ), _esc ( 'If you want to use it later you have to reauthenticate (not that it would be a big deal, though).' ), _esc ( 'Yes, I`m pretty sure' ), $this->_stripOAuthFromURL (), $this->target_name, _esc ( 'Cancel' ) );
?>"></td>
</tr>
<tr>
<td><label for=<?php echo $service_edit_name;?>><?php echo $service_name;?> <?php _pesc('folder');?></label>
</td>
<td><input type="text" name=<?php echo $service_edit_name;?>
id=<?php echo $service_edit_name;?>
value=<?php echo "'" . $this->root . "'"; ?> size="30"
<?php echo $this->enabled_tag; if(defined('FILE_EXPLORER')) echo ' readonly';?>><a
class='help'
onclick=<?php printf( '"js55f82caaae905.popupWindow(\'%s\',\'%s\');"',_esc('Help'),sprintf(_esc('The %s folder where to upload the backup'),$service_name));?>>[?]</a></td>
<td><input style='width: 100%;' type="button"
id=<?php echo "'update_".$this->target_name."_dir'";?> class="button"
value="<?php _pesc('Read folder');?>"
onclick="<?php echo $this->getRefreshFolderJS();?>"
title='<?php _pesc('Click to read this folder now');?>'
<?php echo $this->enabled_tag; ?>></td>
</tr>
<tr>
<td><label for=<?php echo $service_age_name;?>><?php _pesc('Retention time');?></label></td>
<td colspan=2><input type="number" name=<?php echo $service_age_name;?>
id=<?php echo $service_age_name;?>
value=<?php
echo "'" . $this->settings [$this->target_name . '_age'] . "'";
?>
size="3" <?php echo $this->enabled_tag; ?> min="0"><a class='help'
onclick=<?php printf('"js55f82caaae905.popupWindow(\'%s\',\'%s\');"',_esc('Help'),sprintf(_esc('Keep only the last n-days backups on %s'),$service_name));?>
min="0">[?]</a></td>
</tr>