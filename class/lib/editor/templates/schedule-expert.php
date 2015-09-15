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
 * @file    : schedule-expert.php $
 * 
 * @id      : schedule-expert.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;

if ($this->_is_wpcron_disabled) {
?>
<tr>
<td><input type="checkbox" id="schedule_wpcron_force"
name="schedule_wpcron_force" value="1"
<?php
echo $this->enabled_tag;
if (strToBool ( $this->settings ['schedule_wpcron_force'] ))
echo ' checked';
?>
style="width: 100%"><input type="hidden" name="schedule_wpcron_force"
value="0"></td>
<td><label for="schedule_wpcron_force"><?php _pesc('Set DISABLE_WP_CRON=false');?></label></td>
<td><a class='help' onclick=<?php
echoHelp ( $help_1 );
?>> [?]</a></td>
</tr>
<?php }?>
<tr>
<td><input type="checkbox" id="schedule_wpcron_alt"
name="schedule_wpcron_alt" value="1"
<?php
echo $this->enabled_tag;
if (strToBool ( $this->settings ['schedule_wpcron_alt'] ))
echo ' checked'?>
style="width: 100%"><input type="hidden" name="schedule_wpcron_alt"
value="0"></td>
<td><label for="schedule_wpcron_alt"><?php _pesc('Set ALTERNATE_WP_CRON=true');?></label></td>
<td><a class='help'
onclick=<?php
echoHelp ( $help_2 );
?>> [?]</a></td>
</tr>
