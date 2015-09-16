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
 * @file    : MySQLSourceEditor.php $
 * 
 * @id      : MySQLSourceEditor.php | Wed Sep 16 11:33:37 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;
class MySQLSourceEditor extends AbstractTargetEditor {
private $_db_list;
private $_mysql_host;
private $_mysql_port;
private $_mysql_user;
private $_mysql_pwd;
private $_mysql_db;
private function _getExpertJavaScript() {
global $PROGRESS_PROVIDER;
$this->java_scripts [] = 'parent.toggle_mysql_maint=function(sender, opt_list) { var opts = opt_list.split(","), i,el; for (i = 0; i < opts.length; i += 1) { el=document.getElementById(opts[i]); if(el) el.disabled = !sender.checked;}}';
$this->java_scripts [] = "parent.run_mysql_maint=function(){parent.asyncRunBackup('run_mysql_maint','" . _esc ( 'MySQL Maintenance' ) . "','" . wp_create_nonce_wrapper ( 'run_mysql_maint' ) . "','" . wp_create_nonce_wrapper ( 'get_progress' ) . "','" . wp_create_nonce_wrapper ( 'cleanup_progress' ) . "','" . wp_create_nonce_wrapper ( 'abort_job' ) . "');}";
$this->java_scripts [] = getBackupSourcesJS ( $PROGRESS_PROVIDER );
}
protected function initTarget() {
parent::initTarget ();
$this->_db_list = array ();
$this->hasInfoBanner = defined ( 'FILE_EXPLORER' );
$this->hasPasswordField = ! $this->is_wp;
$this->_mysql_host = $this->settings ['mysql_host'];
$this->_mysql_port = $this->settings ['mysql_port'];
$this->_mysql_user = $this->settings ['mysql_user'];
$this->_mysql_pwd = $this->settings ['mysql_pwd'];
$this->_mysql_db = $this->settings ['mysql_db'];
$this->_getExpertJavaScript ();
}
protected function getEditorTemplate() {
global $wpdb;
$db_prefix = is_wp () ? $wpdb->base_prefix : '';
$help_1 = "'" . _esc ( "Here you may specify the tables to be included in the database SQL script." );
$help_1 .= '<div class=&quot;inside rounded-container&quot;>' . _esc ( "The database SQL script comprises of one CREATE TABLE statement for each table mentioned herein(including its indexes/constrains/keys).<br>Additionally each table is followed by one INSERT statement corresponding to all records within that table. So the backup will be `a huge script` that will allow you to regenerate the whole database whenever it`s needed." ) . '</div>';
$help_1 .= _esc ( 'You may specify a single table name or a comma-delimited list of tables. The table name may be specified as absolute value or you may use any MySQL regexp pattern matching expression.' ) . '<br>';
$help_1 .= getExample ( 'RegExp pattern example', _esc ( '<strong>^(ab|xy)+.*[0-9]$</strong> captures all those tables which name starts either with ab or xy and ends in a digit.' ) . ' ' . readMoreHereE ( 'https: // dev.mysql.com/doc/refman/5.0/en/pattern-matching.html' ), false );
! empty ( $db_prefix ) && $help_1 .= sprintf ( _esc ( '<strong>Note</strong>: DO NOT add the WordPress database prefix <strong>%s</strong> as it will prepended automatically!' ), $db_prefix );
$help_1 .= "'";
$help_2 = "'" . _esc ( "Use Oracles mysqldump tool instead of our internal dump algorithm.<br>This should be the prefered method if your web server settings allows you to call the PHP exec() function." ) . "'";
$help_3 = "'" . sprintf ( _esc ( "Select SQL to create the script in SQL ANSI format otherwise as phpMyAdmin XML schema.<br>Note that only SQL format is recognized by the %s." ), getAnchorE ( _esc ( 'Restore addon' ), APP_ADDONS_SHOP_URI . 'shop/restore-wizard' ) ) . "'";
$help_4 = "'" . sprintf ( _esc ( 'Please note that under WordPress it is possible that a PHP script belonging to an installed plugin will print-out a CRLF (0A or 0A0D) either at the beginning or the end of the script. Although this is not a problem for the generated HTML page it would become a problem for the .sql file you download.' ) );
$help_4 .= ' ' . sprintf ( _esc ( 'With other words, if you download a file that is compressed/packed in an archive and you cannot extract its content then for sure you encounter the problem mentioned earlier. To fix this you could try to spot the plugin that causes the problem by disabling each of them one at a time then retrying downloading the .sql file (I know, it sucks!).' ) );
$example = _esc ( '# On Unix like systems it can be fixed with the aid of tools like <b>hexdump</b> and <b>dd</b>:' ) . '<pre>f=[file-to-fix]<br>hexdump -C -n64 $f # ' . _esc ( '$f should not start with whitespaces' ) . '<br>n=[' . _esc ( 'number of whitespaces to skip] # check with aid of hexdump' ) . '<br>dd bs=1 skip=$n if=$f of=fixed-$f # ' . _esc ( 'trim the whitespaces' ) . '</pre>';
$help_4 .= getExample ( _esc ( 'Fix it yourself' ), preg_replace ( '/(#.*?)(<\/?(pre|br)>)/m', '<span style="color:#008B8B">$1</span>$2', $example ), false ) . "'";
$mysql_format = $this->settings ['mysql_format'];
$mysqldump = strToBool ( $this->settings ['mysqldump'] );
if (! $this->hideEditorContent ()) {
$this->_db_list [] = '';
try {
$link = @\mysql_connect ( $this->_mysql_host . ':' . $this->_mysql_port, $this->_mysql_user, $this->_mysql_pwd );
if (false !== $link) {
$res = @\mysql_query ( 'SHOW DATABASES;', $link );
if (false !== $res)
while ( $row = \mysql_fetch_assoc ( $res ) )
if ('information_schema' != $row ['Database'] && 'performance_schema' != $row ['Database'] && 'mysql' != $row ['Database'])
$this->_db_list [] = $row ['Database'];
mysql_close ( $link );
}
} 			
catch ( MyException $err ) {
echo $err->getMessage ();
}
}
require_once $this->getTemplatePath ( 'mysql.php' );
}
protected function getExpertEditorTemplate() {
$_settings_ = &$this->settings;
$get_mysql_param = function ($param_name) use(&$_settings_) {
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
case 'mysql_db' :
$default = DB_NAME;
break;
}
return is_wp () ? $default : isNull ( $_settings_, $param_name, $default );
};
$hint = _esc ( "<br>Most users should use this option." );
$help_1 = "'" . _esc ( "Run the table maintenance for all included tables right before the backup.<br>This operation is done by MySQL itself so it`s safe :-)" ) . '<br>' . readMoreHereE ( 'http://dev.mysql.com/doc/refman/4.1/en/table-maintenance-sql.html' ) . "'";
$help_2 = "'" . sprintf ( _esc ( "This option analyzes and stores the key distribution for a table. From MySQL Reference Manual: %s MySQL uses the stored key distribution to decide the order in which tables should be joined when you perform a join on something other than a constant. In addition, key distributions can be used when deciding which indexes to use for a specific table within a query.%s" ) . '<br>' . readMoreHereE ( 'http://dev.mysql.com/doc/refman/4.1/en/analyze-table.html' ), '<blockquote><i>', '</i></blockquote>' . $hint ) . "'";
$help_3 = "'" . _esc ( "This option checks a table for errors. For MyISAM tables, the key statistics are updated as well." ) . $hint . '<br>' . readMoreHereE ( 'http://dev.mysql.com/doc/refman/4.1/en/check-table.html' ) . "'";
$help_4 = "'" . _esc ( "This option tries to reclaim the unused space and also to defragment the data file." ) . $hint . '<br>' . readMoreHereE ( 'http://dev.mysql.com/doc/refman/4.1/en/optimize-table.html' ) . "'";
$help_5 = "'" . _esc ( "This option repairs a possibly corrupted table. Normally, you should never have to use this option." ) . '<br>' . readMoreHereE ( 'http://dev.mysql.com/doc/refman/4.1/en/repair-table.html' ) . "'";
$help_6 = "'" . _esc ( "Send an e-mail notification when the maintenance task reports warnings and/or errors." ) . $hint . "'";
$name = max ( $get_mysql_param ( 'name' ), $get_mysql_param ( 'url' ) ) . '-yyyymmdd-hhmmss.sql';
$array = array (
'h' => $get_mysql_param ( 'mysql_host' ),
'P' => $get_mysql_param ( 'mysql_port' ),
'u' => $get_mysql_param ( 'mysql_user' ),
'p' => '******',
'r' => $name,
'-log-error' => $name . '.log',
' ' => $get_mysql_param ( 'mysql_db' ),
'' => sprintf ( _esc ( 'list of tables for pattern `%s`' ), $get_mysql_param ( 'tables' ) ) 
);
$mysqldump_opts = array ();
foreach ( $array as $key => $value )
$mysqldump_opts [] = sprintf ( '<b>%s</b> <i>%s</i>', '' != trim ( $key ) ? "-$key" : '', $value );
$help_7 = "'" . sprintf ( _esc ( "Set the %s that you want to use when creating the MySQL backup via %s.<br>This gives you much more flexibility comparing with the standard option provided by default (which is OK if you want only a simple backup script)." ), getAnchorE ( 'extra options', 'https://dev.mysql.com/doc/refman/5.1/en/mysqldump.html#option_mysqldump_opt' ), getAnchorE ( 'mysqldump', 'https://dev.mysql.com/doc/refman/5.1/en/mysqldump.html#option_mysqldump_opt' ) );
$help_7 .= getExample ( _esc ( 'Usage example' ), sprintf ( _esc ( 'By default <b>mysqldump</b> is run with the following options (so don`t override them):%s' ), '<ul><li>' . implode ( '</li><li>', $mysqldump_opts ) . '</li></ul>' ), false ) . _esc ( 'For a complete list of the available options please see the mysqldump documentation.' ) . "'";
$enabled = strToBool ( $this->settings ['mysql_maint'] );
$disabled = $enabled && $this->enabled ? '' : ' disabled ';
$mysql_maint_opts = array (
'mysql_maint_analyze' => array (
_esc ( 'Analyze' ),
$help_2 
),
'mysql_maint_check' => array (
_esc ( 'Check' ),
$help_3 
),
'mysql_maint_optimize' => array (
_esc ( 'Optimize' ),
$help_4 
),
'mysql_maint_repair' => array (
_esc ( 'Repair' ),
$help_5 
),
'mysql_maint_notify' => array (
_esc ( 'Email on warnings/errors' ),
$help_6 
) 
);
$rows = '';
foreach ( $mysql_maint_opts as $opt_name => $opt_info ) {
$opt_desc = $opt_info [0];
$opt_help = $opt_info [1];
$opt_state = strToBool ( $this->settings [$opt_name] ) ? ' checked' : '';
$rows .= '<tr>';
$rows .= '<td>&nbsp;</td>';
$rows .= '<td><input type="checkbox" id="' . $opt_name . '" name="' . $opt_name . '" ' . $disabled . $opt_state . '><input type="hidden" name="' . $opt_name . '" value="0"></td>';
$rows .= '<td><label for="' . $opt_name . '">' . $opt_desc . '</label><a class="help" onclick=' . getHelpCall ( $opt_help ) . '> [?]</a></td>';
$rows .= '</tr>';
}
require_once $this->getTemplatePath ( 'mysql-expert.php' );
}
protected function hideEditorContent() {
$skip = $this->is_wp || empty ( $this->_mysql_host ) || empty ( $this->_mysql_user ) ;
$skip && $this->_db_list [] = DB_NAME;
return $skip;
}
}
?>
