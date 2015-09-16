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
 * @file    : config-fixes.php $
 * 
 * @id      : config-fixes.php | Wed Sep 16 11:33:37 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;

if (! isset ( $_SERVER ['SERVER_SOFTWARE'] ))
$_SERVER ['SERVER_SOFTWARE'] = PHP_SAPI;
if (! isset ( $_SERVER ['SERVER_NAME'] ))
$_SERVER ['SERVER_NAME'] = PHP_BINARY;
if (! isset ( $_SERVER ['REQUEST_URI'] )) {
$_SERVER ['REQUEST_URI'] = $_SERVER ['PHP_SELF'];
if (isset ( $_SERVER ['QUERY_STRING'] )) {
$_SERVER ['REQUEST_URI'] .= '?' . $_SERVER ['QUERY_STRING'];
}
}
if (! isset ( $_SERVER ['DOCUMENT_ROOT'] )) {
$script_filename = str_replace ( DIRECTORY_SEPARATOR, '/', realpath($_SERVER ['SCRIPT_FILENAME'] ));
$doc_root = str_replace ( realpath($_SERVER ['SCRIPT_NAME']), '', $script_filename );
$_SERVER ['DOCUMENT_ROOT'] = str_replace ( '/', DIRECTORY_SEPARATOR, $doc_root );
if (defined ( 'ALT_ABSPATH' ))
$_SERVER ['DOCUMENT_ROOT'] = ALT_ABSPATH;
}
$tmp_dir = sys_get_temp_dir ();
! file_exists ( $tmp_dir ) && trigger_error ( sprintf ( _esc ( 'The system temporary directory (%s) does not exist.' ), $tmp_dir ), E_USER_WARNING );
define ( 'SANDBOX', file_exists ( RULES_PATH . '0-sandbox.php' ) );
?>
