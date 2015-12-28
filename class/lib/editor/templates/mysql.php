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
 * @file    : mysql.php $
 * 
 * @id      : mysql.php | Mon Dec 28 17:57:55 2015 +0100 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
?>
<tr>
<td><label for="mysql_enabled"><?php _pesc('Enabled');?></label></td>
<td><input type="checkbox" name="mysql_enabled" id="mysql_enabled" value="1"
onclick='js56816a36b58dc.submitOptions(this,0);'
<?php
echo $this->enabled ? 'checked' : '';
?>><input type="hidden" name="mysql_enabled" value="0"></td>
<td style="text-align: right;"><label for="mysql_format"><?php _pesc('Format');?></label></td>
<td><select name="mysql_format" id="mysql_format"
<?php echo $this->enabled_tag; ?>> 
<?php
$format_options = array (
'sql' => 'SQL script',
'xml' => 'phpMyAdmin XML' 
);
foreach ( $format_options as $format => $caption )
printf ( '<option value="%s"%s>%s</option>', $format, $format == $mysql_format ? ' selected="selected"' : '', $caption );
?>
</select><a class='help' onclick=<?php echoHelp($help_3); ?>>[?]</a></td>
</tr>
<?php
if (false !== ($mysql_remote = $this->getTemplatePath ( 'mysql-remote.php', null, true )))
include_once $mysql_remote;
?>
<tr>
<td <?php if ($mysqldump)echo'style="visibility:hidden"';?>><label for="tables"><?php _pesc('Backup tables');?></label></td>
<td colspan="2" <?php if ($mysqldump)echo'style="visibility:hidden"';?>>
<?php echo $prefix_html;?>
<input type="text" name="tables" id="tables"
value=<?php echo "'" . $this->settings['tables'] . "'"; ?> size="40"
<?php echo $this->enabled_tag; ?>><a class='help'
onclick=<?php echoHelp($help_1); ?>>[?]</a>
</td>
<td><input type="button" name='dwl_mysql_script' class="button"
id="btn_dwl_script"
value=<?php echo '"&nbsp;&nbsp;&nbsp;'._esc('Download').' '.($mysqldump?_esc('db dump'):($mysql_format.' '._esc('file'))).'"';?>
onclick=<?php
echo '"js56816a36b58dc.post(js56816a36b58dc.ajaxurl,{action:\'dwl_sql_script\',nonce:\'' . wp_create_nonce_wrapper ( 'dwl_sql_script' ) . '\',tables:\'' . $this->settings ['tables'] . '\',name:\'' . (isset ( $this->settings ['name'] ) && ! empty ( $this->settings ['name'] ) ? $this->settings ['name'] : $this->settings ['url']) . '\',type:' . $this->settings ['compression_type'] . ',level:' . $this->settings ['compression_level'] . '});"';
?>
title='<?php _pesc('Click to download the MySQL script now');?>'
<?php echo $this->enabled_tag; ?>><a class='help'
onclick=<?php echoHelp($help_4); ?>>[?]</a></td>
</tr>