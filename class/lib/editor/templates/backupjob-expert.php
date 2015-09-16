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
 * @version : 0.2.2 $
 * @commit  : 23a9968c44669fbb2b60bddf4a472d16c006c33c $
 * @author  : Eugen Mihailescu <eugenmihailescux@gmail.com> $
 * @date    : Wed Sep 16 11:33:37 2015 +0200 $
 * @file    : backupjob-expert.php $
 * 
 * @id      : backupjob-expert.php | Wed Sep 16 11:33:37 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;

if (defined ( 'OPER_COMPRESS_EXTERN' ) && isWin ()) {
?>
<tr>
<td><label for="cygwin"><?php _pesc('CygWin path/params');?></label></td>
<td><input type="text" name="cygwin" id="cygwin" size="40"
value=<?php
echo "'" . $this->settings ['cygwin'] . "'";
?>><a class='help' onclick=<?php
echoHelp ( $help_1 );
?>> [?]</a></td>
</tr>
<?php
}
if (2 == $this->settings ['compression_type'] && 'extern' == $this->settings ['toolchain']) {
?>
<tr>
<td><label for="bzipver"><?php _pesc('BZIP version');?></label></td>
<td><select name="bzipver" id="bzipver"><option value="bzip2"
<?php
if ('bzip2' == $this->settings ['bzipver'])
echo 'selected';
?>>BZip2</option>
<option value="pbzip2"
<?php
if ('pbzip2' == $this->settings ['bzipver'])
echo 'selected';
?>>PBZip2</option></select><a class='help'
onclick=<?php
echoHelp ( $help_2 );
?>>[?]</a></td>
</tr>
<?php
}
if (defined ( 'CPU_THROTTLING' ) && CPU_THROTTLING && 'intern' == $this->settings ['toolchain']) {
?>
<tr>
<td><label for="cpusleep"><?php _pesc('CPU throttling (ms)');?></label></td>
<td><input type="number" name="cpusleep" id="cpusleep" size="5"
value=<?php echo "'" . $this->settings['cpusleep'] . "'"; ?>><a
class='help' onclick=<?php
echoHelp ( $help_3 );
?>>[?]</a></td>
</tr>
<?php } ?>
<tr>
<td><label for="max_exec_time"><?php _pesc('Max. execution time (s)');?></label></td>
<td><input type="number" name="max_exec_time" id="max_exec_time"
value="<?php echo $this->settings["max_exec_time"];?>"><a class='help'
onclick=<?php
echoHelp ( $help_7 );
?>>[?]</a></td>
</tr>
<tr>
<td><label for="retry"><?php _pesc('Max. retries on error');?></label></td>
<td><input type="number" name="retry" id="retry" disabled="disabled"
value=<?php echo '"'.$this->settings['retry'].'"';?>><a class='help'
onclick=<?php
echoHelp ( $help_5 );
?>>[?]</a></td>
</tr>
<tr>
<td><label for="retrywait"><?php _pesc('Retrial wait time (s)');?></label></td>
<td><input type="number" name="retrywait" id="retrywait"
disabled="disabled"
value=<?php echo '"'.$this->settings['retrywait'].'"';?>><a
class='help' onclick=<?php
echoHelp ( $help_6 );
?>>[?]</a></td>
</tr>
<tr>
<td><label for="mode"><?php _pesc('Backup mode');?></label></td>
<td><select name="mode" id="mode"><?php echo $backup_modes;?>
</select> <a class='help' onclick=<?php
echoHelp ( $help_4 );
?>>[?]</a></td>
</tr>
<tr>
<td><label for="encryption"><?php _pesc('Encrypt backup');?></label></td>
<td><select name="encryption" id="encryption"><?php echo $encryption_opts;?>
</select> <a class='help' onclick=<?php
echoHelp ( $help_8 );
?>>[?]</a></td>
</tr>
<?php if(isset($this->settings ['encryption'] )&&!empty($this->settings ['encryption'] )){?>
<tr>
<td colspan="2"><input type="button" class="button"
value="<?php _pesc('Get the encryption password');?>"
onclick="js55f93aab8f090.asyncGetContent(js55f93aab8f090.ajaxurl,'<?php
echo http_build_query ( array (
'action' => 'encryption_info',
'nonce' => wp_create_nonce_wrapper ( 'encryption_info' ) 
) );
?>',null, null, null, '<?php _esc('Encryption info');?>', false);"> <input
type="file" class="button" id="decrypt_file" name="decrypt_file[]"
accept=".enc" multiple="multiple" style="display: none"> <input
type="button" class="button" id="do_decrypt"
value="<?php _pesc('Decrypt file');?>" onclick="js55f93aab8f090.do_decrypt();"></td>
</tr>
<?php }?>
