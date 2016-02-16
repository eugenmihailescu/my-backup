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
 * @version : 0.2.3-8 $
 * @commit  : 010da912cb002abdf2f3ab5168bf8438b97133ea $
 * @author  : Eugen Mihailescu eugenmihailescux@gmail.com $
 * @date    : Tue Feb 16 21:44:02 2016 UTC $
 * @file    : StatisticsManager.php $
 * 
 * @id      : StatisticsManager.php | Tue Feb 16 21:44:02 2016 UTC | Eugen Mihailescu eugenmihailescux@gmail.com $
*/

namespace MyBackup;
require_once FUNCTIONS_PATH . 'utils.php';
define( __NAMESPACE__."\\METRIC_ACTION_UNCOMPRESS", - 1 );
define( __NAMESPACE__."\\METRIC_ACTION_COMPRESS", 0 );
define( __NAMESPACE__."\\METRIC_ACTION_TRANSFER", 1 );
define( __NAMESPACE__."\\METRIC_ACTION_CLEANUP", 2 );
define( __NAMESPACE__."\\METRIC_ACTION", 'action' );
define( __NAMESPACE__."\\METRIC_FILENAME", 'filename' );
define( __NAMESPACE__."\\METRIC_MEDIAPATH", 'path' );
define( __NAMESPACE__."\\METRIC_UNCOMPRESSED", 'uncompressed' );
define( __NAMESPACE__."\\METRIC_RATIO", 'ratio' );
define( __NAMESPACE__."\\METRIC_COMPRESS_TYPE", 'compression_type' );
define( __NAMESPACE__."\\METRIC_COMPRESS_LEVEL", 'compression_level' );
define( __NAMESPACE__."\\METRIC_CPU_SLEEP", 'cpu_sleep' );
define( __NAMESPACE__."\\METRIC_TOOLCHAIN", 'toolchain' );
define( __NAMESPACE__."\\METRIC_BZIP_VER", 'bzip_ver' );
define( __NAMESPACE__."\\METRIC_TIME", 'operation_time' );
define( __NAMESPACE__."\\METRIC_SIZE", 'filesize' );
define( __NAMESPACE__."\\METRIC_DISK_FREE", 'disk_free' );
define( __NAMESPACE__."\\JOBTBL_FILE_CHECKSUM", 'checksum' );
define( __NAMESPACE__."\\METRIC_PHP_MEM_USAGE", 'php_mem_usage' );
define( __NAMESPACE__."\\METRIC_SCRIPT_MEM_USAGE", 'script_mem_usage' );
define( __NAMESPACE__."\\METRIC_OPERATION", 'operation' );
define( __NAMESPACE__."\\METRIC_ERROR", 'error' );
define( __NAMESPACE__."\\METRIC_SOURCE", 'source_type' );
define( __NAMESPACE__."\\METRIC_SOURCEPATH", 'path' );
define( __NAMESPACE__."\\JOB_STATUS_RUNNING", 0 );
define( __NAMESPACE__."\\JOB_STATUS_ABORTED", 1 );
define( __NAMESPACE__."\\JOB_STATUS_FINISHED", 2 );
define( __NAMESPACE__."\\JOB_STATUS_SUSPENDED", 3 );
define( __NAMESPACE__."\\JOB_STATE_COMPLETED", 0 );
define( __NAMESPACE__."\\JOB_STATE_PARTIAL", 1 );
define( __NAMESPACE__."\\JOB_STATE_FAILED", 2 );
define( __NAMESPACE__."\\COL_INT", 0 );
define( __NAMESPACE__."\\COL_REAL", 1 );
define( __NAMESPACE__."\\COL_NUMERIC", 2 );
define( __NAMESPACE__."\\COL_TEXT10", 3 );
define( __NAMESPACE__."\\COL_TEXT50", 4 );
define( __NAMESPACE__."\\COL_TEXT", 5 );
define( __NAMESPACE__."\\COL_TEXT30", 6 );
define( __NAMESPACE__."\\COL_TEXT100", 7 );
define( __NAMESPACE__."\\COL_BIGINT", 8 );
! defined( __NAMESPACE__.'\\TBL_PREFIX' ) && define( __NAMESPACE__."\\TBL_PREFIX", wp_get_db_prefix() . "_wpmybk_" );
define( __NAMESPACE__."\\TBL_JOBS", "jobs" );
define( __NAMESPACE__."\\TBL_SOURCES", "sources" );
define( __NAMESPACE__."\\TBL_FILES", "files" );
define( __NAMESPACE__."\\TBL_PATHS", "paths" );
define( __NAMESPACE__."\\TBL_STATS", "stats" );
define( __NAMESPACE__."\\TBL_KEYS", "keys" );
define( __NAMESPACE__."\\TBL_SYSINFO", "sysinfo" );
define( __NAMESPACE__."\\TBL_SYSCPU", "sysinfo_cpu" );
define( __NAMESPACE__."\\TBL_SYSMEM", "sysinfo_mem" );
define( __NAMESPACE__.'\\STATISTICS_DEBUG_LOG_MAXROWS', 10 );
class StatisticsManager {
private $_settings;
private $_logfile;
private $_db;
private $_connection_params;
private $_is_sqlite;
private $_sql_open_quote, $_sql_close_quote;
private $_upgrade_db_callbacks;
public function escape( $string ) {
if ( $this->isSQLite() )
return $this->_db->escapeString( $string );
else
return\mysql_real_escape_string( $string, $this->_db );
}
private function _tableExists( $name ) {
$rst = $this->_sqlExec( "SHOW TABLES LIKE '" . $name . "'" );
$result = $this->fetchArray( $rst );
$this->freeResult( $rst );
return is_array( $result ) && count( $result );
}
public function _sqlExec( $query, $noresult = false, $single_row = false ) {
$query = preg_replace( '/(.+);\s*$/', '$1', $query );
if ( $this->_db == null )
throw new MyException( 'Invalid database connection (' . print_r( $this->_db, true ) . ').' );
if ( empty( $query ) )
throw new MyException( 'Invalid SQL statement (empty).' );
$stats_debug_on = defined( __NAMESPACE__.'\\STATISTICS_DEBUG' ) && STATISTICS_DEBUG && defined( __NAMESPACE__.'\\STATISTICS_DEBUG_LOG' );
if ( $stats_debug_on && strlen( $query ) < MB ) {
$this->_logfile->writeLog( str_repeat( '-', 80 ) . PHP_EOL );
$this->_logfile->writeLog( sprintf( '[%s] %s' . PHP_EOL, date( DATETIME_FORMAT ), $query ) );
$this->_logfile->writeLog( str_repeat( '-', 80 ) . PHP_EOL );
}
$result = null;
if ( $this->_is_sqlite ) {
if ( $noresult )
$rst = @$this->_db->exec( $query );
elseif ( $single_row )
$rst = @$this->_db->querySingle( $query, true );
else
$rst = @$this->_db->query( $query );
$result = $rst;
} else {
$rst = @\mysql_query( $query, $this->_db );
if ( false === $result )
throw new MyException( \mysql_error(), \mysql_errno() );
if ( $single_row )
$result = $this->fetchArray( $rst );
else
$result = $rst;
}
if ( $stats_debug_on ) {
$result_array = array();
if ( is_resource( $rst ) || is_object( $rst ) ) {
$single_row && $this->seek( $rst, 0 ); 
while ( $data = $this->fetchArray( $rst ) ) {
if ( STATISTICS_DEBUG_LOG_MAXROWS == count( $result_array ) ) {
$result_array[] = sprintf( 
_esc( 'Result is limited to max %d rows (out of %s)' ), 
STATISTICS_DEBUG_LOG_MAXROWS, 
$this->isSQLite() ? '?' : mysql_affected_rows( $this->_db ) );
break;
} else
$result_array = $result_array + $data;
}
$this->seek( $rst, 0 ); 
} else
$result_array = $result;
$this->_logfile->writeLog( print_r( $result_array, true ) . PHP_EOL );
$this->_logfile->writeLog( str_repeat( '-', 80 ) . PHP_EOL );
}
return $result;
}
private function _lastInsertRowID( $unique = false, $tbl_name = '' ) {
if ( $unique ) {
if ( empty( $tbl_name ) )
return false;
$rst = $this->_sqlExec( 
"select id from " . TBL_PREFIX . "$tbl_name order by id desc limit 1;", 
false, 
true );
if ( is_array( $rst ) ) {
if ( isset( $rst['id'] ) )
return $rst['id'];
else
throw new MyException( 
sprintf( 
_esc( 'Cannot determine the `id` value of the last record of table %s' ), 
TBL_PREFIX . $tbl_name ) );
} else
return $rst;
}
if ( $this->_is_sqlite )
return $this->_db->lastInsertRowID();
else
return \mysql_insert_id( $this->_db );
}
private function _convertArgs( &$array ) {
$result = array();
if ( ! empty( $array ) )
foreach ( $array as $key => $value ) {
$key1 = defined( __NAMESPACE__ . '\\' . $key ) ? @constant( __NAMESPACE__ . '\\' . $key ) : $key;
if ( in_array( $key, array( 'METRIC_ACTION', 'job_status', 'job_state' ) ) ) {
$value1 = defined( __NAMESPACE__ . '\\' . $value ) ? @constant( __NAMESPACE__ . '\\' . $value ) : $value;
} else
$value1 = $value;
$result[$key1] = $value1;
}
return $result;
}
private function _getSQLColType( $col_type ) {
switch ( $col_type ) {
case COL_INT :
$result = 'INTEGER';
break;
case COL_NUMERIC :
$result = 'NUMERIC';
break;
case COL_REAL :
$result = 'REAL';
break;
case COL_TEXT10 :
$result = 'VARCHAR(10)';
break;
case COL_TEXT30 :
$result = 'VARCHAR(30)';
break;
case COL_TEXT50 :
$result = 'VARCHAR(50)';
break;
case COL_TEXT100 :
$result = 'VARCHAR(100)';
break;
case COL_BIGINT :
$result = 'BIGINT';
break;
default :
$result = 'VARCHAR(250)';
break;
}
return $result;
}
private function _createTable( $tbl_name, $field_defs ) {
if ( ! ( is_array( $field_defs ) && count( $field_defs ) > 0 ) )
return false;
$col_list = array();
$tbl_keys = array();
foreach ( $field_defs as $col_name => $col_type ) {
$col_ctr = null;
if ( is_array( $col_type ) ) {
if ( count( $col_type ) > 1 && ! empty( $col_type[1] ) )
$col_ctr = $col_type[1];
if ( count( $col_type ) > 2 && ( true === $col_type[2] ) )
$tbl_keys[] = $col_name;
$col_type = $col_type[0];
}
$col_list[] = '`' . $col_name . '` ' . $this->_getSQLColType( $col_type ) . ' ' . $col_ctr;
}
$sql = 'CREATE TABLE ' . TBL_PREFIX . $tbl_name . '(id INTEGER NOT NULL ' .
( $this->_is_sqlite ? 'PRIMARY KEY' : 'AUTO_INCREMENT' ) . ',' . implode( ',', $col_list ) .
( $this->_is_sqlite ? '' : ',PRIMARY KEY(id)' ) . ');';
$this->_sqlExec( $sql, true );
if ( $this->_tableExists( TBL_PREFIX . $tbl_name ) ) {
return false;
}
for ( $i = 0; $i < count( $tbl_keys ); $i++ )
$this->_sqlExec( 
'CREATE INDEX IX_' . TBL_PREFIX . $tbl_name . '_' . $i . ' ON ' . TBL_PREFIX . $tbl_name . '(' .
$tbl_keys[$i] . ');', 
true );
return true;
}
public function _createTestTable() {
$field_defs = array( 'dummyField' => COL_INT );
$tbl_name = uniqid( '_dummy_' );
$this->_createTable( $tbl_name, $field_defs );
if ( ! $this->_tableExists( TBL_PREFIX . $tbl_name ) ) {
return false;
}
$result = $this->_insertRecord( $tbl_name, array( 'dummyField' => 100 ) );
$this->_sqlExec( 'DROP TABLE ' . TBL_PREFIX . $tbl_name, true );
return false != intval( $result );
}
private function _createJobsTbl() {
$field_defs = array( 
'job_type' => array( COL_INT, null, true ), 
'mode' => array( COL_INT, null, true ), 
'compression_type' => COL_TEXT10, 
METRIC_COMPRESS_LEVEL => COL_INT, 
METRIC_CPU_SLEEP => COL_INT, 
METRIC_TOOLCHAIN => COL_TEXT10, 
METRIC_BZIP_VER => COL_TEXT10, 
METRIC_RATIO => COL_NUMERIC, 
'result_code' => COL_INT, 
'job_size' => COL_BIGINT, 
'volumes_count' => COL_INT, 
'files_count' => COL_INT, 
'job_status' => array( COL_INT, null, true ), 
'job_state' => array( COL_INT, null, true ), 
'started_time' => array( COL_INT, null, true ), 
'finish_time' => array( COL_INT, null, true ), 
'duration' => COL_INT, 
'avg_speed' => COL_REAL, 
'avg_cpu' => COL_REAL, 
'peak_cpu' => COL_REAL, 
'peak_disk' => COL_REAL, 
'peak_mem' => COL_REAL, 
'unique_id' => COL_TEXT30 );
$this->_createTable( TBL_JOBS, $field_defs );
}
private function _createSourcesTbl() {
$field_defs = array( 
'jobs_id' => array( COL_INT, null, true ), 
METRIC_SOURCE => array( COL_INT, null, true ), 
'path' => array( COL_TEXT, null ) );
$this->_createTable( TBL_SOURCES, $field_defs );
}
private function _createPathsTbl() {
$field_defs = array( 
'jobs_id' => array( COL_INT, null, true ), 
METRIC_OPERATION => array( COL_INT, null, true ), 
'path' => array( COL_TEXT, null ) );
$this->_createTable( TBL_PATHS, $field_defs );
}
private function _createStatsTbl() {
$field_defs = array( 
'jobs_id' => array( COL_INT, null, true ), 
'files_id' => array( COL_INT, null, true ), 
METRIC_ACTION => array( COL_INT, null, true ), 
METRIC_TIME => COL_INT, 
METRIC_SCRIPT_MEM_USAGE => COL_INT, 
METRIC_OPERATION => array( COL_INT, null, true ), 
METRIC_ERROR => COL_TEXT, 
'timestamp' => array( COL_INT, 'NOT NULL', true ) );
$this->_createTable( TBL_STATS, $field_defs );
}
private function _createSysinfoTbl() {
$field_defs = array( 
'os' => COL_TEXT50, 
'php_ver' => COL_TEXT50, 
'server_ver' => COL_TEXT50, 
'timestamp' => array( COL_INT, 'NOT NULL', true ) );
$this->_createTable( TBL_SYSINFO, $field_defs );
}
private function _createKeysTbl() {
$field_defs = array( 
'cipher' => COL_TEXT30, 
'key' => COL_TEXT100, 
'iv' => COL_TEXT100, 
'timestamp' => array( COL_INT, 'NOT NULL', true ) );
$this->_createTable( TBL_KEYS, $field_defs );
}
private function _createSysInfoArrayTbl( $tbl_name, $data ) {
if ( ! ( is_array( $data ) && count( $data ) > 0 ) )
return;
$data = str_replace( array( ' ', '(', ')' ), '_', $data );
$field_defs = array( 
'jobs_id' => array( COL_INT, 'NOT NULL', true ), 
'timestamp' => array( COL_INT, 'NOT NULL', true ) );
$field_defs = array_merge( $field_defs, array_combine( $data, array_fill( 0, count( $data ), COL_TEXT ) ) );
$this->_createTable( $tbl_name, $field_defs );
}
private function _createFilesTbl() {
$field_defs = array( 
'jobs_id' => array( COL_INT, null, true ), 
'sources_id' => array( COL_INT, null, true ), 
METRIC_FILENAME => COL_TEXT, 
METRIC_UNCOMPRESSED => COL_BIGINT, 
METRIC_RATIO => COL_REAL, 
METRIC_SIZE => COL_BIGINT, 
METRIC_DISK_FREE => COL_BIGINT, 
JOBTBL_FILE_CHECKSUM => COL_TEXT50 );
$this->_createTable( TBL_FILES, $field_defs );
}
private function _createSysinfoCpuTbl() {
$cpu = getCpuInfo();
if ( is_array( $cpu ) && count( $cpu ) > 0 ) {
$cpu = array_keys( $cpu[0] );
$this->_createSysInfoArrayTbl( TBL_SYSCPU, $cpu );
}
}
private function _createSysinfoMemTbl() {
$mem = getSystemMemoryInfo();
if ( is_array( $mem ) && count( $mem ) > 0 ) {
$mem = array_keys( $mem );
$this->_createSysInfoArrayTbl( TBL_SYSMEM, $mem );
}
}
private function _createDbTables() {
$this->_createJobsTbl();
$this->_createPathsTbl();
$this->_createSourcesTbl();
$this->_createStatsTbl();
$this->_createFilesTbl();
$this->_createSysinfoTbl();
$this->_createSysinfoCpuTbl();
$this->_createSysinfoMemTbl();
$this->_createKeysTbl();
}
private function _createDb( $params, $overwrite = false ) {
if ( empty( $params ) )
return null;
if ( is_string( $params ) ) {
$this->_is_sqlite = true;
$filename = $params;
$db_exists = file_exists( $filename );
$overwrite = $overwrite || ( $db_exists && filesize( $filename ) === 0 );
if ( $overwrite && $db_exists )
unlink( $filename );
$this->_db = new \SQLite3( $filename );
$this->_sql_open_quote = '[';
$this->_sql_close_quote = ']';
} else {
$this->_is_sqlite = false;
if ( ! is_array( $params ) )
return null;
$host = $params['host'];
$port = $params['port'];
$db_name = $params['db_name'];
$user = $params['user'];
$passwrod = $params['pwd'];
$this->_db = \mysql_pconnect( "$host:$port", $user, $passwrod );
if ( false == $this->_db )
throw new MyException( \mysql_error(), \mysql_errno() );
if ( ! \mysql_select_db( $db_name ) )
throw new MyException( \mysql_error(), \mysql_errno() );
$rst = $this->_sqlExec( "SHOW TABLES FROM `$db_name` LIKE '" . TBL_PREFIX . "%';" );
$db_exists = \mysql_num_rows( $rst ) > 0;
$this->freeResult( $rst );
$this->_sql_open_quote = '`';
$this->_sql_close_quote = '`';
if ( null == $this->_db )
throw new MyException( \mysql_error(), \mysql_errno() );
}
if ( ! $db_exists )
$this->_createDbTables();
return $this->_db;
}
public function upgrade_db() {
$result = array();
foreach ( $this->_upgrade_db_callbacks as $new_version => $callbacks ) {
ksort( $callbacks );
foreach ( $callbacks as $callback ) {
if ( ! _is_callable( $callback ) || true !== ( $e = _call_user_func( $callback, $this ) ) )
$result = $result + $e;
}
}
return empty( $result ) ? true : $result;
}
public function register_upgrade_callback( $callback, $new_version, $priority = -1 ) {
if ( _is_callable( $callback ) ) {
isset( $this->_upgrade_db_callbacks[$new_version] ) || $this->_upgrade_db_callbacks[$new_version] = array();
$this->_upgrade_db_callbacks[$new_version][$priority < 0 ? count( $this->_upgrade_db_callbacks ) - 1 : $priority] = $callback;
return true;
}
return false;
}
private function _insertRecord( $tbl_name, $array, $unique = false ) {
if ( empty( $array ) )
throw new MyException( 'Cannot insert into table ' . $tbl_name . '. The array of column=value is empty.' );
$columns = $this->_sql_open_quote .
implode( $this->_sql_close_quote . ',' . $this->_sql_open_quote, array_keys( $array ) ) .
$this->_sql_close_quote;
$_this_ = $this;
array_walk( $array, function ( &$item ) use(&$_this_ ) {
$item = $_this_->escape( $item );
} );
$values = '"' . implode( '","', array_values( $array ) ) . '"';
$select = 'INSERT INTO ' . TBL_PREFIX . $tbl_name . ' (' . $columns . ') ';
if ( ! $unique )
$sql = 'VALUES (' . $values . ');';
else {
$values = '';
$unique_cond = '';
foreach ( $array as $key => $value ) {
$values .= '"' . $value . '" AS ' . $key . ',';
if ( 'timestamp' == $key )
continue;
else
$unique_cond .= $this->_sql_open_quote . $key . $this->_sql_close_quote . '="' . $value . '" AND ';
}
$values = substr( $values, 0, strlen( $values ) - 1 );
if ( strlen( $unique_cond ) > 0 )
$unique_cond = substr( $unique_cond, 0, strlen( $unique_cond ) - 4 );
$sql = 'SELECT subqry.* FROM (SELECT' . $values . ')subqry WHERE NOT EXISTS(SELECT id from ' . TBL_PREFIX .
$tbl_name . ( strlen( $unique_cond ) > 0 ? ' WHERE ' . $unique_cond : '' ) . ');';
}
$this->_sqlExec( $select . $sql, true );
return $this->_lastInsertRowID( $unique, $tbl_name );
}
private function _pushPaths( $jobs_id, $array ) {
$array['jobs_id'] = $jobs_id;
return $this->_insertRecord( TBL_PATHS, $array, true ); 
}
private function _pushStat( $jobs_id, $timestamp, $array ) {
$move_fields = function ( $keys, &$source, &$dest ) {
foreach ( $keys as $key )
if ( isset( $source[$key] ) )
$dest[$key] = $source[$key];
$source = array_diff_assoc( $source, $dest );
};
$file_keys = array( 
METRIC_FILENAME, 
METRIC_UNCOMPRESSED, 
METRIC_RATIO, 
METRIC_SIZE, 
METRIC_DISK_FREE, 
JOBTBL_FILE_CHECKSUM );
$source_keys = array( METRIC_SOURCE, METRIC_SOURCEPATH );
$sources = array( 'jobs_id' => $jobs_id );
$files = array( 'jobs_id' => $jobs_id );
$move_fields( $source_keys, $array, $sources );
$files['sources_id'] = $this->_insertRecord( TBL_SOURCES, $sources, true );
$move_fields( $file_keys, $array, $files );
$array['files_id'] = $this->_insertRecord( TBL_FILES, $files, true ); 
$array[METRIC_SCRIPT_MEM_USAGE] = memory_get_usage();
$array['timestamp'] = $timestamp;
$array['jobs_id'] = $jobs_id;
return $this->_insertRecord( TBL_STATS, $array ); 
}
private function _pushSysinfo( $timestamp ) {
$array = array( 
'os' => PHP_OS, 
'php_ver' => PHP_VERSION, 
'server_ver' => $_SERVER['SERVER_SOFTWARE'], 
'timestamp' => $timestamp );
return $this->_insertRecord( TBL_SYSINFO, $array, true ); 
}
private function _pushKeys( $timestamp, $keys ) {
if ( empty( $keys ) || ! isset( $keys['cipher'] ) )
return;
$array = array( 'cipher' => $keys['cipher'], 'timestamp' => $timestamp );
isset( $keys['key'] ) && $array['key'] = $keys['key'];
isset( $keys['iv'] ) && $array['iv'] = $keys['iv'];
return $this->_insertRecord( TBL_KEYS, $array, true ); 
}
private function _pushDataArray( $tbl_name, $data_array, $jobs_id, $timestamp ) {
$array = array( 'timestamp' => $timestamp, 'jobs_id' => $jobs_id );
$keys = str_replace( array( ' ', '(', ')' ), '_', array_keys( $data_array ) );
$values = array_values( $data_array );
if ( count( $keys ) > 0 && count( $values ) > 0 )
$combined = array_combine( $keys, $values );
else
$combined = array();
$array = array_merge( $array, $combined );
return $this->_insertRecord( $tbl_name, $array, true ); 
}
private function _pushCpuInfo( $jobs_id, $timestamp ) {
$result = array();
$cpus = getCpuInfo();
foreach ( $cpus as $cpu )
$result[] = $this->_pushDataArray( TBL_SYSCPU, $cpu, $jobs_id, $timestamp );
return $result;
}
private function _pushMemInfo( $jobs_id, $timestamp ) {
$mem = getSystemMemoryInfo();
return $this->_pushDataArray( TBL_SYSMEM, $mem, $jobs_id, $timestamp );
}
function __construct( $params, $settings = null ) {
$this->_settings = $settings;
$this->_logfile = new LogFile( defined( __NAMESPACE__.'\\STATISTICS_DEBUG_LOG' ) ? STATISTICS_DEBUG_LOG : null, $settings );
$this->_upgrade_db_callbacks = array();
$this->_connection_params = $params;
$this->_is_sqlite = ! isset( $settings ) || ( 'sqlite' == $settings['historydb'] );
$this->_db = $this->_createDb( $params );
}
function __destruct() {
if ( $this->_is_sqlite )
$this->_db->close();
else
mysql_close( $this->_db );
}
public function onNewJobStarts( $job_type, $mode, $keys = null, $timestamp = null ) {
global $_branch_id_;
if ( null == $timestamp )
$timestamp = time();
$array = array( 
'job_type' => $job_type, 
'job_status' => JOB_STATUS_RUNNING, 
'started_time' => $timestamp, 
'mode' => $mode );
isset( $_branch_id_ ) && $array['unique_id'] = $_branch_id_;
$jobs_id = $this->_insertRecord( TBL_JOBS, $array );
$this->_pushSysinfo( $timestamp );
$this->_pushCpuInfo( $jobs_id, $timestamp );
$this->_pushMemInfo( $jobs_id, $timestamp );
$this->_pushKeys( $timestamp, $keys );
return $jobs_id; 
}
public function onJobEnds( $jobs_id, $params = null ) {
$params = $this->_convertArgs( $params );
$sql = 'select sum(' . TBL_PREFIX . TBL_FILES . '.uncompressed) AS uncompressed, avg(' . TBL_PREFIX . TBL_FILES .
'.ratio) AS ratio from ' . TBL_PREFIX . TBL_STATS . ' inner join ' . TBL_PREFIX . TBL_FILES . ' ON ' .
TBL_PREFIX . TBL_STATS . '.files_id=' . TBL_PREFIX . TBL_FILES . '.id where ' . TBL_PREFIX . TBL_STATS .
'.jobs_id=' . $jobs_id . ' and ' . TBL_PREFIX . TBL_STATS . '.action=' . METRIC_ACTION_COMPRESS . ';';
$rst1 = $this->_sqlExec( $sql, false, true );
$sql = 'select MAX(script_mem_usage) AS script_mem_usage from ' . TBL_PREFIX . TBL_STATS . ' where jobs_id=' .
$jobs_id . ';';
$this->_sqlExec( $sql, false, true );
$sql = 'UPDATE ' . TBL_PREFIX . TBL_JOBS . ' SET ';
if ( ! empty( $params ) )
foreach ( $params as $key => $value )
$sql .= $key . '="' . $value . '",';
$duration = ! empty( $params ) && isset( $params['duration'] ) ? $params['duration'] : ( ( $this->_is_sqlite ? 'strftime(\'%s\',\'now\')' : 'now()' ) .
'-started_time' );
$sql .= ( ! empty( $rst1['uncompressed'] ) ? 'job_size=' . $rst1['uncompressed'] . ',' : '' ) .
( ! empty( $rst1['uncompressed'] ) ? 'avg_speed=' . $rst1['uncompressed'] . '/' . $duration . ',' : '' ) .
'peak_mem=' . memory_get_peak_usage() . ( ! empty( $rst1['ratio'] ) ? ',ratio=' . $rst1['ratio'] : '' ) .
',finish_time=' . $duration . '+started_time';
$sql .= ' WHERE id=' . $jobs_id . ';';
$this->_sqlExec( $sql, true );
}
public function addJobData( $jobs_id, $array ) {
$timestamp = time();
$this->_pushStat( $jobs_id, $timestamp, $this->_convertArgs( $array ) );
}
public function addJobPaths( $jobs_id, $array ) {
$this->_pushPaths( $jobs_id, $this->_convertArgs( $array ) );
}
public function queryData( $sql ) {
return $this->_sqlExec( $sql, false );
}
public function flushData() {
if ( $this->_is_sqlite ) {
if ( file_exists( $this->_connection_params ) )
$result = @unlink( $this->_connection_params );
} else {
$tables = array( 
TBL_PREFIX . TBL_SYSMEM, 
TBL_PREFIX . TBL_SYSCPU, 
TBL_PREFIX . TBL_SYSINFO, 
TBL_PREFIX . TBL_FILES, 
TBL_PREFIX . TBL_STATS, 
TBL_PREFIX . TBL_SOURCES, 
TBL_PREFIX . TBL_PATHS, 
TBL_PREFIX . TBL_JOBS, 
TBL_PREFIX . TBL_KEYS );
$result = $this->_sqlExec( sprintf( 'DROP TABLE IF EXISTS %s;', implode( ',', $tables ) ) );
$this->_createDbTables();
}
return $result !== false;
}
public function fetchArray( &$rst, $mode = 3 ) {
if ( ! ( is_resource( $rst ) || is_object( $rst ) ) ) {
if ( defined( __NAMESPACE__.'\\STATISTICS_DEBUG' ) && STATISTICS_DEBUG && defined( __NAMESPACE__.'\\STATISTICS_DEBUG_LOG' ) )
$this->_logfile->writeLog( print_r( $rst, 1 ) . ' is not resource' . PHP_EOL );
return false;
}
if ( $this->_is_sqlite )
return $rst->fetchArray( $mode );
else {
return mysql_fetch_array( $rst, $mode );
}
}
public function freeResult( &$rst ) {
if ( empty( $rst ) )
return false;
if ( $this->_is_sqlite )
return $rst->finalize();
else
return \mysql_free_result( $rst );
}
public function isSQLite() {
return $this->_is_sqlite;
}
public function getConnectionParams() {
return $this->_connection_params;
}
public function seek( &$rst, $offset = 0 ) {
if ( $this->_is_sqlite )
return $rst->reset();
else {
if ( \mysql_affected_rows( $this->_db ) )
return \mysql_data_seek( $rst, $offset );
}
return false;
}
public function getSettings() {
return $this->_settings;
}
public function basename( $str, $quote = true ) {
if ( $this->isSQLite() ) {
$this->_db->createFunction( 'basename', function ( $str ) {
return basename( $str );
} );
return sprintf( 'basename(%s%s%s)', $quote ? '"' : '', $str, $quote ? '"' : '' );
} else
return sprintf( 
'substring_index(%s%s%s, "%s", -1) ', 
$quote ? '"' : '', 
$str, 
$quote ? '"' : '', 
'\\' . DIRECTORY_SEPARATOR );
}
}
?>