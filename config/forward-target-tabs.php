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
 * @file    : forward-target-tabs.php $
 * 
 * @id      : forward-target-tabs.php | Mon Dec 28 17:57:55 2015 +0100 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

$registered_forward_map = array( 
'WP_SOURCE' => array( is_multisite_wrapper() ? _esc( 'Site files' ) : _esc( 'WP files' ), null, - 4 ), 
'SRCFILE_SOURCE' => array( _esc( 'Backup source' ), 'any-file-visible-to-the-wp', - 3 ), 
'APP_JOB_HISTORY' => array( _esc( 'Job history' ), 'query-job-history', 8 ), 
'APP_STATISTICS' => array( _esc( 'Statistics' ), 'backup-statistics', 10 ), 
'APP_LICENSE' => array( _esc( 'License' ), null, 12 ), 
'APP_LISTVIEW_TARGETS' => array( _esc( 'Target list' ), 'backup-wizard', 16 ), 
'BACKUP_SETTINGS' => array( _esc( 'Settings' ), 'advanced-network-settings', 18 ), 
'APP_EULA' => array( _esc( 'EULA' ), null, 20 ), 
'APP_RESTORE' => array( _esc( 'Restore' ), 'restore-wizard', 21 ), 
'APP_OS_SCHEDULE' => array( PHP_OS . '-Cron', 'wp-schedule-the-backup-via-os', 22 ), 
'APP_WP_SCHEDULE' => array( 'WP-Cron', null, 23 ), 
'APP_ADDONDROPIN' => array( _esc( 'Addons Drop-in' ), 'product-category/addons', 24 ) );
!is_wp()&&
$forward_compatible_targets = array(); 
foreach ( $registered_forward_map as $constant => $tab_info )
$forward_compatible_targets[$tab_info[2]] = array( 
'title' => $tab_info[0], 
'link' => null == $tab_info[1] ? '#' : APP_ADDONS_SHOP_URI . $tab_info[1] );
?>