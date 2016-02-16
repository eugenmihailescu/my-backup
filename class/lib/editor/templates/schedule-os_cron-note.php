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
 * @file    : schedule-os_cron-note.php $
 * 
 * @id      : schedule-os_cron-note.php | Tue Feb 16 21:41:51 2016 UTC | Eugen Mihailescu eugenmihailescux@gmail.com $
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