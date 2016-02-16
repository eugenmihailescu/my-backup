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
 * @file    : backup2mail-expert.php $
 * 
 * @id      : backup2mail-expert.php | Tue Feb 16 15:27:30 2016 +0100 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
?>
<tr>
<td><label for="backup2mail_smtp"><?php _pesc('Use PEAR Mail');?></label></td>
<td><input type="checkbox" name="backup2mail_smtp" id="backup2mail_smtp"
value="1" <?php echo $backup2mail_smtp?'checked':'';?>> <input type="hidden"
name="backup2mail_smtp" value="0"> <a class='help'
onclick=<?php
echoHelp( $help_4 );
?>>[?]</a></td>
</tr>
<tr>
<td><label for="backup2mail_backend"><?php _pesc('E-mail backend');?></label></td>
<td><select name="backup2mail_backend" id="backup2mail_backend"
<?php echo $backup2mail_opts_disabled;?>>
<?php echo $backend_options;?>
</select> <a class='help' onclick=<?php
echoHelp( $help_5 );
?>>[?]</a></td>
</tr>
<tr>
<td><label for="backup2mail_host"><?php _pesc('SMTP host');?></label></td>
<td><input type="text" name="backup2mail_host" id="backup2mail_host"
value="<?php echo $this->settings['backup2mail_host'];?>"
<?php echo $backup2mail_opts_disabled;?>><a class='help'
onclick=<?php
echoHelp( $help_6 );
?>>[?]</a></td>
</tr>
<tr>
<td><label for="backup2mail_port"><?php _pesc('SMTP port');?></label></td>
<td><input type="number" name="backup2mail_port" id="backup2mail_port"
value="<?php echo $this->settings['backup2mail_port'];?>"
<?php echo $backup2mail_opts_disabled;?> min="20" max="65535" size="5"><a
class='help' onclick=<?php
echoHelp( $help_7 );
?>>[?]</a></td>
</tr>
<tr>
<td><label for="backup2mail_auth"><?php _pesc('SMTP authentication');?></label></td>
<td><input type="checkbox" name="backup2mail_auth" id="backup2mail_auth"
value="1"
<?php echo strToBool($this->settings['backup2mail_auth'])?'checked':'';echo ' '. $backup2mail_opts_disabled;?>><input
type="hidden" name="backup2mail_auth" value="0"> <a class='help'
onclick=<?php
echoHelp( $help_8 );
?>>[?]</a></td>
</tr>
<tr>
<td><label for="backup2mail_user"><?php _pesc('SMTP user');?></label></td>
<td><input type="text" name="backup2mail_user" id="backup2mail_user"
value="<?php echo $this->settings['backup2mail_user'];?>"
<?php echo $backup2mail_auth_disabled;?>><a class='help'
onclick=<?php
echoHelp( $help_9 );
?>>[?]</a></td>
</tr>
<tr>
<td><label for="backup2mail_pwd"><?php _pesc('SMTP password');?></label></td>
<td><input type="password" name="backup2mail_pwd" id="backup2mail_pwd"
<?php echo $backup2mail_auth_disabled;echo ' value="'.$this->settings['backup2mail_pwd'].'"'; if(!(isSSL()||empty($this->settings['backup2mail_pwd']))) echo " style='background-color:#FF2C00;'";?>><a
class='help' onclick=<?php
echoHelp( $help_10 );
?>>[?]</a><?php echo getSSLIcon();?></td>
</tr>