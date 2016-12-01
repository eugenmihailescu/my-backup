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
 * @file    : MySQLWrapper.php $
 * 
 * @id      : MySQLWrapper.php | Thu Dec 1 04:37:45 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

defined( __NAMESPACE__.'\\MYSQLI_TRANS_START_NO_OPT' ) || define( __NAMESPACE__.'\\MYSQLI_TRANS_START_NO_OPT', 0 );
defined( __NAMESPACE__.'\\MYSQLI_TRANS_START_CONSISTENT_SNAPSHOT' ) || define( __NAMESPACE__.'\\MYSQLI_TRANS_START_CONSISTENT_SNAPSHOT', 1 );
defined( __NAMESPACE__.'\\MYSQLI_TRANS_START_READ_WRITE' ) || define( __NAMESPACE__.'\\MYSQLI_TRANS_START_READ_WRITE', 2 );
defined( __NAMESPACE__.'\\MYSQLI_TRANS_START_READ_ONLY' ) || define( __NAMESPACE__.'\\MYSQLI_TRANS_START_READ_ONLY', 4 );
defined( __NAMESPACE__.'\\MYSQLI_TRANS_COR_NO_OPT' ) || define( __NAMESPACE__.'\\MYSQLI_TRANS_COR_NO_OPT', 0 );
defined( __NAMESPACE__.'\\MYSQLI_TRANS_COR_AND_CHAIN' ) || define( __NAMESPACE__.'\\MYSQLI_TRANS_COR_AND_CHAIN', 1 );
defined( __NAMESPACE__.'\\MYSQLI_TRANS_COR_AND_NO_CHAIN' ) || define( __NAMESPACE__.'\\MYSQLI_TRANS_COR_AND_NO_CHAIN', 2 );
defined( __NAMESPACE__.'\\MYSQLI_TRANS_COR_RELEASE' ) || define( __NAMESPACE__.'\\MYSQLI_TRANS_COR_RELEASE', 4 );
defined( __NAMESPACE__.'\\MYSQLI_TRANS_COR_NO_RELEASE' ) || define( __NAMESPACE__.'\\MYSQLI_TRANS_COR_NO_RELEASE', 8 );
class MySQLException extends \Exception {
function __construct( $message, $code = null, $previous = null ) {
$class_name = class_exists( 'MyException' ) ? 'MyException' : '\\Exception';
new $class_name( $message, $code, $previous );
}
}
class MySQLErrorException extends MySQLException {
function __construct( $mysql_wrapper ) {
$previous = null;
$class_name = 'MySQLWrapper';
if ( ! ( $mysql_wrapper instanceof $class_name ) ) {
$message = sprintf( _( 'Argument not an instance of %s' ), $class_name );
$code = 50000;
} else {
$error = $mysql_wrapper->get_last_error();
$message = $error['message'];
$code = $error['code'];
}
new MySQLException( $message, $code, $previous );
}
}
class MySQLWrapper {
const FETCH_ASSOC = 1;
const FETCH_NUM = 2;
const FETCH_BOTH = 3;
const MYSQL_EXT = 'mysql';
const MYSQLi_EXT = 'mysqli';
const MYSQLPDO_EXT = 'pdo_mysql';
private $_is_opened;
protected $_params;
protected $_host;
protected $_socket;
protected $_port;
protected $_user;
protected $_pwd;
protected $_db;
protected $_charset;
protected $_collate;
protected $_is_pdo;
protected $_is_mysqli;
protected $_link;
public $is_wp;
function __construct( $params ) {
$this->_is_opened = false;
$this->_link = null;
$this->is_wp = false; 
$this->_params = $params;
$allowed_ext = array( '', self::MYSQL_EXT, self::MYSQLi_EXT, self::MYSQLPDO_EXT );
$ext = $this->get_param( 'mysql_ext' );
if ( ! in_array( $ext, $allowed_ext ) ) {
throw new MySQLException( 
sprintf( 
_( 'Invalid mysql_ext argument. One of %s expected.' ), 
implode( 
', ', 
array_map( function ( $item ) {
return empty( $item ) ? '``' : $item;
}, $allowed_ext ) ) ) );
}
$has_pdo = class_exists( 'PDO' );
$has_mysqli = function_exists( 'mysqli_connect' );
$this->_is_pdo = $has_pdo && self::MYSQLPDO_EXT == $ext;
$this->_is_mysqli = $has_mysqli && in_array( $ext, array( self::MYSQLi_EXT, '' ) );
$this->_is_pdo = $this->_is_pdo ||
$has_pdo && in_array( $ext, array( self::MYSQLi_EXT, '' ) ) && ! $this->_is_mysqli;
$this->_is_mysqli = $this->_is_mysqli ||
$has_mysqli && in_array( $ext, array( self::MYSQLPDO_EXT, '' ) ) && ! $this->_is_pdo;
$this->_host = $this->get_param( 'mysql_host' );
$this->_socket = $this->get_param( 'mysql_socketd' );
$this->_port = $this->get_param( 'mysql_port' );
$this->_user = $this->get_param( 'mysql_user' );
$this->_pwd = $this->get_param( 'mysql_pwd' );
$this->_db = $this->get_param( 'mysql_db' );
$this->_charset = $this->get_param( 'mysql_charset' );
$this->_collate = $this->get_param( 'mysql_collate' );
}
function __destruct() {
$this->_is_opened && $this->disconnect();
return true;
}
private function _get_flag_sqlname( $flag, $type = 0 ) {
if ( 0 == $type ) {
switch ( $flag ) {
case MYSQLI_TRANS_START_READ_ONLY :
$result = 'READ ONLY';
break;
case MYSQLI_TRANS_START_READ_WRITE :
$result = 'READ WRITE';
break;
case MYSQLI_TRANS_START_CONSISTENT_SNAPSHOT :
$result = 'WITH CONSISTENT SNAPSHOT';
break;
case MYSQLI_TRANS_START_NO_OPT :
$result = '';
break;
}
} elseif ( 1 == $type ) {
switch ( $flag ) {
case MYSQLI_TRANS_COR_AND_CHAIN :
$result = 'AND CHAIN';
break;
case MYSQLI_TRANS_COR_AND_NO_CHAIN :
$result = 'AND NO CHAIN';
break;
case MYSQLI_TRANS_COR_RELEASE :
$result = 'RELEASE';
break;
case MYSQLI_TRANS_COR_NO_RELEASE :
$result = 'NO RELEASE';
break;
case MYSQLI_TRANS_COR_NO_OPT :
$result = '';
break;
}
}
return $result;
}
private function _get_autocommit_state() {
$result = true; 
if ( $res = $this->query( 'SELECT @@autocommit' ) ) {
if ( $row = $this->fetch_row( $res ) ) {
$result = $row[0];
}
$this->free_result( $res );
}
return $result;
}
private function _is_null( $value, $key, $default = null ) {
if ( is_array( $value ) )
return isset( $value[$key] ) ? $value[$key] : $default;
return empty( $value ) ? $default : $value;
}
private function _get_mysql_function( $name ) {
return self::MYSQL_EXT . ( $this->_is_mysqli ? 'i' : '' ) . '_' . $name;
}
private function _swap_args() {
$args = func_get_args();
return $this->_is_mysqli ? array_reverse( $args ) : $args;
}
private function _init_connect() {
if ( ! $this->_is_opened ) {
return true;
}
$info = $this->get_connection_info();
if ( false !== $info ) {
$current_user = $info['user'];
$current_host = array( $info['host'], $info['ipaddr'] );
if ( preg_match( '/([^@]+)@?(.*)/', $info['user'], $matches ) ) {
$current_user = $matches[1];
count( $matches ) > 1 && $current_host[] = $matches[2];
}
if ( $this->_user == $current_user && $this->_db == $info['dbname'] &&
( empty( $this->_port ) || $this->_port == $info['port'] ) && $this->_charset == $info['charset'] &&
( empty( $this->_collate ) || $this->_collate == $info['collation'] ) &&
in_array( $this->_host, $current_host ) ) {
return $this->_link;
}
}
return $this->disconnect();
}
private function _prepare( $query ) {
if ( $this->_is_pdo ) {
return $this->_link->prepare( $query );
} elseif ( $this->_is_mysqli ) {
return mysqli_prepare( $this->_link, $query );
}
if ( ! ( mysql_query( sprintf( "SET @sql = '%s'", addslashes( $query ) ) ) &&
mysql_query( 'PREPARE stmt FROM @sql' ) ) )
return false;
return true;
}
private function _bind_params( $stmt, $params ) {
$_get_param_type = function ( $value, $is_mysqli = true ) {
if ( is_int( $value ) || is_bool( $value ) )
return $is_mysqli ? 'i' : \PDO::PARAM_INT;
elseif ( is_double( $value ) )
return $is_mysqli ? 'd' : \PDO::PARAM_STR;
return $is_mysqli ? 's' : \PDO::PARAM_STR;
};
if ( $this->_is_pdo ) {
foreach ( $params as $name => $value )
if ( $this->_is_pdo ) {
$params[$name] = is_string( $value ) ? $this->escape_sql_string( $value ) : $value;
if ( ! $stmt->bindParam( ":$name", $params[$name], $_get_param_type( $params[$name], false ) ) )
return false;
}
} elseif ( $this->_is_mysqli ) {
$types = '';
$args = array( &$stmt, &$types );
foreach ( $params as $name => $value ) {
$types .= $_get_param_type( $value );
$args[] = is_string( $value ) ? $this->escape_sql_string( $value ) : $value;
$i = count( $args ) - 1;
$args[$i] = &$args[$i];
}
if ( ! call_user_func_array( 'mysqli_stmt_bind_param', $args ) )
return false;
} else
foreach ( $params as $name => $value ) {
$quote = is_string( $value ) ? "'" : "";
if ( ! mysql_query( 
sprintf( 
"SET @%s = %s", 
$name, 
$quote . ( empty( $quote ) ? $value : $this->escape_sql_string( $value ) ) . $quote ) ) )
return false;
}
return true;
}
private function _sanitize_transaction_name( $name ) {
return preg_replace( '/[^0-9A-Za-z\-_=]/', '', $name );
}
public function get_param( $param_name ) {
$default = null;
switch ( $param_name ) {
case 'mysql_format' :
$default = $this->_is_null( $this->_params, $param_name, 'sql' );
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
case 'mysql_ext' :
$default = '';
break;
}
return $this->is_wp ? $default : $this->_is_null( $this->_params, $param_name, $default );
}
public function get_connection_info() {
if ( $this->_is_opened &&
$res = $this->query( 
'SELECT current_user() as user, database() as dbname, charset(current_user()) as charset, collation(current_user()) as collation, @@hostname as host, @@port as port, @@bind_address as ipaddr' ) )
return $this->fetch_array( $res, 1 );
return false;
}
public function set_collation( $collation ) {
if ( empty( $collation ) || $this->_is_pdo && $collation == $this->_collate )
return true;
if ( ! ( $this->_is_opened &&
false === ( $res = $this->query( sprintf( "SET NAMES '%s' COLLATE '%s'", $this->_charset, $collation ) ) ) ) ) {
$this->_collate = $collation;
return true;
}
return false;
}
public function connect( $persistent = true ) {
if ( ! is_bool( $link = $this->_init_connect() ) )
return $link;
if ( $this->_is_pdo ) {
$php_prior_536 = version_compare( PHP_VERSION, '5.3.6', '<' );
$pdo_options = array( \PDO::ATTR_PERSISTENT => $persistent );
$pdo_dsn = 'mysql:';
$pdo_dsn .= empty( $this->_socket ) ? sprintf( 'host=%s;port=%s', $this->_host, $this->_port ) : sprintf( 
'unix_socket=%s', 
$this->_socket );
$pdo_dsn .= sprintf( 
';dbname=%s;%s', 
$this->_db, 
$php_prior_536 ? '' : sprintf( 'charset=%s;collation=%s', $this->_charset, $this->_collate ) );
$php_prior_536 && $pdo_options[\PDO::MYSQL_ATTR_INIT_COMMAND] = sprintf( 
"SET NAMES '%s' COLLATE '%s'", 
$this->_charset, 
$this->_collate );
$this->_link = new \PDO( $pdo_dsn, $this->_user, $this->_pwd, $pdo_options );
} else {
$name = self::MYSQL_EXT . ( $this->_is_mysqli ? 'i' : '' ) . '_' .
( $persistent && ! $this->_is_mysqli ? 'p' : '' ) . 'connect';
$host = ( $this->_is_mysqli && $persistent ? 'p:' : '' ) . $this->_host;
$extra_args = array();
if ( ! $this->_is_mysqli )
$host = sprintf( '%s:%s', $this->_host, empty( $this->_socket ) ? $this->_port : $this->_socket );
else {
$extra_args[] = $this->_db;
$extra_args[] = empty( $this->_socket ) ? $this->_port : false; 
empty( $this->_socket ) || $extra_args[] = $this->_socket;
}
$args = array_merge( array( $host, $this->_user, $this->_pwd ), $extra_args );
$this->_link = @call_user_func_array( $name, $args );
}
$this->_is_opened = ! empty( $this->_link );
if ( ! $this->_is_pdo && $this->_is_opened ) {
$this->set_collation( $this->_collate );
$this->set_charset( $this->_charset );
$this->select_db( $this->_db );
}
return $this->_link;
}
public function disconnect() {
if ( $this->_is_pdo )
$result = true;
else
$result = empty( $this->_link ) || call_user_func( $this->_get_mysql_function( 'close' ), $this->_link );
$result && $this->_link = null;
return $result;
}
public function set_charset( $charset ) {
if ( empty( $charset ) || $this->_is_pdo && $charset == $this->_charset )
return true;
if ( $this->_is_pdo ) {
$old_charset = $this->_charset;
$this->_charset = $charset;
if ( $this->_is_opened && false === $this->connect() ) {
$this->_charset = $old_charset;
return false;
}
return true;
}
return call_user_func_array( 
$this->_get_mysql_function( 'set_charset' ), 
$this->_swap_args( $charset, $this->_link ) );
}
public function select_db( $database_name ) {
if ( empty( $database_name ) || $this->_is_pdo && $this->_db == $database_name )
return true;
if ( $this->_is_pdo ) {
$this->disconnect();
$this->_db = $database_name;
return false !== $this->connect();
}
return call_user_func_array( 
$this->_get_mysql_function( 'select_db' ), 
$this->_swap_args( $database_name, $this->_link ) );
}
public function get_last_error() {
if ( $this->_is_pdo ) {
$error = $this->_link->errorInfo();
return array( 'code' => $error[1], 'message' => $error[2], 'state' => $error[0] );
}
return array( 
'code' => call_user_func( $this->_get_mysql_function( 'errno' ), $this->_link ), 
'message' => call_user_func( $this->_get_mysql_function( 'error' ), $this->_link ), 
'state' => null );
}
public function escape_sql_string( $unescaped_string ) {
if ( $this->_is_pdo ) {
return addslashes( $unescaped_string );
}
return call_user_func_array( 
$this->_get_mysql_function( 'real_escape_string' ), 
$this->_swap_args( $unescaped_string, $this->_link ) );
}
public function query( $query, $params = null ) {
if ( ! $this->_link )
return false;
$stmt = null;
if ( ! empty( $params ) ) {
if ( $stmt = $this->_prepare( $query ) )
if ( $this->_bind_params( $stmt, $params ) )
if ( $this->_is_pdo || $this->_is_mysqli ) {
if ( $stmt->execute() )
return $stmt;
} else {
return mysql_query( 
sprintf( "EXECUTE stmt USING %s", '@' . implode( ',@', array_keys( $params ) ) ) );
}
return false;
}
if ( $this->_is_pdo ) {
return $this->_link->query( $query );
}
return call_user_func_array( $this->_get_mysql_function( 'query' ), $this->_swap_args( $query, $this->_link ) );
}
public function free_result( &$result ) {
if ( $this->_is_pdo ) {
$result = null;
return true;
}
$success = call_user_func( $this->_get_mysql_function( 'free_result' ), $result );
return $this->_is_mysqli ? true : $success;
}
public function fetch_row( $result ) {
if ( $this->_is_pdo ) {
return $result->fetch( \PDO::FETCH_NUM );
}
return call_user_func( $this->_get_mysql_function( 'fetch_row' ), $result );
}
public function fetch_array( $result, $result_type = self::FETCH_BOTH ) {
if ( $this->_is_pdo ) {
return $result->fetch( $result_type + 1 );
}
return call_user_func( $this->_get_mysql_function( 'fetch_array' ), $result, $result_type );
}
public function get_affected_rows( $stmt = null ) {
if ( $this->_is_pdo ) {
return $stmt->rowCount();
}
return call_user_func( $this->_get_mysql_function( 'affected_rows' ), $this->_link );
}
public function get_insert_id() {
if ( $this->_is_pdo ) {
return $this->_link->lastInsertId();
}
return call_user_func( $this->_get_mysql_function( 'insert_id' ), $this->_link );
}
public function get_rows_count( $result ) {
if ( $this->_is_pdo ) {
return $result->rowCount();
}
return call_user_func( $this->_get_mysql_function( 'num_rows' ), $result );
}
public function get_cols_count( $result ) {
if ( $this->_is_pdo ) {
return $result->columnCount();
}
return call_user_func( $this->_get_mysql_function( 'num_fields' ), $result );
}
public function seek_row( $result, $row_number ) {
if ( ! $this->get_rows_count( $result ) )
return false;
if ( $this->_is_pdo ) {
return $result->fetch( \PDO::FETCH_BOTH, \PDO::FETCH_ORI_ABS, $row_number );
}
return call_user_func( $this->_get_mysql_function( 'data_seek' ), $result, $row_number );
}
public function get_database_names( $mysql_schema = false ) {
$result = array();
if ( $res = $this->query( 'SHOW DATABASES' ) ) {
$mysql_db = array( 'information_schema', 'performance_schema', 'mysql' );
while ( $row = $this->fetch_row( $res ) )
( $mysql_schema || ! in_array( $row[0], $mysql_db ) ) && $result[] = $row[0];
$this->free_result( $res );
}
return $result;
}
public function get_server_info() {
$info = array( 
'version' => null, 
'version_comment' => null, 
'version_compile_os' => null, 
'version_compile_machine' => null );
if ( $res = $this->query( 'SHOW VARIABLES LIKE "version%";' ) ) {
while ( $row = $this->fetch_row( $res ) )
$info[$row[0]] = $row[1];
$this->free_result( $res );
}
return $info;
}
public function get_database_size( $db_name = '', $table_filter = '' ) {
$dbsize = array( 'dbsize' => 0, 'tblcount' => 0 );
$db_name = empty( $db_name ) ? 'database()' : ( "'" . $this->escape_sql_string( $db_name ) . "'" );
$where = array( sprintf( 'where table_schema=%s', $db_name ) );
empty( $table_filter ) || $where[] = $table_filter;
$where = implode( ' AND ', $where );
if ( $res = $this->query( 
sprintf( 
"select (SELECT SUM(data_length + index_length) as dbsize FROM information_schema.TABLES %s group by table_schema) as dbsize, (SELECT COUNT(*) FROM information_schema.TABLES %s) as tblcount", 
$where, 
$where ) ) ) {
if ( $row = $this->fetch_row( $res ) ) {
$dbsize['dbsize'] = $row[0];
$dbsize['tblcount'] = $row[1];
}
$this->free_result( $res );
}
return $dbsize;
}
public function get_extension() {
return $this->_is_mysqli ? self::MYSQLi_EXT : ( $this->_is_pdo ? self::MYSQLPDO_EXT : self::MYSQL_EXT );
}
public function begin_transaction( $flags = MYSQLI_TRANS_START_READ_ONLY, $name = __CLASS__ ) {
$name = $this->_sanitize_transaction_name( $name );
if ( $this->_is_pdo ) {
return $this->_link->beginTransaction();
} elseif ( $this->_is_mysqli ) {
if ( function_exists( '\\mysqli_begin_transaction' ) )
return mysqli_begin_transaction( $this->_link, $flags, $name );
else
$this->_link->autocommit( FALSE );
}
return 1 == $this->query( 'start transaction ' . $this->_get_flag_sqlname( $flags, 0 ) );
}
public function commit_transaction( $flags = MYSQLI_TRANS_COR_NO_OPT, $name = __CLASS__ ) {
$name = $this->_sanitize_transaction_name( $name );
if ( $this->_is_pdo ) {
return $this->_link->commit();
} elseif ( $this->_is_mysqli && function_exists( '\\mysqli_commit' ) ) {
$args = array( $this->_link );
version_compare( PHP_VERSION, '5.5', '>=' ) && $args = $args + array( $flags, $name );
return call_user_func_array( '\\mysqli_commit', $args );
}
return 1 == $this->query( 'commit ' . $this->_get_flag_sqlname( $flags, 1 ) );
}
public function rollback_transaction( $flags = MYSQLI_TRANS_COR_NO_OPT, $name = __CLASS__ ) {
$name = $this->_sanitize_transaction_name( $name );
if ( $this->_is_pdo ) {
return $this->_link->rollBack();
} elseif ( $this->_is_mysqli && function_exists( '\\mysqli_rollback' ) ) {
$args = array( $this->_link );
version_compare( PHP_VERSION, '5.5', '>=' ) && $args = $args + array( $flags, $name );
return call_user_func_array( '\\mysqli_rollback', $args );
}
return 1 == $this->query( 'rollback ' . $this->_get_flag_sqlname( $flags, 1 ) );
}
}
?>