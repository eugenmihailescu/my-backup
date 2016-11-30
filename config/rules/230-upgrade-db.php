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
 * @file    : 230-upgrade-db.php $
 * 
 * @id      : 230-upgrade-db.php | Wed Nov 30 15:38:35 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

include_once MISC_PATH . 'StatisticsManager.php';
global $registered_db_upgrades;
$db_ver_230 = '2.3.1-dev';
$registered_db_upgrades[$db_ver_230] = array( 'db_upgrade_230' );
function db_upgrade_230( $statistics_manager ) {
global $db_ver_230;
$errors = array();
if ( ! is_object( $statistics_manager ) )
return true;
$conn_settings = $statistics_manager->getSettings();
if ( ! ( isset( $conn_settings['history_enabled'] ) && strToBool( $conn_settings['history_enabled'] ) ) ) {
return true;
}
if ( isset( $conn_settings['historydb'] ) ) {
if ( 'mysql' == $conn_settings['historydb'] ) {
if ( ! ( isset( $conn_settings['mysql_enabled'] ) && strToBool( $conn_settings['mysql_enabled'] ) ) )
return true;
} else
return true;
} else
return true;
$alter_bigint_cols = function ( $table, $cols ) use(&$statistics_manager ) {
$col_list = array();
foreach ( $cols as $col )
$col_list[] = ' MODIFY COLUMN ' . $col . ' BIGINT';
if ( false === $statistics_manager->queryData( 'ALTER TABLE ' . $table . implode( ',', $col_list ) ) ) {
$message = sprintf( _esc( 'Error upgrading table %s' ), $table );
add_alert_message( $message, null, MESSAGE_TYPE_WARNING );
return $message;
}
return true;
};
if ( true !== ( $e = $alter_bigint_cols( 
TBL_PREFIX . TBL_FILES, 
array( METRIC_UNCOMPRESSED, METRIC_SIZE, METRIC_DISK_FREE ) ) ) ) {
$errors[] = $e;
}
if ( true !== ( $e = $alter_bigint_cols( TBL_PREFIX . TBL_JOBS, array( 'job_size' ) ) ) ) {
$errors[] = $e;
}
return empty( $errors ) ? true : $errors;
}
?>