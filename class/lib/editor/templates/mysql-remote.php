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
 * @file    : mysql-remote.php $
 * 
 * @id      : mysql-remote.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;
?>
<tr>
<td><label for="mysql_host"><?php _pesc('MySQL Host');?></label></td>
<td><input type="url" style='width: 100%' name='mysql_host'
id="mysql_host"
value=<?php echo '"'.$this->_mysql_host.'" '.$this->enabled_tag; ?>></td>
<td style='text-align: right;'><label for="mysql_port"><?php _pesc('Port');?></label></td>
<td><input type="number" style='width: 100%' name='mysql_port'
id="mysql_port"
value=<?php echo '"'.$this->_mysql_port.'" '.$this->enabled_tag; ?>></td>
</tr>
<tr>
<td><label for="mysql_user"><?php _pesc('Username');?></label></td>
<td><input type="text" style='width: 100%' name='mysql_user'
id="mysql_user"
value=<?php echo '"'.$this->_mysql_user.'" '.$this->enabled_tag; ?>></td>
<td style='text-align: right;'><label for="mysql_pwd"><?php _pesc('Password');?></label></td>
<td><input type="password" name='mysql_pwd' id="mysql_pwd"
value=<?php echo '"'.$this->_mysql_pwd.'" '.$this->enabled_tag; echo " style='".(!isSSL()?"background-color:red;":"")."'";?>><?php echo getSSLIcon();?>
</td>
</tr>
<tr>
<td><label for="mysql_db"><?php _pesc('DB name');?></label></td>
<td>
<?php
$ctrl_type = empty ( $this->_db_list ) ? 'input' : 'select';
echo "<$ctrl_type style='width: 100%' name='mysql_db' id='mysql_db'" . (empty ( $this->_db_list ) ? ' value="' . $this->_mysql_db . '"' : '') . " $this->enabled_tag onchange='js55f82caaae905.submitOptions(this,0);'>";
foreach ( $this->_db_list as $db )
echo '<option value="' . $db . '" ' . ($db == $this->_mysql_db ? 'selected' : '') . '>' . $db . '</option>';
echo "</$ctrl_type>";
?>
</td>
<?php if(defined('MYSQL_DUMP')){?>
<td style='text-align: right;'><label for="mysqldump"><?php _pesc('Use mysqldump');?></label></td>
<td><input type="checkbox" name="mysqldump" id="mysqldump"
<?php if($mysqldump)echo ' checked ';echo $this->enabled_tag;?>
onclick="document.getElementById('tables').disabled=this.checked;js55f82caaae905.submitOptions(this,0);">
<input type="hidden" name="mysqldump" value="0"><a class='help'
onclick=<?php
echoHelp ( $help_2 );
?>> [?]</a></td><?php }?>
</tr>