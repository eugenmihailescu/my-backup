<?php
/**
 * ################################################################################
 * MyBackup
 * 
 * Copyright 2017 Eugen Mihailescu <eugenmihailescux@gmail.com>
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
 * @version : 1.0-3 $
 * @commit  : 1b3291b4703ba7104acb73f0a2dc19e3a99f1ac1 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Tue Feb 7 08:55:11 2017 +0100 $
 * @file    : globals.php $
 * 
 * @id      : globals.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

global $BACKUP_MODE;
global $NOT_BACKUP_TARGETS;
global $BACKUP_TARGETS;
global $COMPRESSION_NAMES;
global $COMPRESSION_LIBS;
global $COMPRESSION_FILTERS;
global $COMPRESSION_APPS;
global $COMPRESSION_HEADERS;
global $COMPRESSION_ARCHIVE;
global $COMPRESSION_LEVEL_SUPPORT;
global $PROGRESS_PROVIDER;
global $REGISTERED_BACKUP_TABS;
global $REGISTERED_SCHEDULE_TABS;
global $TARGET_NAMES;
global $VERBOSITY_MODES;
global $_CURL_ERROR_MESSAGES;
global $_branch_id;
global $_branch_id_;
global $actions;
global $alert_message_obj;
global $chart_script;
global $container_shape;
global $dashboard_tabs;
global $exclude_files_factory;
global $registered_settings;
global $factory_options;
global $fixed_options;
global $features;
global $has_postbox;
global $java_scripts;
global $java_scripts_beforeunload;
global $java_scripts_load;
global $java_scripts_unload;
global $license;
global $license_id;
global $long_opts;
global $menu_shape;
global $registered_targets;
global $forward_compatible_targets;
global $registered_forward_map;
global $settings;
global $short_opts;
global $tab_orientation;
global $tab_position;
global $transactions;
global $registered_ciphres;
global $registered_db_upgrades;
global $registered_tab_redirects;
isset( $java_scripts ) || $java_scripts = array();
isset( $java_scripts_beforeunload ) || $java_scripts_beforeunload = array();
isset( $java_scripts_load ) || $java_scripts_load = array();
isset( $java_scripts_unload ) || $java_scripts_unload = array();
isset( $chart_script ) || $chart_script = array();
isset( $registered_db_upgrades ) || $registered_db_upgrades = array();
?>