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
 * @date    : Tue Feb 16 21:41:51 2016 UTC $
 * @file    : dropbox-expert.php $
 * 
 * @id      : dropbox-expert.php | Tue Feb 16 21:41:51 2016 UTC | Eugen Mihailescu eugenmihailescux@gmail.com $
*/

namespace MyBackup;
 if(defined(__NAMESPACE__.'\\FILE_EXPLORER')){?>
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
if (defined ( __NAMESPACE__.'\\BANDWIDTH_THROTTLING' )) {
?>
<tr>
<td><label for="dropbox_throttle"><?php _pesc('Upload throttling');?></label></td>
<td><input type="number" name="dropbox_throttle" id="dropbox_throttle"
value="<?php echo $this->settings['dropbox_throttle'];?>"
<?php echo $this->enabled_tag;?>> KiBps<a class='help'
onclick=<?php echo echoHelp($help_2);?>> [?]</a></td>
</tr>
<?php }?>