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
 * @version : 0.2.3-36 $
 * @commit  : c4d8a236c57b60a62c69e03c1273eaff3a9d56fb $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Thu Dec 1 04:37:45 2016 +0100 $
 * @file    : schedule-os_cron-note.php $
 * 
 * @id      : schedule-os_cron-note.php | Thu Dec 1 04:37:45 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
?>
<div id="os_cron_hint_note"
class="hintbox <?php echo $this->container_shape;?>"
style="display: none">
<table>
<tr>
<td colspan="2"><b><?php _pesc('Note');?></b></td>
</tr>
<tr>
<td>&nbsp;</td>
<td><?php printf(_esc('In order to make the OS-Cron to work you should copy the above command and use it to create a new job in your %s host system %s.'),PHP_OS,getAnchor(_esc('job scheduler'), isWin()?'http://en.wikipedia.org/wiki/Windows_Task_Scheduler':'http://en.wikipedia.org/wiki/Cron'));?></td>
</tr>
<tr>
<td>&nbsp;</td>
<td><?php printf(_esc('Read more about %s. %s'),isWin()?$this->_task_win:$this->_cron,$cpanel_note);?></td>
</tr>
</table>
</div>