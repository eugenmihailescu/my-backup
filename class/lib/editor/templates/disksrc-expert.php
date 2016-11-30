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
 * @version : 0.2.3-34 $
 * @commit  : 433010d91adb8b1c49bace58fae6cd2ba4679447 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Wed Nov 30 15:38:35 2016 +0100 $
 * @file    : disksrc-expert.php $
 * 
 * @id      : disksrc-expert.php | Wed Nov 30 15:38:35 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
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
<?php if(BACKUP_MODE_FULL==$this->settings['mode']){?>
<tr>
<td>
<table style="width: 100%">
<tr>
<td><label for=use_cache_preload><?php _pesc('Use a cache preloader');?></label>
<input type="checkbox" id="use_cache_preload" name="use_cache_preload"
value="1"
<?php echo strToBool($this->settings["use_cache_preload"])?'checked':'';?>>
<input type="hidden" id="use_cache_preload" name="use_cache_preload"
value="0"> <a class="help" onclick=<?php echoHelp($help_5); ?>> [?]</a></td>
<td><label for="cache_preload_age"><?php _pesc('Cache preloader age');?></label></td>
<td><input type="number" id="cache_preload_age" name="cache_preload_age"
value="<?php echo $this->settings['cache_preload_age'];?>" min="60"
max="1440"><?php echo ' '._pesc('min');?><a class="help"
onclick=<?php echoHelp($help_6); ?>> [?]</a></td>
</tr>
</table>
</td>
</tr>
<?php }?>