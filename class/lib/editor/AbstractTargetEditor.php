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
 * @file    : AbstractTargetEditor.php $
 * 
 * @id      : AbstractTargetEditor.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;

define ( 'TARGET_TABLE_STYLE', '' ); 
abstract class AbstractTargetEditor {
private $_childInit; 
private $isFeatureInstalled; 
protected $license;
protected $license_id;
protected $java_scripts;
protected $java_scripts_load;
protected $java_scripts_charts;
protected $is_wp;
protected $enabled;
protected $enabled_tag;
protected $target_item;
protected $target_name;
protected $root;
protected $age;
protected $ext_filter;
protected $function_name;
protected $folder_style;
protected $settings;
protected $container_shape;
protected $registered_targets;
protected $hasInfoBanner; 
protected $hasInfoBannerJS; 
protected $hasCustomFrame; 
protected $infoBannerCSSClass; 
protected $infoBannerCSSStyle;
protected $infoBannerRoot; 
protected $inBetweenContent; 
protected $customTitle; 
public $hasPasswordField; 
private function _getEditorTemplate() {
ob_start ();
$this->getEditorTemplate ();
$result = ob_get_contents ();
ob_end_clean ();
return $result;
}
private function _showExpertEditor() {
ob_start ();
$this->getExpertEditorTemplate ();
$expert_rows = ob_get_contents ();
ob_end_clean ();
if (! empty ( $expert_rows ))
echo $this->insertEditorTemplate ( _esc ( 'Expert settings' ), $expert_rows, $this->target_name . '_expert_box', true );
}
abstract protected function getEditorTemplate();
protected function initTarget() {
global $license, $license_id, $TARGET_NAMES, $COMPRESSION_NAMES, $registered_targets;
$this->license = $license;
$this->license_id = $license_id;
$this->target_name = $TARGET_NAMES [$this->target_item->type];
$this->isFeatureInstalled = feature_is_licensed ( $this->target_name, $this->license [$this->license_id], true );
$this->settings = $this->target_item->targetSettings;
$this->function_name = $this->target_item->function_name;
$this->folder_style = $this->target_item->folder_style;
isset ( $this->settings ['compression_type'] ) && $this->ext_filter = $COMPRESSION_NAMES [$this->settings ['compression_type']];
if (isset ( $this->settings [$this->target_name] )) {
$root = $this->settings [$this->target_name];
$this->root = normalize_path ( $root, true );
}
isset ( $this->settings [$this->target_name . '_enabled'] ) && $this->enabled = strToBool ( $this->settings [$this->target_name . '_enabled'] );
$this->enabled_tag = $this->enabled ? '' : ' disabled';
isset ( $this->settings [$this->target_name . '_age'] ) && $this->age = $this->settings [$this->target_name . '_age'];
$this->is_wp = is_wp ();
$this->hasInfoBanner = false;
$this->hasInfoBannerJS = false;
$this->infoBannerRoot = $this->root;
$this->_childInit = true;
}
protected function getImgURL($filename) {
return plugins_url_wrapper ( 'img/' . $filename, IMG_PATH );
}
protected function onGenerateEditorContent() {
}
protected function getTemplatePath($template_file, $path = null, $quiet = false) {
global $TARGET_NAMES;
$rel_path = __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
$path = null == $path ? $rel_path : $path;
if (DIRECTORY_SEPARATOR != substr ( $path, - 1 ))
$path .= DIRECTORY_SEPARATOR;
$filename = $path . $template_file;
if (file_exists ( $filename ))
return $filename;
if ($quiet)
return false;
$filename = $rel_path . '_not_found_template_.php';
echo sprintf ( '<tr><td colspan="3"><p style="color:red">%s</p></td></tr>', sprintf ( _esc ( 'Cannot render this section due to missing template file (%s).' ), $template_file ) );
printf ( '<tr><td colspan="3">' . _esc ( 'Please reinstall the product. If the error persists please %sfill an error report' ) . '</a>.</td></tr>', '<a href="' . getTabLink ( $TARGET_NAMES[APP_SUPPORT] ) . '&support_category=error">' );
return $filename;
}
protected function getExpertEditorTemplate() {
}
protected function validateEditor() {
return true;
}
protected function hideEditorContent() {
return false;
}
protected function insertEditorTemplate($title = null, $editor_template = '', $id = null, $close_divs = false) {
if (null == $title && false !== $this->customTitle)
$title = $this->customTitle;
ob_start ();
echo "<div " . (null != $id ? "id='$id'" : '') . " class='postbox {$this->container_shape}'>" . PHP_EOL;
echo '<h3>' . (null == $title ? $this->target_item->title : $title) . '</h3>' . PHP_EOL;
echo '<div class="inside">' . PHP_EOL;
echo '<table name="' . strtolower ( get_class ( $this ) ) . '"' . TARGET_TABLE_STYLE . '>' . $editor_template . '</table>' . PHP_EOL;
if ($close_divs)
echo '</div>' . PHP_EOL . '</div>' . PHP_EOL;
$result = ob_get_contents ();
ob_end_clean ();
return $result;
}
protected function getRefreshFolderJS() {
return $this->enabled && ! $this->hideEditorContent () ? "var sb=document.getElementById('sortby'),sa=document.getElementById('sortasc'),t=document.getElementById('{$this->target_name}'),tid=document.getElementById('{$this->target_name}_path_id');if(t&&tid)js55f82caaae905.refreshFolderList(t.value,tid.value,js55f82caaae905.isNull(sb.value,null),js55f82caaae905.isNull(sa.value,null));" : ("submitOptions(this,0);");
}
function __construct($target_item) {
if (null == $target_item)
throw new MyException ( sprintf ( _esc ( 'Invalid item_id supplied in %s constructor' ), get_class ( $this ) ) );
global $container_shape, $registered_targets;
$this->target_item = $target_item;
$this->java_scripts = array ();
$this->java_scripts_load = array ();
$this->java_scripts_charts = array ();
$this->isFeatureInstalled = - 1;
$this->hasPasswordField = false;
$this->_childInit = false;
$this->infoBannerCSSClass = null;
$this->infoBannerCSSStyle = null;
$this->inBetweenContent = null;
$this->hasCustomFrame = false;
$this->customTitle = false;
$this->container_shape = $container_shape;
$this->registered_targets = $registered_targets;
try {
$this->initTarget ();
} catch ( MyException $e ) {
echo getSpanE ( $e->getMessage (), 'red', 'bold' );
}
}
public function getJavaScriptsLoad() {
if (0 != $this->isFeatureInstalled) {
$this->java_scripts_load [] = 'parent.toggle_header("' . $this->target_name . '_expert_box");'; 
return $this->java_scripts_load;
}
return array ();
}
public function getJavaScripts() {
parse_str ( $_SERVER ['QUERY_STRING'], $fields );
isset ( $fields ['error_code'] ) && isset ( $fields ['error_message'] ) && $this->java_scripts [] = 'parent.popupError("' . _esc ( 'Error detected' ) . '","' . sprintf ( _esc ( 'It seems that the last request sent encountered a problem:%s' ), '<br>' . $fields ['error_message'] . '(' . $fields ['error_code'] . ')' ) . '");';
$this->java_scripts [] = 'parent.this_url="' . selfURL () . '";';
if (0 != $this->isFeatureInstalled) {
$this->java_scripts [] = "parent.plugin_dir='" . addslashes ( dirname ( realpath($_SERVER ['SCRIPT_NAME'] )) ) . "';";
if ($this->hasPasswordField && ! isSSL ())
$this->java_scripts ['ssl'] = "setInterval(parent.fadeSSLIcons," . SSL_ALERT_FADE_INTERVAL . ");";
if ($this->isFeatureInstalled < 0)
$this->java_scripts [] = 'document.getElementById("content-container").className+=" trial-version";';
if ($this->hasInfoBanner && ! $this->hasInfoBannerJS)
$this->java_scripts [] = "parent.asyncGetContent(parent.ajaxurl,'" . http_build_query ( array (
'action' => 'read_folder_info',
'tab' => $this->target_name,
'service' => $this->target_name,
'root' => $this->infoBannerRoot,
'nonce' => wp_create_nonce_wrapper ( 'read_folder_info' ) 
) ) . "','folder_info');";
return $this->java_scripts;
}
return array ();
}
public function getJavaScriptsCharts() {
if (0 != $this->isFeatureInstalled)
return $this->java_scripts_charts;
return array ();
}
public function showEditor() {
if (! $this->_childInit)
throw new MyException ( sprintf ( _esc ( 'Class %s has not called parent %s::initTarget method. This is a MUST by design.' ), get_class ( $this ), get_class () ) );
if (0 == $this->isFeatureInstalled || ! $this->validateEditor ())
return;
$section_name = ucwords ( $this->target_name ) . '-Options';
insertHTMLSection ( $section_name );
$editor_template = $this->_getEditorTemplate ();
if (! $this->hasCustomFrame)
echo $this->insertEditorTemplate ( null, $editor_template );
else
echo $editor_template;
$skip = $this->hideEditorContent ();
if (! $skip) {
require_once 'target-content-functions.php';
$this->onGenerateEditorContent ();
}
if ($this->hasInfoBanner) {
echo '<br>' . PHP_EOL;
echo '<div id="folder_info" ' . (null == $this->infoBannerCSSStyle ? '' : ('style="' . $this->infoBannerCSSStyle . '"')) . ' class="' . (null == $this->infoBannerCSSClass ? 'folder_info' : $this->infoBannerCSSClass) . '"></div>' . PHP_EOL;
}
if (! $this->hasCustomFrame)
echo '</div>' . PHP_EOL . '</div>' . PHP_EOL;
if (! $skip) {
if (null != $this->inBetweenContent)
echo $this->inBetweenContent;
}
$this->_showExpertEditor (); 
insertHTMLSection ( $section_name, true );
}
}
?>
