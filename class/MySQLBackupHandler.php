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
 * @file    : MySQLBackupHandler.php $
 * 
 * @id      : MySQLBackupHandler.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;

require_once FUNCTIONS_PATH . 'utils.php';
is_wp () && require_once ABSPATH . 'wp-config.php';
class MySQLBackupHandler {
private $_options;
private $_link;
private $_output_clbk;
private $_maint_end_clbk;
private $_progress_clbk;
private $_abort_clbk;
private $_newarc_clbk;
private $_compress_clbk;
function __construct($options = null) {
global $settings;
$this->_options = empty ( $options ) ? $settings : $options;
$this->_output_clbk = null;
$this->_maint_end_clbk = null;
$this->_progress_clbk = null;
$this->_abort_clbk = null;
$this->_newarc_clbk = null;
$this->_compress_clbk = null;
$this->_link = $this->_getDbConnection ();
}
function __destruct() {
FALSE !== $this->_link &&\mysql_close ( $this->_link );
}
private function _outputCallback($table_name, $cmd, $msg_type, $msg_text) {
_is_callable ( $this->_output_clbk ) && _call_user_func ( $this->_output_clbk, $table_name, $cmd, $msg_type, $msg_text );
}
private function _progressCallback($provider, $filename, $bytes, $total_bytes, $ptype = 0, $running = 1, $reset_timer = false) {
_is_callable ( $this->_progress_clbk ) && _call_user_func ( $this->_progress_clbk, $provider, $filename, $bytes, $total_bytes, $ptype, $running, $reset_timer );
}
private function _dumpMySqlDb($name, $pattern = '.+', $format = 'sql') {
$mysql_maint = isNull ( $this->_options, 'mysql_maint', false );
if ($mysql_maint) {
$tables = $this->getTableNameFromPattern ( $pattern );
$this->_runTableMaintenance ( $tables, $pattern );
}
$this->_outputCallback ( $pattern, 'mysqldump', 'function', 'prepare' );
extract ( $this->_getDBConnectionParams () ); 
$unlink_log = true;
$log_file = "$name.log";
$cmd = sprintf ( "mysqldump -h %s -P %s -u %s -p%s -r %s --log-error %s %s %s %s", $mysql_host, $mysql_port, $mysql_user, $mysql_pwd, $name, $log_file, $mysql_db, implode ( ' ', $tables ), 'xml' == $format ? '--xml' : '' );
$result = - 1;
exec ( $cmd, $output, $result ); 
if (_is_callable ( $this->_output_clbk )) {
if ($result != 0) {
$this->_outputCallback ( $pattern, 'mysqldump', 'error', _esc ( '<b>mysqldump</b> terminated with errors' ) );
if (file_exists ( $log_file ) && filesize ( $log_file ) < 255) {
$err_msg = file_get_contents ( $log_file );
$this->_outputCallback ( $pattern, 'mysqldump', 'error', $err_msg );
} else {
$unlink_log = false;
$this->_outputCallback ( $pattern, 'mysqldump', 'error', _esc ( "The error message exeeds 255 chars and thus cannot be printed here." ) );
$this->_outputCallback ( $pattern, 'mysqldump', 'error', sprintf ( _esc ( "The sqldump file and the error log file are saved at %s respectively ***.log" ), $name ) );
}
} else {
$this->_outputCallback ( $pattern, 'mysqldump', 'info', _esc ( 'completed successfuly' ) );
}
}
if ($unlink_log && file_exists ( $log_file ))
unlink ( $log_file );
return $result == 0;
}
private function _formatTableToXML($statement, $db_name, $table) {
$result = '<!-- ' . $table . ' schema -->' . PHP_EOL;
$result .= '<pma:structure_schemas>' . PHP_EOL;
$result .= '<pma:database name="' . $db_name . '" collation="' . $this->_getDBConnectionParam ( 'mysql_collate' ) . '" charset="' . $this->_getDBConnectionParam ( 'mysql_charset' ) . '">' . PHP_EOL;
$result .= sprintf ( '<pma:table name="%s">', $table );
$result .= htmlspecialchars ( $statement );
$result .= sprintf ( '</pma:table>' . PHP_EOL . '</pma:database>' . PHP_EOL . '</pma:structure_schemas>' ) . PHP_EOL . PHP_EOL;
$result .= '<!-- table\'s data -->' . PHP_EOL;
return $result;
}
private function _formatRowToXML($row, $db_name, $table) {
$result = sprintf ( '<table name="%s">', $table ) . PHP_EOL;
foreach ( $row as $colname => $value ) {
$result .= sprintf ( '<column name="%s">%s</column>', $colname, htmlspecialchars ( $value ) ) . PHP_EOL;
}
$result .= '</table>' . PHP_EOL;
return $result;
}
private function _getTableScript($fname, $pattern = '.+', $format = 'sql') {
$ok = false;
$mysql_maint = isNull ( $this->_options, 'mysql_maint', false );
$db_name = $this->_getDBConnectionParam ( 'mysql_db' );
$tables = $this->getTableNameFromPattern ( $pattern );
if (empty ( $tables ))
$this->_outputCallback ( $pattern, null, 'error', sprintf ( _esc ( 'Could not find any table with the pattern %s within database %s' ), $pattern, $db_name ) );
if (is_array ( $tables )) {
if ($mysql_maint)
$this->_runTableMaintenance ( $tables, $pattern );
if (false !== ($fw = fopen ( $fname, 'w' ))) {
if ('xml' == $format) {
$result = '<?xml version="1.0"?>';
$result .= '<!--' . PHP_EOL;
$result .= '- ' . WPMYBACKUP . ' XML Dump' . PHP_EOL;
$result .= '- version ' . APP_VERSION_ID . PHP_EOL;
$result .= '- ' . APP_ADDONS_SHOP_URI . PHP_EOL;
$result .= '- ' . PHP_EOL;
$result .= '- host: ' . gethostname () . PHP_EOL;
$result .= '- created: ' . date ( 'r' ) . PHP_EOL;
$info = $this->getServerInfo ();
$result .= '- MySQL version: ' . $info ['version'] . PHP_EOL;
$result .= '- PHP version: ' . PHP_VERSION . PHP_EOL;
$result .= '-->' . PHP_EOL . PHP_EOL;
$result .= '<pma_xml_export version="1.0" xmlns:pma="' . $_SERVER ['HTTP_REFERER'] . $_SERVER ['REQUEST_URI'] . '">' . PHP_EOL;
fwrite ( $fw, $result );
}
foreach ( $tables as $table ) {
$rst =\mysql_query ( 'SELECT * FROM ' . $table );
if (FALSE !== $rst)
$num_fields =\mysql_num_fields ( $rst );
else
$num_fields = 0;
if ('sql' == $format) {
$result = 'DROP TABLE IF EXISTS ' . $table . ';';
fwrite ( $fw, $result );
}
$rst1 =\mysql_query ( 'SHOW CREATE TABLE ' . $table );
if (FALSE !== $rst1) {
$row2 =\mysql_fetch_row ( $rst1 );
$result = PHP_EOL . PHP_EOL . $row2 [1] . ";" . PHP_EOL . PHP_EOL;
'xml' == $format && $result = $this->_formatTableToXML ( $result, $db_name, $table );
fwrite ( $fw, $result );
for($i = 0; $i < $num_fields; $i ++)
while ( $row = 'xml' == $format ?\mysql_fetch_assoc ( $rst ) :\mysql_fetch_row ( $rst ) ) {
if ('xml' == $format) {
$result = $this->_formatRowToXML ( $row, $db_name, $table );
} else {
$result = 'INSERT INTO ' . $table . ' VALUES(';
for($j = 0; $j < $num_fields; $j ++) {
$row [$j] = addslashes ( $row [$j] );
$row [$j] = str_replace ( "'", "\'", $row [$j] );
$result .= '"' . (isset ( $row [$j] ) ? $row [$j] : '') . '"';
if ($j < ($num_fields - 1))
$result .= ',';
}
$result .= ");" . PHP_EOL;
}
fwrite ( $fw, $result );
unset ( $result );
unset ( $row );
}
}
fwrite ( $fw, PHP_EOL . PHP_EOL . PHP_EOL );
$ok = true;
}
if ('xml' == $format) {
$result = '</pma_xml_export>' . PHP_EOL;
fwrite ( $fw, $result );
}
fclose ( $fw );
}
}
return $ok;
}
private function _getDBConnectionParam($param_name) {
$default = null;
switch ($param_name) {
case 'mysql_host' :
$default = DB_HOST;
break;
case 'mysql_port' :
$default = 3306;
break;
case 'mysql_user' :
$default = DB_USER;
break;
case 'mysql_pwd' :
$default = DB_PASSWORD;
break;
case 'mysql_db' :
$default = DB_NAME;
break;
case 'mysql_format' :
$default = 'sql';
break;
case 'mysql_charset' :
$default = DB_CHARSET;
break;
case 'mysql_collate' :
$default = DB_COLLATE;
break;
}
return is_wp () ? $default : isNull ( $this->_options, $param_name, $default );
}
private function _getDBConnectionParams() {
$result = array ();
$params = array (
'mysql_format',
'mysql_charset',
'mysql_collate',
'mysql_host',
'mysql_port',
'mysql_user',
'mysql_pwd',
'mysql_db' 
);
foreach ( $params as $p )
$result [$p] = $this->_getDBConnectionParam ( $p );
return $result;
}
private function _getDbConnection() {
extract ( $this->_getDBConnectionParams () ); 
$link = @\mysql_connect ( $mysql_host . ':' . $mysql_port, $mysql_user, $mysql_pwd );
$result = $link;
FALSE !== $result && ! empty ( $mysql_db ) && $result =\mysql_select_db ( $mysql_db, $link );
if (FALSE === $result)
throw new MyException (\mysql_error (),\mysql_errno () );
return $link;
}
private function _runTableMaintenance($tables, $pattern) {
$this->_outputCallback ( $pattern, 'table maintenance', 'function', 'prepare' );
$result = $this->execTableMaintenance ( $tables, null, null, null, false );
is_array ( $result ) && _is_callable ( $this->_maint_end_clbk ) && _call_user_func ( $this->_maint_end_clbk, $result );
}
public function getTableNameFromPattern($pattern, $close_link = false) {
$db_name = $this->_getDBConnectionParam ( 'mysql_db' );
$where = explode ( ',', $pattern );
array_walk ( $where, function (&$item) use(&$db_name) {
$item = sprintf ( "(tables_in_%s REGEXP '%s')", $db_name, $item );
} );
$where = empty ( $where ) ? '' : (' WHERE ' . implode ( 'OR', $where ));
$tables = array ();
$rst =\mysql_query ( 'SHOW TABLES' . $where );
if (FALSE !== $rst)
while ( $row =\mysql_fetch_row ( $rst ) )
$tables [] = $row [0];
if ($close_link)
mysql_close ( $close_link );
return $tables;
}
public function getServerInfo() {
$info = array ();
if ($rst =\mysql_query ( 'SHOW VARIABLES LIKE "version%";' ))
while ( FALSE !== $rst && $row =\mysql_fetch_row ( $rst ) )
$info [$row [0]] = $row [1];
return $info;
}
public function getDbSize() {
$dbsize = array ();
if ($rst =\mysql_query ( "select (SELECT SUM(data_length + index_length) as dbsize FROM information_schema.TABLES where table_schema=database() group by table_schema) as dbsize, (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = database()) as tblcount;" ))
if (FALSE !== $rst && $row =\mysql_fetch_row ( $rst )) {
$dbsize ['dbsize'] = $row [0];
$dbsize ['tblcount'] = $row [1];
}
return $dbsize;
}
public function downloadSqlScript($path, $pattern, $name, $type, $level) {
if (! empty ( $pattern )) {
if (empty ( $name ))
$name = uniqid ( 'mysql-db-bak_', MORE_ENTROPY );
$name = addTrailingSlash ( $path ) . sprintf ( '%s-%s.' . $this->_getDBConnectionParam ( 'mysql_format' ), $name, date ( "Ymd-His" ) );
if (empty ( $type ))
$type = BZ2;
if (empty ( $level ))
$level = 9;
$dwl = $this->compressMySQLScript ( $name, $type, $level, 0, $pattern );
if (null == $dwl) {
_pesc ( "It seems I cannot connect MySQL for the time being. Please try later..." );
echo "<script>setTimeout(function(){history.back();},3000);</script>";
exit ();
}
redirectFileDownload ( $dwl [0], "application/x-" . (BZ2 == $type ? 'bzip2' : (GZ == $type ? 'gzip' : 'tar')) );
@unlink ( $dwl [0] );
exit ();
}
}
public function compressMySQLScript($name, $type = BZ2, $level = 9, $vol_size = 0, $pattern = null, $mysqldump = false, $toolchain = 'intern', $bzip_version = 'bzip', $cygwin = CYGWIN_PATH, $cpusleep = 0, $callbacks = null) {
global $COMPRESSION_ARCHIVE;
$fname = $name;
$arcs = null;
if (file_exists ( $fname ))
@unlink ( $fname );
$this->_newarc_clbk = is_array ( $callbacks ) && count ( $callbacks ) > 0 ? $callbacks [0] : null;
$this->_compress_clbk = is_array ( $callbacks ) && count ( $callbacks ) > 1 ? $callbacks [1] : null;
$this->_output_clbk = is_array ( $callbacks ) && count ( $callbacks ) > 2 ? $callbacks [2] : null;
$this->_maint_end_clbk = is_array ( $callbacks ) && count ( $callbacks ) > 3 ? $callbacks [3] : null;
$this->_abort_clbk = is_array ( $callbacks ) && count ( $callbacks ) > 4 ? $callbacks [4] : null;
$this->_progress_clbk = is_array ( $callbacks ) && count ( $callbacks ) > 5 ? $callbacks [5] : null;
if (! empty ( $pattern )) {
$fsize = $this->getDbSize ();
$this->_progressCallback ( MYSQL_SOURCE, $fname, 0, $fsize ['dbsize'], 6, - 1 );
$mysql_format = $this->_getDBConnectionParam ( 'mysql_format' );
if (defined ( 'MYSQL_DUMP' ) && true == strToBool ( $mysqldump ))
$result = $this->_dumpMySqlDb ( $fname, $pattern, $mysql_format );
else
$result = $this->_getTableScript ( $fname, $pattern, $mysql_format );
$this->_progressCallback ( MYSQL_SOURCE, $fname, $fsize ['dbsize'], $fsize ['dbsize'], 6, - 1 );
if (! $result)
throw new MyException ( sprintf ( _esc ( 'Function %s (%s, %s, %s) returned an empty .sql script' ), true == strToBool ( $mysqldump ) ? 'mysqldump' : '_getTableScript', $fname, $pattern, $mysql_format ) );
}
_is_callable ( $this->_compress_clbk ) && _call_user_func ( $this->_compress_clbk, $name, array (
'METRIC_SOURCE' => MYSQL_SOURCE,
'METRIC_SOURCEPATH' => str_replace ( '"', '""', json_encode ( $this->_getDBConnectionParams () ) ) 
) );
$archive_size = file_exists ( $fname ) ? filesize ( $fname ) : 0;
$skip_wp = ! empty ( $toolchain ) && 'extern' == $toolchain;
if ($skip_wp && false !== testOSTools ( $this->_options ['wrkdir'], $type, $level, $vol_size, null, null, null, $bzip_version, $cygwin )) {
$fsize = filesize ( $name );
$this->_progressCallback ( TMPFILE_SOURCE, $name, 0, $fsize, 3, - 1 );
$arcs = unixTarNZip ( $name, $name, $type, $level, $vol_size, false, null, null, null, $bzip_version, $cygwin );
$this->_progressCallback ( TMPFILE_SOURCE, $name, $fsize, $fsize, 3, - 1 );
}
if (false === $skip_wp) {
$archive_classname = __NAMESPACE__ . '\\' . $COMPRESSION_ARCHIVE [$type];
$archive = new $archive_classname ( $name, MYSQL_SOURCE );
$archive->setCPUSleep ( $cpusleep );
$archive->onAbortCallback = $this->_abort_clbk;
$archive->onProgressCallback = $this->_progress_clbk;
$archive->onStdOutput = $this->_output_clbk;
if (false !== $archive->addFile ( $fname, basename ( $fname ) )) {
$arcs = array (
$archive->compress ( $type, $level ) 
);
}
$archive->unlink (); 
}
if (is_array ( $arcs )) {
asort ( $arcs );
foreach ( $arcs as $d ) {
$fs = file_exists ( $d ) ? filesize ( $d ) : 0;
_is_callable ( $this->_newarc_clbk ) && _call_user_func ( $this->_newarc_clbk, $d, $archive_size, $fs );
}
}
unlink ( $fname );
return $arcs;
}
public function execTableMaintenance($tables, $output_callback = null, $progress_callback = null, $abort_callback = null, $close_link = true) {
null == $output_callback && $output_callback = $this->_output_clbk || $this->_output_clbk = $output_callback;
null == $progress_callback && $progress_callback = $this->_progress_clbk || $this->_progress_clbk = $progress_callback;
null == $abort_callback && $abort_callback = $this->_abort_clbk || $this->_abort_clbk = $abort_callback;
if (empty ( $tables )) {
$this->_outputCallback ( $tables, 'prepare', 'error', _esc ( 'No table sent for MySQL maintenance' ) );
return;
}
$result = array ();
$analyze = isNull ( $this->_options, 'mysql_maint_analyze', false );
$check = isNull ( $this->_options, 'mysql_maint_check', false );
$optimize = isNull ( $this->_options, 'mysql_maint_optimize', false );
$repair = isNull ( $this->_options, 'mysql_maint_repair', false );
$options = array (
'ANALYZE' => $analyze,
'CHECK' => $check,
'OPTIMIZE' => $optimize,
'REPAIR' => $repair 
);
$i = 1;
$c = count ( $tables );
$d = count ( $options );
foreach ( $tables as $table_name ) {
if (_is_callable ( $this->_abort_clbk ) && _call_user_func ( $this->_abort_clbk ))
break;
$this->_progressCallback ( MYSQL_SOURCE, __FUNCTION__, $i ++, $c, 6 );
if (empty ( $table_name ))
continue;
else {
$j = 1;
$result [$table_name] = array ();
foreach ( $options as $cmd => $cmd_enabled ) {
$this->_outputCallback ( $table_name, $cmd, 'status', 'prepare' );
$this->_progressCallback ( MYSQL_SOURCE, $table_name, $j ++, $d, 6 );
if ($cmd_enabled && $rst =\mysql_query ( "$cmd TABLE $table_name;" ))
if (FALSE !== $rst && $row =\mysql_fetch_row ( $rst )) {
$result [$table_name] [$cmd] = array (
$row [2],
$row [3] 
);
$this->_outputCallback ( $table_name, $cmd, $row [2], $row [3] );
} else
$this->_outputCallback ( $table_name, $cmd, 'error',\mysql_errno ( $this->_link ) );
}
}
}
if ($close_link) {
mysql_close ( $this->_link );
$this->_link = false;
}
return $result;
}
}
?>
