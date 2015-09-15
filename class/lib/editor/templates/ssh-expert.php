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
 * @file    : ssh-expert.php $
 * 
 * @id      : ssh-expert.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;
?>
<tr>
<td><label for="ssh_publickey_file">SSH public key</label></td>
<td><input type="text" id="ssh_publickey_file"
name="ssh_publickey_file"
value="<?php echo $this->settings['ssh_publickey_file'];?>"
<?php echo $this->enabled_tag;?> style="width: 100%"></td>
</tr>
<tr>
<td><label for="ssh_privkey_file">SSH private key</label></td>
<td><input type="text" id="ssh_privkey_file" name="ssh_privkey_file"
value="<?php echo $this->settings['ssh_privkey_file'];?>"
<?php echo $this->enabled_tag;?> style="width: 100%"></td>
<td><label for="ssh_privkey_pwd">SSH private key pwd</label></td>
<td><input type="password" id="ssh_privkey_pwd" name="ssh_privkey_pwd"
value="<?php echo $ssh_privkey_pwd;?>"
<?php echo $this->enabled_tag;if(!(isSSL()||empty($ssh_privkey_pwd))) echo " style='background-color:#FF2C00;'";?>><?php echo getSSLIcon();?></td>
</tr>
<?php if(defined('BANDWIDTH_THROTTLING')){?>
<tr>
<td><label for="ssh_throttle">Upload throttling</label></td>
<td><input type="number" name="ssh_throttle" id="ssh_throttle"
value="<?php echo $this->settings['ssh_throttle'];?>"
<?php echo $this->enabled_tag;?>> KiBps</td>
<td><a class='help' onclick=<?php
echoHelp ( $help_1 );
?>> [?]</a></td>
</tr>
<?php }?>
