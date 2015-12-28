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
 * @file    : schedule-os_cron.php $
 * 
 * @id      : schedule-os_cron.php | Mon Dec 28 17:57:55 2015 +0100 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
?>
<tr>
<td style="width: 0px"></td>
<td colspan="2" style="white-space: nowrap;"><input type="radio"
id="schedule_grp_os_cron" name="schedule_grp" value="os_cron"
onclick="js56816a36b58dc.toggle_wp_cron(false);"
<?php
if ('os_cron' == $this->settings ['schedule_grp'])
echo 'checked';
?>><label for="schedule_grp_os_cron"><?php _pesc('Schedule by OS-Cron');?></label>
<a class='help'
title="<?php _pesc('You should define a schedule/cron task in your OS with a command like the one shown by this option');?>"
onclick="js56816a36b58dc.popupWindow('<?php _pesc('CLI usage');?>',js56816a36b58dc.globals.help,700,null,null,null,true);">[?]</a></td>
</tr>
<tr id="schedule_cron_row3">
<td colspan="3"><div
class='hintbox <?php echo $this->container_shape;?>'
id="os_cron_hint" style='display: none; background-color: #f0f0f0'
onmouseover="js56816a36b58dc.showClipboardBtn(this,'visible','os_cron_clpb');"
onmouseout="js56816a36b58dc.showClipboardBtn(this,'hidden','os_cron_clpb');"></div>
<img id="os_cron_clpb"
src="<?php echo $this->getImgURL ( 'edit-copy-32.png' ) ;?>"
style="position: relative; float: right; right: 5px; visibility: hidden; cursor: pointer;"
onmouseover="this.style.visibility='visible'"
onclick="<?php echo sprintf("js56816a36b58dc.popupPrompt('%s','%s', null,{'%s':null},js56816a36b58dc.stripHelpLink('os_cron_hint'),'textarea');",_esc('Compatibility-mode copy'),_esc('Copy to clipboard: Ctrl+C, ESC (will strip the HTML tags :-)'),_esc('Close (ESC)'));?>"
title='<?php _pesc('Click to copy to clipboard');?>'></td>
</tr>