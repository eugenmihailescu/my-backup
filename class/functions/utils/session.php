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
 * @version : 0.2.3-37 $
 * @commit  : 56326dc3eb5ad16989c976ec36817cab63bc12e7 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Wed Dec 7 18:54:23 2016 +0100 $
 * @file    : session.php $
 * 
 * @id      : session.php | Wed Dec 7 18:54:23 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

function is_session_started( $auto_start = true ) {
$result = false;
if ( php_sapi_name() !== 'cli' ) {
if ( version_compare( phpversion(), '5.4.0-dev', '>=' ) )
$result = session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
else
$result = session_id() === '' ? FALSE : TRUE;
}
! $result && $auto_start && ! headers_sent() && !isset($_SESSION) && session_start();
return $result;
}
function check_is_logged() {
if ( IS_MULTISITE ) {
return; 
} elseif ( is_wp() )
return; 
if ( ! is_session_started( false ) )
if ( ! ( isset( $_SESSION ) && isset( $_SESSION[SIMPLELOGIN_SESSION_LOGGED] ) &&
$_SESSION[SIMPLELOGIN_SESSION_LOGGED] == true ) )
auth_redirect_wrapper();
}
function get_session_varlist() {
if ( isset( $_SESSION[SESSION_VARLIST_KEY] ) )
return $_SESSION[SESSION_VARLIST_KEY];
else
return array();
}
function add_session_var( $key, $value ) {
$_SESSION[$key] = $value;
$session_vars = get_session_varlist();
if ( ! in_array( $key, $session_vars ) ) {
$session_vars[] = $key;
$_SESSION[SESSION_VARLIST_KEY] = $session_vars;
session_commit();
}
}
function del_session_var( $key, $commit = true ) {
if ( isset( $_SESSION[$key] ) )
unset( $_SESSION[$key] );
$session_vars = get_session_varlist();
if ( false !== ( $key = array_search( $key, $session_vars ) ) ) {
unset( $session_vars[$key] );
$_SESSION[SESSION_VARLIST_KEY] = $session_vars;
$commit && session_commit();
}
}
function unset_session_vars() {
foreach ( get_session_varlist() as $key )
del_session_var( $key, false );
session_commit();
}
?>