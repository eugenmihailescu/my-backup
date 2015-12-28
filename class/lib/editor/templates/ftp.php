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
 * @file    : ftp.php $
 * 
 * @id      : ftp.php | Mon Dec 28 17:57:55 2015 +0100 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
?>
<tr>
<td><label for="ftp_enabled"><?php _pesc('Enabled');?></label></td>
<td><input type="checkbox" name="ftp_enabled" id="ftp_enabled" value="1"
onclick='js56816a36b58dc.submitOptions(this,0);'
<?php
echo $this->enabled ? 'checked' : '';
?>><input type="hidden" value="0" name="ftp_enabled"></td>
<td><label for="ftp_age"><?php _pesc('Retention time');?></label></td>
<td>
<table style="width: 100%">
<tr>
<td><input type="number" name='ftp_age' id='ftp_age'
value=<?php
echo "'" . $this->age . "'";
?> size="3"
<?php echo $this->enabled_tag; ?> min="0"></td>
<td>days</td>
<td style="text-align: right"><a class='help'
onclick=<?php echo echoHelp($help_3);?>>[?]</a></td>
</tr>
</table>
</td>
</tr>
<tr>
<td><label for="ftphost"><?php _pesc('Server');?></label></td>
<td><input type="text" name=ftphost id="ftphost" style='width: 100%;'
value=<?php echo "'" . $this->_ftphost . "'"; ?>
<?php echo $this->enabled_tag;?>></td>
<td><table style="width: 100%">
<tr>
<td style='text-align: left'><a class='help'
onclick=<?php
echoHelp( $help_1 );
?>> [?]</a></td>
<td style='text-align: right;'><label for="ftpport"><?php _pesc('Port');?></label></td>
</tr>
</table></td>
<td><input style='width: 80px;' type="number" name='ftpport' id="ftpport"
value=<?php echo "'" . $this->settings ['ftpport'] . "'"; ?> size="5"
<?php echo $this->enabled_tag; ?> min="20" max="65535"> <input type="checkbox"
name="ftppasv" id="ftppasv"
<?php
echo $this->_ftppasv ? 'checked' : '';
echo $this->enabled_tag;
if ( $is_curl_ftp )
echo ' onclick="toggle_passive(this);"';
?>><input type="hidden" name="ftppasv" value="0"><label for="ftppasv"><?php _pesc('Passive mode');?></label><a
id="passive_ftp_help" class='help' onclick=<?php
echoHelp( $help_2 );
?>> [?]</a></td>
<?php if ($is_curl_ftp){?>
<td>
<table id="ftp_active_port_tbl" <?php echo $this->enabled_tag;?>>
<tr>
<td><label for="ftp_active_port"></label></td>
<td><input type="text" name="ftp_active_port" id="ftp_active_port"
value="<?php echo $this->settings ['ftp_active_port'];?>" size="5"><a id="passive_ftp_help" class='help'
onclick=<?php
echoHelp( $help_4 );
?>> [?]</a></td>
</tr>
</table>
</td>
<?php }?>
</tr>
<tr>
<td><label for="ftpuser"><?php _pesc('User');?></label></td>
<td><input style='width: 100%;' type="text" name='ftpuser' id="ftpuser"
value=<?php
echo "'" . $this->_ftpuser . "'";
echo $this->enabled_tag;
?>></td>
<td style='text-align: right;'><label for="ftppwd"><?php _pesc('Password');?></label></td>
<td><table style="width: 100%">
<tr>
<td style="width: 100%"><input type="password" name='ftppwd'
id="ftppwd" value="<?php echo $this->settings ['ftppwd'] ; ?>" size=20 style='width:100%;<?php echo $this->enabled_tag;if(!(isSSL()||empty($this->settings ['ftppwd']))) echo "background-color:#FF2C00;";?>'></td>
<td><?php echo getSSLIcon();?></td>
</tr>
</table></td>
</tr>
<tr>
<td><label for="ftp"><?php _pesc('Remote dir');?></label></td>
<td colspan="3">
<table style='width: 100%;'>
<tr>
<td width="100%"><input type="text" name='ftp' id="ftp" style='width: 100%'
value=<?php echo "'" . stripslashes($this->root) . "'"; echo $this->enabled_tag; ?>></td>
<td align="right"><input type="button"
id='update_<?php echo $this->target_name;?>_dir' class="button"
value="Read" onclick="<?php echo $this->getRefreshFolderJS();?>"
title='<?php _pesc('Click to read this folder now');?>'
<?php echo $this->enabled_tag; ?>></td>
<td><input type="button" name='exec_ftp_cmd' id="btn_remote_exec"
class="button btn_remote_exec"
title='<?php _pesc('Execute remote FTP command');?>'
onclick="<?php echo "js56816a36b58dc.popupPrompt('"._esc('Exec remote command')."','"._esc('Enter the remote FTP commands (comma-delimited)')."',null,{'"._esc('Execute')."':'js56816a36b58dc.ftpExecCmd(js56816a36b58dc.ajaxurl,\'action=ftp_exec&nonce=".wp_create_nonce_wrapper('ftp_exec')."&ftp_cmd=\' + parentNode.parentNode.parentNode.getElementsByTagName(\'INPUT\')[0].value);','"._esc('Cancel')."':null},'(eg. SYST,STAT)');";?>"
<?php echo $this->enabled_tag; ?>></td>
</tr>
</table>
</td>
</tr>