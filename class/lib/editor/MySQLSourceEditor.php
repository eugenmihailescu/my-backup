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
 * @file    : MySQLSourceEditor.php $
 * 
 * @id      : MySQLSourceEditor.php | Wed Dec 7 18:54:23 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
class MySQLSourceEditor extends AbstractTargetEditor {
private $_mysql_obj;
private $_db_list;
private $_mysql_host;
private $_mysql_port;
private $_mysql_user;
private $_mysql_pwd;
private $_mysql_db;
private $_mysql_charset;
private $_db_prefix;
private $_db_prefixes;
private function _initMySQL() {
$this->_mysql_obj = new MySQLWrapper( $this->settings );
$link = $this->_mysql_obj->connect();
if ( ! $this->is_wp && $link ) {
$this->_db_list = $this->_mysql_obj->get_database_names();
array_unshift( $this->_db_list, '' );
return true;
}
return false;
}
private function _getTablesHtml() {
$settings = $this->settings;
if ( ! ( $tables = getMySQLTableNamesFromPattern( 
implode( '.+|', $this->_db_prefixes ) . '.+', 
$this->_mysql_obj, 
$settings, 
true ) ) )
return '';
$create_table_html = function ( $array, $class = '', $col_num = 2, $sufix = '' ) {
if ( ! $col_num )
return '';
$rows = array();
$cols = array();
foreach ( $array as $label => $info ) {
if ( count( $cols ) >= $col_num ) {
$rows[] = '<td>' . implode( '</td><td>', $cols ) . '</td>';
$cols = array();
}
$id = $label;
$cols[] = sprintf( 
'<input type="checkbox" id="%s" class="mysql-%s-selected"><label for="%s">%s%s</label><span>%drecs@%s</span>', 
$id, 
$class, 
$id, 
$label, 
$sufix, 
$info[0], 
getHumanReadableSize( $info[1] ) );
}
( $count = count( $cols ) ) && ( $col_num - $count ) &&
$cols = $cols + array_fill( $count, $col_num - $count, '' );
$count && $rows[] = '<td>' . implode( '</td><td>', $cols ) . '</td>';
return sprintf( 
'<table class="mysql-%s-selector"><tr>%s</tr></table>', 
$class, 
implode( '</tr><tr>', $rows ) );
};
$get_regex_url = function ( $tables ) use(&$settings ) {
$match_tables = array();
$unmatch_tables = array();
$sent_tables = array();
$prefix = count( wp_get_user_blogs_prefixes() ) < 2 ? wp_get_db_prefix() : '';
foreach ( $tables as $tblname )
if ( preg_match( '/^' . $prefix . $settings['tables'] . '$/', $tblname ) )
$match_tables[] = $tblname;
else
$unmatch_tables[] = $tblname;
$match_tables = array_merge( $match_tables, $unmatch_tables );
$regex_url = sprintf( 
'https://regex101.com/?options=gm&regex=%s', 
urlencode( '^' . $prefix . $settings['tables'] . '$' ) );
foreach ( $match_tables as $tblname ) {
if ( strlen( $regex_url . urlencode( PHP_EOL . $tblname ) ) > 2083 )
break;
$sent_tables[] = $tblname;
}
return sprintf( '%s&text=%s#javascript', $regex_url, urlencode( implode( PHP_EOL, $sent_tables ) ) );
};
$db_prefixes = array();
foreach ( $this->_db_prefixes as $blog_id => $db_prefix ) {
$prefix_recs = 0;
$prefix_size = 0;
foreach ( $tables as $tblname => $tblinfo ) {
if ( preg_match( '/^' . $db_prefix . '.+$/', $tblname ) ) {
$prefix_recs += $tblinfo[0];
$prefix_size += $tblinfo[1];
}
}
$db_prefixes[$db_prefix] = array( $prefix_recs, $prefix_size );
}
$prefixes_html = $create_table_html( $db_prefixes, 'prefix', 4, '.+' );
$tables_html = $create_table_html( $tables, 'table' );
return $prefixes_html . $tables_html . sprintf( 
'<div><a href="%s" target="_blank">%s</a></div>', 
$get_regex_url( array_keys( $tables ) ), 
_esc( 'Do you need to check your regex pattern?' ) );
}
private function _getExpertJavaScript() {
global $PROGRESS_PROVIDER;
$this->java_scripts[] = 'parent.toggle_mysql_maint=function(sender, opt_list) { var opts = opt_list.split(","), i,el; for (i = 0; i < opts.length; i += 1) { el=document.getElementById(opts[i]); if(el) el.disabled = !sender.checked;}}';
$this->java_scripts[] = "parent.run_mysql_maint=function(){parent.asyncRunBackup('run_mysql_maint','" .
_esc( 'MySQL Maintenance' ) . "','" . wp_create_nonce_wrapper( 'run_mysql_maint' ) . "','" .
wp_create_nonce_wrapper( 'get_progress' ) . "','" . wp_create_nonce_wrapper( 'cleanup_progress' ) . "','" .
wp_create_nonce_wrapper( 'abort_job' ) . "');}";
$tables = $this->_getTablesHtml();
$items_i_id = 'items[i].id';
$db_prefix = 1 == count( $this->_db_prefixes ) ? current( $this->_db_prefixes ) : $this->_db_prefix;
if ( count( $this->_db_prefixes ) < 2 && ! empty( $db_prefix ) ) {
$preg_prefixes = array_map( function ( $item ) {
return preg_quote( $item, '/' );
}, $this->_db_prefixes );
$items_i_id .= '.replace(/^' . implode( '|', $preg_prefixes ) . "(.+)/,'\$1')";
}
ob_start();
?>
parent.mysql_table_select=function(pattern,clear){
clear=parent.globals.UNDEFINED==typeof clear?false:clear;
if(!clear)
pattern=true===pattern||'.+'==pattern?'.+':('^'+pattern+'$'); 
var items=document.querySelectorAll('.mysql-table-selected'),i,r=clear?null:new RegExp(pattern);
for(i=0;items.length>i;i+=1)
items[i].checked=!clear && r.test(<?php echo $items_i_id;?>);
};
parent.mysql_table_selector_get_tables=function(classname){
var items=document.querySelectorAll('.mysql-'+classname+'-selected'),i,array=[],unchecked=false;
if(parent.globals.UNDEFINED!==typeof items){
for(i=0;items.length>i;i+=1)
if(items[i].checked)
array.push(<?php echo $items_i_id;?>);
else
unchecked=true;
}
return {items:array,unchecked:unchecked};
};
parent.mysql_table_save=function(){
var e=document.getElementById('tables'),i,obj;
if(parent.globals.UNDEFINED!==typeof e){
obj=parent.mysql_table_selector_get_tables('table');	
e.value=obj.unchecked?(obj.items.length?MynixRegexUtils.array2regex(obj.items):''):'.+';
}
parent.removePopupLast();	
};
parent.mysql_table_selector=function(){
var e=document.getElementById('tables'),items,i,p='',r,callback;
if(parent.globals.UNDEFINED==typeof e)return; 
parent.popupConfirm('Table Selector','<?php echo str_replace(PHP_EOL,'',$tables);?>',null,{ 'Select all' : 'jsMyBackup.mysql_table_select(true);','Deselect all' : 'jsMyBackup.mysql_table_select(null,true);','Save' : 'jsMyBackup.mysql_table_save();','Cancel' :null },'auto');
if(i=e.value.indexOf('[')){
p=e.value.substr(0,i);
r=new RegExp('^'+p+'.+$');
}
items=document.querySelectorAll('.mysql-prefix-selected');
for(i=0;items.length>i;i+=1)
items[i].checked='.+'==e.value||(''!=p && r.test(items[i].id));
parent.mysql_table_select(e.value);
if(parent.globals.UNDEFINED!=typeof items){
callback=function(sender){
sender=sender||window.event;
sender=sender.target||sender.srcElement;
if(parent.globals.UNDEFINED!=sender){
var obj=parent.mysql_table_selector_get_tables('prefix');
parent.mysql_table_select(MynixRegexUtils.array2regex(obj.items)+'.+',!obj.items.length);
}
};	
for(i=0;items.length>i;i+=1)
items[i].onchange=callback;
}
};		
parent.mysql_table_selected='<?php echo $this->settings ['tables'];?>';
<?php
$this->java_scripts[] = ob_get_clean();
$this->java_scripts[] = getBackupSourcesJS( $PROGRESS_PROVIDER );
}
protected function initTarget() {
parent::initTarget();
$this->_db_list = array( '' );
$this->hasInfoBanner = defined( __NAMESPACE__.'\\FILE_EXPLORER' );
$this->hasPasswordField = ! $this->is_wp;
$this->_db_prefix = wp_get_db_prefix();
$this->_db_prefixes = array();
foreach ( wp_get_user_blogs_prefixes() as $blog_id => $blog_db_prefix )
preg_match( '/^' . $this->_db_prefix . '/', $blog_db_prefix ) &&
$this->_db_prefixes[$blog_id] = $blog_db_prefix;
$this->_initMySQL();
$this->_mysql_host = $this->_mysql_obj->get_param( 'mysql_host' );
$this->_mysql_port = $this->_mysql_obj->get_param( 'mysql_port' );
$this->_mysql_user = $this->_mysql_obj->get_param( 'mysql_user' );
$this->_mysql_pwd = $this->_mysql_obj->get_param( 'mysql_pwd' );
$this->_mysql_db = $this->_mysql_obj->get_param( 'mysql_db' );
$this->_mysql_charset = $this->_mysql_obj->get_param( 'mysql_charset' );
$this->_getExpertJavaScript();
}
protected function getEditorTemplate() {
$help_1 = "'" . _esc( "Here you may specify the tables to be included in the database SQL script." );
$help_1 .= '<div class=&quot;inside rounded-container&quot;>' .
_esc( 
"The database SQL script comprises of one CREATE TABLE statement for each table mentioned herein(including its indexes/constrains/keys).<br>Additionally each table is followed by one INSERT statement corresponding to all records within that table. So the backup will be `a huge script` that will allow you to regenerate the whole database whenever it`s needed." ) .
'</div>';
$help_1 .= _esc( 
'You may specify a single table name or a comma-delimited list of tables. The table name may be specified as absolute value or you may use any MySQL regexp pattern matching expression.' ) .
'<br>';
$help_1 .= getExample( 
'RegExp pattern example', 
_esc( 
'<strong>^(ab|xy)+.*[0-9]$</strong> captures all those tables which name starts either with ab or xy and ends in a digit.' ) .
' ' . readMoreHereE( 'https: // dev.mysql.com/doc/refman/5.0/en/pattern-matching.html' ), 
false );
! empty( $this->_db_prefix ) && $help_1 .= sprintf( 
_esc( 
'<strong>Note</strong>: DO NOT add the WordPress database prefix <strong>%s</strong> as it will prepended automatically!' ), 
$this->_db_prefix );
$help_1 .= "'";
$help_2 = "'" .
_esc( 
"Use Oracles mysqldump tool instead of our internal dump algorithm.<br>This should be the prefered method if your web server settings allows you to call the PHP exec() function." ) .
"'";
$help_3 = "'" . sprintf( 
_esc( 
"Select SQL to create the script in SQL ANSI format otherwise as phpMyAdmin XML schema.<br>Note that only SQL format is recognized by the %s." ), 
getAnchorE( _esc( 'Restore addon' ), APP_ADDONS_SHOP_URI . 'shop/restore-wizard' ) ) . "'";
$help_4 = "'" .
sprintf( 
_esc( 
'Please note that under WordPress it is possible that a PHP script belonging to an installed plugin will print-out a CRLF (0A or 0A0D) either at the beginning or the end of the script. Although this is not a problem for the generated HTML page it would become a problem for the .sql file you download.' ) );
$help_4 .= ' ' . sprintf( 
_esc( 
'With other words, if you download a file that is compressed/packed in an archive and you cannot extract its content then for sure you encounter the problem mentioned earlier. To fix this you could try to spot the plugin that causes the problem by disabling each of them one at a time then retrying downloading the .sql file (I know, it sucks!).' ) .
' ' . readMoreHereE( APP_PLUGIN_FAQ_URI . '#q7' ) . '.' );
$example = _esc( 
'# On Unix like systems it can be fixed with the aid of tools like <b>hexdump</b> and <b>dd</b>:' ) .
'<pre>f=[file-to-fix]<br>hexdump -C -n64 $f # ' . _esc( '$f should not start with whitespaces' ) . '<br>n=[' .
_esc( 'number of whitespaces to skip] # check with aid of hexdump' ) .
'<br>dd bs=1 skip=$n if=$f of=fixed-$f # ' . _esc( 'trim the whitespaces' ) . '</pre>';
$help_4 .= getExample( 
_esc( 'Fix it yourself' ), 
preg_replace( '/(#.*?)(<\/?(pre|br)>)/m', '<span style="color:#008B8B">$1</span>$2', $example ), 
false ) . "'";
$help_5 = "'" . sprintf( 
_esc( 'The MySQL server host name or IP address.' ) . '<br><br>' .
_esc( 'If your host uses Unix sockets or pipes then it should be provided like:%s%s' ), 
'<p style=&quot;font-weight:bold&quot;>[host-or-ip]:[/path-to-host-socket-or-pipe]</p>', 
getExample( 
_esc( 'Example' ), 
sprintf( 
'localhost %s<br>128.0.90.174 %s<br>' . 'example.tld:/var/run/mysqld/mysqld.sock %s', 
getSpanE( '# connect to local MySQL server', '#008B8B' ), 
getSpanE( '# connect to a remote MySQL server', '#008B8B' ), 
getSpanE( '# connect to example.tld via Unix socket', '#008B8B' ) ), 
false ) ) . "'";
$mysql_format = $this->settings['mysql_format'];
$mysqldump = strToBool( $this->settings['mysqldump'] );
$prefix_label = empty( $this->_db_prefix ) || count( $this->_db_prefixes ) > 1 ? '[*]' : $this->_db_prefix;
if ( $this->enabled )
$prefix_html = sprintf( '<a class="help" onclick="jsMyBackup.mysql_table_selector();">%s</a>', $prefix_label );
else
$prefix_html = getSpan( $prefix_label, '#00adee' );
require_once $this->getTemplatePath( 'mysql.php' );
}
protected function getExpertEditorTemplate() {
$hint = _esc( "<br>Most users should use this option." );
$help_1 = "'" .
_esc( 
"Run the table maintenance for all included tables right before the backup.<br>This operation is done by MySQL itself so it`s safe :-)" ) .
'<br>' . readMoreHereE( 'http://dev.mysql.com/doc/refman/4.1/en/table-maintenance-sql.html' ) . "'";
$help_2 = "'" . sprintf( 
_esc( 
"This option analyzes and stores the key distribution for a table. From MySQL Reference Manual: %s MySQL uses the stored key distribution to decide the order in which tables should be joined when you perform a join on something other than a constant. In addition, key distributions can be used when deciding which indexes to use for a specific table within a query.%s" ) .
'<br>' . readMoreHereE( 'http://dev.mysql.com/doc/refman/4.1/en/analyze-table.html' ), 
'<blockquote><i>', 
'</i></blockquote>' . $hint ) . "'";
$help_3 = "'" .
_esc( "This option checks a table for errors. For MyISAM tables, the key statistics are updated as well." ) .
$hint . '<br>' . readMoreHereE( 'http://dev.mysql.com/doc/refman/4.1/en/check-table.html' ) . "'";
$help_4 = "'" . _esc( "This option tries to reclaim the unused space and also to defragment the data file." ) .
$hint . '<br>' . readMoreHereE( 'http://dev.mysql.com/doc/refman/4.1/en/optimize-table.html' ) . "'";
$help_5 = "'" . _esc( 
"This option repairs a possibly corrupted table. Normally, you should never have to use this option." ) .
'<br>' . readMoreHereE( 'http://dev.mysql.com/doc/refman/4.1/en/repair-table.html' ) . "'";
$help_6 = "'" . _esc( "Send an e-mail notification when the maintenance task reports warnings and/or errors." ) .
$hint . "'";
$name = max( $this->settings['name'], $this->settings['url'] ) . '-yyyymmdd-hhmmss.sql';
$array = array( 
'h' => $this->_mysql_host, 
'P' => $this->_mysql_port, 
'u' => $this->_mysql_user, 
'p' => '******', 
'r' => $name, 
'-log-error' => $name . '.log', 
' ' => $this->_mysql_db, 
'' => sprintf( _esc( 'list of tables for pattern `%s`' ), $this->settings['tables'] ) );
$mysqldump_opts = array();
foreach ( $array as $key => $value )
$mysqldump_opts[] = sprintf( '<b>%s</b> <i>%s</i>', '' != trim( $key ) ? "-$key" : '', $value );
$help_7 = "'" . sprintf( 
_esc( 
"Set the %s that you want to use when creating the MySQL backup via %s.<br>This gives you much more flexibility comparing with the standard option provided by default (which is OK if you want only a simple backup script)." ), 
getAnchorE( 'extra options', 'https://dev.mysql.com/doc/refman/5.1/en/mysqldump.html#option_mysqldump_opt' ), 
getAnchorE( 'mysqldump', 'https://dev.mysql.com/doc/refman/5.1/en/mysqldump.html#option_mysqldump_opt' ) );
$help_7 .= getExample( 
_esc( 'Usage example' ), 
sprintf( 
_esc( 'By default <b>mysqldump</b> is run with the following options (so don`t override them):%s' ), 
'<ul><li>' . implode( '</li><li>', $mysqldump_opts ) . '</li></ul>' ), 
false ) . _esc( 'For a complete list of the available options please see the mysqldump documentation.' ) . "'";
$help_8 = sprintf( 
_esc( 
'Select the PHP extension used to communicate with the MySQL database.<br>Starting with PHP 5.5.0 the MySQL extension is deprecated. The MySQLi or PDO MySQL is recommended instead. If unsure then select `%s` (MySQLi > PDO > MySQL).' ), 
_esc( 'best available' ) );
$enabled = strToBool( $this->settings['mysql_maint'] );
$disabled = $enabled && $this->enabled ? '' : ' disabled ';
$mysql_maint_opts = array( 
'mysql_maint_analyze' => array( _esc( 'Analyze' ), $help_2 ), 
'mysql_maint_check' => array( _esc( 'Check' ), $help_3 ), 
'mysql_maint_optimize' => array( _esc( 'Optimize' ), $help_4 ), 
'mysql_maint_repair' => array( _esc( 'Repair' ), $help_5 ), 
'mysql_maint_notify' => array( _esc( 'Email on warnings/errors' ), $help_6 ) );
$rows = '';
foreach ( $mysql_maint_opts as $opt_name => $opt_info ) {
$opt_desc = $opt_info[0];
$opt_help = $opt_info[1];
$opt_state = strToBool( $this->settings[$opt_name] ) ? ' checked' : '';
$rows .= '<tr>';
$rows .= '<td>&nbsp;</td>';
$rows .= '<td><input type="checkbox" id="' . $opt_name . '" name="' . $opt_name . '" ' . $disabled .
$opt_state . '><input type="hidden" name="' . $opt_name . '" value="0"></td>';
$rows .= '<td><label for="' . $opt_name . '">' . $opt_desc . '</label><a class="help" onclick=' .
getHelpCall( $opt_help ) . '> [?]</a></td>';
$rows .= '</tr>';
}
$mysql_ext_options = sprintf( '<option value="">%s</option>', _esc( 'best available' ) );
foreach ( array( 'mysql', 'mysqli', 'pdo_mysql' ) as $mysql_ext )
extension_loaded( $mysql_ext ) && $mysql_ext_options .= sprintf( 
'<option value="%s"%s>%s</option>', 
$mysql_ext, 
$mysql_ext == $this->settings['mysql_ext'] ? ' selected="selected"' : '', 
$mysql_ext );
require_once $this->getTemplatePath( 'mysql-expert.php' );
}
protected function hideEditorContent() {
$skip = $this->is_wp || empty( $this->_mysql_host ) || empty( $this->_mysql_user ) ;
$skip && $this->_db_list[] = DB_NAME;
return $skip;
}
}
?>