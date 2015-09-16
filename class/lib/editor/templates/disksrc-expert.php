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
 * @file    : disksrc-expert.php $
 * 
 * @id      : disksrc-expert.php | Wed Sep 16 11:33:37 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;
?>
<tr>
<td><label for="excludeext"><?php _pesc('Exclude files by extension');?></label>
<a class='help' onclick=<?php echoHelp($help_1); ?>> [?]</a></td>
</tr>
<tr>
<td>
<!-- should be text[display:none] and not hidden --> <input
type="text" name="excludedirs" id="excludedirs" style="display: none">
<textarea name="excludeext" id="excludeext"
form="wpmybackup_admin_form" cols=60 rows=3><?php echo $this->settings['excludeext']; ?></textarea>
</td>
</tr>
<tr>
<td><label for="excludefiles"><?php _pesc('Exclude following files');?></label>
<a class="help" onclick=<?php echoHelp($help_2); ?>> [?]</a></td>
</tr>
<tr>
<td><textarea name="excludefiles" id="excludefiles"
form="wpmybackup_admin_form" cols=60 rows=3><?php echo $this->settings['excludefiles']; ?></textarea>
</td>
</tr>
<tr>
<td><label for="excludelinks"><?php _pesc('Exclude file links');?></label><input
type="checkbox" id="excludelinks" name="excludelinks" value="1"
<?php echo strToBool($this->settings["excludelinks"])?'checked':'';?>><input
type="hidden" id="excludelinks" name="excludelinks" value="0"><a
class="help" onclick=<?php echoHelp($help_3); ?>> [?]</a></td>
</tr>
