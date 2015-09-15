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
 * @file    : globals.php $
 * 
 * @id      : globals.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;

global $BACKUP_MODE;
global $NOT_BACKUP_TARGETS;
global $BACKUP_TARGETS;
global $COMPRESSION_NAMES;
global $COMPRESSION_LIBS;
global $COMPRESSION_FILTERS;
global $COMPRESSION_APPS;
global $COMPRESSION_HEADERS;
global $COMPRESSION_ARCHIVE;
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
global $settings;
global $short_opts;
global $tab_orientation;
global $tab_position;
global $transactions;
global $registered_ciphres;
global $registered_tab_redirects;
isset ( $java_scripts ) || $java_scripts = array ();
isset ( $java_scripts_beforeunload ) || $java_scripts_beforeunload = array ();
isset ( $java_scripts_load ) || $java_scripts_load = array ();
isset ( $java_scripts_unload ) || $java_scripts_unload = array ();
isset ( $chart_script ) || $chart_script = array ();
?>