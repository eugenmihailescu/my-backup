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
 * @version : 1.0-2 $
 * @commit  : f8add2d67e5ecacdcf020e1de6236dda3573a7a6 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Tue Dec 13 06:40:49 2016 +0100 $
 * @file    : 11-lite.php $
 * 
 * @id      : 11-lite.php | Tue Dec 13 06:40:49 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
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