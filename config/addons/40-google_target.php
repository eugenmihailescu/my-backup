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
 * @file    : 40-google_target.php $
 * 
 * @id      : 40-google_target.php | Mon Dec 28 17:57:55 2015 +0100 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

define ( __NAMESPACE__.'\\GOOGLE_TARGET', 4 );
$TARGET_NAMES [GOOGLE_TARGET] = 'google';
$REGISTERED_BACKUP_TABS [GOOGLE_TARGET] = $TARGET_NAMES [GOOGLE_TARGET]; 
$BACKUP_TARGETS [GOOGLE_TARGET] = 'google';
registerTab ( GOOGLE_TARGET, 'GoogleTargetEditor', _esc ( 'Google' ), 'getGoogleFiles', 'google', 'gdrive.png' );
spl_autoload_register ( function ($class_name) {
$classes_path = array (
'GoogleCloudStorage' => STORAGE_PATH . 'GoogleCloudStorage.php' 
);
$class_name = preg_replace ( "/" . __NAMESPACE__ . "\\\\/", "", $class_name );
isset ( $classes_path [$class_name] ) && include_once $classes_path [$class_name];
} );
?>