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
 * @version : 0.2.3-30 $
 * @commit  : 11b68819d76b3ad1fed1c955cefe675ac23d8def $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Fri Mar 18 17:18:30 2016 +0100 $
 * @file    : 40-fssource.php $
 * 
 * @id      : 40-fssource.php | Fri Mar 18 17:18:30 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

define( __NAMESPACE__.'\\SRCFILE_SOURCE', - 3 );
$TARGET_NAMES[SRCFILE_SOURCE] = 'fssource';
$NOT_BACKUP_TARGETS[] = SRCFILE_SOURCE;
registerTab( SRCFILE_SOURCE, 'DiskSourceEditor', sprintf( _esc( '%s files' ), PHP_OS ), 'getDirList' );
global $registered_targets;
insertArrayBefore( 
$dashboard_tabs, 
defined( __NAMESPACE__.'\\WP_SOURCE' ) && isset( $registered_targets[WP_SOURCE] ) ? WP_SOURCE : MYSQL_SOURCE, 
SRCFILE_SOURCE );
?>