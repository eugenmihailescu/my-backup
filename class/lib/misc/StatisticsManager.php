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
 * @version : 0.2.0-10 $
 * @commit  : bc79573e2975a220cb1cfbb08b16615f721a68c5 $
 * @author  : Eugen Mihailescu <eugenmihailescux@gmail.com> $
 * @date    : Mon Sep 14 21:14:57 2015 +0200 $
 * @file    : StatisticsManager.php $
 * 
 * @id      : StatisticsManager.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;
require_once FUNCTIONS_PATH . 'utils.php';
define ( "METRIC_ACTION_UNCOMPRESS", - 1 );
define ( "METRIC_ACTION_COMPRESS", 0 );
define ( "METRIC_ACTION_TRANSFER", 1 );
define ( "METRIC_ACTION_CLEANUP", 2 );
define ( "METRIC_ACTION", 'action' );
define ( "METRIC_FILENAME", 'filename' );
define ( "METRIC_MEDIAPATH", 'path' );
define ( "METRIC_UNCOMPRESSED", 'uncompressed' );
define ( "METRIC_RATIO", 'ratio' );
define ( "METRIC_COMPRESS_TYPE", 'compression_type' );
define ( "METRIC_COMPRESS_LEVEL", 'compression_level' );
define ( "METRIC_CPU_SLEEP", 'cpu_sleep' );
define ( "METRIC_TOOLCHAIN", 'toolchain' );
define ( "METRIC_BZIP_VER", 'bzip_ver' );
define ( "METRIC_TIME", 'operation_time' );
define ( "METRIC_SIZE", 'filesize' );
define ( "METRIC_DISK_FREE", 'disk_free' );
define ( "JOBTBL_FILE_CHECKSUM", 'checksum' );
define ( "METRIC_PHP_MEM_USAGE", 'php_mem_usage' );
define ( "METRIC_SCRIPT_MEM_USAGE", 'script_mem_usage' );
define ( "METRIC_OPERATION", 'operation' );
define ( "METRIC_ERROR", 'error' );
define ( "METRIC_SOURCE", 'source_type' );
define ( "METRIC_SOURCEPATH", 'path' );
define ( "JOB_STATUS_RUNNING", 0 );
define ( "JOB_STATUS_ABORTED", 1 );
define ( "JOB_STATUS_FINISHED", 2 );
define ( "JOB_STATUS_SUSPENDED", 3 );
define ( "JOB_STATE_COMPLETED", 0 );
define ( "JOB_STATE_PARTIAL", 1 );
define ( "JOB_STATE_FAILED", 2 );
define ( "COL_INT", 0 );
define ( "COL_REAL", 1 );
define ( "COL_NUMERIC", 2 );
define ( "COL_TEXT10", 3 );
define ( "COL_TEXT50", 4 );
define ( "COL_TEXT", 5 );
define ( "COL_TEXT30", 6 );
define ( "COL_TEXT100", 7 );
! defined ( 'TBL_PREFIX' ) && define ( "TBL_PREFIX", "wpmybk_" );
define ( "TBL_JOBS", "jobs" );
define ( "TBL_SOURCES", "sources" );
define ( "TBL_FILES", "files" );
define ( "TBL_PATHS", "paths" );
define ( "TBL_STATS", "stats" );
define ( "TBL_KEYS", "keys" );
define ( "TBL_SYSINFO", "sysinfo" );
define ( "TBL_SYSCPU", "sysinfo_cpu" );
define ( "TBL_SYSMEM", "sysinfo_mem" );
class StatisticsManager {
private $_logfile;
private $_db;
private $_connection_params;
private $_is_sqlite;
private $_sql_open_quote, $_sql_close_quote;
private function _sqlExec($query, $noresult = false, $single_row = false) {
if ($this->_db == null)
throw new MyException ( 'Invalid database connection (' . print_r ( $this->_db, true ) . ').' );
if (empty ( $query ))
throw new MyException ( 'Invalid SQL statement (empty).' );
$stats_debug_on = defined ( 'STATISTICS_DEBUG' ) && STATISTICS_DEBUG && defined ( 'STATISTICS_DEBUG_LOG' );
if ($stats_debug_on && strlen ( $query ) < MB) {
$this->_logfile->writeLog ( str_repeat ( '-', 80 ) . PHP_EOL );
$this->_logfile->writeLog ( sprintf ( '[%s] %s' . PHP_EOL, date ( DATETIME_FORMAT ), $query ) );
$this->_logfile->writeLog ( str_repeat ( '-', 80 ) . PHP_EOL );
}
$result = null;
if ($this->_is_sqlite) {
if ($noresult)
$result = @$this->_db->exec ( $query );
elseif ($single_row)
$result = @$this->_db->querySingle ( $query, true );
else
$result = @$this->_db->query ( $query );
} else {
$result = @\mysql_query ( $query, $this->_db );
if (false === $result)
throw new MyException (\mysql_error (),\mysql_errno () );
if ($single_row)
$result = $this->fetchArray ( $result, MYSQL_ASSOC );
}
if ($stats_debug_on) {
$result_array = array ();
if (is_object ( $result )) {
while ( $data = $result->fetchArray ( MYSQL_ASSOC | SQLITE3_ASSOC ) )
$result_array += $data;
$result->reset (); 
} else
$result_array = $result;
$this->_logfile->writeLog ( print_r ( $result_array, true ) . PHP_EOL );
$this->_logfile->writeLog ( str_repeat ( '-', 80 ) . PHP_EOL );
}
return $result;
}
private function _lastInsertRowID() {
if ($this->_is_sqlite)
return $this->_db->lastInsertRowID ();
else
return\mysql_insert_id ( $this->_db );
}
private function _convertArgs(&$array) {
$result = array ();
if (! empty ( $array ))
foreach ( $array as $key => $value ) {
$key1 = defined ( $key ) ? constant ( $key ) : $key;
if (in_array ( $key, array (
'METRIC_ACTION',
'job_status',
'job_state' 
) )) {
$value1 = defined ( $value ) ? constant ( $value ) : $value;
} else
$value1 = $value;
$result [$key1] = $value1;
}
return $result;
}
private function _getSQLColType($col_type) {
switch ($col_type) {
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
default :
$result = 'VARCHAR(250)';
break;
}
return $result;
}
private function _createTable($tbl_name, $field_defs) {
if (! (is_array ( $field_defs ) && count ( $field_defs ) > 0))
return;
$col_list = array ();
$tbl_keys = array ();
foreach ( $field_defs as $col_name => $col_type ) {
$col_ctr = null;
if (is_array ( $col_type )) {
if (count ( $col_type ) > 1 && ! empty ( $col_type [1] ))
$col_ctr = $col_type [1];
if (count ( $col_type ) > 2 && (true === $col_type [2]))
$tbl_keys [] = $col_name;
$col_type = $col_type [0];
}
$col_list [] = $col_name . ' ' . $this->_getSQLColType ( $col_type ) . ' ' . $col_ctr;
}
$sql = 'CREATE TABLE ' . TBL_PREFIX . $tbl_name . '(id INTEGER NOT NULL ' . ($this->_is_sqlite ? 'PRIMARY KEY' : 'AUTO_INCREMENT') . ',' . implode ( ',', $col_list ) . ($this->_is_sqlite ? '' : ',PRIMARY KEY(id)') . ');';
$this->_sqlExec ( $sql, true );
for($i = 0; $i < count ( $tbl_keys ); $i ++)
$this->_sqlExec ( 'CREATE INDEX IX_' . TBL_PREFIX . $tbl_name . '_' . $i . ' ON ' . TBL_PREFIX . $tbl_name . '(' . $tbl_keys [$i] . ');', true );
}
private function _createJobsTbl() {
$field_defs = array (
'job_type' => array (
COL_INT,
null,
true 
),
'mode' => array (
COL_INT,
null,
true 
),
'compression_type' => COL_TEXT10,
METRIC_COMPRESS_LEVEL => COL_INT,
METRIC_CPU_SLEEP => COL_INT,
METRIC_TOOLCHAIN => COL_TEXT10,
METRIC_BZIP_VER => COL_TEXT10,
METRIC_RATIO => COL_NUMERIC,
'result_code' => COL_INT,
'job_size' => COL_INT,
'volumes_count' => COL_INT,
'files_count' => COL_INT,
'job_status' => array (
COL_INT,
null,
true 
),
'job_state' => array (
COL_INT,
null,
true 
),
'started_time' => array (
COL_INT,
null,
true 
),
'finish_time' => array (
COL_INT,
null,
true 
),
'duration' => COL_INT,
'avg_speed' => COL_REAL,
'avg_cpu' => COL_REAL,
'peak_cpu' => COL_REAL,
'peak_disk' => COL_REAL,
'peak_mem' => COL_REAL,
'unique_id' => COL_TEXT30 
);
$this->_createTable ( TBL_JOBS, $field_defs );
}
private function _createSourcesTbl() {
$field_defs = array (
'jobs_id' => array (
COL_INT,
null,
true 
),
METRIC_SOURCE => array (
COL_INT,
null,
true 
),
'path' => array (
COL_TEXT,
null 
) 
);
$this->_createTable ( TBL_SOURCES, $field_defs );
}
private function _createPathsTbl() {
$field_defs = array (
'jobs_id' => array (
COL_INT,
null,
true 
),
METRIC_OPERATION => array (
COL_INT,
null,
true 
),
'path' => array (
COL_TEXT,
null 
) 
);
$this->_createTable ( TBL_PATHS, $field_defs );
}
private function _createStatsTbl() {
$field_defs = array (
'jobs_id' => array (
COL_INT,
null,
true 
),
'files_id' => array (
COL_INT,
null,
true 
),
METRIC_ACTION => array (
COL_INT,
null,
true 
),
METRIC_TIME => COL_INT,
METRIC_SCRIPT_MEM_USAGE => COL_INT,
METRIC_OPERATION => array (
COL_INT,
null,
true 
),
METRIC_ERROR => COL_TEXT,
'timestamp' => array (
COL_INT,
'NOT NULL',
true 
) 
);
$this->_createTable ( TBL_STATS, $field_defs );
}
private function _createSysinfoTbl() {
$field_defs = array (
'os' => COL_TEXT50,
'php_ver' => COL_TEXT50,
'server_ver' => COL_TEXT50,
'timestamp' => array (
COL_INT,
'NOT NULL',
true 
) 
);
$this->_createTable ( TBL_SYSINFO, $field_defs );
}
private function _createKeysTbl() {
$field_defs = array (
'cipher' => COL_TEXT30,
'key' => COL_TEXT100,
'iv' => COL_TEXT100,
'timestamp' => array (
COL_INT,
'NOT NULL',
true 
) 
);
$this->_createTable ( TBL_KEYS, $field_defs );
}
private function _createSysInfoArrayTbl($tbl_name, $data) {
if (! (is_array ( $data ) && count ( $data ) > 0))
return;
$data = str_replace ( array (
' ',
'(',
')' 
), '_', $data );
$field_defs = array (
'jobs_id' => array (
COL_INT,
'NOT NULL',
true 
),
'timestamp' => array (
COL_INT,
'NOT NULL',
true 
) 
);
$field_defs = array_merge ( $field_defs, array_combine ( $data, array_fill ( 0, count ( $data ), COL_TEXT ) ) );
$this->_createTable ( $tbl_name, $field_defs );
}
private function _createFilesTbl() {
$field_defs = array (
'jobs_id' => array (
COL_INT,
null,
true 
),
'sources_id' => array (
COL_INT,
null,
true 
),
METRIC_FILENAME => COL_TEXT,
METRIC_UNCOMPRESSED => COL_INT,
METRIC_RATIO => COL_REAL,
METRIC_SIZE => COL_INT,
METRIC_DISK_FREE => COL_INT,
JOBTBL_FILE_CHECKSUM => COL_INT 
);
$this->_createTable ( TBL_FILES, $field_defs );
}
private function _createSysinfoCpuTbl() {
$cpu = getCpuInfo ();
if (is_array ( $cpu ) && count ( $cpu ) > 0) {
$cpu = array_keys ( $cpu [0] );
$this->_createSysInfoArrayTbl ( TBL_SYSCPU, $cpu );
}
}
private function _createSysinfoMemTbl() {
$mem = getSystemMemoryInfo ();
if (is_array ( $mem ) && count ( $mem ) > 0) {
$mem = array_keys ( $mem );
$this->_createSysInfoArrayTbl ( TBL_SYSMEM, $mem );
}
}
private function _createDbTables() {
$this->_createJobsTbl ();
$this->_createPathsTbl ();
$this->_createSourcesTbl ();
$this->_createStatsTbl ();
$this->_createFilesTbl ();
$this->_createSysinfoTbl ();
$this->_createSysinfoCpuTbl ();
$this->_createSysinfoMemTbl ();
$this->_createKeysTbl ();
}
private function _createDb($params, $overwrite = false) {
if (empty ( $params ))
return null;
if (is_string ( $params )) {
$this->_is_sqlite = true;
$filename = $params;
$db_exists = file_exists ( $filename );
$overwrite = $overwrite || ($db_exists && filesize ( $filename ) === 0);
if ($overwrite && $db_exists)
unlink ( $filename );
$this->_db = new \SQLite3 ( $filename );
$this->_sql_open_quote = '[';
$this->_sql_close_quote = ']';
} else {
$this->_is_sqlite = false;
if (! is_array ( $params ))
return null;
$host = $params ['host'];
$port = $params ['port'];
$db_name = $params ['db_name'];
$user = $params ['user'];
$passwrod = $params ['pwd'];
$this->_db =\mysql_pconnect ( "$host:$port", $user, $passwrod );
if (false == $this->_db)
throw new MyException (\mysql_error (),\mysql_errno () );
if (!\mysql_select_db ( $db_name ))
throw new MyException (\mysql_error (),\mysql_errno () );
$rst = $this->_sqlExec ( "SHOW TABLES FROM `$db_name` LIKE '" . TBL_PREFIX . "%';" );
$db_exists =\mysql_num_rows ( $rst ) > 0;
$this->freeResult ( $rst );
$this->_sql_open_quote = '`';
$this->_sql_close_quote = '`';
if (null == $this->_db)
throw new MyException (\mysql_error (),\mysql_errno () );
}
if (! $db_exists)
$this->_createDbTables ();
return $this->_db;
}
private function _insertRecord($tbl_name, $array, $unique = false) {
if (empty ( $array ))
throw new MyException ( 'Cannot insert into table ' . $tbl_name . '. The array of column=value is empty.' );
$columns = $this->_sql_open_quote . implode ( $this->_sql_close_quote . ',' . $this->_sql_open_quote, array_keys ( $array ) ) . $this->_sql_close_quote;
$values = '"' . implode ( '","', array_values ( $array ) ) . '"';
$select = 'INSERT INTO ' . TBL_PREFIX . $tbl_name . ' (' . $columns . ') ';
if (! $unique)
$sql = 'VALUES (' . $values . ');';
else {
$values = '';
$unique_cond = '';
foreach ( $array as $key => $value ) {
$values .= '"' . $value . '" AS ' . $key . ',';
if ('timestamp' == $key)
continue;
else
$unique_cond .= $this->_sql_open_quote . $key . $this->_sql_close_quote . '="' . $value . '" AND ';
}
$values = substr ( $values, 0, strlen ( $values ) - 1 );
if (strlen ( $unique_cond ) > 0)
$unique_cond = substr ( $unique_cond, 0, strlen ( $unique_cond ) - 4 );
$sql = 'SELECT subqry.* FROM (SELECT' . $values . ')subqry WHERE NOT EXISTS(SELECT id from ' . TBL_PREFIX . $tbl_name . (strlen ( $unique_cond ) > 0 ? ' WHERE ' . $unique_cond : '') . ');';
}
$this->_sqlExec ( $select . $sql, true );
if (! $unique)
return $this->_lastInsertRowID ();
else {
$rst = $this->_sqlExec ( "select id from " . TBL_PREFIX . "$tbl_name order by id desc limit 1;", false, true );
if (is_array ( $rst ))
if (isset ( $rst ['id'] ))
return $rst ['id'];
else
throw new MyException ( sprintf ( _esc ( 'Cannot determine the `id` value of the last record of table %s' ), TBL_PREFIX . $tbl_name ) );
else
return $rst;
}
}
private function _pushPaths($jobs_id, $array) {
$array ['jobs_id'] = $jobs_id;
return $this->_insertRecord ( TBL_PATHS, $array, true ); 
}
private function _pushStat($jobs_id, $timestamp, $array) {
$move_fields = function ($keys, &$source, &$dest) {
foreach ( $keys as $key )
if (isset ( $source [$key] ))
$dest [$key] = $source [$key];
$source = array_diff_assoc ( $source, $dest );
};
$file_keys = array (
METRIC_FILENAME,
METRIC_UNCOMPRESSED,
METRIC_RATIO,
METRIC_SIZE,
METRIC_DISK_FREE,
JOBTBL_FILE_CHECKSUM 
);
$source_keys = array (
METRIC_SOURCE,
METRIC_SOURCEPATH 
);
$sources = array (
'jobs_id' => $jobs_id 
);
$files = array (
'jobs_id' => $jobs_id 
);
$move_fields ( $source_keys, $array, $sources );
$files ['sources_id'] = $this->_insertRecord ( TBL_SOURCES, $sources, true );
$move_fields ( $file_keys, $array, $files );
$array ['files_id'] = $this->_insertRecord ( TBL_FILES, $files, true ); 
$array [METRIC_SCRIPT_MEM_USAGE] = memory_get_usage ();
$array ['timestamp'] = $timestamp;
$array ['jobs_id'] = $jobs_id;
return $this->_insertRecord ( TBL_STATS, $array ); 
}
private function _pushSysinfo($timestamp) {
$array = array (
'os' => PHP_OS,
'php_ver' => PHP_VERSION,
'server_ver' => $_SERVER ['SERVER_SOFTWARE'],
'timestamp' => $timestamp 
);
return $this->_insertRecord ( TBL_SYSINFO, $array, true ); 
}
private function _pushKeys($timestamp, $keys) {
if (empty ( $keys ) || ! isset ( $keys ['cipher'] ))
return;
$array = array (
'cipher' => $keys ['cipher'],
'key' => $keys ['key'],
'iv' => $keys ['iv'],
'timestamp' => $timestamp 
);
return $this->_insertRecord ( TBL_KEYS, $array, true ); 
}
private function _pushDataArray($tbl_name, $data_array, $jobs_id, $timestamp) {
$array = array (
'timestamp' => $timestamp,
'jobs_id' => $jobs_id 
);
$keys = str_replace ( array (
' ',
'(',
')' 
), '_', array_keys ( $data_array ) );
$values = array_values ( $data_array );
if (count ( $keys ) > 0 && count ( $values ) > 0)
$combined = array_combine ( $keys, $values );
else
$combined = array ();
$array = array_merge ( $array, $combined );
return $this->_insertRecord ( $tbl_name, $array, true ); 
}
private function _pushCpuInfo($jobs_id, $timestamp) {
$result = array ();
$cpus = getCpuInfo ();
foreach ( $cpus as $cpu )
$result [] = $this->_pushDataArray ( TBL_SYSCPU, $cpu, $jobs_id, $timestamp );
return $result;
}
private function _pushMemInfo($jobs_id, $timestamp) {
$mem = getSystemMemoryInfo ();
return $this->_pushDataArray ( TBL_SYSMEM, $mem, $jobs_id, $timestamp );
}
function __construct($params, $settings = null) {
$this->_logfile = new LogFile ( defined ( 'STATISTICS_DEBUG_LOG' ) ? STATISTICS_DEBUG_LOG : null, $settings );
$this->_connection_params = $params;
$this->_is_sqlite = true;
$this->_db = $this->_createDb ( $params );
}
function __destruct() {
if ($this->_is_sqlite)
$this->_db->close ();
else
mysql_close ( $this->_db );
}
public function onNewJobStarts($job_type, $mode, $keys = null, $timestamp = null) {
global $_branch_id_;
if (null == $timestamp)
$timestamp = time ();
$array = array (
'job_type' => $job_type,
'job_status' => JOB_STATUS_RUNNING,
'started_time' => $timestamp,
'mode' => $mode 
);
isset ( $_branch_id_ ) && $array ['unique_id'] = $_branch_id_;
$jobs_id = $this->_insertRecord ( TBL_JOBS, $array );
$this->_pushSysinfo ( $timestamp );
$this->_pushCpuInfo ( $jobs_id, $timestamp );
$this->_pushMemInfo ( $jobs_id, $timestamp );
$this->_pushKeys ( $timestamp, $keys );
return $jobs_id; 
}
public function onJobEnds($jobs_id, $params = null) {
$params = $this->_convertArgs ( $params );
$sql = 'select sum(' . TBL_PREFIX . TBL_FILES . '.uncompressed) AS uncompressed, avg(' . TBL_PREFIX . TBL_FILES . '.ratio) AS ratio from ' . TBL_PREFIX . TBL_STATS . ' inner join ' . TBL_PREFIX . TBL_FILES . ' ON ' . TBL_PREFIX . TBL_STATS . '.files_id=' . TBL_PREFIX . TBL_FILES . '.id where ' . TBL_PREFIX . TBL_STATS . '.jobs_id=' . $jobs_id . ' and ' . TBL_PREFIX . TBL_STATS . '.action=' . METRIC_ACTION_COMPRESS . ';';
$rst1 = $this->_sqlExec ( $sql, false, true );
$sql = 'select MAX(script_mem_usage) AS script_mem_usage from ' . TBL_PREFIX . TBL_STATS . ' where jobs_id=' . $jobs_id . ';';
$this->_sqlExec ( $sql, false, true );
$sql = 'UPDATE ' . TBL_PREFIX . TBL_JOBS . ' SET ';
if (! empty ( $params ))
foreach ( $params as $key => $value )
$sql .= $key . '="' . $value . '",';
$duration = ! empty ( $params ) && isset ( $params ['duration'] ) ? $params ['duration'] : (($this->_is_sqlite ? 'strftime(\'%s\',\'now\')' : 'now()') . '-started_time');
$sql .= (! empty ( $rst1 ['uncompressed'] ) ? 'job_size=' . $rst1 ['uncompressed'] . ',' : '') . (! empty ( $rst1 ['uncompressed'] ) ? 'avg_speed=' . $rst1 ['uncompressed'] . '/' . $duration . ',' : '') . 'peak_mem=' . memory_get_peak_usage () . (! empty ( $rst1 ['ratio'] ) ? ',ratio=' . $rst1 ['ratio'] : '') . ',finish_time=' . $duration . '+started_time';
$sql .= ' WHERE id=' . $jobs_id . ';';
$this->_sqlExec ( $sql, true );
}
public function addJobData($jobs_id, $array) {
$timestamp = time ();
$this->_pushStat ( $jobs_id, $timestamp, $this->_convertArgs ( $array ) );
}
public function addJobPaths($jobs_id, $array) {
$this->_pushPaths ( $jobs_id, $this->_convertArgs ( $array ) );
}
public function queryData($sql) {
return $this->_sqlExec ( $sql, false );
}
public function flushData() {
if ($this->_is_sqlite) {
if (file_exists ( $this->_connection_params ))
$result = @unlink ( $this->_connection_params );
} else {
$result = $this->_sqlExec ( sprintf ( 'DROP TABLE IF EXISTS %s,%s,%s,%s,%s,%s;', TBL_PREFIX . TBL_SYSMEM, TBL_PREFIX . TBL_SYSCPU, TBL_PREFIX . TBL_SYSINFO, TBL_PREFIX . TBL_FILES, TBL_PREFIX . TBL_STATS, TBL_PREFIX . TBL_JOBS ) );
$this->_createDbTables ();
}
return $result !== false;
}
public function fetchArray($rst, $mode = 3) {
if (! is_object ( $rst ))
return false;
if ($this->_is_sqlite)
return $rst->fetchArray ( $mode );
else
return\mysql_fetch_array ( $rst, $mode );
}
public function freeResult($rst) {
if (empty ( $rst ))
return false;
if ($this->_is_sqlite)
return $rst->finalize ();
else
return\mysql_free_result ( $rst );
}
public function isSQLite() {
return $this->_is_sqlite;
}
public function getConnectionParams() {
return $this->_connection_params;
}
}
?>
