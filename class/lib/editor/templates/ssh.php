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
 * @version : 0.2.2-10 $
 * @commit  : dd80d40c9c5cb45f5eda75d6213c678f0618cdf8 $
 * @author  : Eugen Mihailescu <eugenmihailescux@gmail.com> $
 * @date    : Mon Dec 28 17:57:55 2015 +0100 $
 * @file    : ssh.php $
 * 
 * @id      : ssh.php | Mon Dec 28 17:57:55 2015 +0100 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
?>
<tr>
<td><label for="ssh_enabled"><?php _pesc('Enabled');?></label></td>
<td><input type="checkbox" name="ssh_enabled" id="ssh_enabled" value="1"
onclick='js56816af34b4f1.submitOptions(this,0);'
<?php
echo $this->enabled ? 'checked' : '';
?>><input type="hidden" value="0" name="ssh_enabled"></td>
<td style="text-align: right;"><label for="ssh_age"><?php echo ('Retention time');?></label></td>
<td><table style="width: 100%">
<tr>
<td><input type="number" name='ssh_age' id='ssh_age'
value=<?php
echo "'" . $this->age . "'";
?> size="3"
<?php echo $this->enabled_tag; ?> min="0"></td>
<td><?php _pesc('days');?></td>
<td><a class='help' onclick=<?php
echoHelp( $help_1 );
?>> [?]</a></td>
</tr>
</table></td>
</tr>
<tr>
<td><label for="sshhost"><?php _pesc('Server');?></label></td>
<td><input type="text" name=sshhost id="sshhost" style="width: 100%"
value=<?php echo "'" . $this->_sshhost . "'"; ?>
<?php echo $this->enabled_tag;?>></td>
<td><table>
<tr>
<td style='text-align: left'><a class='help'
onclick=<?php
echoHelp( $help_2 );
?>> [?]</a></td>
<td style='text-align: right;'><label for="sshport"><?php _pesc('Port');?></label></td>
</tr>
</table></td>
<td><table style="width: 100%">
<tr>
<td style="text-align: left"><input style='width: 80px;' type="number"
name='sshport' id="sshport"
value=<?php echo "'" . $this->settings['sshport'] . "'"; ?> size="5"
min="20" max="65535" <?php echo $this->enabled_tag; ?>></td>
<td style="text-align: right"><label for="sshproto"><?php _pesc('Type');?></label></td>
<td><select id="sshproto" name="sshproto"><option
value="<?php echo CURLPROTO_SFTP?>"
<?php if(CURLPROTO_SFTP==$this->_sshproto)echo $selected;?>>SFTP</option>
<option value="<?php echo CURLPROTO_SCP?>"
<?php if(CURLPROTO_SCP==$this->_sshproto)echo $selected;?>>SCP</option>
</select></td>
</tr>
</table></td>
</tr>
<tr>
<td><label for="sshuser"><?php _pesc('User');?></label></td>
<td><input style='width: 100%;' type="text" name='sshuser' id="sshuser"
value=<?php
echo "'" . $this->_sshuser . "'";
echo $this->enabled_tag;
?>></td>
<td style='text-align: right;'><label for="sshpwd"><?php _pesc('Password');?></label></td>
<td><table style="width: 100%">
<tr>
<td><input type="password" name='sshpwd' id="sshpwd"
value=<?php echo "'" . $this->settings['sshpwd'] . "'"; ?> size=20 style='width:100%;<?php echo $this->enabled_tag;if(!(isSSL()||empty($this->settings['sshpwd']))) echo "background-color:#FF2C00;";?>'></td>
<td><?php echo getSSLIcon();?></td>
</tr>
</table></td>
</tr>
<tr>
<td><label for="ssh"><?php _pesc('Remote dir');?></label></td>
<td colspan="3">
<table style='width: 100%;'>
<tr>
<td width="100%"><input type="text" name='ssh' id="ssh" style='width: 100%'
value=<?php echo "'" . stripslashes($this->root) . "'"; echo $this->enabled_tag; ?>></td>
<td align="right"><input type="button"
id='update_<?php echo $this->target_name;?>_dir' class="button"
value="<?php _pesc('Read');?>"
onclick="<?php echo $this->getRefreshFolderJS();?>"
title='<?php _pesc('Click to read this folder now');?>'
<?php echo $this->enabled_tag; ?>></td>
<?php if (CURLPROTO_SFTP == $this->_sshproto){?>
<td><input type="button" name='exec_ssh_cmd' id="btn_remote_exec"
class="button btn_remote_exec"
title='<?php _pesc('Execute remote SSH command');?>'
onclick="<?php printf( "js56816af34b4f1.popupPrompt('%s','%s&lt;br&gt;%s&lt;a href=\'http://curl.haxx.se/docs/manpage.html#-q\' target=\'_blank\'&gt;%s&lt;/a&gt;%s',null,{'%s':'js56816af34b4f1.ftpExecCmd(js56816af34b4f1.ajaxurl,\'action=ftp_exec&nonce=".wp_create_nonce_wrapper('ftp_exec')."&ssh=1&ftp_cmd=\' + parentNode.parentNode.parentNode.getElementsByTagName(\'INPUT\')[0].value);','%s':null},'%s');",_esc('Execute remote SSH command'),_esc('Enter the remote SSH commands (comma-delimited)'),_esc('See also the '),_esc('Curl man-page'),_esc(' for a complete list of supported commands'),_esc('Execute'),_esc('Cancel'),_esc('(eg. mkdir <name>,rmdir <name>)'));?>"
<?php echo $this->enabled_tag; ?>></td>
<?php }?>
</tr>
</table>
</td>
</tr>