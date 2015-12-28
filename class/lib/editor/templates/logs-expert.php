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
 * @file    : logs-expert.php $
 * 
 * @id      : logs-expert.php | Mon Dec 28 17:57:55 2015 +0100 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
?>
<tr>
<td><label for="logdir"><?php _pesc('Log directory');?></label></td>
<td colspan="3"><input type="text" name="logdir" id="logdir" size="42"
value=<?php
echo "'" . $log_dir . "'";
?>><a class='help' onclick=<?php
echoHelp( $help_1 );
?>> [?]</a></td>
</tr>
<tr>
<td><label for="logrotate"><?php _pesc('Rotate logs');?></label></td>
<td><input type="checkbox" name="logrotate" id="logrotate"
<?php
echo strToBool( $this->settings['logrotate'] ) ? 'checked' : '';
?>><input type="hidden" name="logrotate" value="0"><a class='help'
onclick=<?php
echoHelp( $help_2 );
?>> [?]</a></td>
<td><label for="logsize"><?php _pesc('Max log size');?></label></td>
<td><input type='number' size="4" min="1" max="2048" name='logsize'
id="logsize"
value=<?php
echo $this->settings['logsize'];
echo ! $this->settings['logrotate'] ? ' disabled' : '';
?>> MiB <a class='help' onclick=<?php
echoHelp( $help_3 );
?>> [?]</a></td>
</tr>
<?php if(defined ( __NAMESPACE__.'\\APP_LISTVIEW_TARGETS' ) ){?>
<tr>
<td><label for="logbranched"><?php _pesc('Logs per job');?></label></td>
<td colspan="3"><input type="checkbox" name="logbranched" id="logbranched"
<?php echo $logbranched?'checked':'';?> value="1"><input type="hidden"
name="logbranched" value="0"><a class='help'
onclick=<?php
echoHelp( $help_4 );
?>> [?]</a></td>
</tr>
<?php }?>