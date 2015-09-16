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
 * @version : 0.2.2 $
 * @commit  : 23a9968c44669fbb2b60bddf4a472d16c006c33c $
 * @author  : Eugen Mihailescu <eugenmihailescux@gmail.com> $
 * @date    : Wed Sep 16 11:33:37 2015 +0200 $
 * @file    : config-custom.php $
 * 
 * @id      : config-custom.php | Wed Sep 16 11:33:37 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;

if (! defined ( 'ABSPATH' ))
define ( 'ALT_ABSPATH', ROOT_PATH );
else
define ( 'ALT_ABSPATH', str_replace ( '/', DIRECTORY_SEPARATOR, ABSPATH ) );
include_once 'config-fixes.php';
require_once CLASS_PATH . 'constants.php';
require_once CLASS_PATH . 'autoloader.php';
define ( 'BENCHMARK_RANDWORDS_FILE', '/usr/share/dict/words' );
define ( 'CYGWIN_PATH', "C:\cygwin\bin\bash.exe" ); 
define ( 'PBZIP2', false ); 
define ( 'BENCHMARK_FILE_SIZE', 100 ); 
define ( 'DATETIME_FORMAT', 'Y-m-d H:i:s' );
define ( 'MORE_ENTROPY', false ); 
define ( 'SYST_USAGE_THRESOLD', 75 ); 
define ( 'PROGRESS_LAZYWRITE', true ); 
define ( 'LOG_CHECK_TIMEOUT', 10000 ); 
define ( 'LONG_RUNNING_JOB_TIMEOUT', 1000 ); 
if (! function_exists ( 'add_management_page' )) {
! defined ( 'DB_NAME' ) && define ( 'DB_NAME', '' );
! defined ( 'DB_USER' ) && define ( 'DB_USER', '' );
! defined ( 'DB_PASSWORD' ) && define ( 'DB_PASSWORD', '' );
! defined ( 'DB_HOST' ) && define ( 'DB_HOST', 'localhost' );
! defined ( 'DB_CHARSET' ) && define ( 'DB_CHARSET', 'utf8' );
! defined ( 'DB_COLLATE' ) && define ( 'DB_COLLATE', '' );
}
define ( 'SSL_ENFORCE', false ); 
define ( 'DEFAULT_BACKUP_LIFESPAN', 5 ); 
define ( 'GIT_BRANCH_TYPE', '' ); 
define ( 'APP_VERSION_NO', '0.2.2-0' ); 
define ( 'APP_VERSION_TYPE', '' ); 
define ( 'APP_VERSION_ID', '0.2.2-0 - ' ); 
define ( 'APP_VERSION_DATE', '2015-09-16' ); 
?>
