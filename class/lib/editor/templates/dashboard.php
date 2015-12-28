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
 * @version : 0.2.2-10 $
 * @commit  : dd80d40c9c5cb45f5eda75d6213c678f0618cdf8 $
 * @author  : Eugen Mihailescu <eugenmihailescux@gmail.com> $
 * @date    : Mon Dec 28 17:57:55 2015 +0100 $
 * @file    : dashboard.php $
 * 
 * @id      : dashboard.php | Mon Dec 28 17:57:55 2015 +0100 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
?>
<tr>
<td colspan="3"><span id="job_info_title"
style="font-size: 1.3em; font-weight: 600"></span></td>
</tr>
<tr>
<td colspan="3">
<table class="<?echo $this->container_shape;?>">
<tr>
<td><label><?php _pesc('Date');?></label></td>
<td id="job_info_start"></td>
</tr>
<tr>
<td><label><?php _pesc('Status');?></label></td>
<td id="job_info_status"></td>
</tr>
<tr>
<td><label><?php _pesc('State');?></label></td>
<td id="job_info_state"></td>
</tr>
<tr>
<td><label><?php _pesc('Mode');?></label></td>
<td id="job_info_mode"></td>
</tr>
<tr>
<td><label><?php _pesc('Size');?></label></td>
<td id="job_info_size"></td>
</tr>
<tr>
<td><label><?php _pesc('Includes');?></label></td>
<td id="job_info_source"></td>
</tr>
<tr>
<td><label><?php _pesc('Location');?></label></td>
<td id="job_info_location"></td>
</tr>
<tr>
<td><label><?php _pesc('Next schedule');?></label></td>
<td><?php echo $next_schedule;?></td>
</tr>
</table>
</td>
</tr>
<tr>
<td><input type="button" class="button-primary"
value="<?php echo _esc('Run Backup');?>"
onclick="<?php echo $on_backup_click;?>"></td>
<td><input type="button" class="button-primary"
value="<?php echo _esc('Restore Backup');?>"
onclick="<?php echo $on_restore_click;?>" id="btn_restore_backup"
<?php echo $restore_disable;?>></td>
<td><input type="button" class="button-primary" id="btn_view_log"
value="<?php echo _esc('View Log');?>"
onclick="<?php echo $on_viewlog_click;?>"></td>
</tr>