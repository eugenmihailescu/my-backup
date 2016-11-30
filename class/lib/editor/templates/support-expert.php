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
 * @file    : support-expert.php $
 * 
 * @id      : support-expert.php | Wed Nov 30 15:38:35 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
?>
<tr>
<td><label for="whitespace_check"><?php echo _esc('Extra-whitespace check');?></label></td>
<td><input id="whitespace_check" name="whitespace_check" type="number" size="5"
min="0" value="<?php echo $this->settings['whitespace_check'];?>"><?php echo _esc('min');?><a
class="help" onclick=<?php echoHelp( $help_whitespace );?>> [?]</a></td>
</tr>