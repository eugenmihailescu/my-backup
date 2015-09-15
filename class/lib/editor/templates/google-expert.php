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
 * @file    : google-expert.php $
 * 
 * @id      : google-expert.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;
 if(defined('FILE_EXPLORER')){?>
<tr>
<td><label for="google_root"><?php _pesc('Google Drive root');?></label></td>
<td><input type="text" size="30" name="google_root" id="google_root"
value=<?php
echo "'" . $this->settings ['google_root'] . "'";
echo $this->enabled_tag;
?>><a class='help' onclick=<?php
echoHelp ( $help_1 );
?>> [?]</a></td>
<td><input class="button btn_edit_copy" type="button"
id="btn_edit_copy"
title="<?_pesc('Set the Google Drive root as the current folder file`s Id');?>"
onclick="document.getElementsByName('google')[0].value='/';document.getElementsByName('google_root')[0].value=/(\w)*$/.exec(document.getElementsByName('<?php echo $this->target_name;?>_path_id')[0].value)[0];document.getElementsByName('<?php echo $this->target_name;?>_path_id')[0].value=document.getElementsByName('google_root')[0].value;js55f82caaae905.submitOptions(this,0);"
name="btn_copy_path" <?php echo $this->enabled_tag;?> /></td>
<td><input class="button btn_folder" type="button" id="btn_folder"
title=<?php echo '"Set the Google Drive root as '.GOOGLE_ROOT.'"';?>
onclick=<?php echo "\"document.getElementsByName('google')[0].value='/';document.getElementsByName('google_root')[0].value='".GOOGLE_ROOT."';document.getElementsByName('".$this->target_name."_path_id')[0].value='".GOOGLE_ROOT."';js55f82caaae905.submitOptions(this,0);\"";?>
name="btn_copy_root" <?php echo $this->enabled_tag;?> /></td>
</tr>
<tr>
<td><label for="google_direct_dwl"><?php _pesc('Direct download');?></label></td>
<td><input type="checkbox" name="google_direct_dwl"
id="google_direct_dwl" value='1'
<?php echo (isNull($this->settings,'google_direct_dwl',false)?'checked':'').' '.$this->enabled_tag;?>>
<input type='hidden' name='google_direct_dwl' value='0'><a
class='help' onclick=<?php echo echoHelp($help_3);?>> [?]</a></td>
</tr>
<?php
}
if (defined ( 'BANDWIDTH_THROTTLING' )) {
?>
<tr>
<td><label for="google_throttle"><?php _pesc('Upload throttling');?></label></td>
<td colspan="3"><input type="number" name="google_throttle"
id="google_throttle"
value="<?php echo $this->settings['google_throttle'];?>"
<?php echo $this->enabled_tag;?>> KiBps<a class='help'
onclick=<?php echo echoHelp($help_2);?>> [?]</a></td>
</tr>
<?php }?>
