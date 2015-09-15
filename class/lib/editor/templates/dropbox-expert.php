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
 * @file    : dropbox-expert.php $
 * 
 * @id      : dropbox-expert.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;
 if(defined('FILE_EXPLORER')){?>
<tr>
<td><label for="dropbox_root"><?php _pesc('Dropbox root');?></label></td>
<td><select name="dropbox_root" id="dropbox_root"
<?php echo $this->enabled_tag;?>><?php
foreach ( array (
'dropbox',
'sandbox' 
) as $opt )
echo '<option value=\'' . $opt . '\'' . ($opt == $this->settings ['dropbox_root'] ? ' selected' : '') . '>' . $opt . '</option>';
?></select><a class='help' onclick=<?php
echoHelp ( $help_1 );
?>> [?]</a><?php echo getSpanE(_esc('Not implemented'),'red','bold');?></td>
</tr>
<tr>
<td><label for="dropbox_direct_dwl"><?php _pesc('Direct download');?></label></td>
<td><input type="checkbox" name="dropbox_direct_dwl"
id="dropbox_direct_dwl" value='1'
<?php echo ($this->_direct_dwl?'checked':'').' '.$this->enabled_tag;?>>
<input type='hidden' name='dropbox_direct_dwl' value='0'><a
class='help' onclick=<?php echo echoHelp($help_3);?>> [?]</a></td>
</tr>
<?php
}
if (defined ( 'BANDWIDTH_THROTTLING' )) {
?>
<tr>
<td><label for="dropbox_throttle"><?php _pesc('Upload throttling');?></label></td>
<td><input type="number" name="dropbox_throttle" id="dropbox_throttle"
value="<?php echo $this->settings['dropbox_throttle'];?>"
<?php echo $this->enabled_tag;?>> KiBps<a class='help'
onclick=<?php echo echoHelp($help_2);?>> [?]</a></td>
</tr>
<?php }?>
