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
 * @file    : MySQLBackupHandler.php $
 * 
 * @id      : MySQLBackupHandler.php | Tue Dec 13 06:40:49 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

require_once FUNCTIONS_PATH . 'utils.php';
is_wp() && require_once\ABSPATH . 'wp-config.php';
class MySQLBackupHandler {
private $_options;
private $_link;
private $_mysql_obj;
private $_output_clbk;
private $_maint_end_clbk;
private $_progress_clbk;
private $_abort_clbk;
private $_newarc_clbk;
private $_compress_clbk;
private $_nocompress;
function __construct( $options = null ) {
global $settings;
$this->_options = empty( $options ) ? $settings : $options;
$this->_nocompress = ( $o = getParam( $this->_options, "nocompress", null ) ) ? explode( ",", $o ) : array();
$this->_output_clbk = null;
$this->_maint_end_clbk = null;
$this->_progress_clbk = null;
$this->_abort_clbk = null;
$this->_newarc_clbk = null;
$this->_compress_clbk = null;
$this->_mysql_obj = new MySQLWrapper( $this->_options );
$this->_link = $this->_mysql_obj->connect();
}
function __destruct() {
$this->_link && $this->_mysql_obj->disconnect();
$this->_mysql_obj = null;
}
private function _outputCallback( $table_name, $cmd, $msg_type, $msg_text ) {
_is_callable( $this->_output_clbk ) &&
_call_user_func( $this->_output_clbk, $table_name, $cmd, $msg_type, $msg_text );
}
private function _progressCallback( 
$provider, 
$filename, 
$bytes, 
$total_bytes, 
$ptype = 0, 
$running = 1, 
$reset_timer = false ) {
_is_callable( $this->_progress_clbk ) && _call_user_func( 
$this->_progress_clbk, 
$provider, 
$filename, 
$bytes, 
$total_bytes, 
$ptype, 
$running, 
$reset_timer );
}
private function _dumpMySqlDb( $name, $pattern = '.+', $format = 'sql' ) {
$mysql_maint = isNull( $this->_options, 'mysql_maint', false );
if ( $mysql_maint ) {
if ( false != ( $tables = $this->getTableNameFromPattern( $pattern ) ) )
$this->_runTableMaintenance( $tables, $pattern );
}
$this->_outputCallback( $pattern, 'mysqldump', 'function', 'prepare' );
extract( $this->_getDBConnectionParams() ); 
$unlink_log = true;
$log_file = "$name.log";
$cmd = sprintf( 
"mysqldump -h %s -P %s -u %s -p%s -r %s --log-error %s %s %s %s", 
$mysql_host, 
$mysql_port, 
$mysql_user, 
$mysql_pwd, 
$name, 
$log_file, 
$mysql_db, 
implode( ' ', $tables ), 
'xml' == $format ? '--xml' : '' );
$result = - 1;
exec( $cmd, $output, $result ); 
if ( _is_callable( $this->_output_clbk ) ) {
if ( $result != 0 ) {
$this->_outputCallback( 
$pattern, 
'mysqldump', 
'error', 
_esc( '<b>mysqldump</b> terminated with errors' ) );
if ( _file_exists( $log_file ) && filesize( $log_file ) < 255 ) {
$err_msg = file_get_contents( $log_file );
$this->_outputCallback( $pattern, 'mysqldump', 'error', $err_msg );
} else {
$unlink_log = false;
$this->_outputCallback( 
$pattern, 
'mysqldump', 
'error', 
_esc( "The error message exeeds 255 chars and thus cannot be printed here." ) );
$this->_outputCallback( 
$pattern, 
'mysqldump', 
'error', 
sprintf( 
_esc( "The sqldump file and the error log file are saved at %s respectively ***.log" ), 
$name ) );
}
} else {
$this->_outputCallback( $pattern, 'mysqldump', 'info', _esc( 'completed successfuly' ) );
}
}
if ( $unlink_log && _file_exists( $log_file ) )
unlink( $log_file );
return $result == 0;
}
private function _formatTableToXML( $statement, $db_name, $table ) {
$db_collation = $this->_mysql_obj->get_param( 'mysql_collate' );
$db_charset = $this->_mysql_obj->get_param( 'mysql_charset' );
if ( $this->_link && $res = $this->_mysql_obj->query( 
'SELECT @@character_set_database as charset, @@collation_database as collation' ) ) {
if ( $row = $this->_mysql_obj->fetch_row( $rst ) ) {
$db_collation = $row[1];
$db_charset = $row[0];
}
$this->_mysql_obj->free_result( $rst );
}
$result = '<!-- ' . $table . ' schema -->' . PHP_EOL;
$result .= '<pma:structure_schemas>' . PHP_EOL;
$result .= '<pma:database name="' . $db_name . '" collation="' . $db_collation . '" charset="' . $db_charset .
'">' . PHP_EOL;
$result .= sprintf( '<pma:table name="%s">', $table );
$result .= htmlspecialchars( $statement );
$result .= sprintf( '</pma:table>' . PHP_EOL . '</pma:database>' . PHP_EOL . '</pma:structure_schemas>' ) .
PHP_EOL . PHP_EOL;
$result .= '<!-- table\'s data -->' . PHP_EOL;
return $result;
}
private function _formatRowToXML( $row, $db_name, $table ) {
$bin2hex = function ( $str ) {
$result = '';
$has_bin = false;
for ( $i = 0; $i < strlen( $str ); $i++ ) {
$o = ord( $str[$i] );
$is_bin = $o < 32 || $o > 127;
$has_bin |= $is_bin;
$result .= $is_bin ? '\\' . $o : $str[$i];
}
return $has_bin ? $result : $str;
};
$result = sprintf( '<table name="%s">', $table ) . PHP_EOL;
foreach ( $row as $colname => $value ) {
$result .= sprintf( 
'<column name="%s">%s</column>', 
$colname, 
$bin2hex( $this->_mysql_obj->escape_sql_string( $value ) ) ) . PHP_EOL;
}
$result .= '</table>' . PHP_EOL;
return $result;
}
private function _getTableDefinition( $table_name ) {
$defs = array();
if ( $rst = $this->_mysql_obj->query( 'DESCRIBE ' . $table_name ) ) {
while ( $row = $this->_mysql_obj->fetch_row( $rst ) ) {
$defs[$row[0]] = array( 
'type' => preg_replace( '/([^\(]+).*/', '$1', $row[1] ), 
'allow_null' => strToBool( $row[2] ), 
'pk' => ! empty( $row[3] ), 
'default' => $row[4] );
}
$this->_mysql_obj->free_result( $rst );
}
return $defs;
}
private function is_sql_numeric( $col_def ) {
$col_type = is_array( $col_def ) && isset( $col_def['type'] ) ? $col_def['type'] : $col_def;
return in_array( 
$col_type, 
array( 'int', 'bigint', 'double', 'decimal', 'float', 'real', 'smallint', 'tinyint' ) );
}
private function _fix_data( &$row, $table_def ) {
$is_string = function ( $col_def ) {
return in_array( 
$col_def['type'], 
array( 'datetime', 'date', 'time', 'char', 'nvarchar', 'varchar', 'text', 'tinytext', 'longtext' ) );
};
foreach ( $table_def as $col_name => $col_def ) {
if ( ! $col_def['pk'] )
if ( empty( $row[$col_name] ) ) {
$default = NULL;
if ( $this->is_sql_numeric( $col_def ) ) {
$default = 0;
} elseif ( $is_string( $col_def ) ) {
$default = '';
}
$row[$col_name] = empty( $col_def['default'] ) ? ( $col_def['allow_null'] ? $row[$col_name] : $default ) : $col_def['default'];
} else {
$is_string( $col_def ) && $row[$col_name] = addslashes( $row[$col_name] );
}
}
}
private function _getTableScript( $fname, $pattern = '.+', $format = 'sql' ) {
$ok = false;
$mysql_maint = isNull( $this->_options, 'mysql_maint', false );
$db_name = $this->_mysql_obj->get_param( 'mysql_db' );
$tables = $this->getTableNameFromPattern( $pattern, false, true );
if ( empty( $tables ) )
$this->_outputCallback( 
$pattern, 
null, 
'error', 
sprintf( _esc( 'Could not find any table with the pattern %s within database %s' ), $pattern, $db_name ) );
if ( is_array( $tables ) ) {
if ( $mysql_maint )
$this->_runTableMaintenance( $tables, $pattern );
_is_dir( dirname( $fname ) ) || mkdir( dirname( $fname ), 0770, true );
if ( false !== ( $fw = fopen( $fname, 'w' ) ) ) {
if ( 'xml' == $format ) {
$result = '<?xml version="1.0"?>' . PHP_EOL;
$result .= '<!--' . PHP_EOL;
$result .= '- ' . WPMYBACKUP . ' XML Dump' . PHP_EOL;
$result .= '- version ' . APP_VERSION_ID . PHP_EOL;
$result .= '- ' . APP_PLUGIN_URI . PHP_EOL;
$result .= '- ' . PHP_EOL;
$result .= '- host: ' . gethostname() . PHP_EOL;
$result .= '- created: ' . date( 'r' ) . PHP_EOL;
$info = $this->_mysql_obj->get_server_info();
$result .= '- MySQL version: ' . $info['version'] . PHP_EOL;
$result .= '- PHP version: ' . PHP_VERSION . PHP_EOL;
$result .= '-->' . PHP_EOL . PHP_EOL;
$result .= '<pma_xml_export version="1.0" xmlns:pma="' . htmlspecialchars( 
$_SERVER['HTTP_REFERER'] ) . '">' . PHP_EOL;
fwrite( $fw, $result );
}
foreach ( $tables as $table ) {
$table_defs = $this->_getTableDefinition( $table );
$tables_cols = array_keys( $table_defs );
if ( $rst = $this->_mysql_obj->query( 'SELECT * FROM ' . $table ) ) {
$num_fields = $this->_mysql_obj->get_cols_count( $rst );
} else
$num_fields = 0;
if ( 'sql' == $format ) {
$result = 'DROP TABLE IF EXISTS ' . $table . ';';
fwrite( $fw, $result );
}
if ( $rst1 = $this->_mysql_obj->query( 'SHOW CREATE TABLE ' . $table ) ) {
if ( $row2 = $this->_mysql_obj->fetch_row( $rst1 ) )
$result = PHP_EOL . PHP_EOL . $row2[1] . ";" . PHP_EOL . PHP_EOL;
else
$result = '';
$this->_mysql_obj->free_result( $rst1 );
'xml' == $format && $result = $this->_formatTableToXML( $result, $db_name, $table );
fwrite( $fw, $result );
$rec_count = 0;
if ( $rst )
for ( $i = 0; $i < $num_fields; $i++ )
while ( $row = $this->_mysql_obj->fetch_array( $rst ) ) {
$this->_fix_data( $row, $table_defs );
if ( 'xml' == $format ) {
$result = $this->_formatRowToXML( $row, $db_name, $table );
$rec_count++;
} else {
$result = 'INSERT INTO ' . $table . ' VALUES(';
for ( $j = 0; $j < $num_fields; $j++ ) {
if ( NULL === $row[$j] )
$result .= 'NULL';
elseif ( ! $this->is_sql_numeric( $table_defs[$tables_cols[$j]]['type'] ) )
$result .= '"' . $this->_mysql_obj->escape_sql_string( $row[$j] ) . '"';
else
$result .= $row[$j];
if ( $j < ( $num_fields - 1 ) )
$result .= ',';
}
$result .= ");" . PHP_EOL;
}
fwrite( $fw, $result );
unset( $result );
unset( $row );
}
( 'xml' == $format ) && fwrite( 
$fw, 
sprintf( '<!-- ' . _esc( '%s record(s) exported' ) . ' -->', $rec_count ) );
}
fwrite( $fw, PHP_EOL . PHP_EOL . PHP_EOL );
$ok = true;
$rst && $this->_mysql_obj->free_result( $rst );
}
if ( 'xml' == $format ) {
$result = '</pma_xml_export>' . PHP_EOL;
fwrite( $fw, $result );
}
fclose( $fw );
}
}
return $ok;
}
private function _getDBConnectionParams() {
$result = array();
$params = array( 
'mysql_format', 
'mysql_charset', 
'mysql_collate', 
'mysql_host', 
'mysql_port', 
'mysql_user', 
'mysql_pwd', 
'mysql_db' );
foreach ( $params as $p )
$result[$p] = $this->_mysql_obj->get_param( $p );
return $result;
}
private function _runTableMaintenance( $tables, $pattern ) {
$this->_outputCallback( $pattern, 'table maintenance', 'function', 'prepare' );
$result = $this->execTableMaintenance( $tables );
is_array( $result ) && _is_callable( $this->_maint_end_clbk ) &&
_call_user_func( $this->_maint_end_clbk, $result );
}
public function getServerInfo() {
return $this->_mysql_obj->get_server_info();
}
public function getDbSize() {
return $this->_mysql_obj->get_database_size( '', getMySQLTableNamesWhereByPattern( $this->_options['tables'] ) );
}
public function getTableNameFromPattern( $pattern, $close_link = false, $order_by_name = false ) {
return getMySQLTableNamesFromPattern( $pattern, $this->_mysql_obj, $this->_options, false, $order_by_name );
}
public function downloadSqlScript( $path, $pattern, $name, $type, $level ) {
if ( ! empty( $pattern ) ) {
if ( empty( $name ) )
$name = uniqid( 'mysql-db-bak_', MORE_ENTROPY );
$name = addTrailingSlash( $path ) .
sprintf( '%s-%s.' . $this->_mysql_obj->get_param( 'mysql_format' ), $name, date( "Ymd-His" ) );
if ( empty( $type ) )
$type = BZ2;
if ( empty( $level ) )
$level = 9;
$arcs = $this->compressMySQLScript( $name, $type, $level, 0, $pattern );
if ( null == $arcs ) {
_pesc( "It seems I cannot connect MySQL for the time being. Please try later..." );
echo "<script>setTimeout(function(){history.back();},3000);</script>";
exit();
}
redirectFileDownload( 
$arcs[0]['name'], 
"application/x-" . ( BZ2 == $type ? 'bzip2' : ( GZ == $type ? 'gzip' : 'tar' ) ) );
@unlink( $arcs[0]['name'] );
exit();
}
}
public function compressMySQLScript( 
$name, 
$type = BZ2, 
$level = 9, 
$vol_size = 0, 
$pattern = null, 
$mysqldump = false, 
$toolchain = 'intern', 
$bzip_version = 'bzip', 
$cygwin = CYGWIN_PATH, 
$cpusleep = 0, 
$callbacks = null ) {
global $COMPRESSION_ARCHIVE;
$fname = $name;
$arcs = null;
if ( _file_exists( $fname ) )
@unlink( $fname );
$this->_newarc_clbk = is_array( $callbacks ) && count( $callbacks ) > 0 ? $callbacks[0] : null;
$this->_compress_clbk = is_array( $callbacks ) && count( $callbacks ) > 1 ? $callbacks[1] : null;
$this->_output_clbk = is_array( $callbacks ) && count( $callbacks ) > 2 ? $callbacks[2] : null;
$this->_maint_end_clbk = is_array( $callbacks ) && count( $callbacks ) > 3 ? $callbacks[3] : null;
$this->_abort_clbk = is_array( $callbacks ) && count( $callbacks ) > 4 ? $callbacks[4] : null;
$this->_progress_clbk = is_array( $callbacks ) && count( $callbacks ) > 5 ? $callbacks[5] : null;
if ( ! empty( $pattern ) ) {
$fsize = $this->_mysql_obj->get_database_size();
$this->_progressCallback( MYSQL_SOURCE, $fname, 0, $fsize['dbsize'], 6, - 1 );
$mysql_format = $this->_mysql_obj->get_param( 'mysql_format' );
if ( 'sql' == $mysql_format && defined( __NAMESPACE__.'\\MYSQL_DUMP' ) && true == strToBool( $mysqldump ) )
$result = $this->_dumpMySqlDb( $fname, $pattern, $mysql_format );
else
$result = $this->_getTableScript( $fname, $pattern, $mysql_format );
$this->_progressCallback( MYSQL_SOURCE, $fname, $fsize['dbsize'], $fsize['dbsize'], 6, - 1 );
if ( ! $result )
throw new MyException( 
sprintf( 
_esc( 'Function %s (%s, %s, %s) returned an empty .sql script' ), 
true == strToBool( $mysqldump ) ? 'mysqldump' : '_getTableScript', 
$fname, 
$pattern, 
$mysql_format ) );
}
_is_callable( $this->_compress_clbk ) && _call_user_func( 
$this->_compress_clbk, 
$name, 
array( 
'METRIC_SOURCE' => MYSQL_SOURCE, 
'METRIC_SOURCEPATH' => str_replace( '"', '""', json_encode( $this->_getDBConnectionParams() ) ) ) );
$archive_size = _file_exists( $fname ) ? filesize( $fname ) : 0;
$arcname = null;
$skip_wp = ! empty( $toolchain ) && 'extern' == $toolchain;
if ( $skip_wp && false !== testOSTools( 
$this->_options['wrkdir'], 
$type, 
$level, 
$vol_size, 
null, 
null, 
null, 
$bzip_version, 
$cygwin ) ) {
$fsize = filesize( $name );
$this->_progressCallback( TMPFILE_SOURCE, $name, 0, $fsize, 3, - 1 );
$arcname = unixTarNZip( 
$name, 
$name, 
$type, 
$level, 
$vol_size, 
false, 
null, 
null, 
null, 
null, 
$bzip_version, 
$cygwin );
is_array( $arcname ) && $arcname = $arcname[0];
$this->_progressCallback( TMPFILE_SOURCE, $name, $fsize, $fsize, 3, - 1 );
}
if ( false === $skip_wp ) {
$archive_classname = __NAMESPACE__ . '\\' . $COMPRESSION_ARCHIVE[$type];
$archive = new $archive_classname( $name, MYSQL_SOURCE );
$archive->setCPUSleep( $cpusleep );
$archive->onAbortCallback = $this->_abort_clbk;
$archive->onProgressCallback = $this->_progress_clbk;
$archive->onStdOutput = $this->_output_clbk;
if ( false !== $archive->addFile( 
$fname, 
basename( $fname ), 
empty( $this->_nocompress ) ||
! preg_match( '/.*\.(' . implode( '|', $this->_nocompress ) . ')$/', $fname ) ) ) {
$arcname = $archive->compress( $type, $level );
}
$archive->close();
( NONE != $type ) && $archive->unlink(); 
}
if ( $arcname ) {
$fs = _file_exists( $arcname ) ? filesize( $arcname ) : 0;
$arcs = array( 
array( 
'name' => $arcname, 
'count' => 1, 
'bytes' => $archive_size, 
'arcsize' => filesize( $arcname ), 
'queued' => false ) );
if ( _is_callable( $this->_newarc_clbk ) ) {
_call_user_func( $this->_newarc_clbk, $arcname, $archive_size, $fs );
$arcs[0]['queued'] = true;
}
}
unlink( $fname );
return $arcs;
}
public function execTableMaintenance( $tables, $output_callback = null, $progress_callback = null, $abort_callback = null ) {
null == $output_callback && $output_callback = $this->_output_clbk || $this->_output_clbk = $output_callback;
null == $progress_callback &&
$progress_callback = $this->_progress_clbk || $this->_progress_clbk = $progress_callback;
null == $abort_callback && $abort_callback = $this->_abort_clbk || $this->_abort_clbk = $abort_callback;
if ( empty( $tables ) ) {
$this->_outputCallback( $tables, 'prepare', 'error', _esc( 'No table sent for MySQL maintenance' ) );
return;
}
$result = array();
$analyze = isNull( $this->_options, 'mysql_maint_analyze', false );
$check = isNull( $this->_options, 'mysql_maint_check', false );
$optimize = isNull( $this->_options, 'mysql_maint_optimize', false );
$repair = isNull( $this->_options, 'mysql_maint_repair', false );
$options = array( 'ANALYZE' => $analyze, 'CHECK' => $check, 'OPTIMIZE' => $optimize, 'REPAIR' => $repair );
$i = 1;
$c = count( $tables );
$d = count( $options );
foreach ( $tables as $table_name ) {
if ( _is_callable( $this->_abort_clbk ) && _call_user_func( $this->_abort_clbk ) )
break;
$this->_progressCallback( MYSQL_SOURCE, __FUNCTION__, $i++, $c, 6 );
if ( empty( $table_name ) )
continue;
else {
$j = 1;
$result[$table_name] = array();
foreach ( $options as $cmd => $cmd_enabled ) {
$this->_outputCallback( $table_name, $cmd, 'status', 'prepare' );
$this->_progressCallback( MYSQL_SOURCE, $table_name, $j++, $d, 6 );
if ( $cmd_enabled && $rst = $this->_mysql_obj->query( "$cmd TABLE $table_name" ) ) {
if ( $row = $this->_mysql_obj->fetch_row( $rst ) ) {
$result[$table_name][$cmd] = array( $row[2], $row[3] );
$this->_outputCallback( $table_name, $cmd, $row[2], $row[3] );
} else {
$mysql_error = $this->_mysql_obj->get_last_error();
$this->_outputCallback( $table_name, $cmd, 'error', $mysql_error['code'] );
}
$this->_mysql_obj->free_result( $rst );
}
}
}
}
return $result;
}
}
?>