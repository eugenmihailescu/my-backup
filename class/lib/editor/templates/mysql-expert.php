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
 * @date    : Tue Feb 16 21:44:02 2016 UTC $
 * @file    : mysql-expert.php $
 * 
 * @id      : mysql-expert.php | Tue Feb 16 21:44:02 2016 UTC | Eugen Mihailescu eugenmihailescux@gmail.com $
*/

namespace MyBackup;
?>
<tr>
<td colspan="2"><input type="checkbox" name="mysql_maint"
id="mysql_maint"
<?php
if ($enabled)
echo 'checked';
if ($this->enabled)
echo " onclick='jsMyBackup.toggle_mysql_maint(this,\"btn_mysql_maint," . implode ( ',', array_keys ( $mysql_maint_opts ) ) . "\");'";
echo $this->enabled ? '' : ' disabled';
?>><input type="hidden" name="mysql_maint" value="0"></td>
<td><label for="mysql_maint"><?php _pesc('MySQL Table maintenance');?></label><a
class='help' onclick=<?php
echoHelp ( $help_1 );
?>> [?]</a></td>
<td rowspan=<?php echo '"'.(1+count($mysql_maint_opts)).'"'; ?>><input
type="button" class="button btn_mysql_maint"
value="&nbsp;&nbsp;&nbsp;<?php _pesc('Run now');?>"
title="<?php _pesc('Run the maintenance task now');?>"
name="mysql_maint_run" onclick="jsMyBackup.run_mysql_maint();"
id="btn_mysql_maint" <?php echo $disabled;?>></td>
</tr>
<?php echo $rows;?>
<tr>
<td colspan="4">&nbsp;</td>
</tr>
<?php if(defined(__NAMESPACE__.'\\MYSQL_DUMP')){?>
<tr>
<td colspan="4"><label for="mysqldump_opts"><?php _pesc('mysqldump options');?></label><a
class='help' onclick=<?php
echoHelp ( $help_7 );
?>> [?]</a></td>
</tr>
<tr>
<td colspan="4"><textarea name="mysqldump_opts" id="mysqldump_opts"
rows="3" cols="60" form="wpmybackup_admin_form"
<?php
if (! ($this->enabled && strToBool ( $this->settings ['mysqldump'] )))
echo ' disabled';
?>></textarea></td>
</tr>
<?php }?>