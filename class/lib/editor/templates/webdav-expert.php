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
 * @file    : webdav-expert.php $
 * 
 * @id      : webdav-expert.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;
?>
<tr>
<td><label for="webdav_authtype">HTTP auth type</label></td>
<td><select name="webdav_authtype" id="webdav_authtype"
<?php echo $this->enabled_tag;?>>
<?php echo $auth_options;?>
</select><a class='help'
onclick=<?php
echoHelp ( $help_4 );
?>> [?]</a></td>
</tr>
<tr>
<td><label for="webdav_cainfo">CA PEM path/file</label></td>
<td><input type="text" name="webdav_cainfo" id="webdav_cainfo"
style="width: 300px"
value="<?php echo $this->settings['webdav_cainfo'];?>"
<?php echo $this->enabled_tag;?>></td>
<td><a class='help' onclick=<?php
echoHelp ( $help_3 );
?>> [?]</a></td>
</tr>
<?php if(defined('BANDWIDTH_THROTTLING')){?>
<tr>
<td><label for="webdav_throttle">Upload throttling</label></td>
<td><input type="number" name="webdav_throttle" id="webdav_throttle"
value="<?php echo $this->settings['webdav_throttle'];?>"
<?php echo $this->enabled_tag;?>> KiBps<a class='help'
onclick=<?php echo echoHelp($help_1);?>> [?]</a></td>
</tr>
<?php
}
if (defined ( 'FILE_EXPLORER' )) {
?>
<tr>
<td><label for="webdav_direct_dwl">Direct download</label></td>
<td><input type="checkbox" name="webdav_direct_dwl"
id="webdav_direct_dwl" value='1'
<?php echo (isNull($this->settings,'webdav_direct_dwl',false)?'checked':'').' '.$this->enabled_tag;?>>
<input type='hidden' name='webdav_direct_dwl' value='0'><a
class='help' onclick=<?php echo echoHelp($help_2);?>> [?]</a><?php echo getSSLIcon();?></td>
</tr>
<?php }?>