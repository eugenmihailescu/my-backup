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
 * @version : 0.2.2-10 $
 * @commit  : dd80d40c9c5cb45f5eda75d6213c678f0618cdf8 $
 * @author  : Eugen Mihailescu <eugenmihailescux@gmail.com> $
 * @date    : Mon Dec 28 17:57:55 2015 +0100 $
 * @file    : DiskSourceEditor.php $
 * 
 * @id      : DiskSourceEditor.php | Mon Dec 28 17:57:55 2015 +0100 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
class DiskSourceEditor extends AbstractTargetEditor {
private $_dir_show_size;
protected $_source_type;
protected $_readonly;
protected $_show_dir_buttons;
private function _getJavaScripts() {
$js_root = normalize_path( $this->root );
$this->java_scripts[] = "parent.globals.root='$js_root';";
$this->java_scripts[] = "document.getElementById('excludedirs').value='" .
normalize_path( $this->settings['excludedirs'] ) . "';";
$this->java_scripts[] = "parent.refreshDiskSrcInfo=function(path){parent.asyncGetContent(parent.ajaxurl,'root='+parent.isNull(path,\"$js_root\")+'&" . http_build_query( 
array( 
'action' => 'read_folder_info', 
'tab' => $this->target_name, 
'service' => $this->_source_type, 
'file_function' => $this->target_item->function_name, 
'nonce' => wp_create_nonce_wrapper( 'read_folder_info' ) ) ) . "','folder_info');};";
}
protected function initTarget() {
parent::initTarget();
$this->_readonly = '';
$this->_source_type = 'fssource';
$this->_show_dir_buttons = true;
$this->root = normalize_path( $this->settings['dir'], true );
$this->_dir_show_size = strToBool( $this->settings['dir_show_size'] );
$this->hasInfoBanner = defined( __NAMESPACE__.'\\FILE_EXPLORER' );
$this->hasInfoBannerJS = true;
$this->_getJavaScripts();
}
protected function onGenerateEditorContent() {
$js_root = normalize_path( $this->root );
echo "<div id='folder_mask' class='files_wrapper $this->container_shape'>";
echo "<div id='file_list' class='loading'></div></div>";
$args = array( 
'tab' => $this->target_name, 
'action' => 'read_folder', 
'sender' => $this->_source_type, 
'excludedirs' => $this->settings['excludedirs'], 
'dir_show_size' => $this->_dir_show_size, 
'file_function' => $this->target_item->function_name, 
'tlid' => $this->target_item->uniq_id, 
'nonce' => wp_create_nonce_wrapper( 'read_folder' ) );
$this->java_scripts[] = "parent.refreshFileList=function(sender) {
var file_list = document.getElementById('file_list');
if (file_list) file_list.className += ' loading';
if(sender&&null!==parent.isNull(sender)){if(typeof sender==='string')path=sender;else path=0==sender.title.length?sender.innerHTML:sender.title;}else {path='$js_root';}
document.getElementById('dir').value=path;
parent.asyncGetContent(parent.ajaxurl,'dir='+path+'&" .
http_build_query( $args ) . "','file_list');
parent.refreshDiskSrcInfo(path);
};parent.refreshFileList();";
}
protected function getEditorTemplate() {
$help_1 = "'" .
_esc( 
'Calculates and displays the file/folder size.<br>It may take some time to complete such task so use it with care.<br>Anyway, I will cache the data so that it will load faster next time.<br>If the data is not accurate you should clear the cache.' ) .
"'";
$reload_file_list = "js56816a36b58dc.navFilesList(null,0,'" . wp_create_nonce_wrapper( 'auto_save' ) . "');";
$reload_file_list1 = 'js56816a36b58dc.submitOptions(this,0);';
$show_file_size_toggle = sprintf( 
"if(this.checked)js56816a36b58dc.popupConfirm('%s','%s',null,{'%s':'$reload_file_list1;js56816a36b58dc.removePopupLast();','%s':'document.getElementById(\'dir_show_size\').checked=false;js56816a36b58dc.removePopupLast();'});else $reload_file_list1", 
_esc( 'Warning' ), 
_esc( 
'On large folders (i.e. thousands of files) displaying the folder size may take some time. Are you really sure you want to reload the list with this option on?' ), 
_esc( 'Yes, I`m pretty sure' ), 
_esc( 'Cancel' ) );
$clear_cache_click = sprintf( 
"js56816a36b58dc.popupConfirm('%s','%s',null,{'%s':'document.getElementsByName(\'clear_disk_cache\')[0].value=1;$reload_file_list1;','%s':null});", 
_esc( 'Confirm' ), 
_esc( 
'This command will clear the cache that stores the folder(s) size. The cache improves the speed of loading this page when &lt;b&gt;Show file size&lt;/b&gt; option on. The cache, however, will be automatically recreated whenever is needed.&lt;br;&gt;Are you sure you want do that now?' ), 
_esc( 'Yes, I`m pretty sure' ), 
_esc( 'Cancel' ) );
require_once $this->getTemplatePath( 'disksrc.php' );
}
protected function getExpertEditorTemplate() {
global $exclude_files_factory, $COMPRESSION_NAMES;
$ext = $COMPRESSION_NAMES;
array_walk( $ext, function ( &$item, $key, $prefix ) {
$item = $prefix . $item;
}, '.' );
$example_title = _esc( 'Example' );
$example_desc = _esc( 
'by specifying <i>%s</i> the backup will not include those files that end with %s and so on.' );
$media_ext = array( 'mp3', 'avi', 'jpg' );
$help_1 = "'" .
_esc( 
'Enter the file extensions (comma-delimited) for those file that will be excluded from backup. By default the excluded extensions correspond to some well-known archives (the idea behind is `do not backup a backup`).' );
$help_1 .= getExample( 
$example_title, 
'<ul><li>' . sprintf( 
$example_desc, 
implode( ',', $COMPRESSION_NAMES ), 
implode( ' ' . _esc( 'or' ) . ' ', $ext ) ) . '</li></ul>', 
false ) . "'";
$help_2 = "'" .
_esc( 
'Enter a list of files (comma-delimited) that will be excluded from backup.<br>Note that you may also use the following predefined tags instead of the tag associated absolute filename:<ul>' );
foreach ( $exclude_files_factory as $exclude_file )
$help_2 .= '<li>' . getPopup( 
$exclude_file, 
'&quot;' . @constant( __NAMESPACE__ . '\\' . substr( $exclude_file, 1, strlen( $exclude_file ) - 2 ) ) .
'&quot;', 
true ) . '</li>';
$ex = array( 
_esc( 
'by specifying <i>uploads/backup-*.log*.*, uploads/videos*.avi</i> the backup will exclude all these files that match the specified patterns.' ), 
_esc( 
'by specifying <i>%NONCE_LOGFILE%</i> the backup will exclude the file:<blockquote>' . str_replace( 
ROOT_PATH, 
getPopup( 'ROOT', '&quot;' . ROOT_PATH . '&quot;', true ) . DIRECTORY_SEPARATOR, 
NONCE_LOGFILE ) . '</blockquote>' ) );
$help_2 .= '</ul>' . getExample( 
$example_title, 
'<ul><li>' . implode( '</li><li>', $ex ) . '</li></ul>', 
false ) . "'";
$help_3 = "'" .
_esc( 
'Check this option if you want to not create backup for those files which are file links. Enable this option especially when you have cyclic links that may create infinite loops.' ) .
"'";
if ( defined( __NAMESPACE__.'\\PCLZIP' ) ) {
$help_4 = "'" .
_esc( 
'Enter the file extensions (comma-delimited) for those file which will be included in backup but not compressed. By default the excluded extensions correspond to some well-known media formats.' );
$help_4 .= '<br>' . _esc( 'This option requires zlib extension and WP PclZip class support.' );
$help_4 .= getExample( 
$example_title, 
'<ul><li>' . sprintf( 
$example_desc, 
implode( ',', $media_ext ), 
implode( ' ' . _esc( 'or' ) . ' ', $media_ext ) ) . '</li></ul>', 
false ) . "'";
}
require_once $this->getTemplatePath( 'disksrc-expert.php' );
}
}
?>