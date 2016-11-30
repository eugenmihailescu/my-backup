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
 * @version : 0.2.3-34 $
 * @commit  : 433010d91adb8b1c49bace58fae6cd2ba4679447 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Wed Nov 30 15:38:35 2016 +0100 $
 * @file    : 10-cpu_sleep.php $
 * 
 * @id      : 10-cpu_sleep.php | Wed Nov 30 15:38:35 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

define( __NAMESPACE__.'\\CPU_THROTTLING', true );
function onUpgradeCPUSleep( $settings ) {
$prev_version = isset( $settings['plugin_ver'] ) ? $settings['plugin_ver'] : '';
$result = array();
if ( version_compare( $prev_version, '0.2.3-dev' ) < 0 ) {
$result['cpusleep'] = 0;
}
$result['plugin_ver'] = APP_VERSION_NO;
return $result;
}
register_settings( 'onUpgradeCPUSleep' );
?>