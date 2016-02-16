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
 * @version : 0.2.3-3 $
 * @commit  : 961115f51b7b32dcbd4a8853000e4f8cc9216bdf $
 * @author  : Eugen Mihailescu <eugenmihailescux@gmail.com> $
 * @date    : Tue Feb 16 15:27:30 2016 +0100 $
 * @file    : mysql.php $
 * 
 * @id      : mysql.php | Tue Feb 16 15:27:30 2016 +0100 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
function getMySQLError( $link = null, $key = null ) {
$result = array( 'code' => @\mysql_errno( $link ), 'message' => @\mysql_error( $link ) );
if ( ! empty( $key ) )
if ( isset( $result[$key] ) )
return $result[$key];
else
return null;
else
return $result;
}
function createMySQLConnection( $settings = null ) {
$link = @\mysql_connect( 
getMySQLparam( 'mysql_host', $settings ) . ':' . getMySQLparam( 'mysql_port', $settings ), 
getMySQLparam( 'mysql_user', $settings ), 
getMySQLparam( 'mysql_pwd', $settings ) );
if ( ! ( $err = false === $link ) ) {
$mysql_charset = getMySQLparam( 'mysql_charset', $settings );
$mysql_db = getMySQLparam( 'mysql_db', $settings );
! empty( $mysql_charset ) && $err = ! @\mysql_set_charset( $mysql_charset, $link );
! ( $err || empty( $mysql_db ) ) && $err = ! @\mysql_select_db( $mysql_db, $link );
print_r( getMySQLError( $link ), 1 );
}
return $err ? false : $link;
}
function closeMySQLConnection( $link = false ) {
( false === $link ) || @\mysql_close( $link );
}
function getMySQLparam( $param_name, $options = null ) {
global $setting;
isset( $options ) || $options = $setting;
$default = null;
$is_wp = function_exists( '\\add_management_page' );
switch ( $param_name ) {
case 'mysql_format' :
$default = 'sql';
break;
case 'mysql_host' :
$default = @constant( 'DB_HOST' ) ? DB_HOST : 'localhost';
break;
case 'mysql_port' :
$default = 3306;
break;
case 'mysql_user' :
$default = @constant( 'DB_USER' ) ? DB_USER : '';
break;
case 'mysql_pwd' :
$default = @constant( 'DB_PASSWORD' ) ? DB_PASSWORD : '';
break;
case 'mysql_db' :
$default = @constant( 'DB_NAME' ) ? DB_NAME : '';
break;
case 'mysql_charset' :
$default = @constant( 'DB_CHARSET' ) ? DB_CHARSET : 'utf8';
break;
case 'mysql_collate' :
$default = @constant( 'DB_COLLATE' ) ? DB_COLLATE : '';
break;
}
return $is_wp ? $default : isNull( $options, $param_name, $default );
}
function getMySQLTableNamesFromPattern( $pattern = '.+', $close_link = true, $settings = null, $extended = false, $order_by_name = false ) {
if ( false === ( $link = createMySQLConnection( $settings ) ) ) {
return false;
}
$tables = array();
$wp_db_prefix = is_wp() ? wp_get_db_prefix() : '';
$rst = @\mysql_query( 'select DATABASE();', $link );
if ( FALSE !== $rst && $row = @\mysql_fetch_row( $rst ) )
$db_name = $row[0];
else
$db_name = getMySQLparam( 'mysql_db', $settings );
if ( false !== strpos( $pattern, '|' ) && false === strpos( $pattern, ',' ) )
$where = explode( '|', $pattern );
elseif ( false === strpos( $pattern, '|' ) && false !== strpos( $pattern, ',' ) )
$where = explode( ',', $pattern );
else {
$where = array();
foreach ( explode( ',', $pattern ) as $item )
foreach ( explode( '|', $item ) as $tbl )
$where[] = $tbl;
}
array_walk( 
$where, 
function ( &$item ) use(&$db_name ) {
$item = sprintf( "table_name REGEXP '^%s$'", $item );
} );
$where = empty( $where ) ? '' : ( '(' . implode( ' OR ', $where ) . ')' );
empty( $wp_db_prefix ) ||
$where = sprintf( "table_name like '%s%%' AND %s", wp_get_db_prefix(), empty( $where ) ? 'true' : $where );
$sql = sprintf( 
"SELECT * FROM (SELECT table_name,table_rows,(data_length+index_length) as table_size FROM information_schema.tables WHERE table_schema='%s' AND %s) A ORDER BY A.", 
$db_name, 
$where );
$sql .= $order_by_name ? 'table_name' : 'table_size DESC';
$rst = @\mysql_query( $sql, $link );
if ( FALSE !== $rst )
while ( $row = @\mysql_fetch_row( $rst ) ) {
( $extended && $tables[$row[0]] = array( $row[1], $row[2] ) ) || $tables[] = $row[0];
}
else
$tables = false;
$close_link && closeMySQLConnection( $link );
return $tables;
}
?>