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
 * @file    : config-fixes.php $
 * 
 * @id      : config-fixes.php | Thu Dec 1 04:37:45 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

if ( ! isset( $_SERVER['SERVER_SOFTWARE'] ) )
$_SERVER['SERVER_SOFTWARE'] = PHP_SAPI;
if ( ! isset( $_SERVER['SERVER_NAME'] ) )
$_SERVER['SERVER_NAME'] = PHP_BINARY;
if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
$_SERVER['REQUEST_URI'] = $_SERVER['PHP_SELF'];
if ( isset( $_SERVER['QUERY_STRING'] ) ) {
$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
}
}
if ( ! isset( $_SERVER['DOCUMENT_ROOT'] ) ) {
$script_filename = str_replace( DIRECTORY_SEPARATOR, '/', realpath( $_SERVER['SCRIPT_FILENAME'] ) );
$doc_root = str_replace( realpath( $_SERVER['SCRIPT_NAME'] ), '', $script_filename );
$_SERVER['DOCUMENT_ROOT'] = str_replace( '/', DIRECTORY_SEPARATOR, $doc_root );
if ( defined( __NAMESPACE__.'\\ALT_ABSPATH' ) )
$_SERVER['DOCUMENT_ROOT'] = ALT_ABSPATH;
}
define( __NAMESPACE__.'\\SANDBOX', file_exists( RULES_PATH . '0-sandbox.php' ) );
?>