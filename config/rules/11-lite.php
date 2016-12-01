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
 * @version : 0.2.3-36 $
 * @commit  : c4d8a236c57b60a62c69e03c1273eaff3a9d56fb $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Thu Dec 1 04:37:45 2016 +0100 $
 * @file    : 11-lite.php $
 * 
 * @id      : 11-lite.php | Thu Dec 1 04:37:45 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

! defined( __NAMESPACE__.'\\COPYRIGHT' ) && define( 
__NAMESPACE__.'\\COPYRIGHT', 
',' . sprintf( _esc( 'Licensed under %s' ), 'GPLv3' ) . ',https://www.gnu.org/licenses/gpl.txt' );
! ( _function_exists( 'check_is_activated' ) || _function_exists( 'feature_is_licensed' ) ) &&
include_once __FILE__ . '.fix';
$registered_tab_redirects[- 9998] = function ( $tab ) {
global $TARGET_NAMES;
$class_name = __NAMESPACE__ . '\\WelcomeEditor';
if ( ! _file_exists( LOG_DIR . 'filelist.json' ) && class_exists( $class_name ) ) {
return $TARGET_NAMES[APP_WELCOME];
}
};
?>