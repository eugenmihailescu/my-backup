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
 * @date    : Tue Feb 16 21:44:02 2016 UTC $
 * @file    : webdav.php $
 * 
 * @id      : webdav.php | Tue Feb 16 21:44:02 2016 UTC | Eugen Mihailescu eugenmihailescux@gmail.com $
*/

namespace MyBackup;
?>
<tr>
<td><label for="webdav_enabled"><?php _pesc('Enabled');?></label></td>
<td><input type="checkbox" name="webdav_enabled" id="webdav_enabled"
value="1" onclick='jsMyBackup.submitOptions(this,0);'
<?php
echo $this->enabled ? 'checked' : '';
?>><input type="hidden" name="webdav_enabled" value="0"></td>
<td><label for='webdav_age'><?php _pesc('Retention time');?></label></td>
<td><input type="number" name='webdav_age' id='webdav_age'
value=<?php
echo "'" . $this->age . "'";
?> size="3"
<?php echo $this->enabled_tag; ?> min="0">days</td>
<td><a class='help' onclick=<?php echo echoHelp($help_1);?>>[?]</a></td>
</tr>
<tr>
<td><label for="webdavhost"><?php _pesc('WebDAV URL');?></label></td>
<td colspan="3"><input type="url" name=webdavhost id="webdavhost"
style='width: 100%;'
value=<?php echo "'" . $this->_webdavhost . "'"; ?>
<?php echo $this->enabled_tag; ?>></td>
<td><a class='help' onclick=<?php echo echoHelp($help_2);?>>[?]</a></td>
</tr>
<tr>
<td><label for="webdavuser"><?php _pesc('User');?></label></td>
<td><input style='width: 100%;' type="text" name='webdavuser'
id="webdavuser"
value=<?php
echo "'" . $this->_webdavuser . "'";
echo $this->enabled_tag;
?>></td>
<td style='text-align: right;'><label for="webdavpwd"><?php _pesc('Password');?></label></td>
<td><input type="password" name='webdavpwd' id="webdavpwd"
value=<?php echo "'" . $this->_webdavpwd . "'"; ?> size=20
<?php echo $this->enabled_tag;if(!(isSSL()||empty($this->_webdavpwd))) echo " style='background-color:#FF2C00;'";?>><?php echo getSSLIcon();?></td>
<td><a class='help' onclick=<?php echo echoHelp($help_3);?>> [?]</a></td>
</tr>
<tr>
<td><label for="webdav"><?php _pesc('Remote dir');?></label></td>
<td colspan="4">
<table style='width: 100%;'>
<tr>
<td width="100%"><input type="text" name='webdav' id="webdav"
style='width: 100%'
value=<?php echo "'" . stripslashes($this->root) . "'"; echo $this->enabled_tag; ?>></td>
<td align="right"><input type="button"
id='update_<?php echo $this->target_name;?>_dir' class="button"
value="<?php _pesc('Read');?>"
onclick="<?php echo $this->getRefreshFolderJS();?>"
title='<?php _pesc('Click to read this folder now');?>'
<?php echo $this->enabled_tag; ?>></td>
</tr>
</table>
</td>
</tr>