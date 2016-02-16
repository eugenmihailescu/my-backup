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
 * @version : 0.2.3-3 $
 * @commit  : 961115f51b7b32dcbd4a8853000e4f8cc9216bdf $
 * @author  : Eugen Mihailescu <eugenmihailescux@gmail.com> $
 * @date    : Tue Feb 16 15:27:30 2016 +0100 $
 * @file    : 230-upgrade-db.php $
 * 
 * @id      : 230-upgrade-db.php | Tue Feb 16 15:27:30 2016 +0100 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
global $registered_db_upgrades;
$db_ver_230 = '2.3.1-dev';
$registered_db_upgrades[$db_ver_230] = array( 'db_upgrade_230' );
function db_upgrade_230( $statistics_manager ) {
global $db_ver_230;
$errors = array();
$alter_bigint_cols = function ( $table, $cols ) use(&$statistics_manager ) {
$col_list = array();
foreach ( $cols as $col )
$col_list[] = ' MODIFY COLUMN ' . $col . ' BIGINT';
if ( false === $statistics_manager->queryData( 'ALTER TABLE ' . $table . implode( ',', $col_list ) ) )
return sprintf( _esc( 'Error upgrading table %s' ), $table );
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