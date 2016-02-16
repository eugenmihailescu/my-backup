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
 * @file    : disksrc-expert.php $
 * 
 * @id      : disksrc-expert.php | Tue Feb 16 21:41:51 2016 UTC | Eugen Mihailescu eugenmihailescux@gmail.com $
*/

namespace MyBackup;
 if(defined(__NAMESPACE__.'\\PCLZIP')){?>
<tr>
<td><label for="nocompress"><?php _pesc('Do not compress files by extension (PclZip only)');?></label>
<a class='help' onclick=<?php echoHelp($help_4); ?>> [?]</a></td>
</tr>
<tr>
<td><textarea name="nocompress" id="nocompress" class="files_excludes"
form="wpmybackup_admin_form" cols=60 rows=3><?php echo $this->settings['nocompress']; ?></textarea>
</td>
</tr>
<?php }?>
<tr>
<td><label for="excludeext"><?php _pesc('Exclude files by extension');?></label>
<a class='help' onclick=<?php echoHelp($help_1); ?>> [?]</a></td>
</tr>
<tr>
<td>
<!-- should be text[display:none] and not hidden --> <input type="text"
name="excludedirs" id="excludedirs" style="display: none"> <textarea
name="excludeext" id="excludeext" class="files_excludes"
form="wpmybackup_admin_form" cols=60 rows=3><?php echo $this->settings['excludeext']; ?></textarea>
</td>
</tr>
<tr>
<td><label for="excludefiles"><?php _pesc('Exclude following files');?></label>
<a class="help" onclick=<?php echoHelp($help_2); ?>> [?]</a></td>
</tr>
<tr>
<td><textarea name="excludefiles" id="excludefiles" class="files_excludes"
form="wpmybackup_admin_form" cols=60 rows=3><?php echo $this->settings['excludefiles']; ?></textarea>
</td>
</tr>
<tr>
<td><label for="excludelinks"><?php _pesc('Exclude file links');?></label><input
type="checkbox" id="excludelinks" name="excludelinks" value="1"
<?php echo strToBool($this->settings["excludelinks"])?'checked':'';?>><input
type="hidden" id="excludelinks" name="excludelinks" value="0"><a class="help"
onclick=<?php echoHelp($help_3); ?>> [?]</a></td>
</tr>