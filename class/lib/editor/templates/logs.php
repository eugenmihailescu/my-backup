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
 * @file    : logs.php $
 * 
 * @id      : logs.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;
?>
<tr>
<td><label for="check_status"><?php _pesc('Status');?></label></td>
<td>:</td>
<td id='td_job_status'><?php echo $this->_is_running[1]; ?></td>
<td><input style='width: 100%;' type="button" name="check_status"
id="check_status" value="<?php _pesc('Check');?>" class="button"
onclick=<?php echo '"'.$this->_fct_chk_status.'"';?>
title='<?php _pesc('Click to check the status now');?>'></td>
<td colspan="2"><a class='help' onclick=<?php echo echoHelp($help_1);?>>[?]</a></td>
</tr>
